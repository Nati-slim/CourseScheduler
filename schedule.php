<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com",false,true);
session_name('CoursePicker');
session_start();
$controller = "classes/controllers/controller.php";
/**
 * http://php.net/manual/en/language.oop5.autoload.php
 * Autoload the class files for deserializing
 */
function __autoload($class_name) {
    include "classes/helpers/" . $class_name . '.php';
}
$sched = $_SESSION['schedule'][$_SESSION['userid']];
$cListings = $_SESSION['courses'];
$sListings = $_SESSION['sections'];
$requirementName = $_SESSION['requirementName'];
$lenCourses = count($cListings);
$lenSections = count($sListings);
$data = "[]";
$sects = "[]";
//echo "a: " . $lenCourses . "b: ". $cListings . "c " .$sListings ."d:". $lenSections;
if ($lenCourses > 0){
	//Copy output buffer to a variable
	ob_start();
	echo "[";
	for ($i = 0; $i < $lenCourses; $i++){
		if ($i < $lenCourses-1){
			echo $cListings[$i] . ",";
		}else{
			echo $cListings[$i];
		}
	}
	echo "]";
	$data = ob_get_contents();
	ob_end_clean();
}

if ($lenSections > 0){
	//Copy output buffer to a variable
	ob_start();
	echo "[";
	for ($i = 0; $i < $lenSections; $i++){
		if ($i < $lenSections-1){
			echo $sListings[$i] . ",";
		}else{
			echo $sListings[$i];
		}
	}
	echo "]";
	$sects = ob_get_contents();
	ob_end_clean();
	if (gettype($sListings[0]) == "object"){
		$courseName = $sListings[0]->getCourseName();
	}
}
//echo $courseName;
$msg = $_SESSION['errorMessage'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Course Scheduler</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A course scheuling app for the University of Georgia Computer Science students">
    <meta name="author" content="Jane Ullah">

    <!-- Le styles -->
    <script src="assets/js/jquery-1.9.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
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
    <script src="assets/js/coursepicker.js"></script>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/coursepicker.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
        background-image:url('assets/images/escheresque.png');
        background-repeat:repeat;
      }

      .listInfo{
		  border: 2 solid #000000;
	  }

	  #pngModal{
		  width:810px;
	  }
    </style>
    <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="assets/js/html5shiv.js"></script>
    <![endif]-->

    <!-- Fav and touch icons -->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="assets/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="assets/ico/apple-touch-icon-114-precomposed.png">
      <link rel="apple-touch-icon-precomposed" sizes="72x72" href="assets/ico/apple-touch-icon-72-precomposed.png">
                    <link rel="apple-touch-icon-precomposed" href="assets/ico/apple-touch-icon-57-precomposed.png">
                                   <link rel="shortcut icon" href="assets/ico/favicon.png">

  </head>

	<body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="http://apps.janeullah.com/coursepicker">Course Picker</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="http://apps.janeullah.com/coursepicker/schedule.php">Home</a></li>
              <li><a href="#aboutModal" data-toggle="modal">About</a></li>
              <li><a href="#contactModal" data-toggle="modal">Contact</a></li>
              <li><a href="#pngModal" data-toggle="modal">Save Schedule</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">
		<div class="row-fluid">

			<div class="span3">
				<ul class="nav nav-pills">
				  <li class="active"><a href="#loadcourses" data-toggle="tab">Load Courses</a></li>
				  <li><a href="#search" data-toggle="tab">Search</a></li>
				</ul>
				<p class="infoMessage" id="message">
					<?php echo $msg ?>
				</p>
				<div class="tab-content">
					<div class="tab-pane active" id="loadcourses">
						<div class="listInfo" id="requirementInfo">
							<h4>Choose A Requirement</h4>
							<form action="<?php echo $controller; ?>" id="pickRequirement" name="pickRequirement" method="post">
								<select class="selectpicker" id="requirementId" name="requirementId">
									<option value="0">Select A Requirement</option>
									<optgroup label="Core Curriculum">
										<option value="3">Core Curriculum I: Foundation Courses</option>
										<option value="4">Core Curriculum II: Physical Sciences</option>
										<option value="5">Core Curriculum II: Life Sciences</option>
										<option value="6">Core Curriculum III: Quantitative Reasoning</option>
										<option value="7">Core Curriculum IV: World Languages and
											Culture</option>
										<option value="8">Core Curriculum IV: Humanities and Arts</option>
										<option value="9">Core Curriculum V: Social Sciences</option>
										<option value="18">Core Curriculum VI: Major related courses</option>
									</optgroup>
									<optgroup label="Franklin College">>
										<option value="10">Franklin College: Foreign Language</option>
										<option value="11">Franklin College: Literature</option>
										<option value="12">Franklin College: Fine
											Arts/Philosophy/Religion</option>
										<option value="13">Franklin College: History</option>
										<option value="14">Franklin College: Social Sciences other
											than History</option>
										<option value="15">Franklin College: Biological Sciences</option>
										<option value="16">Franklin College:Physical Sciences</option>
										<option value="17">Franklin College: Multicultural
											Requirement</option>
									</optgroup>
									<optgroup label="Miscellaneous">
										<option value="1">Cultural Diversity Requirement</option>
										<option value="2">Environmental Literacy Requirement</option>
									</optgroup>
									<optgroup label="Comp. Sci. Reqs.">
										<option value="19">Computer Science Major Courses</option>
									</optgroup>
								</select>
							</form>
						</div>

						<!-- Displaying the courses -->
						<div class="listInfo" id="courseInfo">
							<?php
								if ($requirementName && strlen($requirementName)){
									echo "<h4>".$requirementName."</h4>";
								}
								echo "<h5> Choose A Course </h5>";
							 ?>
							<form action="<?php echo $controller; ?>" id="courseForm" name="courseForm" method="post">
								<select id="courseitem" name="courseitem">
									<option value="0">Select A Course</option>
									<?php
										if (count($cListings) > 0){
											foreach($cListings as $course){
												echo "<option value=\"".$course->getCoursePrefix()."-".$course->getCourseNumber()."\">".$course->getCoursePrefix()."-".$course->getCourseNumber()."</option>";
											}
										}
									?>
								</select>
							</form>
						</div>

						<!-- HIDDEN / POP-UP DIV -->
						<div class="listInfo" id="explain" style="display:none;">
							<p>Click the link first. Then, scroll down this page to see the generated image. Rightclick the image to save it.
							</p>
						</div>

						<!-- Displaying the sections-->
						<div class="listInfo" id="sectionInfo">
							<?php
								echo "<h4>".$courseName."</h4>";
								echo "<h5> Choose A Section </h5>";
							?>
							<form id="sectionForm" name="sectionForm" action="<?php echo $controller; ?>" method="post">
								<select id="sectionItem" name="sectionItem">
									<option value="0">Choose A Section</option>
									<?php
										if (count($sListings) > 0){
											foreach($sListings as $section){
												echo "<option value=\"".$section->getCallNumber()."\">".$section->getCoursePrefix()."-".$section->getCourseNumber()." Section # ".$section->getCallNumber()."</option>";
											}
										}
									?>
								</select>
							</form>
						</div>

						<!-- Displaying the meeting times of a section -->
						<div id="meetings" style="display:none;">

						</div>

						<!-- Displaying the sections-->
						<div class="listInfo" id="scheduleInfo" style="display:none;">
							<h4>CURRENT SCHEDULE</h4>
						</div>
					</div>
					<div class="tab-pane" id="search">
						<script type="text/javascript">
							/* Charles Lawrence - Feb 16, 2012. Free to use and modify. Please attribute back to @geuis if you find this useful. Twitter Bootstrap Typeahead doesn't support remote data querying. This is an expected feature in the future. In the meantime, others have submitted patches to the core bootstrap component that allow it. The following will allow remote autocompletes *without* modifying any officially released core code. If others find ways to improve this, please share.*/
							$(document).ready(function(){
								var autocomplete = $('#courses').typeahead()
									.on('keyup', function(ev){

									ev.stopPropagation();
									ev.preventDefault();
									//filter out up/down, tab, enter, and escape keys
									if( $.inArray(ev.keyCode,[40,38,9,13,27]) === -1 ){

										var self = $(this);

										//set typeahead source to empty
										self.data('typeahead').source = [];

										//active used so we aren't triggering duplicate keyup events
										if( !self.data('active') && self.val().length > 0){

											self.data('active', true);

											//Do data request. Insert your own API logic here.
											$.getJSON("assets/json/courses.json", function(data) {
												//set this to true when your callback executes
												self.data('active',true);

												//Filter out your own parameters. Populate them into an array, since this is what typeahead's source requires
												var arr = [], i=data.length;
												while(i--){
													arr[i] = data[i];
												}

												//set your results into the typehead's source
												self.data('typeahead').source = arr;

												//trigger keyup on the typeahead to make it search
												self.trigger('keyup');

												//All done, set to false to prepare for the next remote query.
												self.data('active', false);

											});

										}
									}
								});
							});
						</script>
						<form id="sendCourse" name="sendCourse" action="<?php echo $controller; ?>" method="post">
							<input type="hidden" value="typeahead" name="action">
							<input id="courses" name="courses" data-provide="typeahead" class="typeahead" type="text" placeholder="Courses" autocomplete="off" spellcheck="false" dir="auto">
						</form>

						<!-- Displaying the sections-->
						<div class="listInfo" id="sectionInfo2">
							<h4 id="courseName" style="display:none;"></h4>
							<h5> Choose A Section </h5>
							<form id="sectionForm2" name="sectionForm2" action="<?php echo $controller; ?>" method="post">
								<select id="sectionItem2" name="sectionItem2">
									<option value="0">Choose A Section</option>

								</select>
							</form>
						</div>

						<!-- Displaying the meeting times of a section -->
						<div id="meetings2" style="display:none;">

						</div>

						<!-- Displaying the sections-->
						<div class="listInfo" id="scheduleInfo2" style="display:none;">
							<h4>CURRENT SCHEDULE</h4>
						</div>
					</div>
					<!--<div class="tab-pane" id="messages">...</div>
					<div class="tab-pane" id="settings">...</div>-->
				</div>
			</div>
			<div class="span9" id="canvasDiv">
				<canvas id="scheduleCanvas" width="780" height="750">
				</canvas>
			</div>
		</div><!-- /row fluid-->
    </div> <!-- /container -->

	<!-- About Image Modal -->
	<div class="modal hide fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
		<div class="modal-header">
			<h3 id="aboutModalLabel">About</h3>
		</div>
		<div class="modal-body">
			<img src="assets/images/coursepicker.png" alt="Course Picker">
		</div>
		<div class="modal-footer">
			<a class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
		</div>
	</div>

	<!-- Contact Image Modal -->
	<div class="modal hide fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModalLabel" aria-hidden="true">
		<div class="modal-header">
			<h3 id="contactModalLabel">Contact</h3>
		</div>
		<div class="modal-body">
			Tweet @janetalkstech or janeullah@gmail.com
		</div>
		<div class="modal-footer">
			<a class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
		</div>
	</div>

	<!-- PNG Image Modal -->
	<div class="modal hide fade" id="pngModal" tabindex="-1" role="dialog" aria-labelledby="pngModalLabel" aria-hidden="true">
		<div class="modal-header">
			<h3 id="pngModalLabel">Right-click image and save as a .png file</h3>
		</div>
		<div class="modal-body" id="canvasImage">

		</div>
		<div class="modal-footer">
			<a class="btn btn-primary" data-dismiss="modal" aria-hidden="true">Close</a>
		</div>
	</div>
	<?php include_once("includes/analyticstracking.php") ?>
	</body>
</html>
