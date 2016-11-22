<h2>HR Incident Reports</h2>
<hr/>
<style type="text/css">.tab-content{padding-top:10px;}</style>
<?php $current = 'tab-1'; ?>

<ul class="tabs">
<?php
	$current = 'new';
	$tab_labels = ['new' => 'New', 'printed' => 'Printed IR', 'pending' => 'Pending HR Actions', 'all' => 'All Tickets'];
	foreach( $tab_labels as $key => $val ){
		echo '<li class="tab-link '.(($current == $key)?'current':'').'" data-tab="tab-'.$key.'">'.$val.'</li>';
	}
		
?>
</ul>

<div id="tab-new" class="tab-content <?php echo (($current=='new')?'current':''); ?>">
	<table class="tableInfo datatable" style="width: 100%">
		<thead>
			<tr class="trlabel">
				<td>Case #</td>
				<td>Employee</td>
				<td>Alias</td>
				<td>Date Reported</td>
				<td>Age</td>
				<td>Status</td>
				<td>Actions</td>
				<td>Details</td>
			</tr>
		</thead>
		<tbody>
	<?php
		// echo "<pre>";
		// var_dump($reportData);
		// exit();
		foreach($reportData AS $r){
			if( $r->status < 10 && $r->status != 3 ){
				echo '<tr'.(($r->status==10)?' style="background-color:#a1a1a1" ':'').'>';
				echo '<td>'.sprintf('%04d', $r->reportID).'</td>'."\n";
				echo '<td>'.$r->name.'</td>'."\n";
				echo '<td>'.((!empty($r->alias))?$r->alias:'None').'</td>'."\n";
				echo '<td>'.date('d M Y h:i a', strtotime($r->dateSubmitted)).'</td>'."\n";
				$numberOfDays =  $this->commonM->dateDifference(date('Y-m-d H:i:s'),$r->dateSubmitted, '%a');
				$numberOfHours = $this->commonM->dateDifference(date('Y-m-d H:i:s'),$r->dateSubmitted, '%h');

				if( $numberOfDays > 0)
					$numberOfHours = $numberOfDays * 24 + $numberOfHours;

				$colorClass = '';

				
				if($numberOfHours <= 48 ){
					$colorClass = 'colorgreen';
				}
				elseif($numberOfHours > 48 && $numberOfHours < 120){
					$colorClass = 'colorBlue';
				}
				else{
					$colorClass = 'errortext';
				}

				echo '<td><span class="'.$colorClass.'">'.$numberOfHours.' Hour/s</span></td>';
				echo '<td><b>'.$reportStatus[$r->status].'</b></td>'."\n";
				echo '<td><a href="'.$this->config->base_url().'incidentreportaction/action/'.$r->reportID.'/" class="iframe">Quick Action</a></td>'."\n";
				echo '<td width="10%"><a href="'.$this->config->base_url().'incidentreportaction/details/'.$r->reportID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"></a></td>'."\n";
				echo '</tr>'."\n";
			}
		}
	?></tbody>
		</table>
</div>

<div id="tab-printed" class="tab-content <?php echo (($current=='printed')?'current':''); ?>">
<table class="tableInfo datatable">
		<thead>
			<tr class="trlabel">
				<td>Case #</td>
				<td>Employee's Name</td>
				<td>Date Requested</td>
				<td>Signed Incident Report</td>
				<td>HR Options</td>
				<td>HR Details</td>
			</tr>
		</thead>
		<tbody>
	<?php
		foreach($reportData AS $r){
			if( $r->status == 3){
				echo '<tr>';
			
				echo '<td>'.sprintf('%04d', $r->reportID).'</td>'."\n";
				echo '<td>'.$r->name.'</td>'."\n";
				echo '<td>'.date('d M Y h:i a', strtotime($r->dateSubmitted)).'</td>'."\n";
				echo '<td>';
					if(!$r->docs){
						echo "No file uploaded";
					}
					else{
						$_url_ = 'attachment.php?u='.urlencode($this->textM->encryptText('staffs/violationreported')).'&f='.urlencode($this->textM->encryptText($r->docs));

						echo '<a href="'. $this->config->base_url() . $_url_ .'" target="_blank" style="margin-right: 5px;"><img src="'. $this->config->base_url() .'css/images/pdf-icon.png" /></a>';
					}
				echo '</td>';
				echo '<td><a href="'.$this->config->base_url().'incidentreportaction/action/'.$r->reportID.'/" class="iframe">Quick Action</a></td>'."\n";
				echo '<td width="10%"><a href="'.$this->config->base_url().'incidentreportaction/details/'.$r->reportID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"></a></td>'."\n";
				echo '</tr>'."\n";
			}
		}
	?></tbody>
		</table>
