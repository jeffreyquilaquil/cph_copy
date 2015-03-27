<?php
require 'config.php';

if(!isset($_SESSION['u']) || !in_array($_SESSION['u'], $authorized)){
	header("Location: login.php");
	exit();
} 


if(isset($_POST) AND !empty($_POST)){
	date_default_timezone_set("Asia/Manila");
	$hire = $db->selectSingleQueryArray('applicants', 'id, fname, lname, mname, suffix, bdate, address, mnumber, email, gender, isNew, position, title, org, dept, grp, subgrp, startDate, salaryOffer' , 'id='.$_GET['id'], 'LEFT JOIN newPositions ON position=posID');
	$startD = $db->selectSingleQuery('generatedJO', 'startDate' , 'appID='.$_GET['id'].' ORDER BY timestamp DESC');

	$postSuccess = 'yes';
	$postError = '';
		
	$insertStaff = array(
				'username' => $_POST['username'],
				'password' => md5($_POST['password']),
				'sFirst' => $hire['fname'],
				'sLast' => $hire['lname'],
				'sMaidenLast' => $hire['mname'],
				'emailCom' => $_POST['username'].'@tatepublishing.com',
				'email' => $_POST['email'],
				'office' => $_POST['office']
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
	
	if($postSuccess=='yes'){
		if($_POST['office']== 'cebu') $s = 'PH-Cebu';
		else $s = 'OKC';	
		
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
					'SSS' => $_POST['sss'],
					'TIN' => $_POST['tin'],
					'Philhealth' => $_POST['philhealth'],
					'HDMF' => $_POST['hdmf'],
					'py' => $_POST['payroll']
					); 
		
	
		$insertID = $ptDb->insertQuery('staff', $insertStaff);
		$ptDb->insertQuery('eData', $eDataArr);
		$ptDb->executeQuery("INSERT INTO dept(uid, cps_id) SELECT username, '0' FROM staff WHERE username='".$_POST['username']."' AND '".$_POST['username']."' NOT IN(SELECT uid FROM dept)");

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
					'sal' => $hire['salaryOffer'],
					'bdate' => $hire['bdate'],
					'address' => $hire['address'],
					'phone1' => $hire['mnumber'],
					'sss' => $_POST['sss'],
					'tin' => $_POST['tin'],
					'philhealth' => $_POST['philhealth'],
					'hdmf' => $_POST['hdmf'],
					'maritalStatus' => $_POST['maritalStatus']
				);
		$db->insertQuery('staffs', $cstaffData);
		
		$supEmail = $ptDb->selectSingleQuery('staff', 'email' , 'CONCAT(sFirst," ",sLast)="'.$jobReq['supervisor'].'"');
		$reqEmail = $ptDb->selectSingleQuery('staff', 'email' , 'username="'.$jobReq['requestor'].'"');	
				
		if($_POST['office'] == 'cebu'){
			$to = 'helpdesk.cebu@tatepublishing.net,diana.bartulin@tatepublishing.net,hr.cebu@tatepublishing.net,'.$supEmail.','.$reqEmail;
			$from = 'hr.cebu@tatepublishing.net';
			
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
					PayrollHero: OK<br/><br/>
					
					Hiring Manager - Please inform IT which computer will be assigned and welcome employee on his first day.<br/>
						IT - Please coordinate with hiring manager regarding IT preparations, email address and PT account.<br/><br/>
					
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
			<p>If you have questions, please do not hesitate to call HR at 09173015686 or (032)318-2586. You may also email us at <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a>.</p>
			<p>Once again, congratulations and welcome to the Tate Family!</p>
			<p>Good Luck and God Bless.</p>
			<p><br/></p>
			<p>See you soon!</p>
			<p>Tate Publishing HR Team</p>
		';
		sendEmail( $from, $hire['email'].','.$_POST['email'].',hr.cebu@tatepublishing.net', 'Welcome to Tate Publishing & Enterprises (Philippines), Inc.', $Abody, 'Career Index Auto Email' );

		$db->updateQuery('applicants', array('processStat' => '0', 'processText' => 'Hired', 'hiredDate' => date('Y-m-d'), 'startDate' => date('Y-m-d', strtotime($startD))), 'id='.$_GET['id']);	
		addStatusNote($_GET['id'], 'hired', '', $hire['position']);
	}
	
	header('Location:editstatus.php?id='.$_GET['id'].'&hired='.$postSuccess.'&err='.urlencode($postError));
	exit;
}

?>
