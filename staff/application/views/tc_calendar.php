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
			$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'timecard/'.$type.'/'.date('Y-m-d', strtotime('-1 year', $today)).'/\'"><< '.date('Y', strtotime('-1 year', $today)).'</td>';
			for($d=1; $d<=12; $d++){
				$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'timecard/'.$type.'/'.date('Y-m-d', strtotime($year.'-'.$d.'-'.$day)).'/\'" class="'.(($d==date('m', $today))?'dtoday errortext':'').'">'.strtoupper(date('M', strtotime($year.'-'.$d))).'</td>';
			}
			$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'timecard/'.$type.'/'.date('Y-m-d', strtotime('+1 year', $today)).'/\'">'.date('Y', strtotime('+1 year', $today)).' >></td>';
		$calendar .= '</tr>';

		$calendar .= '<tr>
				<td align="left" colspan=14>
					<b>'.strtoupper(date('F Y', $today)).'</b><br/><br/><i>';
					
		if($type=='timelogs') $calendar .= 'Click on a date to submit a request to resolve attendance.';
		else if($type=='attendance') $calendar .= 'Click on a date to view the names of employees with attendance issues.';
		
		$calendar .= '</i></td>
			</tr>'; 
			
		$calendar .= '<tr class="trlabel">';
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
			
			$calendar .= '[';
				if($type=='calendar' && $this->access->accessFull==true) $calendar .= '<a href="" class="iframe" class="adaynum">'.$daynum.'</a>';
				else $calendar .= $daynum;
			$calendar .= ']';
			
			if($type=='calendar'){
				$calendar .= '<br/>';
				
				if($daynum<10) $myday = date('Y-m-d', strtotime($year.'-'.$month.'-0'.$daynum));
				else $myday = date('Y-m-d', strtotime($year.'-'.$month.'-'.$daynum));
				
				$caltxt = '';
				
				//custom schedules
				foreach($calScheds AS $cal){
					$xx = strtolower(date('l', strtotime($year.'-'.$month.'-'.$daynum)));
					
					if($cal->schedType!=0){ //first week
						if($cal->schedType==1) $fval = 'first';
						else if($cal->schedType==2) $fval = 'second';
						else if($cal->schedType==3) $fval = 'third';
						else if($cal->schedType==4) $fval = 'fourth';
						else $fval = 'last';
					
						if($cal->$xx !=0 && $myday==date('Y-m-d', strtotime($fval.' '.$xx.' of '.$year.'-'.$month)))
							$caltxt = $custTime[$cal->$xx];						
					}else{			
						if($myday >= $cal->shiftstart && ($myday <= $cal->shiftend || $cal->shiftend=='0000-00-00')){
							if($cal->$xx!=0)
								$caltxt = $custTime[$cal->$xx];
						}
					}
				}
				
				//custom time schedules
				foreach($calSchedTime AS $ctym){
					if($myday>=$ctym->shiftstart && $myday<=$ctym->shiftend)
						$caltxt = $ctym->timeValue;
				}
				
				$calendar .= $caltxt;
			}
			
		
			
			$calendar .= '</div></td>';
			
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
		
		
		
		echo $calendar;
	//} 
	?>	
</table>