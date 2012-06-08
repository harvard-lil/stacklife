<?php	

require_once('../../sl_ini.php');

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
		if(isset($_REQUEST['stackdisplay']))
			$_SESSION['stackdisplay'] = $_REQUEST['stackdisplay'];
		if(isset($_REQUEST['uid'])) {
			foreach(array_reverse($_SESSION['books']) as $id => $past_book){
				if($id != $_REQUEST['uid']) {
					$_SESSION['books'][$_REQUEST['uid']]['link'] = $_SERVER['REQUEST_URI'];
				}
			}
		}
	}
	
	if($_REQUEST['type'] == 'get') {
		if(isset($_SESSION['stackdisplay']))
    		$_SESSION['stackdisplay'] = $_SESSION['stackdisplay'];
		else
    		$_SESSION['stackdisplay'] = 'spines';
    		
		echo $_SESSION['stackdisplay'];
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

function fetch_worldcat_data()
{
	global $WORLDCAT_KEY;
    
	$oclcnum = $_GET['oclcnum'];
	$type = $_GET['type'];

	if($type == 'summary')
		$url = "http://www.worldcat.org/webservices/catalog/content/". $oclcnum . "?wskey=" . $WORLDCAT_KEY;
	if($type == 'count')
		$url = "http://www.worldcat.org/wcpa/servlet/org.oclc.lac.ui.ajax.ServiceServlet?wcoclcnum=". $oclcnum . "&ht=edition&serviceCommand=holdingsdata";
	if($type == 'review')
		$url = "http://www.worldcat.org/webservices/catalog/content/". $oclcnum . "?wskey=" . $WORLDCAT_KEY;

	$contents = fetch_page($url);
	
	//$contents = preg_replace('/\/wcpa\/*$/', '', $contents);
	$contents = preg_replace('/<img/', '', $contents);

	echo $contents;
}

function format_stack_json($result, $home = '', $type = 'book', $label = '')
{
	$json = array();
	
	$books_fields = array('title','creator','measurement_page_numeric','measurement_height_numeric', 'title_link_friendly', 'shelfrank', 'pub_date', 'format', 'id_oclc', 'id_isbn', 'id', 'rsrc_value', 'loc_sort_order', 'publisher', 'desc_subject_lcsh', 'aggregation_checkout', 'source', 'wp_categories', 'wp_url', 'id_inst');

	while($row = mysql_fetch_array($result))
	{
		$uid = $row[0];

		$title = $row[1];
		$link_title = link_title($title);
		
  		//if($row[6] == 'English')
  		//	$title = title_case($title);
		
		$author = '';
		$creator = array();
		if(isset($row[2]))
			$authors = explode('%%', $row[2]);

		foreach($authors as &$author) {
			$author = preg_replace("/\.$/", '', $author);
			array_push($creator, $author);
		}
	
		$pages = $row[3]; //echo "pages preprocessing: $pages <br />";
		// Assume for the moment that all page indications are followed by possible white space and the letter "p" (without "p" initiating a new word)
		if(preg_match("/(\d*)\s*p\.*/", $pages, $match) || preg_match("/(\d*)/", $pages, $match))
		$pages = $match[1]; //echo 'pages: ' . $pages . '<br />';
		if ($pages == "" || $pages < 200) $pages = 200;
		if ($pages > 540) $pages = 540;
	
		$height_cm = $row[4];
		// Assume for the moment that all heights will be followed by possible white space and then "cm"
		preg_match("/^(\d*)/", $height_cm, $match);
		$height_cm = $match[1]; //echo 'height: ' . $height_cm . '<br />';
		if ($height_cm == "" || $height_cm < 20) $height_cm = 20;
		if ($height_cm > 39) $height_cm = 39;
	
		$shelfrank = $row[5];
		
		$year = $row[7];
		
		$format = $row[8];
		
		$oclc = null;
		$oclc = $row[10];
		
		if(isset($row[11]))
			$rsrc_value = array($row[11]);
		else
			unset($rsrc_value);
		
		$sort_order = $row[12];
		
		$publisher = $row[13];
		
		$subjects = array();
		if(isset($row[14]))
			$subject_array = explode('%%', $row[14]);
		
		foreach($subject_array as $subject) {
			array_push($subjects, $subject);
		}
		
			
		if(isset($row[15]))
			$checkouts = array($row[15]);
		else
			unset($checkouts);
		
		$source = $row[16];
			
		if(isset($row[17]))
			$wp_url = $row[17];
		else
			unset($wp_url);
			
		$wp_categories = array();
		if(isset($row[18])) {
			$wp_categories_pieces = explode('%%', $row[18]);
			foreach($wp_categories_pieces as $wp_category) {
				array_push($wp_categories, $wp_category);
			}
		}
		else
			unset($wp_categories);
		
		$id_inst = $row[19];

		$isbn = array();
		if(isset($row[9])) { 
			$isbn_full = explode('%%', $row[9]);
			$isbn_temp = $isbn_full[0];
			$isbn_full = explode(' ', $isbn_temp);
			array_push($isbn, $isbn_full[0]);
		}

		$books_data   = array($title, $creator, $pages, $height_cm, $link_title, $shelfrank, $year, $format, $oclc, $isbn, $uid, $rsrc_value, $sort_order, $publisher, $subjects, $checkouts, $source, $wp_categories, $wp_url, $id_inst);
		$temp_array  = array_combine($books_fields, $books_data);
		array_push($json, $temp_array);
	}
	return $json;
}

function fetch_trending_stack() {
	fetch_custom_stack('sl_custom_stack');
}

function fetch_custom_stack($table)
{
	$offset = $_GET['start'];
	if (!isset($_GET['limit'])) $count = 11;
	else $count = $_GET['limit'];
	$neighborhood = array();
	
	if($table == 'sl_custom_stack') $order = 'ORDER BY shelfrank DESC';
	
	$limit = "LIMIT $offset, $count";

	connect_db();
	
	mysql_select_db("sl");
	
	$count_query = "SELECT COUNT(DISTINCT id) 
    				FROM $table  
	";
	//echo $count_query;
	$hits = 0;
	$count_result = mysql_query($count_query);
	$row = mysql_fetch_row($count_result);
	$hits = $row[0];
	$last = $offset + $count;
	
	//$hollis_id = preg_replace("/^0+/", "", $hollis_id);

    $userList  = "SELECT id
    				FROM $table 
    				$limit";
    
    //echo $userList;
    $user_result = mysql_query($userList);
    $user_books = array();
    while($row = mysql_fetch_row($user_result)) {
    	$uid = $row[0];
    	array_push($user_books, $uid);
    }
    $number_books = count($user_books);
    if($number_books == 0)
    	$where = "WHERE 1 = 2";
    else
	    $where = "WHERE ";
    $count_books = 1;
    foreach($user_books as $uid) {
    	//$where .= "Marc001 = '$hollis_id' ";
    	$where .= "id = '$uid' ";
    	if($count_books < $number_books)
    		$where .= " OR ";
    	$count_books++;
    }
	
	if (!isset($_GET['callback'])) $callback = "";
	else $callback = $_GET['callback'];
	
	mysql_select_db("sl");
	
	$sl_stackview_select_query = "SELECT id, title, creator, measurement_page_numeric, measurement_height_numeric, shelfrank, language, pub_date, format, id_isbn, id_oclc, rsrc_value, loc_sort_order, publisher, desc_subject_lcsh, aggregation_checkout, source, wp_url, wp_categories, id_inst
	FROM sl.item
	$where
	$order
	";
	//print "sl_stackview_select_query: [$sl_stackview_select_query]<br />";
	$result = mysql_query($sl_stackview_select_query);
	if (!$result) 
	{
		echo 'Could not run sl_stackview_select_query: ' . mysql_error();
	} 	

	$json = array();
	$json = format_stack_json($result, '', '', $label);	
	
	if(count($json) == 0 || $offset == -1) {
		if ($callback) echo $callback . ' ({"start": "0", "num_found": "0", "limit": "0", "docs": ""})'; 
		else echo '({"start": "0", "num_found": "0", "limit": "0", "docs": ""})';
	}
	else {
		if ($callback) echo $callback . '({"start": ' . $last. ', "limit": "' . $count . '", "num_found": "' . $hits . '", "docs": ' . json_encode($json) . '})'; 
		else echo '({"start": ' . $last . ', "limit": "' . $count . '", "num_found": "' . $hits . '", "docs": ' . json_encode($json) . '})';
	}
	mysql_close();
}

function fetch_recently_viewed()
{
	fetch_recently_neighborhood('book');
}

function fetch_recently_viewed_auth()
{
	fetch_recently_neighborhood('author');
}

function fetch_recently_neighborhood($type)
{
	$user_books = array_unique($_GET['recently']);
	$offset = $_GET['start'];
	
	connect_db();
	
	$neighborhood = array();

    $number_books = count($user_books);
    
    if($number_books == 0)
    	$where = "WHERE 1 = 2";
    else
	    $where = "WHERE ";
    $count_books = 1;
    foreach($user_books as $item_id) {
    	//$where .= "Marc001 = '$hollis_id' ";
    	$where .= "id = '$item_id' ";
    	if($count_books < $number_books)
    		$where .= " OR ";
    	$count_books++;
    }
    
    $home = 'not_used';
	
	if (!isset($_GET['callback'])) $callback = "";
	else $callback = $_GET['callback'];
	
	mysql_select_db("sl");
	
	$sl_stackview_select_query = "SELECT id, title, creator, measurement_page_numeric, measurement_height_numeric, shelfrank, language, pub_date, format, id_isbn, id_oclc, rsrc_value, loc_sort_order, publisher, desc_subject_lcsh, aggregation_checkout, source, wp_url, wp_categories, id_inst
	FROM sl.item
	$where
	LIMIT $offset, $number_books
	";
	//print "sl_stackview_select_query: [$sl_stackview_select_query]<br />";
	$result = mysql_query($sl_stackview_select_query);
	if (!$result) 
	{
		echo 'Could not run sl_stackview_select_query: ' . mysql_error();
	} 	

	$json = array();
	$json = format_stack_json($result, '', $type, 'Your recently viewed items');
	
	
	if(count($json) == 0 || $offset == -1) {
		if ($callback) echo $callback . ' ({"start": "0", "num_found": "0", "limit": "0", "docs": ""})'; 
		else echo '({"start": "0", "num_found": "0", "limit": "0", "docs": ""})';
	}
	else {
		if ($callback) echo $callback . '({"start": "0", "limit": "' . $number_books . '", "num_found": "' . $number_books . '", "docs": ' . json_encode($json) . '})'; 
		else echo '({"start": "0", "limit": "' . $number_books . '", "num_found": "' . $number_books . '", "docs": ' . json_encode($json) . '})';
	}
	mysql_close();
}

function fetch_also_neighborhood()
{
	fetch_user_neighborhood('book_also_views');
}

function fetch_friend_neighborhood()
{
	fetch_user_neighborhood('book_friends');
}

function fetch_user_neighborhood($type)
{
	$uid = $_GET['id'];
	$offset = $_GET['start'];
	if (!isset($_GET['limit'])) $count = 11;
	else $count = $_GET['limit'];
	//$type = $_GET['type'];
	//$type = 'hreg.book_also_views';
	$uid_home = $uid;
	$neighborhood = array();
	
	$limit = "LIMIT $offset, $count";

	connect_db();
	
	$count_query = "SELECT COUNT(DISTINCT book_two) 
    				FROM $type 
    				WHERE book_one = '$uid' 
	";
	//echo $count_query;
	$hits = 0;
	$count_result = mysql_query($count_query);
	$row = mysql_fetch_row($count_result);
	$hits = $row[0];
	$last = $offset + $count;
	
	//$hollis_id = preg_replace("/^0+/", "", $hollis_id);

    $userList  = "SELECT COUNT(*), book_two 
    				FROM $type 
    				WHERE book_one = '$uid' 
    				GROUP BY book_two
    				ORDER BY COUNT(*) DESC
    				$limit";
    
    //echo $userList;
    $user_result = mysql_query($userList);
    $user_books = array();
    while($row = mysql_fetch_row($user_result)) {
    	$uid = $row[1];
    	array_push($user_books, $uid);
    }
    $number_books = count($user_books);
    if($number_books == 0)
    	$where = "WHERE 1 = 2";
    else
	    $where = "WHERE ";
    $count_books = 1;
    foreach($user_books as $uid) {
    	//$where .= "Marc001 = '$hollis_id' ";
    	$where .= "id = '$uid' ";
    	if($count_books < $number_books)
    		$where .= " OR ";
    	$count_books++;
    }
    
    $home = 'not_used';
    if($type == 'book_also_views')
    	$label = 'People who viewed this also viewed these';
    else
    	$label = 'Read this too';
	
	if (!isset($_GET['callback'])) $callback = "";
	else $callback = $_GET['callback'];
	
	mysql_select_db("sl");
	
	$sl_stackview_select_query = "SELECT id, title, creator, measurement_page_numeric, measurement_height_numeric, shelfrank, language, pub_date, format, id_isbn, id_oclc, rsrc_value, loc_sort_order, publisher, desc_subject_lcsh, aggregation_checkout, source, wp_url, wp_categories, id_inst
	FROM sl.item
	$where
	";
	//print "sl_stackview_select_query: [$sl_stackview_select_query]<br />";
	$result = mysql_query($sl_stackview_select_query);
	if (!$result) 
	{
		echo 'Could not run sl_stackview_select_query: ' . mysql_error();
	} 	

	$json = array();
	$json = format_stack_json($result, '', '', $label);	
	
	if(count($json) == 0 || $offset == -1) {
		if ($callback) echo $callback . ' ({"start": "0", "num_found": "0", "limit": "0", "docs": ""})'; 
		else echo '({"start": "0", "num_found": "0", "limit": "0", "docs": ""})';
	}
	else {
		if ($callback) echo $callback . '({"start": ' . $last. ', "limit": "' . $count . '", "num_found": "' . $hits . '", "docs": ' . json_encode($json) . '})'; 
		else echo '({"start": ' . $last . ', "limit": "' . $count . '", "num_found": "' . $hits . '", "docs": ' . json_encode($json) . '})';
	}
	mysql_close();
}

function fetch_collections()
{
	connect_db();
	
	$user_id = $_REQUEST['user_id'];
	if (!isset($_GET['callback'])) $callback = "";
	else $callback = $_GET['callback'];
	
	$fields = array('collection_id','name');
	
	$query = "SELECT id, name
	FROM sl_collections
	WHERE user_id = '$user_id'
	ORDER BY name
	";

	$result = mysql_query($query);

	$json = array();
	while($row = mysql_fetch_row($result))
	{
		$data   = array($row[0], $row[1]);
		$temp_array  = array_combine($fields, $data);
		array_push($json, $temp_array);
	}

	if ($callback) echo $callback . '({"collections": ' . json_encode($json) . '})'; 
	else echo '({"collections": ' . json_encode($json) . '})';
	
	mysql_close();
}

function amazon_recommendations()
{
    global $AMAZON_KEY;
    global $AMAZON_SECRET_KEY;
    
	$isbn = $_GET['isbn'];
	$public_key = $AMAZON_KEY;
   	$private_key = $AMAZON_SECRET_KEY;
   	$pxml = aws_signed_request("com", array("Operation"=>"ItemLookup","ItemId"=>$isbn,"ResponseGroup"=>"Similarities"), $public_key, $private_key);
   	print_r($pxml);
   	if ($pxml === False)
   	{
   		echo 'false';
   	}
  	else
  	{ 
  		foreach($pxml->Items->Item->SimilarProducts->SimilarProduct as $product){
  			$img_url = $product->ASIN;
  			echo "$img_url<br />";
  		}
   	}
}

function fetch_reviews()
{
	connect_db();
	
	$uid = $_REQUEST['uid'];
	$callback = $_REQUEST['callback'];
	$json = array();
	
	$review_fields = array('review_id','rating','headline','date','user','review', 'recommended_count','tags');
	
	$avg_query  = "SELECT AVG(rating), COUNT(rating) FROM sl_reviews WHERE item_id = '$uid'";
    //echo $avg_query;
    $avg_result = mysql_query($avg_query);
	
	$avg_row = mysql_fetch_array($avg_result);
	$average = $avg_row[0];
	$count = $avg_row[1];
	if($count == 0) $average = 0;
	
	$query  = "SELECT * FROM sl_reviews WHERE item_id = '$uid' ORDER BY date DESC";
    //echo $query;
    $result = mysql_query($query);
	
	while($row = mysql_fetch_array($result))
	{		
		$review_id = $row[0];
		$tags = array();
		$tag_query  = "SELECT * FROM sl_tags WHERE review_id = '$review_id'";
    	//echo $tag_query;
    	$tag_result = mysql_query($tag_query);
    	while($tag_row = mysql_fetch_array($tag_result))
		{
			$temp_tags['tag_key'] = $tag_row[5];
			$temp_tags['tag'] = $tag_row[6];
			array_push($tags, $temp_tags);
		}
		
		$date = date("F j, Y",strtotime( $row[1] ));
		$review_data   = array($row[0], $row[4], $row[5], $date, 'Jane', $row[6], $row[8], $tags);
		$temp_array  = array_combine($review_fields, $review_data);
		array_push($json, $temp_array);
	}
	
	if ($callback) 
		echo $callback . '({"num_found": ' . $count . ', "average": ' . $average . ', "reviews": ' . json_encode($json) . '})'; 
	else 
		echo '({"reviews": ' . json_encode($json) . '})';
		
	mysql_close();
}

function fetch_tag_neighborhood()
{
	$tag = $_GET['query'];
	$offset = $_GET['start'];
	if (!isset($_GET['limit'])) $count = 11;
	else $count = $_GET['limit'];

	$neighborhood = array();
	
	$limit = "LIMIT $offset, $count";

	connect_db();
	
	$count_query = "SELECT COUNT(DISTINCT item_id) 
    				FROM sl_tags 
    				WHERE tag = '$tag' 
	";
	//echo $count_query;
	$hits = 0;
	$count_result = mysql_query($count_query);
	$row = mysql_fetch_row($count_result);
	$hits = $row[0];
	$last = $offset + $count;
	
	//$hollis_id = preg_replace("/^0+/", "", $hollis_id);

    $userList  = "SELECT COUNT(*), item_id 
    				FROM sl_tags 
    				WHERE tag='$tag' 
    				GROUP BY item_id 
    				ORDER BY COUNT(*) DESC
    				$limit";
    
    //echo $userList;
    $user_result = mysql_query($userList);
    $user_books = array();
    while($row = mysql_fetch_row($user_result)) {
    	$uid = $row[1];
    	array_push($user_books, $uid);
    }
    $number_books = count($user_books);
    if($number_books == 0)
    	$where = "WHERE 1 = 2";
    else
	    $where = "WHERE ";
    $count_books = 1;
    foreach($user_books as $item_id) {
    	$where .= "id = '$item_id' ";
    	if($count_books < $number_books)
    		$where .= " OR ";
    	$count_books++;
    }
    
    $home = 'not_used';
	
	if (!isset($_GET['callback'])) $callback = "";
	else $callback = $_GET['callback'];
	
	mysql_select_db("sl");
	
	$sl_stackview_select_query = "SELECT id, title, creator, measurement_page_numeric, measurement_height_numeric, shelfrank, language, pub_date, format, id_isbn, id_oclc, rsrc_value, loc_sort_order, publisher, desc_subject_lcsh, aggregation_checkout, source, wp_url, wp_categories, id_inst
	FROM sl.item
	$where
	";
	//print "sl_stackview_select_query: [$sl_stackview_select_query]<br />";
	$result = mysql_query($sl_stackview_select_query);
	if (!$result) 
	{
		echo 'Could not run sl_stackview_select_query: ' . mysql_error();
	} 	

	$json = array();
	$json = format_stack_json($result);	
	
	if(count($json) == 0 || $offset == -1) {
		if ($callback) echo $callback . ' ({"start": "0", "num_found": "0", "limit": "0", "docs": ""})'; 
		else echo '({"start": "0", "num_found": "0", "limit": "0", "docs": ""})';
	}
	else {
		if ($callback) echo $callback . '({"start": ' . $last . ', "limit": "' . $count . '", "num_found": "' . $hits . '", "docs": ' . json_encode($json) . '})'; 
		else echo '({"start": ' . $last . ', "limit": "' . $count . '", "num_found": "' . $hits . '", "docs": ' . json_encode($json) . '})';
	}
	mysql_close();
}

function fetch_tag_cloud()
{	
	connect_db();
	
	$uid        = trim($_REQUEST['uid']);	
	//$hollis = preg_replace("/^0+/", "", $hollis);
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

function fetch_librarything_id()
{
$isbn = $_GET['isbn'];

$url = "http://www.librarything.com/api/whatwork.php?isbn=" . $isbn;

$contents = fetch_page($url);
	
$wxml = new SimpleXmlElement($contents, LIBXML_NOCDATA); 
	
//print_r($pxml);
	
$work = $wxml->work;

global $LIBRARYTHING_KEY;

$url = "http://www.librarything.com/services/rest/1.1/?method=librarything.ck.getwork&id=$work&apikey=$LIBRARYTHING_KEY";

$contents = fetch_page($url);

echo $contents;	

}

function set_also_viewed()
{
	connect_db();	

	// CLIENT INFORMATION
	$book        = trim($_REQUEST['book']);
	$uid        = trim($_REQUEST['uid']);
	$id = trim($_REQUEST['id']);
	
	$friend = $book;
	
	//$hollis = preg_replace("/^0+/", "", $hollis);
	//$friend = preg_replace("/^0+/", "", $friend);

    $addClient  = "INSERT INTO book_also_views (book_one,book_two, session) VALUES ('$uid','$friend', '$id')";
    //echo $addClient;
    mysql_query($addClient) or die(mysql_error());
    
    $addClientReverse  = "INSERT INTO book_also_views (book_one,book_two, session) VALUES ('$friend','$uid', '$id')";
    //echo $addClient;
    mysql_query($addClientReverse) or die(mysql_error());
    mysql_close();
}

function set_book_friend()
{
	connect_db();	

	$book        = trim($_REQUEST['book']);
	$uid        = trim($_REQUEST['uid']);
	
	$friend = $book;

    $addClient  = "INSERT INTO book_friends (book_one,book_two) VALUES ('$uid','$friend')";
    //echo $addClient;
    mysql_query($addClient) or die(mysql_error());
    
    $addClientReverse  = "INSERT INTO book_friends (book_one,book_two) VALUES ('$friend','$uid')";
    //echo $addClient;
    mysql_query($addClientReverse) or die(mysql_error());
    mysql_close();
}

function set_book_tag()
{
	connect_db();	

	// TAG INFORMATION
	$uid        = trim($_REQUEST['uid']);
	//$hollis        = trim($_REQUEST['hollis']);
	$tag_array = $_REQUEST['tags']; 
	$tags = explode(',', $tag_array); 
	
	//$hollis = preg_replace("/^0+/", "", $hollis);

    foreach($tags as $tag){
    	$tag = strtolower(trim($tag));
    	//$addClient  = "INSERT INTO sl_tags (item_id,hollis,tag) VALUES ('$uid','$hollis','$tag')";
    	$addClient  = "INSERT INTO sl_tags (item_id,tag) VALUES ('$uid','$tag')";
    	//echo $addClient;
    	mysql_query($addClient) or die(mysql_error());
    }
    mysql_close();
}	

function set_review()
{
	connect_db();	

	$uid        = trim($_REQUEST['uid']);
	$date = date( 'Y-m-d H:i:s' );
	$rating = $_REQUEST['rating'];
	$review = addslashes($_REQUEST['review']); 
	$headline = addslashes($_REQUEST['headline']);
	$recommended = $_REQUEST['recommended'];
	if($_REQUEST['tag1'] && $_REQUEST['tag1'] != '') {
		$tag[0] = explode("=", $_REQUEST['tag1']);
	}
	if($_REQUEST['tag2'] && $_REQUEST['tag2'] != '') {
		$tag[1] = explode("=", $_REQUEST['tag2']);
	}

    $query  = "INSERT INTO sl_reviews (item_id,date,rating,headline,review,recommend_to_friend) VALUES ('$uid','$date','$rating','$headline','$review','$recommended')";
    //echo $query;
    mysql_query($query) or die(mysql_error());
    
    $review_id = mysql_insert_id();
    
    foreach($tag as $tag_pieces) {
    	$tag_key = $tag_pieces[0];
    	$tag = $tag_pieces[1];
    	$tag_query  = "INSERT INTO sl_tags (item_id,review_id, tag_type, tag_key, tag) VALUES ('$uid','$review_id','review_meta','$tag_key','$tag')";

    	mysql_query($tag_query) or die(mysql_error());
    }
    
    mysql_close();
}	

function set_review_recommendation()
{
	connect_db();
	
	$review_id = $_REQUEST['review_id'];
	
	$query  = "UPDATE sl_reviews SET recommended = recommended + 1 WHERE id=$review_id";

    mysql_query($query) or die(mysql_error());
    
    mysql_close();
}

function set_collection_addition()
{
	connect_db();
	
	$item_ids = $_REQUEST['item_id']; print_r($item_ids);
	$collection_id = $_REQUEST['collection_id'];
	$collection_name = $_REQUEST['collection_name'];
	
	if($collection_id == 'null') {
		$col_query  = "INSERT INTO sl_collections (name, user_id) VALUES ('$collection_name', '123456')";

    mysql_query($col_query) or die(mysql_error());
    
    $collection_id = mysql_insert_id();
	}
	
	foreach($item_ids as $item_id) {
	
	$query  = "INSERT IGNORE INTO sl_collections_items (collection_id, item_id) VALUES ('$collection_id', '$item_id')";

    mysql_query($query) or die(mysql_error());
    
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
   	//echo "<p><img src=\"".$img_url."\" /></p>";
   	}
}

function ReplaceAccents ($s) {
	$a = array (
		chr(195).chr(167)=>'c', //c with cedilla
		chr(231)=>'c',
		chr(195).chr(166)=>'ae', //a and e next to each other
		chr(230)=>'ae',
		chr(197).chr(147)=>'oe', //o and e next to each other
		chr(195).chr(161)=>'a', //a acute (small slash from bottom left)
		chr(225)=>'a',
		chr(195).chr(169)=>'e', //e acute
		chr(233)=>'e',
		chr(195).chr(173)=>'i', //i acute
		chr(237)=>'i',
		chr(195).chr(179)=>'o', //o acute
		chr(243)=>'o',
		chr(195).chr(186)=>'u', //u acute
		chr(250)=>'u',
		chr(195).chr(160)=>'a', //a grave (small slash from top left)
		chr(224)=>'a',
		chr(195).chr(168)=>'e', //e grave
		chr(232)=>'e',
		chr(195).chr(172)=>'i', //i grave
		chr(236)=>'i',
		chr(195).chr(178)=>'o', //o grave
		chr(242)=>'o',
		chr(195).chr(185)=>'u', //u grave
		chr(249)=>'u',
		chr(195).chr(164)=>'a', //a umlaut (two dots)
		chr(228)=>'a',
		chr(195).chr(171)=>'e', //e umlaut
		chr(235)=>'e',
		chr(195).chr(175)=>'i', //i umlaut
		chr(239)=>'i',
		chr(195).chr(182)=>'o', //o umlaut
		chr(246)=>'o',
		chr(195).chr(188)=>'u', //u umlaut
		chr(252)=>'u',
		chr(195).chr(191)=>'y', //y umlaut
		chr(255)=>'u',
		chr(195).chr(162)=>'a', //a circumflex (a little hat)
		chr(226)=>'a',
		chr(195).chr(170)=>'e', //e circumflex
		chr(234)=>'e',
		chr(195).chr(174)=>'i', //i circumflex
		chr(238)=>'i',
		chr(195).chr(180)=>'o', //o circumflex
		chr(244)=>'o',
		chr(195).chr(187)=>'u', //u circumflex
		chr(251)=>'u',
		chr(195).chr(165)=>'a', //a with a small ring on top
		chr(229)=>'a',
		chr(101).chr(0)=>'e', //e
		chr(105).chr(0)=>'i', //i
		chr(195).chr(184)=>'o', //o with a slash through it
		chr(248)=>'o',
		chr(117).chr(0)=>'u', //u
	);
	return strtr ($s, $a);
}

function link_title($title) {
	$title = preg_replace('/&sbquo;|&rsquo;|&fnof;|&bdquo;|&hellip;|&dagger;|&Dagger;|&circ;|&lsaquo;|&lsquo;|&ldquo;|&rdquo;|&ndash;|&mdash;|&tilde;|&rsaquo;/', '', $title);
	$title = ReplaceAccents($title);
	$title_words = explode(' ', strtolower($title));
	$link_title = trim(implode(' ', array_slice($title_words, 0, 6)));
    $link_title = str_replace(' :', '', $link_title);
	$link_title = str_replace('#', '', $link_title);
	$link_title = str_replace(',', '', $link_title);
	$link_title = str_replace('\'', '', $link_title);
	$link_title = str_replace('"', '', $link_title);
	$link_title = str_replace('.', '', $link_title);
	$link_title = preg_replace("/[^a-zA-Z0-9\s]/", "", $link_title);
	$link_title = htmlspecialchars(str_replace(' ', '-', $link_title)); 
	return $link_title;
}

function format_link() {
	$title = $_POST['title'];
	$title = link_title($title);
	echo $title;
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
    $params["Version"] = "2009-08-01";
    
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

function title_case($title) {
    $smallwordsarray = array('of','a','the','and','an','or','nor','but','is','if','then','else','when','at','from','by','on','off','for','in','out','over','to','into','with');

    $words = explode(' ', $title);
    foreach ($words as $key => $word)
    {
        if ($key == 0 or !in_array($word, $smallwordsarray))
        	$words[$key] = my_ucwords(strtolower($word));
    }

    $newtitle = implode(' ', $words);
    return $newtitle;
} 

function my_ucwords($string){

    $invalid_characters = array('"',
                                '\(',
                                '\[',
                                '\/',
                                '<.*?>',
                                '<\/.*?>');

    foreach($invalid_characters as $regex){
        $string = preg_replace('/('.$regex.')/','$1 ',$string);
    }

    $string=ucwords($string);

    foreach($invalid_characters as $regex){
        $string = preg_replace('/('.$regex.') /','$1',$string);
    }

    return $string;
} 
?>
