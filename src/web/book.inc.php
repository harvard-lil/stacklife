<?php
// This display errors business should be set in the php.ini config
ini_set("display_errors", 1);
error_reporting(E_ALL ^ E_NOTICE);

// Start our session
session_start();
$session_id = session_id();

if(isset($_SESSION['views'])) {
    $_SESSION['views'] = $_SESSION['views']+ 1;
} else {
    $_SESSION['views'] = 1;
}

if(isset($_SESSION['also_viewed']))
	$also_viewed = $_SESSION['also_viewed'];
else
	$also_viewed = array();

$uid = $_GET['id'];

// Our passed in perspective
$perspective = 'shelfrank';
if (!empty($_GET['perspective'])) {
  $perspective = $_GET['perspective'];
}

// Put our decoded stuff back into the session cookie
$_SESSION['books'][$uid]['link'] = $_SERVER['REQUEST_URI'];
?>
