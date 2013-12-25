<?php
require_once dirname(__FILE__) . '/../models/Course.php';
require_once dirname(__FILE__) . '/../models/Section.php';
require_once dirname(__FILE__) . '/../models/Meeting.php';
require_once dirname(__FILE__) . '/../models/UserSchedule.php';
require_once dirname(__FILE__) . '/../helpers/ScheduleHelper.php';
require_once dirname(__FILE__) . '/../helpers/session.php';
require_once dirname(__FILE__) . '/../../includes/mixpanel/lib/Mixpanel.php';
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

/**
 * Function to check if the schedule ID is an alphanumeric string
 * 
 * @param string $id the id
 * 
 * @return string 'OK' if alphanumeric and not 'OK' otherwise
 * 
 */ 
function checkToken($id){
   if (ctype_alnum($id)) {
        return 'OK';
    }else{
        return "Invalid token type";
    }    
}

//ROUTE REQUEST
$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$result['errorMessage'] = "POST requests not honored";
    $session->errorMessage = $result['errorMessage'];
	echo $res;
}else if ($requestType === 'GET'){
	$name = getGet('id');
    if ($name){
        $test = checkToken($name);
        if (strcmp($test, 'OK') == 0){
            $db = new ScheduleHelper();
            $res = $db->getScheduleByShortname($name);
            if (count($res) == 3){
                $shortName = $res['shortname'];
                $date = $res['dateAdded'];
                $scheduleObj = unserialize($res['scheduleObj']);
                if ($scheduleObj and $scheduleObj instanceOf UserSchedule){
                    $dateAdded = DateTime::createFromFormat('Y-m-d H:i:s',$date);
                    $scheduleObj->setDateAdded($dateAdded);
                    $result['errorMessage'] = "";
                    $session->errorMessage = $result['errorMessage'];
                    $session->scheduleObject = serialize($scheduleObj);
                    $session->scheduleJSON = $scheduleObj->to_json();
                    $mp->track("user viewing schedule", array("shortname" => $shortName));
                }else{
                    $result['errorMessage'] = "Failed to properly deserialize schedule object.";
                    $session->errorMessage = $result['errorMessage'];
                }
            }else{
                $result['errorMessage'] = fail("Failed to retrieve the object from the database",$db->errorMessage);
                $session->errorMessage = $result['errorMessage'];
            }
        }else{
            $result['errorMessage'] = $test;
            $session->errorMessage = $result['errorMessage'];
        }
    }else{
        $result['errorMessage'] = "Improper parameter supplied";
        $session->errorMessage = $result['errorMessage'];
    }
    //print_r($session->scheduleJSON);
    //print_r($res);
	header("Location: http://apps.janeullah.com/coursepicker/share/");
}else{
	$result['errorMessage'] = "Invalid request type";
    $session->errorMessage = $result['errorMessage'];
}
?>
