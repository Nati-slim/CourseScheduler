<?php
require_once dirname(__FILE__) . '/../helpers/UserHelper.php';
require_once dirname(__FILE__) . '/../../../../creds/dhpath.inc';
require_once dirname(__FILE__) . '/../../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../../../creds/mixpanel_coursepicker.inc';
require_once dirname(__FILE__) . '/../../includes/mixpanel/lib/Mixpanel.php';
$result = array();
session_start();
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
    $mp = Mixpanel::getInstance(CP_PROD_MIXPANEL_API_KEY);
}else{
    //load dev token
    $mp = Mixpanel::getInstance(CP_DEV_MIXPANEL_API_KEY);
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
  * Retrieves the best guess of the client's actual IP address.
  * Takes into account numerous HTTP proxy headers due to variations
  * in how different ISPs handle IP addresses in headers between hops.
  */
function get_ip_address() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])){
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    // check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple ips exist in var
        $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) {
            if (validate_ip($ip)){
                return $ip;
            }
        }
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED'])){
        return $_SERVER['HTTP_X_FORWARDED'];
    }
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])){
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR'])){
        return $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED'])){
        return $_SERVER['HTTP_FORWARDED'];
    }
    // return unreliable ip since all else failed
    return $_SERVER['REMOTE_ADDR'];
}

function resetPasswordEmail($username,$email,$token,$ip){    
    require_once '../../../../creds/mail.inc';
    require_once '../../includes/phpmailer/PHPMailerAutoload.php';
    
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
        
    $mail->Subject = CP_WELCOME_SUBJECT;
    $body = '<div style="background-color:#F0F0F0;color:#004A61;font-size:20px;">Thank you for registering to use <a href="http://bit.ly/coursepicker" title="UGA Course Picker by Jane Ullah">Course Picker</a>.</div>';
    $body .= '<div style="display:block;">This registration occurred on ' . date('l jS \of F Y h:i:s A');
    $body .= ' from the IP Address ('.$ip.').</div>';
    $body .= '<div style="display:block;">Please activate your account by <a href="http://apps.janeullah.com/coursepicker/activate.php?token=' . $token. '" title="Click to activate your account.">clicking this link.</a></div>';
    $mail->Body = $body; 
    $plaintext = "Thank you for registering to use Course Picker [http://apps.janeullah.com/coursepicker/]";
    $plaintext .= "Please activate your account by copying this link and pasting into your web browser: http://apps.janeullah.com/coursepicker/activate.php?token=" . $token;
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
                    $validToken = $_SESSION['validToken'];                    
                    if ($token and $validToken and $token === $validToken){
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
                        echo json_encode($result);
                    }
                }
            }else{
                $result['errorMessage'] = "The two email fields must match";
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
