<?php
	//Tester for DBHelper.php
	require_once("../helpers/Course.php");
	require_once("../helpers/Section.php");
	require_once("../helpers/Meeting.php");
	require_once("../helpers/DBHelper.php");
	//Testing dbhelper
	$db = new DBHelper();
	$course = $db->getCourse(19,"CSCI","2150");
	if ($course){
		echo $course->getCoursePrefix() . "-" . $course->getCourseNumber() . "\n";
		//var_dump($course);
	}else{
		echo "CSCI 2150 not found.\n";
	}

	$sections2150 = $db->getSections("CSCI","2150");
	if ($sections2150){
		echo count($sections2150) . " found for CSCI.\n";
		print_r($sections2150);
	}else{
		echo "Sections for CSCI 2150 not found.\n";
	}

	$courses18 = $db->getCourses(18);
	if ($courses18){
		echo "Courses found: " . count($courses18) . "\n";
		print_r($courses18);
	}else{
		echo "Courses not found for requirement 18.\n";
	}

	$courses19 = $db->getCourses(19);
	if ($courses19){
		echo "Courses found: " . count($courses19) . "\n";
	}else{
		echo "Courses not found for requirement 19.\n";
	}

	$sections = $db->getSections("CSCI","1302");
	if ($sections){
		echo count($sections) . " found for CSCI.\n";
	}else{
		echo "Sections for CSCI 1302 not found.\n";
	}







?>
