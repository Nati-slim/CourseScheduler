<?php
require_once("../models/User.php");
require_once("../helpers/UserHelper.php");
$db = new UserHelper();
$user = $db->getUser("dummy");
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

echo "====================<br/><br/><br/>";
echo "====================<br/><br/><br/>";
$result = $db->validateToken($user,"5b2ed02853fab51419b3ca4a06eb365a0a548863");
print_r($result);
?>
