<?php
ini_set('memory_limit', '512M');
//open the csv files
$fp1 = file_get_contents('../../offerings/athens/sorted_course_offering_UNIV_201405.csv');
$fp2 = file_get_contents('../../offerings/athens/sorted_course_offering_UNIV_201402.csv');
$fp3 = file_get_contents('../../offerings/athens/sorted_course_offering_UNIV_201308.csv');
$fp4 = file_get_contents('../../offerings/athens/sorted_course_offering_UNIV_201305.csv');
$fp5 = file_get_contents('../../offerings/gwinnett/sorted_course_offering_GWIN_201405.csv');
$fp6 = file_get_contents('../../offerings/gwinnett/sorted_course_offering_GWIN_201402.csv');
$fp7 = file_get_contents('../../offerings/gwinnett/sorted_course_offering_GWIN_201308.csv');
$fp8 = file_get_contents('../../offerings/gwinnett/sorted_course_offering_GWIN_201305.csv');
$files = array();
$files['201405-UNIV'] = $fp1;
$files['201402-UNIV'] = $fp2;
$files['201308-UNIV'] = $fp3;
$files['201305-UNIV'] = $fp4;
$files['201405-GWIN'] = $fp5;
$files['201402-GWIN'] = $fp6;
$files['201308-GWIN'] = $fp7;
$files['201305-GWIN'] = $fp8;


$masterList = array();

$counter = 0;
foreach($files as $file){
	$explosion = explode("\n",$file);
	$lastItem = end($explosion);
	$lastItem = array_pop($explosion);
	$list = array();
	$tplist = array();
	foreach($explosion as $courseDetails){
		$line = explode("\",\"",$courseDetails);
		$course = array();
		$course['term'] = substr($line[0],1);
		$course['callNumber'] = $line[1];
		$course['coursePrefix'] = $line[2];
		$course['courseNumber'] = trim($line[3]);
		$course['courseName'] = trim($line[4]);
		$course['lecturer'] = trim($line[5]);
		$course['available'] = trim($line[6]);
		$course['creditHours'] = $line[7];
		$course['session'] = trim($line[8]);
		$course['days'] = trim($line[9]);
		$course['startTime'] = trim($line[10]);
		$course['endTime'] = trim($line[11]);
		/*$course['casTaken'] = $line[12];
		$course['casRequired'] = $line[13];
		$course['dasTaken'] = $line[14];
		$course['dasRequired'] = $line[15];
		$course['totalTaken'] = $line[16];
		$course['totalRequired'] = $line[17];
		$course['totalAllowed'] = $line[18];*/
		$course['building'] = $line[19];
		$course['room'] = $line[20];
		$course['sch'] = $line[21];
		$course['currentProgram'] = substr($line[22],0,-1);
		$list[] = $course;

		//typeahead stuff
		$tpcourse = array();
		$tpcourse['coursePrefix'] = $course['coursePrefix'];
		$tpcourse['courseNumber'] = trim($course['courseNumber']);
		$tpcourse['courseName'] = $course['courseName'];
		$tpcourse['lecturer'] = $course['lecturer'];
		$tpcourse['value'] = $course['coursePrefix'] . "-" . trim($course['courseNumber']);
		$tokens = explode(" ",$course['courseName']);
		array_unshift($tokens,$course['coursePrefix'],$course['courseNumber']);
		$tpcourse['tokens'] = $tokens;
		$tplist[] = $tpcourse;
	}
	//echo json_encode($list) . "\n";
	$indexname = '';
	$filename = "../../assets/json/courses.json";
	$tpfilename = "../../assets/json/tp-courses.json";
	if ($counter == 0){
		$masterList['201405-UNIV'] = $list;
		$filename = '../../assets/json/201405-UNIV.json';
		$tpfilename = '../../assets/json/tp-201405-UNIV.json';
	}else if ($counter == 1){
		$masterList['201402-UNIV'] = $list;
		$filename = '../../assets/json/201402-UNIV.json';
		$tpfilename = '../../assets/json/tp-201402-UNIV.json';
	}else if ($counter == 2){
		$masterList['201308-UNIV'] = $list;
		$filename = '../../assets/json/201308-UNIV.json';
		$tpfilename = '../../assets/json/tp-201308-UNIV.json';	
	}else if ($counter == 3){
		$masterList['201305-UNIV'] = $list;
		$filename = '../../assets/json/201305-UNIV.json';
		$tpfilename = '../../assets/json/tp-201305-UNIV.json';
	}else if ($counter == 4){
		$masterList['201405-GWIN'] = $list;
		$filename = '../../assets/json/201405-GWIN.json';
		$tpfilename = '../../assets/json/tp-201405-GWIN.json';
	}else if ($counter == 5){
		$masterList['201402-GWIN'] = $list;
		$filename = '../../assets/json/201402-GWIN.json';
		$tpfilename = '../../assets/json/tp-201402-GWIN.json';
	}else if ($counter == 6){
		$masterList['201308-GWIN'] = $list;
		$filename = '../../assets/json/201308-GWIN.json';
		$tpfilename = '../../assets/json/tp-201308-GWIN.json';
	}else if ($counter == 7){
		$masterList['201305-GWIN'] = $list;
		$filename = '../../assets/json/201305-GWIN.json';
		$tpfilename = '../../assets/json/tp-201305-GWIN.json';
	}
	
	//Write to file
	$fp = fopen($filename,"w");
	fwrite($fp, json_encode($list));
	fclose($fp);

	$tpw = fopen($tpfilename,"w");
	fwrite($tpw,json_encode($tplist));
	fclose($tpw);
	$counter++;
}

$fp = fopen("../../assets/json/allcourses.json","w");
fwrite($fp, json_encode($masterList));
fclose($fp);
?>
