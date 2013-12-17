<?php
require_once("classes/helpers/session.php");
require_once("classes/models/Course.php");
require_once("classes/models/Section.php");
require_once("classes/models/Meeting.php");
require_once("classes/models/UserSchedule.php");
include_once("../../creds/parse_coursepicker.inc");
require_once("../../creds/coursepicker_debug.inc");
$session = new Session();
$controller = "classes/controllers/schedulecontroller.php";
$errorMessage = $session->errorMessage;

//Needed for serialization/deserialization
function __autoload($class_name) {
    include "classes/models/". $class_name . '.php';
}


//This file should be bound to the User object which contains the
//schedules created by the user
$debug = DEBUGSTATUS;
//When not debugging, log to file
if (!$debug){
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", "syslog");
}

function fail($pub, $pvt = ''){
	global $debug;
	$msg = $pub;
	if ($debug && $pvt !== '')
		$msg .= ": $pvt";
/* The $pvt debugging messages may contain characters that would need to be
 * quoted if we were producing HTML output, like we would be in a real app,
 * but we're using text/plain here.  Also, $debug is meant to be disabled on
 * a "production install" to avoid leaking server setup details. */
	//exit("An error occurred ($msg).\n");
	return $msg;
}
/**
 * Function to undo effects of magic quotes
 * Returns the $_POST value matching the provided key
 * @param String $var key in $_POST variable
 * @return String $val value matching $_POST['key']
 */
function getPost($var){
	$val = filter_var($_POST[$var],FILTER_SANITIZE_MAGIC_QUOTES);
	return $val;
}


$user = unserialize($session->loggedInUser);
if (!isset($session->defaultSchedule)){
    $session->defaultSchedule = unserialize($session->scheduleObj);
}


if ($user){
    //has all the schedules that the user was working with.
	$schedule = $user->getSchedules();
	//get latest schedule
	$sched = $session->schedule;
	$sectionListingsJSON = $session->courseSectionsJSON;	
    
    if (strcmp($user->getUserid(), $session->userid) != 0){
        echo "Mismatched user ids found. Uh oh";
    }
}

//print_r($session->defaultSchedule);
	
if (!isset($sched)){
	$sched = "{}";
}

if (!isset($sectionListingsJSON)){
	$sectionListingsJSON = "{}";
}


$uga_file = file_get_contents("assets/json/uga_building_names.json");
//echo count($schedule);
//print_r($schedule);

