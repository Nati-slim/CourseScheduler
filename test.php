<?php
require_once dirname(__FILE__) . '/classes/helpers/session.php';
require_once dirname(__FILE__) . '/../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../creds/mixpanel_coursepicker.inc';
require_once dirname(__FILE__) . '/../../creds/dhpath.inc';
$session = new Session();
$controller = "classes/controllers/controller.php";
$errorMessage = $session->errorMessage;

$debug = DEBUGSTATUS;
if ($debug){
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
}

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
$session->defaultSchedule = unserialize($session->scheduleObj);
if (!isset($sched)){
	$sched = "{}";
}

if (!isset($sectionListingsJSON)){
	$sectionListingsJSON = "{}";
}

//Page Data
$title = "Course Picker";
$longdesc = "";
$shortdesc = "A course scheuling app for the University of Georgia (UGA) Computer Science students";
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
		<!-- Bootstrap -->
		<link href="assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="assets/css/picker.css" rel="stylesheet">

        <style>
        body {
          padding-top: 40px;
          padding-bottom: 40px;
        }

        </style>
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
		<!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="http://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.4.0/fabric.min.js" type="text/javascript"></script>
		<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>	
		<script src="assets/js/coursepicker.js" type="text/javascript"></script>	
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
				</div><!-- /.nav-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->

    <div class="container">
    	<div class="row" style="margin-top:25px;">

			<div class="col-xs-12 col-md-9 drop" id="canvasDiv">
    	  		<canvas id="scheduleCanvas" width="780" height="750">
				</canvas>
			</div>

		</div><!--/row-->


      	<hr>

        <footer>
            <p>&copy; <a href="http://janeullah.com" title="Jane Ullah">Jane Ullah 2014</a></p>
        </footer>

    </div><!--/.container-->
    <?php require_once("includes/dialogs.inc") ?>	
    <?php require_once("includes/analyticstracking.inc") ?>
  </body>
</html>
