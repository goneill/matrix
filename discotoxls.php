<?php
/* 	Header for Rodney Muschette
* 	This project is designed to turn all of the discovery into a spreadhseet for easier review.  
* 	Step 1. Identify the discovery
* 	Step 2. Transform the files into readable links
* 	Step 3.  Put in excel
* 	Step 4. Quality control
* 	One thing we want to do is to check to see automatically where ther eis a jpg next to a pdf and things like that
*/

	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	error_reporting(E_ALL);
	date_default_timezone_set('America/New_York');
	set_time_limit(0);
	ini_set("memory_limit","2400M");
	foreach(glob('Includes/*.php') as $file) {
	     include_once $file;
	}
	//this should be all you change, except if you have modifications to the isIncluded function
	$maindirectory = "/Volumes/Mance/Dropbox/cases/Belle, Wendell/WB Discovery/";
	$toTruncate = $maindirectory;
	$batesRegExp = '/\b[A-Z]{2}[_|\s][0-9]{6,}/';

	// if the file exists it overwrites it and starts the pointer at the beginning.  If it doesn't exist it creates it.  
	//Make sure to set permissions in the folder in order to allow the script to create the file.  
	try {
		$outputFile = fopen("output.csv", "w");
	} catch (Exception $e) {
		echo "Caught exception", $e->getMessage(), "<br>";
		die();
	}


	//only include those files which should be included.  
	// This will be modified depending on the data you receive from the G
	function isIncluded($filename, $type) {

		// an array of file types which we don't include.  
		$prohibited = ["cx3","config","inf","ico", "qm", "dll", "DS_Store", "","ini","dav","cab","BUP","IFO","db","txt","png","jpg","JPG","3gp","MOV","exe","g64","mov"];
		$whiteListedForConvertedFilesArray = ["pdf", "avi", "mp3", "wav"];
		$whereConvertedFilesArray = ["Phase II Production", "Tykeem Berry (271947)", "340 Alexander (271945)"];
		$convertedOnly = false;
		foreach ($whereConvertedFilesArray as $convertedExclude) {
			if (strpos($filename, $convertedExclude)) {
				echo "Filename: $filename <BR>";
				$convertedOnly = true;
			}
		}
		if (in_array($type, $prohibited)) {
			return false;
		} else {
			if (strpos($filename, "1.1-Phones")) {
				if (!strpos($filename,"Cellphone Report")) {
					return false;
				}
			} elseif (strpos($filename, 'RoxioBurn')) {
				return false;
			} elseif (strpos($filename, 'VIDEO_TS.mp4')) {
				return false;
			} elseif(strpos($filename, "1.2-Video")) {
				if (!strpos($filename,".mp4")) {
 				//	echo "filename: $filename <BR>";
 					return false;
 				}
 			} elseif($convertedOnly && !in_array($type,$whiteListedForConvertedFilesArray )  && !strpos($filename, "Converted File")) {
				return false;
 			}
			return true;
		}

	}

	// attempt to extract the bates num from the pdf itself
	function extractBatesNum($filename) {
		
		// Parse pdf file and build necessary objects.

		if (preg_match('/.pdf/',$filename, $matches)) {
				// this throws an error if the text file is already there...  make of it what you will
	           	$content = file_get_contents($filename);
				$pattern = $GLOBALS['batesRegExp'];//.$numBatesNum."}/";
				preg_match_all($pattern, $content, $matches);


				$numBatesPages = count($matches[0]);
				if ($numBatesPages == 0) {
					$batesString = '';
				} elseif ($numBatesPages==1) {
					$batesString = $matches[0][0];
				} else {
					$batesString = $matches[0][0] . ' - ' . end($matches[0]);
				}
				if ($batesString <> '') {
				}
				return $batesString;
    	}
	}

	$types = Array();
	$di = new RecursiveDirectoryIterator($maindirectory, FilesystemIterator::SKIP_DOTS);

	// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  

	foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
		$type = $file->getExtension();
		//echo "filename $filename file $file <BR>";
		// can be modified as needed above.  
		if (isIncluded($filename, $type)) {
			$basename = basename($filename);
			$relativeName = str_replace($toTruncate,"",$filename);
			$escapedName = str_replace(" ","%20", $relativeName);
			$escapedName = str_replace("#","%23", $escapedName);
			$escapedName = str_replace("&", "&&", $escapedName);

			$batesNum = extractBatesNum($filename); //getBatesNum($basename, $batesLogContents);

			$hyperlink = '=HYPERLINK("'.$escapedName.'","'.$relativeName.'")';
		
			if (!in_array ( $type , $types)) {
				$types[] = $type;
			}
		    $outputLine = "\"$batesNum\"|$hyperlink|$basename|$type|||||\n";

		 // 	echo $outputLine ."<BR>";
			fwrite($outputFile,$outputLine);
		}
	

	}
	?>