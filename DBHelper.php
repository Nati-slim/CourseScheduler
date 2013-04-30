<?php
require_once("../credentials.inc");
class DBHelper{
	private $listcourses;
	private $listsections;

//$host, $dbname, $user, $pass
	function __construct(){
		try {
			$this->dbconn = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
			if ($this->dbconn->connect_errno){
				echo "Failed to connect to MySQL: (" . $this->dbconn->connect_errno . ") " . $this->dbconn->error;
			}else{
				//echo $this->dbconn->host_info . "\n";
				$this->listcourses = $this->dbconn->prepare("SELECT * from Requirements where requirementId = ? and coursePrefix = ? and courseNumber = ?");
			}
		} catch(PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
		}
	}

	function getCourses($id,$prefix,$number){
		try{
			if (!($this->listcourses)){
				echo "Prepare failed: (" . $this->dbconn->errno . ") " . $this->dbconn->error;
			}else if (!($this->listcourses->bind_param("iss",$id,$prefix,$number))){
				echo "Binding parameters failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->execute())){
				echo "Execute failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->bind_result($requirementId,$coursePrefix,$courseNumber))){
				echo "Binding results failed: (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if (!($this->listcourses->fetch()) && $this->dbconn->errno){
				echo "Fetch failed (DB): (" . $this->dbconn->errno . ") " . $this->dbconn->error;
				echo "Fetch failed (STMT): (" . $this->listcourses->errno . ") " . $this->listcourses->error;
			}else if ($requirementId){
				//YAY!
				echo "Found: " . $requirementId . " " . $coursePrefix . " " . $courseNumber;
				$this->listcourses->close();
			}

		}catch(Exception $e){
			echo $e->getMessage();
		}
	}
}
?>
