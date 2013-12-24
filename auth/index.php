<?php
require_once dirname(__FILE__) . '/../includes/tmhoauth/tmhOAuth.php';
require_once dirname(__FILE__) . '/../../../creds/dhpath.inc';
require_once dirname(__FILE__) . '/../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../classes/helpers/session.php';

$result = array();
$tmhOAuth = new tmhOAuth();
$session = new Session();
$debug = DEBUGSTATUS;
//Set up debug stuff
//When  not debugging, log to a file!
if (!$debug) {
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
    error_log("You messed up!", 3, ERROR_PATH);
}

/**
 * The $pvt debugging messages may contain characters that would need to be
 * quoted if we were producing HTML output, like we would be in a real app,
 * but we're using text/plain here.  Also, $debug is meant to be disabled on
 * a "production install" to avoid leaking server setup details.
 * 
 * @param string $pub public details of the error
 * @param string $pvt Private details of the error
 * 
 * @return string $msg The error message
 * 
 */
function fail($pub, $pvt = '')
{
    global $debug;
    $msg = $pub;
    if ($debug && $pvt !== '') {
        $msg .= ": $pvt";
    }

    return $msg;
}

// note that a session_start is included in index.php.
// we use the session ($_SESSION) to store the temporary request token issue to us by Twitter

function php_self($dropqs=true) {
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
        $protocol = 'https';
    } elseif (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')) {
        $protocol = 'https';
    }

    $url = sprintf('%s://%s%s',
        $protocol,
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
    );

    $parts = parse_url($url);

    $port = $_SERVER['SERVER_PORT'];
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $path = @$parts['path'];
    $qs   = @$parts['query'];

    $port or $port = ($scheme == 'https') ? '443' : '80';

    if (($scheme == 'https' && $port != '443')
      || ($scheme == 'http' && $port != '80')) {
        $host = "$host:$port";
    }
    $url = "$scheme://$host$path";
    if ( ! $dropqs){
        return "{$url}?{$qs}";
    }else{
        return $url;
    }
}

function uri_params() {
    $url = parse_url($_SERVER['REQUEST_URI']);
    $params = array();
    foreach (explode('&', $url['query']) as $p) {
        list($k, $v) = explode('=', $p);
        $params[$k] =$v;
    }
    return $params;
}


function request_token($tmhOAuth) {
    global $session;
    $results = array();
    $code = $tmhOAuth->apponly_request(array(
        'without_bearer' => true,
        'method' => 'POST',
        'url' => $tmhOAuth->url('oauth/request_token', ''),
        'params' => array(
            'oauth_callback' => php_self(false),
        ),
    ));

    if ($code != 200) {
        $results['errorMessage'] = fail("There was an error communicating with Twitter.",$tmhOAuth->response['raw']);
        unset($_SESSION['oauth']);
        return $results;
    }

    // store the params into the session so they are there when we come back after the redirect
    
    $_SESSION['oauth'] = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    $session->twitter_oauth = $_SESSION['oauth'];
 
    // check the callback has been confirmed
    if ($_SESSION['oauth']['oauth_callback_confirmed'] !== 'true') {
        $results['errorMessage'] =  fail('The callback was not confirmed by Twitter so we cannot continue.');
        unset($_SESSION['oauth']);
    } else {
        $url = $tmhOAuth->url('oauth/authorize', '') . "?oauth_token={$_SESSION['oauth']['oauth_token']}";
        $resultS['errorMessage'] = "";
        $results['oauthUrl'] = $url;
    }
    return $results;
}

function access_token($tmhOAuth) {
    global $session;
    $results = array();
    $params = uri_params();
    if ($params['oauth_token'] !== $_SESSION['oauth']['oauth_token']) {
        $results['errorMessage'] = fail('The oauth token you started with doesn\'t match the one you\'ve been redirected with. do you have multiple tabs open?');
        unset($_SESSION['oauth']);
        //$session->unsetAll();
        return $results;
    }

    if (!isset($params['oauth_verifier'])) {
        $results['errorMessage'] = fail('The oauth verifier is missing so we cannot continue. did you deny the application access?');
        unset($_SESSION['oauth']);
        //$session->unsetAll();
        return $results;
    }

    // update with the temporary token and secret
    $tmhOAuth->reconfigure(array_merge($tmhOAuth->config, array(
        'token'  => $_SESSION['oauth']['oauth_token'],
        'secret' => $_SESSION['oauth']['oauth_token_secret'],
    )));

    $code = $tmhOAuth->user_request(array(
        'method' => 'POST',
        'url' => $tmhOAuth->url('oauth/access_token', ''),
        'params' => array(
        'oauth_verifier' => trim($params['oauth_verifier']),
    )));

    if ($code == 200) {
        $oauth_creds = $tmhOAuth->extract_params($tmhOAuth->response['response']);
        $session->twitter_oauth_token = $oauth_creds['oauth_token'];
        $session->twitter_oauth_token_secret = $oauth_creds['oauth_token_secret'];
        $results['oauth_creds'] = $oauth_creds;
        $results['errorMessage'] = "";
    }else{
        $results['errorMessage'] = fail("There was an error receiving a response from Twitter.",$tmhOAuth->response['response']);
    }
    
    return $results;
}




