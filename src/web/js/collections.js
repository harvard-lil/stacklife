var $changeCollMapBg = $('#changeCollMapBg');
var $removeCollItems = $('#removeCollItems');

var collectionTags = ['adventure', 'biography', 'satirical', 'historical', 'drama', 'science', 'education'];
var bookTags = [
'children', 'biography', 'historical', 'comedy', 'drama', 'education', 'science', 'documentary',
'life', 'adventure', 'satirical', 'popular', 'fiction', 'non-fiction','animals ','internet'
		];
var rndTags = [
		['adventure','internet'],['drama','popular'],['drama', 'education'],['science ','popular'],
		[ 'adventure','popular', 'drama', 'life'],['children', 'popular','adventure'],
['life', 'biography', 'comedy']
];

var peopleTags = ['<img src="images/annie.png">Annie Jo Cain', '<img src="images/jeff.png">Jeff Goldenson', '<img src="images/kim.png">Kim Dulin', '<img src="images/matt.png">Matthew Phillips', '<img src="images/andy.png">Andy Silva<!--<a class="inviteFromPermissions" href="#"> invite</a>-->', '<img src="images/antonio.jpg">Antonio Fernandes',  '<img src="images/paul.png">Paul Deschner',  '<img src="images/david.png">David Weinberger'];

var rndAry = function(array) {
    while (true) {
        var index = Math.floor(Math.random() * 6);
        if (array[index]) {
            return array[index];
        }

    }

};



function giveDropInput(parent){
	var getMapInput = $(".mapcollectionInput");
	if($(parent).children("h3").length > 0){
		//console.log("Found an h3 in here", $(parent).children(".mapcollectionH3"));
	}
	else if($(parent).children(".mapcollectionInput").length > 0){
		//console.log("Found an input in here", $(parent).children(".mapcollectionInput"));
	}else{
	  	var mcInput = $("<input />", {"type": "text", "class": "mapcollectionInput"});
	  	mcInput.css({
	  		"position" : "absolute",
	  		/* "top" : 113, */
	  		"top" : 10,
	  		"left": 25,
	  		"display": "block",
	  		"z-index": 250
	  	});
		//console.log("Just made a new one!", mcInput);
	  	$(parent).append(mcInput);
  	}


}


var mockCovers = [
	{id: "layupCover1", src: "http://images.amazon.com/images/P/0486206912.01.ZTZZZZZZ.jpg"},
	{id: "layupCover2", src: "http://images.amazon.com/images/P/0060254920.01.ZTZZZZZZ.jpg"},
	{id: "layupCover3", src: "http://images.amazon.com/images/P/0060266686.01.ZTZZZZZZ.jpg"},
	{id: "layupCover4", src: "http://images.amazon.com/images/P/0688163165.01.ZTZZZZZZ.jpg"},
	{id: "layupCover5", src: "http://images.amazon.com/images/P/1567303668.01.ZTZZZZZZ.jpg"},
	{id: "layupCover6", src: "http://images.amazon.com/images/P/0684833395.01.ZTZZZZZZ.jpg"},
	{id: "layupCover7", src: "http://images.amazon.com/images/P/1419835890.01.ZTZZZZZZ.jpg"},
	{id: "layupCover8", src: "http://images.amazon.com/images/P/0394569024.01.ZTZZZZZZ.jpg"},
	{id: "layupCover9", src: "http://images.amazon.com/images/P/0140367187.01.ZTZZZZZZ.jpg"},
	{id: "layupCover10", src: "http://images.amazon.com/images/P/0375814248.01.ZTZZZZZZ.jpg"},
	{id: "layupCover11", src: "http://images.amazon.com/images/P/0671664581.01.ZTZZZZZZ.jpg"},
	{id: "layupCover12", src: "http://images.amazon.com/images/P/0192100335.01.ZTZZZZZZ.jpg"}
];

var buildCovers =  function(arr){
	var MockDiv = $("<div></div>");
	$.each(arr, function(index, value){
		var tmpMockDiv = $("<div></div>", {"id": value.id, "class": "coverImageDrag"});
		var parentDragW = $(parent).width() - 50,
			parentDragH = $(parent).height() - 50;
		var dragLeft = (Math.floor(Math.random()*parentDragW + 1)) / 1.6,
			dragTop = (Math.floor(Math.random()*parentDragH + 1)) / 1.6;

		var cssProps = {'left': dragLeft, 'top': dragTop};
		tmpMockDiv.css(cssProps);
		var tmpMockImg =  $("<img />", {'class': "cover-image", 'src': value.src});
		tmpMockImg.css('position', 'absolute');
		tmpMockDiv.append(tmpMockImg);
		MockDiv.append(tmpMockDiv);
	});

	return MockDiv.html();
}



