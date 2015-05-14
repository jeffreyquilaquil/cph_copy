<?php 
	$this->load->view('includes/header_timecard'); 	
	
	$curHour = date('Y-m-d H:i:s');
	foreach($logsArr AS $k=>$logs){
		$ltext = '<div class="daysched" style="background-color:#dc6900;"><b>IN:</b> '.date('h:i a', strtotime($logs['in'])).'</div>';
		
		if($logs['out']!='0000-00-00 00:00:00') 
			$ltext .= '<div class="daysched" style="background-color:#dc6900;"><b>OUT:</b> '.date('h:i a', strtotime($logs['out'])).'</div>';
		
		if($logs['out']=='0000-00-00 00:00:00'){
			
			if($logs['currentDate']==$today && isset($start) && isset($end) && $curHour>=$start && $curHour<=$end)
				$ltext .= '<div class="daysched" style="background-color:#007f00;"><b>IN PROGRESS</div>';
			else if($logs['currentDate']!=$today || ($logs['currentDate']==$today && isset($start) && isset($end) && $curHour>$end))
				$ltext .= '<div class="daysched" style="background-color:#ff0000;"><b>MISSING CLOCK OUT</div>';
		}
			
		
		$logsArr[$k] = $ltext;
	}
	$dataArr['content'] = $logsArr;
?>

<table border=0 class="attendancetbl">
	<tr><td colspan=2>
	<?php
		if(strpos($schedToday, 'Day Off') !== false){
			echo '<b>'.strtoupper($schedToday).'</b><br/>';
		}else{
			if(empty($schedToday)) echo 'You are UNSCHEDULED today ('.date('l, F d, Y').').<br/>';
			else{
				echo 'Your schedule today ('.date('l, F d, Y').') is '.strtoupper($schedToday).'.<br/>';				
			}
		}
	?>
		
		<!--You clocked in at 07:14 AM. This is 14 minutes late.<br/>
		Breaks Taken: 45 minutes and 55 seconds.<br/>
		You would be leaving early by : 2 hours 3 minutes<br/>-->
	</td></tr>
	<tr>
		<td><button class="padding5px">Clock Out</button></td>
		<td><button class="padding5px">Take a Break</button></td>
	</tr>
</table>
<?php 
	$this->load->view('tc_calendartemplate', $dataArr);
?>