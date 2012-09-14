<?php

class OverrideRep {
	
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
		return $this->repNumber . " - " . $this->label . " | Override Percent: " . $this->splitPercent;
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
		$overrideRepToString = "&nbsp;&nbsp;" . "[OverrideRep: " . $this->repNumber . "<br />";
		$overrideRepToString .= "&nbsp;&nbsp;" . "->Label: " . $this->label . "<br />";
		$overrideRepToString .= "&nbsp;&nbsp;" . "->Override Percent: " . $this->splitPercent . "]<br />";
		return $overrideRepToString;
	}
	
}