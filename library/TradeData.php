<?php

//TODO delete me!!!
class TradeData {

	/*
	 * SEARCH PARAMS
	 */
	private $acctNum;
	private $dateType;
	private $groupBy;
	private $repNum;
	private $searchDateStart;
	private $searchDateEnd;
	private $searchMonthSelect;
	private $submit;
	private $viewType;

	/*
	 * SEARCH DATA VARS
	 */
	private $listOfAccounts;
	private $listOfReps;
	private $listOfTrades;
	private $calculatedTrades;
	private $calculatedTotals;

	/*
	 * CONSTRUCTOR: TRADE DATA
	 */
	public function __construct() {

		$this->setViewType();

		$this->setDateType();
		if ($_GET['searchDateStart'] != "") {
			$this->setSearchDates();
		} else if ($_GET['searchMonthSelect'] != "NULL") {
			$this->setSearchMonthSelect();
		} else {
//			$commDate = new CommissionDates();
//			$commDate->setLastTradeDate();
//			$this->searchDateStart = $commDate->getLastTradeDate();
//			$this->searchDateEnd = $this->searchDateStart;
		}

		$this->setRepNum();
		$this->setAcctNum();
		$this->setGroupBy();
		$this->setSubmit();

		switch ($this->viewType) {
			case 'previousDayTotals' :
				$commDate = new CommissionDates();
				$commDate->setLastTradeDate();
				$this->searchDateStart = $commDate->getLastTradeDate();
				$this->searchDateEnd = $this->searchDateStart;
				$this->viewPreviousDayTotals();
				break;
			case 'totals' :
				$this->viewTradeTotals();
				break;
			case 'blotter' :
				$this->viewTradeBlotter();
				break;
			default :
				echo "There was an error. Please try again.<br />";
				break;
		}
	}

	/*
	 * GETTER METHODS
	 */
	public function getAcctNum() {
		return $this->acctNum;
	}

	public function getDateType() {
		return $this->dateType;
	}

	public function getGroupBy() {
		return $this->groupBy;
	}

	public function getRepNum() {
		return $this->repNum;
	}

	public function getSearchDateStart() {
		return $this->searchDateStart;
	}

	public function getSearchDateEnd() {
		return $this->searchDateEnd;
	}

	public function getSearchMonthSelect() {
		return $this->searchMonthSelect;
	}

	public function getSubmit() {
		return $this->submit;
	}

	public function getViewType() {
		return $this->viewType;
	}

	/*
	 * SETTER METHODS
	 */
	public function setAcctNum() {
		if ($_GET['acctNum'] == "") {
			$this->acctNum = null;
		} else {
			$this->acctNum = $_GET['acctNum'];
		}
	}

	public function setDateType() {
		if ($_GET['dateType'] != "null") {
			$this->dateType = $_GET['dateType'];
		} else {
			$this->dateType = "trade";
		}
	}

	public function setGroupBy() {
		if ($_GET['groupBy'] != "null") {
			$this->groupBy = $_GET['groupBy'];
		} else {
			if ($this->acctNum != null) {
				$this->groupBy = "acct";
			} else {
				$this->groupBy = "rep";
			}
		}
	}

	public function setRepNum() {
		if ($_GET['repNum'] == "") {
			$this->repNum = null;
		} else {
			$this->repNum = $_GET['repNum'];
		}
	}

	public function setSearchDates() {
		$this->searchDateStart = $_GET['searchDateStart'];
		$this->searchDateEnd = $_GET['searchDateEnd'];
		if ($this->searchDateStart != "" && $this->searchDateEnd == "") {
			$this->searchDateEnd = $this->searchDateStart;
		}
	}

	public function setSearchMonthSelect() {
		$this->searchMonthSelect = $_GET['searchMonthSelect'];
		$commDate = new CommissionDates();
		$commDate->setDates($this->searchMonthSelect);
		$this->searchDateStart = $commDate->dateInfo[0];
		$this->searchDateEnd = $commDate->dateInfo[3];
	}

	public function setSubmit() {
		$this->submit = $_GET['submit'];
	}

	public function setViewType() {
		if (isset($_GET['viewType']) && $_GET['viewType'] != "null") {
			$this->viewType = $_GET['viewType'];
		} else {
			$this->viewType = "previousDayTotals";
		}
	}

