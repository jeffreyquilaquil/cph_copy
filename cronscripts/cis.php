#!/usr/bin/env php
<?php
	require_once '../classes/database.class.php';
	
	$dbname="tatecareerph_db";
	$user="root";
	$pass="seabiscuit";
	$host="localhost";
	$db = new database($dbname, $user, $pass, $host);
	
	$db->updateQuery('staffCIS', array('preparedby'=>'1'), 'cisID=2');
	
?>