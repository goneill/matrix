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
	public function setPrimaryLanguage($primaryLanguage) {
		$this->primaryLanguage = $primaryLanguage;
	}
	public function getPrimaryLanguage() {
		return $this->primaryLanguage;
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
	//session classficiation direction
	public function setFirstLine($line) {
		$lineText = $line->getLineText();
		$classificationStart = strpos($lineText, "Classification:"); //15
		$directionStart = strpos($lineText, "Direction:"); //9
		// pattern for session
		$pattern = "/(\\s)*Session:(\\s)+[0-9]+/";
		preg_match($pattern, $lineText, $matches);
		$session = trim(substr(trim($matches[0]), 8));
		$this->setSession($session);
		// pattern for Classification
		$classification = trim(substr($lineText, $classificationStart+15,$directionStart-$classificationStart-15));
		$this->setClassification($classification);

		//pattern for direction
		$direction = trim(substr($lineText, $directionStart+10));
		$this->setDirection($direction);
	//	echo "session $session | classification $classification | direction $direction <BR>";
	}
	//date start associate dn
	public function setSecondLine($line) {
		$lineText = $line->getLineText();
	//	echo $lineText . "<BR>";
		$dateStart = strpos($lineText, "Date:"); //5
		$associateDNStart = strpos($lineText, "Associate DN:"); //13
		if ($associateDNStart) {
			// patern for Date
			$date = trim(substr($lineText, $dateStart+5, $associateDNStart-$dateStart-18));
			$this->setDate($date);
			//pattern for Associate DN
			$associateDN = trim(substr($lineText, $associateDNStart+13,30));
			$this->setAssociateDN($associateDN);
		} else { // no associate dn on this 

			$date = trim(substr($lineText, $dateStart+5));
			$this->setDate($date);
		}
		// if there is no associate dn on this line 

	}
	// stop time duration monitored, inoutdigits
	public function setThirdLine($line) {
		// Start Time | Content | In/Out Digits
		$lineText = $line->getLineText();
		$startTimeStart = strpos($lineText, "Start Time:"); //11
		$contentStart = strpos($lineText, "Content:"); //8
		$inOutDigitsStart = strpos($lineText, "Out Digits:"); //11
		$associateDNStart = strPos($lineText, "Associate DN:"); //13
		//start time
		$startTime = trim(substr($lineText, $startTimeStart+11,$contentStart-$startTimeStart-11));
		$this->setStartTime($startTime);
		if ($inOutDigitsStart) {

			//content
			$content = trim(substr($lineText, $contentStart+8, $inOutDigitsStart-$contentStart-11));
			$this->setContent($content);
			$inOutDigits = trim(substr($lineText, $inOutDigitsStart+11, 20));
			$this->setInOutDigits($inOutDigits);
		} elseif ($associateDNStart) {
			//content
			$content = trim(substr($lineText, $contentStart+8, $associateDNStart-$contentStart-11));
			$this->setContent($content);
			$associateDN = trim(substr($lineText, $associateDNStart+13, 20));
			$this->setAssociateDN($associateDN);

		}
//		echo "startTime $startTime | content: $content | inOutDigits: $inOutDigits <BR>";
	}

	// stop Time | Subscriber
	public function setFourthLine($line) {
		$lineText = $line->getLineText();

		$stopTimeStart = strpos($lineText, "Stop Time:"); //10
		$subscriberStart = strpos($lineText, "Subscriber:"); //12
		//iapsystemID: 12
		//start time
		if($subscriberStart) {
			$stopTime = trim(substr($lineText, $stopTimeStart+10,$subscriberStart-$stopTimeStart-10));
		} else {
			$stopTime = trim(substr($lineText, $stopTimeStart+10));
		}
		$this->setStopTime($stopTime);
		if ($subscriberStart) {
			$subscriber = trim(substr($lineText, $subscriberStart+12, 20));
			$this->setSubscriber($subscriber);
		}

	}
	// duration | primary language | participants
	public function setFifthLine($line) {
		$lineText = $line->getLineText();
		$durationStart = strpos($lineText, "Duration:"); //+9;
		$primaryLanguageStart = strpos($lineText, "Primary Language:"); //+17;
		$participantsStart = strpos($lineText, "Participants:"); //+13;
		$inOutDigitsStart = strpos($lineText, "Out Digits:"); //11

		//total duration
		$duration = trim(substr($lineText, $durationStart+9,$primaryLanguageStart-$durationStart-10));
		$this->setTotalDuration($duration); 
		if ($participantsStart) {
			//primary language
			$primaryLanguage = trim(substr($lineText, $primaryLanguageStart+17,$participantsStart-$primaryLanguageStart-17));
			$this->setPrimaryLanguage($primaryLanguage); 

			$participants = trim(substr($lineText, $participantsStart+13));
			$this->setParticipants($participants);

//		echo "duration $duration | primaryLanguage: $primaryLanguage | participants: $participants <BR>";
		} elseif ($inOutDigitsStart) {
			//primary language
			$primaryLanguage = trim(substr($lineText, $primaryLanguageStart+17,$inOutDigitsStart-$primaryLanguageStart-17));
			$this->setPrimaryLanguage($primaryLanguage);
			$inOutDigits = trim(substr($lineText, $inOutDigitsStart+11));
			$this->setInOutDigits($inOutDigits);
		} else {
			$primaryLanguage = trim(substr($lineText, $primaryLanguageStart+17));
			$this->setPrimaryLanguage($primaryLanguage);

		}


	}	

	//Complete | Possible more participants      
	public function setSixthLine($line) {
		$lineText = $line->getLineText();
		$completeStart = strpos($lineText, "Complete:"); //+9;
		$subscriberStart = strpos($lineText, "Subscriber:"); //+11
		$participantsStart = $completeStart + 30; 
	
		//content
		$complete = trim(substr($lineText, $completeStart+9, 22));
		$this->setComplete($complete);
		if ($subscriberStart) {
			$subscriber = trim(substr($lineText, $subscriberStart+11));
		} else {
			//participants
			$participants = trim(substr($lineText, $participantsStart));
			$this->setParticipants($this->getParticipants(). " " . $participants);
		}
//		echo "complete: $complete | participants: " . $this->getParticipants() . "<BR>";
	}
	//Monitor ID: | Possible more participants      
	public function setSeventhLine($line) {
		$lineText = $line->getLineText();
		$monitorIDStart = strpos($lineText, "Monitor ID:"); //+11
		$participantsStart = strpos($lineText, "Participants:"); //+13;
		if (!$participantsStart) {
			$participantsStart = $monitorIDStart + 16; 
		}
		//monitor ID
		$monitorID = trim(substr($lineText, $monitorIDStart+11, 19));
		$this->setMonitorID($monitorID);

		//participants
		$participants = trim(substr($lineText, $participantsStart+13));
		$this->setParticipants($this->getParticipants(). " " . $participants);

//		echo "monitor ID: $monitorID | participants: " . $this->getParticipants() . "<BR>";

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
		$in = $line->getLineText();
		$lineText = preg_replace('/\s+/', ' ', $in);
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

	// changing this to account for the case where the line is Synopsis plus other stuff...
	public function isSynopsis($line) {
		$lineText = trim($line->getLineText());
		if (strpos($lineText, 'Synopsis') === FALSE) {
//		if ($lineText != 'Synopsis') {
/*			$this->setComment($this->getComment(). "\n" . $lineText);
			echo "comments: " . $this->getComment() . "<BR>";
*/			return false;
		} elseif ($lineText == 'Synopsis') {
			return true;
		} else { //there is synopsis plus!  
			$synopsis = substr($lineText, 8);
			$this->setSynopsis1($synopsis);
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