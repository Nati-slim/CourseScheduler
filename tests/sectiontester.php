<?php
	require_once("../helpers/Course.php");
	require_once("../helpers/Section.php");
	require_once("../helpers/Meeting.php");
	//Testing invalid times
	$mtg1 = new Meeting(12345, "M", "0215P", "0330P");
	$mtg2 = new Meeting(23456, "W", "0930A", "1045A");
	$mtg3 = new Meeting(23456, "F", "0900A", "1015A");
	$mtg4 = new Meeting(22123, "R", "1145A", "0100P");
	$mtg5 = new Meeting(12345, "T", "0230P", "0320P");
	$csci1302 = new Section("Intro to Java", "CSCI","1302",12345,"Available",4.0,"Chris Plaue");
	$csci1302->addMeeting($mtg1);
	$csci1302->addMeeting($mtg2);
	$csci1302->addMeeting($mtg3);
	$csci1302->addMeeting($mtg4);
	$csci1302->addMeeting($mtg5);
	$csci1302->setBuildingNumber(1023);
	$csci1302->setRoomNumber("307A");
	//var_dump($csci1302);
	echo "csci has 4 meetings: " . count($csci1302->getMeetings()) . "\n";
	foreach ($csci1302->getMeetings() as $meeting){
		echo "meeting time: " . $meeting->getMeetingTime() . "\n";
	}

	echo "section name: " . $csci1302->getCourseName() . "\n coursePrefix: " . $csci1302->getCoursePrefix() .
	"\n courseNumber: " . $csci1302->getCourseNumber() . "\n courseCredit: " . $csci1302->getCourseCredit() .
	"\n courseStatus: " . $csci1302->getStatus() . "\n courseLecturer: " . $csci1302->getLecturer() .
	"\n courseBuilding: " . $csci1302->getBuildingNumber() . "\n courseRoomNumber: " . $csci1302->getRoomNumber()
	."\n";

	print_r($csci1302);
	echo $csci1302->toJSON();
?>
