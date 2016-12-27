<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  
ini_set('display_errors', 1);
ini_set('log_errors', 0);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');
set_time_limit(0);
ini_set("memory_limit","2400M");
ini_set("auto_detect_line_endings", true);
foreach(glob('Includes/*.php') as $file) {
     include_once $file;
}     

// this code should be executed on every page/script load:
$link = mysqli_connect("localhost", "root", "nathando123", "matrix");

// ...

//And then in any place you can just write:

function errHandle($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errStr in $errFile on line $errLine";
    if ($errNo == E_NOTICE || $errNo == E_WARNING) {
        throw new ErrorException($msg, $errNo);
    } else {
        echo $msg;
    }
}

set_error_handler('errHandle');


$inDirectory = "verizonPhoneRecords/";
$caseID = 1;
$serviceProviderId = getServiceProviderID("Verizon"); 

function getServiceProviderID($serviceProvider) {
	global $link;
	$getServiceProviderIDQuery = "SELECT ServiceProviderID FROM ServiceProviders WHERE Name = '$serviceProvider'";
	if ($serviceProvider = $link->query($getServiceProviderIDQuery)) {
		$row = $serviceProvider->fetch_assoc();
		return $row["ServiceProviderID"];
	} else {
		echo "serviceProvider not found<BR>";
		die();
	}	
}
function stripPhoneNumber ($number) {
	$number = str_ireplace( array('(', ')', ' ', '-', '.','*'), array('', '', '', '', '',''), $number);
	if (strpos($number, '1') === 0) {
		$number = substr($number, 1);
	}
	if ($number == '') {$number = 0;}
	return $number;
}
function getPhoneID($phoneNumber) {
	global $link;
	//first check to see if the id is in the mysql database
	$phoneNumQuery = "SELECT * FROM PHONES where PhoneNumber = $phoneNumber";
//	echo "phoneNumQuery: $phoneNumQuery<BR>";
	mysqli_query($link,$phoneNumQuery);
	if ($phone = $link->query($phoneNumQuery)) {
		if ($phone->num_rows === 0) {
			echo "phone wasn't in database $phoneNumber <BR>";
			// add the phone to the phones database;
			$insertPhoneQuery = "INSERT INTO PHONES (CaseID, PhoneNumber, ServiceProviderID, Created, Modified, ShortName, LongName, Icon) VALUES (" . $GLOBALS['caseID'] . ", $phoneNumber, null, NOW(), NOW(), '', '', '')";  
//			echo "insert phone query: $insertPhoneQuery <BR>";
			mysqli_query($link,$insertPhoneQuery);
			$phoneId = $link->insert_id;
		} else {
			$row = $phone->fetch_assoc();
			$phoneId = $row["PhoneID"];
		}
		return $phoneId;
	} else {
		echo "sql query didn't work! $phoneNumQuery";

		die();
	}
}
function getSqlDate($date) {
//	echo $date;
	if ($date =='') {
		$mysqldate = "NULL"; 
	} else {
		$mysqldate = "'".date( 'Y-m-d H:i:s', strtotime($date) )."'";
	}
	return $mysqldate;
}
function addRecords($filename) {
	//check if its a real filename

	global $link;
	if (!$link) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}
	if (!strpos($filename, '.csv')) {
		return;
	}

	$i=1; 
	// big concern we could have duplicates in here.  
	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";
		$source = '';
