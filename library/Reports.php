<?php

class Reports
{

	/*
	 *  Main Report Functions.
	 */

	//TODO: Troubleshoot this report...
	public function runFirmGrossReport($start, $end)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$dates = new CommissionDates();
		$dates->setArrOfMonths($start, $end);
		$reportDates = $dates->getArrOfMonths();

		$incre = 0;
		$grossTotal = 0;
		$netTotal = 0;
		for ($a = 0; $a < count($reportDates); ++$a) {
			$selectQuery = "SELECT monthEndDate, SUM(totalAdjustedGross), SUM(netFromSales) FROM submittedPayrollData WHERE monthEndDate='$reportDates[$a]'";
			//echo $selectQuery."<br />";
			$selectResult = @mysql_query($selectQuery);
			$row = @mysql_fetch_array($selectResult);
			$repsData[$incre][0] = $row[0];
			$repsData[$incre][1] = $row[1];
			$repsData[$incre][2] = $row[2];
			$grossTotal += $row[1];
			$netTotal += $row[2];
			$incre++;
		}

		$repsData[$incre][0] = "Totals";
		$repsData[$incre][1] = $grossTotal;
		$repsData[$incre][2] = $netTotal;


		unset($colNames);
		$colNames[0][0] = "string";
		$colNames[0][1] = "Month";
		$colNames[1][0] = "number";
		$colNames[1][1] = "Gross Commission";
		$colNames[2][0] = "number";
		$colNames[2][1] = "Net Commission";

