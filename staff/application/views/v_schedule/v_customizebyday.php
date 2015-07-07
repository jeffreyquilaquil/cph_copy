<div>
	<h2>Edit Custom Schedule By Day</h2><hr/>
	<h3>Today's Schedule <?= date('F d, Y', strtotime($today)).' - <b>'.((!empty($schedToday))?$schedToday:'none').'</b>' ?></h3>
	
	<form action="" method="POST" onSubmit="displaypleasewait();">
	<table class="tableInfo">
		<tr>
			<td>Dates</td>
			<td>
				<input name="dates[]" type="text" class="forminput datepick" value="<?= date('F d, Y', strtotime($today)) ?>" required/>
				<input id="addbtn" type="button" value="+ Add" style="margin-top:5px;"/>
			</td>
		</tr>
		<tr>
			<td width="25%">Set To</td>
			<td>
				<select class="forminput" name="timeID_fk" required>
					<?= $this->textM->customTimeSelect() ?>
				</select>
			</td>
		</tr>
		<tr>
			<td><br/></td>
			<td>
				<input type="hidden" name="empID_fk" value="<?= $empID ?>"/>
				<input type="submit" value="Submit" class="btnclass"/>
			</td>
		</tr>
	</table>
	</form>
</div>

<?php
	$mindate = date('Y/m/d');
?>
<script type="text/javascript">
	$(function(){
		$('.datepick').datetimepicker({ 
			format:'F d, Y', timepicker:false,
			minDate:'<?= $mindate ?>'
		});
		
		$('#addbtn').click(function(){
			$('<input name="dates[]" type="text" class="forminput datepick" onClick="$(this).datetimepicker({ format:\'F d, Y\', timepicker:false, minDate:\'<?= $mindate ?>\' });$(this).datetimepicker(\'show\');" onChange="$(this).datetimepicker(\'hide\');"/>').insertBefore(this);
		});
	});
</script>