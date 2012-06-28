<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
  require_once ('../../etc/sl_ini.php');
  include_once('includes/item.inc.php');
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title></title>
<?php
include_once('includes/includes.php');
echo <<<EOF
  <link rel="stylesheet" href="$www_root/css/jquery-ui-1.7.1.custom.css" type="text/css" />
	<script type="text/javascript" src="$www_root/js/item.js"></script>
	<script type="text/javascript" src="$www_root/js/jquery.history.js"></script>
	<script type="text/javascript" src="http://www.google.com/jsapi"></script>
EOF;
?>

<script type="text/javascript">
var worldcatnum = '',
loc_call_num_sort_order = '';
anchor_subject = '',
uniform_id = '',
uniform_count = '',
uid = '<?php echo $uid ?>',
title = '';

var slurl = '<?php echo $www_root ?>/src/web/sl_funcs.php';
var www_root = '<?php echo $www_root ?>';

var recentlyviewed = '';
var alsoviewed = new Array();

var GBSArray = ['ISBN:<?php echo $isbn_trim ?>', 'OCLC:<?php echo $oclcnum ?>'];

google.load("books", "0");

(function(window,undefined){
  var History = window.History;
})(window);

$(document).ready(function() {
	<?php
	foreach(array_reverse($_SESSION['books']) as $id => $past_book){
		if($id != $uid) {
	?>
		recentlyviewed += ('&recently[]=<?php echo $id ?>');
		alsoviewed.push('<?php echo $id ?>');
	<?php }
	} ?>
}); //End document ready
</script>
</head>

