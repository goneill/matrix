<?php 
foreach(glob('../library/*.php') as $file) {
     include_once $file;
} 
// get the phone number we want from the URL

$phoneNumber = $_GET['number'];
echo "Phone Num: $phoneNumber<BR>";
$outFile = "../CHarris/CH_".$phoneNumber."_out.kml";

try {
	$outputFile = fopen($outFile, "w");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}

// first get some general info about the phone we are trying to get stuff from.  
$phoneInfoQuery = "SELECT Phones.ShortName, Phones.LongName, Phones.Icon From Phones where Phones.PhoneNumber = $phoneNumber";
echo $phoneInfoQuery . "<BR>";
if ($result = $link->query($phoneInfoQuery)) {
	while($row=$result->fetch_array()) {
		$prefix = $row['ShortName'];
		$icon = $row['Icon'];
	}
}

$query = "SELECT  PhoneTo.PhoneNumber as PhoneToPhoneNumber, PhoneTo.PhoneID as PhoneToPhoneID, PhoneTo.ShortName as PhoneToShortName, PhoneFrom.PhoneNumber as PhoneFromPhoneNumber, PhoneFrom.PhoneID as PhoneFromPhoneID, PhoneFrom.ShortName as PhoneFromShortName, Calls.PhoneCallID, Calls.StartDate, Calls.EndDate, Calls.Duration, Calls.FirstLatitude, Calls.FirstLongitude, Calls.Source FROM Phones as PhoneTo, Calls, Phones as PhoneFrom WHERE Calls.source = '$phoneNumber' AND PhoneTo.PhoneID = Calls.ToPhoneID AND PhoneFrom.PhoneID = Calls.FromPhoneID and Calls.FirstLatitude <> 0 ORDER BY StartDate";
echo $query;

if ($result = $link->query($query)) {
	while($row=$result->fetch_array()) {
		$rows[] = $row;
	}

	$outputLine =  '<?xml version="1.0" encoding="UTF-8"?>
	<kml xmlns="http://earth.google.com/kml/2.2">
	<Document>
	  <name>'.$prefix.'</name>
	  <Style id="pushpin">
	    <IconStyle>
	      <Icon>
	  	    <href>http://www.oandh.net/kml_icons/mobilephonedirectory/'.$icon.'</href>
	      </Icon>
	      <hotSpot x="32" y="1" xunits="pixels" yunits="pixels"/>
	    </IconStyle>
	  </Style>' ;

    echo $outputLine ."<BR>";
	fwrite($outputFile,$outputLine);
	if (isset($rows)) {
		foreach($rows as $row) {
			//combine shortName and phoneNumber
			if ($row['PhoneFromShortName']<>'') {
				$from = $row['PhoneFromShortName'];
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
			$startDate = $row['StartDate'];
			$name =  "$from TO $to: $startDate";
			$timestamp = $timestamp = date("Y-m-d",strtotime($startDate)) . 'T'.date("H:i:s",strtotime($startDate));
			$coordinates = "$firstLongitude,$firstLatitude,0";

			
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
		}
	} else {
		echo "there was no cellsite datat for $phoneNumber <BR>";
	}
	$footer = "	  </Document>
	</kml>";
	fwrite($outputFile, $footer);
} else {
	echo "stmt didnt work<BR>";
}

echo "FINISHED!";



?>