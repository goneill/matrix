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
$link = mysqli_connect("localhost", "root", "", "matrix");

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


$inDirectory = "attPhoneRecords/";
$caseID = 1;
$ServiceProviderId = getServiceProviderID("AT&T"); 

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
	$number = preg_replace('/[A-Z|a-z]/', '', $number);
	$number = str_ireplace( array('(', ')', ' ', '-', '.','*',"A"), array('', '', '', '', '','',''), $number);
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
//	echo "date: $date<BR>";
	if ($date =='') {
		return "NULL"; 
	} 
//	echo "date: $date<BR>";
	return "'".date_format($date, 'Y-m-d H:i:s' )."'";
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
	if (!strpos($filename, '.txt')) {
		return;
	}

	$i = 0;
	// big concern we could have duplicates in here.  
	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";

		fgets($handle);
		//put each line of the file in the database
	    while (($line = fgetcsv($handle)) !== FALSE) {
			if ($typeStop = strpos($line[0], " Usage For:")) {
				// get the phone number out of this.  
				preg_match ('|[0-9]+|',stripPhoneNumber($line[0]), $matches);
				$source = $matches[0];
				echo "source name = $source <BR>";
				// get the type of call so we can figure out how to store it
				$type = trim(substr($line[0], 0, $typeStop));
				echo "type: $type <BR>"; 
				continue;
			}
			if (!isset($line[1])) {
				echo "not real: $line[0] <BR>";
				continue;
			} elseif (trim($line[1])== "ConnDateTime(UTC)") {
				continue;
			}
			if ($type == "Voice") {
				//continue;
			}
			//print_r($line);
/*
VOICE: Item,ConnDateTime(UTC),SeizureTime,ET,OriginatingNumber,TerminatingNumber,IMEI,IMSI,CT,Feature,DIALED,FORWARDED,TRANSLATED,ORIG_ORIG,MAKE,MODEL,CellLocation */		

     
			$item = $line[0];
			$connDateTimeUTC = getSqlDate(new datetime($line[1]));
//			echo "<BR> $connDateTimeUTC <BR>";
			$datetimeEST = getSqlDate(date_modify(new datetime($line[1]), '-5 hours'));
			if ($type == "Voice") {			
				$seizureDateTime = $line[2];
				$seizureDuration = $line[3];
				$callFromNum = getPhoneID(stripPhoneNumber($line[4]));
				$callToNum = getPhoneID(stripPhoneNumber($line[5]));
				$IMEI = $line[6];
				$imsi = $line[7];
				$ct = $line[8];
				$feature = $line[9];
				$dialedDigitNumber = $line[10];
				$forwarded = $line[11];
				$translated = $line[12];
				$orig_orig = $line[13];
				$make = $line[14];
				$model = $line[15];
				$startLatitude = 0;
				$startLongitude = 0; 
				$startAzimuth = '';
				$endLatitude = 0;
				$endLongitude = 0;
				$endAzimuth ='';
				if (isset($line[16]) AND $line[16] != "[]" AND $line[16]!="") {
									// [35034/21504:-73.9888519:40.7137453:330:-1.0,35034/12615:-73.9905:40.7101389:85:0.0]
					$cellLocationArray = explode(":", str_replace(array('[',']'), "", $line[16])); 	
				//	echo "cell Location array: <BR>";
				//	print_r($cellLocationArray);
				//	echo "<BR>";
					$startLatitude = $cellLocationArray[1];
					$startLongitude = $cellLocationArray[2];
					$startAzimuth = $cellLocationArray[3];
					$endLocationArray = explode(":", str_replace(array('[',']'), "", end($line))); 	
					$endLatitude = $endLocationArray[1];
					$endLongitude = $endLocationArray[2];
					$endAzimuth = $endLocationArray[3];
				}
	    		$insertCallQuery = "INSERT INTO PhoneCalls (
	    			CaseID, 
	    			CallToPhoneID,
	    			CallFromPhoneID,
	    			DialedDigits,
	    			StartDate,
	    			Duration,
	    			FirstLatitude,
	    			FirstLongitude,
	    			FirstCellDirection,
	    			LastLatitude,
	    			LastLongitude,
	    			LastCellDirection,
	    			Pertinent,
	    			Notes,
	    			Source,
	    			Created,
	    			Modified
	    		) VALUES (".
	    			$GLOBALS['caseID']. ", 
	    			$callToNum, 
	    			$callFromNum, 
	    			'$dialedDigitNumber',
	    			$datetimeEST,
	    			'$seizureDuration', 
	    			$startLatitude, 
	    			$startLongitude,
	    			'$startAzimuth',
	    			$endLatitude,
	    			$endLongitude,
	    			'$endAzimuth',
	    			1,
	    			'',
	    			'$source',
	    			NOW(),
	    			NOW()
	    			)";
 			} elseif ($type == "SMS") {
/*
SMS: 
Item,ConnDateTime(UTC),OriginatingNumber,TerminatingNumber,IMEI,IMSI,Desc,MAKE,MODEL,CellLocation
*/ 
				$callFromNum = getPhoneID(stripPhoneNumber($line[2]));
				$callToNum = getPhoneID(stripPhoneNumber($line[3]));
				$IMEI = $line[4];
				$imsi = $line[5];
				$desc = $line[6];
				$make = $line[7];
				$model = $line[8];
					$endLatitude = 0;
					$endLongitude = 0;
					$endAzimuth ='';
					$startLatitude = 0;
					$startLongitude = 0; 
					$startAzimuth = '';
				if (isset($line[9]) and $line[9]!="[]"and $line[9]!="") {
					$cellLocation = $line[9];
					// [35034/21504:-73.9888519:40.7137453:330:-1.0,35034/12615:-73.9905:40.7101389:85:0.0]
					$cellLocationArray = explode(":", str_replace(array('[',']'), "", $line[9])); 	
				//	echo "cell Location array: <BR>";
				//	print_r($cellLocationArray);
				//	echo "<BR>";
					$startLatitude = $cellLocationArray[1];
					$startLongitude = $cellLocationArray[2];
					$startAzimuth = $cellLocationArray[3];
				}
	    		$insertCallQuery = "INSERT INTO PhoneCalls (
	    			CaseID, 
	    			CallToPhoneID,
	    			CallFromPhoneID,
	    			StartDate,
	    			FirstLatitude,
	    			FirstLongitude,
	    			FirstCellDirection,
	    			LastLatitude,
	    			LastLongitude,
	    			LastCellDirection,
	    			Pertinent,
	    			Notes,
	    			Source,
	    			Created,
	    			Modified
	    		) VALUES (".
	    			$GLOBALS['caseID']. ", 
	    			$callToNum, 
	    			$callFromNum, 
	    			$datetimeEST,
	    			$startLatitude, 
	    			$startLongitude,
	    			'$startAzimuth',
	    			$endLatitude,
	    			$endLongitude,
	    			'$endAzimuth',
	    			1,
	    			'',
	    			'$source',
	    			NOW(),
	    			NOW()
	    			)";

 			} 
	 //  		echo "$insertCallQuery <BR>";
    		//	echo "Insert: $insertCallQuery <BR>";
    		// die();
    		if (mysqli_query($link,$insertCallQuery)) {
    		} else {
    			echo "Couldn't insert: $insertCallQuery <BR>";
    			print_r($line);
    			die();
    		}
    		$i++;
    	//	if ($i > 100) {die();}
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