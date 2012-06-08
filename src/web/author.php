<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php

ini_set("display_errors", 1); 
error_reporting(E_ALL ^ E_NOTICE);

require_once('../../sl_ini.php');

session_start(); 

global $hostName;
global $userName;
global $pw;
		
if(!($link=mysql_pconnect($hostName, $userName, $pw)))
{
	echo "before error<br />";
	echo "error connecting to host";
	exit;
}

$author = addslashes($_GET['author']);
// Filter out extra whitespace
$author = preg_replace("/\s{2,}/", " ", $author);
//print "here is author: [$author]<br />";

// Our passed in perspective
$perspective = 'shelfrank';
if (!empty($_GET['perspective'])) {
  $perspective = $_GET['perspective'];
}
$display_author=stripslashes($author);
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
echo <<<EOF
  <link rel="icon" href="$www_root/favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="$www_root/css/template.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/stackstyle.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/jquery.fancybox-1.3.4.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/start/jquery-ui-1.8.2.custom.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/jquery.qtip.min.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/shelflife.theme.css" type="text/css" /> 

  <!--[if IE]>
        <link rel="stylesheet" href="$www_root/css/ie.css" type="text/css" />
  <![endif]-->
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
  <script type="text/javascript" src="$www_root/js/selectmenu.js"></script> 
  <script type="text/javascript" src="$www_root/js/stackscroller.js"></script> 
  <script type="text/javascript" src="$www_root/js/ba-whatevcache.js"></script> 
  <script type="text/javascript" src="$www_root/js/jquery.infinitescroller.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.localscroll-1.2.7-min.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.scrollTo-1.4.2-min.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.mousewheel.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.fancybox-1.3.4.pack.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.qtip.min.js"></script>
  <script type="text/javascript" src="http://use.typekit.com/lfg0one.js"></script>
  <script type="text/javascript">try{Typekit.load();}catch(e){}</script>

  <script src="/s/js/x/underscore.js" type="text/javascript"></script>
  <script src="/s/js/x/jquery.tmpl.js" type="text/javascript"></script>
  <script src="/s/js/x/json2.js" type="text/javascript"></script>
  <script src="/s/js/x/backbone.js" type="text/javascript"></script>
  <script src="/s/js/ss.js" type="text/javascript"></script>
  <title> $display_author | ShelfLife</title>
EOF;
?>

<script type="text/javascript">	

var author = '<?php echo $author;?>';
var perspective = '<?php echo $perspective ?>';
	
var stackheight = $(window).height() - $('.header').height() - $('.footer').height() - 80,
heatmap = 'yes',
orientation = 'V',
axis = 'y',
pagemultiple = 0.13,
heightmultiple = 15.8,
scrollercontent = '<div class="scroller-content"><div class="scroller-loading scroller-loading-prev"></div><div class="scroller-page"></div><div class="scroller-loading scroller-loading-next"></div></div>',
showCount = 5;
        
var scroller = '';

var stackoptions = '';
        
