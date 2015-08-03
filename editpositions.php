<?php
	require 'config.php';
	require_once('includes/labels.php');
	date_default_timezone_set("Asia/Manila");
		
	if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
		echo '<script>window.parent.location = "'.HOME_URL.'login.php";</script>';
		exit();
	} 	
	
	if(isset($_GET['cur']) && isset($_GET['n'])){			
		$typeQ = $db->selectQueryArray('SELECT DISTINCT '.$_GET['n'].' AS tP FROM newPositions WHERE '.$_GET['cur'].' = "'.$_POST['typeVal'].'"');
			
		echo '<option value=""></option>';
		foreach($typeQ AS $t):
			echo '<option value="'.$t['tP'].'">'.$t['tP'].'</option>';
		endforeach;
		
		exit;
	}
	
	if(isset($_POST) && !empty($_POST)){
		$rtest = '';
		$rskills = '';
		$cntLaos = count($_POST['requiredTest']);
		for($i=0; $i<$cntLaos; $i++){
			$rtest .= $_POST['requiredTest'][$i].',';
		}
		$cntLaos2 = count($_POST['requiredSkills']);
		for($j=0; $j<$cntLaos2; $j++){
			$rskills .= $_POST['requiredSkills'][$j].'|';
		}
	
		$upArr = array(
			'title' => $_POST['title'],
			'active' => $_POST['active'],
			'orgLevel_fk' => $_POST['orgLevel'],
			'org' => $_POST['org'],
			'dept' => $_POST['dept'],
			'grp' => $_POST['grp'],
			'subgrp' => $_POST['subgrp'],
			'requiredTest' => rtrim($rtest, ','),
			'requiredSkills' => rtrim($rskills, '|'),
			'user' => $_SESSION['u'],
			'desc' => addslashes($_POST['desc'])			
		);
		$db->updateQuery('newPositions', $upArr, 'posID='.$_POST['position']);
		$updated = true;
	}
	
	$pos = $db->selectSingleQueryArray('newPositions', '*', 'posID="'.$_GET['posID'].'"');
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">	
	<title>Edit <?= $pos['title'] ?></title>
	<link href="css/yeti.bootstrap.min.css" rel="stylesheet">	
	<script src="js/jquery.js"></script>	
