<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

// Site Constants
$SITEVARS[2] = 'payroll'; //pageid
$SITEVARS[3] = "Payroll Management System - Expenses"; //pagetitle
$SITEVARS[4] = "user"; //user access
$SITEVARS[5] = "payrollData"; //permission access

require_once '../../includes/config.php';
$init = new InitializeSite($SITEVARS);
$init->loadLibrary("DatabaseConnection");
$init->loadLibrary("AuthenticationAndAccess");
$aaa = new AuthenticationAndAccess($SITEVARS[4], $SITEVARS[5]);
$aaa->checkSession();
$hasAccess = $aaa->checkPageAccess();
if (!$hasAccess) {
	$init->loadInitHTML();
	$init->printMessage("accessRights");
	$init->printFooter();
	exit();
}
?>

<?php


if (isset($_GET['e']) && isset($_GET['m'])) {
	$database = new DatabaseConnection();
	$database->setConnectionSettings("tradeDataRead");
	if ($GLOBALS['DEBUG']) {
		$database->changeToDevelopmentDatabase();
	}
	$database->openConnection();

	$e = $_GET['e'];
	$m = $_GET['m'];

	$dabCatQuery = "SELECT DISTINCT type FROM dealAdvBal";
	$dabCatResult = @mysql_query($dabCatQuery);
	$instype = "Expense";
	while ($row = @mysql_fetch_array($dabCatResult, MYSQL_NUM)) {
		if ($e == $row[0]) {
			$instype = "DAB";
		}
	}

	if ($instype == "Expense") {
		$sql = "SELECT repNum, expense, amount, misc, expId FROM miscExpenses WHERE expense='".$e."' AND monthEndDate='".$m."'";
		//echo $sql;
	} else {
		$sql = "SELECT repNum, type, amount, misc, dabId FROM dealAdvBal WHERE type='".$e."' AND monthEndDate='".$m."'";
		//echo $sql;
	}

	$result = mysql_query($sql);
	$numResults = mysql_num_rows($result);
	if ($numResults > 0) {
		echo "<div id=\"expense-ajax-table\"><table><tr>
            <td>RepNum</td>
            <td>$instype</td>
            <td>Amount</td>
            <td>Misc</td>
            </tr>";
		$incre = 0;
		while ($ex = mysql_fetch_array($result, MYSQL_NUM)) {
			$delRep = "'".$ex[4]."|".$ex[0]."|".$ex[1]."'";
			echo "<tr>";
			echo "<td>" . $ex[0] . "</td>";
			echo "<td>" . $ex[1] . "</td>";
			echo "<td>" . $ex[2] . "</td>";
			echo "<td>" . $ex[3] . "</td>\n";
			echo "<td><form name='delExpense' action='' method=get >
                <input type=hidden name=delType value=$instype />
                <input type=hidden name=delId value=$ex[4] />
                <input type=submit name=delete value='Delete' /></form>\n</td>";
			echo "</tr>";
			$incre++;
		}
		echo "</table></div>";
	}
	$database->closeConnection();
	exit;
}

?>
