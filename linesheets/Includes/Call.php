<?php
class Call {
	protected $toPhone;
	protected $fromPhone;
	protected $mainToParticipant;
	protected $mainFromParticipant;
	protected $startDatetime;
	protected $duration;
	protected $linesheet;
	protected $created;
	protected $modified;
	protected $callNote;
	protected $completed;


	public function getDuration() {
		return $this->duration;
	}
	public function getStartDateTime() {
		return $this->startDatetime;
	}
}
?>