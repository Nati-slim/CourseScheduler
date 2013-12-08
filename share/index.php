<?php
session_save_path(dirname($_SERVER['DOCUMENT_ROOT']) . '/sessions');
session_set_cookie_params(86400,"/","apps.janeullah.com",false,true);
session_name('CoursePicker');
session_start();
$controller = "../classes/controllers/sharingcontroller.php";
/**
 * http://php.net/manual/en/language.oop5.autoload.php
 * Autoload the class files for deserializing
 */
function __autoload($class_name) {
    include "../classes/helpers/" . $class_name . '.php';
}
$schedule = $_SESSION['schedule'][$_SESSION['userid']];
$msg = $_SESSION['errorMessage'];
$title = "Course Picker";
$longdesc = "";
$shortdesc = "A course scheuling app for the University of Georgia Computer Science students";
$asseturl = "http://apps.janeullah.com/coursepicker/assets";
$captchaurl = "../../../creds/captcha.inc";
$recaptchaurl = "../../../auth/recaptcha/recaptchalib.php";
$emailurl = "../classes/controllers/auth.php";

//FACEBOOK STUFF
require_once("../includes/facebook/facebook.php");
require_once("../../../creds/facebook_coursepicker.inc");
$config = array();
$config['appId'] = APP_ID;
$config['secret'] = APP_SECRET;
$config['fileUpload'] = true; // optional
$facebook = new Facebook($config);
// Upload a photo to a user’s profile
// Your app needs photo_upload permission for this to work
$facebook->setFileUploadSupport(true);
// Get User ID
$user = $facebook->getUser();

//TWITTER STUFF
/*require_once('../includes/twitteroauth/twitteroauth/twitteroauth.php');
require_once('../includes/twitteroauth/config.php');*/

/* If the oauth_token is old redirect to the connect page. */
/*if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  $_SESSION['oauth_status'] = 'oldtoken';
}

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
//$conn = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from twitter */
//$access_token = $conn->getAccessToken($_REQUEST['oauth_verifier']);

/* Save the access tokens. Normally these would be saved in a database for future use. */
//$_SESSION['access_token'] = $access_token;

/* Remove no longer needed request tokens*/
//unset($_SESSION['oauth_token']);
//unset($_SESSION['oauth_token_secret']);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Your Schedule</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="A course scheuling app for the University of Georgia Computer Science students">
		<meta name="author" content="Jane Ullah">
		<script type="text/javascript">
			<?php
				try{
					echo "var sched = '".$schedule."';";
				}catch(Exception $e){
					echo "console.log(\"Problem getting schedule from session.\");";
				}
			?>
		</script>
		<?php require_once("../includes/resources.inc"); ?>
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
              <li><a href="#pngModal" data-toggle="modal">Download Schedule</a></li>
              <!--<li><a href="#" id="saveSchedule">Save Schedule</a></li></li>-->
              <li><a href="#shareModal" id="shareSchedule"  data-toggle="modal">Share Schedule</a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>

    <div class="container-fluid">
		<div class="row-fluid">
			<div class="span2" id="shareButtons">
				<a class="btn btn-primary" href="#shareTwitterModal" data-toggle="modal">Connect To Facebook</a>
			</div>
			<div class="span9" id="canvasDiv">
				<canvas id="scheduleCanvas" width="780" height="750">
				</canvas>
			</div>
		</div>
	</div><!-- /container -->

	<!-- Share Twitter Modal -->
	<div class="modal hide fade" id="shareTwitterModal" tabindex="-1" role="dialog" aria-labelledby="shareTwitterModalLabel" aria-hidden="true">
		<div class="modal-header">
			<h3 id="shareTwitterModalLabel">Connect To Facebook</h3>
		</div>
		<div class="modal-body">
			<?php
				//We may or may not have this data based on whether the user is logged in.
				//If we have a $user id here, it means we know the user is logged into
				//Facebook, but we don't know if the access token is valid. An access
				//token is invalid if the user logged out of Facebook.

				if ($user) {
				  try {
					// Proceed knowing you have a logged in user who's authenticated.
					$user_profile = $facebook->api('/me');

					$img = 'b.png';

					$photo = $facebook->api(
					  '/me/photos',
					  'POST',
					  array(
						'source' => '@' . $img,
						'message' => 'Photo uploaded via the PHP SDK!'
					  )
					);
				  } catch (FacebookApiException $e) {
					error_log($e);
					$user = null;
				  }
				}

				// Login or logout url will be needed depending on current user state.
				if ($user) {
				  $logoutUrl = $facebook->getLogoutUrl();
				} else {
				  $loginUrl = $facebook->getLoginUrl();
				}

				/* Build TwitterOAuth object with client credentials. */
				//$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

				/* Get temporary credentials. */
				//$request_token = $connection->getRequestToken(OAUTH_CALLBACK);

				/* Save temporary credentials to session. */
				//$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
				//$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

				//If last connection failed don't display authorization link.
				/*switch ($connection->http_code) {
				  case 200:
					// Build authorize URL and redirect user to Twitter.
					$url = $connection->getAuthorizeURL($token);
					header('Location: ' . $url);
					break;
				  default:
					//Show notification if something went wrong.
					echo print_r($request_token,true) . "***" .$connection->http_code.'Could not connect to Twitter. Refresh the page or try again later.';
				}*/
			?>
			<?php if ($user): ?>
			  <a class="btn btn-primary" href="<?php echo $logoutUrl; ?>">Logout</a>
			<?php else: ?>
			  <div>
				Login using OAuth 2.0 handled by the PHP SDK:
				<a class="btn btn-primary" href="<?php echo $loginUrl; ?>">Login with Facebook</a>
			  </div>
			<?php endif ?>

			<!--<h3>PHP Session</h3>
			<pre><?php //print_r($_SESSION); ?></pre>-->

			<?php if ($user): ?>
			  <h3>You</h3>
			  <img src="https://graph.facebook.com/<?php echo $user; ?>/picture">

			 <?php echo print_r($photo,true); ?>
			  <h3>Your User Object (/me)</h3>
			  <pre><?php print_r($user_profile); ?></pre>
			<?php else: ?>
			  <strong><em>You are not Connected.</em></strong>
			<?php endif ?>

		</div>
		<div class="modal-footer">
			<a class="btn" data-dismiss="modal" aria-hidden="true">Close</a>
		</div>
	</div>
	<?php require_once("../includes/footer.inc") ?>
	<?php include_once("../includes/analyticstracking.php") ?>
	</body>
</html>
