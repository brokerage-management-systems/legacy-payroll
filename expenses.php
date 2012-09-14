<?php
/**
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Expenses"; //pagetitle
$SITEVARS[4] = "admin"; //user access
$SITEVARS[5] = "payrollData"; //permission access

require_once realpath(dirname(__FILE__) . '/includes/config.php');
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$database = new DatabaseConnection();
$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
if (!$hasAccess) {
	$init->loadInitHTML();
	$init->printMessage("accessRights");
}
$init->loadLibrary("Reports");
$init->loadLibrary("CommissionDates");
$init->loadComponent("default");

?>

<?php
$init->loadInitHTML();

/*
 *  Submit Expenses into miscExpenses
 */
if (isset($_GET['submit'])) {
	$database = new DatabaseConnection();
	$database->setConnectionSettings("tradeDataWrite");
	if ($GLOBALS['DEBUG']) {
		$database->changeToDevelopmentDatabase();
	}
	$database->openConnection();

	$nameQuery = "SELECT repNumber FROM Brokers WHERE repNumber IS NOT NULL AND active=1 ORDER BY repNumber ASC";
	//echo $nameQuery;
	$nameResult = @mysql_query($nameQuery);
	$rnCounter = 0;
	while ($repInfo = @mysql_fetch_array($nameResult, MYSQL_NUM)) {
		$rn[$rnCounter] = $repInfo[0];
		++$rnCounter;
	}

	$exp = $_GET['expense'];
	$counter = $_GET['counter'];

	//Add jointRep splits to individual rep
	for ($a = 0; $a < $counter; ++$a) {
		//echo $rn[$a]."<br />";
		$splitExpQuery = "SELECT mainRep, altRep, splitPercent FROM repNums WHERE type='JointRep' AND altRep='$rn[$a]'";
		//echo $splitExpQuery."<br />";
		$splitExpResult = mysql_query($splitExpQuery);
		if (mysql_num_rows($splitExpResult) > 0) {
			//echo $rn[$a]."<br />";
			while ($row = mysql_fetch_array($splitExpResult)) {
				$_GET['amt-'.$row[0]] += bcdiv($_GET['amt-'.$row[1]],$row[2],2);
			}
			$_GET['amt-'.$rn[$a]] = null;
		}
	}

	### If Expense and NOT Options ###
	if($exp == 'AMEX' || $exp == 'CABLE' || $exp == 'CAPITAL MARKETS' || $exp == 'CAR SERVICE' || $exp == 'COLD CALLERS' || $exp == 'ERROR ACCOUNT' || $exp == 'FEDERAL EXPRESS' || $exp == 'LEADS' || $exp == 'MISC EXPENSE' || $exp == 'POSTAGE' || $exp == 'REG T 30/80' || $exp == 'STATE FARM' || $exp == 'STATE REG' || $exp == 'WIRE FEES') { // Insert Expenses
		$expbuild = null;
		$insertEXP = "INSERT INTO miscExpenses (repNum, monthEndDate, expense, amount, misc) VALUES ";
		for ($a = 0; $a < $counter; ++$a) {
			if ($_GET['amt-'.$rn[$a]] != null) {
				$expbuild .= "('$rn[$a]','".$_GET['settleDateEnd']."','".$_GET['expense']."','".$_GET['amt-'.$rn[$a]]."','".$_GET['com-'.$rn[$a]]."'),";
			}
		}
		$expbuild = substr($expbuild,0,-1);
		$insertEXP .= $expbuild;
		//echo $insertEXP."<br />\n";
		$expResult = mysql_query($insertEXP);
		//echo mysql_errno($expResult) . ": " . mysql_error($expResult) . "<br />\n";
		if($expResult) {
			echo "<h3>Expenses for ".$_GET['expense']." - ".$_GET['settleDateEnd']." have been added.</h3>";
		}
		else {
			echo "<h3>Expense Submittion Error.</h3>";
		}
	}

	### If DEAL/ADVANCE/MISC###
	if($exp == 'ADVANCE' || $exp == 'DEAL' || $exp == 'MISC') // Insert DAB
	{
		$insertDAB = "INSERT INTO dealAdvBal (repNum, monthEndDate, type, amount, misc) VALUES";
		$dabbuild = "";
		for ($a = 0; $a < $counter; ++$a) {
			if ($_GET['amt-'.$rn[$a]] != null) {
				$dabbuild .= "('$rn[$a]','".$_GET['settleDateEnd']."','".$_GET['expense']."','".$_GET['amt-'.$rn[$a]]."','".$_GET['com-'.$rn[$a]]."'),";
			}
		}
		$dabbuild = substr($dabbuild,0,-1);
		$insertDAB .= $dabbuild;
		//echo $insertDAB."<br />";
		$dabResult = mysql_query($insertDAB);
		//echo mysql_errno($dabResult) . ": " . mysql_error($dabResult) . "\n";
		if($dabResult) {
			echo "<h3>".$_GET['expense']." - ".$_GET['settleDateEnd']." has been added.</h3>";
		}
		else {
			echo "<h3>Deal, Advance, or Misc Credit Submittion Error.</h3>";
		}
	}
	$database->closeConnection();
}

