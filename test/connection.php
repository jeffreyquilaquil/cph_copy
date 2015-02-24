<?php
//$dbName = 'C:\Program Files (x86)\Att\att2000.mdb';
//$dbName = $_SERVER["DOCUMENT_ROOT"] . "products\products.mdb";
$dbName = 'C:\Program Files (x86)\Att\att2000.mdb';
if (!file_exists($dbName)) {
    die("Could not find database file.");
}
$db = new PDO("odbc:DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=$dbName; Uid=; Pwd=;");

?>