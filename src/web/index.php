<?php
  require_once('../../sl_ini.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>ShelfLife / Digital Public Library of America</title>

<?php
include_once ('includes.php');
echo <<<EOF
  <script type="text/javascript" src="$www_root/js/stackscroller.js"></script>
  <script type="text/javascript" src="$www_root/js/landing_page.js"></script>
EOF;
?>

</head>

<body>
<div id="wrapper">

<?php require_once('header.php');?>

    <div class="container group">
		<div class="container-content">

		<div class="main">

           	<div id="holder"></div><!-- to display error messages -->
           		<div id="navigation">
            			<div> <!-- start of our control center (arrows and breadcrumbs)-->
             	 			<span id="arrows">
            					<div class="upstream" alt="previous button"/></div>
            					<div class="subject-hits"></div>
            					<div class="downstream" alt="next button"/></div>
            				</span>
            			</div> <!-- end of our control center (arrows) -->
        			</div> <!-- end navigation -->
           		<div class="ribbon">
						<div class="ribbonBody">What's trending in America's Libraries?</div>
						<div class="ribbonBehind"></div>
					</div><!-- end ribbon class -->
            <div id="scroller-wrapper" class="scroller scroller-vertical">
				<div class="scroller-content">
  					<div class="scroller-loading scroller-loading-prev"></div>
  					<div class="scroller-page"></div>
  					<div class="scroller-loading scroller-loading-next"></div>
				</div><!-- end scroller-content -->
			</div> <!-- end scroller-wrapper -->
    </div><!--end main-->


		<div class="overlay">
			
			<div class="welcome">
			
				<h1 class="cyan">Welcome!</h1> 
			
				<form id="search2" method="get" action="<?php echo $www_root?>/search_results.php">
            		<input type="hidden" style="display:none" name="search_type" value="keyword"/>
            		<input type="text" name="q" placeholder="Search"/>
            		<input type="submit" name="submit_search" id="itemsearch" value="Go!"/>
				</form>
                
			</div>
		</div><!--end overlay-->




	</div><!--end container-content-->
</div><!--end container-->


</div><!--end wrapper-->

<script type="text/javascript">
  // Feedbackify script insertion
  document.write('<script src="' + document.location.protocol + '//fby.s3.amazonaws.com/fby.js"><' + '/script>');

 $(function(){
	$('select#weight_select').selectmenu({
		style:'dropdown',
		format: addressFormatting
	});
});
//a custom format option callback, grrr
var addressFormatting = function(text){
	var newText = text;
	//array of find replaces
	var findreps = [
		{find:/^([^\-]+) \- /g, rep: '<span class="ui-selectmenu-item-header">$1</span>'},
		{find:/([^\|><]+) \| /g, rep: '<span class="ui-selectmenu-item-content">$1</span>'},
		{find:/([^\|><\(\)]+) (\()/g, rep: '<span class="ui-selectmenu-item-content">$1</span>$2'},
		{find:/([^\|><\(\)]+)$/g, rep: '<span class="ui-selectmenu-item-content">$1</span>'},
		{find:/(\([^\|><]+\))$/g, rep: '<span class="ui-selectmenu-item-footer">$1</span>'}
	];

	for(var i in findreps){
		newText = newText.replace(findreps[i].find, findreps[i].rep);
	}
	return newText;
}
</script>
</body>
</html>
