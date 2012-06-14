(function($){

  // Tell jQuery to list each paramter in URL:
  // filter=something&filter=somethingelse...
  jQuery.ajaxSettings.traditional = true;
	
  var uid = +new Date(); // stack-view.js workaround
  var offset = 0;
  var tcount = 1;
  var  currentIdForThreads=[]; 

  $.fn.stackScroller = function( options ){
    if ( options !== false ) {
      options = $.extend({
        books_per_page: 10,
        orientation: 'V',
        axis: 'y',
        display: 'spines',
        threshold: 4000,
        heatmap: 'no',
        pagemultiple: 0.23,
        heightmultiple: 12,
        search_type: 'callno',
        query: '',
        url: '/platform/v0.03/api/item/',
        facet: new Array(),
        filter: new Array()
      }, options );
    }

    return this.each(function(){
      var scroller = $(this),
        data = scroller.data( 'scroller' ),
        vertical;

      if ( options === false ) {
        // Destroy!
        scroller.unbind( '.infiniteScroller' );
        scroller.replaceWith( data.clone );
        return;
      }
      
      if ( !data ) {
        scroller.data( 'scroller', data = {
          clone: scroller.clone(),
          options: options
        });
      }
      
      function get_page( elem, loc_sort_order, mode, offset, query, done ) {

        if(options.search_type === 'loc_sort_order' && mode === 'downstream')
        	options.query = '[' + loc_sort_order + '%20TO%20' + (parseFloat(loc_sort_order) + (options.books_per_page * 2)) + ']';
        if(options.search_type === 'loc_sort_order' && mode === 'upstream')
        	options.query = '[' + (loc_sort_order - (options.books_per_page * 2)) + '%20TO%20' + loc_sort_order + ']';
        if(options.search_type === 'loc_sort_order' && mode === 'center')
        	options.query = '[' + (parseFloat(loc_sort_order) - 5) + '%20TO%20' + (parseFloat(loc_sort_order) + (options.books_per_page * 2) - 5) + ']';

        var params = $.param({
            id: options.id,
            limit: options.books_per_page * ( mode !== 'center' ? 2 : 2 ),
            mode: mode,
            start: offset,
            filter: options.search_type + ':' + options.query,
            //facet: ['rsrc_key', 'format', 'language'],
            //filter: options.filter
          }),
          tmp;

        if ( tmp = whatevCache.get( params ) ) {
          render_page( tmp );
        } else {
          $.getJSON( options.url + '&callback=?', params, render_page );
        }
        
        // TODO: This should be moved to a centralized utils module
        function massage_field_name (field_name) {
    		var field_assoc = new Object;
    		field_assoc['language'] = 'Language';
    		field_assoc['creator'] = 'Creator';
    		field_assoc['imprint'] = 'Imprint';
    		field_assoc['format'] = 'Material Format';
    		field_assoc['desc_subject'] = 'Subject';
    		field_assoc['rsrc_key'] = 'Online Resource';
    		field_assoc['total_score'] = 'Total Score';
    		field_assoc['circ_ugrad_score:[1 TO *]'] = 'Checked out by undergraduate students';
    		field_assoc['circ_grad_score:[1 TO *]'] = 'Checked out by graduate students';
    		field_assoc['circ_fac_score:[1 TO *]'] = 'Checked out by faculty members';
    		
    		return field_assoc[field_name];
    	}
        
        function render_page( data ) {
          // TODO: We should render facets outside of stackscroller
          if (data.facets) {
            var facets = '';
            var first = true;
			$.each(data.facets, function(i, item) {
				if (!jQuery.isEmptyObject(item)){
					facets += '<div class="facet_set"><p class="facet_heading">' + massage_field_name(i) + '<span class="arrow"></span></p><ul class="facet_pairs" ';

					// We want to display our first entry, but hide the others by default
					if (first == true) {
						facets += '">';
						first = false;
					} else {
						facets += 'style="display:none;">';
					}
					var count = 1;
					$.each(item, function(facet_key, facet_value) {
						facets += '<li id="' + i + ':' + facet_key + '" class="add_filter">' + facet_key + '<span class="facet-count"> (' + facet_value + ')</span></li>';
						
					});
					facets += '</ul></div>';
				}
			});
			$('.facets').html(facets);
          } else {
        	  $('.facets').html('<div class="facet_set"><p>No facets available for this shelf</p>');  
          }
         

          var rstart = parseInt(data.start) + parseInt(data.limit);
          var roffset = data.num_found - data.start - data.limit;
          if(roffset <= 0) roffset = -1;

          if ( ((!data.docs || !data.docs.length) && data.start != 0) || parseInt(data.limit) === 0 ) {
          	$('.scroller-page ul:last').after('<div class="book-end"></div>');
            done( false );
            return;
          }
          
          data.start = rstart;
          
          whatevCache.set( params, data, 60 ); // cache set to 60 seconds, increase for production use!
                    
          var books = $.extend( [], data.docs );
          var booksend = books.length;

          if(options.display === 'spines') {
          	var $stack = $('<ul class="stack"></ul>');
          }
          else if(options.display === 'covers') {
          	var $stack = $('<ul class="faceUpStack"></ul>');
          }
          
          //$stack.append($('<span>' + books[0].topic + '</span>').css('width', 300));
         
          function soc_heat(threadcount) {
                if (threadcount == 0) {
                        return "msgimg";
                }
                if (threadcount == 1) {
                        return "msgimg1";
                }
                if (threadcount == 2) {
                        return "msgimg2";
                }
                if (threadcount == 3) {
                        return "msgimg3";
                }
                if (threadcount == 4) {
                        return "msgimg4";
                }
                if (threadcount == 5) {
                        return "msgimg5";
                }
                if (threadcount == 6) {
                        return "msgimg6";
                }
                if (threadcount == 7) {
                        return "msgimg7";
                }
                if (threadcount == 8) {
                        return "msgimg8";
                }
                if (threadcount == 9) {
                        return "msgimg9";
                }
                if (threadcount == 10) {
                        return "msgimg10";
                }
                if (threadcount > 10) {
                        return "msgimg";
                }
          } 
          
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
          
          
          if (options.search_type !== 'loc_sort_order') {
        	$('.subject-hits').html(data.num_found + '<br />items').removeClass('empty');
          } else {
          	$('.subject-hits').html('').addClass('empty');
          }
          
          // This probably shouldn't be necessary!
          $.each( books, function(i,v){
            
            if ( !v.title ) {
              v.title = 'NULL';
            }
			
			var measurement_page =  '';
			if(v.measurement_page_numeric)
				measurement_page = v.measurement_page_numeric; 
			if (measurement_page == "" || measurement_page < 200 || !measurement_page) measurement_page = 200;
			if (measurement_page > 540) measurement_page = 540;
			
			var height = '';
			if(v.measurement_height_numeric)
				height = v.measurement_height_numeric;
			if (height == "" || height < 20 || !height) height = 20;
			if (height > 39) height = 39;
        	
        	var isbn = -1;
			if (v.id_isbn && v.id_isbn[0] && v.id_isbn[0].split(' ')[0]) {
				isbn = v.id_isbn[0].split(' ')[0];
			}
			
			var pub_date = '';
			if(v.pub_date) pub_date = v.pub_date;
        	
        	var home = '';
			var format = '';
			if(v.format){
				format = v.format.toLowerCase();
				format = format.replace(" ", "").replace("/", "");
			}
			
			// display online video as videofilm and online audio as soundrecording
			if(format === 'online_video' && options.display === 'spines') format = 'videofilm';
			if(format === 'online_audio' && options.display === 'spines') format = 'soundrecording';

			if(parseInt(v.loc_sort_order) === parseInt(options.loc_sort_order) && mode === 'center')
				home = ' anchorbook';
			
			var creator = '',
			creator_full = '';
			if (v.creator && v.creator.length > 0) {
				creator = v.creator[0];
				creator_full = v.creator[0];
				if (/^([^,]*)/.test(creator)) {
					creator = creator.match(/^[^,]*/);
				}
			}
			
			if (v.desc_subject === undefined) {
				v.desc_subject = [];
			}
			
			var collection_html = '<span class="collectioncontainer' + ' ' + format +'"><input type="checkbox" name="collectionadded[]" class="collectionadd" value="' + v.id + '" style="display:none;" /></span>';
			
			//Temporarily remove 'add to collection' functionality
			collection_html = '';
			
			if(options.display === 'spines') {
			
			if(format === "videofilm" || format === "serial" || format === "soundrecording") {
            	$stack.append($('<div class="itemContainer' + home + '" onmouseover="document.getElementById(\'img'+ v.id +'\').style.display=\'inline\';" onmouseout="document.getElementById(\'img'+ v.id +'\').style.display=\'none\';"></div>').html($(collection_html)).append($('<span class="cover-' + format + ' heat' + get_heat(v[perspective]) + '"></span><span class="edge-' + format + ' heat' + get_heat(v[perspective]) + '"></span>'))
            	.append($('<li imprint="' + v.publisher + '" class="heat' + get_heat(v[perspective]) + ' spine' + home + ' ' + format +'"></li>').html('<p class="spine-text"><span class="title">' + v.title + '</span></p><span class="spine-year">' + pub_date + '</span>').data('item_details', v)));
			} else if(format === "webpage") {
				$stack.append($('<div class="itemContainer' + home + '" onmouseover="document.getElementById(\'img'+ v.id +'\').style.display=\'inline\';" onmouseout="document.getElementById(\'img'+ v.id +'\').style.display=\'none\';"></div>').html($(collection_html)).append($('<li class="browserStack heat' + get_heat(v[perspective]) + '"></li>').html('<span class="browser-circ-btn heat' + get_heat(v[perspective]) + '"></span><span class="browser-circ-btn heat' + get_heat(v[perspective]) + '"></span><div class="heat' + get_heat(v[perspective]) + ' addressbar"><span class="url-source">' + v.publisher + '</span>' + v.title + '</div><span class="browser-btn heat' + get_heat(v[perspective]) + '"></span>').data('item_details', v)));
			}else {
				$stack.append($('<div class="itemContainer' + home + '" onmouseover="document.getElementById(\'img'+ v.id +'\').style.display=\'inline\';" onmouseout="document.getElementById(\'img'+ v.id +'\').style.display=\'none\';"></div>').html($(collection_html).css('height', measurement_page * options.pagemultiple)).append($('<span class="cover heat' + get_heat(v[perspective]) + '"></span>').css('width', height * options.heightmultiple + 2)).append($('<span class="pages heat' + get_heat(v[perspective]) + '"></span>').css('margin-left', height * options.heightmultiple + 35).css('margin-bottom', -measurement_page * options.pagemultiple - 11).css('height', measurement_page * options.pagemultiple + 5)).append($('<li imprint="' + v.publisher + '" class="heat' + get_heat(v[perspective]) + ' spine ' + format +'"></li>').html('<p class="spine-text"><span class="title">' + v.title + '</span><span class="author">' + creator + '</span></p><span class="spine-year">' + pub_date + '</span>').data('item_details', v).css('width', height * options.heightmultiple).css('height', measurement_page * options.pagemultiple)));
			}
			
			}
			else if(options.display === 'covers'){
				$stack.append($('<div class="itemFaceUpContainer ' + home + '"></div>').html($(collectionhtml + '<span class="faceUp-pages"></span>')).append($('<li class="faceUp"><img class="faceUp-' + format + '-image" src="http://covers.openlibrary.org/b/isbn/' + isbn + '-M.jpg" alt="' + v.title + '" /><span class="faceUp-details"><p class="faceUp-title">' + v.title + '</p><p class="faceUp-author">' + creator_full + '</p></li>').data('item_details', v)).append('<span class="faceUp-edges"></span>'));
			}
			//<img class="faceUp-' + format + '-image" src="http://images.amazon.com/images/P/' + isbn + '.01.ZTZZZZZZ.jpg" alt="' + v.title + '" />
			
			var link = '';
        	if (/\/book\//.test(window.location)) {
        		link = '../../';
        	}
        	if (/\/author\//.test(window.location)) {
        		link = '../';
        	}
			// The content in this next if statement is for the message threads. 
			// The random number test should be replace with whether or not there are comment threads
			// Will clean this up, but for now, it works.
				var iconhoriz=height * options.heightmultiple + 60;
				if (iconhoriz < 340) { iconhoriz=340; }
				var iconvert=measurement_page * options.pagemultiple + 5;
				$stack.append($('<div onmouseover="document.getElementById(\'img'+ v.id +'\').style.display=\'inline\';" onmouseout="document.getElementById(\'img'+ v.id +'\').style.display=\'none\';" class="msgbutton"><img id="img' + v.id + '" class="msgimg" src="' + link + 'images/disthreads_trns.png" style="position: relative; top:-'+ iconvert +'px; left:'+ iconhoriz + 'px;"></div>'));
				$stack.append($('<div class="showhide" id="' + v.id + '" style="display: none;"><div class="inject-msgthread" fid="uid:' + v.id + '"></div></div>'));
			$stack.append($('<div style="clear:both;"></div></div>'));
			currentIdForThreads.push(v.id);
			//ss.misc.bindMsgThreadChange("uid:" + v.id, function(n) { $("#img" + v.id).removeClass('msgimg').addClass('' + soc_heat(n)); } );
            if(i % 50 == 0 && options.query != null && options.query != '' && options.search_type === 'loc_sort_order')
            	$stack.append($('<div id="lc-range"><div class="mapPoint"></div>' + v.desc_lc_call_num_subject + '</div>'));

          });

          initSociability(function() {
                //ss.ui.showMessageThreads();
                  for(var i in currentIdForThreads)
                  {
                        var fakesoc=Math.round(Math.random()*30);
                        $("#img" + currentIdForThreads[i]).removeClass("msgimg1").removeClass("msgimg2").removeClass("msgimg3").removeClass("msgimg4").removeClass("msgimg5").removeClass("msgimg6").removeClass("msgimg7").removeClass("msgimg8").removeClass("msgimg9").removeClass("msgimg10").removeClass("msgimg").addClass("" + soc_heat(fakesoc));
                  }



                }, function() { console.log("Problem initializing Sociability.") }); 

          elem
            .empty()
            .attr( 'id', 'stackview' + uid++ ) // stack-view.js workaround
            .data( 'scroller', data )
            .append($stack);

          	done( elem );

          	
        }
      };
	if (options.search_type == 'loc_sort_order')  {
      get_page( scroller.find('.scroller-page'), options.loc_sort_order, 'center', 0, '',  function(){

        scroller.infiniteScroller({
        	search_type: options.search_type,
          axis: options.axis,
          threshold: options.threshold,
          //scroll_duration: 2000,
          fetch: function( dir, done ){
            var data = this.data( 'scroller' ),
              mode = dir === 1 ? 'downstream' : 'upstream',
              loc_sort_order = data.docs[ dir === 1 ? data.docs.length - 1 : 0 ].loc_sort_order[0] + 1;

            get_page(
              $('<div/>').appendTo('body'), // appendTo is a stack-view.js workaround
              loc_sort_order,
              mode,
              offset,
              options.query,
              done
            );
          },
          pagechange: function( prev ){ 
            var books = this.data( 'scroller' ).docs;
            data.loc_sort_order = books[ parseInt( books.length / 2 ) ].loc_sort_order[0];
          }
        });

      });
      }
      else {
      get_page( scroller.find('.scroller-page'), options.id, 'downstream', 0, options.query,  function(){

        scroller.infiniteScroller({ 
          axis: options.axis,
          threshold: options.threshold,
          //scroll_duration: 2000,
          fetch: function( dir, done ){ 
            var data = this.data( 'scroller' ),
              mode = 'downstream',
              id = options.id,
              query = options.query,
              offset = data.start;

            get_page(
              $('<div/>').appendTo('body'), // appendTo is a stack-view.js workaround
              id,
              mode,
              offset,
              query,
              done
            );
          },
          search_type:options.search_type
        });

      });
      }

    });
  };
})(jQuery);
