<?php
require 'config.php';
require_once('includes/labels.php');

if(isset($_GET)){
	if(isset($_GET['remove'])){
		$db->deleteQuery('pt_users', 'id='.$_GET['remove']);
		echo 'User has been removed';
		exit;
	}
}

if(isset($_POST) && !empty($_POST)){
	if($_POST['submitType']=='addUser'){
		$db->insertQuery('pt_users', array('username'=>$_POST['addusername'], 'level'=>$_POST['level'], 'pt_username'=>$_SESSION['u']));
		echo '<script>
			alert("PT User has been added");
			window.location.href="'.HOME_URL.'settings.php";
		</script>';
	}
	
	if($_POST['submitType']=='addChecklist'){
		if(!empty($_POST['skillName'])){
			$db->insertQuery('applicantSkills', array('skillName'=>$_POST['skillName']));
			echo '<script>
				alert("Skill \"'.$_POST['skillName'].'\" has been added.");
				window.location.href="'.HOME_URL.'settings.php?current=tab-3";
			</script>';
		}
	}
}

require "includes/header.php";
?>
<link href="css/jquery.dataTables.css" rel="stylesheet">
<script type="text/javascript" language="javascript" src="js/jquery.dataTables.min.js"></script>
<style type="text/css">
	.tab-content{ padding-bottom:35px; }
