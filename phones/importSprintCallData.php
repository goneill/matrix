<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  
foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     

$inDirectory = "sprintPhoneRecords/";
$caseID = 1;
$serviceProviderID = getServiceProviderID("Sprint PCS");

function getSource($filename) {
	preg_match ('|[0-9]+|',$filename, $matches);
	$source = $matches[0];

	return $source;
}
function getLatLongAz($NEID, $repoll, $cellNum, $sector ) {
	global $link;
	if ($sector) {
		$query = "SELECT Latitude, Longitude, Azimuth FROM SprintTowers where NEID = $NEID AND REPOLL = $repoll AND CellNum = $cellNum AND Sector = $sector";
	} else {
		$query = "SELECT Latitude, Longitude, 0 as Azimuth FROM SprintTowers where NEID = $NEID AND REPOLL = $repoll AND CellNum = $cellNum";
	}
	$results = $link->query($query);
	if ( ($results) && ($results->num_rows !== 0)) { 
		$row=$results->fetch_assoc();
//		print_r($row);
		$cellSiteData['Latitude'] = $row['Latitude'];
		$cellSiteData['Longitude'] = $row['Longitude'];
		$cellSiteData['CellDirection'] = $row['Azimuth'];

	} else {
		if ($sector) {
			$cellSiteData = getLatLongAz($NEID, $repoll, $cellNum, null);
    	} else {
    		$cellSiteData['Latitude'] = '0';
			$cellSiteData['Longitude'] = '0';
			$cellSiteData['CellDirection'] = '0';

    	}
    }
    return $cellSiteData;
}

function getCellSiteData($line) {
	global $link;
	$cellSiteData = Array();
//	echo implode(",",$line) . "<BR>";
	$firstCell = trim($line[9]);
	$lastCell = trim($line[10]);
	$callDirection  = trim($line[3]);
	$NEID = $line[7];
	$repoll = $line[8];
//	echo "$firstCell $lastCell NEID: $NEID Repoll: $repoll<BR>";
	if ($firstCell != '0') { // there is cellsite data on this line

		$startCellNum = substr($firstCell, 1);
		$startSector = substr($firstCell,0,1);
		$startCellSiteData = getLatLongAz($NEID, $repoll, $startCellNum, $startSector);
//		print_r($startCellSiteData);
		$cellSiteData['FirstLatitude'] = $startCellSiteData['Latitude'];
		$cellSiteData['FirstLongitude'] = $startCellSiteData['Longitude']; // get the end lat Long
		$cellSiteData['FirstCellDirection'] = $startCellSiteData['CellDirection'];

	} else {
		$cellSiteData['FirstLatitude'] = '0';
		$cellSiteData['FirstLongitude'] = '0'; // get the end lat Long
		$cellSiteData['FirstCellDirection'] = '0';

	}
	if ($lastCell != '0') {
		$endCellNum = substr($lastCell, 1);
		$endSector = substr($lastCell,0,1);
		$endCellSiteData = getLatLongAz($NEID, $repoll,$endCellNum, $endSector);
		$cellSiteData['LastLatitude'] = $endCellSiteData['Latitude'];
		$cellSiteData['LastLongitude'] = $endCellSiteData['Longitude']; // get the end lat Long
		$cellSiteData['LastCellDirection'] = $endCellSiteData['CellDirection'];
	} else { // if no cellsite data then set the cellsite stuff to 0
		$cellSiteData['LastLatitude'] ='0';
		$cellSiteData['LastLongitude'] = '0'; // get the end lat Long
		$cellSiteData['LastCellDirection'] = '0';

	}
	return $cellSiteData;

}


function addRecords($filename) {
	global $link;
	global $serviceProviderID;
	global $caseID;

	$source = getSource($filename);
	echo "source name = $source";
	$callType = "Voice";
	$i=0; 
	$calls = array();
	// big concern we could have duplicates in here.  
	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";

		//put each line of the file into an array
	    while (($line = fgetcsv($handle)) !== FALSE) {
	    	$linestr = implode('',$line);
	    	if ($linestr=='') {
	    		echo "its a basically empty row!<BR>";
	    		continue;
	    	}
		//	if ($i >12300 ) {
				$cellSiteData = getCellSiteData($line);
/*				if ($cellSiteData['FirstLatitude']<>0) {
					print_r($cellSiteData);
					die();
				} 
*/		    	$call= array();
		    	$call['CaseID'] = $caseID;
		    	if (trim($line[2])<> '') {
		    		$call['ToPhoneID'] = getPhoneID(stripPhoneNumber($line[2]));
			    } else {
			    	$call['ToPhoneID'] = getPhoneID(stripPhoneNumber($line[1]));
			    }
		    	$call['FromPhoneID'] = getPhoneID(stripPhoneNumber($line[0]));
		    	$call['DialedDigits'] = "'".stripPhoneNumber(trim($line[1]))."'";
		    	$call['Direction'] = "'".trim($line[3])."'";
				$call['StartDate'] = getSqlDate(new datetime($line[4]));
				if ($line[5]) {
					$call['EndDate'] = getSqlDate(new datetime($line[5]));
				} else { // if enddate is empty set it to start date
					$call['EndDate'] = $call['StartDate'];
				}
				$call['Duration'] = trim($line[6]);
				$call['NetworkElement'] = "'".trim($line[7])."'";
				$call['Repoll'] = trim($line[8]);
				$call['FirstCell'] = trim($line[9]);
				$call['LastCell'] = trim($line[10]);
				$call['FirstLatitude'] = $cellSiteData['FirstLatitude'];
				$call['FirstLongitude'] = $cellSiteData['FirstLongitude'];
				$call['LastLatitude'] = $cellSiteData['LastLatitude'];
				$call['LastLongitude'] = $cellSiteData['LastLongitude'];
				$call['FirstCellDirection'] = "'".$cellSiteData['FirstCellDirection']."'";
				$call['LastCellDirection'] = "'".$cellSiteData['LastCellDirection']."'";
				$call['Pertinent'] = 1;
				$call['Notes'] = "''";
				$call['Source'] = "'$source'";
				$call['ServiceProviderID'] = $serviceProviderID;
				$call['CallType'] = "'$callType'";
				$call['Created'] = 'Now()';
				$call['Modified'] = 'NOW()';
							
/*				if ($cellSiteData['FirstLatitude']<>0) {
					print_r($call);
					die();
				} 
*/
				$calls[] = "(".implode(',',$call).")";
	//		}
	    	$i++;
     	}	// close while
		echo "finished creating the array: $i <BR>";
		insertCalls($calls);
   
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