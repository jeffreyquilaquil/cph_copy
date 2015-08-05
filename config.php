<?php
ini_set('session.gc_maxlifetime', 86400);
session_start();

date_default_timezone_set('Asia/Singapore');

function __autoload($class_name){
	require_once 'classes/'.$class_name.'.class.php';
}

$hosturl = $_SERVER['HTTP_HOST'];

$dbname="projectTracker";
$user="pt";
$pass="januaryRun&34";
$host="ptracker.clhfapw0bgm7.us-east-1.rds.amazonaws.com";
$ptDb = new database($dbname, $user, $pass, $host);

if($hosturl=='careerph.tatepublishing.net'){
	$dbname="careerph_prod";
	$user="cph_app01";
	$pass="UADyB5dVTs3k3zc7";
	$host="sql01.tatepublishing.net";
}else{
	$dbname="tatecareerph_db";
	$user="root";
	$pass="summer28Thing";
	$host="localhost";
}	
$db = new database($dbname, $user, $pass, $host);

$form = new formHelper();
$content = new content($db);
$settings = new setting($db);
$knowledgebase = new knowledgebase($db);

define("COMPANY","Tate Publishing Philippines");
define("TITLE",COMPANY." - Career Portal");
define("PROJECT","");

if($hosturl=='careerph.tatepublishing.net'){
	define("HOME_URL","https://careerph.tatepublishing.net/".PROJECT);
	define('DIR_DOWNLOAD', $_SERVER['DOCUMENT_ROOT'].'/'.PROJECT);
	define('HTTP_SERVER', 'https://'.$_SERVER['HTTP_HOST'].'/'.PROJECT);
	define('RECAPTCHA_PUBLIC_KEY', '6LcCEf8SAAAAAL2vBZjOw7cnsvKzOt-Cz3JX5YGg');
	define('RECAPTCHA_PRIVATE_KEY', '6LcCEf8SAAAAAJEqr_vkLRIE3vjylf8CiIvgxgQe');
}else{
	define("HOME_URL","http://129.3.252.99/".PROJECT);
	define('DIR_DOWNLOAD', $_SERVER['DOCUMENT_ROOT'].'/'.PROJECT);
	define('HTTP_SERVER', 'http://'.$_SERVER['HTTP_HOST'].'/'.PROJECT);
	define('RECAPTCHA_PUBLIC_KEY', '6LdUdAcTAAAAAJi7BeDbFmj6pj8ZCZUsIe4tJ_6N');
	define('RECAPTCHA_PRIVATE_KEY', '6LdUdAcTAAAAAE3gt6pg1fepFdxm4CjH2bMVgCCY');
}

$recaptcha = new recaptcha(RECAPTCHA_PUBLIC_KEY,RECAPTCHA_PRIVATE_KEY);

$error = array();
$update = array();

$result = $db->selectQuery("pt_users", "username");
if(is_array($result)){
	$_SESSION['authorized'] = array();
	foreach($result AS $v){
		$_SESSION['authorized'][] = $v['username'];
	}
}

if(isset($_SESSION['u'])){
	$userData = $db->selectSingleQueryArray('staffs', 'empID, username, fname, lname, email, position', 'username="'.$_SESSION['u'].'"');	
}

$authorized = $_SESSION['authorized'];
$current_page = strstr(basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']),".",true);

require 'includes/functions.php';

?>
