<?php

$to = $_GET["number"] . $_GET["carrier"];
$title = $_GET['title'];
$msg = $_GET["library"];
//$sub = "Call #";
$title_max = 140 - strlen($msg);
$msg .= " ";
$msg .= substr($title, 0, $title_max);
require 'class.phpmailer.php';

$mail = new PHPMailer(true);
$mail->SetFrom("shelflife@law.harvard.edu", 'ShelfLife');
$mail->AddAddress($to);
$mail->Body = $msg;

$mail->Send();
?>
