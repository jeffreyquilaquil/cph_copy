<h2>Manage Timecard</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Timelogs Pending Request (<?= count($timelogRequests) ?>)</li>
</ul>

<div id="tab-1" class="tab-content current">	
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