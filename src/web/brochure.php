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
echo <<<EOF
  <script type="text/javascript" src="$www_root/js/landing_page.js"></script>
EOF;
?>

<style>
#apron {
	position:relative;
	margin-top:-300px;
	height:200px;
	width:450px;
	padding:0 10px 50px;
	
	background:#FCF0AD;
	background:rgba(252, 240, 173, .9);
	text-shadow: 0px 1px #fff;
	z-index:9999;
	-moz-box-shadow: 0 0 8px #888;
-webkit-box-shadow: 0 0 8px #888;
box-shadow: 0px 0px 8px #888;
}
.span4-negative {
	position:relative;
	
}
</style>
</head>

<body>

    <div class="container group row">
	<div class="group span2">
			
			 <?php require_once('includes/logo.php');?>
	<div id="overlay-buttons">
          		<div id="shelves-panel"></div>
          		<div id="tagGraph"></div>
    		</div><!--end overlay-buttons-->
		
		</div><!--end logo include-->
		
		<div class="main span8">
      		<div id="landing-stack"></div>
      		<div id="apron">
      			<p class="text">StackLife heat-maps books to reflect how often they’ve been checked out, put on reserve, called back early from a loan, etc.</p>
      			
      			<p class="text">Also, it’s not just books. StackLife lets you browse among all of Hollis’ catalog, including DVDs and CDs.</p>
      			
      			<p class="text">Work get their dimensions and page count and height found in the catalog record.</p>
      		</div>
    	</div><!--end main-->

			
		<div class="span4-negative">
			<form id="search" method="get" action="<?php echo $www_root?>/search">
            	<input type="hidden" style="display:none" name="search_type" value="keyword"/>
            	<input type="text" autofocus="autofocus" name="q" placeholder="Search"/>
            	<input type="submit" name="submit_search" id="itemsearch" value="Go!"/>
			</form>
			<a id="inline" href="#advanced" style="display:none">Advanced Search</a>
			<a href="<?php echo $www_root?>/search?advanced=true" class="button advanced-search2">Advanced Search</a>  
			<br/>
			<p class="text">Welcome to a StackLife, a new way to browse the Harvard Library collection.</p> 
			<p class="text">This is a prototype. We’re eager to hear from you about what works, what doesn’t, and what you’d like to see. Email us at <span class="cyan">lil@law.harvard.edu</span>!</p>
			<br/>
			<div class="about-button">
				<a href="<?php echo $www_root ?>/about" class="heading">How To &amp; More</a>
			</div>
				
		</div><!--end-span4-negative-->

</div><!--end container-->

</body>
</html>
