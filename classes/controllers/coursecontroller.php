<?php
/**
 * Controller for retrieving sections/courses/etc from the database
 *
 * Currently responsible for getting the sections where the user
 * selects a course e.g. CSCI-1302
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
require_once '../models/Course.php';
require_once '../models/Section.php';
require_once '../models/Meeting.php';
require_once '../helpers/CourseHelper.php';
require_once '../helpers/session.php';
require_once '../../../../creds/coursepicker_debug.inc';
require_once '../../../../creds/dhpath.inc';
$session = new Session();
$result = array();
$debug = DEBUGSTATUS;

/**
 * Function to autoload classes needed during serialization/unserialization
 * 
 * @param string $class_name name of the Class being loaded
 *
 * @return void
 */
function __autoload($class_name)
{
    include '../models/'. $class_name . '.php';
}

//Set up debug stuff
//When  not debugging, log to a file!
if (!$debug) {
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", "syslog");
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
 * Function to get the JSON-ified sections.
 * Takes in an array of sections and returns a
 * json_encoded version of the array of Section objects
 * 
 * @param array $sections array of Section objects
 * 
 * @return array
 *
 */
function getSectionJSON($sections)
{
    $result = array();
    foreach ($sections as $section) {
        $result[$section->getCallNumber()] = $section->to_array();
    }

    return json_encode($result);
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

$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
    $action = getPost('action');
    if (strcmp($action, "getSections") == 0) {
        $semester = getPost('semesterSelected');
        $course = getPost('courseEntry');
        if (strlen(trim($semester)) > 0 && strlen(trim($course)) > 0) {
            $courseArray = explode("-", $course);
            $semesterArray = explode("-", $semester);
            if (count($courseArray) == 2 && count($semesterArray) == 2) {
                $session->semesterSelected = $semesterArray[0] . "-"
                                            . $semesterArray[1];
                $session->jsonURL = "assets/json/tp/tp-"
                . $semesterArray[0] . "-" . $semesterArray[1] . ".json";
                $db = new CourseHelper();
                //$term,$coursePrefix,$courseNumber,$campus
                try {
                    $courseSections = $db->getSections($semesterArray[0], $courseArray[0], $courseArray[1], $semesterArray[1]);
                    $session->courseSections = $courseSections;
                    $session->courseSectionsJSON = getSectionJSON($courseSections);
                    $session->errorMessage = "";
                    echo $session->courseSectionsJSON;
                } catch (Exception $e) {
                    $result['errorMessage'] = $e->getMessage();
                    $session->errorMessage = $e->getMessage();
                    echo json_encode($result);
                }
            } else {
                $result['errorMessage'] = "Invalid parameters found.";
                $session->errorMessage = "Invalid parameters found.";
                echo json_encode($result);
            }
        } else {
            $result['errorMessage'] = "Invalid parameters found.";
            $session->errorMessage = "Invalid parameters found.";
            echo json_encode($result);
        }
    } elseif (strcmp($action, "filterSections") == 0) {
        $available = getPost('available');
        $full = getPost('full');
        $cancelled = getPost('Cancelled');
        if (isset($session->courseSections)) {
            //Course sections will contain ALL the filters don't modify
            //use the JSONified version for results to the page
            $courseSections = $session->courseSections;
            $filteredSections = array();
            foreach ($courseSections as $section) {
                $status = $section->getStatus();
                if (($status == "Available" && $available == "true")
                    || ($status == "Full" && $full == "true")
                    || ($status == "Cancelled" && $cancelled == "true")
                ) {
                    $filteredSections[$section->getCallNumber()] = $section;
                }
            }
            $session->courseSectionJSON = getSectionJSON($filteredSections);
            $result['errorMessage'] = "";
            $session->errorMessage = "";
            echo $session->courseSectionJSON;
        } else {
            $result['errorMessage'] = "Please select a course first.";
            $session->errorMessage = "Please select a course first.";
            echo json_encode($result);
        }
    } else {
        $result['errorMessage'] = "No action found.";
        $session->errorMessage = "No action found.";
        echo json_encode($result);
    }
} else {
    $result['errorMessage'] = "Invalid request.";
    $session->errorMessage = "Invalid request.";
    echo json_encode($result);
}
