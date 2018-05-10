<?php 
class GLSCallNote extends CallNote {
	protected $classification;
	protected $participants;


	public function appendText($line) {
		$newText = $line->getLineText();
		$this->noteText = $this->noteText . "\n" . $newText;
	}

	public function setSecondLine($line) {
		$lineText = $line->getLineText();
		//pattern for monitored by
		$pattern = "/(\\s)*Monitored By:(\\s)+[a-z ]*(\\s)+Start Time:/";
		preg_match($pattern, $lineText, $matches);
		$match = trim($matches[0]);
		$monitored = trim(substr($match, 13));
		$len = strlen($monitored);
		$monitored = trim(substr($monitored,0, $len - 11));
		$this->authorNote = $monitored;
	}
	public function setThirdLine($line) {

	}
	public function getParticipants() {
		return $this->participants;
	}
	public function setParticipants($participants) {
		$this->participants = $participants;

	}
}
?>