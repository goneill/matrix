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


// the smalot parser for pdf doens't work as well at keepign tables as this command line utility form a milion years ago; it is faster and better for layout
/*$execstring = "/usr/local/bin/pdftotext  -layout '$pdfFile' '$txtFile'";
echo "executing: " . $execstring . "\n<BR>";
system($execstring, $output);
*/
$status = 'FindSMS';
//get the SMS messages - this is hacky becuase its not finding the end using the structure of the file
$lines = file($txtFile);
foreach ($lines as $line_num => $line) {
	
	if (empty(trim($line))) {
		continue;
	}
	if ($line_num == 65889) {
		echo "Line: $line<BR>";
	}
	switch ($status) {
		case 'FindSMS':
		$pattern = '/\fSMS Messages /';
			if (preg_match($pattern, $line)) {
				
				echo "found the SMS: $line";

				$status = 'SMS';
			}
			break;
		case 'SMS':
			// check to see if the pdf is indicating a new source of the txt
			$pattern = '/\w+\s\([0-9]+\)/';
			if (preg_match($pattern, $line, $matches)) {
				$SMSType = trim($matches[0]); // need to trim out the num in parens
				echo "SMS TYPE: $SMSType . <BR>";
				die();
			}
			$pattern = '/^\s*[0-9]+\s+(From|To)/';
			if (preg_match($pattern, $line) { // this means that we are generating actual data on this line - a first line of data
				$contentSMSLine = 1
				$direction = substr($line, 8, )

			}
			break;	

	}
}



//file_put_contents($txtFile, $text);

?>