<?php
ini_set('max_execution_time', 600);
require_once('../helpers/CourseHelper.php');
require_once('../helpers/Section.php');
require_once('../helpers/Course.php');
require_once('../helpers/Meeting.php');

$db = new CourseHelper();
$sections = array();

?>