$(document).ready(function() {
	// TODO: Move this fancy box stuff into a common include
	$("a#inline").fancybox({
		'overlayShow': false,
		'autoDimensions' : false,
		'width' : 650,
		'height' : 375
	});
	
    var recentlyviewed = new Array();
    <?php if(count($_SESSION['books']) > 0) { 
		foreach(array_reverse($_SESSION['books']) as $uid => $past_book){	
		echo "recentlyviewed += ('&recently[]=$uid');";	
		}	
    }?>
    
    if(recentlyviewed.length < 1) {
		$('#arecentlyviewed').text('No recently viewed items');
		$('#arecentlyviewed').removeClass('stack-button').addClass('button-disabled');
	}
			
	scroller = $('#scroller-wrapper');
	$('#scroller-wrapper').css('height', stackheight);
	
	$(window).resize(function() {
		stackheight = $(window).height() - $('.header').height() - $('.header').height() - $('.footer').height();
		scroller.css('height', stackheight);
		$('.container').css('height', stackheight);
	});
	
	$.ajax({
		type: "POST",
		url: '../sl_funcs.php',
		data: "function=session_info&type=get",
		success: function(response){
			$('.stackswap').removeClass('stackswap-icon-covers').removeClass('stackswap-icon-spines').addClass('stackswap-icon-' + response);
			stackoptions = {hollis: '',
							books_per_page: 50,
							orientation: 'V',
							axis: 'y',
							display: response,
							threshold: 2000,
							heatmap: 'yes',
							pagemultiple: 0.11,
							heightmultiple: 12};
		},
		async: false
	});

    $('#jumpbutton').localScroll();
             
    $('.home-stack').click(function(){
    	scroller.trigger( 'move-to', 0 );
    	return false;
  	});
  	
  	//$('ul.stack li').live('click', function(){
  	//	var url = $(this).attr('link');
  	//	window.location = url;
  	//});
  	
  	// If a user clicks on a book, send them directly to the book page
  	$('.scroller-page ul li').live('click', function(){
  		window.location = '../book/' + $(this).data('item_details').title_link_friendly + '/' + $(this).data('item_details').id;
  	});
  			
  	$('.upstream').live('click', function(){
    	scroller.trigger( 'move-by', -$('#scroller-wrapper').height() );
    	return false;
  	});

  	$('.downstream').live('click', function(){
    	scroller.trigger( 'move-by', $('#scroller-wrapper').height() );
    	return false;
  	});
  			
  	$('#scroller-wrapper').bind('mousewheel', function(event, delta){
    	scroller.trigger( 'move-by', -delta * 75);
    	return false;
    });
    
	// Change stack display
	$('.stackswap').live('click', function(){
		stackoptions.display = stackoptions.display === 'covers' ? 'spines' : 'covers';
		$('.stackswap').removeClass('stackswap-icon-covers').removeClass('stackswap-icon-spines').addClass('stackswap-icon-' + stackoptions.display);
		$.ajax({
			type: "POST",
			url: '../sl_funcs.php',
			data: "function=session_info&type=set&stackdisplay=" + stackoptions.display,
			async: false
		});
		scroller.unbind( '.infiniteScroller' );
		scroller.html(scrollercontent).stackScroller(stackoptions);
	});
	
	// Start show/hide message threads 
	$(".showhide").hide();
	$(".msgbutton").live('click', function(){
		$(this).toggleClass("active").next().slideToggle("fast");
	});
	
	$('.scroller-page div[class*="Container"]').live({
        mouseenter: function(){
			$(this).children('.collectioncontainer').children('.collectionadd').show();
        },
        mouseleave: function(){
			$(this).children('.collectioncontainer').children('.collectionadd').hide();
        }
    });
    
    $('.collectionadd').live('click', function() {
    	$(this).toggleClass('collectionadded').toggleClass('collectionadd');
    	$('.collectionsubmit').removeClass('collection-icon-disabled').addClass('collection-icon');
    });

    $('.collectionadded').live('click', function() {
    	$(this).toggleClass('collectionadded').toggleClass('collectionadd');
    	if(!$('.collectionadded').is(':checked'))
    		$('.collectionsubmit').addClass('collection-icon-disabled').removeClass('collection-icon');
    });
	
	$('.collectionsubmit').live('click', function() {
		var item_ids = '';
		$.each($('.collectionadded:checked'), function() {
        	item_ids += '&item_id[]=' + $(this).attr('value');
    	});
    	if(item_ids.length > 0){
		//var item_name = $(this).next().data('item_details').title;
		var html = '<div id="collectionaddwrap"><p>Add items to which collection?</p><br /><form><ul>';
		$.getJSON("../sl_funcs.php?callback=?&function=fetch_collections", $.param({ 'user_id' : '123456' }), function(data) {
			if(data && data.collections.length > 0) {
				$.each(data.collections, function(i, item){
				html += '<li><input type="radio" value="' + item.collection_id + '" name="existing_collection" id="existing_collection" /> <label for="existing_collection">' + item.name + '</label></li>';
				});
				
				html += '<li><input type="radio" value=null name="existing_collection" id="existing_collection" /> <input type="text" id="collection_name" placeholder="Create a collection"/></li>';
				html += '</ul>';
				html += '<br /><p>Add collection tags</p><input type="text" id="collection_tags" name="collection_tags" class="required" type="text" /></form></div>';
				var $dialog = $('<div class="remove"></div>')
				.html(html)
				.dialog({
					autoOpen: false,
					title: 'Add to a collection',
					modal: true,
					resizable: false,
					draggable: false,
					width: 450 ,
					buttons: { 'Add': function() { 
						var collection_id = $('#existing_collection:checked').attr('value');
						var collection_name = "";
						if(collection_id === 'null') 
							collection_name = $('#collection_name').attr('value');
						else
							collection_name = $('#existing_collection:checked').next().text();
						var data = item_ids;
						data += '&collection_id=' + collection_id + '&collection_name=' + collection_name;
						$.ajax({
							url: "../sl_funcs.php?function=set_collection_addition",
							type: "get",
							data: data,
							success: function(){
								$('#collectionaddwrap').html('Items added to <b>' + collection_name + '</b>');
								$('.ui-dialog-buttonpane').hide();
								$('.collectionadded').toggleClass('collectionadded').toggleClass('collectionadd').removeAttr('checked').hide();
								$('.collectionsubmit').addClass('collection-icon-disabled').removeClass('collection-icon');
							}
						});
					}},
					close: function(event, ui) {
						//$dialog.dialog('destroy');
						$('.remove').remove();
					}
				});
				$dialog.dialog('open');
			}
		});	
		}
	});
	
	// Info boxes
	$("img[src$='info-icon.gif']").each(function() {
		var helptype = $(this).attr('help');
		
		$(this).qtip({
   		style: {
      		classes: 'ui-tooltip-green'
   		},
   		content: {
      		text: 'Loading...', // Loading text...
      		ajax: {
         		url: '../js/help.json', // URL to the JSON script
         		type: 'GET', // POST or GET
         		dataType: 'json', // Tell it we're retrieving JSON
         		success: function(data, status) {
            		var content = data[helptype];
            		this.set('content.text', content);
         		}
      		}
      	},
      	position: {
				viewport: $(window),
				my: 'bottom right',
				at: 'left top',
				target: $(this) // my target
			},
		show: {
      		event: 'click'
   		},
   		hide: {
   			event: 'click unfocus'
   		}
	});
	});
       			
    $.getJSON('/librarycloud/v.3/api/item/?callback=?&key=BUILD-LC-KEY', $.param({ 'query' : author, 'search_type' : 'creator_exact', 'limit' : 1 }),
		function (data) {
			if(data.docs && data.docs.length > 0){
				stackoptions.url = '/librarycloud/v.3/api/item/?key=BUILD-LC-KEY&facet=language&facet=language&facet=rsrc_key&facet=format&facet=pub_date_range';
				stackoptions.search_type = 'creator_exact';
				stackoptions.query = author;
				scroller.stackScroller(stackoptions);

				$('.subject-hits').html(data.num_found + '<br />items');
			}
		});
       			       			
	$.getJSON('../sl_funcs.php', $.param({ 'author' : '<?php echo $author;?>', 'function' : 'fetch_author_neighborhood'}), 
       	function(authors) {
       		var jList = $("#author_neighborhood");
       		$.each(authors, function(i, item)
       		{
       			//alert("here is item: " + item);
       			jList.append(
       			$("<li class='subject-button'><a href='../author/" + item + "'><span class='reload'>" + item + "</span></a></li>")
       			);
  			});
  			var count = 0;
  			$('#author_neighborhood li').each(function() {
				if(count > showCount)
					$(this).addClass('more-authors');
				count++;
			});
			$('.more-authors').hide();
			if(count > showCount)
				$('#author_neighborhood').append('<span class="author-toggle clickable">more</span>');
	});

    $.getJSON('../sl_funcs.php', $.param({ 'author' : '<?php echo $author;?>', 'function' : 'fetch_author_subjects'}), 
       	function(subjects) {
       		var jList = $("#subject_neighborhood");
       		var subject_part;

       		$.each(subjects, function(i, item)
       		{
       			//alert("here is item: " + item);       					
       			jList.append(
       			$('<li class="subject-button" id="' + item + '"><span class="reload">' + item + '</span></li>')
       			);
  			});
  			var count = 0;
  			$('#subject_neighborhood li').each(function() {
				if(count > showCount) {
					$(this).addClass('more-subjects'); 
					//$('#subject_neighborhood').prev().addClass('subject-toggle');
				}	
				count++;
			});
			$('.more-subjects').hide();
			if(count > showCount)
				$('#subject_neighborhood').append('<span class="subject-toggle clickable">more</span>');
	});
				
	$('.subject-toggle').live('click', function() {
		$('.more-subjects').slideToggle();
       	if($(this).text() == 'more') 
       		$(this).text('less');
       	else if($(this).text() == 'less')
       		$(this).text('more');
    });
    
    $('.author-toggle').live('click', function() {
		$('.more-authors').slideToggle();
       	if($(this).text() == 'more') 
       		$(this).text('less');
       	else if($(this).text() == 'less')
       		$(this).text('more');
    });
       			
	$('.subject-button').live('click', function() {
		var subject = $(this).text();
		var that = $(this);
		$('.ribbonBody .ribbonLabel').text(subject);
		$(that).addClass('button-selected');
		$('.button-selected').removeClass('button-selected');
		$.getJSON('/librarycloud/v.3/api/item/?callback=?&key=BUILD-LC-KEY', $.param({ 'query' : subject, 'search_type' : 'desc_subject_lcsh_exact', 'limit' : 1 }),
			function (data) {
				if ( data.docs && data.docs.length > 0 ) {
					$('#holder').empty();
					stackoptions.url = '/librarycloud/v.3/api/item/?key=BUILD-LC-KEY&facet=language&facet=language&facet=rsrc_key&facet=format&facet=pub_date_range';
					stackoptions.search_type = 'desc_subject_lcsh_exact';
					stackoptions.query = subject;
					stackoptions.hollis = '';
					
					scroller.unbind( '.infiniteScroller' );
					scroller.html(scrollercontent).stackScroller(stackoptions);
					$('.subject-hits').html(data.num_found + '<br />items');
				}
				else{
					scroller.unbind( '.infiniteScroller' );
					scroller.empty();
					$('#holder').html('<span class="heading">Sorry, no more books on this subject.</span>');
				}
			});
	});
    			
    $('.stack-button').live('click', function() {
    	var compare = $.trim($(this).attr('id'));
    	var nlabel = $(this).text();
    	$('.ribbonBody .ribbonLabel').text(nlabel);
    	$('.button-selected').removeClass('button-selected');
    	$('#langfilter').hide();
    	$('.subject-hits').text('');
    	$(this).addClass('button-selected');
    	if(compare == 'arecentlyviewed') {
       		$.getJSON('../sl_funcs.php?callback=?' + recentlyviewed, $.param({ 'search_type' : 'fetch_recently_viewed', 'start' : '0', 'limit' : '1' }),
			function (data) {  
				if ( data.docs && data.docs.length > 0 ) {
					$('#holder').empty();
					stackoptions.url = '../sl_funcs.php?' + recentlyviewed;
					stackoptions.search_type = 'fetch_recently_viewed';
					stackoptions.query = '';
					stackoptions.hollis = '';
					scroller.unbind( '.infiniteScroller' );
					scroller.html(scrollercontent).stackScroller(stackoptions);
					$('.subject-hits').html(data.num_found + '<br />items');
				}
				else {
					scroller.unbind( '.infiniteScroller' );
					scroller.empty();
					$('#holder').html('<br /><span class="heading">No other books have been connected with this one yet.</span>');
				}
			}); 					
       	}
       	else if(compare == 'authortitles')
       	{	
       		$.getJSON('/librarycloud/v.3/api/item/?callback=?&key=BUILD-LC-KEY', $.param({ 'query' : author, 'search_type' : 'creator_exact', 'limit' : 1 }),
				function (data) {
					if(data.docs && data.docs.length > 0){
						stackoptions.url = '/librarycloud/v.3/api/item/?key=BUILD-LC-KEY&facet=language&facet=language&facet=rsrc_key&facet=format&facet=pub_date_range';
						stackoptions.search_type = 'creator_exact';
						stackoptions.query = author;
						scroller.stackScroller(stackoptions);

						$('.subject-hits').html(data.num_found + '<br />items');
					}
			});
       	}
 	});
 	
 	// Advanced search box controls, start
  	
  	// If a user adds another field/query search pair
	$('.addfield').live('click', function() {
		$('.searchBox:last').parent().after('<p><select class="filter_type"><option value="title_exact">Title begins with</option><option value="title_keyword">Title contains keyword(s)</option><option value="creator_exact">Author (last, first)</option><option value="creator_keyword">Author contains keyword(s)</option><option value="desc_subject_lcsh_exact">Subject begins with</option><option value="desc_subject_keyword">Subject contains keyword(s)</option><option value="keyword" selected="selected">Keyword(s) anywhere</option></select><input type="hidden" value="" name="filter" /> <input type="text" class="searchBox filter_query" /></p>');
		
		if($('#advanced .searchBox').size() > 4)
			$(this).removeClass('addfield');
		if($('#advanced .searchBox').size() > 1)
			$('#addremove span:last').addClass('removefield');
	});
	
	// If a user removes a field/query search pair
	$('.removefield').live('click', function() {
		$('.searchBox:last').parent().remove();
		
		if($('#advanced .searchBox').size() < 2)
			$(this).removeClass('removefield');
		if($('#advanced .searchBox').size() < 6)
			$('#addremove span:first').addClass('addfield');
	});
	
	// Control the clamshelling of our facet lists here
	$('#advanced .facet_set p').live('click', function() {
		$('.facet_set p').not(this).next('ul').slideUp();
		$(this).next().slideToggle();
	});
	
	// The field part of our field/query search pair
	$('#advanced .filter_type').live('change', function() {
		var that = $(this).next();
		$(this).next().val($(this).val() + ':' + that.next().val() );
	});
	
	// The query part of our field/query search pair
	$('#advanced .filter_query').live('change', function() {
		var that = $(this).prev();
		$(this).prev().val(that.prev().val() + ':' + $(this).val() );
	});
				
				
			$('.more-less').live('click', function() {
					var hiddenClass = $(this).prev().attr('class');
					$('.' + hiddenClass).slideToggle();
       				if($(this).text() == 'more') 
       					$(this).text('less');
       				else
       					$(this).text('more');
       			});


			// translate between the score field and the scaled field
			// TODO: this is done for legacy purposes and should be refactored
			function get_scaled_field(translate_this) {
				if (translate_this === 'undergrad_total_score') {
					return 'undergrad_scaled';	
				}
				if (translate_this === 'library_total_score') {
					return 'library_scaled';
				}
				
				return 'shelfrank';
			}
			
			// If a user changes shelf rank view
			$('#weight_select').live('change', function() {
				perspective = $('#weight_select option:selected').attr('value');
				document.location.href= window.location.pathname + '?perspective=' + get_scaled_field(perspective);
			});

			// Translate between the scaled and the field score field 
			// TODO: this is done for legacy purposes and should be refactored
			function get_score_field(translate_this) {
				if (translate_this === 'undergrad_scaled') {
					return 'undergrad_total_score';
				}
				if (translate_this === 'library_scaled') {
					return 'library_total_score';
				}
				
				return 'shelfrank';
			}
			
			function get_uri_params() {
			    var vars = [], hash;
			    var hashes = window.location.href.slice(jQuery.inArray('?', window.location.href) + 1).split('&');

			    if (hashes.length > 1) {	    
				    // create array for each key
				    for(var i = 0; i < hashes.length; i++) {
				    	hash = hashes[i].split('=');
				    	vars[hash[0]] = [];
				    }
				    
				    // populate newly created entries with values 
				    for(var i = 0; i < hashes.length; i++) {
				        hash = hashes[i].split('=');
				        vars[hash[0]].push(decodeURIComponent(hash[1].replace(/\+/g, '%20')));
				    }
			    }
			    return vars;
			}
			
//		    var uri_params = get_uri_params();
//		    var passed_in_perspective = 'shelfrank';
//		    
//		    if (uri_params['perspective'] && uri_params['perspective'][0]) {
//		    	passed_in_perspective = uri_params['perspective'][0];
//			}
			
			// Select our perspective
			$("#weight_select").val(get_score_field(perspective));
    	 
     }); //End document ready
