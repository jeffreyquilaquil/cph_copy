<h2>Manage Schedules</h2><hr/>

<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Custom Time</li>
	<li class="tab-link" data-tab="tab-2">Custom Schedule</li>
</ul>
<br/>
<div id="tab-1" class="tab-content current">	
	<div style="width:50%; float:left;">
		<h3>Add Time Category</h3>
		<table>
			<tr><td width="120px">Category Name</td><td><input type="text" class="padding5px" style="width:250px;" id="catName"/></td></tr>
			<tr><td><br/></td><td><button id="addTimeCategory" class="btnclass">Add Time Category</button></td></tr>
		</table>
	</div>
	
	<div style="width:45%; float:left; border-left:1px solid #ccc; padding:0 20px;">
		<h3>Add Time Option</h3>
		<table>
			<tr><td width="120px">Category</td>
				<td>
					<select id="timecategory" class="padding5px" style="width:260px;">
						<option></option>
					<?php
						foreach($timecategory AS $t):
							echo '<option value="'.$t->timeID.'">'.$t->timeName.'</option>';
						endforeach;
					?>
					</select>
				</td>
			</tr>
			<tr><td>Name (optional)</td><td><input type="text" id="timeName" class="padding5px" style="width:248px;"/></td></tr>
			<tr><td>Time</td>
				<td>
					<input type="text" id="timestart" class="timepickA padding5px" style="width:113px;" placeholder="start"/>&nbsp;&nbsp;
					<input type="text" id="timeend" class="timepickA padding5px" style="width:113px;" placeholder="end"/>
				</td></tr>
			<tr><td><br/></td><td><button id="addTime" class="btnclass">Add Time</button></td></tr>
		</table>
	</div>
	
<?php
	echo '<h3>Time Options</h3>';
	if(count($alltime)==0) echo 'No time records.';
	
	$time = array();
	foreach($alltime AS $t):
		if($t->category==0)
			$time[$t->timeID]['name'] = $t->timeName;
		else
			$time[$t->category][$t->timeID] = $t->timeValue.'|'.$t->timeName;
	endforeach;

	foreach($time AS $k=>$tt):
		echo '<table class="tableInfo">';
		echo '<tr class="trhead"><td colspan=2>'.$tt['name'].'</td>
				<td>
					<div class="hidden" id="editTime'.$k.'">
						<input type="text" name="timeName" class="padding5px" style="width:300px;" value="'.$tt['name'].'"/>
						<input type="hidden" name="start" class="padding5px" value=""/>
						<input type="hidden" name="end" class="padding5px" value=""/>
						<button id="btn'.$k.'" onClick="updateTime('.$k.')">Update</button>
						<button onClick="hideEditTime('.$k.')">Cancel</button>
					</div>
				</td>
				<td align="right">
					<img src="'.$this->config->base_url().'css/images/view-icon.png" width="28px" class="cpointer" onClick="showEditTime('.$k.')"/>';
				if(count($tt)==1) echo '<img src="'.$this->config->base_url().'css/images/delete-icon.png" width="20px" class="cpointer" onClick="deleteTime('.$k.')"/>';
			echo '</td>
			</tr>';			
		foreach($tt AS $n=>$xx):
			if($n!='name'){
				$ss = explode('|', $xx);
				$x = explode(' - ', $ss[0]);
				echo '<tr><td width="150px">'.$ss[0].'</td>
					<td width="150px">'.$ss[1].'</td>
					<td>
						<div class="hidden" id="editTime'.$n.'">
							<input type="text" name="timeName" class="padding5px" value="'.$ss[1].'"/>
							<input type="text" name="start" class="timepickA padding5px" value="'.$x[0].'"/>
							<input type="text" name="end" class="timepickA padding5px" value="'.$x[1].'"/>
							<button id="btn'.$n.'" onClick="updateTime('.$n.')">Update</button>
							<button onClick="hideEditTime('.$n.')">Cancel</button>
						</div>
					</td>
					<td align="right">
						<img src="'.$this->config->base_url().'css/images/view-icon.png" width="28px" class="cpointer" onClick="showEditTime('.$n.')"/>
						<img src="'.$this->config->base_url().'css/images/delete-icon.png" width="20px" class="cpointer" onClick="deleteTime('.$n.')"/>
					</td>
					</tr>';
			}
		endforeach;
		echo '</table>';
	endforeach;
