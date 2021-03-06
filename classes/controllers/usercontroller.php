<?php
/**
 * Controller for registering users, letting users login, etc.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 4.0 of the
 * Creative Commons Attribution-ShareAlike 4.0 International License
 * that is available through the world-wide-web at the following URI:
 * http://creativecommons.org/licenses/by-sa/4.0/.
 *
 * @category   CategoryName
 * @package    PackageName
 * @author     Original Author <jane@janeullah.com>
 * @license    http://creativecommons.org/licenses/by-sa/4.0/  Creative Commons Attribution-ShareAlike 4.0 International License
 * @version    GIT: $Id$
 * @link       https://github.com/janoulle/CourseScheduler
 * @since      N/A
 * @deprecated N/A
 */
require_once dirname(__FILE__) . '/../../../../creds/mixpanel_coursepicker.inc';
require_once dirname(__FILE__) . '/../../../../creds/dhpath.inc';
require_once dirname(__FILE__) . '/../../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../../../creds/captcha.inc';
require_once dirname(__FILE__) . '/../models/Course.php';
require_once dirname(__FILE__) . '/../models/Section.php';
require_once dirname(__FILE__) . '/../models/Meeting.php';
require_once dirname(__FILE__) . '/../models/UserSchedule.php';
require_once dirname(__FILE__) . '/../helpers/session.php';
require_once dirname(__FILE__) . '/../helpers/UserHelper.php';
require_once dirname(__FILE__) . '/../../includes/phppass/PasswordHash.php';
require_once dirname(__FILE__) . '/../../includes/recaptcha/recaptchalib.php';
require_once dirname(__FILE__) . '/../../includes/mixpanel/lib/Mixpanel.php';
$session = new Session();
$result = array();
$debug = DEBUGSTATUS;

// get the Mixpanel class instance, replace with your
// load production token
if (!$debug){
    $mp = Mixpanel::getInstance(CP_PROD_MIXPANEL_TOKEN);
}else{
    //load dev token
    $mp = Mixpanel::getInstance(CP_DEV_MIXPANEL_TOKEN);
}

//Set up debug stuff
//When  not debugging, log to a file!
if (!$debug) {
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
}

//http://www.openwall.com/articles/PHP-Users-Passwords
// Base-2 logarithm of the iteration count used for password stretching
$hash_cost_log2 = 8;
// Do we require the hashes to be portable to older systems (less secure)?
$hash_portable = false;
//Hasher information
$hasher = new PasswordHash($hash_cost_log2, $hash_portable);

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
 * The $pvt debugging messages may contain characters that would need to be
 * quoted if we were producing HTML output, like we would be in a real app,
 * but we're using text/plain here.  Also, $debug is meant to be disabled on
 * a "production install" to avoid leaking server setup details.
 *
 * @param string $pub public details of the error
 * @param string $pvt Private details of the error
 *
 * @return string $msg The error message
 *
 */
function fail($pub, $pvt = '')
{
    global $debug;
    $msg = $pub;
    if ($debug && $pvt !== '') {
        $msg .= ": $pvt";
    }

    return $msg;
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

    self::$ip = sprintf('%u', ip2long($ip)); // you seem to want this

    return true;
}

/**
 * Get either a Gravatar URL or complete image tag for a specified email address.
 *
 * @param string $email The email address 
 * @param string $s     Size in pixels, defaults to 80px [ 1 - 2048 ]
 * @param string $d     Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
 * @param string $r     Maximum rating (inclusive) [ g | pg | r | x ]
 * @param boole  $img   True to return a complete IMG tag False for just the URL
 * @param array  $atts  Optional, additional key/value attributes to include in the IMG tag
 *
 * @return String containing either just a URL or a complete image tag
 *
 * @source http://gravatar.com/site/implement/images/php/
 */
function getGravatar($email, $s = 40, $d = 'mm', $r = 'g', $img = false, $atts = array())
{
    $url = 'http://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&d=$d&r=$r";
    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val) {
            $url .= ' ' . $key . '="' . $val . '"';
        }
        $url .= ' />';
    }

    return $url;
}


