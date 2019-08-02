<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  
foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     
$link = mysqli_connect("localhost", "root", "sylvia123", "aminov");
$inFile = "../input/aminov/master.txt";

function stripCurr($num) {
	return str_replace("$", "", str_replace(",", "", $num));
}

if (($handle = fopen($inFile, "r")) !== FALSE) {
	//put each line of the file into an array
	while (($line = fgetcsv($handle, 0,"\t")) !== FALSE) {


		$patientName = $line[1];
		$dateOfAccident = getSqlDate(new datetime($line[7]));
		$insuranceCo = $line[14];
		$status = $line[15];
		$totalAmountPaid = stripCurr($line[17]);
		$paidDuringConsp = stripCurr($line[18]);
		$govShowBilled = stripCurr($line[22]);
		$govShowPaid = stripCurr($line[23]);

		// first check the insurance company
		// get the insurance company id
		print_r($line);
		$selectInsuranceCompanyQuery =  "SELECT  insurancecompanies.id from aminov.insurancecompanies where name like '$insuranceCo'";
		echo "selectInsuranceCompanyQuery = $selectInsuranceCompanyQuery <BR>";
		$insuranceCompanyRecord = mysqli_query($link, $selectInsuranceCompanyQuery);
		if ($insuranceCompanyRecord->num_rows === 0) {
			echo "insurance company wasn't in database $insuranceCo <BR>";
			// add the phone to the phones database;
			$insertInsuranceCompanyQuery = "INSERT INTO InsuranceCompanies (Name) Values ('$insuranceCo')";
			echo "query: $insertInsuranceCompanyQuery <BR>";
			mysqli_query($link,$insertInsuranceCompanyQuery);
			$insuranceCoId = $link->insert_id;
		} else {
			$row = $insuranceCompanyRecord->fetch_assoc();
			$insuranceCoId = $row["id"];
		}



		$selectPatientQuery = "select Patients.id from aminov.patients where patients.name like '$patientName' and patients.InsuranceCompanyID = $insuranceCoId";

		echo $selectPatientQuery . "<BR>";
		$patient = mysqli_query($link,$selectPatientQuery);

		// patient isn't in the database yet
		if ($patient->num_rows === 0) {
			echo "patient/insurance co wasn't in database $patientName | $insuranceCo<BR>";
			$patientInsert = "Insert INTO Patients (Name, InsuranceCompanyID, dateOfAccident) Values ('$patientName', $insuranceCoId, $dateOfAccident)";
			echo "patient insert query: $patientInsert <BR>";
			mysqli_query($link,$patientInsert);
			$patientId = $link->insert_id;

		} else {
			$row = $patient->fetch_assoc();
			$patientId = $row["id"];
		}
			$updatePatientQuery = "update patients set status='$status', totalpaid='$totalAmountPaid', paidDuringConsp='$paidDuringConsp', govShowPaid='$govShowPaid', govShowBilled='$govShowPaid' where patients.id=$patientId";
			echo "$updatePatientQuery <BR>";
			mysqli_query($link,$updatePatientQuery);
			
	}
}
?>