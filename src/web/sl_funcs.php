<?php	

require_once('../../etc/sl_ini.php');

if(isset($_REQUEST['function']) && $_REQUEST['function'] != '')
	call_user_func($_REQUEST['function']);

if(isset($_REQUEST['func']) && $_REQUEST['func'] != '')
	call_user_func($_REQUEST['func']);

if(isset($_REQUEST['search_type']) && $_REQUEST['search_type'] != '')
	call_user_func($_REQUEST['search_type']);


function session_info()
{
	session_start(); 

	if($_REQUEST['type'] == 'set') {
		if(isset($_REQUEST['uid'])) {
			foreach(array_reverse($_SESSION['books']) as $id => $past_book){
				if($id != $_REQUEST['uid']) {
					$_SESSION['books'][$_REQUEST['uid']]['link'] = $_SERVER['REQUEST_URI'];
				}
			}
		}
	}

}

function fetch_availability()
{
	$hollis = $_GET['hollis'];
	global $AVAILABILITY_URL;
	
	$url = $AVAILABILITY_URL . $hollis;

	$contents = fetch_page($url);
	
	$contents = str_replace('skin.ashx?u=img/info.png', '', $contents);
	$contents = str_replace('skin.ashx?u=img/available.png', '', $contents);
	$contents = str_replace('skin.ashx?u=img/map.png', '', $contents);
	$contents = str_replace('skin.ashx?u=img/requestable.png', '', $contents);

	echo $contents;
}

function fetch_tag_cloud()
{	
	connect_db();
	
	$uid        = trim($_REQUEST['uid']);	
	$biggest = 0;

	//query the database
  $query = mysql_query("SELECT tag, COUNT(tag) FROM sl_tags WHERE item_id = '$uid' GROUP BY tag ORDER BY COUNT(tag) DESC LIMIT 0,5");

	//start json object
	$json = "({ tags:["; 
	
	//loop through and return results
	if(mysql_num_rows($query) == 0)
		$json .= "]})";
	else {
  for ($x = 0; $x < mysql_num_rows($query); $x++) {
    
    $row = mysql_fetch_assoc($query);
	if($x == 0) $biggest = $row['COUNT(tag)'];	
		//continue json object
    $json .= "{tag:'" . $row["tag"] . "',freq:'" . $row["COUNT(tag)"] . "',biggest:'" . $biggest . "'}";
		
		//add comma if not last row, closing brackets if is
		if ($x < mysql_num_rows($query) -1)
			$json .= ",";
		else
			$json .= "]})";
  }
	}
	//return JSON with GET for JSONP callback
	$response = $_GET["callback"] . $json;
	echo $response;
	mysql_close();
}

function fetch_author_neighborhood()
{
	$related_authors_global = array();
	$author = addslashes($_GET['author']);
	$search_type = "creator";
	$q = urlencode($author);
	$url = "http://hlsl7.law.harvard.edu/platform/v0.03/api/item/?filter=$search_type:$q";	
	$contents = fetch_page($url);
	$json = json_decode($contents);
	// Fetch sort numbers for each book authored by current author
	foreach($json->docs as $doc)
	{
		$sort_nums = $doc->loc_call_num_sort_order;
		foreach($sort_nums as $sort_num)
		{
			// Fetch authors for each book by this author as well as books whose sort numbers lie adjacent to the sort number for each book by this author
			$search_type = "loc_call_num_sort_order";
			$q = $sort_num;
			$url_sort_num = "http://hlsl7.law.harvard.edu/platform/v0.03/api/item/?filter=$search_type:$q";	
			$contents_sort_num = fetch_page($url_sort_num);
			$json_sort_num = json_decode($contents_sort_num);	
			$related_authors = array();		
			foreach($json_sort_num->docs as $doc)
			{
				$related_authors = $doc->creator;
				foreach($related_authors as $related_author)
				{
					array_push($related_authors_global, $related_author);
				}
			}
			$sort_num_upstream = $sort_num - 1;
			$search_type = "loc_call_num_sort_order";
			$q = $sort_num_upstream;
			$url_sort_num = "http://hlsl7.law.harvard.edu/platform/v0.03/api/item/?filter=$search_type:$q";	
			$contents_sort_num = fetch_page($url_sort_num);
			$json_sort_num = json_decode($contents_sort_num);	
			$related_authors = array();		
			foreach($json_sort_num->docs as $doc)
			{
				$related_authors = $doc->creator;
				foreach($related_authors as $related_author)
				{
					array_push($related_authors_global, $related_author);
				}
			}
			$sort_num_downstream = $sort_num + 1;
			$search_type = "loc_call_num_sort_order";
			$q = $sort_num_downstream;
			$url_sort_num = "http://hlsl7.law.harvard.edu/platform/v0.03/api/item/?filter=$search_type:$q";	
			$contents_sort_num = fetch_page($url_sort_num);
			$json_sort_num = json_decode($contents_sort_num);	
			$related_authors = array();		
			foreach($json_sort_num->docs as $doc)
			{
				$related_authors = $doc->creator;
				foreach($related_authors as $related_author)
				{
					array_push($related_authors_global, $related_author);
				}
			}			
		}
	}
	$related_authors_global = array_unique($related_authors_global);
	sort($related_authors_global);		
	echo json_encode($related_authors_global);
}

