<?php 
  require_once ('../../sl_ini.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <title>ShelfLife FAQ</title>
<?php
include_once ('includes.php');
	
?>

 
<style>
border-color: blue;*/
    }
a.david-link { 
 	color:#f60;
 }

a:visited .david-link { 
 	color:#000;
 }
 
    h1{
     }
    h2{
    font-size: 18px;
    margin-bottom: 24px;
    
    }
    p,li,.q,.a, dt {
     font-size: 14px;
     margin-bottom:10px;
     line-height: 130%;
    }
    dt {
     margin-left: 0px;
     
    }
.a{
	margin-left:20px;
}

</style>
</head>

<body>
<div id="main">
	<?php require_once('header.php');?>
	
	 <div class="container group">          			
		<div class="container-content">
			<div class="about-column">
			
				<h1 class="h1dw">ShelfLife FAQ</h1>
	
				<h2 class="cyan">What's the difference between ShelfLife and LibraryCloud?</h2>
				
				<p class="a"><em>LibraryCloud</em> is a middleware server. It collects, integrates, and makes available metadata from libraries and other curatorial organizations.</p>
				<p class="a"><em>ShelfLife</em> is a browser-based library/collection navigator that uses LibraryCloud's metadata store via API access.</p>
    
    
	<h2 class="cyan">Does ShelfLife or LibraryCloud manage content?
	</h2>
	<p class="a">Nope. LibraryCloud tracks pointers to content, but it is a metadata server. ShelfLife displays content made available to the DPLA.
    </p>
    
    <h2 class="cyan">What are they based on?
	</h2>
	<p class="a">Both ShelfLife and LibraryCloud are based on projects underway for about the past 1.5 years at the Harvard Library Innovation Lab.
	</p>
 

    <h2 class="cyan">How much of the DPLA beta sprint demo is real?
	</h2>
	<p class="a">With a few exceptions, it's real, functioning code. The items are being drawn from the LibraryCloud collection of almost 15 million items. The re-factoring of the shelves is occurring in real time. The search and faceting is solid. The social networking sub-system is robust. Here are the items in the demo that cheat: </p>
		<dl>
			<dt>Because the demo is not populated with lots of online material, the demo uses some manually constructed shelves of e-material.</dt>
			<dt>Some of the pages are obviously placeholder pages. For example, the Collection Manager is a fairly polished sketch of functionality, but (as we state in the demo) it has not been hooked into LibraryCloud, so it is populated with a static set of items, and it does not save changes.</dt>
			<dt>Because ShelfLife DPLA has no users, we've fabricated some social interaction data, user reviews, etc.</dt>
			<dt>We have left the home page bare because we did not want to presume to design it for the DPLA on our own.</dt>
			<dt>We have ShelfRank working on the local Harvard version of ShelfLife. It weighs about eight different factors, including whether the item has been put on reserve, how often it's been called back early, whether the checkouts have been by students or faculty, etc. In this demo, we have simply used the number of checkouts, because that is the only information we have from our public library partners. Further, we have assigned arbitrary "collection points" to the non-library material (for example, the TED Talks) so that they will show up high enough in searches that users find them. This is an area for lots more research and development.</dt>
			<dt>How to classify Web-based material is also a rich area for research. We have some ideas, but for now, we have not attempted to classify them.</dt> 
		</dl>
			
	<h2 class="cyan">Who decides which Web material gets included?
	</h2>
	<p class="a">This is a matter for DPLA to decide. For the demo, we made what we think is a reasonable decision that TED Talks, @Google Talks, NPR programs, open courseware, and Berkman YouTubes are non-controversial examples to include.
	</p>


    <h2 class="cyan">How do you see ShelfLife's social capabilities developing?
	</h2>
	<p class="a">The core architecture is in place and is solid and scalable, but there's lots of room for growth. 
	<dl>
		<dt>We need to work on the usability of these features in a fully scaled deployment &mdash; what do you do when you have millions of reviews and discussions?</dt>
		<dt>ShelfLife's architecture has a notification system that is ready for deployment, so that news about followed items (and other events) can be pushed to the user.</dt>
		<dt>We have not yet designed a personal preferences page where various privacy options will be set.</dt>
	</dt>
    </p>
    
    <h2 class="cyan">Why show physical books that are not accessible online?
	</h2>
	<p class="a">We think the DPLA should become the reference point for "Library World," and that world has lots and lots of books. So, ShelfLife shows what LibraryCloud knows about books.  That's how we've built our catalog so far. And it is a point of integration between the DPLA and local libraries. 
	</p> 
    

     <h2 class="cyan">What about privacy?
	</h2>
	<p class="a">ShelfLife and LibraryCloud will support whatever privacy policies the DPLA decides on. We have assumed for now that it will be acceptable to allow ShelfLife users to opt in to registering, and opt in to making some of their interactions with the system public, or shareable among named users. 
	</p>

    <h2 class="cyan">What about the name "ShelfLife"?
	</h2>
	<p class="a">"ShelfLife" is a code name for this beta sprint. If DPLA were to use ShelfLife as its default front end, the name "ShelfLife" would presumably not appear anywhere, just as it does not appear in the demo pages we've constructed. DPLA is the "brand."
	</p>


    <h2 class="cyan">What about recommendations?
	</h2>
	<p class="a">The information being gathered by LibraryCloud will, we believe, be very helpful in constructing open recommendation systems. We do, however, observe limits on the correlation of data in order to preserve privacy. (We hope DPLA will sponsor research on  the right parameters for preserving a reasonable degree of privacy.)
	</p>
	
    <h2 class="cyan">You use the OpenLibrary book viewer. Suppose a work is not in OpenLibrary?
	</h2>
	<p class="a">We use the OL viewer because OL sets the standard for openness, and because it gives ShelfLife users access to many hundreds of thousands of e-books. We will use other viewers for other types of reading material. Adding viewers is not a particularly big deal.
	</p>   


	<h2 class="cyan">Why do some items have so little metadata, and no alternative shelves to facet on?
	</h2>
	<p class="a">In the best case, we can match an item from a new source to an existing item in the catalogs LibraryCloud has available to it, chiefly the Harvard Library catalog, because then we get lots of metadata and subject classifications. Without that, we harvest as much metadata as we can from the new source. We are iterating on that right now. And, Linked Open Data presents a possibility of gathering yet more metadata, a path we have been actively exploring with Dan Brickley, one of the leading thinkers and practitioners of LOD.
	</p>   
	
