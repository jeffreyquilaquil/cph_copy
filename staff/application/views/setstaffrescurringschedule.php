<?php 
// print_r($currentSched);
?>
<script type="text/javascript">
function onClickPreSched() {
		var preschedvalue = document.getElementById('presched').value.trim(); 		
		
		if(document.getElementById('presched').value == "0") {				
			$('#schedname').removeAttr('disabled');
			$('#schedType').removeAttr('disabled');
			$('#sunday').removeAttr('disabled');
			$('#monday').removeAttr('disabled');
			$('#tuesday').removeAttr('disabled');
			$('#wednesday').removeAttr('disabled');
			$('#thursday').removeAttr('disabled');
			$('#friday').removeAttr('disabled');
			$('#saturday').removeAttr('disabled');		
			
			$('#schedname').val('');
			$('#schedType').val('');
			$('#sunday').val('');
			$('#monday').val('');
			$('#tuesday').val('');
			$('#wednesday').val('');
			$('#thursday').val('');
			$('#friday').val('');
			$('#saturday').val('');

		}
		else{
			$('#schedname').attr('disabled','disabled');
			$('#schedType').attr('disabled','disabled');
			$('#sunday').attr('disabled','disabled');
			$('#monday').attr('disabled','disabled');
			$('#tuesday').attr('disabled','disabled');
			$('#wednesday').attr('disabled','disabled');
			$('#thursday').attr('disabled','disabled');
			$('#friday').attr('disabled','disabled');
			$('#saturday').attr('disabled','disabled');					
			$('#spanid').html('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/>');
			$.post('<?= $this->config->base_url().'schedules/getvalueofpredefinesched/' ?>'+preschedvalue, {id:preschedvalue}, function(data){							
				document.getElementById('sunday').value = data.sunday;
				document.getElementById('monday').value = data.monday;
				document.getElementById('tuesday').value = data.tuesday;
				document.getElementById('wednesday').value = data.wednesday;
				document.getElementById('thursday').value = data.thursday;
				document.getElementById('friday').value = data.friday;
				document.getElementById('saturday').value = data.saturday;			
				document.getElementById('schedType').value = data.schedType;		
				$("#spanid").html('');
			}, "json");
			

		}
		
}

$(function(){

	
	$('#schedname').removeAttr('disabled');
	$('#schedType').removeAttr('disabled');
	$('#sunday').removeAttr('disabled');
	$('#monday').removeAttr('disabled');
	$('#tuesday').removeAttr('disabled');
	$('#wednesday').removeAttr('disabled');
	$('#thursday').removeAttr('disabled');
	$('#friday').removeAttr('disabled');
	$('#saturday').removeAttr('disabled');		


$('#submitbutton').click(function(){			
			celebrity = '';
			if($('#effective_startdate').val()==''){
				celebrity += 'Effective date must be set.\n';
			}						
			if($('#sunday').val()=='' && $('#monday').val()=='' && $('#tuesday').val()=='' && $('#wednesday').val()=='' && 
				$('#thursday').val()=='' && $('#friday').val()=='' && $('#saturday').val()==''){
				celebrity += 'Schedules are empty.';
			}
			if($('#effective_enddate').val()==''){
				if(confirm("Effective End Date is not set, procedd anyway?"));
					celebrity += '';
			}
			if(celebrity!=''){
				alert(celebrity);
			}else{
				var schedname = $('#schedname').val();
				if(schedname == ""){
					alert("Schedule Name must be set");
				}
				else {
					$("#spanid").html('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/>');
					$.post('<?= $this->config->item('career_uri') ?>',{					
						submitType:'setScheduleForStaff',
						presched:$('#presched').val(),
						effective_startdate:$('#effective_startdate').val(),
						effective_enddate:$('#effective_enddate').val(),
						schedName:,
						schedType:$('#schedType').val(),
						sunday:$('#sunday').val(),
						monday:$('#monday').val(),
						tuesday:$('#tuesday').val(),
						wednesday:$('#wednesday').val(),
						thursday:$('#thursday').val(),
						friday:$('#friday').val(),
						saturday:$('#saturday').val()
					}, function(){					
						location.reload(true);
						alert('New Schedule Set');
					});
				}
			}
		});
		
	
	
	});
