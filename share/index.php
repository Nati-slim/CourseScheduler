<?php
require_once '../classes/helpers/session.php';
require_once '../classes/models/Course.php';
require_once '../classes/models/Section.php';
require_once '../classes/models/Meeting.php';
require_once '../classes/models/UserSchedule.php';
require_once dirname(__FILE__) . '/../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../../creds/dhpath.inc';
$session = new Session();
$errorMessage = $session->errorMessage;

//Needed for serialization/deserialization
function __autoload($class_name) {
    include "../classes/models/". $class_name . '.php';
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

//By loading this page, user should have an id in the url

//$defaultSchedule = unserialize($session->scheduleObject);
//testing//
$defaultSchedule = unserialize($session->scheduleObj);
//testing//
$sched = $session->schedule;
if (!isset($sched)){
	$sched = "{}";
}

//Page Data
$title = "Course Picker - Viewing Schedule";
$author = "Jane Ullah";
$shortdesc = "A course scheduling app for the University of Georgia (UGA) Computer Science students";
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
$ogdesc = "Plan your UGA class schedule with ease using this course scheduling application. Geared towards UGA students, this application includes course info from both Athens and Gwinnett campuses to let you create the perfect class schedule.";
?>
<!DOCTYPE html>
<html lang="en">
	 <head>
		<meta charset="utf-8">
		<title><?php echo $title;?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="<?php echo $shortdesc; ?>">
		<meta name="author" content="<?php echo $author;?>">
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

		<script type="text/javascript">
           
			<?php
				try{
					echo "var sched = '". $sched . "';";
				}catch(Exception $e){
					echo "console.log(\"Problem getting schedule.\");";
				}
			?>
		</script>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="../assets/css/picker.css" rel="stylesheet">
		<link href="../assets/css/signin.css" rel="stylesheet">

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
        <!--<script type="text/javascript">
            var node = document.createElement('script');
            node.type = 'text/javascript';
            node.async = true;
            node.src = 'assets/js/draggable.js';
            $('.container').append(node);
            // Now insert the node into the DOM, perhaps using insertBefore()
        </script>-->
		<!-- Include all compiled plugins (below), or include individual files as needed -->
        <?php if ($defaultSchedule && $defaultSchedule instanceOf UserSchedule) { ?> 	
            <script src="http://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.4.0/fabric.min.js" type="text/javascript"></script>  
            <script src="../assets/js/coursepicker.js" type="text/javascript"></script>
            <script src="../assets/js/schedule.js" type="text/javascript"></script>
        <?php } ?>
		<script src="../assets/js/bootstrap.min.js" type="text/javascript"></script>	
		<!--JS related to the signup/login functions -->
		<script src="../assets/js/register.js" type="text/javascript"></script>
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
					<a class="navbar-brand" href="../">CoursePicker</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="active"><a href="./">Home</a></li>
						<li><a href="#aboutModal" data-toggle="modal" id="about">About</a></li>
						<li><a href="#howtoModal" data-toggle="modal" id="howto">How To</a></li>
                        <?php if ($defaultSchedule && $defaultSchedule instanceOf UserSchedule) { ?> 	
                            <li><a href="#pngModal" data-toggle="modal" id="downloadSchedule" >Download Schedule</a></li>
                        <?php } ?>
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
										. "<li id=\"logoutLi\"><a href=\"#logout\" title=\"Click to log out!\" onclick=\"logout()\">Logout</a></li>"
										."</ul>"
										."</li>";
							echo $submenu;
						} else {
							echo "<li id=\"signupLi\"><a id=\"signup\" data-toggle=\"modal\" href=\"#signupModal\">Sign Up</a></li>";						
							echo "<li id=\"loginLi\"><a id=\"login\" data-toggle=\"modal\" href=\"#loginModal\">Log In</a></li>";
						} ?>
						
					</ul>				  
				</div><!-- /.nav-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->

    <div class="container">
    	<div class="row" style="margin-top:25px;">
            <?php if ($defaultSchedule && $defaultSchedule instanceOf UserSchedule) { ?> 	
                <div class="col-xs-6 col-sm-6 col-md-3" id="leftdiv">
                    
                </div>

                <div class="col-xs-12 col-md-9 drop" id="canvasDiv">
                    <canvas id="scheduleCanvas" width="780" height="750">
                    </canvas>
                </div>
            <?php } else { ?>
                <p id="infoMessage" class="alert alert-info">Looks like this isn't a valid schedule. Check the id or simply create one for yourself by visiting <a href="http://apps.janeullah.com/coursepicker" title="Course Picker">Course Picker</a> to get started.</p>
           <?php } ?>

		</div><!--/row-->


      	<hr>

        <footer>
            <p>&copy; <a href="http://janeullah.com" title="Jane Ullah">Jane Ullah 2014</a></p>
            <img src="assets/img/trash.jpg" style="visibility:hidden;"  width="38" height="30" alt="Drag a rectangle over to the trashcan to delete the section from your schedule." id="trashcan">
        
        </footer>

    </div><!--/.container-->
    <?php require_once("../includes/dialogs.inc") ?>	
    <?php require_once("../includes/analyticstracking.inc") ?>
  </body>
</html>
