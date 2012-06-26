<?php

$id = $_GET['id'];

$avail_fields = array('available', 'library', 'call_num', 'status', 'request');
$json = array();

$url = "http://webservices.lib.harvard.edu/rest/hollis/avail/$id";
	
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$libraries = curl_exec ($ch);
	
curl_close ($ch);
	
$avail = new SimpleXMLElement($libraries);

$any_available = 'false';

foreach($avail->branch as $branch) {
	$library = (string) $branch->repository->name;
	foreach($branch->collection as $collection) {
	  $call = (string) $collection->callnumber;
		foreach($collection->items->itemrecord as $itemrecord) {
		  if($itemrecord->call != '')
		    $call = (string) $itemrecord->call;
		  $status = (string) $itemrecord->stat;
		  $isavail = (string) $itemrecord->isavail;
		  if($isavail == 'Y') {
		    $any_available = 'true';
		    $isavail = true;
		  }
		  else
		    $isavail = false;
		  $request = (string) $itemrecord->req->attributes()->href;
		  //echo "$library $call $status $isavail $request<br />";
		  $avail_data   = array($isavail, $library, $call, $status, $request);
      $temp_array  = array_combine($avail_fields, $avail_data);
      array_push($json, $temp_array);
		}
	}
}

echo '{"any_available": ' . $any_available. ', "items": ' . json_encode($json) . '}'; 
?>