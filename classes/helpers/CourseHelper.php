<?php
//place this file in a directory not accessible over the internet
require_once("../../../../creds/coursepicker.inc");
require_once("../models/Course.php");
require_once("../models/Section.php");
require_once("../models/Meeting.php");

class CourseHelper{
	private $gettermcourses;
	private $getcoursesections;
	private $getsinglesection;
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
			$this->dbconn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			if ($this->dbconn->connect_errno){
				$this->errorMessage = "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
			}else{
				//echo $this->dbconn->host_info . "\n";
				$this->gettermcourses = $this->dbconn->prepare("select * from courses where term = ? and currentProgram = ?");
				$this->getcoursesections = $this->dbconn->prepare("select * from courses where term = ? and coursePrefix = ? and courseNumber = ? and currentProgram = ? order by available asc");
				$this->addcourse = $this->dbconn->prepare("insert into courses (id,term,callNumber,coursePrefix,courseNumber,courseName,lecturer,available,creditHours,session,days,startTime,endTime,casTaken,casRequired,dasTaken,dasRequired,totalTaken,totalRequired,totalAllowed,
building,room,sch,currentProgram) values(DEFAULT,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
				$this->getsinglesection = $this->dbconn->prepare("select * from courses where term = ? and callNumber = ? and currentProgram = ?");
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
	private function clearTable(){
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


	/*
	* @return array of the days
	* @param String $days e.g. AR, VR, X Y, X   Y, XY XY, XY X
	*/
	private function parseDays($days){
		if (strcmp($days,"DAILY") == 0){
			return array('M','T','W','R','F');
		}else{
			$splitString = str_split($days);
			$result = array();
			foreach ($splitString as $day){
				if (strlen(trim($day)) > 0){
					$result[] = $day;
				}
			}
			return $result;
		}
	}


	/**
	* Get the sections for the course 
	* @param String $term e.g. 201405
	* @param String $coursePrefix e.g. CSCI
	* @param String $courseNumber e.g. 1302
	* @param String $campus e.g. UNIV
	*/
	public function getSections($term,$coursePrefix,$courseNumber,$campus){
		$sections = array();
		try{
			if (!($this->getcoursesections)){
				$this->errorMessage = "Prepare for getSections failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->getcoursesections->bind_param("ssss",$term,$coursePrefix,$courseNumber,$campus))){
				$this->errorMessage = "Binding parameters for getSections failed: (" . $this->getcoursesections->errno . ") " . $this->getcoursesections->error;
			}else if (!($this->getcoursesections->execute())){
				$this->errorMessage = "Execute for getSections failed: (" . $this->getcoursesections->errno . ") " . $this->getcoursesections->error;
			}else if (!(($stored = $this->getcoursesections->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch for getSections failed (STMT): (" . $this->getcoursesections->errno . ") " . $this->getcoursesections->error;
			}else if (!($this->getcoursesections->bind_result($id,$term,$callNumber,$coursePrefix,$courseNumber,$courseName,$lecturer,$available,$creditHours,$session,$days,$startTime,$endTime,$casTaken,$casRequired,$dasTaken,$dasRequired,$totalTaken,$totalRequired,$totalAllowed,$building,$room,$sch,$currentProgram))){
				$this->errorMessage = "Binding for getSections results failed: (" . $this->getcoursesections->errno . ") " . $this->getcoursesections->error;
			}else{
				if ($stored){
					$prevCallNumber = 0;
					while($this->getcoursesections->fetch()){
						if ($callNumber != $prevCallNumber){
							$section = Section::makeSection($courseName, $coursePrefix, $courseNumber, $callNumber, $available, $creditHours, $lecturer,$building,$room,$casTaken,$casRequired,$currentProgram,$term);
							if ($section){
								$sections[$callNumber] = $section;
								$prevCallNumber = $callNumber;
							}else{
								printf("Problem using factory pattern for generating sections in getting all sections.\n");
								exit();
							}
						}
						
						//array of days e.g. M T W R F
						$mtgs = $this->parseDays($days);
						//Meeting objects
						$meetings = array();
						foreach ($mtgs as $mtg){
							//12345, "M", "0215P", "0330P");
							if (strcmp($mtg,'A') == 0 || strcmp($mtg,'V') == 0){
								$meeting = new Meeting($callNumber,$mtg,$startTime,$endTime);									
								$section->addMeeting($meeting);
								break;
							}else{
								$meeting = new Meeting($callNumber,$mtg,$startTime,$endTime);									
								$section->addMeeting($meeting);
							}
						}
					}
					$this->errorMessage = "";
				}else{
					$this->errorMessage = "Error storing results.";
				}
			}
			$this->getcoursesections->free_result();
		}catch(Exception $e){
			$this->errorMessage = "Error with getSections: " . $e->getMessage();
		}
		return $sections;
	}

	/*
	 * Assumes call numbers aren't duplicated across the campuses
	 * 
	 */ 
	function getSingleSection($term,$callNumber,$currentProgram){
		$section = null;
		try{
			if (!($this->getsinglesection)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->getsinglesection->bind_param("sds",$term,$callNumber,$currentProgram))){
				$this->errorMessage = "Binding parameters failed: (" . $this->getsinglesection->errno . ") " . $this->getsinglesection->error;
			}else if (!($this->getsinglesection->execute())){
				$this->errorMessage = "Execute failed: (" . $this->getsinglesection->errno . ") " . $this->getsinglesection->error;
			}else if (!(($stored = $this->getsinglesection->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch for getSingleSection failed (STMT): (" . $this->getsinglesection->errno . ") " . $this->getsinglesection->error;
			}else if (!($this->getsinglesection->bind_result($id,$term,$callNumber,$coursePrefix,$courseNumber,$courseName,$lecturer,$available,$creditHours,$session,$days,$startTime,$endTime,$casTaken,$casRequired,$dasTaken,$dasRequired,$totalTaken,$totalRequired,$totalAllowed,$building,$room,$sch,$currentProgram))){
				$this->errorMessage = "Binding for getSingleSection results failed: (" . $this->getsinglesection->errno . ") " . $this->getsinglesection->error;
			}else{
				if ($stored){
					$prevCallNumber = 0;
					while($this->getsinglesection->fetch()){
						if ($callNumber != $prevCallNumber){
							$section = Section::makeSection($courseName, $coursePrefix, $courseNumber, $callNumber, $available, $creditHours, $lecturer,$building,$room,$casTaken,$casRequired,$currentProgram,$term);
							if ($section){
								$prevCallNumber = $callNumber;
							}else{
								printf("Problem using factory pattern for generating objects in get single section.\n");
								exit();
							}
						}
						
						//array of days e.g. M T W R F
						$mtgs = $this->parseDays($days);
						//Meeting objects
						$meetings = array();
						foreach ($mtgs as $mtg){
							//12345, "M", "0215P", "0330P");
							if (strcmp($mtg,'A') == 0 || strcmp($mtg,'V') == 0){
								$meeting = new Meeting($callNumber,$mtg,$startTime,$endTime);									
								$section->addMeeting($meeting);
								break;
							}else{
								$meeting = new Meeting($callNumber,$mtg,$startTime,$endTime);									
								$section->addMeeting($meeting);
							}
						}
					}
					$this->errorMessage = "";
				}else{
					$this->errorMessage = "Error storing results.";
				}
			}
			$this->getsinglesection->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $section;
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

	/*
	 * Add a single course to the database
	 */ 
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
