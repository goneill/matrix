<?php

class LinesheetParseUtils {
	//public static $isNew = 
	// this loops through the files in a directory and translates them 
	// all into text and puts them in another directory
	function translatePDFToText($main, $count=0){
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
	//checks to see if a given line is a header or footer (or whitespace)
	function isHeaderFooter($line) {
		$line = trim($line);
		if ($line == '') {
			return true;
		}
		if (strpos($line, "Case:")===0) {
			return true;
		}
		$date = strtotime(substr($line,0,18));
		if ($date) {
		    return true;
		} 
		return false;		
	}

	//check to see whether we are talking about a new call.  
	function isNew($line) {
		if ((strpos($line, "Session:")==0) && (strpos($line, "Date:")> 0) && (strpos($line, 'Classification:')>0) ) {
			return true;
		}
	}
	function setFirstLine($line, $curCall) {
		// patern for "Session num"
		$pattern = "/(\\s)*Session:(\\s)*[0-9]+/";
		preg_match($pattern, $line, $matches);
		$session = trim(substr($matches[0],8));
		$curCall['session'] = $session;
		// patern for the date
		$pattern = "/(\\s)*Date:(\\s)*(|(0[1-9])|(1[0-2]))\/((0[1-9])|(1\d)|(2\d)|(3[0-1]))\/((\d{4}))/";
		preg_match($pattern, $line, $matches);
		$date = trim(substr(trim($matches[0]), 5));
		$curCall['date'] = $date;
		// pattern for classification
		$pattern = "/(\\s)*Classification:(\\s)+[a-zA-Z-]+(\\s)+/";
		preg_match($pattern, $line, $matches);
		$classification = trim(substr(trim($matches[0]), 15));
		$curCall['classification'] = $classification;

		// pattern for duration
		$pattern = "/(\\s)*Duration:(\\s)+[0-9][0-9]:[0-9][0-9]:[0-9][0-9]/";
		preg_match($pattern, $line, $matches);
		$duration = trim(substr(trim($matches[0]), 9));
		$curCall['duration'] = $duration;
		
		return ($curCall);

	}
function setSecondLine($line, $curCall) {

	//patter for monitored by
	$pattern = "/(\\s)*Monitored By:(\\s)+[a-z ]*(\\s)+Start Time:/";
	preg_match($pattern, $line, $matches);
	$match = trim($matches[0]);
	$monitored = trim(substr($match, 13));
	$len = strlen($monitored);
	$monitored = trim(substr($monitored,0, $len - 11));
	$curCall['monitored'] = $monitored;

	//start time
	$pattern = "/(\\s)*Start Time:(\\s)+[0-9]{2}:[0-9]{2}:[0-9]{2}(\\s)+/";
	preg_match($pattern, $line, $matches);
	$match = trim($matches[0]);
	$start = trim(substr($match,11));
	$curCall['start'] = $start;

	//complete
	$pattern = "/(\\s)*Complete:(.)*Direction:/";
	preg_match($pattern, $line, $matches);
	$match = trim($matches[0]);
	$complete = trim(substr($match,11));
	$len = strlen($complete);
	$complete = trim(substr($complete, 0, $len-10));
	$curCall['complete'] = $complete;

	//direction
	$pattern = "/(\\s)*Direction:(.)*/";
	preg_match($pattern, $line, $matches);
	$match = trim($matches[0]);
	$direction = trim(substr($match,10));
	$curCall['direction'] = $direction;

	return $curCall;
}
function setThirdLine($line, $curCall) {
	$pattern = "/Digits:(\\s)*[0-9]+(\\s)+/";
	preg_match($pattern, $line, $matches);
	if ($matches) {
		$match = trim($matches[0]);
		$digit = trim(substr($match, 7));
		$curCall['digits'] = $digit;
	}
	$pattern = "/Participants:(\\s)+(.)*/";
	preg_match($pattern, $line, $matches);
	if ($matches) {
		$match = trim($matches[0]);
		$participants = trim(substr($match, 13));
		$curCall['participants'] = $participants;
	}
	return $curCall;
}

}