function fetch_author_subjects()
{
	$author = addslashes($_GET['author']);
	$search_type = "creator";
	$q = urlencode($author);
	$url = "http://hlsl7.law.harvard.edu/platform/v0.03/api/item/?filter=$search_type:$q&facet=lcsh";	
	$contents = fetch_page($url);
	$json = json_decode($contents);
	$count = 0;
	$facets = array();
	foreach($json->facets->lcsh as $facet_name => $facet_freq)
	{
		array_push($facets, $facet_name);
	}
	if(count($facets) == 0) {
		$nocallnum = array();
		$FIELDSX = array('callno', 'nope');
	    $DATAX = array('none', 'nope');
		$_tmparr2 = array_combine($FIELDSX, $DATAX);
		array_push($nocallnum, $_tmparr2);
		echo json_encode($nocallnum);
	}
	else {
		echo json_encode($facets);
	}	
}

function text_call_num() 
{
  $to = $_GET["number"] . $_GET["carrier"];
  $title = $_GET['title'];
  $msg = $_GET["library"];
  //$sub = "Call #";
  $title_max = 140 - strlen($msg);
  $msg .= " ";
  $msg .= substr($title, 0, $title_max);
  require 'includes/class.phpmailer.php';
  
  $mail = new PHPMailer(true);
  $mail->SetFrom("shelflife@law.harvard.edu", 'ShelfLife');
  $mail->AddAddress($to);
  $mail->Body = $msg;
  
  $mail->Send();
}

function set_also_viewed()
{
	connect_db();	

	$also        = trim($_REQUEST['also']);
	$id        = trim($_REQUEST['id']);

  $addClient  = "INSERT INTO sl_also_viewed (book_one,book_two) VALUES ('$id','$also')";

  mysql_query($addClient) or die(mysql_error());
    
  $addClientReverse  = "INSERT INTO sl_also_viewed (book_one,book_two) VALUES ('$also','$id')";

  mysql_query($addClientReverse) or die(mysql_error());
  mysql_close();
}

function set_book_tag()
{
	connect_db();	

	// TAG INFORMATION
	$uid        = trim($_REQUEST['uid']);

	$tag_array = $_REQUEST['tags']; 
	$tags = explode(',', $tag_array); 

  foreach($tags as $tag){
    $tag = strtolower(trim($tag));
    $addClient  = "INSERT INTO sl_tags (item_id,tag) VALUES ('$uid','$tag')";
    mysql_query($addClient) or die(mysql_error());
  }
  mysql_close();
}	