function dragCover( event, ui ) {
  var offsetXPos = parseInt( ui.offset.left );
  var offsetYPos = parseInt( ui.offset.top );
  //console.log("This: getting dragged:  >| ", this);
  //console.log( "Drag stopped!\n\nOffset: (" + offsetXPos + ", " + offsetYPos + ")\n");
}

function dropCover( event, ui ) {
  var draggable = $(ui.draggable);
  var gotDroppedOn = $(this);
  draggable.children('img').width(90).height(116);

  if(gotDroppedOn.hasClass("coverImageDrag")){
  	gotDroppedOn.addClass("roundCoverCovers");

  	var close = $('<a></a>',{
			'ahref': '#',
			'text': 'x',
			'width': 20,
			'height': 20,
			'class': "closeLayUp"
		});

		gotDroppedOn.append(close);

	    $(".closeLayUp").click(function(e) {
			//console.log($(this).parent());
			covGiveCovsBack($(this).parent());
	    });


	  	giveDropInput(gotDroppedOn);


	  // >> // gotDroppedOn.children('img').width(90).height(116);

  }else{
	   draggable.width(90).height(116);
  }
  var tempDraggable = draggable.detach();

  var parentDragW = gotDroppedOn.width(),
  	  parentDragH = gotDroppedOn.height(),
  	  dragLeft = (Math.floor(Math.random()*parentDragW + 1)) / 2,
  	  dragTop = (Math.floor(Math.random()*parentDragH + 1)) / 2;

  var cssProps = {'left': dragLeft, 'top': dragTop, 'position' : 'absolute'}
  tempDraggable.css(cssProps);

  gotDroppedOn.append(tempDraggable);

  //console.log( 'The cover with ID "' + draggable.attr('id') + '" was dropped onto me!' );
}


var covGiveCovsBack = function(container){
	//console.log("container", container);
	var buildBackCovs = $(container).children(".coverImageDrag");
	var parent = $(container).parent();
	var parentDragW = parent.width(),
	parentDragH = parent.height();

	//console.log("$(container).children(\".mapcollectionH3\")", $(container).children(".mapcollectionH3"));

  	$(container).children(".closeLayUp").remove();
	$(container).children(".mapcollectionInput").remove();
	$(container).children(".mapcollectionH3").remove();


	$(container).removeClass("roundCoverCovers");
	  // >> // $(container).width(124).height(160);
	  // >> // $(container).children('img').width(124).height(160);

	$.each(buildBackCovs, function(index, value){
		var theCov = $(value);
		theCov.children('img').width(124).height(160);
		$(theCov).draggable({
		    'containment': parent,
			//'containment': '#center-container',
		    'stack': theCov,
		    'cursor': 'move',
		    'stop': dragCover
		}).droppable({
			'drop': dropCover
		});
/*

			theCov.width(124);
			theCov.height(160);
*/

		var dragLeft = (Math.floor(Math.random()*parentDragW + 1)) / 1.3,
			dragTop = (Math.floor(Math.random()*parentDragH + 1)) / 1.3;
		var cssProps = {'left': dragLeft, 'top': dragTop, 'position' : 'absolute'}
		theCov.css(cssProps);
		$(parent).append(theCov);
	});
};

var placeCovsBack = function(container){
	var buildBackCovs = $(container).parent().find(".coverImageDrag");
	var subParent = $(container).parent();
	var parent = $(container).parent().parent();
	var parentDragW = parent.width(),
	parentDragH = parent.height();
	$(subParent).remove();
	$.each(buildBackCovs, function(index, value){
		var theCov = $(value);
		  // >> // theCov.children('img').width(124).height(160);
		$(theCov).draggable({
		    'containment': parent,
		    'stack': theCov,
		    'cursor': 'move',
		    'stop': dragCover
		}).droppable({
			'drop': dropCover
		});
		if(theCov.hasClass('roundCoverCovers')){
/*
			$(theCov).hover(function(){
				$(this).animate({
					opacity: .5,
					height: '50%'
				})
			});
*/
		}else{
			/*
			theCov.width(124);
			theCov.height(160);
			*/
		}
		var dragLeft = (Math.floor(Math.random()*parentDragW + 1)) / 1.3,
			dragTop = (Math.floor(Math.random()*parentDragH + 1)) / 1.3;
		var cssProps = {'left': dragLeft, 'top': dragTop, 'position' : 'absolute'}
		theCov.css(cssProps);
		$(parent).append(theCov);
	});
};

