<?php
//place this file in a directory not accessible over the internet
require_once("../helpers/Course.php");

class CourseHelper{
	private $getcourse;
	private $addcourse;
	private $truncatetable;
	public $errorMessage;

	/**
	 * Default constructor to access the database
	 * Contains methods to access the ReportsAvailable database
	 *
	 */
	function __construct(){
		try {
			$this->dbconn = new mysqli("localhost","demo","demo","Picker");
			if ($this->dbconn->connect_errno){
				$this->errorMessage = "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
			}else{
				//echo $this->dbconn->host_info . "\n";
				$this->getcourse = $this->dbconn->prepare("select * from courses where term = ? and currentProgram = ?");
				$this->addcourse = $this->dbconn->prepare("insert into courses (id,term,callNumber,coursePrefix,courseNumber,courseName,lecturer,available,creditHours,session,days,startTime,endTime,casTaken,casRequired,dasTaken,dasRequired,totalTaken,totalRequired,totalAllowed,
building,room,sch,currentProgram) values(DEFAULT,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
				$this->truncateTable = $this->dbconn->prepare("truncate table courses");
				$this->errorMessage = "";
			}			
		} catch(Exception $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
	}

	/**
	 * Truncate the tables
	 */
	function clearTable(){
		try{
			if (!($this->truncateTable)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($value = $this->truncateTable->execute())){
				echo "Execute failed: (" . $this->truncateTable->errno . ") " . $this->truncateTable->error;
			}else{
				$this->errorMessage = "";
			}
		}catch(Exception $e){
			$this->errorMessage = "Error with clearTable: " . $e->getMessage();
		}
	}

	/**
	 * Get the Course object; Returns null if not present in table.
	 * @param int $id to identify the Course object in the database
	 * @return Course object
	 */
	function getSingleCourse($id){
		$course = null;
		try{
			if (!($this->getcourse)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->getcourse->bind_param("i",$id))){
				$this->errorMessage = "Binding parameters failed: (" . $this->getcourse->errno . ") " . $this->getcourse->error;
			}else if (!($this->getcourse->execute())){
				$this->errorMessage = "Execute failed: (" . $this->getsingleoffering->errno . ") " . $this->getcourse->error;
			}else if (!($this->getcourse->bind_result($id,$term,$callNumber,$coursePrefix,$courseNumber,$courseName,$lecturer,$available,$creditHours,$session,$days,$startTime,$endTime,$casTaken,$casRequired,$dasTaken,$dasRequired,$totalTaken,$totalRequired,$totalAllowed,$building,$room,$sch,$currentProgram))){
				$this->errorMessage = "Binding results failed: (" . $this->getcourse->errno . ") " . $this->getcourse->error;
			}else{
				if ($this->getcourse->fetch() && !($this->dbconn->errno)){
					$offering = new Course($id,$name,$term,$campus,$lastModified);
				}
				if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->getsingleoffering->errno . ") " . $this->getsingleoffering->error;
					$this->errorMessage = "Db error.";
				}else{
					$this->errorMessage = "No Offering found.";
				}
			}
			$this->getsingleoffering->free_result();
			$this->errorMessage = "";
			return $offering;
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $course;
	}


	function addCourse($items){
		try{
			if (!($this->addcourse)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->addcourse->bind_param("sdsssssdssssdddddddssss",$items['term'],$items['callNumber'],$items['coursePrefix'],$items['courseNumber'],$items['courseName'],$items['lecturer'],$items['available'],$items['creditHours'],$items['session'],$items['days'],$items['startTime'],$items['endTime'],$items['casTaken'],$items['casRequired'],$items['dasTaken'],$items['dasRequired'],$items['totalTaken'],$items['totalRequired'],$items['totalAllowed'],$items['building'],$items['room'],$items['sch'],$items['currentProgram']))){
				echo "Binding parameters failed: (" . $this->addcourse->errno . ") " . $this->addcourse->error;
			}else if (!($value = $this->addcourse->execute())){
				echo "Execute failed: (" . $this->addcourse->errno . ") " . $this->addcourse->error;
			}else{
				$this->errorMessage = "";
				return true;
			}
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return false;
	}

	/*function getOfferingByTerm($value){
		$result = array();
		try{
			if (!($this->getofferingbyterm)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->getofferingbyterm->bind_param("s",$value))){
				$this->errorMessage = "Binding parameters failed: (" . $this->getofferingbyterm->errno . ") " . $this->getofferingbyterm->error;
			}else if (!($this->getofferingbyterm->execute())){
				$this->errorMessage = "Execute failed: (" . $this->getofferingbyterm->errno . ") " . $this->getofferingbyterm->error;
			}else if (!(($stored = $this->getofferingbyterm->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch failed (STMT): (" . $this->getofferingbyterm->errno . ") " . $this->getofferingbyterm->error;
			}else if (!($this->getofferingbyterm->bind_result($id,$name,$term,$campus,$dateModified))){
				$this->errorMessage = "Binding results failed: (" . $this->getofferingbyterm->errno . ") " . $this->getofferingbyterm->error;
			}else{
				if ($stored){
					while($this->getofferingbyterm->fetch()){
						$offering = new Offering($id,$name,$term,$campus,$dateModified);
						$result[] = $offering;
					}
				}else{
					$this->errorMessage = "Error storing results.";
				}
			}
			$this->getofferingbyterm->free_result();
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $result;
	}*/
}
?>
