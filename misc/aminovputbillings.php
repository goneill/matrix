<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  
foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     
$link = mysqli_connect("localhost", "root", "sylvia123", "aminov");
$inFile = "../input/aminov/aminovbillings.txt";

function stripCurr($num) {
	return str_replace("$", "", str_replace(",", "", $num));
}

if (($handle = fopen($inFile, "r")) !== FALSE) {
	//put each line of the file into an array
	while (($line = fgetcsv($handle, 0,"\t")) !== FALSE) {
		$patientName = $line[0];
		$patientID = $line[1];
		$dateOfService = getSqlDate(new datetime($line[2]));
		$dateBilled = getSqlDate(new datetime($line[3])); 
		$amountBilled = $line[4];
		$amountPaid = $line[5];
		$description = $line[6];

		// first check the insurance company
		// get the insurance company id
//		print_r($line);
		$insertBilling = "INSERT INTO aminov.billings (PatientID, AmountBilled, AmountPaid, DateOfService, DateBilled, Description) VALUES ($patientID, $amountBilled, $amountPaid, $dateOfService, $dateBilled, '$description')";
		echo "insert billing: $insertBilling <BR>";
	
		mysqli_query($link,$insertBilling);
		
	}
}
?>