<!-- /////////////////// BODY ////////////////////////// -->
<body>
  <div style="display:none;">
    <div id="viewerCanvas" style="width: 610px; height: 725px"></div>
  </div>
    
  <?php require_once('includes/header.php');?>

  <div class="container group">
    <div class="container-content">
      <div class="main">
				<div id="fixedstack"></div>
      </div><!-- end main-->
		  <div id="item-panel" class="itemData-container scrollmarg"></div>   
                
      <div id="contextData" class="group">
        <div id="overlay-buttons">
          <div id="shelves-panel"></div>
  
          <div id="tagGraph"></div>
        </div><!--end overlay-buttons-->
          
        <form id="book-tags-form" method="post">
          <input type="text" id="bookTags" name="bookTags" class="required" onfocus="if (this.value=='tag it') this.value = ''" type="text" value="tag it"/>
            <input type="submit" name="submit_tags"  id="submit_tags" value="Go!"/>
        </form>
        <div class="book-tag-success"><p><span style="display:none;"></span></p></div>
      </div> <!-- end contextData -->
        
      <div id="fixedclear"></div>
        
      <div class="text-description group"> 
        <div id="toc"></div>
      </div> <!-- end text-description -->
    </div> <!--end container-content-->
  </div><!--end container-->

  <script id="gbscript" type="text/javascript" src="http://books.google.com/books?jscmd=viewapi&bibkeys=OCLC:<?php echo $oclcnum ?>,ISBN:<?php echo $isbn_trim ?>&callback=ProcessGBSBookInfo"></script>
  </div> <!--end wrapper-->
   
  <script id="item-template" type="text/x-handlebars-template">
  <div id="itemData">
    <h1 class="home-stack">
      {{title}}{{#if sub_title}} : {{sub_title}}{{/if}}
    </h1>
    <div id="creator_container">
    {{{creators}}}
    </div>
    <img class="cover-image" src="http://covers.openlibrary.org/b/isbn/{{isbn}}-M.jpg" />
    <ul class="access">
      <li><a href="http://holliscatalog.harvard.edu/?itemid=|library/m/aleph|{{id_inst}}" target="_blank">HOLLIS</a></li>
      {{#if url}}
      <li><a href="{{url}}">ONLINE</a></li>
      {{/if}}
      <li class="button-google-disabled"><a class="button-google-disabled" href="#viewerCanvas"><img src="<?php echo $www_root ?>/images/gbs_preview_disabled.png" /></a></li>
      <li class="button-google"><a id="gviewer" class="button-google" href="#viewerCanvas" style="display:none;"><img src="<?php echo $www_root ?>/images/gbs_preview.png" border="0" /></a></li>
      {{#if wp_url}}
      <li><a href="{{wp_url}}" target="_blank" ><img src="<?php echo $www_root ?>/images/wikipedia.png" /></a></li>
      {{/if}}
    </ul>
		<div id="availability-panel"></div>
		
		<div class="buy" style="display:none;">	 
      <a id="amzn" href="http://www.amazon.com/dp/{{isbn}}" target="_blank"><img class="buy" src="<?php echo $www_root ?>/images/amazon.png" alt="Amazon"/></a><span class="author-divider">|</span>                 	
      <a id="abes" href="http://www.abebooks.com/products/isbn/{{isbn}}" target="_blank"><img class="buy" src="<?php echo $www_root ?>/images/abeBooks.png" alt="AbeBooks"/> </a><span class="author-divider">|</span>
      <a id="bandn" href="http://search.barnesandnoble.com/booksearch/ISBNInquiry.asp?EAN={{isbn}}" target="_blank"><img class="buy" src="<?php echo $www_root ?>/images/barnesAndNoble.png" alt="Barnes&amp;Noble"/></a><span class="author-divider">|</span>
      <a id="hrvbs" href="http://site.booksite.com/1624/showdetail/?isbn={{isbn}}" target="_blank"><img class="buy" src="<?php echo $www_root ?>/images/harvardBookStore.png" alt="Harvard Book Store"/></a>
    </div>  <!--end buy-->
    
    <h3 class="imprint">{{#if pub_location}}{{pub_location}}{{/if}}{{#if publisher}}, {{publisher}}{{/if}}{{#if pub_date}}, {{pub_date}}{{/if}}</h3>
    
    <h3 class="clickable advanced-data slide-more">Advanced Bibliographic Data<span class="arrow"></span></h3>
    
    <div class="advanced-data-box slide-content" style="display:none;">
      <ul>
        <li class="advanced-isbn"><p>ISBN: {{isbn}}</p></li>
        <li class="advanced-oclc"><p>OCLC: {{oclc}}</p></li>
        <li class="advanced-language"><p>Language: {{language}}</p></li>
      </ul>
    </div>
    </div>
    <div id="all-rank" class="slide-more">	            
    <div id="shelfRankCalc" class="button-shelfRank">
      <span class="unpack">ShelfRank</span>
      <span class="shelfRank">{{shelfrank}}</span>
    </div><!--end shelfRankCalc-->
  </div><!--end all-rank-->
  <div id="rank-math" class="slide-content" style="display:none;">
    <ul>
      <li><p>Faculty checkouts: {{score_checkouts_fac}}</p></li>
      <li><p>Undergrad checkouts: {{score_checkouts_undergrad}}</p></li>
      <li><p>Graduate checkouts: {{score_checkouts_grad}}</p></li>
      <li><p>Holding libraries: {{score_holding_libs}}</p></li>
    </ul>
  </div><!--end rank-math-->

  </script> 
  <script id="availability-template" type="text/x-handlebars-template">
    <span class="button-availability {{#if any_available}}available-button{{else}}not-available-button{{/if}} slide-more"><span class="icon"></span>Availability<span class="arrow"></span></span>
		<div id="availability" class="slide-content" style="display:none;">
		  <ul>
		  {{#items}}
		    <li class="{{#if available}}available{{else}}not-available{{/if}}">
		      <span class="callno">{{library}} [{{call_num}}]</span>
		      {{#if available}}<span class="small-button sms">SMS</span>{{else}}
		      {{#if request}}<a class="small-button" href="{{request}}">REQUEST</a>{{/if}}{{/if}}<br />
		      {{status}}
		    </li>
		  {{/items}}
		  </ul>
		</div>
	</script>
	<script id="shelves-template" type="text/x-handlebars-template">
    <span class="heading">Library Shelves</span>
    <ul>
      {{#if ut_count}}
			<li id="uniform" class="button stack-button"><span class="reload">All editions</span></li>
			{{/if}}
			{{#if loc_call_num_sort_order}}
			<li id="callview" class="button stack-button"><span class="reload">Infinite bookshelf</span></li>
			{{else}}
			<li id="callview" class="button-disabled">No call number stack</li>
			{{/if}}
			{{#lcsh}}
			<li class="subject-button"><span class="reload">{{this}}</span></li>
			{{/lcsh}}
    </ul>
    <span class="heading">Community Shelves</span>
    <ul>
      <li id="alsoviewed" class="button stack-button"><span class="reload">People who viewed this also viewed these</span></li>
      <li id="recentlyviewed" class="button stack-button"><span class="reload">You recently viewed these</span></li>
    </ul>
    {{#if wp_categories}}
    <span class="heading">Wikipedia Shelves</span>
      <ul>
        {{#wp_categories}}
        <li class="wp_category-button"><span class="reload">{{this}}</span></li>
        {{/wp_categories}}
      </ul>
    {{/if}}
	</script>
</body>
</html>
