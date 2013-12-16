<?php
$session = new Session();

/**
 * Function to undo effects of magic quotes
 * Returns the $_POST value matching the provided key
 * @param String $var key in $_POST variable
 * @return String $val value matching $_POST['key']
 */
function get_post_var($var){
	$val = filter_var($_POST[$var],FILTER_SANITIZE_MAGIC_QUOTES);
	return $val;
}

/**
 *
 * Handle Requests
 */
$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$semesterSelected = get_post_var('semesterSelection');
	$semesters = array();
	$semesters['201405-UNIV'] = '201405-UNIV';
	$semesters['201402-UNIV'] = '201402-UNIV';
	$semesters['201308-UNIV'] = '201308-UNIV';
	$semesters['201305-UNIV'] = '201305-UNIV';
	$semesters['201405-GWIN'] = '201405-GWIN';
	$semesters['201402-GWIN'] = '201402-GWIN';
	$semesters['201308-GWIN'] = '201308-GWIN';
	$semesters['201305-GWIN'] = '201305-GWIN';
	if (array_key_exists($semesterSelected, $semesters)) {		
		return "assets/json/tp/tp-" . $semesterSelected . ".json";
	}else{
		return "";
	}
}else{
	return "";
}
