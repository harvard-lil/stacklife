<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php

ini_set("display_errors", 1); 
error_reporting(E_ALL ^ E_NOTICE);

require_once('../../sl_ini.php');

session_start(); 

global $hostName;
global $userName;
global $pw;
		
if(!($link=mysql_pconnect($hostName, $userName, $pw)))
{
	echo "before error<br />";
	echo "error connecting to host";
	exit;
}

$uid = $_GET['uid'];

// Our passed in perspective
$perspective = 'university_scaled';
if (!empty($_GET['perspective'])) {
  $perspective = $_GET['perspective'];
}

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
echo <<<EOF
  <link rel="icon" href="$www_root/favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="$www_root/css/template.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/shelflife.theme.css" type="text/css" /> 
  <link rel="stylesheet" href="$www_root/css/rateit.css" type="text/css" /> 
  <link rel="stylesheet" href="$www_root/css/reviews.css" type="text/css" />
  <!--[if IE]>
        <link rel="stylesheet" href="$www_root/css/ie.css" type="text/css" />
  <![endif]-->
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
  <script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.rateit.min.js"></script>

  <title> Review | ShelfLife</title>
EOF;
?>

<script type="text/javascript">
				
var author = '<?php echo $author;?>';
var uid = '<?php echo $uid;?>';
var perspective = '<?php echo $perspective ?>';
var isbn = '';
var oclc = '';
var submiturl = '';
var wcurl = '';
        
var mainformat = '';
        
