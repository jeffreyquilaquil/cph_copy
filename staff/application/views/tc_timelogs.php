<?php 
	$this->load->view('includes/header_timecard'); 	
	
	$today = date('Y-m-d');
	$dataArr['content'] = array(); //array of elements that will display on calendar template
?>

<table border=0 class="attendancetbl">
	<tr>
		<td>
	<?php
		if(!empty($schedToday)){
			echo 'Your schedule today ('.date('l, F d, Y').') is '.$schedToday.'.<br/>';
		}else{
			echo 'You are unscheduled today ('.date('l, F d, Y').')<br/>';
		}
		
		if(isset($timelog['timeIn'])){
			echo 'You clocked in at '.date('h:i a', strtotime($timelog['timeIn'])).'.';
			
			
			if($timelog['timeIn']>$schedTodayArr['start']){	//check if late						
				echo ' This is ';
				echo $this->txtM->convertTimeToMinHours(strtotime($timelog['timeIn']) - strtotime($schedTodayArr['start']));					
				echo '<span class="errortext"><b>LATE</b></span>.';				
			}else if($timelog['timeIn']<$schedTodayArr['start']){ //check if in early
				echo ' This is ';
				echo $this->txtM->convertTimeToMinHours(strtotime($schedTodayArr['start'] - strtotime($timelog['timeIn'])));					
				echo '<span class="errortext"><b>EARLY</b></span>.';		
			}
			
			echo '<br/>';
		}
		
		if(isset($timelog['timeIn']) && !isset($timelog['timeOut'])){
			echo '<br/><i class="colorgray">You would be leaving early by: '.$this->txtM->convertTimeToMinHours(strtotime($schedTodayArr['end']) - strtotime(date('Y-m-d H:i:s'))).'</i><br/>';
		
			echo '<tr><td><button class="btnclass">Take a Break</button></td></tr>';
		}
			
		foreach($calendarLogs AS $c){
			$num = ltrim(date('d',strtotime($c->logDate)), '0');
			$dataArr['content'][$num] = 'IN: '.date('h:i a', strtotime($c->timeIn)).'<br/>'.
										'OUT: '.date('h:i a', strtotime($c->timeOut));
			$breaks = explode('|', $c->breaks);
			$cntBreaks = count($breaks);
			if($cntBreaks>0){
				$dataArr['content'][$num] .= '<br/><br/>BREAKS: ';
				for($h=0; $h<$cntBreaks; $h++){
					
					if($h%2==0) $dataArr['content'][$num] .= '<br/>';
					else $dataArr['content'][$num] .= ' - ';
					
					$dataArr['content'][$num] .= date('h:i a', strtotime($breaks[$h]));
				}
			}			
		}
		
		
	?>
		<!--You clocked in at 07:14 AM. This is 14 minutes late.<br/>
		Breaks Taken: 45 minutes and 55 seconds.<br/> -->
		</td>
	</tr>
	
</table>
<?php 
	$this->load->view('tc_calendartemplate', $dataArr);
?>