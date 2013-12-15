<?php
require_once('../../creds/dhpath.inc');
require_once('simple_html_dom.php');

function getSSLPage($url,$referer,$filename) {
	$user_agent = "your user agent";
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
}


function download_csv_files() {
    $html = file_get_html('/tmp/staticReport.html');
    $result = array();
	$counter = 0;

    foreach($html->find('div.body tr') as $article) {
		$item = array();
        // get link
        $csvName = trim($article->find('td a', 0)->plaintext);
        $item['link'] = "https://apps.reg.uga.edu/reporting/static_reports/" . trim($article->find('td a', 0)->plaintext);
        // get term
        $item['term'] = trim($article->find('td', 1)->plaintext);
        // get campus
        $item['campus'] = trim($article->find('td', 2)->plaintext);
        // get time
        $item['time'] = trim($article->find('td', 3)->plaintext);

		if (!empty($csvName)){	
			//check extension
			$extension = substr($csvName, -3);
			$courseOffering = substr($csvName,0,15);
			if (strcmp($extension,"csv") == 0 and strcmp($courseOffering,"course_offering") == 0){
				echo "courseOffering: " . $courseOffering . " csvName: " . $csvName . " extension: " . $extension . " link: " . $item['link'] . "\n";
	        	$filename = HOME_DIR . "csv/coursepicker/csvfiles/" . $csvName;
	        	getSSLPage($item['link'],"http://your/url/",$filename);
			}
		}
		$counter++;
    }
    
    // clean up memory
    $html->clear();
    unset($html);
    return $result;
}
download_csv_files();


?>
