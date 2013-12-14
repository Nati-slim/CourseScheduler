<?php
require_once("../helpers/Course.php");
require_once("../helpers/Section.php");
require_once("../helpers/Meeting.php");
require_once("../helpers/CourseHelper.php");
require_once("../helpers/UserSchedule.php");
require_once("../helpers/session.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
$session = new Session();
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
	$session->init = "initialized";
	$session->userid = $userid;
	$session->schedule = $schedule->to_json();
	$session->scheduleObj = serialize($schedule->to_array());
	$session->errorMessage = "";
}

function reconstructSchedule($arr){
	$userschedule = new UserSchedule($arr['userid']);
	$userschedule->setErrorMessage($arr['errorMessage']);
	$index = 2;
	while ($index < count($arr)){
		$userschedule[$index] = $arr[$index];
		$index++;
	}
	return $userschedule;
}

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$action = get_post_var("action");
	if (strcmp($action,"addSection") == 0){
		$callNum = get_post_var("addSectionCallNumber");
		if (!isset($session->scheduleObj)){
			//Initialize user schedule object & set relevant $_SESSION variables
			$userschedule = new UserSchedule(generateToken());
			initialize($userschedule->getUserId(),$userschedule);
			$userid = $userschedule->getUserId();
		}else{
			$userid = $session->userid;
			$userschedule = unserialize($session->scheduleObj);	
		}	
		
		$semesterSelected = isset($session->semesterSelected) ? $session->semesterSelected:"201402-UNIV";
		$arrayVal = explode("-",$semesterSelected);
		
		$db = new CourseHelper();
		$section = $db->getSingleSection($arrayVal[0],$callNum,$arrayVal[1]);
		try{
			if ($section){
				if (strcmp($section->getStatus(),"Available") == 0){
					//print_r($userschedule);
					$status = $userschedule->addSection($section);
					if (!$status){
						$result['errorMessage'] = $userschedule->getErrorMessage();
						$session->errorMessage = $userschedule->getErrorMessage();
						//echo json_encode($result);
					}else{
						$session->errorMessage = "";
						$session->userid = $userschedule->getUserId();
						$session->infoMessage = "Section " . $callNum. " (". $section->getCoursePrefix()."-".$section->getCourseNumber().") added!";
						$session->schedule = $userschedule->to_json();	
						$session->scheduleObj = serialize($userschedule);	
						//echo $userschedule->to_json();
					}
				}else{
					$result['callNumber'] = $callNum;
					$result['term'] = $arrayVal[0];
					$result['currentProgram']=  $arrayVal[1];
					$result['errorMessage'] = "Invalid section chosen.";
					$session->errorMessage = "Section is not Available";
					//echo json_encode($result);
				}
			}else{
				$result['callNumber'] = $callNum;
				$result['term'] = $arrayVal[0];
				$result['currentProgram']=  $arrayVal[1];
				$result['errorMessage'] = "Invalid section chosen.";
				$session->errorMessage = "Invalid section chosen.";
				//echo json_encode($result);
			}
		}catch(Exception $e){
			$result['callNumber'] = $callNum;
			$result['term'] = $arrayVal[0];
			$result['currentProgram']=  $arrayVal[1];
			$result['errorMessage'] = $e->getMessage();
			$session->errorMessage = $e->getMessage();
			//echo json_encode($result);
		}
		header("Location: http://apps.janeullah.com/coursepicker/new.php");	
	}else if (strcmp($action,"removeSection") == 0){
		$callNum = get_post_var("sectionToBeRemoved");
		$userid = $session->userid;
		if ($callNum){			
			if (isset($userid)){
				$userschedule = unserialize($session->scheduleObj);	
				$status = $userschedule->deleteSection($callNum);
				if (!$status){
					$result['errorMessage'] = $userschedule->getErrorMessage();
					$session->errorMessage = $userschedule->getErrorMessage();
					echo json_encode($result);
				}else{
					$session->errorMessage = "";
					$session->userid = $userschedule->getUserId();;
					$session->infoMessage = "Section " . $callNum . " deleted!";
					$session->schedule = $userschedule->to_json();	
					$session->scheduleObj = serialize($userschedule);	
					echo $userschedule->to_json();
				}
			}else{
				$result['callNumber'] = $callNum;
				$result['errorMessage'] = "Unauthorized to perform this action.";
				$session->errorMessage = "Unauthorized to perform this action.";
				echo json_encode($result);
			}
		}else{
			$result['errorMessage'] = "Invalid section number.";
			$session->errorMessage = "Invalid section number.";
			echo json_encode($result);
		}				
	}else{
		$result['errorMessage'] = "Invalid action.";
		$session->errorMessage = "Invalid action.";
		echo json_encode($result);
	}
}else{
	$result['errorMessage'] = "Invalid Server Request Type found.";
	$session->errorMessage = "Invalid Server Request Type found.";
	echo json_encode($result);
}
?>