</div><!--about-column-->

	<div class="spacer right"></div>
	
<div class="about-column right">
		<h2 class="cyan">I noticed duplicates on shelves. Why?
	</h2>
	<p class="a">This is, of course, one of the classic Hard Problems. It is not completely soluble (because there are conceptual difficulties in the notion of "the same book"), but we can get better and better at it We already group multiple editions into one "Collected Editions" object, which you may come across. Corey Harper at NYU, one of the ShelfLife Collaborative partners, has been working on some advanced techniques for clustering works even across media types. This is an area in which we would very much look forward to working with partners.
	</p>  
  
     <h2 class="cyan">How does ShelfLife handle the various licensing arrangements?
	</h2>
	<p class="a">Licenses are metadata. Once licensing information is available, it will be used to appropriately limit access through ShelfLife.
    </p>




	<h2 class="cyan">Is LibraryCloud open for public access?
	</h2>
	<p class="a">Not quite. We are close to making it publicly available, as an alpha implementation with some reasonable restrictions on the amount of access.
    </p>


        
    <h2 class="cyan">Does LibraryCloud manage closed or encumbered metadata?
	</h2>
	<p class="a">No, as a matter of policy.
    </p>
    
      <h2 class="cyan">Where can I see more information about LibraryCloud?
	</h2>
	<p class="a">Here is a <a href="http://hlsl10.law.harvard.edu/dpla/demo/app/librarycloud.php" class="david-link">working paper</a> written for this beta sprint. There is also a <a href="http://www.librarycloud.org" class="david-link">wiki</a> for the LibraryCloud initiative we began about a year before the sprint was announced.
    </p>
    
    
 	<h2 class="cyan">Are ShelfLife and LibraryCloud separable?
	</h2>
	<p class="a">LibraryCloud does not need ShelfLife. ShelfLife is quite closely tied to LibraryCloud, and makes heavy use of its APIs. Although it would not be trivial to redirect ShelfLife to another set of APIs, it is conceivable.
    </p>
    
     	<h2 class="cyan">Might there be other front ends to DPLA?
	</h2>
	<p class="a">Absolutely. That is indeed one of the aims of LibraryCloud. We can imagine the DPLA or external developers creating specialized front ends for children, Greek scholars, movie fans, etc., either by modifying the ShelfLife code base or by starting from scratch with the same metadata LibraryCloud makes available to ShelfLife.
    </p>
    
    <h2 class="cyan">How would you build a version of ShelfLife and LibraryCloud ready for public use in eighteen months?
	</h2>
	<p class="a">With the help of the DPLA, we would initiate a distributed software project, headquartered at Harvard's Library Innovation Lab in collaboration with the Berkman Center for Internet & Society. The core functionality of the system is well-along and quite robust, including the managing of millions of items, the integration of multiple sources, the exposure of data through APIs and Linked Open Data, and the social infrastructure. There are many areas &mdash; for example, recommendation and clustering algorithms &mdash; in which we would work with other groups. In short, we would establish a collaborative network, including many of the other beta sprint projects.
	</p>
	

   <h2 class="cyan">How did you do the integration with Wikipedia?
	</h2>
   <p class="a">We've gone through Wikipedia with a brute force comb to try to find every book that has its own article. If there is one, we here show the categories it's classified under on its Wikipedia page, and let the user click on a category to see all the other works in that category. If the DPLA collection doesn't contain that work, we put the work's Wikipedia page on the shelf.</p><p class="a">We did this by harvesting Wikipedia categories via DBpedia. We then ran these through Amazon's Mechanical Turk, so that 60,000 possible book articles were checked three times by hand. We have thus expanded the number of known Wikipedia book pages from about 15,000 to about 60,000.</p><p class="a">We are putting this list of 60,000 into the public domain, and, through one of our partners (and fellow beta sprinter) SJ Klein, will make it available for direct use by Wikpedia itself.</p><p class="a"> If the work is used in one of the 2,600 Open Courseware courses that LibraryCloud knows about, we show it on a shelf with the other works used in that course.</p><p class="a">Likewise for other multimedia Web collections of significant value.</p><p class="a">If another user has put this work into a handmade collection, they are browsable here as well.</p>
   
   <p class="a">How can the DPLA cluster items together onto shelves when many of those items do not come with categories? For example, how do you know where to put a TED Talk?</p>
   <p class="a">The shelf we used to explore the "item page" in the demo was constructed by hand and saved as a "personal shelf" (or collection). We did that to make sure we had a good set of e-books and e-materials to demo. But ShelfLife will need to associate objects in the DPLA distributed collections, including items pulled in from the Web. This is not a trivial problem. We've begun doing research into using Linked Data to cluster items. The direction we've explored: <ol><li>Create a 'word cloud' for every Library of Congress Subject Heading by mixing in the text of all the subject headings (at this point, no the text of the works themselves) of every work in our collection within that heading, as well as the text of their tables of contents, and any other metadata we can gather from other organizations and via linked data trails.<li>Gather as much metadata as possible from each of the Web sources, and again traverse webs of linked data.<li>Use these word clouds as signatures to match the Web items' word clouds with the closest LCSH word cloud.</ol><p class="a">We would look forward to working with other DPLA partners and researchers in techniques for finding multiple vectors of similarity among items in the DPLA's distributed collections</p>
   
  <p class="a">&nbsp;</p>
   


	 <h2 class="cyan">Contacts</h2><br />
				<h2>With questions about any aspect of this demo, please contact:
				<ul>
					<li>Kim Dulin <span class="grey"> kdulin@law.harvard.edu <span class="orange">|</span> (617) 496 3292</span>
					<li>David Weinberger <span class="grey"> self@evident.com <span class="orange">|</span> (617) 852 6902</span>
					<li>Paul Deschner <span class="grey"> deschner@law.harvard.edu <span class="orange">|</span> (617) 384 9799</span>
				</ul>

</div>
</div>
</div><!--about-column-->
</div> <!-- main -->
</body>
</html>
