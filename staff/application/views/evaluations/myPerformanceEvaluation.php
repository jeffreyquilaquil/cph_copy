<h2>My Performance Evaluation</h2>
<hr/>

<table class="tableInfo datatable tblEvalNotify">
	<thead>
		<tr>
			<th>Evaluation ID</th>
			<th>Date Generated</th>
			<th>Evaluation Date</th>
			<th>Immediate Supervisor</th>
			<th width="20%">Status</th>
			<th>Evaluation Form</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($notifications as $row){
			$evalForm = "";
		#	echo "<script>console.log('".$row->notifyId."--".$row->status."')</script>";
			switch ($row->status) {
				case 0:
					$statusText = "Pending Self-rating. <a href='".$this->config->base_url()."performanceeval/2/".$this->user->empID."/".$row->evaluatorId."/".$row->notifyId."' target='_blank'>Click Here</a> to enter ratings.";
					break;
				case 1:
					$statusText = "Employee ratings locked in. <a href='".$this->config->base_url()."evaluations/sendEvaluationEmail/1/".$this->user->empID."/".$row->evaluatorId."/".$row->notifyId."' onClick='return false;' class='sendEmail'>Click here</a> to enter evaluator's raings.";
					break;
				case 2:
					$statusText = "Pending Evaluation Form for Printing";
					break;
				case 3:
					$statusText = "Evaluation Done";
					$evalForm = "<a href='".$this->config->base_url()."evaluations/evalPDF/2/".$this->user->empID."/".$row->evaluatorId."/".$row->notifyId."'><img src='data:image/png;base64,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'></a>";
					break;
				case 4:
					$statusText = "Cancelled";
					break;
			}
				echo "<tr>
					<td>".$row->notifyId."</td>
					<td>".date('F d, Y', strtotime($row->genDate))."</td>
					<td>".date('F d, Y', strtotime($row->evalDate))."</td>
					<td>".$row->evaluatorName."</td>
					<td>".$statusText."</td>
					<td align='center'>".$evalForm."</td>
				";
				echo "</tr>";
			} ?>
	</tbody>
</table>

<script type="text/javascript">
	$('.sendEmail').on('click',function(){
		var href = $(this).attr('href');
		$.ajax({
			url:href
		}).done(function(){
			alert('Evaluator has been notified.');
		});
	});
</script>