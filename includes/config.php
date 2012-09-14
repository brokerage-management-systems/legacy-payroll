<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

date_default_timezone_set('America/New_York');
setlocale(LC_MONETARY, 'en_US');

define("APPLICATION_PATH", realpath(dirname(__FILE__) . '/../'));
define("AJAX_PATH", APPLICATION_PATH . '/views/ajax/');
define("MODEL_PATH", APPLICATION_PATH . '/models/');
define("MODEL_DAO_PATH", APPLICATION_PATH . '/models/dao/');
define("VIEW_PATH", APPLICATION_PATH . '/views');
define("CONTROLLER_PATH", APPLICATION_PATH . '/controllers');
define("LIBRARY_PATH", APPLICATION_PATH . '/library/');
define("DEBUG", false);
define("TODAY", date('Ymd'));

$GLOBALS['DEBUG'] = false;
$todayIs = date('Ymd');

class InitializeSite {
	
	private $pageID;
	private $pageTitle;
	private $supportEmail;
	private $versionInfo;
	protected $url;
	
	public function __construct($array) {
		session_start();
		
		// Remove soon
		if ($GLOBALS['DEBUG']) {
			$this->alertMessage($_SERVER['HTTP_USER_AGENT'].'\n\n'." *** DEVELOPMENT MODE IS ON***".'\n'."System is in Development mode. Serious issues may arise if you continue to use the System. ");
		}

		if (DEBUG) {
			$this->alertMessage($_SERVER['HTTP_USER_AGENT'].'\n\n'." *** DEVELOPMENT MODE IS ON***".'\n'."System is in Development mode. Serious issues may arise if you continue to use the System. ");
		}
		
		$this->setPageID($array[2]);
		$this->setPageTitle($array[3]);
		$this->setSupportEmail();
		$this->setVersionInfo();
	}

	public function getPageID() {
		return $this->pageID;
	}

	public function getPageTitle() {
		return $this->pageTitle;
	}

	public function getSupportEmail() {
		return $this->supportEmail;
	}

	public function getVersionInfo() {
		return $this->versionInfo;
	}

	private function setPageID($var) {
		$this->pageID = $var;
	}

	private function setPageTitle($var) {
		$this->pageTitle = $var;
	}

	private function setSupportEmail() {
		$this->supportEmail = "<a href='mailto:support@interdevinc.com'>support@interdevinc.com</a>";
	}

	private function setVersionInfo() {		
		$this->versionInfo[0] = "InterDev Inc.";
		$this->versionInfo[1] = "http://www.interdevinc.com";
		$this->versionInfo[2] = "support@interdevinc.com";
		$this->versionInfo[3] = "Payroll Management System";
		$this->versionInfo[4] = "Version 2.2";
	}

	public function isNullValue($value) {
		return in_array($value, array("", "null", "NULL"));
	}
	
	public function loadComponent($componentName) {
		switch ($componentName) {
			case "htmlComponents":
				require_once("htmlComponents.php");
				break;
			default :
				require_once("htmlComponents.php");
				break;
		}
	}
	
	public function loadController($controllerName) {
		switch ($controllerName) {
			case "RegRepInfo":
				require_once(CONTROLLER_PATH . 'RegRepInfoController.php');
				break;
			case "Reports":
				require_once(CONTROLLER_PATH . 'ReportsController.php');
				break;
			case "Payroll":
				require_once(CONTROLLER_PATH . 'PayrollController.php');
				break;
			case "TradeData":
				require_once(CONTROLLER_PATH . 'TradeDataController.php');
				break;
		}
	}
	
	public function loadDAO($daoName) {
		switch ($daoName) {
			case "Account":
				require_once(MODEL_DAO_PATH . 'AccountDAO.php');
				break;
			case "Broker":
				require_once(MODEL_DAO_PATH . 'BrokerDAO.php');
				break;
			case "BrokerStructure":
				require_once(MODEL_DAO_PATH . 'BrokerStructureDAO.php');
				break;
			case "Client":
				require_once(MODEL_DAO_PATH . 'ClientDAO.php');
				break;
			case "Payroll":
				require_once(MODEL_DAO_PATH . 'PayrollDAO.php');
				break;
			case "SalesAssistant":
				require_once(MODEL_DAO_PATH . 'SalesAssistantDAO.php');
				break;
			case "Trade":
				require_once(MODEL_DAO_PATH . 'TradeDAO.php');
				break;
			default :
				break;
		}
	}
	
