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

<!-- OLD FOOTER CODE BEGIN -->
<br />
</div> <!-- Closing div for "page_wrapper" found in header.php -->
<div id="footer">
<a href="expenses.php">Expenses</a> |
<a href="payroll.php">Payroll</a> |
<a href="repStructure.php">Rep Structure</a> |
<a href="reports.php">Reports</a> |
<a href="tradedata.php">Trade Data</a> |
<a href="#">Bug Tracker</a> |
<a href="javascript:;"  onClick="window.open('version.php','no','scrollbars=yes,width=300,height=200')" >Version Information</a> |
<a href="logout.php">Logout</a>
<br />
InterDev Inc. &copy; Copyright 2008 |
Built by <a href="http://www.interdevinc.com/"><img style="vertical-align:middle;" src="/img/idev_logo_mini.png" width="57" height="30" /></a>
</div>
<!-- OLD FOOTER CODE END -->

</div>
    <footer>

    </footer>
  </div> <!--! end of #container -->


  <!-- JavaScript at the bottom for fast page loading -->

  <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
  <script>window.jQuery || document.write('<script src="includes/js/libs/jquery-1.6.2.min.js"><\/script>')</script>
  <script src="includes/js/libs/jquery-ui-1.8.16.custom.min.js"></script>
  <script src="includes/js/libs/webtoolkit.base64.min.js"></script>
  <script src="includes/js/libs/json2.min.js"></script>
  
  <script src="includes/js/compiledlibs/dbwrapper.js"></script>
  <script src="includes/js/compiledlibs/site-form-validations.js"></script>
  <script src="includes/js/compiledlibs/ajax-data-calls.js"></script>
  <script src="includes/js/mylibs/test.js"></script>
            
  <?php
  $parts = Explode('/', $_SERVER["PHP_SELF"]);
  
  // USED FOR JQUERYUI DATE PICKER IN tradedata.php page.
  if ($parts[count($parts) - 1] === 'tradedata.php'):
  ?>
  <script>
      $(function() {
          $("#searchDateStart").datepicker({
              dateFormat: 'yymmdd',
              showOn: 'both',
              buttonImage: 'img/cal.gif',
              buttonImageOnly: true
          });
      
          $("#searchDateEnd").datepicker({
              dateFormat: 'yymmdd',
              showOn: 'both',
              buttonImage: 'img/cal.gif',
              buttonImageOnly: true
          });
      });
  </script>
  <?php elseif (($parts[count($parts) - 1] === 'repStructure.php')): ?>
      <script src="includes/js/compiledlibs/rep-structure-broker-joint.js"></script>
      <script src="includes/js/compiledlibs/rep-structure-broker-override.js"></script>
      <script src="includes/js/compiledlibs/rep-structure-broker-status.js"></script>
      <script src="includes/js/compiledlibs/rep-structure-payout-percent.js"></script>
      <script src="includes/js/compiledlibs/rep-structure-sales-assistant.js"></script>
  <?php endif; ?>
  
  <!-- scripts concatenated and minified via ant build script-->
  <script defer src="includes/js/plugins.js"></script>
  <script defer src="includes/js/script.js"></script>
  <!-- end scripts-->

  <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 7 ]>
    <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->
  
</body>
</html>
