<?php
	require 'config.php';
	if(isset($_POST) && !empty($_POST)){
		$_POST['editedBy'] = $_SESSION['u'];
		$db->insertQuery('welcomePage', $_POST);
		header('Location: welcome.php');
		exit();
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="shortcut icon" href="">
	<title>WELCOME TO Tate Publishing and Enterprises (Philippines), Inc.</title>
	
	<link href="css/yeti.bootstrap.min.css" rel="stylesheet">
	<link href="css/jumbotron.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
	<style type="text/css">
		.trHead{ color:#fff; font-weight:bold; background-color:#800000; }
	</style>
	
	<script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
	<script type="text/javascript">
	tinymce.init({
		selector: "textarea",	
		menubar : false,
		plugins: [
			"link",
			"code",
			"table"
		],
		toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code"
	});
	</script>
</head>
<body>
<div class="navbar navbar-inverse navbar-static-top">
	<div class="container">
		<div class="navbar-header" style="text-align:center; width:100%;">
			<span class="navbar-brand" style="font-weight: bold; width:100%;">WELCOME TO Tate Publishing and Enterprises (Philippines), Inc.</span>		
		</div>
	</div>
</div>
<div class="containerBody" style="width:750px; margin-left:auto; margin-right:auto;">
<?php		
	if( is_admin() ){
		echo '<center>';
		echo '<button class="btn btn-xs btn-primary" onClick="window.location.href=\'welcome.php?action=edit\'">Edit Contents</button><br/><br/>';
		
		if($_GET['action']=='edit'){
			echo '<form action="" method="POST">';
			echo '<textarea name="content" style="height:500px;">	';
		}
		echo '</center>';
	}

		echo stripslashes($db->selectSingleQuery('welcomePage', 'content' , '1 ORDER BY timestamp DESC'));
	
	if( is_admin() && $_GET['action']=='edit' ){
		echo '</textarea>';
		echo '<br/><button type="submit" class="btn btn-xs btn-primary">Submit</button>&nbsp;&nbsp;<button type="button" class="btn btn-xs btn-primary" onClick="window.location=\'welcome.php\'">Cancel</button>';
		echo '</form>';
	}
?>		
	
</div>
<hr/>
<footer>
	<p>&copy;<?php echo date('Y')." | ".COMPANY;?></p>
</footer>
</body>
</html>