	/*
	 * METHOD: TO STRING
	 * TradeData Submission toString.
	 */
	public function toString() {
		$string = "ViewType: " . $this->viewType . "<br />";
		$string .= "GroupBy: " . $this->groupBy . "<br />";
		$string .= "DateType: " . $this->dateType . "<br />";
		$string .= "SearchMonth: " . $this->searchMonthSelect . "<br />";
		$string .= "RepNum: " . $this->repNum . "<br />";
		$string .= "AcctNum: " . $this->acctNum . "<br />";
		$string .= "SearchDateStart: " . $this->searchDateStart . "<br />";
		$string .= "SearchDateEnd: " . $this->searchDateEnd . "<br />";
		$string .= "Submit: " . $this->submit . "<br />";
		return $string;
	}

	/*
	 * METHOD: VIEW PREVIOUS DAY TOTALS
	 */
	private function viewPreviousDayTotals() {
		echo "Previous Day Totals<br />";
		$this->createListOfReps();
		$this->createListOfAccounts();
		$this->createListOfTrades();
		$this->calculateTradesByGrouping();
		$this->calculateTotals();
		$this->returnCommissionTotalsView();
	}

	private function viewTradeBlotter() {
		echo "Trade Blotter<br />";
		$this->createListOfReps();
		$this->createListOfAccounts();

	}

	/*
	 * METHOD: VIEW TRADE TOTALS
	 */
	private function viewTradeTotals() {
		echo "Trade Totals<br />";
		$this->createListOfReps();
		$this->createListOfAccounts();
		$this->createListOfTrades();
		$this->calculateTradesByGrouping();
		$this->calculateTotals();
		$this->returnCommissionTotalsView();
	}

