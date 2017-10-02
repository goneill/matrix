<?php 
// import MetroPCS Towers (These are actually TMobile)
foreach(glob('../library/*.php') as $file) {
     include_once $file;
}     

$inDirectory = "metroPCSPhoneTowers/";
$caseID = 1;
$serviceProviderID = getServiceProviderID("MetroPCS");


function insertTowers($towers) {

	global $link;

	$sqlInsert = "INSERT INTO MetroPCSTowers (SpbldromCode, Sector, MNC, LAC, Cell_ID, Orientation, HorizBW, Latitude, Longitude, Market, Region, Address, City, State, Zip, County,  Switch, MSC, BSC, CellName, IAP_System, Created, Modified) VALUES " . implode(',', $towers);
	echo "finished creating the sql stmt<BR>";

  //	echo $sqlInsert . "<BR>";
	if (!$link->query($sqlInsert)) {
  		printf("Error message: %s\n", $link->error);
  		print_r($sqlInsert);
  		die();
	}    
}


function addRecords($filename) {

	global $link;
	global $serviceProviderID;
	global $caseID;
	$towers = array(); 
	if (($handle = fopen($filename, "r")) !== FALSE) {
		echo $filename . "<BR>";
		$i=0;
		//put each line of the file into an array
	    while (($line = fgetcsv($handle,0,';')) !== FALSE) {
	    	$linestr = implode('',$line);
	    	if ($linestr==''|| trim($line['0']) == 'spbldrom_code') {
	    		echo "its an empty or header row!<BR>";
	    		echo $linestr . "<BR>";
	    		continue;
	    	}
	    	if (!(isset($line[12]))) {
	    		print_r($line);
	    		echo $linestr . "<BR>";
				die();
	    	}
		//	if ($i<=700000) { continue; }
	    	$tower['SpbldromCode'] = "'".mysqli_escape_string($link,$line[0])."'";
	    	$tower['Sector'] = "'".mysqli_escape_string($link,$line[1])."'";
	    	$tower['MNC']= $line[2]; 
	    	$tower['LAC']= $line[3]; 
	    	$tower['Cell_ID']= $line[4]; 
	    	$tower['Orientation']= $line[5]; 
	    	$tower['HorizBW']= $line[6]; 
	    	$tower['Latitude'] = $line[7];
	    	$tower['Longitude'] = $line[8];
	    	$tower['Market'] = "'".mysqli_escape_string($link,$line[9])."'";
	    	$tower['Region'] = "'".mysqli_escape_string($link,$line[10])."'";
	    	$tower['Address'] = "'".mysqli_escape_string($link,trim($line[11]))."'";
	    	$tower['City'] = "'".mysqli_escape_string($link,$line[12])."'";
	    	$tower['State'] = "'".mysqli_escape_string($link,$line[13])."'";
	    	$tower['Zip'] = "'".mysqli_escape_string($link,$line[14])."'";
	    	$tower['County'] = "'".mysqli_escape_string($link,$line[15])."'";
	    	$tower['Switch'] = "'".mysqli_escape_string($link,$line[16])."'";
	    	$tower['MSC'] = "'".mysqli_escape_string($link,$line[17])."'";
	    	$tower['BSC'] = "'".mysqli_escape_string($link,$line[18])."'";
	    	$tower['CellName'] = "'".mysqli_escape_string($link,$line[19])."'";
	    	$tower['IAP_System'] = "'".mysqli_escape_string($link,$line[20])."'";
			$tower['Created'] = 'NOW()';
			$tower['Modified'] = 'NOW()';
			$tower = array_map("emptyToNull", $tower);
			$towers[] = "(".implode(',',$tower).")";
			$i++;
	/*		if ($i>80043) { 
				print_r($tower);
				print_r(end($towers));
				echo "line: $linestr";
				$towers[] = "(". implode(',', $tower).")";
					break; 
			} */
		}
		echo "finished creating the array";
	    if (!empty($towers)) {
			insertTowers($towers);	    
		}	



		// hspbldrom_code;Sector;MNC;LAC;Cell_id;Orientation;horiz_bw;Lat_decimal;Lon_decimal;Market;Region;Address;City;State;Zip;County;Switch;MSC;BSC;Cell_Name;IAP_System
	}

}


$di = new RecursiveDirectoryIterator($inDirectory, FilesystemIterator::SKIP_DOTS);

// actually loop through the file; this could be shortened, but I think it makes sense for now to leave.  
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
	$basename = basename($filename);
	// need to check for duplicates!!
	addRecords($filename);
	echo "did it for $filename <BR>";


}
?>
