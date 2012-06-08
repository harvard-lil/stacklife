// The JavaScript for the landing page
var perspective = 'shelfrank';

$(document).ready(function() {
	if($.browser.msie) $('.logo').append('<span class="alert">This app has not been tested in Internet Explorer</span>');
	
	var scroller = $('#scroller-wrapper');
	
	var scrollercontent = '<div class="scroller-content"><div class="scroller-loading scroller-loading-prev"></div><div class="scroller-page"></div><div class="scroller-loading scroller-loading-next"></div></div>';

	// Set the size of our stack based on the size of the window
	var stackheight = $(window).height() - $('.header').height();
	$('#scroller-wrapper').css('height', stackheight);
	$(window).resize(function() {
		stackheight = $(window).height() - $('.header').height();
		scroller.css('height', stackheight);
		$('.container').css('height', stackheight);
	});

	// Build the options for the StackScroller
	var stackoptions = {books_per_page: 50,
					orientation: 'V',
					axis: 'y',
					threshold: 2000,
					heatmap: 'yes',
					pagemultiple: 0.11,
					heightmultiple: 12};

	$.getJSON('sl_funcs.php?callback=?', $.param({'search_type' : 'fetch_trending_stack', 'start' : '0', 'limit' : '1' }),
			function (data) {
				if ( data.docs && data.docs.length > 0 ) {
					stackoptions.url = 'sl_funcs.php?';
					stackoptions.search_type = 'fetch_trending_stack';
					stackoptions.query = '';
					drawStack("What's trending in America's Libraries?");
				}
				else {
					emptyStack('No other books have been connected with this one yet.');
				}
			});

	////
	// DOM controls, start
	////
	
	// Control our scroller using the jQuery mousehweel plugin
  	$('#scroller-wrapper').bind('mousewheel', function(event, delta){
    	scroller.trigger( 'move-by', -delta * 75);
    	return false;
    });
    
  	// Wire our up/down arrows to infinitescroller 
    $('.upstream').live('click', function(){
    	scroller.trigger( 'move-by', -$('#scroller-wrapper').height() );
    	return false;
  	});

  	$('.downstream').live('click', function(){ 
    	scroller.trigger( 'move-by', $('#scroller-wrapper').height() );
    	return false;
  	});
  	
  	// If a user clicks on a book, send them directly to the book page
  	$('.scroller-page ul li').live('click', function(){
  		window.location = 'book/' + $(this).data('item_details').title_link_friendly + '/' + $(this).data('item_details').id;
  	});
  	
  	// Start show/hide message threads 
	$(".showhide").hide();
	$(".msgbutton").live('click', function(){
		$(this).toggleClass("active").next().slideToggle("fast");
	});
  	
  	$('.loadLibraryStack').live('click', function(e){
		e.preventDefault();
		var source = $(this).attr('source');
		var label = $(this).attr('alt');
		$.getJSON('/librarycloud/v.3/api/item/?callback=?&key=BUILD-LC-KEY', $.param({ 'query' : source, 'search_type' : 'source', 'limit' : 1 }),
			function (data) {
				if ( data.docs && data.docs.length > 0 ) {
					$('#holder').empty();
					stackoptions.url = '/librarycloud/v.3/api/item/?key=BUILD-LC-KEY';
					stackoptions.search_type = 'source';
					stackoptions.query = source;
					
					drawStack(label);
				}
				else{
					emptyStack('<span class="heading">Sorry, no items were found.</span>');
				}
			});
	});
	
	$('.loadTrendingStack').live('click', function(e){
		e.preventDefault();
		$.getJSON('sl_funcs.php?callback=?', $.param({'search_type' : 'fetch_trending_stack', 'start' : '0', 'limit' : '1' }),
			function (data) {
				if ( data.docs && data.docs.length > 0 ) {
					stackoptions.url = 'sl_funcs.php?';
					stackoptions.search_type = 'fetch_trending_stack';
					stackoptions.query = '';
					drawStack("What's trending in America's Libraries?");
				}
				else {
					emptyStack('No other books have been connected with this one yet.');
				}
			});
	});
			
	function drawStack(ribbon) {
		scroller.unbind( '.infiniteScroller' );
		scroller.html(scrollercontent).stackScroller(stackoptions);
		$('.ribbonBody').text(ribbon);
	}
	
	function emptyStack(message) {
		scroller.unbind( '.infiniteScroller' );
		scroller.empty();
		$('#holder').html(message);
	}
	
});
