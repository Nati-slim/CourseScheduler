<?php
/**
 * Controller for handling client side changes to the UserSchedule
 * object. E.g. adding/removing sections, removing all sections,
 * downloading the png image of the schedule are managed here.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 4.0 of the 
 * Creative Commons Attribution-ShareAlike 4.0 International License
 * that is available through the world-wide-web at the following URI:
 * http://creativecommons.org/licenses/by-sa/4.0/.
 *
 * @category   CategoryName
 * @package    PackageName
 * @author     Original Author <jane@janeullah.com>
 * @license    http://creativecommons.org/licenses/by-sa/4.0/  Creative Commons Attribution-ShareAlike 4.0 International License
 * @version    GIT: $Id$
 * @link       https://github.com/janoulle/CourseScheduler
 * @since      N/A
 * @deprecated N/A
 */
require_once dirname(__FILE__) . '/../../includes/mixpanel/lib/Mixpanel.php';
require_once dirname(__FILE__) . '/../models/Course.php';
require_once dirname(__FILE__) . '/../models/Section.php';
require_once dirname(__FILE__) . '/../models/Meeting.php';
require_once dirname(__FILE__) . '/../models/UserSchedule.php';
require_once dirname(__FILE__) . '/../helpers/CourseHelper.php';
require_once dirname(__FILE__) . '/../helpers/ScheduleHelper.php';
require_once dirname(__FILE__) . '/../helpers/session.php';
require_once dirname(__FILE__) . '/../../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../../../creds/dhpath.inc';
require_once dirname(__FILE__) . '/../../../../creds/mixpanel_coursepicker.inc';
$session = new Session();
$result = array();
$debug = DEBUGSTATUS;


// get the Mixpanel class instance, replace with your
// load production token
if (!$debug){
    $mp = Mixpanel::getInstance(CP_PROD_MIXPANEL_API_KEY);
}else{
    //load dev token
    $mp = Mixpanel::getInstance(CP_DEV_MIXPANEL_API_KEY);
}

$semesters = array();
$semesters['201405-UNIV'] = '(Athens) Summer 2014';
$semesters['201402-UNIV'] = '(Athens) Spring 2014';
$semesters['201308-UNIV'] = '(Athens) Fall 2013';
$semesters['201305-UNIV'] = '(Athens) Summer 2013';
$semesters['201405-GWIN'] = '(Gwinnett) Summer 2014';
$semesters['201402-GWIN'] = '(Gwinnett) Spring 2014';
$semesters['201308-GWIN'] = '(Gwinnett) Fall 2013';
$semesters['201305-GWIN'] = '(Gwinnett) Summer 2013';

/**
 * Function to autoload classes needed during serialization/unserialization
 * 
 * @param string $class_name name of the Class being loaded
 *
 * @return void
 */
function __autoload($class_name)
{
    include dirname(__FILE__) . '/../models/'. $class_name . '.php';
}

//Set up debug stuff
//When  not debugging, log to a file!
if (!$debug) {
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
}

/**
 * The $pvt debugging messages may contain characters that would need to be
 * quoted if we were producing HTML output, like we would be in a real app,
 * but we're using text/plain here.  Also, $debug is meant to be disabled on
 * a "production install" to avoid leaking server setup details.
 * 
 * @param string $pub public details of the error
 * @param string $pvt Private details of the error
 * 
 * @return string $msg The error message
 * 
 */
function fail($pub, $pvt = '')
{
    global $debug;
    $msg = $pub;
    if ($debug && $pvt !== '') {
        $msg .= ": $pvt";
    }

    return $msg;
}

/**
 * Function to undo effects of magic quotes
 * Returns the $_POST value matching the provided key
 * 
 * @param string $var key in $_POST variable
 * 
 * @return string $val value matching $_POST['key']
 */
function getPost($var)
{
    $val = filter_var($_POST[$var], FILTER_SANITIZE_MAGIC_QUOTES);

    return $val;
}

/**
 * http://stackoverflow.com/questions/1846202/php-how-
 * to-generate-a-random-unique-alphanumeric-string
 * http://www.openwall.com/articles/PHP-Users-Passwords
 * Function to generate a cryptographically secure token
 *
 * @param int $length default is 40
 *
 * @return string token
 *
 */
