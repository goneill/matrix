<?php 
// parse the dck-act-full.csv - and put it into an array, then output to a workable csv
// should probably do this for the full htm instead also it is annoying that this is necessary.
// RIP Aaron Schwartz


foreach(glob('../library/*.php') as $file) {
     include_once $file;
} 
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$outname = 'dck-act-full-out.csv';
$htmlname = 'dck-act-full.html';
try {
	$outputFile = fopen($outname, "w+");
	$htmlFile = fopen($htmlname,"r");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}
$contents = file_get_contents($htmlname);
//create a DOM based off of the string from the html table
$DOM = new DOMDocument;
$DOM->loadHTML($contents);

//get all tr and td
$rows = $DOM->getElementsByTagName('tr');

$orders = array();
$order = array();

function getCaseNum($val) {
	$space = strpos($val, " ");
	return substr($val, 0,$space);
}
function getCaseCaption($val) {
	$firstSpace = strpos($val, " ");
	$caseClosed = strpos($val, "CASE CLOSED");
	if ($caseClosed> 0 ) {
		return trim(substr($val,$firstSpace, $caseClosed-$firstSpace));	
	} else {
		return trim(substr($val, $firstSpace));
	}

}
function getCaseStatus($val) {
	$caseClosed = strpos($val, "CASE CLOSED");
	if ($caseClosed>0) {
		return trim(substr($val, $caseClosed));
	} else {
		return "";
	}

}
function getEntered($val) {
	$filed = strpos($val, "Filed:");
//	echo "filed: $filed <BR>";
	return trim(substr($val, 9, $filed-9));
}
function getFiled($val) {
	$filed = strpos($val, "Filed:");
	return trim(substr($val, $filed + 6));
}
function getCategory($val) {
	$event = strpos($val, "Event:");
	return trim(substr($val, 9, $event-9));
}
function getEvent($val) {
	$event = strpos($val, "Event:");
	$document = strpos($val, "Document:");
	if ($document>0) {
		return trim(substr($val, $event+6, $document-$event-6));
	} else {
		return trim(substr($val, $event+6));
	}
}
function getDocument($val) {
	$document = strpos($val, "Document:");
	if ($document>0 ){
	return trim(substr($val, $document + 9));
	} else {
		return "";
	} 
}
function getDocType($val) {
	$type = strpos($val, "Type:");
	return trim(substr($val, $type+5));
}
function getOffice($val) {
	$presider = strpos($val, "Presider:");
	return trim(substr($val, 7, $presider-7));
}
function getPresider($val) {
	$presider = strpos($val, "Presider:");
	$caseFlags = strpos($val, "Case Flags:");
	if ($caseFlags>0 ){
		return trim(substr($val, $presider+9, $caseFlags-$presider-9));
	} else {
		return trim(substr($val, $presider+9));
	}
}
function getFlags($val) {
	$caseFlags = strpos($val, "Case Flags:");
	return trim(substr($val, $caseFlags+11));
}
foreach ($rows as $row) {
	// for each row we wat to add to the array of orders - 
	// if the rowspan = 2 then we have a new $order
	$cols = $row->getElementsByTagName('td');
	//echo $row->nodeValue . "<BR>";
	if($cols[1] && $cols[2]) {
// 		echo $cols[0]->nodeValue. " | " . $cols[1]->nodeValue. "<BR>";
 		$order['CaseNum'] = getCaseNum($cols[0]->nodeValue);
 		$order['CaseCaption'] = getCaseCaption($cols[0]->nodeValue);
// 		$order['CaseStatus'] = getCaseStatus($cols[0]->nodeValue);
// 		$order['Entered'] = getEntered($cols[1]->nodeValue);
		$order['Filed'] = getFiled($cols[1]->nodeValue);
 		$order['Category'] = getCategory($cols[2]->nodeValue);
 		$order['Event'] = getEvent($cols[2]->nodeValue);
 		$order['Document'] = getDocument($cols[2]->nodeValue);
// 		$order['Type'] = getDocType($cols[3]->nodeValue);
// 		$order['Office'] = getOffice($cols[4]->nodeValue);
 		try {
 			$order['Presider'] = getPresider($cols[4]->nodeValue);
 		} catch(exception $e) {
 				echo "Caught exception", $e->getMessage(), "<br>";

 		}
// 		$order['Flags'] = getFlags($cols[4]->nodeValue);
 		
 //		echo $order['CaseNum']. "|" . $order['CaseCaption']."|". $order['CaseStatus']. "|" . $order['Entered']."<BR>";
 //		echo $cols[1]->nodeValue. "|". $cols[2]->nodeValue. "|". $cols[3]->nodeValue. "|". $cols[4]->nodeValue."|<BR>";

 	} elseif ($cols[0]) {
 		if ($cols[0]->nodeValue == "Case number") {
 //			echo $row->nodeValue . "<BR>";
 			break;
 		}
 		$order['OrderText']  = str_replace(";", "-", $cols[0]->nodeValue);
 		$orders[] = $order;
 	} else {
 		$orders[] = $order;
 	}
}
//print_r($orders);
//        $order['caseNum'] = $cols[0];
//print_r($orders);
//fwrite($outputFile, '"Case Number";"Caption";"Filed";"Category";"Event";"Document";"Presider";"Text"'."\r\n");
foreach ($orders as $order) {
	//print_r($order);
	$ln = '"'.implode('";"', $order). '"'."\r\n";
	//	print_r($order);
	//	echo $ln. "<BR>";
	fwrite($outputFile, $ln);
}

//print_r($order);
die();
//	fwrite($outputFile,tdrows($cells)."\n");



?>