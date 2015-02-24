<?php

/*
 * Author: Ramazan APAYDIN
 * Website: http://ramazanapaydin.com
 * Versiyon: 1.0
*/

error_reporting(0);
header('Content-type: application/json');

$username = $_GET['username'];
if ($_GET['page']) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

$getUrl = "http://pinterestapi.co.uk/".$username."/pins?page=".$page;
$results = file_get_contents($getUrl);
echo $results;

?>
