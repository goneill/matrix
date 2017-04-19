<?php 
// Digest Library
//only include those files which should be included.  Modify as needed
function isIncluded($type, $prohibitedArray, $filename) {
	$phoneDumpTypeArray = Array ('pdf', 'html');
	if (in_array($type, $prohibitedArray)) {
		return false;
	} 
	if (strpos($filename, "Phone Dump")) {
		if (!in_array($type, $phoneDumpTypeArray)) {
			return false;
		}
	}
	return true;
}

function getJailDate($filename) {
	$bopNum = $GLOBALS['bopNum'];
	$mon = substr($filename, 9,3);
	$day = substr($filename, 13, 2);
	$year = substr($filename, 16,4);
	$hr= substr($filename, 21,2);
	$min = substr($filename, 24, 2);
	$sec = substr($filename, 27, 2);
	$date = "$mon $day, $year $hr:$min:$sec";
	return $date;
}

// extract the bates num from the the bates logfile from adobe acrobat
function getBatesStringPdf($filename, $pattern) {
	if (preg_match('/.pdf/',$filename, $matches)) {
		// this throws an error if the text file is already there...  make of it what you will
	   	$content = file_get_contents($filename);
		preg_match_all($pattern, $content, $matches);
		$numBatesPages = count($matches[0]);
		if ($numBatesPages == 0) {
			$batesString = '';
		} elseif ($numBatesPages==1) {
			$batesString = $matches[0][0];
		} else {
	//		$batesString = $matches[0][0] . ' - ' . end($matches[0]);
			$batesString = end($matches[0]) . ' - ' . $matches [0][0];
		}
		return $batesString;
	}
}

function getBatesStringFilename($basename, $pattern) {

	preg_match_all($pattern, $basename, $matches);
	$numBatesPages = count($matches[0]);
	if ($numBatesPages == 0) {
		$batesString = '';
	} elseif ($numBatesPages==1) {
		$batesString = $matches[0][0];
	} else {
		$batesString = end($matches[0]) . ' - ' . $matches [0][0];
//		$batesString = $matches[0][0] . ' - ' . end($matches[0]);
	}
	return $batesString;
}

function getBatesStringFoldername($filename, $pattern) {
	// want to edit this to get the most pattern
	preg_match_all($pattern, $filename, $matches);


	$numBatesMatches = count($matches[0]);
	if ($numBatesMatches == 0) {
		$batesString = '';
	} elseif ($numBatesMatches==1) {
		$batesString = $matches[0][0];
	} else {
		$batesString = end($matches[0]);

//		$batesString = $matches[0][0] . ' - ' . end($matches[0]);
	}
	return $batesString;

}
?>