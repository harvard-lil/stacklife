<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="shelflife.css" type="text/css" />
<title>ShelfLife | The Harvard Library Laboratory</title>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="js/DD_roundies.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="twitter.js"></script>
<script type="text/JavaScript" src="js/rounded_corners.inc.js"></script>
<script type="text/JavaScript">
	  window.onload = function() {
	      settings = {
	          tl: { radius: 20 },
	          tr: { radius: 20 },
	          bl: { radius: 20 },
	          br: { radius: 20 },
	          antiAlias: true,
	          autoPad: true
	      }
	      var myBoxObject = new curvyCorners(settings, "rounded");
	      myBoxObject.applyCornersToAll();
	  }
	</script>

<script type="text/javascript">
    $(document).ready(function() {
    	$('.oldnotes').hide();
		$('.oldnoteslink').click(function() {
			$(this).next().slideToggle();
		});
    });
</script>         	         

<script type="text/javascript">
document.write('<scr' + 'ipt src="' + document.location.protocol + 
'//fby.s3.amazonaws.com/fby.js"></scr' + 'ipt>');
</script>
<script type="text/javascript">
FBY.showTab({id: '433', position: 'right', color: '#FF9600'});
</script>   
</head>
<!--<body onload="ImageBook()">	-->
<body>
  <!--[if IE]> 
          <script type='text/javascript'>
        	  DD_roundies.addRule('#bounding-container', '8px');
              DD_roundies.addRule('a.rounded-buttons2', '5px');  
              DD_roundies.addRule('#info-container', '8px');
          </script>
    <![endif]-->

	<div class="container_24" id="bounding-container">
  
        <div class="clear"></div>
        <br />
      	<?php include_once('header.php'); ?>                
          
        <div class="clear"></div>
        
        <div class="grid_24">
      		<h5>Release Notes for ShelfLife Alpha</h5>
      		<br />
            <p>
            	The main object of this initial alpha release is to get your feedback.  Up till now, the application has primarily been developed and tested in house.  This is our first opportunity to scale up one level and get outside users to help us see what we've created.  We would love to hear your reactions.  We have included a feedback widget at the right margin of every page to make this easier for you (and for us in evaluating your comments).  Please feel free to let us know of any bugs you come across, takes on the current UI we've opted for, features we're including, the organization and flow of the site -- whatever you'd like to tell us.  And also feel free to mention any ideas for new features, different kinds of content, different ways of relating materials.  
            </p>
            <br />
            <p>
            	We will continue to release new alpha versions of ShelfLife on roughly a two-week cycle going forward for the next several months.  We have on the drawing board build-outs of current features (support for keyword searching; version views of items within returned search results; smooth, continuous scrolling of the book sets within the stackview window; and many others) as well as new features and feature domains.  So check back often.
            </p>
            <br />                        
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 2.7 (May 20, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
   					A new, brief intro video has been added to the home page, explaining a couple core concepts in the application.
            	</li>
            	<li>
						On the search results page, the user can now choose to see books ranked in any of 3 different ShelfRank perspectives: 
						<ul>
							<li>
								"university" perspective: weights our circulation statistics components according to the results of a survey conducted among librarians and other library professionals
							</li>
							<li>
								"undergraduate" perspective: identical to the university perspective, except that checkouts by undergraduates are rated higher than those by faculty and graduate students
							</li>
							<li>
								"library" perspective: tracks 2 metrics: the number of libraries at Harvard which have chosen to acquire the item and the number of extra copies beyond the first copy which have been acquired by Harvard libraries
							</li>
						</ul>
            	</li>
            	<li>
						A holding-library facet has now been added, allowing the user to constrain any result set to those items in the collections of a given Harvard library.
            	</li>
            	<li>
            		Information tips -- indicated by a small "i" and clicked by the user -- have now been added to various user interface components to make the user interface more clear.
            	</li>       
            	<li>
            		ShelfRank has now been recalibrated on a scale of 1 to 10 (decile scale), a scale we hope will be more easily understood than the former open-ended one with extreme values at the top.
            	</li>       
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">        	         	
            	<li>
						Not all pages fully support Internet Explorer.
            	</li>           	         	
            </ul>
            </div>                 
            <br />                        
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 2.6 (May 6, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
   					On the book page, population of the book panel is now far more quick and less disruptive -- formerly a full page-refresh was required; now the book panel alone is updated.  In addition, the current shelf is maintained when the user clicks a book in it, instead of defaulting the user to the Infinite Bookshelf.
            	</li>
            	<li>
						Linkage out to the book vendors' pages on the book page has now been fixed.
            	</li>
            	<li>
						The shelf-button area on the book page has been reconfigured and items within it partially renamed to make it easier for the user to understand the functions of the button groups and the individual buttons within them.
            	</li>
            	<li>
            		The background color has been changed from dark green to white.
            	</li>       
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
						The new UI does not yet fully support the IE browser.
            	</li>           	         	
            	<li>
						The author page does not yet support some of the UI features recently developed on the book page, appearing broken where this is the case.
            	</li>           	         	
            </ul>
            </div>                 
            <br />                        
              <span class="oldnoteslink">Release Notes for ShelfLife alpha 2.5 (April 22, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		Faceting has been re-designed on the book and author pages: its display defaults to hidden -- to open it, the user clicks on the "Refine the Stack" tab along the left margin.
            	</li>
            	<li>
						The color scheme of the UI has been modified: the background is now a bluish green to allow the content panels to be viewed with greater clarity.
            	</li>
            	<li>
						The search feature has now been made uniform on all pages: a default of global keyword and a clickable lightbox for advanced search options.
            	</li>
            	<li>
            		In the shelf views on the book and author pages, the navigation arrows on the left enclose a number indicating the number of objects in the current shelf.
            	</li>
            	<li>
            		The home page has been re-architected to accommodate a panel which hosts both the search function as well as an explanatory graphic clarifying the meaning of StackView visualization.
            	</li>         
            	<li>
            		On the book and author pages, the currently selected shelf is indicated as a green button state within the shelf navigation area.
            	</li>         
            	<li>
            		On the search results page, faceting display has been moved to the right into two boxes, with usage faceting displayed in its own box above the facets dealing directly with book and other object information.
            	</li>         
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
						The new UI does not yet fully support the IE browser.
            	</li>           	         	
            </ul>
            </div>                 
            <br />                        
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 2.4 (April 8, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		An advanced-search function has now been added to the home page: a link when clicked generates a light box which allows search queries to be constructed using both facets and dropdowns of specific search types.  If more than one search type is selected, then all search queries added together must be satisfied in the search results returned.
            	</li>
            	<li>
						The Infinite Bookshelf is now backed by Solr (instead of MySQL), increasing performance and allowing faceting to be added to that neighborhood.
            	</li>
            	<li>
						In StackView, three new item container types have been created to represent audio, video and serial materials.  These now complement the original book-type container.
            	</li>
            	<li>
            		Publication year labels have now been added to the book, audio and video container types.
            	</li>
            	<li>
            		An Applied Filters function has been added to the top of the facets sidebar on the right of the book page to allow the user to manage adding and subtracting facets in the Infinite Bookshelf and subject neighborhoods on the book page.
            	</li>         
            	<li>
            		The facets display on all pages has been simplified -- the user sees only one facet at a time expanded and only after that facet heading has been clicked.
            	</li>         
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
						A few display issues are currently being resolved in the Infinite Bookshelf faceting and subject-heading components.
            	</li>   
            	<li>
						The author field within the results-page listings sometimes displays "Unknown" when in fact the author is known to the system.
            	</li>   
            	<li>
						Occasional delays can be experienced in the loading of books into StackView.
            	</li>   
            	<li>
						The new UI does not yet fully support the IE browser.
            	</li>           	         	
            </ul>
            </div>                 
            <br />                        
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 2.3 (March 25, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		Facets have now been added to the book and author pages, when subject neighborhoods are being viewed.
            	</li>
            	<li>
            		Faceting has now been substantially improved -- the user may now:
            		<ul>
            			<li>limit results to a range of ShelfRank scores</li>
            			<li>limit results to items checked out by different academic user groups (undergraduate students, graduate students, faculty)</li>
            			<li>limit results by publication-date range</li>
            			<li>limit results by any one of 8 broad format types</li>
            		</ul>
            	</li>
            	<li>
            		The speed has been much increased with which subject neighborhoods are now being generated on the book and author pages, due to the use of Solr indexing instead of database retrieval.
            	</li>
            	<li>
            		In the Infinite Bookshelf (based on LC call-number sort order), subject headings corresponding to the current LC class area have been inserted to the left of the book stack to give the user better awareness of the current subject location.
            	</li>          
            	<li>
            		The Infinite Bookshelf has doubled in size, now consisting of over 6 million titles, or roughly half of all items currently in the Harvard online catalog.
            	</li>          
            	<li>
            		Tooltips have now been added to StackView on the author page.
            	</li>          
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
						StackView tooltips have not yet been implemented on the home page.
            	</li>   
            	<li>
						Occasional delays can be experienced in the loading of books into StackView.
            	</li>   
            	<li>
						The new UI does not yet fully support the IE browser.
            	</li>           	         	
            </ul>
            </div>                 
            <br />                        
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 2.2 (March 11, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		Tooltips have been added to StackView rendering of neighborhoods on the book page, implementing mouse hovering as an enhanced information display of underlying item.
            	</li>
            	<li>
            		User-generated tagging feature has been implemented on the book page, together with the display of the most popular tags and the option to display as neighborhoods all books related to a given tag.
            	</li>
            	<li>
            		Embedded Google Books viewer has been included on the book page, now as an overlay window rather than requiring displacement of other elements to accommodate viewer.
            	</li>          
            	<li>
            		Minor stylistic enhancements have been made, including more prominent and recongizable "Find in Hollis" button on book page.
            	</li>          
            	<li>
            		ShelfRank component scores have been fixed to accurately reflect composition of total scores.
            	</li>          
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
						StackView tooltips have not yet been implemented on the home and author pages.
            	</li>   
            	<li>
						Occasional delays can be experienced in the loading of books into StackView.
            	</li>   
            	<li>
						The new UI does not yet fully support the IE browser.
            	</li>   
            	<li>
						The open-content area of the author page is currently only being tested with sample content from various Web services for 100 or so Harvard Law School authors.  See, for example, <a href="http://librarylab.law.harvard.edu/shelflife/author/Sunstein,%20Cass%20R">Sunstein</a>, <a href="http://librarylab.law.harvard.edu/shelflife/author/Palfrey,%20John%20G.">Palfrey</a>, or <a href="http://librarylab.law.harvard.edu/shelflife/author/Minow,%20Martha,%201954-">Minow</a>.  This is an important and complex feature domain we want to build out, but be aware that it is currently only a stub implementation.
            	</li>            	         	
            </ul>
            </div>                 
            <br />                        
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 2.1 (Feb. 25, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		The home page has been redesigned to more prominently profile the search entry point into the application, and to simplify and clarify the portrayal of the application.
            	</li>
            	<li>
            		The UI is now template-based, with a consistent 3-column page layout and header and footer throughout all pages.
            	</li>
            	<li>
            		StackView presentation has been vertically constrained to an area defined by the browser's screen size minus the header and buffer heights.
            	</li>          
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
						ShelfRank component scores need to be updated (the total scores are accurate).  They currently display values taken from the previous iteration of ShelfRank data.
            	</li>   
            	<li>
						Linkage to full-text and the embedding of the Google Books viewer have yet to be re-implemented in the new UI.
            	</li>   
            	<li>
						Occasional delays can be experienced in the loading of books into StackView.
            	</li>   
            	<li>
						The new UI does not yet fully support the IE browser.
            	</li>   
            	<li>
						The open-content area of the author page is currently only being tested with sample content from various Web services for 100 or so Harvard Law School authors.  See, for example, <a href="http://librarylab.law.harvard.edu/shelflife/author/Sunstein,%20Cass%20R">Sunstein</a>, <a href="http://librarylab.law.harvard.edu/shelflife/author/Palfrey,%20John%20G.">Palfrey</a>, or <a href="http://librarylab.law.harvard.edu/shelflife/author/Minow,%20Martha,%201954-">Minow</a>.  This is an important and complex feature domain we want to build out, but be aware that it is currently only a stub implementation.
            	</li>            	         	
            </ul>
            </div>                 
            <br />
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 2.0 (Feb. 16, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		The site user interface has been redesigned to more powerfully realize the application's core service of facilitating discovery and browsability of books through neighborhoods.
            	</li>
            	<li>
            		StackView has been greatly optimized to allow continuous, smooth scrolling through the whole call-number neighborhood.  Scrolling can happen either by using the scroll arrows top and bottom or by using your mouse wheel.
            	</li>
            	<li>
            		Faceting has been added on the search-results page to allow the user to successively filter search results as desired.  Faceting will soon be added to the book page neighborhoods as well.
            	</li>
            	<li>
            		StackView has now been included also on the author page where applicable (for displaying all titles by the author represented on the page).
            	</li>
            	<li>
						A "Read this too" feature has been created on the book page, which allows users to link any book to the current book, as a recommendation to others to explore a related book in the context of the current book.  We have included a small keyword search function allowing the user to easily find the book to be linked.  You can see the neighborhood of all books linked to the present book in this way by clicking the "Read this too" button in the book information panel on the right of any book page.
					</li>            
            	<li>
						On the home page, a StackView visualization of books recently accessed by ShelfLife users has been included.
					</li>            
            	<li>
						The underlying ShelfRank data has been broadened to include 2002-present stats for most metrics. 
					</li>            
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
						ShelfRank component scores need to be updated (the total scores are accurate).  They currently display values taken from the previous iteration of ShelfRank data.
            	</li>   
            	<li>
						The open-content area of the author page is currently only being tested with sample content from various Web services for 100 or so Harvard Law School authors.  See, for example, <a href="http://librarylab.law.harvard.edu/shelflife/author/Sunstein,%20Cass%20R">Sunstein</a>, <a href="http://librarylab.law.harvard.edu/shelflife/author/Palfrey,%20John%20G.">Palfrey</a>, or <a href="http://librarylab.law.harvard.edu/shelflife/author/Minow,%20Martha,%201954-">Minow</a>.  This is an important and complex feature domain we want to build out, but be aware that it is currently only a stub implementation.
            	</li>            	         	
            </ul>
            </div>                 
             <br />
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 1.4 (Jan. 28, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		All neighborhoods in both the book and author pages are now rendered using the new CSS-implemented StackView implementation, with smooth scrolling. 
            	</li>
            	<li>
						Cross-browser compatibility has now been implemented (Firefox, Google Chrome, Safari, Internet Explorer).
					</li>            
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
						The tags search is currently minimal, since it depends on users' input -- we're hoping you can begin helping us test it out with your own tags.
            	</li>   
            	<li>
						The open-content area of the author page is currently only being tested with sample content from various Web services for 100 or so Harvard Law School authors.  See, for example, <a href="http://librarylab.law.harvard.edu/shelflife/author/Sunstein,%20Cass%20R">Sunstein</a>, <a href="http://librarylab.law.harvard.edu/shelflife/author/Palfrey,%20John%20G.">Palfrey</a>, or <a href="http://librarylab.law.harvard.edu/shelflife/author/Minow,%20Martha,%201954-">Minow</a>.  This is an important and complex feature domain we want to build out, but be aware that it is currently only a stub implementation.
            	</li>            	         	
            </ul>
            </div>
            <br />
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 1.3 (Jan. 14, 2011)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		Search has now been re-implemented using Solr, instead of MySQL, which means far better performance overall and the addition of keyword searching mode to all 3 pre-existing "begins-with" search types: author, title and subject.  Search also includes a global keyword option.
            	</li>
            	<li>
						StackView has been re-implemented using CSS on the book page (StackView on the author page has not yet been re-implemented).  This allows the introduction of continuously scrolling navigation within a StackView neighborhood, as opposed to a user experience in which one StackView increment of books simply supplants the previous view as the user moves forward and backward through a given neighborhood of books.  Book titles are now more legible and complete, and are supplemented with the author's name, where available.  StackView now only supports a vertical orientation, with books sitting atop each other, which facilitates reading of the spine information.  Also, the option to display StackView using randomized colors has been suppressed in favor of the heatmap scheme to immediately indicate book score.
					</li>            	
            	<li>
            		Duplication of e-resource buttons on the book page has now been fixed.
            	</li>
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		The app is not fully cross-browser compatible -- it is currently fully functional only in Firefox.
            	</li>
            	<li>
            		In the interest of beginning to iterate over the new StackView CSS implementation, several features from previous releases which do not yet support the new implementation have been taken off-line: the e-source search and the subject-neighborhood language filter, as well as the book page's user and also-viewed neighborhood.
            	</li>
            	<li>
						The tags search is currently minimal, since it depends on users' input -- we're hoping you can begin helping us test it out with your own tags.
            	</li>   
            	<li>
						The open-content area of the author page is currently only being tested with sample content from various Web services for 100 or so Harvard Law School authors.  See, for example, <a href="http://librarylab.law.harvard.edu/shelflife/author/Sunstein,%20Cass%20R">Sunstein</a>, <a href="http://librarylab.law.harvard.edu/shelflife/author/Palfrey,%20John%20G.">Palfrey</a>, or <a href="http://librarylab.law.harvard.edu/shelflife/author/Minow,%20Martha,%201954-">Minow</a>.  This is an important and complex feature domain we want to build out, but be aware that it is currently only a stub implementation.
            	</li>            	         	
            </ul>
            </div>
            <br />
            <span class="oldnoteslink">Release Notes for ShelfLife alpha 1.2 (Dec. 17, 2010)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		Search now includes the option to filter on e-journals and e-books, allowing the user the option of only returning search results for which there exists a link to a full- or partial-text online version of the item.
            	</li>
            	<li>
						The e-resource data backing the e-journals/e-books filter described above has been re-harvested to include 600,000 more records of these resources.  Currently, roughly 1 million of the 12 million bibliographic items in ShelfLife can be viewed online.
					</li>            	
            	<li>
            		The book page's subject neighborhood now dynamically configures the language filter dropdown to display only those languages actually available in the currently selected subject neighborhood.
            	</li>
            	<li>
						The Google Books Viewer tab on the book page now only appears if there is a Google Books instance of the current book, and the mode of availability is also displayed in the tab.
					</li>            	
					<li>
						A browser alert has been added to the interface to warn users with non-Firefox browsers that the ShelfLife site is not currently cross-browser enabled, and to use Firefox for optimal display.
					</li>
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		The app is not fully cross-browser compatible -- it is currently fully functional only in Firefox.
            	</li>
            	<li>
						There are still latency issues in some areas of searching, primarily when subject searching -- the more so when entering overly generic subject search terms.  Subject searching is over Library of Congress subject headings, and does not currently support keyword mode.
            	</li>
            	<li>
						The tags search is currently minimal, since it depends on users' input -- we're hoping you can begin helping us test it out with your own tags.
            	</li>   
            	<li>
						The open-content area of the author page is currently only being tested with sample content from various Web services for 100 or so Harvard Law School authors.  See, for example, <a href="http://librarylab.law.harvard.edu/shelflife/author/Sunstein,%20Cass%20R">Sunstein</a>, <a href="http://librarylab.law.harvard.edu/shelflife/author/Palfrey,%20John%20G.">Palfrey</a>, or <a href="http://librarylab.law.harvard.edu/shelflife/author/Minow,%20Martha,%201954-">Minow</a>.  This is an important and complex feature domain we want to build out, but be aware that it is currently only a stub implementation.
            	</li>            	         	
            </ul>
            </div>
            <br />            
      		<span class="oldnoteslink">Release Notes for ShelfLife alpha 1.1 (Dec. 3, 2010)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		Completed move from remotely hosted data from Harvard's central systems to locally hosted data.  The only data to which this does not apply is book availability data, which due to its continually updating character is still obtained from a remote service at Harvard's central systems.
            	</li>
            	<li>
            		The book page's subject neighborhood now allows filtering results by language.  Currently, we have pre-selected several languages for the user to choose from.  We will soon dynamically populate the language picklist to represent all languages available for the given subject heading.  The choice of language is "sticky" -- it will become the default language for all subject neighborhoods in ShelfLife over the course of the user's browser session.
            	</li>
            	<li>
						Subject neighborhoods are now scrollable over the full dataset of relevant titles in Harvard's catalog.  The results are returned sorted by ShelfLife's shelf rank.
					</li>
            	<li>
            		Amazon links (from book page) have been fixed.
            	</li>
            	<li>
            		Inadvertent spine-title truncation in StackView has been fixed.
            	</li>
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		The app is not fully cross-browser compatible -- it is currently fully functional only in Firefox.
            	</li>
            	<li>
						There are still latency issues in some areas of searching, primarily when subject searching -- the more so when entering overly generic subject search terms.  Subject searching is over Library of Congress subject headings, and does not currently support keyword mode.
            	</li>
            	<li>
						The tags search is currently minimal, since it depends on users' input -- we're hoping you can begin helping us test it out with your own tags.
            	</li>   
            	<li>
						The open-content area of the author page is currently only being tested with sample content from various Web services for 100 or so Harvard Law School authors.  This is an important and complex feature domain we want to build out, but be aware that it is currently only a stub implementation.
            	</li>            	         	
            </ul>
            </div>
            <br />      		
      		<span class="oldnoteslink">Release Notes for ShelfLife alpha 1.0 (Nov. 19, 2010)</span>
      		<div class="oldnotes">
            <h6>What's in This Release?</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		We've moved almost entirely off remotely hosted data from Harvard's central systems.  This is the first iteration of our metadata-server project LibraryCloud, still closely fitted to the needs of ShelfLife.  LibraryCloud implements local data stores populated via data dumps from Harvard's central systems and optimized data and table structures to deliver more efficient data services -- a major boost in both performance and available data scope.
            	</li>
            	<li>
            		ShelfLife now uses local API's for most of its own data needs, API's which would be accessible via the same protocols to external data harvesters -- an  initial step towards a more generic, non-Harvard-limited data-service architecture.
            	</li>
            	<li>
						The LibraryCloud data-ingestion and -processing regime is now fully scripted, allowing automated regeneration of SL's data sources whenever new data points, indexing or structuring is required.
					</li>
            	<li>
            		The UI has been reworked in substantial ways, most noticeably through a compression of page architecture via tabs, a better look, and better navigation.
            	</li>
            	<li>
						All sorting of search results now operates over full result-set scope and not the earlier, partial-view, api-based scope.  For example, when searching on "iliad" via title-begins-with, SL returns results sorted via rank for the full 848-record result set instead of sorting via rank within increments of 25 results as was the case with the remote-api service.
            	</li>
            	<li>
						Shelf rank has now been expanded (beyond circulation data) to include course reserves and recalls data.
            	</li>
            </ul>
            <br />
            <h6>Known Issues</h6>
            <br />
            <ul class="release_notes">
            	<li>
            		The app is not fully cross-browser compatible -- it is currently fully functional only in Firefox.
            	</li>
            	<li>
						There are still latency issues in some areas of searching, primarily when subject searching -- the more so when entering overly generic subject search terms.  Subject searching is over Library of Congress subject headings, and does not currently support keyword mode.
            	</li>
            	<li>
						The tags search is currently minimal, since it depends on users' input -- we're hoping you can begin helping us test it out with your own tags.
            	</li>   
            	<li>
						The open-content area of the author page is currently only being tested with sample content from various Web services for 100 or so Harvard Law School authors.  This is an important and complex feature domain we want to build out, but be aware that it is currently only a stub implementation.
            	</li>            	         	
            </ul>
            </div>
            <br />
            <br />
        </div>
        
        <div class="clear"></div>        
        
      	<div class="grid_24" id="footer-line">
                    <div class="grid_16 alpha" id="footer">
                         <p><a href="http://librarylab.law.harvard.edu/">The Harvard Library Innovation Lab</a> at the Harvard Law School | <a href="http://www.librarylab.law.harvard.edu/about/privacy.php">Privacy Statement</a></p> 
                    </div>
                    <div class="grid_8 omega">             
                        <ul class="footer-navigation">
                            <!--[if IE]> 
                                <script type='text/javascript'>
                                    DD_roundies.addRule('a.rounded-buttons', '3px');
                                    DD_roundies.addRule('.selected2', '3px');
                                </script>
                            <![endif]-->
                   <!--         <li><a href="analytics.html" class="rounded-buttons2">Analytics</a></li>
                            <li><a href="index.php" class="selected2">Home</a></li>-->
                        </ul>   			
                    </div>  
		</div><!-- end footer-line footer container -->
        <div class="clear"></div>
  </div><!-- end boundiung container -->
<!--  </div>--><!-- end border-shadow -->
<?php //include_once("footer.php"); ?>
    
</body>
</html>
