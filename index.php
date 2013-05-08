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
    <script src="assets/js/alertify.min.js"></script>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/coursepicker.css" rel="stylesheet">
    <link href="assets/css/alertify.core.css" rel="stylesheet" media="screen" />
    <link href="assets/css/alertify.default.css" rel="stylesheet" media="screen" />
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

		#captcha{
			margin-left: 364px;
			margin-bottom: 20px;
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
	<script type="text/javascript">
		$(document).ready(function(){
			$('#sendMessage').submit(function(e){
				e.preventDefault();
                $.ajax({
                    type:'POST',
                    url: 'classes/controllers/auth.php',
                    data:$(this).serialize(),
                    success: function(response) {
                        alertify.alert(response);
                        Recaptcha.reload();
                    }
                });
                return false;
			});
		});
	</script>
</head>

<body>

    <div class="container">

      <div class="masthead">
        <h3 class="muted">Course Picker</h3>
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
        <h1>Course Picker</h1>
        <p class="lead">Yet Another Course Scheduling application. This time, geared towards students at the <strong>University of Georgia</strong>, in <strong>the Franklin College
		 of Arts &amp; Sciences</strong> and <strong>Computer Science</strong> majors.</p>
        <a class="btn btn-large btn-primary" href="<?php echo $controller."?page=schedule"; ?>">Enter</a>
      </div>


	<div id="jumbo1" class="jumbotron hidden">
		<p class="lead">Course Picker is developed by <a href="http://janeullah.com">Jane Ullah</a>. 'Twas born out of a class project taught at UGA (CSCI 4300) and was originally written in Java. This web application is written in PHP and shares some of the DNA of the original project. Also, this application is for the Fall 2013 semester's courses.
		</p>
		<p>If you clear your browser's cookies, your schedule will be lost, take a picture! (it'll last longer. :)). You need to enable JavaScript to use this site.
		</p>
	</div>

	<div id="jumbo2" class="jumbotron hidden">
		<p class="lead">Tweet @janetalkstech or firstnamelastname@gmail.com (substitute appropriately).</p>
		<form id="sendMessage" name="sendMessage" action="#" method="post">
			<div class="control-group">
				<label class="control-label" for="name">Name</label>
				<div class="controls">
					<input type="text" id="name" name="name" class="span3" placeholder="Name" required>
				</div>
		    </div>
		    <div class="control-group">
				<label class="control-label" for="email">Email</label>
				<div class="controls">
					<input type="email" id="email" name="email" class="span3" placeholder="Email" required>
				</div>
			</div>
		    <div class="control-group">
				<label class="control-label" for="message">Message</label>
				<div class="controls">
					<textarea name="message" style="height:15em; width:18em;" id="message" placeholder="Please enter your message here." required></textarea>
				</div>
			</div>
			<div id="captcha">
				<?php
					require_once('../../auth/recaptcha/recaptchalib.php');
                    require_once('../../creds/captcha.inc');
                    $publickey = RECAPTCHA_JANEULLAH_PUBLIC;
                    echo recaptcha_get_html($publickey);
				?>
			</div>
			<div class="control-group">
				<input type="submit" class="btn btn-large btn-primary" value="Send Message">
			</div>
		</form>
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
