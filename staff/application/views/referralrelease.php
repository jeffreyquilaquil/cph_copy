<h3>Confirm Release Date of Php500.00 bonus to <?= $row->lname.', '.$row->fname ?> for successfully getting applications to CPH.</h3>
<hr/>
<?php
	if(isset($posted)){
		echo 'Confirmation Saved.';
	}else{
?>

<form action="" method="POST">
<table width="100%">
	<tr>
		<td colspan=2>
			Please paste in the box below the confirmation email from accounting team (<i>do not forget to include the email details - From, To, Subject</i>):
			<?= $this->textM->formfield('textarea', 'releasedNote', '', 'forminput','','rows=15 required'); ?>		
		</td>
	</tr>
	<tr>
		<td width="32%">Please enter the date confirmed by accounting:</td>
		<td><?= $this->textM->formfield('text', 'dateReleased', '', 'forminput datepick','','required'); ?></td>
	</tr>
	<tr>
		<td align="right" colspan=2><?= $this->textM->formfield('submit', '', 'Confirm Release Date', 'btnclass btngreen'); ?></td>
	</tr>
</table>
</form>
<?php } ?>

<script type="text/javascript">
$(function(){
	$('.datepick').datetimepicker({ 
		format:'F d, Y',
		maxDate:'<?= date('Y/m/d') ?>'
	});	
});
</script>

