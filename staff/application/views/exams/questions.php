<?php echo form_open();
	echo form_hidden('name', $this->session->userdata('name') );
	echo form_hidden('started_at', date('Y-m-d H:i:s'));
 ?>
<p>Multiple choice: </p>
<ol>
<?php 
	//shuffle($questionnaires);
	foreach( $questionnaires as $key => $question ): 
			shuffle_assoc($question['choices']);
		?>
		<li>
			<strong><?php echo $question['questions']; ?></strong>
			<ol class="choice">
				<?php foreach( $question['choices'] as $choice_letter => $choice ): ?>
					<li>
						<div class="radio">
							<label>
								<input type="radio" name="<?php echo $key ?>" value="<?php echo $choice_letter ?>"><?php echo $choice; ?>
							</label>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</li>
<?php endforeach; //end questionnaires ?>
</ol>
<div class="row" style="text-align:center;">
	<input name="submit" value="Submit" class="btn btn-primary" type="submit">
</div>
<?php echo form_close(); ?>