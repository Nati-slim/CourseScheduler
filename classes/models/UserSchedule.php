<?php
class UserSchedule{
	private $schedule;
	private $scheduleID;
	private $shortName;
	private $imageIDs;
    private $dateAdded;
	private $userid;
    private $saved;
	//UNIV
	private $campus;
	//201402
	private $semester;
	private $errorMessage;

	/**
	 * Public constructor for the UserSchedule
	 * class
	 * @param integer $randomId randomly generated number to ID the user's session
	 *
	 */
	function __construct($randomId){
		$this->userid = $randomId;
		$this->schedule = array();
		$this->imageIDs = array();
		$this->campus = "";
		$this->semester = "";
        $this->saved = false;
        $this->dateAdded = new DateTime(null, new DateTimeZone('America/New_York'));//date('Y-m-d H:i:s');
        $this->errorMessage = "";
	}
	
	public static function makeSchedule($userid,$campus,$semester,$scheduleID){
		try{
			$obj = new UserSchedule($userid);
			$obj->setCampus($campus);
			$obj->setSemester($semester);
			$obj->setScheduleID($scheduleID);
			return $obj;
		}catch(Exception $e){
			printf("Error instantiating user schedule object: " . $e->getMessage() . "\n");
			exit();
		}
		return null;	
	}

	/**
	 * Method to delete a section from the user's schedule
	 * Returns true on success and false otherwise.
	 * @param integer $callNumber the call number of the section to be deleted
	 * @return boolean
	 */
	function deleteSection($callNumber){
		$key = array_key_exists($callNumber,$this->schedule);
		if($key){
			unset($this->schedule[$callNumber]);
			$this->errorMessage = "";
			return true;
		}else{
			$this->errorMessage = $callNumber . " not in the user's schedule.";
		}
		return false;
	}

	/**
	 * Method to add a section to the user's schedule
	 * Returns true if succeeded and false otherwise
	 * @param Section $newSection the new section to be added
	 * @return boolean
	 */
	function addSection($newSection){
		$key = array_key_exists($newSection->getCallNumber(),$this->schedule);
		//If key is not already in user's list, check for possible conflicts
		if ($key == false){
			foreach($this->schedule as $currentSection){
				if ($this->isConflict($newSection->getMeetings(),$currentSection->getMeetings())){
					$this->errorMessage = "Conflict detected with existing section " . $currentSection->getCoursePrefix() . "-" . $currentSection->getCourseNumber() . " # " . $currentSection->getCallNumber() . "\n";
					return false;
				}else if (strcasecmp($currentSection->getCoursePrefix(),$newSection->getCoursePrefix()) == 0
				&& strcasecmp($currentSection->getCourseNumber(),$newSection->getCourseNumber()) == 0){
					$this->errorMessage = "Attempting to take the same course " . $newSection->getCoursePrefix() . "-" .$newSection->getCourseNumber(). " twice.";
					return false;
				}
			}
			$this->errorMessage = "";
			$this->schedule[$newSection->getCallNumber()] = $newSection;
			return true;
		}else{
			$this->errorMessage = "Section " . $newSection->getCoursePrefix() . "-" . $newSection->getCourseNumber() . " #" . $newSection->getCallNumber() . " already present in user's schedule.";
		}
		return false;
	}

	/**
	 * Checks two lists of meeting objects for
	 * overlap in order to determine any class conflicts
	 * Returns true if a conflict exists and false otherwise.
	 * @param array() $newMeetings list of meetings from the potential new sections
	 * @param array() $currentMeetings list of meetings from an already existing section in the user's schedule
	 * @return boolean
	 */
	function isConflict($newMeetings,$currentMeetings){
		//Grab the 'keys' aka days from both meeting lists
		if (count($currentMeetings) == 0){
			return true;
		} else if (count($newMeetings) > 0){
			$currKeys = array_keys($currentMeetings);
			$newKeys = array_keys($newMeetings);
			//Get intersecting keys i.e. days common to both meeting lists
			$results = array_intersect($newKeys,$currKeys);
			//Obtain the meeting objects corresponding to that key
			foreach ($results as $key){
				$currMtg = $currentMeetings[$key];
				$newMtg = $newMeetings[$key];
				//Check overlap of the meeting times
				if ($this->isOverlap($newMtg,$currMtg)){
					return true;
				}
			}

		}else{
			return false;
		}
	}

