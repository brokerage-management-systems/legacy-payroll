<?php

class SalesAssistantDAO {

	/*
	 * METHOD: CALCULATE OFFICE MANAGER PAY
	 */
	public function calculateOfficeManagerPay($salesAssistantList) {
		$officeManagerId = "A00"; // Hard coded value for A00 - Andrea Mormile
		$officeManagerPay = 0;
		for ($i = 0; $i < count($salesAssistantList); $i++) {
			if ($salesAssistantList[$i]->getSalesAssistantId() == $officeManagerId) {
				$officeManagerIndex = $i;
				//echo "Got the office manager: " . $officeManagerId . " at index " . $officeManagerIndex . "<br />";
			} else {
				$officeManagerPay += $salesAssistantList[$i]->getPay();
			}
		}
		$officeManagerPay = $salesAssistantList[$officeManagerIndex]->getPay() + bcdiv($officeManagerPay, 3, 2);
		$salesAssistantList[$officeManagerIndex]->setPay($officeManagerPay);
		return $salesAssistantList;
	}

	/*
	 * METHOD: CALCULATE SALES ASSISTANTS PAY
	 */
	public function calculateSalesAssistantsPay($salesAssistantList) {
		for ($i = 0; $i < count($salesAssistantList); $i++) {
			$brokers = $salesAssistantList[$i]->getBrokers();
			$salesAssistantExpenseTotal = 0;
			for ($j = 0; $j < count($brokers); $j++) {
				$salesAssistantExpenseTotal += $brokers[$j][2];
			}
			unset($brokers);
			$salesAssistantList[$i]->setPay($salesAssistantExpenseTotal);
		}
		return $salesAssistantList;
	}

	/*
	 * METHOD: RETRIEVE ACTIVE SALES ASSISTANTS
	 */
	public function retrieveActiveSalesAssistants() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT `id`, `lastName`, `firstName`, `active` FROM `SalesAssistant` WHERE `active`=1 ORDER BY `id` ASC";
		$result = @mysql_query($query);
		if ($result) {
			$increment = 0;
			while ($sa = @mysql_fetch_assoc($result)) {
				$salesAssistant = new SalesAssistant();
				$salesAssistant->setSalesAssistantId($sa['id']);
				$salesAssistant->setLastName($sa['lastName']);
				$salesAssistant->setFirstName($sa['firstName']);
				$salesAssistant->setActive($sa['active']);
				$salesAssistantList[$increment++] = $salesAssistant;
			}
		}
		$database->closeConnection();
		return $salesAssistantList;
	}

	/*
	 * TODO: Test this method.
	 */
	public function retrieveBrokersAndPercentBySalesAssistant($salesAssistant) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT repNum, percent FROM salesAssistantData WHERE salesAssistantId='".$salesAssistant->getSalesAssistantId()."'";
		echo $query . "<br />";
		$result = @mysql_query($query);
		while ($sa = @mysql_fetch_array($result, MYSQL_NUM)) {
			$salesAssistant->setBrokers(array($sa[0], $sa[1]));
		}
		$database->closeConnection();
		return $salesAssistant;
	}

	/*
	 * METHOD: RETRIEVE BROKERS AND PERCENT BY MULTIPLE SALES ASSISTANTS
	 */
	public function retrieveBrokersAndPercentByMultipleSalesAssistants($salesAssistantList) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		for ($i = 0; $i < count($salesAssistantList); $i++) {
			$saId = $salesAssistantList[$i]->getSalesAssistantId();
			$query = "SELECT repNum, percent FROM salesAssistantData WHERE salesAssistantId='".$saId."'";
			//echo $query . "<br />";
			$result = @mysql_query($query);
			$increment = 0;
			while ($sa = @mysql_fetch_array($result, MYSQL_NUM)) {
				$brokers[$increment++] = array($sa[0], $sa[1]);
			}
			$salesAssistantList[$i]->setBrokers($brokers);
			unset($brokers);
		}
		$database->closeConnection();
		return $salesAssistantList;
	}

	/*
	 * TODO: Test this method
	 * METHOD: RETRIEVE SALES ASSISTANT EXPENSE BY BROKER
	 */
	public function retrieveSalesAssistantExpenseByBroker($salesAssistant, $commissionMonth) {
		$brokers = $salesAssistant->getBrokers();

		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		for ($i = 0; $i < count($brokers); $i++) {
			$query = "SELECT amount FROM `miscExpenses` WHERE `expense`='SALES ASSISTANT' AND `repNum`='".$brokers[$i][0]."' AND `monthEndDate`='$commissionMonth' ORDER BY `miscExpenses`.`monthEndDate` , `miscExpenses`.`repNum` ASC";
			//echo $query;
			$result = @mysql_query($query);
			$amount = @mysql_fetch_row($result);
			$salesAssistantList[$i] = array($brokers[$i][0], $brokers[$i][1], $amount[0]);
		}
		$database->closeConnection();
		$salesAssistant->setBrokers($salesAssistantList);
		return $salesAssistant;
	}

	/*
	 * METHOD: RETRIEVE SALES ASSISTANT EXPENSE BY MULTIPLE BROKERS
	 */
	public function retrieveSalesAssistantExpenseByMultipleBrokers($salesAssistantList, $commissionMonth) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		for ($i = 0; $i < count($salesAssistantList); $i++) {
			$brokers = $salesAssistantList[$i]->getBrokers();
			for ($j = 0; $j < count($brokers); $j++) {
				$query = "SELECT amount FROM `miscExpenses` WHERE `expense`='SALES ASSISTANT' AND `repNum`='".$brokers[$j][0]."' AND `monthEndDate`='$commissionMonth' ORDER BY `miscExpenses`.`monthEndDate` , `miscExpenses`.`repNum` ASC";
				//echo $query . "<br />";
				$result = @mysql_query($query);
				if ($result) {
					$amount = @mysql_fetch_row($result);
				} else {
					$amount[0] = "0.00";
				}
				$brokersTemp[$j] = array($brokers[$j][0], $brokers[$j][1], $amount[0]);
			}
			$salesAssistantList[$i]->setBrokers($brokersTemp);
			unset($brokersTemp);
			unset($brokers);
		}
		$database->closeConnection();
		return $salesAssistantList;
	}

	public function retrieveSalesAssistantById($id) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$query = "SELECT id, lastName, firstName, active FROM SalesAssistant WHERE salesAssistantId='$id'";
		//echo $query;
		$result = @mysql_query($query);
		$sa = @mysql_fetch_row($result);
		$salesAssistant = new SalesAssistant();
		$salesAssistant->setSalesAssistantId($sa[0]);
		$salesAssistant->setFirstName($sa[1]);
		$salesAssistant->setLastName($sa[2]);
		$salesAssistant->setActive($sa[3]);

		$database->closeConnection();
		return $salesAssistant;
	}

	public function retrieveSalesAssistantsByIds($ids) {
		for ($i = 0; $i < count($ids); $i++) {
			$salesAssistantList[$i] = retrieveSalesAssistantById($ids[$i]);
		}
		return $salesAssistantList;
	}

}