<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  
foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     

// this code should be executed on every page/script load:
$inDirectory = "../input/verizonPhoneRecords/";
$caseID = 2;
$serviceProviderID = getServiceProviderID("Verizon"); 
echo $serviceProviderID. "<BR>";
$missingElementName = Array();


function skipLine($line) {
	if (strpos(trim($line[0]), 'Network Element Name') !== false) {
		echo "header row!<BR>";
		return true;
	} elseif (implode('',$line)=='') {
   		echo "its a basically empty row!<BR>";
   		return true;
   	} else {
		return false;
	}
}
function getEndDate($startDate, $duration){
	$durationInterval = new DateInterval("PT".$duration."S");
	return $startDate->add($durationInterval);
}
//this isn't working!!!
//NEED TO UPDATE VerizonSwitchElementMap!!!
function getLatLongAz($cellSite, $cellFace, $networkElementName){
	global $link;
	global $missingElementName;
	global $caseID;
	if ($cellFace == 'D4') {
		$cellSiteQuery = "SELECT Latitude, Longitude, 0 AS Azimuth FROM VerizonTowers,VerizonSwitchElementMap   WHERE VerizonSwitchElementMap.NetworkElementName = '$networkElementName' AND VerizonSwitchElementMap.SwitchName = VerizonTowers.SwitchName AND VerizonTowers.CellNumber = $cellSite AND CaseID = $caseID";
	} else {
		$cellSiteQuery = "SELECT Latitude, Longitude, Azimuth FROM VerizonTowers,VerizonSwitchElementMap   WHERE VerizonSwitchElementMap.NetworkElementName = '$networkElementName' AND VerizonSwitchElementMap.SwitchName = VerizonTowers.SwitchName AND VerizonTowers.CellNumber = $cellSite AND VerizonTowers.Sector = '$cellFace' AND CaseID = $caseID";
	}
	if ($results = $link->query($cellSiteQuery)) {
		if ($results->num_rows>0) {
			$row = $results->fetch_assoc(); 
			$cellSiteData['Latitude'] = $row['Latitude'];
			$cellSiteData['Longitude'] = $row['Longitude'];
			$cellSiteData['CellDirection'] = $row['Azimuth'];
		} else {
			// check to see if we can get a lat long azimuth would be zero
		
			echo "cellsite doesn't exist: $cellSite netowrk element name: $networkElementName<BR> cellSiteQuery: $cellSiteQuery <BR>";
			if (!in_array($networkElementName, $missingElementName)){
				$missingElementName[$networkElementName]=1;

			} else {
				$missingElementName[$networkElementName]= $missingElementName[$networkElementName+1];

			//	echo "query didn't return anything: $cellSiteQuery <BR>";
			}// else keep it at 0,0, '';
			$cellSiteData['Latitude'] = 0;
			$cellSiteData['Longitude'] = 0;
			$cellSiteData['CellDirection'] = 0;
		}
	} else {
		echo "getLatLongAZ query didn't work: $cellSiteQuery <BR>";
		die();
	}
	return $cellSiteData;	

}
function getCellSiteData($line) {
	$networkElementName = trim($line[0]);
	$firstServingCellSite = trim($line[6]);
	$firstServingCellFace = "D".trim(substr($line[7],0,1));
	$lastServingCellSite = trim($line[8]);
	$lastServingCellFace = "D".trim(substr($line[9], 0,1));
	$callingPartyNumber = trim($line[10]);

	// if firstservingcellsite <= 0 then there is cellsite data included
	if (($firstServingCellSite != 0)) {
		$startCellSiteData = getLatLongAz($firstServingCellSite, $firstServingCellFace, $networkElementName);

		$cellSiteData['FirstLatitude'] = $startCellSiteData['Latitude'];
		$cellSiteData['FirstLongitude'] = $startCellSiteData['Longitude']; // get the end lat Long
		$cellSiteData['FirstCellDirection'] = $startCellSiteData['CellDirection'];

	} else {
		$cellSiteData['FirstLatitude'] = '0';
		$cellSiteData['FirstLongitude'] = '0'; // get the end lat Long
		$cellSiteData['FirstCellDirection'] = '0';

	}	
	if ($lastServingCellSite != '0') {
		$endCellSiteData = getLatLongAz($lastServingCellSite, $lastServingCellFace, $networkElementName);
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

function setVoiceCall($line, $serviceProviderID, $callType) {
	global $caseID;
	$cellSiteData = getCellSiteData($line);
	$duration = trim($line[5]);
	$call= array();
	$call['CaseID'] = $caseID;
	$call['ToPhoneID'] = getPhoneID(stripPhoneNumber($line[2]));
	$call['FromPhoneID'] = getPhoneID(stripPhoneNumber($line[10]));
	$call['DialedDigits'] = "'".stripPhoneNumber(trim($line[2]))."'";
	$call['Direction'] = "'".trim($line[3])."'";
	$call['StartDate'] = getSqlDate(new datetime($line[4]));
	$call['EndDate']= getSqlDate(getEndDate(new datetime($line[4]), $duration)); 
	$call['Duration'] = $duration;
	$call['NetworkElement'] = "'".trim($line[0])."'";
	$call['Repoll'] = 'NULL';
	$call['FirstCell'] = trim($line[6]);
	$call['LastCell'] = trim($line[8]);
	$call['FirstLatitude'] = $cellSiteData['FirstLatitude'];
	$call['FirstLongitude'] = $cellSiteData['FirstLongitude'];
	$call['LastLatitude'] = $cellSiteData['LastLatitude'];
	$call['LastLongitude'] = $cellSiteData['LastLongitude'];
	$call['FirstCellDirection'] = "'".$cellSiteData['FirstCellDirection']."'";
	$call['LastCellDirection'] = "'".$cellSiteData['LastCellDirection']."'";
	$call['Pertinent'] = 1;
	$call['Notes'] = "''";
	$call['Source'] = "'".$line[1]."'";
	$call['ServiceProviderID'] = $serviceProviderID;
	$call['CallType'] = "'$callType'";
	$call['Created'] = 'Now()';
	$call['Modified'] = 'NOW()';

	return $call;
}
function setSMSCall($line, $serviceProviderID, $callType) {
//Network Element Name,Switch Type Indicator,MDN,Msg Send Date,Msg Deliver Date,Message Completion Status,Originating Address,Destination Address,Message Direction Indicator,MIN,
//Mapleshade_96,M,3478801529,01/11/2016 22:37:36,01/11/2016 22:37:37,16,3478801529,900080004101,4,0,
	global $caseID;

	$duration ="''";
	$call= array();
	$call['CaseID'] = $caseID;
	$fromPhoneNum = 
	$call['ToPhoneID'] = getPhoneID(stripPhoneNumber($line[7]));
	$call['FromPhoneID'] = getPhoneID(stripPhoneNumber($line[6]));
	$call['DialedDigits'] = "'".stripPhoneNumber(trim($line[7]))."'";
	$call['Direction'] = "'".trim($line[8])."'";
	$call['StartDate'] = getSqlDate(new datetime($line[3]));
	$call['EndDate']= $call['StartDate'];
	$call['Duration'] = $duration;
	$call['NetworkElement'] = "'".trim($line[0])."'";
	$call['Repoll'] = 'NULL';
	$call['FirstCell'] = "''";
	$call['LastCell'] = "''"; 
	$call['FirstLatitude'] ='NULL';
	$call['FirstLongitude'] = 'NULL';
	$call['LastLatitude'] = 'NULL';
	$call['LastLongitude'] = 'NULL';
	$call['FirstCellDirection'] = "''";
	$call['LastCellDirection'] = "''";
	$call['Pertinent'] = 1;
	$call['Notes'] = "''";
	$call['Source'] = "'".$line[2]."'";
	$call['ServiceProviderID'] = $serviceProviderID;
	$call['CallType'] = "'$callType'";
	$call['Created'] = 'Now()';
	$call['Modified'] = 'NOW()';
	return $call;

}
function addRecords($filename) {
	global $link;
	global $serviceProviderID;
	global $caseID;

	if (!strpos($filename, '.csv')) {
		return;
	}

	$i=0;
	$calls = array(); 

	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";
		$source = '';
   		if (strpos($filename, 'Text_Message')) {
    			echo "its SMS: $filename <BR>";
   			$callType = 'SMS';

   		} else {
   			echo "Voice file: $filename<BR>";
   			$callType = 'Voice';
    	}

		//put each line of the file in the database
	    while (($line = fgetcsv($handle)) !== FALSE) {
			if (skipLine($line)) {
				$i++;
				continue;
			}

			if ($callType =='Voice') {
	    		$call = setVoiceCall($line, $serviceProviderID, $callType);
			} elseif ($callType == 'SMS') {
				$call = setSMSCall($line, $serviceProviderID, $callType);
			}

			$calls[] = "(".implode(',',$call).")";

    		$i++;
    	}
    	echo "finished creating the array: $i<BR>";
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
	}

}
echo "missing network element names: <BR>";
print_r($missingElementName);

?>