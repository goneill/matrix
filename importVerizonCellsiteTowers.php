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


$inDirectory = "verizonPhoneTowers/";

//******* FUNCTIONS ****//
function addRecords($filename) {

	$source = substr($filename, strpos($filename, "/")+1);
	$source = substr($source, 0, strrpos($source, "."));


	$link = mysqli_connect("localhost", "root", "nathando123", "matrix");
	if (!$link) {
	    echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    exit;
	}
	// check to see if this file has already been entered into the database
	
	$query = "Select * from VerizonTowerFiles WHERE FileName = '$source'";
	if (!$result = mysqli_query($link, $query)) {
		die('Error: ' . mysqli_error($link));
	}
	if(mysqli_num_rows($result) > 0){
	    echo "$source already in the database <BR>";
	    return;
	} else {
		echo "not in there yet: $source <BR>";
		$query = "INSERT INTO VerizonTowerFiles (FileName) VALUES ('$source')";
		if (! mysqli_query($link,$query)) {
			echo "query: $query <BR>";
			echo "MySQL Error: " .$link->error ; 
			die();
		} else {
			$sourceID = $link->insert_id;
		}

	}
	if (($handle = fopen($filename, "r")) !== FALSE) {
		fgets($handle);
		//put each line of the file in the database
		$i = 0;
	//	echo "in the loop able to open the files <BR>";
		while (($line = fgetcsv($handle)) !== FALSE) {

			if ($i==0) {
				//continue;
			}
			if ($line[0]=="# EOF") {
				return;
			}
			if ($line[0]=='' && $line[1]=='' && $line[2]=='') {
				continue;
			}
			$query = "INSERT INTO VerizonTowers (
				MarketSID,
				SwitchNumber,
				SwitchName,
				CellNumber,
				Latitude,
				Longitude,
				StreetAddress,
				City,
				State,
				ZIP,
				Sector,
				Technology,
				Azimuth,
				SourceId,
				Created,
				Modified
			) VALUES (
				'$line[0]',
				'$line[1]',
				'$line[2]',
				'$line[3]',
				$line[4],
				$line[5], 
				'".mysqli_real_escape_string($link, $line[6])."',
				'".mysqli_real_escape_string($link, $line[7])."',
				'$line[8]',
				'$line[9]',
				'$line[10]',
				'$line[11]',
				'$line[12]',
				$sourceID,
				now(),
				now()
			)";
			if (! mysqli_query($link,$query)) {
				echo "query: $query <BR>";
				echo "MySQL Error: " .$link->error ; 
				die();
			}
			$i++;
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