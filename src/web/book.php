<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
  require_once ('../../sl_ini.php');
  include_once('book.inc.php');
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
include_once('includes.php');
echo <<<EOF

	<title></title>

	<link rel="stylesheet" href="$www_root/css/book.css" type="text/css" />
	<script type="text/javascript" src="$www_root/js/book.js"></script>
	<script type="text/javascript" src="$www_root/js/jquery.history.js"></script>
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
EOF;
?>

<script type="text/javascript">

var 
hollis = '<?php echo $hollis ?>',
worldcatnum = '',
source = '',
prettysource = '',
loc_sort_order = '';
sessionid = '<?php echo $session_id ?>',
anchor_subject = '',
uniform_id = '',
uniform_count = '',
uid = '<?php echo $uid ?>',
title = '';
scroller = '',
scrollercontent = '',
stackoptions = '';

var slurl = '<?php echo $www_root ?>/src/web/sl_funcs.php';
var www_root = '<?php echo $www_root ?>';

var start_record = 0,
num_requested = 3,
q = '';

var recentlyviewed = '';

var GBSArray = ['ISBN:<?php echo $isbn_trim ?>', 'OCLC:<?php echo $oclcnum ?>'];

google.load("books", "0");

var perspective = '<?php echo $perspective ?>';

var History = window.History;

$(document).ready(function() {

	<?php
	foreach(array_reverse($_SESSION['books']) as $id => $past_book){
		if($id != $uid) {
	?>
		recentlyviewed += ('&recently[]=<?php echo $id ?>');
	<?php }
	} ?>
	//$('#overlaynav ul').localScroll();

	/*In place of local scroll so that we can customize the offset a little easier*/

	$('a[href*=#]').live('click', function() {
       if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
          var $target = $(this.hash);
          $target = $target.length && $target || $('[name=' + this.hash.slice(1) +']');
 		  if ($target.length) {
            var targetOffset = $target.offset().top - 45;
            $('html,body').animate({scrollTop: targetOffset}, 1000);
           return false;
        }
       }
	});

	$(window).scroll(function (event) {
		// what the y position of the scroll is
		var y = $(this).scrollTop();
		// whether that's below the form
		// HEIGHT OF HEADER
		if (y >= $('.header').height()) {
		// if so, ad the fixed class
			$('#fixedstack').addClass('fixed');
			$('#overlaynav').addClass('fixed');
			stackheight = $(window).height();
			scroller.css('height', stackheight);
			$('.container').css('height', stackheight);
			$('#viewerCanvas').css('height', stackheight*.9).css('width', stackheight*.75);
			$('#fixedclear').css('clear', 'both');
		} else {
			// otherwise remove it
			$('#fixedstack').removeClass('fixed');
			$('#overlaynav').removeClass('fixed');
			stackheight = $(window).height() - $('.header').height();
			scroller.css('height', stackheight);
			$('.container').css('height', stackheight);
			$('#viewerCanvas').css('height', stackheight*.9).css('width', stackheight*.75);
			$('#fixedclear').css('clear', '');
		}

	});

}); //End document ready
</script>
</head>

