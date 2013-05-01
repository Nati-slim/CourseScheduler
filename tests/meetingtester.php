<?php
	require_once("../helpers/Course.php");
	require_once("../helpers/Section.php");
	require_once("../helpers/Meeting.php");
	//Testing invalid times
	$csci = new Course("CSCI","1302");
	$mtg1 = new Meeting(12345, 'VR', "", "");
	$mtg2 = new Meeting(23456, 'AR', "", "");
	$mtg3 = new Meeting(12345, "M", "0215P", "0330P");
	$mtg4 = new Meeting(23456, "W", "0930A", "1045A");
	$mtg5 = new Meeting(23456, "F", "0900A", "1015A");
	$mtg6 = new Meeting(22123, "R", "1145A", "0100P");
	echo "meeting 1: " . $mtg1->getDay() . "\n meeting start Hour: " . $mtg1->getStartHour() . "\n meeting start Minute: "
	. $mtg1->getStartMinute() . "\n  meeting start AMPM: " . $mtg1->getStartAMPM() . "\n meeting end Hour: " . $mtg1->getEndHour() . "\n  meeting end Minute: " . $mtg1->getEndMinute() . "\n  meeting end AMPM: " . $mtg1->getEndAMPM() ."\n  meeting time: " . $mtg1->getMeetingTime() . "\n";
	echo "meeting 5: " . $mtg5->getDay() . "\n meeting start Hour: " . $mtg5->getStartHour() . "\n meeting start Minute: "
	. $mtg5->getStartMinute() . "\n  meeting start AMPM: " . $mtg5->getStartAMPM() . "\n  meeting end Hour: " . $mtg5->getEndHour() . "\n  meeting end Minute: " . $mtg5->getEndMinute() . "\n  meeting end AMPM: " . $mtg5->getEndAMPM() ."\n  meeting time: " . $mtg5->getMeetingTime() . "\n";
	var_dump($mtg6);
?>
