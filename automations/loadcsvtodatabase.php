<?php
require_once dirname(__FILE__) . '/../../creds/coursepicker.inc';
require_once dirname(__FILE__) . '/../../creds/dhpath.inc';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$path = HOME_DIR . "csv/coursepicker/";
$fileList = array();
try{
	$dbconn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
	foreach(glob('csvfiles/sorted_*.csv') as $file) {
		$fullname = $path . $file;
		//Gets the name e.g. GWIN_201305
		$semester = substr($file,-15,-4);
		//echo $fullname . "\n";	
		//load data local infile '/home/user/csv/coursepicker/csvfiles/sorted_course_offering_GWIN_201405.csv' into table courses fields terminated by ',' enclosed by '"' escaped by '\\' lines terminated by '\n' 
		//`(term` ,  `callNumber` ,  `coursePrefix` ,  `courseNumber` ,  `courseName` ,  `lecturer` ,  `available` ,  `creditHours` ,  `session` ,  `days` ,  `startTime` ,  `endTime` ,  `casTaken` ,  `casRequired` ,  `dasTaken` ,  `dasRequired` ,  `totalTaken` ,  `totalRequired` , `totalAllowed` ,  `building` ,  `room` ,  `sch` ,  `currentProgram`);

		$stmt = "load data local infile '" . $fullname . "' into table courses fields terminated by ',' enclosed by '\"' (term,callNumber,coursePrefix,courseNumber,courseName,lecturer,available,creditHours,session,days,startTime,endTime,casTaken,casRequired,dasTaken,dasRequired,totalTaken,totalRequired,totalAllowed,building,room,sch,currentProgram)";
		$fileList[$semester] = $stmt;
		echo $stmt . "\n";
	}

	if ($dbconn->connect_errno) {
		printf("Connect failed: %s\n", $dbconn->connect_error);
		exit();
	}
	if ($dbconn->query("truncate table courses") === TRUE) {
		printf("Successfully truncated the table courses.\n");
	}else{
		printf("Unable to truncate the table: %s\n", $dbconn->connect_error);
		exit();
	}
	
	foreach($fileList as $key => $sqlStmt){
		/* Create table doesn't return a resultset */
		if ($dbconn->query($sqlStmt) === TRUE) {
			printf("Entries for " . $key . " successfully created.\n");
		}else{
			printf("Failed to insert entries for " . $key . ". Error: %s\n", $dbconn->connect_error);
			exit();
		}
	}
	
}catch(Exception $e){	
	printf("Error: %s\n", $e->message);
	exit();
}
?>
