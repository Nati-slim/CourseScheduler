<?php
ini_set('max_execution_time', 600);
require_once('../helpers/CourseHelper.php');
$course = array();
$course['term'] = "201405";
$course['callNumber'] = 22984;
$course['coursePrefix'] = "ACCT";
$course['courseNumber'] = "2101";
$course['courseName'] = "PRIN OF ACC I";
$course['lecturer'] = "BHANDARKAR";
$course['available'] = "Available";
$course['creditHours'] = 3.00;
$course['session'] = "First";
$course['days'] = "MTWRF";
$course['startTime'] = "0915A";
$course['endTime'] = "1130A";
$course['casTaken'] = 0;
$course['casRequired'] = "";
$course['dasTaken'] = "";
$course['dasRequired'] = "";
$course['totalTaken'] = "";
$course['totalRequired'] = "";
$course['totalAllowed'] = "";
$course['building'] = "AR";
$course['room'] = "AR";
$course['sch'] = "02";
$course['currentProgram'] = "UNIV";


function processLine($line){

}


$db = new CourseHelper();
//open the csv files
//$fp1 = file_get_contents('../../offerings/athens/sorted_course_offering_UNIV_201405.csv');
//$fp2 = file_get_contents('../../offerings/athens/sorted_course_offering_UNIV_201402.csv');
//$fp3 = file_get_contents('../../offerings/athens/sorted_course_offering_UNIV_201308.csv');
//$fp4 = file_get_contents('../../offerings/athens/sorted_course_offering_UNIV_201305.csv');
//$fp5 = file_get_contents('../../offerings/gwinnett/sorted_course_offering_GWIN_201405.csv');
//$fp6 = file_get_contents('../../offerings/gwinnett/sorted_course_offering_GWIN_201402.csv');
//$fp7 = file_get_contents('../../offerings/gwinnett/sorted_course_offering_GWIN_201308.csv');
$fp8 = file_get_contents('../../offerings/gwinnett/sorted_course_offering_GWIN_201305.csv');
$explosion = explode("\n",$fp8);
foreach($explosion as $courseDetails){
		$line = explode("\",\"",$courseDetails);
		//print_r($line);
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
		$results = $db->addCourse($course);
		if (!$results){
			$isError = true;
			echo $db->errorMessage;
			break;
		}
}
?>