/**
 * Function to undo effects of magic quotes
 * Returns the $_POST value matching the provided key
 *
 * @param String $var key in $_POST variable
 *
 * @return String $val value matching $_POST['key']
 *
 */
function getPost($var)
{
    $val = filter_var($_POST[$var], FILTER_SANITIZE_MAGIC_QUOTES);

    return $val;
}

/**
 * Simple regex to check password validity
 *
 * @param String $pass user's password which must be between 8 and 50, and needs to include number and symbol
 *
 * @return String 'OK' if password is good; Anything else is bad
 *
 */
function pwdcheck($pass)
{
    if (preg_match("#.*^(?=.{8,50})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $pass)) {
        return 'OK';
    }

    return "Invalid password. Please enter a password or phrase with the following characteristics: 8 - 50 characters and at least 1 uppercase, 1 number and 1 symbol.";
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
function generateUserid($length = 16)
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
 * Sends an email to the user to confirming the password change.
 * 
 * @param String $username username
 * @param String $email User's email
 * @param int    $reset_ip the ip originating the request
 * 
 * @return '' if OK and not otherwise.
 * 
 */ 
function sendPasswordChangeEmail($username,$email,$reset_ip){    
    require_once dirname(__FILE__) . '/../../../../creds/mail.inc';
    require_once dirname(__FILE__) . '/../../includes/phpmailer/PHPMailerAutoload.php';
    
    $mail = new PHPMailer;
    $mail->isSMTP();                    // Set mailer to use SMTP
    $mail->Host = CP_SMTP_SERVER;       // Specify main and backup server
    $mail->SMTPAuth = true;             // Enable SMTP authentication
    $mail->Username = CP_EMAIL;         // SMTP username
    $mail->Password = CP_MAIL_SECRET;   // SMTP password
    $mail->SMTPSecure = 'tls';          // Enable encryption, 'ssl' also accepted

    $mail->From = CP_REPLYTO;
    $mail->FromName = CP_NAME;
    $mail->addAddress($email, $username);  // Add a recipient
    $mail->addReplyTo(CP_REPLYTO, CP_NAME);
    // Set word wrap to 50 characters
    $mail->WordWrap = 50; 
        
    $mail->Subject = CP_RESET_CONF_SUBJECT;
    $date = new DateTime(null, new DateTimeZone('America/New_York'));
    $body = '<div style="background-color:#F0F0F0;color:#004A61;font-size:20px;">Password Reset Successful for <a href="http://bit.ly/coursepicker" title="UGA Course Picker by Jane Ullah">Course Picker</a>.</div>';
    $body .= '<div style="display:block;">Your password was succesfully changed on ' . $date->format('l jS \of F Y h:i:s A');
    $body .= ' from the IP Address ('.$reset_ip.').</div>';
    $body .= '<div style="display:block;">If this reset request did not come from you, please reach out to welcome@janeullah.com immediately.</div>';
    $mail->Body = $body; 
    $plaintext = "Your password was successfully reset for your account at Course Picker [http://apps.janeullah.com/coursepicker/]";
    $plaintext .= 'This change happened on ' . $date->format('l jS \of F Y h:i:s A') . ' from the IP address ' . $ip;
    $plaintext .= "If this reset request did not come from you, please reach out to welcome@janeullah.com immediately.";
    $mail->AltBody = $plaintext;

    if(!$mail->send()) {
       $msg = 'Message could not be sent.';
       $msg .= 'Mailer Error: ' . $mail->ErrorInfo;
       return $msg;
    }

    return '';
}

function sendWelcomeEmail($username,$email,$token,$ip){    
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
    if (strcmp($action, "newuser") == 0) {
        //check captcha
        $privatekey = CP_PRIVATE_KEY;
        $resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
        if (!$resp->is_valid) {
            $result['errorMessage'] = fail('Nice try, ROBOT! Check the Recaptcha response and try again. Sorry if you\'re really human.', $resp->error);
            $session->errorMessage = $result['errorMessage'];
            echo json_encode($result);
        } else {
            $email = getPost('email');
            $username = getPost('username');
            $password1 = getPost('password1');
            $password2 = getPost('password2');
            if ($password1 === $password2) {
                //Validate password
                //$check = pwdcheck($pass)) !== 'OK
                $pwdCheck = pwdcheck($password1);
                if ($pwdCheck === 'OK') {
                    //Validate email address
                    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        //Validate username
                        $usernameCheck = usernameCheck($username);
                        if (strcmp($usernameCheck, 'OK') == 0) {
                            //Get user id from session or generate one.
                            $userid = $session->userid;
                            if (!$userid) {
                                $userid = generateUserID();
                            }
                            //Hash password
                            $hash = $hasher->HashPassword($password1);
                            if (strlen($hash) < 20) {
                                $result['errorMessage'] = fail('Failed to hash new password', $hash);
                                $session->errorMessage = $result['errorMessage'];
                                echo json_encode($result);
                            } else {
                                unset($hasher);
                                $db = new UserHelper();
                                //generate token
                                $token = generateToken(16);
                                $ip = get_ip_address();
                                //http://stackoverflow.com/questions/4982701/best-way-to-store-ip-in-database
                                $res = $db->addUser($userid, $username, $email, $hash,$token,ip2long($ip));
                                if ($res === false) {
                                    $result['errorMessage'] = fail('Problem creating this user account.', $db->errorMessage);
                                    $session->errorMessage = $result['errorMessage'];
                                    echo json_encode($result);
                                } else {
                                    //store user id in session after user id logged in.
                                    $session->id = $res;
                                    $session->userid = $userid;
                                    $result['errorMessage'] = sendWelcomeEmail($username,$email,$token,$ip);
                                    $session->errorMessage = $result['errorMessage'];
                                    if (strlen($result['errorMessage']) != 0){
                                        $result['ip'] = $ip;
                                        $result['token'] = $token; 
                                    }
                                    $mp->track("user signup", array("userid" => $userid));
                                    echo json_encode($result);
                                }
                            }
                        } else {
                            $result['errorMessage'] = $usernameCheck;
                            $session->errorMessage = $result['errorMessage'];
                            echo json_encode($result);
                        }
                    } else {
                        $result['errorMessage'] = "Invalid email address found.";
                        $session->errorMessage = $result['errorMessage'];
                        echo json_encode($result);
                    }
                } else {
                    $result['errorMessage'] = $pwdCheck;
                    $session->errorMessage = $result['errorMessage'];
                    echo json_encode($result);
                }
            } else {
                $result['errorMessage'] = "Passwords don't match. Please make sure both passwords are the same.";
                $session->errorMessage = $result['errorMessage'];
                echo json_encode($result);
            }
        }
    } elseif (strcmp($action, "login") == 0) {
        if ($session->loggedIn) {
            $result['errorMessage'] = "You're already logged in as " . $session->username;
            $session->errorMessage = $result['errorMessage'];
            echo json_encode($result);
        } else {
            $username = getPost('loginUsername');
            $password = getPost('loginPassword');
            if ($password && $username) {
                //just a cursory check which should always be true if a legit user registered
                if (($check = pwdcheck($password)) !== 'OK') {
                    $result['errorMessage'] = $check;
                    $session->errorMessage = $result['errorMessage'];
                    echo json_encode($result);
                } else {
                    $db = new UserHelper();
                    $user = $db->getUser($username);
                    if ($user) {
                        $passmatch = $hasher->CheckPassword($password, $user->getHash());
                        if ($passmatch) {
                            unset($hasher);
                            $session->loggedIn = true;
                            $session->id = $user->getId();
                            $session->username = $user->getUsername();
                            $session->email = $user->getEmail();
                            try {
                                $session->gravatar_url = getGravatar($user->getEmail());
                            } catch (Exception $e) {
                                $result['gravatar_error'] .= $e->getMessage();
                                $session->errorMessage = $result['gravatar_error'];
                            }
                            //add schedule object to user if already existing
                            if (isset($session->scheduleObj)) {
                                $result['foundObj'] = "Found user schedule object";
                                $userschedule = unserialize($session->scheduleObj);
                                if ($userschedule instanceof UserSchedule) {
                                    if ($userschedule->getUserId() == $user->getUserid()) {
                                        $result['mismatchedIds'] = "userschedule and user ids are same";
                                    } else {
                                        $result['mismatchedIds'] = "userschedule and user ids are NOT same";
                                        //update user id of the userschedule object
                                        $userschedule->setUserId($user->getUserid());
                                        //stash the updated userschedule object back to session
                                        $session->scheduleObj = serialize($userschedule);
                                        //set session userid
                                    }
                                    if ($user->addSchedule($userschedule)) {
                                        $result['addedSchedule'] = "Added found schedule to user object.";
                                        $result['errorMessage'] = "";
                                        $session->errorMessage = "";
                                    } else {
                                        $result['errorMessage'] = $user->getErrorMessage();
                                        $session->errorMessage = $result['errorMessage'];
                                    }
                                } else {
                                    $result['errorMessage'] = "Invalid user object found.";
                                    $session->errorMessage = $result['errorMessage'];
                                }
                            } else {
                                $result['errorMessage'] = "";
                                $session->errorMessage = $result['errorMessage'];
                            }
                            //set this last in case the user schedule object was found and added
                            $session->loggedInUser = serialize($user);
                            $session->userid = $user->getUserid();
                            $mp->track("user login", array("userid" => $user->getUsername()));
                            echo json_encode($result);
                        } else {
                            $result['errorMessage'] = "Invalid credentials.";
                            $session->errorMessage = $result['errorMessage'];
                            echo json_encode($result);
                        }
                    } else {
                        $result['errorMessage'] = $db->errorMessage;
                        $session->errorMessage = $result['errorMessage'];
                        echo json_encode($result);
                    }
                }
            } else {
                $result['errorMessage'] = "Username and password fields are mandatory.";
                $session->errorMessage = $result['errorMessage'];
                echo json_encode($result);
            }
        }
    } elseif (strcmp($action, "logout") == 0) {
        $session->unsetAll();
        $result['errorMessage'] = "";
        echo json_encode($result);
    } elseif (strcmp($action, "resetPassword") == 0){
        $pass1 = getPost("password1");
        $pass2 = getPost("password2");
        if ($pass1 === $pass2){
            //Check that user has passed the token validation and authentication step
            if (isset($session->resetRequestValidated) && $session->resetRequestValidated){
                $pwdCheck = pwdcheck($pass1);
                if ($pwdCheck === 'OK') {
                    $hash = $hasher->HashPassword($pass1);
                    if (strlen($hash) < 20) {
                        $result['errorMessage'] = fail('Failed to hash new password', $hash);
                    } else {
                        unset($hasher);
                        $db = new UserHelper();
                        $user = unserialize($session->requestingUser);
                        $res = $db->updatePassword($user,$hash);
                        if ($res){
                        	$date = new DateTime(null, new DateTimeZone('America/New_York'));
                            $expire = $db->expireResetToken($user,$session->validToken,$date->format('Y-m-d H:i:s'));
                            if ($expire){
                                $result['errorMessage'] = "";
                                $res = sendPasswordChangeEmail($user->getUsername(),$user->getEmail(),get_ip_address());
                                if ($res !== ''){
                                    $result['errorMessage'] = "Your password was changed but a notification email did not get sent out: " . $res;
                                }
                                //Invalidate set tokens
                                unset($session->resetRequestValidated);
                                unset($session->validToken);
                            }else{                                
                                $result['errorMessage'] = fail("Error expiring reset token.",$db->errorMessage);
                            }
                        }else{
                            $result['errorMessage'] = fail("Error updating the password. Please send an email to welcome@janeullah.com",$db->errorMessage);
                        }
                    }
                }else{
                    $result['errorMessage'] = $pwdCheck;
                }
            }else{
                $result['errorMessage'] = "Reset request not validated. Please click the link and begin the process again.";
            }
        }else{
            $result['errorMessage'] = "Both passwords must match."; 
        }  
        echo json_encode($result);          
    } else {
        $result['errorMessage'] = "Invalid action.";
        $session->errorMessage = $result['errorMessage'];
        echo json_encode($result);
    }
} else {
    $result['errorMessage'] = "Invalid request.";
    $session->errorMessage = $result['errorMessage'];
    echo json_encode($result);
}

