<?php
require_once dirname(__FILE__) . '/../../creds/dhpath.inc';
require_once 'simple_html_dom.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Function to retrieve SSL pages
 * specifically the https://apps.reg.uga.edu/reporting/static_reports/
 * url which has something to d with the SSL version mismatch
 * 
 * @param string $url      https://apps.reg.uga.edu/reporting/static_reports/
 * @param string $referer  http://yourwebsite.com
 * @param string $filename /full/path/to/location
 * 
 * @return void it writes the downloaded csv files to disk
 * 
 */ 
function getSSLPage($url,$referer,$filename) {
	$user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:16.0) Gecko/20100101 Firefox/16.0";
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

/**
 * Method to get the entire webpage downloaded and parses it to get
 * the list of csv files to download. These are files that have 
 * course_offering in their name and end in .csv extension.
 * 
 * @return void
 * 
 */ 
function download_csv_files() {
    $html = file_get_html('/tmp/staticReport.html');
    $counter = 0;
    foreach($html->find('div.body tr') as $article) {
        //Skip the first line which is a th
        if ($counter > 1){
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
                //check extension.
                //Assumes extension is 3 letters and will return csv or xls
                $extension = substr($csvName, -3);
                //Get the first 15 letter which should be course_offering
                $courseOffering = substr($csvName,0,15);
                if (strcmp($extension,"csv") == 0 and strcmp($courseOffering,"course_offering") == 0){
                    echo "courseOffering: " . $courseOffering . " csvName: " . $csvName . " extension: " . $extension . " link: " . $item['link'] . "\n";
                    $filename = HOME_DIR . "csv/coursepicker/csvfiles/" . $csvName;
                    getSSLPage($item['link'],"http://apps.janeullah.com/coursepicker",$filename);
                }
            }
        }
        $counter++;
    }
    
    // clean up memory
    $html->clear();
    unset($html);
}

download_csv_files();
?>
