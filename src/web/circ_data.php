<?php

require_once ('../../sl_ini.php');

$db = $_GET['db'];
$result_type = $_GET['result_type'];
$offset = $_GET['offset'];
if($result_type == 'hits')
	$data_rows_param = '';
else
	$data_rows_param = 'LIMIT '.$offset.', 100';
	
    global $hostName;
    global $userName;
    global $pw;
			
	if(!($link=mysql_pconnect($hostName, $userName, $pw)))
	{
		echo "before error<br />";
		echo "error connecting to host";
		exit;
	}
	
	// Following directive is essential for proper utf-8 resolution at the client
	$set_utf8_query = "SET NAMES 'utf8'";
	//print "here is set_utf8_query: [$set_utf8_query]<br />";
	$result_utf8 = mysql_query($set_utf8_query, $link);
	
	if (!$result_utf8) 
	{
		echo 'Could not run set_utf8_query: ' . mysql_error();
		//exit;
	}
	
	$tblAlephUserData_select_query = "
	(SELECT alephCount, alephTitleDisplay, alephAuthor, alephBibID
	FROM hreg.$db
	ORDER BY alephCount DESC
	$data_rows_param) ORDER BY alephCount DESC
	";
	//print "here is tblAlephUserData_select_query: [$tblAlephUserData_select_query]<br />";
	
	$result = mysql_query($tblAlephUserData_select_query, $link);
	
	if (!$result) 
	{
		echo 'Could not run tblAlephUserData_select_query: ' . mysql_error();
		//exit;
	}
	
	$hits = mysql_num_rows($result);
	
	if ($hits)
	{			
		$count = 1;
		$FIELDS     = array('title','count','hollis', 'number', 'total');
		$JSON = array();
		while ($row = mysql_fetch_row($result))
		{
			if($count == 1)
				$total = trim($row[0]);
			$aleph_count = trim($row[0]);
			//$aleph_title = addslashes(trim($row[1]));
			$aleph_title = trim($row[1]);
			$aleph_title = mb_substr($aleph_title, 0, 80, "utf-8");
			$aleph_number = $row[3];
			$hollis_length = strlen($aleph_number);
			if($hollis_length != 9) {
				$loop = 9 - $hollis_length;
				for($j=0; $j<$loop; $j++){
					$aleph_number = '0'.$aleph_number;
				}
			}
			
			$_datas   = array($aleph_title, $aleph_count, $aleph_number, $count, $total);
  					
  			$_tmparr  = array_combine($FIELDS, $_datas);
			array_push($JSON, $_tmparr);
			$count++;
		}
	}	

echo json_encode($JSON);
?>