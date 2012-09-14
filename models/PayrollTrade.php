<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class PayrollTrade {
	
	private $repNumber;
	private $cancelCode;
	private $commission;
	private $concession;
	private $quantity;
	private $securityType;
	private $tradeReferenceTradeID;
	
	public function getCancelCode() {
		return $this->cancelCode;
	}

	public function getCommission() {
		return $this->commission;
	}

	public function getConcession() {
		return $this->concession;
	}
	
	public function getQuantity() {
		return $this->quantity;
	}

	public function getRepNumber() {
		return $this->repNumber;
	}
	
	public function getSecurityType() {
		return $this->securityType;
	}
	
	public function getTradeReferenceTradeID() {
		return $this->tradeReferenceTradeID;
	}
	
	public function setCancelCode($cc) {
		$this->cancelCode = $cc;
	}

	public function setCommission($cm) {
		$this->commission = $this->parseCommissionField($cm);
		if ($this->getCancelCode()==1) {
			$this->commission = -$this->commission;
		}
	}

	public function setCommissionRolled($cm) {
		$this->commission = $cm;
	}

	public function setConcession($cn) {
		$this->concession = $this->parseConcessionField($cn);
		if ($this->getCancelCode()==1) {
			$this->concession = -$this->concession;
		}
	}

	public function setConcessionRolled($cn) {
		$this->concession = $cn;
	}
	
	public function setQuantity($qty) {
		$this->quantity = $this->parseQuantityField($qty);
	}

	public function setQuantityRolled($qty) {
		$this->quantity = $qty;
	}

	public function setRepNumber($rn) {
		$this->repNumber = $rn;
	}
	
	public function setSecurityType($st) {
		$this->securityType = $st;
	}
	
	public function setTradeReferenceTradeID($trti) {
		$this->tradeReferenceTradeID = $trti;
	}
	
	/*
	 * METHOD: TO STRING
	 */
	public function toString() {
		$payrollTradeToString = "Rep#: ".$this->repNumber."<br />";
		$payrollTradeToString .= "Cancel Code: ".$this->cancelCode."<br />";
		$payrollTradeToString .= "Quantity: ".$this->quantity."<br />";
		$payrollTradeToString .= "Commission: ".$this->commission."<br />";
		$payrollTradeToString .= "Concession: ".$this->concession."<br />";
		$payrollTradeToString .= "Security Type: ".$this->securityType."<br />";
		$payrollTradeToString .= "Trade Reference Trade ID: ".$this->tradeReferenceTradeID."<br />";
		return $payrollTradeToString;
	}
	
	private function parseCommissionField($cm) {
		$commission = substr_replace($cm, ".", -2, 0);
		return (double)$commission;
	}

	private function parseConcessionField($c) {
		$concession = substr_replace($c, ".", -2, 0);
		return (double)$concession;
	}
	
	private function parseQuantityField($qty) {
		$quantity = substr($qty,0,-5);
		return (int)$quantity;
	}

	/*
	 * METHOD: BUNCH TRADES
	 * Takes 2 Trades
	 * Sums the Commission, Concession, & Quantity, Averages the Price
	 * Returns single Trade
	 */
	public function bunchTwoTrades($firstTrade, $secondTrade) {
		$rolledTrade = new PayrollTrade();
		$rolledTrade->setRepNumber($secondTrade->getRepNumber());
		$rolledTrade->setCancelCode($secondTrade->getCancelCode());
		$rolledTrade->setQuantityRolled(($firstTrade->getQuantity() + $secondTrade->getQuantity())); // Sum
		$rolledTrade->setCommissionRolled(($firstTrade->getCommission() + $secondTrade->getCommission())); // Sum
		$rolledTrade->setConcessionRolled(($firstTrade->getConcession() + $secondTrade->getConcession())); // Sum
		$rolledTrade->setSecurityType($secondTrade->getSecurityType());
		$rolledTrade->setTradeReferenceTradeID($secondTrade->getTradeReferenceTradeID());
		return $rolledTrade;
	}

	/*
	 * METHOD: COMPARE TRADE DEFINITION TRADE ID
	 * Returns true if TradeReferenceTradeID are equal, otherwise false.
	 */
	public function compareTradeDefinitionTradeID($arrayOfTrades, $startIndex, $endIndex) {
		if ($arrayOfTrades[$startIndex]->getTradeReferenceTradeID() == $arrayOfTrades[$endIndex]->getTradeReferenceTradeID()) {
			return true;
		} else {
			return false;
		}
	}
	
}