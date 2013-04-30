<?php
	//Tester for DBHelper.php
	require_once("Course.php");
	require_once("Section.php");
	require_once("Meeting.php");
	require_once("DBHelper.php");
	//Testing dbhelper
	$db = new DBHelper();
	$course = $db->getCourses(19,"CSCI","2150");
	$sections = $db->getSections("CSCI","1302");
	if ($course){
		echo $course->getCoursePrefix() . "-" . $course->getCourseNumber() . "\n";
		var_dump($course);
	}else{
		echo "Course not found.";
	}

	if ($sections){
		echo count($sections) . " found for CSCI.\n";
		var_dump($sections);
	}else{
		echo "Sections not found.";
	}
?>
