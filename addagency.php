<?php
	require 'config.php';
	date_default_timezone_set("Asia/Manila");
	
	if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
		echo '<script>window.parent.location = "'.HOME_URL.'login.php";</script>';
		exit();
	} 
	
	if(!empty($_POST)){		
		$_POST['addedBy'] = $_SESSION['u'];
		$_POST['dateAdded'] = date('Y-m-d H:i:s');
		$db->insertQuery('agencies', $_POST);
		echo '<p style="color:red;">Agency '.$_POST['agencyName'].' added.</p>';
		exit;
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">	
	<title>Add Agency</title>
	<link href="css/yeti.bootstrap.min.css" rel="stylesheet">
	<script src="js/jquery.js"></script>
</head>
<body>
	<h2>Add Agency</h2>
	<form action="" method="POST">
	<table width="80%">
		<tr><td width="20%">Agency Name</td><td><input type="text" name="agencyName" class="form-control" required/></td></tr>
		<tr><td>Contact Person</td><td><input type="text" name="contactPerson" class="form-control" required/></td></tr>
		<tr><td>Email Address</td><td><input type="email" name="contactEmail" class="form-control" required/></td></tr>
		<tr><td>Contact Numbers</td><td><input type="text" name="contactNos" class="form-control" required/></td></tr>
		<tr><td><br/></td><td><input type="submit" value="Submit" class="btn btn-primary"/></td></tr>
	</table>
	</form>
</body>
</html>