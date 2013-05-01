<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com/coursepicker",false,true);
session_name('CourseScheduler');
require_once("../helpers/Course.php");
require_once("../helpers/Section.php");
require_once("../helpers/Meeting.php");
session_start();
require_once("../helpers/DBHelper.php");
require_once("../helpers/UserSchedule.php");
function get_post_var($var){
	$val = $_POST[$var];
	if (get_magic_quotes_gpc()){
		$val = stripslashes($val);
	}
	return $val;
}

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
    // …
}else if ($requestType === 'GET'){
	$value = $_GET['page'];
	if ($value === "schedule"){
		$db = new DBHelper();
		$init = $_SESSION['init'];
		if ($init !== "initialized"){
			$schedules = array();
			$schedule = new UserSchedule(rand(1,PHP_INT_MAX));
			$mtg1 = new Meeting(12345, "M", "0215P", "0330P");
			$mtg2 = new Meeting(12345, "W", "0930A", "1045A");
			$mtg3 = new Meeting(23456, "F", "0900A", "1015A");
			$mtg4 = new Meeting(23456, "R", "1145A", "0100P");
			$csci1302a = new Section("Intro to Java", "CSCI","1302",12345,"Available",4.0,"Chris Plaue");
			$csci1302a->setBuildingNumber(1023);
			$csci1302a->setRoomNumber(306);
			$csci1302a->addMeeting($mtg1);
			$csci1302a->addMeeting($mtg2);
			$csci1302b = new Section("Systems Programming", "CSCI","1730",23456,"Available",4.0,"Eileen Kraemer");
			$csci1302a->setBuildingNumber(1023);
			$csci1302a->setRoomNumber("307A");
			$csci1302b->addMeeting($mtg3);
			$csci1302b->addMeeting($mtg4);
			$schedule->addSection($csci1302a);
			$schedule->addSection($csci1302b);
			if ($schedule->getErrorMessage()){
				echo "Error adding items to schedule.\n";
			}
			$schedules[] = $schedule;
			$_SESSION['init'] = "initialized";
			$_SESSION['schedule'][$schedule->getUserId()] = $schedule->toJSON();
			$_SESSION['userid'] = $schedule->getUserId();
			$_SESSION['schedules'] = $schedules;
		}
		header("Location: http://apps.janeullah.com/coursepicker/schedule.php");
	}else{
		header("Location: http://apps.janeullah.com/coursepicker/index.php");
	}
}else{
	echo "Unknown request type.";
}
?>
