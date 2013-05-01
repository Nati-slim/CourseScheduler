<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com/coursepicker",false,true);
session_name('CourseScheduler');
session_start();
$sched = $_SESSION['schedule'][$_SESSION['userid']];
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
	<script type="text/javascript">
		<?php
			try{
				echo "var sched = '".$sched."';";
			}catch(Exception $e){
				echo "var sched = '".$_SESSION['test']."';";
			}
		?>
	</script>
    <script src="assets/js/coursepicker.js"></script>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/coursepicker.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
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
          <a class="brand" href="http://apps.janeullah.com/coursepicker">Project name</a>
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
				<?php echo "User id: " . $_SESSION['userid'] ?>
			</div>
			<div class="span9" id="canvasDiv">
				<canvas id="scheduleCanvas" width="780" height="750">
				</canvas>
			</div>
		</div><!-- /row fluid-->
    </div> <!-- /container -->


  </body>
</html>
