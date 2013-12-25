<?php
require_once dirname(__FILE__) . '/../../../../creds/coursepicker_debug.inc';
require_once dirname(__FILE__) . '/../../../../creds/mixpanel_coursepicker.inc';
require_once dirname(__FILE__) . '/../../includes/mixpanel/lib/Mixpanel.php';
$debug = DEBUGSTATUS;
if ($debug){
    ini_set("display_errors", 0);
    ini_set("log_errors", 1);
    //Define where do you want the log to go, syslog or a file of your liking with
    ini_set("error_log", ERROR_PATH);
}
// get the Mixpanel class instance, replace with your
// load production token
if (!$debug){
    $mp = Mixpanel::getInstance(CP_PROD_MIXPANEL_API_KEY);
}else{
    //load dev token
    $mp = Mixpanel::getInstance(CP_DEV_MIXPANEL_API_KEY);
}
print_r($mp);
echo CP_PROD_MIXPANEL_TOKEN . "<br/>";
echo CP_DEV_MIXPANEL_TOKEN . "<br/>";
?>
