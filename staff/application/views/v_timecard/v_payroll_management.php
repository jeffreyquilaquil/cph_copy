<button class="floatright btnclass" onClick="location.href='<?= $this->config->base_url() ?>timecard/managepayroll/'"><< Back to Manage Payroll</button>
<h2><?= $managePayOptionsArr[$type] ?></h2>
<hr/>

<b>Payroll Type: </b><?php
	$paytype = $this->textM->constantArr('payrollType');
	echo $paytype[$computationtype].'<br/>';
	echo 'From '.date('F d, Y', strtotime($start)).' to '.date('F d, Y', strtotime($end)).'<br/><br/>';
	echo $this->textM->formfield('hidden', 'computationtype', $computationtype);
?>

<div style="padding-bottom:5px;">
	<a class="cpointer" id="selectAll">Select All</a> | <a class="cpointer" id="deselectAll">Deselect All</a>
</div>

<div style="overflow: auto;">
	<table id="tblPayroll" class="tableInfo tacenter">
		<tr class="trlabel">
			<td><div style="width:200px;">Day Type:</div></td>
		<?php
			foreach($dataDates AS $d){
				$wday = date('D', strtotime($d->dateToday));
				if($wday=='Sat' || $wday=='Sun') echo '<td bgcolor="#000">';
				else echo '<td>';				
				
					echo '<div style="width:130px;">';
						echo date('D', strtotime($d->dateToday)).'<br/>'.date('d M', strtotime($d->dateToday)).'<br/>';
						echo $this->textM->formfield('selectoption', 'holidayType['.$d->dateToday.']', $d->holidayType, 'payrollSelectReviewDate', '', 'onChange="holidayChange(\''.$d->dateToday.'\', this)"', $this->textM->constantArr('holidayTypes'));
					echo '</div>';
				echo '</td>';
			}
		?>
		</tr>
		
		<tr>
			<td>
		<?php //other info
			echo '<form id="formdates" action="" method="POST" onSubmit="displaypleasewait();">';
				echo $this->textM->formfield('hidden', 'submitType', 'changeHoliday');
				echo $this->textM->formfield('hidden', 'holidayType', '0');
				echo $this->textM->formfield('hidden', 'dtoday', '');
				
				echo $this->textM->formfield('hidden', 'computationtype', $computationtype);
				echo $this->textM->formfield('hidden', 'type', $_POST['type']);
				echo $this->textM->formfield('hidden', 'start', $_POST['start']);
				echo $this->textM->formfield('hidden', 'end', $_POST['end']);
				echo $this->textM->formfield('hidden', 'empIDs', $_POST['empIDs']);	 
			echo '</form>';
		?>
			<br/></td>
		<?php
			foreach($dataDates AS $d){				
				$wday = date('D', strtotime($d->dateToday));
				if($wday=='Sat' || $wday=='Sun') echo '<td bgcolor="#aaa">';
				else echo '<td>';
					
				echo '<b>';
					echo 'HW&nbsp;&nbsp;|&nbsp;&nbsp;D&nbsp;&nbsp;|&nbsp;&nbsp;ND';
					
					$holiday = $this->payrollM->isHoliday(date('Y-m-d', strtotime($d->dateToday)));
					if($d->holidayType>0 || $holiday!=false){
						echo '&nbsp;&nbsp;|&nbsp;&nbsp;<span class="errortext">HO</span>';
					}
				echo '</b></td>';
			}
		?>
		</tr>
	<?php
	echo '<form id="formattendance" action="" method="POST" onSubmit="displaypleasewait();">';
	///staffHolidaySched 0-PHL and 1 for US
	
		foreach($dataAttendance AS $empID=>$att){
			echo '<tr>';
				echo '<td align="left">';
					echo $this->textM->formfield('checkbox', 'empIDs[]', $empID, 'classCheckMe').'&nbsp;';
					echo '<a href="'.$this->config->base_url().'timecard/'.$empID.'/timelogs/" class="tanone"><b>'.$att['name'].'</b></a>';				
				echo '</td>';
								
				foreach($dataDates AS $d){					
					$wday = date('D', strtotime($d->dateToday));
					if($wday=='Sat' || $wday=='Sun') echo '<td bgcolor="#aaa">';
					else echo '<td>';					
							$addclass = '';
							$timePaid = 0;
							$timeND = 0;
							$timeDeduct = 0;
							if(isset($att['dates'][$d->dateToday])){
								$log = $att['dates'][$d->dateToday];
								
								$timePaid = $log->publishTimePaid;
								$timeND = $log->publishND;
								$timeDeduct = $log->publishDeduct;
								if($log->publishBy==''){
									$addclass .= ' payboxunpublish';
									//if($timeDeduct==0) $timeDeduct = $log->schedHour;
								} 
								
								if($timePaid!=8) $addclass .= ' payboxdiff';
								
								$stat = '';
								if($log->status==1) $stat = 'disabled';
							
								echo $this->textM->formfield('text', 'log['.$log->slogID.'][publishTimePaid]', $timePaid, 'payrollboxes'.$addclass, '', $stat);
								echo $this->textM->formfield('text', 'log['.$log->slogID.'][publishDeduct]', $timeDeduct, 'payrollboxes '.(($timeDeduct>0)?'payboxdeduct':''), '', $stat);
								echo $this->textM->formfield('text', 'log['.$log->slogID.'][publishND]', $timeND, 'payrollboxes '.(($timeND>0)?'payND':''), '', $stat);
								
								$holiday = $this->payrollM->isHoliday(date('Y-m-d', strtotime($d->dateToday)));
								if($holiday!=false){	
									$holidayDate = $holiday['date'];
								
									$isDisabled = false;
									if($holiday['type']!=4){
										if($att['staffHolidaySched']==1 && $holiday['type']!=3) $isDisabled = true;
										else if($att['staffHolidaySched']==0 && $holiday['type']==3) $isDisabled = true;
									}
									
									if($isDisabled==true) $timeHO = 0;
									else $timeHO = (($log->publishHO=='')?$this->payrollM->getHolidayHours($holidayDate, $log):$log->publishHO);
									
									echo $this->textM->formfield('text', 'log['.$log->slogID.'][publishHO]', $timeHO, 'payrollboxes '.(($isDisabled==false)?'payHO':''), '', (($isDisabled==true)?'disabled':'').' '.$stat);
								}
									
								
								echo '<br/><a href="'.$this->config->base_url().'timecard/'.$empID.'/viewlogdetails/?d='.$d->dateToday.'" class="iframe fs11px">View details</a>';
							}						
							
					echo '</td>';
				}
			echo '</tr>';
		}
	?>
	</table>
