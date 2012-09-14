<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class PayrollDAO {

	private $OPTIONCODE = 8;
	private $todayIs;

	/*
	 * METHOD: FORMAT AS CURRENCY
	 * @param $value
	 * @return money_format('%1.2n', $value);
	 * Takes a variable of string, int, etc... and returns a formated string as $0.00.
	 */
	public function formatAsCurrency($value) {
		setlocale(LC_MONETARY, 'en_US');
		return money_format('%(1.2n', ($value ? $value : 0));
	}

	/*
	 * METHOD: CALCULATE DEAL ADVANCE BALANCE TOTALS
	 */
	public function sumDealAdvanceOwing($dabArray) {
		$dabTotal = 0;
		for ($i = 0; $i < count($dabArray); $i++) {
			$dabTotal += $dabArray[$i]['amount'];
		}
		return $dabTotal;
	}

	/*
	 * METHOD: CALCULATE EXPENSE
	 */
	public function sumExpenses($expenseData) {
		$expenseTotal = 0;
		for ($i = 0; $i < count($expenseData); $i++) {
			$expenseTotal += $expenseData[$i][1];
		}
		return $expenseTotal;
	}

	/*
	 * METHOD: SUBMIT PAYROLL
	 */
	public function submitPayroll($payroll) {
		$this->todayIs = date('Ymd');
		if ($this->todayIs <= $payroll->getCommissionMonth()) {
			echo "<p align=center>To early to submit payroll, try again after ".$payroll->getCommissionMonth()."</p>";
			exit();
		}
		$broker = $this->createBroker($payroll->getRepNumber());
		//echo "Broker: " . $broker->toString() . "<br />";
		$payroll->setRepName($broker->getFirstName() . " " . $broker->getLastname());

		// Set Date information
		$commDate = new CommissionDates();
		$commDate->setDates($payroll->getCommissionMonth());
		$settleDateStart = $commDate->dateInfo[0];
		$owingMonth = $commDate->dateInfo[1];
		$monthLabel = $commDate->dateInfo[2];
		$payroll->setMonthLabel($monthLabel);
			
		/*
		 * Check if payroll has already been submitted
		 * if so go right to view.
		 * Otherwise submit and then go to view
		 */
		if (!$this->checkIfPayrollHasBeenSubmitted($payroll->getRepNumber(), $payroll->getCommissionMonth())) {
			// Calculate Brokers Individual Gross
			$mainCommission = $this->calculateCommissionByRepNumberAndMonth($broker->getRepNumber(), $payroll->getCommissionMonth());
			//echo "Main Commission: "; print_r($mainCommission); echo "<br />";
				
			// Calculate Brokers Joint Rep Gross
			//echo "Number of joint reps " . count($broker->getJointReps()) . "<br />";
			for ($i = 0; $i < count($broker->getJointReps()); $i++) {
				$jointReps = $broker->getJointReps();
				$jointTempCommission = $this->calculateCommissionByRepNumberAndMonth($jointReps[$i]->getRepNumber(), $payroll->getCommissionMonth());
				//echo "Joint Temp Commission: " . $jointReps[$i]->getRepNumber() . " | "; print_r($jointTempCommission); echo "<br />";
				for ($j = 0; $j < count($jointTempCommission); $j++) {
					$jointCommission[$j] += bcdiv($jointTempCommission[$j], $jointReps[$i]->getSplitPercent(), 2);
				}
			}
			//echo "Joint Commission: "; print_r($jointCommission); echo "<br />";

			$grossFromSales = $mainCommission[1] + $jointCommission[1];
			//echo "Gross From Sales-> " . $grossFromSales . "<br />";

			// Calculate any Deals Broker participated in.
			$dealData = $this->retrieveAdvancesDealsOrOwing($broker->getRepNumber(), $payroll->getCommissionMonth(), "DEAL");
			//echo "Deal Data: "; print_r($dealData); echo "<br />";
			$dealTotal = $this->sumDealAdvanceOwing($dealData);
			//echo "Deal Total: " . $dealTotal . "<br />";

			// Total Cancels
			$cancelTotal = $mainCommission[0] + $jointCommission[0];
			//echo "Total Cancels: " . $cancelTotal . "<br />";

			// Total Commissions
			$totalAdjustedGross = $grossFromSales + $dealTotal;
			//echo "Total Adjusted Gross-> " . $totalAdjustedGross . "<br />";

			// Total Option Contracts
			$optionContractTotal = $mainCommission[2] + $jointCommission[2];
			//echo "Total Option Contracts: " . $optionContractTotal . "<br />";

			// Payout
			$brokerPayout = $this->calculatePayoutToBroker($broker, $totalAdjustedGross, $payroll->getCommissionMonth());
			//echo "Broker Payout: "; print_r($brokerPayout); echo "<br />";

			// Calculate Brokers Override Rep Gross
			//echo "Number of rep Overrides " . count($broker->getOverrideReps()) . "<br />";
			for ($i = 0; $i < count($broker->getOverrideReps()); $i++) {
				$overrideReps = $broker->getOverrideReps();
				$overrideTempCommission = $this->calculateCommissionByRepNumberAndMonth($overrideReps[$i]->getRepNumber(), $payroll->getCommissionMonth());
				//echo "Override Temp Commission: " . $overrideReps[$i]->getRepNumber() . " | "; print_r($overrideTempCommission); echo "<br />";
				for ($j = 0; $j < count($overrideTempCommission); $j++) {
					$overrideCommission[$j] += bcmul($overrideTempCommission[$j], $overrideReps[$i]->getSplitPercent(), 2);
				}
				$this->insertOverrideIntoDatabase($overrideReps[$i]->getRepNumber(), $payroll->getCommissionMonth(), $overrideReps[$i]->getSplitPercent(), bcmul($overrideTempCommission[1], $overrideReps[$i]->getSplitPercent(), 2), $broker->getRepNumber());
			}
			//echo "Override Commission: "; print_r($overrideCommission); echo "<br />";

			// Calculate any Misc Credits Broker received.
			$miscCreditData = $this->retrieveAdvancesDealsOrOwing($broker->getRepNumber(), $payroll->getCommissionMonth(), "MISC");
			//echo "Misc Credit Data: "; print_r($miscCreditData); echo "<br />";
			$miscCreditTotal = $this->sumDealAdvanceOwing($miscCreditData);
			//echo "Misc Credit Total: " . $miscCreditTotal . "<br />";

			// Insert remaining expenses and fees into the database
			$this->insertRemainingExpensesAndFees($broker, $payroll->getCommissionMonth(), $cancelTotal, $totalAdjustedGross, $optionContractTotal);

			// Option Fees
			$optionFee = $this->retrieveOptionFeeData($broker->getRepNumber(), $payroll->getCommissionMonth());
			//echo "Option Fees: "; print_r($optionFee); echo "<br />";

			// Expenses Data
			$expenseData = $this->retrieveExpensesData($broker->getRepNumber(), $payroll->getCommissionMonth());
			//echo "Expenses Data: "; print_r($expenseData); echo "<br />";
			$expenseTotal = $this->sumExpenses($expenseData);
			//echo "Expense Total: " . $expenseTotal . "<br />";

			// Calculate if Broker owes from previous months.
			$owingData = $this->retrieveAdvancesDealsOrOwing($broker->getRepNumber(), $payroll->getCommissionMonth(), "OWING");
			//echo "Owing Data: "; print_r($owingData); echo "<br />";
			$owingTotal = $this->sumDealAdvanceOwing($owingData);
			//echo "Owing Total: " . $owingTotal . "<br />";

			// Calculate if Broker has taken any advances.
			$advanceData = $this->retrieveAdvancesDealsOrOwing($broker->getRepNumber(), $payroll->getCommissionMonth(), "ADVANCE");
			//echo "Advance Data: "; print_r($advanceData); echo "<br />";
			$advanceTotal = $this->sumDealAdvanceOwing($advanceData);
			//echo "Advance Total: " . $advanceTotal . "<br />";

			// Net From Sales
			$netFromSales = $brokerPayout[0];

			// Net Before Expense
			$netBeforeExpenses = $netFromSales + $overrideCommission[1] + $miscCreditTotal - $optionFee['amount'];
			//echo "Net Before Expense: " . $netBeforeExpenses . "<br />";

			// Amount Payable
			$amountPayable = $netBeforeExpenses - $expenseTotal - $owingTotal;
			//echo "Amount Payable: " . $amountPayable . "<br />";

			// Total Check
			$totalCheck = $amountPayable - $advanceTotal;
			//echo "Total Check: " . $totalCheck . "<br />";

			$this->insertPayrollDataIntoDatabase($broker->getRepNumber(), $payroll->getCommissionMonth(), $grossFromSales, $totalAdjustedGross, $netFromSales, $netBeforeExpenses, $amountPayable, $totalCheck, $monthLabel, $owingMonth);

			$payroll->setGrossFromSales($this->formatAsCurrency($grossFromSales));
			$payroll->setDealData($dealData);
			$payroll->setTotalAdjustedGross($this->formatAsCurrency($totalAdjustedGross));
			$payroll->setPayoutPercent($brokerPayout[1]);
			$payroll->setNetFromSales($this->formatAsCurrency($netFromSales));
			$payroll->setOverrideTotal($this->formatAsCurrency($overrideCommission[1]));
			$payroll->setMiscCreditData($miscCreditData);
			$payroll->setOptionData($optionFee);
			$payroll->setNetBeforeExpenses($this->formatAsCurrency($netBeforeExpenses));
			$payroll->setExpenseData($expenseData);
			$payroll->setOwingData($owingData);
			$payroll->setAmountPayable($this->formatAsCurrency($amountPayable));
			$payroll->setAdvanceData($advanceData);
			$payroll->setTotalCheck($this->formatAsCurrency($totalCheck));

			if (false) {
				echo "Payroll Data Variables<br />";
				echo "Broker: " . $broker->toString() . "<br />";
				echo "Main Commission: "; print_r($mainCommission); echo "<br />";
				echo "Joint Commission: "; print_r($jointCommission); echo "<br />";
				echo "Gross From Sales-> " . $payroll->getGrossFromSales() . "<br />";
				echo "Deal Data: "; print_r($payroll->getDealData()); echo "<br />";
				echo "Deal Total: " . $dealTotal . "<br />";
				echo "Total Adjusted Gross-> " . $payroll->getTotalAdjustedGross() . "<br />";
				echo "Broker Payout: " . $payroll->getNetFromSales() . "<br />";
				echo "Payout Percent: " . $payroll->getPayoutPercent() . "<br />";
				echo "Override Commission: "; print_r($overrideCommission); echo "<br />";
				echo "Override Commission Total: " . $payroll->getOverrideTotal() . "<br />";
				echo "Misc Credit Data: "; print_r($payroll->getMiscCreditData()); echo "<br />";
				echo "Misc Credit Total: " . $miscCreditTotal . "<br />";
				echo "Total Option Contracts: " . $optionContractTotal . "<br />";
				echo "Option Fees: "; print_r($payroll->getOptionData()); echo "<br />";
				echo "Net Before Expense: " . $payroll->getNetBeforeExpenses() . "<br />";
				echo "Total Cancels: " . $cancelTotal . "<br />";
				echo "Expenses Data: "; print_r($payroll->getExpenseData()); echo "<br />";
				echo "Expense Total: " . $expenseTotal . "<br />";
				echo "Owing Data: "; print_r($payroll->getOwingData()); echo "<br />";
				echo "Owing Total: " . $owingTotal . "<br />";
				echo "Amount Payable: " . $payroll->getAmountPayable() . "<br />";
				echo "Advance Data: "; print_r($payroll->getAdvanceData()); echo "<br />";
				echo "Advance Total: " . $advanceTotal . "<br />";
				echo "Total Check: " . $payroll->getTotalCheck() . "<br />";
			}

			return $payroll;
		} else {
			return $this->viewPayroll($payroll);
		}
	}

	/*
	 * METHOD: UNSUBMIT PAYROLL
	 * params $repNumber, $commissionMonth
	 * Deletes values from:
	 * - monthEndPayoutOverride
	 * - miscExpenses
	 * - submittedPayrollData
	 * - dealAdvBal
	 */
	public function unsubmitPayroll($repNumber, $commissionMonth) {
		$dates = new CommissionDates();
		$dates->setDates($commissionMonth);

		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataWrite");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
			
		$deletePayOverQuery = "DELETE FROM monthEndPayoutOverride WHERE repNum='$repNumber' AND monthEndDate='$commissionMonth'";
		//echo $deletePayOverQuery . "<br />";
		$deletePayOverResult = mysql_query($deletePayOverQuery);
		printf("Payout/Override Records Deleted: %d<br />", mysql_affected_rows());

		$deleteMiscExpQuery = "DELETE FROM miscExpenses WHERE repNum='$repNumber' AND monthEndDate='$commissionMonth' AND (expense='E&O INSURANCE' OR expense='OPTION CONTRACTS' OR expense='CXL/REBILLS 40/120' OR expense='SALES ASSISTANT')";
		//echo $deleteMiscExpQuery . "<br />";
		$deleteMiscExpResult = mysql_query($deleteMiscExpQuery);
		printf("Expense Records Deleted: %d<br />", mysql_affected_rows());

		$deleteCommissionQuery = "DELETE FROM submittedPayrollData WHERE repNum='$repNumber' AND monthEndDate='$commissionMonth'";
		//echo $deleteCommissionQuery . "<br />";
		$deleteCommissionResult = mysql_query($deleteCommissionQuery);
		printf("Commission Records Deleted: %d<br />", mysql_affected_rows());

		$deleteOwingQuery = "DELETE FROM dealAdvBal WHERE repNum='$repNumber' AND monthEndDate='".$dates->dateInfo[1]."' AND type='OWING'";
		//echo $deleteOwingQuery . "<br />";
		$deleteOwingResult = mysql_query($deleteOwingQuery);
		printf("Owing Records Deleted: %d<br />", mysql_affected_rows());

		$database->closeConnection();
	}

	/*
	 * METHOD: VIEW PAYROLL
	 */
	private function viewPayroll($payroll) {

		$payrollData = $this->retrieveSubmittedPayrollData($payroll->getRepNumber(), $payroll->getCommissionMonth());
		$dealData = $this->retrieveAdvancesDealsOrOwing($payroll->getRepNumber(), $payroll->getCommissionMonth(), "DEAL");
		$payoutPercent = $this->retrievePayoutPercent($payroll->getRepNumber(), $payroll->getCommissionMonth());
		$miscCreditData = $this->retrieveAdvancesDealsOrOwing($payroll->getRepNumber(), $payroll->getCommissionMonth(), "MISC");
		$optionFee = $this->retrieveOptionFeeData($payroll->getRepNumber(), $payroll->getCommissionMonth());
		$overrideCommission = $this->retrieveOverrideData($payroll->getRepNumber(), $payroll->getCommissionMonth());
		$expenseData = $this->retrieveExpensesData($payroll->getRepNumber(), $payroll->getCommissionMonth());
		$owingData = $this->retrieveAdvancesDealsOrOwing($payroll->getRepNumber(), $payroll->getCommissionMonth(), "OWING");
		$advanceData = $this->retrieveAdvancesDealsOrOwing($payroll->getRepNumber(), $payroll->getCommissionMonth(), "ADVANCE");

		$payroll->setGrossFromSales($this->formatAsCurrency($payrollData['grossFromSales']));
		$payroll->setDealData($dealData);
		$payroll->setTotalAdjustedGross($this->formatAsCurrency($payrollData['totalAdjustedGross']));
		$payroll->setPayoutPercent($payoutPercent);
		$payroll->setNetFromSales($this->formatAsCurrency($payrollData['netFromSales']));
		$payroll->setOverrideTotal($this->formatAsCurrency($overrideCommission));
		$payroll->setMiscCreditData($miscCreditData);
		$payroll->setOptionData($optionFee);
		$payroll->setNetBeforeExpenses($this->formatAsCurrency($payrollData['netBeforeExpenses']));
		$payroll->setExpenseData($expenseData);
		$payroll->setOwingData($owingData);
		$payroll->setAmountPayable($this->formatAsCurrency($payrollData['amountPayable']));
		$payroll->setAdvanceData($advanceData);
		$payroll->setTotalCheck($this->formatAsCurrency($payrollData['totalCheck']));

		if (false) {
			echo "Payroll Data Variables<br />";
			echo "Broker: " . $payroll->getRepNumber() . " - " . $payroll->getRepName() . "<br />";
			echo "Gross From Sales-> " . $payroll->getGrossFromSales() . "<br />";
			echo "Deal Data: "; print_r($payroll->getDealData()); echo "<br />";
			echo "Total Adjusted Gross-> " . $payroll->getTotalAdjustedGross() . "<br />";
			echo "Broker Payout: " . $payroll->getNetFromSales() . "<br />";
			echo "Payout Percent: " . $payroll->getPayoutPercent() . "<br />";
			echo "Override Commission: " . $payroll->getOverrideTotal() . "<br />";
			echo "Misc Credit Data: "; print_r($payroll->getMiscCreditData()); echo "<br />";
			echo "Option Fees: "; print_r($payroll->getOptionData()); echo "<br />";
			echo "Net Before Expense: " . $payroll->getNetBeforeExpenses() . "<br />";
			echo "Expenses Data: "; print_r($payroll->getExpenseData()); echo "<br />";
			echo "Owing Data: "; print_r($payroll->getOwingData()); echo "<br />";
			echo "Amount Payable: " . $payroll->getAmountPayable() . "<br />";
			echo "Advance Data: "; print_r($payroll->getAdvanceData()); echo "<br />";
			echo "Total Check: " . $payroll->getTotalCheck() . "<br />";
		}
			
		return $payroll;
	}

	/*
	 * METHOD: BUNCH TRADES BY TRADE DEFINITION TRADE ID
	 * Takes an array of trades
	 * Tests if 2 trades have equal TradeDefTradeIDs and if true bunchs them and adds to new array.
	 * Otherwise just adds the trade to the new array
	 * After all trades have been tested, returns new array of Trades.
	 */
	private function bunchTradesByTradeDefinitionTradeID($arrayOfPayrollTrades) {
		$tradeCount = count($arrayOfPayrollTrades);
		$newTradeCounter = 0;
		for ($tradeIndex = 0; $tradeIndex < $tradeCount; $tradeIndex++) {
			$tempTrade = null;
			if ($tradeIndex == $tradeCount - 1) {
				//Last Trade so just add Trade to new array of Trades
				$tempTrade = $arrayOfPayrollTrades[$tradeIndex];
			} else {
				$tempTrade = $arrayOfPayrollTrades[$tradeIndex];
				$firstTrade = $tempTrade;
				$secondTrade = $arrayOfPayrollTrades[$tradeIndex+1];
				while ($firstTrade->getTradeReferenceTradeID() == $secondTrade->getTradeReferenceTradeID()) {
					$tradeIndex++;
					$tempTrade = PayrollTrade::bunchTwoTrades($firstTrade, $secondTrade);
					if ($tradeIndex == $tradeCount - 1) {
						//Last Trade, break out of loop
						break;
					} else {
						$firstTrade = $tempTrade;
						$secondTrade = $arrayOfPayrollTrades[$tradeIndex+1];
					}
				}
			}
			$tradesRolledUp[$newTradeCounter++] = $tempTrade; // new trade
		}
		return $tradesRolledUp;
	}

	/*
	 * METHOD: CALCULATE COMMISSION BY REP NUMBER AND MONTH
	 */
	private function calculateCommissionByRepNumberAndMonth($repNumber, $commissionMonth) {
		$logTransaction = null;
		$commDate = new CommissionDates();
		$commDate->setDates($commissionMonth);
		$settleDateStart = $commDate->dateInfo[0];

		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectQuery = "SELECT REGISTERED_REP_OWNING_REP_RR_09, CANCEL_CODE_01, SECURITY_TYPE_02, QUANTITY_03, TRADE_COMMISSION_04, TRADE_CONCESSION_05, TRADE_DEFINITION_TRADE_ID_12 FROM TRDREV_TD";
		$selectQuery .= " WHERE REGISTERED_REP_OWNING_REP_RR_09='$repNumber'";
		$selectQuery .= " AND SETTLEMENT_DATE_01 BETWEEN '$settleDateStart' AND '$commissionMonth'";
		$selectQuery .= " ORDER BY BRANCH_01_a, ACCOUNT_NUMBER_01, SETTLEMENT_DATE_01, TRADE_DEFINITION_TRADE_ID_12 ASC"; // use RUN_DATE_01 instead of $dateModifier
		//InitializeSite::alertMessage($selectQuery);

		$result = @mysql_query($selectQuery);
		$counter = 0;
		while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			$tempTrade = new PayrollTrade();
			$tempTrade->setRepNumber($row[0]);
			$tempTrade->setCancelCode($row[1]);
			$tempTrade->setSecurityType($row[2]);
			$tempTrade->setQuantity($row[3]);
			$tempTrade->setCommission($row[4]);
			$tempTrade->setConcession($row[5]);
			$tempTrade->setTradeReferenceTradeID($row[6]);
			$payrollTrades[$counter++] = $tempTrade;
		}
		$database->closeConnection();
		$rolledPayrollTrades = $this->bunchTradesByTradeDefinitionTradeID($payrollTrades);
		$summedCancels = $this->sumCancels($rolledPayrollTrades);
		$summedCommissions = $this->sumCommissionConcession($rolledPayrollTrades);
		$summedOptionContracts = $this->sumOptionsContracts($rolledPayrollTrades);
		return array($summedCancels, $summedCommissions, $summedOptionContracts);
		// TODO DELETE - Old process below...
		/*
		 $cancelCount = 0;
		 $commissionSum = 0;
		 $concessionSum = 0;
		 $optionContracts = 0;
		 $logTransaction .= "Rep: $row[0] - Commission: " . $commissionSum . " | Concession: " . $concessionSum . "<br />";
		 	
		 while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
			// assign db result to var
			$cancelCode = $row[1];
			$tradeType = $row[2];

			// Cancels
			$cancelCount += $cancelCode;

			// Commission & Concession
			if ($cancelCode == 1) { //Delete from Commission and Concession
			//$logTransaction .= "Cancel - " . (int)$row[4] . " ";
			$commissionSum -= (int)$row[4];
			$concessionSum -= (int)$row[5];
			} else {
			//$logTransaction .= "Trade  + " . (int)$row[4] . " ";
			$commissionSum += (int)$row[4];
			$concessionSum += (int)$row[5];
			}
			//$logTransaction .= " Commission: " . $commissionSum . " | Concession: " . $concessionSum . " - Account#: $row[6]-$row[7] <br />";

			// Option Contracts
			if($tradeType == $this->OPTIONCODE) {
			$quantity = substr($row[3],0,-2);
			$quantity = substr_replace($quantity, ".",-3,0);
			$quantity = (int)$quantity;
			if ($cancelCode!=1) {
			$optionContracts += $quantity; // Option Contracts
			} else if ($cancelCode==1) {
			$optionContracts -= $quantity; // Option Contracts
			}
			}
			}
			//$logTransaction .= "Total  = Commission: " . $commissionSum . " | Concession: " . $concessionSum . "<br />";
			$commissionSum = $commissionSum + $concessionSum;
			$commissionSum = substr_replace($commissionSum, ".", -2, 0);
			// Why dont I sum concession?
			//$logTransaction .= "SUM   = Commission: " . $commissionSum . "<br />";
			//echo $logTransaction;

			$database->closeConnection();
			return array($cancelCount, $commissionSum, $optionContracts);
			*/
	}

	private function sumCancels($arrayOfPayrollTrades) {
		$counter = 0;
		for ($index = 0; $index < count($arrayOfPayrollTrades); $index++) {
			$counter += $arrayOfPayrollTrades[$index]->getCancelCode();
		}
		return $counter;
	}

	private function sumCommissionConcession($arrayOfPayrollTrades) {
		// We dont need to check the cancel code because the commission sign is changed during setCommission and setConcession
		$commissionSum = 0;
		for ($index = 0; $index < count($arrayOfPayrollTrades); $index++) {
			$commissionSum += $arrayOfPayrollTrades[$index]->getCommission() + $arrayOfPayrollTrades[$index]->getConcession();
		}
		return $commissionSum;
	}

	private function sumOptionsContracts($arrayOfPayrollTrades) {
		$optionContracts = 0;
		for ($index = 0; $index < count($arrayOfPayrollTrades); $index++) {
			if($arrayOfPayrollTrades[$index]->getSecurityType() == $this->OPTIONCODE) {
				if ($arrayOfPayrollTrades[$index]->getCancelCode() == 1) {
					$optionContracts -= $arrayOfPayrollTrades[$index]->getQuantity();
				} else {
					$optionContracts += $arrayOfPayrollTrades[$index]->getQuantity();
				}
			}
		}
		return $optionContracts;
	}

	/*
	 * METHOD: CALCULATE PAYOUT TO BROKER (INSERT DATA INTO DATABASE)
	 */
	private function calculatePayoutToBroker($broker, $grossToCompare, $commissionMonth) {
		for ($i = 0; $i < count($broker->getPayoutValues()); $i++) {
			$payoutValues = $broker->getPayoutValues();
			if ($grossToCompare > $payoutValues[$i]->getMinimum() && $grossToCompare < $payoutValues[$i]->getMaximum()) {
				$payoutToBroker = bcmul($grossToCompare, $payoutValues[$i]->getPayPercent(), 2);
				$payoutPercent = $payoutValues[$i]->getPayPercent() * 100;
				$payoutPercent .= "%";
				//echo $payoutValues[$i]->getPayPercent() . " x " . $grossToCompare . " = " . $payoutToBroker . " | " . $payoutPercent . " Payout<br />";
			}
		}
		
		// If no rep structure set values to 0
		//TODO - I should probably check for a broker structure before even attempting to run payroll
		$payoutToBroker = $payoutToBroker ? $payoutToBroker : 0;
		$payoutPercent = $payoutPercent ? $payoutPercent : 0;
		
		// Insert data into database
		if ($this->todayIs > $commissionMonth) {
			$database = new DatabaseConnection();
			$database->setConnectionSettings("tradeDataWrite");
			if ($GLOBALS['DEBUG']) {
				$database->changeToDevelopmentDatabase();
			}
			$database->openConnection();
			$selectQuery = "SELECT * FROM monthEndPayoutOverride WHERE monthEndDate='$commissionMonth' AND repNum='".$broker->getRepNumber()."' AND type='Payout'";
			//echo $selectQuery . "<br />";
			$selectResult = @mysql_query($selectQuery);
			$wasSubmited = @mysql_num_rows($selectResult);
			if ($wasSubmited < 1) {
				// Add values to database...
				$submitQuery = "INSERT INTO monthEndPayoutOverride (repNum, monthEndDate, percent, amount, type, misc) VALUES ('".$broker->getRepNumber()."', '$commissionMonth', '$payoutPercent', '$payoutToBroker', 'Payout', '')";
				//echo $submitQuery . "<br />";
				$submitResult = @mysql_query($submitQuery);
				//echo $submitResult;
			}
			$database->closeConnection();
		}

		return array($payoutToBroker, $payoutPercent);
	}

	/*
	 * METHOD: CHECK IF PAYROLL HAS BEEN SUBMITTED
	 */
	private function checkIfPayrollHasBeenSubmitted($repNumber, $commissionMonth) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$wasSubmittedQuery = "SELECT repNum, monthEndDate FROM submittedPayrollData WHERE repNum='$repNumber' AND monthEndDate='$commissionMonth'";
		//echo $wasSubmittedQuery . "<br />";
		$wasSubmittedResult = @mysql_query($wasSubmittedQuery);
		$wasSubmitted = mysql_num_rows($wasSubmittedResult);
		//echo "Result Rows: " . $wasSubmitted . "<br />";
		$database->closeConnection();

		if($wasSubmitted < 1) {
			//echo "Payroll for this broker has not yet been submitted.<br />";
			return false;
		} else {
			//echo "Payroll for this broker has already been submitted.<br />";
			return true;
		}
	}

	/*
	 * METHOD: CREATE BROKER
	 */
	private function createBroker($repNumber) {
		$broker = new Broker();
		$brokerDAO = new BrokerDAO();
		$broker = $brokerDAO->retrieveBrokerByNumber($repNumber);
		$broker = $brokerDAO->addJointRepsToBroker($broker);
		$broker = $brokerDAO->addOverrideRepsToBroker($broker);
		$broker = $brokerDAO->addBrokerPayoutToBroker($broker);
		$broker = $brokerDAO->addSalesAssistantToBroker($broker);

		return $broker;
	}

	/*
	 * METHOD: INSERT OVERRIDE INTO DATABASE
	 */
	private function insertOverrideIntoDatabase($overrideRep, $commissionMonth, $splitPercent, $overrideCommission, $mainRep) {
		// Submit Override into database
		if ($this->todayIs > $commissionMonth) {
			$database = new DatabaseConnection();
			$database->setConnectionSettings("tradeDataWrite");
			if ($GLOBALS['DEBUG']) {
				$database->changeToDevelopmentDatabase();
			}
			$database->openConnection();
			$selectQuery = "SELECT * FROM monthEndPayoutOverride WHERE monthEndDate='$commissionMonth' AND repNum='$mainRep' AND misc='$overrideRep' AND type='Override'";
			//echo $selectQuery . "<br />";
			$selectResult = @mysql_query($selectQuery);
			$wasSubmited = @mysql_num_rows($selectResult);
			if ($wasSubmited < 1) {
				// Add values to database...
				$submitQuery = "INSERT INTO monthEndPayoutOverride (repNum, monthEndDate, percent, amount, type, misc) VALUES ('$mainRep', '$commissionMonth', '$splitPercent', '$overrideCommission', 'Override', '$overrideRep')";
				//echo $submitQuery . "<br />";
				$submitResult = @mysql_query($submitQuery);
				//echo $submitResult;
			}
			$database->closeConnection();
		}
	}

	/*
	 * METHOD: INSERT PAYROLL DATA INTO DATABASE
	 * params $repNumber, $commissionMonth
	 * Inserts data into database.
	 */
	private function insertPayrollDataIntoDatabase($repNumber, $commissionMonth, $grossFromSales, $totalAdjustedGross, $netFromSales, $netBeforeExpenses, $amountPayable, $totalCheck, $monthLabel, $owingMonth) {

		if ($GLOBALS['DEBUG']) {
			echo "Data being inserted into submittedPayrollData.<br />";
			echo "RepNumber: " . $repNumber . "<br />" .
		 		"Month End Date: " . $commissionMonth . "<br />" .
		 		"Gross From Sales: " . $grossFromSales . "<br />" .
		 		"Total Adjusted Gross: " . $totalAdjustedGross . "<br />" .
		 		"Net From Sales: " . $netFromSales . "<br />" .
		 		"Net Before Expenses: " . $netBeforeExpenses . "<br />" .
		 		"Amount Payable: " . $amountPayable . "<br />" .
		 		"Total Check: " . $totalCheck . "<br />" .
		 		"Todays Date: " . $this->todayIs . "<br />" .
		 		"Commission Month: " . $commissionMonth . "<br />" .
		 		"Month Label: " . $monthLabel . "<br />" .
		 		"Owing Month: " . $owingMonth . "<br />";
		}

		if ($this->todayIs > $commissionMonth) {
			$database = new DatabaseConnection();
			$database->setConnectionSettings("tradeDataWrite");
			if ($GLOBALS['DEBUG']) {
				$database->changeToDevelopmentDatabase();
			}
			$database->openConnection();

			$wasSubmittedQuery = "SELECT repNum, monthEndDate FROM submittedPayrollData WHERE repNum='$repNumber' AND monthEndDate='$commissionMonth'";
			//echo $wasSubmittedQuery . "<br />";
			$wasSubmittedResult = @mysql_query($wasSubmittedQuery);
			$wasSubmitted = mysql_num_rows($wasSubmittedResult);
			//echo "Result Rows: " . $wasSubmitted . "<br />";
			if($wasSubmitted < 1) {
				$payReportQuery = "INSERT INTO submittedPayrollData (repNum, monthEndDate, grossFromSales, totalAdjustedGross, netFromSales, netBeforeExpenses, amountPayable, totalCheck) VALUES ('$repNumber','$commissionMonth','$grossFromSales','$totalAdjustedGross','$netFromSales', '$netBeforeExpenses', '$amountPayable', '$totalCheck')";
				//echo $payReportQuery . "<br />";
				$reportResult = @mysql_query($payReportQuery);
				if($totalCheck < 0) {
					$totalCheck = abs($totalCheck);
					$owingQuery = "INSERT INTO dealAdvBal (repNum, monthEndDate, type, amount, misc) VALUES ('$repNumber','$owingMonth','OWING','$totalCheck','$monthLabel')";
					//echo $owingQuery . "<br />";
					$owingResult = @mysql_query($owingQuery);
				}
			}
			$database->closeConnection();
		} else { echo $this->todayIs . " > " . $commissionMonth . "<br />"; }
	}

	/*
	 * METHOD: INSERT REMAINING EXPENSES AND FEES
	 * TODO: Get Fee values from table.
	 */
	private function insertRemainingExpensesAndFees($broker, $commissionMonth, $totalCancels, $totalCommission, $optionContracts) {
		$repNumber = $broker->getRepNumber();

		// Set Sales Assistant and related Data.
		$salesAssistant = $broker->getSalesAssistant(); //print_r($salesAssistant); echo "<br />";
		if ($salesAssistant!=null) {
			$salesAssistantData = $salesAssistant[0]->getBrokers(); //print_r($salesAssistantData); echo "<br />";
			$salesAssistant[0]->setPercent($salesAssistantData[0][1]);
			$saPercent = $salesAssistant[0]->getPercent();
		} else {
			// No Sales Assistant
			$saPercent = 0;
		}

		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataWrite");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
			
		if ($this->todayIs > $commissionMonth) {
			$wasSubmittedQuery = "SELECT repNum, monthEndDate FROM miscExpenses WHERE repNum='$repNumber' AND monthEndDate='$commissionMonth' AND (expense='CXL/REBILLS 40/120' OR expense='E&O INSURANCE' OR expense='OPTION CONTRACTS' OR expense='SALES ASSISTANT')";
			//echo $wasSubmittedQuery . "<br />";
			$wasSubmittedResult = @mysql_query($wasSubmittedQuery);
			$wasSubmitted = mysql_num_rows($wasSubmittedResult);
			//echo "Returned Rows: " . $wasSubmitted . "<br />";
			if($wasSubmitted < 1) {
				// Add Option Contracts and Fees
				$optionContractFees = bcmul($optionContracts, 1.25, 2);
				// Add Cancel Fee.
				$cancelFee = bcmul($totalCancels,20,2);
				// Sales Assistant Fee
				$saExpense = bcmul($totalCommission, $saPercent, 2);
				$saPercent = $saPercent * 100;
				$saPercent .= "%";
				// Insert Expenses
				$insertQuery = "INSERT INTO miscExpenses (repNum,monthEndDate,expense,amount,misc) VALUES ";
				$insertQuery .= "('$repNumber', '$commissionMonth', 'E&O INSURANCE', '350.00', ''), "; // Add E&O Insurance
				if ($optionContractFees > 0) {
					$insertQuery .= "('$repNumber', '$commissionMonth', 'OPTION CONTRACTS', '$optionContractFees', '$optionContracts'), ";
				}
				if ($cancelFee > 0) {
					$insertQuery .= "('$repNumber', '$commissionMonth', 'CXL/REBILLS 40/120', '$cancelFee', '$totalCancels'), ";
				}
        if ($saExpense > 0) {
					//$insertQuery .= "('$repNumber','$commissionMonth','SALES ASSISTANT','$saExpense','$saPercent')"; // Old, inserted the percent in the misc field. Now we insert the sales assistants id
					$insertQuery .= "('$repNumber','$commissionMonth','SALES ASSISTANT','$saExpense','".$salesAssistant[0]->getSalesAssistantId()."'), ";
				}
				$insertQuery = substr($insertQuery, 0, -2);
        // echo "$insertQuery<br />";
        $insertResult = @mysql_query($insertQuery);
			}
		}
		$database->closeConnection();
	}

	/*
	 * METHOD: RETRIEVE ADVANCES DEALS OR OWING
	 */
	private function retrieveAdvancesDealsOrOwing($repNumber, $commissionMonth, $type) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectQuery = "SELECT type, amount, misc FROM dealAdvBal";
		$selectQuery .= " WHERE repNum='$repNumber' AND monthEndDate='$commissionMonth' AND type='$type'";
		//echo $selectQuery . "<br />";
		$selectResult = mysql_query($selectQuery);

		$dabMisc = array(null);
		$counter = 0;
		switch ($type) {
			case 'DEAL':
				while($row = mysql_fetch_array($selectResult)) {
					//echo $counter."<br />";
					$dabMisc[$counter]['type'] = $row[0];
					$dabMisc[$counter]['amount'] = $row[1];
					$dabMisc[$counter]['misc'] = $row[2];
					$counter++;
					//echo $dabMisc[$counter]['type'] . " | " . $dabMisc[$counter]['amount'] . " | " . $dabMisc[$counter]['misc'] . "<br />";
				}
				break;
			case 'MISC':
				while($row = mysql_fetch_array($selectResult)) {
					//echo $counter."<br />";
					$dabMisc[$counter]['type'] = $row[0];
					$dabMisc[$counter]['amount'] = $row[1];
					$dabMisc[$counter]['misc'] = $row[2];
					$counter++;
					//echo $dabMisc[$counter]['type'] . " | " . $dabMisc[$counter]['amount'] . " | " . $dabMisc[$counter]['misc'] . "<br />";
				}
				break;
			case 'OWING':
				while($row = mysql_fetch_array($selectResult)) {
					//echo $counter."<br />";
					$dabMisc[$counter]['type'] = $row[0];
					$dabMisc[$counter]['amount'] = $row[1];
					$dabMisc[$counter]['misc'] = $row[2];
					$counter++;
					//echo $dabMisc[$counter]['type'] . " | " . $dabMisc[$counter]['amount'] . " | " . $dabMisc[$counter]['misc'] . "<br />";
				}
				break;
			case 'ADVANCE':
				while($row = mysql_fetch_array($selectResult)) {
					//echo $counter."<br />";
					$dabMisc[$counter]['type'] = $row[0];
					$dabMisc[$counter]['amount'] = $row[1];
					$dabMisc[$counter]['misc'] = $row[2];
					$counter++;
					//echo $dabMisc[$counter]['type'] . " | " . $dabMisc[$counter]['amount'] . " | " . $dabMisc[$counter]['misc'] . "<br />";
				}
				break;
			default :
				$dabMisc = "NA";
				break;
		}
		$database->closeConnection();
		return $dabMisc;
	}

	/*
	 * METHOD: RETRIEVE EXPENSES DATA
	 */
	private function retrieveExpensesData($repNumber, $commissionMonth) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		// Get All Expenses except Option Contracts...
		$selectExpensesQuery = "SELECT expense, amount, misc FROM miscExpenses WHERE repNum='$repNumber'";
		$selectExpensesQuery .= " AND monthEndDate='$commissionMonth' AND expense!='OPTION CONTRACTS' ORDER BY expense";
		//echo $selectExpensesQuery;
		$selectExpensesResult = mysql_query($selectExpensesQuery);
		if($selectExpensesResult) {
			$counter = 0;
			while($row = mysql_fetch_array($selectExpensesResult)) {
				$expenseData[$counter][0] = $row[0];
				$expenseData[$counter][1] = $row[1];
				$expenseData[$counter][2] = $row[2];
				$counter++;
			}
		}
		$database->closeConnection();
		return $expenseData;
	}

	/*
	 * METHOD: RETRIEVE OPTION FEE DATA
	 */
	private function retrieveOptionFeeData($repNumber, $commissionMonth) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		// Get Option Contract Expense
		$selectOptionsQuery = "SELECT amount, misc FROM miscExpenses WHERE repNum='$repNumber'";
		$selectOptionsQuery .= " AND monthEndDate='$commissionMonth' AND expense='OPTION CONTRACTS'";
		//echo $selectOptionsQuery;
		$selectOptionsResult = mysql_query($selectOptionsQuery);
		if($selectOptionsResult) {
			while($optRow = mysql_fetch_array($selectOptionsResult)) {
				$optionFee['amount']	= $optRow[0];
				$optionFee['contracts']	= $optRow[1];
			}
		}
		$database->closeConnection();
		return $optionFee;
	}

	/*
	 * METHOD: RETRIEVE OVERRIDE DATA
	 * @param $repNumber, $commissionMonth
	 * @return $overrideCommission
	 */
	private function retrieveOverrideData($repNumber, $commissionMonth) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		$query = "SELECT amount FROM monthEndPayoutOverride WHERE monthEndDate='$commissionMonth' AND repNum='".$repNumber."' AND type='Override'";
		//echo $query . "<br />";
		$result = @mysql_query($query);
		while ($row = @mysql_fetch_array($result)) {
		  $amount += $row[0];
		}
		$database->closeConnection();
		return $amount;
	}
	
	/*
	 * METHOD: RETRIEVE PAYOUT PERCENT
	 * @param $repNumber, $commissionMonth
	 * @return $payoutPercent
	 */
	private function retrievePayoutPercent($repNumber, $commissionMonth) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		$query = "SELECT percent FROM monthEndPayoutOverride WHERE monthEndDate='$commissionMonth' AND repNum='".$repNumber."' AND type='Payout'";
		//echo $query . "<br />";
		$result = @mysql_query($query);
		if ($result) {
			$row = @mysql_fetch_row($result);
			$percent = $row[0];
		}
		$database->closeConnection();
		return $percent;
	}

	/*
	 * METHOD: RETRIEVE SUBMITTED PAYROLL DATA
	 * @params $repNumber, $commissionMonth
	 * @return $payData (associative array)
	 *  : Array Elements:
	 *  - grossFromSales
	 *  - totalAdjustedGross
	 *  - netFromSales
	 *  - netBeforeExpenses
	 *  - amountPayable
	 *  - totalCheck
	 */
	private function retrieveSubmittedPayrollData($repNumber, $commissionMonth) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
			
		$query = "SELECT grossFromSales, totalAdjustedGross, netFromSales, netBeforeExpenses, amountPayable, totalCheck FROM submittedPayrollData WHERE repNum='$repNumber' AND monthEndDate='$commissionMonth'";
		//echo $query . "<br />";
		$result = @mysql_query($query);
		if($result) {
			$pay = @mysql_fetch_row($result);
			$payData['grossFromSales']		= $pay[0];
			$payData['totalAdjustedGross']	= $pay[1];
			$payData['netFromSales']		= $pay[2];
			$payData['netBeforeExpenses']	= $pay[3];
			$payData['amountPayable']		= $pay[4];
			$payData['totalCheck']			= $pay[5];
		}
		$database->closeConnection();
		return $payData;
	}

}