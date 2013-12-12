<?php

$uga_file = file_get_contents("../../assets/json/uga_building_names.json");
$uga_buildings = json_decode($uga_file,true);
print_r(json_encode($uga_buildings));
//print_r($uga_buildings);
?>
