<h2>Manage Schedule Settings</h2><hr/>

<div id="schedSettingsDIV" class="schedDiv">
<button id="btnsettingupdate" class="btnclass btnorange" style="float:right; margin-top:-45px;">Update</button>
<table class="tableInfo">
<?php	
	$optArr = array('-8','-7','-6','-5','-4','-3', '-2','-1','0','+1','+2','+3','+4','+5','+6','+7','+8');	
	foreach($settingsQuery AS $s){
		echo '<tr>';
			echo '<td width="70%"><b>'.$s->settingName.'</b><br/>
				<i>'.$s->settingNote.'</i>				
				</td>';
			echo '<td>
					<span class="spanSet" id="val'.$s->settingID.'">'.$s->settingVal.'</span>';
			echo '<select class="selectSet forminput hidden" onChange="changeSetting('.$s->settingID.', this);">';
				foreach($optArr AS $o){
					echo '<option value="'.$o.' hours" '.(($s->settingVal==$o.' hours')?'selected="selected"':'').'>'.$o.' hours</option>';
				}
			echo '</select>';
			echo '</td>';
		echo '</tr>';
	}
	
	if(count($settingsQuery)>0){
		echo '<tr>';
			echo '<td colspan=2 align="right">
					<button id="btnsettingcancel" class="btnclass selectSet hidden">Cancel Update</button>
				</td>';
		echo '</tr>';
	}
?>
</table>
</div>

<!-------------------------- START Custom Time ------------------------>
<div id="customtimeDIV" class="schedDiv hidden">	
	<div style="width:48%; float:left;">
		<h3>Add Time Category</h3>
		<table>
			<tr><td width="120px">Category Name</td><td><input type="text" class="padding5px" style="width:250px;" id="catName"/></td></tr>
			<tr><td><br/></td><td><button id="addTimeCategory" class="btnclass btngreen">+ Add Time Category</button></td></tr>
		</table>
	</div>
<?php if(count($timecategory)>0){ ?>
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
				</td>
			</tr>
			<tr><td># of Paid Hours</td><td><input type="number" id="timeHours" class="padding5px" style="width:248px;"/></td></tr>
			<tr><td><br/></td><td><button id="addTime" class="btnclass btngreen">+ Add Time</button></td></tr>
		</table>
	</div>
<?php
}
	
	echo '<h3>Time Options</h3>';
	if(count($alltime)==0) echo 'No time records.';
	
	$time = array();
	foreach($alltime AS $t):
		if($t->category==0)
			$time[$t->timeID]['name'] = $t->timeName;
		else
			$time[$t->category][$t->timeID] = $t->timeValue.'|'.$t->timeName.'|'.$t->timeHours;
	endforeach;
	
	foreach($time AS $k=>$tt):
		echo '<table class="tableInfo">';
		echo '<tr class="trhead"><td colspan=2>'.$tt['name'].'</td>
				<td>
					<div class="hidden" id="editTime'.$k.'">
						<input type="text" name="timeName" class="padding5px" style="width:300px;" value="'.$tt['name'].'"/>
						<input type="hidden" name="start" class="padding5px" value=""/>
						<input type="hidden" name="end" class="padding5px" value=""/>
						<br/><button id="btn'.$k.'" onClick="updateTime('.$k.')">Update</button>
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
					<td width="150px">'.$ss[1].' ['.$ss[2].'h]</td>
					<td>
						<div class="hidden" id="editTime'.$n.'">
							<input type="text" name="timeName" class="padding5px" placeholder="Name" value="'.$ss[1].'"/>
							<input type="text" name="start" class="timepickA padding5px" placeholder="Time Starts" value="'.$x[0].'"/>
							<input type="text" name="end" class="timepickA padding5px" placeholder="Time Ends" value="'.$x[1].'"/>
							<input type="number" name="timeHours" class="padding5px" placeholder="# of Paid Hours" value="'.$ss[2].'"/>
							<br/>
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
<!-------------------------- END Custom Time ------------------------>

<!-------------------------- Start Custom Schedules ------------------------>
<div id="customschedDIV" class="schedDiv hidden">
	<div id="addCustomSchedDiv" class="hidden">	
		<h3>Add Custom Schedule</h3><hr class="gray"/>
		Name&nbsp;&nbsp;&nbsp;<input id="schedname" type="text" class="padding5px" style="width:250px;"/><br/><br/>
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
			<?php
				foreach($weekdayArray AS $w){
					echo '<td>';
						echo '<select id="'.$w.'" class="schedSelect everyday padding5px">';
							echo $this->textM->customTimeSelect();
						echo '</select>';
					echo '</td>';
				}
			?>
			</tr>
		</table>
		<br/>
		<button id="addcustomschedule" class="btnclass btngreen">+ Add Schedule</button>
		<button class="btnclass" onClick="$('#addCustomSchedDiv').hide(); $('#addCustomSchedbtn').show();">Cancel</button>
		<hr/>
	</div>
	
	<h3/>All Custom Schedules
		<button id="addCustomSchedbtn" class="btnclass btngreen" style="float:right; margin-top:-10px;" onClick="$(this).hide(); $('#addCustomSchedDiv').show(); ">+ Add Custom Schedule</button>
	</h3>
	<table class="tableInfo">
