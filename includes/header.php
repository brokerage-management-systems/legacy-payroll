<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

if (!isset($pageID)) {
    header("HTTP/1.0 404 Not Found");
}
?>

<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">

  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/b/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <!-- Site Title -->
  <title><?php echo $pageTitle; ?></title>
        
  <meta name="description" content="Payroll Management System developed by InterDev Inc." />
  <meta name="keywords" content="payroll, management, system, broker, rep, gross" />
  <meta name="author" content="">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

  <!-- CSS: implied media=all -->
  <link rel="stylesheet" href="includes/css/ui-lightness/jquery-ui-1.8.16.custom.css">
  <!-- CSS concatenated and minified via ant build script-->
  <link rel="stylesheet" href="includes/css/style.css">

  <!-- <link href="includes/css/normalize.css" rel="stylesheet" /> -->  
  <!-- <link href="includes/css/less.css" rel="stylesheet" /> -->
  
  <!-- OLD HEADER CSS BEGIN -->
  <link href="includes/css/main.css" rel="stylesheet" />
  <!-- OLD HEADER CSS END -->
  
  <!-- end CSS-->

  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except for Modernizr / Respond.
       Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
       For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
  <script src="includes/js/libs/modernizr-2.0.6.min.js"></script>

  <!-- Google Ajax Api-->
  <script src="includes/js/libs/googlejsapi.js"></script>
  <script>
    //Load multiple Google packages:
    google.load('visualization', '1', {'packages':['piechart', 'table', 'linechart', 'areachart']});
  </script>
  
</head>
<body>

  <div id="container">
    <header>

    </header>
    <div id="main" role="main">

<!-- OLD BODY CODE BEGIN -->
        <div id="page_wrapper">
          <div id="header_wrapper">
            <div id="header">
			  <a href=index.php><h1>Branding</h1></a>
              <h2>Payroll Management System</h2>
            </div>
