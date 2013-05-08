<?php
//place this file in a directory not accessible over the internet
require_once("../../../../creds/credentials.inc");
require_once("Course.php");
require_once("Section.php");
require_once("Meeting.php");
class DBHelper{
	private $listcourses;
	private $singlecourse;
	private $listsections;
	private $singlesection;
	private $saveschedule;
	private $lastsavedversion;
	private $retrieveschedules;
	private $retrieveshortname;
	private $truncateTable;
	private $errorMessage;

	/**
	 * Default constructor to access the database
	 * Contains methods to access the Requirements and StaticReports database
	 *
	 */
	function __construct(){
		try {
			$this->dbconn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			if ($this->dbconn->connect_errno){
				echo "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
			}else{
				//echo $this->dbconn->host_info . "\n";
				$this->singlecourse = $this->dbconn->prepare("SELECT * from Requirements where requirementId = ? and coursePrefix = ? and courseNumber = ?");
				$this->singlesection = $this->dbconn->prepare("SELECT * from StaticReport where callNumber = ?");
				$this->listcourses = $this->dbconn->prepare("SELECT * from Requirements where requirementId = ?");
				$this->listsections = $this->dbconn->prepare("SELECT * from StaticReport where coursePrefix = ? and courseNumber = ?");
				$this->saveschedule = $this->dbconn->prepare("INSERT into Schedules (version, userid, scheduleObject) values(?,?,?)");
				$this->retrieveschedules = $this->dbconn->prepare("SELECT * from Schedules where version = ? and userid = ?");
				$this->lastsavedversion = $this->dbconn->prepare("SELECT MAX(version) from Schedules where userid = ?");
				$this->retrieveshortname = $this->dbconn->prepare("SELECT * from Schedules where version = ? and userid = ?");
				$this->truncateTable = $this->dbconn->prepare("truncate table Schedules");
			}
			$this->errorMessage = "";
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
			$this->errorMessage = "Error with clearTable.";
		}
	}

	/**
	 * Save the schedule Object and the userid to the database
	 * for access later
	 * @param String $id should be the userid from when schedule was created
	 * @param bytes $object serialized UserSchedule object
	 * @return boolean
	 */
	function saveSchedule($version, $id, $object){
		try{
			if (!($this->saveschedule)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->saveschedule->bind_param("dss",$version, $id,$object))){
				echo "Binding parameters failed: (" . $this->saveschedule->errno . ") " . $this->saveschedule->error;
			}else if (!($value = $this->saveschedule->execute())){
				echo "Execute failed: (" . $this->saveschedule->errno . ") " . $this->saveschedule->error;
			}else{
				return $value;
			}
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return false;
	}