</script>	

</head>
    <body>
    <div id="wrapper">
      <?php require_once('header.php');?>
      <div class="container group"> 
      		<div class="container-content">
				<div class="main">
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
							<div class="ribbonBody group">
								<span class="ribbonLabel">Titles by this author</span>
								<ul class="facet-icons">
            						<li class="stackswap button stackswap-icon"></li>
            						<li id="format:online_audio" class="refine-stack-disabled soundrecording-icon"></li>
            						<li id="format:online_video" class="refine-stack-disabled videofilm-icon"></li>
            						<li id="format:online_full_text" class="refine-stack-disabled ebook-icon"></li>
            						<li id="format:webpage" class="refine-stack-disabled webpage-icon"></li>
            						<li id="mylibrary" class="mylibrary-icon refine-stack-disabled"></li>
            						<li class="collection-icon-disabled collectionsubmit"></li>
            					</ul>
            				</div><!-- end ribbonBody -->
							<div class="ribbonBehind"></div>
						</div><!-- end ribbon class -->
					
           			<div id="holder"></div><!-- to display error messages -->
            		<div id="scroller-wrapper" class="scroller scroller-vertical">
						<div class="scroller-content">	
  							<div class="scroller-loading scroller-loading-prev"></div>
  							<div class="scroller-page"></div>
  							<div class="scroller-loading scroller-loading-next"></div>
						</div><!-- end scroller-content -->
					</div> <!-- end scroller-wrapper -->
        		</div><!--end main-->  
             
	        <div class="itemData-container">
	        <div id="itemData">    
	       		<h1><?php echo $display_author ?></h1> 
                <div class="inject-like" fid="uid:<?php echo $uid ?>"></div><br />
				<div class="inject-follow" fid="uid:<?php echo $uid ?>"></div>
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
              		<span class="heading">Related Author Shelves <img src="<?php echo $www_root ?>/images/info-icon.gif" help="hrelated-authorpage" /></span>
            		<ul id="author_neighborhood"></ul>
            	</div> <!-- end neighborhoods -->  
            </div> <!--end contextData-->  
        	</div> <!--end container-content-->
    	</div> <!--end container-->
   </body>
</html>
