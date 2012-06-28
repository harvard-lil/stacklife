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
    <?php require_once('includes/header.php');?>
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
        		
        		<div class="result-hits-container"></div>	
        		
        		<div id="results">
            		<table width="100%" cellspacing="0" id="searchresults">
            			<thead id="search_results_header"><tr><th id="title_sort" class="sort_heading">Title</th><th id="creator" class="sort_heading">Author</th><th id="pub_date" class="sort_heading">Year</th><th id="shelfrank" class="sort_heading sortable score">ShelfRank<span class="search-arrow-down"></span></th><th></th></tr></thead>
            			<tbody id="search_results_body"></tbody>
            		</table> 
				</div><!-- end results div -->
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
</body>
</html>
