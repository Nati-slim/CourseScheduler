<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com",false,true);
session_name('CourseScheduler');
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
	$val = $_POST[$var];
	if (get_magic_quotes_gpc()){
		$val = stripslashes($val);
	}
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
 * Handle Requests
 */
$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
    $reqId = get_post_var('requirementId');
    $courseitem = get_post_var("courseitem");
	$addSection = get_post_var("add");
	$del = get_post_var("delete");
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
	}else if ($courseitem){
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
	}else if ($addSection){
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
				$status = $userschedule->addSection($section);
				if (!$status){
					$_SESSION['errorMessage'] = $userschedule->getErrorMessage();
				}else{
					$_SESSION['errorMessage'] = "Section " . $addSection. "(". $section->getCoursePrefix()."-".$section->getCourseNumber().") added!";
					//.gettype($userschedule) . " " . print_r($userschedule,true)." " . gettype($_SESSION['schedObj'][$userid]);
					$_SESSION['schedule'][$userschedule->getUserId()] = $userschedule->toJSON();
					$_SESSION['schedObj'][$userid] = serialize($userschedule);
				}
			}catch(Exception $e){
				$_SESSION['errorMessage'] = $e->getMessage();
			}
		}else{
			$_SESSION['errorMessage'] = "Please select a section to add first.";
		}
	}else if ($del){
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
			$userschedulejson = $_SESSION['schedule'][$userid];
			$userschedule = unserialize($_SESSION['schedObj'][$userid]);
			try{
				$status = $userschedule->deleteSection($callNum);
				if (!$status){
					$_SESSION['errorMessage'] = $userschedule->getErrorMessage();
				}else{
					$_SESSION['errorMessage'] = "Section " . $addSection. " deleted!";
					$_SESSION['schedule'][$userschedule->getUserId()] = $userschedule->toJSON();
					$_SESSION['schedObj'][$userid] = serialize($userschedule);
				}
			}catch(Exception $e){
				$_SESSION['errorMessage'] = $e->getMessage();
			}
		}else{
			$_SESSION['errorMessage'] = "Please select a section to delete first.";
		}
	}else{
		$_SESSION['errorMessage'] = "Unknown POST request";
		$_SESSION['courses'] = "[]";
		$_SESSION['sections'] = "[]";
	}
	header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
}else if ($requestType === 'GET'){
	if ($_GET['page'] === "schedule"){
		$init = $_SESSION['init'];
		if ($init !== "initialized"){
			$schedules = array();
			$schedule = new UserSchedule(generateToken());
			//Test schedule
			/*$mtg1 = new Meeting(12345, "M", "0230P", "0320P");
			$mtg2 = new Meeting(12345, "T", "0200P", "0315P");
			$mtg3 = new Meeting(12345, "R", "0200P", "0315P");
			$csci1302a = new Section("Web Programming", "CSCI","4300",12345,"Available",4.0,"EVERETT");
			$csci1302a->setBuildingNumber(1023);
			$csci1302a->setRoomNumber("206");
			$csci1302a->addMeeting($mtg1);
			$csci1302a->addMeeting($mtg2);
			$csci1302a->addMeeting($mtg3);
			$mtg4 = new Meeting(23456, "T", "0930A", "1045A");
			$mtg5 = new Meeting(23456, "W", "1010A", "1100A");
			$mtg6 = new Meeting(23456, "R", "0930A", "1045A");
			$csci1302b = new Section("Compilers", "CSCI","4570",23456,"Available",4.0,"KOCHUT");
			$csci1302b->setBuildingNumber(1023);
			$csci1302b->setRoomNumber("306");
			$csci1302b->addMeeting($mtg4);
			$csci1302b->addMeeting($mtg5);
			$csci1302b->addMeeting($mtg6);
			$mtg7 = new Meeting(34567, "M", "1100A", "1215P");
			$mtg8 = new Meeting(34567, "T", "1115A", "1205A");
			$mtg9 = new Meeting(34567, "R", "1100A", "1215P");
			$csci1302c = new Section("Networks", "CSCI","4760",34567,"Available",4.0,"PERDISCI");
			$csci1302c->setBuildingNumber(1023);
			$csci1302c->setRoomNumber("306");
			$csci1302c->addMeeting($mtg7);
			$csci1302c->addMeeting($mtg8);
			$csci1302c->addMeeting($mtg9);
			$schedule->addSection($csci1302a);
			$schedule->addSection($csci1302b);
			$schedule->addSection($csci1302c);
			if ($schedule->getErrorMessage()){
				echo "Error adding items to schedule: " . $schedule->getErrorMessage() . "\n";
			}*/
			$schedules[] = $schedule;
			$_SESSION['schedules'] = $schedules;
			initialize($schedule->getUserId(),$schedule);
		}
	}
	header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
}else{
	echo "Unknown request type.";
}
?>
