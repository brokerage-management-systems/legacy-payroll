<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class AuthenticationAndAccess {

	private $pageAccess;
	private $usersAccess;

	public function __construct($user, $permission) {
		$this->setPageAccess($user, $permission);
		$this->setUsersAccess();
	}

	public function getPageAccess() {
		return $this->pageAccess;
	}

	public function getUsersAccess() {
		return $this->usersAccess;
	}

	private function setPageAccess($user, $permission) {
		$this->pageAccess[0] = $user;
		$this->pageAccess[1] = $permission;
	}

	private function setUsersAccess() {
		if (!empty($_COOKIE['loginuser'])) {
			$database = new DatabaseConnection();
			$database->setConnectionSettings("appAuth");
			if ($DEBUG) {
				$database->changeToDevelopmentDatabase();
			}
			$database->openConnection();

			// Check access rights
			$access = "SELECT roles.rolename, resources.resourcename FROM access LEFT JOIN roles ON access.roleid=roles.roleid LEFT JOIN resources ON access.resourceid=resources.resourceid WHERE userid=(SELECT userid FROM users WHERE username='".$_COOKIE['loginuser']."')";
			//echo $access;
			$result = mysql_query($access);
			$counter = 0;
			while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
				$this->usersAccess[$counter][0] = $row[0];
				$this->usersAccess[$counter][1] = $row[1];
				++$counter;
			}
			$database->closeConnection();
		}
	}

	public function checkElementAccess() {
		$hasAccess = false;
		for ($a = 0; $a < count($this->usersAccess); ++$a) {
			//echo $this->usersAccess[$a][0] ." | ". $elementAccess[0] ." | ". $this->usersAccess[$a][1] ." | ". $elementAccess[1];
			if ($this->usersAccess[$a][0] == $this->pageAccess[0] && $this->usersAccess[$a][1] == $this->pageAccess[1]) {
				$hasAccess = true;
			}
		}
		return $hasAccess;
	}

	public function checkPageAccess() {
		$hasAccess = false;
		for ($a = 0; $a < count($this->usersAccess); ++$a) {
			//echo "<div><p>".$this->usersAccess[$a][0] ." | ". $this->pageAccess[0] ." | ". $this->usersAccess[$a][1] ." | ". $this->pageAccess[1]."</p></div>";
			if ($this->usersAccess[$a][0] == $this->pageAccess[0] && $this->usersAccess[$a][1] == $this->pageAccess[1]) {
				$hasAccess = true;
			}
		}
		return $hasAccess;
	}

	public function checkSession() {
		if(isset($_COOKIE['loginval']) && isset($_SESSION['loginval'])) {
			if($_COOKIE['loginval'] == $_SESSION['loginval']) {
				$loggedIn = true;
			}
			else {
				$loggedIn = false;
			}
		}
		else {
			$loggedIn = false;
		}

		if(!$loggedIn) {
			header("Location: $this->url/login.php");
			exit();
		}
	}

	public function doLogin($uname, $upass, $site) {
		$database = new DatabaseConnection();
		$database->setConnectionSettings("appAuth");
		if ($DEBUG) {
			$database->changeToDevelopmentDatabase();
		}
		$database->openConnection();
			
		$ldate = date("Y-m-d");
		$ltime = date("H:i:s");
		$md5pass = md5($upass);
		$result = mysql_query("SELECT * FROM users WHERE username='$uname' AND password='$md5pass'");
		$numrows = mysql_num_rows($result);
		if($numrows > 0) {
			$cookieValue = mt_rand() . "_" . $uname;
			setcookie("loginval",$cookieValue,time()+3600); //time()+3600 sets the cookie expiration time to one hour.
			setcookie("loginuser",$uname,time()+3600);
			setcookie("logindate",$ldate,time()+3600);
			setcookie("logintime",$ltime,time()+3600);

			$_SESSION['loginval'] = $cookieValue;
			//Write login to log file.
			$logQuery = "INSERT INTO authLog (username, date, time, site) VALUES ('$uname', '$ldate', '$ltime', '$site')";
			$logEvent = mysql_query($logQuery);
			header("Location: /");
		} else {
			echo "<div class=\"ui-widget\">
			<div class=\"ui-state-error ui-corner-all\" style=\"padding: 0 .7em;\">
				<p><span class=ui-icon ui-icon-alert style=float: left; margin-right: .3em;></span>
				<strong>Alert:</strong> Username or password are incorrect!</p>
			</div>
                        </div>";
		}
		$database->closeConnection();
	}

	public function doLogout() {
		session_destroy();
		unset($_SESSION['loginval']);
	}

}

?>
