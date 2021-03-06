<?php
//ini_set('display_errors', 1); 
	require 'config.php';
	require_once('includes/labels.php');
	date_default_timezone_set("Asia/Manila");
	setlocale(LC_MONETARY, 'en_US');
		
	if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
		echo '<script>window.parent.location = "'.HOME_URL.'login.php";</script>';
		exit();
	}

	//get access level for HR and user
	$access_level = $db->selectSingleQuery('pt_users', 'level', 'username = "'.$_SESSION['u'].'"');
	$authorized_access_level = 1;
	
	$id = $applicant_id = $_GET['id'];
	
	$info = $db->selectSingleQueryArray('applicants','*','id="'.$id.'"');	
	
	$recProcess = $db->selectQueryArray('SELECT * FROM recruitmentProcess');	
	
	
	if($info['isNew']==0){
		$position_array = $db->selectSingleQueryArray('positions', 'id, title' , 'id="'.$info['position'].'"');
		$position = $position_array['title'];
		$positions_id = $position_array['id'];
	}else if(!empty($info['position'])){
		$pp = $db->selectSingleQueryArray('newPositions', 'title, org, dept, grp, subgrp, requiredTest, posID' , 'posID="'.$info['position'].'"');
		$position = $pp['title'];
		$positions_id = $pp['posID'];
		$info += $pp;
	}
	
	
	$hiring_manager_name = $db->selectSingleQuery("jobReqData", "supervisor" , 'positionID = '.$positions_id.' AND status = 0 ORDER BY reqID ASC');
	$hiring_manager_email = $db->selectSingleQuery("staffs", 'email', 'CONCAT(fname, " ", lname) LIKE "%'.str_replace(' ', '%', $hiring_manager_name).'%"');
	$maxSal = $db->selectSingleQuery('jobReqData', 'salary' , 'positionID="'.$info['position'].'" ORDER BY startDate', 'LEFT JOIN salaryRange ON maxSal=salID');
	
	//job offers generated
	$gen = $db->selectQueryArray('SELECT * FROM generatedJO WHERE appID='.$id.' ORDER BY timestamp DESC');	
	
	$joID = $jo_id = $_GET['joID'];
	$response = $_GET['response'];

	if( ( isset($joID) AND !empty($joID) ) OR (isset($_POST['edit_jo_id']) AND !empty($_POST['edit_jo_id']) ) ){
		$jo_id = isset($joID) ? $joID : $_POST['edit_jo_id'];
		$jo_details = $db->selectSingleQueryArray('generatedJO', '*' , 'joID='.$jo_id);
	}
	
	if( isset($response) ){
		if( $response == 'N' ){
			//update jo status
            $update_array = array('status' => 2);			
            addStatusNote($id, 'genJobOffer', 'admin', $info['position'], 'Job Offer Generated has declined by Hiring Manager');
		} else if( $response == 'Y' ) {
			$update_array = array('status' => 1);
			//if approved send notification to recruitment
			
			//update jo status
			
			$from = $hiring_manager_email;
			$to = 'careers.cebu@tatepublishing.net';
			$body = "
				<p>Job Offer for Job Req {$jo_reg_id} {$position}, {$info['fname']} {$info['lname']} is approved by hiring manager {$hiring_manager_name}</p>
				<p>Go to CPH recruitment manager, download the approved generated job offer and schedule job offer with the applicant.</p>
			";
			$subject = 'CPH Job Offer Generated Approved by the Hiring Manager';
            // sendEmail( $from, $to, $subject, $body, $hiring_manager_name);
			addEmailStatusNote( $id, $subject, $hiring_manager_email, $body);
            
		}
		$db->updateQuery('generatedJO', $update_array, 'joID = '.$joID);
	}
	
	


	if(isset($_GET['pos'])){
		if($_GET['pos']=='back'){
			$db->updateQuery('applicants', array('process' => $_GET['stat']), 'id='.$id);
			if($_GET['stat']==5)
				addStatusNote($id, 'changeStatus', '', $info['position'], 'Status changed from Hired to Job Offering');
				
			echo '<script>window.location.href="editstatus.php?id='.$id.'";</script>';
		}else if($_GET['pos']=='reprof'){
			echo $db->selectSingleQuery('jobReqData', 'positionID' , 'status=0 AND positionID="'.$_GET['posid'].'"');
			
		}else if($_GET['pos']=='testing'){
			if($_POST['type']=='jobOffer'){
				$off = $db->selectSingleQueryArray('generatedJO', 'joID, offer' , 'appID='.$_POST['appID'].' ORDER BY timestamp DESC');
				addStatusNote($_POST['appID'], $_POST['type'], $_POST['testStatus'], $off['joID'], $_POST['reason']);
				if($_POST['testStatus']=='accepted'){
					$db->updateQuery('applicants', array('salaryOffer' => $off['offer']), 'id="'.$_POST['appID'].'"');
				}
			}else{
				addStatusNote($_POST['appID'], $_POST['type'], $_POST['testStatus'], $_POST['positionID'], $_POST['reason']);
			}
			
			//check if hr passed and update MRB status and employees referral
			if($_POST['type']=='hr' && $_POST['testStatus']=='passed'){
				$refID = $db->selectSingleQuery('applicants', 'referrerID' , 'id="'.$_POST['appID'].'"');
				if($refID!=0){
					//insert into staffReferralBonus table
					$insArr['empID_fk'] = $refID;
					$insArr['appID_fk'] = $_POST['appID'];
					$insArr['bonus'] = 100;
					$insArr['dateAdded'] = date('Y-m-d H:i:s');
					$db->insertQuery('staffReferralBonus', $insArr);
					
					$bonusInfo = $db->selectQueryArray('SELECT bonusID FROM staffReferralBonus WHERE empID_fk="'.$refID.'" AND status=0');
					
					if(count($bonusInfo)==5){
						$name = $db->selectSingleQuery('staffs', 'CONCAT(lname,", ",fname) AS name' , 'empID="'.$refID.'"');
						$from = 'careers.cebu@tatepublishing.net';
						$to = 'accounting.cebu@tatepublishing.net,hr.cebu@tatepublishing.net';
						$ebody = '<p>Dear Accounting Team:</p>
							<p>Please be informed that employee '.$name.' has successfully referred five new applicants to apply in CPH.
							HR has validated that the applicants referred are valid and may be hired in any of the open positions in Tate.
							As a reward, he will be entitled to PHP 500.00 Referral Bonus.</p>
							<p>Please reply to this email to confirm when this bonus can be released.
							You will receive a reminder on this on (2 business days later), until the date is confirmed in CPH.</p>
							<p><br/></p>
							<p>Thank you very much!</p>';
							
						// sendEmail( $from, $to, 'Mini Referral Bonus', $ebody, 'Career Index Auto Email' );
						
						$valID = '';
						foreach($bonusInfo AS $v){
							$valID .= $v['bonusID'].',';
						}
						
						$mrbID = $db->insertQuery('staffMRB', array('empID_fk'=>$refID, 'releasedAmount'=>'500', 'bonusIDs'=>rtrim($valID,',')));//insert to MRB table
					}
				}			
			}
			
			if($_POST['testStatus']=='failed'){
				$freeze = array('iq', 'typing', 'written', 'hr', 'final');
				if(in_array($_POST['type'], $freeze))
					$db->updateQuery('applicants', array('processStat' => '0', 'processText' => 'Failed '.$processLabels[$_POST['type']]), 'id='.$_POST['appID']);
				else
					$db->updateQuery('applicants', array('processText' => 'Failed '.$processLabels[$_POST['type']]), 'id='.$_POST['appID']);
			}
		}else if($_GET['pos']=='freeze'){
			$db->updateQuery('applicants', array('processStat' => '0'), 'id='.$_POST['appID']);
			addStatusNote($_GET['appID'], 'changeStatus', '', $_POST['positionID'], 'Freeze Application');
		}else if($_GET['pos']=='interviewSched'){
			$interviewer = $_POST['interviewer'];
			$sched = date('Y-m-d H:i', strtotime($_POST['dateNtime']));
			$timezone = $_POST['timezone'];
			$db->updateQuery('applicants', array('interviewSched' => $interviewer.'|'.$sched.'|'.$timezone), 'id="'.$_POST['appID'].'"');
		}else if($_GET['pos']=='agencySelect'){
			$sel = $db->selectQuery('agencies', '*', 'agencyID="'.$_POST['id'].'"');
			echo(json_encode($sel));
		}
		exit;		
	}
	
	if(isset($_POST) && !empty($_POST)){		
		if($_POST['formSubmit']=='reprofile'){
			$db->updateQuery('applicants', array('position' => $_POST['newPos'], 'isNew' => 1), 'id='.$id);
			
			$updateArr['processStat'] = 1;
			$updateArr['processText'] = 'Reprofiled';
			if($_POST['prevStat']>2){
				$updateArr['process'] = 2;
			}

			$db->updateQuery('applicants', $updateArr, 'id='.$id);		
			
			if($info['isNew']==0)
				$old = $db->selectSingleQuery("positions", "title" , "id=".$info['position']);
			else
				$old = $db->selectSingleQuery('newPositions', 'title' , 'posID='.$info['position']);
			
			$newP = $db->selectSingleQuery('newPositions', 'title' , 'posID='.$_POST['newPos']);
			addStatusNote($id, 'reprofiled', 'reprofiled', $info['position'], '<b>Reprofiled from '.$old.' to '.$newP.'<br/>'.$_POST['reason'].'</b>');
			echo '<script>alert("Reprofiled");</script>';
			
		}else if( $_POST['formSubmit']=='cancel' && !empty($_POST['reason'])){		
			$stat = 0;
			$txt = 'Cancelled';
			$label = '';
			if($_POST['cancelMessage']!=''){
				$m = explode('|', $_POST['cancelMessage']);
				$stat = $m[1];
				$txt .= ' ('.$m[0].')';
				$label = $m[2];
			}
			
		
			$db->updateQuery('applicants', array('processStat' => "$stat", 'processText' =>$txt), 'id='.$id);		
			addStatusNote($id, 'cancelled', 'cancelled', $info['position'], $txt.'<br/>'.$_POST['reason']);
			if(!empty($label) && isset($m[3])){
				addStatusNote($id, $label, $m[3], $info['position'], $txt.'<br/>'.$_POST['reason']);
			}			
		}else if( $_POST['formSubmit']=='advanceProcess'){
			if($_POST['process']==6){
				$db->updateQuery('applicants', array('process' => $_POST['process'], 'pHEnrolled'=>1, 'pHBDay'=>1, 'pHCompensation'=>1, 'batchID'=>1, 'referral'=>1 , 'pictureTaken'=>1 , 'biometricsEnrolled'=>1 ), 'id='.$id);
			}else{
				$db->updateQuery('applicants', array('process' => $_POST['process'], 'processText'=>''), 'id='.$id);
			}
			addStatusNote($id, 'advance', '', $info['position'], 'Advanced to '.$recProcess[$info['process']+1]['processType']);
		}else if($_POST['formSubmit'] == 'Generate' || $_POST['formSubmit'] == 'Regenerate'){		
			$joTxt = 'Job offer is: Php'.number_format($_POST['salary'], 2).'<br/>Start date is: '.$_POST['startDate'];			
			if(!empty($_POST['salReason'])){
				$joTxt .= '<br/>Reason:'.$_POST['salReason'];
			}	
			if(isset($_POST['genReason']) && !empty($_POST['genReason'])){
				$joTxt .= '<br/>Regenerate Reason:'.$_POST['genReason'];
			}
			addStatusNote($id, 'genJobOffer', 'admin', $info['position'], $joTxt);
			$gJO = array('appID' => $id, 
						'offer' => number_format($_POST['salary'], 2), 
						'startDate' => date('Y-m-d', strtotime($_POST['startDate']) ), 
						'offeredBy' => $_SESSION['u']);
			$joInsID = $db->insertQuery('generatedJO', $gJO);
			
			if($_POST['prefix']=='Ms.') $db->updateQuery('applicants', array('gender' => 'female'), 'id='.$id);
			else $db->updateQuery('applicants', array('gender' => 'male'), 'id='.$id);				
				
			$from = 'careers.cebu@tatepublishing.net';
			$to = $hiring_manager_email;
			$applicant_name = $info['fname'].' '.$info['lname'];
            $approve_url = HOME_URL .'editstatus.php?id='.$applicant_id.'&joID='.$joInsID.'&response';
            $job_offer_file = 'gen_jo.php?jo_id='.$joInsID.'&method=I&appID='.$applicant_id;
			
			$ebody = '<p>Hi '. $hiring_manager_name .', </p>
			<p>Job Offer to applicant '. $applicant_name .' for the position of '.$position.' has been generated.<br/>
			You may view the job offer <a href="'.HOME_URL . $job_offer_file .'">here</a><br/>
			If you agree with the job offer, please click the link below to approve the job offer.<br/>
			<a href="'.$approve_url.'=Y">'.$approve_url.'=Y</a><br/>
			Otherwise, please click the link below to deny the job offer.<br/>
			<a href="'.$approve_url.'=N">'.$approve_url.'=N</a><br/>';			
			
			$subject = 'CPH Job Offer Generated for your approval';
            // sendEmail( $from, $to, $subject, $ebody, 'Career Index Auto Email' );			
            addEmailStatusNote($id, $subject, $to, $ebody);
			//end send email
			
			
			
		}else if($_POST['formSubmit']=='checklist'){	
		
			$uArr['pHEnrolled'] = false;
			$uArr['pHBDay'] = false;
			$uArr['pHCompensation'] = false;
			$uArr['batchID'] = false;
			$uArr['referral'] = false;
			$uArr['pictureTaken'] = false;
			$uArr['biometricsEnrolled'] = false;	
			
			if(isset($_POST['pHEnrolled']) && $_POST['pHEnrolled']=='on') $uArr['pHEnrolled'] = true;
			if(isset($_POST['pHBDay']) && $_POST['pHBDay']=='on') $uArr['pHBDay'] = true;
			if(isset($_POST['pHCompensation']) && $_POST['pHCompensation']=='on') $uArr['pHCompensation'] = true;
			if(isset($_POST['batchID']) && $_POST['batchID']=='on') $uArr['batchID'] = true;
			if(isset($_POST['referral']) && $_POST['referral']=='on') $uArr['referral'] = true;
			if(isset($_POST['pictureTaken']) && $_POST['pictureTaken']=='on') $uArr['pictureTaken'] = true;
			if(isset($_POST['biometricsEnrolled']) && $_POST['biometricsEnrolled']=='on') $uArr['biometricsEnrolled'] = true;
			
			$db->updateQuery('applicants', $uArr, 'id='.$_POST['appID']);			
		}else if($_POST['formSubmit']=='agencyUpload'){			
			$filname = '';			
			$toupload = '';			
			$tochange = '';			
			if(!empty($_FILES['letter']['name'])){
				$n = array_reverse(explode('.', $_FILES['letter']['name']));	
				$filname = 'endorsementletter.'.$n[0];
				$toupload = $_FILES['letter']['tmp_name'];
				$tochange = 'endorsementLetter';
			}else if(!empty($_FILES['signed']['name'])){
				$n = array_reverse(explode('.', $_FILES['signed']['name']));	
				$filname = 'signedguideline.'.$n[0];
				$toupload = $_FILES['signed']['tmp_name'];
				$tochange = 'signedGuidelines';
			}
			
			if(!empty($toupload)){
				$uploadDIR = 'uploads/applicants/'.$id.'/';
				if (!file_exists($uploadDIR)) {
					mkdir($uploadDIR, 0755, true);
					chmod($uploadDIR.'/', 0777);
				}
				
				move_uploaded_file($toupload, $uploadDIR.'/'.$filname);
				$db->updateQuery('applicants', array($tochange=>$filname), 'id="'.$id.'"');
			}
			echo '<script> alert("Uploaded.");</script>';
			
		}else if($_POST['formSubmit']=='removeFile'){
			$uploadDIR = 'uploads/applicants/'.$id.'/';
			unlink($uploadDIR.$info[$_POST['fld']]);
			
			$db->updateQuery('applicants', array($_POST['fld']=>''), 'id="'.$id.'"');
						
		} else if( isset($_POST['edit_jo']) AND isset($_POST['edit_jo_id']) ){
			
			//send notification to hr
			$jo_error = array();
			
			$orig_jo_details = $jo_details;
			$new_jo_details = array('offer' => str_replace(',', '', $_POST['edit_offer']), 'startDate' => $_POST['edit_startDate'] );
			if( $new_jo_details['offer'] > $maxSal ){
				$jo_error = array('Salary should not exceed with the maximum salary ('. $maxSal .') offered on the job requisition.');
				$_SESSION['jo_error'] = $jo_error;
			}
			
			//no error then send notification and proceed
			if( count($jo_error) < 1 ){
				
				unset($_SESSION['jo_error']);
				$_SESSION['jo_updated'] = true;
				
				//update jo status
				$update_array = array('status' => 3, 'offer' => $new_jo_details['offer'], 'startDate' => $new_jo_details['startDate'] );
				$db->updateQuery('generatedJO', $update_array, 'joID = '.$_POST['edit_jo_id']);
				
				$from = $hiring_manager_email;
				$to = 'careers.cebu@tatepublishing.net';
				$body = "
					<p>Hiring Manager {$hiring_manager_name} has dispproved your job offer. Below updated details are approved.</p>
					<p>Start Date: <span style='font-weight: bold; text-decoration:underline;'> {$new_jo_details['startDate']}</span></br>
					Salary Offer: <span style='font-weight: bold; text-decoration:underline;'> {$new_jo_details['offer']}</span>
					</p>
					<p>If you feel that there is a problem with the above job offer, please communicate directly with hiring manager and come back here when you have come to an agreement.</p>
					<p>Please download the approved job offer.</p>
				";
				$subject = 'CPH Job Offer Generated disapproved and updated';
				sendEmail( $from, $to, $subject, $body, $hiring_manager_name);
				addEmailStatusNote( $id, $subject, $hiring_manager_email, $body);
				
			} 
		}
		
		if(isset($_POST['formSubmit']) && $_POST['process']==4){
			echo '<script>alert("Please input final interview schedule.");</script>';
		}
		
		echo '<script>window.location.href="'.$_SERVER['REQUEST_URI'].'";</script>';
		exit; 
	}
	
	function generateJOTable( $gen, $id ){
		echo '
		
				<div style="max-width:600px; width: 400px;">
				<h5>Generated Job Offers</h5>
				
				<table width="100%" cellpadding=5>					
					<thead>
						<th>Generated by</th>
						<th>Offer</th>
						<th>Start Date</th>
						<th>Status</th>
						<th>View</th>
						<th>Download</th>
					</thead>';
				
					$c=0;
					foreach($gen AS $g):
				
						if($c==0) echo '<tr bgcolor="#ce2029" style="color:#fff; font-weight:bold;">';
						else if($c%2==0) echo '<tr bgcolor="#dcdcdc">';
						else echo '<tr>';
						echo '
								<td>'.$g['offeredBy'].' | <span style="font-size:10px;">'.$g['timestamp'].'</span></td>
								<td>Php '.$g['offer'].'</td>
								<td>'.date('F d, Y', strtotime($g['startDate'])).'</td>';
								
						switch( $g['status'] ){
							case 0: echo '<td>waiting on approval</td>';break;
							case 1: echo '<td>approved</td>';break;
							case 2: echo '<td>disapproved</td>';break;
							case 3: echo '<td>disapproved with update</td>';break;
							
						}
						
							echo '<td><a href="'.HOME_URL.'gen_jo.php?jo_id='.$g['joID'].'&method=I&appID='.$id.'" target="_blank"><img src="img/attach_file.png"></a></td>';
							echo '<td><a href="'.HOME_URL.'gen_jo.php?jo_id='.$g['joID'].'&method=D&appID='.$id.'" target="_blank"><img src="images/view_icon.png"></a></td>';
						
						echo '</tr>';
						
						$c++;
					endforeach;
			
			echo '	</table>	
				</div>
			';
	}
		
	
	function techTest($type, $s, $rows='3', $options=''){
		global $db, $id, $info, $processLabels;
		
		$styl ='';
		if($s==1) $styl = 'bgcolor="#dcdcdc"';
		
		
		if($type=='iq' || $type=='typing' || $type=='written')
			$testQ = $db->selectSingleQueryArray('processStatusData', 'testStatus, reason, positionID' , 'appID="'.$id.'" AND type="'.$type.'"');
		else if($type=='jobOffer'){
			$offID = $db->selectSingleQuery('generatedJO', 'joID' , 'appID='.$id.' ORDER BY timestamp DESC');
			$testQ = $db->selectSingleQueryArray('processStatusData', 'testStatus, reason, positionID' , 'appID="'.$id.'" AND type="'.$type.'" AND positionID='.$offID);		
		}else
			$testQ = $db->selectSingleQueryArray('processStatusData', 'testStatus, reason, positionID' , 'appID="'.$id.'" AND type="'.$type.'" AND positionID='.$info['position']);
		
		
		$txt = '
		<tr '.$styl.'>
			<td width="40%" valign="top"><b>'.strtoupper($processLabels[$type]).'</b></td>
			<td>';
		if(isset($testQ['testStatus']) && 
			(( $testQ['positionID'] == $info['position'] || $type=='iq' || $type=='typing' || $type=='written') ||
			($type=='jobOffer' && $testQ['positionID'] == $offID ))
		){
			$txt .=	'<input type="text" disabled class="form-control" id="'.$type.'" value="'.ucfirst($testQ['testStatus']).'"/>
				<a href="javascript:void(0);" onClick="hidenote(this, \''.$type.'\')">Show Note</a>
				<div id="note'.$type.'" class="hidden">NOTE: '.nl2br($testQ['reason']).'</div>';
		}else{
			if(empty($options)){
				$txt .=	'<select class="form-control" onChange="showDiv(\''.$type.'\')" id="'.$type.'">
						<option></option value=""><option value="passed">Pass</option><option value="failed">Fail</option>
					</select>';
			}else{
				$op = explode('|', $options);
				$txt .= '<select class="form-control" onChange="showDiv(\''.$type.'\')" id="'.$type.'"><option></option value="">';
				$cntChina = count($op);
				for($i=0;$i<$cntChina;$i++){
					$txt .= '<option value="'.$op[$i].'">'.ucfirst($op[$i]).'</option>';
				}
				$txt .= '</select>';
			}
			
		}
					
			if(($type=='hr' || $type=='final') && !empty($testQ['testStatus'])){
				$txt .=	'<div id="'.$type.'Div">';
				$txt .=		'<textarea class="form-control" id="'.$type.'reason" rows="'.$rows.'" disabled>'.$testQ['reason'].'</textarea>						
						</div>';
				
			}else{
				$txt .=	'<div id="'.$type.'Div" style="display:none;">';
				$txt .=		'<textarea class="form-control" id="'.$type.'reason" rows="'.$rows.'"></textarea>
							<input type="button" value="Submit" onClick="checkTest(\''.$type.'\')"/>
						</div>';
			}
		
		$txt .=	'</td>
		</tr>';
		return $txt;
	}
	
	$showhire = 1;	
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">	
	<title>Edit Status of <?= ucfirst($info['fname']).' '.ucfirst($info['lname']) ?></title>
	<link href="css/yeti.bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="css/progressbar.css" type="text/css" />
	<style type="text/css">
		.pad5px{ padding:5px; }		
		.gen{ display:none; }
		.jo_deny {			margin-top: 15px; }
		.jo_deny	ul { list-style: none;	}
		.jo_deny	li { margin-bottom: 5px; display: block; }
		.jo_deny	label { display: block; width: 50%; float: left; text-align: right; margin-right: 10px; line-height: 35px; }
		.jo_deny	div.form_control { text-align: left; }
		.jo_deny	span { display: block; }		
	</style>
	<script src="js/jquery.js"></script>
	<!-- Date time picker -->
	<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/ >
	<script src="js/jquery.datetimepicker.js"></script>
	
