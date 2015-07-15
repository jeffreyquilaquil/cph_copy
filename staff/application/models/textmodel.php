<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Textmodel extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
    }
	
	function aaa($i, $s=true){
		echo '<pre>';
		print_r($i);
		
		if($s==true) exit;
		else echo '</pre>';
	}
	
	function formfield($type, $name='', $val='', $class='', $placeholder='', $additional=''){
		$t = '';
		
		if($type=='text' || $type=='hidden' || $type=='submit'){
			$t = '<input type="'.$type.'" name="'.$name.'" class="'.$class.'" value="'.$val.'" placeholder="'.$placeholder.'" '.$additional.'/>';
		}else if($type=='select'){
			$t = '<select name="'.$name.'" class="'.$class.'" '.$additional.'>'.$val.'</select>';
		}else if($type=='textarea'){
			$t = '<textarea name="'.$name.'" class="'.$class.'" '.$additional.' placeholder="'.$placeholder.'">'.$val.'</textarea>';
		}		
		
		return $t;
	}
		
	function getEmps($all, $id, $n){
		$emptxt = '';
		if(isset($all[$id])){
			$emptxt .= '<ul class="ul_'.$n.' emp_'.$id.' '.(($n>2)?'uldisp':'').'">';
			foreach($all[$id] AS $a):
				if(isset($all[$a[0]])){
					$emptxt .= '<li class="li_'.$n.'" style="cursor:pointer" onClick="toggleDisplay('.$a[0].')">';
				}else{
					$emptxt .= '<li class="li_'.$n.' li_none">';
				}
				
				$emptxt .= '<u>'.$a[2].'</u>, <i><a href="'.$this->config->base_url().'staffinfo/'.$a[3].'/" target="_blank">'.$a[1].'</a></i>';
				
				
				if(isset($all[$a[0]]))
					$emptxt .= '<div id="pointer_'.$a[0].'" class="tpointer" style="float:right; background-color:#ccc; padding:0 5px;">+</div>';
					
				$emptxt .= '</li>';
				if(isset($all[$a[0]])){
					$emptxt .= $this->getEmps($all, $a[0], ($n+1));
				}
			endforeach;	
			$emptxt .= '</ul>';
		}
		
		return $emptxt;
	}
	
	function encryptText($text){
		if(!empty($text)){
			$secret = base64_encode(THEENCRYPTOR);
			$text = base64_encode($secret.base64_encode($text));
		}
		return $text;
	}
	
	function decryptText($text){
		if(!empty($text)){
			$secret = base64_encode(THEENCRYPTOR);
			$text = base64_decode($text);
			$text = str_replace($secret, '', $text);
			$text = base64_decode($text);
		}
		return $text;
	}
	
	//if salType==1 no Php
	function convertDecryptedText($fld, $text, $salType=0){
		if(in_array($fld, $this->textM->constantArr('encText'))){
			$text = $this->decryptText($text);
		}
		
		if($fld=='sal' || $fld=='allowance'){
			if($salType==1) $text = $this->convertNumFormat($text);
			else $text = 'Php '.$this->convertNumFormat($text);
		}
		
		return $text;
	}
	
	public function convertNumFormat($n){
		return number_format((int)str_replace(',','',$n),2);
	}
	
	function convertTimeToMinHours($tdiff){
		$tText = '';
		$hours = floor($tdiff/3600);
		$remainder = $tdiff - ($hours * 3600);
		$minutes = floor($remainder/60);
		$remainder = $remainder - ($minutes * 60);
		$seconds = $remainder;

		if($hours>0 && $hours==1) $tText .= $hours.' hour ';
		else if($hours>0 && $hours>1) $tText .= $hours.' hours ';

		if($minutes==1) $tText .= $minutes.' minute ';
		else if($minutes>1) $tText .= $minutes.' minutes ';
		
		if($seconds==1) $tText .= $seconds.' second ';
		else if($seconds>1) $tText .= $seconds.' seconds ';

		return $tText;
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

	function displaycis($info, $status){
		$disp = '<table class="tableInfo datatable tblcis_'.$status.'">
				<thead>
					<tr class="trhead" align="center">
						<td>Employee\'s Name</td>
						<td>Date Filed</td>
						<td>Effective Date</td>
						<td>Changes<br/>
							<hr/>
							<div style="width:100%; color:#555555; font-style:italic;">
								<span style="float:left;">Type</span>
								<span style="text-align:center;">'.(($status==3)?'Previous':'Current').' Info</span>
								<span style="float:right;">New Info</span>
							</div>
						</td>
						<td>Immediate Supervisor</td>
						<td>Prepared By</td>
						<td><br/></td>
						<td><br/></td>
					</tr>
				</thead>
			';
			/* $disp = '<table class="tableInfo datatable tblcis_'.$status.'">
				<thead>
					<tr class="trhead" align="center">
						<td>Employee\'s Name</td>
						<td>Date Filed</td>
						<td>Effective Date</td>
						<td>Type</td>
						<td>'.(($status==3)?'Previous':'Current').' Info</td>
						<td>New Info</td>
						<td>Immediate Supervisor</td>
						<td>Prepared By</td>
						<td><br/></td>
						<td><br/></td>
					</tr>
				</thead>
			';	
		 */
		if(count($info)>0){
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
					$arr[$cnt][0] = 'Basic Salary'; $arr[$cnt][1] = 'Php '.$this->textM->convertNumFormat($c->salary->c); $arr[$cnt][2] = 'Php '.$this->textM->convertNumFormat($c->salary->n);
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
				
				/* $disp .= '<td>'.date('d M Y', strtotime($a->effectivedate)).'</td>';
				$disp .= '<td>'.date('d M Y', strtotime($a->effectivedate)).'</td>';
				$disp .= '<td>'.date('d M Y', strtotime($a->effectivedate)).'</td>'; */
				
				$disp .= '<td>';
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
	
	function getLeaveStatusText($status, $iscancelled){
		$leaveStatusArr = $this->textM->constantArr('leaveStatus');
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
	
	function leaveTableDisplay($rQuery, $type, $datatable=false){
		$leaveTypeArr = $this->textM->constantArr('leaveType');
		$yellowArr = array('imquery', 'imcancelledquery', 'allpending');
		$hideArr = array('allpending', 'allapproved', 'allapprovedNopay', 'alldisapproved', 'allcancelled');
		$disp = '<div class="'.((in_array($type,$hideArr))?'hidden':'').'" id="tbl'.$type.'">
				<table class="tableInfo fs11px '.(($datatable===true)?'datatable':'').'">
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
					<td>'.$this->textM->getLeaveStatusText($row->status, $row->iscancelled).'</td>
					<td><a class="iframe" href="'.$this->config->base_url().'staffleaves/'.$row->leaveID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
				</tr>
			';
		}
		$disp .= '</table>
				</div>';
				
		return $disp;
	}
	
	//assign $time to empty if you dont want to call customTimeArrayByCat function. Please call customTimeArrayByCat first if this is loop
	function customTimeSelect($val=''){
		$time = $this->commonM->customTimeArrayByCat();
		$valentine = '<option value=""></option>';
		foreach($time AS $t=>$t2):
			$valentine .= '<optgroup label="'.$t2['name'].'">';						
			foreach($t2 AS $k=>$t):
				if($k!='name'){
					$ex = explode('|', $t);
					$valentine .= '<option value="'.$k.'" '.(($k==$val)?'selected="selected"':'').'>'.$ex[0].'</option>';
				}
			endforeach;
			$valentine .= '</optgroup>';
		endforeach;	
		
		return $valentine;
	}
	
	function getTodayBetweenSchedCondition($start, $end){
		return '( (("'.$start.'" OR "'.$end.'" BETWEEN effectivestart AND effectiveend) AND effectiveend!="0000-00-00")
				 OR (effectiveend="0000-00-00" AND effectivestart<="'.$start.'")
				 OR(effectiveend="0000-00-00" AND effectivestart >= "'.$start.'" AND effectivestart <= "'.$end.'")		
				 )';
	}
	
	function constantText($t){
		$txt = '';
		if($t=='txt_fname') $txt = 'First Name';
		else if($t=='txt_lname') $txt = 'Last Name';
		else if($t=='txt_mname') $txt = 'Middle Name';
		else if($t=='txt_suffix') $txt = 'Name Suffix';
		else if($t=='txt_username') $txt = 'Username';
		else if($t=='txt_email') $txt = 'Username';
		else if($t=='txt_username') $txt = 'Company E-mail';
		else if($t=='txt_pemail') $txt = 'Personal E-mail';
		else if($t=='txt_address' || $t=='txt_address1') $txt = 'Address';
		else if($t=='txt_city') $txt = 'City';
		else if($t=='txt_country') $txt = 'Country';
		else if($t=='txt_zip') $txt = 'Zipcode';
		else if($t=='txt_phone') $txt = 'Phone Number';
		else if($t=='txt_phone1') $txt = 'Phone 1';
		else if($t=='txt_phone2') $txt = 'Phone 2';
		else if($t=='txt_bdate') $txt = 'Birthday';
		else if($t=='txt_gender') $txt = 'Gender';
		else if($t=='txt_maritalStatus') $txt = 'Marital Status';
		else if($t=='txt_spouse') $txt = 'Spouse';
		else if($t=='txt_dependents') $txt = 'Dependents';
		else if($t=='txt_sss') $txt = 'SSS';
		else if($t=='txt_tin') $txt = 'TIN';
		else if($t=='txt_philhealth') $txt = 'Philhealth';
		else if($t=='txt_hdmf') $txt = 'HDMF';
		else if($t=='txt_office') $txt = 'Office Branch';
		else if($t=='txt_staffHolidaySched') $txt = 'Holiday Schedule';
		else if($t=='txt_shift') $txt = 'Shift Sched';
		else if($t=='txt_startDate') $txt = 'Start Date';
		else if($t=='txt_idNum') $txt = 'Payroll ID';
		else if($t=='txt_supervisor') $txt = 'Supervisor';
		else if($t=='txt_department') $txt = 'Department';
		else if($t=='txt_grp') $txt = 'Group';
		else if($t=='txt_dept') $txt = 'Department';
		else if($t=='txt_title' || $t=='txt_position') $txt = 'Position Title';
		else if($t=='txt_skype') $txt = 'Skype Account';
		else if($t=='txt_google') $txt = 'Google Account';
		else if($t=='txt_endDate') $txt = 'Separation Date';
		else if($t=='txt_accessEndDate') $txt = 'Access End Date';		
		else if($t=='txt_fulltime') $txt = 'Full-Time';
		else if($t=='txt_empStatus') $txt = 'Employee Status';
		else if($t=='txt_regDate') $txt = 'Regularization Date';
		else if($t=='txt_separationDate') $txt = 'SeparationDate Date';
		else if($t=='txt_evalDate') $txt = 'Evaluation Date';
		else if($t=='txt_levelName' || $t=='txt_levelID_fk') $txt = 'Org Level';
		else if($t=='txt_coachedOf') $txt = 'Coached Of';
		else if($t=='txt_sal' || $t=='txt_salary') $txt = 'Salary';
		else if($t=='txt_active') $txt = 'Is Active';
		else if($t=='txt_leaveCredits') $txt = 'Leave Credits';
		else if($t=='txt_allowance') $txt = 'Monthly Allowance';
		else if($t=='txt_bankAccnt') $txt = 'Payroll Bank Account Number';
		else if($t=='txt_hmoNumber') $txt = 'HMO Policy Number';
		else if($t=='txt_terminationType') $txt = 'Termination Reason';
		else if($t=='txt_taxstatus') $txt = 'Tax Status';			
		else if($t=='txt_agencyID_fk') $txt = 'Agency';			
		
		return $txt;
	}
	
	function constantArr($a){
		$arr = array();
		
		if($a=='maritalStatus'){
			$arr = array(
					'Single' => 'Single',
					'Married' => 'Married',
					'Widowed' => 'Widowed',
					'Separated' => 'Separated',
					'Divorced' => 'Divorced'
				);
		}else if($a=='yesno'){
			$arr = array(
						'No' => 'No',
						'Yes' => 'Yes'
					);
		}else if($a=='yesno01'){
			$arr = array(
						'0' => 'No',
						'1' => 'Yes'
					);
		}else if($a=='active'){
			$arr = array(
						'0' => 'No',
						'1' => 'Yes'
					);
		}else if($a=='empStatus'){
			$arr = array(
						'contract' => 'Contract', 
						'probationary' => 'Probationary', 
						'regular' => 'Regular',
						'part-time' => 'Part-Time'
					);	
		}else if($a=='gender'){
			$arr = array(
						'M' => 'Male', 
						'F' => 'Female'
					);	
		}else if($a=='sanctionawol'){
			$arr = array(
						'1' => '1-4 Days Suspension', 
						'2' => '5-10 Days Suspension',
						'3' => 'Termination'
					);
		}else if($a=='sanctiontardiness'){
			$arr = array(
						'1' => 'Verbal Warning', 
						'2' => 'Written Warning',
						'3' => '1 - 4 Days Suspension',
						'4' => '5 - 10 Days Suspension',
						'5' => 'Termination'
					);
		}else if($a=='leaveType'){
			$arr = array(
						'1' => 'Vacation Leave',
						'2' => 'Sick Leave',
						'3' => 'Emergency Leave',
						'4' => 'Offsetting',
						'5' => 'Paternity Leave',
						'6' => 'Maternity Leave',
						'7' => 'Solo Parent Leave',
						'8' => 'Special Leave for Women'
					);
		}else if($a=='leaveStatus'){
			$arr = array(
						'0' => 'pending approval',
						'1' => 'approved w/ pay',
						'2' => 'approved w/o pay',
						'3' => 'disapproved',
						'4' => 'additional information required',
						'5' => 'deleted'
					);
		}else if($a=='noteType'){
			$arr = array(
						'other'=>0,
						'salary'=>1,
						'performance'=>2,
						'timeoff'=>3,
						'disciplinary'=>4,
						'actions'=>5
					);
		}else if($a=='office'){
			$arr = array(
						'PH-Cebu'=>'PH-Cebu',
						'US-OKC'=>'US-OKC'
					);
		}else if($a=='staffHolidaySched'){
			$arr = array(
						0=>'Philippine Holidays',
						1=>'US Holidays'
					);	
		}else if($a=='terminationType'){
			$arr = array(
						'0'=>'',
						'1'=>'Voluntary (Resignation)',
						'2'=>'Involuntary (Just Cause - AWOL)',
						'3'=>'Involuntary (End of Probationary Employment)',
						'4'=>'Involuntary (Just Cause)',
						'5'=>'Terminated for Just Cause: No Show'
					);
		}else if($a=='areaofimprovement'){
			$arr = array(
					0 => 'Work Quality/Productivity/Performance',
					1 => 'Attendance/Dependability',
					2 => 'Safety or Work Environment',
					3 => 'Conduct or Behavior (Interpersonal Skills)'
				);
		}else if($a=='coachingrecommendations'){
			$arr = array(
					0 => 'Eligible for Regularization',
					1 => 'End of Probationary Employment',
					2 => 'NTE for Poor Performance',
					3 => 'Eligible for continued employment',
					4 => 'Follow-up with another Coaching',
					5 => 'Extension of Probationary Period',
					6 => 'Recommended Transfer',
					7 => 'Other'
				);
		}else if($a=='taxstatus'){
			$arr = array(
					0 => '',
					1 => 'Single with No Dependents (S)',
					2 => 'Single with 1 Qualified Dependent (S1)',
					3 => 'Single with 2 Qualified Dependents (S2)',
					4 => 'Single with 3 Qualified Dependents (S3)',
					5 => 'Single with 4 Qualified Dependents (S4)',
					6 => 'Married',
					7 => 'Married with 1 Qualified Dependent (M1)',
					8 => 'Married with 2 Qualified Dependents (M2)',
					9 => 'Married with 3 Qualified Dependents (M3)',
					10 => 'Married with 4 Qualified Dependents (M4)'
				);
		}else if($a=='requiredTest'){
			$arr = array(
				'iq' => 'IQ Test',
				'typing' => 'Typing Test',
				'written' => 'Written Comprehension Test',
				'pmEmail' => 'PM Email Test',
				'pressRelease' => 'Press Release Writing Test',
				'design' => 'Design Test',
				'editing' => 'Copy Editing Test',
				'it' => 'IT Test',									
				'sales' => 'Sales Quiz',				
				'acqEmail' => 'Acquisitions Email Test',			
				'pcfTest' => 'PCF Test',		
				'editingTest' => 'Editing Test',			
				'sampleAudio' => 'Sample Audio Recording',		
				'illustrations' => 'Illustrations Test'			
			);
		}else if($a=='nteStat'){
			$arr = array(
				'0' => 'CAR Generated',		
				'1' => 'NTE Generated',		
				'2' => 'Cancelled',		
				'3' => 'Satisfactory'		
			);
		}else if($a=='schedType'){
			$arr = array(
				'0' => '',		
				'1' => 'First',		
				'2' => 'Second',		
				'3' => 'Third',		
				'4' => 'Fourth',		
				'5' => 'Last'		
			);
		}else if($a=='holidayTypes'){
			$arr = array(
				'0' => 'Regular Holiday',
				'1' => 'Regular PHL Holiday',
				'2' => 'Special PHL Holiday',
				'3' => 'US Holiday',
				'4' => 'Others'
			);
		}else if($a=='hrOptionStatus'){
			$arr = array(
				0 => 'None',
				1 => 'Coaching Form Printed',
				2 => 'Coaching Form Uploaded',
				3 => 'Evaluation Form Printed',
				4 => 'Evaluation Form Uploaded'
			);
		}else if($a=='hrOptionPending'){
			$arr = array(
				0 => 'Pending Coaching Form for Printing',
				1 => 'Pending Coaching Form for Upload',
				2 => 'Pending Evaluation Form for Printing',
				3 => 'Pending Evaluation Form for Upload',
				4 => 'Done'
			);
		}else if($a=='encText'){
			$arr = array('bankAccnt', 'hmoNumber', 'sss', 'tin', 'philhealth', 'hdmf', 'sal');
		}else if($a=='timeLogType'){
			$arr = array(
				'A' => 'Time In',
				'B' => 'Lunch Out',
				'C' => 'Lunch In',
				'D' => 'Break Out',
				'E' => 'Break In',
				'Z' => 'Time Out',
			);
		}else if($a=='weekdayArray'){
			$arr = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
		}else if($a=='weekShortArray'){
			$arr = array('sunday'=>'sun', 'monday'=>'mon', 'tuesday'=>'tue', 'wednesday'=>'wed', 'thursday'=>'thu', 'friday'=>'fri', 'saturday'=>'sat');
		}else if($a=='monthArray'){
			$arr = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		}
		
		return $arr;
	}
		
}

?>