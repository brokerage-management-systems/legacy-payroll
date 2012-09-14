<?php

class BrokerStructureDAO
{
	private function changeRepNum($newRep, $branch, $acctNum, $settleDateStart, $settleDateEnd)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataWrite");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$changeRepUpdate = "UPDATE TRDREV_TD SET REGISTERED_REP_OWNING_REP_RR_09='$newRep'";
		$changeRepUpdate .= " WHERE BRANCH_01_a='$branch' AND ACCOUNT_NUMBER_01='$acctNum' AND SETTLEMENT_DATE_01 BETWEEN '$settleDateStart' AND '$settleDateEnd'";
		echo $changeRepUpdate;
		$changeRepResult = @mysql_query($changeRepUpdate);
		if ($changeRepResult) {
			echo "<h3>$acctNum Rep changed to: $newRep</h3>";
		} else {
			echo "<h3>Did not run query!</h3>";
		}

		$database->closeConnection();
	}
	
	public function repChange()
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataWrite");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		if (!empty($_GET['settleDateSelect'])) {
			$settleDateEnd = $_GET['settleDateSelect'];
			$newDate = new CommissionDates();
			$repChange = new RegRepInfo();
			$newDate->setDates($settleDateEnd);
			$settleDateStart = $newDate->dateInfo[0];
		}

		if (!empty($_GET['submit']) && !empty($_GET['settleDateSelect']) && !empty($_GET['acctNum']) && !empty($_GET['newRep']) ) {
			$tempAcct = explode("-",$_GET['acctNum']);
			$branch = $tempAcct[0];
			$acctNum = $tempAcct[1];
			$this->changeRepNum($_GET['newRep'],$branch,$acctNum,$settleDateStart,$settleDateEnd);
			$init->printFooter($pageID);
		}

		//view code... if what???
		echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET" >';
        echo '<input type=hidden name=modify value=repChange />
        <fieldset><legend>Rep Change:</legend><table>
        <tr><td>Commission Month: </td><td>
        <select name=settleDateSelect>';
		if (!empty($_GET['settleDateSelect']) && ($_GET['settleDateSelect'] != "NULL")) {
			echo $_GET['settleDateSelect'];
			echo "<option value=".$newDate->dateInfo[3].">".$newDate->dateInfo[2]."</option>";
			htmlComponents::dateDropDown();
		} else {
			htmlComponents::dateDropDown();
		}
		echo '</select></td>';

		if (!empty($_GET['settleDateSelect']) && ($_GET['settleDateSelect'] != 'NULL')) {
			echo "<tr><td>Acct#: </td><td><select name=acctNum>";
			if (!$_GET['acctNum']) {
				$acctNumsSelect = "SELECT DISTINCT BRANCH_01_a, ACCOUNT_NUMBER_01 FROM TRDREV_TD WHERE SETTLEMENT_DATE_01 BETWEEN '$settleDateStart' AND '$settleDateEnd' ORDER BY BRANCH_01_a ASC";
				$acctNumsResult = @mysql_query($acctNumsSelect);
				echo "<option value=></option>";
				while ($row = @mysql_fetch_array($acctNumsResult, MYSQL_NUM)) {
					$acctNum = $row[0] . "-" . $row[1];
					echo "<option value=$acctNum>$acctNum</option>";
				}
			} else {
				echo '<option value="'.$_GET['acctNum'].'">'.$_GET['acctNum'].'</option>';
				$acctNumsSelect = "SELECT DISTINCT BRANCH_01_a, ACCOUNT_NUMBER_01 FROM TRDREV_TD WHERE SETTLEMENT_DATE_01 BETWEEN '$settleDateStart' AND '$settleDateEnd' ORDER BY BRANCH_01_a ASC";
				$acctNumsResult = @mysql_query($acctNumsSelect);
				echo "<option value=></option>";
				while ($row = @mysql_fetch_array($acctNumsResult, MYSQL_NUM)) {
					$acctNum = $row[0] . "-" . $row[1];
					echo "<option value=$acctNum>$acctNum</option>";
				}
				echo "</select></td></tr>";
			}
		}

		if (!empty($_GET['settleDateSelect']) && ($_GET['settleDateSelect'] != 'NULL') && !empty($_GET['acctNum'])) {
			$tempAcct = explode("-",$_GET['acctNum']);
			$branch = $tempAcct[0];
			$acctNum = $tempAcct[1];
			$oldRepNumSelect = "SELECT DISTINCT REGISTERED_REP_OWNING_REP_RR_09 FROM TRDREV_TD WHERE BRANCH_01_a='$branch' AND ACCOUNT_NUMBER_01='$acctNum' AND SETTLEMENT_DATE_01 BETWEEN '$settleDateStart' AND '$settleDateEnd'";
			$oldRepResult = @mysql_query($oldRepNumSelect);
			while ($oldRep = @mysql_fetch_array($oldRepResult, MYSQL_NUM)) {
				echo "<tr><td>Old Rep: </td><td>$oldRep[0]</td>";
				echo "<input type=hidden name=oldRep value=$oldRep[0]>";
			}
			echo "</tr><tr><td>New Rep: </td><td><select name=newRep>";
			$newRepNumSelect = "SELECT DISTINCT REGISTERED_REP_OWNING_REP_RR_09 FROM TRDREV_TD WHERE SETTLEMENT_DATE_01 BETWEEN '$settleDateStart' AND '$settleDateEnd' ORDER BY REGISTERED_REP_OWNING_REP_RR_09 ASC";
			$newRepResult = @mysql_query($newRepNumSelect);
			echo "<option value=></option>";
			while ($newRep = @mysql_fetch_array($newRepResult, MYSQL_NUM)) {
				$repChange->setRepName($newRep[0]);
				$repName = NULL;
				$repName = $repChange->repName;
				echo "<option value=$newRep[0]>$newRep[0] - $repName</option>";
			}
			echo "</select></td></tr>";
		}

		echo '</tr><tr><td><input type=submit name=submit value=submit> <a href="javascript: window.close();">Cancel</a></td></tr>
        </table>
        </fieldset>
        </form><br />';
		
		$database->closeConnection();
	}
		
}