<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com",false,true);
session_name('CourseScheduler');
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
          <a class="brand" href="http://apps.janeullah.com/coursepicker">Course Scheduler</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class="active"><a href="#">Home</a></li>
              <li><a href="#about">About</a></li>
              <li><a href="#contact">Contact</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">
		<div class="row-fluid">
			<div class="span3">
				<p class="alert-error">
					<?php echo $msg ?>
				</p>
				<div class="listInfo" id="requirementInfo">
					<form action="<?php echo $controller; ?>" id="pickRequirement2" name="pickRequirement" method="post">
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
						<input type="submit" class="btn btn-primary" value="Submit Requirement">
					</form>
				</div>

				<!-- Displaying the courses -->
				<div class="listInfo" id="courseInfo">
					<?php
						if ($requirementName && strlen($requirementName)){
							echo "<h4>".$requirementName."</h4>";
						}
					 ?>
					<form action="<?php echo $controller; ?>" id="courseForm" name="courseForm" method="post">
						<select id="courseitem" name="courseitem">
							<option value="0">Select A Course</option>
							<?php
								foreach($cListings as $course){
									echo "<option value=\"".$course->getCoursePrefix()."-".$course->getCourseNumber()."\">".$course->getCoursePrefix()."-".$course->getCourseNumber()."</option>";
								}
							?>
						</select>
						<input class="btn btn-primary" type="submit" value="Submit Course">
					</form>
				</div>

				<!-- HIDDEN / POP-UP DIV -->
				<div class="listInfo" id="explain" style="display:none;">
					<p>Click the link first. Then, scroll down this page to see the generated image. Rightclick the image to save it.
					</p>
				</div>

				<!-- Displaying the sections-->
				<div class="listInfo" id="sectionInfo">
					<?php echo "<h4>".$courseName."</h4>"; ?>
					<form id="sectionForm" name="sectionForm" action="<?php echo $controller; ?>" method="post">
						<select id="sectionItem" name="sectionItem">
							<option value="0">Choose A Section</option>
							<?php
								foreach($sListings as $section){
									echo "<option value=\"".$section->getCallNumber()."\"> Section ".$section->getCallNumber()."</option>";
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
			<div class="span9" id="canvasDiv">
				<canvas id="scheduleCanvas" width="780" height="750">
				</canvas>
				<div id="canvasImage" style="display: none"></div>
			</div>
		</div><!-- /row fluid-->
    </div> <!-- /container -->


  </body>
</html>
