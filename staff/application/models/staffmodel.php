<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staffmodel extends CI_Model {

	var $ptDB;
	var $db;
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();
		
		$this->db = $this->load->database('default', TRUE);
		$this->ptDB = $this->load->database('projectTracker', TRUE);
    }
	
	function dbQuery($sql){
		$this->db->query($sql);
	}
	
	function ptdbQuery($sql){
		$this->ptDB->query($sql);
	}		
	
	function getPTQueryResults($table, $fields, $where=1, $join='', $orderby=''){
		if($orderby!='') $orderby = 'ORDER BY '.$orderby;
		$query = $this->ptDB->query("SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby);
		return $query->result();
	}
	
	function getSingleField($table, $field, $where=1){
		$query = $this->db->query('SELECT '.$field.' FROM '.$table.' WHERE '.$where.' LIMIT 1');
		$f = '';
		foreach($query->row() AS $r){
			$f = $r;
		}
		return $f;
	}
			
	function getSingleInfo($table, $fields, $where=1, $join='', $orderby=''){
		if($orderby!='') $orderby = 'ORDER BY '.$orderby;
		$query = $this->db->query("SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." LIMIT 1 ".$orderby);
		return $query->row();
	}
	
	function getQueryResults($table, $fields, $where=1, $join='', $orderby=''){
		if($orderby!='') $orderby = 'ORDER BY '.$orderby;
		$query = $this->db->query("SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby);
		return $query->result();
	}
	
	function getSQLQueryResults($sql){
		$query = $this->db->query($sql);
		return $query->result();
	}
	
	function getQueryArrayResults($table, $fields, $where=1, $join='', $orderby=''){
		$arr = array();
		if($orderby!='') $orderby = 'ORDER BY '.$orderby;
		$query = $this->db->query("SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby);
		foreach($query->result() AS $q):
			$arr[] = $q;
		endforeach;
		return $arr;
	}
	
	function getSQLQueryArrayResults($sql){
		$query = $this->db->query($sql);
		foreach($query->result() AS $q):
			$arr[] = $q;
		endforeach;
		return $arr;
	}
	
	function insertQuery($table, $array){
		date_default_timezone_set("Asia/Manila");
		if(count($array) > 0){
			$sql = "INSERT INTO $table (";
			$cols = '';
			$vals = '';
			foreach($array AS $k => $v){
				$cols .= '`'.$k.'`,';
				if($v=='NOW()')
					$vals .= $v.',';
				else
					$vals .= '"'.$v.'",';
			}
			$sql .= rtrim($cols,',').') VALUES ('.rtrim($vals,',').')';
			
			$this->db->query($sql);
			return $this->db->insert_id();
		}	
	}
		
	function updateQuery($table, $where = array(), $data = array()) {
		$this->db->where($where);
		$this->db->update($table, $data);
	}
	
	function updateConcat($table, $where=1, $field, $fieldvalue){
		$this->staffM->dbQuery('UPDATE '.$table.' SET '.$field.'=CONCAT('.$field.',"'.addslashes($fieldvalue).'") WHERE '.$where.'');
	}
	
	
	function getLoggedUser(){ 
		$uid = $this->session->userdata('uid');		
		$ufromPT = $this->session->userdata('ufromPT');		
		if(!empty($uid)){
			$query = $this->db->query('SELECT e.*, CONCAT(fname," ",lname) AS name, n.title, n.org, n.dept, n.grp, n.subgrp, n.orgLevel_fk, n.orgLevel_fk AS level FROM staffs e LEFT JOIN newPositions n ON e.position=n.posID WHERE e.empID = "'.$uid.'" AND md5(CONCAT(e.username,"","dv"))="'.$this->session->userdata('u').'"');
			return $query->row();		
		}else if(!empty($ufromPT)){
			$query = $this->ptDB->query('SELECT s.username, CONCAT(s.sFirst," ",s.sLast) AS name, s.email, "exec" AS access, 0 AS empID FROM staff s WHERE username="'.$ufromPT.'" AND active="Y"');
			return $query->row();	
		}else{
			return false;
		}
	}
	 	
	function checklogged($username, $pw){
		return $query = $this->db->query('SELECT empID, username, password FROM staffs WHERE active = 1 AND username = "'.$username.'" AND password = "'.md5($pw).'" LIMIT 1');
	}
		
	function definevar($con){
		$a = array();
		if(strtolower($con)=='maritalstatus'){
			$a = array(
					'Single' => 'Single',
					'Married' => 'Married',
					'Widowed' => 'Widowed',
					'Separated' => 'Separated',
					'Divorced' => 'Divorced'
				);
		}else if($con=='yesno'){
			$a = array(
					'No' => 'No',
					'Yes' => 'Yes'
				);
		}else if($con=='yesno01' || $con=='active'){
			$a = array(
					'0' => 'No',
					'1' => 'Yes'
				);
		}else if($con=='empStatus'){
			$a = array(
						'probationary' => 'Probationary', 
						'regular' => 'Regular',
						'part-time' => 'Part-Time'
					);
		}else if($con=='gender'){
			$a = array(
						'M' => 'Male', 
						'F' => 'Female'
					);
		}else if($con=='sanctionawol'){
			$a = array(
						'1' => '1-4 Days Suspension', 
						'2' => '5-10 Days Suspension',
						'3' => 'Termination'
					);
		}else if($con=='sanctiontardiness'){
			$a = array(
						'1' => 'Verbal Warning', 
						'2' => 'Written Warning',
						'3' => '1 – 4 Days Suspension',
						'4' => '5 - 10 Days Suspension',
						'5' => 'Termination'
					);
		}else if($con=='leaveType'){
			$a = array(
						'1' => 'Vacation Leave',
						'2' => 'Sick Leave',
						'3' => 'Emergency Leave',
						'4' => 'Offsetting',
						'5' => 'Paternity Leave',
						'6' => 'Maternity Leave',
						'7' => 'Solo Parent Leave',
						'8' => 'Special Leave for Women'
					);
		}else if($con=='leaveStatus'){
			$a = array(
						'0' => 'pending approval',
						'1' => 'approved w/ pay',
						'2' => 'approved w/o pay',
						'3' => 'disapproved',
						'4' => 'additional information required'
					);						
		}else if($con=='noteType'){
			$a = array(
						'other'=>0,
						'salary'=>1,
						'performance'=>2,
						'timeoff'=>3,
						'disciplinary'=>4,
						'actions'=>5
					);
		}else if($con=='office'){
			$a = array(
						'PH-Cebu'=>'PH-Cebu',
						'US-OKC'=>'US-OKC'
					);
		}
		
		return $a;
	}
	
	function defineField($f){
		$v = '';
		if($f=='fname') $v = 'First Name';
		else if($f=='lname') $v = 'Last Name';
		else if($f=='lname') $v = 'Last Name';
		else if($f=='mname') $v = 'Middle Name';
		else if($f=='suffix') $v = 'Name Suffix';
		else if($f=='username') $v = 'Username';
		else if($f=='email') $v = 'Company E-mail';
		else if($f=='pemail') $v = 'Personal E-mail'; 
		else if($f=='address' || $f=='address1') $v = 'Address';
		else if($f=='city') $v = 'City';
		else if($f=='country') $v = 'Country';
		else if($f=='zip') $v = 'Zipcode';
		else if($f=='phone') $v = 'Phone Number';
		else if($f=='phone1') $v = 'Phone 1';
		else if($f=='phone2') $v = 'Phone 2';
		else if($f=='bdate') $v = 'Birthday';
		else if($f=='gender') $v = 'Gender';
		else if($f=='maritalStatus') $v = 'Marital Status';
		else if($f=='spouse') $v = 'Spouse';
		else if($f=='dependents') $v = 'Dependents';
		else if($f=='sss') $v = 'SSS';
		else if($f=='tin') $v = 'TIN';
		else if($f=='philhealth') $v = 'Philhealth';
		else if($f=='hdmf') $v = 'HDMF';
		else if($f=='office') $v = 'Office Branch';
		else if($f=='shift') $v = 'Shift Sched';
		else if($f=='startDate') $v = 'Start Date';
		else if($f=='idNum') $v = 'Payroll ID';
		else if($f=='supervisor') $v = 'Supervisor';
		else if($f=='department') $v = 'Department';
		else if($f=='grp') $v = 'Group'; 
		else if($f=='dept') $v = 'Department';
		else if($f=='title' || $f=='position') $v = 'Position Title';
		else if($f=='skype') $v = 'Skype Account';
		else if($f=='google') $v = 'Google Account';
		else if($f=='endDate') $v = 'Separation Date';
		else if($f=='accessEndDate') $v = 'Access End Date';
		else if($f=='fulltime') $v = 'Full-Time';
		else if($f=='empStatus') $v = 'Employee Status';
		else if($f=='regDate') $v = 'Regularization Date'; 
		else if($f=='separationDate') $v = 'SeparationDate Date';  
		else if($f=='evalDate') $v = 'Evaluation Date';  
		else if($f=='levelName' || $f=='levelID_fk') $v = 'Org Level';
		else if($f=='sal' || $f=='salary') $v = 'Salary';
		else if($f=='active') $v = 'Is Active';
		else if($f=='leaveCredits') $v = 'Leave Credits';
		else if($f=='allowance') $v = 'Monthly Allowance';
		else if($f=='bankAccnt') $v = 'Payroll Bank Account Number';
		else if($f=='hmoNumber') $v = 'HMO Policy Number ';
		
		return $v;
	}
	
	function getSupervisors(){
		$query = $this->db->query('SELECT CONCAT(fname," ",lname) AS name, empID FROM staffs WHERE empID IN (SELECT DISTINCT supervisor FROM staffs WHERE supervisor != 0)');
		$a = array();
		foreach($query->result() AS $q):
			$a[$q->empID] = $q->name;
		endforeach;
		return $a;		
	}
	
	function getStaffUnder($empID, $level){
		$query = $this->db->query('SELECT empID, CONCAT(fname," ",lname) AS name FROM staffs LEFT JOIN newPositions ON posID=position WHERE supervisor="'.$empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e LEFT JOIN newPositions ON posID=position WHERE orgLevel_fk!=0 AND orgLevel_fk<"'.$level.'" AND supervisor="'.$empID.'")');
		return $query->result();
	}
	
	function checkStaffUnderMe($username){
		$valid = true;
		if(md5($username.'dv') != $this->session->userdata('u')){
			$query = $this->db->query('SELECT username FROM staffs LEFT JOIN newPositions ON posID=position WHERE (supervisor="'.$this->user->empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e LEFT JOIN newPositions ON posID=position WHERE orgLevel_fk!=0 AND orgLevel_fk<"'.$this->user->level.'" AND supervisor="'.$this->user->empID.'")) AND username="'.$username.'"');
			$row = $query->row();
			if(!isset($row->username)) $valid = false;
		}	
		return $valid;
	}
		
	function checkStaffUnderMeByID($empID){
		$query = $this->db->query('SELECT empID FROM staffs LEFT JOIN newPositions ON posID=position WHERE (supervisor="'.$this->user->empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e LEFT JOIN newPositions ON posID=position WHERE orgLevel_fk!=0 AND orgLevel_fk<"'.$this->user->level.'" AND supervisor="'.$this->user->empID.'")) AND empID="'.$empID.'"');
		$r = $query->row();
		if(isset($r->empID))
			return true;
		else
			return false;		
	}
	
	function getStaffSupervisorsID($id){
		$supArr = array();
		$sup = $this->getSingleField('staffs', 'supervisor', 'empID="'.$id.'"');		
		while($sup !=0){
			$supArr[] = $sup;
			$sup = $this->getSingleField('staffs', 'supervisor', 'empID="'.$sup.'"');
		}
		return $supArr;
	}
	
	function compareResults($new, $orig){
		unset($new['empID']);
		unset($new['submitType']);
		$updated = array();
		if(count($orig)>0){			
			foreach($new AS $k=>$v):
				if($k=='bdate' || $k=='startDate' || $k=='endDate' || $k=='accessEndDate' || $k=='regDate'){
					if($v=='') $v = '0000-00-00';
					else $v = date('Y-m-d', strtotime($v));
				}else if($k=='bankAccnt' || $k=='hmoNumber'){
					$v = $this->staffM->encryptText($v);
				}
				
				if($v != $orig[$k])
					$updated[$k] = $v;
			endforeach;			
		}
				
		return $updated;
	}
	
	function ordinal($num){
		if ( ($num / 10) % 10 != 1 )
		{
			switch( $num % 10 )
			{
				case 1: return $num . 'st';
				case 2: return $num . 'nd';
				case 3: return $num . 'rd';  
			}
		}
		return $num . 'th';
	}
		
	function addMyNotif($empID, $ntexts, $ntype=0, $isNotif=0){
		$insArr['empID_fk'] = $empID;
		$insArr['sID'] = $this->user->empID;
		$insArr['ntexts'] = addslashes($ntexts);
		$insArr['dateissued'] = date('Y-m-d H:i:s');
		$insArr['ntype'] = $ntype;
		$insArr['isNotif'] = $isNotif;
		if($insArr['sID']==0){
			$insArr['userSID'] = $this->user->username.'|'.$this->user->name;
		}
		
		$this->insertQuery('staffMyNotif', $insArr);
	}
	
	function sendEmail( $from, $to, $subject, $body, $fromName='' ){
		$url = 'https://pt.tatepublishing.net/api.php?method=sendGenericEmail';
		  /*
		   * from = sender's email
		   * fromName = sender's name
		   * BCC = cc's
		   * replyTo = reply to email address
		   * sendTo = recipient email address
		   * subject = email subject
		   * body = email body
		   */
		 
		$toEmail = $this->config->item('toEmail');
		if($toEmail !='' ){
			$subject = $subject.' to-'.$to;
			$to = $toEmail;
		}
		
		
		$body = '<div style="font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:14px;">'.$body.'</div>';
		$fields = array(
			'from' => $from,
			'sendTo' => $to,
			'subject' => $subject,
			'body' => $body
		);

		if( !empty($fromName) ){
			$fields['fromName'] = $fromName;
		}
		//build the urlencoded data
		$postvars='';
		$sep='';
		foreach($fields as $key=>$value) { 
		   $postvars.= $sep.urlencode($key).'='.urlencode($value); 
		   $sep='&'; 
		}
		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		
		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
	}
	
	function createLeavepdf($leave){
		$leaveArr = $this->staffM->definevar('leaveType');
			
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile('includes/pdftemplates/leave_form.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		if($leave->iscancelled==1){			
			$textap = array_reverse(explode('^_^', $leave->canceldata));			
			$pdf->SetFont('Arial','B',16);
			$pdf->SetTextColor(255, 0, 0);
			$pdf->Rotate(30, 280, 40); 
			if(isset($textap[0])) $pdf->MultiCell(180, 15, $textap[0],1,'C',false);	
			else $pdf->MultiCell(180, 15, 'Cancellation approved',1,'C',false);		
			$pdf->Rotate(0);
		}
		
		$pdf->SetFont('Arial','B',9);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->setXY(13, 70);
		$pdf->MultiCell(34, 4, date('F d, Y',strtotime($leave->date_requested)),0,'C',false);
				
		$pdf->setXY(50, 70);
		$pdf->MultiCell(45, 4, $leave->name,0,'C',false);	
		
		$pdf->setXY(98, 70);
		$pdf->MultiCell(45, 4, $leaveArr[$leave->leaveType],0,'C',false);
		
		$pdf->setXY(148, 70);
		$pdf->MultiCell(50, 4, $leave->reason,0,'C',false);
		
		$pdf->setXY(25, 90);
		$pdf->Write(0, date('F d, Y', strtotime($leave->leaveStart)));
		$pdf->setXY(25, 96.5);
		$pdf->Write(0, date('h:i a', strtotime($leave->leaveStart)));
		
		$pdf->setXY(81, 90);
		$pdf->Write(0, date('F d, Y', strtotime($leave->leaveEnd)));
		$pdf->setXY(81, 96.5);
		$pdf->Write(0, date('h:i a', strtotime($leave->leaveEnd)));
				
		$pdf->setXY(150, 90);
		$pdf->Write(0, $leave->totalHours.' hours');
		
		if(file_exists(UPLOAD_DIR.$leave->username.'/signature.png'))
			$pdf->Image(UPLOAD_DIR.$leave->username.'/signature.png', 65, 102, 0);
			
		if($leave->approverID!=0){
			$sup = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$leave->approverID.'"');
			$pdf->setXY(120, 167);
			$pdf->Write(0, strtoupper($sup->name).' '.date('Y-m-d', strtotime($leave->dateApproved)));
			if(file_exists(UPLOAD_DIR.$sup->username.'/signature.png'))
				$pdf->Image(UPLOAD_DIR.$sup->username.'/signature.png', 130, 154, 0);			
			
			if($leave->status==1){
				$pdf->setXY(15.3, 143);
				$pdf->Write(0, 'X');
			}else if($leave->status==2){
				$pdf->setXY(15.3, 149);
				$pdf->Write(0, 'X');
			}else if($leave->status==3){
				$pdf->setXY(15.3, 155);
				$pdf->Write(0, 'X');
			}
				
			if(!empty($leave->remarks)){
				$pdf->setXY(15, 170);
				$pdf->MultiCell(92, 4, $leave->remarks,0,'C',false);
			}
		}
			
		if($leave->hrapprover!=0){
			$hr = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$leave->hrapprover.'"');						
			$pdf->setXY(120, 217);
			$pdf->Write(0, strtoupper($hr->name).' '.date('Y-m-d', strtotime($leave->hrdateapproved)));
			if(file_exists(UPLOAD_DIR.$hr->username.'/signature.png'))
				$pdf->Image(UPLOAD_DIR.$hr->username.'/signature.png', 130, 204, 0);
			
			$pdf->setXY(15.3, 195.7);
			$pdf->Write(0, 'X');
			$pdf->setXY(15.3, 203.5);
			$pdf->Write(0, 'X');
			
			$pdf->setXY(15.3, 215);
			$pdf->MultiCell(92, 4, $leave->hrremarks,0,'C',false);
		}
		
								
		$pdf->Output('leave_form_'.$leave->leaveID.'.pdf', 'I');
	}
		
	function createOffsetpdf($leave){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile('includes/pdftemplates/Offset_Form.pdf');
			
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		if($leave->iscancelled==1){			
			$textap = array_reverse(explode('^_^', $leave->canceldata));			
			$pdf->SetFont('Arial','B',16);
			$pdf->SetTextColor(255, 0, 0);
			$pdf->Rotate(30, 280, 40); 
			if(isset($textap[0])) $pdf->MultiCell(180, 15, $textap[0],1,'C',false);	
			else $pdf->MultiCell(180, 15, 'Cancellation approved',1,'C',false);		
			$pdf->Rotate(0);
		}
				
		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->setXY(108, 71.4);
		$pdf->Write(0, date('F d, Y', strtotime($leave->date_requested)));
		$pdf->setXY(108, 76.6);
		$pdf->Write(0, $leave->name);
				
		$pdf->SetFont('Arial','',8);
		$pdf->setXY(108, 80);
		$pdf->MultiCell(80, 4, $leave->reason ,0,'L',false);
		
		$pdf->SetFont('Arial','',9);
		$pdf->setXY(100, 94.5);
		$pdf->Write(0, date('F d, Y', strtotime($leave->leaveStart)));
		$pdf->setXY(150, 94.5);
		$pdf->Write(0, date('h:i a', strtotime($leave->leaveStart)));
		
		$pdf->setXY(100, 99);
		$pdf->Write(0, date('F d, Y', strtotime($leave->leaveEnd)));
		$pdf->setXY(150, 99);
		$pdf->Write(0, date('h:i a', strtotime($leave->leaveEnd)));
		
		$pdf->setXY(110, 103.5);
		$pdf->Write(0, $leave->totalHours.' hours');
		
		if(file_exists(UPLOAD_DIR.$leave->username.'/signature.png'))
			$pdf->Image(UPLOAD_DIR.$leave->username.'/signature.png', 70, 104, 0);
		
		//schedule of work	
		$odates = explode('|', rtrim($leave->offsetdates,'|'));
		for($i=0; $i<count($odates); $i++){
			list($start, $end) = explode(',', $odates[$i]);
			
			if(!empty($start) && !empty($end)){
				if($i==0) $y = 128.5;
				else if($i==1) $y = 132.8;
				else if($i==2) $y = 137;
				else if($i==3) $y = 141;
				else if($i==4) $y = 145;
				else if($i==5) $y = 149.5;
				else if($i==6) $y = 153.5;
				
				$pdf->setXY(45, $y);
				$pdf->Write(0, date('F d, Y', strtotime($start)));
				$pdf->setXY(110, $y);
				$pdf->Write(0, date('H:i', strtotime($start)));
				
				if(date('Y-m-d',strtotime($start)) != date('Y-m-d', strtotime($end))){
					$pdf->setXY(145, $y);
					$pdf->Write(0, date('F d, Y H:i', strtotime($end)));	
				}else{				
					$pdf->setXY(160, $y);
					$pdf->Write(0, date('H:i', strtotime($end)));		
				}	
			}
		}
		
		if($leave->approverID!=0){
			$sup = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$leave->approverID.'"');
			
			if($leave->status==1){
				$pdf->setXY(25.8, 184.8);
				$pdf->Write(0, 'X');
			}else{
				$pdf->setXY(25.8, 189);
				$pdf->Write(0, 'X');
			}		
			if(file_exists(UPLOAD_DIR.$sup->username.'/signature.png'))
				$pdf->Image(UPLOAD_DIR.$sup->username.'/signature.png', 135, 187, 0);
			$pdf->setXY(125, 200);
			$pdf->Write(0, strtoupper($sup->name).' '.date('Y/m/d', strtotime($leave->dateApproved)));
			$pdf->SetFont('Arial','',7);
			$pdf->setXY(25.8, 196);		
			$pdf->MultiCell(85, 4, $leave->remarks,0,'L',false);	
		}
			
		if($leave->hrapprover!=0){
			$hr = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$leave->hrapprover.'"');
			$pdf->SetFont('Arial','',9);						
			$pdf->setXY(25.8, 214);
			$pdf->Write(0, 'X');		
			if(file_exists(UPLOAD_DIR.$hr->username.'/signature.png'))
				$pdf->Image(UPLOAD_DIR.$hr->username.'/signature.png', 135, 217, 0);
			$pdf->setXY(125, 230);
			$pdf->Write(0, strtoupper($hr->name).' '.date('Y/m/d', strtotime($leave->hrdateapproved)));
			$pdf->SetFont('Arial','',7);
			$pdf->setXY(25.8, 222);		
			$pdf->MultiCell(85, 4, $leave->hrremarks,0,'L',false);
		}
		
		$pdf->Output('leave_form_'.$leave->leaveID.'.pdf', 'I');
	}
	
	function createCISpdf($cis, $isupname, $nsupname, $t){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile('includes/pdftemplates/CIS_Form.pdf');
			
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
				
		$pdf->SetFont('Arial','B',12);	
		if(strlen($cis->name)<26) $pdf->setXY(18, 57);
		else $pdf->setXY(18, 54);
		$pdf->MultiCell(60, 4, $cis->name ,0,'C',false);
		
		$pdf->setXY(84, 59);
		$pdf->Write(0, date('F d, Y', strtotime($cis->datefiled)));	
		
		$pdf->setXY(142, 59);
		$pdf->Write(0, date('F d, Y', strtotime($cis->effectivedate)));	
		
		$cnum=0;
		$cval = array();
		$changes = json_decode($cis->changes);	
		$pdf->SetFont('Arial','I',10);	
		if(isset($changes->position)){
			$cval[$cnum][0] = 'Change in Position Title';
			$cval[$cnum][1] = $changes->position->c;
			$cval[$cnum][2] = $changes->position->n;
			$cnum++;
		}		
		if(isset($changes->office)){
			$cval[$cnum][0] = 'Change in Office Branch';
			$cval[$cnum][1] = (($changes->office->c!='')?strtoupper($changes->office->c):'None');
			$cval[$cnum][2] = strtoupper($changes->office->n);
			$cnum++;
		}	
		if(isset($changes->shift)){
			$cval[$cnum][0] = 'Change in Shift Schedule';
			$cval[$cnum][1] = (($changes->shift->c!='')?$changes->shift->c:'None');
			$cval[$cnum][2] = $changes->shift->n;
			$cnum++;
		}
		if(isset($changes->supervisor)){
			$cval[$cnum][0] = 'Change in Immediate Supervisor';
			$cval[$cnum][1] = $changes->supervisor->c;
			$cval[$cnum][2] = $changes->supervisor->n;
			$cnum++;
		}
		if(isset($changes->salary)){
			$cval[$cnum][0] = 'Change in Basic Salary';
			$cval[$cnum][1] = 'Php '.$this->staffM->convertNumFormat($changes->salary->c);
			$cval[$cnum][2] = 'Php '.$this->staffM->convertNumFormat($changes->salary->n);
			$cnum++;
			
			$cval[$cnum][0] = 'Justification for salary adjustment:';
			$cval[$cnum][1] = $changes->salary->com;
			$cval[$cnum][2] = '';
			$cnum++;
		}
		
		if(isset($changes->separationDate)){
			$cval[$cnum][0] = 'Change in Separation Date';
			if($changes->separationDate->c=='0000-00-00')
				$cval[$cnum][1] = 'N/A - Active employee';
			else 
				$cval[$cnum][1] = $changes->separationDate->c;
			$cval[$cnum][2] = $changes->separationDate->n;
			$cnum++;
		}
		
		if(isset($changes->empStatus)){
			$cval[$cnum][0] = 'Change in Employment Status';
			$cval[$cnum][1] = ucfirst($changes->empStatus->c);
			$cval[$cnum][2] = ucfirst($changes->empStatus->n);
			$cnum++;
			
			$cval[$cnum][0] = 'Date when evaluation was conducted:';
			$cval[$cnum][1] = date('F d, Y', strtotime($changes->empStatus->evalDate));
			$cval[$cnum][2] = '';
			$cnum++;
			
			$cval[$cnum][0] = 'Effective date of regularization:';
			$cval[$cnum][1] = date('F d, Y', strtotime($changes->empStatus->regDate));
			$cval[$cnum][2] = '';
			$cnum++;
		}
		
		if(isset($cval[0])){
			$pdf->setXY(20, 88); $pdf->Write(0, $cval[0][0]); 
			$pdf->setXY(80, 86); $pdf->MultiCell(50, 4, $cval[0][1] ,0,'L',false);
			$pdf->setXY(142, 86); $pdf->MultiCell(50, 4,$cval[0][2] ,0,'L',false);
		}
		if(isset($cval[1])){
			$pdf->setXY(20, 103); $pdf->Write(0, $cval[1][0]);
			$pdf->setXY(85, 103); $pdf->Write(0, $cval[1][1]);
			$pdf->setXY(140, 103); $pdf->Write(0, $cval[1][2]);
		}
		if(isset($cval[2])){
			$pdf->setXY(20, 116); $pdf->Write(0, $cval[2][0]);
			$pdf->setXY(85, 116); $pdf->Write(0, $cval[2][1]);
			$pdf->setXY(140, 116); $pdf->Write(0, $cval[2][2]);
		}
		if(isset($cval[3])){
			$pdf->setXY(20, 128); $pdf->Write(0, $cval[3][0]);
			$pdf->setXY(85, 128); $pdf->Write(0, $cval[3][1]);
			$pdf->setXY(140, 128); $pdf->Write(0, $cval[3][2]);
		}
		if(isset($cval[4])){
			$pdf->setXY(20, 141); $pdf->Write(0, $cval[4][0]);
			$pdf->setXY(85, 141); $pdf->Write(0, $cval[4][1]);
			$pdf->setXY(140, 141); $pdf->Write(0, $cval[4][2]);
		}
		if(isset($cval[5])){
			$pdf->setXY(20, 153); $pdf->Write(0, $cval[5][0]);
			$pdf->setXY(85, 153); $pdf->Write(0, $cval[5][1]);
			$pdf->setXY(140, 153); $pdf->Write(0, $cval[5][2]);
		}
		
		$cnum++;
		if($cnum==1){ $pdf->setXY(75, 91); $pdf->Write(0, '--------- NOTHING FOLLOWS ---------'); }
		else if($cnum==2){  $pdf->setXY(75, 103); $pdf->Write(0, '--------- NOTHING FOLLOWS ---------'); }
		else if($cnum==3){  $pdf->setXY(75, 116); $pdf->Write(0, '--------- NOTHING FOLLOWS ---------'); }
		else if($cnum==4){  $pdf->setXY(75, 128); $pdf->Write(0, '--------- NOTHING FOLLOWS ---------'); }
		else if($cnum==5){  $pdf->setXY(75, 141); $pdf->Write(0, '--------- NOTHING FOLLOWS ---------'); }
		else if($cnum==6){  $pdf->setXY(75, 153); $pdf->Write(0, '--------- NOTHING FOLLOWS ---------'); }
		else if($cnum==7){  $pdf->setXY(75, 165); $pdf->Write(0, '--------- NOTHING FOLLOWS ---------'); }
		
		$pdf->SetFont('Arial','B',11);
		if(strlen($isupname)<21) $pdf->setXY(20, 209);
		else $pdf->setXY(20, 205);
		$pdf->MultiCell(50, 4, strtoupper($isupname),0,'C',false);
		
		if(strlen($nsupname)<21) $pdf->setXY(80, 209);
		else $pdf->setXY(80, 205);
		$pdf->MultiCell(50, 4, strtoupper($nsupname),0,'C',false);
			
		$pdf->setXY(132, 209);
		$pdf->MultiCell(60, 4, strtoupper($cis->prepby),0,'C',false);
	
		$pdf->setXY(45, 240);	
		$pdf->MultiCell(120, 4, strtoupper($cis->name) ,0,'C',false);
			
		
		$pdf->Output('CIS_'.str_replace(' ','_',$cis->name).'.pdf', $t);
	}
	
	function photoResizer($source_image, $destination_filename, $width = 200, $height = 150, $quality = 70, $crop = true){
		if( ! $image_data = getimagesize( $source_image ) ){
			return false;
		}
		
		switch( $image_data['mime'] )
		{
			case 'image/gif':
				$get_func = 'imagecreatefromgif';
				$suffix = ".gif";
			break;
			case 'image/jpeg';
				$get_func = 'imagecreatefromjpeg';
				$suffix = ".jpg";
			break;
			case 'image/png':
				$get_func = 'imagecreatefrompng';
				$suffix = ".png";
			break;
		}
		
		$img_original = call_user_func( $get_func, $source_image );
		$old_width = $image_data[0];
		$old_height = $image_data[1];
		$new_width = $width;
		$new_height = $height;
		$src_x = 0;
		$src_y = 0;
		$current_ratio = round( $old_width / $old_height, 2 );
		$desired_ratio_after = round( $width / $height, 2 );
		$desired_ratio_before = round( $height / $width, 2 );
		/**
		 * If the crop option is left on, it will take an image and best fit it
		 * so it will always come out the exact specified size.
		 */
		if( $crop ){
			/**
			 * create empty image of the specified size
			 */
			$new_image = imagecreatetruecolor( $width, $height );
			/**
			 * Landscape Image
			 */
			if( $current_ratio > $desired_ratio_after ){
				$new_width = $old_width * $height / $old_height;
			}
			/**
			 * Nearly square ratio image.
			 */
			if( $current_ratio > $desired_ratio_before && $current_ratio < $desired_ratio_after ){
				if( $old_width > $old_height ){
					$new_height = max( $width, $height );
					$new_width = $old_width * $new_height / $old_height;
				}else{
					$new_height = $old_height * $width / $old_width;
				}
			}
			/**
			 * Portrait sized image
			 */
			if( $current_ratio < $desired_ratio_before  ){
				$new_height = $old_height * $width / $old_width;
			}
			/**
			 * Find out the ratio of the original photo to it's new, thumbnail-based size
			 * for both the width and the height. It's used to find out where to crop.
			 */
			$width_ratio = $old_width / $new_width;
			$height_ratio = $old_height / $new_height;
			/**
			 * Calculate where to crop based on the center of the image
			 */
			$src_x = floor( ( ( $new_width - $width ) / 2 ) * $width_ratio );
			$src_y = round( ( ( $new_height - $height ) / 2 ) * $height_ratio );
		}
		/**
		 * Don't crop the image, just resize it proportionally
		 */
		else{
			if( $old_width > $old_height ){
				$ratio = max( $old_width, $old_height ) / max( $width, $height );
			}else{
				$ratio = max( $old_width, $old_height ) / min( $width, $height );
			}
			$new_width = $old_width / $ratio;
			$new_height = $old_height / $ratio;
			$new_image = imagecreatetruecolor( $new_width, $new_height );
		}
				
		if($image_data['mime']=='image/png'){
			imagealphablending( $new_image, false );
			imagesavealpha( $new_image, true );
		}
		
		/**
		 * Where all the real magic happens
		 */
		imagecopyresampled( $new_image, $img_original, 0, 0, $src_x, $src_y, $new_width, $new_height, $old_width, $old_height );
		
		switch( $image_data['mime'] )
		{
			case 'image/gif':
				imagegif($tmp, $path);
			break;
			case 'image/jpeg';
				imagejpeg( $new_image, $destination_filename, $quality );				
			break;
			case 'image/png':
				imagepng( $new_image, $destination_filename, 0 );
			break;
		}
		
		imagedestroy( $new_image );
		imagedestroy( $img_original );
		/**
		 * Return true because it worked and we're happy. Let the dancing commence!
		 */
		return true;
	}
	
	function getHRStaffID(){
		$hrArr = array();
		$query = $this->getQueryArrayResults('staffs', 'empID', 'access LIKE "%hr%"');
		for($i=0; $i<count($query); $i++){
			$hrArr[] = $query[$i]->empID;
		}
		return $hrArr;
	}
	
	function convertshift($shift){
		$stext = '';
		if($shift!=''){
			$s = explode(' ', $shift);
			if(isset($s[0])){
				$s1 = explode('|', $s[0]);
				$stext = date('h:i a', strtotime($s1[0])).' - '.date('h:i a', strtotime($s1[1])).' '.$s[1];
			}			
		}
		return $stext;		
	}
	
	function displaycis($info, $status){
		$disp = '<table class="tableInfo">
			<tr class="trhead" align="center">
				<td rowspan=2>Employee\'s Name</td>
				<td rowspan=2>Date Filed</td>
				<td rowspan=2>Effective Date</td>
				<td colspan=3>Changes</td>
				<td rowspan=2>Immediate Supervisor</td>
				<td rowspan=2>Prepared By</td>
				<td rowspan=2><br/></td>
				<td rowspan=2><br/></td>
			</tr>
			<tr class="trhead" align="center">
				<td class="weightnormal">Type</td>
				<td class="weightnormal">'.(($status==3)?'Previous':'Current').' Info</td>
				<td class="weightnormal">New Info</td>
			</tr>';
		if($status==1){
			$disp .= '<tr><td colspan=3><i>These are approved CIS but not yet the effective date.</i></td></tr>';
		}
		
		if(count($info)==0){
			$disp .= '<tr><td class="weightnormal" colspan=10>No pending CIS.</td></tr>';
		}else{
			foreach($info AS $a):
				$c = json_decode($a->changes);
				$cnt = 0;
				$arr = array();
				if(isset($c->position)){
					$arr[$cnt][0] = 'Position Title'; $arr[$cnt][1] = $c->position->c; $arr[$cnt][2] = $c->position->n;
					$cnt++;
				}			
				if(isset($c->office)){
					$arr[$cnt][0] = 'Office Branch'; $arr[$cnt][1] = strtoupper($c->office->c); $arr[$cnt][2] = strtoupper($c->office->n);
					$cnt++;
				}
				if(isset($c->shift)){
					$arr[$cnt][0] = 'Shift Schedule'; $arr[$cnt][1] = strtoupper($c->shift->c); $arr[$cnt][2] = strtoupper($c->shift->n);
					$cnt++;
				}				
				if(isset($c->supervisor)){
					$arr[$cnt][0] = 'Immediate Supervisor'; $arr[$cnt][1] = $c->supervisor->c; $arr[$cnt][2] = $c->supervisor->n;
					$cnt++;
				}
				if(isset($c->salary)){
					$arr[$cnt][0] = 'Basic Salary'; $arr[$cnt][1] = 'Php '.$this->staffM->convertNumFormat($c->salary->c); $arr[$cnt][2] = 'Php '.$this->staffM->convertNumFormat($c->salary->n);
					$arr[$cnt][3] = $c->salary->com;
					$cnt++;	
				}
				if(isset($c->separationDate)){
					$arr[$cnt][0] = 'Separation Date'; $arr[$cnt][1] = (($c->separationDate->c)?'N/A - Active Employee':date('F d,Y',strtotime($c->separationDate->c))); $arr[$cnt][2] = date('F d,Y',strtotime($c->separationDate->n));
					$cnt++;
				}
				if(isset($c->empStatus)){
					$arr[$cnt][0] = 'Employment Status'; $arr[$cnt][1] = ucfirst($c->empStatus->c); $arr[$cnt][2] = ucfirst($c->empStatus->n);
					$arr[$cnt][3] = date('F d, Y',strtotime($c->empStatus->evalDate));
					$cnt++;	
				}
				
				$disp .= '<tr class="maintr">';
				$disp .= '<td><a href="'.$this->config->base_url().'staffinfo/'.$a->username.'/">'.$a->name.'</a></td>';
				$disp .= '<td>'.date('d M Y', strtotime($a->datefiled)).'</td>';
				$disp .= '<td>'.date('d M Y', strtotime($a->effectivedate)).'</td>';
				$disp .= '<td colspan=3>';
					$disp .= '<table class="tableInfo">';
					for($i=0; $i<$cnt; $i++){
						$disp .= '<tr><td width="30%">'.$arr[$i][0].'</td><td width="35%">'.$arr[$i][1].'</td><td class="errortext" width="35%">'.$arr[$i][2].'</td></tr>';	
						if($arr[$i][0]=='Basic Salary')				
							$disp .= '<tr><td width="30%">Justification for salary adjustment: </td><td colspan=2 class="errortext">'.$arr[$i][3].'</td></tr>';					
						if($arr[$i][0]=='Employment Status')
							$disp .= '<tr><td width="30%">Date when evaluation was conducted: </td><td colspan=2 class="errortext">'.$arr[$i][3].'</td></tr>';
					}
					$disp .= '</table>';			
				$disp .= '</td>';
				$disp .= '<td>'.$a->supName.'</td>';
				$disp .= '<td>'.$a->prepby.'</td>';
				
			if($status==3 || $status==1 && $a->effectivedate>=date('Y-m-d')){
				$disp .= '<td><a class="iframe" href="'.$this->config->base_url().UPLOADS.'CIS/CIS_'.$a->cisID.'.pdf"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>';
				$disp .= '<td><br/></td>';
			}else{
				$disp .= '<td><a class="iframe" href="'.$this->config->base_url().'cispdf/'.$a->cisID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>';
				$disp .= '<td><a class="iframe" href="'.$this->config->base_url().'updatecis/'.$a->cisID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>';
				$disp .= '</tr>';
			}
			endforeach; 
		}
		$disp .= '</table>';
		return $disp;
	}
	
	function genCOEpdf($row){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile('includes/pdftemplates/COE_form.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','B',14);
		$pdf->setXY(95, 91);
		$pdf->Write(0, $row->name);
		$pdf->setXY(95, 100);
		$pdf->Write(0, date('F d, Y',strtotime($row->startDate)));
		$pdf->setXY(95, 109);
		$pdf->Write(0, $row->title);
		
		$sal = (double)str_replace(',','',$row->salary);
		$allowance = (double)str_replace(',','',$row->allowance);
		
		$pdf->setXY(125, 127);
		$pdf->Write(0, $this->staffM->convertNumFormat(($sal*12)));
		$pdf->setXY(127, 137);
		$pdf->Write(0, $this->staffM->convertNumFormat(($allowance*12)) );
		$pdf->setXY(125, 155);
		$pdf->Write(0, $this->staffM->convertNumFormat((($sal*12)+($allowance*12))));
		
		$pdf->SetFont('Arial','B',12);
		$pdf->setXY(10, 177);
		$pdf->MultiCell(180, 15, $row->purpose,0,'C',false);	
		
		$pdf->setXY(95, 205);
		$pdf->Write(0, date('F d, Y',strtotime($row->dateissued)));
		
		
		$pdf->Output('coe_form'.$row->coeID.'.pdf', 'I');
	}
	
	function getEmailTemplate($id, $empID){
		$template = $this->getSingleField('staffCustomEmails', 'emailTemplate', 'emailID="'.$id.'"');
		$staff = $this->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, fname, lname', 'emailID="'.$empID.'"');
		
		$template = str_replace('_NAME', $staff->name, $template);
		$template = str_replace('_FNAME', $staff->fname, $template);
		$template = str_replace('_LNAME', $staff->lname, $template);
		return $template;
	}
	
	function getLeaveStatusText($status, $iscancelled){
		$leaveStatusArr = $this->staffM->definevar('leaveStatus');
		$status = ucfirst($leaveStatusArr[$status]); 
		
		if($iscancelled==1 && $status==0)
			$status = 'CANCELLED';
		else if($iscancelled==1)
			$status .= ' - <i>CANCELLED</i>';
		else if($iscancelled==2)
			$status .= ' - <i>Pending Cancel Approval</i>'; 
		else if($iscancelled==3)
			$status .= ' - <i>Pending HR Cancel Approval</i>'; 
		else if($iscancelled==4)
			$status = 'Additional Information Required'; 
		
		return $status;
	}
	
	function leaveTableDisplay($rQuery){
		$leaveTypeArr = $this->staffM->definevar('leaveType');
		$disp = '<table class="tableInfo">
				<thead>
					<tr>
						<th>Leave ID</th>
						<th>Name</th>
						<th>Type of Leave</th>
						<th>Leave Start</th>
						<th>Leave End</th>		
						<th>Department</th>
						<th>Approved By</th>
						<th>HR</th>
						<th width="150px">Status</th>
						<th><br/></th>
					</tr>
				</thead>';
				
		foreach($rQuery AS $row){
			$disp .= '<tr>
					<td>'.$row->leaveID.'</td>
					<td><a href="'.$this->config->base_url().'staffinfo/'.$row->username.'/">'.$row->name.'</a></td>
					<td>'.$leaveTypeArr[$row->leaveType].'</td>
					<td>'.date('M d, Y h:i a', strtotime($row->leaveStart)).'</td>
					<td>'.date('M d, Y h:i a', strtotime($row->leaveEnd)).'</td>
					<td>'.$row->dept.'</td>
					<td>'.$row->approverName.'</td>
					<td>'.$row->hrName.'</td>
					<td>'.$this->staffM->getLeaveStatusText($row->status, $row->iscancelled).'</td>
					<td><a class="iframe" href="'.$this->config->base_url().'staffleaves/'.$row->leaveID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
				</tr>
			';
		}
		$disp .= '</table>';
		return $disp;
	}
	
	function displayInfo($c, $fld, $v, $t=false, $placeholder='', $showhide=''){
		$vvalue = $v;
		if($fld=='pemail' || $fld=='email')
			$vvalue = '<a href="mailto:'.$v.'">'.$v.'</a>';
		$aclass='';
		if(in_array($fld,array('bdate', 'startDate', 'endDate', 'accessEndDate', 'regDate'))) $aclass = 'datepick';
		
		$disp = '<tr class="'.$c.'tr '.$showhide.'">
					<td width="30%">'.$this->defineField($fld).'</td>
					<td class="td'.$fld.'">';
				if($t==true){	
					if(in_array($fld,array('gender', 'maritalStatus', 'supervisor', 'title', 'empStatus', 'active', 'office', 'levelID_fk'))){
						$disp .= '<select id="'.$fld.'" class="forminput '.$c.'input hidden '.$aclass.'">';
						$disp .= '<option value=""></option>';
						if($fld=='supervisor'){
							$aRR = $this->getQueryResults('staffs', 'empID AS id, CONCAT(fname," ",lname) AS val, active', 'is_supervisor=1', '', 'fname ASC');
						}else if($fld=='title'){
							$aRR = $this->getQueryResults('newPositions', 'posID AS id, title AS val, org, dept, grp, subgrp, active', '1', '', 'title ASC');
						}else if($fld=='levelID_fk'){
							$aRR = $this->getQueryResults('orgLevel', 'levelID AS id, levelName AS val, 1 AS active', '1');
						}						
						
						if($fld=='supervisor' || $fld=='title' || $fld=='levelID_fk'){							
							foreach($aRR AS $va):
								if( $v==$va->id || ($fld=='title' && $v==$va->val)) $vvalue=$va->val;
								
								if($va->active==1 || $v==$vvalue){								
									$disp .= '<option value="'.$va->id.'" '.(( $v==$va->id || ($fld=='title' && $v==$va->val)) ? 'selected="selected"' : '').'>';
									if($fld=='title') $disp .= $va->val.' ('.$va->org.' > '.$va->dept.' > '.$va->grp.' > '.$va->subgrp.')';
									else $disp .= $va->val;
									
									$disp .= '</option>';
								}
							endforeach;
						}else{
							$arr = $this->definevar($fld);
							foreach($arr AS $k=>$va):
								if($k==$v) $vvalue=$va;
								$disp .= '<option value="'.$k.'" '.(($k==$v) ? 'selected="selected"' : '').'>'.$va.'</option>';
							endforeach;
						}						
						$disp .= '</select>';
					}else{
						$disp .= '<input type="text" class="forminput '.$c.'input hidden '.$aclass.'" placeholder="'.$placeholder.'" value="'.(($fld=='bankAccnt' || $fld=='hmoNumber')?$this->staffM->decryptText($v):$v).'" id="'.$fld.'">';
					}	
					$disp .= '<span class="'.$c.'fld">';					
						if($fld=='sal' || $fld=='allowance') $disp .= 'Php '.$vvalue;
						else if($fld=='bankAccnt' || $fld=='hmoNumber') $disp .= $this->staffM->decryptText($vvalue);
						else $disp .= $vvalue;	
					$disp .= '</span>';
				}else{
					$disp .= $vvalue;
				}				
		$disp .= '</td>
				</tr>';
				
		return $disp;
		
	}	
	
	public function mergeMyNotes($empID, $username){
		$notesArr = array();
		$noteType = $this->staffM->definevar('noteType');
		$myNotes = $this->staffM->getQueryResults('staffMyNotif', 'staffMyNotif.*, username, CONCAT(fname," ",lname) AS name', 'empID_fk="'.$empID.'"','LEFT JOIN staffs ON empID=sID', 'dateissued DESC');
		$ptNotes = $this->staffM->getPTQueryResults('eNotes', 'eNotes.*, eData.u', 'u="'.$username.'"', 'LEFT JOIN eData ON eKey=eNoteOwner', 'eNoteStamp DESC');
		
		foreach($myNotes AS $m):
			$notesArr[] = array(
				'from' => 'careerPH',
				'timestamp' => $m->dateissued,
				'note' => $m->ntexts,
				'staffID' => $m->sID,
				'username' => $m->username,
				'name' => $m->name,
				'type' => $m->ntype,
				'access' => $m->accesstype
			);
		endforeach;
		
		
		foreach($ptNotes AS $p):
			$notesArr[] = array(
				'from' => 'pt',
				'timestamp' => $p->eNoteStamp,
				'note' => $p->eNoteText,
				'staffID' => 0,
				'username' => $p->username,
				'type' => ((isset($noteType[$p->category]))?$noteType[$p->category]:0),
				'access' => $p->permissions
			);
		endforeach;
				
		foreach ($notesArr as $key => $row) {
			$volume[$key]  = $row['timestamp'];
		}

		array_multisort($volume, SORT_DESC, $notesArr);
		
		return $notesArr;		
	}  
	
	public function convertNumFormat($n){
		return number_format((int)str_replace(',','',$n),2);
	}
	
	function encryptText($text){		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		$secret_key = $this->config->item('demnCryptKey');
		$secret_iv = $this->config->item('demnCryptIV');

		$key = hash('sha256', $secret_key);		
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		$output = openssl_encrypt($text, $encrypt_method, $key, 0, $iv);
		$output = base64_encode($output);		

		return $output;		
    }
	
	function decryptText($text){		
		$output = false;

		$encrypt_method = "AES-256-CBC";
		$secret_key = $this->config->item('demnCryptKey');
		$secret_iv = $this->config->item('demnCryptIV');

		$key = hash('sha256', $secret_key);		
		$iv = substr(hash('sha256', $secret_iv), 0, 16);
		$output = openssl_decrypt(base64_decode($text), $encrypt_method, $key, 0, $iv);	

		return $output;		
    }
	
}

?>