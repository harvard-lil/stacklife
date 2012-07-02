$(document).ready(function() {

// Utilities class (dumping ground for common methods)
var util = (function () {
    var my = {};

    // An IE8 friendly method for the indexOf method
    my.inArray = function( elem, array ) {
        if ( array.indexOf ) {
            return array.indexOf( elem );
        }

        for ( var i = 0, length = array.length; i < length; i++ ) {
            if ( array[ i ] === elem ) {
                return i;
            }
        }
        return -1;
    }
    
    // Returns an array of http params
	my.get_uri_params = function() {
	    var vars = [], hash;

        var hashes = window.location.href.slice(util.inArray('?', window.location.href) + 1).split('&');

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
    	if(uri_params[config.search_type] === 'advanced')
    		return true;
    	else
    		return false;
    }
	
	// Clean up field names
	my.massage_field_name = function(field_name) {
		var field_assoc = new Object;
		field_assoc['format'] = 'Material Format';
		field_assoc['language'] = 'Language';
		field_assoc['lcsh'] = 'Subject';
		field_assoc['holding_libs'] = 'Holding Library';
		field_assoc['creator'] = 'Creator';
		field_assoc['pub_date'] = 'Publication Date';
		return field_assoc[field_name];
	}
	
	// On initial load, populate search field and page title 
	// with http params
	my.populate_form = function() {
		$('#search input:nth-child(2)').val(config.query);
		$('form > select').val(config.search_type);
		document.title = config.query + ' | ShelfLife Search';
	}

    return my; 
}());

// The class to hold all config related items
var config = (function () { 
	var my = {};
	
	// The numeric value the slider operates on
	my.scaled_field = 'shelfrank';
	
    // LibraryCloud location:
    my.lc_url = www_root + '/translators/cloud.php';
	
    uri_params = util.get_uri_params();

    if (uri_params['search_type'] && uri_params['search_type'][0]) {
	    my.search_type = uri_params['search_type'][0];
    }

    if (uri_params['q'] && uri_params['q'][0]) {
	    my.query = uri_params['q'][0];
    }

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
	
    // The list of facets we want to get from LibraryCloud and display
	my.facets = ['format', 'holding_libs','lcsh', 'creator', 'pub_date', 'language'];
	
	// The list of facets we want to display in list form (the missing 
	// facets are used by a jQuery UI widget or ...)
	my.facets_to_display = [
                     'format', 'holding_libs','lcsh', 'creator', 'pub_date', 'language'];
	
	// Facets to be opened (clamshelled open)
	my.facets_open_by_default = ['format',  'holding_libs'];
	
	// The number of facet results we want to display for each category
	my.facet_limits = {'format' : 10, 'holding_libs' : 10, 'lcsh' : 10,
                           'creator' : 10, 'pub_date' : 10, 'language' : 10 };
	
	// A method to combine all of the above params into a query string
	// we can send to LibraryCloud
	my.get_query_string = function() {
		var composite_query = my.search_type + ':' + my.query;
		
		var std_params = ['search_type=' + my.search_type, 'query=' + my.query,
		                  'start=' + my.start, 'limit=' + my.limit, 
		                  'sort=' + my.sort_field + ' ' + my.sort_direction];
				
		$.each(my.facets, function(i, item) {
			std_params.push('facet=' + item);
		});
		
		$.each(my.filters, function(i, item) {
			std_params.push('filter='+ item);
		});

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
			if (/shelfrank/.test(filter_value)) {
				$.each(config.filters, function(i, item) {
					if (/shelfrank/.test(item)) {
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
                          dataType: "JSON",
			  cache: false,
			  success:
				function (results) {
					my.lc_results = results;
				},
			  error:
				 function(obj,status,error) {
                			alert('Search could not be performed.' + error);
            			 }
		});
	}
     
    return my; 
}());

// The class we use to draw our results, facets, filters, etc in the DOM
var view = (function () { 
	var my = {};
	
	// Draw our LibraryCloud results (the table containg the title, creator...)
	my.draw_results = function () {
	    
	    // Draw search results count and paging with Handlebars template
        var source = $("#result-hits-container-template").html();
        var template = Handlebars.compile(source);
        var context = {'start': config.start, 
            'showing': config.start + config.limit,
            'num_found': library_cloud.lc_results.num_found,
            'query': config.query};
        $('#result-hits-container').html(template(context));
	    
	    // Draw search results Handlebars template
        var source = $("#search-results-template").html();
        var template = Handlebars.compile(source);
        var context = {'results': library_cloud.lc_results,
            'sort_direction': config.sort_direction};
        $('#results').html(template(context));
	}

	// Draw our LibraryCloud facets (the list of facets we defined in the config)
	my.draw_facets = function () {

		// This will hold our facet markup string
		var facets = '';
		console.log(library_cloud.lc_results);
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

			// Setup our DOM done so that we can attach a slider to it
            // Draw slider with Handlebars template
            var source = $("#slider-container-template").html();
            var template = Handlebars.compile(source);
            var context = {};
            $('#persistent_controls').html(template(context));

			// Draw our range slider for total_score
			$('select#valueA, select#valueB').selectToUISlider({
				labels: 5,
				sliderOptions: {
					stop: function(event) { 
						filter.add_filter(config.scaled_field + ':[' + $('select#valueA').attr('value') + ' TO ' + $('select#valueB').attr('value') + ']');
					} 
				}
			});
		}
	}
	
	// A helper method. Here we'll clean up our filter labels (they go in the
    	// breadcrumbs)
    	massage_filter_labels = function(label) {
    		return label.replace(/^[^:]*:/, '');
    	}
	
	// Draw the list of filters that are applied to the result set 
	my.draw_filters = function () {
		var filter_text = '<ul id="facet_bread_crumb">';
		$.each(config.filters, function(i, item){
				filter_text += '<li id="' + item + '" class="rem_filter">' + massage_filter_labels(item) + '<span class="refine-arrow"></span></li>';
		});
		filter_text += '</ul>';
		$('#facet_bread_crumb_container').html(filter_text);
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
		}
	}
	
	return my; 
}());
	
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
});