?>
	
</div>
<!-------------------------- End of tab 1 ------------------------>
<div id="tab-2" class="tab-content">
	<h3>Add Custom Schedule</h3><hr class="gray"/>
	Name&nbsp;&nbsp;&nbsp;<input id="schedname" type="text" class="padding5px" style="width:250px;"/><br/><br/>
	Every&nbsp;&nbsp;&nbsp;
	<select id="schedType" class="padding5px" style="width:260px;">
		<option value=0></option>
		<option value=1>First</option>
		<option value=2>Second</option>
		<option value=3>Third</option>
		<option value=4>Fourth</option>
		<option value=5>Last</option>
	</select>	
	&nbsp;&nbsp;&nbsp;
	(Select an option if creating recurring schedule in a month)
	<br/><br/>
	
	<table class="tableInfo">
		<tr class="trlabel" align="center">
			<td>Sunday</td>
			<td>Monday</td>
			<td>Tuesday</td>
			<td>Wednesday</td>
			<td>Thursday</td>
			<td>Friday</td>
			<td>Saturday</td>			
		</tr>
		<tr>
			<td><?= $this->scheduleM->customTimeDisplay($time, 'sunday') ?></td>
			<td><?= $this->scheduleM->customTimeDisplay($time, 'monday') ?></td>
			<td><?= $this->scheduleM->customTimeDisplay($time, 'tuesday') ?></td>
			<td><?= $this->scheduleM->customTimeDisplay($time, 'wednesday') ?></td>
			<td><?= $this->scheduleM->customTimeDisplay($time, 'thursday') ?></td>
			<td><?= $this->scheduleM->customTimeDisplay($time, 'friday') ?></td>
			<td><?= $this->scheduleM->customTimeDisplay($time, 'saturday') ?></td>
		</tr>
	</table>
	<br/>
	<button id="addcustomschedule" class="btnclass">Add Custom Schedule</button>
