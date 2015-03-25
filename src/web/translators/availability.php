<?php
/*  Connector to an availability API 
    Out put should be in the following formatOutput
    {
      any_available: true,
      items: 
      [
        {
          available: true,
          library: "Grossman",
          call_num: "HB74 .P8 L479 2005",
          status: "In-library use | Not checked out",
          request: ""
        },
        {
          available: true,
          library: "Kennedy Sch of Gov",
          call_num: "HB74.P8 L479 2005",
          status: "3-hour loan | Not checked out",
          request: ""
        },
        {
          available: true,
          library: "Lamont",
          call_num: "HB74.P8 L479 2005",
          status: "28-day loan | Not checked out",
          request: ""
        },
        {
          available: false,
          library: "Lamont",
          call_num: "HB74.P8 L479 2005",
          status: "28-day loan | Checked out: Due: 07/23/12",
          request: "http://hollisservices.lib.harvard.edu/hollisservices/itemrequest?bibid=HVD01012809860&itemid=HVD50009610180000040"
        }
      ]
    }
*/
require_once('../../../etc/sl_ini.php');

$id = $_GET['id'];

$avail_fields = array('available', 'library', 'call_num', 'status', 'request', 'depository');
$json = array();

global $AVAILABILITY_URL;

$url = "$AVAILABILITY_URL$id";
	
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
	  $depository = false;
	  $coll = (string) $collection->collectionname;
	  $call = (string) $collection->holding->callnumber;
	  if($coll == 'Harvard Depository')
	    $depository = true;
		foreach($collection->holding->items->itemrecord as $itemrecord) {
		  if($itemrecord->call != '')
		    $call = (string) $itemrecord->call;
		  $status = (string) $itemrecord->stat;
		  //$isavail = (string) $itemrecord->isavail;
		  if($status == 'Not checked out | Regular loan' || $status == 'Not checked out | 28-day loan'||  $status == 'Not checked out | 7-day loan' || $depository) {
		    $any_available = 'true';
		    $thisavail = true;
		  }
		  else
		    $thisavail = false;
		  $request = (string) $itemrecord->req->attributes()->href;
		  $avail_data   = array($thisavail, $library, $call, $status, $request, $depository);
      $temp_array  = array_combine($avail_fields, $avail_data);
      array_push($json, $temp_array);
		}
	}
}
header('Content-type: application/json');
echo '{"any_available": ' . $any_available. ', "items": ' . json_encode($json) . '}'; 
?>