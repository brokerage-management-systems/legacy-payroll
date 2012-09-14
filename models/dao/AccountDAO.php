<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class AccountDAO {

	public function retrieveAccountsByBrokerNumbers($brokerNumbers) {
		for ($i = 0; $i < count($accountNumbers); $i++) {
			$accountList[$i] = retrieveAccountByBrokerNumber($brokerNumbers[$i]);
		}
		return $accountList;
	}
	
	public function retrieveAccountByBrokerNumber($brokerNumber) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("efilingRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT * FROM clientInfo WHERE repNum='$brokerNumber'";
		//echo $query;
		$result = @mysql_query($query);
		$acct = @mysql_fetch_row($result);
		$account = new Account();
		$account->setID($acct[0]);
		$account->setAccountNumber($acct[1]);
		$account->setFirstName($acct[3]);
		$account->setLastName($acct[2]);
		$account->setBrokerNumber($acct[4]);
		$account->setActive($acct[5]);
		
		$database->closeConnection();
		return $account;
	}
	
	/*
	 * METHOD: RETRIEVE ACCOUNTS BY DATE RANGE
	 */
	public function retrieveAccountsByDateRange($dateType, $searchDateStart, $searchDateEnd) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT DISTINCT BRANCH_01_a, ACCOUNT_NUMBER_01, REGISTERED_REP_OWNING_REP_RR_09 FROM TRDREV_TD";
		if ($dateType=="settlement") {
			$query .= " WHERE SETTLEMENT_DATE_01 BETWEEN '$searchDateStart' AND '$searchDateEnd'";
		} else if ($dateType=="trade") {
			$query .= " WHERE TRADE_DATE_01 BETWEEN '$searchDateStart' AND '$searchDateEnd'";
		}
		$query .= " ORDER BY REGISTERED_REP_OWNING_REP_RR_09, BRANCH_01_a, ACCOUNT_NUMBER_01 ASC";
		//echo $query."<br />"; // Show Query
		$result = @mysql_query($query);
		$counter = 0;
		while ($acctNums = @mysql_fetch_array($result, MYSQL_NUM)) {
			$listOfAccounts[$counter] = new Account();
			$listOfAccounts[$counter]->setAccountNumber($acctNums[0]."-".$acctNums[1]);
			$listOfAccounts[$counter]->setRepNumber($acctNums[2]);
			
			$counter++;
		}
		$database->closeConnection();
		return $listOfAccounts;
	}
	
	public function retrieveAccountsByNumbers($accountNumbers) {
		for ($i = 0; $i < count($accountNumbers); $i++) {
			$accountList[$i] = retrieveAccountByNumber($accountNumbers[$i]);
		}
		return $accountList;
	}
	
	public function retrieveAccountByNumber($accountNumber) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("efilingRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT * FROM clientInfo WHERE acctNum='$accountNumber'";
		//echo $query;
		$result = @mysql_query($query);
		$acct = @mysql_fetch_row($result);
		$account = new Account();
		$account->setID($acct[0]);
		$account->setAccountNumber($acct[1]);
		$account->setFirstName($acct[3]);
		$account->setLastName($acct[2]);
		$account->setBrokerNumber($acct[4]);
		$account->setActive($acct[5]);
		
		$database->closeConnection();
		return $account;
	}
	
	public function retrieveAccountShortName($accountNumber) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$branch = substr($accountNumber, 0, 3);
		$number = substr($accountNumber, 4, strlen($accountNumber));
		$query = "SELECT SHORT_NAME_02 FROM TRDREV_TD WHERE BRANCH_01_a='$branch' AND ACCOUNT_NUMBER_01='$number'";
		//InitializeSite::alertMessage($query);
		$result = @mysql_query($query);
		$shortName = @mysql_fetch_row($result);
		$database->closeConnection();
		return $shortName[0];
	}
	
	public function retrieveBrokerNameByNumber($accountNumber) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("efilingRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$nameQuery = "SELECT firstName, lastName FROM clientInfo WHERE acctNum='$accountNumber'";
		//echo $nameQuery;
		$nameResult = @mysql_query($nameQuery);
		$acctFullName = @mysql_fetch_row($nameResult);
		$database->closeConnection();
		return $acctFullName[0] . " " . $acctFullName[1];
	}

}