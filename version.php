<?php
/**
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[0] = ""; //site
$SITEVARS[1] = ""; //domain
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Version Information"; //pagetitle
$SITEVARS[4] = "user"; //user access
$SITEVARS[5] = "payrollData"; //permission access
$SITEVARS[6] = "http://payroll.com"; //url

require_once("/opt/lampp/htdocs/".$SITEVARS[0]."/includes/config.php");
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$database = new DatabaseConnection();
$database->setConnectionSettings("appAuth");
$database->openConnection();
$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
$database->closeConnection();
if (!$hasAccess) {
	$init->loadInitHTML();
	$init->printMessage("accessRights");
}
$init->loadComponent("default");

$versInfo = $init->getVersionInfo();
echo '<a href="http://www.interdevinc.com/"><img align="center" width="150" height="78" src="/img/gappidevlogo.gif" border=0 /></a><br />';
echo "$versInfo[0]<br />";
echo "<a href=\"$versInfo[1]\">$versInfo[1]</a><br />";
echo "<a href=mailto:\"$versInfo[2]\">$versInfo[2]</a><br />";
echo "$versInfo[3]<br />";
echo "$versInfo[4]<br />";
