<?php $this->load->view('includes/header_timecard'); ?>

<table border=0 class="attendancetbl">
	<tr><td colspan=2>
		Your schedule today (Friday, December 5, 2014) is 7:00 AM – 4:00 PM.<br/>
		You clocked in at 07:14 AM. This is 14 minutes late.<br/>
		Breaks Taken: 45 minutes and 55 seconds.<br/>
		You would be leaving early by : 2 hours 3 minutes<br/>
	</td></tr>
	<tr>
		<td><button class="padding5px">Clock Out</button></td>
		<td><button class="padding5px">Take a Break</button></td>
	</tr>
</table>
<?php 
	$this->load->view('tc_calendartemplate', $dataArr);
?>