<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class ClientDAO {

	public function retrieveClientsByAccountNumbers($accountNumber) {
		for ($i = 0; $i < count($accountNumber); $i++) {
			$accountList[$i] = retrieveClientByAccountNumber($accountNumber[$i]);
		}
		return $accountList;
	}

	public function retrieveClientByAccountNumber($accountNumber) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("efilingRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT `key`, acctNum, lastName, firstName, repNum, active FROM clientInfo WHERE acctNum='$accountNumber'";
		//echo $query . "<br />";
		$result = @mysql_query($query);
		$acct = @mysql_fetch_row($result);
		$client = new Client();
		$client->setClientID($acct[0]);
		$client->setFullAccountNumber($acct[1]);
		$client->setLastName($acct[2]);
		$client->setFirstName($acct[3]);
		$client->setRepNumber($acct[4]);
		$client->setActive($$acct[5]);

		$database->closeConnection();

		return $client;
	}

	public function retrieveClientsByMultipleRepNumbers($repNumber) {
		for ($i = 0; $i < count($repNumber); $i++) {
			$accountList[$i] = retrieveClientByRepNumber($repNumber[$i]);
		}
		return $accountList;
	}

	public function retrieveClientsByRepNumber($repNumber) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("efilingRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT `key`, acctNum, lastName, firstName, repNum, active FROM clientInfo WHERE repNum='$repNumber'";
		//echo $query . "<br />";
		$result = @mysql_query($query);
		$i = 0;
		while ($acct = @mysql_fetch_array($result, MYSQL_NUM)) {
			$client = new Client();
			$client->setClientID($acct[0]);
			$client->setFullAccountNumber($acct[1]);
			$client->setLastName($acct[2]);
			$client->setFirstName($acct[3]);
			$client->setRepNumber($acct[4]);
			$client->setActive($$acct[5]);
			$accountList[$i++] = $client;
		}
		$database->closeConnection();

		return $accountList;
	}

}