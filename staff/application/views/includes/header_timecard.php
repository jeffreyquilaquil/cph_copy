<?php
	$this->load->view('includes/header_searchemployee');
	
	echo '<h2>';		
		if(!isset($row)) echo 'My Timecard and Payroll';
		else if(isset($row->fname)) echo $row->fname.'\'s Timecard and Payroll';
		else echo 'Timecard and Payroll';
	
	if( ($this->access->accessFullHRFinance == true OR $this->user->level > 0) AND (isset($report_attendance) AND $report_attendance == true) ){
		echo '<form name="frm_attendance_report" method="post" action="'.$this->config->base_url().'timecard/generate_attendance">
			<input type="hidden" name="report_start" value="'.date('Y-m-01', strtotime($report_start)).'" />
			<input type="hidden" name="report_end" value="'.date('Y-m-t', strtotime($report_end)).'" />
			<input type="hidden" name="visitID[]" value="'.$visitID.'" />
			<input type="submit" class="btnclass" name="attendance_report" value="Generate Attendance Report" />
		</form>';
	}


	if($this->access->accessFullHRFinance==true)
		echo ' <a href="'.$this->config->base_url().'timecard/'.$visitID.'/mypayrollsetting/" class="iframe"><button class="btnclass">'.((!isset($row) && $visitID==$this->user->empID)?'My ':'').'Payroll Settings</button></a>';
	echo '</h2>';
?>
<hr/>
<ul class="tabs2">
<?php
	if($visitID==$this->user->empID) $tnt='My ';
	else{
		$tnt = trim($row->fname);
		$tnt = strtok($tnt, ' ');
		
		if($this->access->accessFullHR && strlen($tnt)>6) 
			$tnt = substr($tnt,0,6).'..';
		
		$tnt .= '\'s ';
	}
	
	if(!isset($tpage)) $tpage = '';
	echo '<li class="tab-link '.(($tpage=='index' || $tpage=='timelogs')?'current':'').'" data-tab="timelogs">'.$tnt.'Time Logs</li> ';
	echo '<li class="tab-link '.(($tpage=='calendar')?'current':'').'" data-tab="calendar">'.$tnt.'Calendar</li> ';
	echo '<li class="tab-link '.(($tpage=='payslips')?'current':'').'" data-tab="payslips">'.$tnt.'Payslips</li> ';
	
	if($this->user->level>0 || $this->access->accessFullHRFinance==true)
		echo '<li class="tab-link admin '.(($tpage=='attendance')?'current':'').'" data-tab="attendance">Attendance</li> ';
	
	if($this->access->accessFullHR==true){		
		echo '<li class="tab-link admin '.(($tpage=='scheduling')?'current':'').'" data-tab="scheduling">Scheduling</li> ';
	}
	if($this->access->accessFullHRFinance==true){
		echo '<li class="tab-link admin '.(($tpage=='payrolls')?'current':'').'" data-tab="payrolls">Payrolls</li> ';
		//echo '<li class="tab-link admin '.(($tpage=='reports')?'current':'').'" data-tab="reports">Reports</li> ';
	}
?>	
</ul>


<script type="text/javascript">
	$(function(){
		$('.tab-link').click(function(){
			location.href="<?= $this->config->base_url().'timecard/'.(($visitID!='' && $visitID!=$this->user->empID)?$visitID.'/':'').''?>"+$(this).attr('data-tab')+"/";
		});
	});
</script>

<?php
	if(isset($column) && $column=='withLeft' && count($row)==0){ echo '<br/>No record for this employee.'; exit; }
?>