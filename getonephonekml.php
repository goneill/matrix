<?php 
foreach(glob('library/*.php') as $file) {
     include_once $file;
}     

// get the phone number we want from the URL
$phoneNumber = $_GET['number'];
echo "$phoneNumber<BR>";

try {
	$outputFile = fopen("output.kml", "w");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}

// we are taking things that don't include kml data

$query = "SELECT  PhoneTo.PhoneNumber as PhoneToPhoneNumber, PhoneTo.PhoneID as PhoneToPhoneID, PhoneTo.ShortName as PhoneToShortName, PhoneFrom.PhoneNumber as PhoneFromPhoneNumber, PhoneFrom.PhoneID as PhoneFromPhoneID, PhoneFrom.ShortName as PhoneFromShortName, PhoneCalls.PhoneCallID, PhoneCalls.StartDate, PhoneCalls.EndDate, PhoneCalls.Duration, PhoneCalls.FirstLatitude, PhoneCalls.FirstLongitude, PhoneCalls.Source, PhoneCalls.LastLatitude, PhoneCalls.LastLongitude FROM Phones as PhoneTo, PhoneCalls, Phones as PhoneFrom WHERE PhoneCalls.source = '$phoneNumber' AND PhoneTo.PhoneID = PhoneCalls.CallToPhoneID AND PhoneFrom.PhoneID = PhoneCalls.CallFromPhoneID ORDER BY StartDate";
echo $query;

if ($result = $link->query($query)) {
	while($row=$result->fetch_array()) {
		$rows[] = $row;
	}
   // $outputLine = "Start Date| End Date|Duration|To Number|To Short Name|From Phone Number|FromShortName|FirstLatitude|First Longitude|Last Latitude|Last Lontgitude|Source \n";
	$outLine .=  '<?xml version="1.0" encoding="UTF-8"?>
	<kml xmlns="http://earth.google.com/kml/2.2">
	<Document>
	  <name>' . $prefix.' ' .$num.'</name>
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
		
		$name = / get the name in kml format
			    	if ($items[4] == $OriginatingNumber) {
			    		$name = "$prefix To $items[5]: $timestampEST";
			    	} else {
			    		$name = "$prefix From $items[4]: $timestampEST";
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
		$source = $row['Source'];
		
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
				fwrite ($outFile, $kmlLine);
//	    $outputLine = "\"$startDate\"|$endDate|$duration|$toPhoneNumber|$toShortName|$fromPhoneNumber|$fromShortName|$firstLatitude|$firstLongitude|$source\n";
//	    echo $outputLine ."<BR>";
		fwrite($outputFile,$outputLine);

	
		
	}
	echo "in the if statement - no results...<BR>";
} else {
	echo "stmt didnt work<BR>";
}

echo "FINISHED!";



?>