<?php
//place this file in a directory not accessible over the internet
require_once("../../credentials.inc");
require_once("Course.php");
require_once("Section.php");
require_once("Meeting.php");
class DBHelper{
	private $listcourses;
	private $listsections;

	//Default constructor
	function __construct(){
		try {
			$this->dbconn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			if ($this->dbconn->connect_errno){
				echo "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
			}else{
				//echo $this->dbconn->host_info . "\n";
				$this->listcourses = $this->dbconn->prepare("SELECT * from Requirements where requirementId = ? and coursePrefix = ? and courseNumber = ?");
				$this->listsections = $this->dbconn->prepare("SELECT * from StaticReport where coursePrefix = ? and courseNumber = ?");
			}
		} catch(PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
	}

	//Retrieve a record from the table
	//and returns a course object
	function getCourses($id,$prefix,$number){
		try{
			if (!($this->listcourses)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->listcourses->bind_param("iss",$id,$prefix,$number))){
				echo "Binding parameters failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->execute())){
				echo "Execute failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->bind_result($requirementId,$coursePrefix,$courseNumber))){
				echo "Binding results failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->fetch()) && $this->dbconn->errno){
				echo "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				echo "Fetch failed (STMT): (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if ($requirementId){
				//YAY!
				$course = new Course($coursePrefix,$courseNumber);
				$this->listcourses->close();
			}
			return $course;
		}catch(Exception $e){
			echo $e->getMessage();
		}
		return null;
	}

	//Return the sections for the coursePrefix & courseNumber
	//with the class meetings stuffed inside.
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
						$section = new Section($courseName,$coursePrefix,$courseNumber,$callNumber,$available,$credithours,$lecturer);
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
					}else{
						echo "No match found.\n";
					}
				}
				if ($this->dbconn->errno){
					echo "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					echo "Fetch failed (STMT): (" . $this->listsections->errno . ") " . $this->listsections->error;
				}
			}
			$this->listsections->close();
			return $sectionListing;
		}catch(Exception $e){
			echo $e->getMessage();
		}
		return null;
	}
}
?>
