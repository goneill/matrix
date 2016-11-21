<?php 
// import sprint cellsite data
// mysql connection 
ini_set('display_errors', 1);
ini_set('log_errors', 0);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');
set_time_limit(0);
ini_set("memory_limit","2400M");
ini_set("auto_detect_line_endings", true);
foreach(glob('Includes/*.php') as $file) {
     include_once $file;
}     

function errHandle($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errStr in $errFile on line $errLine";
    if ($errNo == E_NOTICE || $errNo == E_WARNING) {
        throw new ErrorException($msg, $errNo);
    } else {
        echo $msg;
    }
}

set_error_handler('errHandle');


$inDirectory = "sprintRecords/";

//******* FUNCTIONS ****//
function addRecords($filename) {

	$link = mysqli_connect("localhost", "root", "", "matrix");
	if (!$link) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}

	if (($handle = fopen($filename, "r")) !== FALSE) {
		fgets($handle);
		//put each line of the file in the database
		while (($line = fgetcsv($handle)) !== FALSE) {


			$query = "INSERT INTO SprintTowers (
				CellNum,
				CascadeID,
				Switch,
				NEID,
				Repoll,
				Latitude,
				Longitude,
				BTSManufacturer,
				Sector,
				Azimuth,
				CDRStatus,
				Created,
				Modified
			) VALUES (

			$line[0] ,
			'$line[1]' ,
			'$line[2]',
			$line[3] ,
			$line[4] ,
			$line[5] , 
			$line[6] ,
			'$line[7]' ,
			$line[8],
			$line[9],
			'$line[10]',

			now(),
			now()
			)";
			mysqli_query($link,$query);
		}
	}

	$link->close();

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