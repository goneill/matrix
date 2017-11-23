<?php 
// import sprint cellsite data
// per case variables 
$caseID = 2;
$inDirectory = "../input/sprintPhoneTowers/";

foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     

//******* FUNCTIONS ****//
function addRecords($filename) {
	global $link;
	global $caseID;

	if (($handle = fopen($filename, "r")) !== FALSE) {
		ini_set('auto_detect_line_endings',TRUE);
		echo "can read the file: $filename <BR>";
		fgets($handle);
		echo "handle: $handle <BR>";
		print_r($handle);

		//put each line of the file in the database
		while (($line = fgetcsv($handle)) !== FALSE) {



			$query = "INSERT INTO SprintTowers (
				CaseID,
				CellNum,
				CascadeID,
				Switch,
				NEID,
				Repoll,
				Latitude,
				Longitude,
				BTSManufacturer,
				Sector,
				Azimuth,
				CDRStatus,
				Created,
				Modified
			) VALUES (
			$caseID,
			$line[0] ,
			'$line[1]' ,
			'$line[2]',
			$line[3] ,
			$line[4] ,
			$line[5] , 
			$line[6] ,
			'$line[7]' ,
			$line[8],
			$line[9],
			'$line[10]',

			now(),
			now()
			)";
		
			mysqli_query($link,$query);
		}


	} else {
		echo "file wouldn't open: $filename <BR>";
	}

	//$link->close();
}




$di = new RecursiveDirectoryIterator($inDirectory, FilesystemIterator::SKIP_DOTS);

// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
	$basename = basename($filename);
	if (strpos($filename, '.csv')) {
		// need to check for duplicates!!
		addRecords($filename);
		echo "did it for $filename <BR>";
	} else {
		echo "skipped: $filename <BR>";
	}

}


?>