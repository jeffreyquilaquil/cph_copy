<h2>My Timecard and Payroll</h2>
<hr/>
<ul class="tabs2">
	<li class="tab-link <?= (($tpage=='index' || $tpage=='timelogs')?'current':'') ?>" data-tab="timelogs">Time Logs</li>
	<li class="tab-link <?= (($tpage=='attendance')?'current':'') ?>" data-tab="attendance">Attendance</li>
	<li class="tab-link <?= (($tpage=='calendar')?'current':'') ?>" data-tab="calendar">Calendar</li>
	<li class="tab-link <?= (($tpage=='scheduling')?'current':'') ?>" data-tab="scheduling">Scheduling</li>
	<li class="tab-link <?= (($tpage=='payslips')?'current':'') ?>" data-tab="payslips">Payslips</li>
	<li class="tab-link <?= (($tpage=='payrolls')?'current':'') ?>" data-tab="payrolls">Payrolls</li>
	<li class="tab-link <?= (($tpage=='reports')?'current':'') ?>" data-tab="reports">Reports</li>
</ul>

<script type="text/javascript">
	$(function(){
		$('.tab-link').click(function(){
			location.href="<?= $this->config->base_url().'timecard/'?>"+$(this).attr('data-tab')+"/";
		});
	});
</script>