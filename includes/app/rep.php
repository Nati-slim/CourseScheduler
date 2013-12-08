<?php
include_once('../simpledom/simple_html_dom.php');

function scraping_report() {
    // create HTML DOM
    $html = file_get_html('./staticReports.html');
    $ret = array();
	$counter = 0;

    // get news block
    foreach($html->find('div.body tr') as $article) {
		$item = array();
        // get link
        $item['link'] = trim($article->find('td', 0)->plaintext);
        // get term
        $item['term'] = trim($article->find('td', 1)->plaintext);
        // get campus
        $item['campus'] = trim($article->find('td', 2)->plaintext);
        // get time
        $item['time'] = trim($article->find('td', 3)->plaintext);

		if (!empty($item['link'])){	
			//check extension
			$extension = substr($item['link'], -3);	
			if (strcmp($extension,"csv") == 0){
	        	$ret[] = $item;
			}
		}
		$counter++;
    }
    
    // clean up memory
    $html->clear();
    unset($html);
    return $ret;
}


// -----------------------------------------------------------------------------
// test it!


$ret = scraping_report();

foreach($ret as $v) {
    echo '<ol>';
    echo '<li>Link: '.$v['link'].'</li>';
    echo '<li>Term: '.$v['term'].'</li>';
    echo '<li>Campus: '.$v['campus'].'</li>';
    echo '<li>Time: '.$v['time'].'</li>';
    echo '</ol>';
}

?>
