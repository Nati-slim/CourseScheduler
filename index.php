<?php
require_once("classes/helpers/session.php");
include_once("../../creds/parse_coursepicker.inc");
error_reporting(E_ALL);
ini_set('display_errors', 1);
$session = new Session();
$controller = "classes/controllers/controller.php";
$errorMessage = $session->errorMessage;
$uga_file = file_get_contents("assets/json/uga_building_names.json");


/**
 * Function to undo effects of magic quotes
 * Returns the $_POST value matching the provided key
 * @param String $var key in $_POST variable
 * @return String $val value matching $_POST['key']
 */
function get_post_var($var){
	$val = filter_var($_POST[$var],FILTER_SANITIZE_MAGIC_QUOTES);
	return $val;
}

//Spring 2014 is default
if (isset($session->semesterSelected)){
	$semesterSelected = $session->semesterSelected;
}else{
	$semesterSelected = "201402-UNIV";
	$session->semesterSelected = $semesterSelected;
}

if (isset($session->jsonURL)){
	$jsonURL = $session->jsonURL;
}else{
	$jsonURL = "assets/json/tp/tp-201402-UNIV.json";
	$session->jsonURL = $jsonURL;
}


$semesters = array();
$semesters['201405-UNIV'] = '(Athens) Summer 2014';
$semesters['201402-UNIV'] = '(Athens) Spring 2014';
$semesters['201308-UNIV'] = '(Athens) Fall 2013';
$semesters['201305-UNIV'] = '(Athens) Summer 2013';
$semesters['201405-GWIN'] = '(Gwinnett) Summer 2014';
$semesters['201402-GWIN'] = '(Gwinnett) Spring 2014';
$semesters['201308-GWIN'] = '(Gwinnett) Fall 2013';
$semesters['201305-GWIN'] = '(Gwinnett) Summer 2013';


$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$semesterSelected = get_post_var('semesterSelection');
	if (array_key_exists($semesterSelected, $semesters)) {		
		$jsonURL =  "assets/json/tp/tp-" . $semesterSelected . ".json";
		$errorMessage = "";
	}else{
		$errorMessage = "Invalid selection";
		$semesterSelected = "201402-UNIV";
		$jsonURL = "assets/json/tp/tp-201402-UNIV.json";
	}
	$session->jsonURL = $jsonURL;
	$session->semesterSelected = $semesterSelected;
}

$sched = $session->schedule;
$sectionListingsJSON = $session->courseSectionsJSON;

if (!isset($sched)){
	$sched = "{}";
}

if (!isset($sectionListingsJSON)){
	$sectionListingsJSON = "{}";
}

