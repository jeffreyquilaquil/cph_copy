<?php

	$t = microtime(true);
	$i=0;
	$dat = date('Y-m-d');
    while($i < 2000) {
       echo $dat;
	   $i++;
    }
	$xx =(microtime(true) - $t) * 1000000;
	echo '<br/>--------'.$xx.'<br/>';
	
	$c = microtime(true);
	$x=0;
    while($x < 2000) {
       echo date('Y-m-d');
	   $x++;
    }
	$xx2 =(microtime(true) - $c) * 1000000;
	echo '<br/>--------'.$xx2;


	//phpinfo();

	/* ini_set('display_errors', 1);
	error_reporting(E_ALL);


	//$dbName = "C:\Program Files (x86)\Att\att2000.mdb"; echo $dbName.'<br/>';
	$dbName = "att2000dv.mdb"; echo $dbName.'<br/>';
	if (!file_exists($dbName)) {
		die("Could not find database file.");
	} 
	//$db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$dbName; Uid=; Pwd=;");
	$db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=".realpath($dbName)); */


 

?>

