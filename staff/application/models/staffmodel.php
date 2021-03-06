<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staffmodel extends CI_Model {
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();		
		$this->load->model('timecardmodel', 'timeM');
    }
			
	function compareResults($new, $orig){
		unset($new['empID']);
		unset($new['submitType']);
		$encArr = $this->textM->constantArr('encText');
		$updated = array();
		if(count($orig)>0){			
			foreach($new AS $k=>$v):
				if($k=='bdate' || $k=='startDate' || $k=='endDate' || $k=='accessEndDate' || $k=='regDate' || $k=='floatStartDate'){
					if($v=='') $v = '0000-00-00';
					else $v = date('Y-m-d', strtotime($v));
				}else if(in_array($k, $encArr)){
					if($k=='sal') $v = str_replace(',','', str_replace('.00','', $v));
					
					$v = $this->textM->encryptText($v);
				}
				
				if($v != $orig[$k])
					$updated[$k] = $v;
			endforeach;			
		}
				
		return $updated;
	}
		
	function createLeavepdf($leave){
		$leaveArr = $this->textM->constantArr('leaveType');
			
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
			$sup = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$leave->approverID.'"');
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
			$hr = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$leave->hrapprover.'"');						
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
			$sup = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$leave->approverID.'"');
			
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
			$hr = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, username', 'empID="'.$leave->hrapprover.'"');
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

		$changes = json_decode($cis->changes);
				
		$pdf = new FPDI();
		$pdf->AddPage();

		$isChangeSup = FALSE;
		$yy = 0;

		if(isset($changes->supervisor)){
			$isChangeSup = TRUE;
			$yy = -20;
			$pdf->setSourceFile(PDFTEMPLATES_DIR.'CIS_IS_Form.pdf');
		}
		else
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

		$pdf->SetFont('Arial','I',10);	
		
		$nextY = 85;
		if(isset($changes->staffHolidaySched)){
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Change in Holiday Schedule",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4, $changes->staffHolidaySched->c,0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			$pdf->setXY(133, $y);
			$pdf->MultiCell(60, 4, $changes->staffHolidaySched->n,0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();			
		}

		if(isset($changes->position)){
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Change in Position Title",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4, $changes->position->c,0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			$pdf->setXY(133, $y);
			$pdf->MultiCell(60, 4, $changes->position->n,0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();			
		}	
						
		if(isset($changes->office)){
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Change in Office Branch",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4,(($changes->office->c!='')?strtoupper($changes->office->c):'None'),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			$pdf->setXY(133, $y);
			$pdf->MultiCell(60, 4, strtoupper($changes->office->n),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();	
		}	
		if(isset($changes->shift)){
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Change in Shift Schedule",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4,(($changes->shift->c!='')?$changes->shift->c:'None'),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			$pdf->setXY(133, $y);
			$pdf->MultiCell(60, 4, $changes->shift->n,0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
		}
				
		if($isChangeSup){
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Change in Immediate Supervisor",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4,$changes->supervisor->c,0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			$pdf->setXY(133, $y);
			$pdf->MultiCell(60, 4, $changes->supervisor->n,0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
		}
		
		if(isset($changes->salary)){
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Change in Basic Salary",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4,'Php '.$this->textM->convertNumFormat($changes->salary->c),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			$pdf->setXY(133, $y);
			$pdf->MultiCell(60, 4, 'Php '.$this->textM->convertNumFormat($changes->salary->n),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			
			$pdf->SetFont('Arial','I',9);	
			$pdf->setXY(25, $nextY);
			$pdf->MultiCell(170, 4, "Justification for salary adjustment:\n".$changes->salary->com,0,'L',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
		}		
		
		$pdf->SetFont('Arial','I',10);	
		if(isset($changes->separationDate)){
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Change in Separation Date",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			if($changes->separationDate->c=='0000-00-00') $pdf->MultiCell(60, 4,'N/A - Active employee',0,'C',false); 
			else $pdf->MultiCell(60, 4,$changes->separationDate->c,0,'C',false); 
			$pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			$pdf->setXY(133, $y);
			$pdf->MultiCell(60, 4, $changes->separationDate->n,0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
		}
		
		if(isset($changes->empStatus)){
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Change in Employment Status",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4,ucfirst($changes->empStatus->c),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			$pdf->setXY(133, $y);
			$pdf->MultiCell(60, 4, ucfirst($changes->empStatus->n),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Date when evaluation was conducted:",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4,date('F d, Y', strtotime($changes->empStatus->evalDate)),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
			
			
			$y = $nextY;
			$pdf->setXY(18, $y);
			$pdf->MultiCell(55, 4, "Effective date of regularization:",0,'L',false); $pdf->Ln();
			
			$pdf->setXY(73, $y);
			$pdf->MultiCell(60, 4,date('F d, Y', strtotime($changes->empStatus->regDate)),0,'C',false); $pdf->Ln();
			if($nextY<$pdf->getY()) $nextY = $pdf->getY();
		}
		
		$pdf->setXY(18, $nextY);
		$pdf->MultiCell(170, 4, '------------------------------------- NOTHING FOLLOWS ------------------------------------',0,'C',false);
		
		
		$pdf->SetFont('Arial','B',11);
		$pdf->setXY(20, 225+$yy);
		$pdf->MultiCell(50, 4, strtoupper($isupname),0,'C',false); //immediate supervisor
		
		$pdf->setXY(80, 225+$yy);
		$pdf->MultiCell(50, 4, strtoupper($nsupname),0,'C',false); //second level manager
			
		$pdf->setXY(128, 225+$yy);
		$pdf->MultiCell(70, 4, strtoupper($this->user->name),0,'C',false); //Reviewed by
	
		$pdf->setXY(45, 258+$yy);	
		$pdf->MultiCell(120, 4, strtoupper($cis->name) ,0,'C',false); //name of employee
		if( $isChangeSup ){
			$pdf->setXY(45, 266);	
			$pdf->MultiCell(120, 4, strtoupper($changes->supervisor->n) ,0,'C',false); //name of new immediate supervisor
		}
			
		
		$pdf->Output('CIS_'.str_replace(' ','_',$cis->name).'.pdf', $t);
	}
	
	function getHRStaffID(){
		$hrArr = array();
		$query = $this->dbmodel->getQueryArrayResults('staffs', 'empID', 'access LIKE "%hr%"');
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
	
	function genCOEpdf($row){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'coe_standard.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','B',14);
		$pdf->setXY(85, 92);
		$pdf->Write(0, utf8_decode($row->name));
		$pdf->setXY(85, 101);
		$pdf->Write(0, date('F d, Y',strtotime($row->startDate)));
		$pdf->setXY(85, 110);
		$pdf->Write(0, $row->title);
		
		$sal = (double)str_replace(',','',$this->textM->decryptText($row->salary));
		$allowance = (double)str_replace(',','',$row->allowance);
		
		$pdf->setXY(115, 127);
		$pdf->Write(0, $this->textM->convertNumFormat(($sal*12)));
		$pdf->setXY(117, 137);
		$pdf->Write(0, $this->textM->convertNumFormat(($allowance*12)) );
		$pdf->setXY(115, 155);
		$pdf->Write(0, $this->textM->convertNumFormat((($sal*12)+($allowance*12))));
		
		$pdf->SetFont('Arial','B',12);
		$pdf->setXY(15, 177);
		$pdf->MultiCell(0, 15, ((!empty($row->purposeEdited))?$row->purposeEdited:$row->purpose),0,'C',false);	
		
		$pdf->setXY(87, 205);
		$pdf->Write(0, date('F d, Y',strtotime($row->dateissued)));
		
		$pdf->SetFont('Arial','',10);
		$pdf->setXY(141, 127);
		$pdf->Write(0, '(Excluding 13th month pay)');

		$pdf->SetFont('Arial','B',12);

		$pdf->setXY(15, 237);
		$pdf->Cell(0, 8, $this->user->name, 0, 0, 'C');
		
		$pdf->Output('coe_form'.$row->coeID.'.pdf', 'I');
	}
	
	function genCOEpdfLast($row){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
		
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'coe_separated_employee.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','B',14);
		$pdf->setXY(95, 91);
		$pdf->Write(0, utf8_decode($row->name));
		$pdf->setXY(95, 100);
		$pdf->Write(0, $row->title);
		$pdf->setXY(95, 109);
		$pdf->Write(0, date('F d, Y',strtotime($row->startDate)));
		$pdf->setXY(95, 119);
		$pdf->Write(0, date('F d, Y',strtotime($row->endDate)));
		
		
		$pdf->setXY(87, 163);
		$pdf->Write(0, date('F d, Y'));

		$pdf->setXY(10, 195);
		$pdf->Cell(0, 8, $this->user->name, 0, 1, 'C');
		
		
		
		$pdf->Output('coe_form'.$row->coeID.'.pdf', 'I');
	}
	
	function getEmailTemplate($id, $empID){
		$template = $this->dbmodel->getSingleField('staffCustomEmails', 'emailTemplate', 'emailID="'.$id.'"');
		$staff = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) AS name, fname, lname', 'emailID="'.$empID.'"');
		
		$template = str_replace('_NAME', $staff->name, $template);
		$template = str_replace('_FNAME', $staff->fname, $template);
		$template = str_replace('_LNAME', $staff->lname, $template);
		return $template;
	}
	
	function displayInfo($c, $fld, $v, $t=false, $placeholder='', $showhide=''){
		$vvalue = $v;
		if($fld=='pemail' || $fld=='email')
			$vvalue = '<a href="mailto:'.$v.'">'.$v.'</a>';
		$aclass='';
		if(in_array($fld,array('bdate', 'startDate', 'endDate', 'accessEndDate', 'regDate', 'floatStartDate'))) $aclass = 'datepick';
		
		$disp = '<tr class="'.$c.'tr '.$showhide.'">
					<td width="30%">'.$this->textM->constantText('txt_'.$fld).'</td>
					<td class="td'.$fld.'">';
				if($t==true){	
					if(in_array($fld,array('gender', 'maritalStatus', 'supervisor', 'title', 'empStatus', 'active', 'office', 'staffHolidaySched', 'levelID_fk', 'terminationType', 'taxstatus', 'shiftSched'))){
						$disp .= '<select id="'.$fld.'" class="forminput '.$c.'input hidden '.$aclass.'">';
						$disp .= '<option value=""></option>';
						if($fld=='supervisor'){
							$aRR = $this->dbmodel->getQueryResults('staffs', 'empID AS id, CONCAT(fname," ",lname) AS val, active', 'levelID_fk>0', '', 'fname ASC');
						}else if($fld=='title'){
							$aRR = $this->dbmodel->getQueryResults('newPositions', 'posID AS id, title AS val, org, dept, grp, subgrp, active', '1', '', 'title ASC');
						}else if($fld=='levelID_fk'){
							$aRR = $this->dbmodel->getQueryResults('orgLevel', 'levelID AS id, levelName AS val, 1 AS active', '1');
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
							$arr = $this->textM->constantArr($fld);							
							foreach($arr AS $k=>$va):
								if($k==$v) $vvalue=$va;
								$disp .= '<option value="'.$k.'" '.(($k==$v) ? 'selected="selected"' : '').'>'.$va.'</option>';
							endforeach;
						}						
						$disp .= '</select>';
					}else{
						$disp .= '<input type="text" class="forminput '.$c.'input hidden '.$aclass.'" placeholder="'.$placeholder.'" value="'.$this->textM->convertDecryptedText($fld, $v, 1).'" id="'.$fld.'">';
					}	
					$disp .= '<span class="'.$c.'fld">';						
						$disp .= $this->textM->convertDecryptedText($fld, $vvalue);
					$disp .= '</span>';
				}else{
					if($fld=='staffHolidaySched'){
						$farr = $this->textM->constantArr($fld);
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
		$noteType = $this->textM->constantArr('noteType');
		$myNotes = $this->dbmodel->getQueryResults('staffMyNotif', 'staffMyNotif.*, username, CONCAT(fname," ",lname) AS name', 'empID_fk="'.$empID.'"','LEFT JOIN staffs ON empID=sID', 'dateissued DESC');
		$ptNotes = $this->dbmodel->getPTQueryResults('eNotes', 'eNotes.*, eData.u, "" AS userSID', 'u="'.$username.'"', 'LEFT JOIN eData ON eKey=eNoteOwner', 'eNoteStamp DESC');
		
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
                'exec' => $m->userSID,
                'ntype' => $m->ntype
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
						
		foreach ($notesArr AS $key => $row) {
			$volume[$key]  = $row['timestamp'];
		}

		if(!empty($notesArr) && !empty($volume))
			array_multisort($volume, SORT_DESC, $notesArr);
		
		return $notesArr;		
	}  
		
	function infoTextVal($type, $tval){
		$was = '';
		
		if($type=='title') 
			$was = $this->dbmodel->getSingleField('newPositions', 'title', 'posID="'.$tval.'"');
		else if($type=='supervisor')
			$was = $this->dbmodel->getSingleField('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$tval.'"');
		else if($type=='levelID_fk')
			$was = $this->dbmodel->getSingleField('orgLevel', 'levelName AS name', 'levelID="'.$tval.'"');
		else if($type=='terminationType' || ( $type=='taxstatus' && !empty($tval))){
			$tarr = $this->textM->constantArr($type); 
			$was = $tarr[$tval];
		}else if($type=='staffHolidaySched'){
			$schedLoc = $this->textM->constantArr('staffHolidaySched');
			$was = $schedLoc[$tval];
		}else if($type=='shiftSched'){
			$shft = $this->textM->constantArr('shiftSched');
			$was = $shft[$tval];
		}else 
			$was = $this->textM->convertDecryptedText($type, $tval);
			
		if($was=='')
			$was = '<i>none</i>';
			
		return $was;		
	}
	
	function createCoachingPDF($row, $type){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'CoachingForm2016.pdf');
			
		if($type=='evaluation') $tplIdx = $pdf->importPage(3);
		else $tplIdx = $pdf->importPage(1);
			
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		$pdf->SetAutoPageBreak('auto', .5);
				
		$pdf->SetFont('Arial','',8.5);
		$pdf->SetTextColor(0, 0, 0);

		$column_first_x = 56;
		$column_second_x = 162;
		$column_third_x = 250;
		
		$pdf->setXY($column_first_x, 45.5);
		$pdf->Write(0, $row->name);
		$pdf->setXY($column_first_x, 50);
		$pdf->Write(0, $row->title);
		$pdf->setXY($column_first_x, 54.5);
		$pdf->Write(0, $row->dept);
		$pdf->setXY($column_first_x, 58.5);
		$pdf->Write(0, $row->reviewer);

		if( $type == 'expectation'){
			$pdf->setXY($column_first_x + 8, 63.5);
			$pdf->Write(0, $row->coachedImprovement);	
		}
		
		
		if(isset($row->supName)){
			$pdf->setXY($column_second_x, 45.5);
			$pdf->Write(0, $row->supName);
			$pdf->setXY($column_second_x, 50);
			$pdf->Write(0, $row->supTitle);
		}
		if(isset($row->sup2ndName)){
			$pdf->setXY($column_second_x, 54.5);
			$pdf->Write(0, $row->sup2ndName);
			$pdf->setXY($column_second_x, 58.5);
			$pdf->Write(0, $row->sup2ndTitle);
		}
		
		$pdf->setXY($column_third_x, 45.5);
		$pdf->Write(0, date('F d, Y',strtotime($row->coachedDate)));
		$pdf->setXY($column_third_x, 50);
		$pdf->Write(0, date('F d, Y',strtotime($row->coachedEval)));

		$width = 89;
		$width_3 = 82;
		$first_row = 96;
		$second_row = 117;
		$third_row = 138;
		$fourth_row = 160;
		$second_col = 108;
		$third_col = 197;
		$line_height = 3.5;
		$pdf->SetFont('Arial','',8);
		$allAE = explode('--^_^--',$row->coachedAspectExpected);						
		if($type=='expectation'){
			if(isset($allAE[0])){
				$deta = explode('++||++',$allAE[0]);
				$pdf->setXY(20, $first_row + 4);
				$pdf->MultiCell($width, 4, $deta[0],0,'L',false);
				$pdf->setXY($second_col, $first_row);
				$pdf->SetFont('Arial','',8);
				$pdf->MultiCell($width, $line_height, $deta[1],0,'L',false);	
			}
			if(isset($allAE[1])){
				$deta = explode('++||++',$allAE[1]);
				$pdf->setXY(20, $second_row + 4);
				$pdf->MultiCell($width, 4, $deta[0],0,'L',false);
				$pdf->setXY($second_col, $second_row);
				$pdf->MultiCell($width, $line_height, $deta[1],0,'L',false);
			}
			if(isset($allAE[2])){
				$deta = explode('++||++',$allAE[2]);
				$pdf->setXY(20, $third_row + 4);
				$pdf->MultiCell($width, 4, $deta[0],0,'L',false);
				$pdf->setXY($second_col, $third_row);
				$pdf->MultiCell($width, $line_height, $deta[1],0,'L',false);
			}
			if(isset($allAE[3])){
				$deta = explode('++||++',$allAE[3]);
				$pdf->setXY(20, $fourth_row + 4);
				$pdf->MultiCell($width, 4, $deta[0],0,'L',false);
				$pdf->setXY($second_col, $fourth_row);
				$pdf->MultiCell($width, $line_height, $deta[1],0,'L',false);
			}
			
			$support = explode('--^_^--',$row->coachedSupport);
			if(isset($support[0])){
				$pdf->setXY($third_col, $first_row);
				$pdf->MultiCell($width_3, $line_height, $support[0],0,'L',false);
			}
			if(isset($support[1])){
				$pdf->setXY($third_col, $second_row);
				$pdf->MultiCell($width_3, $line_height, $support[1],0,'L',false);
			}
			if(isset($support[2])){
				$pdf->setXY($third_col, $third_row);
				$pdf->MultiCell($width_3, $line_height, $support[2],0,'L',false);
			}
			if(isset($support[3])){
				$pdf->setXY($third_col, $fourth_row);
				$pdf->MultiCell($width_3, $line_height, $support[3],0,'L',false);
			}
		
			//page2
			$pdf->AddPage();
			$tplIdx = $pdf->importPage(2);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);	


			//acknowledgements
			$pdf->SetFont('Arial','B',10);
			$pdf->setXY(15, 88);
			$pdf->MultiCell(75, 4, strtoupper($row->supName),0,'C',false); //immediate supervisor
			$pdf->setXY(105, 88); $pdf->Write(0, date('Y-m-d', strtotime($row->dateGenerated)));
			
			$pdf->setXY(15, 118);
			$pdf->MultiCell(75, 4, strtoupper($row->sup2ndName),0,'C',false); //second level supervisor
			$pdf->setXY(105, 118); $pdf->Write(0, date('Y-m-d', strtotime($row->dateGenerated)));
			
			$pdf->setXY(150, 118);
			$pdf->MultiCell(75, 4, strtoupper($row->name),0,'C',false); //employee's name
			$pdf->setXY(245, 118); $pdf->Write(0, date('Y-m-d', strtotime($row->dateGenerated)));
		}
		
		if($type=='evaluation'){

			$first_row = 90;
			$second_row = 112;
			$third_row = 134;
			$fourth_row = 154;
			$width = 69;
			$second_col = 91;
			$fourth_col = 238;
			$fifth_col = 263;
			$third_col = 161;

			if(isset($allAE[0])){
				$deta = explode('++||++',$allAE[0]);
				$pdf->setXY(23, $first_row + 6);
				$pdf->MultiCell($width, $line_height, $deta[0],0,'L',false);
				$pdf->setXY($second_col, $first_row);
				$pdf->MultiCell($width, $line_height, $deta[1],0,'L',false);	
			}
			if(isset($allAE[1])){
				$deta = explode('++||++',$allAE[1]);
				$pdf->setXY(23, $second_row + 6 );
				$pdf->MultiCell($width, $line_height, $deta[0],0,'L',false);
				$pdf->setXY($second_col, $second_row );
				$pdf->MultiCell($width, $line_height, $deta[1],0,'L',false);	
			}
			if(isset($allAE[2])){
				$deta = explode('++||++',$allAE[2]);
				$pdf->setXY(23, $third_row + 6);
				$pdf->MultiCell($width, $line_height, $deta[0],0,'L',false);
				$pdf->setXY($second_col, $third_row );
				$pdf->MultiCell($width, $line_height, $deta[1],0,'L',false);	
			}
			if(isset($allAE[3])){
				$deta = explode('++||++',$allAE[3]);
				$pdf->setXY(23, $fourth_row + 6);
				$pdf->MultiCell($width, $line_height, $deta[0],0,'L',false);
				$pdf->setXY($second_col, $fourth_row);
				$pdf->MultiCell($width, $line_height, $deta[1],0,'L',false);	
			}

			$support = explode('--^_^--',$row->coachedSupport);
			if(isset($support[0])){
				$pdf->setXY($third_col, $first_row);
				$pdf->MultiCell($width, $line_height, $support[0],0,'L',false);
			}
			if(isset($support[1])){
				$pdf->setXY($third_col, $second_row);
				$pdf->MultiCell($width, $line_height, $support[1],0,'L',false);
			}
			if(isset($support[2])){
				$pdf->setXY($third_col, $third_row);
				$pdf->MultiCell($width, $line_height, $support[2],0,'L',false);
			}
			if(isset($support[3])){
				$pdf->setXY($third_col, $fourth_row);
				$pdf->MultiCell($width, $line_height, $support[3],0,'L',false);
			}



			//ratings
			$pdf->SetFont('Arial','B',10);
			$eRating = explode('|', $row->selfRating);
			if(isset($eRating[0])){
				$pdf->setXY($fourth_col, $first_row + 6);
				$pdf->Write(0, $eRating[0]);
			}
			if(isset($eRating[1])){
				$pdf->setXY($fourth_col, $second_row + 6);
				$pdf->Write(0, $eRating[1]);
			}
			if(isset($eRating[2])){
				$pdf->setXY($fourth_col, $third_row + 6);
				$pdf->Write(0, $eRating[2]);
			}
			if(isset($eRating[3])){
				$pdf->setXY($fourth_col, $fourth_row + 6);
				$pdf->Write(0, $eRating[3]);
			}
			
			$sRating = explode('|', $row->supervisorsRating);
			if(isset($sRating[0])){
				$pdf->setXY($fifth_col, $first_row + 6);
				$pdf->Write(0, $sRating[0]);
			}
			if(isset($sRating[1])){
				$pdf->setXY($fifth_col, $second_row + 6);
				$pdf->Write(0, $sRating[1]);
			}
			if(isset($sRating[2])){
				$pdf->setXY($fifth_col, $fourth_row + 6);
				$pdf->Write(0, $sRating[2]);
			}
			if(isset($sRating[3])){
				$pdf->setXY($fifth_col, $third_row + 6);
				$pdf->Write(0, $sRating[3]);
			}
			
			$erate = 0;
			$srate = 0;
			$rnum = 0;
			foreach($eRating AS $e):
				$erate += $e;
				$rnum++;
			endforeach;	
			
			foreach($sRating AS $s):
				$srate += $s;
			endforeach;
			
			$eRateAve = $erate/$rnum;
			$sRateAve = $srate/$rnum;
			$fscore = 0;
			if($row->selfRating!='' && $row->supervisorsRating!=''){
				//averages
				$pdf->setXY($fourth_col, 177);
				$pdf->Write(0, number_format($eRateAve, 2));
				$pdf->setXY($fifth_col, 177);
				$pdf->Write(0, number_format($sRateAve, 2));
				//weighed average
				$pdf->setXY($fourth_col, 183);
				$pdf->Write(0, number_format(($eRateAve*0.20), 2));
				$pdf->setXY($fifth_col, 183);
				$pdf->Write(0, number_format(($sRateAve*0.80), 2));
				//total weighed average
				$fscore = number_format((($eRateAve*0.20) + ($sRateAve*0.80)), 2);
				$pdf->setXY($fourth_col + 8, 188);
				$pdf->Write(0, $fscore);
				
				$pdf->setXY($fourth_col - 6, 191.5);
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
			
			//page2
			$pdf->AddPage();
			$tplIdx = $pdf->importPage(4);
			$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
			
			$pdf->setXY(183, 60);
			$pdf->Write(0, $fscore);

			if(!empty($row->supervisorsRatingNotes)){
					$pdf->SetFont('Arial','',8);
					$pdf->setXY(143, 85);
					$pdf->MultiCell(55, 4, $row->supervisorsRatingNotes,0,'L',false);	
				}

			//acknowledgements
			$pdf->SetFont('Arial','B',10);
			$pdf->setXY(15, 135);
			$pdf->MultiCell(75, 4, strtoupper($row->supName),0,'C',false); //immediate supervisor
			$pdf->setXY(103, 136); $pdf->Write(0, (($row->dateEvaluated=='0000-00-00')?date('Y-m-d'):date('Y-m-d', strtotime($row->dateEvaluated))));
			
			$pdf->setXY(15, 164);
			$pdf->MultiCell(75, 4, strtoupper($row->sup2ndName),0,'C',false); //second level supervisor
			$pdf->setXY(103, 165); $pdf->Write(0, (($row->dateEvaluated=='0000-00-00')?date('Y-m-d'):date('Y-m-d', strtotime($row->dateEvaluated))));
			
			$pdf->setXY(145, 164);
			$pdf->MultiCell(80, 4, strtoupper($row->name),0,'C',false); //employee's name
			$pdf->setXY(245, 165); $pdf->Write(0, (($row->dateEvaluated=='0000-00-00')?date('Y-m-d'):date('Y-m-d', strtotime($row->dateEvaluated))));
		}	
		
		
		$pdf->Output('coaching.pdf', 'I');
	}
	
	public function coachingScore($score){
		$scoretext = '';
		// if($score>=s.00 && $score<=10.00)
		// 	$scoretext = 'Excellent (Exceeds expectations)';
		// else if($score>=5.00 && $score<=7.99)
		// 	$scoretext = 'Good (Meets expectations)';
		// else if($score>=3.00 && $score<=4.99)
		// 	$scoretext = 'Fair (Improvement needed)';
		// else if($score<=2.99)
		// 	$scoretext = 'Poor (Unsatisfactory)';
		if( $score >= 1.5 ){
			$scoretext = 'Exceeds Expectations';
		} else if( $score >= 1.00 ){
			$scoretext = 'Met Expectations';
		} else if( $score < 1.00 ){
			$scoretext = 'Did Not Meet Expectations';
		}
		
		return $scoretext;
	}
	
	/*** $row is an array of of
		-coachedBy, empID_fk, coachedDate, coachedEval, selfRating, supervisorsRating, status, finalRating, name, supervisor
	***/
	public function coachingStatus($id, $row=''){		
		$stat = '';			
		if(!isset($row->coachedBy) || !isset($row->empID_fk) || !isset($row->coachedDate) || !isset($row->coachedEval) || !isset($row->selfRating) || !isset($row->supervisorsRating) || !isset($row->status) || !isset($row->finalRating) || !isset($row->name) ){
			$row = $this->dbmodel->getSingleInfo('staffCoaching', 'coachedBy, empID_fk, coachedDate, coachedEval, selfRating, supervisorsRating, status, finalRating, fname AS name, supervisor', 'coachID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk');
		}
	
		if($row->status==0){
			$today = date('Y-m-d');
			if($today<$row->coachedEval)	
				$stat = 'Coaching Period in Progress';
			else{
				if($row->selfRating==''){
					$stat = 'Evaluation Due.';
					if($this->user->empID==$row->empID_fk) $stat .= ' Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$id.'/" class="iframe">here</a> to Evaluate.';
					else $stat .= ' Notification Sent to employee for Self-Rating.';
				}else if($row->selfRating!='' && $row->supervisorsRating==''){
					$stat = 'Evaluation Due.';
					if($this->user->empID==$row->empID_fk || ($this->user->empID!=$row->coachedBy && $this->user->empID!=$row->supervisor)) 
						$stat .= ' Self-Rating submitted. Pending coach evaluation.';
					else 
						$stat .= ' Self-Rating submitted. Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$id.'/" class="iframe">here</a> to evaluate.'; 
				}
			}
		}else if($row->status==1){
			$stat = $this->staffM->coachingScore($row->finalRating);
		}else if($row->status==2){
			$stat = 'Feedback Session in Progress';
			if($this->user->empID==$row->coachedBy)
				$stat .= '. Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$id.'/" class="iframe">here</a> to finalize evaluation.';
		}else if($row->status==3){
			$stat = 'Coach ratings locked in.';
			if($row->empID_fk==$this->user->empID)
				$stat .= ' Click <a href="'.$this->config->base_url().'coachingEvaluation/'.$id.'/" class="iframe">here</a> to Acknowledge.';
			else
				$stat .= ' Click <a class="iframe" href="'.$this->config->base_url().'sendEmail/'.$row->empID_fk.'/acknowledgecoaching/'.$id.'/">here</a> to send message to '.$row->name.' to input coaching score.';
		}else if($row->status==4){
			$stat = 'CANCELLED';
		}
		return $stat;	
	}	
	
	public function evaluationpdf($eval, $row){
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'performance_review_form.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','B',9);
		$pdf->SetTextColor(0, 0, 0);		
		
		$pdf->setXY(32, 47.5);
		$pdf->Write(0, $row->name); //Employee Name	
		$pdf->setXY(27, 51);
		$pdf->Write(0, $row->title); //Position title	
		$pdf->setXY(25, 54.5);
		$pdf->Write(0, $row->dept); //department	
		$pdf->setXY(23, 58);
		$pdf->Write(0, $eval->reviewer); //reviewer
		
		if(isset($row->firstsup->name)){
			$pdf->setXY(125, 47.5);
			$pdf->Write(0, $row->firstsup->name); //is name
			$pdf->setXY(145, 51);
			$pdf->Write(0, $row->firstsup->title); //is title
		}
		if(isset($row->secondsup->name)){
			$pdf->setXY(125, 54.5);
			$pdf->Write(0, $row->secondsup->name); //2nd is name
			$pdf->setXY(144, 58);
			$pdf->Write(0, $row->secondsup->title); //2nd is title
		}
		
		$pdf->setXY(245, 47.5);
		$pdf->Write(0, date('F Y', strtotime($eval->reviewFrom)).' - '.date('F Y', strtotime($eval->reviewTo))); //review period
		$pdf->setXY(223, 51);
		$pdf->Write(0, date('F d, Y', strtotime($eval->reviewDate))); //review date
		
		//dedication to excellence
		$dedication = explode('+++', $eval->dedicationToExcellence);
		$dEmp = explode(',',$dedication[0]);
		$dSup = explode(',',$dedication[1]);		
		$pdf->setXY(265, 85); $pdf->Write(0, $dEmp[0]);
		$pdf->setXY(280, 85); $pdf->Write(0, $dSup[0]);
		$pdf->setXY(265, 90); $pdf->Write(0, $dEmp[1]);
		$pdf->setXY(280, 90); $pdf->Write(0, $dSup[1]);
		$pdf->setXY(265, 95); $pdf->Write(0, $dEmp[2]);
		$pdf->setXY(280, 95); $pdf->Write(0, $dSup[2]);
		$pdf->setXY(265, 100); $pdf->Write(0, $dEmp[3]);
		$pdf->setXY(280, 100); $pdf->Write(0, $dSup[3]);
		$pdf->setXY(265, 105); $pdf->Write(0, $dEmp[4]);
		$pdf->setXY(280, 105); $pdf->Write(0, $dSup[4]);
		
		//proactiveness
		$proactiveness = explode('+++', $eval->proactiveness);
		$pEmp = explode(',',$proactiveness[0]);
		$pSup = explode(',',$proactiveness[1]);		
		$pdf->setXY(265, 116); $pdf->Write(0, $pEmp[0]);
		$pdf->setXY(280, 116); $pdf->Write(0, $pSup[0]);
		$pdf->setXY(265, 122); $pdf->Write(0, $pEmp[1]);
		$pdf->setXY(280, 122); $pdf->Write(0, $pSup[1]);
		
		//teamwork
		$teamwork = explode('+++', $eval->teamwork);
		$tEmp = explode(',',$teamwork[0]);
		$tSup = explode(',',$teamwork[1]);		
		$pdf->setXY(265, 134); $pdf->Write(0, $tEmp[0]);
		$pdf->setXY(280, 134); $pdf->Write(0, $tSup[0]);
		$pdf->setXY(265, 138); $pdf->Write(0, $tEmp[1]);
		$pdf->setXY(280, 138); $pdf->Write(0, $tSup[1]);
		$pdf->setXY(265, 143); $pdf->Write(0, $tEmp[2]);
		$pdf->setXY(280, 143); $pdf->Write(0, $tSup[2]);
		
		//communication
		$communication = explode('+++', $eval->communication);
		$cEmp = explode(',',$communication[0]);
		$cSup = explode(',',$communication[1]);		
		$pdf->setXY(265, 154); $pdf->Write(0, $cEmp[0]);
		$pdf->setXY(280, 154); $pdf->Write(0, $cSup[0]);
		$pdf->setXY(265, 161); $pdf->Write(0, $cEmp[1]);
		$pdf->setXY(280, 161); $pdf->Write(0, $cSup[1]);
		$pdf->setXY(265, 166); $pdf->Write(0, $cEmp[2]);
		$pdf->setXY(280, 166); $pdf->Write(0, $cSup[2]);
		
		//reliability
		$reliability = explode('+++', $eval->reliability);
		$rEmp = explode(',',$reliability[0]);
		$rSup = explode(',',$reliability[1]);		
		$pdf->setXY(265, 177); $pdf->Write(0, $rEmp[0]);
		$pdf->setXY(280, 177); $pdf->Write(0, $rSup[0]);
		$pdf->setXY(265, 183); $pdf->Write(0, $rEmp[1]);
		$pdf->setXY(280, 183); $pdf->Write(0, $rSup[1]);
		$pdf->setXY(265, 189); $pdf->Write(0, $rEmp[2]);
		$pdf->setXY(280, 189); $pdf->Write(0, $rSup[2]);
		
		//page2
		$pdf->AddPage();
		$tplIdx2 = $pdf->importPage(2);
		$pdf->useTemplate($tplIdx2, null, null, 0, 0, true);
		$pdf->setXY(265, 31.8); $pdf->Write(0, $rEmp[3]);
		$pdf->setXY(280, 31.8); $pdf->Write(0, $rSup[3]);
		
		//professionalism
		$professionalism = explode('+++', $eval->professionalism);
		$ppEmp = explode(',',$professionalism[0]);
		$ppSup = explode(',',$professionalism[1]);		
		$pdf->setXY(265, 43); $pdf->Write(0, $ppEmp[0]);
		$pdf->setXY(280, 43); $pdf->Write(0, $ppSup[0]);
		$pdf->setXY(265, 48); $pdf->Write(0, $ppEmp[1]);
		$pdf->setXY(280, 48); $pdf->Write(0, $ppSup[1]);
		$pdf->setXY(265, 54); $pdf->Write(0, $ppEmp[2]);
		$pdf->setXY(280, 54); $pdf->Write(0, $ppSup[2]);
		
		//flexibility
		$flexibility = explode('+++', $eval->flexibility);
		$fEmp = explode(',',$flexibility[0]);
		$fSup = explode(',',$flexibility[1]);		
		$pdf->setXY(265, 66); $pdf->Write(0, $fEmp[0]);
		$pdf->setXY(280, 66); $pdf->Write(0, $fSup[0]);
		$pdf->setXY(265, 72); $pdf->Write(0, $fEmp[1]);
		$pdf->setXY(280, 72); $pdf->Write(0, $fSup[1]);
		$pdf->setXY(265, 78); $pdf->Write(0, $fEmp[2]);
		$pdf->setXY(280, 78); $pdf->Write(0, $fSup[2]);
		$pdf->setXY(265, 83.5); $pdf->Write(0, $fEmp[3]);
		$pdf->setXY(280, 83.5); $pdf->Write(0, $fSup[3]);
		$pdf->setXY(265, 89); $pdf->Write(0, $fEmp[4]);
		$pdf->setXY(280, 89); $pdf->Write(0, $fSup[4]);
		
		//evalrating
		$erating = explode('+++', $eval->evalRating);	
		$pdf->setXY(263, 94.5); $pdf->Write(0, $erating[0]);
		$pdf->setXY(278, 94.5); $pdf->Write(0, $erating[1]); //averages
		
		$pdf->setXY(263, 99.5); $pdf->Write(0, $this->textM->convertNumFormat($erating[0]*0.20));
		$pdf->setXY(278, 99.5); $pdf->Write(0, $this->textM->convertNumFormat($erating[1]*0.80)); //weighted score
		
		$pdf->setXY(273, 104.5); $pdf->Write(0, $eval->finalRating); //total
		
		$pdf->SetFont('Arial','',9);
		
		//achievements
		$achievements = explode('+++', $eval->achievements);
		$pdf->setXY(5, 123); $pdf->MultiCell(137, 3, $achievements[0],0,'L',false);	
		$pdf->setXY(150, 123); $pdf->MultiCell(143, 3, $achievements[1],0,'L',false);

		//strengths
		$strengths = explode('+++', $eval->strengths);
		$pdf->setXY(5, 165); $pdf->MultiCell(137, 3, $strengths[0],0,'L',false);	
		$pdf->setXY(150, 165); $pdf->MultiCell(143, 3, $strengths[1],0,'L',false);	
		
		//page3	
		$pdf->AddPage();
		$tplIdx = $pdf->importPage(3);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);	
				
		//areasOfImprovement
		$areasOfImprovement = explode('+++', $eval->areasOfImprovement);
		$pdf->setXY(5, 41); $pdf->MultiCell(137, 3, $areasOfImprovement[0],0,'L',false);	
		$pdf->setXY(150, 41); $pdf->MultiCell(143, 3, $areasOfImprovement[1],0,'L',false);
		
		//goals
		$goals = explode('+++', $eval->goals);
		$pdf->setXY(5, 77); $pdf->MultiCell(137, 3, $goals[0],0,'L',false);	
		$pdf->setXY(150, 77); $pdf->MultiCell(143, 3, $goals[1],0,'L',false);
		
		$pdf->SetFont('Arial','B',9);
		$pdf->setXY(72, 121.5); $pdf->Write(0, $eval->finalRating); //total weighed score
		$pdf->setXY(48, 126.8); $pdf->Write(0, $this->textM->getScoreMatrix($eval->finalRating)); //final rating
		$pdf->setXY(35, 132); $pdf->Write(0, $eval->recommendation); //recommendation
		
		if($eval->effectiveDate!='0000-00-00'){
			$pdf->setXY(55, 137.5); $pdf->Write(0, date('F d, Y', strtotime($eval->effectiveDate))); //recommendation effective date
		}
		if($eval->nextReviewDate!='0000-00-00'){
			$pdf->setXY(143, 121.5); $pdf->Write(0, date('F d, Y', strtotime($eval->nextReviewDate))); //recommendation effective date
		}
		
		$pdf->SetFont('Arial','',9);
		
		//other reviewer remarks
		if(!empty($eval->recommendationRemarks)){
			$pdf->setXY(112, 130); $pdf->MultiCell(100, 4, $eval->recommendationRemarks,1,'L',false);
		}
		
		
		//acknowledgements
		$pdf->SetFont('Arial','B',10);
		$pdf->setXY(16, 182.5); $pdf->MultiCell(78, 3, strtoupper($row->firstsup->name).' / '.date('F d, Y', strtotime($eval->reviewDate)),0,'C',false); //reviewer IS
		$pdf->setXY(108, 182.5); $pdf->MultiCell(78, 3, strtoupper($row->secondsup->name).' / '.date('F d, Y', strtotime($eval->reviewDate)),0,'C',false); //2nd level IS
		$pdf->setXY(200, 182.5); $pdf->MultiCell(78, 3, strtoupper($row->lname.', '.$row->fname).' / '.date('F d, Y', strtotime($eval->reviewDate)),0,'C',false); //employee 
		
		
		
		$pdf->Output('performance_'.$eval->evalID.'.pdf', 'I');
	}
	
	function pdfincidentreport($row){
		$dir = 'uploads/violationreported/';
		$statusArr = $this->textM->constantArr('incidentRepStatus');
			
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'Incident_Report_Form.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','B',9);
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->setXY(113, 39.5); $pdf->Write(0, date('F d, Y h:i a', strtotime($row->dateSubmitted)));
		
		$pdf->SetFont('Arial','B',11);
		$pdf->setXY(116, 51); $pdf->Write(0, ((!empty($row->alias))?$row->alias:$row->name));
		$pdf->setXY(116, 68); $pdf->Write(0, $row->where);
		$pdf->setXY(106, 98); $pdf->Write(0, date('F d, Y', strtotime($row->when)));
		$pdf->setXY(106, 107); $pdf->Write(0, $row->where);
		
		$pdf->SetFont('Arial','',8);
		$pdf->setXY(25, 120); $pdf->MultiCell(160, 4, $row->what,0,'L',false);
		
		
		$offenses = $this->dbmodel->getQueryResults('staffOffenses', '*', 'offenseID IN ('.$row->whatViolation.')');
		$offtxt = '';
		foreach($offenses AS $o){
			$offtxt .= '-  '.$o->offense."\n";
		}
		$pdf->setXY(25, 160); $pdf->MultiCell(160, 4, $offtxt,0,'L',false);
		
		$pdf->SetFont('Arial','B',11);
		$pdf->setXY(145, 189); $pdf->Write(0, (($row->reported==1)?'YES':'NO'));
		
		$pdf->SetFont('Arial','',8);
		$pdf->setXY(25, 202); $pdf->MultiCell(160, 4, $row->whatISaction,0,'L',false);	

		$pdf->SetFont('Arial','B',11);
		$pdf->setXY(145, 231); $pdf->Write(0, ((!empty($row->proof))?'YES':'NO'));
		$pdf->setXY(145, 239.5); $pdf->Write(0, ((!empty($row->witnesses))?'YES':'NO'));
		
		$pdf->SetFont('Arial','',9);
		$pdf->setXY(30, 255); $pdf->Write(0, $row->witnesses);
		
		
		//page2	
		$pdf->AddPage();
		$tplIdx = $pdf->importPage(2);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);	
		
		$pdf->SetFont('Arial','',9);
		
		$docs = explode('|', $row->docs);
		$dtext = '';
		foreach($docs AS $d){
			if(!empty($d)){
				$dtext .= $this->config->base_url().$dir.$d.', ';
			}
		}
		$pdf->SetFont('Arial','',8);
		$pdf->setXY(25, 40); $pdf->MultiCell(160, 4,$row->otherdetails,0,'L',false);
		$pdf->setXY(25, 78); $pdf->MultiCell(160, 4,rtrim($dtext, ', '),0,'L',false);	
		
		$pdf->SetFont('Arial','B',11);
		$pdf->setXY(155, 110); $pdf->Write(0, ((!empty($row->alias))?'YES':'NO'));
		$pdf->setXY(155, 118.5); $pdf->Write(0, ((!empty($row->whyExcludeIS))?'YES':'NO'));
		$pdf->SetFont('Arial','',8);
		$pdf->setXY(25, 131); $pdf->MultiCell(160, 4,$row->whyExcludeIS,0,'L',false);	
		
		//SIGNATURES
		$pdf->SetFont('Arial','B',10);
		$pdf->setXY(25, 196); $pdf->Write(0, '(Complainant) '.strtoupper(((!empty($row->alias))?$row->alias:$row->name)).' (ID#: '.$row->idNum.'), '.date('F d, Y', strtotime($row->dateSubmitted)));
		
		$pdf->setXY(25, 220.5); $pdf->Write(0, strtoupper($this->user->name).', '.date('F d, Y', strtotime($row->dateSubmitted)));
		$pdf->setXY(25, 254); $pdf->Write(0, strtoupper($row->supervisorName).', '.date('F d, Y', strtotime($row->dateSubmitted)));
			
								
		$pdf->Output('incident_report_form'.$row->reportID.'.pdf', 'I');
	}
	
	
	function pdfwrittenwarning($row, $type='I'){			
		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'nte_written.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		
		$pdf->SetFont('Arial','B',9);
		$pdf->SetTextColor(0, 0, 0);
		
		$pdf->setXY(35, 36); $pdf->Write(0, date('l, F d, Y h:i a', strtotime($row->dateissued)));
		$pdf->setXY(35, 40.5); $pdf->Write(0, $row->fname.' '.$row->lname);
		$pdf->setXY(35, 44.5); $pdf->Write(0, 'The Human Resource Department');
		$pdf->setXY(35, 48.5); $pdf->Write(0, $row->supName);
		
		$pdf->SetFont('Arial','',9);
		$pdf->setXY(22, 83); $pdf->MultiCell(175, 3,$row->wrDetails,0,'L',false);
		
		$pdf->SetFont('Arial','B',9);
		$pdf->SetTextColor(255, 0, 0);
		$pdf->setXY(22, 150); $pdf->Write(0, $this->textM->ordinal($row->offenselevel).' Offense');
		
		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->setXY(22, 154.5); $pdf->MultiCell(175, 3,$row->category.' - '.$row->offense.' - Level '.$row->level,0,'L',false);
		
		$pdf->SetFont('Arial','B',9); ///ISSUED BY
		$pdf->setXY(20, 212); $pdf->MultiCell(78, 3, strtoupper($row->supName),0,'C',false);
		$pdf->setXY(124, 212); $pdf->MultiCell(60, 3,date('F d, Y', strtotime($row->dateissued)),0,'C',false);
		///RECEIVED BY
		$pdf->setXY(20, 233); $pdf->MultiCell(78, 3, strtoupper($row->fname.' '.$row->lname),0,'C',false);
		$pdf->setXY(124, 233); $pdf->MultiCell(60, 3,date('F d, Y', strtotime($row->dateissued)),0,'C',false);
				
		$pdf->Output('nte_written'.$row->nteID.'.pdf', $type);
	}
	
	
	function updatePublishLog($leaveID){
		$dateToday = date('Y-m-d H:i:s');
		$leave = $this->dbmodel->getSingleInfo('staffLeaves', '*', 'leaveID='.$leaveID); 
				
		if(!empty($leave)){
			if($dateToday>=$leave->leaveStart){ //update tcStaffLogPublish for previous dates	
				$this->dbmodel->updateQueryText('tcStaffLogPublish', 
					'leaveID_fk="'.$leaveID.'", publishTimePaid=0, publishDeduct=0, publishND=0, datePublished="0000-00-00 00:00:00", publishBy="", publishNote=""', 
					'empID_fk="'.$leave->empID_fk.'" AND (schedIn BETWEEN "'.$leave->leaveStart.'" AND "'.$leave->leaveEnd.'" OR schedOut BETWEEN "'.$leave->leaveStart.'" AND "'.$leave->leaveEnd.'")');
				
				$this->timeM->updateStaffLog($leave->leaveStart, $leave->empID_fk);
			}
			
			if(!empty($leave->offsetdates)){
				$tawa = explode('|', rtrim($leave->offsetdates, '|'));
				
				foreach($tawa AS $ta){
					$smile = explode(',', $ta);
					$start = date('Y-m-d H:i:s', strtotime($smile[0].' -1 day'));
										
					if($dateToday>=$start){
						//remove publish details						
						$this->dbmodel->updateQueryText('tcStaffLogPublish', 
							'leaveID_fk="'.$leaveID.'", publishTimePaid=0, publishDeduct=0, publishND=0, datePublished="0000-00-00 00:00:00", publishBy="", publishNote=""', 
							'empID_fk="'.$leave->empID_fk.'" AND slogDate BETWEEN "'.$start.'" AND "'.$smile[1].':00'.'"');
						
						$this->timeM->updateStaffLog($start, $leave->empID_fk);
					}
				}
			}
		}
	}
	
	function excelGenerateLeaveCodes($query, $start, $end){
		require_once('includes/excel/PHPExcel/IOFactory.php');
		$fileType = 'Excel5';
		$fileName = 'includes/templates/leaveCodesTemplate.xls';

		// Read the file
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($fileName);
		
		// Change the file
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', 'GENERATED LEAVE CODES REPORT - '.date('F d, Y', strtotime($start)).' - '.date('F d, Y', strtotime($end)));
					
					
		$staffArr = array();
		$queryStaffs = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(lname,", ",fname) AS name');
		foreach($queryStaffs AS $s){
			$staffArr[$s->empID] = $s->name;
		}
		$statusArr = array(0=>'Expired', 1=>'Active', 2=>'Redeemed');
		
		$cnt = 3;
		foreach($query AS $q){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, $q->dategenerated);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, $q->code);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, ((isset($staffArr[$q->generatedBy]))?$staffArr[$q->generatedBy]:''));
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, ((isset($staffArr[$q->forWhom]))?$staffArr[$q->forWhom]:''));
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, ((isset($staffArr[$q->usedBy]))?$staffArr[$q->usedBy]:''));
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt, $q->why);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt, $statusArr[$q->status]);
			
			$cnt++;
		}
		
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
		ob_end_clean();
		// We'll be outputting an excel file
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="Payroll_Distribution_Report.xls"');
		$objWriter->save('php://output');
	}

	public function getAttendanceReport($start, $end){

		//get all employee
		//$all_staff = $this->dbmodel->getQueryResults('staffs', 'empID, fname, lname, idNum', 'active = 1 AND office = "PH-Cebu" AND exclude_schedule = 0');
		
		$data_array = array();
		//foreach( $all_staff as $staff ){
			$staff_time_logs = $this->timeM->getCalendarSchedule( $start, $end, 97 );			
			$data_array[ 97 ]['time_logs'] = $staff_time_logs;
		//	$data_array[ $staff->empID ]['staff_info'] = $staff;
		//}

		$this->textM->aaa($data_array);
		//$this->timeM->getAllStaffAttendance( $start, $end );
	}

	public function hdmf_loan( $empID, $other_info ){
		//$this->textM->aaa($other_info);
		$employee_info = $this->dbmodel->getSingleInfo('staffs', '*', 'empID = '. $empID );
	//	$this->textM->aaa($employee_info);
		$month = strtoupper(date('F', strtotime($other_info['date_submitted'])));
		
		$from = date('Y-m-01', strtotime($other_info['date_submitted'] . '-1 month') );
		$to = date('Y-m-t', strtotime($other_info['date_submitted'] . '-1 month') );
		
		
		$lf_name = strtoupper($employee_info->lname.' '.$employee_info->fname);
		$complete_name = $employee_info->lname.'   '.$employee_info->fname;
		if( !empty($employee_info->suffix) ){
			$complete_name .= '   '.$employee_info->suffix;
		} else {
			$complete_name .= '                                              ';
		}
		$complete_name .= '   '.$employee_info->mname;

		if( $employee_info->gender == 'F' AND $employee_info->maritalStatus != 'Single'){
			$complete_name .= '   '.$employee_info->maiden_name;
		}
		$complete_name = strtoupper($complete_name);
 

		$fl_name = strtoupper($employee_info->fname.' '.$employee_info->lname);
		$lfm_name = strtoupper($employee_info->lname.'          '.$employee_info->fname.'          '.$employee_info->mname);
		$address = strtoupper( $employee_info->address.' '.$employee_info->city.' '.$employee_info->zip);

		$payslip_info = $this->payrollM->getPayslipOnTimeRange($empID, $from, $to, true);
		//$this->textM->aaa($payslip_info);		

		require_once('includes/fpdf/fpdf.php');
		require_once('includes/fpdf/fpdi.php');
				
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->setSourceFile(PDFTEMPLATES_DIR.'hdmf_loan.pdf');
		$tplIdx = $pdf->importPage(1);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);
		//set font
		$pdf->SetFont('Arial','B',8);
		$pdf->SetTextColor(0, 0, 255);

		//defaults values
		$company_name = 'TATE PUBLISHING AND ENTERPRISES (PHILIPPINES), INC.';
		$pdf->setXY(10, 67.5); 
		$pdf->Write(0, $company_name);

		$pdf->setXY(10, 74.5);
		$pdf->MultiCell(115, 4, '4F JY SQUARE DISCOVERY MALL, SALINAS DRIVE, LAHUG, CEBU CITY 6000', 0, 'L');

		$pdf->setXY(137,68);
		$pdf->Cell(0, 0, '(032) 318-2586');

		$pdf->setXY(25, 136.5); 
		$pdf->setFontSize(7);
		$pdf->Write(0, $company_name);

		$pdf->setFontSize(8);
		$pdf->setXY(153, 164); 
		$pdf->Cell(0, 0, 'DIANA ROSE BARTULIN');

		$pdf->setXY(153, 174); 
		$pdf->Cell(0, 0, 'DIRECTOR OF OPERATIONS');

		$pdf->setXY(140, 179); 
		$pdf->Cell(0, 0, '2028-8357-0006');
		//end of defaults

		//infos
		$complete_name = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $complete_name);
		$pdf->setXY(10, 30);
		$pdf->Cell(0, 0, strtoupper($complete_name) );

		$pdf->setXY(10, 35);
		$pdf->MultiCell(105, 6, $address, 0, 'L');

		//gender
		switch( $employee_info->gender ){
			case 'M': $pdf->setXY(88.5, 59); break;
			case 'F': $pdf->setXY(105, 59); break;
		}		
		$pdf->Cell(0, 0, '/');

		//civil status
		switch( $employee_info->maritalStatus ){
			case 'Single': $pdf->setXY(9, 58); break;
			case 'Married': $pdf->setXY(9, 61); break;
			case 'Widowed': $pdf->setXY(44, 58); break;
			case 'Separated': $pdf->setXY(44, 61); break;
			case 'Divorced': $pdf->setXY(73, 58); break;
		}
		$pdf->Cell(0, 0, '/');

		$pdf->setXY(173, 83);
		$pdf->Cell(0, 0, $employee_info->idNum);

		$pdf->setXY(173, 60);
		$pdf->Cell(0, 0, $employee_info->phone1);

		$pdf->setXY(137, 83);
		$pdf->Cell(0, 0, '423-687-498-000');

		$pdf->setXY(173, 52);
		$pdf->Cell(0, 0, $this->textM->decryptText($employee_info->hdmf));

		$pdf->setXY(137, 60);
		$pdf->Cell(0, 0, $this->textM->decryptText($employee_info->sss));

		//birthdate
		$birth_date = explode('-', $employee_info->bdate);
		$pdf->setXY(12, 52.5);
		$pdf->Cell(0, 0, $birth_date[1]);		

		$pdf->setXY(23, 52.5);
		$pdf->Cell(0, 0, $birth_date[2]);		

		$pdf->setXY(32, 52.5);
		$pdf->Cell(0, 0, $birth_date[0]);		

		$pdf->setXY(50, 180);
		$pdf->Cell(0, 0, $fl_name);

		$pdf->setXY(140, 254);
		$pdf->Cell(0, 0, $fl_name);

		//witnesses
		$pdf->setXY(22, 252);
		$pdf->Cell(0, 0, strtoupper($other_info['all_staff'][ $other_info['witness_1'] ]));

		$pdf->setXY(72, 252);
		$pdf->Cell(0, 0, strtoupper($other_info['all_staff'][ $other_info['witness_2'] ]));		
		//end infos

		//loan info
		if( isset($other_info['loan_type']) ){
			switch( $other_info['loan_type'] ){
				case 'new': $pdf->setXY(136, 51); break;
				case 'renew': $pdf->setXY(152, 51); break;
			}
			$pdf->Cell(0, 0, '/');	
		}
		

		switch( $other_info['loan_amt'] ){
			case 60: $pdf->setXY(136, 28); break;
			case 70: $pdf->setXY(136, 31); break;	
			case 80: $pdf->setXY(165.5, 28); break;	
			case 90: $pdf->setXY(165.5, 31); break;	
		}
		$pdf->Cell(0, 0, '/');
		if( $other_info['loan_amt'] == 00 ){
			$pdf->setFontSize(7);
			$pdf->setXY(188, 31);
			$pdf->Cell(0, 0, $other_info['loan_amt_others']);
		}		
		$pdf->setFontSize(7);
		$purpose_array = $this->textM->constantArr('hdmf_loan_purpose')[ $other_info['loan_purpose'] ];
		$pdf->setXY(136, 39);
		$pdf->MultiCell(73, 2.5, strtoupper($purpose_array), 0, 'L');

		$pdf->setFontSize(8);

		$pdf->setXY(43.5, 49);
		$pdf->MultiCell(29, 2.5, strtoupper($other_info['birth_place']), 0, 'L' );

		$pdf->setXY(73, 51);
		$other_info['mo_maiden_name'] = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $other_info['mo_maiden_name']);
		$pdf->Cell(0, 0, strtoupper($other_info['mo_maiden_name']) );




		if( !empty($other_info['employer_1']) ){
			$pdf->setFontSize(6);
			$pdf->setXY(9, 96);
			$pdf->Cell(69, 4, $other_info['employer_1'][0], 0, 0, 'L' );			
			$pdf->Cell(94, 4, $other_info['employer_1'][1], 0, 0, 'L' );			
			$pdf->Cell(20, 4, date('m/Y', strtotime($other_info['employer_1'][2])), 0, 0, 'L' );
			$pdf->Cell(18, 4, date('m/Y', strtotime($other_info['employer_1'][3])), 0, 2, 'L' );			
		}
		if( !empty($other_info['employer_2']) ){
			$pdf->setFontSize(6);
			$pdf->setXY(9, 99);
			$pdf->Cell(69, 4, $other_info['employer_2'][0], 0, 0, 'L' );			
			$pdf->Cell(94, 4, $other_info['employer_2'][1], 0, 0, 'L' );
			if( !empty($other_info['employer_2'][2] ) ){
				$pdf->Cell(20, 4, date('m/Y', strtotime($other_info['employer_2'][2])), 0, 0, 'L' );	
			} 			
			if( !empty($other_info['employer_2'][3] ) ){
				$pdf->Cell(18, 4, date('m/Y', strtotime($other_info['employer_2'][3])), 0, 0, 'L' );			
			} 			
			
		}

		//page 2
		$pdf->AddPage();
		$tplIdx = $pdf->importPage(2);
		$pdf->useTemplate($tplIdx, null, null, 0, 0, true);

		$pdf->setFontSize(10);
		$pdf->setXY(10, 39);
		$pdf->Cell(85, 5, $fl_name, 0, 1, 'C');

		$pdf->setXY(55, 55);
		$pdf->Cell(0, 0, strtoupper( date('F', strtotime($from) ) ) );

		$base_pay = $payslip_info['total_month']['basePay'];
		$pdf->setXY(50, 68);
		$pdf->Cell(0, 0, $this->textM->numFormat( $base_pay ) );

		$pdf->setFontSize(7);
		$pdf->setXY(13, 80);
		foreach( $payslip_info['allowances'] as $name => $val ){			
			$pdf->Cell(58, 5, strtoupper($name), 0, 0, 'L' );
			$pdf->Cell(20, 5, $this->textM->numFormat($val), 0, 1, 'R');
			$pdf->setX(13);
			$total_allowance += $val;
		}
		$gross = $base_pay + $total_allowance;

		$pdf->setXY(75, 140);
		$pdf->setFontSize(9);
		$pdf->Cell(0, 0, $this->textM->numFormat($gross));

		$pdf->setFontSize(7);
		$pdf->setXY(13, 160);
		foreach( $payslip_info['deductions'] as $name => $val ){			
			$pdf->Cell(58, 5, strtoupper($name), 0, 0, 'L' );
			$pdf->Cell(20, 5, $this->textM->numFormat($val), 0, 1, 'R');
			$pdf->setX(13);
			$total_deductions += $val;
		}

		$net = $gross - $total_deductions;
		$pdf->setXY(75, 206);
		$pdf->setFontSize(9);
		$pdf->Cell(0, 0, $this->textM->numFormat($total_deductions));

		$pdf->setXY(75, 221);		
		$pdf->Cell(0, 0, $this->textM->numFormat($net));

		$pdf->setXY(32, 231);
		$pdf->Cell(0, 0, $this->textM->ordinal(date('d', strtotime($other_info['date_submitted']) ) ) );

		$pdf->setXY(56, 231);
		$pdf->Cell(0, 0, $month);

		$pdf->setXY(80, 231);
		$pdf->Cell(0, 0, date('y', strtotime($other_info['date_submitted']) ) );

		$pdf->setXY(18, 248); 
		$pdf->Cell(75, 5, 'DIANA ROSE BARTULIN', 0, 1, 'C');

		$pdf->setXY(38, 302); 
		$pdf->Cell(55, 5, $fl_name, 0, 1, 'C');

		$pdf->Output('hdmf_loan_'.$employee_info->username.'.pdf', 'I');

		//redirect( $this->config->base_url() );
		return true;
	}

	//get_all staff
	public function get_all_staff( $index = 'empID' ){
		$all_staff = $this->dbmodel->getQueryResults('staffs', '*');
		$staffs = [];
		if( $all_staff ){
			foreach( $all_staff as $key => $val ){
				$staffs[ $val->$index ] = $val;
			}
		}
		return $staffs;
	}


	//return the cached list of staff
	public function get_cached_all_staff(){		

		if( ! $item = $this->cache->get('all_staff') ){
			$item = $this->get_all_staff();
			$this->cache->save( 'all_staff', $item, 3600 );
		}
		return $item;
	}
	
} //end class

