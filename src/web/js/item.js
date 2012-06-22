$(document).ready(function() {

	History.Adapter.bind(window,'statechange',function(){
		var State = History.getState(); 
		draw_item_panel(State.data.data);
	});
	
	// Fetch data about the item
	$.ajax({
  		url: '/platform/v0.03/api/item/',
  		dataType: 'json',
  		data: {query : uid, search_type : 'id', start : '0', limit : '1'},
  		async: false,
  		success: function(data){
  			if(data.docs[0].loc_call_num_sort_order && data.docs[0].loc_call_num_sort_order != undefined)
  				loc_call_num_sort_order = data.docs[0].loc_call_num_sort_order;
  			uniform_count = data.docs[0].ut_count;
  			uniform_id = data.docs[0].ut_id;
  			if (data.docs[0].lcsh != undefined) { 
				$.each(data.docs[0].lcsh, function(i, item) {
					item = item.replace(/\.\s*$/, '');
					if(anchor_subject === '') {
  						anchor_subject = item;
  					}
				});
			}
			var this_details = data.docs[0];
			History.replaceState({data:this_details,rand:Math.random()}, this_details.title, "../" + this_details.title_link_friendly + "/" + this_details.id);
    }
	});

	var stackheight = $(window).height() - $('.header').height();

	$('.container').css('height', stackheight);
	$('#viewerCanvas').css('height', stackheight*.9).css('width', stackheight*.75);

	$(window).resize(function() {
		stackheight = $(window).height() - $('.header').height();
		$('.container').css('height', stackheight);
		$('#viewerCanvas').css('height', stackheight*.9).css('width', stackheight*.75);
	});

	if(uniform_count > 1) {
		$('#fixedstack').stackView({url: www_root + '/cloud.php', search_type: 'ut_id', query: uniform_id, ribbon: $('#uniform').text()});
	}
	else if (loc_call_num_sort_order) {
		$('#fixedstack').stackView({url: www_root + '/cloud.php', search_type: 'loc_call_num_sort_order', id: loc_call_num_sort_order, ribbon: 'Call number shelf: what you\'d see in the library'});
	}
	else if(anchor_subject !== '') {
		$('#fixedstack').stackView({url: www_root + '/cloud.php', search_type: 'lcsh', query: anchor_subject, ribbon: anchor_subject});
	}
	else if(anchor_subject === '') {
		$('#fixedstack').text('Sorry, no Library of Congress call number or subject neighborhood found.');
	}
	
	if(!loc_call_num_sort_order) {
		$('#callview').text('No call number stack').removeClass('button').removeClass('stack-button').addClass('button-disabled');
	}

   	// load availability details
	function loadAvailability (page, div) {
		var location = '',
		callno = '',
		availableresult = '',
		notresult = '',
		isavailable = '',
		isdepository = '';
		
		div.empty();
		$(page).find('p.location').each(function() {				
			location = $(this).text();
			var availabilities = $(this).nextUntil('p');
			$(availabilities).find('td.call').each(function() {
				if($(this).text() != 'Call #') {
					callno = $(this).html().replace('<br>', ' ');
					callno = callno.replace(new RegExp("\<br\>", "gi"), ' ');
					callno = jQuery.trim(callno);
					if(callno.length > 0)
						callno = ' [' + callno + ']';
				}
				$(this).prev().each(function() {
					if($(this).text() == 'RESERVE' || $(this).text() == 'Reserve') {
						callno = ' [Reserve]';
					}
				});
				$(this).next().children('#availtable_status_text').each(function() {
					isdepository = '';
					if($(this).prev().prev('td.col').text() == 'Harvard Depository')
						isdepository = 'yes';
					if($(this).text() != 'Status') {
						var availability = $(this).html().replace('<br>', ': ');
						availability = availability.replace(new RegExp("\<br\>", "gi"), ': ');
						if((availability == 'Regular loan: Not checked out' || availability == '28-day loan: Not checked out'|| availability == '7-day loan: Not checked out' || availability == 'Regular loan: Not checked out: Widener copy') && isdepository == '') {
							availableresult += '<li class="available"><span class="callno">' + location + '' + callno + '</span> <span class="small-button sms">SMS</span><br />' + availability + '</li>';
							isavailable = 'yes';
						}
						else if(availability == 'Regular loan (depository): Not checked out' || isdepository == 'yes') { 
							var requestlink = $(this).parent().next().children('a[href^="http://hollisservices"]').attr('href'); 
							if(requestlink != undefined) {
								isavailable = 'yes';
								availableresult += '<li class="available availability"><span class="callno">Depository' + callno + '</span> <a class="small-button" href="' + requestlink + '">REQUEST</a></li>';
							}
							else
								notresult += '<li class="not-available"><span class="callno">Depository' + callno + '</span><br />' + availability + '</li>';
							}
							else {
						notresult += '<li class="not-available"><span class="callno">' + location + '' + callno + '</span><br />' + availability + '</li>';
						}
					}
				});				
			});
			$(availabilities).next('.noitems').each(function() {
				var findingaid = $(this).prev().find('.collection').text();
				
				var explanation = $(this).html();
				notresult += '<li class="not-available"><span class="callno">' + location + ' [' + findingaid + ']</span><br />' + explanation + '</li>';
			});
			});
			div.html(availableresult);
			div.append(notresult);
			
			if(availableresult != '' || notresult != '') {
				$('.button-availability').show();
			} else {
				$('.button-availability').hide();
			}
				
			if(isavailable != 'yes') {				
				$('.button-availability').removeClass('available-button').addClass('not-available-button');
			} else {
				$('.button-availability').addClass('available-button').removeClass('not-available-button');
			}
			div.wrapInner('<ul>');
		}

	$('.slide-more').live('click', function() {
		$(this).next('.slide-content').slideToggle();
		$(this).find('.arrow').toggleClass('arrow-down');
	});
	
	$('.sms').live('click', function() {

		//find item locations
	
		var location = $(this).parent().find('.callno:first').text();
	
		//build form
		var html = ""; 
		if(location.length>0) {	
			html = "<div id='wrap'><p>" + location + "<br />" + title + "</p><br /><form id='form'><input id='smstitle' type='hidden' value='" + title + "' /><input id='smslibrary' type='hidden' value='" + location + "' /><input id='smsnumber' type='text' size='12' maxlength='12' placeholder='your number' />";
			html += "<select id='smscarrier'><option>Select a Carrier</option>";
			html += "<option value=@txt.att.net>AT&T</option>";
			html += "<option value=@message.alltel.com>Alltel</option>";
			html += "<option value=@myboostmobile.com>Boost</option>";
			html += "<option value=@mobile.mycingular.com>Cingular</option>";
			html += "<option value=@messaging.nextel.com>Nextel</option>";
			html += "<option value=@tmomail.net>T-Mobile USA</option>";
			html += "<option value=@vtext.com>Verizon Wireless</option>";
			html += "<option value=@vmobl.com>Virgin Mobile USA</option></select>";
			html += "</select></form></div>";
		} else {
			html += "<p>Something is amiss, are all the items at HD or networked?</p>";
		}
		launchDialog(html);
	});

	// When an item in the stack is clicked, we update the book panel here
	function draw_item_panel(item_details) {

		uid = item_details.id;
		
		// Here we pad any values less than 10 with a 0
		function left_pad(value) {
			if (value < 10) {
				return '0' + value;
			}

			return value;
		}

		// store this as an "also viewed"
		$.each(alsoviewed, function(i, item){

      $.ajax({
        type: "POST",
        url: slurl,
        data: "also="+ item + "&id=" + item_details.id + "&function=set_also_viewed",
        success: function(){
        }
      });
      alsoviewed.push(item_details.id);
    });

		// add to recently viewed
		$.ajax({
			type: "POST",
			url: slurl,
			data: "function=session_info&type=set&uid=" + item_details.id,
			async: false
		});
		recentlyviewed += '&recently[]=' + uid;
		$('#recentlyviewed').html('<span class="reload">You recently viewed these</span>');
		$('#recentlyviewed').addClass('stack-button').removeClass('button-disabled');

		loc_call_num_sort_order = item_details.loc_call_num_sort_order;

		// set our global var
		hollis = item_details.id_inst;
		title = item_details.title;
		
		// update our window title
		document.title = title + ' | ShelfLife';

		// replace title
		var home_stack_title = title;
		if(item_details.sub_title != undefined)
			home_stack_title += ' : ' + item_details.sub_title;
		$('.home-stack').text(home_stack_title);

		// replace creator list
		$('#creator_container').html('');
		if(item_details.creator && item_details.creator.length > 0) {
			var creator_markup_list = [];
			$.each(item_details.creator, function(i, item){
				creator_markup_list.push('<a class="creator" href="../../author/' + item + '">' + item + '</a>');
			});

			var creator_markup = creator_markup_list.join('<span class="divider"> | </span>');
			$('#creator_container').html(creator_markup);
		}

		var imprint_vals = [];

		if (item_details.pub_location) {
			imprint_vals.push(item_details.pub_location);
		}
		if (item_details.publisher) {
			imprint_vals.push(item_details.publisher);
		}
		if (item_details.pub_date) {
			imprint_vals.push(item_details.pub_date);
		}

		// replace imprint
		$('.imprint').text(imprint_vals.join(', '));

		// replace scores
		$('.shelfRank').text(left_pad(item_details.shelfrank));

		var perspective_markup = '';
        if (item_details.aggregation_checkout) {
            perspective_markup += '<p><strong id="fac_val">' + item_details.aggregation_checkout + '</strong>  checkouts</p><br />';
            perspective_markup += '<p>For this demo, we are only tracking checkouts aggregated from participating libraries. ShelfRank will factor in downloads, views, social activity and much more, and will make those factors transparent here.</p>';
		}
		else {
			perspective_markup += '<p>ShelfRank will factor in downloads, views, social activity, circulation information from participating libraries, and much more, and will make those factors transparent here.</p><br /><p>Using randomized circ data for this item.</p>';
		}

		$('#rank-math').html(perspective_markup);

		// Translate a total score value to a class value (after removing the old class)
		$('.shelfRank').removeClass(function (index, css) {
		    return (css.match(/color\d+/g) || []).join(' ');
		});

		$('.shelfRank').addClass('color' + get_heat(item_details.shelfrank));
		$('.itemData-container').removeClass(function (index, css) {
		    return (css.match(/color\d+/g) || []).join(' ');
		});

		$('.itemData-container').addClass('color' + get_heat(item_details.shelfrank));
		
		// replace hollis and button vals
		$('#hollis_button').attr('href', 'http://holliscatalog.harvard.edu/?itemid=|library/m/aleph|' + hollis);

		$('.unpack').removeClass(function (index, css) {
		    return (css.match(/color\d+/g) || []).join(' ');
		});

		$('.unpack').addClass('color' + get_heat(item_details.shelfrank));
		// replace google books link
		// get the google books info for our isbn and oclc (and if those are empty, use 0s)
		var isbn = -1;
		if (item_details.id_isbn && item_details.id_isbn[0] && item_details.id_isbn[0].split(' ')[0]) {
			isbn = item_details.id_isbn[0].split(' ')[0];
		}

		var oclc = -1;
		if (item_details.id_oclc) {
			oclc = item_details.id_oclc;
		}
		
		var gbsrc = 'http://books.google.com/books?jscmd=viewapi&bibkeys=OCLC:' + oclc + ',ISBN:' + isbn + '&callback=ProcessGBSBookInfo';
		$("#gbscript").attr('src', gbsrc);		
		
		GBSArray = ['ISBN:' + isbn, 'OCLC:' + oclc];
		$.getScript($("#gbscript").attr('src'));
		
		// replace advanced data
		$('.advanced-isbn p').text('ISBN:');
		$('.advanced-oclc p').text('OCLC:');
		if(isbn != undefined && isbn != -1)
			$('.advanced-isbn p').text('ISBN: ' + isbn);
		if(oclc != undefined && oclc != -1)
			$('.advanced-oclc p').text('OCLC: ' + oclc);
		$('.advanced-language p').text('Language: ' + item_details.language);

		// replace cover image
		$('#itemData .ol-cover-image').attr('src', 'http://covers.openlibrary.org/b/isbn/' + isbn + '-M.jpg');

		// replace subject buttons
		var subject_markup = '<span class="heading">Library Shelves</span><ul>';

		if(item_details.ut_count > 0)
			subject_markup += '<li id="uniform" class="button stack-button"><span class="reload">All Editions</span></li>';

		subject_markup += '<li id="callview" class="button stack-button"><span class="reload">Infinite bookshelf</span></li>';

		if (item_details.lcsh != undefined) {
			$.each(item_details.lcsh, function(i, item) {
				item = item.replace(/\.\s*$/, '');
				subject_markup += '<li class="subject-button" id=" ' + item.replace(/[\W]/g, '_') + '"><span class="reload">' + item + '</span></li>';
			});
		}

		subject_markup += '</ul>';
		$('.subjects').html(subject_markup);
		
		if(!loc_call_num_sort_order) {
			$('#callview').text('No call number stack').removeClass('button-selected').removeClass('button').removeClass('stack-button').addClass('button-disabled');
		}

		// replace wikipedia buttons
		$('.wikipedia').html('');
		if (item_details.wp_categories != undefined) {
			var wikipedia_markup = '<span class="heading">Wikipedia Shelves</span><ul>';
			$.each(item_details.wp_categories, function(i, item) {
				wikipedia_markup += '<li class="wp_category-button"><span class="reload">' + item + '</span></li>';
			});
			wikipedia_markup += '</ul>';
			$('.wikipedia').html(wikipedia_markup);
		}

		// wikipedia link
		if (item_details.wp_url != undefined) {
			$('.wikipedia_link a').attr('href', item_details.wp_url);
			$('.wikipedia_link').show();
		}
		else {
			$('.wikipedia_link').hide();
		}
		
		$("#toc").html('');
		var toc = item_details.toc[0];
		toc = toc.replace(/--/g, '<br />').replace(/- -/g, '<br />').replace(/-/g, '<br />');
		if(toc) {
		  $("#toc").html('<span class="heading">Table of Contents</span><p class="longtoc>' + toc + '</p>');
		}
		
		// load availability data
		$.ajax({
			url: slurl + "?hollis=" + hollis + "&function=fetch_availability",
			method: 'GET',
			success: function(page){loadAvailability(page, $('#availability'));}
		});
		
		var trimmed_isbns = [];
		if (item_details.id_isbn && item_details.id_isbn[0]) {
			trimmed_isbns = item_details.id_isbn[0].split(' ');  			
		}

		// If we have our first isbn, get affiliate info. if not, hide the DOM element
		if (trimmed_isbns[0]) {
			$.ajax({
				type: "GET",
				url: slurl,
				data: "isbn=" + trimmed_isbns[0] + "&function=check_amazon",
				success: function(response){
					if(response != 'false') {
						$('#amzn').attr('href', 'http://www.amazon.com/dp/' + response);
						$('#abes').attr('href', 'http://www.abebooks.com/products/isbn/' + response);
						$('#bandn').attr('href', 'http://search.barnesandnoble.com/booksearch/ISBNInquiry.asp?EAN=' + response);
						$('#hrvbs').attr('href', 'http://site.booksite.com/1624/showdetail/?isbn=' + response);
	
						$('.buy').show();
					} else {
						$('.buy').hide();
					}
				}
		});
		} else {
			$('.buy').hide();
		}

		// Redraw our tags
		drawTagNeighborhood();

	}

	// When a new anchor book is selected
	$('.stack-item a').live('click', function(e){
	  var this_details = $(this).parent().data('stackviewItem');
		$.ajax({
  		url: '/platform/v0.03/api/item/',
  		dataType: 'json',
  		data: {query : this_details.id, search_type : 'id', start : '0', limit : '1'},
  		async: false,
  		success: function(data){
			  var this_details = data.docs[0];
			  History.pushState({data:this_details,rand:Math.random()}, this_details.title, "../" + this_details.title_link_friendly + "/" + this_details.id);
        	draw_item_panel(data.docs[0]);
        }
	  });
		$('.active-item').removeClass('active-item');
		$(this).parent().addClass('active-item');
		e.preventDefault();
	});

	$('.stack-button').live('click', function() {
		var compare = $.trim($(this).attr('id'));
		var nlabel = $(this).text();
		if(compare === 'recentlyviewed') {
			$('#fixedstack').stackView({url: www_root + '/recently.php?' + recentlyviewed, search_type: 'recently', ribbon: 'You recently viewed these'});
		}
		else if(compare === 'callview') {
			$('#fixedstack').stackView({url: www_root + '/cloud.php', search_type: 'loc_call_num_sort_order', id: loc_call_num_sort_order, items_per_page: 25, ribbon: 'Call number shelf: what you\'d see in the library'});
		}
		else if(compare === 'alsoviewed') {
			$('#fixedstack').stackView({url: www_root + '/also.php', query: uid, search_type: 'also', ribbon: 'People who viewed this also viewed these'});
		}
		else if(compare === 'uniform') {
			$('#fixedstack').stackView({url: www_root + '/cloud.php', search_type: 'ut_id', query: uniform_id, items_per_page: 25, ribbon: ulabel});
		}
	});

	$('.subject-button').live('click',function() {
		$('#fixedstack').stackView({url: www_root + '/cloud.php', search_type: 'lcsh', query: $(this).text(), items_per_page: 25, ribbon: $(this).text()});
	});
	
	$('.wp_category-button').live('click',function() {
		$('#fixedstack').stackView({url: www_root + '/cloud.php', search_type: 'wp_categories', query: $(this).text(), items_per_page: 25, ribbon: $(this).text()});
	});

	$('.tag-button').live('click', function() {
		$('#fixedstack').stackView({url: www_root + '/tag.php', query: $('span', this).text(), search_type: 'tag', ribbon: $('span', this).text()});
	});

    //
    //	User Generated Content
    //

    $("#book-tags-form").validate({
    	errorPlacement: function(error, element) {
    		error.insertAfter( element.next("input") );
    	},
		messages: {
			bookTags: "tag?"
		},
		submitHandler: function(form) {
			var tags     = encodeURIComponent($('#bookTags').attr('value'));
			$.ajax({
				type: "POST",
				url: slurl,
				data: "tags="+ tags + "&uid=" + uid + "&function=set_book_tag",
				success: function(){
					var phrases = ['Nice!', 'Good one!', 'Woot!', 'Rock n\' roll!', 'Hey thanks.', 'Super cool!', 'Yeah, that seems like a good one-', 'Smart.', 'Keep \'em coming!', 'They say the darkest hour is right before the dawn', 'en fuego!'];
					var number = Math.floor(Math.random()*phrases.length);
					$('#book-tags').attr('value', '');
					$('.book-tag-success span').text(phrases[number]);
					$('.book-tag-success span').fadeIn().delay(750).fadeOut(400);
					drawTagNeighborhood();
				}
			});
			return false;
		}
	});
}); //end document ready

