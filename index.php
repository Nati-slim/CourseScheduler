<?php
$controller = "classes/controllers/controller.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Course Scheduler</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A course scheduling app for the University of Georgia Computer Science students">
    <meta name="author" content="Jane Ullah">

    <!-- Le styles -->
    <script src="assets/js/jquery-1.9.1.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/coursepicker.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding-top: 20px;
        padding-bottom: 60px;
        background-image:url('assets/images/escheresque.png');
        background-repeat:repeat;

      }

      /* Custom container */
      .container {
        margin: 0 auto;
        max-width: 1000px;
      }
      .container > hr {
        margin: 60px 0;
      }

      /* Main marketing message and sign up button */
      .jumbotron {
        margin: 80px 0;
        text-align: center;
      }
      .jumbotron h1 {
        font-size: 100px;
        line-height: 1;
      }
      .jumbotron .lead {
        font-size: 24px;
        line-height: 1.25;
      }
      .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
      }

      .jumbotron .hidden{
		  display:none;
	  }

      /* Supporting marketing content */
      .marketing {
        margin: 60px 0;
      }
      .marketing p + h4 {
        margin-top: 28px;
      }


      /* Customize the navbar links to be fill the entire space of the .navbar */
      .navbar .navbar-inner {
        padding: 0;
      }
      .navbar .nav {
        margin: 0;
        display: table;
        width: 100%;
      }
      .navbar .nav li {
        display: table-cell;
        width: 1%;
        float: none;
      }
      .navbar .nav li a {
        font-weight: bold;
        text-align: center;
        border-left: 1px solid rgba(255,255,255,.75);
        border-right: 1px solid rgba(0,0,0,.1);
      }
      .navbar .nav li:first-child a {
        border-left: 0;
        border-radius: 3px 0 0 3px;
      }
      .navbar .nav li:last-child a {
        border-right: 0;
        border-radius: 0 3px 3px 0;
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
<script type="text/javascript">
$(document).ready(function(){
	$('#navigator li:eq(0)').bind('click',function(){
		$('#jumbo0').removeClass("hidden");
		$('#jumbo1, #jumbo2').addClass("hidden");
		$('#navigator li:eq(0)').addClass("active");
		$('#navigator li:eq(1), #navigator li:eq(2)').removeClass("active");
	});
	$('#navigator li:eq(1)').bind('click',function(){
		$('#jumbo1').removeClass("hidden");
		$('#jumbo0, #jumbo2').addClass("hidden");
		$('#navigator li:eq(1)').addClass("active");
		$('#navigator li:eq(0), #navigator li:eq(2)').removeClass("active");
	});
	$('#navigator li:eq(2)').bind('click',function(){
		$('#jumbo2').removeClass("hidden");
		$('#jumbo0, #jumbo1').addClass("hidden");
		$('#navigator li:eq(2)').addClass("active");
		$('#navigator li:eq(0), #navigator li:eq(1)').removeClass("active");
	});
});
</script>
</head>

<body>

    <div class="container">

      <div class="masthead">
        <h3 class="muted">Course Scheduler</h3>
        <div class="navbar">
          <div class="navbar-inner">
            <div class="container">
              <ul id="navigator" class="nav">
                <li class="active"><a href="http://apps.janeullah.com/coursepicker">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
              </ul>
            </div>
          </div>
        </div><!-- /.navbar -->
      </div>

      <!-- Jumbotron -->
      <div id="jumbo0" class="jumbotron">
        <h1>Course Scheduler</h1>
        <p class="lead">Yet Another Course Scheduling application. This time, geared towards students at the <strong>University of Georgia</strong>, in <strong>the Franklin College
		 of Arts &amp; Sciences</strong> and <strong>Computer Science</strong> majors.</p>
        <a class="btn btn-large btn-primary" href="<?php echo $controller."?page=schedule"; ?>">Enter</a>
      </div>


	<div id="jumbo1" class="jumbotron hidden">
		<p class="lead">Originally created in Java and originated out of a group project for a class at UGA. This port was written by <a href="http://janeullah.com">Jane Ullah</a>.
		</p>
		<p>
		If you refresh your browser's cache, your schedule will be lost, take a picture! (it'll last longer. :))
		</p>
	</div>

	<div id="jumbo2" class="jumbotron hidden">
		<p class="lead">Tweet @janetalkstech or firstnamelastname@gmail.com (substitute appropriately).</p>
	</div>

    <?php /*<div class="jumbotron hidden">
    </div>

    <div class="jumbotron hidden">
    </div>*/?>


      <div class="footer">
        <p>&copy; Jane Ullah</p>
      </div>

    </div> <!-- /container -->

	<?php include_once("includes/analyticstracking.php") ?>
	</body>
</html>
