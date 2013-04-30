<?php
class Section{
	var $courseNumber;
	var $coursePrefix;
	var $lecturer;
	var $callNumber;
	var $status;
	var $courseName;
	var $courseCredit;
	var $meetings;


	function __construct($name, $prefix, $number, $callNo, $availability, $credit, $teacher){
		$this->courseName = (string) $name;
		$this->coursePrefix   = (string) $prefix;
		$this->courseNumber   = (string) $number;
		$this->callNumber = (int) $callNo;
		$this->status = (string) $availability;
		$this->courseCredit = (double) $credit;
		$this->lecturer = (string) $teacher;
		$this->meetings = array();
		echo "Section constructor called." ."\n";
	}

	function getCourseNumber(){
		return $courseNumber;
	}

	function getCoursePrefix(){
		return $coursePrefix;
	}

	function getLecturer(){
		return $lecturer;
	}

	function getCallNumber(){
		return $callNumber;
	}

	function getStatus(){
		return $status;
	}

	function getCourseName(){
		return $courseName;
	}

	function getCourseCredit(){
		return $courseCredit;
	}

	function getMeetings(){
		return $meetings;
	}

	function addMeeting($mtg){
		$this->meetings[] = $mtg;
	}
}
?>