</div>
<div id="tab-pending" class="tab-content <?php echo (($current=='pending')?'current':''); ?>">
	<table class="tableInfo datatable" style="width: 100%">
	<thead>
			<tr class="trlabel">
				<td>Case #</td>
				<td>Employee's Name</td>
				<td>Date Filed</td>
				<td>Status</td>
				<td>Immediate Supervisor</td>
				<td>HR Options</td>
			</tr>
		</thead>
		<tbody>
	<?php
		$numCount = 0;
		foreach($reportData AS $r){
			if( $r->status == 11){
				if( $r->supervisor ){
					$g = $this->commonM->getImSupervisor($r->supervisor);
				}
				$numCount++;
				echo '<tr>';
			
				echo '<td>'.sprintf('%04d', $r->reportID).'</td>'."\n";
				echo '<td>'.$r->name.'</td>'."\n";
				echo '<td>'.date('d M Y h:i a', strtotime($r->dateSubmitted)).'</td>'."\n";
				echo '<td>';
				echo "
					<div class='reportID' data-empid='".$r->empID_fk."' data-reportid='".$r->reportID."'></div>
					<select name='statusHRAction'>
						<option value='0'>Select an Option...</option>";

				$hrActions = array('forIssuance' => 'For Issuance of NTE', 'noMerit' => 'No Merit (For Refiling)');

				foreach($hrActions as $k => $v){
					$selected = '';
					if($k == $r->hrAction){
						$selected = 'selected';
					}
					echo "<option value='".$k."' $selected>".$v."</option>";
				}

				echo "</select>";
				echo '</td>';
				echo '<td>'.$g->name.'</td>'."\n";
				echo '<td width="10%"><a href="'.$this->config->base_url().'incidentreportaction/action/'.$r->reportID.'/" class="iframe">Quick Action</a></td>'."\n";
				echo '</tr>'."\n";
			}
		}
	?></tbody>
	</table>
</div>
<div id="tab-all" class="tab-content <?php echo (($current=='printed')?'current':''); ?>">
	<table class="tableInfo datatable" style="width: 100%">
	<thead>
			<tr class="trlabel">
				<td>Case #</td>
				<td>Employee's Name</td>
				<td>Date Filed</td>
				<td>Status</td>
				<td>Files</td>
				<td>Immediate Supervisor</td>
				<td>Actions</td>
			</tr>
		</thead>
		<tbody>
	<?php
		foreach($reportData AS $r){
			$numCount++;
			if( $r->supervisor ){
				$g = $this->commonM->getImSupervisor($r->supervisor);
			}
			echo '<tr>';
			echo '<td>'.sprintf('%04d', $r->reportID).'</td>'."\n";
			echo '<td>'.$r->name.'</td>'."\n";
			echo '<td>'.date('d M Y h:i a', strtotime($r->dateSubmitted)).'</td>'."\n";
			echo '<td><b>'.$reportStatus[$r->status].'</b></td>'."\n";
			echo '<td>';
			if(!$r->docs){
				echo "No file uploaded";
			}
			else{
				$_url_ = 'attachment.php?u='.urlencode($this->textM->encryptText('staffs/violationreported')).'&f='.urlencode($this->textM->encryptText($r->docs));

				echo '<a href="'. $this->config->base_url() . $_url_ .'" target="_blank" style="margin-right: 5px;"><img src="'. $this->config->base_url() .'css/images/pdf-icon.png" /></a>';
			}
			echo "</td>";
			echo '<td>'.$g->name.'</td>'."\n";
			echo '<td width="10%"><a href="'.$this->config->base_url().'incidentreportaction/action/'.$r->reportID.'/" class="iframe">Quick Action</a></td>'."\n";
			echo '</tr>'."\n";
		}
	?></tbody>
	</table>
</div>
<script type='text/javascript'>
	$(document).ready(function(){
		$('select[name="statusHRAction"]').change(function(){
			var reportType = $(this).val();

			if( reportType != 0){
				var reportID = $(this).parent().find('.reportID').data('reportid');
				var empID = $(this).parent().find('.reportID').data('empid');
				

				var formData = new FormData();

				formData.append('hrAction','hrAction');
				formData.append('reportID', reportID);
				formData.append('reportType', reportType);
				formData.append('empID', empID);

				$.ajax({
					url: 'incidentreportaction/',
					data: formData,
					type: 'POST',
					processData: false,
	  				contentType: false,
					success: function(e){
						console.log(e);
					}
				});
			}
		});
	});
</script>


		
