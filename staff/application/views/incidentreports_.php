<h2>HR Incident Reports</h2>
<hr/>
<style type="text/css">.tab-content{padding-top:10px;}</style>
<?php $current = 'tab-1'; ?>

<ul class="tabs">
<?php
	$current = 'new';
	$tab_labels = ['new' => 'New', 'printed' => 'Printed IR', 'pending' => 'Pending HR Actions'];
	foreach( $tab_labels as $key => $val ){
		echo '<li class="tab-link '.(($current == $key)?'current':'').'" data-tab="tab-'.$key.'">'.$val.'</li>';
	}
		
?>
</ul>



<div id="tab-new" class="tab-content <?php echo (($current=='new')?'current':''); ?>">
	<table class="tableInfo datatable">
		<thead>
			<tr class="trlabel">
				<td>Case #</td>
				<td>Employee</td>
				<td>Alias</td>
				<td>Date Reported</td>
				<td>Status</td>
				<td>File</td>
				<td>Actions</td>
				<td>Details</td>
			</tr>
		</thead>
		<tbody>
	<?php
		foreach($reportData AS $r){
			echo '<tr'.(($r->status==10)?' bgcolor="#a1a1a1" ':'').'>';
				echo '<td>'.sprintf('%04d', $r->reportID).'</td>'."\n";
				echo '<td>'.$r->name.'</td>'."\n";
				echo '<td>'.((!empty($r->alias))?$r->alias:'None').'</td>'."\n";
				echo '<td>'.date('d M Y h:i a', strtotime($r->dateSubmitted)).'</td>'."\n";
				echo '<td><b>'.$reportStatus[$r->status].'</b></td>'."\n";
				echo '<td></td>'."\n";
				echo '<td><a href="'.$this->config->base_url().'incidentreportaction/action/'.$r->reportID.'/" class="iframe">Quick Action</a></td>'."\n";
				echo '<td width="10%"><a href="'.$this->config->base_url().'incidentreportaction/details/'.$r->reportID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"></a></td>'."\n";
			echo '</tr>'."\n";
		}
	?></tbody>
		</table>

</div>
<div id="tab-printed" class="tab-content <?php echo (($current=='printed')?'current':''); ?>">Print IR</div>
<div id="tab-pending" class="tab-content <?php echo (($current=='pending')?'current':''); ?>">Pending HR Actions</div>


		
