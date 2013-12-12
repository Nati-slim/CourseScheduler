<?php
require_once("../helpers/Course.php");
require_once("../helpers/Section.php");
require_once("../helpers/Meeting.php");
require_once("../helpers/CourseHelper.php");
require_once("../helpers/UserSchedule.php");
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
	$action = get_post_var("action");
	if (strcmp($action,"addSection") == 0){
		$callNum = get_post_var("addSectionCallNumber");
		$db = new CourseHelper();
		$isFirst = false;
		$init = $_SESSION['init'];
		if (strcmp($init,"initialized") != 0){
			//Initialize user schedule object & set relevant $_SESSION variables
			$userschedule = new UserSchedule(generateToken());
			initialize($userschedule->getUserId(),$userschedule);
			$isFirst = true;
		}

		$userid = $_SESSION['userid'];
		if (!$isFirst){
			$userschedule = unserialize($_SESSION['schedObj'][$userid]);
		}
		$semesterSelected = isset($_SESSION['semesterSelected']) ? $_SESSION['semesterSelected']:"201402-UNIV";
		$arrayVal = explode("-",$semesterSelected);
		$section = $db->getSingleSection($arrayVal[0],$callNum,$arrayVal[1]);
		try{
			if ($section){
				if (strcasecmp($section->getStatus(),"Available") == 0){
					$status = $userschedule->addSection($section);
					if (!$status){
						$result['errorMessage'] = $userschedule->getErrorMessage();
						$_SESSION['errorMessage'] = $userschedule->getErrorMessage();
						echo json_encode($result);
					}else{
						$_SESSION['errorMessage'] = "";
						$_SESSION['infoMessage'] = "Section " . $callNum. " (". $section->getCoursePrefix()."-".$section->getCourseNumber().") added!";
						$_SESSION['schedule'][$userid] = $userschedule->to_json();
						$_SESSION['schedObj'][$userid] = serialize($userschedule);						
						echo $userschedule->to_json();
					}
				}else{
					$result['callNumber'] = $callNum;
					$result['term'] = $arrayVal[0];
					$result['currentProgram']=  $arrayVal[1];
					$result['errorMessage'] = "Invalid section chosen.";
					$_SESSION['errorMessage'] = "Section is not Available";
					echo json_encode($result);
				}
			}else{
				$result['callNumber'] = $callNum;
				$result['term'] = $arrayVal[0];
				$result['currentProgram']=  $arrayVal[1];
				$result['errorMessage'] = "Invalid section chosen.";
				$_SESSION['errorMessage'] = "Invalid section chosen.";
				echo json_encode($result);
			}
		}catch(Exception $e){
			$result['callNumber'] = $callNum;
			$result['term'] = $arrayVal[0];
			$result['currentProgram']=  $arrayVal[1];
			$result['errorMessage'] = $e->getMessage();
			$_SESSION['errorMessage'] = $e->getMessage();
			echo json_encode($result);
		}
	}else if ($deleteSection){


	}else{
		$result['errorMessage'] = "Invalid action.";
		$_SESSION['errorMessage'] = "Invalid action.";
		echo json_encode($result);
	}
}else{
	$result['errorMessage'] = "Invalid Server Request Type found.";
	$_SESSION['errorMessage'] = "Invalid Server Request Type found.";
	echo json_encode($result);
}
?>
