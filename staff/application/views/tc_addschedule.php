<?php
	if(count($row)==0){
		echo 'No staff selected.';
	}else{
		$weekArr = $this->config->item('weekdayArray');
		$timeArr = array();
		$stime = array();
		foreach($schedTimes AS $t):
			if($t->category==0)
				$timeArr[$t->timeID]['name'] = $t->timeName;
			else
				$timeArr[$t->category][$t->timeID] = $t->timeValue.'|'.$t->timeName;
			$stime[$t->timeID] = $t->timeValue;
		endforeach;
	
?>
	
<?php
	$numCurrentSched = count($currentSched);
	if($numCurrentSched>0){
		echo '<h3>Current recurring schedule</h3><hr/>';
		echo 'Start Date: <b>'.date('F d, Y', strtotime($currentSched->effectivestart)).'</b>';
		echo '<table class="tableInfo tacenter">';
			echo '<tr class="trlabel">';
				foreach($weekArr AS $w) echo '<td>'.ucfirst($w).'</td>';
			echo '</tr>';
			echo '<tr>';
				foreach($weekArr AS $w) 
				echo '<td>'.(($currentSched->$w!=0)?$stime[$currentSched->$w]:'').'</td>';
			echo '</tr>';
		echo '</table>';
		
		
		echo '<br/><br/><hr/>';
	}
?>	
	<h3>Set Schedule for <?= $row->name ?></h3><hr/>
	<form action="" method="POST" onSubmit="return validateForm();">
	<table class="tableInfo">
		<tr>
			<td width="20%">Start Date</td>
			<td><input type="text" name="startDate" class="forminput datepick" required/></td>
		</tr>
		<tr>
			<td>End Date</td>
			<td>
				<select class="forminput" name="endDate">
					<option value="">Not yet determined</option>
					<option value="chooseDate">Choose date</option>
				</select>
			</td>
		</tr>
		<tr id="tr_enddate" class="hidden">
			<td><br/></td>
			<td><input type="text" name="endDate" class="forminput datepick"/></td>
		</tr>
		<tr>
			<td>Select schedule</td>
			<td>
			<select name="schedTemplate" class="forminput">
				<option value="">- Custom -</option>
			<?php
				foreach($schedTemplates AS $sched){
					echo '<option value="'.$sched->custschedID.'">'.$sched->schedName.'</option>';
				}
			?>
			</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<table width="100%" class="tacenter fs11px">
			<?php
				echo '<tr class="trlabel trs trsched_">';
					foreach($weekArr AS $w) echo '<td>'.ucfirst($w).'</td>';
				echo '</tr>';
				echo '<tr class="trs trsched_">';
					foreach($weekArr AS $w) echo '<td>
						'.$this->textM->customTimeDisplay($timeArr, $w).'</td>';
				echo '</tr>';
				
				//templates
				foreach($schedTemplates AS $st){
					echo '<tr class="trs hidden trlabel trsched_'.$st->custschedID.'">';
					foreach($weekArr AS $w) echo '<td>'.ucfirst($w).'</td>';
					echo '</tr>';
					
					echo '<tr class="trs hidden trsched_'.$st->custschedID.'">';
					foreach($weekArr AS $w) echo '<td>'.(($st->$w!=0)?$stime[$st->$w]:'').'</td>';
					echo '</tr>';
				}
			?>					
			</table>
			<input type="hidden" name="customSched" value=""/>
			<input type="hidden" name="username" value=""/>
			<?php if($numCurrentSched>0){ echo '<input type="hidden" name="schedID" value="'.$currentSched->schedID.'"/>'; } ?>
			</td>
		</tr>
		
		<tr>
			<td colspan=2>
				<input type="submit" value="+ Set Schedule" class="btnclass"/>
				<i id="schedNote" class="errortext"><?= (($numCurrentSched>0)?'<b>Note:</b> Adding new recurring schedule will automatically set end date of the current schedule.':'') ?></i>
			</td>
		</tr>
	</table>
	</form>

<script type="text/javascript">
$(function(){
	$('.datepick').datetimepicker({ 
		format:'F d, Y', timepicker:false,
		minDate:'<?= date('Y/m/d') ?>'
	});
	
	$('select[name="schedTemplate"]').change(function(){ 
		$('.trs').addClass('hidden');
		$('.trsched_'+$(this).val()).removeClass('hidden');
	});
	
	$('select[name="endDate"]').change(function(){
		if($(this).val()=='chooseDate'){
			$('#tr_enddate').removeClass('hidden');
			$('#tr_enddate input').prop('required',true);
			$('#schedNote').addClass('hidden');
		}else{
			$('#tr_enddate').addClass('hidden');
			$('#tr_enddate input').prop('required',false);
			$('#schedNote').removeClass('hidden');
		}
	});
});

function validateForm(){
	xx='';
	valid = true;
	var arrSched = [];
		
	if($('input[name="endDate"]').val()!='' && $('input[name="startDate"]').val()<$('input[name="endDate"]').val()){
		alert('Invalid End Date.');
		valid = false;
	}
		
	plate = $('select[name="schedTemplate"]').val();
	if(plate==''){		
		$('.schedSelect').each(function(){
			xx += $(this).val();
			arrSched.push($(this).val());
		});
		if(xx==''){
			alert('Please select schedule.');
			valid = false;
		} 		
	}
	
	$('input[name="customSched"]').val(arrSched);
	if(valid) 
		displaypleasewait();

	return valid;
}
</script>
	
<?php } ?>