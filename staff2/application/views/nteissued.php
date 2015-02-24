<h2>NTE Issued</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Active NTE's</li>
	<li class="tab-link" data-tab="tab-2">Generated CAR</li>
</ul>

<div id="tab-1" class="tab-content current">
<br/><br/>
<?php
	if(count($nte)==0){
		echo 'No pending NTE\'s.';
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
	foreach($nte AS $n):
		echo '
			<tr>
				<td>'.$n->name.'</td>
				<td>'.$n->type.'</td>
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
<br/><br/>
<?php
	if(count($ntewcar)==0){
		echo 'No generated CAR.';
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
