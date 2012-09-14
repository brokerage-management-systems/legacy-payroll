<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class CommissionDates
{

	private $arrOfMonths;
	private $yesterdayDate;
	private $todayDate;
	public $dateInfo;
	public $monthBeginEnd;
	public $prevMonth;
	public $lastTradeDate;

	public function __construct() {
		$this->setTodayDate();
		$this->setYesterdayDate();
	}
	
	public function getTodayDate() {
		return $this->todayDate;
	}
	
	public function getYesterdayDate() {
		return $this->yesterdayDate;
	}
	
	private function setTodayDate() {
		$this->todayDate = date('Ymd');
	}
	
	private function setYesterdayDate() {
		$this->yesterdayDate = date("Ymd", strtotime("-1 day"));
	}
	
	public function setDates($settleDateEnd) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT commissionMonthStart, commissionMonthEnd, previousMonth, nextMonth, monthLabel FROM commissionMonths WHERE commissionMonthEnd='$settleDateEnd'";
		//InitializeSite::alertMessage($query);
		$result = @mysql_query($query);
		$row = @mysql_fetch_row($result);
		if (isset($row)) {
			$settleDateStart = $row[0];
			$owingMonth = $row[3];
			$monthLabel = $row[4];
			$settleDateEnd = $row[1];
		}
		$database->closeConnection();
		$this->dateInfo = array($settleDateStart, $owingMonth, $monthLabel, $settleDateEnd);
	}

	public function getDates() {
		return $this->dateInfo;
	}

	public function setArrOfMonths($start, $end) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT commissionMonthEnd FROM commissionMonths WHERE commissionMonthEnd BETWEEN '$start' AND '$end' ORDER BY commissionMonthEnd ASC";
		$result = @mysql_query($query);
		$incr = 0;
		while ($row = @mysql_fetch_array($result, MYSQL_NUM)) {
			$this->arrOfMonths[$incr] = $row[0];
			//InitializeSite::alertMessage($this->arrOfMonths[$incr]);
			++$incr;
		}
		$database->closeConnection();
	}

	public function getArrOfMonths() {
		return $this->arrOfMonths;
	}

	public function setMonthBeginEnd($date) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT commissionMonthStart, commissionMonthEnd FROM commissionMonths";
		//InitializeSite::alertMessage($query);
		$result = @mysql_query($query);
		$row = @mysql_fetch_array($result);
		while ($row = @mysql_fetch_array($result, MYSQL_NUM)) {
			if ($date >= $row[0] && $date <= $row[1]) {
				$this->monthBeginEnd = array($row[0], $row[1]);
			}
		}
		$database->closeConnection();
	}

	public function getMonthBeginEnd() {
		return $this->monthBeginEnd;
	}

	public function setPrevMonth($settleDateEnd) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT commissionMonthStart, commissionMonthEnd, previousMonth, nextMonth, monthLabel FROM commissionMonths WHERE commissionMonthEnd='$settleDateEnd'";
		//InitializeSite::alertMessage($query);
		$result = @mysql_query($query);
		$row = @mysql_fetch_row($result);
		if (isset($row)) {
			$this->prevMonth = $row[2];
		}
		$database->closeConnection();
	}

	public function getPrevMonth() {
		return $this->prevMonth;
	}

	public function setLastTradeDate() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$prevDayQuery = "SELECT TRADE_DATE_01 FROM TRDREV_TD ORDER BY TRADE_DATE_01 DESC LIMIT 1";
		$prevDayResult = @mysql_query($prevDayQuery);
		$row = mysql_fetch_row($prevDayResult);
		$this->lastTradeDate = $row[0];
		$database->closeConnection();
	}

	public function getLastTradeDate() {
		return $this->lastTradeDate;
	}

	public function checkEndDateNotLower($start, $end)
	{
		if ($start <= $end) {
			return true;
		} else {
			return false;
		}
	}
	
	public function returnArrayOfMonthsForDropdown($type) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$str = substr($this->todayDate, 0, strlen($this->todayDate)-2)."99";
		$query = "SELECT commissionMonthEnd, monthLabel FROM commissionMonths WHERE commissionMonthEnd<=".$str." ORDER BY commissionMonthEnd DESC";
		//InitializeSite::alertMessage($query);
		$result = @mysql_query($query);
		$incrementor=0;
		while ($row = @mysql_fetch_array($result, MYSQL_NUM)) {
			$dropdownData[$incrementor][0] = $row[0];
			$dropdownData[$incrementor][1] = $row[1];
			$incrementor++;
		}
		$database->closeConnection();
		return $dropdownData;
	}

	public function retrieveArrayOfMonthsRange($start, $end) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$query = "SELECT commissionMonthEnd, monthLabel FROM commissionMonths WHERE commissionMonthEnd BETWEEN '$start' AND '$end' ORDER BY commissionMonthEnd ASC";
		//InitializeSite::alertMessage($query);
		$result = @mysql_query($query);
		$incrementor=0;
		while ($row = @mysql_fetch_array($result, MYSQL_NUM)) {
			$monthArray[$incrementor][0] = $row[0];
			$monthArray[$incrementor][1] = $row[1];
			$incrementor++;
		}
		$database->closeConnection();
		return $monthArray;
	}
	
	public function retrieveLastTradeDate() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
		
		$prevDayQuery = "SELECT TRADE_DATE_01 FROM TRDREV_TD ORDER BY TRADE_DATE_01 DESC LIMIT 1";
		$prevDayResult = @mysql_query($prevDayQuery);
		$row = mysql_fetch_row($prevDayResult);
		$database->closeConnection();
		return $lastTradeDate = $row[0];
	}
	
}