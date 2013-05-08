<?php
	//Tester for DBHelper.php
	require_once("../helpers/Course.php");
	require_once("../helpers/Section.php");
	require_once("../helpers/Meeting.php");
	require_once("../helpers/DBHelper.php");
	//Testing dbhelper
	$db = new DBHelper();
	$db->clearTable();
	$id = "jem4mEAXjU8dGqEYmwerwrerwygtCqw2XTBM0MTVcEVg3w5VQFlZ8yb";
	$lv = $db->findLastSavedVersion("jem4mEAXjU8dGqEYmkobygtCqw2XTBM0MTVcEVg3w5VQFlZ8yb");
	echo $lv . "*\n";
	echo $db->saveSchedule(1,$id,"ben") , "\n";
	echo $db->saveSchedule(2,$id,"benn") , "\n";
	$lv = $db->findLastSavedVersion($id);
	echo $lv ."\n";
?>
