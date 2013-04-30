<?php
class Meeting{
	private $day;
	private $time;
	private $startTime;
	private $endTime;
	private $callNumber;

	function __construct($number, $dy, $stime, $etime){
		$this->day = $dy;
		$this->time = $stime . "-" . $etime;
		$this->startTime = $stime;
		$this->endTime = $etime;
		$this->callNumber = $number;
	}

	function getDay(){
		return $this->day;
	}

	function getCallNumber(){
		return $this->callNumber;
	}

	function getStartTime(){
		return $this->startTime;
	}

	function getEndTime(){
		return $this->getEndTime;
	}

	function getStartHour(){
		$hr = (int)substr($this->startTime,0,2);
		if (getStartAMPM() === 'P' && $hr != 12){
			$hr += 12;
		}
		return $hr;
	}

	function getStartMinute(){
		return (int)substr($this->startTime,2,4);
	}

	function getEndHour(){
		$hr = (int)substr($this->endTime,0,2);
		if (getEndAMPM() === 'P' && $hr != 12){
			$hr += 12;
		}
		return $hr;
	}

	function getEndMinute(){
		return $this->callNumber;
	}

	function getStartAMPM(){
		return $this->startTime[strlen($this->startTime)-1];
	}

	function getEndAMPM(){
		return $this->endTime[strlen($this->endTime)-1];
	}
}
?>
