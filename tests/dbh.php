<?php
	//Tester for DBHelper.php
	require_once("../helpers/Course.php");
	require_once("../helpers/Section.php");
	require_once("../helpers/Meeting.php");
	require_once("../helpers/DBHelper.php");
	//Testing dbhelper
	$db = new DBHelper();
	$course = $db->getCourses(19,"CSCI","2150");
	$sections = $db->getSections("CSCI","1302");
	if ($course){
		echo $course->getCoursePrefix() . "-" . $course->getCourseNumber() . "\n";
		//var_dump($course);
	}else{
		echo "Course not found.";
	}

	if ($sections){
		echo count($sections) . " found for CSCI.\n";
		//var_dump($sections);
	}else{
		echo "Sections not found.";
	}

	print_r($sections);
?>
