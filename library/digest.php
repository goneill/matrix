<?php 
// Digest Library
//only include those files which should be included.  Modify as needed
function isIncluded($type, $prohibitedArray) {

	if (in_array($type, $prohibitedArray)) {
		return false;
	} else {
		return true;
	}
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

function getBatesStringFoldername($fielname, $pattern) {

}
?>