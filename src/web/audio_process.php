<?php 
	require_once ('../../sl_ini.php');

	function get_audio($e3u){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch,CURLOPT_URL,$e3u);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result=curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	if(isset($_POST['audio'])){
		$filesPath = $_POST['audio'];
		echo get_audio($filesPath);
	}
?>