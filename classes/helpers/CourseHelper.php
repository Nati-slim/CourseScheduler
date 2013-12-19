<?php
/**
 * Helper for getting courses/sections from the database
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
require_once '../../../../creds/coursepicker.inc';
require_once '../models/Course.php';
require_once '../models/Section.php';
require_once '../models/Meeting.php';
/**
 * Helper for getting courses/sections from the database
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
class CourseHelper
{
    private $gettermcourses;
    private $getcoursesections;
    private $getsinglesection;
    private $addcourse;
    private $truncatetable;
    public $errorMessage;
    private $dbconn;

    /**
     * Default constructor to access the database
     * Contains methods to access the ReportsAvailable database
     *
     */
    public function __construct()
    {
        try {
            $this->dbconn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
            if ($this->dbconn->connect_errno) {
                $this->errorMessage = "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
            } else {
                //echo $this->dbconn->host_info . "\n";
                $this->gettermcourses = $this->dbconn->prepare("select * from courses where term = ? and currentProgram = ?");
                $this->getcoursesections = $this->dbconn->prepare("select * from courses where term = ? and coursePrefix = ? and courseNumber = ? and currentProgram = ? order by available asc");
                $this->addcourse = $this->dbconn->prepare(
                    "insert into courses (id,term,callNumber,coursePrefix,courseNumber,courseName,lecturer,available,creditHours,session,days,startTime,endTime,casTaken,casRequired,dasTaken,dasRequired,totalTaken,totalRequired,totalAllowed,
building,room,sch,currentProgram) values(DEFAULT,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
                );
                $this->getsinglesection = $this->dbconn->prepare("select * from courses where term = ? and callNumber = ? and currentProgram = ?");
                $this->truncateTable = $this->dbconn->prepare("truncate table courses");
                $this->errorMessage = "";
            }
        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    }

    /**
     * Truncate the courses table
     * 
     * @return void
     * 
     */ 
    private function clearTable()
    {
        try {
            if (!($this->truncateTable)) {
                echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($value = $this->truncateTable->execute())) {
                echo "Execute failed: (" . $this->truncateTable->errno . ") " . $this->truncateTable->error;
            } else {
                $this->errorMessage = "";
            }
        } catch (Exception $e) {
            $this->errorMessage = "Error with clearTable: " . $e->getMessage();
        }
    }

    /**
    * Takes in the day field and breaks it down into the actual days
    * 
    * @param String $days e.g. AR, VR, X Y, X   Y, XY XY, XY X
    * 
    * @return array an array of days
    * 
    */
    private function parseDays($days)
    {
        if (strcmp($days, "DAILY") == 0) {
            return array('M','T','W','R','F');
        } else {
            $splitString = str_split($days);
            $result = array();
            foreach ($splitString as $day) {
                if (strlen(trim($day)) > 0) {
                    $result[] = $day;
                }
            }

            return $result;
        }
    }

    /**
    * Get the sections for the course
    *
    * @param String $term         e.g. 201405
    * @param String $coursePrefix e.g. CSCI
    * @param String $courseNumber e.g. 1302
    * @param String $campus       e.g. UNIV
    *
    * @return array list of sections
    *
    */
    public function getSections($term,$coursePrefix,$courseNumber,$campus)
    {
        $sections = array();
        try {
            if (!($this->getcoursesections)) {
                $this->errorMessage = "Prepare for getSections failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($this->getcoursesections->bind_param("ssss", $term, $coursePrefix, $courseNumber, $campus))) {
                $this->errorMessage = "Binding parameters for getSections failed: (" . $this->getcoursesections->errno . ") " . $this->getcoursesections->error;
            } elseif (!($this->getcoursesections->execute())) {
                $this->errorMessage = "Execute for getSections failed: (" . $this->getcoursesections->errno . ") " . $this->getcoursesections->error;
            } elseif (!(($stored = $this->getcoursesections->store_result())) && $this->dbconn->errno) {
                //switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
                //storeresult buffers the fetched data
                $this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                $this->errorMessage .= "Fetch for getSections failed (STMT): (" . $this->getcoursesections->errno . ") " . $this->getcoursesections->error;
            } elseif (!($this->getcoursesections->bind_result($id, $term, $callNumber, $coursePrefix, $courseNumber, $courseName, $lecturer, $available, $creditHours, $session, $days, $startTime, $endTime, $casTaken, $casRequired, $dasTaken, $dasRequired, $totalTaken, $totalRequired, $totalAllowed, $building, $room, $sch, $currentProgram))) {
                $this->errorMessage = "Binding for getSections results failed: (" . $this->getcoursesections->errno . ") " . $this->getcoursesections->error;
            } else {
                if ($stored) {
                    $prevCallNumber = 0;
                    while ($this->getcoursesections->fetch()) {
                        if ($callNumber != $prevCallNumber) {
                            $section = Section::makeSection($courseName, $coursePrefix, $courseNumber, $callNumber, $available, $creditHours, $lecturer, $building, $room, $casTaken, $casRequired, $currentProgram, $term);
                            if ($section) {
                                $sections[$callNumber] = $section;
                                $prevCallNumber = $callNumber;
                            } else {
                                printf("Problem using factory pattern for generating sections in getting all sections.\n");
                                exit();
                            }
                        }

                        //array of days e.g. M T W R F
                        $mtgs = $this->parseDays($days);
                        //Meeting objects
                        $meetings = array();
                        foreach ($mtgs as $mtg) {
                            //12345, "M", "0215P", "0330P");
                            if (strcmp($mtg, 'A') == 0 || strcmp($mtg, 'V') == 0) {
                                $meeting = new Meeting($callNumber, $mtg, $startTime, $endTime);
                                $section->addMeeting($meeting);
                                break;
                            } else {
                                $meeting = new Meeting($callNumber, $mtg, $startTime, $endTime);
                                $section->addMeeting($meeting);
                            }
                        }
                    }
                    $this->errorMessage = "";
                } else {
                    $this->errorMessage = "Error storing results.";
                }
            }
            $this->getcoursesections->free_result();
        } catch (Exception $e) {
            $this->errorMessage = "Error with getSections: " . $e->getMessage();
        }

        return $sections;
    }

    /**
     * Get a single section
     *
     * @param string $term           e.g. 201405
     * @param int    $callNumber     e.g. 1258
     * @param string $currentProgram e.g. UNIV
     *
     * @return Section returns a section object
     *
     */
    public function getSingleSection($term,$callNumber,$currentProgram)
    {
        $section = null;
        try {
            if (!($this->getsinglesection)) {
                $this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($this->getsinglesection->bind_param("sds", $term, $callNumber, $currentProgram))) {
                $this->errorMessage = "Binding parameters failed: (" . $this->getsinglesection->errno . ") " . $this->getsinglesection->error;
            } elseif (!($this->getsinglesection->execute())) {
                $this->errorMessage = "Execute failed: (" . $this->getsinglesection->errno . ") " . $this->getsinglesection->error;
            } elseif (!(($stored = $this->getsinglesection->store_result())) && $this->dbconn->errno) {
                //switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
                //storeresult buffers the fetched data
                $this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                $this->errorMessage .= "Fetch for getSingleSection failed (STMT): (" . $this->getsinglesection->errno . ") " . $this->getsinglesection->error;
            } elseif (!($this->getsinglesection->bind_result($id, $term, $callNumber, $coursePrefix, $courseNumber, $courseName, $lecturer, $available, $creditHours, $session, $days, $startTime, $endTime, $casTaken, $casRequired, $dasTaken, $dasRequired, $totalTaken, $totalRequired, $totalAllowed, $building, $room, $sch, $currentProgram))) {
                $this->errorMessage = "Binding for getSingleSection results failed: (" . $this->getsinglesection->errno . ") " . $this->getsinglesection->error;
            } else {
                if ($stored) {
                    $prevCallNumber = 0;
                    while ($this->getsinglesection->fetch()) {
                        if ($callNumber != $prevCallNumber) {
                            $section = Section::makeSection($courseName, $coursePrefix, $courseNumber, $callNumber, $available, $creditHours, $lecturer, $building, $room, $casTaken, $casRequired, $currentProgram, $term);
                            if ($section) {
                                $prevCallNumber = $callNumber;
                            } else {
                                printf("Problem using factory pattern for generating objects in get single section.\n");
                                exit();
                            }
                        }

                        //array of days e.g. M T W R F
                        $mtgs = $this->parseDays($days);
                        //Meeting objects
                        $meetings = array();
                        foreach ($mtgs as $mtg) {
                            //12345, "M", "0215P", "0330P");
                            if (strcmp($mtg, 'A') == 0 || strcmp($mtg, 'V') == 0) {
                                $meeting = new Meeting($callNumber, $mtg, $startTime, $endTime);
                                $section->addMeeting($meeting);
                                break;
                            } else {
                                $meeting = new Meeting($callNumber, $mtg, $startTime, $endTime);
                                $section->addMeeting($meeting);
                            }
                        }
                    }
                    $this->errorMessage = "";
                } else {
                    $this->errorMessage = "Error storing results.";
                }
            }
            $this->getsinglesection->free_result();
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        return $section;
    }

    /**
     * Get the Course object; Returns null if not present in table.
     *
     * @param int $id the id to identify the Course object in the database
     *
     * @return Course a Course object
     *
     */
    public function getSingleCourse($id)
    {
        $course = null;
        try {
            if (!($this->getcourse)) {
                $this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($this->getcourse->bind_param("i", $id))) {
                $this->errorMessage = "Binding parameters failed: (" . $this->getcourse->errno . ") " . $this->getcourse->error;
            } elseif (!($this->getcourse->execute())) {
                $this->errorMessage = "Execute failed: (" . $this->getsingleoffering->errno . ") " . $this->getcourse->error;
            } elseif (!($this->getcourse->bind_result($id, $term, $callNumber, $coursePrefix, $courseNumber, $courseName, $lecturer, $available, $creditHours, $session, $days, $startTime, $endTime, $casTaken, $casRequired, $dasTaken, $dasRequired, $totalTaken, $totalRequired, $totalAllowed, $building, $room, $sch, $currentProgram))) {
                $this->errorMessage = "Binding results failed: (" . $this->getcourse->errno . ") " . $this->getcourse->error;
            } else {
                if ($this->getcourse->fetch() && !($this->dbconn->errno)) {
                    $offering = new Course($id, $name, $term, $campus, $lastModified);
                }
                if ($this->dbconn->errno) {
                    $this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                    $this->errorMessage .= "Fetch failed (STMT): (" . $this->getsingleoffering->errno . ") " . $this->getsingleoffering->error;
                    $this->errorMessage = "Db error.";
                } else {
                    $this->errorMessage = "No Offering found.";
                }
            }
            $this->getsingleoffering->free_result();
            $this->errorMessage = "";

            return $offering;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        return $course;
    }

    /**
     * Add a course to the database
     *
     * @param array $items an array containing the parameters of the Course object
     *
     * @return int the id identifying the course object or false
     *
     */
    public function addCourse($items)
    {
        try {
            if (!($this->addcourse)) {
                echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($this->addcourse->bind_param("sdsssssdssssdddddddssss", $items['term'], $items['callNumber'], $items['coursePrefix'], $items['courseNumber'], $items['courseName'], $items['lecturer'], $items['available'], $items['creditHours'], $items['session'], $items['days'], $items['startTime'], $items['endTime'], $items['casTaken'], $items['casRequired'], $items['dasTaken'], $items['dasRequired'], $items['totalTaken'], $items['totalRequired'], $items['totalAllowed'], $items['building'], $items['room'], $items['sch'], $items['currentProgram']))) {
                echo "Binding parameters failed: (" . $this->addcourse->errno . ") " . $this->addcourse->error;
            } elseif (!($value = $this->addcourse->execute())) {
                echo "Execute failed: (" . $this->addcourse->errno . ") " . $this->addcourse->error;
            } else {
                $this->errorMessage = "";

                return $this->dbconn->insert_id;
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        return false;
    }
}
