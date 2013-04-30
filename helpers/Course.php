<?php
/*
 * Course class representing a course e.g. CSCI 1301
 */
class Course{
	private $courseNumber;
	private $coursePrefix;
	private $sectionListings;

	/**
	 * Constructor for the Course class
	 * @param String $prefix e.g. "CSCI"
	 * @param String $number e.g. "1302"
	 */
	function __construct($prefix, $number){
		$this->coursePrefix   = (string) $prefix; // It's good practice to use type-casting.
		$this->courseNumber   = (string) $number;
		$this->sectionListings = array();
	}

	/**
	 * Getter for the courseNumber data member
	 * @return String $courseNumber
	 */
	function getCourseNumber(){
		return $this->courseNumber;
	}

	/**
	 * Getter for the coursePrefix data member
	 * @return String $coursePrefix
	 */
	function getCoursePrefix(){
		return $this->coursePrefix;
	}

	/**
	 * Getter for the sectionListings array
	 * @return array $sectionListings
	 */
	function getCourseListings(){
		return $this->sectionListings;
	}

	/**
	 * Add a section object to the course
	 * @return boolean
	 */
	function addSection($sec){
		if (gettype($sec) == "object" && $sec != null){
			$this->sectionListings[] = $sec;
			return true;
		}
		return false;
	}
}
?>