//		fgets($handle);
		//put each line of the file in the database
	    while (($line = fgetcsv($handle)) !== FALSE) {
/*Network Element Name,Mobile Directory Number,Dialed Digit Number,Call Direction,Seizure Dt Tm,Seizure Duration,First Serving Cell Site,First Serving Cell Face,Last Serving Cell Site,Last Serving Cell Face,Calling Party Number
*/			if ($i==1) {
				$i++;
				continue;
			}
			if ($source == '') {
				$source = $line[1];
			}
			$networkElementName = $line[0];
			$mobileDirectoryNumber = $line[1];
			$dialedDigitNumber = stripPhoneNumber($line[2]);
			$callDirection = $line[3];
			$SeizureDtTm = getSqlDate($line[4]);
			$seizureDuration = $line[5];
			$firstServingCellSite = $line[6];
			$firstServingCellFace = "D".substr($line[7],0,1);
			$lastServingCellSite = $line[8];
			$lastServingCellFace = "D".substr($line[9], 0,1);
			$callingPartyNumber = $line[10];
			$mrNum = $line[3];
			$callToNum = $line[1];
			$callFromNum = $line[0];
			$startDate = getSqlDate($line[4]);
			$endDate = getSqlDate($line[5]);
			$startLatitude = 0;
			$startLongitude = 0; 
			$startAzimuth = '';
			$endLatitude = 0;
			$endLongitude = 0;
			$endAzimuth ='';

			// if firstservingcellsite <= 0 then there is cellsite data included
			if (($firstServingCellSite != 0)) {
				$startCellSiteQuery = "SELECT Latitude, Longitude, Azimuth FROM VerizonTowers,VerizonSwitchElementMap   WHERE VerizonSwitchElementMap.NetworkElementName = '$networkElementName' AND VerizonSwitchElementMap.SwitchName = VerizonTowers.SwitchName AND VerizonTowers.CellNumber = $firstServingCellSite AND VerizonTowers.Sector = '$firstServingCellFace'";
				if ($startTower = $link->query($startCellSiteQuery)) {
					if ($startTower->num_rows>0) {
						$row = $startTower->fetch_assoc(); 
						$startLatitude = $row['Latitude'];
						$startLongitude= $row['Longitude'];
						$startAzimuth = $row['Azimuth'];
					} else {
						echo "query didn't return anything: $startCellSiteQuery <BR>";
					}// else keep it at 0,0, '';
				} else {
					echo "query didn't work: $startCellSiteQuery <BR>";
				}
			} else {
			}
			if ($lastServingCellSite != 0) {
				$endCellSiteQuery = "SELECT Latitude, Longitude, Azimuth FROM VerizonTowers,VerizonSwitchElementMap   WHERE VerizonSwitchElementMap.NetworkElementName = '$networkElementName' AND VerizonSwitchElementMap.SwitchName = VerizonTowers.SwitchName AND VerizonTowers.CellNumber = $lastServingCellSite AND VerizonTowers.Sector = '$lastServingCellFace'";
				if ($endTower = $link->query($endCellSiteQuery)) {
					if ($endTower->num_rows >0) {
						$row = $endTower->fetch_assoc(); 
						$endLatitude = $row['Latitude'];
						$endLongitude= $row['Longitude'];
						$endAzimuth = $row['Azimuth'];
					} else {

						echo "nothing was returned for the endquery: $endCellSiteQuery<BR>";
					}

				} else {
					echo "query didn't work: $endCellSiteQuery <BR>";
					die();
				} // checking to see the start tower
			}
				
//			echo "datetime: " . $line[4] . " latitude: $latitude | longitude $longitude | azimuth $azimuth <BR>"
    		// check to see if either of these phones are in the phone table - if not add them in.  
    		$phoneFromId = getPhoneID(stripPhoneNumber($callingPartyNumber));
    		$phoneToId = getPhoneID(stripPhoneNumber($dialedDigitNumber));
			// now put the phone calls in: 
    		$insertCallQuery = "INSERT INTO PhoneCalls (
    			CaseID, 
    			CallToPhoneID,
    			CallFromPhoneID,
    			DialedDigits,
    			MRNum,
    			StartDate,
    			Duration,
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
    			Created,
    			Modified
    		) VALUES (".
    			$GLOBALS['caseID']. ", 
    			$phoneToId, 
    			$phoneFromId, 
    			$dialedDigitNumber,
    			'$callDirection',
    			$SeizureDtTm,
    			$seizureDuration, 
    			$firstServingCellSite, 
    			$lastServingCellSite, 
    			$startLatitude, 
    			$startLongitude,
    			'$startAzimuth',
    			$endLatitude,
    			$endLongitude,
    			'$endAzimuth',
    			1,
    			'',
    			'$source',
    			".$GLOBALS['caseID'].",
    			NOW(),
    			NOW()
    			)";
//    			echo "INSERT CALL QUERY: $insertCallQuery <BR>";
//    			die();
  //  		echo "$insertCallQuery <BR>";
    		if (mysqli_query($link,$insertCallQuery)) {
    		} else {
    			echo "Couldn't insert: $insertCallQuery <BR>";
    			die();
    		}
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