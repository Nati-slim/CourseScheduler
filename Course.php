<?php

class Course{
	private $courseNumber;
	private $coursePrefix;
	private $sectionListings;

	function __construct($prefix, $number){
		$this->coursePrefix   = (string) $prefix; // It's good practice to use type-casting.
		$this->courseNumber   = (string) $number;
		$this->sectionListings = array();
	}

	function getCourseNumber(){
		return $this->courseNumber;
	}

	function getCoursePrefix(){
		return $this->coursePrefix;
	}

	function getCourseListings(){
		return $this->sectionListings;
	}

	function addSection($sec){
		$this->sectionListings[] = $sec;
	}
}
?>
