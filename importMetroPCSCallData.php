<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  

foreach(glob('library/*.php') as $file) {
     include_once $file;
}     

$inDirectory = "MetroPCSPhoneRecords/";
$caseID = 1;
$serviceProviderId = getServiceProviderID("MetroPCS"); 


function addRecords($filename) {
	global $link;
	if (!$link) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}
	$i=1; 
	// big concern we could have duplicates in here.  
	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";
//		fgets($handle);
		//put each line of the file in the database
	    while (($line = fgetcsv($handle)) !== FALSE) {
			if (strpos($line[0], "Search Number: ")) {
				preg_match ('|[0-9]+|',stripPhoneNumber($line[0]), $matches);
				$source = $matches[0];
				echo "source name = $source <BR>";
				continue;
			}
			if (preg_match('/Date/',$line[0])) {
				echo "header row<BR>";
				continue;
			} 
			$dt = new datetime($line[0] . ' ' . $line[1]);
			$startDate = getSqlDate($dt);
			$duration = $line[2];
			$durationParts = explode(':',$duration);
			$durationInterval = new DateInterval("PT$durationParts[0]M$durationParts[1]S");
			$endDate = getSqlDate($dt->add($durationInterval));
			$dialedDigits = $line[4];
			//get the phone numbers
			if ($line[8]) {
				$callFromNum = $line[8];
			} else {
				$callFromNum = $source;
			}
			$callToNum = $line[5];
	
			// here if possible then calc the latitude and longitude.  We can't do that because we don't have the keys yet
			if (FALSE) {
				$startCellNum = substr($line[9], 1);
				$startSector = substr($line[9],0,1);
				$startQuery = "SELECT Latitude, Longitude, Azimuth FROM SprintTowers where NEID = $line[7] AND REPOLL = $line[8] AND CellNum = $startCellNum AND Sector = $startSector";
				mysqli_query($link,$startQuery);

				// nothing returned; usually a problem with the sector so lets eliminate it 
				if ($tower = $link->query($startQuery)) {
					if ($tower->num_rows === 0) {
	
						$startQuery = "SELECT Latitude, Longitude FROM SprintTowers where NEID = $line[7] AND REPOLL = $line[8] AND CellNum = $startCellNum LIMIT 1";
						mysqli_query($link,$startQuery);
						$tower = $link->query($startQuery);
						$row = $tower->fetch_assoc();
						if ($tower->num_rows === 0 ) {
							echo "nothing returned without sector: <BR> $startQuery <BR>";
						} else {
							$startLatitude = $row["Latitude"];
							$startLongitude = $row["Longitude"];
							$startAzimuth = 0;
						}
					} else {
						$row = $tower->fetch_assoc();
						$startLatitude = $row["Latitude"];
						$startLongitude = $row["Longitude"];
						$startAzimuth = $row["Azimuth"];

					}

				} // checking to see the start tower
				$endCellNum = substr($line[10], 1);
				$endSector = substr($line[10],0,1);
				$endQuery = "SELECT Latitude, Longitude, Azimuth FROM SprintTowers where NEID = $line[7] AND REPOLL = $line[8] AND CellNum = $endCellNum AND Sector = $endSector";
				mysqli_query($link,$endQuery);

				// nothing returned; usually a problem with the sector so lets eliminate it 
				if ($tower = $link->query($endQuery)) {
					if ($tower->num_rows === 0) {
				//		echo "nothing returned $filename: " . $line[4] . "<BR>";
				//		echo "$endQuery <BR>";

						$endQuery = "SELECT Latitude, Longitude FROM SprintTowers where NEID = $line[7] AND REPOLL = $line[8] AND CellNum = $endCellNum LIMIT 1";
						mysqli_query($link,$endQuery);
						$tower = $link->query($endQuery);
						if ($tower->num_rows ===0 ) {
							echo "nothing returned without sector: <BR> $endQuery <BR>";
						} else {
							$row = $tower->fetch_assoc();
							$endLatitude = $row["Latitude"];
							$endLongitude = $row["Longitude"];
							$endAzimuth = 0;
						}
					} else {
						$row = $tower->fetch_assoc();
						$endLatitude = $row["Latitude"];
						$endLongitude = $row["Longitude"];
						$endAzimuth = $row["Azimuth"];

					}

				}	 // end of check end tower					
			} else { // if no cellsite data then set the cellsite stuff to 0
    			$startLatitude = '0';
    			$startLongitude = '0';
    			$startAzimuth = '';
    			$endLatitude = '0';
    			$endLongitude = '0';
    			$endAzimuth = '';
    		}

//			echo "datetime: " . $line[4] . " latitude: $latitude | longitude $longitude | azimuth $azimuth <BR>";
    		// check to see if either of these phones are in the phone table - if not add them in.  
			$phoneFromId = getPhoneID(stripPhoneNumber($callFromNum));
			$phoneToId = getPhoneID(stripPhoneNumber($callToNum));	
			$DialedDigits = $line[4];
			// now put the phone calls in: 
    		$insertCallQuery = "INSERT INTO PhoneCalls (
    			CaseID, 
    			CallToPhoneID,
    			CallFromPhoneID,
    			DialedDigits,
    			MRNum,
    			StartDate,
    			EndDate,
    			Duration,
    			NEID,
    			REPOLL,
    			FirstCell,
    			LastCell,
    			FirstLatitude,
    			FirstLongitude,
    			FirstCellDirection,
    			LastLatitude,
    			LastLongitude,
    			LastCellDirection,
    			Pertinent,
    			Notes,
    			Source,
    			ServiceProviderID,
    			CallType,
    			Created,
    			Modified
    		) VALUES (".
    			$GLOBALS['caseID']. ", 
    			$phoneToId, 
    			$phoneFromId, 
    			'$dialedDigits',
    			'',
    			$startDate,
    			$endDate, 
    			'$duration', 
    			null, 
    			null, 
    			null, 
    			null,
    			$startLatitude,
    			$startLongitude,
    			'$startAzimuth',
    			$endLatitude,
    			$endLongitude,
    			'$endAzimuth',
    			1,
    			'',
    			'$source',
    			".$GLOBALS['serviceProviderId'].",
    			'Voice',
    			NOW(),
    			NOW()
    			)";
//    			echo "INSERT CALL QUERY: $insertCallQuery <BR>";
//    			die();
    		echo "$insertCallQuery <BR>";
    		mysqli_query($link,$insertCallQuery);

    		$i++;
    //		if ($i > 100) {die();}
    	}	// close while
    } // close if
    echo "inserted $i rows, hopefully <BR>";
}



$di = new RecursiveDirectoryIterator($inDirectory, FilesystemIterator::SKIP_DOTS);

// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
	$basename = basename($filename);
	// need to check for duplicates!!
	addRecords($filename);
	echo "did it for $filename <BR>";


}



?>