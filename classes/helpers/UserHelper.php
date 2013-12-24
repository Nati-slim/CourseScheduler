<?php
//place this file in a directory not accessible over the internet
require_once dirname(__FILE__) . "/../../../../creds/coursepicker.inc";
require_once dirname(__FILE__) . "/../models/User.php";

class UserHelper{
	private $authenticateuser;
    private $updatepassword;
    private $checktokenexpiration;
    private $expireresettoken;
	private $checkuser;
	private $adduser;
	private $deleteuser;
    private $resetrequest;
    private $checkresettoken;
	private $checkactivationtoken;
	private $setverifiedstatus;
	private $setmetadata;
	private $truncatetable;
    private $dbconn;
    public  $infoMessage;
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
				$this->expireresettoken = $this->dbconn->prepare("update coursepicker_users_metadata set reset_expiration_date = NOW() where userid = ? and reset_token = ?");
                $this->authenticateuser = $this->dbconn->prepare("select * from coursepicker_users where username = ?");
                $this->checkuser = $this->dbconn->prepare("select * from coursepicker_users where username = ? and email = ?");
                $this->checkactivationtoken = $this->dbconn->prepare("select * from coursepicker_users where activation_token = ?");
                $this->checkresettoken = $this->dbconn->prepare("select * from coursepicker_users_metadata where reset_token = ? and userid = ?");
                $this->setverifiedstatus = $this->dbconn->prepare("update coursepicker_users set emailVerified = ? where username = ?"); 
				$this->updatepassword = $this->dbconn->prepare("update coursepicker_users set password = ? where id = ?"); 
				$this->setmetadata = $this->dbconn->prepare("insert into coursepicker_users_metadata (id, userid, activation_token, activation_date, activation_ip, login_attempts, login_after, reset_token, reset_expiration_date, reset_request_ip, resend_activation_ip) values (DEFAULT,?,?,NOW(),?,DEFAULT,DEFAULT,DEFAULT,DEFAULT,DEFAULT,DEFAULT)");
				$this->resetrequest = $this->dbconn->prepare("update coursepicker_users_metadata set reset_token = ?, reset_expiration_date = ?, reset_request_ip = ? where userid =?");
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
	
