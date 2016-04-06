<h2>My Timecard and Payroll</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Time Logs</li>
	<li class="tab-link" data-tab="tab-2">Attendance</li>
	<li class="tab-link" data-tab="tab-3">Calendar</li>
	<li class="tab-link" data-tab="tab-4">Scheduling</li>
	<li class="tab-link" data-tab="tab-5">Payslips</li>
	<li class="tab-link" data-tab="tab-6">Payrolls</li>
	<li class="tab-link" data-tab="tab-7">Reports</li>
</ul>
<div id="tab-1" class="tab-content current">
	<table border=0 class="attendancetbl">
		<tr><td colspan="14">
		<?php	
			if(isset($shiftstart) && isset($shiftend)){
				echo 'Your schedule today ('.date('l, F d, Y').') is '.date('h:i a', strtotime($shiftstart)).' - '.date('h:i a', strtotime($shiftend)).'.<br/>';
			}else{
				echo 'You are unscheduled today ('.date('l, F d, Y').').<br/>';
			}
			
			if(count($clockedin)!=0){
				echo 'You clocked in at '.date('h:i A',strtotime($clockedin->clockin)).'. ';
				if(isset($shiftstart) && isset($shiftend)){
					$start = date('Y-m-d H:i:s', strtotime($shiftstart));
					$clockin = date('Y-m-d H:i:s', strtotime($clockedin->clockin));
					
					if($start>$clockin){
						$seconds = strtotime($start) - strtotime($clockin);	
						$earlylate = 'early';
					}else{
						$seconds = strtotime($clockin) - strtotime($start);
						$earlylate = 'late';
					}	

					$days    = floor($seconds / 86400);
					$hours   = floor(($seconds - ($days * 86400)) / 3600);
					$minutes = floor(($seconds - ($days * 86400) - ($hours * 3600))/60);
					$seconds = floor(($seconds - ($days * 86400) - ($hours * 3600) - ($minutes*60)));
					echo 'This is ';
					if($hours>0)
						echo $hours.' hours ';
					if($minutes>0)
						echo $minutes.' minutes ';
					if($seconds>0)
						echo $seconds.' seconds ';	
					
					echo $earlylate.'.<br/>';
				}		
			}
		?>
		</td></tr>		
		<?php if(count($clockedin)==0){ ?>
		<tr><td colspan="14">
			<iframe src="<?= $this->config->base_url() ?>includes/timeclock/index.html" width="370" height="340" frameborder=0 scrolling="no"></iframe>
		</td></tr>
		<?php } ?>
		<tr>
			<td colspan=7><button>Clock Out</button></td>
			<td colspan=7><button>Take a Break</button></td>
		</tr>			
		<?php
			$day_count = 1;
			$daynum = 1;
			$today = strtotime($today);
			$year = date('Y', $today);
			$month = date('m', $today);
			$day = date('d', $today);
			$first_day = date('N',mktime(0,0,0,$month, 1, $year)); 
			$days_in_month = cal_days_in_month(0, $month, $year); 

			$calendar = '<tr class="monthtd">';
				$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'myattendance/'.date('Y-m-d', strtotime('-1 year', $today)).'/\'"><< '.date('Y', strtotime('-1 year', $today)).'</td>';
				for($d=1; $d<=12; $d++){
					$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'myattendance/'.date('Y-m-d', strtotime($year.'-'.$d.'-'.$day)).'/\'" class="'.(($d==date('m', $today))?'dtoday errortext':'').'">'.strtoupper(date('M', strtotime($year.'-'.$d))).'</td>';
				}
				$calendar .= '<td onClick="location.href=\''.$this->config->base_url().'myattendance/'.date('Y-m-d', strtotime('+1 year', $today)).'/\'">'.date('Y', strtotime('+1 year', $today)).' >></td>';
			$calendar .= '</tr>';

			$calendar .= '<tr>
					<td align="left" colspan=14>
						<b>'.strtoupper(date('F Y', $today)).'</b><br/><br/>
						<i>Click on a date to submit a request to resolve attendance.</i>
					</td>
				</tr>';
				
			$calendar .= '<tr class="weekname">';
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
			
			//this is the process to display calendar contents
			$aArr = array();
			foreach($myattendance AS $a){
				$aArr[date('d', strtotime($a->clockin))] = array(
					'clockin' => $a->clockin,
					'clockout' => $a->clockout,
					'breaks' => $a->breaks
				);
			}

			
			while ( $daynum <= $days_in_month ){ 
				$calendar .= '<td colspan=2 class="'.(($daynum==date('d', $today))?'dtoday':'').'">
					<a href="" class="iframe" class="adaynum"><div class="daynum '.(($daynum==date('d', $today))?'errortext':'').'">&nbsp;&nbsp;&nbsp;'.$daynum.'</div></a>
					<div class="daycontent">';
										
					if(isset($aArr[$daynum])){
						$txt = '';
						$errtxt = '';
						$arr = $aArr[$daynum];
						if( $daynum==date('d', $today) && $arr['clockout']=='0000-00-00 00:00:00' && date('H:s')<date('H:s', strtotime($shiftend))){
							$errtxt .= 'Shift in Progress';
						}else{
							$txt .= '<span class="colorgray">In:</span> '.date('h:i', strtotime($arr['clockin'])).'<br/>';
						if($arr['breaks']!=''){
							$br = explode(',',$arr['breaks']);
							$cntMuscle = count($br);
							for($b=0; $b<$cntMuscle;$b++){
								$bx = explode('|', $br[$b]);
								if(isset($bx[0])) $txt .= '<span class="colorgray">BrkIn:</span> '.$bx[0].'<br/>';
								if(isset($bx[1])) $txt .= '<span class="colorgray">BrkOut:</span> '.$bx[1].'<br/>';
							}
						}							
							if($arr['clockout']=='0000-00-00 00:00:00')
								$errtxt .= 'No Clock Out<br/>';
							else
								$txt .= '<span class="colorgray">Out:</span> '.date('H:i', strtotime($arr['clockout']));
						}
						if($errtxt!='') $calendar .= '<span class="errortext">'.$errtxt.'</span>';
						$calendar .= $txt;
					}
					
				$calendar .='</div>
				</td>'; 
				
				$daynum++; 
				$day_count++;
				if ($day_count > 7){
					$calendar .= "</tr><tr class='weekdays'>";
					$day_count = 1;
				}
			} 

			while ( $day_count >1 && $day_count <=7 ){ 
				$calendar .= "<td colspan=2><br/></td>"; 
				$day_count++; 
			}
			
			
			
			echo $calendar;
		//} 
		?>	
		</table>		
	

</div> <? //end of tab-1 ?>