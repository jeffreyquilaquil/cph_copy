<h3>Add PER Requirement</h3>
<hr/>
<?php
	if(isset($added)){
		echo '<span class="errortext">Successfully added <b>'.$added.'</b>.</span>';
	}else{
?>
<table class="tableInfo">
<form action="" method="POST" onSubmit="return disableBtn();">
	<tr>
		<td width="25%">Requirement</td>
		<td><?= $this->textM->formfield('text', 'perName', '', 'forminput', '', 'required'); ?></td>
	</tr>
	<tr>
		<td>Brief Description</td>
		<td><?= $this->textM->formfield('text', 'perDesc', '', 'forminput', '', 'required'); ?></td>
	</tr>
	<tr>
		<td>Enable N/A Checkbox?</td>
		<td>
		<?php 
			$selOption = '<option value="0">No</option><option value="1">Yes</option>';
		?>
			<?= $this->textM->formfield('select', 'enableNA', $selOption, 'forminput', '', 'required'); ?>
		</td>
	</tr>
	<tr>
		<td><br/></td>
		<td><?= $this->textM->formfield('submit', '', '+ Add Requirement', 'btnclass', '', 'id="submitBtn"'); ?></td>
	</tr>
</form>
</table>
<script type="text/javascript">
	function disableBtn(){
		$('#submitBtn').attr('disabled', 'disabled');
		$('<img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25px"/>').insertAfter('#submitBtn');
		return true;
	}
</script>
<?php } ?>