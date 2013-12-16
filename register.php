<?php
require_once("classes/helpers/session.php");
require_once("../../creds/parse_coursepicker.inc");
$controller = "classes/controllers/registercontroller.php";
$session = new Session();
$errorMessage = $session->errorMessage;

$title = "Register on Course Picker";
$longdesc = "";
$shortdesc = "Register For Course Picker!";
$captchaurl = "../../creds/captcha.inc";
$recaptchaurl = "../../auth/recaptcha/recaptchalib.php";
$oglocal = "en_US";
$ogtitle = "Register For Course Picker by Jane Ullah";
$ogimg = "http://apps.janeullah.com/coursepicker/assets/img/coursepicker.png";
$ogdesc = "Register For Course Picker to share your class schedule at UGA!";
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php echo $title;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $shortdesc; ?>">
    <meta name="author" content="Jane Ullah">
	<meta property="og:locale" content="en_US" />
	<meta property="og:title" content="<?php echo $ogtitle; ?>" />
	<meta property="og:description" content="<?php echo $ogdesc; ?>"/>
	<meta property="og:url" content="http://apps.janeullah.com/coursepicker/" />
	<meta property="og:site_name" content="<?php echo $ogtitle; ?>" />
	<meta property="og:image" content="<?php echo $ogimg; ?>" />    

	<meta itemprop="name" content="<?php echo $ogtitle; ?>">
	<meta itemprop="description" content="<?php echo $ogdesc;?>">
	<meta itemprop="image" content="<?php echo $ogimg; ?>">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/register.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="assets/js/canvasstyle.js" type="text/javascript"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>	
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
	<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

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
            <li><a href="#about" id="about">About</a></li>
            <li><a href="#contact" id="contact">Contact</a></li>
            <li><a href="#downloadSchedule" id="downloadSchedule">Download Schedule</a></li>
          </ul>
          
          <ul id="social" class="nav navbar-nav navbar-right">
			<!-- <img src="assets/img/elegantmedia/facebook.png" alt="Facebook icon"/>
			<img src="assets/img/elegantmedia/twitter.png" alt="Twitter icon"/>
			<img src="assets/img/elegantmedia/google.png" alt="Google icon"/>-->
            <li><a id="facebook" href="https://facebook.com/janetalkstech" title="Connect with Jane Ullah on Facebook!">F</a></li>
            <li><a id="twitter" href="https://twitter.com/janetalkstech" title="Connect with Jane Ullah on Twitter!">T</a></li>
            <li><a id="google" href="https://plus.google.com/+JaneUllah" title="Connect with Jane Ullah on Googl+">G</a></li>
          </ul>
          
        </div><!-- /.nav-collapse -->
      </div><!-- /.container -->
    </div><!-- /.navbar -->

    <div class="container">
		<form class="form-signin" role="form">
			  <div class="form-group">
					<label for="email">Email address</label>
					<input type="email" class="form-control" id="email" name="email" placeholder="Enter email">
			  </div>
			  <div class="form-group">
					<label for="password1">Password</label>
					<input type="password" class="form-control" id="password1" name="password1" placeholder="Password">
			  </div>
			  <div class="form-group">
					<label for="password2">Password</label>
					<input type="password" class="form-control" id="password2" name="password2" placeholder="Password">
			  </div>
			  <button type="submit" class="btn btn-default">Submit</button>
		</form>

      	<hr>

     	<footer>
        	<p>&copy; Jane Ullah 2014</p>
      	</footer>

    </div><!--/.container-->
    <?php require_once("includes/dialogs.inc") ?>	
    <?php require_once("includes/analyticstracking.inc") ?>
  </body>
</html>
