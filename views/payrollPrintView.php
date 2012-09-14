<?php
/**
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Payroll Print View"; //pagetitle

$pageTitle = "Payroll Management System - Landing";
$role = "user";
$resource = "payrollData";

require_once '../includes/config.php';
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
//$init->loadController($page);
?>

<html>
<head>
<?php echo "<title>Broker: " . $_GET['repNum'] . " Paystub</title>"; ?>
<link href="/includes/css/main.css" rel="stylesheet" type="text/css" />
</head>
<body id="payroll-paystub-body">
<div id="payroll-paystub-content"><?php
if (isset($_GET['submit'])) {
	$payrollDao = new PayrollDAO();
	if ($_GET['payrollAction'] == "submitPayroll") {
		$payroll = new Payroll();
		$payroll->setRepNumber($_GET['repNum']);
		$payroll->setCommissionMonth($_GET['settleDateEnd']);
		$payroll = $payrollDao->submitPayroll($payroll);
		displayPaystub($payroll, $payrollDao);
	} else if ($_GET['payrollAction'] == "unsubmitPayroll") {
		$payrollDao->unsubmitPayroll($_GET['repNum'], $_GET['settleDateEnd']);
	}
}
?></div>
</body>
</html>

<?php
function displayPaystub($payroll, $payrollDao) {
	echo "<div id='payroll-paystub-main'>";
	echo "<table id='payroll-paystub-table'><tr><th colspan=8>A.E. #".$payroll->getRepNumber()." - BRANCH</th></tr>";
	echo "<tr bgcolor=#CCCCCC><th colspan=8><a href='newPayroll.php'></a><br />
		COMMISSIONS AND EXPENSES FOR " . $payroll->getRepName() . "<br />
		FOR THE MONTH OF " . $payroll->getMonthLabel() . "</th><tr>";
	echo "<tr><td colspan=5>GROSS COMMISSIONS FROM SALES RECORD:</td><td id='number-column'>".$payroll->getGrossFromSales()."</td></tr>";
	for ($i = 0; $i < count($payroll->getDealData()); $i++) {
		$dealData = $payroll->getDealData();
		if ($dealData[$i]['amount']) {
			echo "<tr><td colspan=2>DEAL:</td><td colspan=3>" . $dealData[$i]['misc'] . "</td><td id='number-column'>".$payrollDao->formatAsCurrency($dealData[$i]['amount'])."</td></tr>";
		}
	}
	echo "<tr><td colspan=5>TOTAL ADJUSTED GROSS:</td><td id='number-column'>".$payroll->getTotalAdjustedGross()."</td></tr>";
	echo "<tr><td colspan=8></td></tr>";
	echo "<tr><td colspan=4>BROKER COMMISSION PAYOUT:</td><td colspan=3></td><td id='number-column'>".$payroll->getPayoutPercent()."</td></tr>";
	echo "<tr><td colspan=4>NET COMMISSIONS FROM SALES RECORD:</td><td colspan=3></td><td id='number-column'>".$payroll->getNetFromSales()."</td></tr>";
	if ($payroll->getOverrideTotal() != '$0.00') {
		echo "<tr><td colspan=4>NET COMMISSIONS FROM OVERRIDE:</td><td colspan=3></td><td id='number-column'>".$payroll->getOverrideTotal()."</td></tr>";
	}
	for ($i = 0; $i < count($payroll->getMiscCreditData()); $i++) {
		$miscCreditData = $payroll->getMiscCreditData();
		if ($miscCreditData[$i]['amount']) {
			echo "<tr><td></td><td>MISC CREDIT:</td><td colspan=5>".$miscCreditData[$i]['misc']."</td><td id='number-column'>".$payrollDao->formatAsCurrency($miscCreditData[$i]['amount'])."</td></tr>";
		}
	}
	$optionData = $payroll->getOptionData();
	if ($optionData['amount']) {
		echo "<tr><td></td><td colspan=4>ADJUSTMENT-OPTION CONTRACTS:</td><td id='number-column'>".$optionData['contracts']."</td><td></td><td id='number-column'><font color=red>".$payrollDao->formatAsCurrency($optionData['amount'])."</font></td></tr>";
	}
	echo "<tr><td></td><td colspan=6>NET PAYABLE BEFORE EXPENSES:</td><td id='number-column'><span class=\"double_underline\">".$payroll->getNetBeforeExpenses()."</span></td></tr>";
	echo "<tr><td colspan=8>EXPENSE SUMMARY:</td></tr>";
	for ($i = 0; $i < count($payroll->getExpenseData()); $i++) {
		$expenseData = $payroll->getExpenseData();
		echo "<tr><td></td><td colspan='2'>".$expenseData[$i][0].":</td><td>".$expenseData[$i][2]."</td><td> - </td><td id='number-column'>".$payrollDao->formatAsCurrency($expenseData[$i][1])."</td><td colspan='2'></td></tr>";
	}
	echo "<tr><td colspan=4>TOTAL EXPENSES:</td><td colspan=3></td><td id='number-column'><span class=\"double_underline\">(".$payrollDao->formatAsCurrency($payrollDao->sumExpenses($payroll->getExpenseData())).")</span></td></tr>";
	for ($i = 0; $i < count($payroll->getOwingData()); $i++) {
		$owingData = $payroll->getOwingData();
		if ($owingData[$i]['amount']) {
			echo "<tr><td colspan=5>LESS LOSSES OWING FROM PREVIOUS MONTH:</td><td colspan=2></td><td id='number-column'>(".$payrollDao->formatAsCurrency($owingData[$i]['amount']).")</td></tr>";
		}
	}
	echo "<tr><td colspan=4>AMOUNT PAYABLE:</td><td colspan=3></td><td id='number-column'>".$payroll->getAmountPayable()."</td></tr>";
	for ($i = 0; $i < count($payroll->getAdvanceData()); $i++) {
		$advanceData = $payroll->getAdvanceData();
		if ($advanceData[$i]['amount']) {
			echo "<tr><td>LESS:</td><td colspan='2'>".$advanceData[$i]['misc']."</td><td id='number-column'>".$payrollDao->formatAsCurrency($advanceData[$i]['amount'])."</td><td></td></tr>";
		} else {
			echo "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td colspan='7'></td></tr>";
		}
	}
	echo "<tr><td colspan=4>TOTAL OF CHECK OR AMOUNT OWING:</td><td colspan=3></td><td id='number-column'><span class=\"double_underline\">".$payroll->getTotalCheck()."</span></td></tr>";
	echo "</table>";
	echo "</div>";
}
?>