<?php
	if(count($allCustomSched)>0){
		echo '<br/><br/><br/><hr/><h3/>All Custom Schedules</h3>';
		echo '<table class="tableInfo">';
		foreach($allCustomSched AS $acs){
			echo '<tr class="trhead ctimetr'.$acs->schedID.'">
					<td colspan=6>
						<span class="una">'.$acs->schedName.'</span>
						<input id="inputschedName'.$acs->schedID.'" type="text" value="'.$acs->schedName.'" style="width:250px;" class="padding5px ikalawa hidden"/></td>
					<td align="right">
						<img src="'.$this->config->base_url().'css/images/view-icon.png" width="28px" class="cpointer una" onClick="editCustSched('.$acs->schedID.')"/>
						<img src="'.$this->config->base_url().'css/images/delete-icon.png" width="20px" class="cpointer una" onClick="deleteCustSched('.$acs->schedID.')"/>
						<button class="hidden ikalawa" onClick="updateCustSched('.$acs->schedID.')">Update</button>
						<button class="hidden ikalawa" onClick="cancelCustSched('.$acs->schedID.')">Cancel</button>
						<img src="'.$this->config->base_url().'css/images/small_loading.gif" width="20px" class="hidden loading"/>
					</td>
				</tr>';
			if($acs->schedType!=0){
				echo '<tr class="ctimetr'.$acs->schedID.'"><td colspan="7">Every '.$schedTypeArr[$acs->schedType];
					if($acs->sunday!=0 ) echo ' Sunday ';
					else if($acs->monday!=0 ) echo ' Monday ';
					else if($acs->tuesday!=0 ) echo ' Tuesday ';
					else if($acs->wednesday!=0 ) echo ' Wednesday ';
					else if($acs->thursday!=0 ) echo ' Thursday ';
					else if($acs->friday!=0 ) echo ' Friday ';
					else if($acs->saturday!=0 ) echo ' Saturday ';
					echo 'of the Month';
					
					echo '&nbsp;&nbsp;&nbsp;<select id="schedType" class="padding5px ikalawa hidden" style="width:150px;">
						<option value=0></option>
						<option value=1 '.(($acs->schedType==1)?'selected="selected"':'').'>First</option>
						<option value=2 '.(($acs->schedType==2)?'selected="selected"':'').'>Second</option>
						<option value=3 '.(($acs->schedType==3)?'selected="selected"':'').'>Third</option>
						<option value=4 '.(($acs->schedType==4)?'selected="selected"':'').'>Fourth</option>
						<option value=5 '.(($acs->schedType==5)?'selected="selected"':'').'>Last</option>
					</select>';
					
				echo '</td></tr>';
			}
			echo '<tr align="center" class="trlabel">
					<td>Sunday</td>
					<td>Monday</td>
					<td>Tuesday</td>
					<td>Wednesday</td>
					<td>Thursday</td>
					<td>Friday</td>
					<td>Saturday</td>			
				</tr>';
			echo '<tr class="ctimetr'.$acs->schedID.'">
				<td>'.$this->scheduleM->customTimeDisplay($time, 'sunday', $acs->sunday, false).'</td>
				<td>'.$this->scheduleM->customTimeDisplay($time, 'monday', $acs->monday, false).'</td>
				<td>'.$this->scheduleM->customTimeDisplay($time, 'tuesday', $acs->tuesday, false).'</td>
				<td>'.$this->scheduleM->customTimeDisplay($time, 'wednesday', $acs->wednesday, false).'</td>
				<td>'.$this->scheduleM->customTimeDisplay($time, 'thursday', $acs->thursday, false).'</td>
				<td>'.$this->scheduleM->customTimeDisplay($time, 'friday', $acs->friday, false).'</td>
				<td>'.$this->scheduleM->customTimeDisplay($time, 'saturday', $acs->saturday, false).'</td>
			</tr>';
			echo '<tr><td colspan=7><br/></td></tr>';
		}
		echo '</table>';
	}
?>
</div>

