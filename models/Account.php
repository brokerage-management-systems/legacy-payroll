<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

require_once 'Trade.php';

class Account {
	
	private $id;
	private $accountNumber;
	private $firstName;
	private $lastName;
	private $repNumber;
	private $active;
	
	private $accountTrades;
	
	public function getID() {
		return $this->id;
	}
	
	public function getAccountNumber() {
		return $this->accountNumber;
	}
	
	public function getFirstName() {
		return $this->firstName;
	}
	
	public function getLastName() {
		return $this->lastName;
	}
	
	public function getRepNumber() {
		return $this->repNumber;
	}
	
	public function getActive() {
		return $this->active;
	}
	
	public function getAccountTrades() {
		return $this->accountTrades;
	}

	public function setID($id) {
		$this->id = $id;
	}
	
	public function setAccountNumber($an) {
		$this->accountNumber = $an;
	}
	
	public function setFirstName($fn) {
		$this->firstName = $fn;
	}
	
	public function setLastName($ln) {
		$this->lastName = $ln;
	}
	
	public function setRepNumber($bn) {
		$this->repNumber = $bn;
	}
	
	public function setActive($act) {
		$this->active = $act;
	}
	
	public function setAccountTrades($at) {
		$this->accountTrades = $at;
	}
	
	public function toString() {
		$accountToString = "ID: ".$this->id."<br />";
		$accountToString .= "Account#: ".$this->accountNumber."<br />";
		$accountToString .= "FirstName: ".$this->firstName."<br />";
		$accountToString .= "LastName: ".$this->lastName."<br />";
		$accountToString .= "Broker#: ".$this->repNumber."<br />";
		$accountToString .= "Active: ".$this->active."<br />";
		
		if ($this->accountTrades!=NULL) {
			for ($i = 0; $i < count($this->accountTrades); $i++) {
				$accountToString .= $this->accountTrades[$i]->toString();
			}
		} else {
			$accountToString .= "No Trades.<br />";
		}
		return $accountToString;
	}
	
}