</head>
<body>
<?php
if(isset($_GET['desc']) && $_GET['desc']=='yes'){
	echo '<button onClick="location.href=\'/editpositions.php?posID='.$_GET['posID'].'\'" class="btn-xs btn-primary">Edit '.ucwords($pos['title']).' Details</button><hr/>';
	echo '<p><b>Description of '.$pos['title'].':</b></p>';
	if($pos['desc']=='') echo '<p>None.</p>';
	else echo nl2br($pos['desc']);
	
	echo '<hr/><p><b>Required Skills of '.$pos['title'].':</b></p><hr/>';
	if($pos['requiredSkills']==''){
		echo '<p>None.</p>';
	}else{
		$rskillsArr = explode('|', $pos['requiredSkills']);	
		$sQuery = $db->selectQueryArray('SELECT * FROM applicantSkills');
		$sArr = array();
		foreach($sQuery AS $q):
			$sArr[$q['skillID']] = $q['skillName'];
		endforeach;
	
		echo '<ul>';
		$cntMalay = count($rskillsArr);
		for($k=0; $k<$cntMalay; $k++){
			echo '<li>'.$sArr[$rskillsArr[$k]].'</li>';
		}
		echo '</ul>';
	}	
}else{
?>

<h3>Edit <?= $pos['title'] ?></h3>
<hr/>
<?php
	if(isset($updated) && $updated == true){
		echo 'Position has been updated.';
	}else{
?>
<form action="" method="POST" onSubmit="return validateForm();">
<table width="80%" cellspacing=10 cellpadding=10 align="center">				
	<tr bgcolor="#dcdcdc">
		<td width="30%">Position ID</td>
		<td><input type="text" name="position" value="<?= $pos['posID'] ?>" class="form-control" readonly /></td>
	</tr>
	<tr>
		<td>Title</td>
		<td><input type="text" name="title" id="title" value="<?= $pos['title'] ?>" class="form-control" /></td>
	</tr>
	<tr bgcolor="#dcdcdc">
		<td>Active</td>
		<td>
			<select name="active" id="active" class="form-control">
				<option value="1">Yes</option>
				<option value="0">No</option>
			</select>
		</td>		
	</tr>
	<tr>
		<td>Organization Level</td>
		<td>
			<select name="orgLevel" class="form-control">
		<?php
			$orgLevel = $db->selectQueryArray('SELECT * FROM orgLevel');
			foreach($orgLevel AS $o):
				echo '<option value="'.$o['levelID'].'" '.(($o['levelID']==$pos['orgLevel'])?'selected':'').'>'.$o['levelName'].'</option>';
			endforeach;
		?>
			</select>
		</td>		
	</tr>
	<tr bgcolor="#dcdcdc">
		<td>Organization</td>
		<td>
		<?php
			$org = $db->selectQueryArray('SELECT DISTINCT org FROM newPositions');
		?>
			<select class="form-control" name="org" id="org" onChange="getOrgVal('org', 'dept')">
				<?php
					foreach($org AS $o):
						echo '<option value="'.$o['org'].'" '.(($pos['org']==$o['org'])?'selected':'').'>'.$o['org'].'</option>';
					endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr id="depttr">
		<td>Department</td>
		<td>
		<?php
			$dept = $db->selectQueryArray('SELECT DISTINCT dept FROM newPositions WHERE org="'.$pos['org'].'"');
		?>
			<select class="form-control" name="dept" id="dept" onChange="getOrgVal('dept', 'grp')">
				<option value=""></option>
				<?php
					foreach($dept AS $d):
						echo '<option value="'.$d['dept'].'" '.(($pos['dept']==$d['dept'])?'selected':'').'>'.$d['dept'].'</option>';
					endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr bgcolor="#dcdcdc" id="grptr">
		<td>Group</td>
		<td>
		<?php
			$grp = $db->selectQueryArray('SELECT DISTINCT grp FROM newPositions WHERE org="'.$pos['org'].'" AND dept="'.$pos['dept'].'"');
		?>
			<select class="form-control" name="grp" id="grp" onChange="getOrgVal('grp', 'subgrp')">
				<option value=""></option>
				<?php
					foreach($grp AS $g):
						echo '<option value="'.$g['grp'].'" '.(($pos['grp']==$g['grp'])?'selected':'').'>'.$g['grp'].'</option>';
					endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr id="subgrptr">
		<td>Subgroup</td>
		<td>
		<?php
			$subgrp = $db->selectQueryArray('SELECT DISTINCT subgrp FROM newPositions WHERE org="'.$pos['org'].'" AND dept="'.$pos['dept'].'" AND grp="'.$pos['grp'].'"');
		?>
			<select class="form-control" name="subgrp" id="subgrp">
				<option value=""></option>
				<?php
					foreach($subgrp AS $s):
						echo '<option value="'.$s['subgrp'].'" '.(($pos['subgrp']==$s['subgrp'])?'selected':'').'>'.$s['subgrp'].'</option>';
					endforeach;
				?>
			</select>
		</td>
	</tr>
	<tr bgcolor="#dcdcdc">
		<td>Required Test<br/>
			<i style="color:#aaa">IQ, Typing and Written Test are also required</i>
		</td>
		<td>
			<table width="100%">
				<tr>
					<td width="50%">
					<?php
						echo '<input type="checkbox" value="pmEmail" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'pmEmail')!== false)?'checked':'').'> '.$processLabels['pmEmail'].'<br/>';
						echo '<input type="checkbox" value="pressRelease" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'pressRelease')!== false)?'checked':'').'> '.$processLabels['pressRelease'].'<br/>';
						echo '<input type="checkbox" value="design" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'design')!== false)?'checked':'').'> '.$processLabels['design'].'<br/>';
						echo '<input type="checkbox" value="editing" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'editing')!== false)?'checked':'').'> '.$processLabels['editing'].'<br/>';
						echo '<input type="checkbox" value="editingTest" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'editingTest')!== false)?'checked':'').'> '.$processLabels['editingTest'].'<br/>';
						echo '<input type="checkbox" value="it" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'it')!== false)?'checked':'').'> '.$processLabels['it'].'<br/>';
						echo '<input type="checkbox" value="sales" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'sales')!== false)?'checked':'').'> '.$processLabels['sales'].'<br/>';
					?>
					</td>
					<td valign="top">
					<?php
						echo '<input type="checkbox" value="acqEmail" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'acqEmail')!== false)?'checked':'').'> '.$processLabels['acqEmail'].'<br/>';
						echo '<input type="checkbox" value="acquisitionassistantemailtest" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'acquisitionassistantemailtest')!== false)?'checked':'').'> '.$processLabels['acquisitionassistantemailtest'].'<br/>';
						echo '<input type="checkbox" value="pcfTest" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'pcfTest')!== false)?'checked':'').'> '.$processLabels['pcfTest'].'<br/>';						
						echo '<input type="checkbox" value="sampleAudio" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'sampleAudio')!== false)?'checked':'').'> '.$processLabels['sampleAudio'].'<br/>';
						echo '<input type="checkbox" value="illustrations" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'illustrations')!== false)?'checked':'').'> '.$processLabels['illustrations'].'<br/>';
						echo '<input type="checkbox" value="illustrationsB" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'illustrationsB')!== false)?'checked':'').'> '.$processLabels['illustrationsB'].'<br/>';
						echo '<input type="checkbox" value="webdesigntest" name="requiredTest[]" '.((strpos($pos['requiredTest'], 'webdesigntest')!== false)?'checked':'').'> '.$processLabels['webdesigntest'].'<br/>';
					?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>Description</td>
		<td><textarea name="desc" class="form-control" rows="8"><?= $pos['desc'] ?></textarea></td>
	</tr>
	<tr bgcolor="#dcdcdc">
		<td>Required Skills</td>
		<td>
		<?php
			$rskillsArr = explode('|', $pos['requiredSkills']);
			$skillsQ = $db->selectQuery("applicantSkills", "*", "1 ORDER BY skillName");
			foreach($skillsQ AS $s):
				echo '<input type="checkbox" name="requiredSkills[]" value="'.$s['skillID'].'" '.((in_array($s['skillID'],$rskillsArr))?'checked':'').'/> '.$s['skillName'].'<br/>';
			endforeach;
		?>
		</td>
	</tr>
	<tr>
		<td><br/></td>
		<td><input type="submit" value="Update" id="update"/></td>
	</tr>
</table>
</form>
<?php } ?>

<script type="text/javascript">
	$(function () { 
	
	});
	
	function getOrgVal(c, n){
		$('#update').attr('disabled', 'disabled');
		if(c=='org'){
			$('#depttr').addClass('hide');
			$('#grptr').addClass('hide');
			$('#subgrptr').addClass('hide');
		}else if(c=='dept'){
			$('#grptr').addClass('hide');
			$('#subgrptr').addClass('hide');
		}else{
			$('#subgrptr').addClass('hide');
		}
		
		$.post("editpositions.php?cur="+c+"&n="+n,
			{typeVal:$('#'+c).val()},
			function(data){ 
				$('#update').removeAttr('disabled');
				$('#'+n+'tr').removeClass('hide');
				$('#'+n).html(data); 
			}
		); 
		$('#'+c+'Val').val($('#'+c).val());
	}
	
	function validateForm(){
		if($('#title').val()=='' || $('#active').val()=='' || $('#org').val()=='' || $('#dept').val()=='' || $('#grp').val()=='' || $('#subgrp').val()==''){
			alert('There are missing values.  Please check all fields.');
			return false;
		}else{
			return true;
		}
	}
</script>
<?php } ?>

</body>
</html>