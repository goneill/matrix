<?php 

// import sprint call data for phones with cellsite info
// need ot pu tthis as a source - like we need to say where these calls come from!  
foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     
$link = mysqli_connect("localhost", "root", "sylvia123", "aminov");

$inFile = "../input/aminov/patientnames.txt";
if (($handle = fopen($inFile, "r")) !== FALSE) {
	//put each line of the file into an array
	while (($line = fgetcsv($handle, 0,"\t")) !== FALSE) {
	//	print_r($line);

		$insuranceCompany = $line[2];
		$patientName = $line[0];
		$dateOfAccident =getSqlDate(new datetime($line[1]));
		echo "insurance company: $insuranceCompany <BR>";
		//first check to see if the insurance company  is in the mysql database
		$insuranceCoQuery = "SELECT * FROM InsuranceCompanies where Name = '$insuranceCompany'";
		mysqli_query($link,$insuranceCoQuery);
		if ($insuranceCompanyRecord = $link->query($insuranceCoQuery)) {
			if ($insuranceCompanyRecord->num_rows === 0) {
				echo "insurance company wasn't in database $insuranceCompany <BR>";
				// add the phone to the phones database;
				$insertInsuranceCompanyQuery = "INSERT INTO InsuranceCompanies (Name) Values ('$insuranceCompany')";
				echo "query: $insertInsuranceCompanyQuery <BR>";
				mysqli_query($link,$insertInsuranceCompanyQuery);
				$insuranceCoId = $link->insert_id;
			} else {
				$row = $insuranceCompanyRecord->fetch_assoc();
				$insuranceCoId = $row["id"];
			}
		} else {
			echo "sql query didn't work:<BR>$insuranceCoQuery";
			die();
		}

		//add in the patient record
		$patientInsert = "Insert INTO Patients (Name, InsuranceCompanyID, dateOfAccident) Values ('$patientName', $insuranceCoId, $dateOfAccident)";
		echo "patient insert query: $patientInsert <BR>";
		mysqli_query($link,$patientInsert);
		$patientId = $link->insert_id;
		
	}
}




?>
