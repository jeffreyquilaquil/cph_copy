<?php
	if(isset($row->fname)) echo '<h2>'.$row->fname.'\'s Timecard and Payroll</h2>';
	else echo '<h2>My Timecard and Payroll</h2>';
?>
<hr/>
<ul class="tabs2">
<?php
	if(!isset($row->empID) || $row->empID==$this->user->empID) echo '<li class="tab-link '.(($tpage=='index' || $tpage=='timelogs')?'current':'').'" data-tab="timelogs">Time Logs</li>';
	
	echo '<li class="tab-link '.(($tpage=='attendance')?'current':'').'" data-tab="attendance">Attendance</li> ';
	echo '<li class="tab-link '.(($tpage=='calendar')?'current':'').'" data-tab="calendar">Calendar</li> ';
	echo '<li class="tab-link '.(($tpage=='schedules')?'current':'').'" data-tab="schedules">Schedules</li> ';
	
	if($this->access->accessFullHR==true) echo '<li class="tab-link '.(($tpage=='scheduling')?'current':'').'" data-tab="scheduling">Scheduling</li> ';
	
	echo '<li class="tab-link '.(($tpage=='payslips')?'current':'').'" data-tab="payslips">Payslips</li> ';
	echo '<li class="tab-link '.(($tpage=='payrolls')?'current':'').'" data-tab="payrolls">Payrolls</li> ';
	echo '<li class="tab-link '.(($tpage=='reports')?'current':'').'" data-tab="reports">Reports</li> ';
?>	
</ul>

<script type="text/javascript">
	$(function(){
		$('.tab-link').click(function(){
			location.href="<?= $this->config->base_url().'timecard/'.(($visitID!='')?$visitID.'/':'').''?>"+$(this).attr('data-tab')+"/";
		});
	});
</script>

<?php
	if(isset($column) && $column=='withLeft' && count($row)==0){ echo '<br/>No record for this employee.'; exit; }
?>