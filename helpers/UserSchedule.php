<?php
class UserSchedule{
	private $schedule;
	private $userid;
	private $errorMessage;

	function __construct($randomId){
		$this->userid = (int)$randomId;
		$this->schedule = array();
	}

	/**
	 * Method to delete a section from the user's schedule
	 * Returns true on success and false otherwise.
	 * @param integer $callNumber the call number of the section to be deleted
	 * @return boolean
	 */
	function deleteSection($callNumber){
		$key = array_search($callNumber,$this->schedule);
		if($key!==false){
			unset($this->schedule[$callNumber]);
			$this->schedule = array_values($this->schedule);
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
		$key = array_search($newSection->getCallNumber(),$this->schedule);
		if ($key != false){
			foreach($this->schedule as $currentSection){
				if (isConflict($newSection->getMeetings(),$currentSection->getMeetings())){
					$this->errorMessage = "Conflict detected with existing section " . print_r($currentSection,true) . "\n";
					return false;
				}
			}
			$this->errorMessage = "";
			$this->schedule[$newSection->getCallNumber()] = $newSection;
			return true;
		}else{
			$this->errorMessage = $callNumber . " not in the user's schedule.";
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
				$newMtg = $newMeetings[$skey];
				//Check overlap of the meeting times
				if (isOverlap($newMtg,$currMtg)){
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
		$hr2start = $currMtgObj->getEndHour();
		$min2start = $currMtgObj->getEndMinute();
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
	 * Returns the list of sections the user is enrolled in
	 * @return array() $schedule
	 */
	function getSchedule(){
		return $this->schedule;
	}

	/**
	 * Returns the user id
	 * @return int $userid
	 */
	function getUserId(){
		return $this->userid;
	}
}
?>
