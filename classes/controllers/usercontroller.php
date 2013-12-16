<?php
require_once("../../../../creds/dhpath.inc");
require_once("../../../../creds/captcha.inc");
require_once("../../../../creds/coursepicker_debug.inc");
require_once("../helpers/session.php");
require_once("../helpers/UserHelper.php");
require_once('../../includes/phppass/PasswordHash.php');
require_once("../../includes/recaptcha/recaptchalib.php");
$session = new Session();
$result = array();

//Set up debug stuff
$valid = true;
$what = NULL;
$debug = DEBUGSTATUS;
if ($debug){
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", "syslog");
}

//http://www.openwall.com/articles/PHP-Users-Passwords
// Base-2 logarithm of the iteration count used for password stretching
$hash_cost_log2 = 8;
// Do we require the hashes to be portable to older systems (less secure)?
$hash_portable = FALSE;
//Hasher information
$hasher = new PasswordHash($hash_cost_log2, $hash_portable);
               
                                
/* The $pvt debugging messages may contain characters that would need to be
 * quoted if we were producing HTML output, like we would be in a real app,
 * but we're using text/plain here.  Also, $debug is meant to be disabled on
 * a "production install" to avoid leaking server setup details. */
function fail($pub, $pvt = ''){
	global $debug;
	$msg = $pub;
	if ($debug && $pvt !== ''){
		$msg .= ": " . $pvt;
    }
    /* The $pvt debugging messages may contain characters that would need to be
     * quoted if we were producing HTML output, like we would be in a real app,
     * but we're using text/plain here.  Also, $debug is meant to be disabled on
     * a "production install" to avoid leaking server setup details. */
    return $msg;
}


/**
 * Function to undo effects of magic quotes
 * Returns the $_POST value matching the provided key
 * @param String $var key in $_POST variable
 * @return String $val value matching $_POST['key']
 */
function get_post_var($var){
	$val = filter_var($_POST[$var],FILTER_SANITIZE_MAGIC_QUOTES);
	return $val;
}

//Simple regex to check password validity
function pwdcheck($pass){
    if (preg_match("#.*^(?=.{8,50})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $pass)){
        return 'OK';
    }
    return "Invalid password. Please enter a password or phrase with the following characteristics: 8 - 50 characters and at least 1 uppercase, 1 number and 1 symbol.";
}

function usernameCheck($username){
	if (!preg_match('/^[a-zA-Z0-9_]{1,60}$/', $username)){
		return "Please use only alphanumeric characters.";
	}
	return 'OK';
}

//http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string
function generateUserid($length = 40){
    if (function_exists('openssl_random_pseudo_bytes')){
        $token = base64_encode(openssl_random_pseudo_bytes($length,$strong));
        if ($strong){
            return $token;
        }
    }
	return sha1(uniqid(mt_rand(), true));
}

//Generate a cryptographically secure 256 bit string
function generateToken($length = 256){
    if (function_exists('openssl_random_pseudo_bytes')){
        $token = base64_encode(openssl_random_pseudo_bytes($length,$strong));
        if ($strong){
            return $token;
        }        
    } 
    return sha1(uniqid(mt_rand(), true));
}



$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$action = get_post_var('action');
	if (strcmp($action,"newuser") == 0){
		//check captcha
		$privatekey = CP_PRIVATE_KEY;
		$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);
		if (!$resp->is_valid){
			$result['errorMessage'] = fail('Nice try, ROBOT! Check the Recaptcha response and try again. Sorry if you\'re really human.',$resp->error);
			$session->errorMessage = $result['errorMessage'];
			echo json_encode($result);
		}else{
			$email = get_post_var('email');
			$username = get_post_var('username');
			$password1 = get_post_var('password1');
			$password2 = get_post_var('password2');
			if ($password1 === $password2){
				//Validate password
				//$check = pwdcheck($pass)) !== 'OK
				$pwdCheck = pwdcheck($password1);
				if ($pwdCheck === 'OK'){
					//Validate email address
					$email = filter_var($email, FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)){
						//Validate username
						$usernameCheck = usernameCheck($username);
						if (strcmp($usernameCheck,'OK') == 0){
							//Get user id from session or generate one.
							$userid = $session->userid;
							if (!$userid){
								$userid = generateUserID();
							}
							//Hash password
							$hash = $hasher->HashPassword($password1);
                            if (strlen($hash) < 20){	                            
								$result['errorMessage'] = fail('Failed to hash new password',$hash);
								$session->errorMessage = $result['errorMessage'];
								echo json_encode($result);
                            }
                            else{
                                unset($hasher);
								$db = new UserHelper();
								$res = $db->addUser($userid,$username,$email,$hash);
								if ($res === false){								
									$result['errorMessage'] = fail('Problem creating this user account.',$db->errorMessage);
									$session->errorMessage = $result['errorMessage'];
									echo json_encode($result);
								}else{
									$result['errorMessage'] = "";
									//$result['id'] = $res;
									$session->id = $res;
									$session->userid = $userid;
									$session->errorMessage = $result['errorMessage'];
									echo json_encode($result);
								}
							}
						}else{
							$result['errorMessage'] = $usernameCheck;
							$session->errorMessage = $result['errorMessage'];
							echo json_encode($result);
						}
					}else{
						$result['errorMessage'] = "Invalid email address found.";
						$session->errorMessage = $result['errorMessage'];
						echo json_encode($result);
					}
				}else{
					$result['errorMessage'] = $pwdCheck;
					$session->errorMessage = $result['errorMessage'];
					echo json_encode($result);
				}
			}else{
				$result['errorMessage'] = "Passwords don't match. Please make sure both passwords are the same.";
				$session->errorMessage = $result['errorMessage'];
				echo json_encode($result);
			}
		}
	}else if (strcmp($action,"login") == 0){
		$username = get_post_var('loginUsername');
		$password = get_post_var('loginPassword');
		if ($password && $username){
			if (($check = pwdcheck($password)) !== 'OK'){
				$result['errorMessage'] = $check;
				$session->errorMessage = $result['errorMessage'];
				echo json_encode($result);
            }else{
				$db = new UserHelper();
				$user = $db->getUser($username);
				if ($user){					
					$passmatch = $hasher->CheckPassword($password, $user->getHash());
					if ($passmatch) {
						$result['errorMessage'] = "";
						$session->errorMessage = $result['errorMessage'];
						$session->loggedIn = true;
						$session->id = $user->getId();
						$session->userid = $user->getUserid();
						$session->loggedInUser = serialize($user);						
						echo json_encode($result);
					}else{
						$result['errorMessage'] = "Invalid credentials.";
						$session->errorMessage = $result['errorMessage'];
						echo json_encode($result);
					}
					unset($hasher);
				}else{
					$result['errorMessage'] = $db->errorMessage;
					$session->errorMessage = $result['errorMessage'];
					echo json_encode($result);
				}
			} 
		}else{
			
		}
	}else if (strcmp($action,"logout") == 0){
		$session->loggedIn = false;
		unset($session->id);
		unset($session->loggedInUser);	
		unset($session->userid);
		$result['errorMessage'] = "";
		$session->errorMessage = $result['errorMessage'];
		echo json_encode($result);
	}else{
		$result['errorMessage'] = "Invalid action.";
		$session->errorMessage = $result['errorMessage'];
		echo json_encode($result);
	}
}else{
	$result['errorMessage'] = "Invalid request.";
	$session->errorMessage = $result['errorMessage'];
	echo json_encode($result);
}

function clearVariables(){
	unset($session->id);
	unset($session->loggedInUser);	
	unset($session->userid);
}

?>