<!-- /////////////////// BODY ////////////////////////// -->
<body>
<div id="wrapper">
    <div style="display:none;">
    	<div id="viewerCanvas" style="width: 610px; height: 725px"></div>
    </div>
      <?php require_once('header.php');?>

    <div class="container group">
      	<div class="container-content">
      		<div class="main">
				<div id="fixedstack">
					
				</div><!-- end fixedstack-->
        </div><!-- end main-->
			<div class="itemData-container scrollmarg">

				<div id="itemData">
            		<h1 class="home-stack"></h1>
            		<div id="creator_container" id="itemData">

          			</div>

          			<img class="cover-image ol-cover-image" src="http://covers.openlibrary.org/b/isbn/<?php echo $isbn_trim ?>-M.jpg" />
					
					<!--<div id="reviewsummary">
						<div id="ratingaverage" class="rateit" data-rateit-value="" data-rateit-ispreset="true" data-rateit-readonly="true">
						</div>
						<span id="createreview"></span>
					</div>
					
					<div class="ss-line-height inject-like" fid="uid:<?php echo $uid ?>"></div><br />
					<div class="ss-line-height inject-follow" fid="uid:<?php echo $uid ?>"></div>-->
					<?php echo '<a href="http://holliscatalog.harvard.edu/?itemid=|library/m/aleph|'.$hollis.'" target="_blank" class="button" id="hollis_button">HOLLIS</a>' ?>
					<div id="online">
                    	<a class="button-google-disabled" href="#viewerCanvas"><img src="<?php echo $www_root ?>/images/gbs_preview_disabled.png" /></a>
                    	<a id="gviewer" class="button-google" href="#viewerCanvas" style="display:none;"><img src="<?php echo $www_root ?>/images/gbs_preview.png" border="0" /></a>
                	</div>
            		<div class="wikipedia-icon"> 
						<?php
                    		echo '<div class="wikipedia_link"><a href="" target="_blank" ><img src="' . $www_root . '/images/wikipedia.png" /></a></div>';
						?>
					</div><!--end wikipedia-icon-->	
					<div class="buy" style="display:none;">	 
                 		<a id="amzn" href="" target="_blank"><img class="buy" src="<?php echo $www_root ?>/images/amazon.png" alt="Amazon"/></a><span class="author-divider">|</span>                 	
                 		<a id="abes" href="" target="_blank"><img class="buy" src="<?php echo $www_root ?>/images/abeBooks.png" alt="AbeBooks"/> </a><span class="author-divider">|</span>
                 		<a id="bandn" href="" target="_blank"><img class="buy" src="<?php echo $www_root ?>/images/barnesAndNoble.png" alt="Barnes&amp;Noble"/></a><span class="author-divider">|</span>
                 		<a id="hrvbs" href="" target="_blank"><img class="buy" src="<?php echo $www_root ?>/images/harvardBookStore.png" alt="Harvard Book Store"/></a>
            		</div>  <!--end buy-->	
            		<span class="button-availability available-button slide-more" style="display:none;"><span class="icon"></span>Availability<span class="arrow"></span></span>
					<div id="availability" class="slide-content" style="display:none;"></div>
                   <h3 class="imprint"></h3>  
                    
                	<h3 class="clickable advanced-data slide-more">Advanced Bibliographic Data<span class="arrow"></span></h3>
        	    	<div class="advanced-data-box slide-content" style="display:none;">
        	    		<ul>
        	    			<li class="advanced-isbn"><p>ISBN: </p></li>
        	    			<li class="advanced-oclc"><p>OCLC: </p></li>
        	    			<li class="advanced-language"><p>Language: </p></li>
        	    		</ul>
        	    	</div>
                   
                    <div class="play-media">

                    </div><!--end play-media-->   
                </div><!--end itemData-->              
                
                <div id="all-rank" class="slide-more">	            
        			<div id="shelfRankCalc" class="button-shelfRank">
        		  		<span class="unpack">ShelfRank</span>
        		  		<span class="shelfRank">
        		  		</span>
        		 		</div><!--end shelfRankCalc-->
        			</div><!--end all-rank-->
                	<div id="rank-math" class="slide-content" style="display:none;">
              		
            		</div><!--end rank-math-->
                </div><!--end itemDataContainer-->   
                
				<div id="contextData" class="group">
                	<div id="overlay-buttons">
                		<div class="subjects">
							<span class="heading">Library Shelves <!--<img src="../../images/info-icon.gif" help="hlibrary" />--></span>
             				<ul>
                				<li id="callview" class="button button-selected stack-button"><span class="reload"><span class="reload-text">Infinite bookshelf</span></span></li>
           				</ul>
           			</div><!--end subjects-->
            		<div class="neighborhoods">
            			<span class="heading">Community Shelves</span>
             			<ul>
             				<li id="alsoviewed" class="button stack-button"><span class="reload">People who viewed this also viewed these</span></li>
        					<li id="recentlyviewed" class="button stack-button"><span class="reload">You recently viewed these</span></li>
         					<li id="friend" class="button stack-button"><span class="reload">People who read this recommend these</span></li>
         				</ul>
         			</div><!--end neighborhoods-->
         			<div class="wikipedia">

           			</div><!--end wikipedia-->

						<span class="clickable heading readtoo">Recommendation?<span class="arrow"></span></span>
        	   	<div id="friend-box" style="display:none;">
    		 		<div id="readForm">
                	    <form id="titlesearch">
                    	    <input type="text" id="friendSearch" name="q"/>
                			<input type="submit" value="Search"/>
               			</form>
					</div><!--end readForm-->
      				<div id="results" class="group">
						<span class="next-page next-page2"></span>
      					<p class="num_found"></p>
						<span class="prev-page prev-page2"></span>
					</div><!-- end results -->
					<table cellspacing="0" id="searchresults"></table>
            	</div><!-- end friend-box-->

           			<div id="tagGraph"></div>
				</div><!--end overlay-buttons-->
                <form id="book-tags-form" method="post">
                	<input type="text" id="bookTags" name="bookTags" class="required" onfocus="if (this.value=='tag it') this.value = ''" type="text" value="tag it"/>
				   	<input type="submit" name="submit_tags"  id="submit_tags" value="Go!"/>
				</form>
				<div class="book-tag-success"><p><span style="display:none;"></span></p></div>

 		       
           	</div> <!-- end contextData -->
           	<div id="fixedclear"></div>
           	<div class="text-description group"> <!-- end contextData -->	
           		<div class="worldcat"></div>
				<div id="reviewfull"></div>
        	</div> <!-- end text-description -->
        </div> <!--end container-content-->
    </div><!--end container-->
    <div id="dug"></div>

  <script id="gbscript" type="text/javascript" src="http://books.google.com/books?jscmd=viewapi&bibkeys=OCLC:<?php echo $oclcnum ?>,ISBN:<?php echo $isbn_trim ?>&callback=ProcessGBSBookInfo"></script>
   </div> <!--end wrapper-->
</body>
</html>
