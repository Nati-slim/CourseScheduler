<?php
/*
 * Course class representing a course e.g. CSCI 1301
 */
class Course{
	private $courseNumber;
	private $coursePrefix;
	private $sectionListings;
	private $errorMessage;

	/**
	 * Constructor for the Course class
	 * @param String $prefix e.g. "CSCI"
	 * @param String $number e.g. "1302"
	 */
	function __construct($prefix, $number){
		try{
			$this->coursePrefix   = (string) $prefix; // It's good practice to use type-casting.
			$this->courseNumber   = (string) $number;
			$this->sectionListings = array();
			$errorMessage = "";
		}catch(Exception $e){
			echo "Error instantiating Course object: " . $e->getMessage() . "\n";
		}
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
		//Condition is true if a object is being added
		if (gettype($sec) == "object" && $sec != null){
			//verify coursePrefix and courseNumbers match the course objects.
			if (strcasecmp($sec->getCoursePrefix(),$this->coursePrefix) == 0 && strcasecmp($sec->getCourseNumber(),$this->courseNumber) == 0){
				//echo "Adding " . print_r($sec,true). "\n";
				$this->sectionListings[$sec->getCallNumber()] = $sec;
				$this->errorMessage = "";
				return true;
			}else{
				$this->errorMessage = "Please add only section objects that belong to this course  prefix and course number.";
			}
		}else{
			$this->errorMessage = "Please create a section object first before adding items to the course list of sections.";
		}
		return false;
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

	/**
	 * Return a string version of the Course object
	 * which should be valid JSON output for use in drawing the schedule
	 * @return String $output valid JSON representation of the Course object
	 */
	public function toJSON(){
		$output = "{";
		$output .= "\"coursePrefix\": \"" . $this->coursePrefix . "\",";
		$output .= "\"courseNumber\":\"" . $this->courseNumber . "\",";
		$output .= "\"sections\": [";
		$len = strlen($output);
		foreach ($this->sectionListings as $section){
			//print each section followed by a comma
			$output .= $section->toJSON() . ",";
		}
		//close brackets if sections were found.
		if ($len < strlen($output)){
			$output = substr($output,0,strlen($output)-1);
		}
		$output .= "]}";
		return $output;
	}

	/**
	 * Return a string version of the Course object
	 * which should be valid JSON output for use in drawing the schedule
	 * @return String $output valid JSON representation of the Course object
	 */
	public function __toString(){
		$output = "{";
		$output .= "\"coursePrefix\": \"" . $this->coursePrefix . "\",";
		$output .= "\"courseNumber\":\"" . $this->courseNumber . "\",";
		$output .= "\"sections\": [";
		$len = strlen($output);
		foreach ($this->sectionListings as $section){
			//print each section followed by a comma
			$output .= $section->toJSON() . ",";
		}
		//close brackets if sections were found.
		if ($len < strlen($output)){
			$output = substr($output,0,strlen($output)-1);
		}
		$output .= "]}";
		return $output;
	}
}
?>
