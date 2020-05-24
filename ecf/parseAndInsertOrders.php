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
$judges= array();
// get the judges into an array so we can keep them relational style in the db
$judgesQuery = "SELECT judges.Judge_ID, Judges.JudgeName from judges";
if ($judgesRes = mysqli_query($link,$judgesQuery)) {
	while($row = mysqli_fetch_assoc($judgesRes)) {
		$judges[$row['JudgeName']] = $row['Judge_ID'];
	}
} 	


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
	return trim(substr($val, $filed + 6,10));
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
		$doctext = trim(substr($val, $document + 9));
		$doctext = trim(html_entity_decode($doctext));	
		$doctext = substr($doctext, 0, -4);

		return $doctext;
	} else {
		return "0";
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

$insertOrders = "INSERT INTO Orders (CaseNumber, CaseCaption, CaseStatus, Entered, Filed, Category, Event, Document, Type, Office, Judge_ID, CaseFlags, OrderText) VALUES ";


function getJudgeID($presider) {
	GLOBAL $judges;
	Global $link;
	if (isset($judges[$presider])){
		return $judges[$presider];
	} else {
		$qry = "INSERT INTO JUDGES (JudgeName) VALUES ('$presider')";
echo $qry . "<BR>";
		mysqli_query($link, $qry);
		$id =  mysqli_insert_id($link);
		$judges[$presider] = $id;
		return $id;
	}

}
foreach ($rows as $row) {
	// for each row we want to add to the array of orders 

	$cols = $row->getElementsByTagName('td');
	
	// this is to make sure we don't get the weird tables at the end.
	if($cols[1] && $cols[2]) {
// 		echo $cols[0]->nodeValue. " | " . $cols[1]->nodeValue. "<BR>";
 		$order['CaseNum'] = getCaseNum($cols[0]->nodeValue);
 		$order['CaseCaption'] = getCaseCaption($cols[0]->nodeValue);
 		$order['CaseStatus'] = getCaseStatus($cols[0]->nodeValue);
 		$order['Entered'] = getEntered($cols[1]->nodeValue);
		$order['Filed'] = getFiled($cols[1]->nodeValue);
 		$order['Category'] = getCategory($cols[2]->nodeValue);
 		$order['Event'] = getEvent($cols[2]->nodeValue);
 		$order['Document'] = TRIM(getDocument($cols[2]->nodeValue));
 		$order['Type'] = getDocType($cols[3]->nodeValue);
 		$order['Office'] = getOffice($cols[4]->nodeValue);
		$order['Flags'] = getFlags($cols[4]->nodeValue);
 		try {
 			$order['Presider'] = getPresider($cols[4]->nodeValue);
 		} catch(exception $e) {
 				echo "Caught exception", $e->getMessage(), "<br>";

 		}
 		
 //		echo $order['CaseNum']. "|" . $order['CaseCaption']."|". $order['CaseStatus']. "|" . $order['Entered']."<BR>";
 //		echo $cols[1]->nodeValue. "|". $cols[2]->nodeValue. "|". $cols[3]->nodeValue. "|". $cols[4]->nodeValue."|<BR>";

 	// this gets rid of the tables at the end. 
 	} elseif ($cols[0]) {
 		if ($cols[0]->nodeValue == "Case number") {
 //			echo $row->nodeValue . "<BR>";
 			break;
 		} // there is no 3rd column so its order text - add that to the order and then 
// 		echo "Document:|".$order['Document']."|<BR>";
 		$order['OrderText']  = mysqli_escape_string($link, $cols[0]->nodeValue);
 		$orders[] = $order;
 		$insertOrders.="('".$order['CaseNum']."', '".
 			mysqli_escape_string($link,$order['CaseCaption'])."', '" .
 			//mysqli_escape_string($link,$order['CaseStatus'])."', ".
 			getSqlDate(new datetime($order['Entered'])).",".
 			//getSqlDate(new datetime($order['Filed'])).",'".
 			$order['Category']."','".
 			$order['Event']."',"
 			.trim($order['Document']).",'".
 			$order['Type']."','".
 			//$order['Office']."',".
 			//getJudgeID($order['Presider'], $judges).",'".
 			$order['Presider']."',".
// 			$order['Flags']."',".
 			'"'.$order['OrderText'].'"),';
// 			die();

 	} else { // this means there is only one cell with a colspan of 4 - ie 
 		$orders[] = $order;
 		$insertOrders.="('".$order['CaseNum']."', '".
 			mysqli_escape_string($link,$order['CaseCaption'])."', '" .
 			mysqli_escape_string($link,$order['CaseStatus'])."', ".
 			getSqlDate(new datetime($order['Entered'])).",".
 			getSqlDate(new datetime($order['Filed'])).",'".
 			$order['Category']."','".
 			$order['Event']."',".
 			trim($order['Document']).",'".
 			$order['Type']."','".
 			$order['Office']."',".
 			getJudgeID($order['Presider'], $judges).",'".
 			$order['Flags']."',".
 			'"'.$order['OrderText'].'"),';
//
 	}
}

//print_r($orders);
//        $order['caseNum'] = $cols[0];
//print_r($orders);
//fwrite($outputFile, '"Case Number";"Caption";"Filed";"Category";"Event";"Document";"Presider";"Text"'."\r\n");
/*foreach ($orders as $order) {
	//print_r($order);
	$ln = '"'.implode('";"', $order). '"'."\r\n";
	//	print_r($order);
	//	echo $ln. "<BR>";
	fwrite($outputFile, $ln);
	// need to create a sql statement here to put these in the db...


} */
$insertOrders = substr($insertOrders, 0, -1);
echo $insertOrders . "<BR>";

//	mysqli_query($link, $insertOrders);
$link->query($insertOrders);
//echo "<BR> GAH DO NOT KNOW WHAT WENT WRONG<BR>";
echo mysqli_errno($link) . ": " . mysqli_error($link) . "<BR>";

$setCompassionate = "UPDATE ORDERS SET CompassionateRelease =1 WHERE OrderText LIKE '%Compassionate%' or OrderText LIKE '%COVID-19%' OR OrderText LIKE '%3582%';";
mysqli_query($link, $setCompassionate);
$setSentencing= "UPDATE ORDERS SEt Sentencing =1 WHERE Event LIKE 'JUDGMENT%';";
mysqli_query($link, $setSentencing);
//print_r($order);
//	fwrite($outputFile,tdrows($cells)."\n");



?>