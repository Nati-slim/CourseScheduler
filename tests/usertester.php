<?php
	require_once("../helpers/Course.php");
	require_once("../helpers/Section.php");
	require_once("../helpers/Meeting.php");
	require_once("../helpers/UserSchedule.php");
	require_once("../helpers/DBHelper.php");
	$schedule = new UserSchedule(rand(1,12366468));

	$mtg1 = new Meeting(12345, "M", "0230P", "0345P");
	$mtg2 = new Meeting(12345, "M", "0100P", "0230P");
	$mtg3 = new Meeting(12345, "M", "0215P", "0330P");
	$mtg4 = new Meeting(23456, "W", "0930A", "1045A");
	$mtg5 = new Meeting(23456, "F", "0900A", "1015A");
	$mtg6 = new Meeting(22123, "R", "1145A", "0100P");
	$mtg7 = new Meeting(12345, "M", "0800A", "0915A");
	$mtg8 = new Meeting(12345, "M", "0930A", "1045A");

	$answer = $schedule->isOverlap($mtg1,$mtg2
	echo "isOverlap should be true: " . $answer . "\n";
	$answer = $schedule->isOverlap($mtg1,$mtg3);
	echo "isOverlap should be true: " . $answer . "\n";
	$answer = $schedule->isOverlap($mtg2,$mtg3);
	echo "isOverlap should be true: " .  $answer. "\n";
	$answer = $schedule->isOverlap($mtg2,$mtg6);
	echo "isOverlap should be true: " .  $answer . "\n";
	$answer = $schedule->isOverlap($mtg7,$mtg8);
	echo "isOverlap should be false: " .  $answer . "\n";
?>
