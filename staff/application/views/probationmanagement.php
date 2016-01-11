<h2>Probation Management</h2><hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Probationary Employees</li>
	<li class="tab-link" data-tab="tab-2">Pre-Employment Requirements (Regular Employees)</li>
	<li class="tab-link" data-tab="tab-3">Pending for HR (<?= count($queryEval) ?>)</li>
</ul>
<br/>

<div id="tab-1" class="tab-content current">
	<table class="tableInfo datatable">
		<thead>
			<tr class="trhead">
				<th>Name of Employee</th>
				<th>Email Address</th>
				<th>Position Title</th>
				<th>Immediate Supervisor</th>
				<th>Start Date</th>
				<th>90th Day</th>
				<th>PER Status</th>
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
				<th>Name of Employee</th>
				<th>Email Address</th>
				<th>Position Title</th>
				<th>Immediate Supervisor</th>
				<th>Start Date</th>
				<th>Tenure Age (Years)</th>
				<th>PER Status</th>
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

<div id="tab-3" class="tab-content">
	<table class="tableInfo datatable">
		<thead>
			<tr class="trhead">
				<th>Evaluation ID</th>
				<th>Name of Employee</th>
				<th>Reviewer</th>
				<th>Final Rating</th>
				<th>PDF File</th>
				<th><br/></th>
			</tr>
		</thead>
	<?php
		foreach($queryEval AS $qe){
			echo '<tr>';
				echo '<td>'.$qe->evalID.'</td>';
				echo '<td>'.$qe->name.'</td>';
				echo '<td>'.$qe->reviewerName.'</td>';
				echo '<td>'.$this->textM->getScoreMatrix($qe->finalRating).'</td>';
				echo '<td><a href="'.$this->config->base_url().'evaluationpdf/'.$qe->evalID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>';
				echo '<td>';
					if($qe->hrStatus==1) echo '<a href="javascript:void(0);" onClick="printed('.$qe->evalID.')">Click if done printing.</a>';
					else if($qe->hrStatus==2){
						echo '<form id="uploadEvalForm_'.$qe->evalID.'" action="" method="POST" enctype="multipart/form-data">';
						echo '<input type="hidden" name="empID" value="'.$qe->empID_fk.'"/>';
						echo '<input type="hidden" name="evalID" value="'.$qe->evalID.'"/>';
						echo '<input type="hidden" name="submitType" value="uploadEval"/>';
						echo '<input type="file" name="evalDoc" id="evalDoc_'.$qe->evalID.'" onChange="fileChange('.$qe->evalID.')" class="hidden"/>';						
						echo '</form>';
						echo '<button onClick="btnUpload('.$qe->evalID.')">Upload Signed File</button>';
					} 
				echo '</td>';
			echo '</tr>';
		}
	?>
	</table>
</div>

<script type="text/javascript">	
	function btnUpload(id){
		$('#evalDoc_'+id).trigger('click');
	}
	
	function fileChange(id){
		displaypleasewait();
		$('#uploadEvalForm_'+id).submit();
	}
	
	function printed(id){
		if(confirm('Are you sure you printed the evaluation form?')){
			displaypleasewait();
			$.post('<?= $this->config->item('career_uri') ?>', {submitType:'printedEval', id:id},
			function(){
				location.reload();
			});
		}
	}
</script>