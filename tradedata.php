<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Trade Data"; //pagetitle
$SITEVARS[4] = "user"; //user access
$SITEVARS[5] = "payrollData"; //permission access

require_once realpath(dirname(__FILE__) . '/includes/config.php');
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
if (!$hasAccess) {
	$init->loadInitHTML();
	$init->printMessage("accessRights");
}
$init->loadComponent("default");
$init->loadLibrary("CommissionDates");

$init->loadModel("Account");
$init->loadModel("Broker");
$init->loadModel("Trade");
$init->loadDAO("Account");
$init->loadDAO("Broker");
$init->loadDAO("Trade");

?>

<?php $init->loadInitHTML(); ?>

<?php include_once 'views/tradesearchform.phtml'; ?>

<?php 
if (isset($_GET['submit'])) {
	$tradeDao = new TradeDAO();
	$tradeDao->runSearchByCriteria();
}
?>

<?php
$init->printFooter();
?>
