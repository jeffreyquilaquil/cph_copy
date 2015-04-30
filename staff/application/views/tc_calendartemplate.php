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
	<tr class="monthtd">
		<td onClick="location.href='<?= $this->config->base_url().'timecard/'.$tpage.'/'.date('Y-m-d', strtotime('-1 year', $today)).'/' ?>'"><?= date('Y', strtotime('-1 year', $today)) ?><br/> << </td>
	<?php
			for($d=1; $d<=12; $d++){
				echo '<td onClick="location.href=\''.$this->config->base_url().'timecard/'.$tpage.'/'.date('Y-m-d', strtotime($year.'-'.$d.'-'.$day)).'/\'" class="'.(($d==date('m', $today))?'dtoday errortext':'').'">'.strtoupper(date('M', strtotime($year.'-'.$d))).'</td>';
			}
	?>	
		<td onClick="location.href='<?= $this->config->base_url().'timecard/'.$tpage.'/'.date('Y-m-d', strtotime('+1 year', $today)).'/' ?>"><?= date('Y', strtotime('+1 year', $today)) ?><br/> >> </td>
	</tr>
	<tr>
		<td align="left" colspan=14>
			<b><?= strtoupper(date('F Y', $today)) ?></b><br/><br/>
		<?php
			if($tpage=='timelogs') echo '<i>Click on a date to submit a request to resolve attendance.</i>';
			else if($tpage=='attendance') echo '<i>Click on a date to view the names of employees with attendance issues.</i>';
		?>
		</td>
	</tr>
	<tr class="trlabel calendarweek">
		<td colspan=2>SUNDAY</td>
		<td colspan=2>MONDAY</td>
		<td colspan=2>TUESDAY</td>
		<td colspan=2>WEDNESDAY</td>
		<td colspan=2>THURSDAY</td>
		<td colspan=2>FRIDAY</td>
		<td colspan=2>SATURDAY</td>
	</tr>
		
	<tr class="weekdays">
	<?php		
		while ( $first_day > 0 ){
			echo "<td colspan=2></td>"; 
			$first_day = $first_day-1; 
			$day_count++;
		}
					
		while ( $daynum <= $days_in_month ){ 
			echo '<td colspan=2 class="'.(($daynum==date('d', $today))?'dtoday':'').'">
					<div class="daycontent">';
			
			if($tpage=='calendar' && $this->access->accessFull==true)
				echo '<a href="'.$this->config->base_url().'schedules/holidayevents/'.$month.'-'.$daynum.'/" class="iframe"><div class="daynum">'.$daynum.'</div></a>';
			else
				echo '<div class="daynum">'.$daynum.'</div>';
				
			//print the content of the day found in page view file
			if(isset($content[$daynum])) echo $content[$daynum];
			
			echo '</div></td>';
			
			$daynum++; 
			$day_count++;
			if ($day_count > 6){
				echo "</tr><tr class='weekdays'>";
				$day_count = 0;
			}
		} 
		
		while ( $day_count>0 && $day_count <=6 ){ 
			echo "<td colspan=2><br/></td>"; 
			$day_count++; 
		}
	?>	
	</tr>
</table>