	/**
	 * Retrieve the schedule Object and the userid to the database
	 * for access later
	 * @param integer $vId version id which indicates which version of the schedule
	 * @param String $uId userid to identify in the database
	 * @return UserSchedule object
	 */
	function retrieveSchedule($vId, $uId){
		$result = null;
		try{
			if (!($this->retrieveschedules)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->retrieveschedules->bind_param("ds",$vId,$uId))){
				$this->errorMessage = "Binding parameters failed: (" . $this->retrieveschedules->errno . ") " . $this->retrieveschedules->error;
			}else if (!($this->retrieveschedules->execute())){
				$this->errorMessage = "Execute failed: (" . $this->retrieveschedules->errno . ") " . $this->retrieveschedules->error;
			}else if (!(($stored = $this->retrieveschedules->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch failed (STMT): (" . $this->retrieveschedules->errno . ") " . $this->retrieveschedules->error;
			}else if (!($this->retrieveschedules->bind_result($id,$version,$userid,$scheduleObject,$shortName))){
				$this->errorMessage = "Binding results failed: (" . $this->retrieveschedules->errno . ") " . $this->retrieveschedules->error;
			}else{
				if ($stored){
					while($this->retrieveschedules->fetch()){
						$result = $scheduleObject;
					}
					if (!$userid){
						$result = null;
					}
				}else{
					$this->errorMessage = "Error storing results of getShortName.";
				}
			}
			$this->retrieveschedules->free_result();
			return $result;
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $result;
	}

	/**
	 * Retrieve the shortname for the user's schedule to be used for sharing
	 * the schedule
	 * @param integer $vId version id which indicates which version of the schedule
	 * @param String $uId userid to identify in the database
	 * @return String value in shortName field
	 */
	function getShortName($vId,$uId){
		$result = null;
		try{
			if (!($this->retrieveshortname)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->retrieveshortname->bind_param("ds",$vId,$uId))){
				$this->errorMessage = "Binding parameters failed: (" . $this->retrieveshortname->errno . ") " . $this->retrieveshortname->error;
			}else if (!($this->retrieveshortname->execute())){
				$this->errorMessage = "Execute failed: (" . $this->retrieveshortname->errno . ") " . $this->retrieveshortname->error;
			}else if (!(($stored = $this->retrieveshortname->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch failed (STMT): (" . $this->retrieveshortname->errno . ") " . $this->retrieveshortname->error;
			}else if (!($this->retrieveshortname->bind_result($id,$version,$userid,$scheduleObject,$shortName))){
				$this->errorMessage = "Binding results failed: (" . $this->retrieveshortname->errno . ") " . $this->retrieveshortname->error;
			}else{
				if ($stored){
					while($this->retrieveshortname->fetch()){
						$result = $shortName;
					}
					if (!$shortName){
						$result = null;
					}
				}else{
					$this->errorMessage = "Error storing results of getShortName.";
				}
			}
			$this->retrieveshortname->free_result();
			return $result;
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return null;
	}

	/**
	 * Return the last saved version belonging
	 * to the provided userid; Returns -1 if new version is already in table so increase by 1.
	 * @param String $userid UserSchedule object userid
	 * @return integer $version last version saved to db
	 */
	function findLastSavedVersion($uId){
		$result = 0;
		try{
			if (!($this->lastsavedversion)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->lastsavedversion->bind_param("s",$uId))){
				$this->errorMessage = "Binding parameters failed: (" . $this->lastsavedversion->errno . ") " . $this->lastsavedversion->error;
			}else if (!($this->lastsavedversion->execute())){
				$this->errorMessage = "Execute failed: (" . $this->lastsavedversion->errno . ") " . $this->lastsavedversion->error;
			}else if (!(($stored = $this->lastsavedversion->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage =  "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch failed (STMT): (" . $this->lastsavedversion->errno . ") " . $this->lastsavedversion->error;
			}else if (!($this->lastsavedversion->bind_result($version))){
				$this->errorMessage = "Binding results failed: (" . $this->lastsavedversion->errno . ") " . $this->lastsavedversion->error;
			}else{
				if ($stored){
					while($this->lastsavedversion->fetch()){
						$result = $version;
					}
					if (!$version){
						$result = 0;
					}
				}else{
					$this->errorMessage = "Error storing results.";
				}
			}
			$this->lastsavedversion->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $result;
	}

	/**
	 * Retrieve a list of 'empty' course objects
	 * to minimize the database load.
	 * @return array() Course objects
	 * @param integer $id requirement id
	 */
	function getShellCourses($id){
		try{
			$courseListing = array();
			if (!($this->listcourses)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->listcourses->bind_param("i",$id))){
				$this->errorMessage = "Binding parameters failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->execute())){
				$this->errorMessage = "Execute failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!(($stored = $this->listcourses->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch failed (STMT): (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->bind_result($requirementId,$coursePrefix,$courseNumber))){
				$this->errorMessage = "Binding results failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else{
				if ($stored){
					while($this->listcourses->fetch()){
						$course = new Course($coursePrefix,$courseNumber);
						$courseListing[] = $course;
					}
				}else{
					$this->errorMessage = "Error storing results.";
				}
			}
			$this->listcourses->free_result();
			return $courseListing;
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		$this->listcourses->close();
		return null;
	}

	/**Retrieve a record from the table
	 * e.g. retrieve a single CSCI 1302 course
	 * @param int $id requirement Id
	 * @param String $prefix e.g. CSCI
	 * @param String $number e.g. 1302
	 * @return Course object
	 */
	function getCourse($id,$prefix,$number){
		try{
			$course = null;
			if (!($this->singlecourse)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->singlecourse->bind_param("iss",$id,$prefix,$number))){
				$this->errorMessage = "Binding parameters failed: (" . $this->singlecourse->errno . ") " . $this->singlecourse->error;
			}else if (!($this->singlecourse->execute())){
				$this->errorMessage = "Execute failed: (" . $this->singlecourse->errno . ") " . $this->singlecourse->error;
			}else if (!($this->singlecourse->store_result()) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch failed (STMT): (" . $this->singlecourse->errno . ") " . $this->singlecourse->error;
			}else if (!($this->singlecourse->bind_result($requirementId,$coursePrefix,$courseNumber))){
				$this->errorMessage = "Binding results failed: (" . $this->singlecourse->errno . ") " . $this->singlecourse->error;
			}else if ($requirementId){
				//YAY!
				$course = new Course($coursePrefix,$courseNumber);
				$sections = $this->getSections($coursePrefix,$courseNumber);
				if ($sections != null){
					foreach($sections as $section){
						if (!($course->addSection($section))){
							$this->errorMessage = "Problem adding section to course object - " . $course->getErrorMessage() . "\n";
						}
					}
				}
				return $course;
			}
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return null;
	}

	/**Return the sections for the coursePrefix & courseNumber
	 * with the class meetings stuffed inside.
	 * Returns null if no sections are found.
	 * @return array() of sections
	 */
	function getSections($prefix,$number){
		try{
			$sectionListing = array();
			if (!($this->listsections)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->listsections->bind_param("ss",$prefix,$number))){
				$this->errorMessage = "Binding parameters failed: (" . $this->listsections->errno . ") " . $this->listsections->error;
			}else if (!($this->listsections->execute())){
				$this->errorMessage = "Execute failed: (" . $this->listsections->errno . ") " . $this->listsections->error;
			}else if (!($this->listsections->bind_result($term,$callNumber,$coursePrefix,$courseNumber,$courseName,$lecturer,$available,$credithours,$session,$days,$startTime,$endTime,$castaken,$casreq,$dastaken,$dasreq,$totaltaken,$totalreq,$totalallowed,$building,$room,$sch,$currprog))){
				$this->errorMessage = "Binding results failed: (" . $this->listsections->errno . ") " . $this->listsections->error;
			}else{
				$cNo = 0;
				$section = null;
				while ($this->listsections->fetch() && !($this->dbconn->errno)){
					//Code to merge the class meetings into one section
					if ($callNumber != $cNo){
						$section = new Section(filter_var($courseName,FILTER_SANITIZE_SPECIAL_CHARS),$coursePrefix,$courseNumber,$callNumber,$available,$credithours,trim(filter_var($lecturer,FILTER_SANITIZE_SPECIAL_CHARS)));
						$section->setBuildingNumber($building);
						$section->setRoomNumber($room);
						$sectionListing[] = $section;
						$cNo = $callNumber;
					}
					//Avoid the funky days
					if (strcmp($days,"AR") != 0 && strcmp($days,"VR") != 0){
						$dys = explode(" ",$days);
						foreach ($dys as $singleday){
							//Ensure no empty day strings
							if (strlen($singleday) > 0){
								$mtg = new Meeting($callNumber,$singleday,$startTime,$endTime);
								$section->addMeeting($mtg);
							}
						}
						$this->errorMessage = "";
					}else{
						$this->errorMessage = "Found one of those funky VR or AR thingies.";
					}
				}
				if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->listsections->errno . ") " . $this->listsections->error;
				}
			}
			return $sectionListing;
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return null;
	}

	/**
	 * Retrieve a single record (Section object) from the StaticReport
	 * table. Returns null if no such record exists.
	 * @return Section object
	 */
	function getSingleSection($callNumber){
		try{
			$section = null;
			if (!($this->singlesection)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->singlesection->bind_param("i",$callNumber))){
				$this->errorMessage = "Binding parameters failed: (" . $this->singlesection->errno . ") " . $this->singlesection->error;
			}else if (!($this->singlesection->execute())){
				$this->errorMessage = "Execute failed: (" . $this->singlesection->errno . ") " . $this->singlesection->error;
			}else if (!($this->singlesection->execute())){
				$this->errorMessage = "Execute failed: (" . $this->singlesection->errno . ") " . $this->singlesection->error;
			}else if (!($this->singlesection->bind_result($term,$callNumber,$coursePrefix,$courseNumber,$courseName,$lecturer,$available,$credithours,$session,$days,$startTime,$endTime,$castaken,$casreq,$dastaken,$dasreq,$totaltaken,$totalreq,$totalallowed,$building,$room,$sch,$currprog))){
				$this->errorMessage = "Binding results failed: (" . $this->singlesection->errno . ") " . $this->singlesection->error;
			}else{
				$cNo = 0;
				while ($this->singlesection->fetch() && !($this->dbconn->errno)){
					//Code to merge the class meetings into one section
					if ($callNumber != $cNo){
						$section = new Section(trim(filter_var($courseName,FILTER_SANITIZE_SPECIAL_CHARS)),$coursePrefix,$courseNumber,$callNumber,$available,$credithours,trim(filter_var($lecturer,FILTER_SANITIZE_SPECIAL_CHARS)));
						$section->setBuildingNumber($building);
						$section->setRoomNumber($room);
						$cNo = $callNumber;
					}
					//Avoid the funky days
					if (strcmp($days,"AR") != 0 && strcmp($days,"VR") != 0){
						$dys = explode(" ",$days);
						foreach ($dys as $singleday){
							//Ensure no empty day strings
							if (strlen($singleday) > 0){
								$mtg = new Meeting($callNumber,$singleday,$startTime,$endTime);
								$section->addMeeting($mtg);
							}
						}
						$this->errorMessage = "";
					}else{
						$this->errorMessage = "Found one of those funky VR or AR thingies.";
					}
				}
				if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->singlesection->errno . ") " . $this->singlesection->error;
					$this->errorMessage = "Db error.";
				}else{
					$this->errorMessage = "No section found.";
				}
			}
			return $section;
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return null;
	}

	/**
	 * Returns the error Message string.
	 * @return String $errorMessage
	 */
	public function getErrorMessage(){
		return $this->errorMessage;
	}

	/**
	 * Sets the error Message string.
	 * @param String $err Set the error message
	 * @return void
	 */
	public function setErrorMessage($err){
		$this->errorMessage = $err;
	}
}
?>
