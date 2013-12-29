<?php
require_once 'classes/helpers/session.php';
$session = new Session();
$errorMessage = $session->errorMessage;
$title = "Course Picker - Learn How To Use";
$shortdesc = "A course scheuling app for the University of Georgia Computer Science students";
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
						<li><a href="./">Home</a></li>
						<li><a href="./about.php" id="about">About</a></li>
						<li class="active"><a href="./howto.php" id="howto">How To</a></li>
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
                                    <li id="tweetScheduli"><a data-toggle="modal" href="#tweetModal" title="Click to save your created schedules.">Tweet Schedule</a></li>
                                    <li id="logoutLi"><a href="#logout" title="Click to log out!" onclick="logout()">Logout</a></li>
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
                        <li class="active"><a href="#terminology">Terminology</a></li>
                        <li><a href="#adding">Adding Classes</a></li>
                        <li><a href="#saving">Saving Schedules</a></li>
                        <li><a href="#sharing">Sharing Schedules</a></li>
                    </ul>
                </div>
                <div class="col-md-8" id="displayhowto">  
                    <h4 id="terminology">Terminology</h4> 
                        <p>
                            <ol class="instructions">
                                <li><strong>course prefix</strong>: This is the 4 or 5 letter abbreviation of the course e.g. MATH for Mathematics, CSCI for Computer Science, ENGL for English, etc.</li>
                                <li><strong>course number</strong>: This is the number that comes after the course prefix e.g. CSCI <strong>1302</strong> or ENGL <strong>1101</strong></li>
                                <li><strong>course name</strong>: This is the "official" course name. For instance, ENGL 1101 is officially called "ENGLISH COMP I".</li>
                                <li><strong>call number</strong>: This is the number uniquely identifying the section of the course. A course has many sections and a section can have many different meeting times.</li>
                            </ol>
                        </p>
                    <h4 id="adding">Adding Classes</h4>
                        <p>
                            <ol class="instructions">
                                <li>Search for a course by any combination of the <strong>course prefix</strong>, <strong>course number</strong> or <strong>course name</strong>. 
                                Please don't be alarmed if you only type the course prefix and receive results that don't match the course prefix exactly. 
                                This is because the course prefix you are searching for is also in the course name of some of the results that were returned to you.</li>
                                <li>If you choose to search by couse prefix only, I recommend entering the term and having a space after the word. 
                                If that doesn't work, add a number after the space e.g. ENGL 11 or HIST 22 to bring up more specific results.</li>
                                <li>If you know the exact course prefix and course number, please type it out and choose the dropdown selection. </strong>THIS IS IMPORTANT FOR THE FORM TO WORK.</strong></li>
                                <li>Choose from the autocomplete menu or manually enter the course you desire in this manner as long as you separate the course prefix from the course number by a dash or space</li>
                                <li>Select a section from the list and your choice will be automatically submitted.</li>                                       
                            </ol>
                        </p>
                    <h4 id="saving">Saving Schedules</h4>
                        <p>
                            <ol class="instructions">
                                <li>This feature is available only to users that have signed up for the site.</li>
                                <li>You can sign up for the service at any point during schedule creation.</li>
                                <li>After signing up, you will be able to save your schedule to the database right away but you will need to confirm and activate your account by clicking
                                the activation link sent to the email address you provided during signup.</li>
                            </ol>
                        </p>
                    <h4 id="sharing">Sharing Schedules</h4>
                        <p>
                            <ol class="instructions">
                                <li>This feature is only available for users who have signed up for the service.</li>
                                <li>First, login to the service and create your schedule</li>
                                <li>Then, visit the <a href="http://apps.janeullah.com/coursepicker/saveschedules.php" title="Save Your Schedules!">Save Schedules</a> page by clicking on your gravatar and selecting the appropriate link named "Save Schedules"</li>
                                <li>After giving your schedule a name e.g. XXXXXX, you can share the schedule by appending the short name to the end of this url: http://apps.janeullah.com/coursepicker/share/id=<strong>XXXXXX</strong></li>
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

