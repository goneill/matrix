<?php
/* 	Header for Terrell Pinkney
*/
foreach(glob('../library/*.php') as $file) {
     include_once $file;
} 
//this should be all you change, except if you have modifications to the isIncluded function
$maindirectory = "/Volumes/Mance/Dropbox/cases/Harris, Cory/CH Discovery/";

$batesRegExp = '/\b((US)|(CH))(_|\s)[0-9]{4,}\b/';
$prohibitedArray = ["inf","ico", "qm", "dll", "DS_Store", "","ini","dav","cab","BUP","IFO","db","cnt","eml","zip","clog","xml","ufdx","PBB","SMS","ufd"];
$outname = 'CH Digest.csv';
$folderName = "CH Discovery ";
//$bopNum = '75182053';
// if the file exists it overwrites it and starts the pointer at the beginning.  If it doesn't exist it creates it.  
//Make sure to set permissions in the folder in order to allow the script to create the file.  
try {
	$outputFile = fopen($outname, "w");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}

$di = new RecursiveDirectoryIterator($maindirectory, FilesystemIterator::SKIP_DOTS);



function getPhoneNum($basename) {
	$phoneNum = substr($basename, 33, 10);
	return $phoneNum;
}
// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
	$type = $file->getExtension();



	// can be modified as needed above.  
	if (isIncluded($type, $prohibitedArray, $filename)) {
		$basename = basename($filename);
		$relativeName = str_replace($maindirectory,"",$filename);
		$dateProvided = str_replace($folderName, "", substr($relativeName, 0,strpos($relativeName, '/')));
		$dateProvided = substr($dateProvided, 0,4). '-' . substr($dateProvided, 4,2) . '-' . substr($dateProvided, 6,2);

		$escapedName = str_replace(" ","%20", $relativeName);
		$escapedName = str_replace("#","%23", $escapedName);
		$batesString = '';
		if ($type == 'pdf') {
			$batesString = getBatesStringPdf($filename, $batesRegExp);
	};
/*		if (!$batesString) {
			$batesString = getBatesStringFilename($basename, $batesRegExp);
		} 
		get
*/		
	//	$callDate = getJailDate($basename);
	//	$phoneNum = getPhoneNum($basename);
		$hyperlink = '=HYPERLINK("'.$escapedName.'","'.$basename.'")';

	    $outputLine = "$batesString|$hyperlink|$dateProvided|$type|||||\n";
//	    echo "$outputLine <BR>";
	 // 	echo $outputLine ."<BR>";
		fwrite($outputFile,$outputLine);

	}
}
echo "finished. <BR>";
?>