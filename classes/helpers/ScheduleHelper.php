<?php
//place this file in a directory not accessible over the internet
require_once dirname(__FILE__) . '/../../../../creds/coursepicker.inc';
require_once dirname(__FILE__) . '/../models/Course.php';
require_once dirname(__FILE__) . '/../models/Section.php';
require_once dirname(__FILE__) . '/../models/Meeting.php';

class ScheduleHelper
{
    private $getschedule;
    private $saveschedule;
    private $updateschedule;
    private $getuserschedules;
    private $getsingleschedule;
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
            $this->dbconn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
            if ($this->dbconn->connect_errno) {
                $this->errorMessage = "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
            } else {
                //echo $this->dbconn->host_info . "\n";
                $this->getschedule = $this->dbconn->prepare("select * from coursepicker_schedules where scheduleID = ?");
                $this->getsingleschedule = $this->dbconn->prepare("select * from coursepicker_schedules where scheduleID = ?");
                $this->getuserschedules = $this->dbconn->prepare("select * from coursepicker_schedules where userID = ?");
                $this->saveschedule = $this->dbconn->prepare("insert into coursepicker_schedules (id,userID,scheduleID,scheduleObject,shortname,dateAdded) values(DEFAULT,?,?,?,?,?)");
                $this->updateschedule = $this->dbconn->prepare("update coursepicker_schedules set shortName = ?, scheduleObject = ? where scheduleID = ?");
                $this->truncatetable = $this->dbconn->prepare("truncate table coursepicker_schedules");
                $this->errorMessage = "";
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function getSingleSchedule($scheduleID){
        $results = array();
        try {
            if (!($this->getsingleschedule)) {
                $this->errorMessage = "Prepare for getsingleschedule failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($this->getsingleschedule->bind_param("s",$scheduleID))) {
                $this->errorMessage = "Binding parameters for getsingleschedule failed: (" . $this->getsingleschedule->errno . ") " . $this->getsingleschedule->error;
            } elseif (!($this->getsingleschedule->execute())) {
                $this->errorMessage = "Execute failed for getsingleschedule : (" . $this->getsingleschedule->errno . ") " . $this->getsingleschedule->error;
            } elseif (!(($stored = $this->getsingleschedule->store_result())) && $this->dbconn->errno) {
                //switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
                //storeresult buffers the fetched data
                $this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                $this->errorMessage .= "Fetch for getsingleschedule failed (STMT): (" . $this->getsingleschedule->errno . ") " . $this->getsingleschedule->error;
            } elseif (!($this->getsingleschedule->bind_result($id,$userid,$schedID,$schedObj,$shortname,$dateAdded))) {
                $this->errorMessage = "Binding for getsingleschedule results failed: (" . $this->getsingleschedule->errno . ") " . $this->getsingleschedule->error;
            } else {
                if ($this->getsingleschedule->fetch() && !($this->dbconn->errno)) {
                    $results['shortname'] = $shortname;
                    $results['scheduleObj'] = $schedObj;
                }
                if ($this->dbconn->errno) {
                    $this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                    $this->errorMessage .= "Fetch failed (STMT): (" . $this->getsingleschedule->errno . ") " . $this->getsingleschedule->error;
                } else {
                    $this->errorMessage = "No Offering found.";
                }
            }
            $this->getschedule->free_result();
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        return $results;
    }
    
    /**
     * Truncate the tables
     */
    private function clearTable()
    {
        try {
            if (!($this->truncatetable)) {
                $this->errorMessage =  "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($value = $this->truncatetable->execute())) {
                $this->errorMessage =  "Execute failed: (" . $this->truncatetable->errno . ") " . $this->truncatetable->error;
            } else {
                $this->errorMessage = "";
            }
        } catch (Exception $e) {
            $this->errorMessage = "Error with clearTable: " . $e->getMessage();
        }
    }

    /*
     * Retrieves the User Schedule
     * @param String $scheduleID
     * @param String $campus     e.g UNIV
     * @param $term e.g. 201405
     * @return UserSchedule object
     */
    public function getSchedule($scheduleID,$campus,$semester)
    {
        $schedule = null;
        try {
            if (!($this->getschedule)) {
                $this->errorMessage = "Prepare for getSchedule failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($this->getschedule->bind_param("s",$scheduleID))) {
                $this->errorMessage = "Binding parameters for getSchedule failed: (" . $this->getschedule->errno . ") " . $this->getschedule->error;
            } elseif (!($this->getschedule->execute())) {
                $this->errorMessage = "Execute failed for getSchedule : (" . $this->getschedule->errno . ") " . $this->getschedule->error;
            } elseif (!(($stored = $this->getschedule->store_result())) && $this->dbconn->errno) {
                //switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
                //storeresult buffers the fetched data
                $this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                $this->errorMessage .= "Fetch for getSchedule failed (STMT): (" . $this->getschedule->errno . ") " . $this->getschedule->error;
            } elseif (!($this->getschedule->bind_result($id,$userid,$schedID,$schedObj))) {
                $this->errorMessage = "Binding for getSchedule results failed: (" . $this->getschedule->errno . ") " . $this->getschedule->error;
            } else {
                if ($this->getschedule->fetch() && !($this->dbconn->errno)) {
                    $schedule = $schedObj;
                }
                if ($this->dbconn->errno) {
                    $this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                    $this->errorMessage .= "Fetch failed (STMT): (" . $this->getschedule->errno . ") " . $this->getschedule->error;
                }
            }
            $this->getschedule->free_result();
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        return $schedule;
    }

    /**
     * Function to save a User's schedule to the database
     * The schedule is saved as a serialized UserSchedule object.
     * 
     * @param UserSchedule $schedule The User schedule object which must have its shortname value set
     * 
     * @return int id of the newly inserted schedule or false if it failed to insert
     * 
     */ 
    public function saveSchedule($schedule)
    {
        try {
            if (!($this->saveschedule)) {
                $this->errorMessage =  "Prepare failed for saveSchedule: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($this->saveschedule->bind_param("sssss",$schedule->getUserId(),$schedule->getScheduleID(),serialize($schedule),$schedule->getShortName(),$schedule->getDateAdded()))) {
                $this->errorMessage =  "Binding parameters failed for saveSchedule: (" . $this->saveschedule->errno . ") " . $this->saveschedule->error;
            } elseif (!($value = $this->saveschedule->execute())) {
                $this->errorMessage =  "Execute failed for saveSchedule: (" . $this->saveschedule->errno . ") " . $this->saveschedule->error;
            } else {
                $this->errorMessage = "";

                return $this->dbconn->insert_id;
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        return false;
    }

    /**
     * Function to save a User's schedule to the database
     * The schedule is saved as a serialized UserSchedule object.
     * 
     * @param UserSchedule $schedule The User schedule object which must have its shortname value set
     * 
     * @return bool true if successful and false otherwise
     * 
     */ 
    public function updateSchedule($schedule)
    {
        try {
            if (!($this->updateschedule)) {
                $this->errorMessage = "Prepare failed for updateSchedule: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            } elseif (!($this->updateschedule->bind_param("sss",$schedule->getShortName(),serialize($schedule),$schedule->getScheduleID()))) {
                $this->errorMessage = "Binding parameters failed for updateSchedule: (" . $this->updateschedule->errno . ") " . $this->updateschedule->error;
            } elseif (!($value = $this->updateschedule->execute())) {
                $this->errorMessage = "Execute failed for updateSchedule: (" . $this->updateschedule->errno . ") " . $this->updateschedule->error;
            } else {
                $this->errorMessage = "";
                return true;
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }

        return false;
    }
    
}