<?php
	foreach($allCustomSched AS $acs){
		echo '<tr class="trhead ctimetr'.$acs->custSchedID.'">
				<td colspan=6>
					<span class="una">'.$acs->schedName.'</span>
					<input id="inputschedName'.$acs->custSchedID.'" type="text" value="'.$acs->schedName.'" style="width:250px;" class="padding5px ikalawa hidden"/></td>
				<td align="right">
					<img src="'.$this->config->base_url().'css/images/view-icon.png" width="28px" class="cpointer una" onClick="editCustSched('.$acs->custSchedID.')"/>
					<img src="'.$this->config->base_url().'css/images/delete-icon.png" width="20px" class="cpointer una" onClick="deleteCustSched('.$acs->custSchedID.')"/>
					<button class="hidden ikalawa" onClick="updateCustSched('.$acs->custSchedID.')">Update</button>
					<button class="hidden ikalawa" onClick="cancelCustSched('.$acs->custSchedID.')">Cancel</button>
					<img src="'.$this->config->base_url().'css/images/small_loading.gif" width="20px" class="hidden loading"/>
				</td>
			</tr>';
		echo '<tr align="center" class="trlabel">
				<td>Sunday</td>
				<td>Monday</td>
				<td>Tuesday</td>
				<td>Wednesday</td>
				<td>Thursday</td>
				<td>Friday</td>
				<td>Saturday</td>			
			</tr>';
		echo '<tr align="center" class="ctimetrtext_'.$acs->custSchedID.' fs11px">
				<td>'.$timeArr[$acs->sunday].'</td>
				<td>'.$timeArr[$acs->monday].'</td>
				<td>'.$timeArr[$acs->tuesday].'</td>
				<td>'.$timeArr[$acs->wednesday].'</td>
				<td>'.$timeArr[$acs->thursday].'</td>
				<td>'.$timeArr[$acs->friday].'</td>
				<td>'.$timeArr[$acs->saturday].'</td>
			</tr>';
			
		echo '<tr class="ctimetredit'.$acs->custSchedID.' hidden">';
			foreach($weekdayArray AS $w){
				echo '<td>';
					echo '<select id="'.$w.'" class="schedSelect everyday padding5px disabled">';
						echo $this->textM->customTimeSelect($acs->$w);
					echo '</select>';
				echo '</td>';
			}
		echo '</tr>';
		
		echo '<tr><td colspan=7><br/></td></tr>';
	}
?>
	</table>
</div>
<!-------------------------- END Custom Schedules ------------------------>

<!-------------------------- Start of Holiday/Event Schedule ------------------------>
<div id="holeventschedDIV" class="schedDiv hidden" style="margin-top:15px;">
	<div id="addHolidaySchedDiv" class="hidden">
		<?php $this->load->view('v_schedule/v_holiday'); ?>
	</div>
	<h3>All Holiday/Event Schedules
		<button id="addHolidaybtn" class="btnclass btngreen" style="float:right; margin-top:-10px;" onClick="$(this).hide(); $('#addHolidaySchedDiv').show();">+ Add Holiday/Event Schedule</button>	
	</h3>
	<table class="tableInfo">
		<tr class="trlabel">
			<td>Date</td>
			<td>Weekday</td>
			<td>Name</td>
			<td>Type</td>
			<td>Repetition</td>
			<td>Work Day?</td>
			<td>Premium Pay</td>
			<td><br/></td>
		</tr>
	<?php
		foreach($holidaySchedArr AS $hol){
			echo '<tr class="holidaycolor_'.$hol->holidayType.'">
					<td>'.date('F d', strtotime($hol->holidayDate)).'</td>
					<td>'.date('l', strtotime($hol->holidayDate)).'</td>
					<td>'.$hol->holidayName.'</td>
					<td>'.$allDayTypes[$hol->holidayType].'</td>
					<td>'.(($hol->holidaySched==0)?'Yearly':'This Year Only').'</td>
					<td>'.(($hol->holidayWork==0)?'No':'Yes').'</td>
					<td>'.$hol->holidayPremium.'%</td>
					<td><a href="#" onClick="editHoliday('.$hol->holidayID.', this)"><img src="'.$this->config->base_url().'css/images/icon-options-edit.png"/></a></td>
				</tr>';
		}
	?>
	</table>
