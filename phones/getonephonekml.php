<?php 
foreach(glob('../library/*.php') as $file) {
     include_once $file;
} 
// get the phone number we want from the URL

$phoneNumber = $_GET['number'];
echo "Phone Num: $phoneNumber<BR>";
$outFile = "../output/Kw_".$phoneNumber."_out.kml";

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

$query = "SELECT  PhoneTo.PhoneNumber as PhoneToPhoneNumber, PhoneTo.PhoneID as PhoneToPhoneID, PhoneTo.ShortName as PhoneToShortName, PhoneFrom.PhoneNumber as PhoneFromPhoneNumber, PhoneFrom.PhoneID as PhoneFromPhoneID, PhoneFrom.ShortName as PhoneFromShortName, Calls.PhoneCallID, Calls.StartDate, Calls.EndDate, Calls.Duration, Calls.FirstLatitude, Calls.FirstLongitude, Calls.Source, Calls.CallType FROM Phones as PhoneTo, Calls, Phones as PhoneFrom WHERE Calls.source = '$phoneNumber' AND PhoneTo.PhoneID = Calls.ToPhoneID AND PhoneFrom.PhoneID = Calls.FromPhoneID and Calls.FirstLatitude <> 0 ORDER BY StartDate";
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
			$coordinates = "$firstLongitude,$firstLatitude,0";
			$callType = $row['CallType'];
			$startDate = new DateTime($row['StartDate']);
			$strStartDate = $startDate->format('n/j/y H:i:s');
			$endDate = new datetime($row['EndDate']);
			$strEndDate = $endDate->format('n/j/y H:i:s');
			if ($callType =='Voice') {
				$name =  "Voice $from To $to $strStartDate - $strEndDate";
			} elseif ($callType == 'Data') {
				$name = "Data $from $strStartDate - $strEndDate";
			} else { // is SMS
				$name = "SMS $from To $to $strStartDate";
			}
//			Have to change the date to be fiver hours later because GE reads in as universal and converts to eastern
			$startDate = new datetime($row['StartDate']);
			$startDate->modify('5 hours');
			$endDate = new datetime($row['EndDate']);
			$endDate->modify('5 hours'); ;

			if ($startDate == $endDate) {

				$timestamp = $startDate->format("Y-m-d") . 'T'.$startDate->format("H:i:s");
				$timeString = 	"	<TimeStamp>
			    	    <when>$timestamp</when>
			    	  </TimeStamp>";

			} 
			else { // there is a duration for the call/data usage
				$begin = $startDate->format("Y-m-d") . 'T'.$startDate->format("H:i:s");
				$endDate = new datetime($row['StartDate']);
				$endDate->modify('5 hours'); ;

				$end = $endDate->format("Y-m-d") . 'T'.$endDate->format("H:i:s");
				$timeString = "<TimeSpan>
					<begin>$begin</begin>
					<end>$end</end>
					</TimeSpan>";
			} 
			
				$kmlLine = "
		   	<Placemark>
		  	  <name>$name</name>
			    	  <styleUrl>#pushpin</styleUrl>
			    	  $timeString
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