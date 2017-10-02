<?php
# script for parsing a phone Report into a usable format

#
$reportName = 



function transformPDFToText($main, $count=0){
	    $textdir = str_replace('pdf','txt',$main);
	    $olddir = '';
	    $dirHandle = opendir($main);
	    while($file = readdir($dirHandle)){
	    	echo "in the while loop";
			if (preg_match('/.pdf/',$file, $matches)) {
	            $textfile = str_replace('pdf','txt',$file);
	            $execstring = "/usr/local/bin/pdftotext  -layout '" .$main . $file ."' '$textdir$textfile'";
	            echo "executing: " . $execstring . "\n<BR>";
	           system($execstring, $output);
	            $count++;
	            echo "$count: filename: $file in $main \n<br />";
	        }
	    }
	    return $count;
	}

?>