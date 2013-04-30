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
	$sections2150 = $db->getSections("CSCI","2150");
	if ($course){
		echo $course->getCoursePrefix() . "-" . $course->getCourseNumber() . "\n";
		//var_dump($course);
	}else{
		echo "CSCI 2150 not found.\n";
	}

	if ($sections){
		echo count($sections) . " found for CSCI.\n";
		print_r($sections);
	}else{
		echo "Sections for CSCI 1302 not found.\n";
	}

	if ($sections2150){
		echo count($sections2150) . " found for CSCI.\n";
		print_r($sections2150);
	}else{
		echo "Sections for CSCI 2150 not found.\n";
	}

?>
