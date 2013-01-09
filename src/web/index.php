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
<script type="text/javascript" src="$www_root/js/jquery.fitvids.js"></script>
  $TYPEKIT_CODE
EOF;
?>
<script>
  $(document).ready(function(){
    $(".video").fitVids();
  });
</script>
</head>

<body>

    <div class="container group row">
		
		<div class="group span2 middle-position">
			
			 <?php require_once('includes/logo.php');?>
			<br/><br/><br/><br/><br/><p class="tagline"></p>
			 <span class="heading">How does it work?</span>
			<div class="about-button">
				<a href="about.php">About</a>
			</div>
				<br/>
				 <span class="heading">Who built it?</span>
				<div class="about-button">
				<a href="about.php">About</a>
			</div>
		</div><!--end logo include-->
		
		<div class="main span8">
      		<div id="landing-stack"></div>
    	</div><!--end main-->

			
		<div class="span4-negative middle-position-search">
			<form id="search2" method="get" action="<?php echo $www_root?>/search.php">
            	<input type="hidden" style="display:none" name="search_type" value="keyword"/>
            	<input type="text" autofocus="autofocus" name="q" placeholder="Search"/>
            	<input type="submit" name="submit_search" id="itemsearch" value="Go!"/>
			</form>
		</div><!--end-span4-negative-->

</div><!--end container-->

</body>
</html>
