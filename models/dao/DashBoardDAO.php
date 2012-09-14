<?php

/*
 * METHOD: RETRIEVE TOP TEN BROKERS DATE RANGE
 * Creates and sets $this->listOfAccounts.
 */
private function retrieveTopTenBrokersDateRange() {
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