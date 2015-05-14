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

	$('.timepickA').datetimepicker({ format:'h:i a', datepicker:false });
	$('#schedname').removeAttr('disabled');
	$('#schedType').removeAttr('disabled');
	$('#sunday').removeAttr('disabled');
	$('#monday').removeAttr('disabled');
	$('#tuesday').removeAttr('disabled');
	$('#wednesday').removeAttr('disabled');
	$('#thursday').removeAttr('disabled');
	$('#friday').removeAttr('disabled');
	$('#saturday').removeAttr('disabled');		
	$('#div_create').hide();		

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
				$("#spanid").html('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/>');
				$.post('<?= $this->config->item('career_uri') ?>',{					
					submitType:'setScheduleForStaff',
					presched:$('#presched').val(),
					effective_startdate:$('#effective_startdate').val(),
					effective_enddate:$('#effective_enddate').val(),
					schedName:$('#schedname').val(),
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
		});			
	
	
	$("#addSchedule").click(function(){	
			if($('#predefinesched').val() == "") {
					alert('No schedule was selected');
			}
			else{
				$.post('<?= $this->config->base_url().'schedules/addScheduleTime/' ?>',{ 
					buttonsubmit:'addScheduleButton',
					scheduleTime:$('#predefinesched').val()				
				}, function(d){
					location.reload(true);				
					alert('Custom schedule has been updated.');
				});
			}
	});
	
	$("#Addnow").click(function(){			
		var catergoryname = $('#listofcategoryname').val();
		var valueofnewcategoryname = "";
		var predefinetimename = "";
		if(catergoryname == ""){
			alert('No Category was selected');
		}
		else{
			if( catergoryname == "addnewcategoryname")
				valueofnewcategoryname = $('#new_name').val();
			else
				predefinetimename = catergoryname;
				
			// alert(predefinetimename);
			$.post('<?= $this->config->base_url().'schedules/addScheduleTime/' ?>',{
				buttonsubmit:'AddNowbutton',
				newcategoryname: valueofnewcategoryname,
				newtimeschedname : $('#time_name').val(),
				defineschedtime : predefinetimename,
				starttime : $('#starttime').val(),
				endtime : $('#endtime').val()
				
			}, function(){
				location.reload(true);								
				alert('Custom schedule has been updated.');
			});		
		}
	});
	
});
	
function showNewNameField(value) {	
	if(value == "addnewcategoryname")
		 $("#fornewcategoryname").html('<input type="text" name="new_name" id="new_name" class="padding5px" placeholder="New Category Name Here"><br/><br/>');
	else
		$("#fornewcategoryname").html('');
}

function changepresched(value) {	
	if(value == "createnewsched") {
		$( "#div_create" ).show( "slow", function() {});		
		$( "#addSchedule" ).hide( "slow", function() {});
	}
	else{
		$( "#div_create" ).hide( "slow", function() {});
		$( "#addSchedule" ).show( "slow", function() {});
	}
}


</script>
<style>
ul.a {
    list-style-type: none;

}

ul.a li{
		margin-bottom:10px;
		margin-top:10px;
}


ul.b {
    list-style-type: square;
}


</style>
<?php
	if(count($alltime)==0) echo 'No time records.';	
	$time = array();
	foreach($alltime AS $t):
		if($t->category==0)
			$time[$t->timeID]['name'] = $t->timeName;
		else
			$time[$t->category][$t->timeID] = $t->timeValue.'|'.$t->timeName;
	endforeach;

	
	// echo '<form method="post">';	
	// $stringofkeys = "";
	// $stringofid = "";

	echo '<input id="submitType" name="submitType" type="hidden" value="setScheduleForStaff"/>';
	$this->session->set_userdata('rowvaluesession',$row);
	echo '<table border="0" width="100%">
				<tr>';
				echo '<td valign="top">'; 
						echo '<h3>Set Schedule for </h3>';		
						echo '<ul class="a">';
						foreach($row as $key=>$value) {							
							echo '<li>'.$key;
								echo '<ul  class="b">';
										foreach($value as $maonani) {
											echo '<li>';											
											echo $maonani->name;
											echo '</li>';
										}					
								echo '</ul>';
							echo '</li>';	
							
						}
						echo '</ul>';				
				echo '</td>';
				echo '<td valign="top">';
							echo '<table border="0">';
							echo '<tr>';	
							echo '<td></td>';	
							echo '</tr>';	
							echo '<tr valign="top">';	
								echo '<td>';			
									echo '<table>';
									echo '
											<tr>
												<td>Choose From Predefine Schedule</td>
											</tr>
											<tr>
												<td>													
													<select id="predefinesched" class="schedSelect everyday padding5px" onchange="changepresched(this.value);">
															<option value=""></option>
															<option value="createnewsched" >Create New Schedule</option>';					
															foreach($time AS $t=>$t2):
																echo '<optgroup label="'.$t2['name'].'">';						
																foreach($t2 AS $k=>$t):
																	if($k!='name'){
																		$ex = explode('|', $t);
																		echo '<option value="'.$k.'" '.(($k==$v)?'selected="selected"':'').'>'.$ex[0].'</option>';
																	}
																endforeach;
																echo '</optgroup>';
															endforeach;					
															
												
													echo '<option value=""></option>
												</select>													
												</td>
											</tr>';											
									echo '</table>';						
								echo '</td>';
							echo '</tr>';	
							echo '<tr height="10px">';	
							echo '<td></td>';	
							echo '</tr>';	
							echo '<tr>';	
							echo '<td>
										<input type="button" name="addSchedule" id="addSchedule" value="Set Schedule">
										
									</td>';
							echo '</tr>';	
							echo '<table>';
				echo '</td>';
				echo '<tr>';	
	echo '<table>';
	
	
	?>
	<div id="div_create">
		 <!--<fieldset><legend>New Schedule</legend>		-->
			<br/>Category Name <br/><br/>
			<select class="padding5px" name="listofcategoryname" id="listofcategoryname" onchange="showNewNameField(this.value);">				
				<option></option> 
				<option value="addnewcategoryname">Add New Category Name</option> 
				<?php foreach($timeNameList as $listoftimenames) {
					echo '<option value="'.$listoftimenames->timeID.'">'.$listoftimenames->timeName.'</option>';
				}?>
				
			</select>&nbsp;&nbsp;  
			<br/><br/>
			<span id="fornewcategoryname"></span>
			<input type="text" name="time_name" id="time_name" class="padding5px" placeholder="Time New Name Here"><br/><br/>
			<input type="text" id="starttime" class="timepickA padding5px" style="width:113px;" placeholder="start time"/>&nbsp;&nbsp;
			<input type="text" id="endtime" class="timepickA padding5px" style="width:113px;" placeholder="end time"/><br/><br/><br/><br/>
			<input type="button" name="Addnow" id="Addnow" value="Add & Assign Schedule"><br/><br/><br/><br/>
		<!--</fieldset>-->
	</div>
	<?php
	
	// echo '</form>';

?>
