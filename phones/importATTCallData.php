<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  
foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     
//And then in any place you can just write:


$inDirectory = "../input/attPhoneRecords/";
$caseID = 4;
$serviceProviderID = getServiceProviderID("AT&T"); 

function checkUsage($text) {
	if (strpos($text, "Usage For:")) {
		return true;
	} else {
		return false;
	}
}

function getSource($text) {
	preg_match ('|[0-9]+|',stripPhoneNumber($text), $matches);
	$source = $matches[0];
	echo "source name = $source <BR>";
	return $source;
}
function getCallType ($text) {
	$type = trim(substr($text, 0, strpos($text, "Usage For")));
	echo "type: $type <BR>";
	return $type;
}

function skipLine($line) {
	if (!isset($line[1])) {
		return true;
	}
	if (strpos($line[0], "Item")!==FALSE) {
		return true;
	}
	return false;
}
function getEndDate($startDate, $duration) {
//	echo "startDate: " . $startDate->format(DATE_W3C);
	$durationParts = explode(':',$duration);
	$endDate = $startDate;
	$durationString = "PT" . $durationParts[0] . 'M'. $durationParts[1] . 'S';
	$durationInterval = new DateInterval($durationString);
	$endDate =  $endDate->add($durationInterval);
	$endDate = getSqlDate($endDate);
//	echo " end date: $endDate <BR>";
//	die();
	return $endDate;
}

function getDirection($toPhoneNum) {
	global $source;
	if (strpos($toPhoneNum, $source)!==FALSE) {
		return 'Incoming';
	} else {
		return 'Outgoing';
	}
}

function getCellSiteData($origData){

	$origData = trim($origData);
	echo $origData. "<BR>";
	if ($origData == null or $origData=='' or $origData=='[]') {
		$cell['Latitude'] = 0;
		$cell['Longitude'] = 0;
		$cell['Azimuth'] = 0;
	} else {
		$cellArray = explode(":", str_replace(array('[',']'), "", $origData)); 	
		print_r($cellArray);
		$cell['Latitude'] = $cellArray[2]+0;
		$cell['Longitude'] = $cellArray[1]+0;
		if ($cellArray[3]) {
			$cell['Azimuth'] = $cellArray[3]+0;
		} else {
			$cell['Azimuth'] = 0;
		} 
	}
	return $cell;
}

function setEmptyCellsite() {
	$cell['Latitude'] = 0;
	$cell['Longitude'] = 0;
	$cell['Azimuth'] = 0;
	return $cell;
}

function setVoiceCall($line, $source, $serviceProviderID, $callType) {

	global $caseID;
	$startDate = new DateTime(trim($line[1]));
	$startDateEST = date_modify(new datetime($line[1]), '-5 hours');
	$duration = trim($line[3]);
	$toPhoneNum = stripPhoneNumber(trim($line[5]));
	if (isset($line[16])) {
		$firstCell = trim($line[16]);
		$startCellSiteData = getCellSiteData($firstCell);
	} else {
		$firstCell = '';
		$startCellSiteData = setEmptyCellsite();
	}
	if (isset($line[17])) {
		// if 17 is set whe know there is a last, but 'end' gives us the actual last
		$lastCell = trim(end($line));
		$endCellSiteData = getCellSiteData($lastCell);
	} else {
		$lastCell = '';
		$endCellSiteData = setEmptyCellsite();
	}
		
	$call = array();
	$call['CaseID'] = $caseID;
	$call['ToPhoneID'] = getPhoneID($toPhoneNum);
	$call['FromPhoneID'] = getPhoneID(stripPhoneNumber($line[4]));
	$call['DialedDigits'] = "'".$toPhoneNum."'";
	$call['Direction'] = "'". getDirection($toPhoneNum)."'";
	$call['StartDate'] = getSqlDate($startDateEST);
	$call['EndDate'] = getEndDate($startDateEST, $duration);
	$call['Duration'] = "'".$duration."'";  
	$call['NetworkElement'] = "NULL";
	$call['Repoll'] = 'NULL';
	$call['FirstCell'] = "'".$firstCell."'";
	$call['LastCell'] = "'".$lastCell."'";
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
	$call['CallType'] = "'$callType'";
	$call['Created'] = 'NOW()';
	$call['Modified'] = 'NOW()';

	return $call;
}

