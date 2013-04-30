<?php
/**
 * Section object that contains the following information:
 * courseNumber e.g. 1302
 * coursePrefix e.g. CSCI
 * courseName e.g. Intro to Java
 * courseCredit e.g. 3.0
 * callNumber e.g. 123456
 * status e.g. Full, Cancelled or Available
 * lecturer e.g. PERDISCI
 * meetings i.e. list of meeting objects where each meeting object represents a day and meeting time (start Time and end Time)
 */
class Section{
	private $courseNumber;
	private $coursePrefix;
	private $lecturer;
	private $callNumber;
	private $status;
	private $courseName;
	private $courseCredit;
	private $buildingNumber;
	private $roomNumber;
	private $meetings;

	/**
	 * Public constructor for the Section object
	 * @param String $name e.g. "Intro to Java"
	 * @param String $prefix e.g. "CSCI"
	 * @param String $number e.g. "1302"
	 * @param int $callNo e.g. 12345
	 * @param String $availability e.g. Full, Cancelled or Available
	 * @param double $credit e.g. 3.0
	 * @param String $lecturer e.g. PERDISCI
	 */
	function __construct($name, $prefix, $number, $callNo, $availability, $credit, $teacher){
		$this->courseName = (string) $name;
		$this->coursePrefix   = (string) $prefix;
		$this->courseNumber   = (string) $number;
		$this->callNumber = (int) $callNo;
		$this->status = (string) $availability;
		$this->courseCredit = (double) $credit;
		$this->lecturer = (string) $teacher;
		$this->meetings = array();
		$this->buildingNumber = -1;
		$this->roomNumber = "";
	}

	/**
	 * Getter for the courseNumber data member
	 * @return String $courseNumber
	 */
	public function getCourseNumber(){
		return $this->courseNumber;
	}

	/**
	 * Getter for the coursePrefix data member
	 * @return String $coursePrefix
	 */
	public function getCoursePrefix(){
		return $this->coursePrefix;
	}

	/**
	 * Getter for the lecturer data member
	 * @return String $lecturer
	 */
	public function getLecturer(){
		return $this->lecturer;
	}

	/**
	 * Getter for the callNumber data member
	 * @return int $callNumber
	 */
	public function getCallNumber(){
		return $this->callNumber;
	}

	/**
	 * Getter for the status data member
	 * @return String $status
	 */
	public function getStatus(){
		return $this->status;
	}


	/**
	 * Getter for the courseNumber data member
	 * @return String $courseNumber
	 */
	public function getCourseName(){
		return $this->courseName;
	}


	/**
	 * Getter for the courseCredit data member
	 * @return double $courseCredit
	 */
	public function getCourseCredit(){
		return $this->courseCredit;
	}

	/**
	 * Getter for the meetings array
	 * @return array $meetings
	 */
	public function getMeetings(){
		return $this->meetings;
	}

	/**
	 * Setter for the building number
	 * @param int $buildingNumber e.g. 1040
	 * @return boolean
	 */
	public function setBuildingNumber($bldgNumber){
		if (gettype($bldgNumber) == "integer"){
			$this->buildingNumber = $bldgNumber;
			return true;
		}
		return false;
	}

	/**
	 * Getter for the building number
	 * @return int buildingNumber
	 */
	public function getBuildingNumber(){
		return $this->buildingNumber;
	}

	/**
	 * Setter for the roomNumber  data member
	 * @param String $roomNumber e.g. 301
	 * @return void
	 */
	public function setRoomNumber($rmNumber){
		$this->roomNumber = $rmNumber;
	}

	/**
	 * Getter for the roomNumber data member
	 * @return String roomNumber
	 */
	public function getRoomNumber(){
		return $this->roomNumber;
	}

	/**
	 * Getter for the courseNumber data member
	 * @return boolean
	 */
	public function addMeeting($mtg){
		if (gettype($mtg == "object") && $mtg != null){
			if (!(strcasecmp($mtg->getDay(),"AR") == 0 || strcasecmp($mtg->getDay(),"VR") == 0)){
				$this->meetings[] = $mtg;
				return true;
			}
		}
		return false;
	}
}
?>
