<?php
	require 'config.php';
	date_default_timezone_set("Asia/Manila");
	
	if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
		echo '<script>window.parent.location = "'.HOME_URL.'login.php";</script>';
		exit();
	} 
	
	if(!empty($_POST)){		
		if(isset($_POST['addreferral'])){
			$db->insertQuery('staffReferralBonus', array('empID_fk'=>$_POST['referrerID'], 'appID_fk'=>$_GET['id'], 'bonus'=>100, 'dateAdded'=>date('Y-m-d H:i:s')));
			unset($_POST['addreferral']);
		}
		$db->updateQuery('applicants', $_POST, 'id="'.$_GET['id'].'"');
		
		echo 'Applicant referrer successfully linked.';
		exit;
	}
	
	$name = '';
	if(isset($_GET['name'])) $name = $_GET['name'];
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">	
	<title>Link to employees account</title>
	<link href="css/yeti.bootstrap.min.css" rel="stylesheet">
	<script src="js/jquery.js"></script>
</head>
<body>
	<h3>Link to <?= $name ?>'s  Account</h3><hr/>
	<form action="" method="POST">
		Please select <?= $name ?>'s name if not selected then click "Submit".<br/>
		<select name="referrerID" class="form-control" required id="selectName">
			<option value=""></option>
		<?php
			$queryStaffs = $db->selectQueryArray('SELECT empID, CONCAT(fname," ",lname) AS name FROM staffs WHERE active=1 ORDER BY fname');
			foreach($queryStaffs AS $q){
				echo '<option value="'.$q['empID'].'" '.(($name==$q['name'])?'selected':'').'>'.$q['name'].'</option>';
			}
		?>
		</select>
	<?php if($_GET['process']>=3){ ?>
			<br/>Tick checkbox if adding referral bonus to <?= $name ?>:  <input type="checkbox" name="addreferral"/>
	<?php } ?>
		<br/><br/>
		<input type="hidden" name="source_field" id="source_field"/>
		<input type="submit" value="Submit" class="btn btn-primary"/>
	</form>
	
	<script type="text/javascript">
		$(function(){
			$('#selectName').change(function(){
				$('#source_field').val($("#selectName option:selected").text());
			});
		});
		
	</script>
</body>
</html>