<?php 
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');
set_time_limit(0);
ini_set("memory_limit","2400M");
//this should be all you change, except if you have modifications to the isIncluded function
$link = mysqli_connect("localhost", "root", "nathando123", "matrix");
if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

// get the phone number we want from the URL

$phoneNumber = $_GET['number'];
echo "$phoneNumber<BR>";

try {
	$outputFile = fopen("output.csv", "w");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}

$query = "SELECT  PhoneTo.PhoneNumber as PhoneToPhoneNumber, PhoneTo.PhoneID as PhoneToPhoneID, PhoneTo.ShortName as PhoneToShortName, PhoneFrom.PhoneNumber as PhoneFromPhoneNumber, PhoneFrom.PhoneID as PhoneFromPhoneID, PhoneFrom.ShortName as PhoneFromShortName, PhoneCalls.PhoneCallID, PhoneCalls.StartDate, PhoneCalls.EndDate, PhoneCalls.Duration, PhoneCalls.FirstLatitude, PhoneCalls.FirstLongitude, PhoneCalls.Source FROM Phones as PhoneTo, PhoneCalls, Phones as PhoneFrom WHERE PhoneCalls.source = '$phoneNumber' AND PhoneTo.PhoneID = PhoneCalls.CallToPhoneID AND PhoneFrom.PhoneID = PhoneCalls.CallFromPhoneID ORDER BY StartDate";
echo $query;


//$query = "SELECT PhoneTo.PhoneNumber as PhoneToPhoneNumber, PhoneTo.PhoneID as PhoneToPhoneID, PhoneTo.ShortName as PhoneToShortName, PhoneFrom.PhoneNumber as PhoneFromPhoneNumber, PhoneFrom.PhoneID as PhoneFromPhoneID, PhoneFrom.ShortName as PhoneFromShortName, PhoneCalls.PhoneCallID, PhoneCalls.StartDate, PhoneCalls.EndDate, PhoneCalls.Duration, PhoneCalls.FirstLatitude, PhoneCalls.FirstLongitude From Phones as PhoneTo, PhoneCalls, Phones as PhoneFrom WHERE (PhoneTo.PhoneNumber='$phoneNumber' OR PhoneFrom.PhoneNumber = '$phoneNumber') AND PhoneTo.PhoneID = PhoneCalls.CallToPhoneID AND PhoneFrom.PhoneID = PhoneCalls.CallFromPhoneID ORDER BY StartDate";
if ($result = $link->query($query)) {
	while($row=$result->fetch_array()) {
		$rows[] = $row;
	}
	    $outputLine = "Start Date| End Date|Duration|To Number|To Short Name|From Phone Number|FromShortName|Latitude| Longitude|Source \n";
	    echo $outputLine ."<BR>";
		fwrite($outputFile,$outputLine);

	foreach($rows as $row) {

		$toPhoneNumber = $row["PhoneToPhoneNumber"];
		$toPhoneID = $row["PhoneToPhoneID"];
		$toShortName = $row["PhoneToShortName"];
		$fromPhoneNumber = $row['PhoneFromPhoneNumber'];
		$fromPhoneID = $row['PhoneFromPhoneID'];
		$fromShortName = $row['PhoneFromShortName'];
		$callID = $row['PhoneCallID'];
		$startDate = $row['StartDate'];
		$endDate = $row['EndDate'];
		$duration = $row['Duration'];
		$firstLatitude = $row['FirstLatitude'];
		$firstLongitude = $row['FirstLongitude'];
		$source = $row['Source'];
		
	    $outputLine = "\"$startDate\"|$endDate|$duration|$toPhoneNumber|$toShortName|$fromPhoneNumber|$fromShortName|$firstLatitude|$firstLongitude|$source\n";
//	    echo $outputLine ."<BR>";
		fwrite($outputFile,$outputLine);

	
		
	}
	echo "in the if statement - no results...<BR>";
} else {
	echo "stmt didnt work<BR>";
}

echo "FINISHED!";



?>