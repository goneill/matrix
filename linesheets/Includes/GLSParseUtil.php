 <?php 
//notes on what to do - separate dates and time.  delete header notes; make sure audio and transcript are there when they should be
// 2 different wiretaps in 1 case: US v. Blackledge; Gillard is another defendant.  

Class GLSParseUtil {
	protected $linesheetHTMLDir;
	protected $linesheetPDFDir;
	protected $linesheetTXTDir;
	protected $origAudioLink;
	protected $finalAudioLink;
	protected $outputFile;
	protected $lineOfText;
	protected $calls = array(); // array of Calls;
	protected $line;
	protected $file;
	protected $dirHandle;
	protected $fileArray;
	protected $currPageNum;
	protected $currCall = false;
	protected $targetPhone=false;
	protected $targetName = false;

	function __construct($inDirectory, $outDirectory) {
		
		$this->dirHandle = $inDirectory;
//		$this->$linesheetTxtDir = $File;
		
	}
	function strnposr($haystack, $needle, $occurrence, $pos = 0) {
	   return ($occurrence<2)?strpos($haystack, $needle, $pos):$this->strnposr($haystack,$needle,$occurrence-1,strpos($haystack, $needle, $pos) + 1);
	}

	function transformHTMLToText(){
		$count = 0;
	    $dirHandle = opendir($this->linesheetHTMLDir);
	    while($file = readdir($dirHandle)){
			if (preg_match('/.html/',$file, $matches)) {
	            $textfile = str_replace('html','txt',$file);
	            $textfile = fopen($this->linesheetTXTDir.$textfile, 'w');
				$html2text = new Html2Text($this->linesheetHTMLDir.$file,TRUE);
				$output = $html2text->get_text();
				fwrite($textfile, $output);
	            $count++;
	            echo "$count: filename: $file in $this->linesheetHTMLDir \n<br />";
	    	}
    	}
	}	
	public function transformPDFToText($main, $count=0){
	    $textdir = str_replace('pdf','txt',$main);
	    $olddir = '';
	    $dirHandle = opendir($main);
	    while($file = readdir($dirHandle)){
	    	echo "in the while loop";
			if (preg_match('/.pdf/',$file, $matches)) {
	            $textfile = str_replace('pdf','txt',$file);
	            $execstring = "/usr/local/bin/pdftotext  -layout '" .$main . $file ."' '$textdir$textfile'";
	            echo "executing: " . $execstring . "\n<BR>";
	           system($execstring, $output);
	            $count++;
	            echo "$count: filename: $file in $main \n<br />";
	        }
	    }
	    return $count;
	}
	public function findTargetName() {
		$lineText = $this->line->getLineText();
		$pattern = "/Participants:(\\s)+(.)*/";
		preg_match($pattern, $lineText, $matches);
		if ($matches) {
			$match = trim($matches[0]);
			$targetName = trim(substr($match, 13));
			$this->targetName= $targetName;
			if (!(strpos($targetName, "AKA")===FALSE)) {
				// there is an AKA that we need to parse out and put in the AKA section
				// for now maybe strip out the AKA?  Thsi is confusing because for a big case 
				// there will be many akas because of the way thte police figure them
			}
		}

	}
	protected function findTargetPhone() {
		$lineText = $this->line->getLineText();
		$strpos = strpos($lineText, 'Target');
		if (! $strpos ===false) {
			$this->targetPhone = substr(trim(substr($lineText, $strpos+8)), 0, 12); 
		}

	}
	protected function getLinesheetPDFDir() { 
		return $this->linesheetPDFDir;
	}
	protected function setLinesheetPDFDir($linesheetPDFDir) {
		$this->linesheetPDFDir = $linesheetPDFDir;
	}
	protected function getLinesheetTXTDir() { 
		return $this->linesheetTXTDir;
	}
	protected function setLinesheetTXTDir($linesheetTXTDir) {
		$this->linesheetTXTDir = $linesheetTXTDir;
	}
	protected function getTargetPhone() {
		return $this->targetPhone;
	}
	protected function getTargetName() {
		return $this->targetName;
	}
	protected function parseFirstLine() {
		$line = trim($this->line->getLineText());
	}
	protected function parseSecondLine() {
		$line = trim($this->line->getLineText());
		$parts = preg_split("/[\s]+/", $line);
		$datePattern = '^\d{1,2}\/\d{1,2}\/\d{2,4}\s+\d{2}:\d{2}:\d{2}^';//\s+[[AP]M]?^';//:d{2}:d{2}{+}^';//[ AM| PM]'
		// 		 7/3/2012 15:01:49 00:00:11 incoming call with 

		preg_match($datePattern,$line, $matches);
		$space = strpos($matches[0], ' ');
		$this->currCall->setDate(substr($matches[0],0,$space));
		$this->currCall->setTime(substr($matches[0],$space));
			$line = str_replace($matches[0], '', $line);	
		$durationPattern = '^\d{2}:\d{2}:\d{2}^';

		preg_match($durationPattern, $line, $durationMatches);
		$this->currCall->setDuration($durationMatches[0]);
		$line = str_replace($durationMatches[0], '', $line);
		$headerNotes = trim($line);
		$this->currCall->setHeaderNotes($headerNotes);
	}

	protected function parseAudio($lineText) {
		 $audio = str_replace( "[",'', $lineText);
		 $audio = str_replace("]",'', $audio);
		 $audio = str_replace($origAudioLink, $finalAudioLink, $audio);
		 $shortAudio = substr($audio,52);
		 $longAudio = substr($audio,12);
		 $this->currCall->setAudio($shortAudio);
		 $this->currCall->setLongAudio($longAudio);
	}
	public function isJunk($line) {
		$lineText = trim($line->getLineText());
//		echo "line text: " . substr($lineText, 0, 17) . "<BR>";
		if (strpos($lineText, "User:") && strpos($lineText, "Christian E. Tovar")) {
			return true;
		} elseif (substr($lineText, 0,18)=='Minimization Event') {
//			echo "junk: $lineText <BR>";
			return true;
		} elseif (substr($lineText, 0, 11) =='Minimize On') {
//			echo "junk: $lineText <BR>";
			return true;
			//                                                 Linesheet - Minimization Details
		} elseif (substr($lineText, 0, 32) =='Linesheet - Minimization Details') {
 			return true;
		} elseif (trim(substr($lineText,1))=='None') {
 			return true;
 		//Case: Monroe Houses       Target: 202-241-0206         Line:   202-241-0206      File Number:
		} elseif (strpos(" " . $lineText, 'Case:') && strpos($lineText, 'Target:') && strpos($lineText, 'Line:') && strpos($lineText, 'File Number:')) {
			return true;
		}
	}
	public function isNewPage($line) {
		$lineText = trim($line->getLineText());
		$newpagePattern = '^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2}\s+EDT\s^';//\d+^';
		preg_match($newpagePattern, $lineText, $matches);
		
	//	echo $newPage . "<BR>";
		if ($matches) {
			$newPage = $matches[0];
//			echo "this is a new page: $newPage<BR>";
//			get the current page num:
			$currPageNumPattern = '^\d+\sof^';
			preg_match($currPageNumPattern, $lineText, $matches);
			$ofLoc = strpos($matches[0], 'of');
			$currPageNum = substr($matches[0], 0,$ofLoc);
			$this->currPage = $currPageNum;
	//		echo "curr page num: $currPageNum <BR>";
			return true;
		} else {
			return false;
		}
		//check to see if it is 
		//08/21/2014 19:57:20 EDT                                      None                                    12 of 4822
	}
	public function parsePDFFile() {
		$this->fileArray  = file($this->file);

		$fileLength = count($this->fileArray);
		echo "file length is: $fileLength <BR>";
		$lineIs = '';
		for ($i=0; $i <$fileLength; $i++) {
//		for ($i=0; $i <200; $i++) {
			$line = new GLSLine(trim($this->fileArray[$i]));
			if (!(trim($line->getLineText())=='')) {
				$this->line = $line;
				$lineText = $line->getLineText();
				if (substr(trim($lineText), 0,8)=="Session:") {
					if ($this->currCall) {
						$this->calls[] = $this->currCall; 
			//			echo "<BR>";
					}
					$this->currCall = new GLSPDFCall($this->currPageNum);
					$this->currCall->setFirstLine($line);
					$lineIs='second';
					continue;
				} elseif ($this->isJunk($line)){
					continue;
				} elseif ($this->isNewPage($line)) {
					continue;
				} elseif ($lineIs == 'second') {
					$this->currCall->setSecondLine($line);
					$lineIs='third';
					continue;
				} elseif ($lineIs == 'third') {
					$this->currCall->setThirdLine($line);
					$lineIs='checkForIAP';
					continue;
				}elseif ($lineIs == 'checkForIAP') {
					if ($this->currCall->isIAP($line)) {
						$this->currCall->setFourthLine($line);
						$lineIs='checkForSynopsis';
					} else { //more participants so set them
						$this->currCall->setParticipants($this->currCall->getParticipants() . "\n" . $line->getLineText());
					}

				} elseif ($lineIs == 'checkForSynopsis') {
					if ($this->currCall->isSynopsis($line)) {
						$lineIs='synopsis';
				//		echo "comments: <BR>";
						continue;
					} else {
						if (strtotime($line->getLineText()) || $line->getLineText() == 'END') {
					//		print_r($this->currCall);
						} else {
						 	$this->currCall->setParticipants($this->currCall->getParticipants() . "\n" . $line->getLineText());
						}	
						continue;
					}
				}elseif ($lineIs =='synopsis') {
					$this->currCall->setSynopsisLine($line);
				}

			}
		}
		$this->calls[] = $this->currCall; // put the remaining call in
	}
	public function parseHTMLFile() {
		$this->fileArray  = file($this->file);

		$fileLength = count($this->fileArray);
		$lineIs = '';
		for ($i=0; $i <$fileLength; $i++) {
			$line = new GLSLine(trim($this->fileArray[$i]));
			if (!(trim($line->getLineText())=='')) {
				$this->line = $line;
				$lineText = $line->getLineText();
				if (substr(trim($lineText), 0,7)=="Target:") {
					if ($this->currCall) {
						$this->calls[] = $this->currCall; 
					}
					$this->currCall = new GLSCall();
					$this->parseFirstLine();
					$lineIs='second';
					continue;

				}
				if ($lineIs == 'second') {
					$this->parseSecondLine();
					$lineIs='';
					continue;
				}
				switch (trim($line->getLineText())) {
					case "Target":
						$lineIs = "Target";
						break;
					case "Direction:":
						$lineIs = "Direction";
						break;
					case "Reference Number:":
						$lineIs = "Reference";
						break;
					case "Associate Number:":
						$lineIs = "Associate Number";
						break;
					case "IAP:":
						$lineIs = "IAP";
						break;
					case "First Tower:":
						$lineIs = "FirstTower";
						break;
					case "Last Tower:":
						$lineIs = "LastTower";
						break;
					case "Classification:":
						$lineIs = "Classification";
						break;
					case "Call Progress:":
						$lineIs = "CallProgress";
						break;
					case "Language:":
						$lineIs = "Language";
						break;
					case "Monitor:":
						$lineIs = "Monitor";
						break;
					case "Minimizations:":
						$lineIs = "Minimization";
						break;
					case "Minimized Time:":
						$lineIs = "MinimizedTime";
						break;
					case "Text Message:":
						$lineIs = "TextMessage";
						break;
					case "Comments:":
						$lineIs = "Comment";
						break;
					case "Synopsis 1:":
						$lineIs = "Synopsis1";
						break;
					case "Synopsis 2:":
						$lineIs= "Synopsis2";
						break;
					case "Audio";
						$lineIs = "Audio";
						break;
					case "Transcript";
						$lineIs = "Transcript";
						break;
					default:
						$lineText = $line->getLineText();
		 				switch ($lineIs) {
		 					case "Reference": 
		 						$this->currCall->setReference($lineText);
		 						break;
		 					case "Direction":
		 						$this->currCall->setDirection($lineText);
		 						break;
		 					case "Associate Number":
		 						$this->currCall->setAssociateNumber($lineText);
		 						break;
		 					case "IAP":
		 						$this->currCall->setIAP($lineText);
		 						break;
		 					case "FirstTower":
		 						$this->currCall->setFirstTower($lineText);
		 						break;
		 					case "LastTower":
		 						$this->currCall->setLastTower($lineText);
		 						break;
		 					case "Classification":
		 						$this->currCall->setClassification($lineText);
		 						break;
		 					case "CallProgress":
		 						$this->currCall->setCallProgress($lineText);
		 						break;
		 					case "Language":
		 						$this->currCall->setLanguage($lineText);
		 						break;
		 					case "Monitor":
		 						$this->currCall->setMonitor($lineText);
		 						break;
		 					case "Minimization":
		 						$this->currCall->setMinimization($lineText);
		 						break;
		 					case "MinimizedTime":
		 						$this->currCall->setMinimizedTime($lineText);
		 						break;
		 					case "TextMessage":
		 						$this->currCall->setTextMessage($lineText);
		 						break;
		 					case "Comment":
		 						$this->currCall->setComment($lineText);
		 						break;
		 					case "Synopsis1":
		 						//echo "setting the synopsis<BR>";
		 						$this->currCall->setSynopsis1($this->currCall->getSynopsis1() . " " . $lineText);
		 						break;
		 					case "Synopsis2":
		 						$this->currCall->setSynopsis2($this->currCall->getSynopsis2() . " " . $lineText);
		 						break;		 						 
		 					case "Audio";
		 						$audio = str_replace( "[",'', $lineText);
		 						$audio = str_replace("]",'', $audio);
		 						$shortAudio = str_replace($this->origAudioLink, '', $audio);
		 						$longAudio = str_replace($this->origAudioLink, $this->finalAudioLink, $audio);
		 					//	echo "shortAudio: $shortAudio <BR>";
		 				//		echo "longAudio: $longAudio <BR>";
		 						$this->currCall->setAudio($shortAudio);
		 						$this->currCall->setLongAudio($longAudio);
		 						break;
		 					case "Transcript";
		 						$transcript = str_replace("]", '', $lineText);
		 	
		 						$transcriptstart = $this->strnposr($transcript, "/",3);
		 						$transcript = substr($transcript, $transcriptstart);
		 						$transcript = str_replace("/AllTargets2012GangYacoub34-12Transcript", 
		 							"AllTargets/2012/Gang/Yacoub/34-12/Transcript/0", $transcript);
		 						$transcriptfriendly = substr($transcript, strrpos($transcript, "/")+1);
		 						$this->currCall->setTranscript($transcript);
		 						$this->currCall->setTranscriptFriendly($transcriptfriendly);
		 						break; 
		 					default: 
			 					$audioTest = substr(trim($lineText), 0, 5);
		 						if ($audioTest=="Audio") {
		 							$this->parseAudio(substr($lineText, 5));
		 						} else {
			 						echo "missing line: " . $lineText . "<BR>";

		 						}
		 						break;
		 				}

						break;
				}
			}// end for
			//print_r($this->currCall);
		}
		$this->calls[] = $this->currCall; // put the remaining call in

	}
	protected function writeOutput() {
		$outputLine = '"Audio";"Session";"Date";"Classification";"Duration";"Monitored By";"Start Time";"Complete";"Direction";"Out Digits";'.
		'"Associate Number";"Participants";"IAPSystemID";"Location";"Synopsis"'."\r\n";
	//	echo $outputLine . "<BR>";
		return $outputLine;
	}
	public function outputCalls() {
		$calls = $this->calls;
		$fileHandle = fopen($this->outputFile,"a");
		$outputLine = $this->writeOutput();
		//echo $outputLine . "<BR>";
		fwrite ($fileHandle,$outputLine);
		foreach($calls as $call) {
/*=HYPERLINK("MiguelCoronado\202-241-0206 2014-05-20 20-13-52 00002-001.wav","202-241-0206 2014-05-20 20-13-52 00002-001.wav")
=HYPERLINK("MiguelCoronado\202-241-0206 2014-05-20 20-13-52 00002-001.wav","202-241-0206 2014-05-20 20-13-52 00002-001.wav")
 */
//  			$pdfHyperlink = '"=HYPERLINK(""MiguelCoronado202-241-0206.pdf"")";"';
	 		$audioHyperLink = '"=HYPERLINK(""'.'MikeThomas/'.$call->getAudioFilename().'"",""'.$call->getShortAudioFilename().'"")";"';
 //				$audioHyperLink = '"=HYPERLINK(""MiguelCoronado/00002_AUDIO.wav"")";"';
 				$textHyperLink = '";"';
 				$convertedFileLink = '";"';
			 $outputLine = 			 $audioHyperLink 
				/*. $transcriptHyperLink 
				. */ 
				. $call->getSession() . '";"'
				. $call->getDate() . '";"'
				. $call->getClassification() . '";"'
				. $call->getDurationMonitored() . '";"'
				. $call->getMonitorId() . '";"'
				. $call->getStartTime() . '";"'
				. $call->getComplete() . '";"'
				. $call->getDirection() . '";"'
				. $call->getInOutDigits() . '";"'
				. $call->getAssociateDN() . '";"'
				. $call->getParticipants() . '";"'
				. $call->getIAPSystemID() . '";"'
				. $call->getLocation() . '";"'
				. str_replace('"', '""', $call->getSynopsis1()) . '"'

		//		. $call->getHeaderNotes() . '";"'
/*				. $call->getReference() . '";"'
				. $call->getCallProgress() . '";"'
				. $call->getAssociateNumber() . '";"'
				. $call->getIAP() . '";"'
				. $call->getMonitor() . '";"'
				. $call->getFirstTower() . '";"'				
				. $call->getLastTower() . '";"'
				. $call->getMinimization() . '";"'
				. $call->getMinimizedTime() . '";"'
				. $call->getSynopsis2() . '"'
*/				."\r\n";

			fwrite ($fileHandle,$outputLine);
		}
	}
	public function loopThroughFiles(){
		//first loop through the pdfs
		echo "dir handle". $this->dirHandle . "<BR>";
		$pdfDir = opendir($this->dirHandle);
	   	while(false != ($file = readdir($pdfDir))){
	   		echo "filename: $file <BR>";

			if (preg_match('/.pdf/',$file, $matches)) {
				transformPDFToText($file);
		
		/*		$document = new Document();
				$document->setTitle($file);
				$linesheet = new Linesheet();
				$this->file = $file;
				$this->parseFile();
		*/
			} // end if 

	   	} // end while
	}
}

?>