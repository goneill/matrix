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
$query = "SELECT PhoneTo.PhoneNumber, PhoneTo.PhoneID, PhoneTo.ShortName, PhoneFrom.PhoneNumber, PhoneFrom.PhoneID, PhoneFrom.ShortName,PhoneCalls.CallToPhoneID, PhoneCalls.CallFromPhoneID, PhoneCalls.StartDate, PhoneCalls.EndDate, PhoneCalls.Duration, PhoneCalls.FirstLatitude, PhoneCalls.FirstLongitude From Phones as PhoneTo, PhoneCalls, Phones as PhoneFrom WHERE (PhoneTo.PhoneNumber='$phoneNumber' OR PhoneFrom.PhoneNumber = '$phoneNumber') AND PhoneTo.PhoneID = PhoneCalls.CallToPhoneID AND PhoneFrom.PhoneID = PhoneCalls.CallFromPhoneID ORDER BY StartDate";

if (!$result = mysqli_query($link, $query)) {
	die('Error: ' . mysqli_error($link));
}
$calls = $link->query($query);
$row = $calls->fetch_assoc();
if ($calls->num_rows === 0 ) {
	echo "nothing returned <BR> $query <BR>";
} else {
	$startLatitude = $row["Latitude"];
	$startLongitude = $row["Longitude"];
	$startAzimuth = 0;
}


?>