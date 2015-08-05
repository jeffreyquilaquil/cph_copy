<?php
require 'config.php';
$type = $_GET['type'];
$id = $_GET['id'];

if(!empty($id)){
	$info = $db->selectSingleQueryArray("applicants","id, CONCAT(fname,' ',lname) AS fullname, position, isNew, email, interviewSched","id=".$id);

	$position = '';
	if($info['isNew']==0){
		$position = $db->selectSingleQuery("positions", "title" , "id=".$info['position']);
	}else if(!empty($info['position'])){	
		$position = $db->selectSingleQuery("newPositions", "title" , "posID=".$info['position']);	
	}

	$arrValues['%NAME%'] = ucwords($info['fullname']);
	$arrValues['%POSITION%'] = $position;
}


$subject = '';
$to = '';
$message;
$cc = '';
if(isset($userData['email'])) 
	$cc = $userData['email'];

if(isset($_POST) && !empty($_POST)){	
	if($_POST['submitType']=='send'){		
		if(!empty($_POST['ccEmail'])) $cc = $_POST['ccEmail'].','.$cc;
		
		if(empty($_POST['subject']) || empty($_POST['email']) || empty($_POST['message'])){
			echo '<p style="color:red;">Unable to send message. Please check all empty fields and invalid inputs.</p>';
		}else{
			$toId = array();
			
			if(!empty($id)) $toId[] = $id;
			
			if($type=='invitationtoapplyopenposition'){
				$qArr = array();
				$explodeR = array_map('trim', explode(',', $_POST['email']));
					
				if(!empty($_POST['email']))
					$queryApp = $db->selectQueryArray('SELECT id, email FROM applicants WHERE id IN("'.implode('","', $explodeR).'")');
			
				foreach($queryApp AS $v){
					$qArr[$v['id']] = $v;
				}
				
				$noApp = '';
				$emails = '';
				$toArr = array();
				$exCount = count($explodeR);
				
				for($c=0; $c<$exCount; $c++){
					if(!array_key_exists($explodeR[$c], $qArr))
						$noApp .= $explodeR[$c].', ';
					else{
						if(isValidEmail(trim($qArr[$explodeR[$c]]['email']))===false){
							$noApp .= $qArr[$explodeR[$c]]['email'].', ';
						}else{
							$emails .=  $qArr[$explodeR[$c]]['email'].',';
							$toArr[] = array('id'=>$explodeR[$c], 'email'=>$qArr[$explodeR[$c]]['email']);
						}
					}						
				}
				
				foreach($toArr AS $t){
					$body = '<div style="font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:15px;">'.$_POST['message'].'</div>';
					sendEmail( 'careers.cebu@tatepublishing.net', $t['email'], $_POST['subject'], $body, 'Career Index Auto Email' , $cc);					
					addEmailStatusNote($t['id'], $_POST['subject'], $t['email'], $_POST['message'], '', $cc);
				}
				
				if(!empty($noApp)){
					echo '<p style="color:red;">Email NOT sent to '.rtrim(trim($noApp), ',').'.<br/>
					No email address or invalid email.</p>';
				}
				
				$sendto = rtrim(trim($emails),',');
				if(!empty($sendto))
					echo 'Thank you!<br/>The email has been sent to '.$sendto.' individually.';				
				
			}else if($type=='finalInterview'){				
				//send email to interviewer and job requestor
				$to = array();		
				$schedDetails = explode('|', $info['interviewSched']);		
				if(isset($schedDetails[1])) $to[] = $schedDetails[1];
				//get requestor
				$requestor = $db->selectSingleQuery('jobReqData', 'requestor' , 'positionID='.$info['position'].' AND status=0');		
				$rData = $ptDb->selectSingleQueryArray('staff', 'CONCAT(sFirst, " ", sLast) AS name, email', 'username="'.$requestor.'"');
				if(isset($rData['email']) && !in_array($rData['email'], $to)) $to[] = $rData['email'];
				
				
				$to = implode(',', $to);
				$subject = 'NOTE! Final Interview Invitation send to Applicant '.$info['fullname'].' for position '.strtoupper($position).' Final Interviewer, please be prepared.';
				$eBody = 'Hello,<br/><br/>Please be informed that a Final Interview invitation has been sent to '.$info['fullname'].' for the position of '.strtoupper($position).'. Final Interviewer is <b>'.$schedDetails[0].'</b>. Final Interview schedule is on <b>'.date('F d, Y h:i A', strtotime($schedDetails[2])).' '.$schedDetails[3].'</b>. Please reply to this email if you have concerns about this matter.<br/><br/>Thanks!';	
				
				sendEmail( 'careers.cebu@tatepublishing.net', $to, $subject, $eBody, 'Career Index Auto Email', $cc.',hr-list@tatepublishing.net' );
				if(!empty($id)) addEmailStatusNote($id, $subject, $to, $eBody, '', $cc);
				echo '<p>Interviewer and requester are also informed.</p>';
			}else if(!empty($_POST['email'])){				
				$email = array();
				$noSent = '';
				$emailArr = explode(',', $_POST['email']);
				foreach($emailArr AS $e){
					if(isValidEmail(trim($e))===false){
						$noSent .= trim($e).',';												
					}else{
						$email[] = trim($e);
					}
				}
				
				if(isset($_POST['individually'])){
					foreach($email AS $ee){
						sendEmail( 'careers.cebu@tatepublishing.net', $ee, $_POST['subject'], $_POST['message'], 'Career Index Auto Email', $cc );
						$aID = $db->selectSingleQuery('applicants', 'id' , 'TRIM(email)="'.$ee.'"');
						if(!empty($aID)) addEmailStatusNote($aID, $_POST['subject'], $ee, $_POST['message'], '', $cc);
					}
					
					exit;
				}else if(!empty($email)){
					$emails = implode(',', $email);
					sendEmail( 'careers.cebu@tatepublishing.net', $emails, $_POST['subject'], $_POST['message'], 'Career Index Auto Email', $cc );
					
					//adding notes on applicants					
					$condition = '';
					$emailsEx = explode(',', $emails);
					foreach($emailsEx AS $h){
						$condition .= 'TRIM(email) = "'.$h.'" OR ';
					}
					$queryApplicant = $db->selectQuery('applicants', 'id', rtrim($condition,'OR '));
					foreach($queryApplicant AS $qa){
						if($id!=$qa['id'])
							addEmailStatusNote($qa['id'], $_POST['subject'], $emails, $_POST['message'], '', $cc);
					}
					
					addEmailStatusNote($id, $_POST['subject'], $emails, $_POST['message'], '', $cc);
				}

				if(!empty($noSent)){
					echo '<p style="color:red;">Email NOT sent to '.rtrim(trim($noSent), ',').'.<br/>
					Invalid email.</p>';
				}

				if(!empty($email)){
					echo 'Thank you!<br/>The email has been sent to '.implode(',', $email);
					if(isset($_POST['individually'])) echo ' individually.';
				}				
			}else{
				echo '<p style="color:red;">Email not sent. Empty recipient.</p>';
			}
			
			exit;
		}			
	}else if($_POST['submitType']=='generate'){
		$arrValues['%DATEANDTIME%'] = $_POST['dateandtime'];
	}
}
	
