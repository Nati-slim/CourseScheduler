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
	private $casTaken;
	private $casRequired;
	private $buildingNumber;
	private $roomNumber;
	private $meetings;
	private $campus;
	private $semester;
	public $errorMessage;

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
		try{
			$this->courseName = htmlspecialchars($name,ENT_QUOTES);
			$this->coursePrefix   = (string) $prefix;
			$this->courseNumber   = (string) $number;
			$this->callNumber = (int) $callNo;
			$this->status = (string) $availability;
			$this->courseCredit = (double) $credit;
			if (strcmp($teacher,"null") == 0){
				$this->lecturer = "No Professor";
			}else{
				$this->lecturer = htmlspecialchars($teacher,ENT_QUOTES);
			}
			$this->meetings = array();
			//Setting defaults for items not assigned yet.
			$this->buildingNumber = -1;
			$this->roomNumber = "";
			$this->casTaken = -1;
			$this->casRequired = -1;
			$this->campus = "";
			$this->semester = "";
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage =  "Error instantiating section object: " . $e->getMessage() . "\n";
		}
	}

	public static function makeSection($name, $prefix, $number, $callNo, $availability, $credit, $teacher,$building,$room,$casT,$casR,$currentProgram,$term){
		try{
			$obj = new Section($name, $prefix, $number, $callNo, $availability, $credit, $teacher);
			$obj->setBuildingNumber($building);
			$obj->setRoomNumber($room);
			$obj->setCasTaken((int)$casT);
			$obj->setCasRequired((int)$casR);
			$obj->setCampus($currentProgram);
			$obj->setSemester($term);
			$obj->errorMessage = "";
			return $obj;
		}catch(Exception $e){
			printf("Error instantiating section object: " . $e->getMessage() . "\n");
			exit();
		}
		return null;
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

	public function setMeetings($mtgArray){
		$this->meetings = $mtgArray;	
	}
	
	public function getCasTaken(){
		return $this->casTaken;
	}
	
	public function getCasRequired(){
		return $this->casRequired;
	}
	
	public function setCasTaken($casT){
		try{
			$this->casTaken = (int)$casT;
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage = "Unable to cast cas taken to integer.";
		}
	}
	
	public function setCasRequired($casR){
		try{
			$this->casRequired = (int)$casR;
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage = "Unable to cast cas required to integer.";
		}
	}
	
	public function getCampus(){
		return $this->campus;
	}
	
	public function getSemester(){
		return $this->semester;
	}

	public function setCampus($location){
		try{
			$this->campus = (string)$location;	
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage = "Unable to cast building number to integer.";
		}
	}
	
	public function setSemester($sem){
		try{
			$this->semester = (string)$sem;
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage = "Unable to cast building number to integer.";
		}
	}

	
	/**
	 * Setter for the building number
	 * @param int $buildingNumber e.g. 1040
	 * @return boolean
	 */
	public function setBuildingNumber($bldgNumber){
		try{
			$this->buildingNumber = (int)$bldgNumber;
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage = "Unable to cast building number to integer.";
		}
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
			if ($mtg->getCallNumber() == $this->callNumber){
				if (!(strcasecmp($mtg->getDay(),"AR") == 0 || strcasecmp($mtg->getDay(),"VR") == 0)){
					$this->meetings[$mtg->getDay()] = $mtg;
					$this->errorMessage = "";
					return true;
				}
			}else{
				$this->errorMessage = "Please add a meeting number with the correct call number that matches the section.";
			}
		}else{
			$this->errorMessage = "Create a meeting object first and then, add the object to the section.";
		}
		return false;
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
	

	public function getMeetingsArray(){
		$res = array();	
		foreach($this->meetings as $mtg){
			$res[$mtg->getDay()] = $mtg->getMeetingTime();
		}
		return $res;
	}	

	/**
	* Returns an array of the section values as a JSON-encoded object
	*/
    public function to_json() {
        $arrayValues = $this->to_array();
		return json_encode($arrayValues);
    }

    public function to_array() {
        $arrayValues = array();
		$arrayValues['courseName'] = $this->courseName;
		$arrayValues['coursePrefix'] = $this->coursePrefix;
		$arrayValues['courseNumber'] = $this->courseNumber;
		$arrayValues['callNumber'] = $this->callNumber;
		$arrayValues['lecturer'] = $this->lecturer;
		$arrayValues['status'] = $this->status;
		$arrayValues['buildingNumber'] = $this->buildingNumber;
		$arrayValues['roomNumber'] = $this->roomNumber;
		$arrayValues['casTaken'] = $this->casTaken;
		$arrayValues['casRequired'] = $this->casRequired;
		$arrayValues['campus'] = $this->campus;
		$arrayValues['semester'] = $this->semester;
		$arrayValues['meetings'] = $this->getMeetingsArray();
		$arrayValues['errorMessage'] = $this->errorMessage;
		return $arrayValues;
    }


	/**
	 * Return a string version of the Section object
	 * which should be valid JSON output for use in drawing the schedule
	 * @return String $output valid JSON representation of the Section object
	 */
	public function __toString(){
		$output = "{\"courseName\":\"" . $this->courseName . "\",";
		$output .= "\"coursePrefix\": \"" . $this->coursePrefix. "\",";
		$output .= "\"courseNumber\":\"" . $this->courseNumber . "\",";
		$output .= "\"courseLecturer\":\"" . $this->lecturer . "\",";
		$output .= "\"courseCredit\":" . $this->courseCredit . ",";
		$output .= "\"callNumber\":" . $this->callNumber . ",";
		$output .= "\"status\":\"" . $this->status . "\",";
		$output .= "\"building\":" . $this->buildingNumber . ",";
		$output .= "\"room\":\"" . $this->roomNumber . "\",";
		$output .= "\"casTaken\":" . $this->casTaken . ",";
		$output .= "\"casRequired\":\"" . $this->casRequired . "\",";
		$output .= "\"campus\":" . $this->campus . ",";
		$output .= "\"semester\":\"" . $this->semester . "\",";
		$output .= "\"meetings\": {";
		$len = strlen($output);
		foreach($this->meetings as $mtg){
			$output .= "\"" . $mtg->getDay() . "\": \"" . $mtg->getMeetingTime() . "\",";
		}
		$output = substr($output,0,strlen($output)-1);
		if ($len < strlen($output)){
			$output .= "}";
		}else{
			$output .= "{}";
		}
		$output .= "}";
		return $output;
	}
}
?>
