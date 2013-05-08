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
		print_r($course);
	}else{
		echo "CSCI 2150 not found.\n";
	}

	$sections = $db->getSections("CSCI","1302");
	if ($sections){
		echo count($sections) . " found for CSCI 1302.\n";
		print_r($sections);
		//echo json_encode($sections);
		//echo "last error: " . json_last_error();
	}else{
		echo "Sections for CSCI 1302 not found.\n";
	}

	$sections2150 = $db->getSections("CSCI","2150");
	if ($sections2150){
		echo count($sections2150) . " found for CSCI 2150.\n";
		//print_r($sections2150);
	}else{
		echo "Sections for CSCI 2150 not found.\n";
	}

	$courses18 = $db->getCourses(18);
	if ($courses18){
		echo "Courses found for requirement 18: " . count($courses18) . "\n";
		//print_r($courses18);
	}else{
		echo "Courses not found for requirement 18.\n";
	}

	$courses19 = $db->getCourses(19);
	if ($courses19){
		echo "Courses found for requirement 19: " . count($courses19) . "\n";
		//print_r($courses19);
	}else{
		echo "Courses not found for requirement 19.\n";
	}

	$solosection = $db->getSingleSection(73760);
	if ($solosection){
		print_r($solosection);
	}else{
		echo "No section found.\n";
	}

	$shellcourse = $db->getShellCourses(19);
	if ($shellcourse){
		print_r($shellcourse);
	}else{
		echo "No section found.\n";
	}

	$csci1302 = $db->getSections("CSCI","1302");
	//echo json_encode($csci1302);

	$db->clearTable();
	echo $db->saveSchedule(1,2345,"jane");
	echo $db->saveSchedule(2,2345,"matt");
	echo $db->saveSchedule(1,1234,"ben");
	echo $db->saveSchedule(1,3456,"nana"). "\n";

	$res = $db->retrieveSchedule(1,2345);
	var_dump($res);
	$res = $db->getShortName(1,2345);
	var_dump($res);
	$res = $db->findLastSavedVersion(2345);
	echo "Last version: " . $res. "\n";
	$res = $db->findLastSavedVersion(4567);
	echo "Last version: " . $res. "\n";
	$res = $db->findLastSavedVersion(3456);
	echo "Last version: " . $res. "\n";
?>
