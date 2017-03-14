<?php
/* 	THIS WORKS FOR MCC/MDC Calls - the filename format is 
	The Filename is BOP Num Date and num called
	11259052_May_31_2016_20_00_08_PM_7182195837
*/
foreach(glob('../library/*.php') as $file) {
     include_once $file;
} 
//this should be all you change, except if you have modifications to the isIncluded function
$maindirectory = "2016.05.12 - 2016.07.20 (MCC)";
$maindirectory = "2016.07.21 - 2016.09.20 (MCC)";
$maindirectory = "2015.04.16 - 2016.05.09";
// assuming this goes into the TP Work Product folder
$preface = "../TP Discovery/TP 20170125 Discovery/Prison Records/Johnson/US_007857 - Calls/";
$batesRegExp = '';'/\b((US)|(TP))(_|\s)[0-9]{4,}\b/';
$prohibitedArray = ["inf","ico", "qm", "dll", "DS_Store", "","ini","dav","cab","BUP","IFO","db"];
$outname = 'TP Johnson 20160512_20160720.csv';
$bopNum = '11259052';
// if the file exists it overwrites it and starts the pointer at the beginning.  If it doesn't exist it creates it.  
//Make sure to set permissions in the folder in order to allow the script to create the file.  
try {
	$outputFile = fopen($outname, "w");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}

$di = new RecursiveDirectoryIterator($maindirectory, FilesystemIterator::SKIP_DOTS);

/*function getJailDate($filename) {
	$bopNum = $GLOBALS['bopNum'];
	$num= str_replace('_', " ", str_replace($bopNum, '', $filename));
	echo "Jaild Date : $num <BR>";
	die();

} */
$outputLine =  "Date Time|Num Called|link(filename)|Who Called|Notes|Relevant\n";
fwrite($outputFile,$outputLine);
// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
	$type = $file->getExtension();

	if ($type == 'cdr') {
		$xml = simplexml_load_file($file);
 // 		print_r($xml);
 // 		die();

	// can be modified as needed above.  
		$basename = str_replace(".cdr", ".mp3",basename($filename));
		$relativeName = $preface.$filename;
//		$dateProvided = substr($relativeName, 0,strpos($relativeName, '/'));

		$escapedName = str_replace(" ","%20", $relativeName);
		$escapedName = str_replace("#","%23", $escapedName);
		$escapedName = str_replace(".cdr", ".mp3", $escapedName);
//		$batesString = getBatesStringPdf($filename, $batesRegExp);
//		echo "bates string: $batesString <BR>";
/*		if (!$batesString) {
			$batesString = getBatesStringFilename($basename, $batesRegExp);
		} 
		get
*/		
		$dateTime = date_create($xml->startTime);// getJailDate($basename);

 $dateTime = date_format($dateTime, 'm/d/Y H:i:s');
		$hyperlink = '=HYPERLINK("'.$escapedName.'","'.$basename.'")';
		$numCalled = $xml->originalDestinationId;

	    $outputLine = "$dateTime|$numCalled|$hyperlink|||||||\n";
	  //  echo "$outputLine <BR>";
//	    die();
	 // 	echo $outputLine ."<BR>";
		fwrite($outputFile,$outputLine);
	}
}
echo "finished. <BR>";
?>