//Page Data
$title = "Course Picker";
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
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!-- Bootstrap -->
		<link href="assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="assets/css/picker.css" rel="stylesheet">
		<link href="assets/css/sections.css" rel="stylesheet">
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
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="assets/js/canvasstyle.js" type="text/javascript"></script>
		<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>	
		<script src="http://twitter.github.com/hogan.js/builds/2.0.0/hogan-2.0.0.js" type="text/javascript"></script>
		<script src="http://www.parsecdn.com/js/parse-1.2.13.min.js" type="text/javascript"></script>

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
							echo "<li class=\"gravatar\"><img id=\"gravatarImg\" class=\"gravatar\" src=\"" . $session->gravatar_url . "\" alt=\"Gravatar image for " . $session->username . "\"  title=\"Gravatar image for " . $session->username . "\"/>";
							$submenu = "<ul id=\"submenu\" class=\"sub\" style=\"display:none;\">"
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
		    <div class="col-xs-6 col-md-3" id="leftdiv">
				<div class="row">
					<p id="infoMessage" class="alert alert-info" style="font-size:140%;font-weight:bold;color:white;background-color:#004A61">
						<?php echo $semesters[$semesterSelected]; ?>
					</p>
					<div class="sidebar">
						<?php if (strlen($errorMessage) > 0) { 
							echo "<script type=\"text/javascript\"> $('#errorMessage').show(); </script>";	
						?>
							<p id="errorMessage" class="alert alert-warning"><?php echo $errorMessage;?></p>
						<?php  }else if (strlen($errorMessage) == 0){	
							echo "<script type=\"text/javascript\"> $('#errorMessage').hide(); </script>";
						?>
							
						<?php } ?>
					</div>			
				
					<div class="sidebar" id="changeSemesterDiv">
						<span class="intro">Change Semester/Campus:</span><br/>
						<form id="semesterSelectionForm" name="semesterSelectionForm" method="post" action="index.php">
							<select class="form-control" id="semesterSelection" name="semesterSelection">
								<option value="0">Select Campus</option>
								<optgroup label="Athens Campus">
									<option value="201405-UNIV">Summer 2014</option>
									<option value="201402-UNIV">Spring 2014</option>
									<option value="201308-UNIV">Fall 2013</option>
									<option value="201305-UNIV">Summer 2013</option>		
								</optgroup>
								<optgroup label="Gwinnett Campus">
									<option value="201405-GWIN">Summer 2014</option>
									<option value="201402-GWIN">Spring 2014</option>
									<option value="201308-GWIN">Fall 2013</option>
									<option value="201305-GWIN">Summer 2013</option>		
								</optgroup>
							</select>
						</form>
					</div>
					
					<div id="searchBox" class="sidebar">
						<span class="intro">Search:</span><br/>		
						<input id="jsonURL" name="jsonURL" type="hidden" value="<?php echo $jsonURL;?>" />
						<input type="hidden" name="selectedSemester" id="selectedSemester" value="<?php echo $semesterSelected; ?>" />
						<input class="form-control" type="text" id="courseEntry" name="courseEntry" placeholder="e.g. CSCI 1302" />
					</div>

					<div id="controlCheckboxes" style="display:none;" class="checkboxes">
						<input checked type="checkbox" class="checkedElement" id="Available" name="Available" value="Available"/><span id="AvailableSpan">Available</span><br/>
						<input checked type="checkbox" class="checkedElement" id="Full" name="Full" value="Full"/><span id="FullSpan">Full</span><br/>
						<input checked type="checkbox" class="checkedElement" id="Cancelled" name="Cancelled" value="Cancelled"/><span id="CancelledSpan">Cancelled</span>
					</div>

					<div class="panel-group sidebar" id="sectionsFound">

					</div>

					<div class="sidebar" id="userSchedule" style="display:none;">

					</div>
					<script src="assets/js/typeahead.min.js" type="text/javascript"></script>
					<script type="text/javascript">
						/*http://stackoverflow.com/questions/18019653/typeahead-js-get-selected-datum
						https://github.com/twitter/typeahead.js#readme*/
						$(function(){
							$('#courseEntry').typeahead({
								name: 'courses',
								limit: 10,
								prefetch: $('#jsonURL').val(),                                     
								template: [                                                                 
									'<p class="tt-courseShortname">{{coursePrefix}}-{{courseNumber}}</p>',                         
									'<p class="tt-courseName">{{courseName}}</p>'                    
								].join(''),                                                                 
								engine: Hogan                
							}).on('typeahead:selected',function(obj,datum){
								//console.log(obj);
								//console.log(datum.value);
								var semSelected = $('#selectedSemester').val();
								var courseValue  = datum.value;
								$('body').css('cursor', 'wait');
								//console.log("semSelected: " + semSelected + "\n" + "courseValue: " + courseValue + "\n");
								$.ajax({
									type: "POST",
									url: 'classes/controllers/coursecontroller.php',
									data: { action : "getSections", semesterSelected : semSelected, courseEntry : courseValue},
									dataType: "json"
								})
								.done(function(msg){
									$('body').css('cursor', 'auto');
									//console.log(msg);
									sListings = msg;
									populateSections(msg);
								})
								.fail(function(msg){
									$('body').css('cursor', 'auto');
									console.log(msg + "Error getting sections.");
								});
							});
						});
					</script>
				</div><!--/sidebar-->
			</div>

			<div class="col-xs-12 col-md-9" id="canvasDiv">
    	  		<canvas id="scheduleCanvas" width="780" height="750">
				</canvas>
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
