<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staffmodel extends CI_Model {

	var $ptDB;
	var $db;
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();
		
		$this->load->model('Textdefinemodel', 'txtM');	
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
	
	function getPTSQLQueryResults($sql){
		$query = $this->ptDB->query($sql);
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
		$query = $this->db->query("SELECT ".$fields." FROM ".$table." ".$join." WHERE ".$where." ".$orderby." LIMIT 1");
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
		$uName = '';
		if(isset($_SESSION['u'])) $uName = $_SESSION['u'];
		
		if(!empty($uName)){
			$queryDB = $this->db->query('SELECT e.*, CONCAT(fname," ",lname) AS name, n.title, n.org, n.dept, n.grp, n.subgrp, n.orgLevel_fk, e.levelID_fk AS level FROM staffs e LEFT JOIN newPositions n ON e.position=n.posID WHERE md5(CONCAT(e.username,"","dv"))="'.$uName.'" OR e.username="'.$uName.'"');
			$row = $queryDB->row();	
			if(count($row)==0){
				$queryDB = $this->ptDB->query('SELECT s.username, CONCAT(s.sFirst," ",s.sLast) AS name, s.email, "exec" AS access, 0 AS empID, 0 AS level FROM staff s WHERE username="'.$uName.'" AND active="Y"');
				$row = $queryDB->row();	
			}
			
			if(count($row)>0) return $row;
			else return false;	
		}else{
			return false;
		}
	}
	
	function getUserAccess(){			
		$access = new stdClass;
		$access->accessFull = false;
		$access->accessHR = false;
		$access->accessFinance = false;
		$access->accessExec = false;
		$access->accessFullHR = false;
		$access->accessFullFinance = false;
		$access->accessFullHRFinance = false;
		
		if($this->user!=false){
			$access->myaccess = explode(',',$this->user->access);
			if(in_array('full', $access->myaccess)) $access->accessFull = true;
			if(in_array('hr', $access->myaccess)) $access->accessHR = true;
			if(in_array('finance', $access->myaccess)) $access->accessFinance = true;
			if(in_array('exec', $access->myaccess)) $access->accessExec = true;
			if(count(array_intersect($access->myaccess,array('full','hr')))>0) $access->accessFullHR = true;
			if(count(array_intersect($access->myaccess,array('full','finance')))>0) $access->accessFullFinance = true;
			if(count(array_intersect($access->myaccess,array('full','hr','finance')))>0) $access->accessFullHRFinance = true;	
		}
		
		return $access;
	}
	 	
	function checklogged($username, $pw){
		return $query = $this->db->query('SELECT empID, username, password FROM staffs WHERE active = 1 AND username = "'.$username.'" AND password = "'.md5($pw).'" LIMIT 1');
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
		$query = $this->db->query('SELECT empID, CONCAT(fname," ",lname) AS name FROM staffs WHERE supervisor="'.$empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e WHERE levelID_fk!=0 AND levelID_fk<"'.$level.'" AND supervisor="'.$empID.'")');
		return $query->result();
	}
	
	function checkStaffUnderMe($username){
		$valid = true;
		if(md5($username.'dv') != $this->session->userdata('u')){
			$query = $this->db->query('SELECT username FROM staffs WHERE (supervisor="'.$this->user->empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e WHERE levelID_fk!=0 AND levelID_fk<"'.$this->user->level.'" AND supervisor="'.$this->user->empID.'")) AND username="'.$username.'"');
			$row = $query->row();
			if(!isset($row->username)) $valid = false;
		}	
		return $valid;
	}
		
	function checkStaffUnderMeByID($empID){
		$query = $this->db->query('SELECT empID FROM staffs WHERE (supervisor="'.$this->user->empID.'" OR supervisor IN (SELECT DISTINCT empID FROM staffs e WHERE levelID_fk!=0 AND levelID_fk<"'.$this->user->level.'" AND supervisor="'.$this->user->empID.'")) AND empID="'.$empID.'"');
		$r = $query->row();
		if(isset($r->empID))
			return true;
		else
			return false;		
	}
	
	function getStaffSupervisorsID($id){
		$cnt = 0;
		$supArr = array();		
		$sup = $this->getSingleField('staffs', 'supervisor', 'empID="'.$id.'"');	
		//get supervisors id until 2nd level manager
		while($sup !=0 && $cnt<2){
			$supArr[] = $sup;
			$sup = $this->getSingleField('staffs', 'supervisor', 'empID="'.$sup.'"');
			$cnt++;
		}
		return $supArr;
	}
	
	function compareResults($new, $orig){
		unset($new['empID']);
		unset($new['submitType']);
		$encArr = $this->config->item('encText');
		$updated = array();
		if(count($orig)>0){			
			foreach($new AS $k=>$v):
				if($k=='bdate' || $k=='startDate' || $k=='endDate' || $k=='accessEndDate' || $k=='regDate'){
					if($v=='') $v = '0000-00-00';
					else $v = date('Y-m-d', strtotime($v));
				}else if(in_array($k, $encArr)){
					if($k=='sal') $v = str_replace(',','', str_replace('.00','', $v));
					
					$v = $this->txtM->encryptText($v);
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
	
	/***
		$ntype 0-other, 1-salary, 2-performance, 3-timeoff, 4-disciplinary, 5-actions
	***/
	function addMyNotif($empID, $ntexts, $ntype=0, $isNotif=0, $sID=''){
		$insArr['empID_fk'] = $empID;
		
		if($sID!='')$insArr['sID'] = $sID;
		else $insArr['sID'] = $this->user->empID;
		
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
		$leaveArr = $this->config->item('leaveType');
			
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'leave_form.pdf');
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
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'Offset_Form.pdf');
			
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
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'CIS_Form.pdf');
			
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
			$cval[$cnum][1] = 'Php '.$this->txtM->convertNumFormat($changes->salary->c);
			$cval[$cnum][2] = 'Php '.$this->txtM->convertNumFormat($changes->salary->n);
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
			if($cval[1][0]=='Justification for salary adjustment:'){
				$pdf->setXY(20, 103); $pdf->Write(0, $cval[1][0]);
				$pdf->setXY(80, 98); $pdf->MultiCell(115, 4, $cval[1][1],0,'L',false);
			}else{
				$pdf->setXY(20, 103); $pdf->Write(0, $cval[1][0]);
				$pdf->setXY(85, 103); $pdf->Write(0, $cval[1][1]);
				$pdf->setXY(140, 103); $pdf->Write(0, $cval[1][2]);
			}			
		}
		if(isset($cval[2])){
			if($cval[2][0]=='Justification for salary adjustment:'){
				$pdf->setXY(20, 103); $pdf->Write(0, $cval[2][0]);
				$pdf->setXY(80, 111); $pdf->MultiCell(115, 4, $cval[2][1],0,'L',false);
			}else{
				$pdf->setXY(20, 116); $pdf->Write(0, $cval[2][0]);
				$pdf->setXY(85, 116); $pdf->Write(0, $cval[2][1]);
				$pdf->setXY(140, 116); $pdf->Write(0, $cval[2][2]);
			}
		}
		if(isset($cval[3])){
			if($cval[3][0]=='Justification for salary adjustment:'){
				$pdf->setXY(20, 103); $pdf->Write(0, $cval[3][0]);
				$pdf->setXY(80, 123); $pdf->MultiCell(115, 4, $cval[3][1],0,'L',false);
			}else{
				$pdf->setXY(20, 128); $pdf->Write(0, $cval[3][0]);
				$pdf->setXY(85, 128); $pdf->Write(0, $cval[3][1]);
				$pdf->setXY(140, 128); $pdf->Write(0, $cval[3][2]);
			}
		}
		if(isset($cval[4])){
			if($cval[4][0]=='Justification for salary adjustment:'){
				$pdf->setXY(20, 103); $pdf->Write(0, $cval[4][0]);
				$pdf->setXY(80, 136); $pdf->MultiCell(115, 4, $cval[4][1],0,'L',false);
			}else{
				$pdf->setXY(20, 141); $pdf->Write(0, $cval[4][0]);
				$pdf->setXY(85, 141); $pdf->Write(0, $cval[4][1]);
				$pdf->setXY(140, 141); $pdf->Write(0, $cval[4][2]);
			}
		}
		if(isset($cval[5])){
			if($cval[5][0]=='Justification for salary adjustment:'){
				$pdf->setXY(20, 103); $pdf->Write(0, $cval[5][0]);
				$pdf->setXY(80, 148); $pdf->MultiCell(115, 4, $cval[5][1],0,'L',false);
			}else{
				$pdf->setXY(20, 153); $pdf->Write(0, $cval[5][0]);
				$pdf->setXY(85, 153); $pdf->Write(0, $cval[5][1]);
				$pdf->setXY(140, 153); $pdf->Write(0, $cval[5][2]);
			}
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
			$disp .= '<tr><td class="weightnormal" colspan=10>None.</td></tr>';
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
					$arr[$cnt][0] = 'Basic Salary'; $arr[$cnt][1] = 'Php '.$this->txtM->convertNumFormat($c->salary->c); $arr[$cnt][2] = 'Php '.$this->txtM->convertNumFormat($c->salary->n);
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
				$disp .= '<td><a class="iframe" href="'.$this->config->base_url().UPLOADS.'CIS/'.$a->signedDoc.'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>';
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
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'COE_form.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','B',14);
		$pdf->setXY(95, 91);
		$pdf->Write(0, $row->name);
		$pdf->setXY(95, 100);
		$pdf->Write(0, date('F d, Y',strtotime($row->startDate)));
		$pdf->setXY(95, 109);
		$pdf->Write(0, $row->title);
		
		$sal = (double)str_replace(',','',$this->txtM->decryptText($row->salary));
		$allowance = (double)str_replace(',','',$row->allowance);
		
		$pdf->setXY(125, 127);
		$pdf->Write(0, $this->txtM->convertNumFormat(($sal*12)));
		$pdf->setXY(127, 137);
		$pdf->Write(0, $this->txtM->convertNumFormat(($allowance*12)) );
		$pdf->setXY(125, 155);
		$pdf->Write(0, $this->txtM->convertNumFormat((($sal*12)+($allowance*12))));
		
		$pdf->SetFont('Arial','B',12);
		$pdf->setXY(10, 177);
		$pdf->MultiCell(180, 15, ((!empty($row->purposeEdited))?$row->purposeEdited:$row->purpose),0,'C',false);	
		
		$pdf->setXY(87, 205);
		$pdf->Write(0, date('F d, Y',strtotime($row->dateissued)));
		
		$pdf->SetFont('Arial','',10);
		$pdf->setXY(151, 127);
		$pdf->Write(0, '(Excluding 13th month pay)');
		
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
		$leaveStatusArr = $this->config->item('leaveStatus');
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
	
	function leaveTableDisplay($rQuery, $type){
		$leaveTypeArr = $this->config->item('leaveType');
		$yellowArr = array('imquery', 'imcancelledquery', 'allpending');
		$hideArr = array('allpending', 'allapproved', 'allapprovedNopay', 'alldisapproved', 'allcancelled');
		$disp = '<table class="tableInfo fs11px '.((in_array($type,$hideArr))?'hidden':'').'" id="tbl'.$type.'">
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
			if(in_array($type, $yellowArr) && $this->access->accessFull==true && $this->user->empID==$row->supervisor)
				$disp .= '<tr style="background-color:yellow;">';
			else
				$disp .= '<tr>';	
				
			$disp .= '<td>'.$row->leaveID.'</td>
					<td><a href="'.$this->config->base_url().'staffinfo/'.$row->username.'/">'.$row->name.'</a></td>
					<td>'.$leaveTypeArr[$row->leaveType].'</td>
					<td>'.date('d M y h:i a', strtotime($row->leaveStart)).'</td>
					<td>'.date('d M y h:i a', strtotime($row->leaveEnd)).'</td>
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
					<td width="30%">'.$this->config->item('txt_'.$fld).'</td>
					<td class="td'.$fld.'">';
				if($t==true){	
					if(in_array($fld,array('gender', 'maritalStatus', 'supervisor', 'title', 'empStatus', 'active', 'office', 'staffHolidaySched', 'levelID_fk', 'terminationType', 'taxstatus'))){
						$disp .= '<select id="'.$fld.'" class="forminput '.$c.'input hidden '.$aclass.'">';
						$disp .= '<option value=""></option>';
						if($fld=='supervisor'){
							$aRR = $this->getQueryResults('staffs', 'empID AS id, CONCAT(fname," ",lname) AS val, active', 'levelID_fk>0', '', 'fname ASC');
						}else if($fld=='title'){
							$aRR = $this->getQueryResults('newPositions', 'posID AS id, title AS val, org, dept, grp, subgrp, active', '1', '', 'title ASC');
						}else if($fld=='levelID_fk'){
							$aRR = $this->getQueryResults('orgLevel', 'levelID AS id, levelName AS val, 1 AS active', '1');
						}						
						
						if($fld=='supervisor' || $fld=='title' || $fld=='levelID_fk'){							
							foreach($aRR AS $va):
								if( $v==$va->id || ($fld=='title' && $v==$va->val)) $vvalue=$va->val;
								
								if($va->active==1 || ($va->active==0 && $v==$va->id)){								
									$disp .= '<option value="'.$va->id.'" '.(( $v==$va->id || ($fld=='title' && $v==$va->val)) ? 'selected="selected"' : '').'>';
									if($fld=='title') $disp .= $va->val.' ('.$va->org.' > '.$va->dept.' > '.$va->grp.' > '.$va->subgrp.')';
									else $disp .= $va->val;
									
									$disp .= '</option>';
								}
							endforeach;
						}else{ 
							$arr = $this->config->item($fld);							
							foreach($arr AS $k=>$va):
								if($k==$v) $vvalue=$va;
								$disp .= '<option value="'.$k.'" '.(($k==$v) ? 'selected="selected"' : '').'>'.$va.'</option>';
							endforeach;
						}						
						$disp .= '</select>';
					}else{
						$disp .= '<input type="text" class="forminput '.$c.'input hidden '.$aclass.'" placeholder="'.$placeholder.'" value="'.$this->txtM->convertDecryptedText($fld, $v, 1).'" id="'.$fld.'">';
					}	
					$disp .= '<span class="'.$c.'fld">';						
						$disp .= $this->txtM->convertDecryptedText($fld, $vvalue);
					$disp .= '</span>';
				}else{
					if($fld=='staffHolidaySched'){
						$farr = $this->config->item($fld);
						$disp .= $farr[$vvalue];
					}else
						$disp .= $vvalue;
				}				
		$disp .= '</td>
				</tr>';
				
		return $disp;
		
	}	
	
	public function mergeMyNotes($empID, $username){
		$notesArr = array();
		$noteType = $this->config->item('noteType');
		$myNotes = $this->staffM->getQueryResults('staffMyNotif', 'staffMyNotif.*, username, CONCAT(fname," ",lname) AS name', 'empID_fk="'.$empID.'"','LEFT JOIN staffs ON empID=sID', 'dateissued DESC');
		$ptNotes = $this->staffM->getPTQueryResults('eNotes', 'eNotes.*, eData.u, "" AS userSID', 'u="'.$username.'"', 'LEFT JOIN eData ON eKey=eNoteOwner', 'eNoteStamp DESC');
		
		foreach($myNotes AS $m):
			$notesArr[] = array(
				'from' => 'careerPH',
				'timestamp' => $m->dateissued,
				'note' => $m->ntexts,
				'staffID' => $m->sID,
				'username' => $m->username,
				'name' => $m->name,
				'type' => $m->ntype,
				'access' => $m->accesstype,
				'exec' => $m->userSID
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
				'access' => $p->permissions,
				'exec' => $p->userSID
			);
		endforeach;
						
		foreach ($notesArr as $key => $row) {
			$volume[$key]  = $row['timestamp'];
		}

		if(!empty($notesArr) && !empty($volume))
			array_multisort($volume, SORT_DESC, $notesArr);
		
		return $notesArr;		
	}  
		
	function infoTextVal($type, $tval){
		$was = '';
		
		if($type=='title') 
			$was = $this->staffM->getSingleField('newPositions', 'title', 'posID="'.$tval.'"');
		else if($type=='supervisor')
			$was = $this->staffM->getSingleField('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$tval.'"');
		else if($type=='levelID_fk')
			$was = $this->staffM->getSingleField('orgLevel', 'levelName AS name', 'levelID="'.$tval.'"');
		else if($type=='terminationType' || ( $type=='taxstatus' && !empty($tval))){
			$tarr = $this->config->item($type); 
			$was = $tarr[$tval];
		}else if($type=='staffHolidaySched'){
			$schedLoc = $this->config->item('staffHolidaySched');
			$was = $schedLoc[$tval];
		}else 
			$was = $this->txtM->convertDecryptedText($type, $tval);
			
		if($was=='')
			$was = '<i>none</i>';
			
		return $was;		
	}
	
	function countResults($type){
		$cnt = 0;
		if($type=='cis'){
			$cnt = $this->staffM->getSingleField('staffCIS', 'COUNT(cisID) AS cnt', 'status=0');
		}else if($type=='coaching'){
			$condition = '';
			if($this->access->accessHR==false){
				$ids = '"",'; //empty value for staffs with no under yet
				$myStaff = $this->staffM->getStaffUnder($this->user->empID, $this->user->level);				
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				$condition .= ' AND empID_fk IN ('.rtrim($ids,',').')';
			}			
			$cnt = $this->staffM->getSingleField('staffCoaching', 'COUNT(coachID) AS cnt', 'status=0 AND coachedEval<="'.date('Y-m-d').'"'.$condition);
		}else if($type=='updateRequest'){
			$cnt = $this->staffM->getSingleField('staffUpdated', 'COUNT(updateID) AS cnt', 'status=0');
		}else if($type=='pendingCOE'){
			$cnt = $this->staffM->getSingleField('staffCOE', 'COUNT(coeID) AS cnt', 'status=0');
		}else if($type=='staffLeaves'){
			if($this->access->accessFull==true){
				$query = $this->db->query('SELECT COUNT(leaveID) AS cnt FROM staffLeaves LEFT JOIN staffs ON empID=empID_fk WHERE status=0 AND iscancelled=0 AND supervisor="'.$this->user->empID.'" LIMIT 1');
				$r = $query->row();
				$cnt = $r->cnt;
			}else{
				$ids = '"",'; //empty value for staffs with no under yet
				$myStaff = $this->staffM->getStaffUnder($this->user->empID, $this->user->level);				
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				
				$cnt = $this->staffM->getSingleField('staffLeaves', 'COUNT(leaveID) AS cnt', 'status=0 AND iscancelled=0 AND empID_fk IN ('.rtrim($ids,',').')');
			}
			
			if($this->access->accessHR==true){
				$cnt += $this->staffM->getSingleField('staffLeaves', 'COUNT(leaveID) AS cnt', '(status=1 OR status=2) AND ((iscancelled=0 AND hrapprover=0) OR iscancelled=3 OR iscancelled=4)');
			}
		}else if($type=='nte'){	
			//if HR
			if($this->access->accessHR==true){
				$cnt = $this->staffM->getSingleField('staffNTE', 'COUNT(nteID) AS cnt', '(status=1 AND (nteprinted="" OR (nteprinted!="" AND nteuploaded=""))) OR ((status=0 OR status=3) AND (carprinted="" OR (carprinted!="" AND caruploaded="")))', 'LEFT JOIN staffs ON empID=empID_fk');				
			}else{
				$ids = '"",'; //empty value for staffs with no under yet
				$myStaff = $this->staffM->getStaffUnder($this->user->empID, $this->user->level);				
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				
				$cnt = $this->staffM->getSingleField('staffNTE', 'COUNT(nteID) AS cnt', 'empID_fk IN ('.rtrim($ids,',').') AND ((status=1 AND nteprinted="") OR (status=0 AND carprinted="") OR (status=1 AND nteprinted!="" AND nteuploaded="") OR (status=0 AND carprinted!="" AND caruploaded=""))');
			}			
		}
		
		return $cnt;
	}
	
	function createCoachingPDF($row, $type){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'coaching_form.pdf');
			
		if($type=='evaluation') $tplIdx = $pdf->importPage(3);
		else $tplIdx = $pdf->importPage(1);
			
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
				
		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->setXY(40, 55);
		$pdf->Write(0, $row->name);
		$pdf->setXY(35, 60);
		$pdf->Write(0, $row->title);
		$pdf->setXY(35, 65.5);
		$pdf->Write(0, $row->dept);
		$pdf->setXY(35, 70.5);
		$pdf->Write(0, $row->reviewer);
		$pdf->setXY(65, 75.5);
		$pdf->Write(0, $row->coachedImprovement);
		
		if(isset($row->supName)){
			$pdf->setXY(143, 55);
			$pdf->Write(0, $row->supName);
			$pdf->setXY(160, 60);
			$pdf->Write(0, $row->supTitle);
		}
		if(isset($row->sup2ndName)){
			$pdf->setXY(143, 65.5);
			$pdf->Write(0, $row->sup2ndName);
			$pdf->setXY(160, 70.5);
			$pdf->Write(0, $row->sup2ndTitle);
		}
		
		$pdf->setXY(270, 55);
		$pdf->Write(0, date('F d, Y',strtotime($row->coachedDate)));
		$pdf->setXY(270, 60);
		$pdf->Write(0, date('F d, Y',strtotime($row->coachedEval)));
		
		$allAE = explode('--^_^--',$row->coachedAspectExpected);						
		if($type=='expectation'){
			if(isset($allAE[0])){
				$deta = explode('++||++',$allAE[0]);
				$pdf->setXY(20, 108);
				$pdf->MultiCell(110, 4, $deta[0],0,'L',false);
				$pdf->setXY(135, 108);
				$pdf->MultiCell(110, 4, $deta[1],0,'L',false);	
			}
			if(isset($allAE[1])){
				$deta = explode('++||++',$allAE[1]);
				$pdf->setXY(20, 127);
				$pdf->MultiCell(110, 4, $deta[0],0,'L',false);
				$pdf->setXY(135, 127);
				$pdf->MultiCell(110, 4, $deta[1],0,'L',false);
			}
			if(isset($allAE[2])){
				$deta = explode('++||++',$allAE[2]);
				$pdf->setXY(20, 146);
				$pdf->MultiCell(110, 4, $deta[0],0,'L',false);
				$pdf->setXY(135, 146);
				$pdf->MultiCell(110, 4, $deta[1],0,'L',false);
			}
			if(isset($allAE[3])){
				$deta = explode('++||++',$allAE[3]);
				$pdf->setXY(20, 166);
				$pdf->MultiCell(110, 4, $deta[0],0,'L',false);
				$pdf->setXY(135, 166);
				$pdf->MultiCell(110, 4, $deta[1],0,'L',false);
			}
			
			$support = explode('--^_^--',$row->coachedSupport);
			if(isset($support[0])){
				$pdf->setXY(248, 108);
				$pdf->MultiCell(100, 4, $support[0],0,'L',false);
			}
			if(isset($support[1])){
				$pdf->setXY(248, 127);
				$pdf->MultiCell(100, 4, $support[1],0,'L',false);
			}
			if(isset($support[2])){
				$pdf->setXY(248, 146);
				$pdf->MultiCell(100, 4, $support[2],0,'L',false);
			}
			if(isset($support[3])){
				$pdf->setXY(248, 166);
				$pdf->MultiCell(100, 4, $support[3],0,'L',false);
			}
		
			//page2
			$pdf->AddPage();
			$tplIdx = $pdf->importPage(2);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);	

			//acknowledgements
			$pdf->SetFont('Arial','B',10);
			$pdf->setXY(17, 61);
			$pdf->MultiCell(100, 4, strtoupper($row->supName),0,'C',false); //immediate supervisor
			$pdf->setXY(137, 63); $pdf->Write(0, date('Y-m-d', strtotime($row->dateGenerated)));
			
			$pdf->setXY(17, 80);
			$pdf->MultiCell(100, 4, strtoupper($row->sup2ndName),0,'C',false); //second level supervisor
			$pdf->setXY(137, 82); $pdf->Write(0, date('Y-m-d', strtotime($row->dateGenerated)));
			
			$pdf->setXY(180, 71);
			$pdf->MultiCell(100, 4, strtoupper($row->name),0,'C',false); //employee's name
			$pdf->setXY(312, 73); $pdf->Write(0, date('Y-m-d', strtotime($row->dateGenerated)));
		}
		
		if($type=='evaluation'){
			if(isset($allAE[0])){
				$deta = explode('++||++',$allAE[0]);
				$pdf->setXY(23, 136);
				$pdf->MultiCell(125, 4, $deta[0],0,'L',false);
				$pdf->setXY(150, 136);
				$pdf->MultiCell(120, 4, $deta[1],0,'L',false);	
			}
			if(isset($allAE[1])){
				$deta = explode('++||++',$allAE[1]);
				$pdf->setXY(23, 155);
				$pdf->MultiCell(125, 4, $deta[0],0,'L',false);
				$pdf->setXY(150, 155);
				$pdf->MultiCell(120, 4, $deta[1],0,'L',false);	
			}
			if(isset($allAE[2])){
				$deta = explode('++||++',$allAE[2]);
				$pdf->setXY(23, 174);
				$pdf->MultiCell(125, 4, $deta[0],0,'L',false);
				$pdf->setXY(150, 174);
				$pdf->MultiCell(120, 4, $deta[1],0,'L',false);	
			}
			//ratings
			$eRating = explode('|', $row->selfRating);
			if(isset($eRating[0])){
				$pdf->setXY(288, 145);
				$pdf->Write(0, $eRating[0]);
			}
			if(isset($eRating[1])){
				$pdf->setXY(288, 165);
				$pdf->Write(0, $eRating[1]);
			}
			if(isset($eRating[2])){
				$pdf->setXY(288, 182);
				$pdf->Write(0, $eRating[2]);
			}
			
			$sRating = explode('|', $row->supervisorsRating);
			if(isset($sRating[0])){
				$pdf->setXY(330, 145);
				$pdf->Write(0, $sRating[0]);
			}
			if(isset($sRating[1])){
				$pdf->setXY(330, 165);
				$pdf->Write(0, $sRating[1]);
			}
			if(isset($sRating[2])){
				$pdf->setXY(330, 182);
				$pdf->Write(0, $sRating[2]);
			}
			
			//page2
			$pdf->AddPage();
			$tplIdx = $pdf->importPage(4);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			
			if(isset($allAE[3])){
				$deta = explode('++||++',$allAE[3]);
				$pdf->setXY(23, 44);
				$pdf->MultiCell(125, 4, $deta[0],0,'L',false);
				$pdf->setXY(150, 44);
				$pdf->MultiCell(120, 4, $deta[1],0,'L',false);	
			}
			if(isset($eRating[3])){
				$pdf->setXY(288, 51);
				$pdf->Write(0, $eRating[3]);
			}
			if(isset($sRating[3])){
				$pdf->setXY(330, 51);
				$pdf->Write(0, $sRating[3]);
			}
			
			$erate = 0;
			$srate = 0;
			foreach($eRating AS $e):
				$erate += $e;
			endforeach;	
			
			foreach($sRating AS $s):
				$srate += $s;
			endforeach;
			
			$eRateAve = $erate/count($allAE);
			$sRateAve = $srate/count($allAE);
			
			if($row->selfRating!='' && $row->supervisorsRating!=''){
				//averages
				$pdf->setXY(288, 65);
				$pdf->Write(0, number_format($eRateAve, 2));
				$pdf->setXY(330, 65);
				$pdf->Write(0, number_format($sRateAve, 2));
				//weighed average
				$pdf->setXY(288, 72);
				$pdf->Write(0, number_format(($eRateAve*0.20), 2));
				$pdf->setXY(330, 72);
				$pdf->Write(0, number_format(($sRateAve*0.80), 2));
				//total weighed average
				$fscore = number_format((($eRateAve*0.20) + ($sRateAve*0.80)), 2);
				$pdf->setXY(227, 100);
				$pdf->Write(0, $fscore);
				
				$pdf->setXY(203, 110);
				$pdf->Write(0, $this->staffM->coachingScore($fscore));
				
				/* //recommendation
				$recArr = $this->config->item('coachingrecommendations');
				$pdf->setXY(290, 103);
				$pdf->Write(0, $recArr[$row->recommendation]);
				
				if($row->effectiveDate!='0000-00-00'){
					if($row->recommendation==4 || $row->recommendation==5){
						$pdf->setXY(290, 120);
						$pdf->Write(0, date('F d, Y', strtotime($row->effectiveDate)));
					}else{
						$pdf->setXY(290, 110);
						$pdf->Write(0, date('F d, Y', strtotime($row->effectiveDate)));
					}					
				} */				
			}
			
			//acknowledgements
			$pdf->SetFont('Arial','B',10);
			$pdf->setXY(15, 153);
			$pdf->MultiCell(100, 4, strtoupper($row->supName),0,'C',false); //immediate supervisor
			$pdf->setXY(133, 155); $pdf->Write(0, (($row->dateEvaluated=='0000-00-00')?date('Y-m-d'):date('Y-m-d', strtotime($row->dateEvaluated))));
			
			$pdf->setXY(15, 172.5);
			$pdf->MultiCell(100, 4, strtoupper($row->sup2ndName),0,'C',false); //second level supervisor
			$pdf->setXY(133, 174.5); $pdf->Write(0, (($row->dateEvaluated=='0000-00-00')?date('Y-m-d'):date('Y-m-d', strtotime($row->dateEvaluated))));
			
			$pdf->setXY(175, 163);
			$pdf->MultiCell(100, 4, strtoupper($row->name),0,'C',false); //employee's name
			$pdf->setXY(308, 165); $pdf->Write(0, (($row->dateEvaluated=='0000-00-00')?date('Y-m-d'):date('Y-m-d', strtotime($row->dateEvaluated))));
		}	
		
		
		$pdf->Output('coaching.pdf', 'I');
	}

	
	public function coachingScore($score){
		$scoretext = '';
		if($score>=8.00 && $score<=10.00)
			$scoretext = 'Excellent (Exceeds expectations)';
		else if($score>=5.00 && $score<=7.99)
			$scoretext = 'Good (Meets expectations)';
		else if($score>=3.00 && $score<=4.99)
			$scoretext = 'Fair (Improvement needed)';
		else if($score<=2.99)
			$scoretext = 'Poor (Unsatisfactory)';
		
		return $scoretext;
	}
	
	public function coachingStatus($id){
		$stat = '';	
		$row = $this->staffM->getSingleInfo('staffCoaching', 'coachID, coachedBy, empID_fk, coachedDate, coachedEval, selfRating, supervisorsRating, status, finalRating, fname AS name', 'coachID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk');
	
		if($row->status==0){
			$today = date('Y-m-d');
			if($today<$row->coachedEval)	
				$stat = 'Coaching Period in Progress';
			else{
				if($row->selfRating==''){
					$stat = 'Evaluation Due.';
					if($this->user->empID==$row->empID_fk) $stat .= ' Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$row->coachID.'/" class="iframe">here</a> to Evaluate.';
					else $stat .= ' Notification Sent to employee for Self-Rating.';
				}else if($row->selfRating!='' && $row->supervisorsRating==''){
					$stat = 'Evaluation Due.';
					if($this->user->empID==$row->empID_fk || $this->user->empID!=$row->coachedBy) $stat .= ' Self-Rating submitted. Pending coach evaluation.';
					else $stat .= ' Self-Rating submitted. Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$row->coachID.'/" class="iframe">here</a> to evaluate.'; 
				}
			}
		}else if($row->status==1){
			$stat = $this->staffM->coachingScore($row->finalRating);
		}else if($row->status==2){
			$stat = 'Feedback Session in Progress';
			if($this->user->empID==$row->coachedBy)
				$stat .= '. Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$row->coachID.'/" class="iframe">here</a> to finalize evaluation.';
		}else if($row->status==3){
			$stat = 'Coach ratings locked in.';
			if($row->empID_fk==$this->user->empID)
				$stat .= ' Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$row->coachID.'/" class="iframe">here</a> to Acknowledge.';
			else
				$stat .= ' Click <a class="iframe" href="'.$this->config->base_url().'sendEmail/'.$row->empID_fk.'/acknowledgecoaching/'.$row->coachID.'/">here</a> to send message to '.$row->name.' to input coaching score.';
		}else if($row->status==4){
			$stat = 'CANCELLED';
		}
		return $stat;	
	}	
	
}

?>