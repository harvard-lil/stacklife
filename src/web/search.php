<?php 
  ini_set("display_errors", 1); 
  error_reporting(E_ALL ^ E_NOTICE);

  require_once ('../../etc/sl_ini.php');
  
  $page = 'search';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php
  include_once ('includes/includes.php');
  echo <<<EOF
  <link rel="stylesheet" href="$www_root/css/ui.slider.extras.css" type="text/css" />

  <script type="text/javascript" src="$www_root/js/selectToUISlider.jQuery.js"></script> 
  <script charset="utf-8" type="text/javascript" src="$www_root/js/search.js"></script>

  <title></title>
EOF;
?>  
<script type="text/javascript">

var www_root = '<?php echo $www_root ?>';
 
</script>  

</head>
<body>
<div id="wrapper">
    <?php require_once('includes/logo.php');?>
    <?php require_once('includes/searchbox.php');?>
    <div class="search-container group">
		<div class="search-container-content group"> 
			<div id="navigation">
             	 <span id="arrows">
            		<div class="prev-page" alt="previous books button"></div>
            		<div class="subject-hits empty"></div>
            		<div class="next-page" alt="next books button"></div>
            	 </span>	
        	</div> <!-- end navigation -->  
        	
    		<div class="search-results rounded-corners">
    			<div id="facet_bread_crumb_container"></div>
        		
        		<div id="result-hits-container"></div>
        		
        		<div id="results"></div> <!-- end results div -->
        		
			</div><!-- end search-results div -->
			<div class="facet-box-container">
				<!-- Persistent controls (slider, query facets) -->
				<div class="facet-box highlight-border group">
					<div class="heading">Community Relevance</div>
            		<div id="persistent_controls"></div><!--end persistent_controls-->
            		
            		<div id="query_facets"></div><!--end query_facets-->
    			</div>
    			<!-- Dynamic controls (facets) -->
				<div class="facet-box group">
				<div class="heading">Refine</div>
          			<div class="facets"></div><!--end facets-->
    			</div><!-- end Dynamic controls -->
    		</div><!-- end facet-box-container -->
        </div><!--end container-content--> 
	</div><!-- end search-container -->
	
	
    </div><!--end wrapper-->
    
    <script id="result-hits-container-template" type="text/x-handlebars-template">
            {{#if num_found}}
    	        <p class="hits">Showing <span class="orange">{{start}}</span> to <span class="orange">{{showing}}</span> of <span class="orange">{{num_found}}</span> results for &ldquo;{{query}}&rdquo;</p>
    	    {{else}}
    	        <span class="apology">Sorry, no results. Perhaps try <a id="inline" href="#advanced" class="button advanced-search">advanced search</a>?</span>
    	    {{/if}}
	</script>
    
    <script id="search-results-template" type="text/x-handlebars-template">
        {{#if results.num_found}}
    	<table width="100%" cellspacing="0" id="searchresults">
    		<thead id="search_results_header">
    		    <tr>
    		        <th id="title_sort" class="sort_heading">Title</th>
    		        <th id="creator" class="sort_heading">Author</th>
    		        <th id="pub_date" class="sort_heading">Year</th>
    		        <th id="shelfrank" class="sort_heading sortable score">ShelfRank<span class="{{#get_sort_direction sort_direction}}{{this}}{{/get_sort_direction}}"></span></th>
    		        <th></th>
    		    </tr>
    		</thead>
    		<tbody id="search_results_body">
            {{#stripes results.docs "even" "odd"}}
    	        <tr class="result_row {{stripeClass}}">
    	            <td class="title-column"><a href="item/{{title_link_friendly}}/{{id}}">{{title}}</a></td>
    	            <td class="author-column">
    	            {{#first creator}}
    	                <a href="author/{{this}}">{{this}}</a>
    	            {{/first}}
    	            </td>
                    <td class="year-column">{{pub_date}}</td>
                    <td><span class="results-score color{{#heat shelfrank}}{{this}}{{/heat}}">{{#left_pad shelfrank}}{{this}}{{/left_pad}}</span></td>
    	        </tr>
    	    {{/stripes}}
    		</tbody>
    	</table>
    	{{/if}}
	</script>
	
    <script id="slider-container-template" type="text/x-handlebars-template">
		<div class="facet_heading">
		    Refine by ShelfRank
		    <fieldset>
		        <select name="valueA" id="valueA" style="display:none">
		            <option value="1" selected="selected">1</option>
		            <option value="10">10</option>
		            <option value="20">20</option>
		            <option value="30">30</option>
		            <option value="40">40</option>
		            <option value="50">50</option>
		            <option value="60">60</option>
		            <option value="70">70</option>
		            <option value="80">80</option>
		            <option value="90">90</option>
		            <option value="100">100</option>
		        </select>
		        <select name="valueB" id="valueB" style="display:none">
    		        <option value="1">1</option>
    		        <option value="10">10</option>
    		        <option value="20">20</option>
    		        <option value="30">30</option>
    		        <option value="40">40</option>
    		        <option value="50">50</option>
    		        <option value="60">60</option>
    		        <option value="70">70</option>
    		        <option value="80">80</option>
    		        <option value="90">90</option>
    		        <option value="100" selected="selected">100</option>
		        </select>
		    </fieldset>
    		<div id="total_score_slider">
    		    <div id="legend">
    		        <ul class="legend-box"><li class="color1"></li><li class="color2"></li><li class="color3"></li><li class="color4"></li><li class="color5"></li><li class="color6"></li><li class="color7"></li><li class="color8"></li><li class="color9"></li><li class="color10"></li>
    		        </ul>
    		    </div>
    		</div>
		</div>
</body>
</html>
