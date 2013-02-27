<?php
global $TYPEKIT_KEY;
global $GOOGLE_ANALYTICS;
global $GOOGLE_ANALYTICS_DOMAIN;
$tracker = $GOOGLE_ANALYTICS[0];

echo <<<EOF
  <link rel="author" href="$www_root/humans.txt" />
  <link rel="icon" href="$www_root/images/favicon.ico" type="image/x-icon" />
  
  <link rel="stylesheet" href="$www_root/css/jquery.fancybox-1.3.4.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/css/shelflife.theme.css" type="text/css" />

  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
  <script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js"></script>

  <script type="text/javascript" src="$www_root/stackview/jquery.stackview.min.js"></script>
  <script type="text/javascript" src="$www_root/js/handlebars.js"></script>
  <script type="text/javascript" src="$www_root/js/jquery.fancybox-1.3.4.pack.js"></script>
  $TYPEKIT_CODE
    <link rel="stylesheet" href="$www_root/css/bootstrap.css" type="text/css" />	

  <link rel="stylesheet" href="$www_root/css/template.css" type="text/css" />
  <link rel="stylesheet" href="$www_root/stackview/jquery.stackview.css" type="text/css" />

  <!--[if IE]>
        <link rel="stylesheet" href="$www_root/stackview/ie.stackview.css" type="text/css" />
        <link rel="stylesheet" href="$www_root/css/ie.template.css" type="text/css" />
  <![endif]-->
  

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '$tracker']);
  _gaq.push(['_setDomainName', '$GOOGLE_ANALYTICS_DOMAIN']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
EOF;
?>
