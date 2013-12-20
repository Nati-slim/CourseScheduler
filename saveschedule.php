<?php
require_once dirname(__FILE__) . '/classes/helpers/session.php';
require_once dirname(__FILE__) . '/classes/models/Course.php';
require_once dirname(__FILE__) . '/classes/models/Section.php';
require_once dirname(__FILE__) . '/classes/models/Meeting.php';
require_once dirname(__FILE__) . '/classes/models/UserSchedule.php';
require_once dirname(__FILE__) . '/../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../creds/dhpath.inc';
$session = new Session();
$controller = dirname(__FILE__) . '/classes/controllers/schedulecontroller.php';
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
    ini_set("error_log", ERROR_PATH);
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
$session->defaultSchedule = unserialize($session->scheduleObj);
$currentSchedule = $session->defaultSchedule;


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
        <meta name="robots" content="noindex">

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
            <script src="http://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.4.0/fabric.min.js" type="text/javascript"></script>  
            <script src="assets/js/coursepicker.js" type="text/javascript"></script>
        <?php } ?>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>	
		<!--JS handling saving, sharing, downloading schedules -->
		<script src="assets/js/schedule.js" type="text/javascript"></script>
		<!--JS related to the signup/login functions -->
		<script src="assets/js/register.js" type="text/javascript"></script>

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
						<?php if ($schedule){ 
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
										. "<li id=\"saveScheduleLi\"><a href=\"http://apps.janeullah.com/coursepicker/saveschedule.php\" title=\"Click to save your created schedules.\">Save Schedule</a></li>"
										. "<li id=\"logoutLi\"><a href=\"#logout\" onclick=\"logout()\">Logout</a></li>"
										."</ul>"
										."</li>";
							echo $submenu;
						}?>
					</ul>				  
				</div><!-- /.nav-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->

		<div class="container">
			<div class="row" style="margin-top:25px;">                
                <?php if ($user && $schedule) { ?>         
                    <div class="col-xs-8 col-md-4" id="sidebar">               
						<form id="saveScheduleForm" name="saveScheduleForm" class="form-signin" role="form" method="post" action="classes/controllers/writecontroller.php">							
							<div class="form-group">
                                <?php if (isset($session->optionChosen)) { 
                                    $msg = "<div id=\"scheduleSelected\" class=\"alert alert-info\">";
                                    $msg .= $session->optionChosen;
                                    $msg .= " selected</div>";
                                    echo $msg;
                                }
                                ?>
                                <label for="selectedSchedule">Choose Schedule Version</label>
								<select class="form-control" id="selectedSchedule" name="selectedSchedule">
									<?php 
										$counter = 1;
                                        echo "<option value=\"0\">Choose Schedule Version</option>";
										foreach($schedule as $key=>$value){
											echo "<option value=\"" . $key . "\"> Version #" . $counter . "</option>";
											$counter++;								
										}
									?>
								</select>
                                <script type="text/javascript">
                                    var value = <?php echo "'" . $session->selectedScheduleID . "'";?>;
                                    if (value == ''){
                                        value = 0;
                                    }
                                    $('#selectedSchedule').val(value);
                                </script>
							</div>
							<a style="display:none;" id="popoverOption" class="btn" href="#" data-content="Popup with option trigger" rel="popover" data-placement="bottom" data-original-title="Popover test">Popup with option trigger</a>
							<div class="alert alert-danger" id="saveScheduleError" style="display:none"></div>
							<div class="alert alert-success" id="saveScheduleSuccess" style="display:none"></div>
                            <!-- Setting the hidden input field used for error checking-->
                            <?php if (isset($session->selectedScheduleID)){
                                echo "<input type=\"hidden\" id=\"scheduleID\" name=\"scheduleID\" value=\"" . $session->selectedScheduleID. "\" />"; 
                            }else if (isset($session->defaultSchedule)) { 
                                echo "<input type=\"hidden\" id=\"scheduleID\" name=\"scheduleID\" value=\"" . $session->defaultSchedule->getScheduleID(). "\" />"; 
                            } ?>
                            
                            <!-- For now, prevent user from changing the short name. instead display what they have already-->
                            <?php                                
                                if ($currentSchedule->isSaved()) { ?>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="savedShortName" name="savedShortName" 
                                        value="<?php echo $currentSchedule->getShortName(); ?>">
                                    </div>
                                    <input type="hidden" id="action" name="action" value="updateSchedule" />
                                    <button id="updateScheduleBtn" type="button" onclick="updateSchedule()" class="btn btn-primary">Update</button>
                            <?php } else { ?>
                                <div class="form-group">
                                    <label for="shortName1">Name Your Schedule!</label>
                                    <input type="text" class="form-control" id="shortName1" name="shortName1" placeholder="Enter a short name:" required>
                                </div>
                                <div class="form-group">
                                    <label for="shortName2">Re-enter schedule  name</label>
                                    <input type="text" class="form-control" id="shortName2" name="shortName2" placeholder="Enter a short name:" required>
                                </div>

                                <input type="hidden" id="action" name="action" value="saveSchedule" />
                                <button id="saveScheduleBtn" type="submit" class="btn btn-primary">Save</button>
                                <button type="button" class="btn btn-default">Clear</button>                                
                            <?php } ?>
						</form>                    
                    </div>
                    
                    <div class="col-xs-12 col-md-8" id="canvasDiv">
                        <canvas id="scheduleCanvas" width="780" height="750">
                        </canvas>
                    </div>
                <?php } elseif ($user) { ?>
                    <p id="infoMessage" class="alert alert-info">Looks like you don't have any schedules created! Please visit <a href="http://apps.janeullah.com/coursepicker" title="Course Picker">Course Picker</a> to get started.</p>
                <?php } else { ?>
                    <p id="infoMessage" class="alert alert-info">Please visit <a href="http://apps.janeullah.com/coursepicker" title="Course Picker">Course Picker</a> to create an account in order to save your created schedule. If you have already created an account, please login to <a href="http://apps.janeullah.com/coursepicker" title="UGA Course Picker">Course Picker</a>.</p>
                <?php } ?>
                
			</div><!--/row-->


			<hr>

        <footer>
            <p>&copy; <a href="http://janeullah.com" title="Jane Ullah">Jane Ullah 2014</a></p>
            <img src="assets/img/trash.jpg" alt="Drag a rectangle over to the trashcan to delete the section from your schedule." id="trashcan">
        </footer>

		</div><!--/.container-->
    <?php require_once("includes/dialogs.inc") ?>	
    <?php require_once("includes/analyticstracking.inc") ?>
  </body>
</html>