		for ($a = 0; $a < count($repsData); ++$a) {
			$fmtTableData[$a][0] = $repsData[$a][0];
			$fmtTableData[$a][1] = money_format('%(1.2n', $repsData[$a][1]);
			$fmtTableData[$a][2] = money_format('%(1.2n', $repsData[$a][2]);
		}
		HtmlComponents::googPrintTableData($colNames, $repsData, $fmtTableData);
		$database->closeConnection();
	}

	public function runPayrollReport($month)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$brokerDao = new BrokerDAO();

		$selectQuery = "SELECT repNum, monthEndDate, totalAdjustedGross, netFromSales, totalCheck, misc FROM submittedPayrollData WHERE monthEndDate='$month' ORDER BY repNum ASC";
		//echo $selectQuery;
		$selectResult = @mysql_query($selectQuery);
		$grossTotal = 0;
		$netTotal = 0;
		$checkTotal = 0;
		$incre = 0;
		while ($row = @mysql_fetch_array($selectResult)) {
			$repsData[$incre][0] = $row[0]. " - " . $brokerDao->retrieveBrokerNameByNumber($row[0]);
			$repsData[$incre][1] = $row[1];
			$repsData[$incre][2] = $row[2];
			$repsData[$incre][3] = $row[3];
			$repsData[$incre][4] = $row[4];
			$grossTotal += $row[2];
			$netTotal += $row[3];
			if($row[4]>0) { // If Total Check is a negative number it should not take away from the Total Check Totals.
				$checkTotal += $row[4];
			}
			++$incre;
		}

		$repsData[$incre][0] = "Totals";
		$repsData[$incre][1] = $row[1];
		$repsData[$incre][2] = $grossTotal;
		$repsData[$incre][3] = $netTotal;
		$repsData[$incre][4] = $checkTotal;

		unset($colNames);
		$colNames[0][0] = "string";
		$colNames[0][1] = "Rep Num/Name";
		$colNames[1][0] = "string";
		$colNames[1][1] = "Date Range";
		$colNames[2][0] = "number";
		$colNames[2][1] = "Gross Commission";
		$colNames[3][0] = "number";
		$colNames[3][1] = "Net Commission";
		$colNames[4][0] = "number";
		$colNames[4][1] = "Total Check";
		for ($a = 0; $a < count($repsData); ++$a) {
			$fmtTableData[$a][0] = $repsData[$a][0];
			$fmtTableData[$a][1] = $repsData[$a][1];
			$fmtTableData[$a][2] = money_format('%(1.2n', $repsData[$a][2]);
			$fmtTableData[$a][3] = money_format('%(1.2n', $repsData[$a][3]);
			$fmtTableData[$a][4] = money_format('%(1.2n', $repsData[$a][4]);
		}
		HtmlComponents::googPrintTableData($colNames, $repsData, $fmtTableData);
	}

	public function runGrossSummaryReport($repNum, $reportDateStart, $reportDateEnd)
	{
		// If dates are set & valid run report, otherwise quit.
		if (isset($reportDateStart) && isset($reportDateEnd) && CommissionDates::checkEndDateNotLower($reportDateStart, $reportDateEnd)) {
			$months = CommissionDates::retrieveArrayOfMonthsRange($reportDateStart, $reportDateEnd);
			for ($i = 0; $i < count($months); $i++) {
				// If $repNum has a rep number use it, otherwise firm totals
				$nullArray = array("", "null", "NULL");
				if (InitializeSite::isNullValue($repNum)) {
					//InitializeSite::alertMessage("Is NULL");
					$data[$i] = Reports::retrievePayrollDataByMonth($months[$i][0]);
				} else {
					//InitializeSite::alertMessage("Is Not NULL, $repNum");
					$data[$i] = Reports::retrievePayrollDataByRepAndMonth($repNum, $months[$i][0]);
				}
			}
			return Reports::buildJavascriptForGoogleTable($data);
			// Does not work
			//Reports::buildjQueryVisualize($data);
		} else {
			InitializeSite::alertMessage("The dates you entered are not valid. I can't run the report.");
			return null;
		}
	}

	public function runSalesAssistantPayrollReport($commissionMonth)
	{
		$salesAssistantDao = new SalesAssistantDAO();
		$salesAssistants = $salesAssistantDao->retrieveActiveSalesAssistants();
		$salesAssistants = $salesAssistantDao->retrieveBrokersAndPercentByMultipleSalesAssistants($salesAssistants);
		$salesAssistants = $salesAssistantDao->retrieveSalesAssistantExpenseByMultipleBrokers($salesAssistants, $commissionMonth);
		$salesAssistants = $salesAssistantDao->calculateSalesAssistantsPay($salesAssistants);
		$salesAssistants = $salesAssistantDao->calculateOfficeManagerPay($salesAssistants);

		echo "Sales Assistants w/Broker Related Data<br />";
		for ($i = 0; $i < count($salesAssistants); $i++) {
			echo $salesAssistants[$i]->toString() . "<br />";
		}

		/*
		 unset($colNames);
		 $colNames[0][0] = "string";
		 $colNames[0][1] = "Sales Assistant";
		 $colNames[1][0] = "string";
		 $colNames[1][1] = "Broker";
		 $colNames[2][0] = "number";
		 $colNames[2][1] = "Percent from Commission";
		 for ($i = 0; $i < count($saData); $i++) {
			$fmtTableData[$i][0] = $saData[$i][0];
			$fmtTableData[$i][1] = $saData[$i][1];
			$fmtTableData[$i][2] = money_format('%(1.2n', $saData[$i][2]);
			}
			HtmlComponents::googPrintTableData($colNames, $saData, $fmtTableData);
			*/
	}

	/*
	 * Report Support Functions.
	 * TODO Change to private access methods.
	 */
	public function allRepTotals($repNums, $reportDates, $search)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$totals = null;
		for ($a = 0; $a < count($repNums); ++$a) {
			$selectQuery = "SELECT monthEndDate, $search FROM submittedPayrollData WHERE repNum='$repNums[$a]' AND monthEndDate BETWEEN '$reportDates[0]' AND '$reportDates[1]' ORDER BY monthEndDate ASC";
			//echo $selectQuery;
			$selectResult = @mysql_query($selectQuery);
			$incrementor = 0;
			while ($row = @mysql_fetch_array($selectResult, MYSQL_NUM)) {
				$totals[$incrementor][0] = $row[0];
				$totals[$incrementor][1] += $row[1];
				echo "Month: ".$totals[$incrementor][0]." | Gross: ".$totals[$incrementor][1]."<br />";
				++$incrementor;
			}
		}
		$database->closeConnection();
		return $totals;
	}

	public function avgTotalsByMonth($repNums, $reportDates, $search)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		for ($a = 0; $a < count($reportDates); ++$a) {
			$avgGross[$a][0] = $reportDates[$a];
			$tempVal = 0;
			for ($b = 0; $b < count($repNums); ++$b) {
				$selectQuery = "SELECT $search FROM submittedPayrollData WHERE repNum='$repNums[$b]' AND monthEndDate='$reportDates[$a]' ORDER BY repNum ASC";
				//echo $selectQuery;
				$selectResult = @mysql_query($selectQuery);
				while ($row = @mysql_fetch_array($selectResult, MYSQL_NUM)) {
					$tempVal += $row[0];
					//echo "Month: ".$reportDates[$a]." | Gross: ".$row[0]." | Total: ".$tempVal."<br />";
				}
			}
			$avgGross[$a][1] = $tempVal / ($b + 1);
			//echo "Average: ".$avgGross[$a][1]."<br />";
		}
		$database->closeConnection();
		return $avgGross;
	}

	public function calCommissionSingleRep($repNum, $dates)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$brokerDao = new BrokerDAO();
		$selectQuery = "SELECT totalAdjustedGross, netFromSales, totalCheck FROM submittedPayrollData WHERE repNum='$repNum' AND monthEndDate BETWEEN '".$dates[0]."' AND '".$dates[1]."' ORDER BY monthEndDate ASC";
		//echo $selectQuery."<br />";
		$selectResult = @mysql_query($selectQuery);
		$totalAdjustedGross = 0;
		$netFromSales = 0;
		$totalCheck = 0;
		while ($row = @mysql_fetch_array($selectResult, MYSQL_NUM)) {
			$totalAdjustedGross += (double)$row[0];
			$netFromSales += (double)$row[1];
			$totalCheck += (double)$row[2];
		}
		$database->closeConnection();

		$array[0] = $repNum." - ".$brokerDao->retrieveBrokerNameByNumber($repNum);
		$array[1] = $dates[0]." - ".$dates[1];
		$array[2] = $totalAdjustedGross;
		$array[3] = $netFromSales;
		$array[4] = $totalCheck;

		//print_r($array);
		return $array;
	}

	public function singleRepTotals($repNum, $reportDates, $search)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$totals = null;
		$incrementor = 0;
		$selectQuery = "SELECT monthEndDate, $search FROM submittedPayrollData WHERE repNum='$repNum' AND monthEndDate BETWEEN '$reportDates[0]' AND '$reportDates[1]' ORDER BY monthEndDate ASC";
		//echo $selectQuery;
		$selectResult = @mysql_query($selectQuery);
		while ($row = @mysql_fetch_array($selectResult, MYSQL_NUM)) {
			$totals[$incrementor][0] = $row[0];
			$totals[$incrementor][1] = $row[1];
			++$incrementor;
		}
		$database->closeConnection();
		return $totals;
	}

	// printRepChartsYear() Not sure if used as of 09/11/2010
	public function printRepChartsYear($start, $end)
	{
		$dates = new CommissionDates();
		$repNum = $_GET['repNum'];

		if (isset($_GET['vsRepNum'])) {
			$vsRepNum = $_GET['vsRepNum'];
		}

		if (isset($_GET['sdate']) && isset($_GET['edate'])) {
			$reportDate[0] = $_GET['sdate'];
			$reportDate[1] = $_GET['edate'];
		} else {
			$reportDate[0] = $start;
			$reportDate[1] = $end;
		}

		if (isset($_GET['viewBy'])) {
			$viewBy = $_GET['viewBy'];
		} else {
			$viewBy = 'totalAdjustedGross'; // netFromSales or totalCheck
		}

		$regrepinfo->setRepName($repNum);
		$repName = $regrepinfo->getRepName();
		$regrepinfo->setRepList();
		$repNums = $regrepinfo->getRepList();
		echo "<h3>Year Gross Data for Rep: ".$repNum." - ".$repName.", Date Range From: ".$reportDate[0]." - To: ".$reportDate[1]."</h3>";

		// Rep Gross Table
		unset($colNames);
		$colNames[0][0] = "string";
		$colNames[0][1] = "Month";
		$colNames[1][0] = "number";
		$colNames[1][1] = "Rep Gross";
		$repTotalData = $this->singleRepTotals($repNum, $reportDate, $viewBy);
		$grossTotals = 0;
		$incre = 0;
		while ($incre < count($repTotalData)) {
			$grossTotals += $repTotalData[$incre][1];
			$repTableData[$incre][0] = $repTotalData[$incre][0];
			$repTableData[$incre][1] = $repTotalData[$incre][1];
			$fmtTableData[$incre][0] = $repTotalData[$incre][0];
			$fmtTableData[$incre][1] = money_format('%(1.2n', $repTotalData[$incre][1]);
			++$incre;
		}
		// Totals Row - Last Row
		$rowName = "Totals";
		$repTableData[$incre][0] = $rowName;
		$repTableData[$incre][1] = $grossTotals;
		$fmtTableData[$incre][0] = $rowName;
		$fmtTableData[$incre][1] = money_format('%(1.2n', $grossTotals);

		HtmlComponents::googPrintTableData($colNames,$repTableData, $fmtTableData);
		//HtmlComponents::googPrintMoneyChart($colNames,$repTableData, $fmtTableData);

		// Rep Gross Chart
		unset($colNames);
		$colNames[0][0] = "string";
		$colNames[0][1] = "Month";
		$colNames[1][0] = "number";
		$colNames[1][1] = "Rep: ".$repNum." Gross";
		$lineChartData = $this->singleRepTotals($repNum, $reportDate, $viewBy);
		$title = "Rep: ".$repNum." Monthly Performance";
		HtmlComponents::googPrintLineChart($colNames, $lineChartData, $title);

		// Rep VS. Firm Average Chart
		unset($colNames);
		$colNames[0][0] = "string";
		$colNames[0][1] = "Month";
		$colNames[1][0] = "number";
		$colNames[1][1] = "Rep: ".$repNum." Gross";
		$colNames[2][0] = "number";
		$colNames[2][1] = "Firm Avg Gross";
		$dates->setArrOfMonths($reportDate[0], $reportDate[1]);
		$dateArray = $dates->getArrOfMonths();
		$tempData = $this->avgTotalsByMonth($repNums, $dateArray, $viewBy);
		for ($a = 0; $a < count($tempData); ++$a) {
			$areaChartData[$a][0] = $lineChartData[$a][0];
			$areaChartData[$a][1] = $lineChartData[$a][1];
			$areaChartData[$a][2] = $tempData[$a][1];
		}
		$title = "Rep: ".$repNum." vs. Firm Avg";
		HtmlComponents::googPrintAreaChart($colNames,$areaChartData, $title);

		// Rep VS. Rep Chart
		if (isset($vsRepNum)) {
			unset($colNames);
			$colNames[0][0] = "string";
			$colNames[0][1] = "Month";
			$colNames[1][0] = "number";
			$colNames[1][1] = "Rep: ".$repNum." Gross";
			$colNames[2][0] = "number";
			$colNames[2][1] = "Rep: ".$vsRepNum." Gross";
			$tempArray1 = $this->singleRepTotals($repNum, $reportDate, $viewBy);
			$tempArray2 = $this->singleRepTotals($vsRepNum, $reportDate, $viewBy);
			for ($a = 0; $a < count($tempData); ++$a) {
				$areaChartData[$a][0] = $tempArray1[$a][0];
				$areaChartData[$a][1] = $tempArray1[$a][1];
				$areaChartData[$a][2] = $tempArray2[$a][1];
			}
			$title = "Rep: ".$repNum." vs. Rep:".$vsRepNum;
			HtmlComponents::googPrintAreaChart($colNames, $areaChartData, $title);
		}
	}

    /*
     *  Needs work...
     */
    private function buildjQueryVisualize($data)
    {
        echo '<table>';
        echo '  <caption>2009 Employee Sales by Department</caption>';
        echo '  <thead>';
        echo '    <tr>';
        echo '      <td></td>';
        echo '      <th scope="col">Rep Num/Name</th>';
        echo '      <th scope="col">Date Range</th>';
        echo '      <th scope="col">Gross Commission</th>';
        echo '      <th scope="col">Net Commission</th>';
        echo '      <th scope="col">Total Check</th>';
        echo '    </tr>'; 
        echo '  </thead>'; 
        echo '  <tbody>';
        
        $grossTotal = 0;
		$netTotal = 0;
		$checkTotal = 0;
		$incre = 0;
		while ($incre < count($data)) {
			$grossTotal = $data[$incre][2];
			$netTotal   = $data[$incre][3];
			if($data[$incre][4] > 0) { // If Total Check is a negative number it should not take away from the Total Check Totals.
				$checkTotal += $data[$incre][4];
			}
			$formattedData[$incre][0] = $data[$incre][0];
			$formattedData[$incre][1] = $data[$incre][1];
			$formattedData[$incre][2] = money_format('%(1.2n', $data[$incre][2]);
			$formattedData[$incre][3] = money_format('%(1.2n', $data[$incre][3]);
			$formattedData[$incre][4] = money_format('%(1.2n', $data[$incre][4]);
			echo '  <tr>';
			echo '    <th scope="row">'.$data[$incre][0].'</th>';
			echo '    <td>'.$data[$incre][1].'</td>';
			echo '    <td>'.$data[$incre][2].'</td>';
			echo '    <td>'.$data[$incre][3].'</td>';
			echo '    <td>'.$data[$incre][4].'</td>';
			echo '  </tr>';
			$incre++;
		}
		// Totals Row - Last Row
		$rowName = "Totals";
		$data[$incre][0] = $rowName;
		$data[$incre][1] = " ";
		$data[$incre][2] = $grossTotal;
		$data[$incre][3] = $netTotal;
		$data[$incre][4] = $checkTotal;
		$formattedData[$incre][0] = $rowName;
		$formattedData[$incre][1] = "";
		$formattedData[$incre][2] = money_format('%(1.2n', $grossTotal);
		$formattedData[$incre][3] = money_format('%(1.2n', $netTotal);
		$formattedData[$incre][4] = money_format('%(1.2n', $checkTotal);
        echo '  <tr>';
		echo '    <th scope="row">'.$data[$incre][0].'</th>';
		echo '    <td>'.$data[$incre][1].'</td>';
		echo '    <td>'.$data[$incre][2].'</td>';
		echo '    <td>'.$data[$incre][3].'</td>';
		echo '    <td>'.$data[$incre][4].'</td>';
		echo '  </tr>';	
        echo '  </tbody>';
        echo '</table>';
        //echo '$(\'table\').visualize();';
    }

	private function buildJavascriptForGoogleTable($data)
	{
		unset($colNames);
		$colNames[0][0] = "string";
		$colNames[0][1] = "Rep Num/Name";
		$colNames[1][0] = "string";
		$colNames[1][1] = "Date Range";
		$colNames[2][0] = "number";
		$colNames[2][1] = "Gross Commission";
		$colNames[3][0] = "number";
		$colNames[3][1] = "Net Commission";
		$colNames[4][0] = "number";
		$colNames[4][1] = "Total Check";
		$grossTotal = 0;
		$netTotal = 0;
		$checkTotal = 0;
		$incre = 0;
		while ($incre < count($data)) {
			$grossTotal += $data[$incre][2];
			$netTotal   += $data[$incre][3];
			if($data[$incre][4] > 0) { // If Total Check is a negative number it should not take away from the Total Check Totals.
				$checkTotal += $data[$incre][4];
			}
			$formattedData[$incre][0] = $data[$incre][0];
			$formattedData[$incre][1] = $data[$incre][1];
			$formattedData[$incre][2] = money_format('%(1.2n', $data[$incre][2]);
			$formattedData[$incre][3] = money_format('%(1.2n', $data[$incre][3]);
			$formattedData[$incre][4] = money_format('%(1.2n', $data[$incre][4]);
			$incre++;
		}
		// Totals Row - Last Row
		$rowName = "Totals";
		$data[$incre][0] = $rowName;
		$data[$incre][1] = " ";
		$data[$incre][2] = $grossTotal;
		$data[$incre][3] = $netTotal;
		$data[$incre][4] = $checkTotal;
		$formattedData[$incre][0] = $rowName;
		$formattedData[$incre][1] = "";
		$formattedData[$incre][2] = money_format('%(1.2n', $grossTotal);
		$formattedData[$incre][3] = money_format('%(1.2n', $netTotal);
		$formattedData[$incre][4] = money_format('%(1.2n', $checkTotal);

		return HtmlComponents::buildGoogTableDataJS($colNames,$data, $formattedData);
	}

	private function retrievePayrollDataByMonth($mn)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectQuery = "SELECT monthEndDate, SUM(totalAdjustedGross), SUM(netFromSales), SUM(totalCheck) FROM submittedPayrollData WHERE monthEndDate='$mn'";
		//InitializeSite::alertMessage($selectQuery);
		$selectResult = @mysql_query($selectQuery);
        $row = @mysql_fetch_row($selectResult);
        $summedGross = ($row[1] == '') ? 0 : $row[1];
        $summedNet   = ($row[2] == '') ? 0 : $row[2];
        $summedCheck = ($row[3] == '') ? 0 : $row[3];
        //InitializeSite::alertMessage("$summedGross, $summedNet, $summedCheck");
		return array("Firm Totals", $mn, $summedGross, $summedNet, $summedCheck);
	}

	private function retrievePayrollDataByRepAndMonth($rn, $mn)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectQuery = "SELECT repNum, monthEndDate, totalAdjustedGross, netFromSales, totalCheck FROM submittedPayrollData WHERE repNum='$rn' AND monthEndDate='$mn'";
		//InitializeSite::alertMessage($selectQuery);
		$selectResult = @mysql_query($selectQuery);
		$row = @mysql_fetch_row($selectResult);
		return $row;
	}

}
