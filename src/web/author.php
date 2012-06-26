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
include_once('includes.php');
echo "<title>$display_author | ShelfLife</title>";
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
    <body>
    <div id="wrapper">
      <?php require_once('header.php');?>
      <div class="container group"> 
      	<div class="container-content">
				  <div class="main">
            <div id="fixedstack"></div>
          </div><!--end main-->  
             
	        <div class="itemData-container">
	        <div id="itemData">    
	       		<h1><?php echo $display_author ?></h1> 
				    <br />
             	<ul>
             		<li id="authortitles" class="stack-button button-selected"><span class="reload">Titles by this author</span></li><br />
             		<li id="arecentlyviewed" class="stack-button"><span class="reload">You recently viewed these</span></li> 
             	</ul>
             </div>
             </div>
             <div id="contextData">
                <div class="subjects left">
               		<span class="heading">Subject Shelves</span>
             		<ul id="subject_neighborhood"></ul>
              	</div> <!-- end subjects -->
              	<div class="neighborhoods right">
              		<span class="heading">Related Author Shelves</span>
            		<ul id="author_neighborhood"></ul>
            	</div> <!-- end neighborhoods -->  
            </div> <!--end contextData-->  
        	</div> <!--end container-content-->
    	</div> <!--end container-->
   </body>
</html>
