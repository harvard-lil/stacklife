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
	ini_set('display_errors',1);
	error_reporting(E_ALL ^ E_NOTICE);
	//error_reporting(0);
	
	connect_db();
	mysql_select_db("sl");
     
	$count = 0;
	$FIELDS = array('title','author','isbn','callnum','pages','heightCm', 'url', 'circ');
	$JSON = array();

	$author = addslashes($_GET['author']);
	//$author = "palfrey, john g.";
	// This is a kluje till we can get all author fetching coming from the local data instead of sru.ashx
	//$author = preg_replace("/([a-z\d])$/", "$1.", $author);
	//echo "author: [$author]<br />";

	// Fetch Hollis ID's of all titles authored/edited by this author
	$author_select_query = "
	SELECT Marc001
	FROM sl_bib_data
	WHERE (Marc100 = '$author'
	OR Marc700 = '$author'
	OR Marc110 = '$author'
	OR Marc710 = '$author'
	OR Marc111 = '$author'
	OR Marc711 = '$author')
	";
	//print "author_select_query: [$author_select_query]\n";
	$result = mysql_query($author_select_query);
	if (!$result) 
	{
 		echo 'Could not run author_select_query: ' . mysql_error();
	} 
	$other_titles_by_author = array();
	$other_titles_by_author_adjacencies = array();
	$related_authors = array();
	while ($row = mysql_fetch_row($result))
	{
		$hollis_id = $row[0];
		array_push($other_titles_by_author, $hollis_id);
		
		// Check sl_stackview to see if this Hollis ID is a member (requires that this Hollis ID have
		// an LC call number)
		/*
		$sl_stackview_membership_select_query = "
		SELECT RecordID
		FROM sl_stackview
		WHERE HollisID = '$hollis_id'
		";
		*/
		$sl_stackview_membership_select_query = "
		SELECT RecordID
		FROM sl.sl_lc_call_num
		WHERE HollisID = '$hollis_id'
		";
		//print "sl_stackview_membership_select_query: [$sl_stackview_membership_select_query]\n";
		$result_inner = mysql_query($sl_stackview_membership_select_query);
		if (!$result_inner) 
		{
	 		echo 'Could not run sl_stackview_membership_select_query: ' . mysql_error();
		} 
	
		// Now fetch upstream and downstream adjacent Hollis ID's and push them out to collection array
		if ($row_inner = mysql_fetch_row($result_inner))
		{
			$reference_record_id = $row_inner[0];
			$record_id_adjacent_upstream = $reference_record_id - 1;
			$record_id_adjacent_downstream = $reference_record_id + 1;
			//echo "record_id_adjacent_upstream: [$record_id_adjacent_upstream]\n";
			//echo "record_id_adjacent_downstream: [$record_id_adjacent_downstream]\n";
			// Now fetch from sl_stackview the 2 adjacent Hollis ID's
			/*
			$sl_stackview_adjacency_select_query = "
			SELECT HollisID
			FROM sl_stackview
			WHERE (RecordID = $record_id_adjacent_upstream
			OR RecordID = $record_id_adjacent_downstream)
			";
			*/
			$sl_stackview_adjacency_select_query = "
			SELECT HollisID
			FROM sl.sl_lc_call_num
			WHERE (RecordID = $record_id_adjacent_upstream
			OR RecordID = $record_id_adjacent_downstream)
			";
			//print "sl_stackview_adjacency_select_query: [$sl_stackview_adjacency_select_query]\n";
			$result_inner_2 = mysql_query($sl_stackview_adjacency_select_query);
			if (!$result_inner_2) 
			{
		 		echo 'Could not run sl_stackview_adjacency_select_query: ' . mysql_error();
			} 	
			while ($row_inner_2 = mysql_fetch_row($result_inner_2))
			{
				$hollis_id_adjacent = $row_inner_2[0];
				array_push($other_titles_by_author_adjacencies, $hollis_id_adjacent);
				
				$adjacent_authors_select_query = "
				SELECT Marc100, Marc700, Marc110, Marc710, Marc111, Marc711
				FROM sl_bib_data
				WHERE Marc001 = '$hollis_id_adjacent'
				";
				//print "adjacent_authors_select_query: [$adjacent_authors_select_query]\n";
				$result_inner_3 = mysql_query($adjacent_authors_select_query);
				if (!$result_inner_3) 
				{
			 		echo 'Could not run adjacent_authors_select_query: ' . mysql_error();
				} 	
				while ($row_inner_3 = mysql_fetch_row($result_inner_3))
				{ 
					if ($marc_100 = $row_inner_3[0]) array_push($related_authors, $marc_100);
					if ($marc_700 = $row_inner_3[1])
					{
						// Authors are separated by commas because of series format => need to remove these
						$temp = explode("%%", $marc_700);
						foreach($temp as $item)
						{
							$item = trim(preg_replace("/([^-]),\s*$/", "$1.", $item));
							$item = trim(preg_replace("/-,\s*$/", "-", $item));
							array_push($related_authors, $item);	
						}	
					}	
					if ($marc_710 = $row_inner_3[3])
					{
						// Authors are separated by commas because of series format => need to remove these
						$temp = explode("%%", $marc_710);
						foreach($temp as $item)
						{
							$item = trim(preg_replace("/([^-]),\s*$/", "$1.", $item));
							$item = trim(preg_replace("/-,\s*$/", "-", $item));
							array_push($related_authors, $item);	
						}	
					}	
					if ($marc_110 = $row_inner_3[2]) array_push($related_authors, $marc_110);
					//if ($marc_710 = $row_inner_3[3]) array_push($related_authors, $marc_710);
					if ($marc_111 = $row_inner_3[4]) array_push($related_authors, $marc_111);
					if ($marc_711 = $row_inner_3[5]) array_push($related_authors, $marc_711);	
				}				
			}
		}	
	}	

	//print_r($other_titles_by_author);
	//print_r($other_titles_by_author_adjacencies);
	//print_r($related_authors);
	
	$related_authors = array_unique($related_authors);
	sort($related_authors);
	//print_r($related_authors);
	echo json_encode($related_authors);
	mysql_close();
}

