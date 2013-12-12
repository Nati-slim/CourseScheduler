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


/**
 * Generate a cryptographically secure 256 bit string
 * and returns said string
 * @param integer $length default of 256 but you can change the length of the string generated
 * @return String $token generated string
 */
function generateToken($length = 40){
    if (function_exists('openssl_random_pseudo_bytes')){
        $token = base64_encode(openssl_random_pseudo_bytes($length,$strong));
        if ($strong){
            return $token;
        }
    }
	return sha1(uniqid(mt_rand(), true));
}

/**
 * Returns the human version of the requirement selected.
 * @param integer $id i.e. the requirement ID
 */
function getRequirementName($id){
	try{
		$id = (int)$id;
	}catch(Exception $e){
		return "Invalid Requirement ID.";
	}
	switch($id){
	case 1:
		return "Cultural Diversity Requirement";
	case 2:
		return "Environmental Literacy Requirement";
	case 3:
		return "Core Curriculum I: Foundation Courses";
	case 4:
		return "Core Curriculum II: Physical Sciences";
	case 5:
		return "Core Curriculum II: Life Sciences";
	case 6:
		return "Core Curriculum III: Quantitative Reasoning";
	case 7:
		return "Core Curriculum IV: World Languages and Culture";
	case 8:
		return "Core Curriculum IV: Humanities and Arts";
	case 9:
		return "Core Curriculum V: Social Sciences";
	case 10:
		return "Franklin College: Foreign Language";
	case 11:
		return "Franklin College: Literature";
	case 12:
		return "Franklin College: Fine Arts/Philosophy/Religion";
	case 13:
		return "Franklin College: History";
	case 14:
		return "Franklin College: Social Sciences other than History";
	case 15:
		return "Franklin College: Biological Sciences";
	case 16:
		return "Franklin College: Physical Sciences";
	case 17:
		return "Franklin College: Multicultural Requirement";
	case 18:
		return "Core Curriculum VI: Major related courses";
	case 19:
		return "Computer Science Major Courses";
	default:
		return "Error obtaining requirement's name;";
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
 *
 * Processing POST requests
 *
 */
function doPost(){

}

/**
 * processing GET requests
 *
 */
function doGet(){

}
/**
 *
 * Handle Requests
 */
$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
    $reqId = get_post_var('requirementId');
    $courseitem = get_post_var("courseitem");
    $typeaheadcourseitem = get_post_var("courses");
	$addSection = get_post_var("add");
	$del = get_post_var("delete");
	//REQUIREMENT DROPDOWN
    if ($reqId){
		$db = new DBHelper();
		try{
			$reqId = (int)$reqId;
		}catch(Exception $e){
			$_SESSION['errorMessage'] = "Requirement ID must be an integer.";
			echo "{}";
		}
		$courses = $db->getShellCourses($reqId);
		if ($courses){
			$_SESSION['errorMessage'] = "";
			$_SESSION['courses'] = $courses;
			$_SESSION['requirementName'] = getRequirementName($reqId);
		}else{
			$_SESSION['errorMessage'] = "No courses found for requirement Id " . $reqId;
			$_SESSION['courses'] = "[]";
			$_SESSION['requirementName'] = "";
		}
		header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
	}//COURSE DROPDOWN
	else if ($courseitem){
		$db = new DBHelper();
		$pos = strpos($courseitem,"-");

		if ($pos === false) {
			$_SESSION['errorMessage'] = "No sections found for " . $courseitem;
			$_SESSION['sections'] = "[]";
		}else{
			$sections = $db->getSections(substr($courseitem,0,$pos),substr($courseitem,$pos+1,strlen($courseitem)-1));
			$_SESSION['errorMessage'] = "";
			$_SESSION['sections'] = $sections;
		}
		header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
	}
	//TYPEAHEAD
	else if ($typeaheadcourseitem){
		$db = new DBHelper();
		$pos = strpos($typeaheadcourseitem,"-");
		$results = "[";
		if ($pos === false) {
			$_SESSION['errorMessage'] = "No sections found for " . $typeaheadcourseitem;
			$_SESSION['sections'] = "[]";
		}else{
			$sections = $db->getSections(substr($typeaheadcourseitem,0,$pos),substr($typeaheadcourseitem,$pos+1,strlen($typeaheadcourseitem)-1));
			//echo substr($typeaheadcourseitem,0,$pos) . " " . substr($typeaheadcourseitem,$pos+1,strlen($typeaheadcourseitem)-1) ;
			$_SESSION['errorMessage'] = "";
			$_SESSION['sections'] = $sections;
		}
		//json_encode returns a garbage result. need to troubleshoot why so need manually creation of a json string.
		foreach($sections as $section){
			$results .= $section->toJSON() . ",";
		}
		if (strlen($results) > 1){
			$results = substr($results,0,strlen($results)-1);
		}
		$results .= "]";
		echo $results;
	}//ADD SECTION
	else if ($addSection){
		if ($addSection != 0){
			$db = new DBHelper();
			$init = $_SESSION['init'];
			if (strcmp($init,"initialized") != 0){
				//Initialize user schedule object & set relevant $_SESSION variables
				$userschedule = new UserSchedule(generateToken());
				initialize($userschedule->getUserId(),$userschedule);
			}

			$userid = $_SESSION['userid'];
			$userschedulejson = $_SESSION['schedule'][$userid];
			$userschedule = unserialize($_SESSION['schedObj'][$userid]);
			$section = $db->getSingleSection($addSection);
			try{
				if (strcasecmp($section->getStatus(),"Available") == 0){
					$status = $userschedule->addSection($section);
					if (!$status){
						$_SESSION['errorMessage'] = $userschedule->getErrorMessage();
					}else{
						$_SESSION['errorMessage'] = "Section " . $addSection. " (". $section->getCoursePrefix()."-".$section->getCourseNumber().") added!";
						//.gettype($userschedule) . " " . print_r($userschedule,true)." " . gettype($_SESSION['schedObj'][$userid]);
						$_SESSION['schedule'][$userschedule->getUserId()] = $userschedule->toJSON();
						$_SESSION['schedObj'][$userid] = serialize($userschedule);
					}
				}else{
					$_SESSION['errorMessage'] = "Section is not Available";
				}
			}catch(Exception $e){
				$_SESSION['errorMessage'] = $e->getMessage();
			}
		}else{
			$_SESSION['errorMessage'] = "Please select a section to add first.";
		}
		header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
	}//DELETE SECTION
	else if ($del){
		$callNum = get_post_var("deleteSectionItem");
		if ($callNum){
			$db = new DBHelper();
			$init = $_SESSION['init'];
			if (strcmp($init,"initialized") != 0){
				//Initialize user schedule object & set relevant $_SESSION variables
				$userschedule = new UserSchedule(generateToken());
				initialize($userschedule->getUserId(),$userschedule);
			}

			$userid = $_SESSION['userid'];
			$userschedule = unserialize($_SESSION['schedObj'][$userid]);
			try{
				$status = $userschedule->deleteSection($callNum);
				if (!$status){
					$_SESSION['errorMessage'] = $userschedule->getErrorMessage() . " " . $userschedule->toJSON();
				}else{
					$_SESSION['errorMessage'] = "Section " . $callNum. " deleted!";
					$_SESSION['schedule'][$userschedule->getUserId()] = $userschedule->toJSON();
					$_SESSION['schedObj'][$userid] = serialize($userschedule);
				}
			}catch(Exception $e){
				$_SESSION['errorMessage'] = $e->getMessage();
			}
		}else{
			$_SESSION['errorMessage'] = "Please select a section to delete first.";
		}
		header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
	}//ELSE
	else{
		$_SESSION['errorMessage'] = "Unknown POST request";
		$_SESSION['courses'] = "[]";
		$_SESSION['sections'] = "[]";
		header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
	}
}else if ($requestType === 'GET'){
	if ($_GET['page'] === "schedule"){
		$init = $_SESSION['init'];
		if ($init !== "initialized"){
			$schedules = array();
			$schedule = new UserSchedule(generateToken());
			$schedules[] = $schedule;
			$_SESSION['schedules'] = $schedules;
			initialize($schedule->getUserId(),$schedule);
		}
	}
	$_SESSION['errorMessage'] = "";
	header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
}else{
	echo "Unknown request type.";
}
?>
