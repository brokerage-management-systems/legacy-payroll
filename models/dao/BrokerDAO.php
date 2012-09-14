<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class BrokerDAO {

	public function addBrokerPayoutToBroker($broker) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT repNum, minAmt, maxAmt, payPercent FROM payoutStructure WHERE repNum='".$broker->getRepNumber()."'";
		//echo $query."<br />";
		$result = @mysql_query($query);
		//echo "Result: ".$result."<br />";
		if ($result) {
			$incrementor = 0;
			while ($pay = @mysql_fetch_array($result, MYSQL_NUM)) {
				$brokerPayout = new BrokerPayout();
				$brokerPayout->setRepNumber($pay[0]);
				$brokerPayout->setMinimum($pay[1]);
				$brokerPayout->setMaximum($pay[2]);
				$brokerPayout->setPayPercent($pay[3]);
				$brokerPayouts[$incrementor++] = $brokerPayout;
			}
			$broker->setPayoutValues($brokerPayouts);
		}
		$database->closeConnection();
		return $broker;
	}

	public function addJointRepsToBroker($broker) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT altRep, misc, splitPercent FROM repNums WHERE mainRep='".$broker->getRepNumber()."' AND type='JointRep'";
		//echo $query."<br />";
		$result = @mysql_query($query);
		//echo "Result: ".$result."<br />";
		if ($result) {
			$incrementor = 0;
			while ($joint = @mysql_fetch_array($result, MYSQL_NUM)) {
				$jointRep = new JointRep();
				$jointRep->setRepNumber($joint[0]);
				$jointRep->setLabel($joint[1]);
				$jointRep->setSplitPercent($joint[2]);
				$jointReps[$incrementor++] = $jointRep;
			}
			$broker->setJointReps($jointReps);
		}
		$database->closeConnection();
		return $broker;
	}

	public function addOverrideRepsToBroker($broker) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT altRep, misc, splitPercent FROM repNums WHERE mainRep='".$broker->getRepNumber()."' AND type='Override'";
		//echo $query."<br />";
		$result = @mysql_query($query);
		//echo "Result: ".$result."<br />";
		if ($result) {
			$incrementor = 0;
			while ($override = @mysql_fetch_array($result, MYSQL_NUM)) {
				$overrideRep = new OverrideRep();
				$overrideRep->setRepNumber($override[0]);
				$overrideRep->setLabel($override[1]);
				$overrideRep->setSplitPercent($override[2]);
				$overrideReps[$incrementor++] = $overrideRep;
			}
			$broker->setOverrideReps($overrideReps);
		}
		$database->closeConnection();
		return $broker;
	}

	public function addSalesAssistantToBroker($broker) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT salesAssistantId, SalesAssistant.firstName, SalesAssistant.lastName, SalesAssistant.active, percent FROM salesAssistantData LEFT JOIN SalesAssistant ON salesAssistantData.salesAssistantId = SalesAssistant.id WHERE repNum='".$broker->getRepNumber()."'";
		//$query = "SELECT salesAssistantId, percent FROM salesAssistantData WHERE repNum='".$broker->getRepNumber()."'";
		//echo $query."<br />";
		$result = @mysql_query($query);
		//echo "Result: ".$result."<br />";
		if ($result) {
			$incrementor = 0;
			while ($sa = @mysql_fetch_array($result, MYSQL_NUM)) {
				$salesAssistant = new SalesAssistant();
				$salesAssistant->setSalesAssistantId($sa[0]);
				$salesAssistant->setFirstName($sa[1]);
				$salesAssistant->setLastName($sa[2]);
				$salesAssistant->setActive($sa[3]);
				$salesAssistant->setBrokers(array(array($broker->getRepNumber(), $sa[4])));
				$salesAssistants[$incrementor++] = $salesAssistant;
				$salesAssistant->toString();
			}
			$broker->setSalesAssistant($salesAssistants);
		}
		$database->closeConnection();
		return $broker;
	}

	public function addAccountsToBrokers($broker, $dateType, $searchDateStart, $searchDateEnd) {
		if ($dateType != null && $searchDateStart != null && $searchDateEnd != null) {
			$listOfAccounts = AccountDAO::retrieveAccountsByDateRange($dateType, $searchDateStart, $searchDateEnd);
		} else {
			$listOfAccounts = null;
			//$listOfAccounts = AccountDAO::retrieveAccountByBrokerNumber($broker->getRepNumber());
		}
		$brokerAccounts = array();
		for ($i = 0; $i < count($broker); $i++) {
			unset($brokerAccounts);
			$accountCounter = 0;
			for ($j = 0; $j < count($listOfAccounts); $j++) {
				if ($broker[$i]->getRepNumber() == $listOfAccounts[$j]->getRepNumber()) {
					$brokerAccounts[$accountCounter++] = $listOfAccounts[$j];
				}
			}
			$broker[$i]->setBrokerAccounts($brokerAccounts);
		}
		return $broker;
	}

	public function addTradesToBrokerAccounts($listOfReps, $listOfTrades) {
		$accountTrades = array();
		for ($i = 0; $i < count($listOfReps); $i++) {
			unset($account);
			for ($j = 0; $j < count($listOfReps[$i]->getBrokerAccounts()); $j++) {
				unset($accountTrades);
				$account = $listOfReps[$i]->getBrokerAccounts();
				$tradeCounter = 0;
				for ($k = 0; $k < count($listOfTrades); $k++) {
					if ($account[$j]->getAccountNumber() == $listOfTrades[$k]->getBranch()."-".$listOfTrades[$k]->getAccountNumber()) {
						$accountTrades[$tradeCounter++] = $listOfTrades[$k];
					}
				}
				$account[$j]->setAccountTrades($accountTrades);
			}
		}
		return $listOfReps;
	}

	public function createBrokerSearchQuery() {
		$query = "SELECT * FROM Users WHERE repNum='$repNumber'";

		// OR

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
	}

	/*
	 * METHOD: CREATE LIST OF BROKERS
	 * Creates and sets $this->listOfBrokers.
	 */
	public function retrieveBrokersByDateRange($dateType, $searchDateStart, $searchDateEnd) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT DISTINCT REGISTERED_REP_OWNING_REP_RR_09 FROM TRDREV_TD";
		if ($dateType=="settlement") {
			$query .= " WHERE SETTLEMENT_DATE_01 BETWEEN '$searchDateStart' AND '$searchDateEnd'";
		} else if ($dateType=="trade") {
			$query .= " WHERE TRADE_DATE_01 BETWEEN '$searchDateStart' AND '$searchDateEnd'";
		} else {
			// Default is Trade Date
			$query .= " WHERE TRADE_DATE_01 BETWEEN '$searchDateStart' AND '$searchDateEnd'";
		}
		$query .= " ORDER BY REGISTERED_REP_OWNING_REP_RR_09 ASC";
		//echo $query;
		$result = @mysql_query($query);
		$counter = 0;
		while ($reps = @mysql_fetch_array($result)) {
			$listOfBrokers[$counter] = new Broker();
			$listOfBrokers[$counter]->setRepNumber($reps[0]);

			$counter++;
		}
		$database->closeConnection();
		return $listOfBrokers;
	}

	public function retrieveBrokersByNumbers($repNumbers) {
		for ($i = 0; $i < count($repNumbers); $i++) {
			$brokerList[$i] = retrieveBrokerByNumber($repNumbers[$i]);
		}
		return $brokerList;
	}

	public function retrieveBrokerByNumber($repNumber) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT * FROM Users WHERE repNum='$repNumber'";
		//echo $query;
		$result = @mysql_query($query);
		$rep = @mysql_fetch_row($result);
		$broker = new Broker();
		$broker->setID($rep[0]);
		$broker->setRepNumber($rep[1]);
		$broker->setFirstName($rep[2]);
		$broker->setLastName($rep[3]);
		$broker->setActive($rep[4]);

		$database->closeConnection();
		return $broker;
	}

	public function retrieveBrokerNameByNumber($repNumber) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$nameQuery = "SELECT firstName, lastName FROM Brokers WHERE repNumber='$repNumber'";
		//echo $nameQuery;
		$nameResult = @mysql_query($nameQuery);
		$repFullName = @mysql_fetch_row($nameResult);
		$database->closeConnection();
		return $repFullName[0] . " " . $repFullName[1];
	}

}
