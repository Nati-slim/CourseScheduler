<?php
require_once("../helpers/Course.php");
require_once("../helpers/Section.php");
require_once("../helpers/Meeting.php");
require_once("../helpers/CourseHelper.php");
require_once("../helpers/session.php");
$session = new Session();
$result = array();



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
		$options = array();
		$options['Available'] = get_post_var('Available');
		$options['Full']= get_post_var('Full');
		$options['Cancelled'] = get_post_var('Cancelled');
		$options = array_filter($options,'strlen');
		if (!isset($session->courseSections)){
			$result['errorMessage'] = "Please select a course first.";
			$session->errorMessage = "Please select a course first.";
			echo json_encode($result);
		}else if (count($options) == 3){
			echo $session->courseSectionsJSON;
		}else{			
			$courseSections = $session->courseSections;
			$result = array();
			foreach($courseSections as $section){
				if ($section->getStatus() == $options[$section->getStatus()]){
					$result[$section->getCallNumber()] = $section;
				}
			}
			$result['errorMessage'] = "";
			$session->errorMessage = "";
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
