<?php
require_once("../helpers/UserHelper.php");
$db = new UserHelper();
$user = $db->getUser("jane");
echo "Printing serialize user";
$sUser = serialize($user);
print_r($sUser);
$dUser = unserialize($sUser);
echo "Printing unserialized user";
print_r($dUser);
echo "Printing tojson of user";
print_r($dUser->to_json());
?>
