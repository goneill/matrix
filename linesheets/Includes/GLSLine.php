<?php 
class GLSLine {

	protected $isHeaderFooter= false;
	protected $lineText;
	protected $newCall = false;
	protected $isSecondLine = false;
	protected $isThirdLine = false;
	protected $startInText = false;

	function __construct($lineText) {
		$lineText = trim($lineText);
		$this->lineText = $lineText;
		if ($lineText == '') {
			$this->isHeaderFooter = true;
		}
		if (strpos($lineText, "Case:")===0) {
			$this->isHeaderFooter = true;
			return;
		}
		$date = strtotime(substr($lineText,0,18));
		if ($date) {
			$this->isHeaderFooter = true;
			return;
		} 
		if(strpos(" " . $lineText, "Synopsis")>0) {
			$this->startInText = true;
		}
		if (strpos(" " . $lineText, "Digits:")>0 || strpos(" " . $lineText, "Participants:")>0) {
			$this->isThirdLine = true;
		}
		if (strpos(" " . $lineText, "Monitored By:")>0) {
			$this->isSecondLine = true;
		}
		if ((strpos($lineText, "Session:")==0) && (strpos($lineText, "Date:")> 0) && (strpos($lineText, 'Classification:')>0) ) {
			$this->newCall = true;
			return;
		}
	}

	public function getIsHeaderFooter() {
		return $this->isHeaderFooter;
	}
	public function getNewCall() {
		return $this->newCall;
	}
	public function getLineText() {
		return $this->lineText;
	}
	public function getIsSecondLine() {
		return $this->isSecondLine;
	}
	public function getIsThirdLine() {
		return $this->isThirdLine;
	}
	public function getStartInText() {
		return $this->startInText;
	}
}
?> +