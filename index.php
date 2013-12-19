<?php
require_once "classes/helpers/session.php";
require_once "../../creds/coursepicker_debug.inc";
require_once "../../creds/dhpath.inc";
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
		<link href="assets/css/signin.css" rel="stylesheet">
        <style type="text/css">
            .tt-courseShortname{
                font-weight: bold
            }

            .tt-courseName{
                font-size: 14px;
                margin-left:6px;
            }

            .tt-lecturer{
                float: right;
                font-style: italic;
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
        <!--<script type="text/javascript">
            var node = document.createElement('script');
            node.type = 'text/javascript';
            node.async = true;
            node.src = 'assets/js/draggable.js';
            $('.container').append(node);
            // Now insert the node into the DOM, perhaps using insertBefore()
        </script>-->
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="assets/js/canvasstyle.min.js" type="text/javascript"></script>
		<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>	
		<script src="assets/js/hogan.min.js" type="text/javascript"></script>

		<!--JS handling saving, sharing, downloading schedules -->
		<script src="assets/js/schedule.js" type="text/javascript"></script>
		<!--JS related to the dynamic addition of the course navigation elements -->
		<script src="assets/js/drawings.min.js" type="text/javascript"></script>
		<!--JS related to the signup/login functions -->
		<script src="assets/js/register.js" type="text/javascript"></script>
        <!-- Pamela Fox's lscache library https://github.com/pamelafox/lscache-->
		<script src="assets/js/lscache.min.js" type="text/javascript"></script>
        <!-- http://dte-project.googlecode.com/svn/trunk/jquery-draggable/ | http://grantm.github.io/jquery-udraggable/#examples-->
        <script src="assets/js/draggable.min.js" type="text/javascript"></script>
        <script src="assets/js/jquery.event.ue.js" type="text/javascript"></script>
        <script src="assets/js/jquery.udraggable.js" type="text/javascript"></script>
		<!--http://stackoverflow.com/questions/2010892/storing-objects-in-html5-localstorage -->
        <script type="text/javascript">
            try{
                var uga_buildings = lscache.get('uga_buildings');
                if (uga_buildings){
                    console.log("retrieved list of uga buildings from lscache.");
                }else{
                    $.getJSON("assets/json/uga_building_names.json", function(data){
                        uga_buildings = data;
                        lscache.set('uga_buildings', JSON.stringify(data),43200);
                    })
                    .done(function() {
                        console.log( "second success" );
                    })
                    .fail(function() {
                        console.log( "getJSON request failed. :( " );
                        <?php 
                            if (isset($session->uga_file)){
                                $session->uga_file = file_get_contents("assets/json/uga_building_names.json");
                            }
                            echo "var uga_buildings = $.parseJSON(" . json_encode($session->uga_file) . ");"; 
                        ?>
                    }) 
                }
                //console.log(uga_buildings);
            }catch(e){
                <?php 
                    if (isset($session->uga_file)){
                        $session->uga_file = file_get_contents("assets/json/uga_building_names.json");
                    }
                    echo "var uga_buildings = $.parseJSON(" . json_encode($session->uga_file) . ");"; 
                ?>
                console.log("Error getting item from local storage.");
                console.log(e);
            } 
            
        </script>
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
		    <div class="col-xs-6 col-sm-6 col-md-3" id="leftdiv">
				<div class="row">
					<p id="infoMessage" class="alert alert-info">
						<?php echo $semesters[$semesterSelected]; ?>
					</p>
					<div class="sidebar" id="messages">
						<?php if (strlen($errorMessage) > 0) { 
							echo "<script type=\"text/javascript\"> $('#errorMessage').show(); </script>";	
						?>
                        <p id="errorMessage" class="alert alert-danger"><?php echo $errorMessage;?></p>
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
                        <script type="text/javascript">
                            $('#semesterSelection').val(<?php echo "'" . $semesterSelected . "'";?>);
                        </script>
					</div>
					
					<div id="searchBox" class="sidebar" rel="popover" data-placement="top" data-toggle="popover" data-trigger="hover" data-content="Start typing and select an option from the menu presented to trigger a submission. Otherwise, you will need to submit your entry using the button.">
						<span class="intro">Search: </span><br/>		
						<input id="jsonURL" name="jsonURL" type="hidden" value="<?php echo $jsonURL;?>" />
						<input type="hidden" name="selectedSemester" id="selectedSemester" value="<?php echo $semesterSelected; ?>" />
						<input class="form-control" type="text" id="courseEntry" name="courseEntry" placeholder="e.g. CSCI 1302 or FINANCE" />
					</div>

					<div id="controlCheckboxes" style="display:none;" class="checkboxes">
                        <!-- data-content="Popup with option trigger" rel="popover" data-placement="bottom" data-original-title="Title" -->
                        <!-- rel="popover" data-placement="right" data-toggle="popover" data-trigger="hover" data-content="Check this box to see available sections"-->
						<input checked type="checkbox" class="checkedElement" id="Available" name="Available" value="Available" rel="popover" data-placement="right" data-toggle="popover" data-trigger="hover" data-content="Check this box to see available sections"/><span id="AvailableSpan">Available</span><br/>
						<input checked type="checkbox" class="checkedElement" id="Full" name="Full" value="Full" rel="popover" data-placement="right" data-toggle="popover" data-trigger="hover" data-content="Check this box to see full sections"/><span id="FullSpan">Full</span><br/>
						<input checked type="checkbox" class="checkedElement" id="Cancelled" name="Cancelled" value="Cancelled" rel="popover" data-placement="right" data-toggle="popover" data-trigger="hover" data-content="Check this box to see cancelled sections"/><span id="CancelledSpan">Cancelled</span>
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
									'<p class="tt-courseShortname">{{coursePrefix}} {{courseNumber}}</p>',                         
									'<p class="tt-courseName">{{courseName}}</p>'                    
								].join(''),                                                                 
								engine: Hogan                
							}).on('typeahead:selected',function(obj,datum){
								//console.log(obj);
								//console.log(datum.value);
                                
								var semSelected = $('#selectedSemester').val();
								var courseValue  = datum.value;
                                if (semSelected == ""){
                                    $('#messages').empty().append("<p class=\"alert alert-danger\">Please refresh the page. There was a problem getting your semester selection.</p>").show();
                                    setTimeout(function(){ 
                                        $('#messages').hide("slow",function(){}); 
                                    }, 4000);
                                }else{
                                    _gaq.push(['_trackEvent', 'Typeahead Selected', courseValue,  'User selected ' + courseValue]);
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
                                        $('#messages').empty().append("<p class=\"alert alert-danger\">" + msg.responseText+ "</p>").show();
                                        setTimeout(function(){ 
                                            $('#messages').hide("slow",function(){}); 
                                        }, 4000);  
                                        console.log(msg.responseText);
                                    });
                                }
							});  
                                                     
						});
                        
                        /**
                        Workaround for when the user doesn't choose an option from
                        the typehead suggestions.
                        */ 
                        function getSections(){
                            var entry = $('#courseEntry').val();                            
                            var err = "<p class=\"alert alert-danger\">Please enter a course name and number for submission like this XXXX-1234 or XXXX 1234</p>";
                            if (entry == ""){
                                $('#messages').empty().append(err).show();
                                setTimeout(function(){ 
                                    $('#messages').hide("slow",function(){}); 
                                }, 4000);
                            }else if (entry.length < 9){
                                $('#messages').empty().append(err).show();
                                setTimeout(function(){ 
                                    $('#messages').hide("slow",function(){}); 
                                }, 4000);
                            }else{
                                var semSelected = $('#selectedSemester').val();
                                _gaq.push(['_trackEvent', 'Other Input Entered', value,  'User selected ' + value]);
                                $('body').css('cursor', 'wait');
                                //console.log("semSelected: " + semSelected + "\n" + "courseValue: " + courseValue + "\n");
                                $.ajax({
                                    type: "POST",
                                    url: 'classes/controllers/coursecontroller.php',
                                    data: { action : "getSections", semesterSelected : semSelected, courseEntry : entry},
                                    dataType: "json"
                                })
                                .done(function(msg){
                                    $('body').css('cursor', 'auto');
                                    $('#messages').empty();
                                    sListings = msg;
                                    populateSections(msg);
                                })
                                .fail(function(msg){
                                    $('body').css('cursor', 'auto');
                                    $('#messages').empty().append(msg.responseText).show();
                                    setTimeout(function(){ 
                                        $('#messages').hide("slow",function(){}); 
                                    }, 4000);
                                    console.log("<p class=\"alert alert-danger\">" + msg.responseText+ "</p>");
                                });
                            }
                        }
					</script>
				</div><!--/sidebar-->
			</div>

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
