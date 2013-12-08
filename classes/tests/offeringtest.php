<?php
require_once('../../includes/simpledom/simple_html_dom.php');
require_once('../helpers/Offering.php');
require_once('../helpers/ReportListingHelper.php');
$db = new ReportListingHelper();
$offering = new Offering(1,"course_offering_service_UNIV_201405.xls","Summer 2014","Athens","12/07/2013 07:19:29");
//print_r($offering);

//insertion
/*$result = $db->addOffering("course_offering_service_UNIV_201402.xls","Spring 2014","Athens","12/07/2013 07:19:29");
if (strcmp($db->errorMessage,"") == 0){
	echo $result, "<br/>";
}else{	
	echo $db->errorMessage;
}*/


$offeringAgain = $db->getSingleOffering(1);
if ($offeringAgain){
	print_r($offeringAgain);
}else{
	echo "Error: ", $db->errorMessage;
}

echo "<br/>";
echo "<br/>";

$results = array();
$results = $db->getOfferingByCampus("Athens");
print_r($results);
echo count($results);
echo "<br/>";
echo "<br/>";

$results = $db->getOfferingByTerm("Summer 2014");
print_r($results);
echo count($results);

?>
