<?php
require 'config.php';

$posted = 1;
if($_GET['type']=='finalInterview' || $_GET['type']=='testingInvitation')
	$posted = 0;

if(isset($_POST) && !empty($_POST)){
	if($_POST['submitType']=='send'){
		$body = '<div style="font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:15px;">'.$_POST['message'].'</div>';
		
		sendEmail( 'careers.cebu@tatepublishing.net', $_POST['email'], $_POST['subject'], $body, 'Career Index Auto Email' );		
		$messageNote = '<b>Sent '.$_POST['subject'].' Auto-email</b><br/><div class="message">
						From: careers.cebu@tatepublishing.net<br/>
						To: '.$_POST['email'].'<br/>
						Subject: '.$_POST['subject'].'<br/>
						'.$_POST['message'].'
					</div>';
		addStatusNote($_GET['id'], 'email', '', '', $messageNote);		
		
		echo '<div style="font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:15px;">Email Sent</div>';
		exit;
	}else if($_POST['submitType']=='generate' || $_POST['submitType']=='edit'){
		$posted = 1;
		$arrValues['%DATEANDTIME%'] = $_POST['dateandtime'];
		if($_GET['type']=='finalInterview')
			$arrValues['%INTERVIEWER%'] = $_POST['interviewer'];
	}
}


$info = $db->selectSingleQueryArray("applicants","id, CONCAT(fname,' ',lname) AS fullname, position, isNew, email","id=".$_GET['id']);

$position = '';
if($info['isNew']==0){
	$position = $db->selectSingleQuery("positions", "title" , "id=".$info['position']);
}else if(!empty($info['position'])){	
	$position = $db->selectSingleQuery("newPositions", "title" , "posID=".$info['position']);	
}

$arrValues['%NAME%'] = $info['fullname'];
$arrValues['%POSITION%'] = $position;
	
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
<?php if($_GET['type']=='finalInterview' && $posted==0){ 
	$interviewer = '';
	if($info['isNew']==1)
		$interviewer = $db->selectSingleQuery('jobReqData', 'supervisor' , 'positionID='.$info['position'].' AND status=0');
?>
	<form action="" method="POST" onSubmit="return validateVal('finalInterview')">
	<b>Interview Schedule</b>
	<table style="position:relative;">
		<tr><td>Date and Time:</td><td><input type="text" name="dateandtime" id="dateandtime" size="50"></td></tr>
		<tr><td>Interviewer:</td><td><input type="text" name="interviewer" id="interviewer" value="<?= $interviewer ?>" size="50"></td></tr>
		<tr><td colspan=2><input type="submit" value="Generate Email"><input type="hidden" name="submitType" value="generate"/></td></tr>
	</table>
	</form>
	
<?php
}else if($_GET['type']=='testingInvitation' && $posted==0){
?>
	<form action="" method="POST" onSubmit="return validateVal('testingInvitation')">
	<b>Testing Schedule</b>
	<table style="position:relative;">
		<tr><td>Date and Time:</td><td><input type="text" name="dateandtime" id="dateandtime" size="50"></td></tr>
		<tr><td colspan=2><input type="submit" value="Generate Email"><input type="hidden" name="submitType" value="generate"/></td></tr>
	</table>
	</form>
<?php
}

if($posted==1){
?>
<form action="" method="POST">
<?php
	$template = $db->selectSingleQueryArray('emailTemplates', 'template, templateName' , 'templateType="'.$_GET['type'].'"');
	
	if($_GET['type']=='custom' || $_GET['type']=='hmanager'){
		$emailad = ''; 
		$mbody = '';
		$sub = 'Application for '.$position.': '.$info['fullname'].' ('.$info['id'].')';
		
		if($_GET['type']=='hmanager'){
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
				<p>Hello '.$info['fullname'].'!</p>
				<p>A pleasant day!</p>				
				<p>This is to confirm that we have received your application for the position of '.$position.'</p>
				<p><br/></p>
				<p><br/></p>
				<p><br/></p>
				<p>Please send your reply to <a href="mailto:careers.cebu@tatepublishing.net">careers.cebu@tatepublishing.net</a>.</p>
				<p>Thank you very much!</p>			
			'; 
		}	
		echo '<b>Subject:</b> <input type="text" name="subject" value="'.$sub.'" style="width:100%;padding:5px;"/><br/>';
		echo '<b>To:</b> <span style="font-size:10px;font-style:italic; color:#777;">(Separate email addresses with comma)</span><input type="text" name="email" value="'.$emailad.'" style="width:100%;padding:5px;"/><br/>';
		echo '<b>Message:</b><textarea name="message" style="height:270px;">'.$mbody.'</textarea><br/>';
	}else if($_POST['submitType']=='edit'){
		echo '<b>Subject:</b> <input type="text" name="subject" value="'.$template['templateName'].'" style="width:100%;padding:5px;"/><br/>';
		echo '<b>To:</b> <input type="text" name="email" value="'.$info['email'].'" style="width:100%;padding:5px;"/><br/>';
		echo '<b>Message:</b><textarea name="message" style="height:270px;">'.$_POST['message'].'</textarea><br/>';
	}else{
		$emailContent = $template['template'];
		foreach($arrValues AS $a=>$val):
			$emailContent = str_replace($a, $val, $emailContent);
		endforeach;
		
		echo $emailContent;
		echo '<input type="hidden" name="message" value="'.$emailContent.'" />';
		echo '<input type="hidden" name="subject" value="'.$template['templateName'].'" />';
		echo '<input type="hidden" name="email" value="'.$info['email'].'" />';
	}
?>
	
	<input type="hidden" id="sendEdit" name="submitType" value="send"/>
	<input type="submit" id="send" value="Send Email" />
<?php 
	if($_GET['type']!='custom' && $_GET['type']!='hmanager' && (!isset($_POST) || $_POST['submitType'] != 'edit'))
	{
		echo '<input type="submit" id="edit" value="Edit"/>';
	} 
?>
</form>
<?php } ?>
<script type="text/javascript">
	$(function () { 
		$('#dateandtime').datetimepicker({
			format:'F d, Y h:s A',
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