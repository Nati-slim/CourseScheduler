<?php
//place this file in a directory not accessible over the internet
require_once("../../../../creds/coursepicker.inc");
require_once("../models/User.php");

class UserHelper{
	private $authenticateuser;
	private $adduser;
	private $deleteuser;
	private $truncatetable;
	public $errorMessage;

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
				$this->deleteuser = $this->dbconn->prepare("delete * from coursepicker_users where userid = ?");
				$this->authenticateuser = $this->dbconn->prepare("select * from coursepicker_users where username = ?");
				$this->adduser = $this->dbconn->prepare("insert into coursepicker_users (id,userid,firstname,lastname,username,email,emailVerified,password,registration_date) values(DEFAULT,?,DEFAULT,DEFAULT,?,?,DEFAULT,?,NOW())");
				$this->truncatetable = $this->dbconn->prepare("truncate table coursepicker_users");
				$this->errorMessage = "";
			}			
		} catch(Exception $e) {
			$this->errorMessage = $e->getMessage();
		}
	}
	
	/**
	 * Truncate the tables
	 */
	private function clearTable(){
		try{
			if (!($this->truncatetable)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($value = $this->truncatetable->execute())){
				echo "Execute failed: (" . $this->truncatetable->errno . ") " . $this->truncatetable->error;
			}else{
				$this->errorMessage = "";
			}
		}catch(Exception $e){
			$this->errorMessage = "Error with clearTable: " . $e->getMessage();
		}
	}
	

	function getUser($username){
		try{
			if (!($this->authenticateuser)){
				$this->errorMessage = "Prepare for gethash failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->authenticateuser->bind_param("s",$username))){
				$this->errorMessage = "Binding parameters for gethash failed: (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else if (!($this->authenticateuser->execute())){
				$this->errorMessage = "Execute failed for gethash : (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else if (!(($stored = $this->authenticateuser->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch for gethash failed (STMT): (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else if (!($this->authenticateuser->bind_result($id,$userid,$firstname,$lastname,$name,$email,$emailVerified,$passhash,$registration_date))){
				$this->errorMessage = "Binding for gethash results failed: (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else{
				if ($this->authenticateuser->fetch() && !($this->dbconn->errno)){
					//$id,$userid,$username,$hash,$email,$date,$verified = 0,$firstname = "",$lastname = ""
					$user = new User($id,$userid,$username,$passhash,$email,$registration_date,$emailVerified,$firstname,$lastname);
					if ($user){
						$this->errorMessage = "";
						return $user;
					}
				}
				if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
					$this->errorMessage .= "Db error.";
				}else{
					$this->errorMessage = "No user found.";
				}
			}
			$this->authenticateuser->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return null;
	}	
	
	/*
	 * Add user to the database
	 */ 
	function addUser($userid,$username,$email,$password){
		try{
			if (!($this->adduser)){
				$this->errorMessage =  "Prepare failed for addUser: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->adduser->bind_param("ssss",$userid,$username,$email,$password))){
				$this->errorMessage =  "Binding parameters failed for addUser: (" . $this->adduser->errno . ") " . $this->adduser->error;
			}else if (!($value = $this->adduser->execute())){
				$this->errorMessage =  "Execute failed for addUser: (" . $this->adduser->errno . ") " . $this->adduser->error;
			}else{
				$this->errorMessage = "";
				return $this->dbconn->insert_id;
			}
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return false;
	}
		
}

?>
