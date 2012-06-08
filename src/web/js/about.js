// The JavaScript for the landing page

$(document).ready(function() {
	var scroller = $('#scroller-wrapper');

	// Set the size of our stack based on the size of the window
	var stackheight = $(window).height() - $('.header').height() - $('.footer').height() - 12;
	$('#scroller-wrapper').css('height', stackheight);
	$(window).resize(function() {
		stackheight = $(window).height() - $('.header').height() - $('.header').height() - $('.footer').height();
		scroller.css('height', stackheight);
		$('.container').css('height', stackheight);
	});

	// Build the options for the StackScroller
	var stackoptions = {hollis: '',
					books_per_page: 50,
					orientation: 'V',
					axis: 'y',
					threshold: 2000,
					heatmap: 'yes',
					pagemultiple: 0.13,
					heightmultiple: 15.8};

	
	$.getJSON('/librarycloud/v.3/api/item/?callback=?&key=BUILD-LC-KEY&sort=loc_sort_order%20asc', $.param({ 'query' : 'true', 'search_type' : 'recent_acq', 'limit' : 1 }),
			function (data) {
				if ( data.docs && data.docs.length > 0 ) {
					stackoptions.url = '/librarycloud/v.3/api/item/?key=BUILD-LC-KEY';
					stackoptions.search_type = 'recent_acq';
					stackoptions.query = 'true';
					scroller.stackScroller(stackoptions);
					$('.subject-hits').html(data.num_found + '<br />items');
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
  	$('ul.stack li').live('click', function(){
  		window.location = 'book/' + $(this).data('item_details').title_link_friendly + '/' + $(this).data('item_details').id_hollis;
  	});
	
  	
  	// Advanced search box controls, start
  	
  	// If a user adds another field/query search pair
	$('.addfield').live('click', function() { console.log($('#advanced .searchBox'));
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
	$('.facet_set p').live('click', function() {
		$('.facet_set p').not(this).next('ul').slideUp();
		$(this).next().slideToggle();
	});
	
	// The field part of our field/query search pair
	$('.filter_type').live('change', function() {
		var that = $(this).next();
		$(this).next().val($(this).val() + ':' + that.next().val() );
	});
	
	// The query part of our field/query search pair
	$('.filter_query').live('change', function() {
		var that = $(this).prev();
		$(this).prev().val(that.prev().val() + ':' + $(this).val() );
	});
	
	// Info boxes
	$("img[src$='info-icon.gif']").each(function() {
		var helptype = $(this).attr('help');
		
		$(this).qtip({
   		style: {
      		classes: 'ui-tooltip-light'
   		},
   		content: {
      		text: 'Loading...', // Loading text...
      		ajax: {
         		url: 'js/help.json', // URL to the JSON script
         		type: 'GET', // POST or GET
         		dataType: 'json', // Tell it we're retrieving JSON
         		success: function(data, status) {
            		var content = data[helptype];
            		this.set('content.text', content);
         		}
      		}
      	}
	});
	});


	////
	// DOM controls, end
	////
});
