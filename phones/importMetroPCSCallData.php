<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  

foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     

$inDirectory = "MetroPCSPhoneRecords/";
$caseID = 1;
$serviceProviderID = getServiceProviderID("MetroPCS"); 
echo $serviceProviderID . "<BR>";
function checkSource($line) {
	if (strpos($line[0], "Search Number: ")) {
		preg_match ('|[0-9]+|',stripPhoneNumber($line[0]), $matches);
		$source = $matches[0];
		return $source;
	} 
	return FALSE;

}
function getEndDate($startDate, $duration) {
	$durationParts = explode(':',$duration);
	$durationInterval = new DateInterval("PT$durationParts[0]M$durationParts[1]S");
	$endDate = getSqlDate($startDate->add($durationInterval));
	return $endDate;
}
function getDirection($dir) {
	if ($dir== 'Incoming Call') {
		return 'Incoming';		
	} elseif ($dir == 'Outgoing Call') {
		return 'Outgoing';
	} else {
		echo "something weird: $dir <BR>";
		die();
	}
}

function getCallType($type) {
	if (strrpos($type, 'Call')) {
		return 'Call';
	} else {
		echo "some weird type: $type <BR>";
		die();
	}
}
function getCellSiteData($line) {
	$cellSiteData['Latitude'] = '0';
	$cellSiteData['Longitude'] = '0'; // get the end lat Long
	$cellSiteData['CellDirection'] = '0';
	$cellSiteData['Azimuth'] = '0';

	if ($line[11]<> '' AND $line[12] <> '') {
		$query = "SELECT Latitude, Longitude, Orientation From MetroPCStowers WHERE Lac = " . $line[11] . " AND Cell_ID = " . $line[12];
		$results = $link->query($query);
		if ( ($results) && ($results->num_rows !== 0)) { 
			$row=$results->fetch_assoc();
		//		print_r($row);
			$cellSiteData['Latitude'] = $row['Latitude'];
			$cellSiteData['Longitude'] = $row['Longitude'];
			$cellSiteData['CellDirection'] = $row['Orientation'];

	} 	
	$cellSiteData['Latitude'] = '0';
	$cellSiteData['Longitude'] = '0'; // get the end lat Long
	$cellSiteData['CellDirection'] = '0';
	$cellSiteData['Azimuth'] = '0';
	return $cellSiteData;
}
function addRecords($filename) {
	global $link;
	global $serviceProviderID;
	global $caseID;
	$i=1; 
	$source = False;
	$calls = array();

	// big concern we could have duplicates in here.  
	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";
//		fgets($handle);
		//put each line of the file in the database
	    while (($line = fgetcsv($handle)) !== FALSE) {
//	    	if ($i>10) { break;}
	    	if (!$source) {
	    		$source = checkSource($line);
	    		echo "source name = $source <BR>";
	    		continue;
	    	}
			if (preg_match('/Date/',$line[0])) {
				echo "header row<BR>";
				continue;
			} 
			$startCellSiteData = getCellSiteData($line);
			$endCellSiteData = $startCellSiteData;
//Date,Time,Duration,DIR,Dialed Number,Dest Number,Status,Special Features,CallerID,Switch,Sector,LAC,Tower
//12/1/2014,01:02:27,0:07,Incoming Call,,3474446552,Answered,None (2b),3476343903,64662280550,,,
			$direction = getDirection($line[3]);
			if ($direction == 'Outgoing') {
				$fromPhoneNum = $source;
			} else {
				$fromPhoneNum = $line[8];
			}
			$toPhoneNum = $line[5];
			$startDateEST = new datetime($line[0] . ' ' . $line[1]);
			$duration = $line[2];
			$call = array();
			$call['CaseID'] = $caseID;
			$call['ToPhoneID'] = getPhoneID(stripPhoneNumber($toPhoneNum));
			$call['FromPhoneID'] = getPhoneID(stripPhoneNumber($fromPhoneNum));
			$call['DialedDigits'] = "'".$toPhoneNum."'";
			$call['Direction'] = "'". getDirection($line[3])."'";
			$call['StartDate'] = getSqlDate($startDateEST);
			$call['EndDate'] = getEndDate($startDateEST, $duration);
			$call['Duration'] = "'".$duration."'";  
			$call['NetworkElement'] = "'".$line[9]."'";
			$call['Repoll'] = 0+$line[11];
			$call['FirstCell'] = "'".$line[12]."'";
			$call['LastCell'] = "''";
			$call['FirstLatitude'] = $startCellSiteData['Latitude'];
			$call['FirstLongitude'] = $startCellSiteData['Longitude'];
			$call['LastLatitude'] = $endCellSiteData['Latitude'];
			$call['LastLongitude'] = $endCellSiteData['Longitude'];
			$call['FirstCellDirection'] = "'".$startCellSiteData['Azimuth']."'";
			$call['LastCellDirection'] = "'".$endCellSiteData['Azimuth']."'";
			$call['Pertinent'] = 1;
			$call['Notes'] = "''";
			$call['Source'] = "'$source'";
			$call['ServiceProviderID']  = $serviceProviderID;
			$call['CallType'] = "'".getCallType($line[3]) ."'";
			$call['Created'] = 'NOW()';
			$call['Modified'] = 'NOW()';
			$calls[] = "(".implode(',',$call).")";
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