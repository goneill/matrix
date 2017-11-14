<?php 
// import sprint cellsite data

foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     
// per case variables 
$caseID = 2;
$inDirectory = "../input/verizonPhoneTowers/";

//******* FUNCTIONS ****//
function addRecords($filename) {
	global $link;
	global $caseID;
	echo "begging the add records function<BR>";
	echo "$filename <BR>";
	$verizonTowerFilename = substr($filename, strrpos($filename, "/")+1);
	$verizonTowerFilename = substr($verizonTowerFilename, 0, strrpos($verizonTowerFilename, "."));
	echo "VerizonTowerFilename : $verizonTowerFilename";

	
	// check to see if this file has already been entered into the database
	
	$query = "Select VerizonTowerFileID from VerizonTowerFiles WHERE FileName = '$verizonTowerFilename' and CaseID = ". $GLOBALS['caseID'];
	$result = $link->query($query);
	if(mysqli_num_rows($result) > 0){
	    echo "$verizonTowerFilename already in the database <BR>";
	    return;
	} else {
		echo "not in there yet: $verizonTowerFilename <BR>";
		$query = "INSERT INTO VerizonTowerFiles (FileName, CaseID) VALUES ('$verizonTowerFilename', $caseID)";
		if (! mysqli_query($link,$query)) {
			echo "query: $query <BR>";
			echo "MySQL Error: " .$link->error ; 
			die();
		} else {
			$sourceID = $link->insert_id;
		}
	}
	// need to get the tower file id

	if (($handle = fopen($filename, "r")) !== FALSE) {
		fgets($handle);
		//put each line of the file in the database
		$i = 0;
	//	echo "in the loop able to open the files <BR>";
		while (($line = fgetcsv($handle)) !== FALSE) {

			if ($i==0) {
				//continue;
			}
			if ($line[0]=="# EOF") {
				return;
			}
			if ($line[0]=='' && $line[1]=='' && $line[2]=='') {
				continue;
			}
			$query = "INSERT INTO VerizonTowers (
				VerizonTowerFileID,
				CaseID,
				MarketSID,
				SwitchNumber,
				SwitchName,
				CellNumber,
				Latitude,
				Longitude,
				StreetAddress,
				City,
				State,
				ZIP,
				Sector,
				Technology,
				Azimuth,
				SourceId,
				Created,
				Modified
			) VALUES (
				$sourceID,
				$caseID,
				'$line[0]',
				'$line[1]',
				'$line[2]',
				'$line[3]',
				$line[4],
				$line[5], 
				'".mysqli_real_escape_string($link, $line[6])."',
				'".mysqli_real_escape_string($link, $line[7])."',
				'$line[8]',
				'$line[9]',
				'$line[10]',
				'$line[11]',
				'$line[12]',
				$sourceID,
				now(),
				now()
			)";
			if (! mysqli_query($link,$query)) {
				echo "query: $query <BR>";
				echo "MySQL Error: " .$link->error ; 
				die();
			}
			$i++;
		}

	}

	$link->close();

}




$di = new RecursiveDirectoryIterator($inDirectory, FilesystemIterator::SKIP_DOTS);

// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
	echo"beginning for the first file: $filename <BR>";
	
	$basename = basename($filename);
	if (strpos($filename, '.csv')) {
		addRecords($filename);
		echo "did it for $filename <BR>";	
	} else {
		echo "skipping the file: $filename <BR>";
	}

	// need to check for duplicates!!


}


?>