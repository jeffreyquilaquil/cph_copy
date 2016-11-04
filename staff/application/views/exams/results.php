<table class="tableInfo datatable">
	<thead>
		<tr>
			<th>Name</th>
			<th>Score</th>
			<th>Duration</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $results as $result ): ?>
			<tr>
				<td><a href="<?php echo $this->config->base_url().'exams/results/'.$result->id ?>"><?php echo ucwords($result->name); ?></a></td>
				<td><?php echo $this->commonM->check_answers( $this->textM->answers()['answers'], json_decode($result->answers, true) ); ?></td>
				<td><?php echo $this->commonM->dateDifference($result->created_at, $result->started_at, '%H:%I:%S'); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>