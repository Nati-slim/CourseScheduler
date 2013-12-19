<?php
require_once dirname(__FILE__) . '/../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/classes/helpers/UserHelper.php';  

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
    ini_set("error_log", "syslog");
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
        <script type="text/javascript">
            $(function(){
                $('#confirmEmailForm').submit(function(e){
                    e.preventDefault();
                    var email1 = $('#confirmEmail1').val();
                    var email2 = $('#confirmEmail2').val();
                    if (email1 === email2){
                        $.ajax({
                            type: "POST",
                            url: 'classes/controllers/authcontroller.php',
                            data: $(this).serialize(),
                            dataType: "json"
                        })
                        .done(function(msg){
                            $('body').css('cursor', 'auto');
                            console.log(msg);
                            if (msg.errorMessage.length > 0){
                                $('#confirmEmailError').empty();
                                $('#confirmEmailError').append(msg.errorMessage).show();
                                $('#confirmEmailSuccess').hide();
                                setTimeout(function(){
                                    $('#confirmEmailError').empty().hide("slow",function(){});
                                }, 10000);
                            }else{
                                $('#confirmEmailError').empty().hide();
                                $('#confirmEmailSuccess').empty().append("Account activated! You can continue to use <a href=\"http://apps.janeullah.com/coursepicker\" title=\"UGA Course Picker\">Course Picker</a>").show();		
                                $('#confirmEmailForm').hide('slow', function(){ 
                                    $('#confirmEmailForm').hide(); 
                                });	
                                setTimeout(function(){
                                    window.location = "http://apps.janeullah.com/coursepicker/";                                    
                                }, 22000);
                                console.log("Successfully activated account in.");
                            }
                        })
                        .fail(function(msg){
                            $('body').css('cursor', 'auto');
                            $('#confirmEmailError').empty().append(msg.responseText).show();
                            $('#confirmEmailSuccess').empty().hide();
                            console.log(msg.responseText);
                        });
                    }else{
                        $('#confirmEmailError').empty().append("The 2 fields must match!").show();
                        $('#confirmEmailSuccess').empty().hide();
                    }  
                    return false;
                });
            });
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
					</ul>				  
				</div><!-- /.nav-collapse -->
			</div><!-- /.container -->
		</div><!-- /.navbar -->

		<div class="container">
            <div class="row" style="margin-top:25px;">
                <div class="col-xs-12 col-md-8" id="confirmEmailDiv">     
                    
                    <?php  if ($isValidToken) { ?>
                        <div class="alert alert-danger" id="confirmEmailError" style="display:none"></div>
                        <div class="alert alert-success" id="confirmEmailSuccess" style="display:none"></div>
                        <form id="confirmEmailForm" name="confirmEmailForm" class="form-signin" role="form" method="post" action="classes/controllers/authcontroller.php">							
                            <div class="form-group">
                                <label for="confirmEmail1">Confirm Email</label>
                                <input type="email" class="form-control" id="confirmEmail1" name="confirmEmail1" placeholder="Enter registration email used" value="" required/>
                            </div>
                            <div class="form-group">
                                <label for="confirmEmail2">Re-Enter Email</label>
                                <input type="email" class="form-control" id="confirmEmail2" name="confirmEmail2" placeholder="Enter registration email used" required>
                            </div>
                            <input type="hidden" id="token" name="token" value="<?php echo $validToken; ?>" />
                            <input type="hidden" id="action" name="action" value="confirmEmail" />
                            <button id="saveScheduleBtn" type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-default">Clear</button> 
                        </form>   
                    <?php } else { ?>  
                        <p id="errorMessage" class="alert alert-danger"><?php echo $result['errorMessage']; ?></p>
                    <?php } ?>
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

