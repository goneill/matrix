<?php
# script for parsing a phone Report into a usable format

// includes
//include 'library/vendor/autoload.php';
foreach(glob('library/*.php') as $file) {
     include_once $file;
} 
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');

$outname = 'output/cellebritetexts.csv';
try {
	$outputFile = fopen($outname, "w");
} catch (Exception $e) {
	echo "Caught exception", $e->getMessage(), "<br>";
	die();
}

$pdfFile = "input/LGLS675Report.pdf";
$txtFile = "output/txtPhoneReport.txt";
echo "pdfFile: $pdfFile <BR> txtFile: $txtFile <BR>";
// create an array for all of the thigns that are in this specific report that you want to break into separate output documents (ie for import into xls) - in the future this would be tables in a database but alas we are not there yet!

function getDelimitedFields($string,$fieldNum,$delimeter) {
//	our test delimiter is "\s{3,}";
	$string = trim($string);
	$pattern = '/'.$delimeter.'/';
	preg_match_all($pattern, utf8_decode($string), $matches, PREG_OFFSET_CAPTURE);
    //$spaceLoc = $matches[0][$field-1][1];
      //return substr($string, 0, $spaceLoc); 
	if (array_key_exists($fieldNum,$matches[0])) {
	    if ($fieldNum == 0) { // the first field
	    	$field = substr($string, 0, $matches[0][0][1]);
	    } else { // all fields between first and last
		    $startOfField = $matches[0][$fieldNum-1][1];
		    $lengthOfField = $matches[0][$fieldNum][1]-$startOfField;
		    $field = substr($string, $startOfField,$lengthOfField );
	    }
	} elseif (array_key_exists($fieldNum-1, $matches[0])) { // the last field
		$field = substr($string, $matches[0][$fieldNum-1][1]);		# code...
	} else { // the field is blank
		$field = '';
	}

   
    // what is the deal with getting the first item before the 
    return trim($field);
}
// the smalot parser for pdf doens't work as well at keepign tables as this command line utility form a milion years ago; it is faster and better for layout
/*$execstring = "/usr/local/bin/pdftotext  -layout '$pdfFile' '$txtFile'";
echo "executing: " . $execstring . "\n<BR>";
system($execstring, $output);
*/
$status = 'FindSMS';
//get the SMS messages - this is hacky becuase its not finding the end using the structure of the file
$lines = file($txtFile);
$sms = array();
$smsRow = '';
$currText = array();
$texs = array();
$delimiter = "\s{2,}"; 
foreach ($lines as $line_num => $line) {
	
	if (empty(trim($line))) {
		continue;
	}
	// first check to see if we are in a new type (SMS, user account, bookmark, etc.)
	if ($status == 'UserAccounts') {
		break;
	}
	switch ($status) {
		case 'FindSMS':
		$pattern = '/\fSMS Messages /';
			if (preg_match($pattern, $line)) {
				
				echo "found the SMS: $line <BR>";

				$status = 'SMS';
			}
			break;
		case 'SMS':
			// first make sure we haven't left the arena of SMS
			$pattern ='/User Accounts /';
			if (preg_match($pattern, $line)) {
				echo "done with SMS: $line <BR>";
				$status = 'UserAccounts';
				$texts[] = $currText;

				// want to leave the for loop once we get here because we are only parsing the sms for now.
				break;
			}
			// check to see if the pdf is indicating a new source of the txt
			$pattern = '/\w+\s\([0-9]+\)/'; // SMS Header
			if (preg_match($pattern, $line, $matches)) {
				$SMSType = substr(trim($matches[0]), 0, strpos($matches[0], ' ')); // need to trim out the num in parens
				echo "SMS TYPE: $SMSType . <BR>";

			}
			$pattern = '/^\s*[0-9]+\s+(From|To)/'; //$smsRow = 1
			if (preg_match($pattern, $line)) { // this means that we are generating actual data on this line - a first line of data
				//getDelimitedFields($string,$fieldNum,$delimeter)
			
				$texts[] = $currText;
				$currText = array();  

				$currText['SMSType'] = $SMSType;
				$currText['smsNumber'] = str_replace("\f", '', trim(getDelimitedFields(trim($line), 0,$delimiter)));
				$currText['direction'] = getDelimitedFields(trim($line), 1,$delimiter);
				$currText['date'] = getDelimitedFields(trim($line), 2,$delimiter);
				$network = trim(getDelimitedFields(trim($line), 3,$delimiter));
				if ($network =="Network:") {
					$isNetwork = true;
					$currText['status']= getDelimitedFields(trim($line), 4,$delimiter);
					$currText['message'] = getDelimitedFields(trim($line), 5,$delimiter);
				} else { 
					$isNetwork = false;
					$currText['status']= getDelimitedFields(trim($line), 3,$delimiter);
					$currText['message'] = getDelimitedFields(trim($line), 4,$delimiter);
				}
				// echo "direction: ". $currText['direction'] ."<BR>";
				$smsRow = 2;
			} elseif ($smsRow == 2) {
				$currText['phoneNumber'] = stripPhoneNumber(getDelimitedFields(trim($line), 0,$delimiter));
				$currText['time'] = getDelimitedFields(trim($line), 1,$delimiter);
				if ($isNetwork) {
				$currText['timeStamp'] = getDelimitedFields(trim($line), 2,$delimiter);
				$currText['message'] = $currText['message'] . ' ' . getDelimitedFields(trim($line), 3,$delimiter);
				$currText['contactName'] = '';
				} else {
					$currText['timeStamp'] = '';
					$currText['message'] = $currText['message'] . ' ' . getDelimitedFields(trim($line), 2,$delimiter);
					$currText['contactName'] = '';
		
				} 
				$smsRow = 3;
			} elseif ($smsRow == 3) {
				// check to see if there is a name in there - 
				if (preg_match('/^[0-9]\)/', trim($line), $matches)) { // this means there is no contact name
					$currText['contactName'] = '';
					$currText['time'] = $currText['time'].getDelimitedFields(trim($line), 0, $delimiter);
					if ($isNetwork) {
					$currText['timeStamp'] = $currText['timeStamp']. ' ' . getDelimitedFields(trim($line),1, $delimiter);
					$currText['message'] = $currText['message'].' ' . getDelimitedFields(trim($line),2, $delimiter);
					} else {
						$currText['message'] = $currText['message'].' ' . getDelimitedFields(trim($line),1, $delimiter);
					}
				} else {
					$currText['contactName'] = getDelimitedFields(trim($line), 0, $delimiter);
					$currText['time'] = $currText['time'].getDelimitedFields(trim($line), 1, $delimiter);
					if ($isNetwork) {
						$currText['timeStamp'] = $currText['timeStamp']. ' ' . getDelimitedFields(trim($line),2, $delimiter);
						$currText['message'] = $currText['message'].' ' . getDelimitedFields(trim($line),3, $delimiter);
					} else {
						$currText['message'] = $currText['message'].' ' . getDelimitedFields(trim($line),2, $delimiter);
					} 
				}
				
				$smsRow = 4;
			} elseif ($smsRow == 4) {
				if ($isNetwork) {
					$currText['$timeStamp'] = $currText['timeStamp']. getDelimitedFields(trim($line), 0, $delimiter);
					$currText['message'] = $currText['message'] . ' ' . getDelimitedFields(trim($line), 1, $delimiter);
				} else {
						$currText['message'] = $currText['message'] . ' ' . getDelimitedFields(trim($line), 0, $delimiter);
				}
			}
			break;	

	}
}

