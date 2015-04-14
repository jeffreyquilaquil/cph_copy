<h2>NTE Issued</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Pending Employee's Acknowledgement <?= ((count($pendingacknowledgement)>0)?'('.count($pendingacknowledgement).')':'') ?></li>
	<li class="tab-link" data-tab="tab-2">Acknowledged and Pending for CAR <?= ((count($pendingforcar)>0)?'('.count($pendingforcar).')':'') ?></li>
	<li class="tab-link" data-tab="tab-3">CAR Generated <?= ((count($ntewcar)>0)?'('.count($ntewcar).')':'') ?></li>
</ul>

<div id="tab-1" class="tab-content current">
<?php
	if(count($pendingacknowledgement)==0){
		echo '<br/>No pending NTE\'s.';
	}else{
?>
<table class="tableInfo">
	<tr class="trhead">
		<td>Employee</td>
		<td>NTE Type</td>
		<td>Level of Offense</td>
		<td>Date Issued</td>
		<td>Issued By</td>
		<td>NTE</td>
		<td>View</td>
	</tr>
<?php
	foreach($pendingacknowledgement AS $n):
		echo '
			<tr>
				<td>'.$n->name.'</td>
				<td>'.ucfirst($n->type).'</td>
				<td>'.$this->staffM->ordinal($n->offenselevel).' Offense</td>
				<td>'.date('F d, Y', strtotime($n->dateissued)).'</td>
				<td>'.$n->issuerName.'</td>
				<td><a class="iframe" href="'.$this->config->base_url().'ntepdf/'.$n->nteID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>
				<td><a class="iframe" href="'.$this->config->base_url().'detailsNTE/'.$n->nteID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
			</tr>
		';
	endforeach;
?>
</table>
<?php
	}
?>
</div>

<div id="tab-2" class="tab-content">
<?php
	if(count($pendingforcar)==0){
		echo '<br/>No pending NTE\'s.';
	}else{
?>
<table class="tableInfo">
	<tr class="trhead">
		<td>Employee</td>
		<td>NTE Type</td>
		<td>Level of Offense</td>
		<td>Date Issued</td>
		<td>Date Acknowledged</td>
		<td>NTE</td>
		<td>View</td>
	</tr>
<?php
	foreach($pendingforcar AS $p):
		echo '
			<tr>
				<td>'.$p->name.'</td>
				<td>'.ucfirst($p->type).'</td>
				<td>'.$this->staffM->ordinal($p->offenselevel).' Offense</td>
				<td>'.date('F d, Y', strtotime($p->dateissued)).'</td>
				<td>'.date('F d, Y', strtotime($p->responsedate)).'</td>
				<td><a class="iframe" href="'.$this->config->base_url().'ntepdf/'.$p->nteID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>
				<td><a class="iframe" href="'.$this->config->base_url().'detailsNTE/'.$p->nteID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
			</tr>
		';
	endforeach;
?>
</table>
<?php
	}
?>
</div>


<div id="tab-3" class="tab-content">
<?php
	if(count($ntewcar)==0){
		echo '<br/>No generated CAR.';
	}else{
?>
<table class="tableInfo">
	<tr class="trhead">
		<td>Employee</td>
		<td>NTE Type</td>
		<td>Level of Offense</td>
		<td>Date NTE Issued</td>
		<td>NTE Issued By</td>
		<td>Date CAR Issued</td>
		<td>CAR Issued By</td>
		<td>NTE File</td>
		<td>View</td>
	</tr>
<?php
	foreach($ntewcar AS $nt):
		echo '
			<tr>
				<td>'.$nt->name.'</td>
				<td>'.$nt->type.'</td>
				<td>'.$this->staffM->ordinal($nt->offenselevel).' Offense</td>
				<td>'.date('F d, Y', strtotime($nt->dateissued)).'</td>
				<td>'.$nt->issuerName.'</td>
				<td>'.date('F d, Y', strtotime($nt->cardate)).'</td>
				<td>'.$nt->carName.'</td>
				<td><a class="iframe" href="'.$this->config->base_url().'ntepdf/'.$nt->nteID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>
				<td><a class="iframe" href="'.$this->config->base_url().'detailsNTE/'.$nt->nteID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
			</tr>
		';
	endforeach;
?>
</table>
<?php
	}
?>
</div>
