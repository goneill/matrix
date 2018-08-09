 <?php 
//notes on what to do - separate dates and time.  delete header notes; make sure audio and transcript are there when they should be
// 2 different wiretaps in 1 case: US v. Blackledge; Gillard is another defendant.  

Class GLSParseUtil {
	protected $htmlDir = "../html";
	protected $pdfDir = "pdf/";
	protected $txtDir = "txt/";
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
	protected $targetProvider = false;
	protected $user = false;
	protected $linesheetDateRange = false;

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
	protected function setCurrPageNum($pageNum) {
		$this->currPageNum = $pageNum;
	}
	protected function getCurrPageNum() {
		return $this->currPageNum;
	}
	protected function setTargetPhone($targetPhone) {
		$this->targetPhone = $targetPhone;
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
		// date | content | Associate DN

		$line = trim($this->line->getLineText());
		$parts = preg_split("/[\s]+/", $line);
		$datePattern = '^\d{1,2}\/\d{1,2}\/\d{2,4}\s+\d{2}:\d{2}:\d{2}^';//\s+[[AP]M]?^';//:d{2}:d{2}{+}^';//[ AM| PM]'
			$durationPattern = '^\d{2}:\d{2}:\d{2}^';
		// 		 7/3/2012 15:01:49 00:00:11 incoming call with 

		preg_match($datePattern,$line, $matches);
		$space = strpos($matches[0], ' ');
		$this->currCall->setDate(substr($matches[0],0,$space));
		//$this->currCall->setTime(substr($matches[0],$space));
			$line = str_replace($matches[0], '', $line);	
	

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
		} elseif(substr($lineText, 0, 9) =="Linesheet") {

 			return true;
		} elseif (strpos($lineText, "User:") && strpos($lineText, "Mikhail Vinopol")) {
			return true;
		} elseif (strpos($lineText, "Criteria") && strpos($lineText, "Report")) {
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
			$ofLoc = strpos($newPage, 'of');
			$currPageNum = substr($newPage, 0,$ofLoc);
			$this->currPageNum = $currPageNum;
	//		echo "curr page num: $currPageNum <BR>";
			return true;
		} else {
			return false;
		}
		//check to see if it is 
		//08/21/2014 19:57:20 EDT                                      None                                    12 of 4822
	}
	public function parseCALEAFile (){
		echo "its a CALEA file<BR>";
		$this->fileArray  = file($this->file);

		$fileLength = count($this->fileArray);
		$lineIs = '';
//		for ($i=0; $i <$fileLength; $i++) {
		for ($i=0; $i <200; $i++) {
			$line = new GLSLine(trim($this->fileArray[$i]));
		}

	}
	public function getOccurrence($haystack, $needles) {
		$occurrences = array();
	//	print_r($needles);
	//	echo "<BR>line: $haystack <BR>";
       foreach ($needles as $str) {
            $pos = strpos($haystack, $str);
           // echo "pos: $pos: $haystack | $str<BR>";

	        if ($pos !== FALSE) {  // make an array of the occurrences
	   			$occurrences[$str] = $pos;
	   		//	echo "it's a match: $str | $pos";
	        }
	    }
   		asort($occurrences);

        return $occurrences;
	}
	// check to see if this is a page header or footer - set page num if it is
	public function pageHeaderFooter($line) {
		$pageNumFoot = "/[0-9]+[\s]+of[\s]+[0-9]+/";
		$dateFoot = "/[0-9]{2}\/[0-9]{2}\/[0-9]{4}[\s]+[0-9]{2}:[0-9]{2}:[0-9]{2}[\s]+[A-Z]{3}/";
		if ((strpos($line, "Linesheet")!==FALSE &strpos($line, "User:")!==FALSE)){
			return true;
		} elseif($targLoc = strpos($line,"Target:")) {
			if ($this->targetPhone==''){
				$lineLoc = strpos($line, "Line:");
				$fileNumLoc = strpos($line,"File Number");
				$target = substr($line, $targLoc+7,$lineLoc-$targLoc-7);
				$targetPhone= trim(substr($line,$lineLoc+5,$fileNumLoc-$lineLoc-5));
				$this->setTargetPhone($targetPhone);

			}
			return true;
		} elseif (preg_match($pageNumFoot, $line, $matches)){
			$pageNum = substr($line, 0,strpos($line,"of"));
//			echo "pageLine: $line $pageNum <BR>";
			//echo "pageNum: $pageNum<BR>";
			$this->setCurrPageNum($pageNum+1);
			return true;
		} elseif (preg_match($dateFoot,$line,$matches)) {
			return true;
		} else {
		//	echo "line: $line<BR>";
			return false;
		}
	}
	
	public function parseHeader($line,$header) {
		$headerArray = $GLOBALS['headerArray'];
		$occurrences = $this->getOccurrence($line, $headerArray);
		if (!$occurrences) {
				// this sets the line with no occurrences to the last element set... this probably isn't right for all cases
				$endVal = end($header)." " .trim($line);
				$endKey = key($header);
				$header[$endKey] = $endVal;
		}
		foreach($occurrences as $occurrence=>$position) {
			$keyLength = strlen($occurrence);
			$nextStart = next($occurrences);
			if($nextStart) {
				$keyEnd = $nextStart - $position - $keyLength;
				//	prev($occurrences);
				$value = trim(substr($line, $position+$keyLength,$keyEnd));
			} else {
				$value = trim(substr($line, $position+$keyLength));
			}
	//		echo "$occurrence | $value <BR>";
			$header[$occurrence]=$value;
		}
	//	echo "line: $line<BR>";
		return $header;
	}

		// changing this to account for the case where the line is Synopsis plus other stuff...
	public function isBody($lineText) {
	
		if ($lineText == 'Synopsis' || $lineText == "Content (SMS - Pager)") {
			return true;
		} else {
			return false;
		}
	}


	public function parsePDFFile() {
		$this->fileArray= file($this->file);
		$fileLength = count($this->fileArray);
		$stage = '';
		$this->calls = array();
		$this->currCall = array();
		for ($i=0; $i <$fileLength; $i++) {
//		for ($i=0; $i <800; $i++) {
			$line = new GLSLine(trim($this->fileArray[$i]));
			if ( strpos($line->getLineText(), "C.A.L.E.A. Surveillance Intercept Reportx") !== FALSE) {
				parseCALEAFile();
				break; //check this 
			}
			if (!(trim($line->getLineText())=='')) { 			// skip empty lines and proceed with parsing
				$this->line = $line;
				$lineText = $line->getLineText();
				// they always start with session this means we have new call!
				if ($this->pageHeaderFooter($lineText)) {
					continue;
				} elseif (substr(trim($lineText), 0,8)=="Session:") {
			//		echo trim($lineText) . "<BR>";
					// if this is not the first call in the file
					if ($this->currCall) {  // if there is a call then add it into the calls array
						$this->currCall->setHeader($header);
						$this->calls[] = $this->currCall; 
					}
					$header = array();
					$this->currCall = new GLSPDFCall($this->getCurrPageNum());
					$this->parseHeader($lineText, $header);
					$stage = 'header';
				}
				if ($stage == 'header') {
					// if it is the body and isn't the header anymore
					if ($this->isBody($lineText)) {
						$stage = 'body'; 
					} else {
						$header = $this->parseHeader($lineText, $header);
					}
				} elseif ($stage == 'body') {
					
					$this->currCall->setSynopsisLine($line);

				}
			}
		}

	}
	/* this is commented out so that we can try to build a better one with less clauses that will auto parse the file rather than just relying on hard coding the headers.  
	public function parsePDFFile() {
		$this->fileArray  = file($this->file);

		$fileLength = count($this->fileArray);

//		echo "file length is: $fileLength <BR>";
		$lineIs = '';
		$this->calls = array();
		$this->currCall = array();
		for ($i=0; $i <$fileLength; $i++) {
//		for ($i=0; $i <200; $i++) {
			$line = new GLSLine(trim($this->fileArray[$i]));
			if ( strpos($line->getLineText(), "C.A.L.E.A. Surveillance Intercept Reportx") !== FALSE) {
				parseCALEAFile();
				break; //check this 
			}

			if (!(trim($line->getLineText())=='')) {
		//		print_r($line);
				$this->line = $line;
				$lineText = $line->getLineText();
				// they always start with session!
				if (substr(trim($lineText), 0,8)=="Session:") {
					// if this is not the first call in the file
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
					$lineIs='fourth';
					continue;
				}elseif ($lineIs == 'fourth') {
					$this->currCall->setFourthLine($line);
					$lineIs='fifth';
					continue;
				} elseif ($lineIs == 'fifth') {
					$this->currCall->setFifthLine($line);
					$lineIs='sixth';
					continue;

				} elseif($lineIs == 'sixth') {
					$this->currCall->setSixthLine($line);
					$lineIs='seventh';
					continue;
				} elseif ($lineIs== 'seventh') {
					$this->currCall->setSeventhLine($line);
					$lineIs='checkForSynopsis';
					continue;
				} elseif ($lineIs == 'checkForIAP') {
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
				} elseif ($usrLoc = (strpos($line->getLineText(), "User:")!== FALSE)) {
					$this->user = trim (substr($line->getLineText(), $usrLoc+6));
//					echo "user line: " . $this->user . "<BR>";
				} elseif ($lineLoc = (strpos($line->getLineText(), "Line:")=== 0)) {
					$this->targetPhone = trim (substr($line->getLineText(), $lineLoc+5, 15));
//					echo "Phone number line:  line: " . $this->targetPhone . "<BR>";
					$this->targetProvider = trim(substr($line->getLineText(), 20));
//					echo "targetProvider =".$this->targetProvider . "<BR>";
				} elseif ( strpos($line->getLineText(), "'Date")!== FALSE) {
					$linesheetDate = substr($line->getLineText(), 6);
					$linesheetDate = str_replace(":", "", str_replace("/", "", $linesheetDate)); 
 					$this->linesheetDateRange = $linesheetDate;
// 					echo "sheet date: " . $this->linesheetDate. "<BR>";
					$this->user = trim (substr($line->getLineText(), $usrLoc+6));
//					echo "user line: " . $this->user . "<BR>";
				} else {

					//echo "this is a regular line: " . $line->getLineText() . "<BR>";
				//	die();
				}
			}
		}
		$this->calls[] = $this->currCall; // put the remaining call in
	}
	*/
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
		$outputLine='';
		foreach ($GLOBALS['headerArray'] as $heading) {
			$outputLine .= '"'.substr(trim($heading),0,-1).'",';
		}

		return substr($outputLine,0,-1).',"Body","PageNumber"'."\r\n";
	}
	public function replaceParticipants($input) {
		$participants = $GLOBALS['participantsArray'];
		//print_r($participants);
		$input = preg_replace('/#([0-9]+,*\s*&*\s*#*)+/', '', $input); // get rid of all of the #1-9
		$input = preg_replace('/TT:[0-9]+/','',$input); //get rid of the TT:[0-9]
		foreach($participants as $key =>$value) {
			$input = str_replace($key, $value, $input);
		}

		return $input;
	}
	public function outputCalls() {
		$calls = $this->calls;
		$fileHandle = fopen($this->outputFile,"w");
		$outputLine = $this->writeOutput();
		fwrite ($fileHandle,$outputLine);
		foreach($calls as $call) {

			$textHyperLink='","';
			$convertedFileLink='","';
			$outputLine = 			'"';
			// i worry if this is not set then it will error out
			foreach ($GLOBALS['headerArray'] as $heading) {
				$header = $call->getHeader(); 
				if (isset($header[$heading])) {
					$val=$header[$heading];
				//	echo "$heading | $val <BR>";
					if($heading=='Participants:'){
						$val = $this->replaceParticipants($val);
					}
					$outputLine.= $val . '","';
				} else {
					$outputLine.=' ","';
				}
			}
			$outputLine = $outputLine.  str_replace('"', "'",$call->getSynopsis1()) . '","'.$call->getStartPage().
			'"'."\r\n";
		//	echo $outputLine . "<BR>";
			fwrite($fileHandle,$outputLine);
		

		}
	}

	 function transformPDFToText($file){
	    $textfile = str_replace('pdf','txt',$file);
	   $execstring = "/usr/local/bin/pdftotext  -layout '" . $this->pdfDir.$file ."' '".$this->txtDir.$textfile."'";
//	    echo "executing: " . $execstring . "\n<BR>";
	   	system($execstring, $output);
  		return;
	}

	public function loopThroughFiles(){
		//first loop through the pdfs
//		echo "dir handle". $this->dirHandle . "<BR>";
		$pdfDir = opendir($this->dirHandle);
	   	while(false != ($file = readdir($pdfDir))){
//	   		echo "filename: $file <BR>";

			if (preg_match('/.pdf/',$file, $matches)) { // loop through the pdfs
				$this->transformPDFToText($file); // make them into txt files
//				echo "file: $file <BR>";
				$txtFileName = str_replace(".pdf", ".txt", $file);
				$txtFile = $this->txtDir. $txtFileName;
//				echo "txt File: " . $txtFile ."<BR>";
				$document = new Document();
				$document->setTitle($txtFile);
				$linesheet = new Linesheet();

				$this->file = $txtFile;
				$this->parsePDFFile();
				$this->calls;
				$firstCall = $this->calls[0];
				$firstHeader = $firstCall->getHeader();
				$firstDate = $firstHeader['Date:'];
				$firstDate = str_replace("/", "", $firstDate); 
				$lastCall = $this->calls[count($this->calls)-1];
				$lastHeader = $lastCall->getHeader();
				$lastDate = $lastHeader['Date:'];
				$lastDate = str_replace("/", "", $lastDate); 
				$this->linesheetDateRange = $firstDate."_".$lastDate;
//				echo "linesheet Date " . $this->linesheetDate . "<BR>";
				$this->outputFile = 'out/'.$this->targetPhone."_".$this->linesheetDateRange.".csv";
//				$this->outputFile = "out/".$file."_OUT.csv";
				echo "files: $file | $txtFile | " . $this->outputFile . "<BR>";
				
				$this->outputCalls();
			} // end if 

	   	} // end while
	}
}

?>