</script>
<?php
	if(count($alltime)==0) echo 'No time records.';	
	$time = array();
	foreach($alltime AS $t):
		if($t->category==0)
			$time[$t->timeID]['name'] = $t->timeName;
		else
			$time[$t->category][$t->timeID] = $t->timeValue.'|'.$t->timeName;
	endforeach;

	// print_r($row->name);
	echo '<h3>Set Schedule for ';
			$listofnames = "";
			foreach($row as $names) {
				$listofnames .=  $names->name." , ";
			}
			echo "<b>".rtrim($listofnames," , ")."</b>";
	echo '</h3>';
	
	echo '<form method="post">';	
	echo '<input id="submitType" name="submitType" type="hidden" value="setScheduleForStaff"/>';
	echo '<table>';
	echo '<tr valign="top">';	
		echo '<td>';			
			echo '<table class="tableInfo">';
			echo '<tr>
						<td>Effective Date</td>
						<td>
							<input type="text" id="effective_startdate" name="effective_startdate" class="forminput datepick" style="width:38%;" value="" placeholder="Start Date"/> &nbsp;&nbsp;&nbsp; To &nbsp;&nbsp;&nbsp;
							<input type="text" id="effective_enddate" name="effective_enddate" class="forminput datepick" style="width:38%;" value="" placeholder="End Date"/>
						</td>
					</tr>
					<tr>
						<td>Choose From Predefine Schedule</td>
						<td>
							<select name="presched" id="presched" onChange="onClickPreSched();">';								
								echo '<option value="0">Add New Custom Sched</option>';
								echo '<optgroup label="Predefine Schedule">';
								foreach($customSched as $sched) {;
									echo '<option value="'.$sched->custschedID.'">'.$sched->schedName.'</option>';
								};
							echo '</optgroup>';
							// echo '<optgroup label="Custom Time">';
								// foreach($alltime as $schedTime) {;
									// echo '<option value="'.$schedTime->timeValue.'">'.$schedTime->timeValue.'</option>';
								// };
							// echo '</optgroup>';
							echo '</select>
							<span id="spanid"></span>
						</td>
					</tr>
					<tr class="trlabel" align="center">
						<td colspan="2">Custom Schedule</td>						
					</tr>
					<tr >
						<td colspan="2">
							<table class="tableInfo">
								<tr>
									<td>Schedule Name</td>
									<td>
										<input id="schedname" type="text" class="padding5px" style="width:250px;"/>
									</td>
								</tr>
								<tr>
									<td>Every</td>
									<td>
										<select id="schedType" class="padding5px" style="width:140px;">
											<option value=0></option>
											<option value=1>First</option>
											<option value=2>Second</option>
											<option value=3>Third</option>
											<option value=4>Fourth</option>
											<option value=5>Last</option>
										</select>	
										&nbsp;&nbsp;&nbsp;
										(Select an option if specific day of the month)
									</td>
								</tr>
								<tr>
									<td>Sunday</td>';
									echo '<td>'.$this->scheduleM->customTimeDisplay($time, 'sunday').'</td>
								</tr>
								<tr>
									<td>Monday</td>
									<td>'.$this->scheduleM->customTimeDisplay($time, 'monday').'</td>
								</tr>
									<td>Tuesday</td>
									<td>'.$this->scheduleM->customTimeDisplay($time, 'tuesday').'</td>
								</tr>
									<td>Wednesday</td>
									<td>'.$this->scheduleM->customTimeDisplay($time, 'wednesday').' </td>
								</tr>
									<td>Thursday</td>
									<td>'.$this->scheduleM->customTimeDisplay($time, 'thursday').'</td>
								</tr>
									<td>Friday</td>
									<td>'.$this->scheduleM->customTimeDisplay($time, 'friday').'</td>
								</tr>
									<td>Saturday</td>			
									<td>'.$this->scheduleM->customTimeDisplay($time, 'saturday').'</td>
								</tr>
								<tr>
							</table>
						</td>
					</tr>
					<tr align="center">
						<td></td>						
						<td><input type="button" value="Submit" id="submitbutton"/></td>
					</tr>';
					
			echo '</table>';						
		echo '</td>';
	echo '</tr>';	
	echo '<table>';
	echo '</form>';

?>
