<?php
class Course{
	var $courseNumber;
	var $coursePrefix;
	var $sectionListings;

	function __construct($prefix, $number){
		$this->coursePrefix   = (string) $prefix; // It's good practice to use type-casting.
		$this->courseNumber   = (string) $number;
		$this->sectionListings = array();
		echo "Course constructor called." ."\n";
	}


	function getCourseNumber(){
		return $courseNumber;
	}

	function getCoursePrefix(){
		return $coursePrefix;
	}

	function getCourseListings(){
		return $sectionListings;
	}

	function addSection($sec){
		$this->sectionListings[] = $sec;
	}
}
?>
