<?php
require_once("../models/User.php");
require_once("../helpers/UserHelper.php");
$db = new UserHelper();
$user = $db->getUser("jane");
$sUser = serialize($user);
print_r($sUser);
$dUser = unserialize($sUser);
print_r($dUser);
print_r($dUser->to_json());
?>
