<?php
//place this file in a directory not accessible over the internet
require_once("../../../../creds/coursepicker.inc");
require_once("../models/Course.php");
require_once("../models/Section.php");
require_once("../models/Meeting.php");

class ScheduleHelper{
	private $getschedule;
	private $saveschedule;
	private $getuserschedules;
	private $truncatetable;
	public $errorMessage;

	/**
	 * Default constructor to access the database
	 * Contains methods to access the ReportsAvailable database
	 *
	 */
	function __construct(){
		try {
			$this->dbconn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			if ($this->dbconn->connect_errno){
				$this->errorMessage = "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
			}else{
				//echo $this->dbconn->host_info . "\n";
				$this->getschedule = $this->dbconn->prepare("select * from schedules where scheduleID = ?");
				$this->getuserschedules = $this->dbconn->prepare("select * from schedules where userID = ?");
				$this->saveschedule = $this->dbconn->prepare("insert into schedules (id,userID,scheduleID,scheduleObject) values(DEFAULT,?,?,?)");
				$this->truncatetable = $this->dbconn->prepare("truncate table schedules");
				$this->errorMessage = "";
			}			
		} catch(Exception $e) {
			echo 'ERROR: ' . $e->getMessage();
			exit();
		}
	}
	
	/**
	 * Truncate the tables
	 */
	private function clearTable(){
		try{
			if (!($this->truncatetable)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($value = $this->truncatetable->execute())){
				echo "Execute failed: (" . $this->truncatetable->errno . ") " . $this->truncatetable->error;
			}else{
				$this->errorMessage = "";
			}
		}catch(Exception $e){
			$this->errorMessage = "Error with clearTable: " . $e->getMessage();
		}
	}
	
	/*
	 * Retrieves the User Schedule 
	 * @param String $scheduleID 
	 * @param String $campus e.g UNIV
	 * @param $term e.g. 201405
	 * @return UserSchedule object
	 */ 
	function getSchedule($scheduleID,$campus,$semester){
		$schedule = null;
		try{
			if (!($this->getschedule)){
				$this->errorMessage = "Prepare for getSchedule failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->getschedule->bind_param("s",$scheduleID))){
				$this->errorMessage = "Binding parameters for getSchedule failed: (" . $this->getschedule->errno . ") " . $this->getschedule->error;
			}else if (!($this->getschedule->execute())){
				$this->errorMessage = "Execute failed for getSchedule : (" . $this->getschedule->errno . ") " . $this->getschedule->error;
			}else if (!(($stored = $this->getschedule->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch for getSchedule failed (STMT): (" . $this->getschedule->errno . ") " . $this->getschedule->error;
			}else if (!($this->getschedule->bind_result($id,$userid,$schedID,$schedObj))){
				$this->errorMessage = "Binding for getSchedule results failed: (" . $this->getschedule->errno . ") " . $this->getschedule->error;
			}else{
				if ($this->getschedule->fetch() && !($this->dbconn->errno)){
					$schedule = $schedObj;
				}
				if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->getschedule->errno . ") " . $this->getschedule->error;
					$this->errorMessage = "Db error.";
				}else{
					$this->errorMessage = "No Offering found.";
				}
			}
			$this->getschedule->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $schedule;
	}	
	
	
	/*
	 * Add a single course to the database
	 */ 
	function saveSchedule($schedule){
		try{
			if (!($this->saveschedule)){
				echo "Prepare failed for saveSchedule: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->saveschedule->bind_param("dss",$schedule->getUserId(),$schedule->getScheduleID(),$schedule))){
				echo "Binding parameters failed for saveSchedule: (" . $this->saveschedule->errno . ") " . $this->saveschedule->error;
			}else if (!($value = $this->saveschedule->execute())){
				echo "Execute failed for saveSchedule: (" . $this->saveschedule->errno . ") " . $this->saveschedule->error;
			}else{
				$this->errorMessage = "";
				return true;
			}
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return false;
	}
		
}

?>
