<div class="header group">
	<div class="header-content group">
		<div class="search">
			<form id="search" method="get" action="<?php echo $www_root?>/search.php">
            	<input type="hidden" style="display:none" name="search_type" value="keyword"/>
            	<input type="text" name="q" autofocus="autofocus" placeholder="Search"/>
            	<input type="submit" name="submit_search" id="itemsearch" value="Go!"/>
			</form>
			<a id="inline" href="#advanced" style="display:none">Advanced Search</a>

			<a href="<?php echo $www_root?>/search.php?search_type=advanced&q" class="button advanced-search">Advanced Search</a>
			
    	</div><!--end search-->
	</div><!--end header-content-->
</div><!--end header-->

<!-- Advanced search fancybox, start -->
<div style="display:none">
	<div id="advanced">
		<form method="get" action="<?php echo $www_root?>/search.php">

		<div class="left advanced-inputs">
			<div class="facet_set">
				<div class="heading">Advanced search terms</div>
			</div>
              <p>
              	<select name="search_type">
                      <option value="title_keyword">Title contains keyword(s)</option>
                      <option value="creator_keyword">Author contains keyword(s)</option>
                      <option value="lcsh_keyword">Subject contains keyword(s)</option>
                      <option value="keyword" selected="selected">Keyword(s) anywhere</option>
                  </select>
                  <input type="text" class="searchBox" name="q"/>
              </p>
              <!--<p id="addremove"><span class="addfield">add</span> / <span class="removefield">remove</span></p>-->
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
    'width' : 600,
    'height' : 400
  });
});

</script>