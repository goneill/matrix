<?php 
// Phone Library
function getServiceProviderID($serviceProvider) {
	global $link;
	echo "serviceProvider: $serviceProvider <BR>";
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
	$number = str_ireplace( array('(', ')', ' ', '-', '.','*','A','B','0111'), array('', '', '', '', '','','','',''), $number);
	if (strpos($number, '1') === 0) {
		$number = substr($number, 1);
	}
	if ($number == '') {$number = 0;}
	return $number;
}
function getPhoneID($phoneNumber) {
	global $link;
	//first check to see if the id is in the mysql database
	$phoneNumQuery = "SELECT * FROM PHONES where PhoneNumber = '$phoneNumber'";
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
		echo "sql query didn't work:<BR>$phoneNumQuery";
		die();
	}
}



function insertCalls($calls) {
	global $link;
	echo "about to insert the calls!<BR>";
	$sqlInsert = "INSERT INTO Calls (CaseID, ToPhoneID, FromPhoneID, DialedDigits, Direction, StartDate, EndDate, Duration, NetworkElement, Repoll, FirstCell, LastCell, FirstLatitude, FirstLongitude, LastLatitude, LastLongitude,  FirstCellDirection, LastCellDirection, Pertinent, Notes, Source, ServiceProviderID, CallType, Created, Modified) VALUES " . implode(',', $calls);
	echo "finished creating the sql stmt<BR>";

 // 	echo $sqlInsert . "<BR>";
	if (!$link->query($sqlInsert)) {
  		printf("Error message: %s\n", $link->error);
  		print_r($sqlInsert);
  		die();
	}    
 
}
?>