/*
 *  Delete Expense
 */
if (isset($_GET['delete'])) {
	$database = new DatabaseConnection();
	$database->setConnectionSettings("tradeDataWrite");
	if ($GLOBALS['DEBUG']) {
		$database->changeToDevelopmentDatabase();
	}
	$database->openConnection();
	
	$id = $_GET['delId'];
	switch ($_GET['delType']) {
		case 'Expense':
			$delQuery = "DELETE FROM miscExpenses WHERE expId='$id'";
			break;
		case 'DAB':
			$delQuery = "DELETE FROM dealAdvBal WHERE dabId='$id'";
			break;
		default:
			break;
	}
	//echo $delQuery;
	$delResult = @mysql_query($delQuery);
	if ($delResult) {
		echo "The Record has been Deleted.";
	} else {
		echo "The Record was not Deleted. Please contact: ";
		echo $init->getSupportEmail();
	}
	$database->closeConnection();
}
?>

<?php //$init->loadInitHTML(); ?>
<form id="expense-submission" method="get">
<div id="expense-header-title"><h3>Monthly Expenses</h3></div>
<div id="expense-main-table"><table>
	<tr>
		<td>Choose an Expense:</td>
		<td><select id="expense-submission-expense" name="expense">
			<option value=null>*Expense</option>
			<option value=ADVANCE>ADVANCE</option>
			<option value=DEAL>DEAL</option>
			<option value=MISC>MISC CREDIT</option>
			<option value=>---------</option>
			<?php HtmlComponents::expenseCatDropDown(); ?>
		</select></td>
		<td>Select Commission Month:</td>
		<td><select id="expense-submission-commission-month" name="settleDateEnd">
			<?php HtmlComponents::dateDropDown(); ?>
		</select></td>
	</tr>
	<tr>
		<td colspan=4>
		<div id="existingExpenseData"></div>
		</td>
	</tr>
	<tr>
		<td colspan="2">Broker</td>
		<td>Amount</td>
		<td>Comment</td>
	</tr>
	<?php
	$database = new DatabaseConnection();
	$database->setConnectionSettings("tradeDataRead");
	if ($GLOBALS['DEBUG']) {
		$database->changeToDevelopmentDatabase();
	}
	$database->openConnection();
	
	$nameQuery = "SELECT repNumber, firstName, lastName FROM Brokers WHERE repNumber IS NOT NULL AND active=1 ORDER BY repNumber ASC";
	//echo $nameQuery;
	$nameResult = @mysql_query($nameQuery);
	$counter = 0;
	while ($repInfo = @mysql_fetch_array($nameResult, MYSQL_NUM)) {
		echo "<tr><td colspan=\"2\"><input type=hidden value=rep-$repInfo[0]>$repInfo[0] - $repInfo[1] $repInfo[2]</td><td>$:<input size=8 type=text name=amt-$repInfo[0]></td><td><input type=text name=com-$repInfo[0]></td></tr>";
		++$counter;
	}
	echo "<input type=hidden name=counter value=$counter>";
	$database->closeConnection();
	?>
	<tr>
		<td><input type="submit" name="submit" value="Submit Expense"></td>
	</tr>
</table>
</div>
</form>

	<?php
	$init->printFooter();
	?>