$(document).ready(function() {

$.getJSON('/librarycloud/v.3/api/item/?key=BUILD-LC-KEY', $.param({ 'query' : '<?php echo $uid ?>', 'search_type' : 'id', 'start' : '0', 'limit' : '1' }),
		function (data) {
			$('h1:first').html('Write a review of <b>' + data.docs[0].title + '</b>');
			var isfiction = false;
			if(data.docs[0].desc_subject_lcsh){
				$.each(data.docs[0].desc_subject_lcsh, function(i, v) {
					var desc_subject_lcsh = v.toLowerCase();
					if(desc_subject_lcsh.indexOf("fiction") != -1) isfiction = true;
				});
			}
			format = data.docs[0].format.toLowerCase();
			format = format.replace(" ", "").replace("/", "");
			if(format === 'book' && isfiction) format = 'fiction' + format;
			$('#' + format + 'eval').show();
			$('#works .heading').text('Drag into your review');
			if(data.docs[0].creator.length > 0) {
				var creatorlist = '';
				$.each(data.docs[0].creator, function(i, v){
					if(v.substring(v.length - 1) === ',' || v.substring(v.length - 1) === '.')
						v = v.substring(0, v.length - 1);
					if(v.indexOf(",") != -1) {
						var varray = v.split(",");
						v = varray[1] + ' ' + varray[0];
					}
					creatorlist += '<li>' + v + '</li>';
				});
				$('#works').append('<p>Creators</p><ul>' + creatorlist + '</ul>');
			}
			if(data.docs[0].id_oclc){
				oclc = data.docs[0].id_oclc;
				wcurl = "sl_funcs.php?oclcnum=" + oclc + "&type=review&function=fetch_worldcat_data";
			}
			if(data.docs[0].id_isbn){
				isbn = data.docs[0].id_isbn[0]; 
				isbn = isbn.split(" ",1);
				wcurl = "sl_funcs.php?oclcnum=isbn/" + isbn + "&type=review&function=fetch_worldcat_data";
			}
			submiturl = 'book/' + data.docs[0].title_link_friendly + '/' + data.docs[0].id_hollis + '#reviewfull';
			getMeta(isbn);
		});	
	
$('.rateit').rateit();
//rating is $('.rateit').rateit('value')

$("#reviewform").validate({
    	submitHandler: function(form) {
			var rating = $('.rateit').rateit('value');
			if(rating === 0) {
				$('.rateit').after('<span class="error">Please rate this item</span>');
				return false;
			}
			var reviewdata = $('#reviewform').serialize();
			$.ajax({
				type: "POST",
				url: "sl_funcs.php",
				data: "rating="+ rating + "&uid=" + uid + "&function=set_review&" + reviewdata,
				success: function(){
					//$('.container-content').html('<h1>Thanks</h1>');
					//console.log(submiturl);
					//window.location = submiturl;
					parent.drawReviews();
					parent.$.fancybox.close();
				}
			});
			return false;
		}
	});

$( ".droppable" ).droppable({
	drop: function( event, ui ) {
		var insert = $.trim($(ui.draggable).text());
		var existingvalue = $(this).val();
		$(this).val(existingvalue + insert);	
	}
});
function getMeta(isbn) {

$.ajax({
    type: "POST",
    url: "sl_funcs.php?function=fetch_librarything_id&isbn=" + isbn,
    dataType: "xml",
    success: parseXml
});

$.ajax({
	url: wcurl,
	method: 'GET',
	success: parseXml
});

function parseXml(xml)
{
  
  $(xml).find("field").each(function()
  {
    if($(this).attr('type') === '3'){
    	var list = '';
    	$(this).find("fact").each(function() {	
    		var charname = $(this).text();
    		list += '<li>' + charname + '</li>';
    	});
    	$('#works').append('<p>Characters</p><ul>' + list + '</ul>');
    }
    else if($(this).attr('type') === '2'){
    	var list = '';
    	$(this).find("fact").each(function() {	
    		var placename = $(this).text();
    		placename = placename.replace('(Fictional)', ' ');
    		list += '<li>' + placename + '</li>';
    	});
    	$('#works').append('<p>Locations</p><ul>' + list + '</ul>');
    }
    else if($(this).attr('type') === '34'){
    	var list = '';
    	$(this).find("fact").each(function() {	
    		var eventname = $(this).text();
    		list += '<li>' + eventname + '</li>';
    	});
    	$('#works').append('<p>Events</p><ul>' + list + '</ul>');
    }
    else if($(this).attr('type') === '23'){
    	var list = '';
    	$(this).find("fact").each(function() {	
    		var seriesname = $(this).text();
    		list += '<li>' + seriesname + '</li>';
    	});
    	$('#works').append('<p>Series</p><ul>' + list + '</ul>');
    }
  });
  $(xml).find("datafield[tag='505']").each(function() {					
		var toc = $(this).text();
		toc = toc.replace(/--/g, '<li>');
		toc = toc.replace(/- -/g, '<li>');
		toc = toc.replace(/-/g, '<li>');
		$('#works').append('<p>Table of Contents</p><ul><li>' + toc + '</ul>');
 });
 var addedauthor = '';
 $(xml).find("datafield[tag='700'] subfield[code='a']").each(function() {
 	var aauthor = $(this).text();
	if(aauthor.substring(aauthor.length - 1) === ',' || aauthor.substring(aauthor.length - 1) === '.') aauthor = aauthor.substring(0, aauthor.length - 1);
	var authorarray = aauthor.split(",");
	aauthor = authorarray[1] + ' ' + authorarray[0];
	addedauthor += '<li>' + $.trim(aauthor) + '</li>';
 });
 if(addedauthor != '')
 	$('#works').append('<p>Additional Creators</p><ul>' + addedauthor + '</ul>');
 	
  $( "#works ul li" ).draggable({ revert : "invalid",
    helper: "clone",
    opacity: 0.7,
    zIndex: 999,
    appendTo: "body",
});
}	
}
    	 
}); //End document ready
</script>	

