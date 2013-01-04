// The JavaScript for the landing page


$(document).ready(function() {
	if($.browser.msie) $('.logo').append('<span class="alert">This app has not been tested in Internet Explorer</span>');
	
	var stackheight = $(window).height();
	
	$(window).resize(function() {
		stackheight = $(window).height();
		$('.stackview').css('height', stackheight);
	});
  
  /*$('#landing-stack').stackView({url: 'translators/cloud.php', query: 'star wars', ribbon: 'Star Wars'});*/
  
  $('#landing-stack').stackView({
      url: 'js/awesome.json', 
      ribbon: 'Recent Awesome Returns'
  });
	
	$('.stackview').css('height', stackheight);
	
});
