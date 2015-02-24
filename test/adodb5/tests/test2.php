<?php

// BASIC ADO test
ini_set('display_errors', 1);
	error_reporting(E_ALL);
	include_once('../adodb.inc.php');

	$db = ADONewConnection("ado_access");
	$db->debug=1;
	//$access = 'C:\Program Files (x86)\Att\att2000.MDB';
	$access = '../../att2000dv.mdb';
	$myDSN =  'PROVIDER=Microsoft.Jet.OLEDB.4.0;'
		. 'DATA SOURCE=' . $access . ';';

	echo "<p>PHP ",PHP_VERSION,"</p>";

	//$db->Connect($myDSN) || die('fail');
echo 'fdfd'; exit;
	/* print_r($db->ServerInfo());

	try {
		$rs = $db->Execute("select * from USERINFO");
		
		
		$i = 0;
		foreach($rs as $k => $v) {
			$i += 1;
			echo $k[1].'<br/>'; adodb_pr($v);
			flush();
		}
		
		/* ECHO '<PRE>';
		//print_r($rs->fields);
		//print_r($rs);
		print_r($rs->fields);
		foreach($rs->fields AS $r):
			echo $r[1].'<br/>';
		endforeach; */
		
	/*} catch(exception $e) {
		print_r($e);
	//echo "<p> Date m/d/Y =",$db->UserDate($rs->fields[4],'m/d/Y');
	} */