<script type="text/javascript">
	$(function(){
		$('.timepickA').datetimepicker({ format:'h:i a', datepicker:false });
		
		$('#addTimeCategory').click(function(){
			if($('#catName').val()==''){
				alert('Category name is empty.');
			}else{
				$(this).html('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/>');
				$.post('<?= $this->config->item('career_uri') ?>',{ submitType:'addtimecategory',name:$('#catName').val()}, 
				function(){
					$('#catName').val('');
					location.reload(true);
					alert('Category name has been added.');	
				});
			}
		});
		
		$('#addTime').click(function(){
			if($('#timecategory').val()=='' || $('#timestart').val()=='' || $('#timeend').val()==''){
				alert('Please input missing values.');
			}else{
				$(this).html('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/>');
				$.post('<?= $this->config->item('career_uri') ?>',{ submitType:'addtime',name:$('#timeName').val(),start:$('#timestart').val(),end:$('#timeend').val(),cat:$('#timecategory').val()}, function(){
					location.reload(true);
					alert('Time has been added.');	
					$('#timecategory').val('');
					$('#timeName').val('');
					$('#timestart').val('');
					$('#timeend').val('');
				});		
			}
		});
		
		$('#addcustomschedule').click(function(){
			celebrity = '';
			if($('#schedname').val()==''){
				celebrity += 'Name is empty.\n';
			}
			if($('#sunday').val()=='' && $('#monday').val()=='' && $('#tuesday').val()=='' && $('#wednesday').val()=='' && 
				$('#thursday').val()=='' && $('#friday').val()=='' && $('#saturday').val()==''){
				celebrity += 'Schedules are empty.';
			}
			if(celebrity!=''){
				alert(celebrity);
			}else{
				$.post('<?= $this->config->item('career_uri') ?>',{ 
					submitType:'addCustomSched',
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
					alert('Custom Schedule has been added.');										
				});
			}
		});
	});	
	
	function deleteTime(id){
		if(confirm('Are you sure you want to delete this time?')){			
			$.post('<?= $this->config->item('career_uri') ?>',{ submitType:'deleteTime',id:id}, function(){
				location.reload(true);
				alert('Time has been deleted.');				
			});
		}
	}
	
	function showEditTime(id){
		$('#editTime'+id).removeClass('hidden');
	}
	
	function hideEditTime(id){
		$('#editTime'+id).addClass('hidden');
	}
	
	function updateTime(id){
		$('#btn'+id).html('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/>');
		$.post('<?= $this->config->item('career_uri') ?>',{ submitType:'updateTime',id:id,timeName:$('#editTime'+id+' input[name="timeName"]').val(),start:$('#editTime'+id+' input[name="start"]').val(),end:$('#editTime'+id+' input[name="end"]').val()}, function(){
			$('#editTime'+id).addClass('hidden');
			location.reload(true);
			alert('Time has been updated.');
		});
	}
	
	function editCustSched(id){
		$('.ctimetr'+id+' .everyday').removeAttr('disabled');
		$('.ctimetr'+id+' .una').addClass('hidden');
		$('.ctimetr'+id+' .ikalawa').removeClass('hidden');
	}
	
	function cancelCustSched(id){
		$('.ctimetr'+id+' .everyday').attr('disabled','disabled');
		$('.ctimetr'+id+' .una').removeClass('hidden');
		$('.ctimetr'+id+' .ikalawa').addClass('hidden');
	}
	
	function updateCustSched(id){
		celebrity = '';
		if($('#inputschedName'+id).val()==''){
			celebrity += 'Name is empty.\n';
		}
		if($('.ctimetr'+id+'#sunday').val()=='' && $('.ctimetr'+id+'#monday').val()=='' && $('.ctimetr'+id+'#tuesday').val()=='' && $('.ctimetr'+id+'#wednesday').val()=='' && 
			$('.ctimetr'+id+'#thursday').val()=='' && $('.ctimetr'+id+'#friday').val()=='' && $('.ctimetr'+id+'#saturday').val()==''){
			celebrity += 'Schedules are empty.';
		}
		if(celebrity!=''){
			alert(celebrity);
		}else{	
			$('.ctimetr'+id+' .loading').removeClass('hidden');
			$('.ctimetr'+id+' button.ikalawa').addClass('hidden');
		
			$.post('<?= $this->config->item('career_uri') ?>',{ 
				submitType:'updateCustomSched',
				schedID:id,
				schedName:$('#inputschedName'+id).val(),
				schedType:$('.ctimetr'+id+' #schedType').val(),
				sunday:$('.ctimetr'+id+' #sunday').val(),
				monday:$('.ctimetr'+id+' #monday').val(),
				tuesday:$('.ctimetr'+id+' #tuesday').val(),
				wednesday:$('.ctimetr'+id+' #wednesday').val(),
				thursday:$('.ctimetr'+id+' #thursday').val(),
				friday:$('.ctimetr'+id+' #friday').val(),
				saturday:$('.ctimetr'+id+' #saturday').val()
			}, function(){
				location.reload(true);
				alert('Custom schedule has been updated.');
			});
		}
	}
	
	function deleteCustSched(id){
		if(confirm('Are you sure you want to delete this custom schedule?')){
			$('.ctimetr'+id+' .loading').removeClass('hidden');
			$('.ctimetr'+id+' .una').addClass('hidden');
			$.post('<?= $this->config->item('career_uri') ?>',{ submitType:'deleteCustomSched',id:id}, function(){
				location.reload(true);
				alert('Custom schedule has been deleted.');				
			});
		}
	}
	
</script>
