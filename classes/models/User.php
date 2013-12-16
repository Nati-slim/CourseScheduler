<?php
/**
 * Class to represent a user of the web application
 */
class User{
	private $id;
	private $userid;
	private $username;
	private $hash;
	private $registration_date;
	private $emailVerified;
	private $firstname;
	private $lastname;
	private $email;
	private $schedules;
	public $errorMessage;

	function __construct($id,$userid,$username,$hash,$email,$date,$verified = 0,$firstname = "",$lastname = ""){
		try{
			$this->id = $id;
			$this->userid = $userid;
			$this->username = $username;
			$this->hash = $hash;
			$this->email = $email;
			$this->firstname = $firstname;
			$this->lastname = $lastname;
			$this->emailVerified = $verified;
			$this->registration_date = $date;
			//array of user schedule objects.
			$this->schedules = array();
			$this->errorMessage = "";
		}catch(Exception $e){
			$this->errorMessage = $e->getMessage();
		}
	}
	
	public static function makeUser($id,$userid,$username,$email,$date,$verified = 0,$firstname = "",$lastname = "",$userschedule){
		try{
			$obj = new User($id,$userid,$username,$email,$date,$verified,$firstname,$lastname);
			$obj->addSchedule($userschedule);
			return $obj;
		}catch(Exception $e){
			printf("Error instantiating user object: " . $e->getMessage() . "\n");
			exit();
		}
		return null;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getUserid(){
		return $this->userid;
	}
	
	public function getSchedules(){
		return $this->schedules;
	}
	
	public function addSchedule($schedule){
		if ($schedule){
			$this->schedules[$schedule->getScheduleID()] = $userschedule;
			return true;
		}
		return false;
	}
	
	public function setSchedule($userschedule){
		$this->schedules[$userschedule->getScheduleID()] = $userschedule;
	}
	
	public function getHash(){
		return $this->hash;
	}
	
	public function getEmail(){
		return $this->email;
	}
	
	public function getUsername(){
		return $this->username;
	}
	
	public function getFirstName(){
		return $this->firstname;
	}
	
	public function getLastName(){
		return $this->lastname;
	}
	
	public function getRegistrationDate(){
		return $this->registration_date;
	}
	
	public function to_json(){
		return json_encode($this->to_array());
	}
	public function to_array(){
		$result = array();
		$result['id'] = $this->id;
		$result['firstname'] = $this->firstname;
		$result['lastname'] = $this->lastname;
		$result['username'] = $this->username;
		$result['email'] = $this->email;
		$result['userid'] = $this->userid;
		$result['registration_date'] = $this->registration_date;
		return $result;
	}
}
?>
