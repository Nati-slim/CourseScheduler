<?php
class Section{
	private $courseNumber;
	private $coursePrefix;
	private $lecturer;
	private $callNumber;
	private $status;
	private $courseName;
	private $courseCredit;
	private $meetings;


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
		return $this->courseNumber;
	}

	function getCoursePrefix(){
		return $this->coursePrefix;
	}

	function getLecturer(){
		return $this->lecturer;
	}

	function getCallNumber(){
		return $this->callNumber;
	}

	function getStatus(){
		return $this->status;
	}

	function getCourseName(){
		return $this->courseName;
	}

	function getCourseCredit(){
		return $this->courseCredit;
	}

	function getMeetings(){
		return $this->meetings;
	}

	function addMeeting($mtg){
		$this->meetings[] = $mtg;
	}
}
?>
