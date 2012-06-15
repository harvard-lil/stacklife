<?php

	$q = $_GET['query'];
  $q = urlencode($q);
  $offset = $_GET['start'];
  $limit = $_GET['limit']; 
    
  $json = array();
  
  $url = "http://hlsl7.law.harvard.edu/platform/v0.03/api/item/?filter=keyword:$q&limit=$limit&start=$offset";
    
  $contents = fetch_page($url);
    
  $book_data = json_decode($contents, true);
  
  $hits = $book_data['num_found'];
  
  $items = $book_data['docs'];
    
  $books_fields = array('title','creator','measurement_page_numeric','measurement_height_numeric', 'shelfrank', 'pub_date', 'link');
    
  foreach($items as $item) {
    $title = '';
    $author = '';
  
    $link = '/book/' . $item['title_link_friendly'] . '/' . $item['id'];
    $shelfrank = (int) $item['shelfrank'];
  
    $creator = $item['creator'];
    $title = $item['title'];
    $height_cm = $item['height_numeric'];
    if($height_cm > 33 || $height_cm < 20) $height_cm = rand(20, 33);
    $pages = $item['pages_numeric'];
    $year = $item['pub_date_numeric'];
    $year = substr($year, 0, 4);
    
    $books_data   = array($title, $creator, $pages, $height_cm, $shelfrank, $year, $link);
    $temp_array  = array_combine($books_fields, $books_data);
    array_push($json, $temp_array);
  }
    
  $last = $offset + 10;
    
  if(count($json) == 0 || $offset == -1) {
    echo '{"start": "-1", "num_found": "0", "limit": "0", "docs": ""}'; 
  }
  else {
    echo '{"start": ' . $last. ', "limit": "' . $limit . '", "num_found": "' . $hits . '", "docs": ' . json_encode($json) . '}'; 
  }

function fetch_page($url) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,
	$url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$contents = curl_exec ($ch);
	
	curl_close ($ch);
	
	return $contents;
}
?>