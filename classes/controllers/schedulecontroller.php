<?php
require_once("../models/Course.php");
require_once("../models/Section.php");
require_once("../models/Meeting.php");
require_once("../models/UserSchedule.php");
require_once("../helpers/CourseHelper.php");
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

//http://stackoverflow.com/questions/1846202/php-how-to-generate-a-random-unique-alphanumeric-string
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
		//Get semester selected
		$semesterSelected = $session->semesterSelected;
		if ($semesterSelected){
			$arrayVal = explode("-",$semesterSelected);
			if (count($arrayVal) == 2){
				$callNum = get_post_var("addSectionCallNumber");
				if (!isset($session->scheduleObj)){
					//Initialize user schedule object & set relevant $_SESSION variables
					//$userschedule = new UserSchedule(generateToken());
					$userschedule = UserSchedule::makeSchedule(generateToken(),$arrayVal[1],$arrayVal[0]);
					initialize($userschedule->getUserId(),$userschedule);
					$userid = $userschedule->getUserId();
				}else{
					$userid = $session->userid;
					$userschedule = unserialize($session->scheduleObj);	
				}	
				
				//Get section from database
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
						$session->errorMessage = $result['errorMessage'];
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
			}else{			
				$result['errorMessage'] = "Invalid number of parameters found in selected semester.";
				$session->errorMessage = $result['errorMessage'];
			}	
		}else{
			$result['errorMessage'] = "Please choose a semester before adding a section.";
			$session->errorMessage = $result['errorMessage'];
		}
		header("Location: http://apps.janeullah.com/coursepicker/");	
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
		//sending json data back		
	}else if (strcmp($action,"removeAllSections") == 0){
		$userid = $session->userid;
		if (isset($userid)){
			$userschedule = unserialize($session->scheduleObj);	
			if ($userid == $userschedule->getUserId()){
				$semesterSelected = $session->semesterSelected;
				$arrayVal = explode("-",$semesterSelected);
				//$session->unsetAll();
				if (count($arrayVal) == 2){
					$userschedule = UserSchedule::makeSchedule($userid,$arrayVal[1],$arrayVal[0]);
					$session->schedule = json_encode(array());
					$session->scheduleObj = serialize($userschedule->to_array());
					$result['errorMessage'] = "";
					$session->errorMessage = "";
				}else{
					$result['errorMessage'] = "Invalid parameters found for the semester selected.";
					$session->errorMessage = $result['errorMessage'];
				}
			}else{
				$result['errorMessage'] = "Unauthorized to perform this action.";
				$session->errorMessage = $result['errorMessage'];
			}
		}else{
			$result['errorMessage'] = "Unauthorized to perform this action.";
			$session->errorMessage = $result['errorMessage'];
		}
		echo json_encode($result);
	}else if (strcmp($action,"downloadSchedule") == 0){
		//send json data
		$imgDataUrl = get_post_var('dataUrl');
		if ($imgDataUrl){
			$userid = $session->userid;
			if (isset($userid)){
				$userschedule = unserialize($session->scheduleObj);	
				if ($userid == $userschedule->getUserId()){
					$imgData = base64_decode($imgDataUrl);
					$token = bin2hex(openssl_random_pseudo_bytes(25));
					$imgFile = $_SERVER["DOCUMENT_ROOT"]."/coursepicker/assets/schedules/schedule_" . $token . ".png";
					
					if (file_put_contents($imgFile,$imgData)){
						//$imgFile = substr($imgFile,16);
						$userschedule->addImageID($token);
						$result['imgToken'] = $token;
						$result['errorMessage'] = "";
						$session->errorMessage = "";
					}else{
						$result['imgToken'] = "";
						$result['errorMessage'] = "Unable to save image to file";
						$session->errorMessage = "Unable to save image to file.";
					}
				}else{
					$result['imgToken'] = "";
					$result['errorMessage'] = "Not authorized to store this file.";
					$session->errorMessage = "Not authorized to store this file.";
				}
			}else{				
				$result['imgToken'] = "";
				$result['errorMessage'] = "User id not found when attempting to save schedule";
				$session->errorMessage = "User id not found when attempting to save schedule";
			}
		}else{
			$result['imgToken'] = "";
			$result['errorMessage'] = "Invalid action.";
			$session->errorMessage = "Invalid action.";
		}		
		echo json_encode($result);
	}else{
		$result['errorMessage'] = "Invalid action to schedule controller.";
		$session->errorMessage = "Invalid action to schedule controller.";
		echo json_encode($result);
	}
}else{
	$result['errorMessage'] = "Invalid Server Request Type found.";
	$session->errorMessage = "Invalid Server Request Type found.";
	echo json_encode($result);
}
?>