function fetch_author_subjects()
{
	$author = addslashes($_GET['author']);
	$q = "'" . $author . "%'";

	connect_db();
	mysql_select_db("sl");
	
	//$hollis_id = preg_replace("/^0+/", "", $hollis_id);

    $authorList  = "SELECT Marc001
    				FROM sl_bib_data
    				WHERE Marc100 LIKE $q OR Marc700 LIKE $q OR Marc110 LIKE $q OR Marc710 LIKE $q OR Marc111 LIKE $q OR Marc711 LIKE $q";
    
    //echo $authorList;
    $author_result = mysql_query($authorList);
    $author_books = array();
    while($row = mysql_fetch_row($author_result)) {
    	$hollis = $row[0];
    	$hollis_length = strlen($hollis);
    	if($hollis_length != 9) {
			$loop = 9 - $hollis_length;
			for($j=0; $j<$loop; $j++){
				$hollis = '0'.$hollis;
			}
		}
    	array_push($author_books, $hollis);
    }
    $number_books = count($author_books);
    if($number_books == 0)
    	$where = "WHERE 1 = 2";
    else
	    $where = "WHERE ";
    $count_books = 1;
    foreach($author_books as $hollis_id) {
    	$where .= "Marc001 = '$hollis_id' ";
    	if($count_books < $number_books)
    		$where .= " OR ";
    	$count_books++;
    }
	
	$query = "SELECT DISTINCT Subject
	FROM sl_bib_data_subject_search
	$where
	";
	//print "sl_stackview_select_query: [$sl_stackview_select_query]<br />";
	$result = mysql_query($query);
	if (!$result) 
	{
		echo 'Could not run sl_stackview_select_query: ' . mysql_error();
	} 	

	$json = array();
	while($row = mysql_fetch_row($result))
	{
		$subject = $row[0];
		$subject = preg_replace("/\.\s*$/", "", $subject);
		
		array_push($json, $subject);
	}
	
	if(count($json) == 0) {
		$nocallnum = array();
		$FIELDSX = array('callno', 'nope');
	    $DATAX = array('none', 'nope');
		$_tmparr2 = array_combine($FIELDSX, $DATAX);
		array_push($nocallnum, $_tmparr2);
		echo json_encode($nocallnum);
	}
	else {
		echo json_encode($json);
	}
	mysql_close();
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
  require '../../etc/class.phpmailer.php';
  
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

// Check the memcached instance for our data
// if we have it, return it, else, return false
function get_memcached($key) {
    global $enable_memcached_caching;
    global $memcached_host_name;
    global $memcached_port;

    if ($enable_memcached_caching) {
        $m = new Memcached();
        $m->addServer($memcached_host_name , $memcached_port);

        //  if (!$m->get('fetch_latest_views')){
        // If we have they key in memcached, return it, otherwise, return false
        if (!$m->get($key)) {
            return $m->get($key);
        }
    } else {
      return false;
    }
}

// Set data in our memcahced instance
//function set_memcached($key, $data) {
//    global $enable_memcached_caching;
//    global $memcached_host_name;
//    global $memcached_port;
//  
//    $m = new Memcached();
//    $m->addServer($memcached_host_name , $memcached_port);

//  if (!$m->get('fetch_latest_views')){
//    if (!$m->get($key)){
  
  	//$hollis_id = $_GET['hollis'];

	
//  $m->set('fetch_latest_views', $json);
//
//} else {
//  echo  $_GET['callback'] . '({"start": ' . 100 . ', "limit": "' . 100 . '", "num_found": "' . 3897 . '", "docs": ' . json_encode($m->get('fetch_latest_views')) . '})';
//}
//}

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