	/*
	 * METHOD: CREATE LIST OF ACCOUNTS
	 * Creates and sets $this->listOfAccounts.
	 */
	private function createListOfAccounts() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT DISTINCT BRANCH_01_a, ACCOUNT_NUMBER_01, REGISTERED_REP_OWNING_REP_RR_09 FROM TRDREV_TD";
		if ($this->dateType=="settlement") {
			$query .= " WHERE SETTLEMENT_DATE_01 BETWEEN '$this->searchDateStart' AND '$this->searchDateEnd'";
		} else if ($this->dateType=="trade") {
			$query .= " WHERE TRADE_DATE_01 BETWEEN '$this->searchDateStart' AND '$this->searchDateEnd'";
		}
		$query .= " ORDER BY REGISTERED_REP_OWNING_REP_RR_09, BRANCH_01_a, ACCOUNT_NUMBER_01 ASC";
		//echo $query."<br />"; // Show Query
		$result = @mysql_query($query);
		$counter = 0;
		while ($acctNums = @mysql_fetch_array($result, MYSQL_NUM)) {
			$this->listOfAccounts[$counter][0] = $acctNums[0];
			$this->listOfAccounts[$counter][1] = $acctNums[1];
			$this->listOfAccounts[$counter][2] = $acctNums[2];
			//echo $counter." | ".$this->listOfAccounts[$counter][0]." | ".$this->listOfAccounts[$counter][1]." | ".$this->listOfAccounts[$counter][2]."<br />";
			$counter++;
		}
		$database->closeConnection();
	}

	/*
	 * METHOD: CREATE LIST OF REPS
	 * Creates and sets $this->listOfReps.
	 */
	private function createListOfReps() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT DISTINCT REGISTERED_REP_OWNING_REP_RR_09 FROM TRDREV_TD";
		if ($this->dateType=="settlement") {
			$query .= " WHERE SETTLEMENT_DATE_01 BETWEEN '$this->searchDateStart' AND '$this->searchDateEnd'";
		} else if ($this->dateType=="trade") {
			$query .= " WHERE TRADE_DATE_01 BETWEEN '$this->searchDateStart' AND '$this->searchDateEnd'";
		}
		$query .= " ORDER BY REGISTERED_REP_OWNING_REP_RR_09 ASC";
		//echo $query;
		$result = @mysql_query($query);
		$counter = 0;
		while ($repNumbers = @mysql_fetch_array($result)) {
			$this->listOfReps[$counter] = $repNumbers[0];
			//echo $counter." | ".$this->listOfReps[$counter]."<br />";
			$counter++;
		}
		$database->closeConnection();
	}

	/*
	 * METHOD: CREATE LIST OF TRADES
	 * Creates and sets $this->listOfTrades.
	 */
	private function createListOfTrades() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$tradeCounter = 0;
		for ($x = 0; $x < count($this->listOfAccounts); $x++) {
			$query = "SELECT REGISTERED_REP_OWNING_REP_RR_09, BRANCH_01_a, ACCOUNT_NUMBER_01, " .
						"TRADE_REFERENCE_NUMBER_01, DATE_FORMAT(TRADE_DATE_01, '%m/%d/%Y'), DATE_FORMAT(SETTLEMENT_DATE_01, '%m/%d/%Y'), " .
						"ACCOUNT_TYPE_01, MARKET_CODE_01, BLOTTER_CODE_01, BUY_SELL_CODE_01, CANCEL_CODE_01, " .
						"SUM(QUANTITY_03), CUSIP_01, SYMBOL_02, SECURITY_DESCRIPTION_LINE_05_a, SECURITY_DESCRIPTION_LINE_05_b, " .
						"AVG(PRICE_03), SUM(PRINCIPAL_04), SUM(TRADE_COMMISSION_04), SUM(TRADE_CONCESSION_05), " .
						"SOLICITED_CODE_10, SECURITY_TYPE_02, TRADE_DEFINITION_TRADE_ID_12 FROM TRDREV_TD";
			$query .= " WHERE BRANCH_01_a='".$this->listOfAccounts[$x][0]."' AND ACCOUNT_NUMBER_01='".$this->listOfAccounts[$x][1]."'";
			if ($this->dateType=="settlement") {
				$query .= " AND SETTLEMENT_DATE_01 BETWEEN '$this->searchDateStart' AND '$this->searchDateEnd' ";
			} else if ($this->dateType=="trade") {
				$query .= " AND TRADE_DATE_01 BETWEEN '$this->searchDateStart' AND '$this->searchDateEnd' ";
			}
			$query .= " GROUP BY TRADE_DEFINITION_TRADE_ID_12";
			$query .= " ORDER BY REGISTERED_REP_OWNING_REP_RR_09, BRANCH_01_a, ACCOUNT_NUMBER_01, TRADE_DATE_01 ASC";
			//echo $query."<br />"; // Show Query...
			$result = @mysql_query($query);
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {

				/** Variables for Results */
				$quantity = $row[11]; // quantity
				$price = $row[16]; // price
				$principal = $row[17]; // principal
				$commission = $row[18]; // commission
				$concession = $row[19]; // concession

				$quantity = substr($quantity,0,-2);
				$quantity = substr_replace($quantity, ".",-3,0);
				$quantityTemp = explode(".", $quantity);
				$quantity = $quantityTemp[0];
				//$quantity = (int)$quantity;
				$principal = substr_replace($principal, ".", -2, 0);
				$tempPrice = explode(".",$price);
				$price = $tempPrice[0];
				$price = substr($price,0,-5);
				$price = substr_replace($price, ".", -4, 0);
				$commission = substr_replace($commission, ".", -2, 0);
				$concession = substr_replace($concession, ".", -2, 0);

				$this->listOfTrades[$tradeCounter] = new Trade(
				$row[0], //repNumber
				$row[1], //branch
				$row[2], //accountNumber
				$row[3], //tradeReference
				$row[4], //tradeDate
				$row[5], //
				$row[6], //
				$row[7], //marketCode
				$row[8], //blotterCode
				$row[9], //buySellCode
				$row[10], //cancelCode
				$quantity, //quantity
				$row[12], //cusip
				$row[13], //symbol
				$row[14], //securityDescription1
				$row[15], //securityDescription2
				$price, //price
				$principal, //principal
				$commission, //commission
				$concession, //concession
				$row[20], //solicitionCode
				$row[21], //securityType
				$row[22]); //tradeReferenceTradeID
				//echo $this->listOfTrades[$counter]->toString()."<br />";
				$tradeCounter++;
			}
		}
		$database->closeConnection();
	}

	/*
	 * METHOD: CALCULATE TRADES BY GROUPING
	 */
	private function calculateTradesByGrouping() {

		$groupingCount = 0;
		switch ($this->groupBy) {
			case 'rep':
				$groupingCount = count($this->listOfReps);
				break;
			case 'acct':
				$groupingCount = count($this->listOfAccounts);
				break;
			case 'all':
				$groupingCount = 1;
				break;
			default :
				break;
		}

		$calculatedCount = 0;
		for ($groupingLoopCounter = 0; $groupingLoopCounter < $groupingCount; $groupingLoopCounter++) {

			// Clear variables
			$cancelCode = 0;
			$quantity = 0;
			$secType = 0;
			$cancelCount = 0;
			$optTradeCount = 0;
			$optQuantity = 0;
			$optQuantTotal = 0;
			$tradeCount = 0;
			$princTotal = 0;
			$commiTotal = 0;
			$conceTotal = 0;

			for ($tradeCounter = 0; $tradeCounter < count($this->listOfTrades); ++$tradeCounter) {
				$okToCalc = false;

				if ($this->groupBy == "rep" && $this->listOfTrades[$tradeCounter]->getRepNumber() == $this->listOfReps[$groupingLoopCounter]) {
					$okToCalc = true;
					$this->calculatedTrades[$calculatedCount][0] = $this->listOfTrades[$tradeCounter]->getRepNumber();
				} else if ($this->groupBy == "acct" && $this->listOfTrades[$tradeCounter]->getBranch() == $this->listOfAccounts[$groupingLoopCounter][0] && $this->listOfTrades[$tradeCounter]->getAccountNumber() == $this->listOfAccounts[$groupingLoopCounter][1]) {
					$okToCalc = true;
					$this->calculatedTrades[$calculatedCount][0] = $this->listOfAccounts[$groupingLoopCounter][0]."-".$this->listOfAccounts[$groupingLoopCounter][1]."|".$this->listOfTrades[$tradeCounter]->getRepNumber();
				} else if ($this->groupBy == "all") {
					$okToCalc = true;
				}

				if ($okToCalc) {
					$cancelCode = $this->listOfTrades[$tradeCounter]->getCancelCode(); // cancelCode
					$quantity = $this->listOfTrades[$tradeCounter]->getQuantity(); // quantity
					$secType = $this->listOfTrades[$tradeCounter]->getSecurityType(); // secType

					if ($cancelCode == 1) { // Count Cancels
						$cancelCount++;
					}

					if ($secType == 8) { // If Result is an Option Trade...
						if ($cancelCode != 1) { // If Result is not a Canceled Trade...
							$optTradeCount++;
							$optQuantity = $quantity;
							$optQuantTotal += $optQuantity;
						} elseif ($cancelCode == 1) { // If Result is a Canceled Trade...
							$optTradeCount--;
							$optQuantity = $quantity;
							$optQuantTotal -= $optQuantity;
						}
					}
					if($secType && $cancelCode != 1) { // Count Trade
						$tradeCount++;
					}

					/** Total Principal, Commission, & Concession */
					if ($cancelCode == 1) {
						$princTotal -= $this->listOfTrades[$tradeCounter]->getPrincipal();
						$commiTotal -= $this->listOfTrades[$tradeCounter]->getCommission();
						$conceTotal -= $this->listOfTrades[$tradeCounter]->getConcession();
					} else {
						$princTotal += $this->listOfTrades[$tradeCounter]->getPrincipal();
						$commiTotal += $this->listOfTrades[$tradeCounter]->getCommission();
						$conceTotal += $this->listOfTrades[$tradeCounter]->getConcession();
					}
				}
			}

			$this->calculatedTrades[$calculatedCount][1] = $tradeCount;
			$this->calculatedTrades[$calculatedCount][2] = $cancelCount;
			$this->calculatedTrades[$calculatedCount][3] = $optTradeCount;
			$this->calculatedTrades[$calculatedCount][4] = $optQuantTotal;
			$this->calculatedTrades[$calculatedCount][5] = $princTotal;
			$this->calculatedTrades[$calculatedCount][6] = $commiTotal;
			$this->calculatedTrades[$calculatedCount][7] = $conceTotal;
			$this->calculatedTrades[$calculatedCount][8] = $commiTotal + $conceTotal;

			$calculatedCount++; // Count how many results.
		}
	}

	/*
	 * METHOD: CALCULATE TOTALS
	 * 1. Trade Count
	 * 2. Cancel Count
	 * 3. Option Trade Count
	 * 4. Option Quantity Total
	 * 5. Principal Total
	 * 6. Commission Total
	 * 7. Concession Total
	 * 8. Commission & Concession
	 */
	private function calculateTotals() {
		$this->calculatedTotals[0] = null;
		$this->calculatedTotals[1] = null;
		$this->calculatedTotals[2] = null;
		$this->calculatedTotals[3] = null;
		$this->calculatedTotals[4] = null;
		$this->calculatedTotals[5] = null;
		$this->calculatedTotals[6] = null;
		$this->calculatedTotals[7] = null;
		$numberOfTrades = count($this->calculatedTrades);
		for ($totalsIncre = 0; $totalsIncre < $numberOfTrades; ++$totalsIncre) {
			$this->calculatedTotals[1] += $this->calculatedTrades[$totalsIncre][1];
			$this->calculatedTotals[2] += $this->calculatedTrades[$totalsIncre][2];
			$this->calculatedTotals[3] += $this->calculatedTrades[$totalsIncre][3];
			$this->calculatedTotals[4] += $this->calculatedTrades[$totalsIncre][4];
			$this->calculatedTotals[5] += $this->calculatedTrades[$totalsIncre][5];
			$this->calculatedTotals[6] += $this->calculatedTrades[$totalsIncre][6];
			$this->calculatedTotals[7] += $this->calculatedTrades[$totalsIncre][7];
		}
		$this->calculatedTotals[8] = $this->calculatedTotals[6] + $this->calculatedTotals[7];
	}

	/*
	 * METHOD: RETURN PREVIOUS DAY TOTALS VIEW
	 */
	private function returnCommissionTotalsView() {
		$brokerDao = new BrokerDAO();
		$calcCount = count($this->calculatedTrades);
		$chartData[][] = null;
		$formatCols[][] = null;
		$counter = 0;
		for ($a = 0; $a < $calcCount; ++$a) {
			$this->calculatedTrades[$a][0] = $this->calculatedTrades[$a][0]." - ".$brokerDao->retrieveBrokerNameByNumber($this->calculatedTrades[$a][0]);
			$nameLabel = $this->calculatedTrades[$a][0];
			$chartData[$a][0] = $nameLabel;
			$chartData[$a][1] = $this->calculatedTrades[$a][6];
			$formatCols[$a][0] = null;
			$formatCols[$a][1] = $this->calculatedTrades[$a][1];
			$formatCols[$a][2] = $this->calculatedTrades[$a][2];
			$formatCols[$a][3] = $this->calculatedTrades[$a][3];
			$formatCols[$a][4] = $this->calculatedTrades[$a][4];
			$formatCols[$a][5] = money_format('%(1.2n', $this->calculatedTrades[$a][5]);
			$formatCols[$a][6] = money_format('%(1.2n', $this->calculatedTrades[$a][6]);
			$formatCols[$a][7] = money_format('%(1.2n', $this->calculatedTrades[$a][7]);
			$formatCols[$a][8] = money_format('%(1.2n', $this->calculatedTrades[$a][8]);
			++$counter;
		}

		$this->calculatedTrades[$counter][0] = "Totals";
		$formatCols[$counter][0] = null;
		$this->calculatedTrades[$counter][1] = $this->calculatedTotals[1];
		$formatCols[$counter][1] = $this->calculatedTotals[1];
		$this->calculatedTrades[$counter][2] = $this->calculatedTotals[2];
		$formatCols[$counter][2] = $this->calculatedTotals[2];
		$this->calculatedTrades[$counter][3] = $this->calculatedTotals[3];
		$formatCols[$counter][3] = $this->calculatedTotals[3];
		$this->calculatedTrades[$counter][4] = $this->calculatedTotals[4];
		$formatCols[$counter][4] = $this->calculatedTotals[4];
		$this->calculatedTrades[$counter][5] = $this->calculatedTotals[5];
		$formatCols[$counter][5] = money_format('%(1.2n', $this->calculatedTotals[5]);
		$this->calculatedTrades[$counter][6] = $this->calculatedTotals[6];
		$formatCols[$counter][6] = money_format('%(1.2n', $this->calculatedTotals[6]);
		$this->calculatedTrades[$counter][7] = $this->calculatedTotals[7];
		$formatCols[$counter][7] = money_format('%(1.2n', $this->calculatedTotals[7]);
		$this->calculatedTrades[$counter][8] = $this->calculatedTotals[8];
		$formatCols[$counter][8] = money_format('%(1.2n', $this->calculatedTotals[8]);

		$colNames[0][0] = "string";
		switch ($this->groupBy) {
			case 'rep':
				$colNames[0][1] = "Rep Number/Name";
				break;
			case 'acct':
				$colNames[0][1] = "Account Number";
				break;
			case 'all':
				$colNames[0][1] = "";
				break;
			default :
				$colNames[0][1] = "???";
				break;
		}
		$colNames[1][0] = "number";
		$colNames[1][1] = "Trades";
		$colNames[2][0] = "number";
		$colNames[2][1] = "Cancels";
		$colNames[3][0] = "number";
		$colNames[3][1] = "Option TD";
		$colNames[4][0] = "number";
		$colNames[4][1] = "Option CT";
		$colNames[5][0] = "number";
		$colNames[5][1] = "Principal";
		$colNames[6][0] = "number";
		$colNames[6][1] = "Commission";
		$colNames[7][0] = "number";
		$colNames[7][1] = "Concession";
		$colNames[8][0] = "number";
		$colNames[8][1] = "Total";

		HtmlComponents::googPrintTableData($colNames, $this->calculatedTrades, $formatCols);
		$title = "Commission Totals";
		HtmlComponents::googPrintPieData($chartData, $title);
	}

}
?>