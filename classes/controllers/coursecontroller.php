<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com",false,true);
session_name('CoursePicker');
require_once("../helpers/Course.php");
require_once("../helpers/Section.php");
require_once("../helpers/Meeting.php");
require_once("../helpers/CourseHelper.php");
session_start();

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

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$action = get_post_var('action');
	$result = array();
	if (strcmp($action,"getSections") == 0){
		$semester = get_post_var('selectedSemester');
		$course = get_post_var('courseEntry');
		if (strlen(trim($semester)) > 0 && strlen(trim($course)) > 0){
			$courseArray = explode("-",$course);
			$semesterArray = explode("-",$semester);
			if (count($courseArray) == 2 && count($semesterArray) == 2){
				$db = new CourseHelper();
				//$term,$coursePrefix,$courseNumber,$campus
				$courseSections = $db->getSections($semesterArray[0],$courseArray[0],$courseArray[1],$semesterArray[1]);
				echo json_encode(getSectionJSON($courseSections));
			}else{
				echo '"{ \"errorMessage\" : \"Invalid parameters found.\" }"';
			}
		}else{
			echo '"{ \"errorMessage\" : \"Invalid parameters found.\" }"';
		}
	}else{
		echo '"{ \"errorMessage\" : \"No action found.\" }"';
	}
}else{
	echo '"{ \"errorMessage\" : \"Invalid request.\" }"';
}
?>
