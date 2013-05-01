<?php
	require_once("../helpers/Course.php");
	require_once("../helpers/Section.php");
	require_once("../helpers/Meeting.php");
	require_once("../helpers/UserSchedule.php");
	require_once("../helpers/DBHelper.php");
	$schedule = new UserSchedule(rand(1,12366468));
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

	$added = $schedule->addSection($csci1302a);
	echo "Added: " . $added . " Message: " . $schedule->getErrorMessage() ."\n";

	//print_r($schedule);

	$mtg5 = new Meeting(23456, "M", "0215P", "0330P");
	$mtg6 = new Meeting(23456, "W", "0930A", "1045A");
	$mtg7 = new Meeting(23456, "F", "0900A", "1015A");
	$mtg8 = new Meeting(23456, "R", "1145A", "0100P");
	$csci1302b = new Section("Intro to Java", "CSCI","1302",23456,"Available",4.0,"Eileen Kraemer");
	$csci1302b->addMeeting($mtg5);
	$csci1302b->addMeeting($mtg6);
	$csci1302b->addMeeting($mtg7);
	$csci1302b->addMeeting($mtg8);
	$added = $schedule->addSection($csci1302b);
	echo "Added: " . $added . " Message: " . $schedule->getErrorMessage() ."\n";
	//print_r($schedule);

	//Duplicate section should fail
	$added = $schedule->addSection($csci1302a);
	echo "Added: " . $added . " Message: " . $schedule->getErrorMessage() ."\n";
	print_r($schedule);

	$mtg9 = new Meeting(12345, "M", "0230P", "0345P");
	$mtg10 = new Meeting(12345, "M", "0100P", "0230P");
	$mtg11 = new Meeting(12345, "M", "0215P", "0330P");
	$mtg12 = new Meeting(23456, "W", "0930A", "1045A");
	$mtg13 = new Meeting(23456, "F", "0900A", "1015A");
	$mtg14 = new Meeting(22123, "R", "1145A", "0100P");
	$mtg15 = new Meeting(12345, "M", "0800A", "0915A");
	$mtg16 = new Meeting(12345, "M", "0930A", "1045A");
	$mtg17 = new Meeting(12345, "M", "0800A", "0915P");
	$mtg18 = new Meeting(12345, "M", "0215P", "0300P");
	$mtg18 = new Meeting(12345, "M", "0215P", "0300P");


	if ($schedule->isOverlap($mtg9,$mtg10)){
		echo "mtg9/mtg10 Overlap detected between " . $mtg9->getMeetingTime() . " and " . $mtg10->getMeetingTime() . "\n";
	}

	if ($schedule->isOverlap($mtg9,$mtg11)){
		echo "mtg9/mtg11 Overlap detected between " . $mtg9->getMeetingTime() . " and " . $mtg11->getMeetingTime() . "\n";
	}

	if ($schedule->isOverlap($mtg9,$mtg12)){
		echo "mtg9/mtg12 Overlap detected between " . $mtg9->getMeetingTime() . " and " . $mtg12->getMeetingTime() . "\n";
	}

	if ($schedule->isOverlap($mtg10,$mtg15)){
		echo "mtg10/mtg15 Overlap detected between " . $mtg10->getMeetingTime() . " and " . $mtg15->getMeetingTime() . "\n";
	}

	if ($schedule->isOverlap($mtg15,$mtg16)){
		echo "mtg15/mtg16 Overlap detected between " . $mtg15->getMeetingTime() . " and " . $mtg16->getMeetingTime() . "\n";
	}

	if ($schedule->isOverlap($mtg11,$mtg17)){
		echo "mtg11/mtg17 Overlap detected between " . $mtg11->getMeetingTime() . " and " . $mtg17->getMeetingTime() . "\n";
	}

	if ($schedule->isOverlap($mtg11,$mtg18)){
		echo "mtg11/mtg18 Overlap detected between " . $mtg11->getMeetingTime() . " and " . $mtg18->getMeetingTime() . "\n";
	}
?>
