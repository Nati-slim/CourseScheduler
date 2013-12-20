<?php
require_once dirname(__FILE__) . '/../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/classes/helpers/UserHelper.php';  
require_once dirname(__FILE__) . '/../../creds/dhpath.inc';

session_start();
         
$result = array();
$validToken = "";
$isValidToken = false;
$debug = DEBUGSTATUS;
//Set up debug stuff
//When  not debugging, log to a file!
if (!$debug) {
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
}

$result = array();
$requestType = $_SERVER['REQUEST_METHOD'];

/**
 * Function to autoload classes needed during serialization/unserialization
 *
 * @param string $class_name name of the Class being loaded
 *
 * @return void
 */
function __autoload($class_name)
{
    include 'classes/models/'. $class_name . '.php';
}

/**
 * Function to check if the token is an alphanumeric string
 * 
 * @param string $token the token
 * 
 * @return string 'OK' if alphanumeric and not 'OK' otherwise
 * 
 */ 
function isValidToken($token){
    global $validToken;
    if (strlen($token) != 32){        
        return 'Code a: Invalid token';
    }
    if (ctype_alnum($token)) {
        $_SESSION['validToken'] = $token;
        $validToken = $token;
        return 'OK';
    }else{
        return "Code b: Invalid token.";
    }    
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

if ($requestType === 'GET') {
    global $result;
    $token = $_GET['token'];
    if ($token){
        $test = isValidToken($token);
        if (strcmp($test, 'OK') == 0){     
            $isValidToken = true;
        }else{
            $result['errorMessage'] = $test;
            //echo json_encode($result);
        }
    }else{
        $result['errorMessage'] = "Invalid parameters found.";
        //echo json_encode($result);
    }
}else{
    $result['errorMessage'] = "Invalid request found.";
    //echo json_encode($result);
}

$title = "Course Picker - Confirm & Activate Account";
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
						<li><a href="#howtoModal" data-toggle="modal" id="howto">How To</a></li>
					</ul>				  
				</div><!-- /.nav-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->

		<div class="container">
            <div class="row" style="margin-top:25px;">
                <div class="col-xs-12 col-md-8" id="confirmEmailDiv">     
                        <p>
                            <strong>Terminology</strong>
                            <ol class="instructions">
                                <li><strong>course prefix</strong>: This is the 4 or 5 letter abbreviation of the course e.g. MATH for Mathematics, CSCI for Computer Science, ENGL for English, etc.</li>
                                <li><strong>course number</strong>: This is the number that comes after the course prefix e.g. CSCI <strong>1302</strong> or ENGL <strong>1101</strong></li>
                                <li><strong>course name</strong>: This is the "official" course name. For instance, ENGL 1101 is officially called "ENGLISH COMP I".</li>
                                <li><strong>call number</strong>: This is the number uniquely identifying the section of the course. A course has many sections and a section can have many different meeting times.</li>
                            </ol>
                        </p>
                        <p>
                            <strong>Adding Classes</strong>:
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
                        <p>
                            <strong>Saving Schedules</strong>:
                            <ol class="instructions">
                                <li>This feature is available only to users that have signed up for the site.</li>
                                <li>You can sign up for the service at any point during schedule creation.</li>
                                <li>After signing up, you will be able to save your schedule to the database right away but you will need to confirm and activate your account by clicking
                                the activation link sent to the email address you provided during signup.</li>
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

