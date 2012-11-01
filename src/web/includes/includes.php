
<?php
global $TYPEKIT_KEY;
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
EOF;
?>
