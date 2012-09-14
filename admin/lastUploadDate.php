<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
/*
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 *  Payroll Management System
 *  Version 2.1
 */
### Page Initialization ###
// Const
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Last Data Upload Date"; //pagetitle
$SITEVARS[4] = "admin"; //access
$SITEVARS[5] = "payrollData"; //access
// Move somewhere else...
$tdReadParams = array(
    'host'           => 'localhost',
    'username'       => 'tradeDataRead',
    'password'       => '',
    'dbname'         => 'clearingdata'
);
$tdWriteParams = array(
    'host'           => 'localhost',
    'username'       => 'tradeDataWrite',
    'password'       => '',
    'dbname'         => 'clearingdata'
);
// Includes
require_once("/opt/lampp/htdocs/".$SITEVARS[0]."/includes/config.php");
// Create Object
$init = new InitializeSite($SITEVARS);
$init->startSession();
$init->checkPageAccess();
// Future use...
$className = null;
$init->loadClass($className);
$compName = null;
$init->loadComponent($compName);
$init->connectToDB($tdWriteParams);

if (isset($_GET['submit'])) {
    $trdData = new TradeData();
    $trdData->lastUploadDate($_GET['tradeDate']);
}
echo "<form action=".$_SERVER['PHP_SELF']." method=GET>";
echo "<input type=text name=tradeDate /><input type=submit name=submit />";
echo "</form>";

$init->printFooter();
?>
