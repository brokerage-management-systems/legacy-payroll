<?php

//TODO Decouple code, use MVC structure

class RegRepInfo {

	private $repList;
	var $mainRep;
	var $jointRep;
	var $override;
	var $overrideRep;
	var $repName;
	var $saPercent;
	var $decimal = ".";
	var $dabMisc;
	var $payoutVals;
	var $extraSpaces = "&nbsp;&nbsp;&nbsp;&nbsp;";

	public function setRepList() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$listQuery = "SELECT repNum FROM Users WHERE repNum IS NOT NULL AND firstName!='JointRep' ORDER BY repNum ASC";
		//echo $listQuery;
		$listResult = @mysql_query($listQuery);
		$incrementor = 0;
		while ($row = @mysql_fetch_array($listResult, MYSQL_NUM)) {
			$this->repList[$incrementor] = $row[0];
			//echo $this->repList[$incrementor]."<br />";
			++$incrementor;
		}
		$database->closeConnection();
	}

	public function getRepList() {
		return $this->repList;
	}

	public function setMainRepNum($repNum) {
		$this->mainRep = $repNum;
		//echo $this->mainRep . "<br />";
	}

	public function getMainRepNum() {
		return $this->mainRep;
	}

	public function setRepName($repNum) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$nameQuery = "SELECT firstName, lastName FROM Users WHERE repNum='$repNum'";
		//echo $nameQuery;
		$nameResult = @mysql_query($nameQuery);
		while ($repFLName = @mysql_fetch_array($nameResult, MYSQL_NUM)) {
			$this->repName = $repFLName[0] . " " . $repFLName[1];
		}
		//echo $regRep->repName . "<br />";
		$database->closeConnection();
	}

	public function getRepName() {
		return $this->repName;
	}

	public function setJointRepNum($repNum) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$repNumQuery = "SELECT altRep, type, splitPercent, misc FROM repNums WHERE mainRep='$repNum' AND type='JointRep'";
		//echo $repNumQuery;
		$repNumResult = @mysql_query($repNumQuery);
		if ($repNumResult) {
			$counter =  0;
			while ($repNums = mysql_fetch_array($repNumResult, MYSQL_NUM)) {
				$this->jointRep[$counter][0] = $repNums[0];
				$this->jointRep[$counter][1] = $repNums[2];
				$counter++;
			}
		}
		/*$numJointReps = count($this->jointRep);
		 for ($i = 0; $i < $numJointReps; $i++) {
		 echo $this->jointRep[$i][0] . " - " . $this->jointRep[$i][1] . "<br />";
		 }*/
		$database->closeConnection();
	}

	public function getJointRepNum() {
		return $this->jointRep;
	}

	public function setOverrideRepNum($repNum) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$repNumQuery = "SELECT altRep, type, splitPercent, misc FROM repNums WHERE mainRep='$repNum' AND type='Override'";
		//echo $repNumQuery . "<br />";
		$repNumResult = @mysql_query($repNumQuery);
		if ($repNumResult) {
			$counter = 0;
			while ($repNums = mysql_fetch_array($repNumResult, MYSQL_NUM)) {
				$this->overrideRep[$counter][0] = $repNums[0];
				$this->overrideRep[$counter][1] = $repNums[2];
				$counter++;
			}
		}
		/*$numOverrideReps = count($this->overrideRep);
		 for ($i = 0; $i < $numOverrideReps; $i++) {
		 echo $this->overrideRep[$i][0] . " - " . $this->overrideRep[$i][1] . "<br />";
		 }*/
		$database->closeConnection();
	}

	public function getOverrideRepNum() {
		return $this->overrideRep;
	}

	public function setOverride($repNum, $settleDateEnd) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$selectQuery = "SELECT amount FROM monthEndPayoutOverride WHERE monthEndDate='$settleDateEnd' AND repNum='$repNum' AND type='Override'";
		//echo $selectQuery . "<br />";
		$selectResult = @mysql_query($selectQuery);
		$override = @mysql_fetch_row($selectResult);
		$this->override = $override[0];
		
		$database->closeConnection();
	}

	public function getOverride() {
		return $this->override;
	}

	public function setDealAdvBal1($repNum, $settleDateStart, $settleDateEnd, $type) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$selectQuery = "SELECT type, amount, misc FROM dealAdvBal";
		$selectQuery .= " WHERE repNum='$repNum' AND monthEndDate BETWEEN '$settleDateStart' AND '$settleDateEnd' AND type='$type'";
		//echo $selectQuery . "<br />";
		$selectResult = mysql_query($selectQuery);
		$counter = 0;
		switch ($type) {
			case 'DEAL':
				$this->dabMisc = array(null);
				while($row = mysql_fetch_array($selectResult)) {
					//echo $counter."<br />";
					$this->dabMisc[$counter][0] = $row[0];
					$this->dabMisc[$counter][1] = $row[1];
					$this->dabMisc[$counter][2] = $row[2];
					$counter++;
					//echo $this->dabMisc[$counter][0] . " | " . $this->dabMisc[$counter][1] . " | " . $this->dabMisc[$counter][2] . "<br />";
				}
				break;
			case 'MISC':
				$this->dabMisc = array(null);
				while($row = mysql_fetch_array($selectResult)) {
					//echo $counter."<br />";
					$this->dabMisc[$counter][0] = $row[0];
					$this->dabMisc[$counter][1] = $row[1];
					$this->dabMisc[$counter][2] = $row[2];
					$counter++;
					//echo $this->dabMisc[$counter][0] . " | " . $this->dabMisc[$counter][1] . " | " . $this->dabMisc[$counter][2] . "<br />";
				}
				break;
			case 'OWING':
				$this->dabMisc = array(null);
				while($row = mysql_fetch_array($selectResult)) {
					//echo $counter."<br />";
					$this->dabMisc[$counter][0] = $row[0];
					$this->dabMisc[$counter][1] = $row[1];
					$this->dabMisc[$counter][2] = $row[2];
					$counter++;
					//echo $this->dabMisc[$counter][0] . " | " . $this->dabMisc[$counter][1] . " | " . $this->dabMisc[$counter][2] . "<br />";
				}
				break;
			case 'ADVANCE':
				$this->dabMisc = array(null);
				while($row = mysql_fetch_array($selectResult)) {
					//echo $counter."<br />";
					$this->dabMisc[$counter][0] = $row[0];
					$this->dabMisc[$counter][1] = $row[1];
					$this->dabMisc[$counter][2] = $row[2];
					$counter++;
					//echo $this->dabMisc[$counter][0] . " | " . $this->dabMisc[$counter][1] . " | " . $this->dabMisc[$counter][2] . "<br />";
				}
				break;
			default :
				$this->dabMisc = "NA";
				break;
		}
		$database->closeConnection();
	}

	public function getDealAdvBal1() {
		return $this->dabMisc;
	}

	public function setPayout($repNum, $payout, $todayIs, $settleDateEnd, $viewPayroll) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataWrite");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		if ($viewPayroll) {
			$selectQuery = "SELECT amount, percent FROM monthEndPayoutOverride WHERE monthEndDate='$settleDateEnd' AND repNum='$repNum' AND type='Payout'";
			//echo $selectQuery . "<br />";
			$selectResult = @mysql_query($selectQuery);
			$this->payoutVals = @mysql_fetch_row($selectResult);
			//echo $this->payoutVals[0]." | ".$this->payoutVals[1];
		}

		if (!$viewPayroll) {
			$repPayout = 0;
			$payoutPercent = 0;
			// Get values from database do calculations.
			$selectPayStruct = "SELECT minAmt, maxAmt, payPercent FROM payoutStructure WHERE repNum='$repNum' ORDER BY payPercent ASC";
			//echo $selectPayStruct . "<br />";
			$selectPayResult = @mysql_query($selectPayStruct);
			//echo $selectPayResult;
			if ($selectPayResult) {
				while ($row = @mysql_fetch_array($selectPayResult)) {
					if ($payout > $row[0] && $payout < $row[1]) {
						$repPayout = bcmul($payout, $row[2], 2);
						$payoutPercent = $row[2] * 100;
						$payoutPercent .= "%";
						//echo $row[2] . " x " . $payout . " = " . $repPayout . " | " . $payoutPercent . " Payout<br />";
					}
				}
			}
			$this->payoutVals[0] = $repPayout;
			$this->payoutVals[1] = $payoutPercent;

			if ($todayIs > $settleDateEnd) {
				$selectQuery = "SELECT * FROM monthEndPayoutOverride WHERE monthEndDate='$settleDateEnd' AND repNum='$repNum' AND type='Payout'";
				//echo $selectQuery . "<br />";
				$selectResult = @mysql_query($selectQuery);
				$wasSubmited = @mysql_num_rows($selectResult);
				if ($wasSubmited < 1) {
					// Add values to database...
					$submitQuery = "INSERT INTO monthEndPayoutOverride (repNum, monthEndDate, percent, amount, type, misc) VALUES ('$repNum', '$settleDateEnd', '$payoutPercent', '$repPayout', 'Payout', '')";
					//echo $submitQuery . "<br />";
					$submitResult = @mysql_query($submitQuery);
					//echo $submitResult;
				}
			}
		}
		$database->closeConnection();
	}

	public function getPayout() {
		return $this->payoutVals;
	}

	public function setSAPercent($repNum) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$saQuery = "SELECT percent FROM salesAssistantData WHERE repNum='$repNum'";
		//echo $saQuery;
		$saResult = @mysql_query($saQuery);
		while ($saRow = @mysql_fetch_array($saResult)) {
			$this->saPercent = $saRow[0];
		}
		//echo $this->saPercent . "<br />";
		$database->closeConnection();
	}

	public function getSAPercent() {
		return $this->saPercent;
	}

	public function addDeleteRep($repNum, $type, $firstName, $lastName) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataWrite");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		if ($type == 'delRep') {
			$termQuery = "UPDATE Users SET active=0 WHERE repNum='$repNum'";
			//echo $termQuery;
			$termResult = @mysql_query($termQuery);
			// Switch back to clearingdata database...
			mysql_select_db("clearingdata") or die(mysql_error());

			//Remove all rep payout info
			//Delete Payout
			$deletePOQuery = "DELETE FROM payoutStructure WHERE repNum=$repNum";
			//echo $deletePOQuery."<br />";
			$deletePOResult = mysql_query($deletePOQuery);
			//Delete Joint Reps
			$deleteJRMQuery = "DELETE FROM repNums WHERE mainRep=$repNum AND type='JointRep'";
			//echo $deleteJRMQuery."<br />";
			$deleteJRMResult = mysql_query($deleteJRMQuery);
			$deleteJRAQuery = "DELETE FROM repNums WHERE altRep=$repNum AND type='JointRep'";
			//echo $deleteJRAQuery."<br />";
			$deleteJRAResult = mysql_query($deleteJRAQuery);
			//Delete Override
			$deleteOMQuery = "DELETE FROM repNums WHERE mainRep=$repNum AND type='Override'";
			//echo $deleteOMQuery."<br />";
			$deleteOMResult = mysql_query($deleteOMQuery);
			$deleteOAQuery = "DELETE FROM repNums WHERE altRep=$repNum AND type='Override'";
			//echo $deleteOAQuery."<br />";
			$deleteOAResult = mysql_query($deleteOAQuery);
			//Delete Sales Assist
			$deleteSAQuery = "DELETE FROM salesAssistantData WHERE repNum=$repNum";
			//echo $deleteSAQuery."<br />";
			$deleteSAResult = mysql_query($deleteSAQuery);
			echo "Deleted Rep";
			echo "<script language=JavaScript> window.opener.location.reload(true); window.close(); </script>";
		}

		if ($type == 'addRep') {
			$selectQuery = "SELECT repNum, firstName, lastName FROM Users WHERE repNum='$repNum' AND firstName='$firstName' AND lastName='$lastName'";
			//echo $selectQuery."<br />";
			$selectResult = mysql_query($selectQuery);
			if (mysql_num_rows($selectResult) > 0) {
				echo "User: ".$repNum." - ".$firstName." ".$lastName." is already in the database. Changing Status to Active.<br />";
				$updateQuery = "UPDATE Users SET active='1' WHERE repNum='$repNum' AND firstName='$firstName' AND lastName='$lastName'";
				//echo $updateQuery."<br ";
				$updateResult = @mysql_query($updateQuery);
				echo "Updated Rep<br />";
				echo "<script language=JavaScript> window.opener.location.reload(true); window.close(); </script>";
			}
			else {
				$insertQuery = "INSERT INTO Users (repNum, firstName, lastName, active) VALUES ('$repNum','$firstName','$lastName','1')";
				//echo $insertQuery."<br ";
				$insertResult = @mysql_query($insertQuery);
				echo "Added Rep.<br />";
				echo "<script language=JavaScript> window.opener.location.reload(true); window.close(); </script>";
			}
			//echo "<script language=JavaScript> window.opener.location.reload(true); window.close(); </script>";
		}

		if ($type == 'activateRep') {
			$termQuery = "UPDATE Users SET active=1 WHERE repNum='$repNum'";
			//echo $termQuery;
			$termResult = @mysql_query($termQuery);
			// Switch back to clearingdata database...
			mysql_select_db("clearingdata") or die(mysql_error());
			echo "Activated Rep";
			echo "<script language=JavaScript> window.opener.location.reload(true); window.close(); </script>";
		}
		$database->closeConnection();
	}

	public function viewPayoutStructure($repNum) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		echo "<table><tr><td><h4>Rep Structure</h4></td></tr>";
		//Payout
		echo "<tr><th>Payout Percent</th></tr>";
		$selectPayQuery = "SELECT * FROM payoutStructure WHERE repNum='$repNum' ORDER BY minAmt ASC";
		//echo $selectQuery;
		$selectPayResult = mysql_query($selectPayQuery);
		if (mysql_num_rows($selectPayResult) > 0) {
			while ($pay = mysql_fetch_array($selectPayResult, MYSQL_NUM)) {
				echo "<tr><td> If Commission is > ".$pay[1]." AND < ".$pay[2]." Payout is: ".($pay[3] * 100)."%</td></tr>";
			}
			echo "<tr><td> <a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=editPayStruct&repNum=$repNum&type=editPayout','no','scrollbars=yes,width=600,height=400')\" >Edit Payout Structure</a></td></tr>";
		}
		else {
			echo "<tr><td> + <a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=editPayStruct&repNum=$repNum&type=editPayout','no','scrollbars=yes,width=600,height=400')\" >Add Payout Structure</a></td></tr>";
		}


		//JointRep
		echo "<tr><th>JointReps</th></tr>";
		$selectJointQuery = "SELECT * FROM repNums WHERE mainRep='$repNum' AND type='JointRep' ORDER BY altRep ASC";
		//echo $selectJointQuery;
		$selectJointResult = mysql_query($selectJointQuery);
		if (mysql_num_rows($selectJointResult) > 0) {
			while ($jRep = mysql_fetch_array($selectJointResult, MYSQL_NUM)) {
				echo "<tr><td> ".$jRep[2].", ".$jRep[1]." between: ".$jRep[4]." - Split ".$jRep[3]."</td></tr>";
			}
			echo "<tr><td> <a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=editPayStruct&repNum=$repNum&type=editJoint','no','scrollbars=yes,width=600,height=400')\" >Edit JointRep Structure</a></td></tr>";
		}
		else {
			echo "<tr><td> + <a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=editPayStruct&repNum=$repNum&type=editJoint','no','scrollbars=yes,width=600,height=400')\" >Add JointRep Structure</a></td></tr>";
		}

		//Override
		echo "<tr><th>Rep Overrides</th></tr>";
		$selectOverQuery = "SELECT * FROM repNums WHERE mainRep='$repNum' AND type='Override' ORDER BY altRep ASC";
		//echo $selectOverQuery;
		$selectOverResult = mysql_query($selectOverQuery);
		if (mysql_num_rows($selectOverResult) > 0) {
			while ($oRep = mysql_fetch_array($selectOverResult, MYSQL_NUM)) {
				echo "<tr><td>Rep: ".$oRep[0]." receives a ".($oRep[3] * 100)."% Override on Rep: ".$oRep[1]."</td></tr>";
			}
			echo "<tr><td><a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=editPayStruct&repNum=$repNum&type=editOverride','no','scrollbars=yes,width=600,height=400')\" >Edit Override Structure</a></td></tr>";
		}
		else {
			echo "<tr><td> + <a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=editPayStruct&repNum=$repNum&type=editOverride','no','scrollbars=yes,width=600,height=400')\" >Add Override Structure</a></td></tr>";
		}

		//Sales Assistant
		echo "<tr><th>Sales Assistant Bonus</th></tr>";
		$selectSAQuery = "SELECT * FROM salesAssistantData WHERE repNum='$repNum'";
		//echo $selectSAQuery;
		$selectSAResult = mysql_query($selectSAQuery);
		if (mysql_num_rows($selectSAResult) > 0) {
			while ($sa = mysql_fetch_array($selectSAResult, MYSQL_NUM)) {
				echo "<tr><td>Sales Assistant: ".$sa[0]."'s bonus is: ".($sa[2] * 100)."% </td></tr>";
			}
			echo "<tr><td><a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=editPayStruct&repNum=$repNum&type=editSalesAssist','no','scrollbars=yes,width=600,height=400')\" >Edit Sales Assistant Structure</a></td></tr>";
		}
		else {
			echo "<tr><td> + <a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=editPayStruct&repNum=$repNum&type=editSalesAssist','no','scrollbars=yes,width=600,height=400')\" >Add Sales Assistant Structure</a></td></tr>";
		}
		echo "<tr><th>Actavite Rep</th></tr><tr><td> - <a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=addDelRep&repNum=$repNum&type=activateRep','no','scrollbars=yes,width=600,height=400')\" >Activate Rep</a></td></tr>";
		echo "<tr><th>Terminate Rep</th></tr><tr><td> - <a href=\"javascript:;\"  onClick=\"window.open('repStructure.php?modify=addDelRep&repNum=$repNum&type=delRep','no','scrollbars=yes,width=600,height=400')\" >Terminate Rep</a></td></tr>";
		echo "</table>";
		
		$database->closeConnection();
	}

	public function editPayStructures($repNum, $type) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		echo "<form action=repStructure.php method=get >";
		echo "<input type=hidden name=modify value=".$_GET['modify']." />";
		echo "<input type=hidden name=repNum value=$repNum />";
		echo "<h4>Edit Structure</h4><table><tr><td></td></tr>";

		if ($type == "editPayout") {
			$counter = 0;
			//Payout
			$selectPayQuery = "SELECT * FROM payoutStructure WHERE repNum='$repNum' ORDER BY minAmt ASC";
			//echo $selectQuery;
			$selectPayResult = mysql_query($selectPayQuery);
			if (mysql_num_rows($selectPayResult) > 0) {
				echo "<tr><th>Payout Percent</th></tr>";
				while ($pay = mysql_fetch_array($selectPayResult, MYSQL_NUM)) {
					$gtName = "greater-".$counter;
					$ltName = "less-".$counter;
					$perName = "percent-".$counter;
					echo "<tr><td>If Commission is greater than <input type=text name=$gtName size=8 value=".$pay[1]." /> AND less than <input type=text name=$ltName size=8 value=".$pay[2]." /> Payout is: <input type=text name=$perName size=2 value=".($pay[3] * 100)." />%</td></tr>";
					++$counter;
				}
			}
			while ($counter < 5) {
				$gtName = "greater-".$counter;
				$ltName = "less-".$counter;
				$perName = "percent-".$counter;
				echo "<tr><td>If Commission is greater than <input type=text name=$gtName size=8 /> AND less than <input type=text name=$ltName size=8 /> Payout is: <input type=text name=$perName size=2  />%</td></tr>";
				++$counter;
			}
			echo "<input type=hidden name=counter value=$counter />";
			$subType = "Submit Payout";
		}

		if ($type == "editJoint") {
			$counter = 0;
			//JointRep
			$selectJointQuery = "SELECT * FROM repNums WHERE mainRep='$repNum' AND type='JointRep'";
			//echo $selectJointQuery;
			$selectJointResult = mysql_query($selectJointQuery);
			if (mysql_num_rows($selectJointResult) > 0) {
				echo "<tr><th>JointReps</th></tr>";
				while ($jRep = mysql_fetch_array($selectJointResult, MYSQL_NUM)) {
					$jointName = "jointNum-".$counter;
					$betweenName = "between-".$counter;
					$splitName = "split-".$counter;
					echo "<tr><td>JointRep Number: <input type=text name=$jointName size=3 value=".$jRep[1]." /> between: <input type=text name=$betweenName size=20 value=".$jRep[4]." /> - Split: <input type=text name=$splitName size=2 value=".$jRep[3]." /></td></tr>";
					++$counter;
				}
			}
			while ($counter < 5) {
				$jointName = "jointNum-".$counter;
				$betweenName = "between-".$counter;
				$splitName = "split-".$counter;
				echo "<tr><td>JointRep Number: <input type=text name=$jointName size=3 /> between: <input type=text name=$betweenName size=20 /> - Split: <input type=text name=$splitName size=2 /></td></tr>";
				++$counter;
			}
			echo "<input type=hidden name=counter value=$counter />";
			$subType = "Submit Joint";
		}

		if ($type == "editOverride") {
			$counter = 0;
			//Override
			$selectOverQuery = "SELECT * FROM repNums WHERE mainRep='$repNum' AND type='Override'";
			//echo $selectOverQuery;
			$selectOverResult = mysql_query($selectOverQuery);
			if (mysql_num_rows($selectOverResult) > 0) {
				echo "<tr><th>Rep Overrides</th></tr>";
				while ($oRep = mysql_fetch_array($selectOverResult, MYSQL_NUM)) {
					$fromName = "from-".$counter;
					$percentName = "percent-".$counter;
					echo "<tr><td>Rep: ".$oRep[0]." receives a <input type=text name=$percentName size=3 value=".($oRep[3] * 100)." />% Override on Rep: <input type=text name=$fromName size=3 value=".$oRep[1]." /></td></tr>";
					++$counter;
				}
			}
			while ($counter < 5) {
				$fromName = "from-".$counter;
				$percentName = "percent-".$counter;
				echo "<tr><td>Rep: receives a <input type=text name=$percentName size=3 />% Override on Rep: <input type=text name=$fromName size=3 /></td></tr>";
				++$counter;
			}
			echo "<input type=hidden name=counter value=$counter />";
			$subType = "Submit Override";
		}

		if ($type == "editSalesAssist") {
			$counter = 0;
			//Sales Assistant
			$selectSAQuery = "SELECT * FROM salesAssistantData WHERE repNum='$repNum'";
			//echo $selectSAQuery;
			$selectSAResult = mysql_query($selectSAQuery);
			if (mysql_num_rows($selectSAResult) > 0) {
				echo "<tr><th>Sales Assistant Bonus</th></tr>";
				while ($sa = mysql_fetch_array($selectSAResult, MYSQL_NUM)) {
					$saName = "sa-".$counter;
					$percentName = "percent-".$counter;
					echo "<tr><td>Sales Assistant: <input type=text name=$saName size=15 value=\"$sa[0]\" /> Bonus: <input type=text name=$percentName size=3 value=".($sa[2] * 100)." />% </td></tr>";
					++$counter;
				}
			}
			while ($counter < 1) {
				$saName = "sa-".$counter;
				$percentName = "percent-".$counter;
				echo "<tr><td>Sales Assistant: <input type=text name=$saName size=15 /> Bonus: <input type=text name=$percentName size=3 />% </td></tr>";
				++$counter;
			}
			echo "<input type=hidden name=counter value=$counter />";
			$subType = "Submit Sales";
		}

		echo "<tr><td><input type=submit name=submit value=\"$subType\" /> <a href=\"javascript:window.opener.location.reload( true); window.close();\">Cancel</a></td></tr>";
		echo "</table></form>";
		
		$database->closeConnection();
	}

	public function changeRepNum($newRep, $branch, $acctNum, $settleDateStart, $settleDateEnd) {
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

	public function repNumArrayDateRangeInPayroll($dateRange) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT DISTINCT repNum FROM submittedPayrollData WHERE monthEndDate BETWEEN '$dateRange[0]' AND '$dateRange[1]' ORDER BY repNum ASC";
		$result = @mysql_query($query);
		$incre = 0;
		while ($row = @mysql_fetch_array($result, MYSQL_NUM)) {
			$repNums[$incre] = $row[0];
			++$incre;
		}
		$database->closeConnection();
		return $repNums;
	}

}

?>
