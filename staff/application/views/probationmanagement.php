<h2>Probation Management</h2><hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Probationary Employees</li>
	<li class="tab-link" data-tab="tab-2">Pre-Employment Requirements (Regular Employees)</li>
</ul>
<br/>

<div id="tab-1" class="tab-content current">
	<table class="tableInfo datatable">
		<thead>
			<tr class="trhead">
				<td>Name of Employee</td>
				<td>Email Address</td>
				<td>Position Title</td>
				<td>Immediate Supervisor</td>
				<td>Start Date</td>
				<td>90th Day</td>
				<td>PER Status</td>
			</tr>
		</thead>
	<?php
		foreach($queryProbationary AS $qp){
			echo '<tr>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$qp->username.'/" target="_blank">'.$qp->name.'</a></td>';
				echo '<td>'.$qp->email.'</td>';
				echo '<td>'.$qp->title.'</td>';
				echo '<td>'.$qp->isName.'</td>';
				echo '<td>'.date('F d, Y', strtotime($qp->startDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($qp->startDate.' +90 days')).'</td>';
				echo '<td><a href="'.$this->config->base_url().'probperstatus/'.$qp->empID.'/" class="iframe">'.$qp->perStatus.'%</a></td>';
			echo '</tr>';
		}
	?>
	</table>
</div>

<div id="tab-2" class="tab-content">
	<table class="tableInfo datatable">
		<thead>
			<tr class="trhead">
				<td>Name of Employee</td>
				<td>Email Address</td>
				<td>Position Title</td>
				<td>Immediate Supervisor</td>
				<td>Start Date</td>
				<td>Tenure Age (Years)</td>
				<td>PER Status</td>
			</tr>
		</thead>
	<?php
		$dateToday = date('Y-m-d');
		foreach($queryRegular AS $qr){
			echo '<tr>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$qr->username.'/" target="_blank">'.$qr->name.'</a></td>';
				echo '<td>'.$qr->email.'</td>';
				echo '<td>'.$qr->title.'</td>';
				echo '<td>'.$qr->isName.'</td>';
				echo '<td>'.date('F d, Y', strtotime($qr->startDate)).'</td>';
				
				$years = strtotime($dateToday) - strtotime($qr->startDate);
				$years = $years / (365*60*60*24);
				
				echo '<td align="center">'.number_format($years, 2).'</td>';
				echo '<td><a href="'.$this->config->base_url().'probperstatus/'.$qr->empID.'/" class="iframe">'.$qr->perStatus.'%</a></td>';
			echo '</tr>';
		}
	?>
	</table>
</div>