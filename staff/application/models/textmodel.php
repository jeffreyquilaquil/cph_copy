<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Textmodel extends CI_Model {

    function __construct() {
        // Call the Model constructor
        parent::__construct();
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
		if(in_array($fld, $this->config->item('encText'))){
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
	
	function leaveTableDisplay($rQuery, $type, $datatable=false){
		$leaveTypeArr = $this->config->item('leaveType');
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
		
}

?>