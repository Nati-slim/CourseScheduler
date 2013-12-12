<?php
/**
 * Meeting object that contains the day a class meets,
 * start time, end time and a callNumber.
 * day - e.g. M, T, W, R, F
 * startTime e.g. 0845A, 1212P
 * endTime e.g. 0545P, 1305P
 * callNumber e.g. 12345
 */
class Meeting{
	private $day;
	private $time;
	private $startTime;
	private $endTime;
	private $callNumber;
	private $errorMessage;

	/**
	 * Constructor for the Meeting class
	 * @param int $number section call number
	 * @param String $dy e.g. "T", "W", etc
	 * @param String $stime start time of the class meeting e.g. 0730A
	 * @param String $etime end time of the class meeting e.g. 0845A
	 */
	function __construct($number, $dy, $stime, $etime){
		try{
			$this->day = (string)$dy[0];
			$this->time = (string) ($stime . "-" . $etime);
			$this->startTime = (string)$stime;
			$this->endTime = (string)$etime;
			$this->callNumber = (int)$number;
			$this->errorMessage = "";
		}catch(Exception $e){
			echo "Error instantiating meeting object: " . $e->getMessage() . "\n";
		}
	}

	/**
	 * Getter for the day data member
	 * @return String $day
	 */
	public function getDay(){
		return $this->day;
	}

	/**
	 * Getter for the concatenated start Time and end Time
	 * @return String $time
	 */
	public function getMeetingTime(){
		return $this->time;
	}

	/**
	 * Getter for the callNumber data member
	 * @return int $callNumber
	 */
	public function getCallNumber(){
		return $this->callNumber;
	}

	/**
	 * Getter for the startTime data member
	 * @return String $startTime
	 */
	public function getStartTime(){
		return $this->startTime;
	}

	/**
	 * Getter for the endTime data member
	 * @return String $endTime
	 */
	public function getEndTime(){
		return $this->endTime;
	}

	/**
	 * Getter for the starting hour of the class meeting
	 * Returns 0 if startTime is not in right format i.e. XXXXA or XXXXP
	 * @return int startHour
	 */
	public function getStartHour(){
		//Making sure input is of the form: 0845A
		if (strlen($this->startTime) == 5){
			$hr = (int)substr($this->startTime,0,2);
			//Converting to 24hr form
			if ($this->startTime[strlen($this->startTime)-1] == "P" && $hr != 12){
				$hr += 12;
			}
		}else{
			return 0;
		}
		return $hr;
	}

	/**
	 * Getter for the starting minute of the class meeting
	 * Returns 0 if startTime is not in right format i.e. XXXXA or XXXXP
	 * @return int startMinute
	 */
	public function getStartMinute(){
		if (strlen($this->startTime) == 5){
			return (int)substr($this->startTime,2,4);
		}else{
			return 0;
		}
	}

	/**
	 * Getter for the ending hour of the class meeting
	 * Returns 0 if endTime is not in right format i.e. XXXXA or XXXXP
	 * @return int endHour
	 */
	public function getEndHour(){
		if (strlen($this->endTime) == 5){
			$hr = (int)substr($this->endTime,0,2);
			if ($this->endTime[strlen($this->endTime)-1] == "P" && $hr != 12){
				$hr += 12;
			}
		}else{
			return 0;
		}
		return $hr;
	}

	/**
	 * Getter for the end minute of the class meeting
	 * Returns 0 if endTime is not in right format i.e. XXXXA or XXXXP
	 * @return int endMinute
	 */
	public function getEndMinute(){
		if (strlen($this->endTime) == 5){
			return (int)substr($this->endTime,2,4);
		}else{
			return 0;
		}
	}

	/**
	 * Getter for the AM or PM status of the start time
	 * returns 'Z' if startTime is not in the right format
	 * otherwise returns 'A' for AM or 'P' for PM
	 * @return String startAMorPM
	 */
	public function getStartAMPM(){
		if (strlen($this->startTime) == 5){
			return $this->startTime[strlen($this->startTime)-1];
		}else{
			return "Z";
		}
	}

	/**
	 * Getter for the AM or PM status of the end time
	 * returns 'Z' if endTime is not in the right format
	 * otherwise returns 'A' for AM or 'P' for PM
	 * @return String endAMorPM
	 */
	public function getEndAMPM(){
		if (strlen($this->endTime) == 5){
			return $this->endTime[strlen($this->endTime)-1];
		}else{
			return "Z";
		}
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

	public function __toString(){
		return $this->time;
	}

    public function to_json() {
        $arayValues = array();
		$arrayValues['day'] = $this->day;
		$arrayValues['startTime'] = $this->startTime;
		$arrayValues['endTime'] = $this->endTime;
		$arrayValues['callNumber'] = $this->callNumber;
		return json_encode($arrayValues);
    }
}
?>
