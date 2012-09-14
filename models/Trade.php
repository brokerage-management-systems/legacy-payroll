<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class Trade {

	private $repNumber;
	private $branch;
	private $accountNumber;
	private $tradeReferenceNumber;
	private $tradeDate;
	private $settleDate;
	private $accountType;
	private $marketCode;
	private $blotterCode;
	private $buySellCode;
	private $cancelCode;
	private $quantity;
	private $cusip;
	private $symbol;
	private $securityDescription1;
	private $securityDescription2;
	private $price;
	private $principal;
	private $commission;
	private $concession;
	private $solicitationCode;
	private $securityType;
	private $tradeReferenceTradeID;

	public function __construct() {
		// Create empty object.
	}

	/*
	 * GETTER METHODS
	 */

	public function getAccountNumber() {
		return $this->accountNumber;
	}

	public function getAccountType() {
		return $this->accountType;
	}

	public function getBlotterCode() {
		return $this->blotterCode;
	}

	public function getBranch() {
		return $this->branch;
	}

	public function getBuySellCode() {
		return $this->buySellCode;
	}

	public function getCancelCode() {
		return $this->cancelCode;
	}

	public function getCommission() {
		return $this->commission;
	}

	public function getConcession() {
		return $this->concession;
	}

	public function getCusip() {
		return $this->cusip;
	}

	public function getMarketCode() {
		return $this->marketCode;
	}

	public function getPrice() {
		return $this->price;
	}

	public function getPrincipal() {
		return $this->principal;
	}

	public function getQuantity() {
		return $this->quantity;
	}

	public function getRepNumber() {
		return $this->repNumber;
	}

	public function getSecurityDescription1() {
		return $this->securityDescription1;
	}

	public function getSecurityDescription2() {
		return $this->securityDescription2;
	}

	public function getSecurityType() {
		return $this->securityType;
	}

	public function getSettleDate() {
		return $this->settleDate;
	}

	public function getSolicitationCode() {
		return $this->solicitationCode;
	}

	public function getSymbol() {
		return $this->symbol;
	}

	public function getTradeDate() {
		return $this->tradeDate;
	}

	public function getTradeReferenceNumber() {
		return $this->tradeReferenceNumber;
	}

	public function getTradeReferenceTradeID() {
		return $this->tradeReferenceTradeID;
	}

	/*
	 * SETTER METHODS
	 */

	public function setAccountNumber($an) {
		$this->accountNumber = $an;
	}

	public function setAccountType($at) {
		$this->accountType = $at;
	}

	public function setBlotterCode($bc) {
		$this->blotterCode = $bc;
	}

	public function setBranch($br) {
		$this->branch = $br;
	}

	public function setBuySellCode($bsc) {
		$this->buySellCode = $bsc;
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

	public function setCusip($cip) {
		$this->cusip = $cip;
	}

	public function setMarketCode($mc) {
		$this->marketCode = $mc;
	}

	public function setPrice($pr) {
		$this->price = $this->parsePriceField($pr);
	}

	public function setPriceRolled($pr) {
		$this->price = $pr;
	}

	public function setPrincipal($pn) {
		$this->principal = $this->parsePrincipalField($pn);
	if ($this->getCancelCode()==1) {
			$this->principal = -$this->principal;
		}
	}

	public function setPrincipalRolled($pn) {
		$this->principal = $pn;
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

	public function setSecurityDescription1($sd1) {
		$this->securityDescription1 = $sd1;
	}

	public function setSecurityDescription2($sd2) {
		$this->securityDescription2 = $sd2;
	}

	public function setSecurityType($st) {
		$this->securityType = $st;
	}

	public function setSettleDate($sd) {
		$this->settleDate = $sd;
	}

	public function setSolicitationCode($sc) {
		$this->solicitationCode = $sc;
	}

	public function setSymbol($sym) {
		$this->symbol = $sym;
	}

	public function setTradeDate($td) {
		$this->tradeDate = $td;
	}

	public function setTradeReferenceNumber($trn) {
		$this->tradeReferenceNumber = $trn;
	}

	public function setTradeReferenceTradeID($trti) {
		$this->tradeReferenceTradeID = $trti;
	}

	/*
	 * METHOD: TO STRING
	 */
	public function toString() {
		$tradeToString = "Rep#: ".$this->repNumber."<br />";
		$tradeToString .= "Branch & Account#: ".$this->branch."-".$this->accountNumber."<br />";
		$tradeToString .= "Trade Reference#: ".$this->tradeReferenceNumber."<br />";
		$tradeToString .= "Trade Date: ".$this->tradeDate."<br />";
		$tradeToString .= "Settle Date: ".$this->settleDate."<br />";
		$tradeToString .= "Account Type: ".$this->accountType."<br />";
		$tradeToString .= "Market Code: ".$this->marketCode."<br />";
		$tradeToString .= "Blotter Code: ".$this->blotterCode."<br />";
		$tradeToString .= "Buy/Sell Code: ".$this->buySellCode."<br />";
		$tradeToString .= "Cancel Code: ".$this->cancelCode."<br />";
		$tradeToString .= "Quantity: ".$this->quantity."<br />";
		$tradeToString .= "Cusip: ".$this->cusip."<br />";
		$tradeToString .= "Symbol: ".$this->symbol."<br />";
		$tradeToString .= "Security Description: ".$this->securityDescription1." ".$this->securityDescription2."<br />";
		$tradeToString .= "Price: ".$this->price."<br />";
		$tradeToString .= "Principal: ".$this->principal."<br />";
		$tradeToString .= "Commission: ".$this->commission."<br />";
		$tradeToString .= "Concession: ".$this->concession."<br />";
		$tradeToString .= "Solicition Code: ".$this->solicitationCode."<br />";
		$tradeToString .= "Security Type: ".$this->securityType."<br />";
		$tradeToString .= "Trade Reference Trade ID: ".$this->tradeReferenceTradeID."<br />";
		return $tradeToString;
	}

	private function parseCommissionField($cm) {
		$commission = substr_replace($cm, ".", -2, 0);
		return (double)$commission;
	}

	private function parseConcessionField($c) {
		$concession = substr_replace($c, ".", -2, 0);
		return (double)$concession;
	}

	private function parsePriceField($pr) {
		$price = substr($pr,0,-5);
		$price = substr_replace($price, ".", -4, 0);
		return (double)$price;
	}

	private function parsePrincipalField($pn) {
		$principal = substr_replace($pn, ".", -2, 0);
		return (double)$principal;
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
		$rolledTrade = new Trade();
		$rolledTrade->setRepNumber($secondTrade->getRepNumber());
		$rolledTrade->setBranch($secondTrade->getBranch());
		$rolledTrade->setAccountNumber($secondTrade->getAccountNumber());
		$rolledTrade->setTradeReferenceNumber($secondTrade->getTradeReferenceNumber());
		$rolledTrade->setTradeDate($secondTrade->getTradeDate());
		$rolledTrade->setSettleDate($secondTrade->getSettleDate());
		$rolledTrade->setAccountType($secondTrade->getAccountType());
		$rolledTrade->setMarketCode($secondTrade->getMarketCode());
		$rolledTrade->setBlotterCode($secondTrade->getBlotterCode());
		$rolledTrade->setBuySellCode($secondTrade->getBuySellCode());
		$rolledTrade->setCancelCode($secondTrade->getCancelCode());
		$rolledTrade->setQuantityRolled(($firstTrade->getQuantity() + $secondTrade->getQuantity())); // Sum
		$rolledTrade->setCusip($secondTrade->getCusip());
		$rolledTrade->setSymbol($secondTrade->getSymbol());
		$rolledTrade->setSecurityDescription1($secondTrade->getSecurityDescription1());
		$rolledTrade->setSecurityDescription2($secondTrade->getSecurityDescription2());
		$rolledTrade->setPriceRolled(($firstTrade->getPrice() + $secondTrade->getPrice()) / 2); // Average
		$rolledTrade->setPrincipalRolled(($firstTrade->getPrincipal() + $secondTrade->getPrincipal())); // Sum
		$rolledTrade->setCommissionRolled(($firstTrade->getCommission() + $secondTrade->getCommission())); // Sum
		$rolledTrade->setConcessionRolled(($firstTrade->getConcession() + $secondTrade->getConcession())); // Sum
		$rolledTrade->setSolicitationCode($secondTrade->getSolicitationCode());
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