<?php
# script for parsing a phone Report into a usable format

// includes
//include 'library/vendor/autoload.php';
foreach(glob('../library/*.php') as $file) {
     include_once $file;
} 
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', '1');


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
foreach ($lines as $line_num => $line) {
	
	if (empty(trim($line))) {
		continue;
	}
	// first check to see if we are in a new type (SMS, user account, bookmark, etc.)

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
				die();
				break;
			}
			// check to see if the pdf is indicating a new source of the txt
			$pattern = '/\w+\s\([0-9]+\)/'; // SMS Header
			if (preg_match($pattern, $line, $matches)) {
				$SMSType = trim($matches[0]); // need to trim out the num in parens
				echo "SMS TYPE: $SMSType . <BR>";

			}
			$pattern = '/^\s*[0-9]+\s+(From|To)/'; //$smsRow = 1
			if (preg_match($pattern, $line)) { // this means that we are generating actual data on this line - a first line of data
				//getDelimitedFields($string,$fieldNum,$delimeter)
				$currText = array();  
				$delimiter = "\s{3,}"; 
				$currText['smsNumber'] = getDelimitedFields(trim($line), 0,$delimiter);
				$currText['direction'] = getDelimitedFields(trim($line), 1,$delimiter);
				$currText['date'] = getDelimitedFields(trim($line), 2,$delimiter);
				$currText['status ']= getDelimitedFields(trim($line), 4,$delimiter);
				$currText['message'] = getDelimitedFields(trim($line), 5,$delimiter);
				echo "direction: ". $currText['direction'] ."<BR>";
				$smsRow = 2;
			} elseif ($smsRow == 2) {
				$delimiter = "\s{3,}"; 
				$currText['phoneNumber'] = getDelimitedFields(trim($line), 0,$delimiter);
				$currText['time'] = getDelimitedFields(trim($line), 1,$delimiter);
				$currText['timeStamp'] = getDelimitedFields(trim($line), 2,$delimiter);
				$currText['message'] = $currText['message'] . ' ' . getDelimitedFields(trim($line), 3,$delimiter);
				$smsRow = 3;
			} elseif ($smsRow == 3) {
				// check to see if there is a name in there - 
				$delimiter = "\s{3,}"; 
				if (preg_match('/^[0-9]\)/', trim($line), $matches)) { // this means there is no contact name
					$currText['contactName'] = '';
					$currText['time'] = $currText['time'].getDelimitedFields(trim($line), 0, $delimiter);
					$currText['timeStamp'] = $currText['timeStamp']. ' ' . getDelimitedFields(trim($line),1, $delimiter);
					$currText['message'] = $currText['message'].' ' . getDelimitedFields(trim($line),2, $delimiter);
				} else {
					$currText['contactName'] = getDelimitedFields(trim($line), 0, $delimiter);
					$currText['time'] = $currText['time'].getDelimitedFields(trim($line), 1, $delimiter);
					$currText['timeStamp'] = $currText['timeStamp']. ' ' . getDelimitedFields(trim($line),2, $delimiter);
					$currText['message'] = $currText['message'].' ' . getDelimitedFields(trim($line),3, $delimiter);

				}
				print_r($matches);
				print_r($currText);
				$smsRow = 4;
			} elseif ($smsRow == 4) {
				$delimiter = "\s{3,}";
				$currText['$timeStamp'] = $currText['timeStamp']. getDelimitedFields(trim($line), 0, $delimiter);
				$currText['message'] = $currText['message'] . ' ' . getDelimitedFields(trim($line), 1, $delimiter);
			}
			break;	

	}
}



//file_put_contents($txtFile, $text);

?>