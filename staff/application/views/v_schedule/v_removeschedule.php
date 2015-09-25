<h3>Why do you want to remove this schedule?</h3>
<hr/>
<b><?= date('F d, Y', strtotime($schedData['date'])).' '.$schedData['sched'] ?></b>
<form action="" method="POST">
	<?php
		echo $this->textM->formfield('textarea', 'reason', '', 'forminput', 'Type reason for removing schedule...', 'rows="10"');
		echo $this->textM->formfield('hidden', 'submitType', 'removeSched');
		echo $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
	?>
</form>




