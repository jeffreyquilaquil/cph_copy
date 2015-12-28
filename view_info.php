<?php
require 'config.php';
require_once('includes/labels.php');

if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
	header("Location: login.php");
	exit();
} 

$id = $_GET['id'];
$info = $db->selectSingleQueryArray("applicants","*","id=".$id);

$openP = '';

if($info['isNew']==0){
	$position = $db->selectSingleQuery("positions", "title" , "id=".$info['position']);
}else if(!empty($info['position'])){
	$pp = $db->selectSingleQueryArray("newPositions", "`title`, `org`, `dept`, `grp`, `subgrp`" , "posID=".$info['position']);	
	$position = $pp['title'];
	$info += $pp;
	
	$joR = $db->getCountRows('jobReqData', 'positionID', 'positionID="'.$info['position'].'" AND status=0');
	if($joR==0) $openP = ' - No open job requisition';
}

if(isset($_POST) && !empty($_POST)){
	if($_POST['addRemarks']=='Submit'){	
		if(!empty($_POST['rem'])){
			addStatusNote($id, 'remarks', '', $info['position'], $_POST['rem']);
		}
	}else if(isset($_POST['submitType']) && $_POST['submitType']=='deletetemplate'){
		$db->updateQuery('emailTemplates', array('status'=>0), 'templateID='.$_POST['id']);
		exit;
	}
}

require "includes/header.php";

