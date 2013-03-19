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

</head>

<body>

    <div class="container group row">
		<div class="group span2 middle-position">
			
			 <?php require_once('includes/logo.php');?>

		
		</div><!--end logo include-->
		
		<div class="main span8">
      		<div id="landing-stack"></div>
    	</div><!--end main-->

			
		<div class="span4-negative middle-position-search">
			<form id="search2" method="get" action="<?php echo $www_root?>/search">
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
				<a href="<?php echo $www_root ?>/explainer.php" class="heading">Explainer Page</a>
			</div>
			<br/>
			<div class="about-button">
				<a href="<?php echo $www_root ?>/about" class="heading">The StackLife Experiment</a>
			</div>
		</div><!--end-span4-negative-->

</div><!--end container-->

</body>
</html>
