<?php

class JointRep {
	
	private $repNumber;
	private $label;
	private $splitPercent;
	
	public function getRepNumber() {
		return $this->repNumber;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function getSplitPercent() {
		return $this->splitPercent;
	}
	
	public function getFullInfo() {
		return $this->repNumber . " - " . $this->label . " | Split: " . $this->splitPercent;
	}
	
	public function setRepNumber($rn) {
		$this->repNumber = $rn;
	}
	
	public function setLabel($l) {
		$this->label = $l;
	}
	
	public function setSplitPercent($sp) {
		$this->splitPercent = $sp;
	}
	
	public function toString() {
		$jointRepToString = "&nbsp;&nbsp;" . "[JointRep: " . $this->repNumber . "<br />";
		$jointRepToString .= "&nbsp;&nbsp;" . "->Label: " . $this->label . "<br />";
		$jointRepToString .= "&nbsp;&nbsp;" . "->Split: " . $this->splitPercent . "]<br />";
		return $jointRepToString;
	}
	
}