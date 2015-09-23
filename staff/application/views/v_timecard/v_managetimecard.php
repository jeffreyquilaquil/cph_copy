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
			<td>Employee Name</td>
			<td>Log Date</td>
			<td><br/></td>
		</tr>
	<?php
		foreach($timelogRequests AS $tr){
			echo '<tr>';
				echo '<td>'.$tr->name.'</td>';
				echo '<td>'.date('F d, Y', strtotime($tr->logDate)).'</td>';
				echo '<td><a href="'.$this->config->base_url().'timecard/'.$tr->empID_fk.'/timelogs/?d='.$tr->logDate.'" target="_blank">Visit Page</a></td>';
			echo '</tr>';
		}
	?>
	</table>	
<?php
	}
?>
	
</div>