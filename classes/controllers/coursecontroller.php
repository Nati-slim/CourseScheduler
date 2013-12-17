<?php
require_once("../models/Course.php");
require_once("../models/Section.php");
require_once("../models/Meeting.php");
require_once("../helpers/CourseHelper.php");
require_once("../helpers/session.php");
require_once("../../../../creds/coursepicker_debug.inc");
require_once("../../../../creds/dhpath.inc");
$session = new Session();
$result = array();

//Needed for serialization/deserialization
function __autoload($class_name) {
    include "../models/". $class_name . '.php';
}

//Set up debug stuff
$debug = DEBUGSTATUS;
//When  not debugging, log to a file!
if (!$debug){
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", "syslog");
}

/* The $pvt debugging messages may contain characters that would need to be
 * quoted if we were producing HTML output, like we would be in a real app,
 * but we're using text/plain here.  Also, $debug is meant to be disabled on
 * a "production install" to avoid leaking server setup details. */
function fail($pub, $pvt = ''){
	global $debug;
	$msg = $pub;
	if ($debug && $pvt !== '')
		$msg .= ": $pvt";
	exit("An error occurred ($msg).\n");
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

function getSectionJSON($sections){
	$result = array();	
	foreach($sections as $section){
		$result[$section->getCallNumber()] = $section->to_array();
	}
	return json_encode($result);
}


function initialize(){
	//Initialize user schedule object & set relevant $_SESSION variables
	$session->init = "initialized";	
	$userschedule = new UserSchedule(generateToken());
	$session->schedule = $userschedule->to_json();
	$session->schedObj = serialize($userschedule);
	$session->userid = $userschedule->getUserId();
	$session->errorMessage = "";
}

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$action = get_post_var('action');
	if (strcmp($action,"getSections") == 0){
		$semester = get_post_var('semesterSelected');
		$course = get_post_var('courseEntry');
		if (strlen(trim($semester)) > 0 && strlen(trim($course)) > 0){
			$courseArray = explode("-",$course);
			$semesterArray = explode("-",$semester);
			if (count($courseArray) == 2 && count($semesterArray) == 2){
				$session->semesterSelected = $semesterArray[0] . "-" . $semesterArray[1];
				$session->jsonURL = "assets/json/tp/tp-" . $semesterArray[0] . "-" . $semesterArray[1] . ".json";
				$db = new CourseHelper();
				//$term,$coursePrefix,$courseNumber,$campus
				try{
					$courseSections = $db->getSections($semesterArray[0],$courseArray[0],$courseArray[1],$semesterArray[1]);
					$session->courseSections = $courseSections;
					$session->courseSectionsJSON = getSectionJSON($courseSections);
					$session->errorMessage = "";
					echo $session->courseSectionsJSON;
				}catch(Exception $e){
					$result['errorMessage'] = $e->getMessage();
					$session->errorMessage = $e->getMessage();
					echo json_encode($result);
				}
			}else{
				$result['errorMessage'] = "Invalid parameters found.";	
				$session->errorMessage = "Invalid parameters found.";	
				echo json_encode($result);
			}
		}else{
			$result['errorMessage'] = "Invalid parameters found.";
			$session->errorMessage = "Invalid parameters found.";
			echo json_encode($result);
		}
	}else if (strcmp($action,"filterSections") == 0){
		$available = get_post_var('available');
		$full = get_post_var('full');
		$cancelled = get_post_var('Cancelled');
		if (isset($session->courseSections)){
			//Course sections will contain ALL the filters don't modify
			//use the JSONified version for results to the page
			$courseSections = $session->courseSections;
			$filteredSections = array();
			foreach($courseSections as $section){
				$status = $section->getStatus();
				if (($status == "Available" && $available == "true")
					|| ($status == "Full" && $full == "true") 
					|| ($status == "Cancelled" && $cancelled == "true")){
					$filteredSections[$section->getCallNumber()] = $section;
				}
			}
			$session->courseSectionJSON = getSectionJSON($filteredSections);
			$result['errorMessage'] = "";
			$session->errorMessage = "";
			echo $session->courseSectionJSON;
		}else{
			$result['errorMessage'] = "Please select a course first.";
			$session->errorMessage = "Please select a course first.";
			echo json_encode($result);
		}
	}else{
		$result['errorMessage'] = "No action found.";
		$session->errorMessage = "No action found.";
		echo json_encode($result);
	}
}else{
	$result['errorMessage'] = "Invalid request.";
	$session->errorMessage = "Invalid request.";
	echo json_encode($result);
}
?>
