<div>
	<h3>Edit Custom Schedule By Day <?= ((isset($staffInfo->name))?' of '.$staffInfo->name:'') ?></h3><hr/>
	<p style="font-size:14px;">
	<?php
		echo 'Today\'s Schedule '.date('F d, Y', strtotime($today)).' - ';
		if(!isset($schedToday['sched'])){
			echo '<b>none</b>';
		}else if(isset($schedToday['suspend'])){
			echo '<b>Serving Suspension</b>';
		}else{
			echo '<b>'.$schedToday['sched'].'</b>';
		}
		echo (($schedToday['workhome'] == true)?' <span style="font-weight:bold;color:#f00;font-style:italic">Work Home</span>':'');
	?>
	</p>
<?php
	if(isset($schedToday['sched']) && $schedToday['sched']=="On Leave"){
		echo '<p class="errortext">Click <a href="'.$this->config->base_url().'staffleaves/'.$schedToday['leaveID'].'/">here</a> to view leave details.</p>';
	}else if(isset($schedToday['suspend'])){
		echo '<p class="errortext">Click <a href="'.$this->config->base_url().'detailsNTE/'.$schedToday['suspend'].'/">here</a> to view suspension details.</p>';
	}else{
		if(isset($schedToday['leaveID'])){
			echo '<p class="errortext">On Leave'.((isset($schedToday['leave']))?' ('.isset($schedToday['leave']).')':'').'. Click <a href="'.$this->config->base_url().'staffleaves/'.$schedToday['leaveID'].'/">here</a> to view details.</p>';
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
			<td width="25%">Set To <a href="<?= $this->config->base_url() ?>schedules/?page=customtime" target="_blank">+ Add time</a></td>
			<td>
				<select class="forminput" name="selecttime" required>
					<?= $this->textM->customTimeSelect('','Select pre-defined time') ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>Work from home</td>
			<td><?php
				$selOp = '<option value="0"'.(($schedToday['workhome'] == false)?' selected ':'').'>No</option>';
				$selOp .= '<option value="1"'.(($schedToday['workhome'] == true)?' selected ':'').'>Yes</option>';

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