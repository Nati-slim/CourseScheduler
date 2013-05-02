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

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
    $reqId = get_post_var('requirementId');
    if ($reqId){
		$db = new DBHelper();
		try{
			$reqId = (int)$reqId;
		}catch(Exception $e){
			$_SESSION['errorMessage'] = "Requirement ID must be an integer.";
			return "{}";
		}
		$courses = $db->getShellCourses($reqId);
		if ($courses){
			$_SESSION['errorMessage'] = "";
			$_SESSION['courses'] = $courses;
			//$_SESSION['courses'] = json_encode($courses);
			echo 1;
		}else{
			$_SESSION['errorMessage'] = "No courses found for requirement Id " . $reqId;
			$_SESSION['courses'] = "[]";
			echo 0;
		}
	}else{
		$_SESSION['errorMessage'] = "Unknown POST request.";
		$_SESSION['courses'] = "[]";
		echo 0;
	}
}else if ($requestType === 'GET'){
	if ($_GET['page'] === "schedule"){
		$init = $_SESSION['init'];
		if ($init !== "initialized"){
			$schedules = array();
			$schedule = new UserSchedule(generateToken());
			$mtg1 = new Meeting(12345, "M", "0230P", "0320P");
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
			}
			$schedules[] = $schedule;
			$_SESSION['init'] = "initialized";
			$_SESSION['schedule'][$schedule->getUserId()] = $schedule->toJSON();
			$_SESSION['userid'] = $schedule->getUserId();
			$_SESSION['schedules'] = $schedules;
			$_SESSION['errorMessage'] = "";
		}
		header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
	}else{
		header("Location: http://apps.janeullah.com/coursepicker/index.php");
	}
}else{
	echo "Unknown request type.";
}
?>
