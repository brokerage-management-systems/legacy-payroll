<?php

class SalesAssistant {

	private $active;
	private $firstName;
	private $lastName;
	private $pay;
	private $percent;
	private $salesAssistantId;
	
	private $brokers;

	public function getActive() {
		return $this->active;
	}

	public function getBrokers() {
		return $this->brokers;
	}

	public function getFirstName() {
		return $this->firstName;
	}

	public function getLastName() {
		return $this->lastName;
	}

	public function getPay() {
		return $this->pay;
	}

	public function getPercent() {
		return $this->percent;
	}

	public function getSalesAssistantId() {
		return $this->salesAssistantId;
	}

	public function setActive($act) {
		$this->active = $act;
	}

	public function setBrokers($br) {
		$this->brokers = $br;
	}

	public function setFirstName($fn) {
		$this->firstName = $fn;
	}

	public function setLastName($ln) {
		$this->lastName = $ln;
	}

	public function setPay($py) {
		$this->pay = $py;
	}

	public function setPercent($pt) {
		$this->percent = $pt;
	}

	public function setSalesAssistantId($said) {
		$this->salesAssistantId = $said;
	}

	public function toString() {
		$salesAssistantToString = "&nbsp;&nbsp;" . "[SalesAssistantID: ".$this->salesAssistantId."<br />";
		$salesAssistantToString .= "&nbsp;&nbsp;" . "FirstName: ".$this->firstName."<br />";
		$salesAssistantToString .= "&nbsp;&nbsp;" . "LastName: ".$this->lastName."<br />";
		$salesAssistantToString .= "&nbsp;&nbsp;" . "Active: ".$this->active."<br />";

		if ($this->brokers!=NULL) {
			for ($i = 0; $i < count($this->brokers); $i++) {
				$salesAssistantToString .= "&nbsp;&nbsp;" . "->Broker: " . $this->brokers[$i][0] . "<br />";
				$salesAssistantToString .= "&nbsp;&nbsp;" . "->Percentage: " . $this->brokers[$i][1] . "<br />";
				$salesAssistantToString .= "&nbsp;&nbsp;" . "->Expense: " . $this->brokers[$i][2] . "]<br />";
			}
		} else {
			$salesAssistantToString .= "&nbsp;&nbsp;" . "No Brokers Assigned.]<br />";
		}
		
		$salesAssistantToString .= "&nbsp;&nbsp;" . "Pay: ".$this->pay."]<br />";
		
		return $salesAssistantToString;
	}


}