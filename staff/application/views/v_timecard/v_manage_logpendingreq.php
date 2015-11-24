<?php $this->load->view('includes/header_searchemployee'); ?>

<h2>Manage Timecard - Timelog Pending Requests</h2>
<hr/>
<?php
	if(count($timelogRequests)==0){
		echo '<p class="padding5px">No pending request.</p>';
	}else{ ?>
	<table class="tableInfo">
		<tr class="trhead">
			<td width="20%">Employee Name</td>
			<td width="10%">Log Date</td>
			<td width="15%">Date Requested</td>
			<td width="40%">Message</td>
			<td><br/></td>
		</tr>
	<?php
		foreach($timelogRequests AS $tr){
			echo '<tr>';
				echo '<td><a href="'.$this->config->base_url().'timecard/'.$tr->empID_fk.'/timelogs/">'.$tr->name.'</a></td>';
				echo '<td>'.date('d M y', strtotime($tr->logDate)).'</td>';
				echo '<td>'.$tr->dateRequested.'</td>';
				echo '<td>'.nl2br($tr->message).'</td>';
				echo '<td><a href="'.$this->config->base_url().'timecard/'.$tr->empID_fk.'/viewlogdetails/?d='.$tr->logDate.'" class="iframe"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"></a></td>';
			echo '</tr>';
		}
	?>
	</table>	
<?php
	}
?>