</head>
    <body>
    <div id="wrapper">
      <?php //require_once('header.php');?>
      <div class="container group"> 
      		<div class="container-content">               
	       		<h1></h1> 
	       		<div id="content">
	       		<form id="reviewform" method="post">
	       		<legend>Rating <div class="rateit"></div></legend>
	       		<div style="clear:both"></div>
	       		<fieldset>
	       		<legend>Review headline</legend>
	       		<input type="text" class="droppable" id="headline" name="headline" />
	       		</fieldset>
	       		<fieldset>
	       		<legend>Comments</legend>
				<textarea id="review" class="droppable required" name="review" rows="5" cols="53"></textarea>
				</fieldset>
				<div id="fictionbookeval" style="display:none;">
				<fieldset>
				<legend>Writing quality</legend>
				<p><input id="tag1" name="tag1" type="radio" value="Writing=Poor writing" /><br /> 
				<label for="tag1">Poor writing</label></p>
				<p><input id="tag1" name="tag1" type="radio" value="Writing=Average writing" /><br /> 
				<label for="tag1">Average writing</label></p>
				<p><input id="tag1" name="tag1" type="radio" value="Writing=Excellent writing" /><br /> 
				<label for="tag1">Excellent writing</label></p>
				</fieldset>
				<fieldset>
				<legend>Ending satisfaction</legend>
				<p><input id="tag2" name="tag2" type="radio" value="Ending=Disappointing ending" /><br /> 
				<label for="tag2">Disappointing ending</label></p>
				<p><input id="tag2" name="tag2" type="radio" value="Ending=Okay ending" /><br /> 
				<label for="tag2">Okay ending</label></p>
				<p><input id="tag2" name="tag2" type="radio" value="Ending=Satisfying ending" /><br /> 
				<label for="tag2">Satisfying ending</label></p>
				</fieldset>
				</div>
				<div id="bookeval" style="display:none;">
				<fieldset>
				<legend>Currency</legend>
				<p><input id="tag2" name="tag2" type="radio" value="Currency=Out of date" /><br /> 
				<label for="tag2">Out of date</label></p>
				<p><input id="tag2" name="tag2" type="radio" value="Currency=Slightly out of date" /><br />
				<label for="tag2">Slightly out of date</label></p>
				<p><input id="tag2" name="tag2" type="radio" value="Currency=Up to date" /><br />
				<label for="tag2">Up to date</label></p>
				</fieldset>
				<fieldset>
				<legend>Objectivity</legend>
				<p><input id="tag1" name="tag1" type="radio" value="Objectivity=Completely biased" /><br /> 
				<label for="tag1">Completely biased</label></p>
				<p><input id="tag1" name="tag1" type="radio" value="Objectivity=Slightly biased" /><br /> 
				<label for="tag1">Slightly biased</label></p>
				<p><input id="tag1" name="tag1" type="radio" value="Objectivity=Completely unbiased" /><br /> 
				<label for="tag1">Completely unbiased</label></p>
				</fieldset>
				</div>
				<div id="videofilmeval" style="display:none;">
				<fieldset>
				<legend>Video quality</legend>
				<p><input id="tag1" name="tag1" type="radio" value="Video=Poor quality" /><br /> 
				<label for="tag1">Poor quality</label></p>
				<p><input id="tag1" name="tag1" type="radio" value="Video=Average quality" /><br /> 
				<label for="tag1">Average quality</label></p>
				<p><input id="tag1" name="tag1" type="radio" value="Video=Excellent quality" /><br /> 
				<label for="tag1">Excellent quality</label></p>
				</fieldset>
				<fieldset>
				<legend>Directing</legend>
				<p><input id="tag2" name="tag2" type="radio" value="Directing=Bad direction" /><br /> 
				<label for="tag2">Bad direction</label></p>
				<p><input id="tag2" name="tag2" type="radio" value="Directing=Acceptable direction" /><br /> 
				<label for="tag2">Acceptable direction</label></p>
				<p><input id="tag2" name="tag2" type="radio" value="Directing=Amazing direction" /><br /> 
				<label for="tag2">Amazing direction</label></p>
				</fieldset>
				</div>
				<div id="soundrecordingeval" style="display:none;">
				<fieldset>
				<legend>Audio quality</legend>
				<p><input id="tag1" name="tag1" type="radio" value="Audio=Poor quality" /><br /> 
				<label for="tag1">Poor quality</label></p>
				<p><input id="tag1" name="tag1" type="radio" value="Audio=Average quality" /><br /> 
				<label for="tag1">Average quality</label></p>
				<p><input id="tag1" name="tag1" type="radio" value="Audio=Excellent quality" /><br /> 
				<label for="tag1">Excellent quality</label></p>
				</fieldset>
				<fieldset>
				<legend>Production quality</legend>
				<p><input id="tag2" name="tag2" type="radio" value="Production=Poor production" /><br /> 
				<label for="tag2">Poor production</label></p>
				<p><input id="tag2" name="tag2" type="radio" value="Production=Average production" /><br /> 
				<label for="tag2">Average production</label></p>
				<p><input id="tag2" name="tag2" type="radio" value="Production=Excellent production" /><br /> 
				<label for="tag2">Excellent production</label></p>
				</fieldset>
				</div>
				<div style="clear:both"></div>
				<!--<p><b>Bottom line</b></p>
				<p><input id="recommended" type="radio" value="Yes" name="recommended" />
				<label for="recommended">Yes, I would recommend this to a friend</label></p>
				<p><input id="recommended" type="radio" value="No" name="recommended" />
				<label for="recommended">No, I would not recommend this to a friend</label></p>-->
				<input class="button" type="submit" value="Submit" />
			</form>
	       		</div>
	       		<div id="works" class="facet_set">
	       		<span class="heading"></span>
	       		</div>
    	</div> <!--end container-->
    	</div>
    </div>
   </body>
</html>
