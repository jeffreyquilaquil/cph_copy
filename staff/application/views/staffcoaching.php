<h2>Staff Coaching</h2>
<hr/>
<ul class="tabs">
<?php	
	if($this->access->accessFullHR==true){
		echo '<li class="tab-link current" data-tab="tab-1">Pending for HR '.((count($forprinting)>0)?'('.count($forprinting).')':'').'</li>';
	}
?>
	<li class="tab-link <?= (($this->access->accessFullHR==false)?'current':'') ?>" data-tab="tab-2">In Progress <?= ((count($inprogress)>0)?'('.count($inprogress).')':'') ?></li>
	<li class="tab-link" data-tab="tab-3">Pending Evaluations <?= ((count($pending)>0)?'('.count($pending).')':'')?></li>
	<li class="tab-link" data-tab="tab-4">Done <?= ((count($done)>0)?'('.count($done).')':'')?></li>
	<li class="tab-link" data-tab="tab-5">Cancelled <?= ((count($cancelled)>0)?'('.count($cancelled).')':'')?></li>
</ul>

<br/>
<div id="tab-1" class="tab-content <?= (($this->access->accessFullHR==false)?'hidden':'current') ?>">	
<table class="tableInfo datatable">
<thead>
	<tr class="trhead">
		<th>Coaching ID</th>
		<th>Employee's Name</th>
		<th>Date Generated</th>
		<th>Coaching Period</th>
		<th>Coaching Start</th>
		<th>Evaluation Date</th>
		<th>Immediate Supervisor</th>		
		<th>Status</th>
		<th></th>
	</tr>
