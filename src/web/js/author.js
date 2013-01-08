$(document).ready(function() {
	
	$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'creator', query: author, ribbon: author});
	
	$('.stackview').css('height', $(window).height());
  
  $(window).resize(function() {
		$('.stackview').css('height', $(window).height());
	});
	
	// stackview link workaround
	$('body').on('stackview.pageload', function(e) {
    $('.stack-item a').each(function() {
      this.href = this.href.replace('shelflife/shelflife/item', 'shelflife/item');
    });
	});
       			       			
	$.getJSON('../sl_funcs.php', $.param({ 'author' : author, 'function' : 'fetch_author_neighborhood'}), 
    function(authors) {
       var jList = $("#author_neighborhood");
       $.each(authors, function(i, item) {
       		jList.append(
       			$("<li class='subject-button'><a href='../author/" + item + "'><span class='reload'>" + item + "</span></a></li>")
       		);
  		});
  		var count = 0;
  		$('#author_neighborhood li').each(function() {
			if(count > showCount)
				$(this).addClass('author-toggle-more');
			count++;
		});
		$('.author-toggle-more').hide();
		if(count > showCount)
			$('#author_neighborhood').append('<span id="author-toggle" class="toggle clickable">more</span>');
	});

  $.getJSON('../sl_funcs.php', $.param({ 'author' : author, 'function' : 'fetch_author_subjects'}), 
    function(subjects) {
      var jList = $("#subject_neighborhood");
      var subject_part;

      $.each(subjects, function(i, item) {      					
       	jList.append(
       		$('<li class="subject-button" id="' + item + '"><span class="reload">' + item + '</span></li>')
       			);
  		});
  		var count = 0;
  		$('#subject_neighborhood li').each(function() {
				if(count > showCount) {
					$(this).addClass('subject-toggle-more'); 
				}	
				count++;
			});
			$('.subject-toggle-more').hide();
			if(count > showCount)
			  $('#subject_neighborhood').append('<span id="subject-toggle" class="clickable toggle">more</span>');
	});
    
  $('.toggle').live('click', function() {
		$('.' + $(this).attr('id') + '-more').slideToggle();
       if($(this).text() == 'more') 
       	$(this).text('less');
       else if($(this).text() == 'less')
       	$(this).text('more');
    });
       			
	$('.subject-button').live('click', function() {
	  $('.selected-button').removeClass('selected-button');
	  $(this).addClass('selected-button');
		var subject = $(this).text();
		$('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'lcsh', query: subject, ribbon: subject});
	});
    			
  $('.stack-button').live('click', function() {
    $('.selected-button').removeClass('selected-button');
	  $(this).addClass('selected-button');
    var compare = $.trim($(this).attr('id'));
    if(compare == 'arecentlyviewed') {
       $('#fixedstack').stackView({url: www_root + '/translators/recently.php?' + recentlyviewed, search_type: 'recently', ribbon: 'You recently viewed these'}); 					
    }
    else if(compare == 'authortitles') {	
       $('#fixedstack').stackView({url: www_root + '/translators/cloud.php', search_type: 'creator', query: author, ribbon: author});
    }
 	 });
});