function check_amazon()
{
	$isbn = $_GET['isbn'];
	
	if (strlen($isbn) == 13) {
	   $isbn = convertFromIsbn13ToIsbn10($isbn);
	}
	
	global $AMAZON_KEY;
  global $AMAZON_SECRET_KEY;

	$public_key = $AMAZON_KEY;
  $private_key = $AMAZON_SECRET_KEY;
  $pxml = aws_signed_request("com", array("Operation"=>"ItemLookup","ItemId"=>$isbn,"ResponseGroup"=>"ItemAttributes"), $public_key, $private_key);
   	if ($pxml === False)
   	{
   		echo 'false';
   	}
  	else
  	{ 
  		if (isset($pxml->Items->Item->ASIN))
  		{
  			$img_url = $pxml->Items->Item->ASIN;
  			echo $img_url;
  		}
  		else
       	{
           	echo 'false';
       	}
   	}
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

function connect_db() {
	global $hostName;
	global $userName;
	global $pw;
			
	if(!($link=mysql_pconnect($hostName, $userName, $pw))) 
	{
		echo "before error<br />";
		echo "error connecting to host";
		exit;
	}
	else
	
	mysql_select_db("sl");
	
	// Following directive is essential for proper utf-8 resolution at the client
	$set_utf8_query = "SET NAMES 'utf8'";
	//print "here is set_utf8_query: [$set_utf8_query]<br />";
	$result_utf8 = mysql_query($set_utf8_query, $link);
	
	if (!$result_utf8) 
	{
		echo 'Could not run set_utf8_query: ' . mysql_error();
		//exit;
	}
}

function convertFromIsbn13ToIsbn10($isbn13OrEAN)
{
    $isbn10 = "";
    if ($isbn13OrEAN==null)
    {
        return false;        
    }
    $isbn13OrEAN = str_replace(" ","",str_replace("-","",$isbn13OrEAN));
    $isbnLen=strlen($isbn13OrEAN);
    if ($isbnLen!=13)
    {
        //Invalid length
        return false;
    }

    $isbn10 = substr($isbn13OrEAN,3,9);
    $sum = 0;
    $isbnLen=strlen($isbn10);

    for ($i = 0; $i < $isbnLen; $i++) 
    {
        $current = substr($isbn10,$i,1);
        if($current<0||$current>9)
        {
            //Invalid ISBN
            return false;
        }
        $sum+= $current*(10-$i);
    }
    $modulu = $sum%11;
    $checkDigit = 11 - $modulu;

    //if the checkDigit is 10 should be x
    if ($checkDigit==10)
        $isbn10 .= 'X';
    else if($checkDigit==11)
        $isbn10 .= '0';
    else
        $isbn10 .= $checkDigit;
        
    return $isbn10;
}
function aws_signed_request($region, $params, $public_key, $private_key)
{
    /*
    Copyright (c) 2009 Ulrich Mierendorff

    Permission is hereby granted, free of charge, to any person obtaining a
    copy of this software and associated documentation files (the "Software"),
    to deal in the Software without restriction, including without limitation
    the rights to use, copy, modify, merge, publish, distribute, sublicense,
    and/or sell copies of the Software, and to permit persons to whom the
    Software is furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
    THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
    FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
    DEALINGS IN THE SOFTWARE.
    */
    
    /*
    Parameters:
        $region - the Amazon(r) region (ca,com,co.uk,de,fr,jp)
        $params - an array of parameters, eg. array("Operation"=>"ItemLookup",
                        "ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
        $public_key - your "Access Key ID"
        $private_key - your "Secret Access Key"
    */

    // some paramters
    $method = "GET";
    $host = "ecs.amazonaws.".$region;
    $uri = "/onca/xml";
    
    // additional parameters
    $params["Service"] = "AWSECommerceService";
    $params["AWSAccessKeyId"] = $public_key;
    // GMT timestamp
    $params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
    // API version
    $params["Version"] = "2011-08-01";
    
    global $AMAZON_ASSOC_TAG;
    
    $params["AssociateTag"] = $AMAZON_ASSOC_TAG;

    // sort the parameters
    ksort($params);
    
    // create the canonicalized query
    $canonicalized_query = array();
    foreach ($params as $param=>$value)
    {
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $canonicalized_query[] = $param."=".$value;
    }
    $canonicalized_query = implode("&", $canonicalized_query);
    
    // create the string to sign
    $string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
    
    // calculate HMAC with SHA256 and base64-encoding
    $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, True));
    
    // encode the signature for the request
    $signature = str_replace("%7E", "~", rawurlencode($signature));
    
    // create request
    $request = "http://".$host.$uri."?".$canonicalized_query."&Signature=".$signature;
    
    // do request
    $response = @file_get_contents($request);
    
    if ($response === False)
    {
        return False;
    }
    else
    {
        // parse XML
        $pxml = simplexml_load_string($response);
        if ($pxml === False)
        {
            return False; // no xml
        }
        else
        {
            return $pxml;
        }
    }
}
?>
