<?php
require_once dirname(__FILE__) . '/../../../creds/dhpath.inc';
require_once dirname(__FILE__) . '/../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../classes/helpers/session.php';
require_once dirname(__FILE__) . '/../includes/tmhoauth/tmhOAuth.php';

$result = array();
$session = new Session();

$tmhOAuth = new tmhOAuth();


$debug = DEBUGSTATUS;
//Set up debug stuff
//When  not debugging, log to a file!
if (!$debug) {
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
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

/**
 * Function to undo effects of magic quotes
 * Returns the $_POST value matching the provided key
 * 
 * @param string $var key in $_POST variable
 * 
 * @return string $val value matching $_POST['key']
 */
function getPost($var)
{
    $val = filter_var($_POST[$var], FILTER_SANITIZE_MAGIC_QUOTES);

    return $val;
}

function verifyCredentials($tmhOAuth){
    global $session;
    $results = array();
    $code = $tmhOAuth->user_request(array(
        'url' => $tmhOAuth->url('1.1/account/verify_credentials')
    ));

    if ($code == 200){
        $data = json_decode($tmhOAuth->response['response'], true);

        if (isset($data['status'])) {
            $code = $tmhOAuth->user_request(array(
                'url' => $tmhOAuth->url('1.1/statuses/oembed'),
                'params' => array(
                'id' => $data['status']['id_str']
                )
            ));

            if ($code == 200){
                $tweet = json_decode($tmhOAuth->response['response'], true);
                $results['tweets'] = $tweet;
            }
        }
        $results['name'] = $data['screen_name'];
        $results['html'] = $tweet['html'];
    }else{
        $results['errorMessage'] = $tmhOAuth->response['error'];
    }
    return $results;
}


$requestType = $_SERVER['REQUEST_METHOD'];
if ($requestType === 'POST') {
    //verify credential
    $tmhOAuth->reconfigure(array_merge($tmhOAuth->config, array(
        'token'  => $session->twitter_oauth_token,
        'secret' => $session->twitter_oauth_token_secret,
    )));

    $image = file_get_contents($session->imgUrl);
    $params = array(
        'media[]' => $image,
        'status'  => getPost('status')
    );    

        
    $code = $tmhOAuth->user_request(array(
        'method' => 'POST',
        'url' => $tmhOAuth->url("1.1/statuses/update_with_media"),
        'params' => $params,
         'multipart' => true
        ));

    if ($code == 200){
        $result['errorMessage'] = "";
        $data = json_decode($tmhOAuth->response['response'], true);
        $result['message'] = 'You just <a href="https://twitter.com/';
        $result['message'] .= htmlspecialchars($data['user']['screen_name']);
        $result['message'] .= '/statuses/';
        $result['message'] .= htmlspecialchars($data['id_str']);
        $result['message'] .= '">tweeted</a>';
        $result['data'] = $data;
    }else{        
        $result['errorMessage'] = $tmhOAuth->response['error'];//replace with 'error'
        $result['raw'] = $tmhOAuth->response['raw'];
    }
    $result['code'] = $code;
    $result['token'] = $session->twitter_oauth_token;
    $result['secret'] = $session->twitter_oauth_token_secret;
    /*$val = verifyCredentials($tmhOAuth);
    if ($val['code'] == 200){ 
        $image = file_get_contents($session->imgUrl);
        $params = array(
            'media[]' => "@{$image}",
            'status'  => getPost('status')
        );    

        
        $code = $tmhOAuth->user_request(array(
            'method' => 'POST',
            'url' => $tmhOAuth->url("1.1/statuses/update_with_media"),
            'params' => $params,
             true,
             true
        ));

        if ($code == 200){
            $result['errorMessage'] = "";
            $data = json_decode($tmhOAuth->response['response'], true);
            $result['message'] = 'You just <a href="https://twitter.com/';
            $result['message'] .= htmlspecialchars($data['user']['screen_name']);
            $result['message'] .= '/statuses/';
            $result['message'] .= htmlspecialchars($data['id_str']);
            $result['message'] .= '">tweeted</a>';
            $result['data'] = $data;
        }else{        
            $result['errorMessage'] = $tmhOAuth->response['error'];//replace with 'error'
            $result['raw'] = $tmhOAuth->response['raw'];
        }
        $result['code'] = $code;
        $result['token'] = $session->twitter_oauth_token;
        $result['secret'] = $session->twitter_oauth_token_secret;
    }else{
        $result['code'] = $va['code'];
        $result['errorMessage'] = $val['raw'];
    }*/
    echo json_encode($result);
}else{
    $result['errorMessage'] = "Invalid server request.";
    echo json_encode($result);
}
?>
