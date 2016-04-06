<h2>HR Incident Reports</h2>
<hr/>
<?php
	if(count($reportData)==0){
		echo 'No violation reported.';
	}else{ ?>
		<table class="tableInfo">
			<tr class="trlabel">
				<td>Case #</td>
				<td>Employee</td>
				<td>Alias</td>
				<td>Date Reported</td>
				<td>Status</td>
				<td><br/></td>
				<td><br/></td>
			</tr>
	<?php
		foreach($reportData AS $r){
			echo '<tr '.(($r->status==10)?'bgcolor="#a1a1a1"':'').'>';
				echo '<td>'.sprintf('%04d', $r->reportID).'</td>';
				echo '<td>'.$r->name.'</td>';
				echo '<td>'.((!empty($r->alias))?$r->alias:'None').'</td>';
				echo '<td>'.date('d M Y h:i a', strtotime($r->dateSubmitted)).'</td>';
				echo '<td><b>'.$reportStatus[$r->status].'</b></td>';
				echo '<td><a href="'.$this->config->base_url().'incidentreportaction/action/'.$r->reportID.'/" class="iframe">Quick Action</a></td>';
				echo '<td width="10%"><a href="'.$this->config->base_url().'incidentreportaction/details/'.$r->reportID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"></a></td>';
			echo '</tr>';
		}
	?>
		</table>
<?php	
	}
?>