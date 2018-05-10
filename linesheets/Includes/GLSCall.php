<?php 
class GLSPDFCall extends Call{
	protected $session = '';
	protected $timesMinimized = '';
	protected $associateDN = '';
	protected $startTime = '';
	protected $durationMinimized = '';
	protected $monitorID = '';
	protected $stopTime = '';
	protected $durationMonitored = '';
	protected $inOutDigits = '';
	protected $date = '';
	protected $totalDuration = '';
	protected $subscriber = '';
	protected $direction = '';
	protected $language = '';
	protected $classification = '';
	protected $content = '';
	protected $complete = '';
	protected $participants = '';
	protected $comment = '';
	protected $synopsis1 = '';
	protected $synopsis2 = '';
	protected $sMS = '';
	protected $startPage = '';
	protected $IAPSystemID = '';
	protected $location = '';

	Function __construct($startPage) {
		$this->startPage = $startPage;
	}
	public function setLocation($location) {
		$this->location = $location;
	}
	public function getLocation() {
		return $this->location;
	}
	public function setSMS($sMS) {
		$this->sMS = $sMS;
	}
	public function getSMS() {
		return $this->sMS;
	}
	public function setIAPSystemID($IAPSystemID) {
		$this->IAPSystemID = $IAPSystemID;
	}
	public function getIAPSystemID() {
		return $this->IAPSystemID;
	}
	public function setSession($session) {
		$this->session = $session;
	}
	public function getSession() {
		return $this->session;
	}
	public function setTimesMinimized($timesMinimized) {
		$this->timesMinimized = $timesMinimized;
	}
	public function getTimesMinimized() {
		return $this->timesMinimized;
	}
	public function setAssociateDN($associateDN) {
		$this->associateDN = $associateDN;
	}
	public function getAssociateDN() {
		return $this->associateDN;
	}
	public function setStartTime($startTime) {
		$this->startTime = $startTime;
	}
	public function getStartTime() {
		return $this->startTime;
	}
	public function setDurationMinimized($durationMinimized) {
		$this->durationMinimized = $durationMinimized;
	}
	public function getDurationMinimized() {
		return $this->durationMinimized;
	}
	public function setMonitorID($monitorID) {
		$this->monitorID = $monitorID;
	}
	public function getMonitorID() {
		return $this->monitorID;
	}
	public function setStopTime($stopTime) {
		$this->stopTime = $stopTime;
	}
	public function getStopTime() {
		return $this->stopTime;
	}
	public function setDurationMonitored($durationMonitored) {
		$this->durationMonitored = $durationMonitored;
	}
	public function getDurationMonitored() {
		return $this->durationMonitored;
	}
	public function setInOutDigits($inOutDigits) {
		$this->inOutDigits = $inOutDigits;
	}
	public function getInOutDigits() {
		return $this->inOutDigits;
	}
	public function setDate($date) {
		$this->date = $date;
	}
	public function getDate() {
		return $this->date;
	}
	public function setTotalDuration($totalDuration) {
		$this->totalDuration = $totalDuration;
	}
	public function getTotalDuration() {
		return $this->totalDuration;
	}
	public function setSubscriber($subscriber) {
		$this->subscriber = $subscriber;
	}
	public function getSubscriber() {
		return $this->subscriber;
	}
	public function setDirection($direction) {
		$this->direction = $direction;
	}
	public function getDirection() {
		return $this->direction;
	}
	public function setLanguage($language) {
		$this->language = $language;
	}
	public function getLanguage() {
		return $this->language;
	}
	public function setClassification($classification) {
		$this->classification = $classification;
	}
	public function getClassification() {
		return $this->classification;
	}
	public function setContent($content) {
		$this->content = $content;
	}
	public function getContent() {
		return $this->content;
	}
	public function setComplete($complete) {
		$this->complete = $complete;
	}
	public function getComplete() {
		return $this->complete;
	}
	public function setParticipants($participants) {
		$this->participants = $participants;
	}
	public function getParticipants() {
		return $this->participants;
	}
	public function setComment($comment) {
		$this->comment = $comment;
	}
	public function getComment() {
		return $this->comment;
	}
	public function setSynopsis1($synopsis1) {
		$this->synopsis1 = $synopsis1;
	}
	public function getSynopsis1() {
		return $this->synopsis1;
	}
	public function setSynopsis2($synopsis2) {
		$this->synopsis2 = $synopsis2;
	}
	public function getSynopsis2() {
		return $this->synopsis2;
	}
	public function setAudio($audio) {
		$this->audio = $audio;
	}
	public function getAudio() {
		return $this->audio;
	}
	function getInText() {
		return $this->inText;
	}
	public function setInText($val) {
		$this->inText = true;
	}
	public function setFirstLine($line) {
		$lineText = $line->getLineText();
		$dateStart = strpos($lineText, "Date:"); //5
		$classificationStart = strpos($lineText, "Classification:"); //15
		$durationMonitoredStart = strpos($lineText, "Duration:"); //9
		// pattern for session
		$pattern = "/(\\s)*Session:(\\s)+[0-9]+/";
		preg_match($pattern, $lineText, $matches);
		$session = trim(substr(trim($matches[0]), 8));
		$this->setSession($session);
		// patern for Date
		$date = trim(substr($lineText, $dateStart+5, $classificationStart-$dateStart-5));
		$this->setDate($date);
		// pattern for Classification

		$classification = trim(substr($lineText, $classificationStart+15,$durationMonitoredStart-$classificationStart-15));
	//	echo "classification: $classification <BR>";
		$this->setClassification($classification);

		//pattern for Duration
//		$durationMonitoredLen = $inOutDigitsStart - $durationMonitoredStart - 19;
		$durationMonitored = trim(substr($lineText, $durationMonitoredStart+9));
		$this->setDurationMonitored($durationMonitored);
/*
		$pattern = "/Times minimized:(\\s)+[0-9]+/";
		preg_match($pattern, $lineText, $matches);
		$timesMinimized = trim(substr(trim($matches[0]), 16));
		$this->setTimesMinimized($timesMinimized);
		// patern for  Associate DN
		$loc = strpos($lineText, "Associate DN:");
		$associateDN = trim(substr($lineText, $loc+13));
		$this->setAssociateDN($associateDN);
//		echo "session: $session times minimized: $timesMinimized associateDN: $associateDN <BR>";

*/
	}
	public function setSecondLine($line) {
		$lineText = $line->getLineText();
		$monitorIDStart = strpos($lineText, "Monitored By:"); //13
		$startTimeStart = strpos($lineText, "Start Time:"); //11
		$completeStart = strpos($lineText, "Complete:"); //9
		$directionStart = strpos($lineText, "Direction:"); //10
//		$directionLen = $languageStart-$directionStart-9;

		//monitorID
		$monitorID = trim(substr($lineText, $monitorIDStart + 13,$startTimeStart-$monitorIDStart-13));
		$this->setMonitorID($monitorID);

		//start time
		$startTime = trim(substr($lineText, $startTimeStart+11,$completeStart-$startTimeStart-11));
		$this->setStartTime($startTime);

		//complete
		
		$complete = trim(substr($lineText, $completeStart+9, $directionStart-$completeStart-9));
		$this->setComplete($complete);

		//direction
		$direction = trim(substr($lineText, $directionStart+10));
		$this->setDirection($direction);

//		echo "start time: $startTime | duration Minimized: $durationMinimized | Monitor ID: $monitorID <BR>";

	}
	// stop time duration monitored, inoutdigits
	public function setThirdLine($line) {
		//Out Digits | Asscociate Number | Participants
		$lineText = $line->getLineText();
		$associateDNStart = strpos($lineText, "Asscociate Number:"); //18
		$inOutDigitsStart = strpos($lineText, "Out Digits:"); //11
		$participantsStart = strpos($lineText, "Participants:"); //13
		//in out digits
		$inOutDigits = trim(substr($lineText, $inOutDigitsStart+11, $associateDNStart-$inOutDigitsStart-11));
		$this->setInOutDigits($inOutDigits);

		$associateDN = trim(substr($lineText, $associateDNStart+18, $participantsStart-$associateDNStart-18));
		$this->setAssociateDN($associateDN);

		//participants
		$participants = trim(substr($lineText, $participantsStart+13));
		$this->setParticipants($participants);
/*		$durationMonitoredStart = strpos($lineText, "Duration monitored:");
		$inOutDigitsStart = strpos($lineText, "Out Digits:");
		$stopTimeLen = $durationMonitoredStart - 10;
		$durationMonitoredLen = $inOutDigitsStart - $durationMonitoredStart - 19;

		//stoptime
		$stopTime = trim(substr($lineText, 10, $stopTimeLen));
		$this->setStopTime($stopTime);

		//durationMonitored
		$durationMonitored = trim(substr($lineText, $durationMonitoredStart+19, $durationMonitoredLen));
		$this->setDurationMonitored($durationMonitored);

//		echo "stop time: $stopTime | duration monitored: $durationMonitored | inOutDigits: $inOutDigits <BR>";
		
		if ($matches) {
			$match = trim($matches[0]);
			$participants = trim(substr($match, 13));
			$this->participants = $participants;
		}
		$pattern = "/Digits:(\\s)*[0-9]+(\\s)+/";
		preg_match($pattern, $lineText, $matches);
		if ($matches) {
			$match = trim($matches[0]);
			$digit = trim(substr($match, 7));
			$this->otherParty = $digit;
		}
		if ($this->direction == 'Outgoing') {
			$this->fromPhone = $util->getTargetPhone();
			$this->mainFromParticipant = $util->getTargetName();
			$this->toPhone = $this->otherParty;
		} else {
			$this->fromPhone = $this->otherParty;
			$this->toPhone = $util->getTargetPhone();
			$this->mainToParticipant = $util->getTargetName();
		}
		echo "from phone: $this->fromPhone | to phone: $this->toPhone <BR>";
*/
	}
		// date, total duration, subscriber
	public function setFourthLine($line) {
		$lineText = $line->getLineText();
		$locationStart = strpos($lineText, "Location:"); //9
		//iapsystemID: 12
		$IAPSystemID = trim(substr($lineText, 12,$locationStart-12));
		$this->setIAPSystemID($IAPSystemID);
		$location = trim(substr($lineText, $locationStart+9));
		$this->setLocation($location);
/*		$dateStart = strpos($lineText, "Date:")+5;
		$totalDurationStart = strpos($lineText, "Total Duration:")+15;
		$subscriberStart = strpos($lineText, "Subscriber:")+11;
		$dateLen = $totalDurationStart-$dateStart-15;
		$totalDurationLen = $subscriberStart-$totalDurationStart-11;

		//date
		$date = trim(substr($lineText, $dateStart, $dateLen));
		$this->setDate($date);

		//total duration
		$totalDuration = trim(substr($lineText, $totalDurationStart, $totalDurationLen));
		$this->setTotalDuration($totalDuration); 

		//subscriber
		$subscriber = trim(substr($lineText, $subscriberStart));
		$this->setSubscriber($subscriber);

//		echo "date: $date | total Duration: $totalDuration | subscriber: $subscriber <BR>";
*/
	}
	