	public function loadLibrary($libraryName) {
		switch ($libraryName) {
			case "AuthenticationAndAccess":
				require_once(LIBRARY_PATH . 'AuthenticationAndAccess.php');
				break;
			case "CommissionDates":
				require_once(LIBRARY_PATH . 'CommissionDates.php');
				break;
			case "DatabaseConnection":
				require_once(LIBRARY_PATH . 'DatabaseConnection.php');
				break;
			case "Payroll":
				require_once(LIBRARY_PATH . 'Payroll.php');
				break;
			case "RegRepInfo":
				require_once(LIBRARY_PATH . 'RegRepInfo.php');
				break;
			case "Reports":
				require_once(LIBRARY_PATH . 'Reports.php');
				break;
			case "TradeData":
				require_once(LIBRARY_PATH . 'TradeData.php');
				break;
			default:
				break;
		}
	}
	
	public function loadModel($modelName) {
		switch ($modelName) {
			case "Account":
				require_once(MODEL_PATH . 'Account.php');
				break;
			case "Broker":
				require_once(MODEL_PATH . 'Broker.php');
				break;
			case "Client":
				require_once(MODEL_PATH . 'Client.php');
				break;
			case "Payroll":
				require_once(MODEL_PATH . 'Payroll.php');
				break;
			case "PayrollTrade":
				require_once(MODEL_PATH . 'PayrollTrade.php');
				break;
			case "SalesAssistant":
				require_once(MODEL_PATH . 'SalesAssistant.php');
				break;
			case "Trade":
				require_once(MODEL_PATH . 'Trade.php');
				break;
			default:
				break;
		}
	}

	public function loadViewAjax($viewName) {
		switch ($viewName) {
			case "Expense":
				require_once(AJAX_PATH . 'editExpense.php');
				break;
			default:
				break;
		}
	}	

	public function loadView($viewName) {
		switch ($viewName) {
			case "Payroll":
				require_once(VIEW_PATH . 'PayrollView.php');
				break;
			case "TradeData":
				require_once(VIEW_PATH . 'TradeDataView.php');
				break;
			default:
				break;
		}
	}	
	
	// Move code below to a view...
	
	public function loadInitHTML() {
		$this->printHeader();
		$this->printLinks();
		$this->printBody();
	}
	
	public function printHeader() {
		$pageID = $this->pageID;
		$pageTitle = $this->pageTitle;
		require_once("header.php");
	}

	public function printLinks() {
		$pageID = $this->pageID;
		$currentPage;
		echo "<div id='navcontainer'>
                <ul id='navlist'>
                    <li><a href='/index.php' title='Main'>Main</a></li>";
		switch ($pageID) {
			case 'payroll':
				echo '<!-- <li id="active"> or <id="current">-->
					<li><a href="/expenses.php">Expenses</a></li>
					<li><a href="/payroll.php">Payroll</a></li>
					<li><a href="/repStructure.php">Rep Structure</a></li>
					<li><a href="/reports.php">Reports</a></li>
					<li><a href="/tradedata.php">Trade Data</a></li>';
				break;
			default:
				break;
		}
		echo "</ul></div>";
	}

	public function printBody() {
		$pageID = $this->pageID;
		echo '</div>';
		echo '<div id="content">';
	}

	public function printFooter() {
		$pageID = $this->pageID;
		require_once("footer.php");
	}

	public function printVersionInfo() {
		$alert = "<p>";
		for ($a = 0; $a < count($this->versionInfo); ++$a) {
			$alert .= $this->versionInfo[$a]."<br />";
		}
		$alert .= "</p>";
		return $alert;
	}

	public function printErrorMsg($message) {
		echo '<div class="ui-widget">
            <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
            <p><span class=ui-icon ui-icon-alert style=float: left; margin-right: .3em;></span>
            <strong>Alert:</strong>';
		echo $message;
		echo "</p>
            </div>
            </div>";
	}

	public function printMessage($message) {
		echo '<div class="ui-widget">
            <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
            <p><span class=ui-icon ui-icon-alert style=float: left; margin-right: .3em;></span>
            <strong>Alert:</strong>';
		switch ($message) {
			case ("accessRights"):
				echo "You do not have access rights.<br />Please contact <a href=\"mailto:support@interdevinc.com\">support@interdevinc.com</a>.";
				break;
			default:
				echo "Unknown Error.";
				break;	 
		}
		echo "</p>
            </div>
            </div>";
		$this->printFooter($this->pageID);
		exit();
	}

	public function alertMessage($message) {
		$search = array("\r\n", "\n", "\r", "\t");
		$replace = " ";
		$message = str_replace($search, $replace, $message);
		printf('<script type="text/javascript">alert("%s");</script>', $message);
	}

}
