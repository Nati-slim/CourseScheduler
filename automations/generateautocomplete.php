<?php
require_once dirname(__FILE__) . '/../../creds/dhpath.inc';
error_reporting(E_ALL);
ini_set('display_errors', 1);
//open the csv files
$path = HOME_DIR . "csv/coursepicker/";
$fileList = array();

//FORMAT of autocomplete 
/*
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
}else{
	printf(" Did not properly generate the sorted and sliced csv files.\n");
	exit();
}
?>
