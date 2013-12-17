<?php
require_once("../models/User.php");
require_once("../helpers/UserHelper.php");
$db = new UserHelper();
$user = $db->getUser("jane");
echo "Printing serialize user";
$sUser = serialize($user);
print_r($sUser);
$dUser = unserialize($sUser);
echo "====================<br/><br/><br/>";
echo "Printing unserialized user";
print_r($dUser);
echo "====================<br/><br/><br/>";
echo "Printing tojson of user";
print_r($dUser->to_json());
echo "====================<br/><br/><br/>";
echo $dUser instanceOf User;
echo "====================<br/><br/><br/>";
print_r($dUser->to_array());
?>
