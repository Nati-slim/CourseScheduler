<?php
	//Tester for Data Transfer Objects
	require_once("helpers/Course.php");
	require_once("helpers/Section.php");
	require_once("helpers/Meeting.php");
	$csci = new Course("CSCI","1302");
	$mtg1 = new Meeting(12345, 'M', "0930A", "1045A");
	$mtg2 = new Meeting(23456, 'T', "0930A", "1045A");
	$csci1302 = new Section("Intro to Java", "CSCI","1302",12345,"Available",4.0,"Chris Plaue");
	$csci1302->addMeeting($mtg1);
	$csci1302->addMeeting($mtg2);
	echo "Adding meeting to section: " . $mtg1->getStartTime() . "\n";
	$csci->addSection($csci1302);
	echo "Adding section to course: " . $csci1302->getCoursePrefix() . "\n";
	var_dump($csci);
	var_dump($mtg1);
	var_dump($csci1302);
?>
