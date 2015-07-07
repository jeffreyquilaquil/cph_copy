<h3>Add Holiday/Event Schedules</h3><hr class="gray"/>
<table class="tableInfo">
	<tr>
		<td style="width:30%">Holiday/Event Name</td>
		<td><input type="text" id="holidayName" class="forminput" value=""/></td>
	</tr>
	<tr>
		<td>Holiday/Event Type</td>
		<td>
			<select id="holidayType" class="forminput">
			<?php
				foreach($allDayTypes AS $ky=>$kyval){
					echo '<option value="'.$ky.'">'.$kyval.'</option>';
				}					
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td>Holiday/Event Date</td>
		<td><?php
			echo '<select id="month" class="padding5px">';
				for($m=1; $m<=12; $m++){
					echo '<option value="'.$m.'" '.((isset($mmnum) && $mmnum==$m)?'selected="selected"':'').'>'.date('F', strtotime('2015-'.$m)).'</option>';
				}
			echo '</select>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<select id="day" class="padding5px">';
				for($d=1; $d<=31; $d++){
					echo '<option value="'.$d.'" '.((isset($mmday) && $mmday==$d)?'selected="selected"':'').'>'.$d.'</option>';
				}
			echo '</select>';
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			echo '<select id="year" class="padding5px">';
				echo '<option value="0000">0000</option>';
				echo '<option value="'.date('Y').'">'.date('Y').'</option>';
				echo '<option value="'.date('Y',strtotime('+ 1 year')).'">'.date('Y',strtotime('+ 1 year')).'</option>';
				
			echo '</select>';
			echo ' <span class="colorgray">Choose 0000 if holiday is yearly</span>';
		?></td>
	</tr>	
	<tr>
		<td>Work Day?</td>
		<td>
			<select id="holidayWork" class="forminput">
			<?php
				$yesno01 = $this->textM->constantArr('yesno01');
				foreach($yesno01 AS $y=>$yval){
					echo '<option value="'.$y.'">'.$yval.'</option>';
				}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<td><br/></td>
		<td>
			<input type="hidden" value="0" id="holidaySched"/>
			<input type="hidden" value="" id="holidayID"/>
			<input type="submit" value="+ Add Schedule" id="addHolidaySched" class="btnclass"/>
			<input type="button" value="Cancel" id="addHolidayCancel" class="btnclass"/>				
		</td>
	</tr>
</table>
<br/>

<script type="text/javascript">
	$(function(){	
		$('#addHolidaySched').click(function(){
			if($('#holidayName').val()==''){
				alert('Holiday/Event name is empty.');
			}else{
				$('#addHolidaySched').attr('disabled','disabled');
				$('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/>').insertAfter('#addHolidaySched');
				
				if($(this).val()=='Update Schedule') subvalue = 'updateHoliday';
				else subvalue = 'addHoliday';
				
				$.post('<?= $this->config->base_url().'schedules/' ?>',{ 
					submitType:subvalue,
					holidayID:$('#holidayID').val(),
					holidayName:$('#holidayName').val(),
					holidayType:$('#holidayType').val(),
					holidaySched:$('#holidaySched').val(),
					holidayWork:$('#holidayWork').val(),
					holidayDate:$('#year').val()+'-'+$('#month').val()+'-'+$('#day').val()
				}, 
				function(){
					//location.reload(true);
					if(subvalue=='updateHoliday') alert('Holiday/Event schedule has been updated.');	
					else alert('Holiday/Event schedule has been added.');
					parent.location.reload();
					parent.$.colorbox.close();
				});
			}
		});
	
		$('#addHolidayCancel').click(function(){
			$('#addHolidaybtn').show();
			$('#addHolidaySchedDiv').hide();
			$('#holidayName').val('');
			$('#holidayType').val(0);
			$('#month').val(1);
			$('#day').val(1);
			$('#year').val('0000');
			$('#holidaySched').val(0);
			$('#holidayWork').val(0);
			parent.$.colorbox.close();
		});
		
		$('#year').change(function(){			
			if($(this).val()!='0000')
				$("#holidaySched").val(1);
			else
				$("#holidaySched").val(0);
		});
	});	
	
	function editHoliday(id, ths){				
		$(ths).html('<?= '<img src="'.$this->config->base_url().'css/images/small_loading.gif" width="20px"/>' ?>');
		
		$.post('<?= $this->config->base_url().'schedules/' ?>',{submitType:'getHolidayData', id:id}, 
		function(data){
			dd = data.split('|');
			$('#holidayID').val(id);
			$('#holidayName').val(dd[0]);
			$('#holidayType').val(dd[1]);
			$('#holidaySched').val(dd[2]);
			$('#holidayWork').val(dd[3]);
			
			//for date
			yy = dd[4].split('-');
			$('#year').val(yy[0]);
			$('#month').val(parseInt(yy[1]));
			$('#day').val(parseInt(yy[2]));
			
			$('#addHolidaybtn').hide();
			$('#addHolidaySchedDiv').show();
			$('#addHolidaySchedDiv h3').html('Modify Holiday/Event Schedule');
			$('#addHolidaySched').val('Update Schedule');
			$(ths).html('<img src="<?= $this->config->base_url() ?>css/images/icon-options-edit.png"/>');
		});
	}
</script>
