<?php
/* 	Header for Tagliaferro Discovery parser
* 	This project is designed to turn all various files for discovery so that it can be put into a spreadsheet.  
*/
echo "test working";


	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	error_reporting(E_ALL);
	date_default_timezone_set('America/New_York');
	set_time_limit(0);
	ini_set("memory_limit","2400M");
	echo "included the files<BR>";
	$outputFile = "knights.csv";
	$fileHandle = fopen($outputFile,"a");
	

	//script to transform all the pdfs to text
	$mainDiretory = '/Library/Webserver/Documents/_tagliaferro/';
	$discoveryDirectory = "/Volumes/Mance/cases/Knights,_Ryan/RKDiscovery/RKDiscovery20131125";
	$objects =  new RecursiveIteratorIterator(new RecursiveDirectoryIterator($discoveryDirectory));
	$outputLine= "Bates;Location;DocumentTitle;Type;Size"."\r\n";
	fwrite($fileHandle, $outputLine);
	foreach($objects as $filename => $object) {
		if (!($object->isDir()) and ($object->getFilename() != '.DS_Store')) {
	//		echo "$filename <BR>";
			$path = $object->getPath();
//			echo "path: $path <BR>";
			$name = $object->getFilename();
//			echo "name: " . $name . "<BR>";
			$originalName = $path ."/". $name;
//			echo "original name: $originalName <BR>";
			$modifiedName = str_replace(" ", "_", $originalName);
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
			$outputLine = '"";"=HYPERLINK(""'. $filename . '"", ""'. substr($filename,68).'"")";'.str_replace( "_", " ",trim($name)) . ';"'.$type.'";"'.$filesize.'"'."\r\n";
		//	echo $outputLine . "<BR>";
			fwrite ($fileHandle,$outputLine);
			

		}
		//echo $object->getSubPath() . "<BR>"
//		echo $filename->getSubPath() . "<BR>";
	}
?>
