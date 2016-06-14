<?php

//ini_set('display_errors', 1);
require 'config.php';

if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
	header("Location: login.php");
	exit();
} 


//for files uploaded
if(isset($_FILES) AND !empty($_FILES)){
	
	$tmp_id_filename = 'tmp_id_'.$_POST['username'];
	$full_path = '/home/careerph/public_html/staff/uploads/staffs/'. $_POST['username'];
	
	//upload signature
	if( isset($_FILES['e_signature']) AND !empty($_FILES['e_signature']['name']) ){
		$uploaded_file_signature = upload_file( $_FILES['e_signature'], 'signature', $full_path );
	} 
	if( isset($_FILES['e_photo']) AND !empty($_FILES['e_photo']['name']) ){
		$uploaded_file_id = upload_file( $_FILES['e_photo'], $tmp_id_filename, $full_path );
	}	
	
	$signature_file =  $uploaded_file_signature;
	$tmp_id_file = $uploaded_file_id;

	
	$sig_dimension = getimagesize($signature_file);
	
	
	
}
	

if(isset($_POST) AND !empty($_POST)){
	date_default_timezone_set("Asia/Manila");
	$hire = $db->selectSingleQueryArray('applicants', 'id, fname, lname, mname, suffix, bdate, address, mnumber, email, gender, isNew, position, title, org, dept, dt, grp, subgrp, startDate, salaryOffer, agencyID, endorsementLetter, signedGuidelines, endDate' , 'id='.$_GET['id'], 'LEFT JOIN newPositions ON position=posID');
	$startD = $db->selectSingleQuery('generatedJO', 'startDate' , 'appID='.$_GET['id'].' ORDER BY timestamp DESC');

	$postSuccess = 'yes';
	$postError = '';
		
	if($hire['agencyID']==0){
		$insertStaff = array(
					'username' => $_POST['username'],
					'password' => md5($_POST['password']),
					'sFirst' => $hire['fname'],
					'sLast' => $hire['lname'],
					'sMaidenLast' => $hire['mname'],
					'emailCom' => $_POST['username'].'@tatepublishing.com',
					'email' => $_POST['email'],
					'office' => (($_POST['office']=='cebu')?'PH-Cebu':$_POST['office'])
					);
		
		if( $_POST['accountType'] == 2 ){ //mimic user
			$exept = array('uid', 'username', 'password', 'sFirst', 'sLast', 'sMaidenLast', 'emailCom', 'email', 'directPhone', 'directPhoneExt', 'active', 'office', 'timestamp', 'tempPassword', 'maxNumUnits');
			$mUser = $ptDb->selectSingleQueryArray('staff', '*' , 'username="'.$_POST['mimicUser'].'"');
			foreach($mUser AS $cName=>$val){
				if (!in_array($cName, $exept)){
					if (strpos($cName, 'Num') !== false){
						if($val>0){
							$valNew = $ptDb->selectQueryArray('SELECT DISTINCT '.$cName.' FROM staff WHERE '.$cName.' > 0 ORDER BY '.$cName.' DESC LIMIT 1');
							if(count($valNew)==1){
								$insertStaff[$cName] = (int) $valNew[0][$cName] + 1;
							}
						}
					}else if( $val!= '' || $val!= 0 || $val!= NULL || $val!= 'N' || $val!= 'none' ){
						$insertStaff[$cName] = $val;
					}
				}			
			}
		} 
	} 
	

	$jobReq = $db->selectSingleQueryArray('jobReqData', 'reqID, supervisor, requestor' , 'positionID="'.$hire['position'].'" AND status=0 AND appID=0', 'LEFT JOIN newPositions ON positionID = posID'); 
	
	if(empty($jobReq['reqID'])){
		$postSuccess = 'no';
		$postError .= '-  No job requisition for this position.<br/>';
	}
	
	$sss = $ptDb->selectSingleQuery('eData', 'SSS' , 'SSS="'.$_POST['sss'].'"');	
	if(!empty($sss)){
		$postSuccess = 'no';
		$postError .= '-  SSS number is not unique.<br/>';
	}
	
	$tin = $ptDb->selectSingleQuery('eData', 'TIN' , 'TIN="'.$_POST['tin'].'"');	
	if(!empty($tin)){
		$postSuccess = 'no';
		$postError .= '-  TIN number is not unique.<br/>';
	}
	
	//upload esignature to staff data and id to be used
	$hire['fname'] = ucwords($hire['fname']);
	$hire['lname'] = ucwords($hire['lname']);
	
	if($postSuccess=='yes'){
		if($_POST['office']== 'cebu') $s = 'PH-Cebu';
		else $s = 'OKC';	
		
		if($hire['agencyID']==0){
			$eDataArr = array(
						'u' => $_POST['username'],
						'ad' => $hire['address'],
						's' => $s,
						'p1' => $hire['mnumber'],
						'sD' => date('Y-m-d', strtotime($startD)),
						'bD' => $hire['bdate'],
						'sup' => $ptDb->selectSingleQuery('eData','eKey', 'CONCAT( sFirst,  " ", sLast ) =  "'.$jobReq['supervisor'].'"', 'LEFT JOIN staff ON username = u'),
						'shift' => $_POST['shift'],
						'comp_email' => $_POST['email'],
						'title' => $hire['title'],
						'dt' => $hire['dt'],
						'SSS' => $_POST['sss'],
						'TIN' => $_POST['tin'],
						'Philhealth' => $_POST['philhealth'],
						'HDMF' => $_POST['hdmf'],
						'py' => $_POST['payroll']
						); 
			
		
			$insertID = $ptDb->insertQuery('staff', $insertStaff);
			$ptDb->insertQuery('eData', $eDataArr);
			$ptDb->executeQuery("INSERT INTO dept(uid, cps_id) SELECT username, '0' FROM staff WHERE username='".$_POST['username']."' AND '".$_POST['username']."' NOT IN(SELECT uid FROM dept)");
		}
		
		//updating job requisitions
		$db->updateQuery('jobReqData', array('status'=>'1', 'appID'=>$_GET['id'], 'dateClosed'=>date('Y-m-d'), 'closedBy'=>$_SESSION['u']), 'reqID='.$jobReq['reqID']);
		
		//inserting to careerph staffs table
		$cstaffData = array(
					'username' => $_POST['username'],
					'password' => md5($_POST['username']),
					'fname' => $hire['fname'],
					'lname' => $hire['lname'],
					'suffix' => $hire['suffix'],
					'pemail' => $hire['email'],
					'email' => $_POST['email'],
					'gender' => strtoupper(substr($hire['gender'],0, 1)),
					'idNum' => $_POST['payroll'],
					'position' => $hire['position'],
					'supervisor' => $db->selectSingleQuery('staffs','empID', 'CONCAT( fname,  " ", lname ) =  "'.$jobReq['supervisor'].'"'),
					'startDate' => date('Y-m-d', strtotime($startD)),
					'office' => (($_POST['office']=='cebu')?'PH-Cebu':$_POST['office']),
					'shift' => $_POST['shift'],
					'sal' => encryptText($hire['salaryOffer']),
					'bdate' => $hire['bdate'],
					'address' => $hire['address'],
					'phone1' => $hire['mnumber'],
					'sss' => encryptText($_POST['sss']),
					'tin' => encryptText($_POST['tin']),
					'philhealth' => encryptText($_POST['philhealth']),
					'hdmf' => encryptText($_POST['hdmf']),
					'maritalStatus' => $_POST['maritalStatus']
				);
		if($hire['agencyID']!=0){
			$cstaffData['endDate'] = $hire['endDate'];
			$cstaffData['agencyID_fk'] = $hire['agencyID'];
			$cstaffData['empStatus'] = 'contract';
		}
		
		//INSERTING TO STAFFS TABLE
		$lastIDinserted = $db->insertQuery('staffs', $cstaffData);
		$db->insertQuery('staffNewEmployees', array('empID_fk'=>$lastIDinserted)); //insert to staffNewEmployees for IT checklist
		//insert uploaded files if agency hired
		if($hire['agencyID']!=0){
			$srcDIR = 'uploads/applicants/'.$hire['id'].'/';
			$destDIR = 'staff/uploads/staffs/'.$_POST['username'];
			mkdir($destDIR, 0755, true);
			chmod($destDIR.'/', 0777);
			
			copy($srcDIR.$hire['endorsementLetter'], $destDIR.'/'.$hire['endorsementLetter']);
			copy($srcDIR.$hire['signedGuidelines'], $destDIR.'/'.$hire['signedGuidelines']);
			
			//INSERT TO STAFF UPLOADS TABLE
			$uplArr['empID_fk'] = $lastIDinserted;
			$uplArr['docName'] = 'Letter of Endorsement from Agency';
			$uplArr['fileName'] = $hire['endorsementLetter'];
			$uplArr['dateUploaded'] = date('Y-m-d H:i:s');
			$db->insertQuery('staffUploads', $uplArr); //inserting endorsementLetter
			
			$uplArr['docName'] = 'Signed Company Policy and Guidelines';
			$uplArr['fileName'] = $hire['signedGuidelines'];
			$db->insertQuery('staffUploads', $uplArr); //inserting signedGuidelines
		}
		
		$supEmail = $ptDb->selectSingleQuery('staff', 'email' , 'CONCAT(sFirst," ",sLast)="'.$jobReq['supervisor'].'"');
		$reqEmail = $ptDb->selectSingleQuery('staff', 'email' , 'username="'.$jobReq['requestor'].'"');	
				
		if($_POST['office'] == 'cebu'){
			$to = 'helpdesk.cebu@tatepublishing.net,diana.bartulin@tatepublishing.net,hr.cebu@tatepublishing.net,clinic.cebu@tatepublishing.net,raymond.ordono@tatepublishing.net,'.$supEmail.','.$reqEmail;
			$from = 'kenneth.bagao@tatepublishing.net';			
		}else{
			$to = 'helpdesk.us@tatepublishing.net';
			$from = 'vikki.williams@tatepublishing.net';
		}
		
		$subject = "Newly hired ".$hire['title']." auto email request";
		$body = "<html>
				<head></head>
				<body style='font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:15px;'>
					Dear All, <br/><br/>
				
					Please prepare for the joining of new employee:  <b>".ucfirst($hire['fname'])." ".ucfirst($hire['lname'])."</b> <br/>
					Position: <b>".$hire['title']."</b> <br/>
					Start date: ".date('Y/m/d', strtotime($startD))." (yyyy/mm/dd)<br/>
					Shift: ".ucfirst($_POST['shift'])."<br/>
					Staff Index: OK<br/>
					CPH: OK<br/><br/>
					
					Hiring Manager - Please inform IT which computer will be assigned and welcome employee on his first day.<br/>
					IT - Please coordinate with hiring manager regarding IT preparations, email address and PT account.";
					
				if($_POST['office']=='cebu') $body .= 'Click <a href="'.HOME_URL.'staff/itchecklist/newhirestatus/">here</a> to view IT checklist.';
						
		$body .= "<br/><br/>					
					Thanks!
				</body>
				</html>";
		
		sendEmail( $from, $to, $subject, $body, 'Career Index Auto Email' );	

		
		$Abody = '
			<style>p{font-family:arial;}</style>
			<p>Dear '.$hire['fname'].' '.$hire['lname'].',</p>
			<p><br/></p>
			<p>Congratulations!</p>
			<p>We in Tate Publishing and Enterprises (Philippines), Inc. are very excited to welcome you on '.date('F d, Y', strtotime($startD)).'.</p>
			<p>Your Tate Publishing ID Number is: '.$_POST['payroll'].'<br/>
			Your Immediate Supervisor is: '.$jobReq['supervisor'].'</p>
			<p>On your first day, your immediate supervisor '.$jobReq['supervisor'].' will be waiting to welcome you.</p>
			<p>To help you be prepared for your first day, please take time to read this page: <a href="http://employee.tatepublishing.net/hr/welcome-to-tate-publishing-for-new-employees/">http://employee.tatepublishing.net/hr/welcome-to-tate-publishing-for-new-employees/</a>.</p>		
			<p>Please do not hesitate to approach your immediate supervisor'.$jobReq['supervisor'].', any of the leaders in the office, the HR team, the IT team or any of the support team members if you have questions or need any assistance.</p>
			<p>All information you need is answered in <a href="http://employee.tatepublishing.net" target="_blank">employee.tatepublishing.net</a> but if you cannot find the answere there, you may email <br/>
			hr.cebu@tatepublishing.net for HR related concerns<br/>
			helpdesk.cebu@tatepublishing.net for IT releate concerns<br/>
			accounting.cebu@tatepublishing.net for Payroll and Accounting related conrcerns</br/>
			</p>
			<p>We are so happy you are here and we wish you all the best in your carerer with Tate Publishing</p>
			<p>Good Luck and God Bless.</p>
			<p><br/></p>
			<p>See you soon!</p>
			<p>Tate Publishing HR Team</p>
		';
		sendEmail( $from, $hire['email'].','.$_POST['email'].',hr.cebu@tatepublishing.net', 'Welcome to Tate Publishing & Enterprises (Philippines), Inc.', $Abody, 'Career Index Auto Email' );

		$db->updateQuery('applicants', array('processStat' => '0', 'processText' => 'Hired', 'hiredDate' => date('Y-m-d'), 'startDate' => date('Y-m-d', strtotime($startD))), 'id='.$_GET['id']);	
		addStatusNote($_GET['id'], 'hired', '', $hire['position']);

//send email to leaders
		$pronoun = ($hire['gender'] == 'male') ? 'he':'she';
		$possessive_pronoun = ($hire['gender'] == 'male') ? 'his':'her';
		//send also to leaders and management us
		$leaders_msg = '<p>Hello Tate Leaders!</p>
		<p>Please help us welcome '.ucwords($hire['fname'].' '.$hire['lname']).'</p>
		<p>'.$hire['fname'].' will be our new '.$hire['position'].' in the '.$department.'. '.ucwords($pronoun).' will join us on '.date('F d, Y', strtotitme($startD)).' and will be reporting to '.$jobReq['supervisor'].'. '.$possessive_pronoun.' shift would be '.ucfirst($_POST['shift']).'.</p>
		<p>We are very to have '.$hire['fname'].' onboard our awesome '.$hire['dept'].' team!</p>
		<p>Please help make '.$possesive_pronoun.' onboarding as smooth as possible. <span style="text-style: underline;">Please cascade this announcement to anyone in your team who needs to be informed.</span></p>
		<p>Cheers!<br/>
		<strong>The Human Resources Team</strong></p>';

		sendEmail($from, 'management.us@tatepublishing.net,leaders.cebu@tatepublishing.net', 'New Hire Announcement', $leaders_msg, 'Career Index Auto Email');
		
		//once we all have our data then we can create now the template for temporary ID
		//generate tmp ID
		if( file_exists($uploaded_file_id) ){
			
			ob_end_clean();
			require_once('includes/fpdf/fpdf.php');
			require_once('includes/fpdf/fpdi.php');
			$pdf = new FPDI();
			$pdf->AddPage();
			$pdf->setSourceFile('includes/forms/temp_id_template.pdf');
			$tplIdx = $pdf->importPage(1);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);

			$pdf->SetFont('Arial','',6);
			$pdf->setTextColor(0, 0, 0);
			
			$pdf->setXY(42, 34);
			$pdf->Write(0, $_POST['startdate']);
			//hbd
			$pdf->setXY(41, 38.5);		
			$pdf->Write(0, $hire['bdate'] );
			
			$pdf->setXY(41, 42.5);		
			$pdf->Write(0, $_POST['sss'] );
			
			$pdf->setXY(41, 47);		
			$pdf->Write(0, $_POST['philhealth'] );
			
			$pdf->setXY(42, 51);		
			$pdf->Write(0, $_POST['hdmf'] );
			
			$pdf->setXY(40, 55.5);		
			$pdf->Write(0, $_POST['tin'] );
			
			$pdf->setXY(80, 34);		
			$pdf->Write(0, $_POST['e_contact_person'] );
			
			$pdf->setXY(82, 38.5);		
			$pdf->Write(0, $_POST['e_contact_address'] );
			
			$pdf->setXY(84, 42.5);		
			$pdf->Write(0, $_POST['e_contact_number'] );
			
			$pdf->setXY(85, 47);		
			$pdf->Write(0, $_POST['e_contact_relationship'] );
			
			$pdf->SetFont('Arial','B',9);
			//$pdf->setTextColor(255, 255, 255);
			$pdf->setTextColor(0, 0, 0);	
		    $full_name = $hire['fname'].' '.$hire['lname'];	
			$full_name = strtoupper( $full_name );
			$pdf->setXY(67, 118);		
			$pdf->Write(0, $full_name );
			
			//picture
			$pdf->Image($tmp_id_file, 36, 96, -88, -110);
			
			$pdf->Image($signature_file, 70, 125);
			
			
			$pdf->Output($full_path. '/' .$tmp_id_filename.'.pdf', 'F');
			$pdf->Output($tmp_id_filename.'.pdf', 'D');
			
		}
	}
	
	header('Location:editstatus.php?id='.$_GET['id'].'&hired='.$postSuccess.'&err='.urlencode($postError));
	exit;
}

?>
