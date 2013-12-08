<?php
/**
 * Returns the $_POST value matching the provided key
 * with the filter (FILTER_SANITIZE_MAGIC_QUOTES)
 * @param String $var key in $_POST variable
 * @return String $val value matching $_POST['key']
 */
function get_post_var($var){
	$val = filter_var($_POST[$var],FILTER_SANITIZE_MAGIC_QUOTES);
	return $val;
}

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$action = get_post_var("save");
	if ($action){
		$blob = base64_decode(substr($action,22));
		$file = "../../assets/schedules/".uniqid("schedule_", true) .".png";
		// Write the contents back to the file
		file_put_contents($file, $blob);
		echo 1;
	}else{
		echo 0;
	}
}else if ($requestType === 'GET'){
	echo 1;
}else{
	echo 0;
}
?>
