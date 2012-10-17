<?php
  require_once('../../etc/sl_ini.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>ShelfLife</title>

<?php
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
<div id="wrapper">

  <?php require_once('includes/logo.php');?>
  <?php require_once('includes/searchbox.php');?>

    <div class="container group">
		<div class="container-content">

		<div class="main">
      <div id="landing-stack"></div>
    </div><!--end main-->


		<div class="overlay">
			
			<div class="welcome">
			
				<h1 class="cyan">Welcome!</h1> 
			
				<form id="search2" method="get" action="<?php echo $www_root?>/search.php">
            		<input type="hidden" style="display:none" name="search_type" value="keyword"/>
            		<input type="text" name="q" placeholder="Search"/>
            		<input type="submit" name="submit_search" id="itemsearch" value="Go!"/>
				</form>
                
			</div>
		</div><!--end overlay-->




	</div><!--end container-content-->
</div><!--end container-->


</div><!--end wrapper-->

</body>
</html>