?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Email Template</title>
	<link href="css/yeti.bootstrap.min.css" rel="stylesheet">
	<script src="js/jquery.js"></script>
	<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/ >
	<script src="js/jquery.datetimepicker.js"></script>
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
<?php if($type=='finalInterview' && isset($_GET['id'])){ 
	$interviewer = '';
	if($info['isNew']==1)
		$interviewer = $db->selectSingleQuery('jobReqData', 'supervisor' , 'positionID='.$info['position'].' AND status=0');
	
	if(!empty($info['interviewSched'])){ 
			$intDetails = explode('|', $info['interviewSched']);
	?>
	<div id="finalIntDiv">
		<table id="intDetailstbl" width="80%" cellpadding=5 border=0>
			<tr bgcolor="#dcdcdc"><td colspan=2><b>Final Interview Details:</b> <a href="javascript:void(0);" style="float:right;" onClick="$('#intDetailstbl').hide(); $('#selectInterviewertbl').show();">Change</a></td></tr>
			<tr><td width=35%>Final Interviewer</td><td><?= $intDetails[0]; ?></td></tr>
			<tr><td>Interview Schedule</td><td><?= date('F d, Y h:i A', strtotime($intDetails[2])); ?></td></tr>
			<tr><td>Timezone</td><td><?= $intDetails[3]; ?></td></tr>
			<tr><td colspan=2>
				<button style="padding:5px;" id="genEmailFI">Generate Email</button>
			</td></tr>
		</table>	
		<br/>
		
	<?php
		}
?>
	<table id="selectInterviewertbl" width="70%" style="margin-bottom:20px; <?= ((!empty($info['interviewSched']))?'display:none;':'') ?>" cellpadding=5>
	<?php 
		$intName = '';
		if(!empty($info['interviewSched'])){
			$viewArr = explode('|', $info['interviewSched']);
			$intName = $viewArr[0];
		}
		
		$staffs = $ptDb->selectQueryArray('SELECT CONCAT(sFirst, " ", sLast) AS name, sFirst, sLast, username, email FROM staff WHERE active="Y" ORDER BY sLast');
		echo '<tr>';
		if(empty($intName))
			echo '<td>No assigned final interviewer yet, please assign</td>';
		else
			echo '<td>The (last) assigned final interviewer for this applicant is <b>'.$intName.'</b></td>';
		
		echo '<td><select name="interviewer" id="interviewer" class="form-control"><option value="">Select interviewer</option>';
		foreach($staffs AS $s){
			echo '<option value="'.$s['name'].'|'.$s['email'].'" '.(($intName==$s['name'])?'selected="selected"':'').'>'.$s['sLast'],', '.$s['sFirst'].'</option>';
		}
		echo '</select></td></tr>'; 
	?>
		<tr><td width="50%">Recommend time and date of the interviewer:</td><td><input type="text" name="intdate" id="intdate" class="dateandtime form-control"/></td></tr>
		<tr><td>Select a time zone:</td><td>
		<select name="inttimezone" class="form-control">
			<option value="PHT">PHT</option>
			<option value="CST">CST</option>
		</select>
		</td></tr>
		<tr><td colspan=2 style="color:red;"><span style="font-weight:bold;">Note: If one of the interviewers is in OKC, please use CST time.</span><br/><span>Refer to this site for time conversions: <a href="http://www.timeanddate.com/worldclock/converter.html" target="_blank">http://www.timeanddate.com/worldclock/converter.html</a></span></td></tr>
		<tr><td colspan=2 align="right"><button class="btn btn-xs btn-success" onClick="nextInterviewer()">Next</button></td></tr>
	</table>
	
	</div>
	
	<script type="text/javascript">
		$(function(){
			$('#emailContentDiv').hide();
			$('#edit').hide();
			$('#send').hide();
			$('#emailContentShow').hide();
			
			$('#genEmailFI').click(function(){
				$('#finalIntDiv').hide();
				$('#emailContentShow').show();
				
				$('#edit').show();
				$('#send').show();
			});
		});
		
		function nextInterviewer(){
			err = '';
			if($('#interviewer').val()=='')
				err += '-  No final interviewer\n';
			if($('#intdate').val()=='')
				err += '-  No time and date of interview\n';

			if(err!=''){
				alert('Please check error(s):\n'+err);
			}else{
				$(this).attr('disabled', 'disabled');
				$.post("editstatus.php?pos=interviewSched&appID=<?= $info['id'] ?>",{
					appID:'<?= $info['id'] ?>',
					interviewer:$('#interviewer').val(),
					dateNtime:$('#intdate').val(),
					timezone:$('select[name="inttimezone"]').val()
				},function(){
					window.location.href="<?= HOME_URL ?>emailTemplate.php?id=<?= $info['id'] ?>&type=finalInterviewerSched";
				});
			}
			
		}
	</script>
	
<?php
}else if($type=='testingInvitation'){
?>
	<div id="testInvDiv">
		<form action="" method="POST" onSubmit="return validateVal('testingInvitation')">
		<b>Testing Schedule</b>
		<table style="position:relative;">
			<tr><td>Date and Time:</td><td><input type="text" name="dateandtime" id="dateandtime" size="50" style="padding:5px;"></td></tr>
			<tr><td colspan=2><input type="submit" value="Generate Email" style="padding:5px;"><input type="hidden" name="submitType" value="generate"/></td></tr>
		</table>
		</form>
	</div>
<?php 
	if(isset($_POST) && !empty($_POST)){
		echo '<script> $(function(){ testHide(1); }); </script>';
	}else{
		echo '<script> $(function(){ testHide(0); }); </script>';
	}
?>	
	
	<script type="text/javascript">		
		function testHide(tt){
			if(tt==0){
				$('#emailContentDiv').hide();
				$('#edit').hide();
				$('#send').hide();
				$('#emailContentShow').hide();
			}else{
				$('#testInvDiv').hide();
			}
		}
	</script>
<?php
}


	$template = $db->selectSingleQueryArray('emailTemplates', 'template, templateName, toEdit' , 'templateType="'.$type.'"');
	
	if($type=='custom' || $type=='hmanager' && !empty($info)){
		$emailad = ''; 
		$mbody = '';		
		
		if($type=='hmanager'){
			$interviewer = $db->selectSingleQuery('jobReqData', 'supervisor' , 'positionID='.$info['position'].' AND status=0');
			$d2 = explode(' ', $interviewer);			
			$n = '';
			foreach($d2 AS $d):
				if(!empty($d)) $n .= $d.' ';
			endforeach;
			
			$dd = $ptDb->selectQueryArray('SELECT email FROM staff WHERE CONCAT(sFirst," ",sLast) = "'.rtrim($n,' ').'" LIMIT 1');
			if(isset($dd[0]['email']))
				$emailad = $dd[0]['email'];
		}else{
			$emailad = $info['email'];
			$mbody = '
				<p>Hello '.ucwords($info['fullname']).'!</p>
				<p>A pleasant day!</p>				
				<p>This is to confirm that we have received your application for the position of '.$position.'</p>
				<p><br/></p>
				<p><br/></p>
				<p><br/></p>
				<p>Please send your reply to <a href="mailto:careers.cebu@tatepublishing.net">careers.cebu@tatepublishing.net</a>.</p>
				<p>Thank you very much!</p>			
			'; 
		}

		$subject = 'Application for '.$position.': '.ucwords($info['fullname']).' ('.$info['id'].')';
		$to = $emailad;
		$message = $mbody;
	}else if($_POST['submitType']=='edit' && isset($info['email'])){
		$subject = $template['templateName'];
		$to = $info['email'];
		$message = $_POST['message'];
	}else if($type=='invitationtoapplyopenposition'){
		$oQuery = $db->selectQueryArray('SELECT title FROM jobReqData LEFT JOIN newPositions ON posID=positionID WHERE status = 0 GROUP BY positionID ORDER BY title');
		
		$arrValues['%OPENPOSITIONS%'] = '<ul>';
		foreach($oQuery AS $o){
			$arrValues['%OPENPOSITIONS%'] .= '<li>'.$o['title'].'</li>';
		}
		$arrValues['%OPENPOSITIONS%'] .= '</ul>';
		
		$subject = 'Job Opportunities for You in Tate Publishing';
		$emailContent = $template['template'];		
	}else if($type=='finalInterviewerSched'){
		$to = array();		
		$schedDetails = explode('|', $info['interviewSched']);		
		
		if(isset($schedDetails[1])) $to[] = $schedDetails[1];
		//get requestor
		$requestor = $db->selectSingleQueryArray('jobReqData', 'email, CONCAT(fname," ",lname) AS name' , 'positionID='.$info['position'].' AND status=0', 'LEFT JOIN staffs ON username=requestor');	
		if(isset($requestor['email'])) 
			$ccEmail = $requestor['email'];
		
		$rData = $ptDb->selectSingleQueryArray('staff', 'CONCAT(sFirst, " ", sLast) AS name, email', 'username="'.$requestor.'"');
		if(isset($rData['email']) && !in_array($rData['email'], $to)) $to[] = $rData['email'];
		
		//email body
		if(isset($schedDetails[0])) $arrValues['%INTERVIEWER%'] = $schedDetails[0];
		if(isset($schedDetails[2]) && isset($schedDetails[3])) $arrValues['%DATESCHEDULED%'] = date('F d, Y h:i A', strtotime($schedDetails[2])).' '.$schedDetails[3];
		$arrValues['%HRNOTE%'] = $db->selectSingleQuery('processStatusData', 'reason' , 'appID='.$id.' AND type="hr" AND testStatus="passed"');
			
		$emailContent = $template['template'];		
		//PS for requestor
		if(count($to)>1 && isset($rData['name'])){
			$emailContent .= '<p>&nbsp;</p><p><i>***<b>Note for '.$rData['name'].'</b>, you receive this email because you are the job requestor.</i></p>';
		}		
		
		$to = implode(',', $to);
		$subject = $template['templateName'].' Applicant for '.strtoupper($position).', '.$info['fullname'];
		$hideContent = true;
		
		echo '<table cellpadding=5>';
		echo '<tr><td colspan=2><b>Alert!</b><br/>The assigned final interviewer for this applicant is<br>';
		
			if(!empty($schedDetails[0])) echo '<b>'.$schedDetails[0].'</b>';
			else{
				echo '<b>NO ASSIGNED INTEVIEWER YET. PLEASE <a href="emailTemplate.php?id='.$id.'&type=finalInterview">CLICK HERE</a> TO ASSIGN INTERVIEWER.</b>';
				exit;
			} 
			if(isset($requestor['name'])) echo ' cc <b>'.$requestor['name'].'</b>';
			
		echo '</td></tr>';
		echo '<tr><td colspan=2><i>The below email will be sent to the above mentioned person to confirm scheduled interview:</i></td></tr>';
		echo '</table>';		
	}else if($type=='finalInterview'){
		$to = $info['email'];
		$subject = $template['templateName'];
		$emailContent = $template['template'];
		$hideContent = true;
		
		$intDetails = explode('|', $info['interviewSched']);
		if(isset($intDetails[2])) $arrValues['%DATEANDTIME%'] = date('F d, Y h:i A', strtotime($intDetails[2]));
		if(isset($intDetails[0])) $arrValues['%INTERVIEWER%'] = $intDetails[0];
		
	}else if(!empty($template)){
		$emailContent = $template['template'];
		$subject = $template['templateName'];
		
		if($template['toEdit']==1){			
			$to = $info['email'];			
			$hideContent = true;	
		}		
	}
	
	if(!isset($message)){
		if(isset($emailContent)){
			foreach($arrValues AS $a=>$val):
				$emailContent = str_replace($a, $val, $emailContent);
			endforeach;
			$message = $emailContent;
		}else{
			$message = '';
		}		
	}
	
	if(isset($hideContent) && $hideContent==true){
		echo '<div id="emailContentShow" style="padding-bottom:15px;">';
			echo '<table cellpadding=5>';
				echo '<tr><td>Subject:</td><td bgcolor="#ddd">'.$subject.'</td></tr>';
				echo '<tr><td valign="top">Body:</td><td bgcolor="#ddd">'.$message.'</td></tr>';
			echo '</table>';
		echo '</div>';		
	}
