<?php
$session = new Session();
require_once dirname(__FILE__) . '/../models/Course.php';
require_once dirname(__FILE__) . '/../models/Section.php';
require_once dirname(__FILE__) . '/../models/Meeting.php';
require_once dirname(__FILE__) . '/../model/UserSchedule.php';
require_once dirname(__FILE__) . '/../helpers/CourseHelper.php';
require_once dirname(__FILE__) . '/../helpers/session.php';
require_once dirname(__FILE__) . '/../../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../../../creds/dhpath.inc';
require_once dirname(__FILE__) . '/../../../../creds/mixpanel_coursepicker.inc';

$session = new Session();
$result = array();
$debug = DEBUGSTATUS;

//Set up debug stuff
//When  not debugging, log to a file!
if (!$debug) {
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
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

//error_reporting(0);
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
 * Returns the $_GET value matching the provided key
 * with the filter FILTER_SANITIZE_MAGIC_QUOTES
 * @param String $var key in $_POST variable
 * @return String $val value matching $_GET['key']
 */
function getGet($var){
	$val = filter_var($_GET[$var],FILTER_SANITIZE_MAGIC_QUOTES);
	return $val;
}

/**
 * Generate a cryptographically secure 256 bit string
 * and returns said string
 * @param integer $length default of 256 but you can change the length of the string generated
 * @return String $token generated string
 */
function generateToken($length = 256){
    if (function_exists('openssl_random_pseudo_bytes')){
        $token = base64_encode(openssl_random_pseudo_bytes($length,$strong));
        if ($strong){
            return $token;
        }
    }
    return sha1(uniqid(mt_rand(), true));
}

//ROUTE REQUEST
$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$result['errorMessage'] = "Not yet implemented";
    $session->errorMessage = $result['errorMessage'];
	echo $res;
}else if ($requestType === 'GET'){
	$request = getGet('id');
    if ($request){
        
    }else{
        $result['errorMessage'] = "Missing proper parameter";
        $session->errorMessage = $result['errorMessage'];
    }
	header("Location: http://apps.janeullah.com/coursepicker/share/");
}else{
	$result['errorMessage'] = "Invalid request type";
    $session->errorMessage = $result['errorMessage'];
}
?>