	//Direction:    Language:       Classification:   
	public function setFifthLine($line) {
/*		$lineText = $line->getLineText();
		$directionStart = strpos($lineText, "Direction:")+10;
		$languageStart = strpos($lineText, "Language:")+9;
		$classificationStart = strpos($lineText, "Classification:")+15;
		$directionLen = $languageStart-$directionStart-9;
		$languageLen = $classificationStart-$languageStart-15;

		//direction
		$direction = trim(substr($lineText, $directionStart, $directionLen));
		$this->setDirection($direction);

		//language
		$language = trim(substr($lineText, $languageStart, $languageLen));
		$this->setLanguage($language); 

		//classification
		$classification = trim(substr($lineText, $classificationStart));
		$this->setClassification($classification);
		*/

//		echo "direction: $direction | language: $language | classification: $classification <BR>";
	}

	//Content:           Complete:       Participants:
	public function setSixthLine($line) {
		$lineText = $line->getLineText();
		$contentStart = strpos($lineText, "Content:")+8;
		$completeStart = strpos($lineText, "Complete:")+9;
		$participantsStart = strpos($lineText, "Participants:")+13;
		$contentLen = $completeStart-$contentStart-9;
		$completeLen = $participantsStart-$completeStart-13;

		//content
		$content = trim(substr($lineText, $contentStart, $contentLen));
		$this->setContent($content);

		//complete
		$complete = trim(substr($lineText, $completeStart, $completeLen));
		$this->setComplete($complete);

		//participants
		$participants = trim(substr($lineText, $participantsStart));

		$this->setParticipants($participants); 
		//		echo "content: $content | complete: $complete | participants: $participants <BR>";
	}
	public function setCommentsLine($line) {
		$lineText= $line->getLineText();
		if ($this->getComment() == '') {
			$this->setComment($lineText); 
		} else {
			$this->setComment($this->getComment() . "\n".$lineText);
		}
	}
	public function setSynopsisLine($line){
		$lineText = $line->getLineText();
		if ($this->getSynopsis1() == '') {
			$this->setSynopsis1($lineText); 
		} else {
			$this->setSynopsis1($this->getSynopsis1() ."\n". $lineText);
		}
	}
	public function setSMSLine($line) {
		$lineText = $line->getLineText();
		if ($this->getSMS() == '') {
			$this->setSMS($lineText);
		} else {
			$this->setSMS($this->getSMS() . "\n" . $lineText);
		}

	}
	public function isComments($line) {
		$lineText = $line->getLineText();
		if ($lineText == 'Comments') {
			return true;
		} else {
			return false;
		}
	}
	public function isIAP($line) {
		$lineText = $line->getLineText();
		if (substr($lineText,0,3)=="IAP") {
			return true;
		} else {
			return false;
		}
	}

