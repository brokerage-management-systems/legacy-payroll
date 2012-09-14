<?php

class BrokerPayout {
	
	private $repNumber;
	private $minimum;
	private $maximum;
	private $payPercent;
	
	public function getRepNumber() {
		return $this->repNumber;
	}
	
	public function getMinimum() {
		return $this->minimum;
	}
	
	public function getMaximum() {
		return $this->maximum;
	}
	
	public function getPayPercent() {
		return $this->payPercent;
	}
	
	public function getFullInfo() {
		return $this->repNumber . " - " . 
		"If gross is greater than " . $this->minimum . 
		" & less than " . $this->maximum . 
		"Payout is: " . $this->payPercent;
	}
	
	public function setRepNumber($rn) {
		$this->repNumber = $rn;
	}
	
	public function setMinimum($min) {
		$this->minimum = $min;
	}
	
	public function setMaximum($max) {
		$this->maximum = $max;
	}
	
	public function setPayPercent($pay) {
		$this->payPercent = $pay;
	}
	
	public function toString() {
		$payoutToString = "&nbsp;&nbsp;" . "[Payout for: " . $this->repNumber . "<br />";
		$payoutToString .= "&nbsp;&nbsp;" . "->Gross is greater than " . $this->minimum . "<br />";
		$payoutToString .= "&nbsp;&nbsp;" . "->Gross is less than " . $this->maximum . "<br />";
		$payoutToString .= "&nbsp;&nbsp;" . "->Payout percentage is " . $this->payPercent . "]<br />";
		return $payoutToString;
	}
	
}