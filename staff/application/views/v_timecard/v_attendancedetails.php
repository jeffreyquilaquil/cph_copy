<h3>Details of <?= date('l F d, Y', strtotime($today)) ?> <?= '<b class="errortext">Unpublished: '.count($queryUnPublished).'</b>' ?></h3>
<hr/>
<div class="displayinlineblock">
	<div style="width:48%; float:left;">
	<?php
		if(count($queryLate)>0){
			echo '<b>Late: ('.count($queryLate).')</b>';
			echo '<ul>';
				foreach($queryLate AS $late){
					echo '<li>';
						echo '<a href="'.$this->config->base_url().'timecard/'.$late->empID_fk.'/timelogs/" target="_blank">'.$late->name.'</a> <b style="color:red;">('.trim($this->textM->convertTimeToMinStr($late->hourLate)).')</b>';
						if($late->published==0 && $this->access->accessFullHR==true)
							echo ' <a href="'.$this->config->base_url().'timecard/'.$late->empID_fk.'/viewlogdetails/?d='.$late->logDate.'&back=attendancedetails">Resolve</a>';
					echo '</li>';
				}
			echo '</ul>';
		}
		
		if(count($queryOverBreak)>0){
			echo '<b>Over Break: ('.count($queryOverBreak).')</b>';
			echo '<ul>';
				$strOver = strtotime('01:30:00');
				foreach($queryOverBreak AS $over){
					$oHour = $this->textM->convertTimeToMinHours(strtotime($over->timeBreak) - $strOver, true);
					echo '<li>';
						echo '<a href="'.$this->config->base_url().'timecard/'.$over->empID_fk.'/timelogs/" target="_blank">'.$over->name.'</a> <b style="color:red;">('.trim($this->textM->convertTimeToMinStr($oHour)).')</b>';
						if($over->published==0 && $this->access->accessFullHR==true)
							echo ' <a href="'.$this->config->base_url().'timecard/'.$over->empID_fk.'/viewlogdetails/?d='.$over->logDate.'&back=attendancedetails">Resolve</a>';
					echo '</li>';
				}
			echo '</ul>'; 
		}
				
		if(count($queryEarlyClockOut)>0){	
			echo '<b>Early Clock Out: ('.count($queryEarlyClockOut).')</b>';
			echo '<ul>';
				foreach($queryEarlyClockOut AS $earlyout){
					echo '<li>';
						echo '<a href="'.$this->config->base_url().'timecard/'.$earlyout->empID_fk.'/timelogs/" target="_blank">'.$earlyout->name.'</a> <b style="color:red;">('.trim($this->textM->convertTimeToMinStr($earlyout->hourEarly)).')</b>';
						if($earlyout->published==0 && $this->access->accessFullHR==true)
							echo ' <a href="'.$this->config->base_url().'timecard/'.$earlyout->empID_fk.'/viewlogdetails/?d='.$earlyout->logDate.'&back=attendancedetails">Resolve</a>';
					echo '</li>';
				}
			echo '</ul>';
		}
		
		if(count($queryNoClockOut)>0){	
			echo '<b>No Clock Out: ('.count($queryNoClockOut).')</b>';
			echo '<ul>';
				foreach($queryNoClockOut AS $noout){
					echo '<li>';
						echo '<a href="'.$this->config->base_url().'timecard/'.$noout->empID_fk.'/timelogs/" target="_blank">'.$noout->name.'</a> In: '.date('h:i a', strtotime($noout->timeIn));
						if($noout->published==0 && $this->access->accessFullHR==true)
							echo ' <a href="'.$this->config->base_url().'timecard/'.$noout->empID_fk.'/viewlogdetails/?d='.$noout->logDate.'&back=attendancedetails">Resolve</a>';
					echo '</li>';
				}
			echo '</ul>';
		}
		
		if(count($queryAbsent)>0){
			echo '<b>Absent: ('.count($queryAbsent).')</b>';
			echo '<ul>';
				foreach($queryAbsent AS $absent){
					echo '<li><a href="'.$this->config->base_url().'timecard/'.$absent->empID_fk.'/timelogs/" target="_blank">'.$absent->name.'</a> ('.date('h:i a', strtotime($absent->schedIn)).' - '.date('h:i a', strtotime($absent->schedOut)).')</li>';
				}
			echo '</ul>';
		}
		
		if(count($queryUnPublished)>0){
			echo '<h3 class="errortext"><b>Unpublished: ('.count($queryUnPublished).')</b></h3>';
			echo '<ul>';
				foreach($queryUnPublished AS $unpublished){
					echo '<li><a href="'.$this->config->base_url().'timecard/'.$unpublished->empID_fk.'/viewlogdetails/?d='.$unpublished->logDate.'&back=attendancedetails">'.$unpublished->name.'</a></li>';
				}
			echo '</ul>';
		}
	?>
	</div>


	<div style="width:48%; float:left;">
	<?php		
		if(count($queryEarlyBird)>0){		
			echo '<b>Early Birds: ('.count($queryEarlyBird).')</b>';
			echo '<ul>';
				foreach($queryEarlyBird AS $early){
					echo '<li><a href="'.$this->config->base_url().'timecard/'.$early->empID_fk.'/timelogs/" target="_blank">'.$early->name.'</a> ('.trim($this->textM->convertTimeToMinStr($early->hourEarly)).')</li>';
				}
			echo '</ul>';
		}
		
		if(count($queryLeave)>0){
			echo '<b>On-Leave: ('.count($queryLeave).')</b>';
			echo '<ul>';
				foreach($queryLeave AS $leave){
					echo '<li><a href="'.$this->config->base_url().'staffleaves/'.$leave->leaveID.'/">'.$leave->name.'</a> ('.date('h:i a', strtotime($leave->leaveStart)).' - '.date('h:i a', strtotime($leave->leaveEnd)).')</li>';
				}
			echo '</ul>';
		}
		
		if(count($queryOffset)>0){
			echo '<b>On Offset: ('.count($queryOffset).')</b>';
			echo '<ul>';			
				foreach($queryOffset AS $offset){
					$off = explode('|', $offset->offsetdates);
					foreach($off AS $o){
						if(!empty($o)){
							list($star, $en) = explode(',', $o);
							if(date('Y-m-d', strtotime($star))==$today){
								echo '<li><a href="'.$this->config->base_url().'staffleaves/'.$offset->leaveID.'/">'.$offset->name.'</a> ('.date('h:i a', strtotime($star)).' - '.date('h:i a', strtotime($en)).')</li>';
							}
						}
					}
				}
			echo '</ul>';
		}

		if($today==$currentDate && count($queryInProgress)>0){
			echo '<b>Shift In Progress: ('.count($queryInProgress).')</b>';
			echo '<ul>';
				foreach($queryInProgress AS $inprogress){
					echo '<li><a href="'.$this->config->base_url().'timecard/'.$inprogress->empID_fk.'/timelogs/" target="_blank">'.$inprogress->name.'</a> In: '.date('h:i a', strtotime($inprogress->timeIn)).'</li>';
				}
			echo '</ul>';
		}
	?>
	</div>
</div>