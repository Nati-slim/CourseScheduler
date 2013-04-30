<?php
	require_once("Course.php");
	require_once("Section.php");
	require_once("Meeting.php");
	require_once("DBHelper.php");
	//Testing dbhelper
	$db = new DBHelper();
	$courses = $db->getCourses(19,"CSCI","2150");
	var_dump($courses);
?>
