<?php
require_once dirname(__FILE__) . '/../helpers/UserHelper.php';
require_once dirname(__FILE__) . '/../../../../creds/dhpath.inc';
require_once dirname(__FILE__) . '/../../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../../../creds/mixpanel_coursepicker.inc';
require_once dirname(__FILE__) . '/../helpers/session.php';
require_once dirname(__FILE__) . '/../helpers/UserHelper.php';
require_once dirname(__FILE__) . '/../../includes/mixpanel/lib/Mixpanel.php';
require_once dirname(__FILE__) . '/../../includes/securimage/securimage.php';
//Initializing variables

$session = new Session();
$result = array();
$securimage = new Securimage();

$debug = DEBUGSTATUS;


//Set up debug stuff
//When  not debugging, log to a file!
if (!$debug) {
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
    error_log("You messed up!", 3, ERROR_PATH);
}

// get the Mixpanel class instance, replace with your
// load production token
if (!$debug){
    $mp = Mixpanel::getInstance(CP_PROD_MIXPANEL_TOKEN);
}else{
    //load dev token
    $mp = Mixpanel::getInstance(CP_DEV_MIXPANEL_TOKEN);
}


/**
 * Function to autoload classes needed during serialization/unserialization
 *
 * @param string $class_name name of the Class being loaded
 *
 * @return void
 */
function __autoload($class_name)
{
    include dirname(__FILE__) . '/../models/'. $class_name . '.php';
}

/**
 * http://stackoverflow.com/questions/1846202/php-how-
 * to-generate-a-random-unique-alphanumeric-string
 * http://www.openwall.com/articles/PHP-Users-Passwords
 * Function to generate a cryptographically secure token
 *
 * @param int $length default is 40
 *
 * @return string token
 *
 */
function generateToken($length = 20)
{
    //bin2hex(openssl_random_pseudo_bytes(16));
    //base64_encode(openssl_random_pseudo_bytes($length, $strong));
    
    if (function_exists('openssl_random_pseudo_bytes')) {
        $token = bin2hex(openssl_random_pseudo_bytes($length, $strong));
        if ($strong) {
            return $token;
        }
    }

    return sha1(uniqid(mt_rand(), true));
}

/**
 * Function to check if the token is an alphanumeric string
 * 
 * @param string $token the token
 * 
 * @return string 'OK' if alphanumeric and not 'OK' otherwise
 * 
 */ 
function isValidToken($token){
    if (strlen($token) != 32){        
        return 'Code a: Invalid token';
    }
    if (ctype_alnum($token)) {
        return 'OK';
    }else{
        return "Code b: Invalid token.";
    }    
}


/* The $pvt debugging messages may contain characters that would need to be
 * quoted if we were producing HTML output, like we would be in a real app,
 * but we're using text/plain here.  Also, $debug is meant to be disabled on
 * a "production install" to avoid leaking server setup details. */
	//exit("An error occurred ($msg).\n");
function fail($pub, $pvt = ''){
	global $debug;
	$msg = $pub;
	if ($debug && $pvt !== '')
		$msg .= ": $pvt";
	return $msg;
}

/**
 * Returns the $_POST value matching the provided key
 * with the filter (FILTER_SANITIZE_MAGIC_QUOTES)
 * @param String $var key in $_POST variable
 * @return String $val value matching $_POST['key']
 */
function getPost($var){
	$val = filter_var($_POST[$var],FILTER_SANITIZE_MAGIC_QUOTES);
	return $val;
}

/**
 * Ensure the username conforms to alphanumeric characters
 *
 * @param string $username the username entered
 *
 * @return string 'OK' if good and anything else is bad
 *
 */
function usernameCheck($username)
{
    if (!preg_match('/^[a-zA-Z0-9_]{1,60}$/', $username)) {
        return "Please use only alphanumeric characters.";
    }

    return 'OK';
}


/**
 * http://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
 */ 
function get_ip_address(){
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $ip){
                $ip = trim($ip); // just to be safe

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                    return $ip;
                }
            }
        }
    }
}

function validate_ip($ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false)
    {
        return false;
    }

    //self::$ip = sprintf('%u', ip2long($ip)); // you seem to want this

    return true;
}

/**
 * Sends an email to the user to reset their password
 * 
 * @param String $username username
 * @param String $email User's email
 * @param String $token the reset token
 * @param int    $ip the ip originating the request
 * @param String $reset_expiration expiration date of the token
 * 
 * @return '' if OK and not otherwise.
 * 
 */ 
