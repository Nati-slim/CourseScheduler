<?php
ini_set('max_execution_time', 600);
require_once('../helpers/CourseHelper.php');

$db = new CourseHelper();
$sections = array();
//$term,$coursePrefix,$courseNumber,$campus
/**/
$days = $db->parseDays("DAILY");
print_r($days);
echo "<br/>";
$days = $db->parseDays("MWF");
print_r($days);
echo "<br/>";
$days = $db->parseDays("MW F");
print_r($days);
echo "<br/>";
$days = $db->parseDays("M WF");
print_r($days);
echo "<br/>";
$days = $db->parseDays("T  U");
print_r($days);
echo "<br/>";
$days = $db->parseDays("T R S");
print_r($days);
echo "<br/>";
$days = $db->parseDays("AR");
print_r($days);
echo "<br/>";
$days = $db->parseDays("VR");
print_r($days);
echo "<br/>";
echo "<br/>";
$sections = $db->getSections('201308','CSCI','2610','UNIV');
if (count($sections) > 0){
	echo "Count: " . count($sections) . "<br/>";
	echo "<br/><br/><br/>";
	foreach($sections as $section){
		print_r($section);
		echo "<br/><br/";
		echo "JSON ENCODING: " . json_encode($section->to_json());
		echo "<br/><br/";
	}
}else{
	echo "Error: " . $db->errorMessage;
}
?>
