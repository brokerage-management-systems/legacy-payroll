<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Landing"; //pagetitle

$pageTitle = "Payroll Management System - Landing";
$role = "user";
$resource = "payrollData";

require_once realpath(dirname(__FILE__) . '/includes/config.php');
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$aaa = new AuthenticationAndAccess($role, $resource);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
if (!$hasAccess) {
	$init->loadInitHTML();
	$init->printMessage("accessRights");
}

$init->loadComponent("default");

?>

<?php
$init->loadInitHTML();

echo "<h4>Welcome to Payroll Management System ".$_COOKIE['loginuser'].", please choose a link below to start:</h4>";
echo '<h2>Payroll Management System:</h2>';
echo "Run <a href=reports.php>Reports</a>.<br />";
echo "View <a href=tradedata.php>Trade Totals</a>.<br />";
echo "Add <a href=submitExpense.php>Expenses</a> to Payroll .<br />";
echo "Generate Commission & <a href=payroll.php>Run Payroll</a>.<br />";
echo "<div align='right'><p>".$_COOKIE['loginuser']." ".$_COOKIE['logindate']." ".$_COOKIE['logintime']."</p></div>";

$init->printFooter();
?>
