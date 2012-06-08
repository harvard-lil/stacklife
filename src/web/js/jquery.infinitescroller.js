/*!
 * jQuery infiniteScroller - v0.1pre - 9/24/2010
 * http://benalman.com/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */

(function($){
  '$:nomunge'; // Used by YUI compressor.
  
  var plugin_name = 'infiniteScroller',
    plugin_namespace;
  
  $.fn[ plugin_name ] = plugin_namespace = function( options ) {
    // Override defaults with passed options.
    options = $.extend( {}, plugin_namespace.options, options );
    
    // Determine proper method names based on the specified axis option.
    var axis_prop = {
      Width: 'Height',
      Left: 'Top'
    };
    
    $.each( axis_prop, function(k,v){
      if ( options.axis === 'x' ) {
        axis_prop[ k ] = v = k;
      }
      axis_prop[ k.toLowerCase() ] = v.toLowerCase();
    });
    
    return this.each(function(){
      var scroller = $(this),
        scroll_elem = scroller[0] === document.body ? $(window) : scroller,
        content = scroller.children('.scroller-content'),
        
        pages,
        pages_data,
        
        // Handle prev/next "loading" elements.
        loading_prev = content.children('.scroller-loading:first-child'),
        loading_next = content.children('.scroller-loading:last-child'),
        loading_prev_size = get_size( loading_prev ) || 0,
        loading_next_size = get_size( loading_next ) || 0,
        
        // Positions and offsets.
        zero_pos = 0,
        scroll_offset = 0,
        min_pos,
        max_pos,
        new_pos,
        
        current_page_idx;
      
      // Initialize pages and positioning.
      update( false, true );
      init_page( pages.eq( 0 ) );
      
      // Move to the default "zero" position.
      //move_to( 0, true );
      //set_zero();
      
      // Calculate current page and attempt to fetch new content (if necessary)
      // on scroll or resize.
      function on_scroll_resize(){
        var prev_page = current_page_idx;
        
        // Calculate current page.
        current_page_idx = get_current_page_idx();
        
        if ( options.pagechange && prev_page !== current_page_idx ) {
          options.pagechange.call( pages.eq( current_page_idx ), pages.eq( prev_page ) );
        }
        
        if ( options.scroll ) {
          options.scroll.call( scroller, get_pos( scroller ) - zero_pos );
        }
        
        //window.console && console.log( 'current_page_idx', current_page_idx, pages[ current_page_idx ].id );
        
        // If more content can and should be fetched, do so now. 
        if (options.search_type == 'loc_sort_order') more( -1 );
        more( 1 ); 
      };
      
      on_scroll_resize();
      
      // Position initial content.
      reflow();
      
      // Set this as the initial "zero" position.
      set_zero();
      
      scroll_elem.bind( 'scroll.' + plugin_name + ' resize.' + plugin_name, on_scroll_resize );
      
      // Reflow content (should be called on resize) TODO: update zero point?
      scroller.bind( 'reflow.' + plugin_name, reflow );
      
      // Re-zero scroller at its current position.
      scroller.bind( 'set-zero.' + plugin_name, set_zero );
      
      // Move the scroller to a specific position.
      scroller.bind( 'move-to.' + plugin_name, function( e, new_pos ){
        move_to( new_pos );
      });
      
      // Move the scroller by a specific amount.
      scroller.bind( 'move-by.' + plugin_name, function( e, params ){
        var defaults = {
          dir: 1,
          amount: get_size_opt( 'scroll_by' )
        };
        
        params = $.extend( defaults, typeof params === 'number' ? { amount: params } : params );
        
        if ( params.amount === 'page' ) {
          move_by_page( params.dir );
        } else {
          move_by( params.dir * params.amount );
        }
      });
      
      // Re-compute content element positions and show the current page.
      function reflow() {
        update( true );
        move_to_page( current_page_idx, true );
      };
      
      // Called whenever content element (pages, loading elements) positions
      // need to be re-computed.
      function update( reflow, init ) {
        // Re-select pages.
        pages = content.children('.scroller-page');
        pages_data = [];
        
        // Determine mix/max scroller position.
        min_pos = max_pos = get_pos( pages.eq(0) );
        
        pages.each(function(i){
          var page = $(this);
          
          reflow && set_pos( page, max_pos );
          
          pages_data[ i ] = {
            start: max_pos,
            end: max_pos + get_size( page, true )
          };
          
          max_pos += get_size( page );
        });
        
        // Move prev/next "loading" elements as necessary.
        loading_prev_size && set_pos( loading_prev, min_pos - loading_prev_size );
        loading_next_size && set_pos( loading_next, max_pos );
        
        // Reposition content as-needed.
        var diff = get_max_scroll();
        if ( diff < 0 ) {
          // Content smaller than viewport.
          set_pos( content,
            loading_prev_size && loading_next_size ? ( get_size( scroller ) - max_pos ) / 2
            : loading_prev_size ? get_size( scroller ) - max_pos
            : loading_next_size ? min_pos
            : -diff / 2
          );
          
        } else {
          // Content larger than viewport.
          set_pos( content, loading_prev_size - min_pos );
          
          if ( init ) {
            // Only re-zero the scroller when initially creating the scroller.
            //set_zero( ( loading_prev_size - loading_next_size + diff ) / 2 ); // TODO: REMOVE??
          }
        }
      };
      
      // Initialize a "page" of content.
      function init_page( page ) {
        page
          .addClass( 'scroller-page' )
          .data( plugin_name, {} );
      };
      
      /*
      == SHOW PAGE ==
      * centered (if page is smaller than content area)
      OTHERWISE
      * start of page aligned with start of content area (+ offset)
      */
      function move_by_page( dir, no_animate ) {
        var page_idx = current_page_idx + dir;
        page_idx = Math.min( Math.max( page_idx, 0 ), pages.length );
        move_to_page( page_idx, no_animate );
      };
      
      function move_to_page( page_idx, no_animate ) {
        move_to( pages.eq( page_idx ), no_animate );
      };
      
      // Move the scroller to a new position using a relative value.
      function move_by( delta, no_animate, no_offset_reset ) {
        move_to( delta, no_animate, no_offset_reset, true );
      };
      
      // Move the scroller to a new position using an absolute value.
      // (the 'no_offset_reset' argument for internal use only)
      function move_to( pos, no_animate, no_offset_reset, is_move_by ) {
        var duration = options.scroll_duration,
          scroller_pos = get_pos( scroller ),
          elem,
          
          page,
          start,
          end,
          offset,
          
          viewport_dims,
          viewport_size;
        
        // TODO: EXPLAIN
        if ( pos && typeof pos !== 'number' ) {
          if ( ( elem = $(pos) ).length ) {
            offset = loading_prev_size - min_pos - zero_pos - get_size_opt( 'offset' )[ 0 ],
            viewport_dims = get_viewport_dims(),
            viewport_size = viewport_dims.start - viewport_dims.end;
            
            page = pages_data[ pages.index( elem ) ];
            start = page.start;
            end = page.end;
            
            pos = ( start - end < viewport_size ? start : ( start + end + viewport_size ) / 2 ) + offset;
              
          } else {
            return;
          }
        }
        
        // Ensure the new position is within the bounds of the scroller.
        new_pos = limit_pos( is_move_by ? scroller_pos + pos : pos + zero_pos );
        
        if ( !no_offset_reset ) {
          // Stop the scroller (and) don't jump to the end position because a
          // new scrolling action is about to occur). Since scrolling is now
          // stopped, the scroll_offset (which is only used when animating the
          // scrolling) must be reset.
          scroller.stop( true, false );
          scroll_offset = 0;
        }
        
        if ( !duration || no_animate ) {
          // If duration is not set or 0, or no_animate is specified, jump to
          // the new position immediately.
          set_scroller_pos( new_pos );
          
        } else {
          // Animate to the new position. Because new pages can be inserted at
          // the beginning of the scrollable area, a scroll_offset representing
          // the size of the inserted pages must be maintained, and for each
          // animation tick, the scroll position must be offset by that amount.
          // the .animate method can be used with a completely arbitrary custom
          // css property to do this.
          scroller
              .scrollTop( scroller_pos )
              .animate({ scrollTop: new_pos }, {
                  step: function( now, fx ){
                    set_scroller_pos( now - scroll_offset );
                  },
                  duration: duration
              });
        }
        
        // Return a position within the bounds of the scroller.
        function limit_pos( pos ) {
          var max = get_max_scroll();
          return Math.min( Math.max( pos, Math.min( 0, max ) ), Math.max( 0, max ) );
        };
        
        // Actually set the scroller's position.
        function set_scroller_pos( pos ) {
          scroll_elem[ 'scroll' + axis_prop[ 'Left' ] ]( limit_pos( pos ) );
        };
        
      };
      
      // Check to see, based on the threshold and existence of the next/prev
      // loading element, if it's appropriate to fetch more content at that
      // end of the current scroller.
      function more( dir ) { 
        var is_prev = dir !== 1,
          page = pages.eq( is_prev ? 0 : pages.length - 1 ),
          data = page.data( plugin_name ),
          fetch = options.fetch,
          prop = is_prev ? 'prev' : 'next',
          loading_elem_size = is_prev ? loading_prev_size : loading_next_size,
          pos = get_pos( scroller ),
          dist_from_end = ( is_prev ? pos : get_max_scroll() - pos ) - loading_elem_size;
        
        // If there is a loading element, new content hasn't been requested,
        // options.fetch is defined, and the threshold has been passed, fetch
        // more content.
        if ( loading_elem_size && !data[ prop ] && $.isFunction( fetch )
           && dist_from_end <= get_size_opt( 'threshold' ) ) {
          
          // Set a flag saying content has been requested.
          data[ prop ] = true;
          
          // Call options.fetch in the context of the current page, passing a
          // direction of -1 or 1 and a `done` callback function. Execute
          // asynchronously to keep large numbers of subsequent `more` calls
          // from locking the browser.
          setTimeout(function(){
            fetch.call( page, dir, function( new_page ) {
              if ( new_page === false ) {
                // The `done` callback was passed false, signifying that there is
                // no more content available, and that the loading indicator
                // should be hidden.
                
                if ( is_prev ) {
                  // Adjust scroll offset, in case the scroller is animating.
                  scroll_offset += loading_prev_size;
                  
                  // Move zero_pos/scroller to compensate for hiding
                  // loading_prev element.
                  set_zero( zero_pos - loading_prev_size );
                  move_by( -loading_prev_size, true, true );
                  
                  // Hide loading_prev element.
                  loading_prev.hide();
                  loading_prev_size = 0;
                  
                } else {
                  // Hide loading_next element.
                  loading_next.hide();
                  loading_next_size = 0;
                }
                
                // Recompute pages, sizes, etc.
                update();
                
              } else if ( new_page ) {
                // The `done` callback was passed a new element that will now be
                // added as a new page.
                
                var new_page_size;
                
                // Normalize new_page into a jQuery collection, in case a DOM
                // element or selector was passed.
                new_page = $( new_page );
                
                // Initialize new_page.
                init_page( new_page );
                
                if ( is_prev ) {
                  // Insert new_page between loading element and first page in
                  // the DOM.
                  loading_prev.after( new_page );
                  
                  // Position new_page before first existing page.
                  new_page_size = get_size( new_page );
                  set_pos( new_page, get_pos( page ) - new_page_size );
                  
                  // Recompute pages, sizes, and move the loading element to
                  // just before new_page.
                  update();
                  
                  // Move zero_pos/scroller to compensate for adding the new
                  // page.
                  set_zero( zero_pos + new_page_size );
                  move_by( new_page_size, true, true );
                  
                  // Adjust scroll offset, in case the scroller is animating.
                  scroll_offset -= new_page_size;
                  
                } else {
                  // Insert new_page between loading element and last page in
                  // the DOM.
                  loading_next.before( new_page );
                  
                  // Position new_page after last existing page.
                  set_pos( new_page, get_pos( page ) + get_size( page ) );
                  
                  // Recompute pages, sizes, and move the loading element to
                  // just after new_page.
                  update();
                }
             
                // Call this function again, in case the just-added new_page is
                // still not enough to exceed the fetch threshold.
                more( dir );
                
              } else {
                // The `done` callback wasn't passed false or a truthy value, so
                // delete the "content has been requested" flag to allow another
                // fetch attempt.
                delete data[ prop ];
              }
            });
          },1);
        }
      };
      
      // Get position.
      function get_pos( elem ) {
        return elem[0] === scroller[0]
          ? scroll_elem[ 'scroll' + axis_prop[ 'Left' ] ]()
          : elem.position()[ axis_prop[ 'left' ] ];
      };
      
      // Set position.
      function set_pos( elem, pos ) {
        var props = {};
        
        if ( pos !== get_pos( elem ) ) {
          props[ axis_prop[ 'left' ] ] = pos;
          elem.css( props );
        }
      };
      
      // Get size.
      function get_size( elem, no_margins ) {
        return elem[0] === content[0]
          ? loading_prev_size + loading_next_size + max_pos - min_pos
          : elem[ ( elem[0] === scroller[0] ? 'inner' : 'outer' )
            + axis_prop[ 'Width' ] ]( !no_margins );
      };
      
      // Set size.
      function set_size( elem, size ) {
        elem[ axis_prop[ 'width' ] ]( size );
      };
      
      function get_size_opt( prop ) {
        var val = options[ prop ];
        return $.isFunction( val )
          ? val.call( scroller, get_size( scroller ) )
          : val;
      };
      
      function get_max_scroll() {
        return get_size( content ) - get_size( scroller );
      };
      
      function set_zero( pos ) {
        zero_pos = typeof pos === 'number' ? pos : get_pos( scroller );
      };
      
      function get_viewport_dims() {
        var pos = get_pos( scroller ) + min_pos - loading_prev_size,
          offset = get_size_opt( 'offset' );
        
        return {
          start: pos + offset[ 0 ],
          end: pos + get_size( scroller ) - offset[ 1 ]
        };
      };
      
      /*
      == CURRENT PAGE ==
      * the center-most, fully visible page
      OTHERWISE
      * the most-visible partially-visible page
      */
      function get_current_page_idx() {
        var viewport_dims = get_viewport_dims(),
          viewport_start = viewport_dims.start,
          viewport_end = viewport_dims.end,
          is_full,
          full = [],
          partial = [],
          arr = [],
          current;
        
        $.each( pages_data, function( i, page ){
          var start = page.start,
            end = page.end;
          
          if ( start >= viewport_end ) {
            return false;
          }
          
          if ( start >= viewport_start ) {
            if ( end <= viewport_end ) {
              is_full = full.push( i );
            } else {
              partial.push( i );
            }
          } else if ( end > viewport_start ) {
            partial.push( i );
          }
        });
        
        arr = $.map( is_full ? full : partial, function(i){
          var page = pages_data[ i ],
            start = page.start,
            end = page.end;
          
          return {
            i: i,
            d: is_full ? Math.abs( ( viewport_start + viewport_end - start - end ) / 2 )
              : start < viewport_start ? viewport_start - end : start - viewport_end
          };
        }).sort(function(a,b){
          return a.d - b.d;
        });
        
        return arr.length ? arr[0].i : 0;
      };
      
    });
  };
  
  plugin_namespace.options = {
    axis: 'x',
    threshold: function( scroller_size ) {
      // this === scroller
      return scroller_size;
    },
    scroll_by: function( scroller_size ) {
      // this === scroller
      return scroller_size;
    },
    position: function( scroller_size, page_size ) {
      // this === page
      return ( scroller_size + page_size ) / 2; // TODO: MAKE THIS WORK
    },
    pagechange: function( prev ) {
      // this === page
      // console.log( this[0], prev[0] );
    },
    scroll: function( position ) {
      // this === scroller
      // console.log( position );
    },
    scroll_duration: 400,
    offset: [ 0, 0 ],
    fetch: null
  };
  
})(jQuery);
