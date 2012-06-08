<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 

<?php
require_once ('../../sl_ini.php');
include_once ('includes.php');
?>

<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
  <link rel="icon" href="images/favicon.ico" type="image/x-icon"> 

   
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script> 

<script language="javascript" type="text/javascript"> 
 
function callAPI(libr,searcher){
var resp;

$.ajax({
type: "GET",
url: "/librarycloud/v.3/api/item/",
processData:true,
data: {search_type : 'keyword',key:'BUILD-LC-KEY',sort: 'shelfrank desc',filter : 'source:' + libr ,query:searcher, start:'0',limit:'10'},
async:false,
contentType: "application/json; charset=utf-8",
dataType: "json",
success: function(json) {
	resp=json;
	},
error: function(x,y,z) {
  resp = "++ERROR: " + x + " : " + y + " : " + z;
	}
})

return resp;
}

function submitenter(e)
{
var keycode;
if (window.event) keycode = window.event.keyCode;
else if (e) keycode = e.which;
else return true;

if (keycode == 13)
   {
   mainLoop();
   return false;
   }
else
   return true;
}


 
function mainLoop(){ 
	
	// get the libraries to search
	var libraries = new Array();
	var libraryNames= new Array("Darien Public","Harvard University","Northeastern University","San Francisco Public", "San Jose Public");
	var checks = document.checkboxes.librarybtn.length;
	if (checks ==0) { // error check
	  alert("Choose at least one library");
	  return
	}
	// build array of libraries to check. Their librarycloud handles are the buttons' values.
	for (i = 0; i < checks; i++) {
	if (document.checkboxes.librarybtn[i].checked) {
	  //libraries.push(document.checkboxes.librarybtn[i].value);
	  libraries.push(i); // array of which buttons are checked
		}
	}
	// show the container
	document.getElementById("trending-container").style.display="block";
	
    // build the table
	
	//clear old table
	var da = document.getElementById('displayarea');
	da.innerHTML="";
	
	
	 
	// Create line stating the subject searched for
	var searchterm = document.getElementById("searchbox").value;
	searchterm = $.trim(searchterm); // trim it
	if (searchterm != ""){
		var subjtitle = document.createElement("p");
		//subjtitle.innerHTML= "Results for <b>" + unescape(searchterm) + "</b>:";//
		subjtitle.setAttribute("class","subjtitle");
		da.appendChild(subjtitle);
	}
	 
	// create table
	 
	var tbl = document.createElement("table");
	tbl.setAttribute("id","tab");
	da.appendChild(tbl);
	 
	// Create headers
	var i;
	//var headings =  ['Date','Score', 'Title', 'Author','Description'];
	var hdr = document.createElement("tHead"); 		// create table header
	tbl.appendChild(hdr);							// append header
	var hdrrow =  document.createElement("tr");		// create row
	for (i=0; i < libraries.length; i++){			//create cells
			cell = document.createElement("td");	// create cell
			pcell = document.createElement("p");	// create <p>
			pcell.setAttribute("class","hdr found");		// set class
			cell.appendChild(pcell);				//append p to cell
			pcell.innerHTML = libraryNames[libraries[i]];		// insert library name
			pcell.setAttribute("class","hdr");		// set class
			pcell.setAttribute("id", "hdr"+i); 		// create id
			hdrrow.appendChild(cell);				// append cell to row
				}	
	hdr.appendChild(hdrrow);	 					// insert the hdr row 
	
	//create table body
	var tbod = document.createElement("tbody");
	tbl.appendChild(tbod);
	
	// -- loop through the libraries, calling the api for each
	var request_size = 10; // how many returns in a batch; set in apiCall()
	var resp,jso,total_number, arr;
	//var arr = {"doc":[{"title":""},{"creator":""}]};
	var bigarray = new Array();
	for (i=0; i < libraries.length; i++){
	   	//resp = callAPI(libraries[i], searchterm);				// call the api
	   	resp = callAPI(document.checkboxes.librarybtn[libraries[i]].value, searchterm);
	 	jso = eval(resp);
		arr = jso["docs"];
		if (1 > 2) { //(arr.length < request_size) { // didn't get enough returns from a Library
			for (var k = arr.length; k < request_size; k++){
				arr.length = arr.length + 1;
				arr[k].title = "-";
				arr[k].creator = "?";
			}
		}
		bigarray.push(arr);							// array of arrays
		// add number found to header cells
		total_number =jso["num_found"];
		total_number = addCommas(total_number);
		pcell = document.getElementById("hdr" + i);
		pcell.innerHTML = pcell.innerHTML + "<br><span class='found'>Found:</span> " + total_number;
	}
		
	 
	 // -- add the rows
	 
	 
	var yr,title,score, desc,tr,td,s,y, whichrow=true;;
	for (y=0; y <request_size;y++){ // create one row per result
		tr = document.createElement("tr"); // create new row
		if (whichrow) { // alternate row colors
			  tr.setAttribute("class","row1");
			  }
		else {tr.setAttribute("class","row0");
			}
	   whichrow = !whichrow;
		for (i=0; i < libraries.length; i++) { // get next entry for each library
		  // check to make sure this library has a result
		  //var reslen = bigarray[i].length;
		  if (jQuery.isEmptyObject(bigarray[i][y]) == true ){
		    yr="";
		    title="";
		    score="";
		    desc="[No description]";
		    author = "";
		    format = "";
		    callnumber = "";
		    checkouts ="";
		    pubdate = "";
		    link = "";
		  }
		  else {
			yr = bigarray[i][y].pub_date;
			title = bigarray[i][y].title;
			score = bigarray[i][y].total_score;
			desc = bigarray[i][y].desc_subject_lcsh;
			author=bigarray[i][y].creator;
			format = bigarray[i][y].format;
			callnumber = bigarray[i][y].call_num;
			checkouts =  bigarray[i][y].aggregation_checkout;
			pubdate = bigarray[i][y].pub_date;
			link = 'book/' + bigarray[i][y].title_link_friendly + '/' + bigarray[i][y].id
		  }
		  if (yr == null) {yr="[No year]";}
		  if (title == null) {title="[No title]";}
		  if (score == null) {score="-";}
		  if (desc == null) {desc="";};
		  if ((author == null) || (author == "NULL")) {author = "";}		  
		  // create the cells of the row
		  td = document.createElement("td");
		  p = document.createElement("p")
		  p.innerHTML="<span class='trendTitle'><a href='" + link + "'>" + title + "</a></span><br />" + "<span class='trendAuthor'>" + author + "</span><br /> ";
		  p.setAttribute("class","bookcell");
		  s = "DESCRIPTION: " + desc;
		  if (format != undefined) {s = s + " FORMAT: " + format;}
		  if (callnumber != undefined) {s = s + " CALL#: " + callnumber;}
		  if (pubdate != undefined) {s = s + " PUBDATE: " + pubdate;}
		  if (checkouts != undefined) {s = s + " CHECKOUTS: " + checkouts;}
		  p.setAttribute("title",s); // set the tooltip
		  td.appendChild(p);
		  tr.appendChild(td);
		  }
		  		  
		  tbl.appendChild(tr);
		}
}
 