if(count($info)==0){
	echo '<b>Applicant info not found.</b>';
}else{
	//check possible duplicate entry
	$duplicateQuery = $db->selectQueryArray('SELECT id, CONCAT(fname," ",lname) AS name, email, mnumber, date_created, processType, processText, IF(isNew= 0, (SELECT title FROM positions WHERE positions.id=applicants.position LIMIT 1), (SELECT title FROM newPositions WHERE posID=applicants.position LIMIT 1)) as title
		FROM applicants 
		LEFT JOIN recruitmentProcess ON processID=process
		WHERE id!="'.$info['id'].'" AND 
		((fname LIKE "'.$info['fname'].'%" AND lname LIKE "'.$info['lname'].'%") OR email="'.$info['email'].'" OR mnumber="'.$info['mnumber'].'")');
	
?>


<link rel="stylesheet" href="css/progressbar.css" type="text/css" />
<div class="container">	
	<fieldset>
		<legend><?= $info['fname'].' '.$info['lname'] ?> Information</legend>
		<b>Position Applied:</b> <? echo $position; if(($info['isNew']==0 || !empty($openP)) && $info['process']<6){ echo ' <span style="color:red;">(Reprofile Needed'.$openP.')</span>'; } ?>
	</fieldset>	
	<br/>
	<b>Recruitment Status Info</b>
	<?php	
		if($info['processStat']==1 && empty($info['processText'])) echo ' - <span style="color:red;">in progress</span>';		
		if(!empty($info['processText'])) echo ' - <span style="color:red;">'.$info['processText'].'</span>';
	?>
	
	<? if(is_admin()){?><a class="iframe" href="editstatus.php?id=<?= $id ?>"><button class="btn btn-xs">Edit</button></a> <? } ?>
	<table width="100%">
		<tr><td>
			<div class="wizard-steps">
			<?php
				$recProcess = $db->selectQueryArray('SELECT * FROM recruitmentProcess');
				foreach($recProcess AS $p):
					if($p['processID']<$info['process'])
						echo '<div class="completed-step"><a><span>'.$p['processID'].'</span> '.$p['processType'].'</a></div>';
					else if($p['processID']==$info['process'])
						echo '<div class="active-step"><a><span>'.$p['processID'].'</span> '.$p['processType'].'</a></div>';
					else
						echo '<div><a><span>'.$p['processID'].'</span> '.$p['processType'].'</a></div>';
				endforeach;
			?>		
			</div>
		</td></tr>		
	</table>
	<hr/>
	<b>Status History</b> 
	<button id="sHshow" class="btn btn-xs" onClick="showHide(1, 'sHhide', 'sHshow', 'statHist')">show</button>
	<button id="sHhide" class="btn btn-xs"  onClick="showHide(0, 'sHshow', 'sHhide', 'statHist')" style="display:none;">hide</button>
	<button class="btn btn-xs" id="addR">add remarks</button>
	<div id="addRemarks" style="display:none;">
		<form action="" method="POST">
			<textarea class="form-control" rows="5" name="rem"></textarea><input type="submit" name="addRemarks" value="Submit"/><input type="button" id="cancelR" value="Cancel"/>
		</form>
	</div>
	<table id="statHist" width="100%" cellpadding=5 class="table" style="display:none;">		
	<?php
		$hist = $db->selectQueryArray('SELECT * FROM processStatusData WHERE appID='.$id.' ORDER BY timestamp DESC');
		if(count($hist)==0){
			echo '<tr><td>None.</td></tr>';
		}else{	
			echo '
			<thead>
			<tr>
				<th width="20%">Updated</th>
				<th width="15%">Type</th>
				<th width="15%">Status</th>				
				<th>Reason</th>			
			</tr>
			</thead>
			';
			$ctr = 0;
			foreach($hist AS $h):
				if($h['testStatus'] != 'admin' || ( $h['testStatus'] == 'admin' && is_admin()) ){
					if($ctr%2==0) echo '<tr bgcolor="#dedede">';
					else echo '<tr>';
					
					echo '<td>'.$h['examiner'].' '.date('Y-m-d H:i', strtotime($h['timestamp'])).'</td>';
					echo '<td>'.$processLabels[$h['type']].'</td>';
					echo '<td>'.ucfirst($h['testStatus']).'</td>';
					if($h['type']=='hired')
						echo '<td>Hired on '.date('Y-m-d', strtotime($h['timestamp'])).'</td>';
					else{						
						$reason = $h['reason'];
						if (preg_match('<div class="message">', $reason)){
							$reason = str_replace('<div class="message">','<a id="a'.$h['testID'].'" onClick="showMessage('.$h['testID'].')">View email</a><div id="'.$h['testID'].'" class="message hide">', $reason);
						}else{
							$reason = nl2br($reason);
						}
							
						echo '<td>'.$reason.'</td>';
						
					}
					echo '<tr>';
										
					$ctr++;
				}
					
			endforeach; 
		}
	?>
	</table>
	
	<?php 
	$remarks = $db->selectQueryArray('SELECT * FROM applicant_feedbacks WHERE applicant_id='.$id.' ORDER BY date_created DESC');
	if(count($remarks)>0){
	?>
	<hr/>	
	<b>Remarks</b> 
	<button id="rshow" class="btn btn-xs" onClick="showHide(1, 'rhide', 'rshow', 'remarksTbl')">show</button>
	<button id="rhide" class="btn btn-xs"  onClick="showHide(0, 'rshow', 'rhide', 'remarksTbl')" style="display:none;">hide</button>	
	<table id="remarksTbl" width="100%" cellpadding=5 class="table" style="display:none;">	
		<?php			
			$rctr = 0;
			foreach($remarks AS $r):
				if($rctr%2==0) echo '<tr bgcolor="#dedede">';
				else echo '<tr>';
				echo '
						<td width="30%">'.$r['pt_username'].' | '.$r['date_created'].'</td>
						<td>'.nl2br($r['remarks']).'</td>
					</tr>
				';
				$rctr++;
			endforeach;
		?>
		<tr>
			<td></td>
			<td></td>
		</tr>
	</table>
	<?php } if(is_admin()){ ?>
	
	<hr/>
	<b>Email Templates</b>
	<ul>
		<li><a class="iframe" href="emailTemplate.php?id=<?= $id ?>&type=custom">Click here to send custom email</a></li>
		<?php if($info['isNew']==1){ ?><li><a class="iframe" href="emailTemplate.php?id=<?= $id ?>&type=hmanager">Click here to send custom email to Hiring Manager</a></li><? } ?>
		<li><a class="iframe" href="emailTemplate.php?id=<?= $id ?>&type=declined">Click here to send DECLINE EMAIL</a></li>
		<li><a class="iframe" href="emailTemplate.php?id=<?= $id ?>&type=finalInterviewerSched">Click here to send Final Interview Invitation (to FINAL INTERVIEWER)</a></li>
		<li><a class="iframe" href="emailTemplate.php?id=<?= $id ?>&type=finalInterviewerSched">Click here to resend last Final Interview Invitation (to FINAL INTERVIEWER)</a></li>
		<li><a class="iframe" href="emailTemplate.php?id=<?= $id ?>&type=finalInterview">Click here to send Final Interview Invitation</a></li>
		<li><a class="iframe" href="emailTemplate.php?id=<?= $id ?>&type=testingInvitation">Click here to send Testing Invitation</a></li>
	</ul>
	<b>Custom Email Templates</b> <a href="emailTemplate.php?type=addtemplate" class="iframe" style="font-size:12px; color:red;">+ Add Custom Email Template</a>
	<?php 
		$customTemplates = $db->selectQueryArray('SELECT templateID, templateName FROM emailTemplates WHERE templateType="customtemplate" AND status=1');
		if(count($customTemplates)>0){
			echo '<ul>';
			foreach($customTemplates AS $c){
				echo '<li>';
					echo '<a href="emailTemplate.php?id='.$id.'&type=customtemplate" class="iframe">'.$c['templateName'].'</a>';
					echo '&nbsp;[<a href="emailTemplate.php?type=edittemplate&tempID='.$c['templateID'].'" class="iframe" style="color:red; font-size:12px;">Edit</a>]';
					echo '&nbsp;[<a href="javascript:void(0);" onClick="deleteCustomEmail('.$c['templateID'].')" style="color:red; font-size:12px;">Delete</a>]';
				echo '</li>';
			}
			echo '</ul>';
		}
	} ?>
	
<?php
	if(count($duplicateQuery)>0){
		echo '<hr/>';
		echo '<b>Possible Duplicate Applicants</b>';
		echo '<table width="100%">';
		echo '<tr><th>ID</th><th>Applicant\'s Name</th><th>Email</th><th>Contact Number</th><th>Position Applied</th><th>Date Applied</th><th>Status</th></tr>';
		foreach($duplicateQuery AS $dup){
			echo '<tr>';
				echo '<td>'.$dup['id'].'</td>';
				echo '<td><a href="view_info.php?id='.$dup['id'].'" target="_blank">'.$dup['name'].'</a></td>';
				echo '<td><a href="mailto:'.$dup['email'].'">'.$dup['email'].'</a></td>';
				echo '<td>'.$dup['mnumber'].'</td>';
				echo '<td>'.$dup['title'].'</td>';
				echo '<td>'.date('Y-m-d', strtotime($dup['date_created'])).'</td>';
				echo '<td>'.((!empty($dup['processText']))?' <span style="color:red;">'.$dup['processText'].'</span>':$dup['processType']).'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
?>
	
	<hr/>
	<b>Applicant Info</b>
	<table id="applicantInfo" width="100%" class="table">
		<tr bgcolor="#dedede">
			<td width="30%">Last Name</td>
			<td><?= $info['lname'] ?></td>
		</tr>
		<tr>
			<td>First Name</td>
			<td><?= $info['fname'] ?></td>
		</tr>
		<tr bgcolor="#dedede">
			<td>Middle Name</td>
			<td><?= $info['mname'] ?></td>
		</tr>
		<tr>
			<td>Gender</td>
			<td><?= ucfirst($info['gender']) ?></td>
		</tr>
		<tr bgcolor="#dedede">
			<td>Birthday</td>
			<td><?= date('F d, Y',strtotime($info['bdate'])) ?></td>
		</tr>
		<tr>
			<td>Address</td>
			<td><?= $info['address'] ?></td>
		</tr>
		<tr bgcolor="#dedede">
			<td>Mobile Number</td>
			<td><?= $info['mnumber'] ?></td>
		</tr>
		<tr>
			<td>Email Address</td>
			<td><? echo '<a href="mailto:'.$info['email'].'">'.$info['email'].'</a>'; ?></td>
		</tr>
		<tr bgcolor="#dedede">
			<td>Position</td>
			<td><? echo $position; if($info['isNew']==0){ echo ' <span style="color:red;">(Reprofile Needed)</span>'; } ?></td>
		</tr>
		<tr>
			<td>Where did you hear about us</td>
			<td><? echo $info['source']; if(!empty($info['source_field'])){ echo ' : '.$info['source_field'];} ?>
				<?php if(($info['source']=='Referred by a Tate Employee' || strpos($info['source'], 'I have a friend working in Tate')!==false) && $info['referrerID']==0){
					
					if(strpos($info['source'], 'I have a friend working in Tate')!==false)
						$nname = str_replace('I have a friend working in Tate:', '', $info['source']);
					else $nname= $info['source_field'];
					
					echo '<a href="referrallink.php?id='.$info['id'].'&name='.$nname.'&process='.$info['process'].'" class="iframe" style="color:red; font-weight:bold;">(Link to employee\'s account)</a>';
				} ?>
			</td>
		</tr>
		<tr bgcolor="#dedede">
			<td>Portfolio Link(s)</td>
			<td><?= $info['link'] ?></td>
		</tr>
		<tr>
			<td>Expected Salary</td>
			<td><?= $info['expected_salary'] ?></td>
		</tr>
		<tr bgcolor="#dedede">
			<td>Last Employer</td>
			<td><?= $info['last_employer'] ?></td>
		</tr>
		<tr>
			<td>Employment Period</td>
			<td><?= $info['employment_period'] ?></td>
		</tr>
	</table>
	
	<hr/>
	<b>Text Resume</b>
	<button id="tRshow" class="btn btn-xs" onClick="showHide(1, 'tRhide', 'tRshow', 'trDiv')">show</button>
	<button id="tRhide" class="btn btn-xs"  onClick="showHide(0, 'tRshow', 'tRhide', 'trDiv')" style="display:none;">hide</button>
	<div id="trDiv" style="display:none;"><?= nl2br($info['text_resume']) ?></div>
<?php
	$att = get_uploaded_file_icon("uploads/resumes/{$info['uploads']}/","large");
	if($att!=false){
		echo '<hr/>';
		echo '<b>Attachment</b>';
		echo '<div>'.$att.'</div>';
		
	}
?>
	<hr/>
	<b>HR Uploads</b>
	<div class='container'>
		<a name='attach_file'></a>
		<?php 
			$dir = "uploads/applicants/{$_GET['id']}/";
			$uploader = new uploader($dir, "Choose File...","Start Upload","Cancel Upload");
			$uploader->set_multiple(TRUE);
			$uploader->uploader_html();
		?>
	</div>
</div>
<script type="text/javascript">
	$(function () { 
		$(".iframe").colorbox({iframe:true, width:"990px", height:"600px"});
		
		$('#addR').click(function(){
			$('#addRemarks').css('display', 'block');
		});
		$('#cancelR').click(function(){
			$('#addRemarks').css('display', 'none');
		});
				
	});
	
	function showHide(t, show, hide, tbl){
		if(t==1){
			$('#'+tbl).css('display',''); 
		}else{
			$('#'+tbl).css('display','none'); 			
		}
		$('#'+hide).hide(); 
		$('#'+show).show();
	}
	
	function showMessage(id){
		$('#a'+id).hide();
		$('#'+id).removeClass('hide');
	}
	
	function deleteCustomEmail(id){
		if(confirm('Are you sure you want to delete this template?')){
			$.post(window.location, {submitType:'deletetemplate', id:id},
			function(){
				alert('Template Deleted');
				location.reload();
			});
		}
	}
</script>

<?php 
}
require 'includes/footer.php';
?>