function setSMSCall($line, $source, $serviceProviderID, $callType) {
	global $caseID;

//	Network Element Name,Switch Type Indicator,MDN,Msg Send Date,Msg Deliver Date,Message Completion Status,Originating Address,Destination Address,Message Direction Indicator,MIN,
//Mapleshade_96,M,3478801529,01/11/2016 22:37:36,01/11/2016 22:37:37,16,3478801529,900080004101,4,0,

	$startDateEST = date_modify(new datetime($line[1]), '-5 hours');
	$duration = "'0:00'";
	$toPhoneNum = stripPhoneNumber(trim($line[3]));
	$fromPhoneNum = stripPhoneNumber(trim($line[2]));
	if (isset($line[9])) {
		$firstCell = trim($line[9]);
		$startCellSiteData = getCellSiteData($firstCell);
	} else {
		$firstCell = '';
		$startCellSiteData = setEmptyCellsite();
	}
	$call['CaseID'] = $caseID;
	$call['ToPhoneID'] = getPhoneID($toPhoneNum);
	$call['FromPhoneID'] = getPhoneID($fromPhoneNum);
	$call['DialedDigits'] = "'".$toPhoneNum."'";
	$call['Direction'] = "'". getDirection($toPhoneNum)."'";
	$call['StartDate'] = getSqlDate($startDateEST);
	$call['EndDate'] = getSqlDate($startDateEST);
	$call['Duration'] = $duration;  
	$call['NetworkElement'] = "NULL";
	$call['Repoll'] = 'NULL';
	$call['FirstCell'] = "'".$firstCell."'";
	$call['LastCell'] = "'".$firstCell."'";
	$call['FirstLatitude'] = $startCellSiteData['Latitude'];
	$call['FirstLongitude'] = $startCellSiteData['Longitude'];
	$call['LastLatitude'] = $startCellSiteData['Latitude'];
	$call['LastLongitude'] = $startCellSiteData['Longitude'];
	$call['FirstCellDirection'] = "'".$startCellSiteData['Azimuth']."'";
	$call['LastCellDirection'] =  "'".$startCellSiteData['Azimuth']."'";
	$call['Pertinent'] = 1;
	$call['Notes'] = "''";
	$call['Source'] = "'$source'";
	$call['ServiceProviderID']  = $serviceProviderID;
	$call['CallType'] = "'$callType'";
	$call['Created'] = 'NOW()';
	$call['Modified'] = 'NOW()';
	return $call;

}
function setDataCall($line, $source, $serviceProviderID, $callType) {
	global $caseID;
	$toESTInterval = 'PT5H0S';
	$startDateEST = new datetime($line[1]);
	$startDateEST = $startDateEST->sub(new DateInterval($toESTInterval)); 
//	echo "start date est" . $startDateEST->format(DATE_W3C) . "<BR>";

	$endDateEST = new datetime($line[1]);
	$endDateEST = $endDateEST->sub(new DateInterval($toESTInterval)); 
	$duration = trim($line[3]);
	$endDateEST = getEndDate($endDateEST, $duration);
	$toPhoneNum = stripPhoneNumber(trim($line[2]));
	$fromPhoneNum = stripPhoneNumber(trim($line[2]));
	if (isset($line[10])) {
		$firstCell = trim($line[10]);
		$startCellSiteData = getCellSiteData($firstCell);
	} else {
		$firstCell = '';
		$startCellSiteData = setEmptyCellsite();
	}
	$call['CaseID'] = $caseID;
	$call['ToPhoneID'] = getPhoneID($toPhoneNum);
	$call['FromPhoneID'] = getPhoneID($fromPhoneNum);
	$call['DialedDigits'] = stripPhoneNumber(trim($line[2]));
	$call['Direction'] = "'". getDirection($toPhoneNum)."'";
	$call['StartDate'] = getSqlDate($startDateEST);
	$call['EndDate'] = $endDateEST;
	$call['Duration'] = "'".$duration."'";  
	$call['NetworkElement'] = "NULL";
	$call['Repoll'] = 'NULL';
	$call['FirstCell'] = "'".$firstCell."'";
	$call['LastCell'] = "'".$firstCell."'";
	$call['FirstLatitude'] = $startCellSiteData['Latitude'];
	$call['FirstLongitude'] = $startCellSiteData['Longitude'];
	$call['LastLatitude'] = $startCellSiteData['Latitude'];
	$call['LastLongitude'] = $startCellSiteData['Longitude'];
	$call['FirstCellDirection'] = "'".$startCellSiteData['Azimuth']."'";
	$call['LastCellDirection'] =  "'".$startCellSiteData['Azimuth']."'";
	$call['Pertinent'] = 1;
	$call['Notes'] = "''";
	$call['Source'] = "'$source'";
	$call['ServiceProviderID']  = $serviceProviderID;
	$call['CallType'] = "'$callType'";
	$call['Created'] = 'NOW()';
	$call['Modified'] = 'NOW()';
	return $call;

}
function addRecords($filename) {
	echo "in the add records <BR>";

	//check if its a real filename
	global $link;
	global $serviceProviderID;
	global $caseID;

	$i=0;
	$calls = array(); 

	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";

		$source = '';
		$type = '';
	    while (($line = fgetcsv($handle)) !== FALSE) {
    		$i++;
//			if ($i>500) {break;}
 			//if ($i<=10000) {continue;}
	    	if (checkUsage($line[0])) {
	    		print_r($line);
	    		$source =getSource($line[0]);
	    		$type = getCallType($line[0]);
	    		$i++;
	    		continue;
	    	}
			if (skipLine($line)) {
				$i++;
				continue;
			}
/*
SMS: Item,ConnDateTime(UTC),OriginatingNumber,TerminatingNumber,IMEI,IMSI,Desc,MAKE,MODEL,CellLocation

*/
	    	$call= array();
	    	$call['CaseID'] = $caseID;
	    	if ($type =='Voice') {
//	    		continue;
	    		$call = setVoiceCall($line,  $source, $serviceProviderID, $type);

	    	} elseif ($type == 'SMS') {
//	    		continue;
//	    		echo "an SMS!";
	    		$call = setSMSCall($line,  $source, $serviceProviderID, $type);
	    			
	    	} elseif ($type == 'Data') {
//	    		echo "we got data!<BR>";
//	    		die();
	    		$call = setDataCall($line, $source, $serviceProviderID, $type);
//	    		die();

	    	} else {
	    		echo "no type:  <BR>";
	    		print_r($line);
	    		die();

	    	}

			$calls[] = "(".implode(',',$call).")";
			
    	}
		echo "finished creating the array: $i<BR>";
//		print_r($calls);
	    if (!empty($calls)) {
//	    	echo "we've gotten thte calls but something else is wrong...";
	    	insertCalls($calls);
	    }	
    } // close if
    echo "inserted $i rows, hopefully <BR>";
}



$di = new RecursiveDirectoryIterator($inDirectory, FilesystemIterator::SKIP_DOTS);

// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
	$basename = basename($filename);
	// need to check for duplicates!!
	if (strpos($filename, '.txt')) {
		addRecords($filename);
		echo "did it for $filename <BR>";
	}




}



?>