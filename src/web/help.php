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
global $TYPEKIT_CODE;
echo <<<EOF
  <link rel="author" href="$www_root/humans.txt" />
  <link rel="icon" href="$www_root/images/favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="$www_root/css/shelflife.theme.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/template.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/stackview/jquery.stackview.css" type="text/css" />
  
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script type="text/javascript" src="$www_root/stackview/jquery.stackview.min.js"></script>
  <script type="text/javascript" src="$www_root/js/landing_page.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.fitvids.js"></script>
  
  $TYPEKIT_CODE
EOF;

?>
<script>
  $(document).ready(function(){
    $(".video").fitVids();
  });
</script>
</head>

<body>

    <div class="container group">
    	<div class="row">
			<div class="span2 middle-position">
			 	<?php require_once('includes/logo.php');?>
				<div class="about-button">
					<a href="index.php" class="about">Home</a>
				</div>
			</div><!--end logo include-->

			<div class="span10 offset2 text-padding">
				<p><span class="call-out">This is a callout</span>ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Typi non habent claritatem insitam; est usus legentis in iis qui facit eorum claritatem. Investigationes demonstraverunt lectores legere me lius quod ii legunt saepius. Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam littera gothica, quam nunc putamus parum claram, anteposuerit litterarum formas humanitatis per seacula quarta decima et quinta decima. Eodem modo typi, qui nunc nobis videntur parum clari, fiant sollemnes in futurum.</p>
			</div>
		</div><!--end row-->
		
		<div class="row group">
			<div class="span5 offset2 text-padding">		
			
											<span class="question">What is Shelflife?</span><br />
            <p>The Harvard Library Innovation Laboratory at Harvard Law School is an academic institution engaged in experimental research related to the improvement of library services and technologies. Our online software projects may collect information of various kinds from visitors, but the Lab is committed to respecting and protecting personal privacy.</p>

			<p>We collect limited, anonymous information from site visitors in order to measure site activity and improve the usefulness of our site. The usage information we collect may include IP address, browser type, operating system, screen size and resolution, date and time of visit, search engine and search terms, and IP address of referring page. We collect this information on an aggregate basis only. We do not link any of the above information to any personal identifiers. </p>

			<p>Some of our software services and sites make use of third party services that may collect IP addresses and set cookies. We publish exactly what those services are. Those users who do not wish to expose any personal information should consider using anonymizing services where possible.</p>
			
			<p>For information specific to our ShelfLife online service, see below.</p>

			<span class="question">Disclosure to third parties</span><br />

			<p>Available log records, and all stored data may be accessed by our system administrators. The system administrators may produce these records and data to the Administrative Dean, Dean of Students, or the Administrative Board, upon request or suspected violation of our terms of use. It is Harvard's policy to cooperate with law enforcement officials in the detection, investigation, and prosecution of unlawful activity. If we receive a warrant or subpoena for user information, we may disclose requested records to law enforcement authorities or outside parties seeking information through the legal process.</p>

			<p>In the event that we are required by law (including a court order) to disclose the information you submit, we will provide you with notice (unless we are prohibited) that a request for your information has been made so that you may object to the disclosure. We will send this notice by email to the address you have given us. We prefer to independently object to over-broad requests for access to information about users of our site, but we cannot assure you that we will be able to do this in all cases. If you do not challenge the disclosure request yourself, we may be legally required to turn over your information.</p>


			<span class="question">Links to independent sites</span><br />

			<p>The Lab site including Lab research project sites may link to sites outside of the law.harvard.edu domain. The Lab is not responsible for the privacy practices or content of such sites.</p>
			
			<span class="question">Information tracked by ShelfLife</span><br />

			<p>ShelfLife tracks information the set of pages a user visits throughout a session and uses that information to display a list of works ShelfLife users have visited during sessions when they have also visited a particular work's ShelfLife page. ShelfLife does not record any personally identifying information about any particular user's path during any session.</p>

			<p>As noted in the second paragraph of this statement, ShelfLife collects limited, anonymous information from site visitors in order to measure site activity and improve the usefulness of our site.</p>
			</div>
			<div class="span5 video text-padding">		
				<iframe src="http://player.vimeo.com/video/55894472?title=0&amp;byline=0&amp;portrait=0" width="500" height="334" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe> 
				
							<span class="question">ShelfLife and third parties</span><br />
        	
        	<p>As is true for many Web sites, ShelfLife uses a number of third party sites for services and content. When ShelfLife makes a request to those services, the Hypertext Transfer Protocol used by virtually all Web sites  automatically includes the IP address of the user's machine. </p>
        	
        	<p>Some of the third party services are optional and clearly labeled. For example, users of ShelfLife can click on a button to purchase a book at a third party site. But, some of the third party services ShelfLife uses are automatically called by ShelfLife when pages are loaded. Users of ShelfLife should be aware that by visiting various ShelfLife pages, the following third party services may be automatically called, resulting in the Hypertext Transfer Protocol communicating the user's IP address:</p>
        	
        	<p>(1)Google:</p>
        	<p>(a) When a user does not already have cached copy of jQuery (an open source library of Javascript functionality) on her or his machine, ShelfLife fetches a copy via the Google API.  This procedure slightly improves performance</p><br />
        		
        	<p>(b) Similar to (a), if the user does not already have cached copies of some fonts, ShelfLife automatically downloads them via the Google API.</p><br />
        		
        	<p>(c) ShelfLife uses Google Analytics, to provide information to the Library Innovation Lab about the number and patterns of people visiting ShelfLife. Google Analytics is in wide use around the Web. More information about what it tracks can be found <a href="http://www.google.com/analytics/">here</a>. If you would like to opt out of Google Analytics for all sites, please follow the instructions on <a href="http://tools.google.com/dlpage/gaoptout">this page.</a></p><br />
        		
        	<p>(d) Pages that contain embedded YouTube videos fetch the image of that video from YouTube. (YouTube is owned by Google)</p>
        	
        
        	<p>(2) In order to provide a "buy at Amazon" button, ShelfLife checks with Amazon.com to see if the work is available through that site. </p>
        	
        	<p>(3) ShelfLife downloads the Facebook "Like" button from Facebook.com.</p>
        	
        	<p>(4) ShelfLife uses a button from AddThis.com that enables the user to post to various social media sites. </p>
        	
        	<p>In each of these cases, the information automatically communicated to the listed site consists of the standard information included in an HTTP request, including the user's IP address. If, however, the user explicitly clicks on a link to a third party service, additional information will very likely be transmitted; for example, clicking on a link to the Google book viewer will also transmit information that identifies the book to be viewed. Users who do not want to communicate any additional information to third party sites should not click on those links.</p>
        	
        	<p>It is possible that we will add other third party services to ShelfLife, since this brings the user new functionality. If so, we will update this list. Additionally, while we have attempted to provide a complete list, because the HTTP protocol requires the user's IP address and because that protocol is invoked for Web activities as common as using an image from another site, we cannot guarantee that we have here listed all such cases. </p>
        	
        	<span class="question">Children</span><br />
			<p>The Lab site including Lab research project sites are intended for adults. We do not knowingly collect personal information from children under 13 years old. If you are a parent or legal guardian of a child under age 13 who you believe has submitted personal information to a Lab site, please contact us at the address below immediately.</p>
			
			<span class="question">Questions?</span><br />
			<p>If you have any questions about this privacy statement, the practices of this site, or your dealings with this site, you can contact:</p>
			
			<p>Harvard Library Innovation Laboratory<br />
			Harvard Law School Library<br />
			Langdell Hall, First Floor<br />
			Cambridge, MA 02138<br />
			lil@law.harvard.edu</p>
			
			<span class="question">Changes to this privacy statement</span><br />
			<p>An announcement of any changes to this privacy statement will be posted prominently on our blog and the first page of our site.</p>
			
			<span class="question">Effective date of this privacy statement</span><br />
			<p>The effective date of this privacy statement is March 11, 2010.</p>
				
			</div>
		</div><!--end row-->
    	
	</div><!--end container-->


</body>
</html>
