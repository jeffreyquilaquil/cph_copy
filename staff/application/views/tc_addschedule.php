<?php
	if(count($row)==0){
		echo 'No staff selected.';
	}else{
?>
	<h2>Add Schedule for <?= $row->name ?></h2><hr/>
	<form action="" method="POST">
	<table class="tableInfo">
		<tr>
			<td width="30%">Start Date</td>
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
			<td><input type="text" name="endDateText" class="forminput datepick"/></td>
		</tr>
		<tr>
			<td>Select schedule</td>
			<td>
			<select name="schedTemplate" class="forminput">
				<option value="">-Custom-</option>
			<?php
				foreach($scheduleTemplates AS $sched){
					echo '<option value="'.$sched->custschedID.'">'.$sched->schedName.'</option>';
				}
			?>
			</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<table>
				
			</table>
			</td>
		</tr>
		
		<tr>
			<td><br/></td>
			<td><input type="submit" value="+ Add Schedule" class="btnclass"/></td>
		</tr>
	</table>
	</form>

<script type="text/javascript">
$(function(){
	$('.datepick').datetimepicker({ 
		format:'F d, Y', timepicker:false,
		minDate:'<?= date('Y/m/d') ?>'
	});
	
	$('select[name="endDate"]').change(function(){
		if($(this).val()=='chooseDate'){
			$('#tr_enddate').removeClass('hidden');
			$('#tr_enddate input').prop('required',true);
		}else{
			$('#tr_enddate').addClass('hidden');
			$('#tr_enddate input').prop('required',false);
		}
	});
});
</script>
	
<?php } ?>