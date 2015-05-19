<?php 
	$this->load->view('includes/header_timecard'); 	
	
	$dataArr['content'] = array();
?>

<table border=0 class="attendancetbl">
	<tr><td colspan=2>
		Your schedule today (<?= date('l, F d, Y') ?>) is 07:00AM - 04:00PM.<br/>
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