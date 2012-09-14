<?php
/**
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'login'; //pageid $pageID = 'login';
$SITEVARS[3] = "Payroll Management System - Login"; //pagetitle
$SITEVARS[4] = "user"; // user access
$SITEVARS[5] = "payrollData"; //permission access

require_once realpath(dirname(__FILE__) . '/includes/config.php');
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("AuthenticationAndAccess");
AuthenticationAndAccess::checkSession();
setcookie("loginval", "", time()-3600, '/');
setcookie("loginuser", "", time()-3600, '/');
setcookie("logindate", "", time()-3600, '/');
setcookie("logintime", "", time()-3600, '/');
AuthenticationAndAccess::doLogout();
header("Location: login.php");
?>
