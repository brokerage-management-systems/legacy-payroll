<?php
/*
 *  Payroll Management System - Version 2.2
 *  InterDev Inc.
 *  http://www.interdevinc.com
 *  support@interdevinc.com
 */

class Client {
	
	private $accountNumber;
	private $active;
	private $branchCode;
	private $clientID;
	private $firstName;
	private $fullAccountNumber;
	private $lastName;
	private $repNumber;
	
	/*
	 * @return $accountNumber
	 */
	public function getAccountNumber() {
		return $this->accountNumber;
	}
	
	/*
	 * @return $active
	 */
	public function getActive() {
		return $this->active;
	}
	
	/*
	 * @return $branchCode
	 */
	public function getBranchCode() {
		return $this->branchCode;
	}
	
	/*
	 * @return $clientFullInfo
	 */
	public function getClientFullInfo() {
		return $this->lastName + ", " + $this->firstName + " - " + $this->accountNumber;;
	}

	/*
	 * @return $clientFullInfoWRepNumber
	 */
	public function getClientFullInfoWRepNumber() {
		return $this->lastName + ", " + $this->firstName + " - " + $this->accountNumber + " | " + $this->repNumber;
	}

	/*
	 * @return $clientID
	 */
	public function getClientID() {
		return $this->clientID;
	}

	/*
	 * @return $firstName
	 */
	public function getFirstName() {
		return $this->firstName;
	}

	/*
	 * @return $fullAccountNumber
	 */
	public function getFullAccountNumber() {
		return $this->fullAccountNumber;
	}

	/*
	 * @return $lastName
	 */
	public function getLastName() {
		return $this->lastName;
	}

	/*
	 * @return $repNumber
	 */
	public function getRepNumber() {
		return $this->repNumber;
	}

	/*
	 * @param an the $accountNumber to set
	 */
	public function setAccountNumber($an) {
		$this->accountNumber = $an;
	}

	/*
	 * @param act the $active to set
	 */
	public function setActive($act) {
		$this->active = $act;
	}

	/*
	 * @param bc the $accountNumber to set
	 */
	public function setBranchCode($bc) {
		$this->branchCode = $bc;
	}
	
	/*
	 * @param cid the $clientID to set
	 */
	public function setClientID($cid) {
		$this->clientID = $cid;
	}

	/*
	 * @param fn the $firstName to set
	 */
	public function setFirstName($fn) {
		$this->firstName = $fn;
	}

	/*
	 * @param $fullAcct the $fullAccountNumber to set
	 */
	public function setFullAccountNumber($fullAcct) {
		$this->fullAccountNumber = $fullAcct;
		$fullAcct = explode("-", $fullAcct);
		$this->setAccountNumber($fullAcct[1]);
		$this->setBranchCode($fullAcct[0]);
	}

	/*
	 * @param ln the $lastName to set
	 */
	public function setLastName($ln) {
		$this->lastName = $ln;
	}

	/*
	 * @param rn the $repNumber to set
	 */
	public function setRepNumber($rn) {
		$this->repNumber = $rn;
	}
	
	/*
	 * toString
	 */
	public function toString() {
		$clientToString = "[Client: " . $this->clientID . "<br />";
		$clientToString .= "->Account#: " . $this->fullAccountNumber . " -> " . $this->branchCode . "-" . $this->accountNumber . "<br />";
		$clientToString .= "->FirstName: " . $this->firstName . "<br />";
		$clientToString .= "->LastName: " . $this->lastName . "<br />";
		$clientToString .= "->Active: " . $this->active . "]<br />";
		
		return $clientToString;
	}
	
}