// Toggle our facet lists
$('.facet_heading').live('click', function() {
	// Assume that the next node is our ul (list)
    $(this).next().slideToggle();
    $(this).find('.arrow').toggleClass('arrow-down');
    //console.log(this);
});

// If a user clicks a heading to sort (Year, ShelfRank score, ...)
// set the sort params in the config object and reload the results
$('.sortable').live('click', function() {
	if (config.sort_direction == 'asc') {
		config.sort_direction = 'desc';
	} else {
		config.sort_direction = 'asc';
	}
	
	library_cloud.get_results();
	view.draw_results();
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


// Handlebars helpers, start


//  return the first item of a list only
// usage: {{#first items}}{{name}}{{/first}}
// directly from https://gist.github.com/1468937
Handlebars.registerHelper('first', function(context, block) {
    if (context == null || context[0] == null) {
        return block('');
    } else {
        return block(context[0]);
    }
});

//  return the a scaled value for the number (this helps us map a color)
// directly from https://gist.github.com/1468937
Handlebars.registerHelper('left_pad', function(value) {
    if (value < 10) {
    	return '0' + value;
    }
    
    return value;
});

// Get the class of the arrow direction based on the direction parameter's value
Handlebars.registerHelper('get_sort_direction', function(direction) {
    if (direction === 'asc') {
    	return 'search-arrow-up';
    }
    
    return 'search-arrow-down';
});

//  return the a scaled value for the number (this helps us map a color)
Handlebars.registerHelper('heat', function(scaled_value) {
    return scaled_value === 100 ? 10 : Math.floor(scaled_value / 10) + 1;
});

Handlebars.registerHelper("stripes", function(array, even, odd, fn, elseFn) {
  if (array && array.length > 0) {
    var buffer = "";
    for (var i = 0, j = array.length; i < j; i++) {
      var item = array[i];
 
      // we'll just put the appropriate stripe class name onto the item for now
      item.stripeClass = (i % 2 == 0 ? odd : even);
 
      // show the inside of the block
      buffer += fn(item);
    }
 
    // return the finished buffer
    return buffer;
  }
  else {
    return elseFn();
  }
});

// Handlebars helpers, end