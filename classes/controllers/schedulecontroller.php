<?php
require_once("../helpers/Course.php");
require_once("../helpers/Section.php");
require_once("../helpers/Meeting.php");
require_once("../helpers/CourseHelper.php");
session_start();
$result = array();

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

function generateToken($length = 40){
    if (function_exists('openssl_random_pseudo_bytes')){
        $token = base64_encode(openssl_random_pseudo_bytes($length,$strong));
        if ($strong){
            return $token;
        }
    }
	return sha1(uniqid(mt_rand(), true));
}

/**
 * Set the required session variables
 * @param integer @userid user generated id string
 * @param UserSchedule object $schedule
 */
function initialize($userid,$schedule){
	$_SESSION['init'] = "initialized";
	$_SESSION['schedule'][$userid] = $schedule->to_json();
	$_SESSION['schedObj'][$userid] = serialize($schedule);
	$_SESSION['userid'] = $userid;
	$_SESSION['errorMessage'] = "";
}

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$addSectionCallNumber = get_post_var("addSection");
	$deleteSection = get_post_var("deleteSection");
	if ($addSectionCallNumber){
		if ($addSectionCallNumber != 0){
			$db = new CourseHelper();
			$init = $_SESSION['init'];
			$isFirst = false;
			if (strcmp($init,"initialized") != 0){
				//Initialize user schedule object & set relevant $_SESSION variables
				$userschedule = new UserSchedule(generateToken());
				initialize($userschedule->getUserId(),$userschedule);
				$isFirst = true;
			}

			$userid = $_SESSION['userid'];
			$userschedulejson = $_SESSION['schedule'][$userid];
			if (!$isFirst){
				$userschedule = unserialize($_SESSION['schedObj'][$userid]);
			}
			$section = $db->getSingleSection($addSectionCallNumber);
			try{
				if (strcasecmp($section->getStatus(),"Available") == 0){
					$status = $userschedule->addSection($section);
					if (!$status){
						$_SESSION['errorMessage'] = $userschedule->getErrorMessage();
					}else{
						$_SESSION['errorMessage'] = "Section " . $addSection. " (". $section->getCoursePrefix()."-".$section->getCourseNumber().") added!";
						$_SESSION['schedule'][$userschedule->getUserId()] = $userschedule->toJSON();
						$_SESSION['schedObj'][$userid] = serialize($userschedule);
					}
				}else{
					$_SESSION['errorMessage'] = "Section is not Available";
				}
			}catch(Exception $e){
				$_SESSION['errorMessage'] = $e->getMessage();
			}
		}else{
			$_SESSION['errorMessage'] = "Please select a section to add first.";
		}
	}else if ($deleteSection){


	}else[

	}
}else{

}
?>
