<?php

  require_once ('../../../etc/sl_ini.php');

  $q = $_GET['query'];
  $q = urlencode($q);
  $offset = $_GET['start'];
  $limit = $_GET['limit']; 
  $search_type = $_GET['search_type'];
  $sort = urlencode($_GET['sort']);

  global $LIBRARYCLOUD_URL;

  $url = "$LIBRARYCLOUD_URL?filter=$search_type:$q&limit=$limit&start=$offset&sort=$sort";

  // Get facets and filters
  // TODO: This is ugly. Clean this stuff up.
  $incoming = $_SERVER['QUERY_STRING'];
  $facet_list = array();
  foreach (explode('&', $incoming) as $pair) {
      list($key, $value) = explode('=', $pair);
      if ($key == 'facet') {
          $url = $url . "&facet=" . $value;
    }
  }
  
  $filter_list = array();
  $filter_string = '';
    foreach (explode('&', $incoming) as $pair) {
        list($key, $value) = explode('=', $pair);
        if ($key == 'filter') {
            $url = $url . "&filter=" . $value;
      }
    }
    
  $json = array();

  $contents = fetch_page($url);
    
  $book_data = json_decode($contents, true);

  $hits = $book_data['num_found'];
  
  $items = $book_data['docs'];
 
  $facets = $book_data["facets"];
    
  $books_fields = array('id', 'title','creator','measurement_page_numeric','measurement_height_numeric', 'shelfrank', 'pub_date', 'title_link_friendly', 'format', 'loc_call_num_sort_order', 'link');
    
  foreach($items as $item) {
    $title = '';
    $author = '';
    
    $id = $item['id'];
  
    $title_link_friendly = $item['title_link_friendly'];
    $shelfrank = (int) $item['shelfrank'];
  
    $creator = $item['creator'];
    $title = $item['title'];
    $height_cm = $item['height'];
    if(!$height_cm || $height_cm > 33 || $height_cm < 20) $height_cm = 27;
    $pages = $item['pages'];
    if(!$pages) $pages = 200;
    $year = $item['pub_date_numeric'];
    $year = substr($year, 0, 4);
    $format = $item['format'];
    //$format = str_replace(" ", "", $format);
    $loc_sort_order = $item['loc_call_num_sort_order'];
    $link = "$www_root/item/$title_link_friendly/$id";
    
    $books_data   = array($id, $title, $creator, $pages, $height_cm, $shelfrank, $year, $title_link_friendly, $format, $loc_sort_order, $link);
    $temp_array  = array_combine($books_fields, $books_data);
    array_push($json, $temp_array);
  }
    
  $last = $offset + 10;
    
    header('Content-type: application/json');
  if(count($json) == 0 || $offset == -1) {
    echo '{"start": "-1", "num_found": ' . $hits . ', "limit": "0", "docs": ""}'; 
  }
  else {
    echo '{"start": ' . $last. ', "limit": "' . $limit . '", "num_found": ' . $hits . ', "docs": ' . json_encode($json) . ', "facets": ' . json_encode($facets) . '}'; 
  }

function fetch_page($url) {

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$contents = curl_exec ($ch);
	
	curl_close ($ch);
	
	return $contents;
}
?>