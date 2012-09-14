<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

setlocale(LC_MONETARY, 'en_US');

class HtmlComponents {

	// New needs testing and code clean up
	public function buildGoogTableDataJS($colNames, $rowData, $fmtRow)
	{
		//http://code.google.com/apis/visualization/documentation/reference.html
		// $colNames 0 is string, number, boolean | 1 is the stringname

		$random = HtmlComponents::createRandStr();

		$jsForHeader = '<script type="text/javascript">';
		$jsForHeader .= 'google.setOnLoadCallback(drawTable);';
		$jsForHeader .= 'function drawTable() {';
		$jsForHeader .= 'var cssClassNames = {';
		$jsForHeader .= "headerRow: 'lightyellow-background blotter-font header-align',";
		$jsForHeader .= "tableRow: '',";
		$jsForHeader .= "oddTableRow: 'lightblue-background',";
		$jsForHeader .= "selectedTableRow: 'lightgray-background',";
		$jsForHeader .= "hoverTableRow: 'lightgray-background',";
		$jsForHeader .= "headerCell: 'blotter-font bold-font',";
		$jsForHeader .= "tableCell: 'blotter-font normal-font'};";
		$jsForHeader .= "var options= {'allowHtml': true, 'cssClassNames': cssClassNames};";
		$jsForHeader .= 'var data = new google.visualization.DataTable();';

		for ($a = 0; $a < count($colNames); ++$a) {
			$jsForHeader .= "data.addColumn('".$colNames[$a][0]."', '".$colNames[$a][1]."');"."\n";
		}

		$jsForHeader .= "data.addRows(".count($rowData).");"."\n";

		if (!empty($rowData[0][0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						$jsForHeader .= "data.setCell(".$a.", ".$b.", '".$rowData[$a][$b]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						$jsForHeader .= "data.setCell(".$a.", ".$b.", ".$rowData[$a][$b].", '".$fmtRow[$a][$b]."');"."\n";
					} else {
						$jsForHeader .= "data.setCell(".$a.", ".$b.", ".$rowData[$a][$b].");"."\n";
					}
				}
			}
		} else if (!empty($rowData[0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						$jsForHeader .= "data.setCell(".$a.", ".$b.", '".$rowData[$a]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						$jsForHeader .= "data.setCell(".$a.", ".$b.", ".$rowData[$a].", '".$fmtRow[$a]."');"."\n";
					} else {
						$jsForHeader .= "data.setCell(".$a.", ".$b.", ".$rowData[$a].");"."\n";
					}
				}
			}
		}

		$jsForHeader .= "var container = document.getElementById('$random');";
		$jsForHeader .= 'var table = new google.visualization.Table(container);';
		$jsForHeader .= 'table.draw(data, options);';
		$jsForHeader .= '}';
		$jsForHeader .= '</script>';

		$divForBody = "<div align=\"center\" id=\"$random\"></div>";

		return array($jsForHeader, $divForBody);
	}

	public function retrieveRandomColor()
	{
		$randomColor = null;
		for ($i = 0; $i < 6; $i++) {
			$randomColor .=  dechex(rand(0,15));
		}
		return "#$randomColor";
	}

	// Move to bottom
	public function genRandStr($minLen, $maxLen, $alphaLower = 1, $alphaUpper = 1, $num = 1, $batch = 1) {
		$finalArray = null;
		$alphaLowerArray = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
		$alphaUpperArray = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$numArray = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);

		if (isset($minLen) && isset($maxLen)) {
			if ($minLen == $maxLen) {
				$strLen = $minLen;
			} else {
				$strLen = rand($minLen, $maxLen);
			}
			$merged = array_merge($alphaLowerArray, $alphaUpperArray, $numArray);

			if ($alphaLower == 1 && $alphaUpper == 1 && $num == 1) {
				$finalArray = array_merge($alphaLowerArray, $alphaUpperArray, $numArray);
			} elseif ($alphaLower == 1 && $alphaUpper == 1 && $num == 0) {
				$finalArray = array_merge($alphaLowerArray, $alphaUpperArray);
			} elseif ($alphaLower == 1 && $alphaUpper == 0 && $num == 1) {
				$finalArray = array_merge($alphaLowerArray, $numArray);
			} elseif ($alphaLower == 0 && $alphaUpper == 1 && $num == 1) {
				$finalArray = array_merge($alphaUpperArray, $numArray);
			} elseif ($alphaLower == 1 && $alphaUpper == 0 && $num == 0) {
				$finalArray = $alphaLowerArray;
			} elseif ($alphaLower == 0 && $alphaUpper == 1 && $num == 0) {
				$finalArray = $alphaUpperArray;
			} elseif ($alphaLower == 0 && $alphaUpper == 0 && $num == 1) {
				$finalArray = $numArray;
			} else {
				return FALSE;
			}

			$count = count($finalArray) - 1;

			if ($batch == 1) {
				$str = '';
				$i = 1;
				while ($i <= $strLen) {
					$rand = rand(0, $count);
					$newChar = $finalArray[$rand];
					$str .= $newChar;
					$i++;
				}
				$result = $str;
			} else {
				$j = 1;
				$result = array();
				while ($j <= $batch) {
					$str = '';
					$i = 1;
					while ($i <= $strLen) {
						$rand = rand(0, $count);
						$newChar = $finalArray[$rand];
						$str .= $newChar;
						$i++;
					}
					$result[] = $str;
					$j++;
				}
			}

			return $result;
		}
	}

	public function get_random_color()
	{
		$c = null;
		for ($i = 0; $i<6; $i++) {
			$c .=  dechex(rand(0,15));
		}
		return "#$c";
	}

	public function dateDropDown($type)
	{
		$commDate = new CommissionDates();
		$dropdown = $commDate->returnArrayOfMonthsForDropdown($type);
		echo '<option value=null>*Commission Month</option>';
		for ($i = 0; $i < sizeof($dropdown); $i++) {
			echo '<option value='.$dropdown[$i][0].'>'.$dropdown[$i][1].'</option>';
		}
	}

	public function expenseCatDropDown()
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectCategory = "SELECT category FROM expenseCategory ORDER BY category ASC";
		$categoryResult = mysql_query($selectCategory);
		if($categoryResult) {
			while ($cat = mysql_fetch_array($categoryResult, MYSQL_NUM)) {
				echo "<option value=\"$cat[0]\">$cat[0]</option>";
			}
		}

		$database->closeConnection();
	}

	public function fetchBrokerSelector() {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectRepQuery = "SELECT repNumber, firstName, lastName, active FROM Brokers WHERE repNumber IS NOT NULL AND firstName!='JointRep' ORDER BY active DESC, repNumber ASC";
		$selectRepResult = mysql_query($selectRepQuery);
		if($selectRepResult) {
			echo '<option value=null>*Select a Broker</option>';
			while ($rep = mysql_fetch_array($selectRepResult, MYSQL_NUM)) {
				if ($rep[3] == '0') {
					$status = '- Not Active';
				} else {
					$status = null;
				}
				echo '<option value="'.$rep[0].'">'.$rep[0].' - '.$rep[1].' '.$rep[2].' '.$status.'</option>';
			}
		}
		$database->closeConnection();
	}
	
	// FUNCTION CHANGE - ADD ARGS
	public function repNameNumDropDown()
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectRepQuery = "SELECT repNum, firstName, lastName, active FROM Users WHERE repNum IS NOT NULL AND firstName!='JointRep' ORDER BY active DESC, repNum ASC";
		$selectRepResult = mysql_query($selectRepQuery);
		if($selectRepResult) {
			//echo "<select name='$name' $scripts>"; // FUNCTION CHANGE - ADD ARGS
			echo "<option value=NULL>*RegRep</option>";
			while ($rep = mysql_fetch_array($selectRepResult, MYSQL_NUM)) {
				if ($rep[3] == '0') {
					$status = '- Not Active';
				} else {
					$status = null;
				}
				echo "<option value=$rep[0]>$rep[0] - $rep[1] $rep[2] $status</option>";
			}
			//echo "</select>"; // FUNCTION CHANGE - ADD ARGS
		}
		$database->closeConnection();
	}

	public function saNameNumDropDown($name, $script)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectSAQuery = "SELECT salesAssistantId, saName FROM salesAssistantData";
		$selectSAResult = mysql_query($selectSAQuery);
		if($selectSAResult) {
			echo "<select name=\"$name\" $scripts>";
			echo '<option value="null">*Sales Assistant</option>';
			while ($sa = mysql_fetch_array($selectSAResult, MYSQL_NUM)) {
				echo "<option value=\"$sa[0]\">$sa[0] - $sa[1]</option>";
			}
			echo "</select>";
		}
		$database->closeConnection();
	}
	
	public function individualRepName($repNum)
	{
		$database = new DatabaseConnection();
		$database->setConnectionSettings("tradeDataRead");
		if ($GLOBALS['DEBUG']) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();

		$selectRepQuery = "SELECT firstName, lastName FROM Brokers WHERE repNumber='$repNum'";
		$selectRepResult = mysql_query($selectRepQuery);
		$repName = null;
		if($selectRepResult) {
			while ($rep = mysql_fetch_array($selectRepResult, MYSQL_NUM)) {
				$repName = "$rep[0] $rep[1]";
			}
		}
		$database->closeConnection();
		return $repName;
	}

	public function datePicker($eleNames)
	{
		echo '<script type="text/javascript">
                $(function() {';
		if (count($eleNames) > 0) {
			for ($a = 0; $a < count($eleNames); ++$a) {
				echo "$(\"#".$eleNames[$a]."\").datepicker({
                    dateFormat: 'yymmdd',
                    showOn: 'both',
                    buttonImage: 'img/cal.gif',
                    buttonImageOnly: true
                    });";
			}
		}
		echo '});
                </script>';
	}

	public function createRandStr()
	{
		$random = HtmlComponents::genRandStr(5, 10);
		return $random;
	}

	public function googPrintTableData($colNames, $rowData, $fmtRow)
	{
		//http://code.google.com/apis/visualization/documentation/reference.html
		// $colNames 0 is string, number, boolean | 1 is the stringname

		$random = HtmlComponents::createRandStr();

		echo "<script type='text/javascript'>
            google.setOnLoadCallback(drawTable);
            function drawTable() {
            var cssClassNames = {
                headerRow: 'lightyellow-background blotter-font header-align',
                tableRow: '',
                oddTableRow: 'lightblue-background',
                selectedTableRow: 'lightgray-background',
                hoverTableRow: 'lightgray-background',
                headerCell: 'blotter-font bold-font',
                tableCell: 'blotter-font normal-font'};
            var options= {'allowHtml': true, 'cssClassNames': cssClassNames};
            var data = new google.visualization.DataTable();"."\n";

		for ($a = 0; $a < count($colNames); ++$a) {
			echo "data.addColumn('".$colNames[$a][0]."', '".$colNames[$a][1]."');"."\n";
		}

		echo "data.addRows(".count($rowData).");"."\n";

		if (!empty($rowData[0][0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						echo "data.setCell(".$a.", ".$b.", '".$rowData[$a][$b]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						echo "data.setCell(".$a.", ".$b.", ".$rowData[$a][$b].", '".$fmtRow[$a][$b]."');"."\n";
					} else {
						echo "data.setCell(".$a.", ".$b.", ".$rowData[$a][$b].");"."\n";
					}
				}
			}
		} else if (!empty($rowData[0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						echo "data.setCell(".$a.", ".$b.", '".$rowData[$a]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						echo "data.setCell(".$a.", ".$b.", ".$rowData[$a].", '".$fmtRow[$a]."');"."\n";
					} else {
						echo "data.setCell(".$a.", ".$b.", ".$rowData[$a].");"."\n";
					}
				}
			}
		}

		echo "var container = document.getElementById('$random');
            var table = new google.visualization.Table(container);
            table.draw(data, options);
            }
            </script>"."\n";

		echo "<div align='center' id='$random'></div>";
	}

	public function googPrintLineChart($colNames, $rowData, $title)
	{

		$random = HtmlComponents::createRandStr();

		echo '<script type="text/javascript">
              google.setOnLoadCallback(drawChart);
              function drawChart() {
                var data = new google.visualization.DataTable();';
		for ($a = 0; $a < count($colNames); ++$a) {
			echo "data.addColumn('".$colNames[$a][0]."', '".$colNames[$a][1]."');"."\n";
		}

		echo "data.addRows(".count($rowData).");"."\n";

		if (!empty($rowData[0][0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						echo "data.setValue(".$a.", ".$b.", '".$rowData[$a][$b]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						echo "data.setValue(".$a.", ".$b.", ".$rowData[$a][$b].");"."\n";
					} else {
						echo "data.setValue(".$a.", ".$b.", ".$rowData[$a][$b].");"."\n";
					}
				}
			}
		} else if (!empty($rowData[0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						echo "data.setValue(".$a.", ".$b.", '".$rowData[$a]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						echo "data.setValue(".$a.", ".$b.", ".$rowData[$a].");"."\n";
					} else {
						echo "data.setValue(".$a.", ".$b.", ".$rowData[$a].");"."\n";
					}
				}
			}
		}

		if ($title == null) {
			$title = "Rep Monthly Performance";
		}

		echo "var chart = new google.visualization.LineChart(document.getElementById('$random'));
                chart.draw(data, {width: 600, height: 360, legend: 'bottom', title: '$title'});
              }
            </script>";

		echo "<div align='center' id='$random'></div>";
	}

	public function googPrintAreaChart($colNames, $rowData, $title)
	{

		$random = HtmlComponents::createRandStr();

		echo '<script type="text/javascript">
              google.setOnLoadCallback(drawChart);
              function drawChart() {
                var data = new google.visualization.DataTable();';
		for ($a = 0; $a < count($colNames); ++$a) {
			echo "data.addColumn('".$colNames[$a][0]."', '".$colNames[$a][1]."');"."\n";
		}

		echo "data.addRows(".count($rowData).");"."\n";

		if (!empty($rowData[0][0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						echo "data.setValue(".$a.", ".$b.", '".$rowData[$a][$b]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						echo "data.setValue(".$a.", ".$b.", ".$rowData[$a][$b].");"."\n";
					} else {
						echo "data.setValue(".$a.", ".$b.", ".$rowData[$a][$b].");"."\n";
					}
				}
			}
		} else if (!empty($rowData[0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						echo "data.setValue(".$a.", ".$b.", '".$rowData[$a]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						echo "data.setValue(".$a.", ".$b.", ".$rowData[$a].");"."\n";
					} else {
						echo "data.setValue(".$a.", ".$b.", ".$rowData[$a].");"."\n";
					}
				}
			}
		}

		if ($title == null) {
			$title = "Rep vs. Firm Average";
		}

		// original w:400 h:240
		echo "var chart = new google.visualization.AreaChart(document.getElementById('$random'));
                chart.draw(data, {width: 600, height: 360, legend: 'bottom', title: '$title'});
              }
            </script>";

		echo "<div align='center' id='$random'></div>";
	}

	public function googPrintPieData($chartData, $title)
	{

		$random = HtmlComponents::createRandStr();

		//Chart Data
		echo "<script type=\"text/javascript\">
        // Set a callback to run when the Google Visualization API is loaded.
        google.setOnLoadCallback(drawChart);

        // Callback that creates and populates a data table,
        // instantiates the pie chart, passes in the data and
        // draws it.
        function drawChart() {

            // Create our data table.
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Rep');
            data.addColumn('number', 'Commission');
            data.addRows([";
		for ($inc = 0; $inc < count($chartData); ++$inc) {
			if ($inc < count($chartData)) {
				echo "['".$chartData[$inc][0]."', ".$chartData[$inc][1]."],";
			} else {
				echo "['".$chartData[$inc][0]."', ".$chartData[$inc][1]."]";
			}
		}

		if ($title == null) {
			$title = "Trade Totals by Rep";
		}

		echo "
            ]);

            // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.PieChart(document.getElementById('$random'));
            chart.draw(data, {width: 700, height: 350, is3D: true, title: '$title'});
        }
        </script>";

		echo "<div align='center' id='$random'></div>";
	}

	public function googPrintMoneyChart($colNames, $rowData, $fmtRow) {

		$random = HtmlComponents::createRandStr();

		echo '<script type="text/javascript">
              google.setOnLoadCallback(drawChart);
              function drawChart() {
                var data = new google.visualization.DataTable();';

		for ($a = 0; $a < count($colNames); ++$a) {
			echo "data.addColumn('".$colNames[$a][0]."', '".$colNames[$a][1]."');"."\n";
		}

		echo "data.addRows(".count($rowData).");"."\n";

		if (!empty($rowData[0][0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						echo "data.setCell(".$a.", ".$b.", '".$rowData[$a][$b]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						echo "data.setCell(".$a.", ".$b.", ".$rowData[$a][$b].", '".$fmtRow[$a][$b]."');"."\n";
					} else {
						echo "data.setCell(".$a.", ".$b.", ".$rowData[$a][$b].");"."\n";
					}
				}
			}
		} else if (!empty($rowData[0])) {
			for ($a = 0; $a < count($rowData); ++$a) {
				for ($b = 0; $b < count($rowData[$a]); ++$b) {
					if ($colNames[$b][0] == "string") {
						echo "data.setCell(".$a.", ".$b.", '".$rowData[$a]."');"."\n"; //after last arg add formatedvalue
					} else if ($colNames[$b][0] == "number") {
						echo "data.setCell(".$a.", ".$b.", ".$rowData[$a].", '".$fmtRow[$a]."');"."\n";
					} else {
						echo "data.setCell(".$a.", ".$b.", ".$rowData[$a].");"."\n";
					}
				}
			}
		}

		if ($title == null) {
			$title = "Piles of Money";
		}

		echo "var chart = new google.visualization.PilesOfMoney(document.getElementById('moneyChart_div'));
                chart.draw(data, {width: 400, height: 240, legend: 'bottom', title: '$title'});
              }
            </script>";

		echo "<div align='center' id='moneyChart_div'></div>";
	}

	public function doPagination($array) {
		$targetPage = $array[0]; //$_SERVER['PHP_SELF']
		$query = $array[1]; //SQL query
		$adjacents = $array[2]; //3
		$limit = $array[3]; //30
		/*
		* First get total number of rows in data table.
		* If you have a WHERE clause in your query, make sure you mirror it here.
		*/
		$totalPages = mysql_num_rows(mysql_query($query));

		/* Setup vars for query. */
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		} else {
			$page = 1;
		}
		if($page)
		$start = ($page - 1) * $limit;  //first item to display on this page
		else
		$start = 0;                     //if no page var is given, set start to 0

		/* Get data. */
		$sql = $query . " LIMIT $start, $limit";
		$result = mysql_query($sql);

		/* Setup page vars for display. */
		if ($page == 0) $page = 1;              //if no page var is given, default to 1.
		$prev = $page - 1;			//previous page is page - 1
		$next = $page + 1;			//next page is page + 1
		$lastpage = ceil($totalPages/$limit);  //lastpage is = total pages / items per page, rounded up.
		$lpm1 = $lastpage - 1;			//last page minus 1

		/*
		 * Now we apply our rules and draw the pagination object.
		 * We're actually saving the code to a variable in case we want to draw it more than once.
		 */
		$pagination = "";
		if($lastpage > 1) {
			$pagination .= "<div align=\"center\" class=\"pagination\">";
			//previous button
			if ($page > 1)
			$pagination.= "<a href=\"$targetPage&page=$prev\">previous</a>";
			else
			$pagination.= "<span class=\"disabled\">previous</span>";

			//pages
			if ($lastpage < 7 + ($adjacents * 2))	//not enough pages to bother breaking it up
			{
				for ($counter = 1; $counter <= $lastpage; $counter++) {
					if ($counter == $page)
					$pagination.= "<span class=\"current\">$counter</span>";
					else
					$pagination.= "<a href=\"$targetPage&page=$counter\">$counter</a>";
				}
			}
			elseif($lastpage > 5 + ($adjacents * 2))	//enough pages to hide some
			{
				//close to beginning; only hide later pages
				if($page < 1 + ($adjacents * 2)) {
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
						if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
						else
						$pagination.= "<a href=\"$targetPage&page=$counter\">$counter</a>";
					}
					$pagination.= "...";
					$pagination.= "<a href=\"$targetPage&page=$lpm1\">$lpm1</a>";
					$pagination.= "<a href=\"$targetPage&page=$lastpage\">$lastpage</a>";
				}
				//in middle; hide some front and some back
				elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
					$pagination.= "<a href=\"$targetPage&page=1\">1</a>";
					$pagination.= "<a href=\"$targetPage&page=2\">2</a>";
					$pagination.= "...";
					for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
						if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
						else
						$pagination.= "<a href=\"$targetPage&page=$counter\">$counter</a>";
					}
					$pagination.= "...";
					$pagination.= "<a href=\"$targetPage&page=$lpm1\">$lpm1</a>";
					$pagination.= "<a href=\"$targetPage&page=$lastpage\">$lastpage</a>";
				}
				//close to end; only hide early pages
				else {
					$pagination.= "<a href=\"$targetPage&page=1\">1</a>";
					$pagination.= "<a href=\"$targetPage&page=2\">2</a>";
					$pagination.= "...";
					for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
						if ($counter == $page)
						$pagination.= "<span class=\"current\">$counter</span>";
						else
						$pagination.= "<a href=\"$targetPage&page=$counter\">$counter</a>";
					}
				}
			}

			//next button
			if ($page < $counter - 1)
			$pagination.= "<a href=\"$targetPage&page=$next\">next</a>";
			else
			$pagination.= "<span class=\"disabled\">next</span>";
			$pagination.= "</div>\n";
		}

		echo $pagination;

		return $result;
		/*while($row = mysql_fetch_array($result)) {
		 // Your while loop here
		 }
		 <?=$pagination?>*/
	}

}

?>
