<?php
require 'config.php';
$type = $_GET['type'];

$posted = 1;
if($type=='finalInterview' || $type=='testingInvitation')
	$posted = 0;

if(isset($_POST) && !empty($_POST)){
	if($_POST['submitType']=='generate' || $_POST['submitType']=='edit'){
		$posted = 1;
		$arrValues['%DATEANDTIME%'] = $_POST['dateandtime'];
		if($type=='finalInterview')
			$arrValues['%INTERVIEWER%'] = $_POST['interviewer'];
	}else if($_POST['submitType']=='send'){
		if(empty($_POST['subject']) || empty($_POST['email']) || empty($_POST['message'])){
			echo '<p style="color:red;">Unable to send message. Please check all empty fields.</p>';
		}else{
			$toId = array();
			
			if(isset($_GET['id'])) $toId[] = $_GET['id'];
			
			if($type=='invitationtoapplyopenposition'){
				$qArr = array();
				$explodeR = array_map('trim', explode(',', $_POST['email']));
					
				if(!empty($_POST['email']))
					$queryApp = $db->selectQueryArray('SELECT id, email FROM applicants WHERE id IN("'.implode('","', $explodeR).'")');
			
				foreach($queryApp AS $v){
					$qArr[$v['id']] = $v;
				}
				
				$noApp = '';
				$_POST['email'] = '';				
				$exCount = count($explodeR);
				
				for($c=0; $c<$exCount; $c++){
					if(!array_key_exists($explodeR[$c], $qArr))
						$noApp .= $explodeR[$c].', ';
					else if(!empty($qArr[$explodeR[$c]]['email'])){
						$_POST['email'] .= $qArr[$explodeR[$c]]['email'].', ';
						$toId[] = $explodeR[$c];
					}
				}
								
				if(!empty($noApp)){
					echo '<p style="color:red;">Email NOT sent to '.rtrim(trim($noApp), ',').'.  No email address or invalid input.</p>';
				}
			}
			
			if(!empty($_POST['email'])){
				$body = '<div style="font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:15px;">'.$_POST['message'].'</div>';
				sendEmail( 'careers.cebu@tatepublishing.net', $_POST['email'], $_POST['subject'], $body, 'Career Index Auto Email' );		
				$messageNote = '<b>Sent '.$_POST['subject'].' Auto-email</b><br/><div class="message">
								From: careers.cebu@tatepublishing.net<br/>
								To: '.rtrim(trim($_POST['email']), ',').'<br/>
								Subject: '.$_POST['subject'].'<br/>
								'.$_POST['message'].'
							</div>';
			
				foreach($toId AS $t){
					addStatusNote($t, 'email', '', '', $messageNote);
				}
				
				echo '<div style="font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:15px;">Email Sent to '.rtrim(trim($_POST['email']),',').'</div>';
			}else{
				echo '<p style="color:red;">Email not sent. Empty recipient.</p>';
			}
			exit;
		}		
		
	}
}

if(isset($_GET['id'])){
	$info = $db->selectSingleQueryArray("applicants","id, CONCAT(fname,' ',lname) AS fullname, position, isNew, email","id=".$_GET['id']);

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
?>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Email Template</title>
	<style type="text/css">
		body{ font-family:'Open Sans','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; }
	</style>
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
<?php if($type=='finalInterview' && $posted==0){ 
	$interviewer = '';
	if($info['isNew']==1)
		$interviewer = $db->selectSingleQuery('jobReqData', 'supervisor' , 'positionID='.$info['position'].' AND status=0');
?>
	<form action="" method="POST" onSubmit="return validateVal('finalInterview')">
	<b>Interview Schedule</b>
	<table style="position:relative;">
		<tr><td>Date and Time:</td><td><input type="text" name="dateandtime" id="dateandtime" size="50" style="padding:5px;"></td></tr>
		<tr><td>Interviewer:</td><td><input type="text" name="interviewer" id="interviewer" value="<?= $interviewer ?>" size="50"style="padding:5px;"></td></tr>
		<tr><td colspan=2><input type="submit" value="Generate Email" style="padding:5px;"><input type="hidden" name="submitType" value="generate"/></td></tr>
	</table>
	</form>
	
<?php
}else if($type=='testingInvitation' && $posted==0){
?>
	<form action="" method="POST" onSubmit="return validateVal('testingInvitation')">
	<b>Testing Schedule</b>
	<table style="position:relative;">
		<tr><td>Date and Time:</td><td><input type="text" name="dateandtime" id="dateandtime" size="50" style="padding:5px;"></td></tr>
		<tr><td colspan=2><input type="submit" value="Generate Email" style="padding:5px;"><input type="hidden" name="submitType" value="generate"/></td></tr>
	</table>
	</form>
<?php
}

if($posted==1){
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
		$oQuery = $db->selectQueryArray('SELECT title FROM jobReqData LEFT JOIN newPositions ON posID=positionID WHERE status = 0 GROUP BY jobReqID ORDER BY title');
		
		$arrValues['%OPENPOSITIONS%'] = '<ul>';
		foreach($oQuery AS $o){
			$arrValues['%OPENPOSITIONS%'] .= '<li>'.$o['title'].'</li>';
		}
		$arrValues['%OPENPOSITIONS%'] .= '</ul>';
		
		$emailContent = $template['template'];
		foreach($arrValues AS $a=>$val):
			$emailContent = str_replace($a, $val, $emailContent);
		endforeach;
		
		$subject = 'Job Opportunities for You in Tate Publishing';
		$message = $emailContent;
		
	}else if(!empty($template) && $template['toEdit']==1){
		$emailContent = $template['template'];
		foreach($arrValues AS $a=>$val):
			$emailContent = str_replace($a, $val, $emailContent);
		endforeach;
		
		
		$subject = $template['templateName'];
		$to = $info['email'];
		$message = $emailContent;
		$hideContent = true;
		
		echo $emailContent;
	}
?>
	

<form action="" method="POST">
<?php if(isset($hideContent) && $hideContent==true){ echo '<div style="display:none;">'; } ?>
	<b>Subject:</b> <input type="text" name="subject" value="<?= $subject ?>" style="width:100%;padding:5px;"/><br/>
	
	<b>To:</b> <span style="font-size:10px;font-style:italic; color:#777;"><?= (($type!='invitationtoapplyopenposition')?'(Separate email addresses with comma)':'') ?></span>
	<input type="text" name="email" value="<?= $to ?>" style="width:100%;padding:5px;" <?= (($type=='invitationtoapplyopenposition')?'placeholder="Application IDs, separated by commas"':'') ?>/><br/>
	
	<b>Message:</b><textarea name="message" style="height:270px;"><?= $message ?></textarea><br/>
<?php if(isset($hideContent) && $hideContent==true){ echo '</div>'; } ?>	

	<input type="hidden" id="sendEdit" name="submitType" value="send"/>
	<input type="submit" id="send" value="Send Email" style="padding:5px;"/>
<?php 
	if(isset($template['toEdit']) && $template['toEdit']==1 && (!isset($_POST) || $_POST['submitType'] != 'edit')){
		echo '<input type="submit" id="edit" value="Edit" style="padding:5px;"/>';
	} 
?>
</form>
<?php } ?>
<script type="text/javascript">
	$(function () { 
		$('#dateandtime').datetimepicker({
			format:'F d, Y h:00 A',
			timepicker:true
		}); 
		
		$('#edit').click(function(){
			$('#sendEdit').val('edit');
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