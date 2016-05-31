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
	
	function formfield($type, $name='', $val='', $class='', $placeholder='', $additional='', $opSelect=''){
		$t = '';
		
		if($type=='select'){
			$t = '<select name="'.$name.'" class="'.$class.'" '.$additional.'>'.$val.'</select>';
		}else if($type=='selectoption'){
			$t = '<select name="'.$name.'" class="'.$class.'" '.$additional.'>';
				if(!empty($placeholder)) $t .= '<option value="">'.$placeholder.'</option>';
				
				foreach($opSelect AS $k=>$p){
					$t .= '<option value="'.$k.'" '.(($k==$val)?'selected="selected"':'').'>'.$p.'</option>';
				}
			$t .= '</select>';
		}else if($type=='textarea'){
			$t = '<textarea name="'.$name.'" class="'.$class.'" '.$additional.' placeholder="'.$placeholder.'">'.$val.'</textarea>';
		}else{
			$t = '<input type="'.$type.'" name="'.$name.'" class="'.$class.'" value="'.$val.'" placeholder="'.$placeholder.'" '.$additional.'/>';
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
		return number_format((double)str_replace(',','',$n),2);
	}

	public function numFormat($i){
		return number_format( $i, 2, '.', ',');
	}
	
	function convertTimeToMinHours($tdiff, $numOnly=false){
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
		
		if($numOnly===true) return sprintf("%02d", $hours).':'.sprintf("%02d", $minutes).':'.sprintf("%02d", $seconds);
		else return $tText;
	}
	
	function convertTimeToYMD($diff){
		$tText = '';		
		
		$years = floor($diff / (365*60*60*24));
		$months = floor(($diff-$years * 365*60*60*24) / ((30*60*60*24)));
		$days = floor(($diff - $years * 365*60*60*24 - $months * 30*60*60*24) / (60*60*24));
		
		
		if($years>0 && $years==1) $tText .= $years.' year ';
		else if($years>0 && $years>1) $tText .= $years.' years ';

		if($months==1) $tText .= $months.' month ';
		else if($months>1) $tText .= $months.' months ';
		
		if($days==1) $tText .= $days.' day ';
		else if($days>1) $tText .= $days.' days ';
		

		return $tText;
	}
	
	function convertTimeToStr($time){
		$basetime = $this->convertTimeToSec($time);
		return $this->convertTimeToMinHours($basetime);
	}
	
	//accepts HH:MM:SS
	function convertTimeToSec($time){
		list($hours,$mins,$secs) = explode(':',$time);
		$seconds = mktime($hours,$mins,$secs) - mktime(0,0,0);
		return $seconds;
	}
	
	function convertTimeToMinStr($time){
		$str = '';
		$ex = explode(':', $time);
		$ex = array_reverse($ex);
		if(isset($ex[2]) && $ex[2]!='00') $str .= ltrim($ex[2],'0').' hr ';
		if(isset($ex[1]) && $ex[1]!='00') $str .= ltrim($ex[1],'0').' min ';
		if(isset($ex[0]) && $ex[0]!='00') $str .= ltrim($ex[0],'0').' sec ';		
		
		if(empty($str)) $str = '0';
		return $str;		
	}
	
	
	function ordinal($num){
		if( ($num / 10) % 10 != 1 )
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
					<tr>
						<th>Employee\'s Name</th>
						<th>Date Filed</th>
						<th>Effective Date</th>
						<th>Changes<br/>
							<hr/>
							<div style="width:100%; color:#555555; font-style:italic;">
								<span style="float:left;">Type</span>
								<span style="text-align:center;">'.(($status==3)?'Previous':'Current').' Info</span>
								<span style="float:right;">New Info</span>
							</div>
						</th>
						<th>Immediate Supervisor</th>
						<th>Prepared By</th>
						<th>Updated By</th>
						<th><br/></th>
						<th><br/></th>
					</tr>
				</thead>
			';
		if(count($info)>0){
			foreach($info AS $a):
				$c = json_decode(stripslashes($a->changes));
				$cnt = 0;
				$arr = array();
				if(isset($c->staffHolidaySched)){
					$arr[$cnt][0] = 'Holiday Schedule'; $arr[$cnt][1] = $c->staffHolidaySched->c; $arr[$cnt][2] = $c->staffHolidaySched->n;
					$cnt++;
				}
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
				$disp .= '<td>'.$a->updatedby.'</td>';
				
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
	
	function getLeaveStatusText($status, $iscancelled, $isrefiled){
		$leaveStatusArr = $this->textM->constantArr('leaveStatus');
		$statusText = ucfirst($leaveStatusArr[$status]); 
		
		if($iscancelled==1 && $status==0)
			$statusText = 'CANCELLED';
		else if($iscancelled==1)
			$statusText .= ' - <i>CANCELLED</i>';
		else if($iscancelled==2 )
			$statusText .= ' - <i>Pending Cancel Approval</i>'; 
		else if($iscancelled==3)
			$statusText .= ' - <i>Pending HR Cancel Approval</i>'; 
		else if($iscancelled==4)
			$statusText = 'Additional Information Required';
		
		if($isrefiled==1 && $status==0)
			$statusText = 'REFILED';
		else if($isrefiled==1)
			$statusText .= ' - <i>REFILED</i>';
		else if($isrefiled==2 )
			$statusText .= ' - <i>Pending Refiling Approval</i>'; 
		else if($isrefiled==3 || $isrefiled==3)
			$statusText .= ' - <i>Pending HR Refiling Approval</i>';
		
		return $statusText;
	}
	
	function getLeaveMaternityStatusText($status){
		$statusArr = $this->textM->constantArr('statusMaternityLeave');
		return $statusArr[$status];
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
					<td>'.$row->hrName.'</td>';
			
			if($row->matStatus>0) $disp .= '<td>'.$this->textM->getLeaveMaternityStatusText($row->matStatus).'</td>';
			else $disp .= '<td>'.$this->textM->getLeaveStatusText($row->status, $row->iscancelled, $row->isrefiled).'</td>';
			
			$disp .= '<td><a class="iframe" href="'.$this->config->base_url().'staffleaves/'.$row->leaveID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
				</tr>
			';
		}
		$disp .= '</table>
				</div>';
				
		return $disp;
	}
	
	//medicine reimbursement views
	function reimbursementTableDisplay($data_query, $status, $hide = false, $datatable = true){
		
		$status_labels = $this->textM->constantArr('medrequest');
		$id = uniqid();
		$hideArr = array('pending_med', 'pending_accounting', 'all');
		$disp = '<div id="tbl'.$status.'"';
		if( $hide == true ){
			$disp .= ' class="hidden" ';
		}
		$disp .= '">
				<table class="tableInfo fs11px" id="tbl_'.$id.'">
				<thead>
					<tr>
						<th>Employee Name</th>
						<th>Prescription Date</th>
						<th>Submission Date</th>
						<th>Requested Amount</th>
						<th>Status</th>												
						<th>File</th> 
					</tr>
				</thead>
				<tbody>
				';
						
		foreach($data_query AS $row){	
			$status = '';
			$status = (in_array($row->status_accounting, array(2, 4) ) )? $status_labels[ $row->status_accounting ] : $status_labels[ $row->status ];
			$disp .= '<tr>
					<td><a href="'.$this->config->base_url().'staffinfo/'.$row->username.'/">'.$row->lname.' '.$row->fname.'</a></td>
					<td>'.date('d M y h:i a', strtotime($row->prescription_date) ).'</td>
					<td>'.date('d M y h:i a', strtotime($row->date_submitted) ).'</td>
					<td>'.$row->requested_amount.'</td>
					<td>'.$status.'</td>
					<td><a class="iframe" href="'.$this->config->base_url().'medrequest/'.$row->medrequestID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
				</tr>
			';
		}
		$disp .= '</tbody></table>
				</div>';
		
		if( $datatable == true ){
			$disp .= '<script>
				$(function(){
					$("table#tbl_'.$id.'").dataTable();					
				});
			</script>';
		}

		
		return $disp;
	}
	
	//kudos table
	public function kudosRequestTableDisplay($data, $hide = false, $all = FALSE, $tableClass = '', $display = TRUE){
		$hiddenClass = (!$display)? "hidden": '';
		$displayTable = "<div class = 'table".$tableClass." $hiddenClass'>
			<table class='datatable tableInfo fs11px'>
				<thead>
					<tr><th>Requests</th><th>Name of Employee</th><th>Name of Requestor</th><th>Reason for KBR</th><th>Amount</th><th>Date Submitted</th><th>Status</th></tr>
				</thead>";

		$displayTable .= "<tbody>";
		foreach ($data as $key => $value) {
			$show = TRUE;
			if($all && $hide && $value->kudosReceiverSupID != $this->user->empID ){	
				if( !$this->access->accessFull && !$this->access->accessFinance ){
					$show = FALSE;
				}
			}
			
			if( $show ){
				$displayTable .= "<tr>
						<td>".$value->kudosRequestID."</td>
						<td>".$value->staffName."</td>
						<td>".$value->requestorName."</td>
						<td>".$value->kudosReason."</td>
						<td>".$value->kudosAmount."</td>
						<td>".$value->dateRequested."</td>";
					if(!$display)
						$displayTable .= "<td>$value->statusName</td>";
					else{
						$displayTable .= "<td><a href='".$this->config->base_url()."kudosrequest/evaluation/".$value->kudosRequestID."/".$value->kudosRequestStatus."' class='iframe'>".$value->statusName."</a></td>
					
						</tr>
					";
				}
			}
		}
		$displayTable .= '</tbody>';
		$displayTable .= "</table></div>";

		return $displayTable;
	}
	
	
	//assign $time to empty if you dont want to call customTimeArrayByCat function. Please call customTimeArrayByCat first if this is loop
	function customTimeSelect($val='', $fvalue=''){
		$time = $this->commonM->customTimeArrayByCat();		
		$valentine = '<option value="">'.$fvalue.'</option>';
		
		foreach($time AS $t=>$t2):
			$valentine .= '<optgroup label="'.$t2['name'].'">';						
			foreach($t2 AS $k=>$t):
				if($k!='name'){
					$ex = explode('|', $t);
					$valentine .= '<option data-id="'.$ex[3].'" data-time="'.$ex[2].'" value="'.$ex[0].'" '.(($k==$val)?'selected="selected"':'').'>'.$ex[0].'</option>';
				}
			endforeach;
			$valentine .= '</optgroup>';
		endforeach;	
		
		return $valentine;
	}
	
	
	function constantText($t, $special = ''){
		$txt = '';
		if($t=='txt_fname') $txt = 'First Name';
		else if($t=='txt_lname') $txt = 'Last Name';
		else if($t=='txt_mname') $txt = 'Middle Name';
		else if($t=='txt_suffix') $txt = 'Name Suffix';
		else if($t=='txt_username') $txt = 'Username';
		else if($t=='txt_email') $txt = 'Email';
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
		else if($t=='txt_maiden_name') $txt = 'Maiden Name';
		else if($t=='txt_spouse') $txt = 'Spouse';
		else if($t=='txt_dependents') $txt = 'Dependents';
		else if($t=='txt_sss') $txt = 'SSS';
		else if($t=='txt_tin') $txt = 'TIN';
		else if($t=='txt_philhealth') $txt = 'Philhealth';
		else if($t=='txt_hdmf') $txt = 'HDMF';
		else if($t=='txt_office') $txt = 'Office Branch';
		else if($t=='txt_staffHolidaySched') $txt = 'Holiday Schedule';
		else if($t=='txt_shift') $txt = 'Shift Sched';
		else if($t=='txt_shiftSched') $txt = 'Shift Sched for Inventory';
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
		else if($t=='timeOut') $txt = 'Time Out';			
		else if($t=='timeIn') $txt = 'Time In';			
		else if($t=='breakIn') $txt = 'Break In';			
		else if($t=='breakOut') $txt = 'Break Out';			
		else if($t=='txt_emergency_person') $txt = 'Emergency Contact Person';
		else if($t=='txt_emergency_number') $txt = 'Emergency Contact Number';
		else if($t=='txt_emergency_address') $txt = 'Emergency Contact Address';
		elseif($special != ''){
			$txt = str_replace("ñ", 'N', $special);
			//echo $txt;
		}
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
		}else if($a=='shiftSched'){
			$arr = array(
						0=>'Morning Shift',
						1=>'Night Shift'
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
				'illustrations' => 'Illustrations Test',
				'acquisitionassistantemailtest' => 'Acquisition Assistant Email Test'				
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
				'0' => 'Ordinary',
				'1' => 'Regular PHL Holiday',
				'2' => 'Special PHL Holiday',
				'3' => 'US Holiday',
				'4' => 'Regular Holiday'
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
		}else if($a=='timeLogTypeText'){
			$arr = array(
				'timeIn' => 'Time In',
				'lunchOut' => 'Lunch Out',
				'lunchIn' => 'Lunch In',
				'breakOut' => 'Break Out',
				'breakIn' => 'Break In',
				'timeOut' => 'Time Out',
			);
		}else if($a=='weekdayArray'){
			$arr = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
		}else if($a=='weekShortArray'){
			$arr = array('sunday'=>'sun', 'monday'=>'mon', 'tuesday'=>'tue', 'wednesday'=>'wed', 'thursday'=>'thu', 'friday'=>'fri', 'saturday'=>'sat');
		}else if($a=='monthArray'){
			$arr = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		}else if( $a == 'monthFullArray' ){
			$arr = array(1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December');			
		}else if( $a == 'yearFullArray' ){
			for( $year = 2014; $year <= 2017; $year++ ){
				$arr[ $year ] = $year;
			}		
		}else if($a=='statusMaternityLeave'){
			$arr = array(0=>'', 1=>'Requested to Shorten Leave', 2=>'HR Approved - Pending Supervisor\'s Approval', 3=>'HR Disapproved', 4=>'SHORTENED', 5=>'Disapproved by Supervisor',6=>'SHORTENED - PayrollHero Updated');
		}else if($a=='recommendations'){
			$arr = array(
						1 => 'Eligible for regularization',
						2 => 'Eligible for continued employment',
						3 => 'End of probationary period',
						4 => 'Eligible for promotion',
						5 => 'Eligible for salary adjustment',
						6 => 'Extension of probationary period',
						7 => 'Recommended for Performance Management',
						8 => 'Recommended for Coaching',
						9 => 'Others (please specify)'
					);
		}else if($a=='evaluationTextHeaders'){
			$arr = array(
					'dedicationToExcellence' => 'Dedication to Excellence',
					'proactiveness' => 'Proactiveness',
					'teamwork' => 'Teamwork (Demonstrates ability to work with others)',
					'communication' => 'Communication',
					'reliability' => 'Reliability',
					'professionalism' => 'Professionalism',
					'flexibility' => 'Flexibility to Change and Continuous Improvement'
				);
		}else if($a=='incidentRepStatus'){
			$arr = array(
					0 => 'Cancelled',
					1 => 'New',
					2 => 'Printed',
					3 => 'Signed incident report uploaded',
					4 => 'NTE printed',
					5 => 'Signed NTE uploaded',
					6 => 'Explanation Received',
					7 => 'Signed explanation uploaded',
					8 => 'CAR and NOD printed',
					9 => 'Signed CAR and NOD uploaded',
					10 => 'DONE'
				);
		}else if($a=='payrollItemType'){
			$arr = array(
				0 => 'pay',
				1 => 'allowance',
				2 => 'bonus',
				3 => 'deduction',
				4 => 'incentive',
				5 => 'adjustment (+)',				
				6 => 'adjustment (-)',				
				7 => 'deduction other'				
			);
			
			////incentives are taxable income added to gross pay
			////bonus are non-taxable added to net pay
			/// adjustment non-taxable either add or minus added/subtracted after net pay
		}else if($a=='managePayOptions'){
			$arr = array('reviewattendance'=>'Review Attendance', 
						'generatepayslip'=>'Generate Payslip',
						'addpayslipitem'=>'Add Payslip Item',
						'generate13thmonth'=>'Generate 13th Month'
					);
		}else if($a=='payrollType' || $a=='payPeriod'){
			$arr = array('once'=>'Once', 
						'semi'=>'Semi-Monthly', 
						'monthly'=>'Monthly',
						'per payroll'=>'Per Payroll');
		}else if($a=='payCategory'){
			$arr = array('pay', 'adjustment', 'advance', 'allowance', 'benefit', 'bonus', 'deduction', 'vacation pay');
		}else if($a=='payAmountOptions'){
			$arr = array('specific amount'=>'specific amount',
							'basePay'=>'Base Pay', 
							'hourly'=>'Hourly Rate', 
							'sssTable'=>'SSS Table', 
							'taxTable'=>'Tax Table', 
							'philhealthTable'=>'Philhealth Table',
							'taken'=>'Regular Hours Taken',
							'overtime'=>'Over Time (30%)',
							'nightdiff'=>'Night Diff (10%)',							
							'NDspecial'=>'Night Diff (10%) x Special Holiday (30%)',
							'NDregular'=>'Night Diff (10%) x Regular Holiday (100%)',
							'specialHoliday'=>'Special Holiday (30%)',
							'regularHoliday'=>'Regular Holiday (100%)'
						);
		}else if($a=='payGoingTo'){
			$arr = array('base'=>'base', 'gross'=>'gross', 'taxable'=>'taxable', 'net'=>'net');
		}else if($a=='payrollStatusArr'){
			$arr = array('Generated', 'Published', 'Finalized');
		}else if($a=='writtenWarningStatus'){
			$arr = array(
					'1'=>'New',
					'2'=>'Revision submitted by immediate sup',
					'3'=>'Employee responded',
					'4'=>'Deliberate',
					'5'=>'Employee Response NO MERIT. Pending upload signed written warning (IS).',
					'6'=>'Written Warning VOID. Pending upload signed written warning (IS).',
					'7'=>'Signed written warning on file (uploaded).'
				);
		}else if($a == 'medrequest'){
			$arr = array(
					'0' => 'Waiting on approval from medical personnel',
					'1' => 'Approved by medical personnel',
					'2' => 'Approved by accounting',
					'3' => 'Disapproved by medical personnel',
					'4' => 'Disapproved by accounting',
					'5' => 'Cancelled'
			);
		}else if($a == 'hdmf_loan_purpose'){
			$arr = array(
				1 => 'Minor home improvement/home renovation/upgrades', 
				2 => 'Livelihood/additional capital in small business', 
				3 => 'Tuition/education expense', 
				4 => 'Health and wellness', 
				5 => 'Purchase of appliance and furniture/electronic gadgets', 
				6 => 'Bill/credit card payment', 
				7 => 'Vacation/travel', 
				8 => 'Special events', 
				9 => 'Car repair', 
				10 => 'Balance transfer/debt consolidation', 
				11 => 'Other needs');
		} else if( $a == 'hdmf_loan_status' ){
			$arr = array('for printing', 'printed', 'endorsed to employee', 'approved loans', 'for salary deductions', 'done');

		}
		elseif($a == 'allowances'){
			$arr = array('Medicine Reimbursement','Clothing Allowance','Laundry Allowance','Meal Allowance','Medical Cash Allowance', 'Pro-Rated Allowance','Rice Allowance','Training Allowance','Performance Bonus','Kudos Bonus','Discrepancy on Previous Bonus','Vacation Pay');
		}
		
		return $arr;
	}
	
	public function constantValues($type){
		$val = '';
		
		if($type=='datetimepattern')
			$val = '(?:19|20)[0-9]{2}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])-(?:30))|(?:(?:0[13578]|1[02])-31)) (0[0-9]|1[0-9]|2[0-3])(:[0-5][0-9]){2}';
		
		return $val;
	}
	
	//$filename is $_FILES['uploadfilename']['name']
	public function getFileExtn($filename){
		return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	}
	
	public function getEvaluationText($type){
		$arr = array();
		
		if($type=='dedicationToExcellence'){
			$arr = array(
					'This employee displays a positive, "can-do" attitude and he/she doesn\'t stop at "NO."',
					'This employee is resourceful and is able to find means to work around challenges if only to get the work done on time.',
					'This employee is creative – he/she develops multiple options to "get the job done."',
					'This employee demonstrates good industry and technical knowledge in the field or work that he is in and is able to apply this knowledge effectively in his work.',
					'This employee is performance-conscious and makes sure he/she doesn’t do the same mistake twice.'
				);
		}else if($type=='proactiveness'){
			$arr = array(
					'This employee promptly communicates to immediate supervisor whenever he/she sees that there is an issue in a project or in a process.',
					'This employee points out and raises to immediate supervisor or appropriate authority matters that he/she thinks affects his/her productivity and that of his/her team mates, and offers solutions or possible courses of action (rather than just merely pointing it out)'
				);
		}else if($type=='teamwork'){
			$arr = array(
					'This employee pitches in as an active team member;',
					'This employee respects the opinions of others.',
					'This employee develops trust and credibility with team members; settles problems without alienating team members.'
				);
		}else if($type=='communication'){
			$arr = array(
					'This employee communicates professionally in written and verbal communications. Communicates in a way that is sensitive to the recipient and is not offensive (sensitivity is seen in choice of words and tone of voice.)',
					'This employee has ability to communicate effectively, clearly, concisely, appropriately with peers, management, subordinates and customers.',
					'This employee balances talking and listening.'
				);
		}else if($type=='reliability'){
			$arr = array(
					'This employee demonstrates ability to identify, formulate, and solve problems independently.',
					'This employee shows genuine concern to his/her productivity and that of the team in general by finding solutions to issues that come up and does not wait on somebody else to fix the problem.',
					'This employee shows effort to understand the process thoroughly by asking questions and by doing so he/she is able to work without a need for close supervision.',
					'(Timeliness) This employee prioritizes tasks to meet schedules and deadlines. Completes assignments on time.'
				);
		}else if($type=='professionalism'){
			$arr = array(
					'This employee demonstrates an understanding of professional and ethical responsibilities. (e.g., complies with all company policies)',
					'This employee thinks rationally and is able to make effective and reasonable decisions despite of any difficult personal circumstances.',
					'This employee maintains a good attendance record. Can be relied on to come to work regularly. Manages the use of time off credits well. Comes to work on time and returns to work from breaks as scheduled.'
				);
		}else if($type=='flexibility'){
			$arr = array(
					'This employee accepts change and the need for lifelong learning: Views change as an opportunity to improve performance and productivity;',
					'This employee asks questions whenever there are changes in processes in the department and company wide, and seeks full understanding of the rationale behind these changes.',
					'This employee addresses his/her concerns about the changes via correct channels (through immediate supervisor or through HR).',
					'This employee is able to cope up and eventually work as effectively after a change in processes or work circumstances.',
					'This employee does not react negatively and defensively to any change that is introduced and never displays rebellious behavior.',
				);
		}
		
		return $arr;
	}
	
	//if empType==0 that's self evaluation else Supervisor
	public function evaluationTable($empType=0, $arrType=1, $row=''){
		$text = '';
		$arrConstant = $this->textM->constantArr('evaluationTextHeaders');
		
		if($arrType==1) $dream = array('dedicationToExcellence', 'proactiveness', 'teamwork');
		else if($arrType==2) $dream = array('communication', 'reliability', 'professionalism');
		else $dream = array('flexibility');
		
		$text .= '<table class="tableInfo">';
		foreach($dream AS $a){			
			$text .= '<tr class="trhead">';
				$text .= '<td width="70%">'.$arrConstant[$a].'</td>';
				if($empType==1) 
					$text .= '<td align="center">Employee\'s Rating</td>';				
				$text .= '<td align="center">Your Rating</td>';
			$text .= '</tr>';
			
			$sige = 0;
			$team = $this->textM->getEvaluationText($a);
			if($empType==1) $haha = $row->$a;
			foreach($team AS $t){
				$text .= '<tr>';
					$text .= '<td>'.$t.'</td>';
					if($empType==1){
						$text .= '<td>'.$this->textM->formfield('text', '', ((isset($haha[$sige]))?$haha[$sige]:''), 'forminput tacenter', '', 'disabled="disabled"').'</td>';
					}
					$text .= '<td>'.$this->textM->formfield('number', $a.'[]', '', 'forminput tacenter', '', 'min="1" max="10" required').'</td>';
				$text .= '</tr>';
				$sige++;
			}
		}
		
		$text .= '</table>';
		
		return $text;
	}
	
	public function getScoreMatrix($score){
		$sc = '';
		if($score>=8)
			$sc = 'Excellent (Exceeds expectations)';
		else if($score>=5 && $score<8)
			$sc = 'Good (Meets expectations).';
		else if($score>=3 && $score<5)
			$sc = 'Fair (Improvement needed).';
		else
			$sc = 'Poor (Unsatisfactory)';
			
		return $sc;
	}
	
	public function getAlias(){
		$arr = array('Harry Potter', 'Tyrion Lannister', 'Cersei Lannister', 'Daenerys Targaryen', 'Jon Snow', 'Sansa Stark', 'Arya Stark', 'Robb Stark', 'Draco Malfoy', 'Hermione Granger', 'Ronald Weasley', 'Lord Voldemort', 'Albus Dumbledore', 'Severus Snape', 'Ginny Weasley', 'Luna Lovegood', 'Rubeus Hagrid', 'Remus Lupin', 'Neville Longbottom', 'Sirius Black', 'Bran Stark', 'Khal Drogo', 'Petyr Baelish', 'Joffrey Baratheon', 'Sandor Clegane', 'Lord Varys');
		
		$k = array_rand($arr);
		return $arr[$k];
	}
	
	public function displayAttAdditional($say){
		$hello = '<td>';
			if($say->publishBy!="") $hello .= 'Published';
			else if($this->access->accessFullHR==true) $hello .= '<a href="'.$this->config->base_url().'timecard/'.$say->empID_fk.'/viewlogdetails/?d='.$say->slogDate.'&back=attendancedetails"><button>Resolve</button></a>';
			else $hello .= 'Unpublished';
		$hello .= '</td>';				
		$hello .= '<td align="right"><a href="'.$this->config->base_url().'timecard/'.$say->empID_fk.'/viewlogdetails/?d='.$say->slogDate.'&back=attendancedetails"><button>View log details</button></a></td>';
		
		return $hello;
	}
	
	
	public function displayPaymentItems($dataItems, $empID=''){
		$bye = '';
		if(count($dataItems)>0){
			$catArray = $this->textM->constantArr('payCategory');
			$arrPeriod = $this->textM->constantArr('payPeriod');

			$bye .= '<tr class="trhead">
						<td>Name</td>
						<td>Type</td>
						<td>Category</td>						
						<td>Amount</td>
						<td align="center">Period</td>
						<td>Status</td>
						<td><br/></td>
					</tr>';
								
			foreach($dataItems AS $item){
				$bye .= '<tr '.(($item->status==0)?'style="background-color:#ccc; color:#fff;"':'').'>';	
					$bye .= '<td>'.$item->payName.' ('.$item->payCDto.')</td>';
					$bye .= '<td>'.$item->payType.' '.(($item->isMain==0)?'(custom)':'').'</td>';
					$bye .= '<td>'.$catArray[$item->payCategory].'</td>';					
					$bye .= '<td>';
						if($item->prevAmount=='hourly') $bye .= $item->payAmount.' hours';
						else if(is_numeric(str_replace(',','',$item->payAmount))) $bye .= $this->textM->convertNumFormat($item->payAmount);
						else $bye .= 'computed';
					$bye .= '</td>';
					$bye .= '<td align="center">'.$arrPeriod[$item->payPeriod];
							if($item->payStart!='0000-00-00'){
								if($item->payStart==$item->payEnd) $bye .= '<br/>('.$item->payStart.')';
								else $bye .= '<br/>('.$item->payStart.' to '.$item->payEnd.')';
							}
					$bye .= '</td>';
					$bye .= '<td>'.(($item->status==0)?'Inactive':'Active').'</td>';
					$bye .= '<td>';
						if($this->access->accessFullHRFinance==true){
							if(!empty($empID)){
								$hrefV = $this->config->base_url().'timecard/'.$empID.'/manangepaymentitem/?pageType=empUpdate'.(($item->isMain==1)?'&payID='.$item->payID:'&staffPayID='.$item->payID);
							}else $hrefV = $this->config->base_url().'timecard/manangepaymentitem/?pageType=updateItem&payID='.$item->payID;
							
							$bye .= '<a href="'.$hrefV.'" class="iframe"><img width="25px" src="'.$this->config->base_url().'css/images/icon-options-edit.png"></a>';
						}
					$bye .= '</td>';				
				$bye .= '</tr>';
			}
		}
		
		return $bye;
	}
	
	
	function getRandText($num){
		$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$charNum = strlen($chars)-1;
		$txt = "";
		for ($i = 0; $i < $num; $i++) {
			$txt .= $chars[mt_rand(0, $charNum)];
		}
		
		return $txt;
	}
	
	
	function convert_number_to_words($number) { 
    
		$hyphen      = '-'; 
		$conjunction = ' and ';
		$separator   = ', ';
		$negative    = 'negative ';
		$decimal     = ' pesos and ';
		$dictionary  = array(
			0                   => 'zero',
			1                   => 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
			10                  => 'ten',
			11                  => 'eleven',
			12                  => 'twelve',
			13                  => 'thirteen',
			14                  => 'fourteen',
			15                  => 'fifteen',
			16                  => 'sixteen',
			17                  => 'seventeen',
			18                  => 'eighteen',
			19                  => 'nineteen',
			20                  => 'twenty',
			30                  => 'thirty',
			40                  => 'fourty',
			50                  => 'fifty',
			60                  => 'sixty',
			70                  => 'seventy',
			80                  => 'eighty',
			90                  => 'ninety',
			100                 => 'hundred',
			1000                => 'thousand',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		);
		
		if (!is_numeric($number)) {
			return false;
		}
		
		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . $this->convert_number_to_words(abs($number));
		}
		
		$string = $fraction = null;
		
		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}
		
		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . $this->convert_number_to_words($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= $this->convert_number_to_words($remainder);
				}
				break;
		}
		
		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$string .= $this->convert_number_to_words($fraction);
			$string .= ' centavos';
			/*$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);*/
		}
		
		return $string;
	}

	//render table universal	
	public function renderTable( $headers, $col_options, $data_query, $data_table = true, $display = true ){

		$table = '';
		$dataTable = ( $data_table == true ) ? 'class="datatable"' : '';
		$table .= '<table '.$dataTable.' style="width: 100%;">';
		//thead
		$table .= '<thead><tr>';
			foreach( $headers as $val ){
				$table .= '<td>'.ucwords( $val ).'</td>'; 
			}
		$table .= '</tr></thead>';
		//tbody
		$table .= '<tbody>';
		if( isset($data_query) AND !empty($data_query) ){
			unset($data_query['headers']);
			foreach( $data_query as $val ){
				$table .= '<tr>';
				foreach( $headers as $val_ ){
					$table .= '<td>'.$val[ $val_ ].'</td>'; 		
				}				
				$table .= '</tr>';
			}
		}
			
		$table .= '</tbody>';

		$table .= '</table>';

		if( $display == true ){
			echo $table;
		} else {
			return $table;
		}
	}

		
} //end class