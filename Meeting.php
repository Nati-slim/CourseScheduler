<?php
class Meeting{
	var $day;
	var $time;
	var $startTime;
	var $endTime;
	var $callNumber;

	function __construct($number, $dy, $stime, $etime){
		$this->day = $dy;
		$this->time = $stime . "-" . $etime;
		$this->startTime = $stime;
		$this->endTime = $etime;
		$this->callNumber = $number;
		echo "Meeting constructor called." ."\n";
	}

	function getDay(){
		return $day;
	}

	function getCallNumber(){
		return $callNumber;
	}

	function getStartTime(){
		return $startTime;
	}

	function getEndTime(){
		return $getEndTime;
	}

	function getStartHour(){
		$hr = (int)substr($startTime,0,2);
		if (getStartAMPM() === 'P' && $hr != 12){
			$hr += 12;
		}
		return $hr;
	}

	function getStartMinute(){
		return (int)substr($startTime,2,4);
	}

	function getEndHour(){
		$hr = (int)substr($endTime,0,2);
		if (getEndAMPM() === 'P' && $hr != 12){
			$hr += 12;
		}
		return $hr;
	}

	function getEndMinute(){
		return $callNumber;
	}

	function getStartAMPM(){
		return $startTime[strlen($startTime)-1];
	}

	function getEndAMPM(){
		return $endTime[strlen($endTime)-1];
	}
}
?>