function getProfileImage($screen_name,$tmhOAuth){
    $results = array();
    $code = $tmhOAuth->apponly_request(array(
        'url' => $tmhOAuth->url('1.1/users/lookup.json'),
        'params' => array(
        'screen_name' => $screen_name
        )
    ));
    
    if ($code != 200) {
        $results['errorMessage'] = fail("There was an error communicating with Twitter.",$tmhOAuth->response['response']);
        return $results;
    }

    //Get data
    $user = $tmhOAuth->extract_params($tmhOAuth->response['response']);
    $session->twitter_user = $user;
    $results['user'] = $user;   
    return $results;
}



//print_r($tmhOAuth);
$params = uri_params();
$session->request_success = false;
$session->access_success = false;

//print_r($params);
if (!isset($params['oauth_token'])) {
    // Step 1: Request a temporary token and
    // Step 2: Direct the user to the authorize web page
    $res = request_token($tmhOAuth);
    if (strlen($res['errorMessage']) == 0){
        $session->url = $res['oauthUrl'];
        $session->authMessage = '<p>To complete the OAuth flow please visit URL: <a href="' . $res['oauthUrl'] . '">' .  $res['oauthUrl'] . '</a></p>';
        $session->request_success = true;
    }else{
        $session->request_errorMessage = $res['errorMessage'];
    }
} else {
    // Step 3: This is the code that runs when Twitter redirects the user to the callback. Exchange the temporary token for a permanent access token
    $res = access_token($tmhOAuth);
    if (strlen($res['errorMessage']) == 0){
        $session->oauth_object = $res['oauth_creds'];
        $session->screen_name = $res['oauth_creds']['screen_name'];
        $session->accessMessage = 'Congratulations! ' . $session->screen_name;
        $session->access_success = true;
        //update tmhoauth object
        /*$tmhOAuth->reconfigure(array_merge($tmhOAuth->config, array(
            'token'  => $res['oauth_creds']['oauth_token'],
            'secret' => $res['oauth_creds']['oauth_token_secret'],
        )));
        $res = getProfileImage($session->screen_name,$tmhOAuth);*/
    }else{
        $session->access_errorMessage = $res['errorMessage'];
    }
}

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
		<link href="../assets/css/bootstrap.min.css" rel="stylesheet">   

        <style type="text/css">
        body {
            padding-top: 40px;
            padding-bottom: 40px;
        }
        </style>
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
		<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="https://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>	
        
        <!-- Include all compiled plugins (below), or include individual files as needed -->
		<script src="../assets/js/bootstrap.min.js" type="text/javascript"></script>	
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
					<a class="navbar-brand" href="../">CoursePicker</a>
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
                <?php if (!isset($params['oauth_token']) && strlen($session->request_errorMessage) != 0) { ?>                    
                    <div class="alert alert-danger" id="authError">
                        <?php echo $session->request_errorMessage; ?>
                    </div>
                <?php } elseif (strlen($session->access_errorMessage) != 0) { ?>
                    <div class="alert alert-danger" id="authError">
                        <?php echo $session->access_errorMessage; ?>
                    </div>
                <?php } ?>
                <?php if (!isset($params['oauth_token']) && strlen($session->request_errorMessage) == 0) { ?>
                    <div class="alert alert-success" id="authSuccess">
                        <?php echo $session->authMessage; ?>
                    </div>
                <?php } elseif (strlen($session->access_errorMessage) == 0) { ?>
                    <div class="alert alert-success" id="authSuccess">
                        <?php 
                            //print_r($session->oauth_object);
                            echo $session->accessMessage;  
                            //print_r($session->twitter_user);
                        ?>
                        You can continue browsing CoursePicker.
                    </div>
                <?php } ?>
            </div><!--/row-->

			<hr>

            <footer>
                <p>&copy; <a href="http://janeullah.com" title="Jane Ullah">Jane Ullah 2014</a></p>
            </footer>

		</div><!--/.container-->
    <?php require_once("../includes/dialogs.inc") ?>	
    <?php require_once("../includes/analyticstracking.inc") ?>
  </body>
</html>
