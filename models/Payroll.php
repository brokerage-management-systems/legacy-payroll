<?php

class Payroll {
	
	private $repNumber;
	private $commissionMonth;
	private $repName;
	private $monthLabel;
	private $grossFromSales;
	private $dealData;
	private $totalAdjustedGross;
	private $payoutPercent;
	private $netFromSales;
	private $overrideTotal;
	private $miscCreditData;
	private $optionData;
	private $netBeforeExpenses;
	private $expenseData;
	private $expenseTotal;
	private $owingData;
	private $amountPayable;
	private $advanceData;
	private $totalCheck;
	
	public function getRepNumber() {
		return $this->repNumber;
	}
	
	public function getCommissionMonth() {
		return $this->commissionMonth;
	}
	
	public function getRepName() {
		return $this->repName;
	}
	
	public function getMonthLabel() {
		return $this->monthLabel;
	}
	
	public function getGrossFromSales() {
		return $this->grossFromSales;
	}
	
	public function getDealData() {
		return $this->dealData;
	}
	
	public function getTotalAdjustedGross() {
		return $this->totalAdjustedGross;
	}
	
	public function getPayoutPercent() {
		return $this->payoutPercent;
	}
	
	public function getNetFromSales() {
		return $this->netFromSales;
	}
	
	public function getOverrideTotal() {
		return $this->overrideTotal;
	}
	
	public function getMiscCreditData() {
		return $this->miscCreditData;
	}
	
	public function getOptionData() {
		return $this->optionData;
	}
	
	public function getNetBeforeExpenses() {
		return $this->netBeforeExpenses;
	}
	
	public function getExpenseData() {
		return $this->expenseData;
	}
	
	public function getExpenseTotal() {
		return $this->expenseTotal;
	}
	
	public function getOwingData() {
		return $this->owingData;
	}
	
	public function getAmountPayable() {
		return $this->amountPayable;
	}
	
	public function getAdvanceData() {
		return $this->advanceData;
	}
	
	public function getTotalCheck() {
		return $this->totalCheck;
	}

	public function setRepNumber($val) {
		$this->repNumber = $val;
	}

	public function setCommissionMonth($val) {
		$this->commissionMonth = $val;
	}
	
	public function setRepName($val) {
		$this->repName = $val;
	}

	public function setMonthLabel($val) {
		$this->monthLabel = $val;
	}

	public function setGrossFromSales($val) {
		$this->grossFromSales = $val;
	}

	public function setDealData($val) {
		$this->dealData = $val;
	}

	public function setTotalAdjustedGross($val) {
		$this->totalAdjustedGross = $val;
	}

	public function setPayoutPercent($val) {
		$this->payoutPercent = $val;
	}

	public function setNetFromSales($val) {
		$this->netFromSales = $val;
	}

	public function setOverrideTotal($val) {
		$this->overrideTotal = $val;
	}

	public function setMiscCreditData($val) {
		$this->miscCreditData = $val;
	}

	public function setOptionData($val) {
		$this->optionData = $val;
	}

	public function setNetBeforeExpenses($val) {
		$this->netBeforeExpenses = $val;
	}

	public function setExpenseData($val) {
		$this->expenseData = $val;
	}

	public function setExpenseTotal($val) {
		$this->expenseTotal = $val;
	}

	public function setOwingData($val) {
		$this->owingData = $val;
	}

	public function setAmountPayable($val) {
		$this->amountPayable = $val;
	}

	public function setAdvanceData($val) {
		$this->advanceData = $val;
	}

	public function setTotalCheck($val) {
		$this->totalCheck = $val;
	}

	public function toString() {
		$payrollToString = "[Broker#: " . $this->repNumber . "<br />";
		$payrollToString .= "->Commission Month: " . $this->commissionMonth . "<br />";
		$payrollToString .= "->Broker: " . $this->repName . "<br />";
		$payrollToString .= "->Month: " . $this->monthLabel . "<br />";
		$payrollToString .= "->Gross From Sales: " . $this->grossFromSales . "<br />";
		// Array...
		$payrollToString .= "->Deal Data: " . $this->dealData . "<br />";
		//
		$payrollToString .= "->Total Adjusted Gross: " . $this->totalAdjustedGross . "<br />";
		$payrollToString .= "->Payout Percent: " . $this->payoutPercent . "<br />";
		$payrollToString .= "->Net From Sales: " . $this->netFromSales . "<br />";
		$payrollToString .= "->Override: " . $this->overrideTotal . "<br />";
		// Array...
		$payrollToString .= "->Misc Credit Data: " . $this->miscCreditData . "<br />";
		//
		// Array...
		$payrollToString .= "->Option Data: " . $this->optionData . "<br />";
		//
		$payrollToString .= "->Net Before Expenses: " . $this->netBeforeExpenses . "<br />";
		// Array...
		$payrollToString .= "->Expense Data: " . $this->expenseData . "<br />";
		//
		$payrollToString .= "->Expense Total: " . $this->expenseTotal . "<br />";
		// Array ...
		$payrollToString .= "->Owing Data: " . $this->owingData . "<br />";
		//
		$payrollToString .= "->Amount Payable: " . $this->amountPayable . "<br />";
		// Array...
		$payrollToString .= "->Advance Data: " . $this->advanceData . "<br />";
		//
		$payrollToString .= "->Total Check: " . $this->totalCheck . "]<br />";
		return $payrollToString;
	}
}