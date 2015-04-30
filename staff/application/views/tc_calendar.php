<?php			
	$day_count = 0;
	$daynum = 1;
	$today = strtotime($today);
	$year = date('Y', $today);
	$month = date('m', $today);
	$day = date('d', $today);
	$first_day = date('w',mktime(0,0,0,$month, 1, $year));
	$days_in_month = cal_days_in_month(0, $month, $year);
	
?>
<table border=0 class="attendancetbl">					
	<?php
		$calendar = '<tr class="monthtd">';
			$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'timecard/'.$type.'/'.date('Y-m-d', strtotime('-1 year', $today)).'/\'">'.date('Y', strtotime('-1 year', $today)).'<br/> << </td>';
			for($d=1; $d<=12; $d++){
				$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'timecard/'.$type.'/'.date('Y-m-d', strtotime($year.'-'.$d.'-'.$day)).'/\'" class="'.(($d==date('m', $today))?'dtoday errortext':'').'">'.strtoupper(date('M', strtotime($year.'-'.$d))).'</td>';
			}
			$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'timecard/'.$type.'/'.date('Y-m-d', strtotime('+1 year', $today)).'/\'">'.date('Y', strtotime('+1 year', $today)).'<br/> >> </td>';
		$calendar .= '</tr>';

		$calendar .= '<tr>
				<td align="left" colspan=14>
					<b>'.strtoupper(date('F Y', $today)).'</b><br/><br/><i>';
					
		if($type=='timelogs') $calendar .= 'Click on a date to submit a request to resolve attendance.';
		else if($type=='attendance') $calendar .= 'Click on a date to view the names of employees with attendance issues.';
		
		$calendar .= '</i></td>
			</tr>'; 
			
		$calendar .= '<tr class="trlabel calendarweek">';
			$calendar .= '<td colspan=2>SUNDAY</td>';
			$calendar .= '<td colspan=2>MONDAY</td>';
			$calendar .= '<td colspan=2>TUESDAY</td>';
			$calendar .= '<td colspan=2>WEDNESDAY</td>';
			$calendar .= '<td colspan=2>THURSDAY</td>';
			$calendar .= '<td colspan=2>FRIDAY</td>';
			$calendar .= '<td colspan=2>SATURDAY</td>';
		$calendar .= '</tr>';


		
		$calendar .= "<tr class='weekdays'>";
		while ( $first_day > 0 ){
			$calendar .= "<td colspan=2></td>"; 
			$first_day = $first_day-1; 
			$day_count++;
		}
		
			
		while ( $daynum <= $days_in_month ){ 
			$calendar .= '<td colspan=2 class="'.(($daynum==date('d', $today))?'dtoday':'').'">
					<div class="daycontent">';
			
			if($type=='calendar' && $this->access->accessFull==true)
				$calendar .= '<a href="'.$this->config->base_url().'schedules/holidayevents/'.$month.'-'.$daynum.'/" class="iframe"><div class="daynum">'.$daynum.'</div></a>';
			else
				$calendar .= '<div class="daynum">'.$daynum.'</div>';
			
			echo $calendar; /**** ECHO CALENDAR ****/
			
			if($type=='calendar'){
				$calendardata['today'] = $today;
				$calendardata['year'] = $year;
				$calendardata['month'] = $month;
				$calendardata['daynum'] = $daynum;
				$calendardata['calHoliday'] = $calHoliday;
				$calendardata['calScheds'] = $calScheds;
				$calendardata['calSchedTime'] = $calSchedTime;
				$calendardata['calLeaves'] = $calLeaves;
				$this->load->view ('tc_calendarview', $calendardata);
			}
			
		
			
			$calendar = '</div></td>';
			
			$daynum++; 
			$day_count++;
			if ($day_count > 6){
				$calendar .= "</tr><tr class='weekdays'>";
				$day_count = 0;
			}
		} 
		
		while ( $day_count>0 && $day_count <=6 ){ 
			$calendar .= "<td colspan=2><br/></td>"; 
			$day_count++; 
		}
		
		echo $calendar; /**** ECHO CALENDAR ****/
	//} 
	?>	
</table>