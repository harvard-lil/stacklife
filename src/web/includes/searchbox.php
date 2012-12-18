<div class="header group">
	<div class="header-content group">
		<div class="search">
			<form id="search" method="get" action="<?php echo $www_root?>/search.php">
            	<input type="hidden" style="display:none" name="search_type" value="keyword"/>
            	<input type="text" name="q" autofocus="autofocus" placeholder="Search"/>
            	<input type="submit" name="submit_search" id="itemsearch" value="Go!"/>
			</form>
			<!-- <a id="inline" href="#advanced" style="display:none">Advanced Search</a>
			<a href="<?php echo $www_root?>/search.php?search_type=advanced&q" class="button advanced-search">Advanced Search</a> -->
    	</div><!--end search-->
	</div><!--end header-content-->
</div><!--end header-->

<!-- Advanced search fancybox, start -->
<div style="display:none">
	<div id="advanced">
		<form method="get" action="<?php echo $www_root?>/search.php">
		<div class="left advanced-facets">
		<div class="facet_set">
		<div class="heading">Show only</div>
		<label><input type="checkbox" value="rsrc_key:Google" name="filter" /> Full text available</label>
		<div class="heading">Language</div>
		<ul class="facet_pairs">
			<li><label><input type="checkbox" value="language:English" name="filter" class="pointer" /> English</label></li>
			<li><label><input type="checkbox" value="language:German" name="filter" class="pointer" /> German</label></li>
			<li><label><input type="checkbox" value="language:Spanish" name="filter" class="pointer" /> Spanish</label></li>
			<li><label><input type="checkbox" value="language:French" name="filter" class="pointer" /> French</label></li>
			<li><label><input type="checkbox" value="language:Italian" name="filter" class="pointer" /> Italian</label></li>
			<li><label><input type="checkbox" value="language:Latin" name="filter" class="pointer" /> Latin</label></li>
			<li><label><input type="checkbox" value="language:Arabic" name="filter" class="pointer" /> Arabic</label></li>
			<li><label><input type="checkbox" value="language:Chinese" name="filter" class="pointer" /> Chinese</label></li>
			<li><label><input type="checkbox" value="language:Russian" name="filter" class="pointer" /> Russian</label></li>
		</ul>
		<!--<p>Location</p>
		<ul class="facet_pairs" style="display:none">
			<li id="language:English"><label><input type="checkbox" /> Widener</label></li>
			<li id="language:German"><label><input type="checkbox" /> Lamont</label></li>
			<li id="language:Spanish"><label><input type="checkbox" /> Baker Business</label></li>
			<li id="language:French"><label><input type="checkbox" /> Law School</label></li>
			<li id="language:Italian"><label><input type="checkbox" /> Gutman Education</label></li>
			<li id="language:Latin"><label><input type="checkbox" /> Countway Medicine</label></li>
			<li id="language:Arabic"><label><input type="checkbox" /> Cabot Science</label></li>
			<li id="language:French"><label><input type="checkbox" /> Loeb Design</label></li>
			<li id="language:Russian"><label><input type="checkbox" /> Harvard-Yenching</label></li>
			<li id="language:Polish"><label><input type="checkbox" /> Tozzer</label></li>
		</ul>-->
		<div class="heading">Material Format</div>
		<ul class="facet_pairs">
			<li><label><input type="checkbox" value="format:Book" name="filter" /> Book</label></li>
			<li><label><input type="checkbox" value="format:Serial" name="filter" /> Serial</label></li>
			<li><label><input type="checkbox" value="format:Sound Recording" name="filter" /> Sound Recording</label></li>
			<li><label><input type="checkbox" value="format:Video/Film" name="filter" /> Video/Film</label></li>
			
		</ul>
		</div>
		</div>
		<div class="left advanced-inputs">
			<div class="facet_set">
				<div class="heading">Advanced search terms</div>
			</div>

              <p>
              	<select name="search_type">
                  	<option value="title_exact">Title begins with</option>
                      <option value="title_keyword">Title contains keyword(s)</option>
                      <option value="creator">Author (last, first)</option>
                      <option value="creator_keyword">Author contains keyword(s)</option>
                      <option value="desc_subject_lcsh_exact">Subject begins with</option>
                      <option value="desc_subject_keyword">Subject contains keyword(s)</option>
                      <option value="keyword" selected="selected">Keyword(s) anywhere</option>
                  </select>
                  <input type="text" class="searchBox" name="q"/>
              </p>
              <p>
              	<select class="filter_type">
                      <option value="title_exact">Title begins with</option>
                      <option value="title_keyword">Title contains keyword(s)</option>
                      <option value="creator">Author (last, first)</option>
                      <option value="creator_keyword">Author contains keyword(s)</option>
                      <option value="desc_subject_lcsh_exact">Subject begins with</option>
                      <option value="desc_subject_keyword">Subject contains keyword(s)</option>
                      <option value="keyword" selected="selected">Keyword(s) anywhere</option>
                  </select>
                  <input type="hidden" value="" name="filter" />
                  <input type="text" class="searchBox filter_query" />
              </p>
              <p id="addremove"><span class="addfield">add</span> / <span class="removefield">remove</span></p>

          </div>
		<input type="submit" name="submit_search" value="Go!" class="right"/>
          </form>



	</div>
</div>
<!-- Advanced search fancybox, end -->

<!-- Scripts common to all pages -->
<script type="text/javascript" src="<?php echo $www_root?>/js/jquery.fancybox-1.3.4.pack.js"></script>

<script type="text/javascript">

$(document).ready(function() {
  $("a#inline").fancybox({
    'overlayShow': true,
    'autoDimensions' : false,
    'width' : 700,
    'height' : 400
  });
});

</script>