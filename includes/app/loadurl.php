<?php
error_reporting(E_ALL);


function getSSLPage($url,$referer) {
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:16.0) Gecko/20100101 Firefox/16.0";
	$filename = "./staticReports.html";
	$fp = fopen($filename,"w");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_REFERER,$referer);
    curl_setopt($ch, CURLOPT_SSLVERSION,3); 
    $contents = curl_exec($ch);
	fwrite($fp, $contents);
	//$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($ch);
	fclose($fp);
    return $contents;
}

$url = 'https://apps.reg.uga.edu/reporting/staticReports';
$data = getSSLPage($url,"http://www.janeullah.com"); //Get the entire contents of url;