	/**
	 * check if 2 meeting object overlap
	 * Returns true if they do and false otherwise.
	 * True if the back to back courses don't have at least 15 mins between then
	 * @param Meeting $newMtgObj the potential meeting object
	 * @param Meeting $currMtgObj the currently existing meeting object
	 * @return boolean
	 *
	 */
	function isOverlap($newMtgObj, $currMtgObj){
		if (strcasecmp($newMtgObj->getDay(),$currMtgObj->getDay()) != 0){
			return false;
		}
		//Get the start and end time for the first parameter
		$hr1start = $newMtgObj->getStartHour();
		$min1start = $newMtgObj->getStartMinute();
		$hr1end = $newMtgObj->getEndHour();
		$min1end = $newMtgObj->getEndMinute();
		//get the start and end times for the second parameter
		$hr2start = $currMtgObj->getStartHour();
		$min2start = $currMtgObj->getStartMinute();
		$hr2end = $currMtgObj->getEndHour();
		$min2end = $currMtgObj->getEndMinute();

		//Start comparisons. Make sure there is a buffer of at least 15 mins between the end / start times
		// of back to back classes.
		if (($hr1end < $hr2start && $hr1end < $hr2end) || ($hr2end < $hr1start && $hr2end < $hr1end) ){
			return false;
		} else if ($hr2end == $hr1start && (($min2end - $min1start) <= -15)){
			return false;
		} else if ($hr1end == $hr2start && ($min1end - $min2start <= -15)){
			return false;
		}
		return true;
	}

    /**
     * Returns the short name of the schedule
     * 
     *@return string the short name selected by user
     *  
     */ 
	public function getShortName(){
		return $this->shortName;
	}

    /**
     * Returns the schedule ID of the schedule
     * 
     *@return string the schedule ID of the UserSchedule object
     *  
     */ 
	public function getScheduleID(){
		return $this->scheduleID;
	}
	
    //http://stackoverflow.com/questions/470617/get-current-date-and-time-in-php
    public function getDateAdded(){
        return $this->dateAdded->format('Y-m-d H:i:s');
    }
    
    /**
     * 
     * @param $date DateTime object
     */ 
    public function setDateAdded($date){
        $this->dateAdded = $date;
    }
    
	public function getCampus(){
		return $this->campus;
	}
	
	public function getSemester(){
		return $this->semester;
	}
	
	public function setShortName($name){
		try{
			$this->shortName = (string)$name;	
			$this->errorMessage = "";
			return true;
		}catch(Exception $e){
			$this->errorMessage = "Unable to cast short name to string: " . $e->getMessage();
		}
		return false;
	}
	
	public function setScheduleID($id){
		try{
			$this->scheduleID = (string)$id;	
			$this->errorMessage = "";
			return true;
		}catch(Exception $e){
			$this->errorMessage = "Unable to cast building number to integer: " . $e->getMessage();
		}
		return false;
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
	
    public function isSaved(){
        return $this->saved;
    }
    
    public function setSaved($status){
        $this->saved = $status;
    }
	
	public function getSavedImages(){
		return $this->imageIDs;
	}
	
	/**
	 * Adds the image token to the user schedule object for retrieval later
	 **/
	public function addImageID($token){
		$this->imageIDs[] = $token;
	}
	
	/**
	 * Returns the list of sections the user is enrolled in
	 * @return array() $schedule
	 */
	public function getSchedule(){
		return $this->schedule;
	}

	/**
	 * Returns the user id
	 * @return int $userid
	 */
	function getUserId(){
		return $this->userid;
	}
	
	/**
	 * Returns the user id
	 * @return int $userid
	 */
	function setUserId($userid){
		$this->userid = $userid;
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

	public function to_json(){
		$result = array();
		foreach($this->schedule as $section){
			$result[$section->getCallNumber()] = $section->to_array();
		}	
		return json_encode($result);
	}

	public function to_array(){
		$result = array();
		$result['userid'] = $this->getUserId();
		$result['errorMessage'] = $this->getErrorMessage();
		$classSchedule = array();
		foreach($this->schedule as $section){
			$classSchedule[$section->getCallNumber()] = $section->to_array();
		}
		$result['classSchedule'] = $classSchedule;
		$result['scheduleID'] = $this->scheduleID;
		$result['savedImageIDs'] = $this->imageIDs;
		return $result;
	}
}
?>
