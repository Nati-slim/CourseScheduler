<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com",false,true);
session_name('CoursePicker');
require_once("../helpers/Course.php");
require_once("../helpers/Section.php");
require_once("../helpers/Meeting.php");
require_once("../helpers/DBHelper.php");
require_once("../helpers/UserSchedule.php");
session_start();
//error_reporting(0);
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
 * Returns the $_GET value matching the provided key
 * with the filter FILTER_SANITIZE_MAGIC_QUOTES
 * @param String $var key in $_POST variable
 * @return String $val value matching $_GET['key']
 */
function get_get_var($var){
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
    } else{
        return sha1(uniqid(mt_rand(), true));
    }
}


/**
 * Set the required session variables
 * @param integer @userid user generated id string
 * @param UserSchedule object $schedule
 */
function initialize($userid,$schedule){
	$_SESSION['init'] = "initialized";
	$_SESSION['schedule'][$userid] = $schedule->toJSON();
	$_SESSION['schedObj'][$userid] = serialize($schedule);
	$_SESSION['userid'] = $userid;
	$_SESSION['errorMessage'] = "";
}

/**
 * Function to save the user's schedule to the database
 * @param integer $version Last saved version of user's schedule in database
 * @param DBHelper $db
 * @return boolean $status of the save
 */
function saveSchedule($version,$db){
	//SAVE SCHEDULE TO DATABASE
	$userid = $_SESSION['userid'];
	$serializedschedule = $_SESSION['schedObj'][$userid];
	$status = $db->saveSchedule($version+1,$userid,$serializedschedule);
	return $status;
}

/**
 * Processing POST requests
 */
function doPost(){
	//INITIALIZE SCHEDULE
	$init = $_SESSION['init'];
	if (strcmp($init,"initialized") != 0){
		//Initialize user schedule object & set relevant $_SESSION variables
		$userschedule = new UserSchedule(generateToken());
		initialize($userschedule->getUserId(),$userschedule);
	}
	$action = get_post_var("action");
	//SHARE LAST SAVED VERSION IN DATABASE
	if ($action && strcasecmp($action,"Share") == 0){
		//check if schedule exists
		$db = new DBHelper();
		$userid = $_SESSION['userid'];
		$lastversion = $db->findLastSavedVersion($userid);
		//return $lastversion;
		if ($lastversion == 0){
			$status = saveSchedule($lastversion,$db);
			if (!$status){
				$_SESSION['errorMessage'] = "Problem saving schedule.";
				return "Problem saving schedule.";
			}
		}
		//$resultingversion = $db->retrieveSchedule($lastversion+1,$_SESSION['userid']);
		$shortName = $db->getShortName($lastversion,$_SESSION['userid']);
		return $shortName;
	}else{
		return "-1";
	}
}

/**
 * processing GET requests
 */
function doGet(){
	$schedDigest = get_get_var("schedule");
	if ($schedDigest){
		$db = new DBHelper();
		//Get the user schedule object and return an unserialized version
		$schedule = $db->getSingleSchedule($schedDigest);
		return unserialize($schedule);
	}
}

//ROUTE REQUEST
$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$res = doPost();
	echo $res;
}else if ($requestType === 'GET'){
	$res = doGet();
	header("Location: http://apps.janeullah.com/coursepicker/share/");
	//echo $res;
}else{
	echo "Unknown request type.";
}
?>