</style>
<div class="container">
	<fieldset>
		<legend>Settings</legend>
	</fieldset>
	<ul class="tabs">
		<li class="tab-link <?= ((!isset($_GET['current']) || $_GET['current']=='')?'current':'') ?>" data-tab="tab-1">CAREERPH Authorized Users</li>
		<li class="tab-link <?= ((isset($_GET['current']) && $_GET['current']=='tab-2')?'current':'') ?>" data-tab="tab-2">Available Positions Info</li>
		<li class="tab-link <?= ((isset($_GET['current']) && $_GET['current']=='tab-3')?'current':'') ?>" data-tab="tab-3">Applicant Skills Checklist</li>		
	</ul>
	
	<div id="tab-1" class="tab-content <?= ((!isset($_GET['current']) || $_GET['current']=='')?'current':'') ?>">
	<table width="100%" border="1" cellpadding=10 style="font-size:13px;">
		<tr>
			<td colspan=2>
			<form action="" method="POST" onSubmit="return validateAUser();">
				<b>Add Access to User</b><br/>
				<b>User:</b>&nbsp;&nbsp;
					<select style="padding:5px;" name="addusername" id="addusername">
						<option value=""></option>
					<?php
						$xxx = '';
						foreach($authorized AS $au):
							$xxx .= '\''.$au.'\',';
						endforeach;
						$xxx = rtrim($xxx,',');
						
						$cQuery = $db->selectQuery('staffs', 'username, CONCAT(lname,", ",fname) AS name', 'active=1 AND username NOT IN ('.$xxx.') ORDER BY lname');
						$pQuery = $ptDb->selectQuery('staff', 'username, CONCAT(sLast,", ",sFirst) AS name', 'active="Y" AND username NOT IN ('.$xxx.') ORDER BY sLast');
						
						$result = array_merge($cQuery, $pQuery);
												
						foreach($result AS $u):
							echo '<option value="'.$u['username'].'">'.$u['name'].'</option>';
						endforeach;
					?>
					</select>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<b>Access Type:</b>&nbsp;&nbsp;
					<select style="padding:5px;" name="level" id="level">
						<option value="2">Hiring Manager</option>
						<option value="1"> HR/Admin</option>
					</select>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type="hidden" name="submitType" value="addUser"/>
				<input type="submit" value="Add User" class="btn-primary btn-xs"/>
			</form>
			</td>
		</tr>
		<tr>
			<td><b>Users with HR/Admin Access</b></td>
			<td><b>Users with Hiring Manager Access</b></td>
		</tr>
		<tr>		
		<tr>
			<td valign="top">
				<?php
					$hrAccess = $db->selectQuery("pt_users", "pt_users.*, CONCAT(fname,' ',lname) AS name", "level=1", "LEFT JOIN staffs ON staffs.username=pt_users.username");
					foreach($hrAccess AS $a):
						if($a['name']=='')
							$name = $a['username'];
						else
							$name = $a['name'];
														
						echo '<a onClick="funcdelete(\''.$a['id'].'\', \''.$a['name'].'\')"><img src="images/delete_icon.png"  title="Remove Access of '.$name.'"/></a> ';
						echo $name.' <i style="font-size:10px;">[Added by: '.$a['pt_username'].' '.date('Y-m-d',strtotime($a['timestamp'])).']</i><br/>';
					endforeach;
				?>
			</td>
			<td valign="top">
				<?php
					$hmAccess = $db->selectQuery("pt_users", "pt_users.*, CONCAT(fname,' ',lname) AS name", "level=2", "LEFT JOIN staffs ON staffs.username=pt_users.username");
					foreach($hmAccess AS $h):
						if($h['name']=='')
							$name = $h['username'];
						else
							$name = $h['name'];
														
						echo '<a onClick="funcdelete(\''.$h['id'].'\', \''.$h['name'].'\')"><img src="images/delete_icon.png"  title="Remove Access of '.$name.'"/></a> ';
						echo $name.' <i style="font-size:10px;">[Added by: '.$h['pt_username'].' '.date('Y-m-d',strtotime($h['timestamp'])).']</i><br/>';
					endforeach;
				?>			
			</td>
		<tr>
	
	</table>
	</div>
	
	<? //TAB 3?>
	<div id="tab-2" class="tab-content <?= ((isset($_GET['current']) && $_GET['current']=='tab-2')?'current':'') ?>">
		<table class="posTable" class="hover stripe row-border">
		<thead>
			<tr style="font-size:10px">
				<th>Position ID</th>
				<th width="150px">Title</th>
				<th>Active</th>
				<th width="100px">Org Level</th>
				<th>Organization</th>
				<th>Department</th>
				<th>Group</th>
				<th>Subgroup</th>
				<th>Required Test</th>
				<th>Description & Skills</th>
				<th><br/></th>
			</tr>
		</thead>
		<tbody>
		<?php
			$orgLevel = $db->selectQueryArray('SELECT * FROM orgLevel');
			$posQuery = $db->selectQuery("newPositions", "*", "1 ORDER BY org, dept, title ASC");
			foreach($posQuery AS $p):
				echo '<tr>
						<td>'.$p['posID'].'</td>
						<td>'.$p['title'].'</td>
						<td>'.(($p['active']==1)?'Yes':'No').'</td>
						<td>'.$orgLevel[$p['orgLevel_fk']]['levelName'].'</td>
						<td>'.$p['org'].'</td>
						<td>'.$p['dept'].'</td>
						<td>'.$p['grp'].'</td>
						<td>'.$p['subgrp'].'</td>';
				if($p['requiredTest']=='') echo '<td><br/></td>';
				else{
					$ttest = '';
					echo '<td>';
					$eQuery = explode(',', $p['requiredTest']);
					$cntJapan = count($eQuery);
					for($i=0; $i<$cntJapan; $i++){
						$ttest .= $processLabels[$eQuery[$i]].', ';
					}
					echo rtrim($ttest,', ');
					echo '</td>';
				}
				
				echo '<td align="center"><a class="iframe" href="/editpositions.php?posID='.$p['posID'].'&desc=yes"><img src="images/view_icon.png"></a></td>';
				echo 	'<td><a href="/editpositions.php?posID='.$p['posID'].'" class="iframe"><img src="images/edit_icon.png"></a></td>
					</tr>';
			endforeach;
		?>
		</tbody>
		</table>	
	</div>
	
	<? //TAB 3 ?>
	<div id="tab-3" class="tab-content <?= ((isset($_GET['current']) && $_GET['current']=='tab-3')?'current':'') ?>">
	<table width="100%" border="1" cellpadding=10 style="font-size:13px;">
		<tr>
			<td colspan=2>
			<form action="" method="POST">
				<b>Add Checklist</b><br/>					
				Skill Name:&nbsp;&nbsp;&nbsp;<input type="text" name="skillName" value="" style="width:80%; padding:3px;"/>&nbsp;&nbsp;&nbsp;
				<input type="hidden" name="submitType" value="addChecklist"/>
				<input type="submit" value="Submit" class="btn-primary btn-xs"/>
			</form>
			</td>
		</tr>
	</table>
	<br/>
	<?php
		$skillsQ = $db->selectQuery("applicantSkills", "*", "1 ORDER BY skillName");
		$scnt=0;
		echo '<ul>';
		foreach($skillsQ AS $s):
			
			echo '<li>'.$s['skillName'].'</li>';
			$scnt++;
		endforeach;
		echo '</ul>';
	?>
	
	</div>

</div>

<script type="text/javascript">
$(document).ready(function() {
	$(".iframe").colorbox({iframe:true, width:"990px", height:"600px"});
	
	var oTable;
	oTable = $('.posTable').dataTable({
		"aaSorting": [[4,"desc"],[5,"desc"],[0,"asc"]],
		"sDom": 'RC<"clear">lfrtip',
		"oLanguage": {
			"sSearch": "Search all columns:"
		},
		"bSortCellsTop": true
	});

	$('ul.tabs li').click(function(){
		var tab_id = $(this).attr('data-tab');

		$('ul.tabs li').removeClass('current');
		$('.tab-content').removeClass('current');

		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
	});
	
});

function funcdelete(id, name){
	if(confirm('Are you sure you want to remove access of '+name+'?')){
		$.post("settings.php?remove="+id,
			function(data){ 
				alert(data);
				window.location.href="<?= HOME_URL.'settings.php' ?>";
			}
		);
	}
}

function validateAUser(){
	if($('#addusername').val()==''|| $('#level').val()==''){
		alert('Check fields again.');
		return false;
	}else{
		return true;
	}
}
</script>

<?php 
require "includes/footer.php";
?>