function generateToken($length = 40)
{
    //bin2hex(openssl_random_pseudo_bytes(16));
    //base64_encode(openssl_random_pseudo_bytes($length, $strong));
    
    if (function_exists('openssl_random_pseudo_bytes')) {
        $token = bin2hex(openssl_random_pseudo_bytes($length, $strong));
        if ($strong) {
            return $token;
        }
    }

    return sha1(uniqid(mt_rand(), true));
}

/**
 * Sets the required session variables (init, user id,schedule, 
 * scheduleObj and errorMessage
 * 
 * @param string       $userid   must match the userid in schedule
 * @param UserSchedule $schedule contains the user's schedule
 * 
 * @return void
 * 
 */ 
function initialize($userid,$schedule)
{
    $session->init = "initialized";
    $session->userid = $userid;
    $session->schedule = $schedule->to_json();
    $session->scheduleObj = serialize($schedule->to_array());
    $session->errorMessage = "";
}

/**
 * Reconstruct the Userschedule object when provided with an array
 * which contains the data in a UserSchedule object
 * 
 * @param array $arr array containing data in UserSchedule object
 * 
 * @return UserSchedule 
 * 
 */ 
function reconstructSchedule($arr)
{
    $userschedule = new UserSchedule($arr['userid']);
    $userschedule->setErrorMessage($arr['errorMessage']);
    $index = 2;
    while ($index < count($arr)) {
        $userschedule[$index] = $arr[$index];
        $index++;
    }

    return $userschedule;
}

function validateShortname($name){
    //if (preg_match("/^(?![- 0-9])([a-zA-Z0-9-])+{1,60}$/D", $name)) {
    if (!preg_match("/^[0-9a-zA-Z]{1,80}$/D", $name)) {
        return "Please enter only letters/numbers from the English alphabet.Must be at least 1 character.";        
    }else{
        return 'OK';
    }
}

/**
 * Method to update the saved user schedule object to the database
 * user must be logged in i.e. signed up to use this feature
 * 
 * @return array
 * 
 */ 
function updateSchedule($userid,$user,$selectedSchedule,$scheduleID,$savedShortName){
    $msg = array(); 
    if ($scheduleID){
        $scheduleHelper = new ScheduleHelper();
        if ($selectedSchedule->getScheduleID() == $scheduleID){
            $selectedSchedule->setShortName($savedShortName);
            //save this schedule to the database
            $saved = $scheduleHelper->updateSchedule($selectedSchedule);
            if ($saved){
                $msg['errorMessage'] = "";
                $msg['shortName'] = $savedShortName;
            }else{
                $msg['errorMessage'] = fail("Failed to save schedule to database.",$scheduleHelper->errorMessage);
            }
        }else{
            $msg['errorMessage'] = "Mismatch between found schedule object and the schedule id submitted";
            $msg['scheduleID'] = $scheduleID;
            $session->errorMessage = $msg['errorMessage'];
        }
    }else{
        $msg['errorMessage'] = "Schedule ID not found.";
        $msg['scheduleID'] = $scheduleID;
        $session->errorMessage = $msg['errorMessage'];
    }
    return $msg;
}

/**
 * Method to save a user's schedule to the database
 * user must be logged in i.e. signed up to use this feature
 * 
 * @return array saved schedule's row number and any error information
 * 
 */ 
