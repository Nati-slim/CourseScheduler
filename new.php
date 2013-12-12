<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com",false,true);
session_name('CoursePicker');
session_start();
$controller = "classes/controllers/controller.php";
$errorMessage = $_SESSION['errorMessage'];


/**
 * http://php.net/manual/en/language.oop5.autoload.php
 * Autoload the class files for deserializing
 */
function __autoload($class_name) {
    include "classes/helpers/" . $class_name . '.php';
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

/**
 *
 * Handle Requests
 */
$semesterSelected = "201405-UNIV";

$semesters = array();
$semesters['201405-UNIV'] = '(Athens) Summer 2014';
$semesters['201402-UNIV'] = '(Athens) Spring 2014';
$semesters['201308-UNIV'] = '(Athens) Fall 2013';
$semesters['201305-UNIV'] = '(Athens) Summer 2013';
$semesters['201405-GWIN'] = '(Gwinnett) Summer 2014';
$semesters['201402-GWIN'] = '(Gwinnett) Spring 2014';
$semesters['201308-GWIN'] = '(Gwinnett) Fall 2013';
$semesters['201305-GWIN'] = '(Gwinnett) Summer 2013';

$jsonURL = "assets/json/tp/tp-201405-UNIV.json";
$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
	$semesterSelected = get_post_var('semesterSelection');
	if (array_key_exists($semesterSelected, $semesters)) {		
		$jsonURL =  "assets/json/tp/tp-" . $semesterSelected . ".json";
		$errorMessage = "";
	}else{
		$errorMessage = "Invalid selection";
		$semesterSelected = "201405-UNIV";
		$jsonURL = "assets/json/tp/tp-201405-UNIV.json";
	}
}

$sched = $_SESSION['schedule'][$_SESSION['userid']];
$cListings = $_SESSION['courses'];
$sListings = $_SESSION['sections'];
$lenCourses = count($cListings);
$lenSections = count($sListings);
$data = "[]";
$sects = "[]";
$title = "Course Picker";
$longdesc = "";
$shortdesc = "A course scheuling app for the University of Georgia Computer Science students";
$asseturl = "http://apps.janeullah.com/coursepicker/assets";
$captchaurl = "../../creds/captcha.inc";
$recaptchaurl = "../../auth/recaptcha/recaptchalib.php";
$emailurl = "classes/controllers/auth.php";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $title;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $shortdesc; ?>">
    <meta name="author" content="Jane Ullah">

	<script type="text/javascript">
		<?php
			try{
				echo "var sched = '".$sched."';";
				echo "var courseListings = '".$data."';";
				echo "var sectionListings = '".$sects."';";
			}catch(Exception $e){
				echo "console.log(\"Problem getting schedule.\");";
			}
		?>
	</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/bootstrap/css/offcanvas.css" rel="stylesheet">
    <link href="assets/css/picker.css" rel="stylesheet">
    <link href="assets/css/sections.css" rel="stylesheet">
    <link href="assets/css/typeahead.js-bootstrap.css" rel="stylesheet">
    <link href="assets/css/tt-suggestions.css" rel="stylesheet">
    <link href="assets/css/alertify.bootstrap.css" media="screen">

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
    <script src="assets/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>	
	<script src="http://twitter.github.com/hogan.js/builds/2.0.0/hogan-2.0.0.js" type="text/javascript"></script>
    <script src="assets/js/alertify.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(function(){
			$('#semesterSelection').change(function(){
				clearLocalStorage();
            	$('#semesterSelectionForm').submit();
			});

		});

		function clearLocalStorage() {
        	localStorage.clear();
        	return false;
    	}

		function populateSections(data){
			console.log(data);
			$('#sectionsFound').empty();
			var size = Object.keys(data).length;
			var sectionDiv;
			var allSections = "<span id=\"sectionFoundHeader\" class=\"intro\">Sections Found:<span class=\"badge pull-right\">" + size + "</span></span><br/>"; 
			Object.keys(data).forEach(function(key){
				var section = data[key];
				sectionDiv = "<div class=\"individualSection\"> <span onclick=\"addSection(" +  section.callNumber + ")\" class=\"glyphicon glyphicon-plus pull-right\"></span>";
				sectionDiv += "<span class=\"heading\">" + section.courseName + " #" + section.callNumber + "</span>";
				sectionDiv += section.coursePrefix + "-" + section.courseNumber + "<br/>";
				sectionDiv += "Lecturer: " + section.lecturer + "<br/>";
				sectionDiv += "Available: " + section.status + "<br/>";
				sectionDiv += "</div>";
				allSections += sectionDiv;
			});
			$('#sectionsFound').append(allSections);
		}

		function addSection(callNumber){
			console.log(callNumber);
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
          <a class="navbar-brand" href="#">Project name</a>
        </div>
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div><!-- /.nav-collapse -->
      </div><!-- /.container -->
    </div><!-- /.navbar -->

    <div class="container">
    	<div class="row">
		    <div class="col-xs-4 col-md-3" id="leftdiv">
				<p id="infoMessage" class="alert alert-info">Selected: <?php echo $semesters[$semesterSelected]; ?></p>
				<?php if (strlen($errorMessage) > 0) {  ?>
				<p id="errorMessage" class="alert alert-warning"><?php echo $errorMessage;?></p>
				<?php
				}
				?>
				<span class="intro">Change Semester/Campus:</span><br/>
				<form id="semesterSelectionForm" name="semesterSelectionForm" method="post" action="new.php">
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
				<br/>
				<span class="intro">Search:</span>				
				<input id="jsonURL" name="jsonURL" type="hidden" value="<?php echo $jsonURL;?>" />
				<input type="hidden" name="selectedSemester" id="selectedSemester" value="<?php echo $semesterSelected; ?>" />
				<input class="typeahead" type="text" id="courseEntry" name="courseEntry" placeholder="e.g. CSCI 1302" />


				<div id="sectionsFound">

				</div>

				<div id="userSchedule" style="display:none;">
					Hey Hey hey Hey hey hey hey Hey Hey hey Hey hey hey hey Hey Hey hey Hey hey hey hey
					<div class="individualSection">
						<!--<span class="glyphicon glyphicon-trash pull-right"></span>-->
						<span class="heading">Heading</span>
						Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye Bye
					</div>
					<div class="individualSection">
						<span class="heading">Heading</span>
						You You You You You You You You You You You You You You You You You You You You You You You You You You
					</div>
				</div>
				<script src="assets/js/typeahead.min.js" type="text/javascript"></script>
				<script type="text/javascript">
					/*http://stackoverflow.com/questions/18019653/typeahead-js-get-selected-datum
					https://github.com/twitter/typeahead.js#readme*/
					$(function(){
						$('#courseEntry').typeahead({
							name: 'courses',
							limit: 7,
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
							console.log("semSelected: " + semSelected + "\n" + "courseValue: " + courseValue + "\n");
							$.ajax({
								type: "POST",
  								url: 'classes/controllers/coursecontroller.php',
  								data: { action : "getSections", selectedSemester : semSelected, courseEntry : courseValue},
								dataType: "json",
  								success: function(data, textStatus, jqXHR){
									populateSections(data);
								}
							});
						});
					});
				</script>
		    </div><!--/sidebar-->


			<div class="col-xs-12 col-md-9" id="canvasDiv">
    	  		<canvas id="scheduleCanvas" width="780" height="750">
				</canvas>
			</div>

		</div><!--/row-->


      	<hr>

     	<footer>
        	<p>&copy; Company 2013</p>
      	</footer>

    </div><!--/.container-->
  </body>
</html>
