<?php
require_once('../../creds/dhpath.inc');
error_reporting(E_ALL);
ini_set('display_errors', 1);
//open the csv files
$path = HOME_DIR . "csv/coursepicker/";
$fileList = array();

//FORMAT of autocomplete 
/*
 * ,
    {
        "coursePrefix": "SOWK",
        "courseNumber": "7273",
        "courseName": "ADDICTIVE DISORDERS",
        "value": "SOWK-7273",
        "tokens": [
            "SOWK",
            "7273",
            "ADDICTIVE",
            "DISORDERS"
        ]
    },
    {
        "coursePrefix": "SOWK",
        "courseNumber": "7387",
        "courseName": "TOPICS SOC PROB",
        "value": "SOWK-7387",
        "tokens": [
            "SOWK",
            "7387",
            "TOPICS",
            "SOC",
            "PROB"
        ]
    }
]*/

foreach(glob('csvfiles/pre_tp_*.csv') as $file) {
    //echo $path . $file . "\n";
    //Gets the name e.g. GWIN_201305
    $semester = substr($file,-15,-4);
    echo $semester . "\n";
    $fileList[$semester] = file_get_contents($path . $file);
}

if (count($fileList) > 0){
	foreach($fileList as $key => $file){
		//Blow up each .csv file
		$explosion = explode("\n",$file);
		$csvArray = array();
		foreach($explosion as $courseDetail){
			//Separated by ","
			$line = explode("\",\"",$courseDetail);
			//print_r($line);
			if (strlen($line[0]) > 0){
				$course = array();
				$course['coursePrefix'] = substr($line[0],1);
				$course['courseNumber'] = trim("" . $line[1]);
				$course['courseName'] = trim(substr($line[2],0,-1));
				$course['value'] = $course['coursePrefix'] . "-" . $course['courseNumber'];
				$tokens = array();
				$tokens[] = $course['coursePrefix'];
				$tokens[] = $course['courseNumber'];	
				$explodeCourseName = explode(" ",$course['courseName']);
				foreach($explodeCourseName as $item){
					$tokens[] = $item;
				}
				$course['tokens'] = $tokens;
				$csvArray[] =$course;
			}
		}
		//Write contents of the array to the json file
		//GWIN_201305
		$arrVal = explode("_",$key);
		//echo $jsonFile;
		$jsonFile = HOME_DIR . "apps.janeullah.com/coursepicker/assets/json/tp/tp-" . $arrVal[1] . "-" . $arrVal[0] . ".json";
		//$jsonFile = HOME_DIR . "csv/coursepicker/jsonfiles/tp-"  . $arrVal[1] . "-" . $arrVal[0] . ".json";
		file_put_contents($jsonFile,json_encode($csvArray));
	}

	/*foreach($files as $file){
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
	}*/
}else{
	printf(" Did not properly generate the sorted and sliced csv files.\n");
	exit();
}
?>
