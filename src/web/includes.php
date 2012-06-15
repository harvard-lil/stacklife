
<?php
echo <<<EOF
  <link rel="author" href="$www_root/humans.txt" />
  <link rel="icon" href="$www_root/favicon.ico" type="image/x-icon" />
  
  <link rel="stylesheet" href="$www_root/css/start/jquery-ui-1.8.2.custom.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/jquery.fancybox-1.3.4.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/shelflife.theme.css" type="text/css" />

  <!--[if IE]>
        <link rel="stylesheet" href="$www_root/css/ie.css" type="text/css" />
  <![endif]-->

  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
  <script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js"></script>

  <script type="text/javascript" src="$www_root/js/selectmenu.js"></script>
  <script type="text/javascript" src="$www_root/js/stackscroller.js"></script>
  <script type="text/javascript" src="$www_root/js/ba-whatevcache.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.infinitescroller.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.mousewheel.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.tabSlideOut.v2.0.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.localscroll-1.2.7-min.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.scrollTo-1.4.2-min.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.fancybox-1.3.4.pack.js"></script>
  <script type="text/javascript" src="http://use.typekit.com/gre3ysx.js"></script>
  <script type="text/javascript">try{Typekit.load();}catch(e){}</script>

  <script src="/s/js/x/underscore.js" type="text/javascript"></script>
  <script src="/s/js/x/jquery.tmpl.js" type="text/javascript"></script>
  <script src="/s/js/x/json2.js" type="text/javascript"></script>
  <script src="/s/js/x/backbone.js" type="text/javascript"></script>
  <script src="/s/js/ss.js" type="text/javascript"></script>

<link rel="stylesheet" href="$www_root/css/template.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/stackstyle.css" type="text/css" />
  
  <title> $title_without_author | ShelfLife</title>
EOF;
?>
