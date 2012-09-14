<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

require_once 'JointRep.php';
require_once 'OverrideRep.php';
require_once 'BrokerPayout.php';
require_once 'SalesAssistant.php';
require_once 'Account.php';
require_once 'Trade.php';

class Broker {

	private $id;
	private $repNumber;
	private $firstName;
	private $lastName;
	private $active;
	
	private $brokerAccounts;
	private $brokerTrades;
	private $jointReps;
	private $overrideReps;
	private $payoutVals;
	private $salesAssistant;
	
	private $repFullName;
	private $repNameAndNumber;
	
	public function getID() {
		return $this->id;
	}
	
	public function getRepNumber() {
		return $this->repNumber;
	}
	
	public function getFirstName() {
		return $this->firstName;
	}
	
	public function getLastName() {
		return $this->lastName;
	}
	
	public function getActive() {
		return $this->active;
	}
	
	public function getFullName() {
		return $this->lastName . ", " . $this->firstName;
	}
	
	public function getNameAndNumber() {
		return $this->repNumber . " - " . $this->lastName . ", " . $this->firstName;
	}
	
	public function getJointReps() {
		return $this->jointReps;
	}
	
	public function getOverrideReps() {
		return $this->overrideReps;
	}
	
	public function getPayoutValues() {
		return $this->payoutVals;
	}
	
	public function getSalesAssistant() {
		return $this->salesAssistant;
	}
	
	public function getBrokerAccounts() {
		return $this->brokerAccounts;
	}
	
	public function getBrokerTrades() {
		return $this->brokerTrades;
	}
	
	public function setID($id) {
		$this->id = $id;
	}
	
	public function setRepNumber($rn) {
		$this->repNumber = $rn;
	}
	
	public function setFirstName($fn) {
		$this->firstName = $fn;
	}
	
	public function setLastName($ln) {
		$this->lastName = $ln;
	}
	
	public function setActive($a) {
		$this->active = $a;
	}
	
	public function setJointReps($jr) {
		$this->jointReps = $jr;
	}
	
	public function setOverrideReps($or) {
		$this->overrideReps = $or;
	}

	public function setPayoutValues($pv) {
		$this->payoutVals = $pv;
	}

	public function setSalesAssistant($sa) {
		$this->salesAssistant = $sa;
	}

	public function setBrokerAccounts($ba) {
		$this->brokerAccounts = $ba;
	}
	
	public function setBrokerTrades($bt) {
		$this->brokerTrades = $bt;
	}
	
	public function toString() {
		$brokerToString = "ID: ".$this->id."<br />";
		$brokerToString .= "Rep#: ".$this->repNumber."<br />";
		$brokerToString .= "FirstName: ".$this->firstName."<br />";
		$brokerToString .= "LastName: ".$this->lastName."<br />";
		$brokerToString .= "Active: ".$this->active."<br />";
		
		if ($this->jointReps!=NULL) {
			for ($i = 0; $i < count($this->jointReps); $i++) {
				$brokerToString .= $this->jointReps[$i]->toString();
			}
		} else {
			$brokerToString .= "No Joint Reps.<br />";
		}
		
		if ($this->overrideReps!=NULL) {
			for ($i = 0; $i < count($this->overrideReps); $i++) {
				$brokerToString .= $this->overrideReps[$i]->toString();
			}
		} else {
			$brokerToString .= "No Rep Overrides.<br />";
		}
		
		if ($this->payoutVals!=NULL) {
			for ($i = 0; $i < count($this->payoutVals); $i++) {
				$brokerToString .= $this->payoutVals[$i]->toString();
			}
		} else {
			$brokerToString .= "No Payout Structure.<br />";
		}
		
		if ($this->salesAssistant!=NULL) {
			$brokerToString .= $this->salesAssistant[0]->toString();
		} else {
			$brokerToString .= "No Sales Assistant.<br />";
		}
		
		if ($this->brokerAccounts!=NULL) {
			for ($i = 0; $i < count($this->brokerAccounts); $i++) {
				$brokerToString .= $this->brokerAccounts[$i]->toString();
			}
		} else {
			$brokerToString .= "No Accounts.<br />";
		}
		
		if ($this->brokerTrades!=NULL) {
			for ($i = 0; $i < count($this->brokerTrades); $i++) {
				$brokerToString .= $this->brokerTrades[$i]->toString();
			}
		} else {
			$brokerToString .= "No Trades.<br />";
		}
		
		return $brokerToString;
	}
	
}