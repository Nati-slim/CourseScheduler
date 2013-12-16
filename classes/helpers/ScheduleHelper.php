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
				$this->getschedule = $this->dbconn->prepare("select * from schedules where term = ? and currentProgram = ?");
				$this->getuserschedules = $this->dbconn->prepare("select * from schedules where userid = ?");
				$this->saveschedule = $this->dbconn->prepare("insert into schedules (id,userID,scheduleID,scheduleObject) values(DEFAULT,?,?,?)");
				$this->truncatetable = $this->dbconn->prepare("truncate table schedules");
				$this->errorMessage = "";
			}			
		} catch(Exception $e) {
			echo 'ERROR: ' . $e->getMessage();
			exit();
		}
	}	
}

?>