?>
	

<form action="" method="POST">
	<div id="emailContentDiv" style="padding:10px; <?= ((isset($hideContent) && $hideContent==true)?'display:none;':'') ?>">
		<b>Subject:</b> <input type="text" name="subject" value="<?= ((isset($_POST['subject']))?$_POST['subject']:$subject) ?>" style="width:100%;padding:5px;" required/><br/><br/>
		
		<b>To:</b> <span style="font-size:10px;font-style:italic; color:#777;"><?= (($type!='invitationtoapplyopenposition')?'(Separate email addresses with comma)':'') ?></span>
		<input type="text" name="email" value="<?= ((isset($_POST['email']))?$_POST['email']:$to) ?>" style="width:100%;padding:5px;" <?= (($type=='invitationtoapplyopenposition')?'placeholder="Application IDs, separated by commas"':'') ?> required/>
		<input type="checkbox" name="individually" <?= ((isset($_GET['type']) && $_GET['type']=='invitationtoapplyopenposition')?'checked':'') ?>/> <i>Send individually</i>
		<br/><br/>
	<?php
		if(isset($ccEmail)){
			echo '<b>Cc:</b> <input type="text" name="ccEmail" value="'.$ccEmail.'" style="width:100%;padding:5px;"/><br/><br/>';
		}
	?>	
				
		<b>Message:</b><textarea name="message" style="height:250px;"><?= ((isset($_POST['message']))?$_POST['message']:$message) ?></textarea><br/>
	</div>
