<?php
require_once dirname(__FILE__) . '/../classes/helpers/session.php';
require_once dirname(__FILE__) . '/../classes/models/Course.php';
require_once dirname(__FILE__) . '/../classes/models/Section.php';
require_once dirname(__FILE__) . '/../classes/models/Meeting.php';
require_once dirname(__FILE__) . '/../classes/models/UserSchedule.php';
require_once dirname(__FILE__) . '/../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../../creds/dhpath.inc';
$session = new Session();
$errorMessage = $session->errorMessage;

//Needed for serialization/deserialization
function __autoload($class_name) {
    include dirname(__FILE__) . '/../classes/models/'. $class_name . '.php';
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
$semesters = array();
$semesters['201405-UNIV'] = '(Athens) Summer 2014';
$semesters['201402-UNIV'] = '(Athens) Spring 2014';
$semesters['201308-UNIV'] = '(Athens) Fall 2013';
$semesters['201305-UNIV'] = '(Athens) Summer 2013';
$semesters['201405-GWIN'] = '(Gwinnett) Summer 2014';
$semesters['201402-GWIN'] = '(Gwinnett) Spring 2014';
$semesters['201308-GWIN'] = '(Gwinnett) Fall 2013';
$semesters['201305-GWIN'] = '(Gwinnett) Summer 2013';

//By loading this page, user should have an id in the url
$requestType = $_SERVER['REQUEST_METHOD'];

$defaultSchedule = unserialize($session->scheduleObject);
//testing//
$sched = $session->scheduleJSON;
if (!isset($sched)){
	$sched = "{}";
}


//Page Data
$title = "Course Picker - Viewing Schedule";
$author = "Jane Ullah";
$shortdesc = "A course scheduling app for the University of Georgia (UGA) Computer Science students";
$asseturl = "http://apps.janeullah.com/coursepicker/assets";
$officialurl = "http://apps.janeullah.com/coursepicker/";
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
        <style type="text/css">
            div.individualSection{
                margin-top: 3px;
                padding: 4px;
                border: 2px solid #DCDCDC;
                -moz-border-radius: 20px;
                -webkit-border-radius: 20px;
                -khtml-border-radius: 20px;
                border-radius: 20px;
            }

            div.individualSection span.heading{
                display: block;
                border: 2px solid #004A61;
                -moz-border-radius: 15px;
                -webkit-border-radius: 15px;
                -khtml-border-radius: 15px;
                border-radius: 15px;
                background-color: #004A61;
                color: #F0F0F0;
                text-transform: uppercase;
                font-weight:bold;
            }
            
            span.row1, span.row2{
                font-weight:bold;
                text-transform:uppercase;
            }

            span.row1.right{
                float:left;
            }

            span.row1.left{
                float:right;
            }

            span.row1.left a{
                color: #004A61;
            }

            span.row2.right{
                float:right;
            }

            span.row2.left{
                float:left;
            }

            span.meetingTimes{
                display:block;
                width:100px;
                height: 100%;
                margin: 0 auto;
            }
            
            span.day{
                text-align:center;
                padding:2px;	
                margin:2px;
                border: 2px solid #004A61;
                -moz-border-radius: 20px;
                -webkit-border-radius: 20px;
                -khtml-border-radius: 20px;
                border-radius: 20px;
                letter-spacing:2px;
                /*text-shadow: 1px 0 0 #000, -1px 0 0 #000, 0 1px 0 #000, 0 -1px 0 #000, 1px 1px #000, -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000;*/
            }

            span.day:hover{
                background-color:#004A61;
                color:#F0F0F0;
                font-weight:bold;
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
        <?php if ($defaultSchedule && $defaultSchedule instanceOf UserSchedule) { ?> 	
            <script src="http://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.4.0/fabric.min.js" type="text/javascript"></script>  
            <script src="../assets/js/share.js" type="text/javascript"></script>
            <script src="../assets/js/schedule.js" type="text/javascript"></script>
            <!-- Pamela Fox's lscache library https://github.com/pamelafox/lscache-->
            <script src="../assets/js/lscache.min.js" type="text/javascript"></script>
            <!--http://stackoverflow.com/questions/2010892/storing-objects-in-html5-localstorage -->
            <script type="text/javascript">

                try{
                    var uga_buildings = lscache.get('uga_buildings');
                    if (uga_buildings){
                        console.log("retrieved list of uga buildings from lscache.");
                    }else{
                        $.getJSON("../assets/json/uga_building_names.json", function(data){
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
                                    $session->uga_file = file_get_contents("../assets/json/uga_building_names.json");
                                }
                                echo "var uga_buildings = $.parseJSON(" . json_encode($session->uga_file) . ");"; 
                            ?>
                        }) 
                    }
                    //console.log(uga_buildings);
                }catch(e){
                    <?php 
                        if (isset($session->uga_file)){
                            $session->uga_file = file_get_contents("../assets/json/uga_building_names.json");
                        }
                        echo "var uga_buildings = $.parseJSON(" . json_encode($session->uga_file) . ");"; 
                    ?>
                    console.log("Error getting item from local storage.");
                    console.log(e);
                } 
            </script>
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
                    
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                          <h3 class="panel-title">Schedule Details</h3>
                        </div>
                        <div class="panel-body">                            
                            <strong>Last Saved</strong>: <?php echo $defaultSchedule->getDateAdded(); ?><br/>
                            <strong>Semester/Campus</strong>: <?php echo $semesters[$defaultSchedule->getSemester() . "-" . $defaultSchedule->getCampus()]; ?><br/>
                            <strong>URL: </strong> <a id="link" href="http://apps.janeullah.com/coursepicker/share/id=<?php echo $defaultSchedule->getShortname(); ?>" title="Sharing Your Schedule">Link</a> [<a onclick="copyUrl();">Copy</a>]
                            <script type="text/javascript">
                                function copyUrl() {
                                    window.prompt("Copy to clipboard: Ctrl+C or Cmd + C, Enter", $('#link').attr('href'));
                                }
                            </script>
                        </div>
                    </div>
                        
                    <div id="userSchedule" class="sections">
                        
                    </div>
                </div>

                <div class="col-xs-12 col-md-9 drop" id="canvasDiv">
                    <canvas id="scheduleCanvas" width="780" height="750">
                    </canvas>
                </div>
            <?php } else { ?>
                <p id="infoMessage" class="alert alert-info">Looks like this isn't a valid schedule. Check the id or simply create one for yourself by visiting <a href="http://apps.janeullah.com/coursepicker" title="Course Picker">Course Picker</a> to get started.</p>
                <?php if (strlen($errorMessage) > 0) { 
                    echo "<script type=\"text/javascript\"> $('#errorMessage').show();";
                    echo "setTimeout(function(){ $('#errorMessage').hide('slow',function(){}); },10000);</script>";	
                ?>
                <p id="errorMessage" class="alert alert-danger"><?php echo $errorMessage;?></p>
                <?php  }else if (strlen($errorMessage) == 0){	
                    echo "<script type=\"text/javascript\"> $('#errorMessage').hide(); </script>";
                ?>
							
                <?php } ?>    
           <?php } ?>

		</div><!--/row-->


      	<hr>

        <footer>
            <p>&copy; <a href="http://janeullah.com" title="Jane Ullah">Jane Ullah 2014</a></p>
            <img src="assets/img/trash.jpg" style="visibility:hidden;"  width="38" height="30" alt="Drag a rectangle over to the trashcan to delete the section from your schedule." id="trashcan" />
        </footer>

    </div><!--/.container-->
    <?php require_once dirname(__FILE__) . '/../includes/dialogs.inc'; ?>	
    <?php require_once dirname(__FILE__) . '/../includes/analyticstracking.inc'; ?>
  </body>
</html>
