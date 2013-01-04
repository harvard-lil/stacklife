<?php
    error_reporting(E_ALL ^ E_NOTICE);

  require_once ('../../../etc/sl_ini.php');

  $q = $_GET['query'];
  $q = urlencode($q);
  $offset = $_GET['start'];
  $limit = $_GET['limit']; 
  $search_type = $_GET['search_type'];

  global $LIBRARYCLOUD_URL;

  $url = "$LIBRARYCLOUD_URL?key=$LIBRARYCLOUD_KEY&filter=$search_type:$q&limit=$limit&start=$offset";

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
    

  $contents = fetch_page($url);
    
  echo $contents;

function fetch_page($url) {

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$contents = curl_exec ($ch);
	
	curl_close ($ch);
	
	return $contents;
}
?>