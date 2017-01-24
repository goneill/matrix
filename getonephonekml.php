<?php 
foreach(glob('library/*.php') as $file) {
     include_once $file;
}     

// get the phone number we want from the URL
$phoneNumber = $_GET['number'];
echo "$phoneNumber<BR>";
$filename = $phoneNumber."_out.kml";
try {
	$outputFile = fopen($filename, "w");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}
echo "$outputFile <BR>";
// we are taking things that don't include kml data
$query = "SELECT * FROM Phones WHERE PhoneNumber=$phoneNumber";
if($result = $link->query($query)) {
	while($row=$result->fetch_array()) {
			$icon = $row['Icon'];
			$prefix = $row['ShortName'];
			// get the last 4 characters of both the source and the shortname - this isn't done!
	}
}

$query = "SELECT Calls.PhonecallID, PhoneTo.PhoneNumber as PhoneToPhoneNumber, PhoneTo.PhoneID as PhoneToPhoneID, PhoneTo.ShortName as PhoneToShortName, PhoneFrom.PhoneNumber as PhoneFromPhoneNumber, PhoneFrom.PhoneID as PhoneFromPhoneID, PhoneFrom.ShortName as PhoneFromShortName, Calls.PhoneCallID, Calls.StartDate, Calls.EndDate, Calls.Duration, Calls.FirstLatitude, Calls.FirstLongitude, Calls.Source, Calls.LastLatitude, Calls.LastLongitude FROM Phones as PhoneTo, Calls, Phones as PhoneFrom WHERE Calls.source = '$phoneNumber' AND PhoneTo.PhoneID = Calls.ToPhoneID AND PhoneFrom.PhoneID = Calls.FromPhoneID AND Calls.FirstLatitude <> 0 ORDER BY PhoneCallID";
echo $query;

if ($rows = $link->query($query)) {

   // $outputLine = "Start Date| End Date|Duration|To Number|To Short Name|From Phone Number|FromShortName|FirstLatitude|First Longitude|Last Latitude|Last Lontgitude|Source \n";
	$outputLine =  '<?xml version="1.0" encoding="UTF-8"?>
	<kml xmlns="http://earth.google.com/kml/2.2">
	<Document>
	  <name>' . $prefix.'</name>
	  <Style id="pushpin">
	    <IconStyle>
	      <Icon>
	  	    <href>'.$icon.'</href>
	      </Icon>
	      <hotSpot x="32" y="1" xunits="pixels" yunits="pixels"/>
	    </IconStyle>
	  </Style>' ;

    echo $outputLine ."<BR>";
	fwrite($outputFile,$outputLine);

	foreach($rows as $row) {
		//combine shortName and phoneNumber
		if ($row['PhoneFromShortName']<>'') {
		//	$from = $row['PhoneFromShortName'];
		} else {
			$from = $row['PhoneFromPhoneNumber'];
		}
		if ($row['PhoneToShortName']<>'') {
			$to = $row['PhoneToShortName'];
		} else {
			$to = $row['PhoneToPhoneNumber'];
		}

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
		$lastLatitude = $row['LastLatitude'];
		$lastLongitude = $row['LastLongitude'];		

		$startDate = $row['StartDate'];
		$name =  "$from TO $to: $startDate";
		$timestamp = $timestamp = date("Y-m-d",strtotime($startDate)) . 'T'.date("H:i:s",strtotime($startDate));
		$coordinates = "$firstLatitude,$firstLongitude,0";

		
			$kmlLine = "
	   	<Placemark>
	  	  <name>$name</name>
		    	  <TimeStamp>
		    	    <when>$timestamp</when>
		    	  </TimeStamp>
		    	  <styleUrl>#pushpin</styleUrl>
		    	  <Point>
		       	 	<coordinates>$coordinates</coordinates>
		      	  </Point>
		        </Placemark>";
				fwrite ($outputFile, $kmlLine);
//	    $outputLine = "\"$startDate\"|$endDate|$duration|$toPhoneNumber|$toShortName|$fromPhoneNumber|$fromShortName|$firstLatitude|$firstLongitude|$source\n";
//	    echo $outputLine ."<BR>";
//		fwrite($outputFile,$outputLine);

	
//die();		
	}
	echo "in the if statement - no results...<BR>";
} else {
	echo "stmt didnt work<BR>";
}	fwrite ($outputFile, "
	  </Document>
	</kml>");
	echo "finished <BR>";
	fclose($outputFile);


?>