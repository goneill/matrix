<?php
/* 	Linesheet parser index - uses the input from the input/linesheet; 
*	Variables are set here.  
* 	Parses a pdf/html linesheet into a .csv file for use in a spreadsheet
*/
echo "test working <BR>";


ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');
set_time_limit(0);
ini_set("memory_limit","2400M");
echo "included the files<BR>";
$inputDirectory =  "../input/linesheets/";

// global library files
$dirHandle = opendir('../library'); 
while($file = readdir($dirHandle)){
	if (preg_match('/.php/',$file, $matches)) {
	//	Include '../library/'.$file; // excluding for now because of mysql error
	}
}
Include '../library/vendor/autoload.php';

// linesheet specific include files
$dirHandle = opendir('Includes'); 
while($file = readdir($dirHandle)){
	if (preg_match('/.php/',$file, $matches)) {
		Include 'Includes/'.$file;
		
	}
}

//script to transform all the pdfs to text
$maindiretory = '/';
$htmlDirectory = 'html/';
$pdfDirectory = 'pdf/';
$txtDirectory = 'txt/';

$util = new GLSParseUtil($pdfDirectory, $txtDirectory);
$util->loopThroughFiles();


	/*. WE still need to add in the calls component of this - the link to calls - doesn't work in mac anymore but can work in windows*/
//	$discoveryDirectory = "/Volumes/Mance/cases/Knights,_Ryan/RKDiscovery/RKDiscovery20131125";
/*
	$objects =  new RecursiveIteratorIterator(new RecursiveDirectoryIterator($inputDirectory));
//	$outputLine= "Bates;Location;DocumentTitle;Type;Size"."\r\n";
	//fwrite($fileHandle, $outputLine);
	foreach($objects as $filename => $object) {
		if (!($object->isDir()) and ($object->getFilename() != '.DS_Store')) {
			echo "$filename <BR>";
			$path = $object->getPath();
			$name = $object->getFilename();
			$originalName = $path ."/". $name;
			$modifiedName = str_replace(" ", "_", $originalName);
			$outputFile = str_replace(".pdf", ".txt", $filename);
			$fileHandle = fopen($outputFile, "a");
			if (strpos($modifiedName, " ")) {
				echo "still has a space!  $modifiedName <BR>";
			}
			if ($originalName != $modifiedName) {
				$newPath = str_replace(" ", "_", $path); 
				if (is_dir($newPath)) {
					// no changes to directory
				} else {
					echo "need to mkdir: $path <BR>";
					mkdir($newPath, 0777, true);
				}
			//	echo "originalName: |$originalName|<BR>  modified name: |$modifiedName| <BR><BR>";
				rename ($originalName, $modifiedName);
			}
			$type= $object->getExtension();
			$filesize = $object->getSize();
			fwrite ($fileHandle,$outputLine);
			$linesheetParser = new LinesheetParseUtils() 

		}
		//echo $object->getSubPath() . "<BR>"
//		echo $filename->getSubPath() . "<BR>";
	}
	*/
?>
