<?php
require_once('../../../../auth/recaptcha/recaptchalib.php');
require_once('../../../../creds/captcha.inc');
session_start();
$privatekey = RECAPTCHA_JANEULLAH_PRIVATE;
$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);


/**
 * Returns the $_POST value matching the provided key
 * with the filter (FILTER_SANITIZE_MAGIC_QUOTES)
 * @param String $var key in $_POST variable
 * @return String $val value matching $_POST['key']
 */
function get_post_var($var){
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

/**
 * Function to send the message
 * @param String $name name
 * @param String $email email message
 * @param String $message
 * @param integer $ip
 *
 */
function sendMail($name, $email, $message, $ip){
    //Send Confirmation Email
    $to = "janeullah@gmail.com";
    $subject = 'Message from ';
    $subject .= $name;
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: '.$email.' via <no-reply@janeullah.com>' . "\r\n" .
                    'Reply-To: CoursePicker <no-reply@janeullah.com>' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
    $message = '<html><head><title>Message from CoursePicker</title></head><body><table cellpadding="10">';
    $message .= '<tr><td>Message from CoursePicker</td></tr><tr><td>';
    $message = 'You received a message via http://apps.janeullah.com/coursepicker on ' . date('l jS \of F Y h:i:s A');
    $message .= ' from the IP Address ('.$ip.').';
    $message .= '</td></tr><tr><td>';
    $message .= $message;
    $message .= '</td></tr>';
    mail($to, $subject, $message, $headers);
    return "Message sent.";
}

if (!$resp->is_valid) {
    // What happens when the CAPTCHA was entered incorrectly
    $errorMessage = "The reCAPTCHA wasn't entered correctly. Go back and try it again." .
         "(reCAPTCHA said: " . $resp->error . ")";
    //$_SESSION['errorMessage'] = ;
    echo $errorMessage;
}else{
    // Your code here to handle a successful verification
	$name = get_post_var("name");
	$email = get_post_var("email");
	$message = get_post_var("message");
	if (!$name){
		echo "Please fill out your name. You entered: " . $name;
	}else if (!$email){
		echo "Please fill out your email address. You entered: " . $message;
	}else if (!$message){
		echo "Please enter a message. You entered: " . $message;
	}else{
		$ip = get_ip_address();
		echo sendMail($name,$email,$message,$ip);
	}
}
?>
