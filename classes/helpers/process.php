<?php
/*cp /tmp/courses.csv ./courses.csv
Field 3 --> CoursePrefix, Field 4 --> CourseNumber, Field 5 --> CourseName
$output = shell_exec("cut -d',' -f3-5 courses.csv > source.csv");
echo $output;*/

$data = file_get_contents("/tmp/source.csv"); //Get the entire contents of source.csv
$file = "/home/h1548w56/csv/jsonfiles/courses.json";
//Recall: the fields are enclosed by " and separated by , but each record is separated by \ns
$explosion = explode("\n", $data); //create array separate by new line
$results = "[";
foreach($explosion as $courseDetails){
	$line = explode("\"",$courseDetails);
	//$line[1] --> coursePrefix
	//$line[3] --> courseNumber
	//$line[5] --> courseName
	$results .= "\"". trim($line[1]) . "-" . trim($line[3]) . " " . trim($line[5]) . "\",";
	//$results .= "{\"courseShortName\": " . "\"". trim($line[1]) . "-" . trim($line[3]) . "\",";
	//$results .= "\"courseName\": \"" .trim($line[5]) ."\"},";
}

$results = substr($results,0,strlen($results)-1) . "]";
file_put_contents($file, $results);
?>
