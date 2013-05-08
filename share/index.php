<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com",false,true);
session_name('CoursePicker');
session_start();
$controller = "../classes/controllers/sharingcontroller.php";
/**
 * http://php.net/manual/en/language.oop5.autoload.php
 * Autoload the class files for deserializing
 */
function __autoload($class_name) {
    include "../classes/helpers/" . $class_name . '.php';
}
$schedule = $_SESSION['schedule'][$_SESSION['userid']];
//echo $courseName;
$msg = $_SESSION['errorMessage'];
$title = "Course Picker";
$longdesc = "";
$shortdesc = "A course scheuling app for the University of Georgia Computer Science students";
$asseturl = "http://apps.janeullah.com/coursepicker/assets";
$captchaurl = "../../../creds/captcha.inc";
$recaptchaurl = "../../../auth/recaptcha/recaptchalib.php";
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Your Schedule</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="A course scheuling app for the University of Georgia Computer Science students">
		<meta name="author" content="Jane Ullah">
		<script type="text/javascript">
			<?php
				try{
					echo "var sched = '".$schedule."';";
				}catch(Exception $e){
					echo "console.log(\"Problem getting schedule.\");";
				}
			?>
		</script>
		<?php require_once("../includes/resources.inc"); ?>
	</head>
	<body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="http://apps.janeullah.com/coursepicker">Course Picker</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="http://apps.janeullah.com/coursepicker/schedule.php">Home</a></li>
              <li><a href="#aboutModal" data-toggle="modal">About</a></li>
              <li><a href="#contactModal" data-toggle="modal">Contact</a></li>
              <li><a href="#pngModal" data-toggle="modal">Download Schedule</a></li>
              <!--<li><a href="#" id="saveSchedule">Save Schedule</a></li></li>-->
              <li><a href="#shareModal" id="shareSchedule"  data-toggle="modal">Share Schedule</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">
		<div class="row-fluid">
			<div class="span9" id="canvasDiv">
				<canvas id="scheduleCanvas" width="780" height="750">
				</canvas>
			</div>
		</div>
	</div><!-- /container -->
	<?php require_once("../includes/footer.inc") ?>
	<?php include_once("../includes/analyticstracking.php") ?>
	</body>
</html>