</head>
<body>
<h3>Recruitment Status of <?= ucfirst($info['fname']).' '.ucfirst($info['lname']) ?></h3>
<?php if($info['isNew']==0){ ?>
	<p><b>Old Position Applied:</b> <?= $position ?> <span style="color:red">(Reprofile Needed)</span></p>
<?php }else{ ?>
	<p><b>Position Applied:</b> <?= $position ?> <?php if($info['processStat']==2){ echo '<span style="color:red">(for reprofile)</span>';} ?></p>
<?php } ?>
<table width="100%">
	<tr><td>
		<div class="wizard-steps">
		<?php
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
	<tr><td><br/></td></tr>
	<?php if( $access_level == 2 ){ ?>
		<tr>
			<td align="center">
    <?php 		
		if( isset($joID) AND isset($response) ){			
			generateJOTable($gen, $id);	
			
			if($response == 'N' AND !isset($_SESSION['jo_updated'])){ ?>
			<div class="jo_deny">
				<?php if( isset($_SESSION['jo_error']) ) {
					foreach( $_SESSION['jo_error'] AS $error ){
						echo '<p style="font-weight: bold; color: #f00;">'.nl2br($error).'</p>';
					}
					
				} else if( isset($_SESSION['jo_updated']) ){
					echo '<p style="font-weight: bold; color: #f00;">Job Offer has been updated.</p>';
				} ?>
				<form name="jo_deny" action="" method="post">
				<input type="hidden" name="edit_jo_id" value="<?php echo $jo_id; ?>" />
					<ul>
						<li><span style="font-weight: bold;">Edit details of Job Offer:</span></li>
						<li>
							<label for="edit_start_date">Start date:</label> 
							<div class="form_control"><input type="date"  name="edit_startDate" value="<?php echo isset($_POST['edit_startDate']) ? $_POST['edit_startDate'] : $jo_details['startDate']; ?>" placeholder="<?php echo isset($_POST['edit_startDate']) ? $_POST['edit_startDate'] : $jo_details['startDate']; ?>" id="edit_start_date" ></div>
						</li>
						<li>
							<label for="edit_salary">Salary offer:</label> 
							<div class="form_control">
								<input type="text" name="edit_offer" value="<?php echo isset($_POST['edit_offer']) ? $_POST['edit_offer'] : $jo_details['offer']; ?>" placeholder="<?php echo isset($_POST['offer']) ? $_POST['edit_offer'] : $jo_details['offer']; ?>" id="edit_salary" >
							</div>
						</li>
						<li><input type="submit" name="edit_jo" value="Submit" /></li>
						<li><span style="color: red; font-style: italic">Note: Hiring Manager cannot enter salary offer that is greater than the maximum job offer indicated in the job requisition.<br/><br/>Contact <a href="mailto:careers.cebu@tatepublishing.net">Recruitment</a> for assistance.</span></li>
					</ul>
				</form>
			</div>
			<?php }	} ?>

			</td>
		</tr>
	<?php } ?>
	<tr><td align="center">
	<?php if($info['process']<6 && $info['processStat']>0 AND $access_level == $authorized_access_level){ ?>
		<div style="margin-bottom:10px;">
		<?php
			if($info['process']>0 || ($info['process']==5 && $info['agencyID']==0)) echo '<button id="rep" class="pad5px">Reprofile</button>&nbsp;&nbsp;&nbsp;';
			if($info['processStat']==2) echo '<button id="freeze" class="pad5px">Freeze Application</button>';
			else echo '<button id="cancelApp" class="pad5px">Cancel Application</button>';
			if($info['process']==5 && $info['agencyID']==0) echo '&nbsp;&nbsp;&nbsp;<button id="agencyhire" class="pad5px">Hire through Agency</button>';
		?>
		</div>
	<?php }else if($info['processStat']==0 && $info['processText']=='Failed Final Interview' AND $access_level == $authorized_access_level){ echo '<button id="rep" class="pad5px">Reprofile</button>'; } ?>
	
		<?php //REPROFILE DIV ?>
		<div id="reprofile" style="display:none;">
			<form id="reprofileForm" action="" method="POST">
			<table>
				<tr>
					<td align="right"><b>Reprofile to:</b></td>
					<td>
						<select class="pad5px" name="newPos" id="newPos">
							<option value=""></option>
							<?php
								$dept = '';
								$oPos = array();
								$pos = $db->selectQuery("newPositions", "posID,title,dept", "active=1 ORDER BY dept, title ASC");
								$oq = $db->selectQueryArray('SELECT DISTINCT positionID FROM jobReqData WHERE status=0');
								foreach($oq AS $o):
									array_push($oPos, $o['positionID']);
								endforeach;
																
								foreach( $pos AS $p ){
									if($p['dept'] != $dept){
										if($dept!='')
											echo '</optgroup>';
											
										echo '<optgroup label="'.$p['dept'].'">';
										$dept = $p['dept'];
									}
									if($p['posID'] != $info['position']){										
										if(in_array($p['posID'], $oPos))
											echo '<option value="'.$p['posID'].'" style="color:red;">'.$p['title'].'</option>';
										else
											echo '<option value="'.$p['posID'].'">'.$p['title'].'</option>';	
									}										
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right"><b>Reason:</b></td>
					<td><textarea name="reason" id="reason" cols=37 rows=8></textarea></td>
				</tr>
				<tr>
					<td colspan=2 align="center">
						<input type="hidden" name="prevStat" value="<?= $info['process'] ?>"/>
						<input type="hidden" name="formSubmit" value="reprofile"/>
						<input type="button" id="reprofilebtn" value="Submit"/>
						<input type="button" value="Cancel" id="reprofileCancel"/>
					</td>
				</tr>
			</table>
			</form>
			<hr/>
		</div>
		
		<?php //CANCEL APPLICATION DIV ?>
		<div id="cancelApplication" style="display:none">
			<form action="" method="POST" onSubmit="return checkCancelReason()">
				<b>Why do you want to cancel the application?</b><br/>
				<select style="width:245px; padding:5px; margin-bottom:10px;" name="cancelMessage" id="cancelMessage">
					<option value="">Other Reason</option>
				<?php
					$cQuery = $db->selectQueryArray('SELECT * FROM cancelReasons ORDER BY reason ASC');
					foreach($cQuery AS $c):
						echo '<option value="'.$c['reason'].'|'.$c['isReprofiled'].'|'.$c['testLabel'].'">'.$c['reason'].'</option>';
					endforeach;
				?>
				</select><br/>
				<textarea rows="8" cols=30 name="reason" id="cancelReason"></textarea></br>
				<input type="hidden" name="formSubmit" value="cancel">
				<input type="submit" value="Submit"/>&nbsp;&nbsp;&nbsp;	
				<input type="button" value="Cancel" onClick="cancelCancelApplication();"/>			
				<hr/>
			</form>
		</div>
	<?php
		if($info['process']==5 || $info['process']==6 AND $access_level == $authorized_access_level){
			$usernameTest = 1;
			$shortFirst = 0;
			$number = 0;
			$tryNumbers = false;
			while($usernameTest > 0) {
				if($tryNumbers === true and $number == 9) {
					print "<p class='white'>Could not find a username to work.  Please contact IT.</p>";
					exit();
				} else if($tryNumbers === true or $shortFirst == strlen($info['fname'])) {
					$number++;
					$tryNumbers = true;
				} else $shortFirst++;
				
				if($tryNumbers === true) $potentialUsername = substr(strtolower($info['fname'] . $info['lname']),0,11) . $number;
				else $potentialUsername = substr(strtolower(substr($info['fname'], 0, $shortFirst) . $info['lname']),0,12);
				$usernameTest = count($ptDb->selectQueryArray("SELECT username FROM staff WHERE username = '". $potentialUsername ."'"));
			}
			
			#for email
			$e = explode(' ',$info['fname']);
			$email = $e[0].'.'.str_replace(' ','',$info['lname']).'@tatepublishing.net';
			$emailq = $ptDb->selectSingleQuery('staff', 'email' , 'email="'.$email.'"');
			if(!empty($emailq) && count($e)>1){
				$email = str_replace(' ','',$e[0].$e[1].'.'.$info['lname'].'@tatepublishing.net');
				$emailq = $ptDb->selectSingleQuery('staff', 'email' , 'email="'.$email.'"');	
			}

			if(!empty($emailq)) $email = 'Email address already assigned. Please contact IT.';
			else $email = strtolower($email);

		}
		
		//IF CANCELLED APPLICATION
		if($info['process']<6 && $info['processStat']==0 AND $access_level == $authorized_access_level){ 
	?>
		<div>
			<?php if(!empty($info['processText'])){ echo '<b>Status:</b> '.$info['processText'].'</br>';} ?>
			<b>Application has been cancelled.</b>
		</div>
		<?php }else if($info['process']<=6 AND $access_level == $authorized_access_level){
			//THIS IS THE PROCESS
		?>
			<div id="processDiv" style="margin-top:20px;">
				<?php
				//PROFILE FOR OPEN POSITION STATUS
				if($info['process']==1){
					$openPos = $db->selectSingleQuery('jobReqData', 'positionID' , 'positionID="'.$info['position'].'"');
					if(empty($openPos)){
						echo 'No job requisition for <b>'.$position.'</b>.  Reprofile needed.';
						echo '<input type="hidden" id="submitForm" value="Reprofile Needed."/>';
					}else{
						echo '<input type="hidden" id="submitForm" value="yes"/>';
					}
				}else if($info['process']==2 || $info['process']==3){ //TECHNICAL TESTING OR HR INTERVIEWING STATUS
					$tests = 'iq,typing,written';
					if(!empty($info['requiredTest'])) $tests .= ','.$info['requiredTest'];
										
					echo '<table width="50%" cellspacing=10 cellpadding=10>';
					
					if($info['process']==2){ 
						if($info['processStat']!= 0){
							$testQ = explode(',',$tests);
							$c=0;
							foreach($testQ AS $t):
								if($c%2==0)
									echo techTest($t, 1);
								else
									echo techTest($t, 0);
								$c++;
							endforeach;
						}
					}else if($info['process']==3){ 
						if(!empty($tests)) $tests .= ',hr';
						echo techTest('hr', 1, '8');
					}
					
					echo '</table>';
							
					$sQ = ltrim(str_replace('iq,typing,written','',$tests),','); 
					$tQuery = $db->selectQueryArray('SELECT type, testStatus FROM processStatusData WHERE appID='.$id.' AND ( (positionID='.$info['position'].' AND type IN ("'.str_replace(',','","',$sQ).'")) OR type IN ("iq","typing","written") )');
							
					$tests = $tests.',';
					foreach($tQuery AS $t){
						if($t['testStatus'] == 'passed'){ 
							$tests = str_replace($t['type'].',','',$tests);
						}
					}
					
					$isTestEmpty = $tests;
					echo '<input type="hidden" id="processTests" value="'.$tests.'"/>';
				}else if($info['process']==4 AND $access_level == $authorized_access_level){ //FINAL INTERVIEWING STATUS
									
					if(!empty($info['interviewSched'])){ 
						$intDetails = explode('|', $info['interviewSched']);
				?>
					<table id="intDetailstbl" width="30%" cellpadding=5 border=1>
						<tr bgcolor="#dcdcdc"><td colspan=2><b>Final Interview Details:</b> <a href="javascript:void(0);" style="float:right;" onClick="$('#intDetailstbl').hide(); $('#selectInterviewertbl').show();">Change</a></td></tr>
						<tr><td>Final Interviewer</td><td><?= $intDetails[0]; ?></td></tr>
						<tr><td>Interview Schedule</td><td><?= date('F d, Y h:i A', strtotime($intDetails[2])); ?></td></tr>
						<tr><td>Timezone</td><td><?= $intDetails[3]; ?></td></tr>
					</table>					
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
				
					<hr/>
					<table width="50%" cellspacing=10 cellpadding=10>
						<?php echo techTest('final', 1, '8'); ?>
					</table>
				<?php 
				}else if($info['process']==5){ //JOB OFFERING STATUS
					if($info['agencyID']!=0){
						$uploadDIR = 'uploads/applicants/'.$id.'/';
						
				?>		
					<div style="width:40%; text-align:left; margin:auto;">
						<b style="text-align:center;">This application cannot be moved to HIRED until the following documents are received and uploaded:</b><br/><br/>	
						<form id="agencyUploadForm" action="" method="POST" enctype="multipart/form-data">
						<?php
							if(empty($info['endorsementLetter']) || !file_exists($uploadDIR.$info['endorsementLetter'])){
								$showhire = 0;
								echo '<input id="letterID" type="file" name="letter" style="display:none;"/>		
									<input type="button" id="letterBtn" onClick="$(\'#letterID\').trigger(\'click\');" value="Upload" class="btn btn-xs btn-primary"/>&nbsp;&nbsp;';
							}else{
								echo '<input type="button" value="View" class="btn btn-xs btn-success" onClick="location.href=\''.$uploadDIR.$info['endorsementLetter'].'\'"/>  <a href="javascript:void(0);" onClick="removeUfile(\'endorsementLetter\', this)">Remove</a>&nbsp;&nbsp;';
							}
							echo '<span style="color:red;">1. Letter of Endorsement from Agency</span><br/><br/>';							
						?>

						<?php
							if(empty($info['signedGuidelines']) || !file_exists($uploadDIR.$info['signedGuidelines'])){
								$showhire = 0;
								echo '<input id="signedID" type="file" name="signed" style="display:none;"/>		
									<input type="button" id="signedBtn" onClick="$(\'#signedID\').trigger(\'click\');" value="Upload" class="btn btn-xs btn-primary"/>&nbsp;&nbsp;';
							}else{
								echo '<input type="button" value="View" class="btn btn-xs btn-success" onClick="location.href=\''.$uploadDIR.$info['signedGuidelines'].'\'"/>  <a href="javascript:void(0);" onClick="removeUfile(\'signedGuidelines\', this)">Remove</a>&nbsp;&nbsp;';
							}
							echo '<span style="color:red;">2. Signed Company Policy and Guidelines</span>';							
						?>							
							<input type="hidden" name="formSubmit" value="agencyUpload"/>
						</form>	 
					</div>
					
					<?php
						if($showhire==1 ){
							echo '<input type="hidden" id="jobOffer" value="agency"/>';
						} 
					?>
					
					<script type="text/javascript">
						$(function(){
							$('#letterID').change(function(){
								$('#agencyUploadForm').submit();
							});
							
							$('#signedID').change(function(){
								$('#agencyUploadForm').submit();
							});
						});
						
						function removeUfile(f, t){
							if(confirm('Are you sure you want to remove this file?')){
								$(t).replaceWith('<img src="images/loading.gif"/>');
								$.post(window.location, {formSubmit:'removeFile', fld:f},
								function(){
									alert('File Removed');
									location.reload();
								});
							}
						}
					</script>
					
				<?php 		
					}else{						
						//FOR HIRE THROUGH AGENCY 
						echo '<div id="agencyhirediv" style="width:70%; display:none;">';
						include('hirethruagency.php');
						echo '</div>';
				
						$jo = $db->selectSingleQueryArray('jobReqData', 'salary, startDate' , 'status=0 AND positionID="'.$info['position'].'" ORDER BY startDate', 'LEFT JOIN salaryRange ON minSal=salID');
						$joStat = $db->selectSingleQuery('processStatusData', 'testStatus' , 'appID="'.$id.'" AND type="jobOffer" AND positionID='.$info['position']);
						
					
						
					//NORMAL JOB OFFER VIEW
					?>
					<div id="genjobofferdiv">
						<table width="50%" cellspacing=10 cellpadding=10>					
							<tr>
								<td width="40%"><br/></td>
								<td><button id="generate" class="pad5px btn btn-xs btn-primary"><?php if(count($gen)==0){ echo 'Generate Job Offer'; }else{ echo 'Regenerate Job Offer'; } ?></button></td>
							</tr>
						<form method="POST" action="" onSubmit="return validGen()">
							<tr class="gen">
								<td>Position Title</td>
								<td><input type="text" name="position" value="<?= $position ?>" class="form-control" readonly /></td>
							</tr>
							<tr class="gen">
								<td>Name Prefix</td>
								<td><select name="prefix" class="form-control"><option value="Ms.">Ms.</option><option value="Mr.">Mr.</option></select></td>
							</tr>
							<tr class="gen">
								<td>Basic Salary Offer</td>						
								<td>
									<input type="text" value="<?= $jo['salary'] ?>" name="salary" id="salary" class="form-control" placeholder="Ex. 10,000.00"/>
									<input type="hidden" value="<?= $jo['salary'] ?>" id="minsalary"/>
									<input type="hidden" value="<?= $maxSal ?>" id="maxsalary"/>
									<textarea class="form-control" name="salReason" id="salReason" style="display:none;"></textarea>
								</td>
							</tr>
							<tr class="gen">
								<td>Start Date</td>
								<?php
									if(date('N')<4){
										$sD = date('F d, Y', strtotime("next monday"));
									}else{
										$sD = date('F d, Y', strtotime("next monday + 1 week"));
									}
								?>
								<td><input type="text" id="startDate" name="startDate" value="<?= $sD ?>" class="form-control datepick"/></td>
							</tr>
						<?php if(count($gen)>0){ ?>
							<tr class="gen">
								<td>Reason</td>
								<td><textarea class="form-control" name="genReason" id="genReason"></textarea></td>
							</tr>
						<?php } ?>
							<tr class="gen">
								<td><br/></td><td><input type="submit" name="formSubmit" value="<? if(count($gen)==0){ echo 'Generate'; }else{ echo 'Regenerate'; } ?>"/></td>
							</tr>
						<?php if(count($gen)>0){  ?>
						<tr>
							<td colspan=2>	
						<?php 
							generateJOTable( $gen, $id );
						?>
						</td>
		</tr>
						 <?php } ?>						
							
							<tr <?= empty($joStat) ? '': 'class="gen"'?>><td colspan=2><hr/></td></tr>	
							<?php if( $access_level == $authorized_access_level ){ echo techTest('jobOffer', 1, '8', 'accepted|declined'); } ?>						
						</table>
						</form>
						<?php
							$offID = $db->selectSingleQuery('generatedJO', 'joID' , 'appID='.$id.' ORDER BY timestamp DESC');
							$isAccepted = $db->selectSingleQuery('processStatusData', 'testStatus' , 'appID="'.$id.'" AND type="jobOffer" AND positionID='.$offID);
							
							if($isAccepted== 'accepted'){
								$last_payID = $ptDb->selectSingleQuery('eData', 'py' , 's="PH" ORDER BY py DESC');
														
							$dir = 'uploads/applicants/'.$id.'/picture/';
							if($info['pictureTaken']==0 && is_dir($dir)){
								$fctr = 0;
								foreach(scandir($dir) AS $f):
									$a = getimagesize($dir.$f);
									$image_type = $a[2];								 
									if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP))){
										rename($dir.$f, "uploads/applicants/".$id."/".$f);
										unlink($dir.$f);
										$fctr++;
									}
								endforeach;
								
								
								if($fctr!=0){
									$db->updateQuery('applicants', array('pictureTaken' =>  '1'), 'id='.$id);
									$info['pictureTaken'] = 1;									
								}							
							}
							
						?>		
<?php if( $access_level == $authorized_access_level ){	 ?>					
						<form method="POST" action="" enctype="multipart/form-data">
							<table width="40%">
								<tr><td colspan=2><h4>Checklist</h4></td></tr>
								<tr><td width="10%" valign="top"><input type="checkbox" name="pHEnrolled" id="pHEnrolled" <? if($info['pHEnrolled']){ echo 'checked'; } ?>/></td><td>Enrolled in PayrollHero<br/>
									<i><span style="font-size:10px; color:#808080;">(Cebu's last payroll ID is <b><?= $last_payID ?></b>)<br/>
										(Use username <b><?= $potentialUsername ?></b>)<br/>
										(Use email address <b><?= $email ?></b>)
									</span></i>
								</td></tr>
								<tr><td><input type="checkbox" name="pHBDay" id="pHBDay" <? if($info['pHBDay']){ echo 'checked'; } ?>/></td><td>Updated PayrollHero Date of Birth</td></tr>
								<tr><td><input type="checkbox" name="pHCompensation" id="pHCompensation" <? if($info['pHCompensation']){ echo 'checked'; } ?>/></td><td>Double Check PayrollHero Compensation Amount</td></tr>
								<tr><td><input type="checkbox" name="batchID" id="batchID" <? if($info['batchID']){ echo 'checked'; } ?>/></td><td>Updated Batch ID</td></tr>
								<tr><td valign="top"><input type="checkbox" name="pictureTaken" id="pictureTaken" <? if($info['pictureTaken']){ echo 'checked'; } ?>/></td><td>Picture Taken<br/>
								<?php
									if($info['pictureTaken']==0){
										echo '<a href="uploadpicture.php?id='.$id.'">Click here to upload file</a>';
									}
								?>
								</td></tr>
								<tr><td><input type="checkbox" name="biometricsEnrolled" id="biometricsEnrolled" <? if($info['biometricsEnrolled']){ echo 'checked'; } ?>/></td><td>Biometrics Enrolled</td></tr>							
								<tr><td><input type="checkbox" name="referral" id="referral" <? if($info['referral']){ echo 'checked'; } ?>/></td><td>Employee Referral Program (<a href="http://goo.gl/BEK3q3">http://goo.gl/BEK3q3</a>)</td></tr>
								
								
							</table>
							<input type="hidden" name="potentail_username" value="<?php echo $potentialUsername; ?>" /> 
							<input type="hidden" name="formSubmit" value="checklist"/>
							<input type="hidden" name="appID" value="<?= $id ?>"/>
							<input type="submit" value="Update Checklist" class="btn btn-xs btn-warning">
						</form>
<? } } ?>
					</div>	<?php //END OF genjobofferdiv ?>
				<?php } ?>				
				<?php
				}else if($info['process']==6 && $info['processStat']==0){ //HIRED AND CLOSED STATUS ?>	
					<div>
						<b>Applicant is successfully hired on <?= date('F d, Y', strtotime($info['hiredDate'])) ?>. Start date is on <?= date('F d, Y', strtotime($info['startDate'])) ?>.<br/>An email has been sent to careerph.tatepublishing.net, immediate supervisor, IT, and the Job Requisition requester.</b>
						<br/><a href="create_temp_id.php?username=<?php echo $_GET['username']; ?>">Click here</a> to generate temporary ID.
						<br/><br/>
					<?php
						$empID = $db->selectSingleQuery('staffs', 'empID' , 'fname="'.$info['fname'].'" AND lname="'.$info['lname'].'" AND active="1"');
						
						if(!empty($empID)){
							echo '<a href="'.HOME_URL.'staff/schedules/setschedule/'.$empID.'/?d='.$info['startDate'].'" target="blank"><button type="button" style="padding:5px;">Click here to add schedule</button></a>';
						}
					?>						
					</div>				
				<?php
				}else if($info['process']==6 && $info['processStat']==1){ //HIRED STATUS NOT YET CLOSED	
					$jobReq = $db->selectSingleQueryArray('jobReqData', 'shift', 'status=0 AND positionID="'.$info['position'].'" ORDER BY startDate');
					
					$startD = $db->selectSingleQuery('generatedJO', 'startDate' , 'appID='.$id.' ORDER BY timestamp DESC');
					
						if(isset($_GET['hired']) && $_GET['hired']=='no'){
				?>
						<div style="color:red; margin:10px;">
							<b>Unable to create new profile.  See below for errors and re-enter values:</b><br/>
								<?= $_GET['err'] ?>
						</div>
				<?php } ?>
					<p><button class="pad5px" onClick="window.location.href='editstatus.php?id=<?= $id ?>&pos=back&stat=5'">Back to Job Offer</button></p>
					<form action="hired.php?id=<?= $id ?>" method="POST"  enctype="multipart/form-data">
					<table border=0 cellspacing=0 cellpadding=0 width="80%">	
						<tr>
							<td width="40%">Username *</td>
							<td><input type="text" name="username" value="<? if(isset($_POST['username'])){ echo $_POST['username']; }else{ echo $potentialUsername; } ?>" class="form-control"></td>
						</tr>
						<tr>
							<td>Password *</td>
							<td><input type="text" name="password" id="password" value="<? if(isset($_POST['password'])){ echo $_POST['password']; }else{ echo $potentialUsername; } ?>" class="form-control"></td>
						</tr>
						<tr>
							<td>Company Email *</td>
							<td><input type="text" name="email" id="email" value="<? if(isset($_POST['email'])){ echo $_POST['email']; }else{ echo $email; } ?>" class="form-control"></td>
						</tr>
						<tr>
							<td>Office *</td>
							<td>
								<select name="office" id="office" class="form-control">
									<option value="cebu" <? if(isset($_POST['office']) && $_POST['office']=='cebu'){ echo 'selected';} ?>>Cebu</option>
									<option value="okc" <? if(isset($_POST['office']) && $_POST['office']=='okc'){ echo 'selected';} ?>>OKC</option>													
								</select>	
							</td>
						</tr>
						<tr>
							<td>Group</td>
							<td><input type="text" value="<?= $info['org'].' > '.$info['dept'].' > '.$info['grp'].' > '.$info['subgrp'] ?>" class="form-control" disabled></td>
						</tr>
						<tr>
							<td>Position Title</td>
							<td><input type="text" value="<?= $info['title'] ?>" class="form-control" readonly></td>
						</tr>
						<tr>
							<td>Start Date *</td>
							<td><input type="text" name="startdate" id="startdate" class="form-control datepick" value="<?= date('F d, Y', strtotime($startD)); ?>" readonly ></td>
						</tr>
						<tr>
							<td>Shift *</td>
							<td>
								<input type="text" name="shift" id="shift" value="<?php if(isset($_POST['shift'])){ echo $_POST['shift']; }else{ echo $jobReq['shift']; } ?>" class="form-control"/>
							</td>
						</tr>	
					<?php if($info['agencyID']==0){ ?>
						<tr>
							<td>Type of Account *</td>
							<td>
								<select name="accountType" id="accountType" onChange="changeType()" class="form-control">
									<option value="1" <? if(isset($_POST['accountType']) && $_POST['accountType']=='1'){ echo 'selected';} ?>>Unique Account Type</option>
									<option value="2" <? if(isset($_POST['accountType']) && $_POST['accountType']=='2'){ echo 'selected';} ?>>Mimic User's Account</option>
								</select>
							</td>
						</tr>
						<tr id="mimictr" style="display:none;">
							<td><br/></td>
							<td style="position:relative;">
								<select name="mimicUser" id="mimicUser" class="form-control">
									<option value="">Existing username to mimic</option>
								<?php
									$st = $ptDb->selectQueryArray('SELECT username, sFirst, sLast FROM staff WHERE active="Y" AND sLast !="" ORDER BY sLast');
									foreach($st AS $s):
										echo '<option value="'.$s['username'].'">'.$s['sLast'].', '.$s['sFirst'].'</option>';
									endforeach;
								?>
								</select>
							</td>
						</tr>
					<?php } ?>
						<tr>
							<td><b>Other Info</b></td>
							<td><br/></td>
						</tr>
						<tr>
							<?php
								$last_payID = $ptDb->selectSingleQuery('eData', 'py' , 's="PH" ORDER BY py DESC');
							?>
							<td>Payroll ID <span id="cebuPay" style="font-size:10px; font-style:italic; color:#808080;">(Cebu's last payroll ID is <?= $last_payID ?>)</span></td>
							<td>
								<input type="hidden" id="payrollID" value="<?php echo $last_payID+1; ?>"/>
								<input type="text" name="payroll" id="payroll" value="<? if(isset($_POST['payroll'])){ echo $_POST['payroll']; }else{ echo $last_payID+1; } ?>" class="form-control" readonly />
								</td>
						</tr>
						<tr>
							<td>Marital Status *</td>
							<td>
								<select name="maritalStatus" class="form-control">
									<option value="Single">Single</option>
									<option value="Married">Married</option>
									<option value="Widowed">Widowed</option>
									<option value="Separated">Separated</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>SSS *</td>
							<td><input type="text" name="sss" id="sss" value="<? if(isset($_POST['sss'])){ echo $_POST['sss']; } ?>" class="form-control" placeholder="00-0000000-0"/></td>
						</tr>
						<tr>
							<td>TIN *</td>
							<td><input type="text" name="tin" id="tin" value="<? if(isset($_POST['tin'])){ echo $_POST['tin']; } ?>" class="form-control" placeholder="000-000-000-0000"/></td>
						</tr>
						<tr>
							<td>Philhealth</td>
							<td><input type="text" name="philhealth" id="philhealth" value="<? if(isset($_POST['philhealth'])){ echo $_POST['philhealth']; } ?>" class="form-control" placeholder="00-000000000-0"/></td>
						</tr>
						<tr>
							<td>HDMF</td>
							<td><input type="text" name="hdmf" id="hdmf" value="<? if(isset($_POST['hdmf'])){ echo $_POST['hdmf']; } ?>" class="form-control" placeholder="0000-0000-0000"/></td>
						</tr>
						
						<tr>
							<td>Upload eSignature</td>
							<td>
								<input type="file" name="e_signature" id="e_signature" />
							</td>
						</tr>
						<tr>
							<td>Upload photo for temporary ID</td>
							<td><input type="file" name="e_photo" id="e_photo" /></td> 
						</tr>
						<tr>
							<td><b>Emergency Contact Person</b></td>
							<td><br/></td>
						</tr>
						<tr>
							<td>Name</td>
							<td><input type="text" name="e_contact_person" id="e_contact_person"  class="form-control" placeholder="Name" /></td> 
						</tr>
						<tr>
							<td>Address</td>
							<td><input type="text" name="e_contact_address" id="e_contact_address" class="form-control" placeholder="Address" /></td> 
						</tr>
						<tr>
							<td>Contact #</td>
							<td><input type="number" name="e_contact_number" id="e_contact_number" class="form-control" placeholder="Contact #" /></td> 
						</tr>
						<tr>
							<td>Relationship</td>
							<td><input type="text" name="e_contact_relationship" id="e_contact_relationship" class="form-control" placeholder="Relationship" /></td> 
						</tr>
						<tr>
							<td colspan=2 align="right">Fields with (*) are required fields.</td>
						</tr>
						<tr>
							<td colspan=2 align="right"><br/></td>
						</tr>
						<tr>
							<td colspan=2 align="right">
								<input type="hidden" name="appID" value="<?= $_GET['id'] ?>"/>
								<input type="submit" value="Create Staff Account"/>
							</td>
						</tr>
					</table>
					</form>
				<?php
				}
				?>
			</div>
			
			<?php if($info['processStat']==1 && $info['process']<6 && is_admin() && $showhire==1){ 
				if(!empty($isTestEmpty)){
					if($info['process']==2){
						echo '<br/><input type="submit" value="Submit Later" onClick="window.location.href=\'editstatus.php?id='.$id.'&pos=back&stat=3\'" class="btn btn-xs btn-warning">';
					}else if($info['process']==3  && $isTestEmpty != 'hr'){
						echo '<br/><input type="submit" value="Back to Technical Testing" onClick="window.location.href=\'editstatus.php?id='.$id.'&pos=back&stat=2\'" class="btn btn-xs btn-warning">';
					}
				}
			?>
			<?php if( $access_level == $authorized_access_level ) { ?>
			<form action="" method="POST" onSubmit="return validateAdvance()">	
				<div style="margin-top:15px;" id="advanceProcessDiv">
					<input type="hidden" name="formSubmit" value="advanceProcess"/>	
					<input type="hidden" name="process" id="process" value="<?= $info['process']+1 ?>"/>	
					<input type="submit" value='Advance to "<?= $recProcess[$info['process']+1]['processType'] ?>"' class="btn btn-primary">
				</div>
			</form>
			<?php } } ?>		
	<?php } ?>
					
	</td></tr>	
<table>

<script type="text/javascript">
	$(function () { 
		$('.datepick').datetimepicker({
			format:'F d, Y',
			timepicker:false
		}); 
		
		$('.dateandtime').datetimepicker({
			format:'F d, Y h:00 A',
			timepicker:true
		});

		if($('#accountType').val() == 2){
			$('#mimictr').css('display', '');
		}
		
		$('#office').change(function(){
			$('#payroll').val('');
			$('#cebuPay').css('display', 'none');
			if( $('#office').val() == 'cebu' ){
				$('#payroll').val($('#payrollID').val());
				$('#payroll').attr('disabled','disabled');
				$('#cebuPay').css('display', '');
			}else{
				$("#payroll").removeAttr('disabled');
			}
		});	
		

		$('#rep').click(function(){
			$('#reprofile').css('display', 'block');
		});
		
		$('#cancelApp').click(function(){
			$('#cancelApplication').css('display', 'block');
		});
		
		$('#freeze').click(function(){
			if (confirm('Are you sure you want to freeze application of this applicant?')){
				$.post("editstatus.php?pos=freeze&appID=<?= $_GET['id'] ?>",{
					appID:'<?= $_GET['id'] ?>',
					positionID:'<?= $info['position'] ?>'
				},function(){
					location.reload();
				});
			}
		});
		
		$('#generate').click(function(){
			$(this).css('display', 'none');
			$('.gen').removeClass('gen');
		});
		
		$('#reprofileCancel').click(function(){
			location.reload();
		});
		
		$('#reprofilebtn').click(function(){
			if($('#newPos').val() == '' || $('#reason').val() == ''){
				alert('Please input all fields.');
			}else{
				$.post("editstatus.php?pos=reprof&posid="+$('#newPos').val(),				
				function(data){
					if(data==''){
						alert('No job requisition for this position.');
					}else{
						$("#reprofileForm").submit();
					}
				});
			}
		});

		$('#agencyhire').click(function(){
			$('#genjobofferdiv').css('display', 'none');
			$('#advanceProcessDiv').css('display', 'none');
			$('#agencyhirediv').css('display', '');			
		});		
		
	});
	
	function validateAdvance(){
		valid = true;
		var process = $('#process').val()- 1;		
		
		if(process == 1 && $('#submitForm').val() != 'yes'){
			alert($('#submitForm').val());
			valid = false;
		}else if(process==2 || process==3){
			var tests = $('#processTests').val();
		
			if( tests != '' ){
				valid = false;
				alert('Unable to advance to the next status.  Please check test results.');
			}	
			
			if(process==3 && valid!=false){
				if($('#hr').val()=='' || $('#hr').val()=='Failed' || $('#hr').is(':disabled')==false){
					valid = false;
					alert('Unable to advance to the next status.  Please check HR interview result.');
				}
			}
		}else if(process==4){
			if($('#final').val()=='' || $('#final').val()=='Failed'  || $('#final').is(':disabled')==false){
				alert('Unable to advance to the next status.  Please check Final Interview result.');
				valid = false;
			}				
		}else if(process==5){
			if($('#jobOffer').val()!='agency' && 
				($('#jobOffer').val()=='' || $('#jobOffer').val()=='Declined' || $('#jobOffer').is(':disabled')==false)){
				alert('Unable to advance to the next status.  Please check Job Offer result.');
				valid = false;
			}else{
				if(	$("#pHEnrolled").prop('checked') == false ||
					$("#pHBDay").prop('checked') == false ||
					$("#pHCompensation").prop('checked') == false ||
					$("#batchID").prop('checked') == false ||
					$("#referral").prop('checked') == false ||
					$("#pictureTaken").prop('checked') == false ||
					$("#biometricsEnrolled").prop('checked') == false 
				){
					valid = false;
					alert('Unable to advance to the next status.  Please check checklist.');
				}
				
			}
		}
		
		return valid;
	}
	
	function showDiv(d){
		var dval = $('#'+d).val(); 
		if(dval=='')
			$('#'+d+'Div').css('display', 'none');
		else
			$('#'+d+'Div').css('display', 'block');
	}
	
	function checkTest(t){
		if($('#'+t+'test').val()=='' || $('#'+t+'reason').val()==''){
			alert('Please input all fields.');
		}else{
			$.post("editstatus.php?pos=testing",
			{
				appID:'<?= $id ?>',
				type: t,
				testStatus: $('#'+t).val(),
				positionID: '<?= $info['position'] ?>',
				reason: $('#'+t+'reason').val(),
				examiner: '<?= $_SESSION['u'] ?>',
			},
			function(){
				if((t=='final' && $('#'+t).val()=='failed') || (t=='jobOffer' && $('#'+t).val()=='declined')){
					if (confirm('Do you want to reprofile this candidate?')) {
						$('#reprofile').css('display', 'block');
						$('#processDiv').css('display', 'none');
						$('#advanceProcessDiv').css('display', 'none');
					} else {
						location.reload();
					}
				}else{
					location.reload();
				}
			});
		}			
	}
	
	//for hired
	function changeTitle(title){
		if(title == ''){
			$('#newTitle').css('display','none');
			$('#title').val('');
		}else{
			$('#newTitle').css('display','');
			$('#title').val(title);
		}
	}
			
	function changeType(){
		$('#mimictr').css('display', 'none');
		if($('#accountType').val() == 2){
			$('#mimictr').css('display', '');
		}
	}
	
	function validateForm(isNew){
		var valid = true;
		var validText = '';
		var ssspattern = new RegExp(/^[0-9]{2}-[0-9]{7}-[0-9]{1}$/);
		var tinpattern = new RegExp(/^[0-9]{3}-[0-9]{3}-[0-9]{3}-[0-9]{4}$/); 
		var phpattern = new RegExp(/^[0-9]{2}-[0-9]{9}-[0-9]{1}$/);  
		var hdmfpattern = new RegExp(/^[0-9]{4}-[0-9]{4}-[0-9]{4}$/);
		
		if($('#office').val() == '')
			validText += '\t- Office\n';		
		if($('#startdate').val()=='')
			validText += '\t- Start date\n';	
		if($('#shift').val()=='')
			validText += '\t- Shift\n';
		if($('#password').val()=='')
			validText += '\t- Password\n';
		if($('#email').val()=='')
			validText += '\t- Email\n';
		if($('#accountType').val()=='2' && $('#mimicUser').val()=='')
			validText += '\t- Existing username\n';
		if(isNew==0 && $('#position').val()==''){
			validText += '\t- Assign to job requisition\n';
		}
		if($('#sss').val()==''){
			validText += '\t- SSS\n';
		}else if(ssspattern.test($('#sss').val())==false){
			validText += '\t- SSS Invalid\n';
			valid = false;			
		}
		if($('#tin').val()==''){
			validText += '\t- TIN\n';
		}else if(tinpattern.test($('#tin').val())==false){
			validText += '\t- TIN Invalid\n';
			valid = false;			
		}
		
		if($('#philhealth').val() != '' &&  phpattern.test($('#philhealth').val())==false){
			validText += '\t- Philhealth Invalid\n';
			valid = false;	
		}
		if($('#hdmf').val() != '' &&  hdmfpattern.test($('#hdmf').val())==false){
			validText += '\t- HDMF Invalid\n';
			valid = false;	
		}
		
		if(validText != ''){
			valid = false;
			alert('Please complete missing values: \n'+validText);
		}
		
		return valid;
	}
	
	function validGen(){
		valid = true;
		txt = '';
		if($('#salary').val()==''){
			txt += 'Salary offer empty\n';
			valid = false;
		}else if( $.isNumeric($('#salary').val())== false){
			txt += 'Invalid salary offer (enter digits only)\n';
			valid = false;
		}else if( ($('#salary').val() < $('#minsalary').val() || $('#salary').val() > $('#maxsalary').val()) && $('#salReason').val()==''){	
			valid = false;
			if($('#salary').val() > $('#maxsalary').val())
				r = confirm('The amount you entered exceeded the maximum salary offer entered for the job requisition.\nPlease provide a rationale for this.');
			else if($('#salary').val() < $('#minsalary').val())
				r = confirm('The amount you entered is below the minimum salary offer entered for the job requisition.\nPlease provide a rationale for this.');
				
			if(r){
				$('#salReason').css('display', '');
			}else{
				$('#salReason').css('display', 'none');
			}
			
		}		
		if($('#startDate').val()==''){
			txt += 'Invalid start date\n';
			valid = false;
		}
		if($('#genReason').length!=0 && $('#genReason').val()==''){
			txt += 'Reason is empty\n';
			valid = false;
		}
		
		if(txt != '') alert(txt);
		
		return valid;
	}
		
	function checkCancelReason(){
		if($('#cancelReason').val()==''){
			alert('Please state reason.');
			return false;
		}else{
			return true;
		}
	}
	
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
			$.post("editstatus.php?pos=interviewSched&appID=<?= $_GET['id'] ?>",{
				appID:'<?= $_GET['id'] ?>',
				interviewer:$('#interviewer').val(),
				dateNtime:$('#intdate').val(),
				timezone:$('select[name="inttimezone"]').val()
			},function(){
				window.location.href="<?= HOME_URL ?>emailTemplate.php?id=<?= $_GET['id'] ?>&type=finalInterviewerSched";
			});
		}		
	}
	
	function cancelCancelApplication(){
		$('#cancelApplication').hide();
		$('#cancelMessage').val('');
		$('#cancelReason').val('');
		
	}
	
	function hidenote(t, note){
		$(t).hide();
		$('#note'+note).removeClass('hidden');
	}
</script>

</body>
</html>
