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
				echo '<tr class="trlabel"><td colspan=4>'.date('F d, Y', strtotime($d8)).' ('.count($dbyd).')</td></tr>';
				echo '<tr class="trhead">
						<td width="30%">Name</td>
						<td width="30%">Schedule</td>
						<td width="30%">Time In/Out</td>
						<td>Details</td>
					</tr>';
				foreach($dbyd AS $d){
					echo '<tr>
							<td><a href="'.$this->config->base_url().'timecard/'.$d->empID_fk.'/?d='.$d->logDate.'" target="_blank">'.$d->name.'</a></td>
							<td>'.date('h:i a', strtotime($d->schedIn)).' - '.date('h:i a', strtotime($d->schedOut)).'</td>
							<td>'.date('h:i a', strtotime($d->timeIn)).' - '.date('h:i a', strtotime($d->timeOut)).'</td>
							<td><a href="'.$this->config->base_url().'timecard/'.$d->empID_fk.'/viewlogdetails/?d='.$d->logDate.'" class="iframe"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
						</tr>';
				}
			echo '</table>';
		}
		
	}
?>

