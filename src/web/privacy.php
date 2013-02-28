<?php
  require_once('../../etc/sl_ini.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>StackLife</title>

<?php
include_once('includes/includes.php');
?>
</head>

<body>

    <div class="container group">
    	<div class="row group">
			<div class="span2 middle-position">
			 	<?php require_once('includes/logo.php');?>
			</div>
			<div class="span4 offset1 about-button">
				<a href="<?php echo $www_root ?>/" class="about home">Home</a>
			</div>
	
			<div class="span4 offset1">
				<?php require_once('includes/searchbox.php');?>  
			</div> 
		</div>
		
		<div class="row group">
			<div class="span5 offset2 text-padding">		
			
				<span class="question">Our Policy Privacy</span><br />
            	<p>The Harvard Library Innovation Laboratory at Harvard Law School is an academic institution engaged in experimental research related to the improvement of library services and technologies. Our online software projects may collect information of various kinds from visitors, but the Lab is committed to respecting and protecting personal privacy.</p>

				<p>We collect limited, anonymous information from site visitors in order to measure site activity and improve the usefulness of our site. The usage information we collect may include IP address, browser type, operating system, screen size and resolution, date and time of visit, search engine and search terms, and IP address of referring page. We collect this information on an aggregate basis only. We do not link any of the above information to any personal identifiers. </p>

				<p>Some of our software services and sites make use of third party services that may collect IP addresses and set cookies. We publish exactly what those services are. Those users who do not wish to expose any personal information should consider using anonymizing services where possible.</p>
			
				<p>For information specific to our StackLife online service, see below.</p>
					<br />
					
				<span class="question">Circulation Data</span><br />

				<p>StackLife performs computations on anonymized circulation data about Harvard Library materials. This data represents ten-year cumulative checkouts of items. StackLife uses this data to compute a "StackScore," which is then broken into ten percentiles which are displayed as ten different shades of blue in StackLife's visualization of stacked items.</p>
					<br />
				<span class="question">Disclosure to third parties</span><br />

				<p>Available log records, and all stored data may be accessed by our system administrators. The system administrators may produce these records and data to the Administrative Dean, Dean of Students, or the Administrative Board, upon request or suspected violation of our terms of use. It is Harvard's policy to cooperate with law enforcement officials in the detection, investigation, and prosecution of unlawful activity. If we receive a warrant or subpoena for user information, we may disclose requested records to law enforcement authorities or outside parties seeking information through the legal process.</p>

				<p>In the event that we are required by law (including a court order) to disclose the information you submit, we will provide you with notice (unless we are prohibited) that a request for your information has been made so that you may object to the disclosure. We will send this notice by email to the address you have given us. We prefer to independently object to over-broad requests for access to information about users of our site, but we cannot assure you that we will be able to do this in all cases. If you do not challenge the disclosure request yourself, we may be legally required to turn over your information.</p>

					<br />
					
				<span class="question">Links to independent sites</span><br />

				<p>The Lab site including Lab research project sites may link to sites outside of the law.harvard.edu domain. The Lab is not responsible for the privacy practices or content of such sites.</p>
					<br />
								
				<span class="question">Information tracked by StackLife</span><br />

				<p>StackLife tracks information the set of pages a user visits throughout a session and uses that information to display a list of works StackLife users have visited during sessions when they have also visited a particular work's StackLife page. StackLife does not record any personally identifying information about any particular user's path during any session.</p>

				<p>As noted in the second paragraph of this statement, StackLife collects limited, anonymous information from site visitors in order to measure site activity and improve the usefulness of our site.</p>
				
				<p>As is true for many Web sites, StackLife uses a number of third party sites for services and content. When StackLife makes a request to those services, the Hypertext Transfer Protocol used by virtually all Web sites  automatically includes the IP address of the user's machine. </p>
			</div>
					<br />
								
			<div class="span5 video text-padding">		

        	<p>Some of the third party services are optional and clearly labeled. For example, users of StackLife can click on a button to purchase a book at a third party site. But, some of the third party services StackLife uses are automatically called by StackLife when pages are loaded. Users of StackLife should be aware that by visiting various StackLife pages, the following third party services may be automatically called, resulting in the Hypertext Transfer Protocol communicating the user's IP address:</p>
        	<br/>
        	
        	<p>(1)Google:</p>
        	<p class="indent">(a) When a user does not already have cached copy of jQuery (an open source library of Javascript functionality) on her or his machine, StackLife fetches a copy via the Google API.  This procedure slightly improves performance</p><br />
        		
        	<p class="indent">(b) Similar to (a), if the user does not already have cached copies of some fonts, StackLife automatically downloads them via the Google API.</p><br />
        		
        	<p class="indent">(c) StackLife uses Google Analytics, to provide information to the Library Innovation Lab about the number and patterns of people visiting StackLife. Google Analytics is in wide use around the Web. More information about what it tracks can be found <a href="http://www.google.com/analytics/">here</a>. If you would like to opt out of Google Analytics for all sites, please follow the instructions on <a href="http://tools.google.com/dlpage/gaoptout">this page.</a></p><br />
        		
        	<p class="indent">(d) Pages that contain embedded YouTube videos fetch the image of that video from YouTube. (YouTube is owned by Google)</p>
        		<br/>
        
        	<p>(2) In order to provide a "buy at Amazon" button, StackLife checks with Amazon.com to see if the work is available through that site. </p>
        	
        	<p>(3) StackLife downloads the Facebook "Like" button from Facebook.com.</p>
        	
        	<p>(4) StackLife uses a button from AddThis.com that enables the user to post to various social media sites. </p>
        	
        	<p>In each of these cases, the information automatically communicated to the listed site consists of the standard information included in an HTTP request, including the user's IP address. If, however, the user explicitly clicks on a link to a third party service, additional information will very likely be transmitted; for example, clicking on a link to the Google book viewer will also transmit information that identifies the book to be viewed. Users who do not want to communicate any additional information to third party sites should not click on those links.</p>
        	
        	<p>It is possible that we will add other third party services to StackLife, since this brings the user new functionality. If so, we will update this list. Additionally, while we have attempted to provide a complete list, because the HTTP protocol requires the user's IP address and because that protocol is invoked for Web activities as common as using an image from another site, we cannot guarantee that we have here listed all such cases. </p>
  					<br />
					      	
        	<span class="question">Children</span><br />
			<p>The Lab site including Lab research project sites are intended for adults. We do not knowingly collect personal information from children under 13 years old. If you are a parent or legal guardian of a child under age 13 who you believe has submitted personal information to a Lab site, please contact us at the address below immediately.</p>
					<br />
								
			<span class="question">Questions?</span><br />
			<p>If you have any questions about this privacy statement, the practices of this site, or your dealings with this site, you can contact:</p>
			
			<img class="stamp" src="images/liblabstampmed.png">
			<p>Harvard Library Innovation Laboratory<br />
			Harvard Law School Library<br />
			Langdell Hall, First Floor<br />
			Cambridge, MA 02138<br />
			lil@law.harvard.edu</p>
					<br />
								
			<span class="question">Changes to this privacy statement</span><br />
			<p>An announcement of any changes to this privacy statement will be posted prominently on our blog and the first page of our site.</p>
					<br />
								
			<span class="question">Effective date of this privacy statement</span><br />
			<p>The effective date of this privacy statement is March 11, 2010.</p>
				
			</div>
		</div><!--end row-->
    	
	</div><!--end container-->


</body>
</html>