    /**
     * Function to save the metadata about the user
     * specifically: activation ip, user's id and their activation token
     * 
     * @param User $user User object
     * @param String $token the activation token
     * @param int $ip the ip the user activated their account from
     * @param tinyint $verifiedstatus used for updating the user's verified status in the user's table
     * 
     * @return true (i.e. the id of the created record in the metadata table. False otherwise
     * 
     */ 
    function saveMetadata($user,$token, $ip,$verifiedstatus){
		try{
			if (!($this->setmetadata)){
				$this->errorMessage =  "Prepare failed for saveMetadata: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}elseif (!($this->setmetadata->bind_param("dsd",$user->getId(),$token,$ip))){
				$this->errorMessage =  "Binding parameters failed for setmetadata: (" . $this->setmetadata->errno . ") " . $this->setmetadata->error;
			}elseif (!($value = $this->setmetadata->execute())){
				$this->errorMessage =  "Execute failed for setmetadata: (" . $this->setmetadata->errno . ") " . $this->setmetadata->error;
			}else{                
                $inserted_id = $this->dbconn->insert_id;
                $this->checkactivationtoken->free_result();
                if (!($this->setverifiedstatus)){
                    $this->errorMessage =  "Prepare failed for setverifiedstatus: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
                    return false;
                }elseif (!($this->setverifiedstatus->bind_param("ds",$verifiedstatus,$user->getUsername()))){
                    $this->errorMessage =  "Binding parameters failed for setverifiedstatus: (" . $this->setverifiedstatus->errno . ") " . $this->setverifiedstatus->error;
                    return false;
                }elseif (!($value = $this->setverifiedstatus->execute())){
                    $this->errorMessage =  "Execute failed for setverifiedstatus: (" . $this->setverifiedstatus->errno . ") " . $this->setverifiedstatus->error;
                    return false;
                }else{
                    $this->errorMessage = "";
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
     * @return User user object if found and null if not found.
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
					$user = new User($id,$userid,$name,$passhash,$email,$registration_date,$emailVerified,$firstname,$lastname);
                    $user->setRegistrationDate($registration_date);
                    $user->setRegistrationIP($registration_ip);
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

    
    /**
     * Used to verify the existence of the user when attempting to reset
     * a password or resend the confirmation email
     * 
     * @param String $username username
     * 
     * @return User user object if found and null if not found.
     * 
     */ 
	function getUser($username){
		try{
			if (!($this->authenticateuser)){
				$this->errorMessage = "Prepare for getuser failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->authenticateuser->bind_param("s",$username))){
				$this->errorMessage = "Binding parameters for getuser failed: (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else if (!($this->authenticateuser->execute())){
				$this->errorMessage = "Execute failed for getuser : (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else if (!(($stored = $this->authenticateuser->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch for getuser failed (STMT): (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else if (!($this->authenticateuser->bind_result($id,$userid,$firstname,$lastname,$name,$email,$emailVerified,$passhash,$registration_date,$activation_token,$registration_ip))){
				$this->errorMessage = "Binding for getuser results failed: (" . $this->authenticateuser->errno . ") " . $this->authenticateuser->error;
			}else{
				if ($this->authenticateuser->fetch() && !($this->dbconn->errno)){
					//$id,$userid,$username,$hash,$email,$date,$verified = 0,$firstname = "",$lastname = ""
					$user = new User($id,$userid,$name,$passhash,$email,$registration_date,$emailVerified,$firstname,$lastname);
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
				}
			}
			$this->authenticateuser->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return null;
	}	
    
    /**
     * Used to verify the existence of the user when attempting to reset
     * a password or resend the confirmation email
     * 
     * @param String $username username
     * @param String $email    email address
     * 
     * @return User user object if found and null if not found.
     * 
     */ 
	function checkUser($username,$email){
		try{
			if (!($this->checkuser)){
				$this->errorMessage = "Prepare for checkuser failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->checkuser->bind_param("ss",$username,$email))){
				$this->errorMessage = "Binding parameters for checkuser failed: (" . $this->checkuser->errno . ") " . $this->checkuser->error;
			}else if (!($this->checkuser->execute())){
				$this->errorMessage = "Execute failed for checkuser : (" . $this->checkuser->errno . ") " . $this->checkuser->error;
			}else if (!(($stored = $this->checkuser->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch for checkUser failed (STMT): (" . $this->checkuser->errno . ") " . $this->checkuser->error;
			}else if (!($this->checkuser->bind_result($id,$userid,$firstname,$lastname,$name,$email,$emailVerified,$passhash,$registration_date,$activation_token,$registration_ip))){
				$this->errorMessage = "Binding for checkuser results failed: (" . $this->checkuser->errno . ") " . $this->checkuser->error;
			}else{
				if ($this->checkuser->fetch() && !($this->dbconn->errno)){
					//$id,$userid,$username,$hash,$email,$date,$verified = 0,$firstname = "",$lastname = ""
					$user = new User($id,$userid,$name,$passhash,$email,$registration_date,$emailVerified,$firstname,$lastname);
                    $user->setRegistrationDate($registration_date);
                    $user->setRegistrationIP($registration_ip);
					if ($user){
						$this->errorMessage = "";
						return $user;
					}
				}
				if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->checkuser->errno . ") " . $this->checkuser->error;
					$this->errorMessage .= "Db error.";
				}
			}
			$this->checkuser->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return null;
	}	
    
    /**
     * Track token, ip and date for the reset request
     * 
     */ 
    public function logResetRequest($user,$token,$reset_ip,$reset_expiration){
		try{
			if (!($this->resetrequest)){
				$this->errorMessage =  "Prepare failed for resetrequest: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->resetrequest->bind_param("ssdd",$token,$reset_expiration,$reset_ip,$user->getId()))){
				$this->errorMessage =  "Binding parameters failed for resetrequest: (" . $this->resetrequest->errno . ") " . $this->resetrequest->error;
			}else if (!($value = $this->resetrequest->execute())){
				$this->errorMessage =  "Execute failed for resetrequest: (" . $this->resetrequest->errno . ") " . $this->resetrequest->error;
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
     * Expire reset token after its use
     * 
     */ 
    public function expireResetToken($user,$token){
		try{
			if (!($this->expireresettoken)){
				$this->errorMessage =  "Prepare failed for updatepassword: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->expireresettoken->bind_param("ds",$user->getId(),$token))){
				$this->errorMessage =  "Binding parameters failed for expireResetToken: (" . $this->expireresettoken->errno . ") " . $this->expireresettoken->error;
			}else if (!($value = $this->expireresettoken->execute())){
				$this->errorMessage =  "Execute failed for expireResetToken: (" . $this->expireresettoken->errno . ") " . $this->expireresettoken->error;
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
     * 
     */ 
    public function validateToken($user,$reset_token){
        $result = false;
		try{
			if (!($this->checkresettoken)){
				$this->errorMessage = "Prepare for checkresettoken failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->checkresettoken->bind_param("sd",$reset_token,$user->getId()))){
				$this->errorMessage = "Binding parameters for checkresettoken failed: (" . $this->checkresettoken->errno . ") " . $this->checkresettoken->error;
			}else if (!($this->checkresettoken->execute())){
				$this->errorMessage = "Execute failed for checkresettoken : (" . $this->checkresettoken->errno . ") " . $this->checkresettoken->error;
			}else if (!(($stored = $this->checkresettoken->store_result())) && $this->dbconn->errno){
				//switched from using fetch() to store_result() because of mysql error 2014 about commands being out of sync
				//storeresult buffers the fetched data
				$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				$this->errorMessage .= "Fetch for checkresettoken failed (STMT): (" . $this->checkresettoken->errno . ") " . $this->checkresettoken->error;
			}else if (!($this->checkresettoken->bind_result($id,$userid,$activation_token,$activation_date,$activation_ip,$login_attempts,$login_after,$reset_token,$reset_expiration_date,$reset_request_ip,$resend_activation_ip))){
				$this->errorMessage = "Binding for checkresettoken results failed: (" . $this->checkresettoken->errno . ") " . $this->checkresettoken->error;
			}else{
				if ($this->checkresettoken->fetch() && !($this->dbconn->errno)){
                    //check if expiration date is still valid
                    $today = new DateTime(null, new DateTimeZone('America/New_York'));//date('Y-m-d H:i:s');
                    $expdate = DateTime::createFromFormat('Y-m-d H:i:s',$reset_expiration_date);
					$result = ($today < $expdate);
                    //$this->infoMessage = $expdate . "-" . $today . " " . $reset_expiration_date;
				}
				if ($this->dbconn->errno){
					$this->errorMessage = "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
					$this->errorMessage .= "Fetch failed (STMT): (" . $this->checkresettoken->errno . ") " . $this->checkresettoken->error;
					$this->errorMessage .= "Db error.";
				}
			}
			$this->checkresettoken->free_result();
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
		return $result;
    }
    
    private function checkTokenExpiration($token){
        
    }
  
    /**
     * Update the password hash of the user
     * 
     * @param User $user user to update
     * @param String $hash the hashed password
     * 
     * @return boolean true if update succeeded; false otherwise.
     * 
     */ 
  	function updatePassword($user,$hash){
		try{
			if (!($this->updatepassword)){
				$this->errorMessage =  "Prepare failed for updatepassword: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->updatepassword->bind_param("sd",$hash,$user->getId()))){
				$this->errorMessage =  "Binding parameters failed for updatepassword: (" . $this->updatepassword->errno . ") " . $this->updatepassword->error;
			}else if (!($value = $this->updatepassword->execute())){
				$this->errorMessage =  "Execute failed for addUser: (" . $this->updatepassword->errno . ") " . $this->updatepassword->error;
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
     * Function to add a user to the database
     * 
     * @param String $userid alphanumeric string 32 bits in length
     * @param String $username alphanumeric string 8  - 50 chars in length
     * @param String $email  user's email address
     * @param String $password really the hash of the user's password
     * @param String $token Activation token
     * @param int    $ip ip the user signed up for the service from 
     * 
     * @return int the id of the newly created record in the Users table
     * 
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
