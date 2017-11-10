<?php 
// Phone Library
function errHandle($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errStr in $errFile on line $errLine";
    if ($errNo == E_NOTICE || $errNo == E_WARNING) {
        throw new ErrorException($msg, $errNo);
    } else {
        echo $msg;
    }
}

//used to change an empty value to null;
function emptyToNull($val) {
    if ($val == '') {
        return 'null';
    }
    return str_replace("\xE1", "a", str_replace("\xD1", "N", str_replace("\xF3", "o", str_replace("\xED", "i", str_replace("\xF1","n", str_replace("\xBE", "3/4", str_replace("\xBD", "1/2",str_replace("\x92","\'",str_replace("\xA0", ' ',$val)))))))));
}
function getSqlDate($date) {
//	echo "date: $date<BR>";
	if ($date =='') {
		return "NULL"; 
	} 
//	echo "date: $date<BR>";
	return "'".date_format($date, 'Y-m-d H:i:s' )."'";
}

ini_set('display_errors', 1);
ini_set('log_errors', 0);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');
set_time_limit(0);
ini_set("memory_limit","2400M");
ini_set("auto_detect_line_endings", true);
set_error_handler('errHandle');
// this code should be executed on every page/script load:
$link = mysqli_connect("localhost", "root", "sylvia", "matrix");
mysqli_set_charset($link, 'utf8');

?>