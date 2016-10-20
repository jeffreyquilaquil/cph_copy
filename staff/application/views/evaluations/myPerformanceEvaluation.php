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
			switch ($row->status) {
				case 0:
					$statusText = "Pending Self-rating. <a href='".$this->config->base_url()."performanceeval/2/".$this->user->empID."/".$row->evaluatorId."' target='_blank'>Click Here</a> to enter ratings.";
					break;
				case 1:
					$statusText = "In Progress. <a href='".$this->config->base_url()."evaluations/sendEvaluationEmail/1/".$this->user->empID."/".$row->evaluatorId."' onClick='return false;' class='sendEmail'>Click here</a> to remind Evaluator to enter ratings.";
					break;
				case 2:
					$statusText = "Pending for HR";
					break;
				case 3:
					$statusText = "Evaluation Done";
					$evalForm = "<a href='".$this->config->base_url()."evaluations/evalPDF/2/".$this->user->empID."/".$row->evaluatorId."'><img src='#'></a>";
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
					<td>".$evalForm."<td>
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