// We heatmap our shelfrank fields based on the scaled value
function get_heat(scaled_value) {
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

function drawTagNeighborhood(){
	$.getJSON(slurl + "?callback=?&function=fetch_tag_cloud", $.param({ 'uid' : uid }), function(data) {
		$("#tagGraph").empty();
		var tagList = '';
		if(data.tags.length > 0) {
			$.each(data.tags, function(i, val) {
				var percentage = val.freq/val.biggest * 100;
				percentage = Math.round(percentage) + '%';
				tagList += '<li class="tag-button button"><span class="reload">' + val.tag + '</span> (' + val.freq + ')</li>';
			});

			$('#tagGraph').append('<span class="heading">Tags</span><ul>' + tagList + '</ul>');
		}
	});
}

function ProcessGBSBookInfo(booksInfo) {
	$('.button-google').hide();
	$('.button-google-disabled').show();
	for (isbn in booksInfo) {
		var GBSParts = isbn.split(':');
		var bookInfo = booksInfo[isbn];
		if (bookInfo) {
			if ((bookInfo.preview == "full" || bookInfo.preview == "partial") && bookInfo.embeddable) {
				$('.button-google-disabled').hide();
				$('.button-google').css('display', 'block');
				$("a#gviewer").fancybox({
					'onStart' : initialize
				});
			} 
        } 
    }
}

function alertNotFound() {
 	document.getElementById('viewerCanvas').innerHTML = '<p>Sorry, no preview available for this book.</p>';
}

function initialize() {
    var viewer = new google.books.DefaultViewer(document.getElementById('viewerCanvas'));
    viewer.load(GBSArray, alertNotFound);
}

function launchDialog(html){ 
	var $dialog = $('<div class="remove"></div>')
		.html(html)
		.dialog({
			autoOpen: false,
			title: 'Text Book Location',
			modal: true,
			resizable: false,
			width: 450 ,
			buttons: { 'Text me': function() { 
				var data = 'number=' + $('#smsnumber').val();
				data += '&carrier=' + $('#smscarrier').val();
				data += '&library=' + $('#smslibrary').val();
				data += '&title=' + $('#smstitle').val();
				$.ajax({
					url: www_root + "/sl_funcs.php?func=text_call_num",
					type: "get",
					data: data,
					success: function(){
						$('#wrap').html("<p>Done!</p>");
					}
				});
				$(this).dialog('close');
			}} 
		});
	$dialog.dialog('open');
	kill = 0;	
}
