<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  
foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     

$inDirectory = "../input/tmobile/";
$caseID = 3;
$serviceProviderID = getServiceProviderID("T-Mobile");

function getSource($filename) {
	preg_match ('|[0-9]+|',$filename, $matches);
	$source = $matches[0];

	return $source;
}
function getLatLongAz($NEID, $repoll, $cellNum, $sector ) {
	global $link;
	if ($sector) {
		$query = "SELECT Latitude, Longitude, Azimuth FROM SprintTowers where NEID = $NEID AND CellNum = $cellNum AND Sector = $sector";
	} else {
		$query = "SELECT Latitude, Longitude, 0 as Azimuth FROM SprintTowers where NEID = $NEID AND CellNum = $cellNum";
	}

	$results = $link->query($query);
	if ( ($results) && ($results->num_rows !== 0)) { 
		$row=$results->fetch_assoc();
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
	$firstCell = trim($line[20]);
	$lastCell = trim($line[30]);
	$firstLatitude = trim($line[22]); 
	$firstLongitude = trim($line[23]); 
	$lastLatitude = trim($line[32]); 
	$lastLongitude = trim($line[33]); 
	$cellSiteData['FirstCellDirection'] = "'".$line[21]."'";
	$cellSiteData['LastCellDirection'] = "'".$line[31]."'";
	$cellSiteData['FirstLAC'] = "'".$line[19]."'";
	$cellSiteData['LastLAC'] = "'".$line[29]."'";
	$cellSiteData['NetworkElement'] = $line[17];

	if ($firstCell != '') { // there is cellsite data on this line
		$cellSiteData['FirstCell'] = $firstCell;
	} else {
		$cellSiteData['FirstCell'] = "''";
	}
	if ($firstLatitude != '') {
		$cellSiteData['FirstLatitude'] = $firstLatitude;
		$cellSiteData['FirstLongitude'] = $firstLongitude; // get the end lat Long
	} else {
		$cellSiteData['FirstLatitude'] = '0';
		$cellSiteData['FirstLongitude'] = '0'; // get the end lat Long

	}			
	if ($lastCell != '') { // there is cellsite data on this line
		$cellSiteData['LastCell'] = $lastCell;
	} else {
		$cellSiteData['LastCell'] = "''";
	}
	if ($lastLatitude != '') {
		$cellSiteData['LastLatitude'] = $lastLatitude;
		$cellSiteData['LastLongitude'] = $lastLongitude; // get the end lat Long
	} else {
		$cellSiteData['LastLatitude'] = '0';
		$cellSiteData['LastLongitude'] = '0'; // get the end lat Long

	}			

	return $cellSiteData;

}


function addRecords($filename) {
	global $link;
	global $serviceProviderID;
	global $caseID;

	$source = getSource($filename);
	echo "source name = $source";
	$i=0; 
	$calls = array();
	// all records come in in UTC
	date_default_timezone_set('UTC');
	// big concern we could have duplicates in here.  
	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";

		//put each line of the file into an array
	    while (($line = fgetcsv($handle, 0,"\t")) !== FALSE) {
	    	$linestr = implode('',$line);
	    	if ($linestr=='') {
	    		echo "its a basically empty row!<BR>";
	    		continue;
	    	}
	    	if ($line[0]== 'Date') {
	    		echo "header row <BR>";
	    		continue;
	    	}

			//$cellSiteData = getCellSiteData($line);
			/*Date	Time	Duration	Call Type	Direction	Calling Number	Dialed Number	Called Number	Destination Number	IMSI	IMEI	Completion Code	Answered?	Service Code	Disconnecting Party	Service Indicator	SMS Deliv Status	Switch Name	1st LTE Site ID	1st LAC	1st Cell ID	1st Tower Azimuth	1st Tower LAT	1st Tower LONG	1st Tower Address	1st Tower City	1st Tower State	1st Tower Zip	Last LTE Site ID	Last LAC ID	Last Cell ID	Last Tower Azimuth	Last Tower LAT	Last Tower LONG	Last Tower Address	Last Tower City	Last Tower State	Last Tower Zip
CALLING_NBR,CALLED_NBR,DIALED_DIGITS,MOBILE ROLE,START_DATE,END_DATE,DURATION (SEC),Call Type,NEID,1ST CELL,LAST CELL
*/
	    	$call= array();
	    	$cellSiteData = getCellSiteData($line);
	    	$call['CaseID'] = $caseID;
	    	$call['ToPhoneID'] = getPhoneID(stripPhoneNumber($line[7]));		    	
	    	$call['FromPhoneID'] = getPhoneID(stripPhoneNumber($line[5]));
	    	$call['DialedDigits'] = "'".stripPhoneNumber(trim($line[6]))."'";
	    	$call['Direction'] = "'".trim($line[4])."'";
			$startDate = new datetime($line[0]. ' ' .$line[1]);
			$startDate->setTimezone(new DateTimezone('America/New_York'));
			$call['StartDate'] = getSqlDate($startDate);
			if ($line[4]) {
				$start = $startDate;
			
				if ($line[2]) {
					$call['EndDate'] = getSqlDate($start->add(new DateInterval ('PT'.$line[2].'S')));
				} else {
					$call['EndDate'] = $call['StartDate'];
				}
			} else { // if enddate is empty set it to start date
				$call['EndDate'] = $call['StartDate'];
			}

			$call['Duration'] = "'". trim($line[2])."'";
			$call['NetworkElement'] = "'".trim($line[17])."'";
			$call['Repoll'] = "0";
			$call['FirstCell'] = $cellSiteData['FirstCell'];
			$call['LastCell'] = $cellSiteData['LastCell'];
			$call['FirstLatitude'] = $cellSiteData['FirstLatitude'];
			$call['FirstLongitude'] = $cellSiteData['FirstLongitude'];
			$call['LastLatitude'] = $cellSiteData['LastLatitude'];
			$call['LastLongitude'] = $cellSiteData['LastLongitude'];
			$call['FirstCellDirection'] = $cellSiteData['FirstCellDirection'];
			$call['LastCellDirection'] = $cellSiteData['LastCellDirection'];
			$call['Pertinent'] = 1;
			$call['Notes'] = "''";
			$call['Source'] = "'$source'";
			$call['ServiceProviderID'] = $serviceProviderID;
			$call['CallType'] = "'".$line[3]."'";
			$call['Created'] = 'Now()';
			$call['Modified'] = 'NOW()';									

		//	print_r($call);
		//	die();

			$calls[] = "(".implode(',',$call).")";
			//print_r($call);
			//die();
		
		    $i++;
		}
  		// close while
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
	if (strpos($filename, '.csv')|| strpos($filename, '.txt')) {
		addRecords($filename);
	echo "did it for $filename <BR>";
	} else {
		echo "skipped $filename <BR>";
	}

}



?>