function saveSchedule($userid,$user,$defaultSchedule,$scheduleID,$shortName1,$shortName2){
    $msg = array();  
    if (strcmp($shortName1, $shortName2) == 0){
        if ($scheduleID && strlen($scheduleID) > 0){                
            $scheduleHelper = new ScheduleHelper();
            if ($defaultSchedule->getScheduleID() == $scheduleID){                
                $defaultSchedule->setShortName($shortName1);
                //save this schedule to the database
                $savedId = $scheduleHelper->saveSchedule($defaultSchedule);
                if ($savedId){
                    $msg['errorMessage'] = "";
                    $msg['shortName'] = $shortName1;
                }else{
                    $msg['errorMessage'] = fail("Failed to save schedule to database.",$scheduleHelper->errorMessage);
                }
            }else{                    
                //get the matching schedule object from the user object
                foreach($user->getSchedules() as $schedObj){
                    if ($schedObj->getScheduleID() == $scheduleID){
                        $scheduleToSave = $schedObj;
                        break;
                    }
                }
                
                //If schedule object was found, save it.
                if ($scheduleToSave){
                    $scheduleToSave->setShortName($shortName1);
                    $savedId = $scheduleHelper->saveSchedule($scheduleToSave);
                    if ($savedId){
                        $msg['errorMessage'] = "";
                        $msg['shortName'] = $shortName1;
                    }else{
                        $msg['errorMessage'] = fail("Failed to save schedule to database.",$scheduleHelper->errorMessage);
                    }
                }else{
                    $msg['errorMessage'] = "Unable to find User Schedule object to save.";
                    $session->errorMessage = $msg['errorMessage'];
                }                   
            }
        }else{
            $msg['errorMessage'] = "Schedule ID not found.";
            $msg['userid'] = $userid;
            $msg['shortname1'] = $shortName1;
            $msg['shortName2'] = $shortName2;
            $msg['scheduleID'] = $scheduleID;
            $session->errorMessage = $msg['errorMessage'];
        }
    }else{
        $msg['errorMessage'] = "Short name fields must match.";
        $msg['userid'] = $userid;
        $msg['shortname1'] = $shortName1;
        $msg['shortName2'] = $shortName2;
        $msg['scheduleID'] = $scheduleID;
        $session->errorMessage = $msg['errorMessage'];
    }
    return $msg;
}

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
    $action = getPost("action");
    if (strcmp($action, "addSection") == 0) {
        //Get semester selected
        $semesterSelected = $session->semesterSelected;
        if ($semesterSelected) {
            $arrayVal = explode("-", $semesterSelected);
            if (count($arrayVal) == 2) {
                $callNum = getPost("addSectionCallNumber");

                //check if user is present
                if (isset($session->loggedIn) && $session->loggedIn) {
                    $user = unserialize($session->loggedInUser);
                    if ($user) {
                        //Get user id
                        $userid = $user->getUserid();
                        //Check user schedule object presence
                        if (!isset($session->scheduleObj)) {
                            //userid,campus,term,scheduleid
                            $userschedule = UserSchedule::makeSchedule($userid, $arrayVal[1], $arrayVal[0], generateToken());
                        } else {
                            $userschedule = unserialize($session->scheduleObj);
                        }
                        $result['errorMessage'] = "";
                        $session->errorMessage = $result['errorMessage'];
                    } else {
                        $result['errorMessage'] = "Unable to unserialize user properly";
                        $session->errorMessage = $result['errorMessage'];
                    }
                } else {
                    if (!isset($session->scheduleObj)) {
                        //Initialize user schedule object & set relevant $_SESSION variables
                        if (isset($session->userid)) {
                            //userid,campus,term,scheduleid
                            $userschedule = UserSchedule::makeSchedule($session->userid, $arrayVal[1], $arrayVal[0], generateToken());
                        } else {
                            $userschedule = UserSchedule::makeSchedule(generateToken(), $arrayVal[1], $arrayVal[0], generateToken());
                        }
                        $userid = $userschedule->getUserId();
                        initialize($userid, $userschedule);
                    } else {
                        $userid = $session->userid;
                        $userschedule = unserialize($session->scheduleObj);
                    }
                    $result['errorMessage'] = "";
                    $session->errorMessage = $result['errorMessage'];
                }

                try {
                    //Get section from database
                    $db = new CourseHelper();
                    $section = $db->getSingleSection($arrayVal[0], $callNum, $arrayVal[1]);                   
                    
                    
                    if ($section) {
                        //make sure the section's semester and campus matches the user schedule object                       
                        if (strcmp($userschedule->getSemester(), $arrayVal[0]) != 0 || strcmp($userschedule->getCampus(), $arrayVal[1]) != 0){
                            $result['callNumber'] = $callNum;
                            $result['term'] = $arrayVal[0];
                            $result['currentProgram']=  $arrayVal[1];
                            $result['errorMessage'] = "This schedule is bound to the " . $semesters[$userschedule->getSemester()."-".$userschedule->getCampus()] . " semester. Only add courses from the " . $semesters[$userschedule->getSemester()."-".$userschedule->getCampus()] . " catalog.";
                            echo json_encode($result);
                        }elseif (strcmp($section->getStatus(), "Available") == 0) {
                            //print_r($userschedule);
                            $status = $userschedule->addSection($section);
                            if (!$status) {
                                $result['errorMessage'] = $userschedule->getErrorMessage();
                                echo json_encode($result);
                            } else {
                                $session->errorMessage = "";
                                $session->userid = $userschedule->getUserId();
                                $session->schedule = $userschedule->to_json();
                                $session->scheduleObj = serialize($userschedule);
                                // track an event
                                $mp->track("add section", array("success" => $callNum));
                    
                                //Fix logic of this later
                                try {
                                    if ($user) {
                                        if ($user instanceOf User) {
                                            if ($user->addSchedule($userschedule)) {
                                                $result['errorMessage'] = "";
                                                $session->loggedInUser = serialize($user);
                                            } else {
                                                $result['errorMessage'] = $user->getErrorMessage();
                                            }
                                        } else {
                                            $result['errorMessage'] = "Object found not instance of User class.";
                                        }
                                    } else {
                                        //Do nothing. Only means user hasn't logged in yet.
                                    }
                                } catch (Exception $e) {
                                    $result['errorMessage'] = fail("Error updating the internal User object",$e->getMessage());
                                }
                                $result['errorMessage'] = "";
                                $result['infoMessage'] = "Section " . $callNum. " (". $section->getCoursePrefix()."-".$section->getCourseNumber().") added!";
                                $result['userschedule'] = $userschedule->to_json();
                                echo json_encode($result);
                            }
                        } else {
                            $result['callNumber'] = $callNum;
                            $result['term'] = $arrayVal[0];
                            $result['currentProgram']=  $arrayVal[1];
                            $result['errorMessage'] = "Section is not Available";
                            echo json_encode($result);
                        }
                    } else {
                        $result['callNumber'] = $callNum;
                        $result['term'] = $arrayVal[0];
                        $result['currentProgram'] =  $arrayVal[1];
                        $result['errorMessage'] = fail("Invalid section chosen.",$db->errorMessage);
                        echo json_encode($result);
                    }
                } catch (Exception $e) {
                    $result['callNumber'] = $callNum;
                    $result['term'] = $arrayVal[0];
                    $result['currentProgram'] =  $arrayVal[1];
                    $result['errorMessage'] = fail("Error instantiating a helper object",$e->getMessage());
                    echo json_encode($result);
                }
            } else {
                $result['errorMessage'] = "Invalid number of parameters found in selected semester.";
                echo json_encode($result);
            }
        } else {
            $result['errorMessage'] = "Please choose a semester before adding a section.";
            echo json_encode($result);
        }
    } elseif (strcmp($action, "removeSection") == 0) {
        $callNum = getPost("sectionToBeRemoved");
        $userid = $session->userid;
        if ($callNum) {
            if (isset($userid)) {
                $userschedule = unserialize($session->scheduleObj);
                $status = $userschedule->deleteSection($callNum);
                if (!$status) {
                    $result['errorMessage'] = $userschedule->getErrorMessage();
                    $session->errorMessage = $userschedule->getErrorMessage();
                    echo json_encode($result);
                } else {
                    $session->errorMessage = "";
                    $session->userid = $userschedule->getUserId();
                    $session->schedule = $userschedule->to_json();
                    $session->scheduleObj = serialize($userschedule);
                    
                    //update schedule insider user object
                    if (isset($session->loggedInUser)){
                        $user = unserialize($session->loggedInUser);
                        if ($user){
                            foreach($user->getSchedules() as $currSchedule){
                                if (strcmp($userschedule->getScheduleID(), $currSchedule->getScheduleID()) == 0){
                                    $found = true;
                                    //Should simply replace the same scheduleID with a diff. object
                                    $user->addSchedule($userschedule);
                                    //update the user object in session.
                                    $session->loggedInUser = serialize($user);
                                    $res['userMsg'] = "User object updated.";
                                    break;
                                }
                            }
                        }else{
                            $result['userMessage'] = "Could not update the userschedule object in the user.";
                            $session->errorMessage = $result['userMessage'];
                        }
                    }
                    $mp->track("remove section", array("success" => $callNum));
                    $result['errorMessage'] = "";
                    $result['infoMessage'] = "Section " . $callNum . " deleted!";
                    $result['userschedule'] = $userschedule->to_json();
                    echo json_encode($result);
                    //echo $userschedule->to_json();
                }
            } else {
                $result['callNumber'] = $callNum;
                $result['errorMessage'] = "Unauthorized to perform this action.";
                $session->errorMessage = "Unauthorized to perform this action.";
                echo json_encode($result);
            }
        } else {
            $result['errorMessage'] = "Invalid section number.";
            $session->errorMessage = "Invalid section number.";
            echo json_encode($result);
        }
        //sending json data back
    } elseif (strcmp($action, "removeAllSections") == 0) {
        //Revisit logic of this. currently if user is logged in, all the 
        //created schedules are saved. Maybe make saving an explicit action
        $userid = $session->userid;
        if (isset($userid)) {
            $userschedule = unserialize($session->scheduleObj);
            if ($userid == $userschedule->getUserId()) {
                $semesterSelected = $session->semesterSelected;
                $arrayVal = explode("-", $semesterSelected);
                //Remove all from the schedule will cause a new schedule id to be generated
                if (count($arrayVal) == 2) {
                    $oldScheduleID = $userschedule->getScheduleID();
                    $userschedule = UserSchedule::makeSchedule($userid, $arrayVal[1], $arrayVal[0], generateToken());
                    $session->schedule = $userschedule->to_json();
                    $session->scheduleObj = serialize($userschedule);
                    $session->selectedScheduleID = $userschedule->getScheduleID();
                    $result['errorMessage'] = "";
                    $session->errorMessage = "";
                    $mp->track("remove all sections", array("old schedule id" => $oldScheduleID));
                } else {
                    $result['errorMessage'] = "Invalid parameters found for the semester selected.";
                    $session->errorMessage = $result['errorMessage'];
                }
            } else {
                $result['errorMessage'] = "User id mismatch";
                $session->errorMessage = $result['errorMessage'];
            }
        } else {
            $result['errorMessage'] = "Missing user id.";
            $session->errorMessage = $result['errorMessage'];
        }
        echo json_encode($result);
    } elseif (strcmp($action, "downloadSchedule") == 0) {
        //send json data
        $imgDataUrl = getPost('dataUrl');
        if ($imgDataUrl) {
            $userid = $session->userid;
            if (isset($userid)) {
                $userschedule = unserialize($session->scheduleObj);
                $ref = $_SERVER['HTTP_REFERER'];
                if (!$userschedule){
                    //check for $session->scheduleObject
                    $userschedule = unserialize($session->scheduleObject);
                }
                //check referer. let users coming from share/ download stuff too
                //maybe investigate the removal of this check completely
                //http://stackoverflow.com/questions/14854117/php-allow-access-to-specific-referrer-url-page-only
                
                //check if there are sections in the course
                if (count($userschedule->getSchedule()) == 0){
                    $result['imgToken'] = "";
                    $result['errorMessage'] = "Please add at least 1 course to your schedule.";
                    $session->errorMessage = $result['errorMessage'];
                }elseif ($userid == $userschedule->getUserId() || ($ref === 'http://apps.janeullah.com/share/')) {
                    $imgData = base64_decode($imgDataUrl);
                    $token = bin2hex(openssl_random_pseudo_bytes(25));
                    $imgFile = $_SERVER["DOCUMENT_ROOT"]."/coursepicker/assets/schedules/schedule_" . $token . ".png";

                    if (file_put_contents($imgFile, $imgData)) {
                        //$imgFile = substr($imgFile,16);
                        $userschedule->addImageID($token);
                        $result['imgToken'] = $token;
                        $result['errorMessage'] = "";
                        $session->errorMessage = "";
                        $mp->track("download schedule as image", array("image_token" => $token));
                    } else {
                        $result['imgToken'] = "";
                        $result['errorMessage'] = "Unable to save image to file";
                        $session->errorMessage = $result['errorMessage'];
                    }
                } else {
                    $result['imgToken'] = "";
                    $result['errorMessage'] = "Not authorized to store this file.";
                    $session->errorMessage = $result['errorMessage'];
                }
            } else {
                $result['imgToken'] = "";
                $result['errorMessage'] = "User id not found when attempting to save schedule";
                $session->errorMessage = $result['errorMessage'];
            }
        } else {
            $result['imgToken'] = "";
            $result['errorMessage'] = "Invalid action.";
            $session->errorMessage = $result['errorMessage'];
        }
        echo json_encode($result);
    } elseif (strcmp($action, "saveSchedule") == 0){ 
        $userid = $session->userid;
        $user = unserialize($session->loggedInUser);
        $defaultSchedule = unserialize($session->scheduleObj);
        $scheduleID = getPost('scheduleID');
        $selectedSchedule = $defaultSchedule->getScheduleID();
        $shortName1 = getPost('shortName1');
        $shortName2 = getPost('shortName2');          
        if ($userid){
            if ($user){
                if (strcmp($scheduleID, $selectedSchedule) == 0){
                    $found = false;
                    if ($shortName1 === $shortName2){
                        $test = validateShortname($shortName1);
                        if ($test === 'OK'){
                            $res = saveSchedule($userid,$user,$defaultSchedule,$scheduleID,$shortName1,$shortName2);
                            if ($res['shortName']){
                                $defaultSchedule->setSaved(true);
                                $defaultSchedule->setShortName($shortName1);
                                $session->schedule = $defaultSchedule->to_json();
                                $session->scheduleObj = serialize($defaultSchedule);
                                
                                //update schedule insider user object
                                foreach($user->getSchedules() as $userschedule){
                                    if (strcmp($scheduleID, $userschedule->getScheduleID()) == 0){
                                        $found = true;
                                        //Should simply replace the same scheduleID with a diff. object
                                        $user->addSchedule($defaultSchedule);
                                        //update the user object in session.
                                        $session->loggedInUser = serialize($user);
                                        $res['userMsg'] = "User object updated.";
                                        break;
                                    }
                                }
                            }
                            $mp->track("save schedule to database", array("saved_schedule_id" => $scheduleID));
                            echo json_encode($res);  
                        }else{
                            $result['errorMessage'] = $test;
                            $session->errorMessage = $result['errorMessage'];
                            echo json_encode($result);
                        }
                    }else{
                        $result['errorMessage'] = "Please make sure the 2 name fields match.";
                        $session->errorMessage = $result['errorMessage'];
                        echo json_encode($result);
                    }
                }else{
                    $result['errorMessage'] = fail("Oops! You forgot to select a schedule to save.","Mismatch between selected schedule and the found schedule ID");
                    $session->errorMessage = $result['errorMessage'];
                    echo json_encode($result);
                }
            }else{
                $result['errorMessage'] = "User object deserialized to null";
                $session->errorMessage = $result['errorMessage'];
                echo json_encode($result);
            }
        }else{
            $result['errorMessage'] = "Missing user information. Please register first to use this feature or login if you're already signed up.";
            $session->errorMessage = $result['errorMessage'];
            echo json_encode($result);
        }
    } elseif (strcmp($action, "updateSchedule") == 0){ 
        $userid = $session->userid;
        $user = unserialize($session->loggedInUser);
        $defaultSchedule = unserialize($session->scheduleObj);
        $scheduleID = getPost('scheduleID');
        $selectedSchedule =  $defaultSchedule->getScheduleID();
        $savedShortName = getPost('savedShortName');     
        if ($userid){
            if ($user){
                if (strcmp($scheduleID, $selectedSchedule) == 0){
                    $found = false;
                    
                    //check that the name is still just alphabets
                    $test = validateShortname($savedShortName);
                    if ($test === 'OK'){
                        $res = updateSchedule($userid,$user,$defaultSchedule,$scheduleID,$savedShortName);
                        //if no error, update schedule in user object
                        if (strlen($res['errorMessage']) == 0){
                            $defaultSchedule->setSaved(true);
                            $defaultSchedule->setShortName($savedShortName);
                            $session->schedule = $defaultSchedule->to_json();
                            $session->scheduleObj = serialize($defaultSchedule);
                            
                            //update schedule insider user object
                            foreach($user->getSchedules() as $userschedule){
                                if (strcmp($scheduleID, $userschedule->getScheduleID()) == 0){
                                    $found = true;
                                    //Should simply replace the same scheduleID with a diff. object
                                    $user->addSchedule($defaultSchedule);
                                    //update the user object in session.
                                    $session->loggedInUser = serialize($user);
                                    $res['userMsg'] = "User object updated.";
                                    break;
                                }
                            }
                            
                            if ($found){
                                $mp->track("update schedule in database", array("saved_schedule_id" => $scheduleID));
                                echo json_encode($res);
                            }else{
                                $result['errorMessage'] = "Problem updating user object.";
                                $session->errorMessage = $result['errorMessage'];
                                echo json_encode($result);
                            }
                        }else{                        
                            $result['errorMessage'] = $res['errorMessage'];
                            $session->errorMessage = $res['errorMessage'];
                            echo json_encode($result);
                        }
                    }else{
                        $result['errorMessage'] = $test;
                        $session->errorMessage = $res['errorMessage'];
                        echo json_encode($result);
                    }
                }else{
                    $result['errorMessage'] = "Mismatch between selected schedule and passed param of scheduleID.";
                    $session->errorMessage = $result['errorMessage'];
                    echo json_encode($result);
                }
            }else{                
                $result['errorMessage'] = "User object deserialized to null";
                $session->errorMessage = $result['errorMessage'];
                echo json_encode($result);
            }
        }else{
            $result['errorMessage'] = "Missing user information. Please register first to use this feature or login if you're already signed up.";
            $session->errorMessage = $result['errorMessage'];
            echo json_encode($result);
        }
    } elseif (strcmp($action, "switchSchedule") == 0) { 
        $scheduleID = getPost('scheduleID');
        $optionChosen = getPost('optionChosen');
        if (!$optionChosen){
            $results['errorMessage'] = "Invalid option selected.";
            $results['optionChosen'] = $optionChosen;
            $session->errorMessage =  $results['errorMessage'];
            echo json_encode($results);
        }elseif ($scheduleID){
            //Get the loggedIn user
            $user = unserialize($session->loggedInUser);
            if ($user){
                //Find the schedule in their collection
                $found = false;
                $foundSchedule = null;
                foreach($user->getSchedules() as $userschedule){
                    if (strcmp($scheduleID, $userschedule->getScheduleID()) == 0){
                        //replace default schedule so user can modify it still
                        $foundSchedule = $userschedule;
                        $session->schedule = $userschedule->to_json();
                        $session->scheduleObj = serialize($userschedule);
                        $session->optionChosen = $optionChosen;
                        $found = true;
                        break;
                    }
                }
                
                if ($found){
                    $result['errorMessage'] = "";
                    $result['optionChosen'] = $optionChosen;
                    if ($foundSchedule){
                        $result['isSaved'] = $foundSchedule->isSaved();
                    }
                    $session->selectedScheduleID = $scheduleID;
                    $session->errorMessage = $result['errorMessage'];
                    
                    //check the semester and campus. Update the semesterSelected and campus variables as needed                    
                    $session->semesterSelected = $foundSchedule->getSemester() . "-" . $foundSchedule->getCampus();
                    
                    $mp->track("switch schedule", array("schedule" => $scheduleID));
                    echo json_encode($result);
                }else{
                    $result['errorMessage'] = "Schedule not found.";
                    $session->errorMessage = $result['errorMessage'];
                    echo json_encode($result);
                }
            }else{
                $result['errorMessage'] = "Please register or login to use this function.";
                $session->errorMessage = $result['errorMessage'];
                echo json_encode($result);
            }
        }else{
            $result['errorMessage'] = "Missing schedule ID selection.";
            $session->errorMessage = $result['errorMessage'];
            echo json_encode($result);
        }
    }else {
        $result['errorMessage'] = "Invalid action to schedule controller.";
        $session->errorMessage = $result['errorMessage'];
        echo json_encode($result);
    }
} else {
    $result['errorMessage'] = "Invalid Server Request Type found.";
    $session->errorMessage = $result['errorMessage'];
    echo json_encode($result);
}
