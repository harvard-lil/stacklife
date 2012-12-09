<?php
  require_once('../../etc/sl_ini.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>StackLife</title>

<?php
include_once('includes/includes.php');
global $TYPEKIT_CODE;
echo <<<EOF
  <link rel="author" href="$www_root/humans.txt" />
  <link rel="icon" href="$www_root/images/favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="$www_root/css/shelflife.theme.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/template.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/stackview/jquery.stackview.css" type="text/css" />
  
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script type="text/javascript" src="$www_root/stackview/jquery.stackview.min.js"></script>
  <script type="text/javascript" src="$www_root/js/landing_page.js"></script>
  $TYPEKIT_CODE
EOF;
?>

</head>

<body>

    <div class="container group row">
		
		<div class="group span2">
			
			 <?php require_once('includes/logo.php');?>
			 <p class="tagline">An experimental browsing interface to the Harvard Library</p>
			<div class="about-button">
				<a href="index.php" class="about">Home</a>
			</div>
		</div><!--end logo include-->
		
		<div class="span8">
			<br/><br/><br/><br/>
      		<p class="tagline">An experimental browsing interface to the Harvard Library</p>
			
    	</div><!--end main-->

			
		<div class="span4-negative">
			<?php require_once('includes/searchbox.php');?>
			<div id="item-panel" class="itemData-container"></div>   
		</div> 
</div><!--end container-->

</body>
</html>