</div>
<!-------------------------- End of Holiday/Event Schedule ------------------------>


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
			if($('#timecategory').val()=='' || $('#timestart').val()=='' || $('#timeend').val()=='' || $('#timeHours').val()=='' || $.isNumeric($('#timeHours').val())==false){
				alert('Please input missing values and invalid values.');
			}else{
				$(this).html('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/>');
				$.post('<?= $this->config->item('career_uri') ?>',{ submitType:'addtime',name:$('#timeName').val(),start:$('#timestart').val(),end:$('#timeend').val(),timeHours:$('#timeHours').val(),cat:$('#timecategory').val()}, function(){
					location.reload(true);
					alert('Time has been added.');	
					$('#timecategory').val('');
					$('#timeName').val('');
					$('#timestart').val('');
					$('#timeend').val('');
					$('#timeHours').val('');
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
					sunday:$('option:selected', '#sunday').data('id'),
					monday:$('option:selected', '#monday').data('id'),
					tuesday:$('option:selected', '#tuesday').data('id'),
					wednesday:$('option:selected', '#wednesday').data('id'),
					thursday:$('option:selected', '#thursday').data('id'),
					friday:$('option:selected', '#friday').data('id'),
					saturday:$('option:selected', '#saturday').data('id')
				}, function(d){	alert(d);
					location.reload(true);	
					alert('Custom Schedule has been added.');							
				});
			}
		});
		
		$('#btnsettingupdate').click(function(){
			$(this).hide();
			$('.spanSet').hide();
			$('.selectSet').show();			
		});
		
		$('#btnsettingcancel').click(function(){
			$('#btnsettingupdate').show();
			$('.spanSet').show();
			$('.selectSet').hide();			
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
		$.post('<?= $this->config->item('career_uri') ?>',{ submitType:'updateTime',id:id,timeName:$('#editTime'+id+' input[name="timeName"]').val(),start:$('#editTime'+id+' input[name="start"]').val(),end:$('#editTime'+id+' input[name="end"]').val(),timeHours:$('#editTime'+id+' input[name="timeHours"]').val()}, function(){
			$('#editTime'+id).addClass('hidden');
			location.reload(true);
			alert('Time has been updated.');
		});
	}
	
	function editCustSched(id){
		$('.ctimetredit'+id+' .everyday').removeAttr('disabled');
		$('.ctimetr'+id+' .una').addClass('hidden');
		$('.ctimetr'+id+' .ikalawa').removeClass('hidden');
		
		$('.ctimetredit'+id).removeClass('hidden');
		$('.ctimetrtext_'+id).addClass('hidden');		
	}
	
	function cancelCustSched(id){
		$('.ctimetredit'+id+' .everyday').attr('disabled','disabled');
		$('.ctimetr'+id+' .una').removeClass('hidden');
		$('.ctimetr'+id+' .ikalawa').addClass('hidden');
		
		$('.ctimetredit'+id).addClass('hidden');
		$('.ctimetrtext_'+id).removeClass('hidden');
	}
	
	function updateCustSched(id){
		celebrity = '';
		if($('#inputschedName'+id).val()==''){
			celebrity += 'Name is empty.\n';
		}
		
		if($('.ctimetredit'+id+' #sunday').val()=='' && $('.ctimetredit'+id+' #monday').val()=='' && $('.ctimetredit'+id+' #tuesday').val()=='' && $('.ctimetredit'+id+' #wednesday').val()=='' && $('.ctimetredit'+id+' #thursday').val()=='' && $('.ctimetredit'+id+' #friday').val()=='' && $('.ctimetredit'+id+' #saturday').val()==''){
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
				sunday: $('option:selected', '.ctimetredit'+id+' #sunday').data('id'),
				monday: $('option:selected', '.ctimetredit'+id+' #monday').data('id'),
				tuesday: $('option:selected', '.ctimetredit'+id+' #tuesday').data('id'),
				wednesday: $('option:selected', '.ctimetredit'+id+' #wednesday').data('id'),
				thursday: $('option:selected', '.ctimetredit'+id+' #thursday').data('id'),
				friday: $('option:selected', '.ctimetredit'+id+' #friday').data('id'),
				saturday: $('option:selected', '.ctimetredit'+id+' #saturday').data('id')
			}, function(){
				alert('Custom schedule has been updated.');
				location.reload(true);
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
	
	function changeSetting(id, jee){
		if(confirm('Are you sure you want to update this setting?')){
			displaypleasewait();
			$.post('<?= $this->config->base_url().'schedules/' ?>',{submitType:'updateSettings', id:id, setVal:$(jee).val()},
			function(){
				parent.$.colorbox.close();
				$(jee).hide();
				$('#val'+id).text($(jee).val());
				$('#val'+id).show();
			});
		}
	}
</script>