function setupLabel() {
	if ($('.label_check input').length) {
       $('.label_check').each(function(){
           $(this).removeClass('c_on');
       });
       $('.label_check input:checked').each(function(){
           $(this).parent('label').addClass('c_on');
       });
   };
   if ($('.label_radio input').length) {
       $('.label_radio').each(function(){
           $(this).removeClass('r_on');
       });
       $('.label_radio input:checked').each(function(){
           $(this).parent('label').addClass('r_on');
       });
   };
};


$(document).ready(function() {

	$(".mapcollectionInput").live("change", function() {
		headerText = $(this).val();
		//console.log("headerText", headerText);
		var header = $('<h3></h3>', {html: headerText});
		header.css({
			"width" : 210,
			"height" : 23,
			"class" : "mapcollectionH3",
	  		"position" : "absolute",
	  		/* "top" : 101, */
	  		"top" : 0,
	  		"left": 20,
	  		"display": "block",
	  		"z-index": 250,
	  		"border-radius" : 3

		});
		$(this).replaceWith(header);
	});


    $('#aqbbut').click(function(e) {
        $('#book_history_section').slideToggle('swing', function() {
            if ($('#book_history_section').hasClass('hiding')) {
                $('#book_history_section').removeClass('hiding');
                $('#book_history_section').addClass('showing');
                $('#aqbbut').html("&uarr;Hide");

            }
            else if ($('#book_history_section').hasClass('showing')) {
                $('#book_history_section').removeClass('showing');
                $('#book_history_section').addClass('hiding');
                $('#aqbbut').html("&darr;Show");

            }

        });
        return false;

    });


    $('#showCollItemSlides').click(function(e) {
        $('#book_collection_section').slideToggle('fast', 'linear', function() {
            if ($('#book_collection_section').hasClass('hiding')) {
                $('#book_collection_section').removeClass('hiding');
                $('#book_collection_section').addClass('showing');
                $('#showCollItemSlides').html("&uarr;Hide");

            }
            else if ($('#book_collection_section').hasClass('showing')) {
                $('#book_collection_section').removeClass('showing');
                $('#book_collection_section').addClass('hiding');
                $('#showCollItemSlides').html("&darr;Show");

            }

        });
        return false;

    });



    $("#tagfield2").superblyTagField({
        'allowNewTags': true,
        'showTagsNumber': 10,
        'preset': ['life', 'drama', 'popular'],
        'tags': collectionTags

    });



    $("#allowed_users").superblyTagField({
        'allowNewTags': true,
        'showTagsNumber': 10,
        'preset': ['<img src="images/annie.png">Annie Jo Cain', '<img src="images/jeff.png">Jeff Goldenson', '<img src="images/kim.png">Kim Dulin'],
        'tags': peopleTags,
		'autoCompletion': true

    });


    $('#collection_holder').load('src/web/views/collection_view_stack.php', function() {
    	$('.label_check, .label_radio').click(function() {
			setupLabel()
		});
	    setupLabel();
        $(".grid_item_tag").each(function() {
            $(this).superblyTagField({
                'allowNewTags': true,
                'showTagsNumber': 10,
                'preset': rndAry(rndTags),
                'tags': bookTags
            });
        });
        $("#sortable").sortable();
        $("#sortable").disableSelection();
    });

/*
    $("#showvid").fancybox({
        'overlayShow': true,
        'autoDimensions': false
    });
*/


    $.each($('.displays input[type="radio"]'), function(index, value) {
        $(this).click(function() {

            if ($(this).is(':checked')) {
                if ($(this).val() === "Stack") {
                    $.ajax({
                        'url': "src/web/views/collection_view_stack.php",
                        'success': function(data) {
                            $('#collection_holder').hide('fast', 'linear', function() {
                                $(this).show('fast').html(data);
                                $(".grid_item_tag").each(function(item) {
                                    $(this).superblyTagField({
                                        'allowNewTags': true,
                                        'showTagsNumber': 10,
                                        'preset': rndAry(rndTags),
                                        'tags': bookTags
                                    });
                                    $("#sortable").sortable();
                                    $("#sortable").disableSelection();

                                });
                                if ($changeCollMapBg.is(":visible")) {
                                    $changeCollMapBg.hide();
                                    $('.displays .left').html($removeCollItems.show());

                                }

                                $('.label_check, .label_radio').click(function() {
								        setupLabel();

								    });
								    setupLabel();



                            });

                        }

                    });

                }
                if ($(this).val() === "Grid") {
                    $.ajax({
                        'url': "src/web/views/collection_view_grid.php",
                        'success': function(data) {
                            $('#collection_holder').hide('fast', 'linear', function() {
                                $(this).show('fast').html(data);
                                $(".grid_item_tag").each(function(item) {
                                    $(this).superblyTagField({
                                        'allowNewTags': true,
                                        'showTagsNumber': 10,
                                        'preset': rndAry(rndTags),
                                        'tags': bookTags

                                    });

                                    $('#sortable2 .superblyTagItem').hover(function(){
                                    	var $comparedTag = $(this).children('span').html();
                                    	var sort2div = $('#sortable2');
                                    	sort2div.each(function(index){
                                    		var grabTags = $(this).children().find('.superblyTagItems');
                                    		grabTags.each(function(indix){
                                    			var checkaTag = $(this).children('li');
                                    			checkaTag.each(function(indice){
                                    				finallyGotTag = $(this).children('span').html();
		                                    		if($comparedTag === finallyGotTag){
		                                    			var dadOfTag = $(this).parent().parent().parent().parent().parent();
		                                    			$(dadOfTag).removeClass("regDadTag").addClass("hiDadTag");
		                                    		}
                                    			});
                                    		});

                                    	});
                                    }, function() {
                                    	var sort2div = $('#sortable2');
                                    	sort2div.each(function(index){
                                    		var grabLis = $(this).children('li');
                                    		grabLis.each(function(indix){
	                                    		$(this).removeClass("hiDadTag").addClass("regDadTag");
                                    		});

                                    	});
									});


								    $('.label_check, .label_radio').click(function() {
								        setupLabel();

								    });
								    setupLabel();

                                    $("#sortable2").sortable();
                                    $("#sortable2").disableSelection();

                                });

                                if ($changeCollMapBg.is(":visible")) {
                                    $changeCollMapBg.hide();
                                    $('.displays .left').html($removeCollItems.show());

                                }

                            });

                        }

                    });

                }
                if ($(this).val() === "Map") {
                    $.ajax({
                        'url': "src/web/views/collection_view_map.php",
                        'success': function(data) {
                            $('#collection_holder').hide('fast', 'linear', function() {
								var colMapCovs = $(data).append(buildCovers(mockCovers, data));
                                $(this).show('fast').html(colMapCovs);
                                if ($removeCollItems.is(":visible")) {
                                    $removeCollItems.hide();
                                    $('.displays .left').html($changeCollMapBg.show());

                                }


                                // Using the boxer plugin

                                //.layupjs is the collections manager map page.
                                /*
                                $('.layupjs').boxer({
                                    stop: function(event, ui) {
                                        var offset = ui.box.offset();
                                        ui.box.css({
                                            border: '1px solid blue',
                                            //  background: 'rgba(255,255,255,0.2)',
                                            padding: '0.5em'

                                        })
                                        //.append('x:' + offset.left + ', y:' + offset.top).append('<br>')
                                        //.append('w:' + ui.box.width() + ', h:' + ui.box.height())
                                        ;

                                        $(".resizeLayUps").resizable().draggable({
	                                        containment: '#center-container',
										    cursor: 'move',
										    stop: dragCover
									    }).droppable({
												drop: dropCover
										});


                                        $(".closeLayUp").click(function(e) {
                                        	//console.log("this", this);
                                        	//console.log("$(this).parent()", $(this).parent());
                                        	placeCovsBack(this);
                                        });

                                    }

                                });
								*/

								   $(".coverImageDrag").draggable({
									    'containment': '#center-container',
									    'stack': '.coverImageDrag',
									    'cursor': 'move',
									    'stop': dragCover
								    }).droppable({
										'drop': dropCover
									});

									$("#changeCollMapBg a").click(function(e){
										e.preventDefault();
										//console.log(this);
										//$(this).children('input[type=file]').trigger('click');
									});

                            });

                        }

                    });

                }

            }

        });

    });
});
