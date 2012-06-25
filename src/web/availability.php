<?php

$url = 'http://webservices.lib.harvard.edu/rest/hollis/avail/011500528';

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);

curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json'));

$contents = curl_exec ($ch);
	
curl_close ($ch);
	
return $contents;
?>