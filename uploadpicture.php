<?php
	require 'config.php';	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
	<link href="css/yeti.bootstrap.min.css" rel="stylesheet">	
	<link href="css/jumbotron.css" rel="stylesheet">  
	<script src="js/jquery.js"></script>
</head>
<body>
<div class="jumbotron">
	<div class="container">
		<fieldset>
			<legend>
				Upload Picture Taken
				<input class="btn btn-xs btn-danger" type="button" style="float:right;" value="<< Back to Recruitment Status" onClick="window.location='editstatus.php?id=<?= $_GET['id'] ?>'">
			</legend>			
		</fieldset>
		
		<a name='attach_file'></a>
		<?php 
			$dir = 'uploads/applicants/'.$_GET['id'].'/picture/';	
			if (!is_dir($dir)){
				mkdir($dir);         
			}
			
			$uploader = new uploader($dir, "Choose File...","Start Upload","Cancel Upload");
			$uploader->set_multiple(false);
			$uploader->uploader_html(); 			
		?>		
		<br/>
	</div>
	
</div>
</body>
</html>