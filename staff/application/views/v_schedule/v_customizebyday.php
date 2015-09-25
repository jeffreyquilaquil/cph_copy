<div>
	<h2>Edit Custom Schedule By Day</h2><hr/>
	<h3>
	<?php
		echo 'Today\'s Schedule '.date('F d, Y', strtotime($today)).' - ';
		if(!isset($schedToday['sched'])){
			echo '<b>none</b>';
		}else{
			echo '<b>'.$schedToday['sched'].'</b>';
		}
	?>
	</h3>
<?php
	if(isset($schedToday['sched']) && $schedToday['sched']=="On Leave"){
		echo '<p class="errortext">Click <a href="'.$this->config->base_url().'staffleaves/'.$schedToday['leaveID'].'/">here</a> to view leave details.</p>';
	}else{
		if(isset($schedToday['leaveID'])){
			echo '<p class="errortext">On Leave ('.$schedToday['leave'].'). Click <a href="'.$this->config->base_url().'staffleaves/'.$schedToday['leaveID'].'/">here</a> to view details.</p>';
		}
?>	
	<form action="" method="POST" onSubmit="return checkSubmit();">
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
				<select class="forminput" name="selecttime" required>
					<?= $this->textM->customTimeSelect('','Select pre-defined time') ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Work from home</td>
			<td><?php
				$selOp = '<option value="0">No</option>';
				$selOp .= '<option value="1">Yes</option>';
				echo $this->textM->formfield('select', 'workhome', $selOp, 'forminput');
			?></td>
		</tr>
		<tr>
			<td><br/></td>
			<td>
				<input type="hidden" name="timeText" value=""/>
				<input type="hidden" name="timeHours" value=""/>
				<input type="hidden" name="empID_fk" value="<?= $empID ?>"/>
				<input type="submit" value="Submit" class="btnclass btngreen"/>
			</td>
		</tr>
	</table>
	</form>
<?php } ?>
</div>

<?php
	if(!empty($is_edit)) $mindate = date('Y/m/d', strtotime($today));
	else $mindate = date('Y/m/d');
?>
<script type="text/javascript">
	$(function(){
		$('.timepick').datetimepicker({ format:'H:i', datepicker:false });
		$('.datepick').datetimepicker({ 
			format:'F d, Y', timepicker:false,
			minDate:'<?= $mindate ?>'
		});
		
		$('#addbtn').click(function(){
			$('<input name="dates[]" type="text" class="forminput datepick" onClick="$(this).datetimepicker({ format:\'F d, Y\', timepicker:false, minDate:\'<?= $mindate ?>\' });$(this).datetimepicker(\'show\');" onChange="$(this).datetimepicker(\'hide\');"/>').insertBefore(this);
		});
		
		$('select[name="selecttime"]').change(function(){
			$('input[name="timeText"]').val($(this).val());
			$('input[name="timeHours"]').val($(this).find(':selected').data('time'));
		});
	});
	
	function checkSubmit(){		
		if($('input[name="timeText"]').val()!=''){
			displaypleasewait();
			return true;
		}else{
			alert('Please select schedule.');
			return false;
		} 
	}
</script>