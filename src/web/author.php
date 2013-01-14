<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
require_once('../../etc/sl_ini.php');

session_start(); 

$author = addslashes($_GET['author']);
$author = preg_replace("/\s{2,}/", " ", $author);
$display_author=stripslashes($author);
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
include_once('includes/includes.php');
echo "<title>$display_author | StackLife</title>";
echo "<script type='text/javascript' src='$www_root/js/author.js'></script>";
?>
<script type="text/javascript">	

var author = '<?php echo $author;?>';
var www_root = '<?php echo $www_root ?>';
var showCount = 5;
var recentlyviewed = new Array();
        
$(document).ready(function() {	
  <?php if(count($_SESSION['books']) > 0) { 
	  foreach(array_reverse($_SESSION['books']) as $uid => $past_book){	
	    echo "recentlyviewed += ('&recently[]=$uid');";	
	  }	
  }?>
    
  if(recentlyviewed.length < 1) {
		$('#arecentlyviewed').text('No recently viewed items');
		$('#arecentlyviewed').removeClass('stack-button').addClass('button-disabled');
	}
}); //End document ready
</script>	
</head>

<!-- /////////////////// BODY ////////////////////////// -->
    <body>
    	<div class="container group row">
      
      		<div style="display:none;">
				<div id="viewerCanvas" style="width: 610px; height: 725px"></div>
			</div> <!--end hidden viewerCanvas-->

      		<div id="contextData" class="group span2">
		
				<?php require_once('includes/logo.php');?>
                	<div class="subjects left">
               			<span class="heading">Subject Stacks</span>
             			<ul id="subject_neighborhood"></ul>
             			<br/>
             			 <span class="heading">Community Stacks</span>
    						<ul>
      							<li id="recentlyviewed" class="button stack-button"><span class="reload">Recently Viewed</span></li>
    						</ul>
              		</div> <!-- end subjects -->
              		<!--<div class="neighborhoods right">
              			<span class="heading">Related Authors</span>
            			<ul id="author_neighborhood"></ul>
            		</div>  end neighborhoods -->  
            	</div> <!--end contextData-->  
      
				<div class="main span8">
					<div id="fixedstack"></div>
      			</div><!-- end main-->
                       
            	<div class="span4-negative offset6">
					<?php require_once('includes/searchbox.php');?>
					<div id="itemData">    
						<div class="authorData-container">
							<div class="authorData-inner">
	       						<h1><?php echo $display_author ?></h1> 
				    			<br />
				    			<span class="heading">Author Stacks</span>
             					<ul>
             						<li id="authortitles" class="stack-button selected-button"><span class="reload">Titles by this author</span></li><br />
             					</ul>
             				</div><!--end authorData-inner-->
             			</div><!--end authorData-container-->
             		</div><!--end itemData-->
				</div> <!--end span4-negative offset6-->
		
        	</div> <!--end container-content-->
    	</div> <!--end container-->
   </body>
</html>
