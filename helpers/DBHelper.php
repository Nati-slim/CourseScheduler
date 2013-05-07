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
			}
			$this->errorMessage = "";
		} catch(Exception $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
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
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->listcourses->bind_param("i",$id))){
				echo "Binding parameters failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->execute())){
				echo "Execute failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!(($stored = $this->listcourses->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				echo "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				echo "Fetch failed (STMT): (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->bind_result($requirementId,$coursePrefix,$courseNumber))){
				echo "Binding results failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
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
			echo $e->getMessage();
		}
		$this->listcourses->close();
		return null;
	}

	/**Retrieve a list of records that fulfill a particular requirement
	 * @param int $id requirement Id
	 * @return array() of course objects
	 */
	function getCourses($id){
		try{
			$courseListing = null;
			$resultset = null;
			if (!($this->listcourses)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->listcourses->bind_param("i",$id))){
				echo "Binding parameters failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->execute())){
				echo "Execute failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!(($stored = $this->listcourses->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				echo "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				echo "Fetch failed (STMT): (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->bind_result($requirementId,$coursePrefix,$courseNumber))){
				echo "Binding results failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else{
				if ($stored){
					$recordcount = $this->listcourses->num_rows;
					$fieldcount = $this->listcourses->field_count;
					while($this->listcourses->fetch()){
						$course = new Course($coursePrefix,$courseNumber);
						$sections = $this->getSections($coursePrefix,$courseNumber);
						if ($sections != null){
							foreach($sections as $section){
								if (!($course->addSection($section))){
									echo "Problem adding section to course object - " . $course->getErrorMessage() . "\n";
								}
							}
						}
						$courseListing[] = $course;
					}
				}else{
					$this->errorMessage = "Error storing results.";
				}
			}
			$this->listcourses->free_result();
			return $courseListing;
		}catch(Exception $e){
			echo $e->getMessage();
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
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->singlecourse->bind_param("iss",$id,$prefix,$number))){
				echo "Binding parameters failed: (" . $this->singlecourse->errno . ") " . $this->singlecourse->error;
			}else if (!($this->singlecourse->execute())){
				echo "Execute failed: (" . $this->singlecourse->errno . ") " . $this->singlecourse->error;
			}else if (!($this->singlecourse->store_result()) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				echo "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				echo "Fetch failed (STMT): (" . $this->singlecourse->errno . ") " . $this->singlecourse->error;
			}else if (!($this->singlecourse->bind_result($requirementId,$coursePrefix,$courseNumber))){
				echo "Binding results failed: (" . $this->singlecourse->errno . ") " . $this->singlecourse->error;
			}else if ($requirementId){
				//YAY!
				$course = new Course($coursePrefix,$courseNumber);
				$sections = $this->getSections($coursePrefix,$courseNumber);
				if ($sections != null){
					foreach($sections as $section){
						if (!($course->addSection($section))){
							echo "Problem adding section to course object - " . $course->getErrorMessage() . "\n";
						}
					}
				}
				return $course;
			}
		}catch(Exception $e){
			echo $e->getMessage();
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
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->listsections->bind_param("ss",$prefix,$number))){
				echo "Binding parameters failed: (" . $this->listsections->errno . ") " . $this->listsections->error;
			}else if (!($this->listsections->execute())){
				echo "Execute failed: (" . $this->listsections->errno . ") " . $this->listsections->error;
			}else if (!($this->listsections->bind_result($term,$callNumber,$coursePrefix,$courseNumber,$courseName,$lecturer,$available,$credithours,$session,$days,$startTime,$endTime,$castaken,$casreq,$dastaken,$dasreq,$totaltaken,$totalreq,$totalallowed,$building,$room,$sch,$currprog))){
				echo "Binding results failed: (" . $this->listsections->errno . ") " . $this->listsections->error;
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
						echo "No match found.\n";
						$this->errorMessage = "Found one of those funky VR or AR thingies.";
					}
				}
				if ($this->dbconn->errno){
					echo "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					echo "Fetch failed (STMT): (" . $this->listsections->errno . ") " . $this->listsections->error;
				}
			}
			return $sectionListing;
		}catch(Exception $e){
			echo "getSections error: " . $e->getMessage();
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
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->singlesection->bind_param("i",$callNumber))){
				echo "Binding parameters failed: (" . $this->singlesection->errno . ") " . $this->singlesection->error;
			}else if (!($this->singlesection->execute())){
				echo "Execute failed: (" . $this->singlesection->errno . ") " . $this->singlesection->error;
			}else if (!($this->singlesection->execute())){
				echo "Execute failed: (" . $this->singlesection->errno . ") " . $this->singlesection->error;
			}else if (!($this->singlesection->bind_result($term,$callNumber,$coursePrefix,$courseNumber,$courseName,$lecturer,$available,$credithours,$session,$days,$startTime,$endTime,$castaken,$casreq,$dastaken,$dasreq,$totaltaken,$totalreq,$totalallowed,$building,$room,$sch,$currprog))){
				echo "Binding results failed: (" . $this->singlesection->errno . ") " . $this->singlesection->error;
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
						echo "No match found.\n";
						$this->errorMessage = "Found one of those funky VR or AR thingies.";
					}
				}
				if ($this->dbconn->errno){
					echo "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					echo "Fetch failed (STMT): (" . $this->singlesection->errno . ") " . $this->singlesection->error;
					$this->errorMessage = "Db error.";
				}else{
					$this->errorMessage = "No section found.";
				}
			}
			return $section;
		}catch(Exception $e){
			echo $e->getMessage();
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