function toggleExplanation(){
 // show and hide the explanation
 var div = document.getElementById("explans");
 if (div.style.display=="none") {
     $(div).show("slow");
     }
     else {
     $(div).hide("slow");
     }
}

	
	
function addCommas(nStr) {
  // tnanks, mredkj!
  //http://www.mredkj.com/javascript/nfbasic.html
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

$(document).ready(function($) {
   mainLoop();
});
	
</script> 
</head> 
<body> 
	<div id="wrapper">
		<?php require_once('header.php');?>
			<div class="container group">
				<div class="container-content">
		<br />
		<h1>Top Checkout Trends</h1> 
 
		<span class="heading tagline">Compare and contrast the most circulated books at libraries participating in LibraryCloud. </span>
		<span class="checkOutsLogo"></span>
		<div id="ui">
			<div class="hidden">
				<form name="checkboxes"  class="pclass">
					<input type="checkbox" name="librarybtn" value="darienlibrary_org" checked> Darien Public Library
					<input type="checkbox" name="librarybtn" value="harvard_edu" checked>Harvard 
					<input type="checkbox" name="librarybtn" value="northeastern_edu" checked>Northeastern U. 
					<input type="checkbox" name="librarybtn" value="sfpl_org" checked> San Francisco Public Library
					<input type="checkbox" name="librarybtn" value="sjlibrary_org" checked> San Jose Public Library
				</form>
			</div><!-- end hidden -->
			
			<input type="text" style="border:1px solid #09f;" id="searchbox" width="40" onKeyPress="return submitenter(event)" placeholder="Keyword Search">

			<input type="submit" onclick="mainLoop()" value="Go!">
		</div><!-- end ui -->	
	
		<div id="totnumb"></div>
		
		<div id="trending-container">
			<div id="displayarea"></div> 	
		</div><!-- end ui -->	
	</div><!-- end wrapper -->	
</body> 