	public function isSMS($line) {
		$lineText = $line->getLineText();
		if ($lineText =='Content (SMS - Pager)') {
			return true;
		} else {
			return false;
		}
	}
	public function isSynopsis($line) {
		$lineText = $line->getLineText();
		if ($lineText != 'Synopsis') {
/*			$this->setComment($this->getComment(). "\n" . $lineText);
			echo "comments: " . $this->getComment() . "<BR>";
*/			return false;
		} else {
			return true;
		}
	}
	public function getShortConvertedFilename() {
		if ($this->getContent() =='SMS') {
			return str_pad($this->getSession(), 5, "0", STR_PAD_LEFT) . "Content(SMS-Pager).pdf";
		} else {
			return false;
		}
	}

	public function getConvertedFilename() {
		$date = $this->getDate();
		$time = $this->getStartTime();
		$session = $this->getSession();
		$content = $this->getContent();
		if ($content == 'SMS') {
			$linkString = "202-241-0206_" . substr($date, 6,4)."-".substr($date,0,2) ."-".substr($date, 3,2). "_"  .
			substr($time,0, 2) . "-" . substr($time,3, 2) ."-" . substr($time, 6,2) ."_" . str_pad($session, 5, "0",STR_PAD_LEFT) . "Content(SMS-Pager).pdf";
		} else {
			$linkString = false;
		}		
		return $linkString;
	}
	public function getAudioFilename() {
		$date = $this->getDate();
	//	echo "date: $date <BR>";
		$time = $this->getStartTime();
		$session = $this->getSession();
		$content = $this->getContent();
		if ($content == 'SMS') {
			return "";
			//$contentString = "Content(SMS-Pager).rtf";
		} else {
			$contentString = "-001.wav";
			$linkString = "MikeThomas512-547-5707_" . substr($date, 6,4)."-".substr($date,0,2) ."-".substr($date, 3,2). "_" . 
			substr($time,0, 2) . "-" . substr($time,3, 2) ."-" . substr($time, 6,2) . "_" . str_pad($session, 5, "0",STR_PAD_LEFT) . $contentString;
//			echo $linkString . "<BR>";
			return $linkString; 
		}

	}
	public function getTextFilename() {
		$date = $this->getDate();
		$time = $this->getStartTime();
		$session = $this->getSession();
	$content = $this->getContent();
		if ($content == 'SMS') {
			$contentString = "Content(SMS-Pager).rtf";
			$linkString = "305-713-2075" . substr($date, 6)."-".substr($date,0,2) ."-".substr($date, 3,2).   
			substr($time,0, 2) . "-" . substr($time,3, 2) ."-" . substr($time, 6,2) . str_pad($session, 5, "0",STR_PAD_LEFT) . $contentString;
			return $linkString;
		} else {
			return "";
		}
	}
	public function getShortAudioFilename() {
		if ($this->getContent() =='SMS') {
// return str_pad($this->getSession(), 5, "0", STR_PAD_LEFT) . "Content(SMS-Pager).rtf";
			return "";
		} else {
			return str_pad($this->getSession(), 5, "0", STR_PAD_LEFT) . ""; 
		}
	}
	public function getShortTextFilename() {
		if ($this->getContent() =='SMS') {
			return str_pad($this->getSession(), 5, "0", STR_PAD_LEFT) . "Content(SMS-Pager).rtf";
		} else {
			return "";
		}
	}
}
?> 