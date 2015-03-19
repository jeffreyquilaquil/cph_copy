<h2>Staff Coaching</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link" data-tab="tab-1">In Progress <?= ((count($inprogress)>0)?'('.count($inprogress).')':'') ?></li>
	<li class="tab-link current" data-tab="tab-2">Pending Evaluations <?= ((count($pending)>0)?'('.count($pending).')':'')?></li>
	<li class="tab-link" data-tab="tab-3">Done <?= ((count($done)>0)?'('.count($done).')':'')?></li>
</ul>
<div id="tab-1" class="tab-content">	
<table class="tableInfo">
	<tr class="trhead">
		<td>Employee's Name</td>
		<td>Date Generated</td>
		<td>Coaching Period</td>
		<td>Coaching Start</td>
		<td>Evaluation Date</td>
		<td>Immediate Supervisor</td>
		<td>Details</td>
	</tr>
<?php
	if(count($inprogress)==0){
		echo '<tr><td colspan=3>No pending requests.</td></tr>';
	}else{	
		foreach($inprogress AS $i):
			echo '<tr>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$i->username.'/">'.$i->name.'</a></td>';
				echo '<td>'.date('F d, Y', strtotime($i->dateGenerated)).'</td>';
				echo '<td>'.$i->coachedPeriod.'</td>';
				echo '<td>'.date('F d, Y', strtotime($i->coachedDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($i->coachedEval)).'</td>';
				echo '<td>'.$i->coachedBy.'</td>';
				echo '<td><a class="iframe" href="'.$this->config->base_url().'coachingform/acknowledgment/'.$i->coachID.'/">
					<img src="'.$this->config->base_url().'css/images/view-icon.png">
					</a></td>';			
			echo '</tr>';
		endforeach;
	}
?>
	
</table>
</div>

<div id="tab-2" class="tab-content current">	
<table class="tableInfo">
	<tr class="trhead">
		<td>Employee's Name</td>
		<td>Date Generated</td>
		<td>Coaching Period</td>
		<td>Coaching Start</td>
		<td>Evaluation Date</td>
		<td>Immediate Supervisor</td>
		<td>Status</td>
		<td>Coaching Form</td>
		<td>Details</td>
	</tr>
<?php
	if(count($pending)==0){
		echo '<tr><td colspan=3>No pending coaching for evaluation.</td></tr>';
	}else{	
		foreach($pending AS $p):
			echo '<tr>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$p->username.'/">'.$p->name.'</a></td>';
				echo '<td>'.date('F d, Y', strtotime($p->dateGenerated)).'</td>';
				echo '<td>'.$p->coachedPeriod.'</td>';
				echo '<td>'.date('F d, Y', strtotime($p->coachedDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($p->coachedEval)).'</td>';
				echo '<td>'.$p->coachedBy.'</td>';
				echo '<td>'.$this->staffM->coachingStatus($p).'</td>';
				echo '<td align="center">
					<a href="'.$this->config->base_url().'coachingform/expectation/'.$p->coachID.'/" class="iframe">
						<img src="'.$this->config->base_url().'css/images/pdf-icon.png">
					</a>
				</td>';
				echo '<td><a class="iframe" href="'.$this->config->base_url().'coachingform/acknowledgment/'.$p->coachID.'/">
					<img src="'.$this->config->base_url().'css/images/view-icon.png">
					</a></td>';			
			echo '</tr>';
		endforeach;
	}
?>
	
</table>
</div>



<div id="tab-3" class="tab-content">	
<table class="tableInfo">
	<tr class="trhead">
		<td>Employee's Name</td>
		<td>Date Generated</td>
		<td>Coaching Period</td>
		<td>Coaching Start</td>
		<td>Evaluation Date</td>
		<td>Immediate Supervisor</td>
		<td>Status</td>
		<td>Coaching Form</td>
		<td>Evaluation Form</td>
		<td>Details</td>
	</tr>
<?php
	if(count($done)==0){
		echo '<tr><td colspan=3>No coaching form.</td></tr>';
	}else{	
		foreach($done AS $d):
			echo '<tr>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$d->username.'/">'.$d->name.'</a></td>';
				echo '<td>'.date('F d, Y', strtotime($d->dateGenerated)).'</td>';
				echo '<td>'.$d->coachedPeriod.'</td>';
				echo '<td>'.date('F d, Y', strtotime($d->coachedDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($d->coachedEval)).'</td>';
				echo '<td>'.$d->coachedBy.'</td>';
				echo '<td>'.$this->staffM->coachingStatus($d).'</td>';
				echo '<td align="center">
					<a href="'.$this->config->base_url().'coachingform/expectation/'.$d->coachID.'/" class="iframe">
						<img src="'.$this->config->base_url().'css/images/pdf-icon.png">
					</a>
				</td>';
				echo '<td align="center">
					<a href="'.$this->config->base_url().'coachingform/evaluation/'.$d->coachID.'/" class="iframe">
						<img src="'.$this->config->base_url().'css/images/pdf-icon.png">
					</a>
				</td>';
				echo '<td><a class="iframe" href="'.$this->config->base_url().'coachingform/acknowledgment/'.$d->coachID.'/">
					<img src="'.$this->config->base_url().'css/images/view-icon.png">
					</a></td>';			
			echo '</tr>';
		endforeach;
	}
?>
	
</table>
</div>

