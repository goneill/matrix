<?php
/* 	Header for Terrell Pinkney
*/
foreach(glob('../library/*.php') as $file) {
     include_once $file;
} 
//this should be all you change, except if you have modifications to the isIncluded function
$maindirectory = "/Volumes/Mance/Dropbox/cases/Pinkney, Terrell/TP Discovery/";

$batesRegExp = '/\b((US)|(TP))(_|\s)[0-9]{4,}\b/';
$prohibitedArray = ["inf","ico", "qm", "dll", "DS_Store", "","ini","dav","cab","BUP","IFO","db"];
$outname = 'TP_Digest.csv';
// if the file exists it overwrites it and starts the pointer at the beginning.  If it doesn't exist it creates it.  
//Make sure to set permissions in the folder in order to allow the script to create the file.  
try {
	$outputFile = fopen($outname, "w");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}

$di = new RecursiveDirectoryIterator($maindirectory, FilesystemIterator::SKIP_DOTS);

// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
	$type = $file->getExtension();



	// can be modified as needed above.  
	if (isIncluded($type, $prohibitedArray)) {
		$basename = basename($filename);
		$relativeName = str_replace($maindirectory,"",$filename);
		$dateProvided = substr($relativeName, 0,strpos($relativeName, '/'));

		$escapedName = str_replace(" ","%20", $relativeName);
		$escapedName = str_replace("#","%23", $escapedName);
		$batesString = getBatesStringPdf($filename, $batesRegExp);
//		echo "bates string: $batesString <BR>";
		if (!$batesString) {
			$batesString = getBatesStringFilename($basename, $batesRegExp);
		} 
		$hyperlink = '=HYPERLINK("'.$escapedName.'","'.$basename.'")';

	    $outputLine = "$batesString|$hyperlink|$dateProvided|$type|||||\n";
	    echo "$outputLine <BR>";
	 // 	echo $outputLine ."<BR>";
		fwrite($outputFile,$outputLine);

	}
}
echo "finished. <BR>";
?>