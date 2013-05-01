<?php
	//Tester for Data Transfer Objects
	require_once("../helpers/Course.php");
	require_once("../helpers/Section.php");
	require_once("../helpers/Meeting.php");
	$csci = new Course("CSCI","1302");
	$mtg1 = new Meeting(12345, "M", "0215P", "0330P");
	$mtg2 = new Meeting(12345, "W", "0930A", "1045A");
	$mtg3 = new Meeting(12345, "F", "0900A", "1015A");
	$mtg4 = new Meeting(12345, "R", "1145A", "0100P");
	$csci1302a = new Section("Intro to Java", "CSCI","1302",12345,"Available",4.0,"Chris Plaue");
	$csci1302a->addMeeting($mtg1);
	$csci1302a->addMeeting($mtg2);
	$csci1302a->addMeeting($mtg3);
	$csci1302a->addMeeting($mtg4);
	$csci1302a->setBuildingNumber(1023);
	$csci1302a->setRoomNumber("307A");
	$csci->addSection($csci1302a);

	$mtg5 = new Meeting(23456, "M", "0215P", "0330P");
	$mtg6 = new Meeting(23456, "W", "0930A", "1045A");
	$mtg7 = new Meeting(23456, "F", "0900A", "1015A");
	$mtg8 = new Meeting(23456, "R", "1145A", "0100P");
	$csci1302b = new Section("Intro to Java", "CSCI","1302",23456,"Available",4.0,"Eileen Kraemer");
	$csci1302b->addMeeting($mtg5);
	$csci1302b->addMeeting($mtg6);
	$csci1302b->addMeeting($mtg7);
	$csci1302b->addMeeting($mtg8);
	$csci1302b->setBuildingNumber(1023);
	$csci1302b->setRoomNumber("306");
	$csci->addSection($csci1302b);

	//Testing invalid addition
	$badsection = new Section("Systems Programming","CSCI","1730",22234,"Full",4.0,"Chris Plaue");
	$val = $csci->addSection($badsection);
	echo "Error: " . $csci->getErrorMessage() . " val = " . $val . "\n";
	var_dump($csci);
?>