$title = "Course Picker - Save Your Schedule";
$longdesc = "";
$shortdesc = "A course scheuling app for the University of Georgia Computer Science students";
$asseturl = "http://apps.janeullah.com/coursepicker/assets";
$officialurl = "http://apps.janeullah.com/coursepicker/";
$captchaurl = "../../creds/captcha.inc";
$recaptchaurl = "../../auth/recaptcha/recaptchalib.php";
$emailurl = "classes/controllers/auth.php";
$oglocale = "en_US";
$ogtitle = "Course Picker by Jane Ullah";
$creator = "@janetalkstech";
$coursepicker = "@coursepicker";
$ogimg = "http://apps.janeullah.com/coursepicker/assets/img/coursepicker.png";
$ogdesc = "Plan your college schedule with ease using this course schedule application. Geared towards UGA students, this application includes course info from both Athens and Gwinnett campuses.";
?>
<!DOCTYPE html>
<html lang="en">
	 <head>
		<meta charset="utf-8">
		<title><?php echo $title;?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="<?php echo $shortdesc; ?>">
		<meta name="author" content="Jane Ullah">
		<!-- FB metadata -->
		<meta property="og:locale" content="<?php echo $oglocale;?>" />
		<meta property="og:title" content="<?php echo $ogtitle; ?>" />
		<meta property="og:description" content="<?php echo $ogdesc; ?>"/>
		<meta property="og:url" content="<?php echo $officialurl;?>" />
		<meta property="og:site_name" content="<?php echo $ogtitle; ?>" />
		<meta property="og:image" content="<?php echo $ogimg; ?>" />    

		<!-- Twitter Card -->
		<meta name="twitter:card" content="summary">
		<meta name="twitter:site" content="<?php echo $coursepicker; ?>">
		<meta name="twitter:title" content="<?php echo $ogtitle;?>">
		<meta name="twitter:creator" content="<?php echo $creator; ?>">
		<meta name="twitter:description" content="<?php echo $ogdesc;?>">
		<meta name="twitter:image:src" content="<?php echo $ogimg;?>">
		
		<meta itemprop="name" content="<?php echo $ogtitle; ?>">
		<meta itemprop="description" content="<?php echo $ogdesc;?>">
		<meta itemprop="image" content="<?php echo $ogimg; ?>">

		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
		<script type="text/javascript">
			<?php
				try{
					echo "var sched = '". $sched . "';";
					echo "var sListings = '". $sectionListingsJSON . "';";
				}catch(Exception $e){
					echo "console.log(\"Problem getting schedule.\");";
				}
			?>
			var schedule = null;
		</script>
		
		<!-- Bootstrap -->
		<link href="assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="assets/css/picker.css" rel="stylesheet">
		<link href="assets/css/typeahead.js-bootstrap.css" rel="stylesheet">
		<link href="assets/css/tt-suggestions.css" rel="stylesheet">
		<link href="assets/css/signin.css" rel="stylesheet">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
		<?php if ($user && $schedule) { ?>
		<script src="assets/js/canvasstyle.js" type="text/javascript"></script>
		<?php } ?>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>	
		<script src="http://twitter.github.com/hogan.js/builds/2.0.0/hogan-2.0.0.js" type="text/javascript"></script>

		<!--JS handling saving, sharing, downloading schedules -->
		<script src="assets/js/schedule.js" type="text/javascript"></script>
		<!--JS related to the dynamic addition of the course navigation elements -->
		<script src="assets/js/drawings.js" type="text/javascript"></script>
		<!--JS related to the signup/login functions -->
		<script src="assets/js/register.js" type="text/javascript"></script>
		<?php echo "<script type=\"text/javascript\"> var uga_buildings = $.parseJSON(" . json_encode($uga_file) . "); </script>"; ?>

	</head>
	<body>
		<div class="navbar navbar-fixed-top navbar-inverse" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="./">CoursePicker</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active"><a href="./">Home</a></li>
						<li><a href="#aboutModal" data-toggle="modal" id="about">About</a></li>
						<li><a href="#contact" id="contact">Contact</a></li>
						<?php if (isset($session->userid)){ 
							echo "<li><a id=\"downloadSchedule\" href=\"#pngModal\" data-toggle=\"modal\">Download Schedule</a></li>";
						}
						?>
					</ul>
					<ul id="social" class="nav navbar-nav navbar-right">
						<!-- If session exists and user is logged in-->
						<?php if (isset($session->loggedIn) && $session->loggedIn){ 
							$submenu = "<li class=\"dropdown gravatar\">";
							$submenu .= "<a id=\"menuLi\" href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">";
							$submenu .= "<img id=\"gravatarImg\" class=\"gravatar\" src=\"" . $session->gravatar_url . "\" alt=\"Gravatar image for " . $session->username;
							$submenu .= "\"  title=\"Gravatar image for " . $session->username . "\"/><b class=\"caret\" style=\"float:right;\"></b></a>";
							$submenu .= "<ul id=\"menuDropdown\" class=\"dropdown-menu\">"
										. "<li id=\"welcome\">Welcome, " .  $session->username. "</li>"
										. "<li id=\"logoutLi\"><a href=\"#logout\" onclick=\"logout()\">Logout</a></li>"
										."</ul>"
										."</li>";
							echo $submenu;
						} else {
							echo "<li id=\"signupLi\"><a id=\"signup\" data-toggle=\"modal\" href=\"#signupModal\">Sign Up</a></li>";						
							echo "<li id=\"loginLi\"><a id=\"login\" data-toggle=\"modal\" href=\"#loginModal\">Log In</a></li>";
						} ?>
						
						<!--<li><a id="facebook" href="https://facebook.com/janetalkstech" title="Connect with Jane Ullah on Facebook!">F</a></li>
						<li><a id="twitter" href="https://twitter.com/janetalkstech" title="Connect with Jane Ullah on Twitter!">T</a></li>
						<li><a id="google" href="https://plus.google.com/+JaneUllah" title="Connect with Jane Ullah on Googl+">G</a></li>-->
					</ul>				  
				</div><!-- /.nav-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->

		<div class="container">
			<div class="row" style="margin-top:25px;">
				<div class="col-xs-8 col-md-4" id="canvasDiv">
					<?php if ($user && $schedule) { ?>                        
						<form id="saveScheduleForm" name="saveScheduleForm" class="form-signin" role="form" method="post" action="classes/controllers/writecontroller.php">							
							<div class="form-group">
                                <?php if (isset($session->optionChosen)) { 
                                    $msg = "<div id=\"scheduleSelected\" class=\"alert alert-info\">";
                                    $msg .= $session->optionChosen;
                                    $msg .= "</div>";
                                    echo $msg;
                                }
                                ?>
                                <label for="selectedSchedule">Choose Schedule Version</label>
								<select class="form-control" id="selectedSchedule" name="selectedSchedule">
									<?php 
										$counter = 1;
										foreach($schedule as $key=>$value){
											echo "<option value=\"" . $key . "\"> Version #" . $counter . "</option>";
											$counter++;								
										}
									?>
								</select>
							</div>
							
							<div class="alert alert-danger" id="saveScheduleError" style="display:none"></div>
							<div class="alert alert-success" id="saveScheduleSuccess" style="display:none"></div>
							<div class="form-group">
								<label for="shortName1">Name Your Schedule!</label>
								<input type="text" class="form-control" id="shortName1" name="shortName1" placeholder="Enter:" required>
							</div>
							<div class="form-group">
								<label for="shortName2">Re-enter schedule  name</label>
								<input type="text" class="form-control" id="shortName2" name="shortName2" placeholder="Enter:" required>
							</div>
							<input type="hidden" id="scheduleID" name="scheduleID" value="<?php echo $session->defaultSchedule->getScheduleID(); ?>" />
							<input type="hidden" id="action" name="action" value="saveSchedule" />
							<button type="submit" class="btn btn-primary">Save</button>
							<button type="button" class="btn btn-default">Clear</button>
						</form>
					<?php } else { ?>
						<p class="alert alert-info">Please sign up first to save your created schedule. If you have already created an account, please login.</p>
					<?php } ?>
				</div>
				
				<div class="col-xs-12 col-md-8" id="canvasDiv">
					<?php if ($user && $schedule) { ?>
						<canvas id="scheduleCanvas" width="780" height="750">
						</canvas>
					<?php } ?>
				</div>

			</div><!--/row-->


			<hr>

			<footer>
				<p>&copy; Jane Ullah 2014</p>
			</footer>

		</div><!--/.container-->
    <?php require_once("includes/dialogs.inc") ?>	
    <?php require_once("includes/analyticstracking.inc") ?>
  </body>
</html>
