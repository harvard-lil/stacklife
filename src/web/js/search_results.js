$(document).ready(function() {

//var wheight = $(window).height() - $('.header').height() - $('.header').height() - //$('.footer').height();
//$('.container').css('height', wheight);
	
// Utilities class (dumping ground for common methods)
var util = (function () {
    var my = {};
    
    // Returns an array of http params
	my.get_uri_params = function() {
	    var vars = [], hash;
	    var hashes = window.location.href.slice(jQuery.inArray('?', window.location.href) + 1).split('&');
	    
	    // create array for each key
	    for(var i = 0; i < hashes.length; i++) {
	    	hash = hashes[i].split('=');
	    	vars[hash[0]] = [];
	    }
	    
	    // populate newly created entries with values 
	    for(var i = 0; i < hashes.length; i++) {
	        hash = hashes[i].split('=');
	        if (hash[1]) {
	        	vars[hash[0]].push(decodeURIComponent(hash[1].replace(/\+/g, '%20')));
	        }
	    }
	    return vars;
	}
    
    my.is_advanced = function() {
    	var uri_params = util.get_uri_params();
    	if(uri_params['search_type'][0] === 'advanced')
    		return true;
    	else
    		return false;
    }
	
	// Clean up field names
	my.massage_field_name = function(field_name) {
		var field_assoc = new Object;
		field_assoc['source'] = 'Source';
		field_assoc['language'] = 'Language';
		field_assoc['creator'] = 'Creator';
		field_assoc['imprint'] = 'Imprint';
		field_assoc['format'] = 'Material Format';
		field_assoc['desc_subject_lcsh'] = 'Subject';
		field_assoc['rsrc_key'] = 'Online Resource';
		field_assoc['online_avail'] = 'Online Only';
		//field_assoc['pub_date_range'] = 'Publication Date';
		field_assoc['university_checkouts_undergrad_score:[1 TO *]'] = 'Checked out by undergrads';
		field_assoc['university_checkouts_grad_score:[1 TO *]'] = 'Checked out by grad students';
		field_assoc['university_checkouts_fac_score:[1 TO *]'] = 'Checked out by faculty';
		field_assoc['wp_categories'] = 'Wikipedia Categories';
		
		return field_assoc[field_name];
	}
	
	// Clean up source
	my.massage_source_name = function(source_name) {
		var source_assoc = new Object;
		source_assoc['harvard_edu'] = 'Harvard University';
		source_assoc['sfpl_org'] = 'San Francisco Public Library';
		source_assoc['ted_com'] = 'TED';
		source_assoc['sjlibrary_org'] = 'San Jose Public Library';
		source_assoc['darienlibrary_org'] = 'Darien Public Library';
		source_assoc['northeastern_edu'] = 'Northeastern University';
		source_assoc['wikipedia_org'] = 'Wikipedia';
		source_assoc['youtube_com'] = 'YouTube';
		source_assoc['npr_org'] = 'NPR';
		source_assoc['openlibrary_org'] = 'Open Library';
		source_assoc['1'] = 'Online Only';
		
		return source_assoc[source_name];
	}
	
	// On initial load, populate search field and page title 
	// with http params
	my.populate_form = function() {
		$('#search input:nth-child(2)').val(config.query);
		$('form > select').val(config.search_type);
		document.title = config.query + ' | ShelfLife Search';
	}
	
	// We heatmap our shelfrank fields based on the scaled value
	my.get_heat = function(scaled_value) {
		if (scaled_value >= 0 && scaled_value < 10) {
			return 1;
		}
		if (scaled_value >= 10 && scaled_value < 20) {
			return 2;
		}
		if (scaled_value >= 20 && scaled_value < 30) {
			return 3;
		}
		if (scaled_value >= 30 && scaled_value < 40) {
			return 4;
		}
		if (scaled_value >= 40 && scaled_value < 50) {
			return 5;
		}
		if (scaled_value >= 50 && scaled_value < 60) {
			return 6;
		}
		if (scaled_value >= 60 && scaled_value < 70) {
			return 7;
		}
		if (scaled_value >= 70 && scaled_value < 80) {
			return 8;
		}
		if (scaled_value >= 80 && scaled_value < 90) {
			return 9;
		}
		if (scaled_value >= 90 && scaled_value <= 100) {
			return 10;
		}
	}
	
	// Here we pad any values less than 10 with a 0
	my.left_pad = function(value) {
		if (value < 10) {
			return '0' + value;
		}
		
		return value;
	}
	
    return my; 
}());

// The class to hold all config related items
var config = (function () { 
	var my = {};
	
	// A method to get our sort field name (translate between total score and
	// the related scaled)
	my.get_scaled_field = function() {
                return 'shelfrank';
                /*
		if (my.sort_field === 'university_total_score') {
			return 'university_scaled';
		}
		if (my.sort_field === 'undergrad_total_score') {
			return 'undergrad_scaled';
		}
		if (my.sort_field === 'library_total_score') {
			return 'library_scaled';
		}
                */
	}
	
    // LibraryCloud location:
    my.lc_url = '/librarycloud/v.3/api/item/';
	
    uri_params = util.get_uri_params();

    my.search_type = uri_params['search_type'][0];
    my.query = uri_params['q'][0];

    // If we don't get a complete search request, let's
    // set a keyword search with an empty string (LC will give us all docs)
    if (!my.search_type || !my.query) {
    	my.search_type = 'keyword';
        my.query = '';
    }
    
    my.start = 0;
    my.limit = 20;
    my.sort_field = 'shelfrank';
    my.sort_direction = 'desc'

	// The container for our filters (when someone clicks a facet, that becomes
	// a fliter
	my.filters = new Array();
    
    // Push any incoming filters onto our filters list (these would 
    // probably come from an advanced search
    if (uri_params['filter'] && uri_params['filter'][0]) {
    	my.filters = my.filters.concat(uri_params['filter']);
    }
	
	// The container for our filters (when someone clicks a facet, that becomes
	// a fliter
	my.filter_facet_queries = new Array();
	
    // Our librarycloud API key
    my.key = 'BUILD-LC-KEY';
    
    // The list of facets we want to get from LibraryCloud and display
	my.facets = [
                     //'pub_date_range', 
                      'online_avail', 'source', 'rsrc_key',
                     'format', 
	             'desc_subject_lcsh', 'language', my.get_scaled_field(),
	             'wp_categories'];
	
	// The list of facet queries we want to display
	/*
	my.facet_query = ['university_checkouts_undergrad_score:[1 TO *]',
	                  'university_checkouts_grad_score:[1 TO *]',
	                  'university_checkouts_fac_score:[1 TO *]'];
        */
	
	// The list of facets we want to display in list form (the missing 
	// facets are used by a jQuery UI widget or ...)
	my.facets_to_display = [
                     'online_avail', 'source',
	             'rsrc_key', 
                     'language',
	             'format', 'desc_subject_lcsh',
                     //'pub_date_range', 
                     'holding_libs', 'wp_categories'];
	
	// Facets to be opened (clamshelled open)
	my.facets_open_by_default = ['online_avail', 'source', 'rsrc_key'];
	
	// Get stats (we'll use them for min and max values in our jQuery UI sliders)
	my.stats = [my.get_scaled_field()];
	
	// The number of facet results we want to display for each category
	my.facet_limits = {
                           'source' : 10,
                           'language' : 10, 
                           //'pub_date_range' : 10,
					   'format' : 10, 'desc_subject_lcsh' : 10,
					   'rsrc_key' : 10, 
					   'holding_libs' : 10};
	
	// A method to combine all of the above params into a query string
	// we can send to LibraryCloud
	my.get_query_string = function() {
		var composite_query = my.search_type + ':' + my.query;
		
		var std_params = ['search_type=' + my.search_type, 'query=' + my.query,
		                  'start=' + my.start, 'limit=' + my.limit, 
		                  'sort=' + my.sort_field + ' ' + my.sort_direction,
		                  'key=' + my.key];
				
		$.each(my.facets, function(i, item) {
			std_params.push('facet=' + item);
		});
		
		//$.each(my.facet_query, function(i, item) {
		//	std_params.push('facet_query=' + item);
		//});
		
		$.each(my.stats, function(i, item) {
			std_params.push('stats=' + item);
		});
		
		$.each(my.filters, function(i, item) {
			std_params.push('filter='+ item);
		});
		
		//$.each(my.filter_facet_queries, function(i, item) {
		//	std_params.push('filter='+ item);
		//});
		
		return std_params.join('&');
	}
	
	return my; 
}());

// A class to manage our filters (a filter is cretaed when a facet is selected)
var filter = (function () {
    var my = {};
	
    // When we add a facet...
	my.add_filter = function(filter_value) {
		var index = jQuery.inArray(filter_value, config.filters);
		// The filter isn't already in the filter list and we have more than
		// one result to filter on
		if (index === -1) {
			var replaced = false;
			// We need to make sure we're only adding one range filter per field
			if (/_scaled/.test(filter_value)) {
				$.each(config.filters, function(i, item) {
					if (/_scaled/.test(item)) {
						var splice_loc = jQuery.inArray(item, config.filters);
						config.filters.splice(splice_loc, 1, filter_value);
						replaced = true;
					}
				});
			}
			
			// We have a value that we need to replace
			if (!replaced) {
				config.filters.push(filter_value);
			}
		} 

		library_cloud.get_results(); 
		view.draw_filters();
		view.draw_results();
		view.draw_facets();
		drawHelp();
	}

    // When we remove a facet...
	my.rem_filter = function(filter_value) {
		var index = jQuery.inArray(filter_value, config.filters);
		if (index != -1) {
			config.filters.splice(index, 1);
		}
		
		library_cloud.get_results();
		view.draw_filters();
		view.draw_results();
		view.draw_persistent_controls();
		view.draw_facets();
		drawHelp();
	}
	
    return my; 
}());

// The class that we use to get results from LibraryCloud, we hold the
// results here too
var library_cloud = (function () {
    var my = {};
    // Holds the JSON we get back from LibraryCloud
    my.lc_results;

    // The AJAX call to get the results from LibraryCloud
	my.get_results = function() {
		$.ajax({
			  url: config.lc_url + '?' + config.get_query_string(),
			  async: false,
			  success:
				function (results) {
					my.lc_results = results;
				}}
		);
	}
     
    return my; 
}());

// The class we use to draw our results, facets, filters, etc in the DOM
var view = (function () { 
	var my = {};
	
	// Draw our LibraryCloud results (the table containg the title, creator...)
	my.draw_results = function () {
		$('.result-hits-container, #search_results_header tr th').show();
		$('.apology').remove();
		var rows = '';
		if (library_cloud.lc_results.num_found > 0) {
			var showing_num_results = config.start + config.limit;
			if (config.start + config.limit >= library_cloud.lc_results.num_found) {
				showing_num_results = library_cloud.lc_results.num_found;
			}
			$(".result-hits-container").html('<p class="hits">Showing <span class="orange">' + (config.start + 1) + '</span> to <span class="orange">' + showing_num_results + '</span> of <span class="orange">' + library_cloud.lc_results.num_found + '</span> results for &ldquo;' + config.query + '&rdquo;</p>');
//			rows += '<thead><tr><th id="title_sort" class="sort_heading">Title</th><th id="creator" class="sort_heading">Author</th><th id="pub_date" class="sort_heading">Year</th><th id="' + config.sort_field + '" class="sort_heading sortable score">ShelfRank<span class="arrow"></span></th><th><img src="images/info-icon.gif" help="hsort" /></th></tr></thead>';
			$.each(library_cloud.lc_results.docs, function(i, item) {
				if (item.creator == null || item.creator[0] == null) {
					item.creator = '';
				} else {
					if (item.creator instanceof Array) { 
					item.creator = item.creator[0];
					
					if(item.creator === 'NULL')
						item.creator = '';
					}
				}
				
				if(!item.pub_date)
					item.pub_date = '';
					
				rows += '<tr class="result_row"><td class=\"title-column\"><a href="book/' + item.title_link_friendly + '/' + item.id + '?perspective=' + config.get_scaled_field() + '">' + item.title; 
				if (item.ut_score != null && item.ut_score > 0) {
//					rows += ' (' + item.ut_score + ')';
					rows += ' <span class="ut-count">(All Editions)</span>';
				}
				
				rows += '</a></td><td class=\"author-column\">' +
				'<a href=\"author/' + item.creator + '\">' + item.creator + '</a></td><td class=\"year-column\">' + item.pub_date + '</td><td><span class=\"results-score heat' + util.get_heat(item[config.get_scaled_field()]) + '\">' + util.left_pad(item[config.get_scaled_field()]) + '</span></td></tr>';
			});
		} else {
			rows = '<span class=\"apology\">Sorry, no results. Perhaps try <a id=\"inline\" href=\"#advanced\" class=\"button advanced-search\">advanced search</a>?</span>';
			$('.result-hits-container, #search_results_header tr th').hide();
		}
		$('.result_row').remove();
		$('#search_results_body').append(rows);
		$('#searchresults tr:odd').addClass('odd');
	}

	// Draw our LibraryCloud facets (the list of facetswe defined in the config)
	my.draw_facets = function () {
		// Did LibraryCloud supply us with any facet query results?
		// TODO: clean up this count business, it seems a kludgish
		//var facet_query_count = 0;
		//$.each(library_cloud.lc_results.facet_queries, function(i, item) {
		//	if (item > facet_query_count) {
		//		facet_query_count = item;
		//	}

		//});
		
		//var query_facets = '';
		// Community relevance is a facet query and is a corner case. Draw it here.
		//if (facet_query_count > 0) {
		//	query_facets += '<div class="facet_set"><p class="facet_set" id="community_rel">Filter by Group<img src="images/info-icon.gif" help="hrelevance" /></p><ul class="facet_pairs">';
		//	$.each(library_cloud.lc_results.facet_queries, function(i, item) {
		//		if (item > 0){
		//			query_facets += '<li id="' + i + '" class="add_filter">' + util.massage_field_name(i) + '<span class="facet-count"> (' + item + ')</span></li>';
		//		}
		//	});
		//	query_facets += '</ul></div>';
		//}
		//if (query_facets != ''){
		//	$('#query_facets').html(query_facets);
		//} else {
		//	$('#query_facets').html('');
		//}
		
		// This will hold our facet markup string
		var facets = '';
		// Did LibraryCloud supply us with any facet results?
		if (library_cloud.lc_results.facets) {
			$.each(library_cloud.lc_results.facets, function(i, item) {
				// Look for anything that's not empty and is in our list of
				// facets to display 
				if (!jQuery.isEmptyObject(item) && jQuery.inArray(i, config.facets_to_display) !== -1) {
					
					// We want some of our facets clamshelled open by default and some clamshelled
					// closed by default.
					var style_markup = '';
					if (jQuery.inArray(i, config.facets_open_by_default) === -1) {
						style_markup = 'style="display:none;"';
					  }
					
					facets += '<div class="facet_set"><p class="facet_heading">' + util.massage_field_name(i) + '<span class="arrow"></span></p><ul class="facet_pairs" ' + style_markup + '>';
					var count = 1;
					$.each(item, function(facet_key, facet_value) {
						var facet_key_display = facet_key;
						if(i === 'source' || i === 'online_avail') facet_key_display = util.massage_source_name(facet_key);
						facets += '<li id="' + i + ':' + facet_key + '" class="add_filter">' + facet_key_display + '<span class="facet-count"> (' + facet_value + ')</span></li>';
						// This business of getting the length by turning the 
						// object into a string and then getting the length is a total kludge
						if (count == config.facet_limits[i] && $.param(item).split('&').length > count) {
							facets += '<li id="' + i + '" class="more_facets">more</li>';
							return false;
						}
						count++;
					});
					facets += '</ul></div>';
				}
			});
			
		}
		$('.facets').html(facets);

	}
	
	// Some controls we only want to draw once, let's do that here
	my.draw_persistent_controls = function () {
		if (library_cloud.lc_results.docs.length > 0) {
//			$( "#total_score_slider" ).slider('destroy');
			
			// Setup our DOM done so that we can attach a slider to it
			var slider_container_markup = '<div class="facet_heading">Refine by ShelfRank' +
				'<fieldset>' + 
//					'<label for="valueA">From:</label>' + 
					'<select name="valueA" id="valueA" style="display:none">' +
						'<option value="1" selected="selected">1</option><option value="10">10</option>' +
						'<option value="20">20</option><option value="30">30</option>' + 
						'<option value="40">40</option><option value="50">50</option>' + 
						'<option value="60">60</option><option value="70">70</option>' + 
						'<option value="80">80</option><option value="90">90</option>' +
						'<option value="100">100</option>' +
					'</select>' + 
//					'<label for="valueB">To:</label>' + 
					'<select name="valueB" id="valueB" style="display:none">' +
						'<option value="1">1</option><option value="10">10</option>' +
						'<option value="20">20</option><option value="30">30</option>' + 
						'<option value="40">40</option><option value="50">50</option>' + 
						'<option value="60">60</option><option value="70">70</option>' + 
						'<option value="80">80</option><option value="90">90</option>' +
						'<option value="100" selected="selected">100</option>' +  
					'</select>' + 
				'</fieldset> ' +
				'<div id="total_score_slider"><div id="legend"><ul class="legend-box"><li class="one"></li><li class="two"></li><li class="three"></li><li class="four"></li><li class="five"></li><li class="six"></li><li class="seven"></li><li class="eight"></li><li class="nine"></li><li class="ten"></li></div></div></div>';
			
			$('#persistent_controls').html(slider_container_markup);
			
			// Draw our range slider for total_score
			$('select#valueA, select#valueB').selectToUISlider({
				labels: 5,
				sliderOptions: {
					stop: function(event) { 
						filter.add_filter(config.get_scaled_field() + ':[' + $('select#valueA').attr('value') + ' TO ' + $('select#valueB').attr('value') + ']');
					} 
				}
			});
			
//			$( "#total_score_slider" ).slider({
//				range: true,
//				min: library_cloud.lc_results.stats[config.get_scaled_field()].min,
//				max: library_cloud.lc_results.stats[config.get_scaled_field()].max,
//				values: [library_cloud.lc_results.stats[config.get_scaled_field()].min, library_cloud.lc_results.stats[config.get_scaled_field()].max],
//				stop: function(event, ui) {
//					$( "#total_score_label" ).val($( "#total_score_slider" ).slider( "values", 0 ) +
//							" - " + $( "#total_score_slider" ).slider( "values", 1 ) );
//					filter.add_filter('total_score:[' + ui.values[ 0 ] + ' TO ' + ui.values[ 1 ] + ']');
//				},
//				create: function(event, ui) {
//					$( "#total_score_label" ).val($( "#total_score_slider" ).slider( "values", 0 ) +
//							" - " + $( "#total_score_slider" ).slider( "values", 1 ) );
//				}
//			});			
		}
	}
	
	// A helper method. Here we'll clean up our filter labels (they go in the
	// breadcrumbs)
	massage_filter_labels = function(label) {
		if (/^university_checkouts_undergrad_score.+/.test(label)) {
			return 'Checked out by undergraduates';
		}
		if (/^university_checkouts_grad_score.+/.test(label)) {
			return 'Checked out by graduates';
		}
		if (/^university_checkouts_fac_score.+/.test(label)) {
			return 'Checked out by faculty';
		}

		return label.replace(/^[^:]*:/, '');		
	}
	
	// Draw the list of filters that are applied to the result set 
	my.draw_filters = function () {
		var filter_text = '<ul id="facet_bread_crumb">';
		$.each(config.filters, function(i, item){
			// We don't want to display filters controlled by our range sliders
			if (!/_scaled/.test(item)) {
				filter_text += '<li id="' + item + '" class="rem_filter">' + massage_filter_labels(item) + '<span class="refine-arrow"></span></li>';
			}
		});
		filter_text += '</ul>';
		$('#facet_bread_crumb_container').html(filter_text);
		
		// Draw help buttons
			drawHelp()
	}
	
	// Draw the paging controls (the next and prev arrows)
	my.draw_paging_controls = function () {
		
		if (config.start + config.limit <= library_cloud.lc_results.num_found) {
			$('.next-page').show();
		} else {
			$('.next-page').hide();
		}
		
		if (config.start - config.limit >= 0) {
			$('.prev-page').show();
		} else {
			//$('.prev-page').hide();
		}

	}
	
	return my; 
}());

function drawHelp(){
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
         		url: 'js/help.json', // URL to the JSON script
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
}
	
// DOM event controls, start
// A filter is applied...
$('.add_filter').live('click', function() {
	filter.add_filter(this.id);
	view.draw_paging_controls();
});

// A filter is removed...
$('.rem_filter').live('click', function() {
	filter.rem_filter(this.id);
	view.draw_paging_controls();
});

// The next arrow is clicked. Adjust the paging values in config,
// ask LibraryCloud for some new results, and redraw
$('.next-page').live('click', function() {
	if (config.start + config.limit <= library_cloud.lc_results.num_found) {
		config.start = config.start + config.limit;
	}

	library_cloud.get_results();
	view.draw_filters();
	view.draw_results();
	view.draw_paging_controls();
});

// The prev arrow is clicked. Adjust the paging values in config,
// ask LibraryCloud for some new results, and redraw
$('.prev-page').live('click', function() {
	
	if (config.start - config.limit >= 0) {
		config.start = config.start - config.limit;
	}
	
	library_cloud.get_results();
	view.draw_filters();
	view.draw_results();
	view.draw_paging_controls();
});

// We allow users to add chunks of 10 facet values at a time...
$('.more_facets').live('click', function() {
	var facet_field = this.id;
	 var facet_markup = '<ul>';
	 $.each(library_cloud.lc_results.facets[facet_field], function(i, item) {
			facet_markup += '<li id="' + facet_field + ':' + i + '" class="add_filter" onClick="$.fancybox.close()">' + i + '<span class="facet-count"> (' + item + ')</span></li>';
		});
	 facet_markup += '</ul>';
	
	$.fancybox({
		'content' : facet_markup
	});
//	config.facet_limits[this.id] += 10;
//	view.draw_facets();
});

// Toggle our facet lists
$('.facet_heading').live('click', function() {
	// Assume that the next node is our ul (list)
//	$('.facet_set p').not(this).not('#community_rel').next('ul').slideUp();
    $(this).next().slideToggle();
    $(this).find('.arrow').toggleClass('arrow-down');
});

// If a user clicks a heading to sort (Year, ShelfRank score, ...)
// set the sort params in the config object and reload the results
$('.sortable').live('click', function() {
	$(this).find('.search-arrow-down').toggleClass('search-arrow-up');
	if (config.sort_direction == 'asc') {
		config.sort_direction = 'desc';
	} else {
		config.sort_direction = 'asc';
	}
	
	library_cloud.get_results();
	view.draw_results();
});

// If a user changes shelf rank view
$('#weight_select').change(function() {
	config.sort_field = $('#weight_select option:selected').attr('value');
	$('.score_sortable').attr('id', config.get_scaled_field());
	
	library_cloud.get_results(); 
	view.draw_filters();
	view.draw_results();
	view.draw_persistent_controls();
	view.draw_facets();
});

// Advanced search box controls, start
  	
  	// If a user adds another field/query search pair
	$('.addfield').live('click', function() {
		$('.searchBox:last').parent().after('<p><select class="filter_type"><option value="title_exact">Title begins with</option><option value="title_keyword">Title contains keyword(s)</option><option value="creator_exact">Author (last, first)</option><option value="creator_keyword">Author contains keyword(s)</option><option value="desc_subject_lcsh_exact">Subject begins with</option><option value="desc_subject_lcsh_keyword">Subject contains keyword(s)</option><option value="keyword" selected="selected">Keyword(s) anywhere</option></select><input type="hidden" value="" name="filter" /> <input type="text" class="searchBox filter_query" /></p>');
		
		if($('#advanced .searchBox').size() > 4)
			$(this).removeClass('addfield');
		if($('#advanced .searchBox').size() > 1)
			$('#addremove span:last').addClass('removefield');
	});
	
	// If a user removes a field/query search pair
	$('.removefield').live('click', function() {
		$('#advanced .searchBox:last').parent().remove();
		
		if($('#advanced .searchBox').size() < 2)
			$(this).removeClass('removefield');
		if($('#advanced .searchBox').size() < 6)
			$('#addremove span:first').addClass('addfield');
	});
	
	// Control the clamshelling of our facet lists here
	$('#advanced .facet_set p').live('click', function() {
		$('#advanced .facet_set p').not(this).next('ul').slideUp();
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

//DOM event controls, end

// When the page is first loaded, let's set things up here
if(util.is_advanced()) {
	document.title = 'Advanced Search | ShelfLife Search';
	$('.search-container').hide();
	$("a#inline").fancybox({
    	'overlayShow': true,
    	'autoDimensions' : false,
    	'width' : 700,
    	'height' : 400
    });
	$("a#inline").trigger('click');
}
else {
	library_cloud.get_results();
	view.draw_persistent_controls();
	view.draw_facets();
	view.draw_results();
	view.draw_filters();
	view.draw_paging_controls();
	util.populate_form();
}

});
