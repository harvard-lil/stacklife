// The JavaScript for the landing page


$(document).ready(function() {
	if($.browser.msie) $('.logo').append('<span class="alert">This app has not been tested in Internet Explorer</span>');
	
  $('#landing-stack').stackView({url: 'cloud.php', query: 'june', ribbon: 'June'});
	
});