// wnat to put the $texts array into a delimited structure

// terrell's number is: (347) 677-7532
$csvSMS = '"SMS Num", "DateTime", "From", "To", "Party", "Message", "Direction", "Date", "Status", "Number", "Time",  "SMS Type"';
foreach ($texts as $key => $currText) {

	if (empty($currText)){
		continue;
	}
	if ($currText['direction']=='To') {
		$from = '3476777532';
		$to = $currText['phoneNumber'];
	} else {
		$from = $currText['phoneNumber'];
		$to = '3476777532';
	}
	$timeStamp = $currText['date']. ' '. $currText['time'];
	$timeStampCut = strpos($timeStamp, '(');
	$timeStamp = substr($timeStamp, 0, $timeStampCut);

	$csvSMS.='"'. $currText['smsNumber'].'", "'. $timeStamp.'", "'.$from.'", "'.$to.'", "'. $currText['contactName']. '", "'. str_replace('"', "'", $currText['message']) . '", "' . $currText['direction'] . '", "' .  $currText['date'] . '", "' .  $currText['status'] . '", "' .  $currText['phoneNumber'] . '", "' .  $currText['time'] . '", "' .  $currText['SMSType'] . '", "'."\n";
	//$csvSMS .= '"'.implode('","', $currText) . '"'."\n";

	# code...
}
//echo $csvSMS;
		fwrite($outputFile,$csvSMS);

//file_put_contents($txtFile, $text);

?>