</div>
<?php
	echo $this->textM->formfield('hidden', 'submitType', '');
	echo $this->textM->formfield('button', '', 'Save', 'btnclass btngreen', '', 'id="btnsave"');
	echo ' or ';
	echo $this->textM->formfield('button', '', 'Save and Generate Payroll', 'btnclass', '', 'id="btnsaveandgenerate"');
	
	//other info
	echo $this->textM->formfield('hidden', 'computationtype', $computationtype);
	echo $this->textM->formfield('hidden', 'type', $_POST['type']);
	echo $this->textM->formfield('hidden', 'start', $_POST['start']);
	echo $this->textM->formfield('hidden', 'end', $_POST['end']);
	echo $this->textM->formfield('hidden', 'empIDs', $_POST['empIDs']);	
?>
</form>

<div style="margin-top:20px;">
	<b>Legend:</b> 
	<span class="fs11px colorgray">
		<b>HW</b> - Hours Worked, 
		<b>D</b> - Deduction, 
		<b>ND</b> - Night Differential,
		<b>HO</b> - Holiday Hours,<br/>
		<b style="color:red;">Red Box</b> - With Deduction, 
		<b style="color:orange;">Orange Box</b> - Not yet published
		<b style="color:green;">Light Green Box</b> - For holidays, disabled if not applicable
	</span>
</div>

<?php
	//$generatepayroll = '178,203';
	if(isset($generatepayroll)){
		echo $this->textM->formfield('textarea', '', $generatepayroll, 'hidden', '', 'id="empGenPay"');		
		echo '<script type="text/javascript">';
			echo '$(function(){ 
				window.parent.jQuery.colorbox({href:"'.$this->config->base_url().'timecard/generatepayroll/?id=empGenPay", iframe:true, width:"990px", height:"600px"});
			});';
		echo '</script>';
	}
?>
<script type="text/javascript">
	$(function(){
		$('#btnsave').click(function(){
			$('input[name="submitType"]').val('save');
			$('#formattendance').submit();
		});
		$('#btnsaveandgenerate').click(function(){
			tugnaw = false;
			$('.classCheckMe').each(function(){
				if($(this).is(':checked')){
					tugnaw = true;
				}
			});
			
			if(tugnaw==true){
				$('input[name="submitType"]').val('saveandgenerate');
				$('#formattendance').submit();
			}else{
				alert('Please select employee.');
			}
		});
		
		
		$('#selectAll').click(function(){
			$('.classCheckMe').prop('checked', true);
		});
		
		$('#deselectAll').click(function(){
			$('.classCheckMe').prop('checked', false);
		});
	});
	
	function holidayChange(dtoday, t){		
		if(confirm('Are you sure you want to change the day type?')){
			displaypleasewait();
			$('input[name="holidayType"]').val($(t).val());
			$('input[name="dtoday"]').val(dtoday);
			$('#formdates').submit();
		}else $(t).val(0);
	}
</script>


