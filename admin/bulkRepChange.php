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
$SITEVARS[3] = "Payroll Management System - Bulk Rep Change"; //pagetitle
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

//$acctNumArray[0] = "";
//$newRepNum = "";
//$settleDateStart = "";
//$settleDateEnd = "";
//
//for ($a = 0; $a < count($acctNumArray); ++$a) {
//    $tempAcct = explode("-",$acctNumArray[$a]);
//    $branch = $tempAcct[0];
//    $acctNum = $tempAcct[1];
//    RegRepInfo::changeRepNum($newRepNum,$branch,$acctNum,$settleDateStart,$settleDateEnd);
//}

$init->printFooter();
?>
