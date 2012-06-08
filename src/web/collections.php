<?php
  require_once ('../../sl_ini.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>ShelfLife / Digital Public Library of America</title>
<?php
include_once ('includes.php');
echo <<<EOF

		<link rel="stylesheet" href="$www_root/css/ui.slider.extras.css" type="text/css" />
		<link rel="stylesheet" href="$www_root/css/superbly-tagfield.css" />
		<link rel="stylesheet" href="$www_root/css/collections.css" type="text/css" />
		<link rel="stylesheet" href="$www_root/css/chosen.css" />
		<script type="text/javascript"  src="$www_root/js/modernizr.js"></script>
		<script type="text/javascript" src="$www_root/js/superbly-tagfield.js"></script>
		<script charset="utf-8" type="text/javascript" src="$www_root/js/jquery.tablednd_0_5.js"></script>
		<script type="text/javascript" src="$www_root/js/selectToUISlider.jQuery.js"></script>

EOF;
?>
</head>

<body>
<div id="wrapper">
	<?php require_once('header.php');?>
    <div class="container group">
		<div id="collection_content" class="container-content">


			<!-- START MANAGE BOOK BAG -->
			<section id="manage_book_bag">
				<h1 id="top">Manage your Shelves</h1>

			<ul id="book_info_bar">
				<li class="infoSection">
					<p style="padding-left:10px;">Shelves are your personal collections. You can share them or keep them to yourself. </p>
				</li>
				<li class="infoSection right">
					<p>
						<span class="span_for_chzn_select">Your shelves:</span>
						<select name="whichcollection" data-placeholder="Your shelves..." class="chzn-select" style="width:220px;" tabindex="2">>
						<option value="0" selected="">My teenage years</option>
						<option value="1">Beginning physics experiments</option>
						<option value="2">Science in science fiction</option>
						<option value="3">History of Space</option>
						<option value="4">Space travel</option>
						</select>
					</p>
				</li>
			</ul>

			<div id="book_history">
			<article class="mast">
				<h2>My teenage years</h2>
				<a href="#" id="aqbbut" >&darr;Show</a>
				<div class="clear"></div>
			</article>

			<section id="book_history_section" class="hiding">
				<aside class="bhs_half">
					<ul class="bhs_nav">
						<li class="bhs_item">
							<div class="book_item_blocks">
								<aside class="left"><p>Number of items in this Shelf:</p></aside>
								<aside class="right">
									<p>12</p>
								</aside>
									<div class="clear"></div>
							</div>
						</li>
						<li class="bhs_item">
							<div class="book_item_blocks">
								<aside class="left"><p>Date created:</p></aside>
								<aside class="right">
									<p>September 1, 2011</p>
								</aside>
									<div class="clear"></div>
							</div>
						</li>
						<li class="bhs_item">
							<div class="book_item_blocks">
								<aside class="left"><p>Link:</p></aside>
								<aside class="right">
									<p><input type="text" disabled="disabled" value="http://www.dpla.org/bonnir/collections/my_teenage_years.php"><input class="copyButton" type="button" value="copy"></p>
								</aside>
									<div class="clear"></div>
							</div>
						</li>
						<li class="bhs_item">
							<div class="book_item_blocks">
								<aside class="left"><p>Embed this shelf:</p></aside>
								<aside class="right">
									<textarea class="md">http://www.dpla.org/bonnir/collections/my_teenage_years.php</textarea><input class="copyButton" type="button" value="copy">
								</aside>
									<div class="clear"></div>
							</div>
						</li>
					</ul>
				</aside>
				<aside class="bhs_half">
					<ul class="bhs_nav">
						<li class="bhs_item">
							<div class="book_item_blocks">
								<aside class="left"><p>Title:</p></aside>
								<aside class="right">
							     <input type="text" value="My teenage years">
								</aside>
								<div class="clear"></div>
							</div>
						</li>
						<li class="bhs_item">
							<div class="book_item_blocks">
								<aside class="left"> <p>Tags for this Shelf:</p> </aside>
								<aside class="right">
							    <input type="text" id="tagfield2" />
								</aside>
								<div class="clear"></div>
							</div>
						</li>
						<li class="bhs_item">
							<div class="book_item_blocks">
								<aside class="left"><p>Description:
							</p></aside>
								<aside class="right">
							     <textarea class="metatext">When I was younger I read these books, and now I plan on reading them to my children. </textarea>
								</aside>
									<div class="clear"></div>
							</div>
						</li>
						<li class="bhs_item">
							<div class="book_item_blocks">
								<aside class="left">
										<p>Who gets to see this Shelf?</p>
								</aside>
								<aside class="right">
								<form>
									<table>
										<tr>
											<td>
												<label class="label_radio">
													<input type="radio" name="perm">
													Everyone
												</label>
											</td>
											<td>
												<label class="label_radio">
												<input type="radio" name="perm" checked="checked">
												Only with permission
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="label_radio">
													<input type="radio" name="perm">
													Anyone who follows you
												</label>
											</td>
											<td>
												<label class="label_radio">
													<input type="radio" name="perm">
													Just you
												</label>
											</td>
										</tr>
									</table>
							   </form>
								</aside>
								<div class="clear"></div>
							</div>
				 		</li>
					</ul>
				</aside>
				<div class="clear"></div>
			</section>
			</div>





			    <!-- Start Collection -->
			  <div id="collection">

				<article class="mast">
					<h2 id="item">Manage Items</h2>
					<!-- <a href="#" id="showCollItemSlides" >&darr;Hide</a> -->
					<div class="clear"></div>
				</article>

				<section id="book_collection_section" class="showing">
				    <p style="padding:10px 10px 10px">Drag items to reorder the list.</p>
				    <div id="collection_holder">
						<!-- INSERT COLLECTION VIEW -->
					</div>
					<div class="clear"></div>
					<div class="displays">
						<aside class="left">
							<input id="removeCollItems" type="button" value="x Remove checked items">
							<div style="display:none;" id="changeCollMapBg">
								<a style="font-size:small; color:#999;" href="#">Change Background</a>
							</div>
						</aside>
						<aside class="right">
							<form style="height:20px;">
								<table>
									<tr>
										<td>Change default view: </td>
										<td>
											<label class="label_radio">
												<input checked="checked" type="radio" name="perm" value="Stack">
												 Stack
											</label>
											</td>
										<td>
											<label class="label_radio">
												<input type="radio" name="perm" value="Grid">
												Grid
											</label>
										</td>
										<td>
											<label class="label_radio">
												<input type="radio" name="perm" value="Map">
												Map
											</label>
										</td>
									</tr>
								</table>
						   </form>
						</aside>
					</div>
				</section>
				<div class="clear"></div>
			  </div><!-- End Collection -->

			  <div class="clear"></div>


			     <!-- Start Collection Info -->
			  <div id="collection_info">
			  <!--
					<aside class="left"><em>For a very short video on the grid and map views, click the button</em> <a href="#videodemo" id="showvid">Show Demo</a>
						<div  style="display:none;">
							<div id="videodemo">
								<iframe seamless src="http://player.vimeo.com/video/26963094?title=0&amp;byline=0&amp;portrait=0" width="100%" height="350" frameborder="0"></iframe>
						    	<p>
						    		<a href="http://vimeo.com/26963094">Two ways of displaying a collection</a> from <a href="http://vimeo.com/user7249558">ShelfLife Collaborative.</a>
						    	</p>
							</div>
						</div>
					</aside>
			-->
					<aside class="right">
			    <p id="extramuros">
			    	You can export this Book Bag to the extraMUROS collection viewer. <input type="button" value="Export">
			    </p>
					</aside>
						<div class="clear"></div>
			 	</div><!-- End Collection Info -->

				<div class="clear"></div>

			      <!-- Start Collection Info -->
			  <div id="collection_permissions">
			    <h2>Manage permissions</h2>
			    <p>The following DPLA users have permission to see this collection</p>
			    <input type="text" id="allowed_users" />
			 	</div><!-- End Collection Permissions -->

				<div class="clear"></div>

			</section> <!-- END MANAGE BOOK BAG-->


		</div><!--end container-content-->
	</div><!--end container-->
	<?php require_once('footer.php');?>
	<script>



	</script>
	<script src="<?php echo $www_root; ?>/js/jquery.layup.js" type="text/javascript"></script>
	<script src="<?php echo $www_root; ?>/js/jquery.event.drag-2.0.min.js" type="text/javascript"></script>
	<script src="<?php echo $www_root; ?>/js/jquery.event.drop-2.0.min.js" type="text/javascript"></script>
	<script src="<?php echo $www_root; ?>/js/excanvas.min.js" type="text/javascript"></script>

	<script src="<?php echo $www_root; ?>/js/collections.js"></script>
	<script src="<?php echo $www_root; ?>/js/chosen.jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript"> $(".chzn-select").chosen(); </script>

</div><!--end wrapper-->

</body>
</html>