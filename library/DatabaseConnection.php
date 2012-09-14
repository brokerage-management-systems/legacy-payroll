<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class DatabaseConnection {

	private $databaseInfo;
	private $connection;
	
	public function setConnectionSettings($userName) {
			switch ($userName) {
			case ("tradeDataRead"):
				$this->databaseInfo = array(
					'host'           => 'localhost',
					'username'       => 'tradeDataRead',
					'password'       => '',
					'database'       => 'clearingdata'
				);
				break;
			case ("tradeDataWrite"):
				$this->databaseInfo = array(
					'host'           => 'localhost',
					'username'       => 'tradeDataWrite',
					'password'       => '',
					'database'       => 'clearingdata'
				);
				break;
			case ("appAuth") :
				$this->databaseInfo = array(
					'host'           => 'localhost',
					'username'       => 'appAuth',
					'password'       => '',
					'database'       => 'appAuth'
				);
			default :
				break;		
		}		
	}
	
	public function closeConnection() {
		mysql_close($this->connection);
	}
	
    public function openConnection() {
        // Make the connection.
        $this->connection = mysql_connect($this->databaseInfo['host'], $this->databaseInfo['username'], $this->databaseInfo['password']);
        if ($this->connection) {
            $isConnected = true;
        } else {
            $isConnected = false;
            echo mysql_error();
        }

        // Select the database.
        if (mysql_select_db($this->databaseInfo['database'])) {
            $isConnected = true;
        } else {
            $isConnected = false;
            echo mysql_error();
        }
        return $isConnected;
    }
	
    public function changeToDevelopmentDatabase() {
		$this->databaseInfo['database'] = substr_replace($this->databaseInfo['database'], "dev_", 0, 0);
    }
    
}

?>
