<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class TradeDAO {

	// Search Criteria Variables
	private $account;
	private $broker;
	private $dateType;
	private $searchDateEnd;
	private $searchDateStart;

	// Variables for instance methods
	private $listOfAccounts;
	private $listOfReps;
	private $listOfTrades;
	private $tradesResultsFromQuery;
	private $tradesRolledUp;

	/*
	 * (NEW) METHOD: BUNCH TRADES BY TRADE DEFINITION TRADE ID
	 * Takes an array of trades
	 * Tests if 2 trades have equal TradeDefTradeIDs and if true bunchs them and adds to new array.
	 * Otherwise just adds the trade to the new array
	 * After all trades have been tested, returns new array of Trades.
	 */
	private function bunchTradesByTradeDefinitionTradeID() {
		$tradesIndex = count($this->tradesResultsFromQuery);
		$newTradeCounter = 0;
		for ($i = 0; $i < $tradesIndex; $i++) {
			$tempTrade = null;
			if ($i == $tradesIndex - 1) {
				//Last Trade so just add Trade to new array of Trades
				$tempTrade = $this->tradesResultsFromQuery[$i];
			} else {
				$tempTrade = $this->tradesResultsFromQuery[$i];
				$firstTrade = $tempTrade;
				$secondTrade = $this->tradesResultsFromQuery[$i+1];
				while ($firstTrade->getTradeReferenceTradeID() == $secondTrade->getTradeReferenceTradeID()) {
					$i++;
					$tempTrade = Trade::bunchTwoTrades($firstTrade, $secondTrade);
					if ($i == $tradesIndex - 1) {
						//Last Trade, break out of loop
						break;
					} else {
						$firstTrade = $tempTrade;
						$secondTrade = $this->tradesResultsFromQuery[$i+1];
					}
				}
			}
			$this->tradesRolledUp[$newTradeCounter++] = $tempTrade; // new trade
		}
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
	 * METHOD: CREATE LIST OF BROKERS
	 * Creates and sets $this->listOfBrokers.
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
		} else {
			// Default is Trade Date
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
	 * (NEW) METHOD: CREATE LIST OF TRADES BY CRITERIA
	 */
	private function createListOfTradesByCriteria() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		///*
		$query = "SELECT
		 REGISTERED_REP_OWNING_REP_RR_09,
		 BRANCH_01_a, ACCOUNT_NUMBER_01,
		 TRADE_REFERENCE_NUMBER_01,
		 DATE_FORMAT(TRADE_DATE_01, '%m/%d/%Y'),
		 DATE_FORMAT(SETTLEMENT_DATE_01, '%m/%d/%Y'),
		 ACCOUNT_TYPE_01,
		 MARKET_CODE_01,
		 BLOTTER_CODE_01,
		 BUY_SELL_CODE_01,
		 CANCEL_CODE_01,
		 QUANTITY_03,
		 CUSIP_01,
		 SYMBOL_02,
		 SECURITY_DESCRIPTION_LINE_05_a,
		 SECURITY_DESCRIPTION_LINE_05_b,
		 PRICE_03,
		 PRINCIPAL_04,
		 TRADE_COMMISSION_04,
		 TRADE_CONCESSION_05,
		 SOLICITED_CODE_10,
		 SECURITY_TYPE_02,
		 TRADE_DEFINITION_TRADE_ID_12
		 FROM TRDREV_TD";
		//*/

		//
		/*
		$query = "SELECT
		REGISTERED_REP_OWNING_REP_RR_09,
		BRANCH_01_a,
		ACCOUNT_NUMBER_01,
		TRADE_REFERENCE_NUMBER_01,
		DATE_FORMAT(TRADE_DATE_01, '%m/%d/%Y'),
		DATE_FORMAT(SETTLEMENT_DATE_01, '%m/%d/%Y'),
		ACCOUNT_TYPE_01,
		MARKET_CODE_01,
		BLOTTER_CODE_01,
		BUY_SELL_CODE_01,
		CANCEL_CODE_01,
		QUANTITY_03, CUSIP_01,
		SYMBOL_02,
		SECURITY_DESCRIPTION_LINE_05_a,
		SECURITY_DESCRIPTION_LINE_05_b,
		PRICE_03, PRINCIPAL_04,
		TRADE_COMMISSION_04,
		TRADE_CONCESSION_05,
		SOLICITED_CODE_10,
		SECURITY_TYPE_02,
		ORDER_REFERENCE_NUMBER_12
		FROM TRDREV_TD";
		//*/

		if ($this->dateType=="settlement") {
			$dateModifier = "SETTLEMENT_DATE_01";
		} else if ($this->dateType=="trade") {
			$dateModifier = "TRADE_DATE_01";
		}

		$query .= " WHERE ".$dateModifier." BETWEEN '".$this->searchDateStart."' AND '".$this->searchDateEnd."' ";

		if ($this->broker != null) {
			$query .= " AND REGISTERED_REP_OWNING_REP_RR_09='".$this->broker."'";
		}

		if ($this->account != null) {
			$fullAccount = explode("-" , $this->account);
			$query .= " AND BRANCH_01_a='$fullAccount[0]' AND ACCOUNT_NUMBER_01='$fullAccount[1]'";
		}

		$query .= " ORDER BY REGISTERED_REP_OWNING_REP_RR_09, $dateModifier, TRADE_DEFINITION_TRADE_ID_12 ASC"; // use RUN_DATE_01 instead of $dateModifier

		//InitializeSite::alertMessage($query); // Show Query...
		$result = @mysql_query($query);

		$rowCounter = 0;
		while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			$trade = new Trade();
			$trade->setRepNumber($row[0]);
			$trade->setBranch($row[1]);
			$trade->setAccountNumber($row[2]);
			$trade->setTradeReferenceNumber($row[3]);
			$trade->setTradeDate($row[4]);
			$trade->setSettleDate($row[5]);
			$trade->setAccountType($row[6]);
			$trade->setMarketCode($row[7]);
			$trade->setBlotterCode($row[8]);
			$trade->setBuySellCode($row[9]);
			$trade->setCancelCode($row[10]);
			$trade->setQuantity($row[11]);
			$trade->setCusip($row[12]);
			$trade->setSymbol($row[13]);
			$trade->setSecurityDescription1($row[14]);
			$trade->setSecurityDescription2($row[15]);
			$trade->setPrice($row[16]);
			$trade->setPrincipal($row[17]);
			$trade->setCommission($row[18]);
			$trade->setConcession($row[19]);
			$trade->setSolicitationCode($row[20]);
			$trade->setSecurityType($row[21]);
			$trade->setTradeReferenceTradeID($row[22]);
			//InitializeSite::alertMessage($trade->toString());
			$this->tradesResultsFromQuery[$rowCounter++] = $trade;
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
					$princTotal += $this->listOfTrades[$tradeCounter]->getPrincipal();
					$commiTotal += $this->listOfTrades[$tradeCounter]->getCommission();
					$conceTotal += $this->listOfTrades[$tradeCounter]->getConcession();
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
	 * METHOD: FORMAT AS CURRENCY
	 * @param $value
	 * @return money_format('%1.2n', $value);
	 * Takes a variable of string, int, etc... and returns a formated string as $0.00.
	 */
	public function formatAsCurrency($value) {
		setlocale(LC_MONETARY, 'en_US');
		return money_format('%(1.2n', $value);
	}

	/*
	 * (MAIN) METHOD: RUN SEARCH BY CRITERIA
	 *
	 */
	public function runSearchByCriteria() {
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

		$this->setBroker();
		$this->setAccount();
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
	 * TODO Whats the point of all the getters/setters?
	 * They are not called from anywhere and porbably never will be.
	 * Move some of the functionality to Models and some to Controllers.
	 */

	/*
	 * GETTER METHODS
	 */
	public function getAccount() {
		return $this->account;
	}

	public function getBroker() {
		return $this->broker;
	}

	public function getDateType() {
		return $this->dateType;
	}

	public function getGroupBy() {
		return $this->groupBy;
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
	public function setAccount() {
		if ($_GET['acctNum'] == "") {
			$this->account = null;
		} else {
			$this->account = $_GET['acctNum'];
		}
	}

	public function setBroker() {
		if ($_GET['repNum'] == "") {
			$this->broker = null;
		} else {
			$this->broker = $_GET['repNum'];
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
		$this->createListOfTradesByCriteria();
		$this->bunchTradesByTradeDefinitionTradeID();
		$this->listOfTrades = $this->tradesRolledUp;
		//$this->listOfReps = BrokerDAO::addTradesToBrokerAccounts($this->listOfReps, $this->listOfTrades);
		$this->calculateTradesByGrouping();
		$this->calculateTotals();
		$this->returnCommissionTotalsView();
	}

	private function viewTradeBlotter() {
		echo "Trade Blotter<br />";
		if ($this->broker != null) {
			$this->listOfReps[0] = BrokerDAO::retrieveBrokerByNumber($this->broker, $this->searchDateStart, $this->searchDateEnd);
		} else if ($this->account != null) {
			InitializeSite::alertMessage("Blotter by Account not currently functional");
			//TODO blotter by account number...
		} else {
			$this->listOfReps = BrokerDAO::retrieveBrokersByDateRange($this->dateType, $this->searchDateStart, $this->searchDateEnd);
		}
		$this->listOfReps = BrokerDAO::addAccountsToBrokers($this->listOfReps, $this->dateType, $this->searchDateStart, $this->searchDateEnd);
		$this->createListOfTradesByCriteria();
		$this->bunchTradesByTradeDefinitionTradeID();
		$this->listOfTrades = $this->tradesRolledUp;
		$this->listOfReps = BrokerDAO::addTradesToBrokerAccounts($this->listOfReps, $this->listOfTrades);
		$this->returnTradeBlotterView();
		//TODO: Finish Trade Blotter
	}

	/*
	 * METHOD: VIEW TRADE TOTALS
	 */
	private function viewTradeTotals() {
		echo "Trade Totals<br />";
		$this->createListOfReps();
		$this->createListOfAccounts();
		$this->createListOfTradesByCriteria();
		$this->bunchTradesByTradeDefinitionTradeID();
		$this->listOfTrades = $this->tradesRolledUp;
		//$this->listOfReps = BrokerDAO::addTradesToBrokerAccounts($this->listOfReps, $this->listOfTrades);
		$this->calculateTradesByGrouping();
		$this->calculateTotals();
		$this->returnCommissionTotalsView();
	}

	/*
	 * TODO Move *View(s) to a View
	 */

	/*
	 * METHOD: RETURN COMMISSION TOTALS VIEW
	 */
	private function returnCommissionTotalsView() {
		$calcCount = count($this->calculatedTrades);
		$chartData[][] = null;
		$formatCols[][] = null;
		$counter = 0;
		for ($a = 0; $a < $calcCount; ++$a) {
			$this->calculatedTrades[$a][0] = $this->calculatedTrades[$a][0]." - ".HtmlComponents::individualRepName($this->calculatedTrades[$a][0]);
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

	private function returnTradeBlotterView() {
		echo "<table>";
		for ($i = 0; $i < count($this->listOfReps); $i++) {
			$broker = $this->listOfReps[$i];
			echo "<tr><table class=blotter-broker><th>Broker</th><td>" . $broker->getRepNumber() . "</td></table></tr>";
			for ($j = 0; $j < count($broker->getBrokerAccounts()); $j++) {
				$account = $broker->getBrokerAccounts();
				// Clear Commission and Concession from last Broker.
				$commissionTotal = 0.0;
				$concessionTotal = 0.0;
				echo "<tr><table class=blotter-account><th>Account#</th><td>" . $account[$j]->getAccountNumber() . "</td>".
					"<th>Short Name:</th><td>" . AccountDAO::retrieveAccountShortName($account[$j]->getAccountNumber()) . "</td>".
					"</table></tr>";
				echo "<tr><table class=blotter-trade><tr>";
				echo "<th>Trade Ref#</th>" .
						"<th>Trade Date</th>" .
						"<th>Settle Date</th>" .
						"<th>Acct Type</th>" .
						"<th>MKT Code</th>" .
						"<th>BLT Code</th>" .
						"<th>B/S Code</th>" .
						"<th>CXL Code</th>" .
						"<th>Quantity</th>" .
						"<th>CUSIP</th>" .
						"<th>Symbol</th>" .
						"<th>Sec Desc</th>" .
						"<th>Price</th>" .
						"<th>Principal</th>" .
						"<th>Commission</th>" .
						"<th>Concession</th>" .
						"<th>Sol Code</th>" .
						"<th>Sec Type</th>" .
						"<th>TradeID</th>" .
						"</tr>";
				for ($k = 0; $k < count($account[$j]->getAccountTrades()); $k++) {
					$trades = $account[$j]->getAccountTrades();
					$commissionTotal += $trades[$k]->getCommission();
					$concessionTotal += $trades[$k]->getConcession();
					echo "<tr>" .
						"<td>" . $trades[$k]->getTradeReferenceNumber() . "</td>" . 
						"<td>" . $trades[$k]->getTradeDate() . "</td>" .
						"<td>" . $trades[$k]->getSettleDate() . "</td>" .
						"<td style=text-align:center>" . $trades[$k]->getAccountType() . "</td>" .
						"<td style=text-align:center>" . $trades[$k]->getMarketCode() . "</td>" .
						"<td style=text-align:center>" . $trades[$k]->getBlotterCode() . "</td>" .
						"<td style=text-align:center>" . $trades[$k]->getBuySellCode() . "</td>" .
						"<td style=text-align:center>" . $trades[$k]->getCancelCode() . "</td>" .
						"<td>" . $trades[$k]->getQuantity() . "</td>" .
						"<td>" . $trades[$k]->getCusip() . "</td>" .
						"<td>" . $trades[$k]->getSymbol() . "</td>" .
						"<td>" . $trades[$k]->getSecurityDescription1() . " " . $trades[$k]->getSecurityDescription2() . "</td>" .
						"<td style=text-align:right>" . $this->formatAsCurrency($trades[$k]->getPrice()) . "</td>" .
						"<td style=text-align:right>" . $this->formatAsCurrency($trades[$k]->getPrincipal()) . "</td>" .
						"<td style=text-align:right>" . $this->formatAsCurrency($trades[$k]->getCommission()) . "</td>" .
						"<td style=text-align:right>" . $this->formatAsCurrency($trades[$k]->getConcession()) . "</td>" .
						"<td style=text-align:center>" . $trades[$k]->getSolicitationCode() . "</td>" .
						"<td style=text-align:center>" . $trades[$k]->getSecurityType() . "</td>" .
						"<td>" . $trades[$k]->getTradeReferenceTradeID() . "</td>" .
						"</tr>";
				}
				// A Totals row can go here...
				echo "<tr><th colspan=\"14\"></td><td style=text-align:right>".$this->formatAsCurrency($commissionTotal)."</td><td style=text-align:right>".$this->formatAsCurrency($concessionTotal)."</td><th colspan=\"3\"></td></tr>";
				echo "</tr>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}

	// New Methods for Dashboard...
	public function retrieveTopFiveProducers($timePeriod) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "";
		//echo $query."<br />";
		$result = @mysql_query($query);
		if ($result) {
			$incrementor = 0;
			while ($joint = @mysql_fetch_array($result, MYSQL_NUM)) {
				//
			}
			//
		}

		$database->closeConnection();
	}

	public function retrieveTopTenStockBuys($timePeriod) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "";
		//echo $query."<br />";
		$result = @mysql_query($query);
		if ($result) {
			$incrementor = 0;
			while ($joint = @mysql_fetch_array($result, MYSQL_NUM)) {
				//
			}
			//
		}

		$database->closeConnection();
	}

	public function retrieveTopTenStockSells($timePeriod) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "";
		//echo $query."<br />";
		$result = @mysql_query($query);
		if ($result) {
			$incrementor = 0;
			while ($joint = @mysql_fetch_array($result, MYSQL_NUM)) {
				//
			}
			//
		}

		$database->closeConnection();
	}

	public function retrieveMonthToDateGross($timePeriod) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "";
		//echo $query."<br />";
		$result = @mysql_query($query);
		if ($result) {
			$incrementor = 0;
			while ($joint = @mysql_fetch_array($result, MYSQL_NUM)) {
				//
			}
			//
		}

		$database->closeConnection();
	}

}