<table class="tableInfo datatable">
	<thead>
		<tr>
			<th>#</th>
			<th>Question</th>
			<th>Correct</th>
			<th>Answer</th>
			<th>Result</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		$answers = json_decode($results->answers, true);
		//dd($answers);
		$c = 1;
		foreach( $questions['questionnaires'] as $key =>  $result ): ?>
			<tr>
				<td><?php echo $c; ?></td>
				<td><?php echo $result['questions'].'<br/><br/>';
						foreach( $result['choices'] as $c_key => $c_val ){
							echo $c_key.') '.$c_val.'<br/>';
						}
				 ?></td>
				<td><?php echo $answer_key['answers'][$key].') '.$result['choices'][ $answer_key['answers'][$key] ] ; ?></td>
				<td><?php echo (isset($answers[$key]) ? $answers[$key] : 'No Answer'); ?></td>
				<td><?php $check = $this->commonM->check_answers( $answer_key['answers'][$key], $answers[$key], 'check') ;
					echo ($check == true) ? 'Correct' : 'Wrong';
				 ?></td>
			</tr>
			<?php $c++; ?>
		<?php endforeach; ?>
	</tbody>
</table>