<?php 
	if(isset($template['toEdit']) && $template['toEdit']==1){
		echo '<input type="button" id="edit" value="Edit Message" style="padding:5px 10px; background-color:gray; cursor:pointer; border:1px solid #000;"/>&nbsp;&nbsp;';
	} 
?>
	<input type="hidden" id="sendEdit" name="submitType" value="send"/>
	<input type="submit" id="send" value="Send Email" style="padding:5px 20px; background-color:green; cursor:pointer; border:1px solid #000;"/>

</form>

<script type="text/javascript">
	$(function () { 
		$('#dateandtime').datetimepicker({
			format:'F d, Y h:00 A',
			timepicker:true
		}); 
		
		$('.dateandtime').datetimepicker({
			format:'F d, Y h:00 A',
			timepicker:true
		});
		
		$('#edit').click(function(){
			$('#emailContentShow').hide();
			$('#emailContentDiv').show();
			$(this).hide();			
		});
		
		$('#send').click(function(){
			$('#sendEdit').val('send');
		});
	}); 
	
	function validateVal(t){
		valid = true;
		if(t=='finalInterview'){
			if($('#dateandtime').val()=='' || $('#interviewer').val()==''){
				valid = false;
				alert('Fill up all fields.');
			}				
		}else if(t=='testingInvitation' && $('#dateandtime').val()==''){
			valid = false;
			alert('Fill up all fields.');
		}
		return valid;
	}
</script>
</body>
</html>