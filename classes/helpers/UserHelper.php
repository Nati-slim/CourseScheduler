<?php
//place this file in a directory not accessible over the internet
require_once dirname(__FILE__) . "/../../../../creds/coursepicker.inc";
require_once dirname(__FILE__) . "/../models/User.php";

class UserHelper{
	private $authenticateuser;
	private $adduser;
	private $deleteuser;
	private $checkactivationtoken;
	private $setverifiedstatus;
	private $setmetadata;
	private $truncatetable;
    private $dbconn;
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
                $this->checkactivationtoken = $this->dbconn->prepare("select * from coursepicker_users where activation_token = ?");
                $this->setverifiedstatus = $this->dbconn->prepare("update coursepicker_users set emailVerified = ? where username = ?"); 
				$this->setmetadata = $this->dbconn->prepare("insert into coursepicker_users_metadata (id, userid, activation_token, activation_date, activation_ip, login_attempts, login_after, reset_token, reset_expiration_date, reset_request_ip, resend_activation_ip) values (DEFAULT,?,?,NOW(),?,DEFAULT,DEFAULT,DEFAULT,DEFAULT,DEFAULT,DEFAULT)");
				$this->adduser = $this->dbconn->prepare("insert into coursepicker_users (id,userid,firstname,lastname,username,email,emailVerified,password,registration_date,activation_token,registration_ip) values(DEFAULT,?,DEFAULT,DEFAULT,?,?,DEFAULT,?,NOW(),?,?)");
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
				$this->errorMessage =  "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($value = $this->truncatetable->execute())){
				$this->errorMessage =  "Execute failed: (" . $this->truncatetable->errno . ") " . $this->truncatetable->error;
			}else{
				$this->errorMessage = "";
			}
		}catch(Exception $e){
			$this->errorMessage = "Error with clearTable: " . $e->getMessage();
		}
	}
	
    function saveMetadata($user,$token, $ip,$verifiedstatus = 1){
		try{
			if (!($this->setmetadata)){
				$this->errorMessage =  "Prepare failed for saveMetadata: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}elseif (!($this->setmetadata->bind_param("dsd",$user->getId(),$token,$ip))){
				$this->errorMessage =  "Binding parameters failed for setmetadata: (" . $this->setmetadata->errno . ") " . $this->setmetadata->error;
			}elseif (!($value = $this->setmetadata->execute())){
				$this->errorMessage =  "Execute failed for setmetadata: (" . $this->setmetadata->errno . ") " . $this->setmetadata->error;
			}else{
				$this->errorMessage = "";
                $inserted_id = $this->dbconn->insert_id;
                if (!($this->setverifiedstatus)){
                    $this->errorMessage =  "Prepare failed for setverifiedstatus: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                }elseif (!($this->setverifiedstatus->bind_param("ds",$verifiedstatus,$user->getUsername()))){
                    $this->errorMessage =  "Binding parameters failed for setverifiedstatus: (" . $this->setverifiedstatus->errno . ") " . $this->setverifiedstatus->error;
                }elseif (!($value = $this->setverifiedstatus->execute())){
                    $this->errorMessage =  "Execute failed for setverifiedstatus: (" . $this->setverifiedstatus->errno . ") " . $this->setverifiedstatus->error;
                }else{
                    return $inserted_id;
                }
			}
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return false;
    }
	
    
    /**
     * Method to return the user to be activated
     * 
     * @param string $token 
     * @param string $presentedEmail
     * 
     */ 
    public function findUserToBeActivated($token,$presentedEmail){
        try{
            if (!($this->checkactivationtoken)){
                $this->errorMessage = "Prepare failed for checkactivationtoken: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
            }elseif (!$this->checkactivationtoken->bind_param('s',$token)){
                $this->errorMessage = "Binding parameters for checkactivationtoken failed: (" . $this->checkactivationtoken->errno . ") " . $this->checkactivationtoken->error;
            }elseif (!($this->checkactivationtoken->execute())){
                $this->errorMessage = "Execute failed for checkactivationtoken : (" . $this->checkactivationtoken->errno . ") " . $this->checkactivationtoken->error;            
            }else if (!(($stored = $this->checkactivationtoken->store_result())) && $this->dbconn->errno){
                //switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
                //storeresult buffers the fetched data
                $this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                $this->errorMessage .= "Fetch for checkactivationtoken failed (STMT): (" . $this->checkactivationtoken->errno . ") " . $this->checkactivationtoken->error;
            }else if (!($this->checkactivationtoken->bind_result($id,$userid,$firstname,$lastname,$name,$email,$emailVerified,$passhash,$registration_date,$activation_token,$registration_ip))){
                $this->errorMessage = "Binding for checkactivationtoken results failed: (" . $this->checkactivationtoken->errno . ") " . $this->checkactivationtoken->error;
            }else{
                if ($this->checkactivationtoken->fetch() && !($this->dbconn->errno)){
                    //$id,$userid,$username,$hash,$email,$date,$verified = 0,$firstname = "",$lastname = ""
					$user = new User($id,$userid,$username,$passhash,$email,$registration_date,$emailVerified,$firstname,$lastname);
					if ($user){
                        //compare emails
                        if ($email === $presentedEmail){
                            $this->errorMessage = "";
                            return $user;
                        }else{
                            $this->errorMessage = "Not authorized to validate this account.";
                        }
					}else{
                        $this->errorMessage = "User or token not found.";
                    }
                }
                
                if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->checkactivationtoken->errno . ") " . $this->checkactivationtoken->error;
				}else{
                    if (strlen($this->errorMessage) == 0){
                        $this->errorMessage = "User or token not found.";
                    }
				}
            }            
            $this->checkactivationtoken->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
        return null;
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
			}else if (!($this->authenticateuser->bind_result($id,$userid,$firstname,$lastname,$name,$email,$emailVerified,$passhash,$registration_date,$activation_token,$registration_ip))){
				$this->errorMessage = "Binding for gethash results failed: (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else{
				if ($this->authenticateuser->fetch() && !($this->dbconn->errno)){
					//$id,$userid,$username,$hash,$email,$date,$verified = 0,$firstname = "",$lastname = ""
					$user = new User($id,$userid,$username,$passhash,$email,$registration_date,$emailVerified,$firstname,$lastname);
                    $user->setRegistrationDate($registration_date);
                    $user->setRegistrationIP($registration_ip);
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
	function addUser($userid,$username,$email,$password,$token,$ip){
		try{
			if (!($this->adduser)){
				$this->errorMessage =  "Prepare failed for addUser: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->adduser->bind_param("sssssd",$userid,$username,$email,$password,$token,$ip))){
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
