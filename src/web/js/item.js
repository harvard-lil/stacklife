$(document).ready(function() {
	if (History.enabled) {
    History.Adapter.bind(window,'statechange',function(){
		  var State = History.getState();
		  if(State.data.data) {
		    draw_item_panel(State.data.data);
		  }
	  });
  }

	// Fetch data about the item
	$.ajax({
  		url: www_root + '/translators/item.php',
  		dataType: 'json',
  		data: {query : uid, search_type : 'id', start : '0', limit : '1'},
  		async: false,
  		success: function(data){
  			if(data.docs[0].loc_call_num_sort_order && data.docs[0].loc_call_num_sort_order != undefined)
  				loc_call_num_sort_order = data.docs[0].loc_call_num_sort_order[0];
  			uniform_count = data.docs[0].ut_count;
  			uniform_id = data.docs[0].ut_id;
  			if (data.docs[0].lcsh != undefined) {
				$.each(data.docs[0].lcsh, function(i, item) {
					//item = item.replace(/\.\s*$/, '');
					if(anchor_subject === '') {
  						anchor_subject = item;
  					}
				});
			}
			var this_details = data.docs[0];
			if ( History.enabled ) {
			  History.replaceState({data:this_details}, this_details.title, "../" + this_details.title_link_friendly + "/" + this_details.id);
			}
			else {
			  draw_item_panel(this_details);
			}
    }
	});

	$('#viewerCanvas').css('height', stackheight*.9).css('width', stackheight*.75);

	$(window).resize(function() {
		stackheight = $(window).height();
		$('.stackview').css('height', stackheight);
		$('#viewerCanvas').css('height', stackheight*.9).css('width', stackheight*.75);
	});

	if(uniform_count > 0) {
		$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'ut_id', query: uniform_id, ribbon: $('#uniform').text()});
		$('#uniform').addClass('selected-button');
	}
	else if (loc_call_num_sort_order) {
		$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'loc_call_num_sort_order', id: loc_call_num_sort_order, ribbon: 'Infinite Stack: the library arranged by call number'});
		$('#callview').addClass('selected-button');
	}
	else if(anchor_subject !== '') {
		$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'lcsh', query: anchor_subject, ribbon: anchor_subject});
		$('.subject-button:first').addClass('selected-button');
	}
	else if(anchor_subject === '') {
		$('#fixedstack').html("<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><b>Sorry</b>, no Library of Congress call number or <br/>subject neighborhood found.");
	}

	$('.stackview').css('height', stackheight);

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

		// set our global var
		loc_call_num_sort_order = item_details.loc_call_num_sort_order;
		title = item_details.title;
		uid = item_details.id;

		// update our window title
		document.title = title + ' | StackLife';

		// store this as an "also viewed"
		$.each(alsoviewed, function(i, item){
      $.ajax({
        type: "POST",
        url: slurl,
        data: "also="+ item + "&id=" + item_details.id + "&function=set_also_viewed",
        success: function(){
        }
      });
    });
    alsoviewed.push(item_details.id);

		// add to recently viewed
		$.ajax({
			type: "POST",
			url: slurl,
			data: "function=session_info&type=set&uid=" + item_details.id,
			async: false
		});
		recentlyviewed += '&recently[]=' + uid;

		// replace creator list
		item_details.creators = '';
		if(item_details.creator && item_details.creator.length > 0) {
			var creator_markup_list = [];
			$.each(item_details.creator, function(i, item){
				creator_markup_list.push('<a class="creator" href="../../author/' + item + '">' + item + '</a>');
			});

			item_details.creators = creator_markup_list.join('<span class="divider"> | </span>');
		}

        if(item_details.source_record.rsrc_key && item_details.source_record.rsrc_key.length > 0){
            var isArray = Array.isArray || function(obj) {
                return Object.prototype.call(obj) == '[object Array]';
            };
            if (!isArray(item_details.source_record.rsrc_key)){
                item_details.source_record.rsrc_key = [item_details.source_record.rsrc_key];
            };
			$.each(item_details.source_record.rsrc_key, function(i, item){
                    if(item == 'wikipedia_org')
                        item_details.wp_url = item_details.source_record.rsrc_value[i];
                    if(item == 'npr_org_broadcast')
                        item_details.npr_url = item_details.source_record.rsrc_value[i];
			});
		}

		item_details.shelfrank = left_pad(item_details.shelfrank);

		// Translate a total score value to a class value (after removing the old class)
		$('.shelfRank, .itemData-container, .unpack').removeClass(function (index, css) {
		    return (css.match(/color\d+/g) || []).join(' ');
		});

		$('.shelfRank, .itemData-container, .unpack').addClass('color' + get_heat(item_details.shelfrank));

		// replace google books link
		// get the google books info for our isbn and oclc (and if those are empty, use 0s)
		var isbn = '';
		if (item_details.id_isbn && item_details.id_isbn[0] && item_details.id_isbn[0].split(' ')[0]) {
			isbn = item_details.id_isbn[0].split(' ')[0];
		}

		item_details.isbn = isbn;

		var oclc = '';
		if (item_details.id_oclc) {
			oclc = item_details.id_oclc;
		}

		item_details.oclc = oclc;

		var gbsrc = 'http://books.google.com/books?jscmd=viewapi&bibkeys=OCLC:' + oclc + ',ISBN:' + isbn + '&callback=ProcessGBSBookInfo';
		$("#gbscript").attr('src', gbsrc);

		GBSArray = ['ISBN:' + isbn, 'OCLC:' + oclc];
		$.getScript($("#gbscript").attr('src'));

                /*
		if (item_details.lcsh != undefined) {
			$.each(item_details.lcsh, function(i, item) {
				item_details.lcsh[i] = item.replace(/\.\s*$/, '');
			});
		}*/

		// Redraw our tags
		drawTagNeighborhood();

		var source = $("#item-template").html();
		var template = Handlebars.compile(source);
    $('#item-panel').html(template(item_details));

    var source = $("#shelves-template").html();
		var template = Handlebars.compile(source);
    $('#shelves-panel').html(template(item_details));

    $.getJSON(www_root + '/translators/availability.php?id=' + item_details.id_inst, function(data) {
      if(data) {
        var source = $("#availability-template").html();
        var template = Handlebars.compile(source);
        $('#availability-panel').html(template(data));
      }
    });

    $("#toc").html('');
    if('505a' in item_details.source_record) {
        var sr = item_details.source_record;
        var toc = String(sr['505a']);
        toc = toc.replace(/--/g, '<br />').replace(/- -/g, '<br />').replace(/-/g, '<br />');
        if(toc) {
            $("#toc").html('<p>' + toc + '</p>')
            $(".toc-title").show();
        }
    } else {
        $(".toc-title").hide();
    }

		// If we have our first isbn, get affiliate info. if not, hide the DOM element
		if (isbn) {
			$.ajax({
				type: "GET",
				url: slurl,
				data: "isbn=" + isbn + "&function=check_amazon",
				success: function(response){
					if(response != 'false') {
					  $('#amzn').attr('href', 'http://www.amazon.com/dp/' + response);
						$('.buy').show();
					} else {
						$('.buy').hide();
					}
				}
		});
		} else {
			$('.buy').hide();
		}

		if(item_details.this_button) {
      $(".reload:contains('" + item_details.this_button + "')").parent().addClass('selected-button');
    }

	} //end draw item panel

	// When a new anchor book is selected
	$('.stack-item a').live('click', function(e){
	  var this_details = $(this).parent().data('stackviewItem');
	  var this_button = $('.selected-button').text();
		$.ajax({
  		url: www_root + '/translators/item.php',
  		dataType: 'json',
  		data: {query : this_details.id, search_type : 'id', start : '0', limit : '1'},
  		async: false,
  		success: function(data){
			  var this_details = data.docs[0];
			  data.docs[0].this_button = this_button;
			  if(History.enabled) {
			    History.pushState({data:this_details}, this_details.title, "../" + this_details.title_link_friendly + "/" + this_details.id);
			  }
			  else {
        	draw_item_panel(data.docs[0]);
        }
      }
	  });
		$('.active-item').removeClass('active-item');
		$(this).parent().addClass('active-item');
		e.preventDefault();
	});

	$('.stack-button').live('click', function() {
	  $('.selected-button').removeClass('selected-button');
	  $(this).addClass('selected-button');
		var compare = $.trim($(this).attr('id'));
		if(compare === 'recentlyviewed') {
			$('#fixedstack').stackView({url: www_root + '/translators/recently.php?' + recentlyviewed, search_type: 'recently', ribbon: 'You recently viewed these'});
		}
		else if(compare === 'callview') {
			$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'loc_call_num_sort_order', id: loc_call_num_sort_order, ribbon: 'Infinite Stack: the library arranged by call number'});
		}
		else if(compare === 'alsoviewed') {
			$('#fixedstack').stackView({url: www_root + '/translators/also.php', query: uid, search_type: 'also', ribbon: 'People who viewed this also viewed these'});
		}
		else if(compare === 'uniform') {
			$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'ut_id', query: uniform_id, ribbon: 'All editions'});
		}
	});

	$('.subject-button').live('click',function() {
		$('.selected-button').removeClass('selected-button');
	  $(this).addClass('selected-button');
		$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'lcsh', query: $(this).text(), ribbon: $(this).text()});
	});

	$('.wp_category-button').live('click',function() {
	  $('.selected-button').removeClass('selected-button');
	  $(this).addClass('selected-button');
		$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'wp_categories', query: $(this).text(), ribbon: $(this).text()});
	});

	$('.tag-button').live('click', function() {
	  $('.selected-button').removeClass('selected-button');
	  $(this).addClass('selected-button');
		$('#fixedstack').stackView({url: www_root + '/translators/tag.php', query: $('span', this).text(), search_type: 'tag', ribbon: $('span', this).text()});
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
  return scaled_value === 100 ? 10 : Math.floor(scaled_value / 10) + 1;
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

// Here we pad any values less than 10 with a 0
function left_pad(value) {
	if (value < 10) {
		return '0' + value;
	}
	return value;
}
