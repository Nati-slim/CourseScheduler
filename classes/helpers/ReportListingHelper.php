<?php
//place this file in a directory not accessible over the internet
require_once("../../../../creds/credentials.inc");
require_once("../models/Offering.php");

class ReportListingHelper{
	private $getlistofferings;
	private $getsingleoffering;
	private $deleteoffering;
	private $addsingleoffering;
	private $getlatestoffering;
	private $getofferingbycampus;
	private $getofferingbyterm;
	private $truncateTable;
	public $errorMessage;
    private $dbconn;

	/**
	 * Default constructor to access the database
	 * Contains methods to access the ReportsAvailable database
	 *
	 */
	function __construct(){
		try {
			$this->dbconn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			if ($this->dbconn->connect_errno){
				$this->errorMessage = "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
			}else{
				//echo $this->dbconn->host_info . "\n";
				$this->getlistofferings = $this->dbconn->prepare("select * from ReportsAvailable");
				$this->getsingleoffering = $this->dbconn->prepare("select * from ReportsAvailable where id = ?");
				$this->deleteoffering = $this->dbconn->prepare("delete from ReportsAvailable where id = ?");
				$this->addsingleoffering = $this->dbconn->prepare("insert into ReportsAvailable (id,name,term,campus,lastModified) values(DEFAULT,?,?,?,?)");
				$this->getlatestoffering = $this->dbconn->prepare("select max(lastModified) from ReportsAvailable");
				$this->getofferingbycampus = $this->dbconn->prepare("select * from ReportsAvailable where campus = ?");
				$this->getofferingbyterm = $this->dbconn->prepare("select * from ReportsAvailable where term = ?");
				$this->truncateTable = $this->dbconn->prepare("truncate table ReportsAvailable");
				$this->errorMessage = "";
			}			
		} catch(Exception $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
	}

	/**
	 * Truncate the tables
	 */
	function clearTable(){
		try{
			if (!($this->truncateTable)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($value = $this->truncateTable->execute())){
				echo "Execute failed: (" . $this->truncateTable->errno . ") " . $this->truncateTable->error;
			}else{
				$this->errorMessage = "";
			}
		}catch(Exception $e){
			$this->errorMessage = "Error with clearTable: " . $e->getMessage();
		}
	}

	/**
	 * Get the Offering object; Returns null if not present in table.
	 * @param int $id to identify the Offering object in the database
	 * @return Offering object
	 */
	function getSingleOffering($id){
		$offering = null;
		try{
			if (!($this->getsingleoffering)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->getsingleoffering->bind_param("i",$id))){
				$this->errorMessage = "Binding parameters failed: (" . $this->getsingleoffering->errno . ") " . $this->getsingleoffering->error;
			}else if (!($this->getsingleoffering->execute())){
				$this->errorMessage = "Execute failed: (" . $this->getsingleoffering->errno . ") " . $this->getsingleoffering->error;
			}else if (!($this->getsingleoffering->bind_result($id,$name,$term,$campus,$lastModified))){
				$this->errorMessage = "Binding results failed: (" . $this->getsingleoffering->errno . ") " . $this->getsingleoffering->error;
			}else{
				while ($this->getsingleoffering->fetch() && !($this->dbconn->errno)){
					$offering = new Offering($id,$name,$term,$campus,$lastModified);
				}
				if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->getsingleoffering->errno . ") " . $this->getsingleoffering->error;
					$this->errorMessage = "Db error.";
				}else{
					$this->errorMessage = "No Offering found.";
				}
			}
			$this->getsingleoffering->free_result();
			$this->errorMessage = "";
			return $offering;
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $offering;
	}

	/**
	 * Save the Offering entry to the database
	 * for access later
	 * @param String $name e.g. course_offering_service_UNIV_201405.xls
	 * @param String $term e.g. "Summer 2013"
	 * @param String $campus e.g. "Athens"
	 * @param String $lastModified e.g. 12/07/2013 07:19:29
	 * @return int newly created item's ID
	 */
	function addOffering($name,$term, $campus, $lastModified){
		//http://stackoverflow.com/questions/6238992/converting-string-to-date-and-datetime
		$dateModified = date('Y-m-d H:i:s',strtotime($lastModified));
		try{
			if (!($this->addsingleoffering)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->addsingleoffering->bind_param("ssss",$name,$term,$campus,$dateModified))){
				echo "Binding parameters failed: (" . $this->addsingleoffering->errno . ") " . $this->addsingleoffering->error;
			}else if (!($value = $this->addsingleoffering->execute())){
				echo "Execute failed: (" . $this->addsingleoffering->errno . ") " . $this->addsingleoffering->error;
			}else{
				$this->errorMessage = "";
				return true;
			}
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return false;
	}

	/**
	*
	* Return an array of the offerings
	* empty if nothing is present.
	* @param String $campus e.g. Athens
	* @return array list of offerings
	*/
	function getOfferingByCampus($camp){
		$result = array();
		try{
			if (!($this->getofferingbycampus)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->getofferingbycampus->bind_param("s",$camp))){
				$this->errorMessage = "Binding parameters failed: (" . $this->getofferingbycampus->errno . ") " . $this->getofferingbycampus->error;
			}else if (!($this->getofferingbycampus->execute())){
				$this->errorMessage = "Execute failed: (" . $this->getofferingbycampus->errno . ") " . $this->getofferingbycampus->error;
			}else if (!(($stored = $this->getofferingbycampus->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch failed (STMT): (" . $this->getofferingbycampus->errno . ") " . $this->getofferingbycampus->error;
			}else if (!($this->getofferingbycampus->bind_result($id,$name,$term,$campus,$dateModified))){
				$this->errorMessage = "Binding results failed: (" . $this->getofferingbycampus->errno . ") " . $this->getofferingbycampus->error;
			}else{
				if ($stored){
					while($this->getofferingbycampus->fetch()){
						$offer = new Offering($id,$name,$term,$campus,$dateModified);
						$result[] = $offer;
					}
				}else{
					$this->errorMessage = "Error storing results.";
				}
			}
			$this->getofferingbycampus->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $result;
	}

	function getOfferingByTerm($value){
		$result = array();
		try{
			if (!($this->getofferingbyterm)){
				$this->errorMessage = "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->getofferingbyterm->bind_param("s",$value))){
				$this->errorMessage = "Binding parameters failed: (" . $this->getofferingbyterm->errno . ") " . $this->getofferingbyterm->error;
			}else if (!($this->getofferingbyterm->execute())){
				$this->errorMessage = "Execute failed: (" . $this->getofferingbyterm->errno . ") " . $this->getofferingbyterm->error;
			}else if (!(($stored = $this->getofferingbyterm->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch failed (STMT): (" . $this->getofferingbyterm->errno . ") " . $this->getofferingbyterm->error;
			}else if (!($this->getofferingbyterm->bind_result($id,$name,$term,$campus,$dateModified))){
				$this->errorMessage = "Binding results failed: (" . $this->getofferingbyterm->errno . ") " . $this->getofferingbyterm->error;
			}else{
				if ($stored){
					while($this->getofferingbyterm->fetch()){
						$offering = new Offering($id,$name,$term,$campus,$dateModified);
						$result[] = $offering;
					}
				}else{
					$this->errorMessage = "Error storing results.";
				}
			}
			$this->getofferingbyterm->free_result();
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $result;
	}
}
?>
