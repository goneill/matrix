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
$headerArray = array("Session:", "Total Duration:", "Direction:", "Date:", "Content:", "Associate DN:", "Start Time:", "Language:", "In/Out Digits:", "Test:","Stop Time:", "Complete:", "Subscriber:", "Monitor ID:", "Participants:","Classification:");
$participantsArray = array("JASMIN a/k/a MIN a/k/a MANNY TT: 3683"=>"Min",
"RAY [Formerly UM4482 / ses. 716]"=>"Ray",
"KENNY [SesTT]"=>"Kenny",
"BILLY ID'd in Session 291"=>"Billy",
"JOHN [ID'd in Session 523 by Target]"=>"John",
"CHOPPA (IDed in Session 4322) a/k/a UM9704"=>"Choppa",
"KERRY [ses. 1581, Formerly UM5507]"=>"Kerry",
"BUCC a/k/a BUCCI [Formerly UM4855, as per ses. 771, 772"=>"Bucci",
", FRANKIE"=>"Frankie",
"[Formerly UM7660] [TT] SDAMIAN"=>"Damian",
"FRANKIE [SesNEW NUMBER, TT]"=>"Frankie",
"ANT [id'ed SMS: 885]"=>"Ant",
"JOE [id'ed in ses. 1466, Formerly UM0206]"=>"Joe",
"UM0183 [FORMERLY UNKNOWN 0183] -"=>"UM0183",
"STEVE [3176]  Formerly UM0346"=>"Steve",
"MATT [Formerly UM0352, id'ed ses. 887, 2226]"=>"Matt",
"JACKIE [girlfriend/ formerly UF9144]"=>"Jackie (gf)",
"JASMIN a/k/a MIN a/k/a MANNY [ID Sessms 1590, TT"=>"Min",
"STEVE [3176]  Formerly UM0346"=>"Steve",
"# 2469 TT6189, MIKEY"=>"Mikey",
"MITTY (FORMERLY: UM5339)"=>"Mitty",
"[1798]  Formerly UM0346, STEVE"=>"Steve"
);
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
