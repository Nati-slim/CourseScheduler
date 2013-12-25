<?php
require_once 'classes/helpers/session.php';
$session = new Session();
$errorMessage = $session->errorMessage;
$title = "Course Picker - About";
$shortdesc = "Learn how to use Course Picker, a web application for planning the perfect class schedule.";
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
						<li><a href="./about.php" id="about">About</a></li>
						<li><a href="./howto.php" id="howto">How To</a></li>
					</ul>
					<ul id="social" class="nav navbar-nav navbar-right">
						
						<?php if (isset($session->loggedIn) && $session->loggedIn){ ?>
                            <!-- If session exists and user is logged in-->
							<li class="dropdown gravatar">
                                <a id="menuLi" href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img id="gravatarImg" class="gravatar" src="<?php echo $session->gravatar_url; ?>" alt="Gravatar image for <?php echo $session->username; ?>"/>
                                    <b class="caret" style="float:right;"></b>
                                </a>
                                <ul id="menuDropdown" class="dropdown-menu">
                                    <li id="welcome">Welcome,  <?php echo $session->screen_name; ?></li>
                                    <li id="saveScheduleLi"><a href="http://apps.janeullah.com/coursepicker/saveschedule.php" title="Click to save your created schedules.">Save Schedule</a></li>
									<li id="tweetScheduli"><a data-toggle="modal" href="#tweetModal" title="Click to tweet a link to this schedule. Please save the schedule first otherwise you will simply be tweeting a png of the schedule in which case you should click the \"Download Schedule\" link first.">Tweet Schedule</a></li>
									<li id="logoutLi"><a href="#logout" title="Click to log out!" onclick="logout()">Logout</a></li>
								</ul>
							</li>
						<?php } elseif (isset($session->oauth_object)){ ?>
                            <!-- If user only signs in with twitter-->
							<li class="dropdown gravatar">
                                <a id="menuLi" href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <img id="avatarImg" class="gravatar" src="http://apps.janeullah.com/coursepicker/assets/img/mm.png" alt="Default image for <?php echo $session->screen_name; ?>"  title="Avatar for <?php echo $session->screen_name; ?>"/>
                                    <b class="caret" style="float:right;"></b></a>
                                <ul id="menuDropdown" class="dropdown-menu">
                                    <li id="welcome">Welcome, <?php echo $session->screen_name; ?></li>
                                    <li id="tweetScheduli"><a data-toggle="modal" href="#tweetModal" title="Click to save your created schedules.">Tweet Schedule</a></li>"
                                    <li id="logoutLi"><a href="#logout" title="Click to log out!" onclick="logout()">Logout</a></li>"
                                </ul>
                            </li>
                        <?php } else { ?>
                            <li id="signupLi"><a id="signup" data-toggle="modal" href="#signupModal">Sign Up</a></li>						
                            <li id="loginLi"><a id="login" data-toggle="modal" href="#loginModal">Log In</a></li>
                        <?php } ?>
						
					</ul>					  
				</div><!-- /.nav-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->

		<div class="container">
            <div class="row" style="margin-top:25px;">
                <div class="col-md-3" id="leftdiv">
                    <ul class="nav">
                        <li class="active"><a href="#about">About</a></li>
                        <li><a href="#credits">Credits</a></li>
                        <li><a href="#legal">Legal</a></li>
                    </ul>
                </div>
                <div class="col-md-8" id="displayhowto">  
                    <h4 id="about">About</h4> 
                        <p>
							<ol class="instructions">
                                <li>This is a labor of love by <a href="http://janeullah.com" title="Jane Ullah!">Jane Ullah</a>: <a href="http://uga.edu" title="Go DAWGS!">UGA student</a>, pet-mom and <a href="http://janetalkscode.com" title="Jane Talks Code">technophile</a>.:) </li>
                                <li>Other projects: <a href="http://dawgtransit.com" title="DawgTransit - Get around UGA's campus with ease.">DawgTransit</a>, <a href="http://apps.janeullah.com/dawgspeak" title="UGA SLanguage Dictionary">DawgSpeak</a>, and <a href="https://play.google.com/store/apps/details?id=org.janeullah.android.healthrecords" title="Restaurant Health Inspection Records">Restaurant Health Inspection Records app</a> on Google Play.</li>
                                <li>I write at <a href="http://janetalkscode.com" title="Jane Talks Code!">Jane Talks Code</a> and tweet <a href="https://twitter.com/janetalkstech" title="@janetalkstech on Twitter">@janetalkstech</a>.</li>
                                <li>The source code for this web application is hosted <a href="https://github.com/janoulle/CourseScheduler" title="CoursePicker Source Code">on Github</a>.<br/><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">CoursePicker</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.</li>
                            </ol>     
                        </p>
                    <h4 id="credits">Credits</h4>
                        <p>
                            <ol class="instructions">
                                <li>I'm using <a href="https://mixpanel.com/free/" title="Mixpanel">Mixpanel</a>, <a href="http://www.google.com/analytics/" title="Google Analytics">Google Analytics</a> and <a href="http://statcounter.com/" title="Statcounter">Statcounter</a> for event tracking and analytics. <a href="https://mixpanel.com/f/partner"><img src="//cdn.mxpnl.com/site_media/images/partner/badge_light.png" alt="Mobile Analytics" style="width:114px;height:36px;" width="114" height="36"/></a></li>
                                <li><a href="http://www.openwall.com/phpass/" title="Portable PHP password hashing framework">Portable PHP password hashing framework</a> or phpass was used for hashing the passwords and I made extensive use of the functions listed on the OpenWall article about <a href="http://www.openwall.com/articles/PHP-Users-Passwords" title="managing a PHP application's users and passwords">managing a PHP application's users and passwords</a>.</li>
                                <li><a href="https://github.com/PHPMailer/PHPMailer" title="PHPMailer">PHP Mailer</a> was used to handle the task of sending emails to users that sign up for the Course Picker site. Eventually, I'd like this task to be outsourced to Mailchimp.</li>
                                <li><a href="https://www.google.com/recaptcha" title="reCAPTCHA by Google">reCAPTCHA</a> is an "anti-bot service that helps digitize books". I used it in preventing automated submissions of the signup form.</li>
                                <li><a href="http://simplehtmldom.sourceforge.net/" title="PHP Simple HTML DOM Parser">PHP Simple HTML DOM Parser</a> is another awesome tool that I use a lot for scraping web pages to extract useful information. For parsing HTML with Java, I use <a href="http://jsoup.org/" title="Java HTML Parser">JSoup</a> and I highly recommend that as well.</li>
                            </ol>
                        </p>
                    <h4 id="legal">Legal</h4>
                        <p>
                            <ol class="instructions">
                                <li><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">CoursePicker</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>.</li>
                                <li>I use several libraries and your use of those libraries is subject to the terms of their respective owners.</li>
                                <li>The app pulls data from <a href="https://apps.reg.uga.edu/reporting/staticReports">UGA Registrar Reporting System. Any issues regarding the course contents or semester offerings should be directed to there.</li>
                            </ol>
                        </p>
                </div>
            </div>

            <hr>

            <footer>
                <p>&copy; <a href="http://janeullah.com" title="Jane Ullah">Jane Ullah 2014</a></p>
            </footer>

		</div><!--/.container-->
    <?php require_once("includes/dialogs.inc") ?>	
    <?php require_once("includes/analyticstracking.inc") ?>
  </body>
</html>

