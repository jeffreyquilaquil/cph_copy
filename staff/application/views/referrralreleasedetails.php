<h3>Mini Referral Bonus Details</h3>
<hr/>
<table class="tableInfo">
	<tr>
		<td width="30%">MRB ID</td>
		<td><?= sprintf('%04d',$mrb->mrbID) ?></td>
	</tr>
	<tr>
		<td>Employee Name</td>
		<td><?= $mrb->name ?></td>
	</tr>
	<tr>
		<td>Referral Bonus</td>
		<td><?= $this->textM->convertNumFormat($mrb->releasedAmount) ?></td>
	</tr>
	<tr>
		<td>For these applicants</td>
		<td>
	<?php
		foreach($applicants AS $a){
			echo '<a href="'.$this->config->item('career_url').'/view_info.php?id='.$a->id.'" target="_blank">'.$a->name.'</a><br/>';
		}
	?>
		</td>
	</tr>
	<tr>
		<td>Released Date</td>
		<td><?= (($mrb->dateReleased!='0000-00-00')?date('F d, Y', strtotime($mrb->dateReleased)):'') ?></td>
	</tr>
	<tr>
		<td>Released Note</td>
		<td><?= nl2br($mrb->releasedNote) ?></td>
	</tr>
</table>