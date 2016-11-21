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


$inDirectory = "sprintPhoneRecords/";
$caseID = 1;
$ServiceProviderId = 1; 

function stripPhoneNumber ($number) {
	$number = str_ireplace( array('(', ')', ' ', '-', '.'), array('', '', '', '', ''), $number);
	if (strpos($number, '1') === 0) {
		substr($number, 1);
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
		echo "sql query didn't work! ";
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
	global $link;
	if (!$link) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}
	preg_match ('|[0-9]+|',$filename, $matches);
	$source = $matches[0];
	echo "source name = $source";

	$i=1; 
	// big concern we could have duplicates in here.  
	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";
//		fgets($handle);
		//put each line of the file in the database
	    while (($line = fgetcsv($handle)) !== FALSE) {
//			echo $line[0] . ", " . $line[1] . ", " . $line[2] . "<BR>";			
			$mrNum = $line[3];
			$callToNum = $line[1];
			$callFromNum = $line[0];
			$startDate = getSqlDate($line[4]);
			$endDate = getSqlDate($line[5]);
			// if 9&10 are not 0 then there is cellsite data included
			if (!($line[9] == 0 AND $line[10] == 0)) {
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
			$phoneFromId = getPhoneID(stripPhoneNumber($line[1]));
			$phoneToId = getPhoneID(stripPhoneNumber($line[0]));	
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
    			Created,
    			Modified
    		) VALUES (".
    			$GLOBALS['caseID']. ", 
    			$phoneToId, 
    			$phoneFromId, 
    			'$line[2]',
    			'$line[3]',
    			$startDate,
    			$endDate, 
    			$line[6], 
    			$line[7], 
    			$line[8], 
    			$line[9], 
    			$line[10],
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
//    			echo "INSERT CALL QUERY: $insertCallQuery <BR>";
//    			die();
  //  		echo "$insertCallQuery <BR>";
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