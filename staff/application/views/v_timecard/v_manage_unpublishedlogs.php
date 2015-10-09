<h2>Manage Timecard - Unpublished Logs</h2>
<hr/>
<?php
	if(count($dataUnpublished)==0){
		echo '<p>No unpublish logs.</p>';
	}else{
		$pubByDate = array();
		foreach($dataUnpublished AS $d){
			$pubByDate[$d->logDate][] = $d;
		}
		
		foreach($pubByDate AS $d8=>$dbyd){
			echo '<table class="tableInfo">';
				echo '<tr class="trlabel"><td colspan=4>'.date('l, F d, Y', strtotime($d8)).' ('.count($dbyd).')</td></tr>';
				echo '<tr class="trhead">
						<td width="30%">Name</td>
						<td width="30%">Schedule</td>
						<td width="30%">Time In/Out</td>
						<td>Details</td>
					</tr>';
				foreach($dbyd AS $d){
					$sched = '';
					if($d->schedIn=='0000-00-00 00:00:00' && $d->schedOut=='0000-00-00 00:00:00') $sched = 'None';
					else $sched = date('h:i a', strtotime($d->schedIn)).' - '.date('h:i a', strtotime($d->schedOut));
					
					$timeOut = '';
					if($d->timeIn=='0000-00-00 00:00:00' && $d->timeOut=='0000-00-00 00:00:00') $timeOut = 'None';
					else{
						if($d->timeIn!='0000-00-00 00:00:00') $timeOut =  date('h:i a', strtotime($d->timeIn));
						if($d->timeOut!='0000-00-00 00:00:00') $timeOut .=  ' - '.date('h:i a', strtotime($d->timeOut));
						else $timeOut .= ' - <span class="errortext">NO TIME OUT</span>';
					}					
					
					echo '<tr>';
						echo '<td><a href="'.$this->config->base_url().'timecard/'.$d->empID_fk.'/?d='.$d->logDate.'" target="_blank">'.$d->name.'</a></td>';
						echo '<td>'.$sched.'</td>';							
						echo '<td>'.$timeOut.'</td>';
						echo '<td><a href="'.$this->config->base_url().'timecard/'.$d->empID_fk.'/viewlogdetails/?d='.$d->logDate.'" class="iframe"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>';							
					echo '</tr>';
				}
			echo '</table>';
		}
		
	}
?>