</thead>
<?php
	$hrOptionsPending = $this->textM->constantArr('hrOptionPending');
	if(count($forprinting)>0){	
		foreach($forprinting AS $i):
			echo '<tr id="tr_'.$i->coachID.'">';
				echo '<td>'.$i->coachID.'</td>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$i->username.'/">'.$i->name.'</a></td>';
				echo '<td>'.date('F d, Y', strtotime($i->dateGenerated)).'</td>';
				echo '<td>'.$i->coachedPeriod.'</td>';
				echo '<td>'.date('F d, Y', strtotime($i->coachedDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($i->coachedEval)).'</td>';
				echo '<td>'.$i->coachedByName.'</td>';
				echo '<td width="200px" class="errortext">'.$hrOptionsPending[$i->HRoptionStatus].'</td>';
				echo '<td><a href="'.$this->config->base_url().'coachingform/hroptions/'.$i->coachID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/view-icon.png"></a></td>';							
			echo '</tr>';
		endforeach;
	}
?>
	
</table>
</div>

<div id="tab-2" class="tab-content <?= (($this->access->accessFullHR==false)?'current':'') ?>">	
<table class="tableInfo datatable">
<thead>
	<tr class="trhead">
		<th>Coaching ID</th>
		<th>Employee's Name</th>
		<th>Date Generated</th>
		<th>Coaching Period</th>
		<th>Coaching Start</th>
		<th>Evaluation Date</th>
		<th>Immediate Supervisor</th>
		<th>Details</th>
	</tr>
</thead>
<?php
	if(count($inprogress)>0){	
		foreach($inprogress AS $i):
			echo '<tr>';
				echo '<td>'.$i->coachID.'</td>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$i->username.'/">'.$i->name.'</a></td>';
				echo '<td>'.date('F d, Y', strtotime($i->dateGenerated)).'</td>';
				echo '<td>'.$i->coachedPeriod.'</td>';
				echo '<td>'.date('F d, Y', strtotime($i->coachedDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($i->coachedEval)).'</td>';
				echo '<td>'.$i->coachedByName.'</td>';
				echo '<td><a class="iframe" href="'.$this->config->base_url().'coachingform/acknowledgment/'.$i->coachID.'/">
					<img src="'.$this->config->base_url().'css/images/view-icon.png">
					</a></td>';			
			echo '</tr>';
		endforeach;
	}
?>
	
</table>
</div>

<div id="tab-3" class="tab-content">	
<table class="tableInfo datatable">
<thead>
	<tr class="trhead">
		<th>Coaching ID</th>
		<th>Employee's Name</th>
		<th>Date Generated</th>
		<th>Coaching Period</th>
		<th>Coaching Start</th>
		<th>Evaluation Date</th>
		<th>Immediate Supervisor</th>
		<th>Status</th>
		<th>Coaching Form</th>
		<th>Details</th>
	</tr>
</thead>
<?php
	if(count($pending)>0){	
		foreach($pending AS $p):
			echo '<tr>';
				echo '<td>'.$p->coachID.'</td>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$p->username.'/">'.$p->name.'</a></td>';
				echo '<td>'.date('F d, Y', strtotime($p->dateGenerated)).'</td>';
				echo '<td>'.$p->coachedPeriod.'</td>';
				echo '<td>'.date('F d, Y', strtotime($p->coachedDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($p->coachedEval)).'</td>';
				echo '<td>'.$p->coachedByName.'</td>';
				echo '<td width="200px">'.$this->staffM->coachingStatus($p->coachID, $p).'</td>';
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

<div id="tab-4" class="tab-content">	
<table class="tableInfo datatable">
<thead>
	<tr class="trhead">
		<th>Coaching ID</th>
		<th>Employee's Name</th>
		<th>Date Generated</th>
		<th>Coaching Period</th>
		<th>Coaching Start</th>
		<th>Evaluation Date</th>
		<th>Immediate Supervisor</th>
		<th>Status</th>
		<th>Coaching Form</th>
		<th>Evaluation Form</th>
		<th>Details</th>
	</tr>
</thead>
<?php
	if(count($done)>0){	
		foreach($done AS $d):
			echo '<tr>';
				echo '<td>'.$d->coachID.'</td>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$d->username.'/">'.$d->name.'</a></td>';
				echo '<td>'.date('F d, Y', strtotime($d->dateGenerated)).'</td>';
				echo '<td>'.$d->coachedPeriod.'</td>';
				echo '<td>'.date('F d, Y', strtotime($d->coachedDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($d->coachedEval)).'</td>';
				echo '<td>'.$d->coachedByName.'</td>';
				echo '<td width="200px">'.$this->staffM->coachingStatus($d->coachID, $d).'</td>';				
				
				echo '<td align="center">';
				$fileloc = UPLOADS.'coaching/coachingform_'.$d->coachID.'.pdf';
				if($d->HRoptionStatus>=2 && file_exists($fileloc)){
					echo '<a class="iframe" href="'.$this->config->base_url().$fileloc.'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a>';
				}else{
					echo '<a href="'.$this->config->base_url().'coachingform/expectation/'.$d->coachID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"> </a>';
				}
				echo '</td>';
				
				echo '<td align="center">';
				$evalFileLoc = UPLOADS.'coaching/coachingevaluation_'.$d->coachID.'.pdf';
				if($d->HRoptionStatus>=2 && file_exists($evalFileLoc)){
					echo '<a class="iframe" href="'.$this->config->base_url().$evalFileLoc.'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a>';
				}else{
					echo '<a class="iframe" href="'.$this->config->base_url().'coachingform/evaluation/'.$d->coachID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"> </a>';
				}
				echo '</td>';
				
				
				echo '<td><a class="iframe" href="'.$this->config->base_url().'coachingform/acknowledgment/'.$d->coachID.'/">
					<img src="'.$this->config->base_url().'css/images/view-icon.png">
					</a></td>';			
			echo '</tr>';
		endforeach;
	}
?>	
</table>
</div>

<div id="tab-5" class="tab-content">	
<table class="tableInfo datatable">
<thead>
	<tr class="trhead">
		<th>Coaching ID</th>
		<th>Employee's Name</th>
		<th>Date Generated</th>
		<th>Coaching Period</th>
		<th>Coaching Start</th>
		<th>Evaluation Date</th>
		<th>Immediate Supervisor</th>
		<th>Status</th>
		<th>Details</th>
	</tr>
</thead>
<?php
	if(count($cancelled)>0){	
		foreach($cancelled AS $c):
			echo '<tr>';
				echo '<td>'.$c->coachID.'</td>';
				echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$c->username.'/">'.$c->name.'</a></td>';
				echo '<td>'.date('F d, Y', strtotime($c->dateGenerated)).'</td>';
				echo '<td>'.$c->coachedPeriod.'</td>';
				echo '<td>'.date('F d, Y', strtotime($c->coachedDate)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($c->coachedEval)).'</td>';
				echo '<td>'.$c->coachedByName.'</td>';
				echo '<td width="200px">'.$this->staffM->coachingStatus($c->coachID, $c).'</td>';
				echo '<td><a class="iframe" href="'.$this->config->base_url().'coachingform/acknowledgment/'.$c->coachID.'/">
					<img src="'.$this->config->base_url().'css/images/view-icon.png">
					</a></td>';			
			echo '</tr>';
		endforeach;
	}
?>	
</table>
</div>