function resetPasswordEmail($username,$email,$token,$ip,$reset_expiration){    
    require_once dirname(__FILE__) . '/../../../../creds/mail.inc';
    require_once dirname(__FILE__) . '/../../includes/phpmailer/PHPMailerAutoload.php';
    
    $mail = new PHPMailer;
    $mail->isSMTP();            // Set mailer to use SMTP
    $mail->Host = CP_SMTP_SERVER;               // Specify main and backup server
    $mail->SMTPAuth = true;                     // Enable SMTP authentication
    $mail->Username = CP_EMAIL;        // SMTP username
    $mail->Password = CP_MAIL_SECRET;        // SMTP password
    $mail->SMTPSecure = 'tls';                  // Enable encryption, 'ssl' also accepted

    $mail->From = CP_REPLYTO;
    $mail->FromName = CP_NAME;
    $mail->addAddress($email, $username);  // Add a recipient
    $mail->addReplyTo(CP_REPLYTO, CP_NAME);
    // Set word wrap to 50 characters
    $mail->WordWrap = 50; 
        
    $mail->Subject = CP_RESET_SUBJECT;
    $body = '<div style="background-color:#F0F0F0;color:#004A61;font-size:20px;">Password reset request for <a href="http://bit.ly/coursepicker" title="UGA Course Picker by Jane Ullah">Course Picker</a>.</div>';
    $body .= '<div style="display:block;">This request occurred on ' . date('l jS \of F Y h:i:s A');
    $body .= ' from the IP Address ('.$ip.').</div>';
    $body .= '<div style="display:block;">Please reset your password by <a href="http://apps.janeullah.com/coursepicker/reset.php?token=' . $token. '" title="Click to reset your password.">clicking this link.</a></div>';
    $body .= '<div style="display:block;font-weight:bold;">This reset token will remain active for 24 hours. So please reset your password as soon as you can within this timeframe.</div>';
    $body .= '<div style="display:block;">If this reset request did not come from you, simple ignore this email.</div>';
    $mail->Body = $body; 
    $plaintext = "You requested a password reset for your account at Course Picker [http://apps.janeullah.com/coursepicker/]";
    $plaintext .= "Please reset your password by copying this link and pasting into your web browser: http://apps.janeullah.com/coursepicker/activate.php?token=" . $token;
    $plaintext .= 'This link will <strong>expire on ' . $reset_expiration . '<strong>';
    $mail->AltBody = $plaintext;

    if(!$mail->send()) {
       $msg = 'Message could not be sent.';
       $msg .= 'Mailer Error: ' . $mail->ErrorInfo;
       return $msg;
    }

    return '';
}

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
    $action = getPost('action');
    if ($action){
        if (strcmp($action, "confirmEmail") == 0){
            $email1 = getPost('confirmEmail1');
            $email2 = getPost('confirmEmail2');
            if ($email1 and $email2 and $email1 === $email2){ 
                $email = filter_var($email1, FILTER_SANITIZE_EMAIL);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
                    $result['errorMessage'] = "Invalid email address.";
                    echo json_encode($result);
                }else{                               
                    //Grab tokens which must match
                    $token = getPost('token');
                    $validToken = $session->validToken;              
                    if ($token && $validToken && $token === $validToken){
                        //Check that there's a matching user        
                        $db = new UserHelper();
                        //check that the token matches the email address
                        try{
                            $user = $db->findUserToBeActivated($token,$email);
                            if ($user){
                                if (!$user->isVerified()){
                                    //Get ip address                     
                                    $activation_ip = ip2long(get_ip_address());
                                    $res = $db->saveMetadata($user,$token,$activation_ip,1);
                                    if ($res){
                                        $result['errorMessage'] = "";
                                        $mp->track("user activation", array("success" => $email));
                                        echo json_encode($result);
                                    }else{
                                        $result['errorMessage'] = fail("Unable to save user metadata to database",$db->errorMessage);
                                        echo json_encode($result);
                                    }                                
                                }else{ 
                                    $result['errorMessage'] = "User already verified! If you are not actually verified, please send an email to welcome@janeullah.com";
                                    echo json_encode($result);
                                }
                            }else{
                                $result['errorMessage'] = fail("Please check the email and token combination.",$db->errorMessage);
                                echo json_encode($result);
                            }
                        }catch(Exception $e){
                            $result['errorMessage'] = fail("Error performing user lookup",$db->errorMessage);
                            echo json_encode($result);
                        }
                    }else{
                        $result['errorMessage'] = "Mismatch between token and session token";
                        $result['validToken'] = $session->validToken;
                        $result['token'] = $token;
                        echo json_encode($result);
                    }
                }
            }else{
                $result['errorMessage'] = "The two email fields must match";
                echo json_encode($result);
            }
        }elseif (strcmp($action, "forgotPassword") == 0){
            $code = getPost("captcha_code");
            if ($securimage->check($code) == false){
                $result['errorMessage'] = "Failed captcha test. Please try again or request a new image.";
                $result['captcha'] = $code;
                $result['actual'] = $securimage->getCode();
                //print_r($securimage);
                echo json_encode($result);
            }else{
                $username = getPost("forgotPwdUsername");
                $email = getPost("forgotPwdEmail");
                if ($username && $email){
                    //Validate username
                    $usernameCheck = usernameCheck($username);
                    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                    if (strcmp($usernameCheck, 'OK') != 0) {
                        $result['errorMessage'] = "Error validating the username provided.";
                        echo json_encode($result); 
                    }elseif (filter_var($email, FILTER_VALIDATE_EMAIL)){
                        //Check the username and email combo in database
                        //If there is a hit, then generate token for use in resetting,
                        //create entries in user_metadata and set expiration date for the reset token
                        $db = new UserHelper();
                        $user = $db->checkUser($username,$email);
                        if ($user){
                            if ($user->isVerified()){
                                $token = generateToken();
                                $reset_ip = get_ip_address();
                                //reset token will expire after 24 hours
                                $date = new DateTime(null, new DateTimeZone('America/New_York'));
                                $date->modify('+1 day');
                                $reset_expiration = $date->format('Y-m-d H:i:s');
                                $res = $db->logResetRequest($user,$token,ip2long($reset_ip),$reset_expiration);
                                if ($res){
                                    $res = resetPasswordEmail($user->getUsername(),$user->getEmail(),$token,$reset_ip,$reset_expiration);
                                    if (strlen($res) == 0){
                                        $mp->track("user forgot password", array("success" => $email));
                                        $result['errorMessage'] = "";
                                        echo json_encode($result); 
                                    }else{  
                                        $result['errorMessage'] = $res;
                                        echo json_encode($result); 
                                    }
                                }else{
                                    $result['errorMessage'] = fail("Error logging your reset request. Apologies for that; Please send an email to welcome@janeullah.com for help.",$db->errorMessage);
                                    echo json_encode($result);
                                }
                            }else{
                                $result['errorMessage'] = "Please activate your account first.";
                                echo json_encode($result);     
                            }                       
                        }else{
                            $result['errorMessage'] = "Please make sure you entered the exact username and email address combination you signed up with.";
                            echo json_encode($result);                            
                        }
                    }else{                    
                        $result['errorMessage'] = "Error validating the email address.";
                        echo json_encode($result);
                    }
                }else{
                    $result['errorMessage'] = "Please make sure both the username and email fields are filled out.";
                    $result['username'] = $username;
                    $result['email'] = $email;
                    echo json_encode($result);
                }
            }
        }elseif (strcmp($action, "resetPassword") == 0){
            $username = getPost("username");
            $email = getPost("email");       
            $session->resetRequestValidated = false; 
            $reset_token = $session->validToken;
            if ($username && $email){
                //Validate username
                $usernameCheck = usernameCheck($username);
                $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                if (strcmp($usernameCheck, 'OK') != 0) {
                    $result['errorMessage'] = "Error validating the username provided.";
                    echo json_encode($result); 
                }elseif (filter_var($email, FILTER_VALIDATE_EMAIL)){
                    //Check the username and email combo in database
                    //If there is a hit, then generate token for use in resetting,
                    //create entries in user_metadata and set expiration date for the reset token
                    $db = new UserHelper();
                    $user = $db->checkUser($username,$email);
                    if ($user && $user instanceof User){
                        if ($user->isVerified()){
                            $res = $db->validateToken($user,$reset_token);
                            if ($res){
                                $session->resetRequestValidated = true;
                                $session->requestingUser = serialize($user);
                                $result['errorMessage'] = "";
                                $mp->track("user reset password", array("success" => $email));
                                echo json_encode($result);  
                            }else{
                                $result['errorMessage'] = fail("This token is either invalid or has expired. Please request another reset token and remember to use it within 24 hours.",$db->errorMessage);
                                $result['infoMessage'] = $db->infoMessage . "--" . $res;
                                $result['username'] = $username;
                                $result['token'] = $reset_token;
                                echo json_encode($result);  
                            }
                        }else{
                            $result['errorMessage'] = "Please activate your account first.";
                            echo json_encode($result);  
                        }
                    }else{
                        $result['errorMessage'] = "Please make sure you entered the exact username and email address combination you signed up with.";
                        echo json_encode($result); 
                    }
                }else{
                    $result['errorMessage'] = "Error validating the email address.";
                    echo json_encode($result);
                }
            }else{
                $result['errorMessage'] = "Please make sure both the username and email fields are filled out.";
                $result['username'] = $username;
                $result['email'] = $email;
                echo json_encode($result);
            }            
        }else{
            $result['errorMessage'] = "Invalid action requested.";
            echo json_encode($result);
        }
    }else{
        $result['errorMessage'] = "Invalid POST request.";
        echo json_encode($result);
    }
}else{
    $result['errorMessage'] = "Invalid request method.";
    echo json_encode($result);
}
?>
