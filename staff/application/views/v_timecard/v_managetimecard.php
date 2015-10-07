<h2>Manage Timecard</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-unpublished">Unpublished Logs (<?= count($dataUnpublished) ?>)</li>
	<li class="tab-link" data-tab="tab-pending">Timelogs Pending Request (<?= count($timelogRequests) ?>)</li>
</ul>

<!--------------- START OF UNPUBLISHED LOGS------------------------->
<div id="tab-unpublished" class="tab-content current">	
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
				echo '<tr class="trlabel"><td>'.date('F d, Y', strtotime($d8)).' ('.count($dbyd).')</td></tr>';
				echo '<tr><td><ul>';
					foreach($dbyd AS $d){
						echo '<li><a href="'.$this->config->base_url().'timecard/'.$d->empID_fk.'/viewlogdetails/?d='.$d->logDate.'" class="iframe">'.$d->name.'</a></li>';
					}
				echo '</ul></td></tr>';
			echo '</table>';
		}
		
	}
?>
</div>

<!--------------- START OF TIMELOGS PENDING REQUESTS------------------------->
<div id="tab-pending" class="tab-content">	
<?php
	if(count($timelogRequests)==0){
		echo '<p class="padding5px">No pending request.</p>';
	}else{ ?>
	<table class="tableInfo">
		<tr class="trhead">
			<td width="15%">Employee Name</td>
			<td width="10%">Log Date</td>
			<td width="15%">Date Requested</td>
			<td width="50%">Message</td>
			<td><br/></td>
		</tr>
	<?php
		foreach($timelogRequests AS $tr){
			echo '<tr>';
				echo '<td>'.$tr->name.'</td>';
				echo '<td>'.date('d M y', strtotime($tr->logDate)).'</td>';
				echo '<td>'.date('d M y h:a a', strtotime($tr->dateRequested)).'</td>';
				echo '<td>'.nl2br($tr->message).'</td>';
				echo '<td><a href="'.$this->config->base_url().'timecard/'.$tr->empID_fk.'/viewlogdetails/?d='.$tr->logDate.'" class="iframe"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"></a></td>';
			echo '</tr>';
		}
	?>
	</table>	
<?php
	}
?>
</div>

