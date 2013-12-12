<?php

//open the csv files
$fp1 = file_get_contents('../../../../csv/offerings/athens/sorted_course_offering_UNIV_201405.csv');
$fp2 = file_get_contents('../../../../csv/offerings/athens/sorted_course_offering_UNIV_201402.csv');
$fp3 = file_get_contents('../../../../csv/offerings/athens/sorted_course_offering_UNIV_201308.csv');
$fp4 = file_get_contents('../../../../csv/offerings/athens/sorted_course_offering_UNIV_201305.csv');
$fp5 = file_get_contents('../../../../csv/offerings/gwinnett/sorted_course_offering_GWIN_201405.csv');
$fp6 = file_get_contents('../../../../csv/offerings/gwinnett/sorted_course_offering_GWIN_201402.csv');
$fp7 = file_get_contents('../../../../csv/offerings/gwinnett/sorted_course_offering_GWIN_201308.csv');
$fp8 = file_get_contents('../../../../csv/offerings/gwinnett/sorted_course_offering_GWIN_201305.csv');
$files = array();
$files[] = $fp1;
$files[] = $fp2;
$files[] = $fp3;
$files[] = $fp4;
$files[] = $fp5;
$files[] = $fp6;
$files[] = $fp7;
$files[] = $fp8;

foreach($files as $file){
	$explosion = explode("\n",$fp2);
	foreach($explosion as $courseDetails){
		$line = explode("\",\"",$courseDetails);
		$course = array();
		$course['term'] = substr($line[0],1);
		$course['callNumber'] = $line[1];
		$course['coursePrefix'] = $line[2];
		$course['courseNumber'] = $line[3];
		$course['courseName'] = trim($line[4]);
		$course['lecturer'] = trim($line[5]);
		$course['available'] = trim($line[6]);
		$course['creditHours'] = $line[7];
		$course['session'] = trim($line[8]);
		$course['days'] = trim($line[9]);
		$course['startTime'] = trim($line[10]);
		$course['endTime'] = trim($line[11]);
		$course['casTaken'] = $line[12];
		$course['casRequired'] = $line[13];
		$course['dasTaken'] = $line[14];
		$course['dasRequired'] = $line[15];
		$course['totalTaken'] = $line[16];
		$course['totalRequired'] = $line[17];
		$course['totalAllowed'] = $line[18];
		$course['building'] = trim($line[19]);
		$course['room'] = $line[20];
		$course['sch'] = $line[21];
		$course['currentProgram'] = substr($line[22],0,-1);
		echo $course;
		break;
	}
	break;
}
?>
