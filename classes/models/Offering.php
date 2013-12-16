<?php
/*
 * Offering class representing a USG offering 
https://apps.reg.uga.edu/reporting/staticReports
 */
class Offering{
	private $id;
	private $name;
	private $term;
	private $campus;
	private $lastModified;

	/**
	 * Constructor for the Offering class
	 * @param String $name e.g. "course_wbe.csv"
	 * @param String $term e.g. "Spring 2014"
	 * @param String $campus e.g. "Athens"
	 * @param String $dateModified e.g. "12/07/2013 06:30:23"
	 */
	function __construct($id, $name, $term, $campus, $dateModified){
		try{
			$this->id   = (int) $id;
			$this->name   = (string) $name;
			$this->term  = (string) $term;
			$this->campus   = (string) $campus;
			//http://stackoverflow.com/questions/9490177/how-to-generate-date-for-mysql-datetime-type-with-php
			$time = strtotime($dateModified);
			$this->lastModified = date('Y-m-d H:i:s',$time);
			$errorMessage = "";
		}catch(Exception $e){
			echo "Error instantiating Course object: " . $e->getMessage() . "\n";
		}
	}

	/**
	 * Getter for the id
	 * @return int $id
	 */
	function getId(){
		return $this->id;
	}

	/**
	 * Getter for the id
	 */
	function setId($id){
		$this->id = $id;
	}

	/**
	 * Getter for the name
	 * @return string $name
	 */
	function getName(){
		return $this->name;
	}

	/**
	 * Setter for the name
	 */
	function setName($name){
		$this->name = name;
	}

	/**
	 * Getter for the term
	 * @return string $term
	 */
	function getTerm(){
		return $this->term;
	}

	/**
	 * Setter for the id
	 */
	function setTerm($term){
		$this->term = $term;
	}

	/**
	 * Getter for the campus
	 * @return string $campus
	 */
	function getCampus(){
		return $this->campus;
	}

	/**
	 * Setter for the id
	 */
	function setCampus($campus){
		$this->campus = $campus;
	}

	/**
	 * Getter for the lastModified
	 * @return string $lastModified
	 */
	function getLastModified(){
		return $this->lastModified;
	}

	/**
	 * Setter for the lastModified
	 */
	function setLastModified($lastModified){
		$this->lastModified = $lastModified;
	}
}

?>
