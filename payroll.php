<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Broker Information"; //pagetitle

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
$init->loadLibrary("CommissionDates");
$init->loadComponent("default");

$init->loadModel("Broker");
$init->loadModel("Payroll");
$init->loadModel("PayrollTrade");
$init->loadModel("SalesAssistant");
$init->loadDAO("Broker");
$init->loadDAO("Payroll");
$init->loadDAO("SalesAssistant");

$todayIs = date('Ymd');
?>

<?php $init->loadInitHTML(); ?>

<script type="text/javascript">
function openNewWindow() {
	if (validateForm(this)) {
		window.open('', 'payrollView', 'width=640, height=640, status=yes, resizable=yes, scrollbars=yes');
	} else {
		return false;
	}
}

function validateForm() {
	if (document.doPayroll.repNum.selectedIndex == 0) {
		alert ("You need to select an Individual.");
		return false;
	}

	if (document.doPayroll.settleDateEnd.selectedIndex == 0) {
		alert ("You need to select Commission Month.");
		return false;
	}

	if (document.doPayroll.payrollAction.selectedIndex == 0) {
		var answer = confirm("Are you sure you want to Submit Payroll for the selected Individual?");
		if (!answer){
			return false;
		}
	} else if (document.doPayroll.payrollAction.selectedIndex == 1) {
		var answer = confirm("Are you sure you want to Unsubmit Payroll for the selected Individual?");
		if (!answer){
			return false;
		}
	}
	return true;
}
</script>

<div id="payroll-selection">
	<form name="doPayroll" action="views/payrollPrintView.php" target="payrollView" onsubmit="return openNewWindow(this)" method="GET">
		<table>
			<tr>
				<td>View Payroll</td>
			</tr>
		</table>
		
		<table border=1>
			<tr>
				<td>Select an Individual:</td>
				<td><select name=repNum> <?php HtmlComponents::repNameNumDropDown(); ?> </select></td>
			</tr>
			<tr>
				<td>Select the Commission Month:</td>
				<td><select name=settleDateEnd> <?php HtmlComponents::dateDropDown("payroll"); ?> </select></td>
			</tr>
			<tr>
				<td>Select an Action:</td>
				<td>
					<select name=payrollAction> 
						<option value="submitPayroll">Submit Payroll</option>
						<option value="unsubmitPayroll">Unsubmit Payroll</option>
					</select>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><input type=submit name=submit value=submit></td>
			</tr>
		</table>
	</form>
</div>

<?php $init->printFooter(); ?>

