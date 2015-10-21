<?php
	$date00 = '0000-00-00 00:00:00';
	$EARLYCIN = $this->timeM->timesetting('earlyClockIn');
	$OUTL8 = $this->timeM->timesetting('outLate');
	
	$paidHour = $dataLog->schedHour;
	$deductionHour = 0;
		
	
	if(isset($_GET['back']) && $_GET['back']=='attendancedetails')
		echo '<a href="'.$this->config->base_url().'timecard/attendancedetails/?d='.$today.'" class="tanone"><button class="btnclass floatright btnorange"><< Back to Attendance Details</button></a>';
	
	echo '<h3 style="width:100%;">';
		if($visitID!=$this->user->empID) echo $row->fname.'\'s ';
		echo 'Log Details for '.date('l, F d, Y', strtotime($today));
	
	///PUBLISHED
		if(!empty($dataLog->publishBy))  echo '&nbsp;&nbsp;<b class="errortext">PUBLISHED</b>';
		else if($this->access->accessFullHR==true && empty($dataLog->publishBy)) 
			echo '&nbsp;&nbsp;<button id="btnpublish" class="btnclass btngreen">Publish</button>';
	
		echo '&nbsp;&nbsp;<a href="'.$this->config->base_url().'timecard/'.$visitID.'/?d='.$today.'" target="_blank"><button class="btnclass">Go to Timelog Page</button></a>';
		
		///REQUEST UPDATE BUTTON
		if($visitID==$this->user->empID)
			echo '<a href="'.$this->config->base_url().'timecard/requestupdate/?d='.$today.'" class="iframe"><button class="btnclass btngreen floatright">Request Update</button></a>';
	echo '</h3>';
	echo '<hr/>';
	
	if(isset($alerttext)){
		echo '<p class="tacenter"><b class="errortext">'.$alerttext.'</b></p>';
	}
	
		
	///SCHEDULE TODAY
	if(isset($schedToday['sched']) || isset($schedToday['offset'])){
		if(isset($schedToday['sched'])){
			echo '<b>Schedule Today:</b> <b class="errortext">'.$schedToday['sched'].'</b><br/>';
		} 
		if(isset($schedToday['offset'])){
			echo '<b>Offset Schedule:</b> <b class="errortext">'.$schedToday['offset'].'</b><br/>';
		} 
	}else{
		echo '<b>Schedule Today:</b> NONE ';
	}
	
	//THIS IS INSERTED LOG ON tcStaffLogPublish
	if(!empty($dataLog)){
		///PUBLISH DETAILS
		if(!empty($dataLog->publishBy)){
			echo '<table id="tblpublishdetails" class="tableInfo" style="margin-top:10px;">';
				echo '<tr class="trlabel"><td colspan=2>PUBLISH DETAILS';
					if($this->access->accessFullHR==true) echo '&nbsp;<button id="unpublish">Unpublish</button>';
				echo '</td></tr>';
				echo '<tr><td width="15%">Base Paid Hours</td><td>'.$dataLog->schedHour.' '.(($dataLog->schedHour>1)?'Hours':'Hour').'</td></tr>';
				if($dataLog->offsetHour>0)
					echo '<tr><td>Offset Paid Hours</td><td>'.$dataLog->offsetHour.' '.(($dataLog->offsetHour>1)?'Hours':'Hour').'</td></tr>';
				echo '<tr><td>Total Time Paid</td><td><b>'.$dataLog->publishTimePaid.' '.(($dataLog->publishTimePaid>1)?'Hours':'Hour').'</b></td></tr>';
				if(!empty($dataLog->publishNote)) echo '<tr><td>Note</td><td>'.$dataLog->publishNote.'</td></tr>';
				echo '<tr><td>Date Published</td><td>'.date('F d, Y h:i a', strtotime($dataLog->datePublished)).'</td></tr>';
				echo '<tr><td>Published By</td><td>'.(($dataLog->publishBy=="system")?'System':$dataLog->publishBy).'</td></tr>';
			echo '</table>';
		}
		
		
		////LEAVE DETAILS
		if($dataLog->leaveID_fk>0){
			$leaveTypeArr = $this->textM->constantArr('leaveType');
			$leave = $this->dbmodel->getSingleInfo('staffLeaves', 'leaveID, leaveType, leaveStart, leaveEnd, status, totalHours, offsetdates', 'leaveID="'.$dataLog->leaveID_fk.'"');
			if(count($leave)>0){
				if($leave->status!=1) $deductionHour = $dataLog->schedHour;
				
				echo '<table id="tblleavedetails" class="tableInfo" style="margin-top:10px;">';
					echo '<tr class="trlabel"><td colspan=2>LEAVE DETAILS</td></tr>';
					echo '<tr><td width="15%">Status</td><td><b>'.(($leave->status==1)?'Approved WITH pay':'Approved WITHOUT pay').'</b> '.(($leave->leaveType==4)?'<span class="errortext">Hours paid based on offset schedules</span>':'').'</td></tr>';
					echo '<tr><td>Leave Type</td><td>'.$leaveTypeArr[$leave->leaveType].'</td></tr>';
					echo '<tr><td>Leave Start</td><td>'.date('F d, Y h:i a', strtotime($leave->leaveStart)).'</td></tr>';
					echo '<tr><td>Leave End</td><td>'.date('F d, Y h:i a', strtotime($leave->leaveEnd)).'</td></tr>';
					if($leave->leaveType==4){
						echo '<tr><td>Offset Schedules</td><td>';
						$off = explode('|', $leave->offsetdates);
						foreach($off AS $o){
							if(!empty($o)){
								list($s, $e) = explode(',', $o);
								echo date('F d, Y h:i a', strtotime($s)).' - '.date('F d, Y h:i a', strtotime($e)).'<br/>';
							}
						}
						echo '</td></tr>';
					}
					echo '<tr><td>Total Leave Hours</td><td>'.$leave->totalHours.' Hours</td></tr>';
					echo '<tr><td colspan=2><i class="errortext">Click <a href="'.$this->config->base_url().'staffleaves/'.$leave->leaveID.'/">here</a> to view all leave details.</i></td></tr>';
				echo '</table>';
			}
		}
		
		
		////THIS IS FOR RESOLVING LOG OR SCHEDULE
		if($this->access->accessFullHR==true){
			echo '<form class="formresolve hidden" action="" method="POST" onSubmit="displaypleasewait();">';
			echo '<table id="tblresolvelog" class="tableInfo" style="margin-top:10px; background-color:#ffb2b2;">';
				echo '<tr class="trlabel"><td colspan=2>RESOLVE LOG AND SCHEDULE</td></tr>';
				echo '<tr>';
					echo '<td width=15%><b>What to change</b></td>';
					echo '<td>';
						$selectOption = '<option value=""></option>';
						$selectOption .= '<option value="timeIn">Time In</option>';
						$selectOption .= '<option value="timeOut">Time Out</option>';
						$selectOption .= '<option value="breaks">Breaks</option>';
						$selectOption .= '<option value="schedIn">Schedule In</option>';
						$selectOption .= '<option value="schedOut">Schedule Out</option>';
						$selectOption .= '<option value="offsetIn">Offset In</option>';
						$selectOption .= '<option value="offsetOut">Offset Out</option>';
						echo $this->textM->formfield('select', 'changetype', $selectOption, 'forminput', '', 'required');
					echo '</td>';
				echo '</tr>';
				echo '<tr class="trresolve hidden">';
					echo '<td>Value/s</td>';
					echo '<td>';
						echo 'Current date: <b>'.date('Y-m-d', strtotime($today)).'</b>';
						///TIME IN and OUT
						echo '<div id="divInOut"><br/>'.$this->textM->formfield('text', 'inoutval', date('Y-m-d 00:00:00', strtotime($today)), 'forminput', 'YYYY-MM-DD HH:MM:SS (24-hour format)', 'pattern="'.$this->textM->constantValues('datetimepattern').'"').'</div>';	
						
						///BREAKS
						echo '<div id="divBreaks">';
							if(!empty($dataLog->breaks)){
								$breaks = explode('|', rtrim($dataLog->breaks, '|'));
								foreach($breaks AS $k=>$b){
									if($k%2==0) echo '<br/>';
									else echo ' - ';
									
									echo $this->textM->formfield('text', 'breakval[]', $b, 'forminput breakinside', 'YYYY-MM-DD HH:MM:SS', 'style="width:200px;" pattern="'.$this->textM->constantValues('datetimepattern').'"');
									
								}
							}
							echo '<button id="btnaddbreak" type="button">+ Add</button>';
							echo '<div id="divaddbreak" class="hidden">';
								echo $this->textM->formfield('text', 'breakval[]', '', 'forminput', 'YYYY-MM-DD HH:MM:SS', 'style="width:200px;" pattern="'.$this->textM->constantValues('datetimepattern').'"');
								echo ' - '.$this->textM->formfield('text', 'breakval[]', '', 'forminput', 'YYYY-MM-DD HH:MM:SS', 'style="width:200px;" pattern="'.$this->textM->constantValues('datetimepattern').'"').' <i class="colorgray fs11px">Optional. This is for additional breaks.</i><br/>';
							echo '</div>';
						echo '</div>';
						
						echo '<i class="errortext fs11px">Format: YYYY-MM-DD HH:MM:SS (24-hour format)</i>';
					echo '</td>';
				echo '</tr>';
				echo '<tr class="trresolve hidden">';
					echo '<td>Reason</td>';
					echo '<td>'.$this->textM->formfield('textarea', 'reason', '', 'forminput', 'Add reason here...', 'required').'</td>';
				echo '</tr>';
				echo '<tr class="trresolve hidden"><td><br/></td><td>';
					echo $this->textM->formfield('hidden', 'slogID', ((isset($dataLog->slogID))?$dataLog->slogID:''));
					echo $this->textM->formfield('hidden', 'updateID', '');
					echo $this->textM->formfield('hidden', 'submitType', 'resolvelog');
					echo $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
					echo '&nbsp;'.$this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'id="btncancelresolve"');
				echo '</td></tr>';
			echo '</table>';
			echo '</form>';
		}
				
		if((isset($schedToday['sched']) && $schedToday['sched']!='On Leave') || $dataLog->timeIn!=$date00 || $dataLog->timeOut!=$date00){
			echo '<table id="tblinsertedlog" class="tableInfo" style="margin-top:10px;">';
				///INSERTED LOGS RECORD
				echo '<tr class="trlabel"><td colspan=4>INSERTED LOG TODAY ';
					if($this->access->accessFullHR==true && empty($dataLog->publishBy) && count($dataBiometrics)>0) echo '<button id="btnresolvelog">Resolve Logs</button>';
				echo '</td></tr>';
				
				if(isset($schedToday['sched']) && $schedToday['sched']!='On Leave'){
					echo '<tr class="trhead">';
						echo '<td width="15%"><b>Schedule In</b></td>';
						echo '<td>'.(($dataLog->schedIn!=$date00)?date('h:i a', strtotime($dataLog->schedIn)):'NONE').'</td>';
						echo '<td width="15%"><b>Schedule Out</b></td>';
						echo '<td>'.(($dataLog->schedOut!=$date00)?date('h:i a', strtotime($dataLog->schedOut)):'NONE').'</td>';
					echo '</tr>';
				}
				
				/* if($dataLog->offsetIn!=$date00 && $dataLog->offsetOut!=$date00){
					echo '<tr class="trhead">';
						echo '<td width="15%"><b>Offset In</b></td>';
						echo '<td>'.(($dataLog->offsetIn!=$date00)?date('h:i a', strtotime($dataLog->offsetIn)):'NONE').'</td>';
						echo '<td width="15%"><b>Offset Out</b></td>';
						echo '<td>'.(($dataLog->offsetOut!=$date00)?date('h:i a', strtotime($dataLog->offsetOut)):'NONE').'</td>';
					echo '</tr>';
				} */
				
				if($dataLog->timeIn==$date00 && $dataLog->timeOut==$date00 && empty($breaks)){
					echo '<tr><td colspan=4 class="errortext">No Logs - ABSENT</td></tr>';
					$deductionHour = $dataLog->schedHour;
				}else{
					////TIME IN
					echo '<tr>';
						echo '<td><b>Time In</b></td>';
						echo '<td colspan=3>';
							if($dataLog->timeIn==$date00) echo '<span class="errortext">NO TIME IN</span>';
							else{
								echo '<b>'.date('h:i a', strtotime($dataLog->timeIn)).'</b>';
								if($dataLog->schedIn!=$date00){
									echo ' <span class="errortext">';
										if(isset($schedToday['sched']) && $schedToday['sched']!='On Leave'){
											if($dataLog->timeIn > date('Y-m-d H:i:s', strtotime($dataLog->schedIn.' +1 minutes'))){
												$lateSec = strtotime($dataLog->timeIn) - strtotime($dataLog->schedIn);
												$lateHour = $this->timeM->hourDeduction($lateSec);
												$deductionHour += $lateHour;
												
												echo 'LATE ('.trim($this->textM->convertTimeToMinHours($lateSec)).')';
												echo '<br/><b>Hour Deduction: '.$lateHour.' '.(($lateHour>1)?'Hours':'Hour').'</b>';
											}else if(strtotime($dataLog->timeIn)<= strtotime($dataLog->schedIn.' '.$EARLYCIN) && $dataLog->offsetIn==$date00)  echo 'EARLY IN';
										}
									echo '</span>';
								}
								
							} 
						echo '</td>';
					echo '</tr>';
					
					//BREAKS
					echo '<tr>';
						echo '<td><b>Breaks</b></td>';
						echo '<td colspan=3>';
							if(empty($dataLog->breaks)) echo '<span class="errortext">NO BREAKS</span>';
							else{
								$break = explode('|', rtrim($dataLog->breaks,'|'));
								$totalBreak = 0;
								foreach($break AS $n=>$b){
									if($n%2==0) echo date('h:i a', strtotime($b));
									else{
										$breakdiff = strtotime($b)-strtotime($break[$n-1]);
										$totalBreak += $breakdiff;
										
										echo ' - '.date('h:i a', strtotime($b));								
										echo ' <i class="colorgray">('.trim($this->textM->convertTimeToMinHours($breakdiff, false)).')</i>';								 
										echo '<br/>';
									}
								}
								
								echo '<br/>Total Breaks: <b>'.$this->textM->convertTimeToMinHours($totalBreak, false).'</b>';
								
								if(!empty($dataLog->breaks)){	
									$secBreak = $this->textM->convertTimeToSec($dataLog->timeBreak);
									$oBreak = $this->timeM->timesetting('overBreak');
									if($secBreak > $oBreak){
										$overSec = $secBreak - $oBreak;
										$overHour = $this->timeM->hourDeduction($overSec);										
										$deductionHour += $overHour;
										
										echo ' <span class="errortext">OVER BREAK ('.trim($this->textM->convertTimeToMinHours($overSec)).')</span>';
										echo '<br/><b class="errortext">Hour Deduction: '.$overHour.' '.(($overHour>1)?'Hours':'Hour').'</b>';
									} 
									if($dataLog->numBreak%2!=0) echo ' <span class="errortext">MISSING BREAK IN</span>';							
								}
							}
						echo '</td>';
					echo '</tr>';
					
					////TIME OUT
					echo '<tr>';
						echo '<td><b>Time Out</b></td>';
						echo '<td colspan=3>';
							if($dataLog->timeOut==$date00){
								echo '<span class="errortext">NO TIME OUT</span>';
								if($dataLog->timeIn!=$date00) $deductionHour += $dataLog->schedHour;							
							}else{
								echo '<b>'.date('h:i a', strtotime($dataLog->timeOut)).'</b>';
								
								if($schedToday['sched']!='On Leave'){
									if($dataLog->timeOut < $dataLog->schedOut){
										$outSec = strtotime($dataLog->schedOut) - strtotime($dataLog->timeOut);
										$outHour = $this->timeM->hourDeduction($outSec);
										$deductionHour += $outHour;
										
										echo '<span class="errortext">';
											echo ' EARLY OUT ('.trim($this->textM->convertTimeToMinHours($outSec)).')';
											echo '<br/><b>Hour Deduction: '.$outHour.' '.(($outHour>1)?'Hours':'Hour').'</b>';
										echo '</span>';
									}else if(strtotime($dataLog->timeOut)>=strtotime($dataLog->schedOut.' '.$OUTL8) && $dataLog->offsetOut==$date00) echo ' <span class="errortext">OUT LATE</span>';
								} 
							} 
						echo '</td>';
					echo '</tr>';
				}
			echo '</table>';
		}
		
		
		////THIS IS FOR PUBLISHING LOG
		echo '<form id="formpublish" class="hidden" action="" method="POST" onSubmit="displaypleasewait();">';
		echo '<table id="tblpublishlog" class="tableInfo" style="margin:10px 0; background-color:#ffb2b2;">';
			echo '<tr class="trlabel"><td colspan=2>REVIEW PUBLISH DETAILS</td></tr>';
			echo '<tr><td width="15%">Base Paid Hours</td><td>'.($dataLog->schedHour - $dataLog->offsetHour).' Hours</td></tr>';
			if($dataLog->offsetHour>0)
				echo '<tr><td>Offset Paid Hours</td><td>'.$dataLog->offsetHour.' Hours</td></tr>';
			if($deductionHour>0)
				echo '<tr><td>Total Deducted Hours</td><td>'.$deductionHour.' '.(($deductionHour>1)?'Hours':'Hour').'</td></tr>';
			
			$totalpaid = $dataLog->schedHour-$deductionHour;
			if($totalpaid<0) $totalpaid = 0;
			echo '<tr><td>Total Paid Hours</td><td>'.$this->textM->formfield('number', 'publishTimePaid', $totalpaid, 'forminput', '', 'required').'</td></tr>';
			echo '<tr><td>Note <i class="colorgray">(Optional)</i></td><td>'.$this->textM->formfield('text', 'publishNote', '', 'forminput').'</td></tr>';			
			echo '<tr><td><br/></td><td>';
				echo $this->textM->formfield('hidden', 'submitType', 'publishlog');
				echo $this->textM->formfield('hidden', 'slogID', $dataLog->slogID);
				echo $this->textM->formfield('submit', '', 'Publish', 'btnclass btngreen');
				echo '&nbsp;'.$this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'id="btncancelpublish"');
			echo '</td></tr>';
		echo '</table>';
		echo '</form>';
		
	} ////END OF if(!empty($dataLog))
	
	////BIOMETRIC LOGS
	if(count($dataBiometrics)>0){
		echo '<table class="tableInfo" style="margin-top:10px;">';
			echo '<tr><td class="trhead" colspan=2>Biometric Logs <a class="cpointer fs11px" onClick="showdetails(\'bioclass\', this)">[Show]</a></td></tr>';
			$breaknum = 0;
			foreach($dataBiometrics AS $b){
				echo '<tr class="bioclass hidden">';
					echo '<td width="15%">';
						if($b->logtype=='D' || $b->logtype=='E'){
							if($breaknum%2!=0) echo 'Break Out';
							else echo 'Break In';
						}else echo $logtypeArr[$b->logtype];
					echo '</td>';					
					echo '<td>'.date('h:i a', strtotime($b->logtime)).'</td>';
				echo '</tr>';
				
				$breaknum++;
			}
		echo '</table>';
	}
	
	
	//////UPDATE REQUESTS
	if(count($updateRequests)>0){
		echo '<table class="tableInfo">';
		echo '<tr class="trhead"><td colspan=3>Update History <a class="cpointer fs11px" onClick="showdetails(\'updateclass\', this)">[Show]</a></td></tr>';
		foreach($updateRequests AS $u){
			echo '<tr class="updateclass hidden" '.(($u->status==1)?'style="background-color:#ffb2b2;"':'').'>';
				echo '<td width="120px">'.date('d M y h:i a', strtotime($u->dateRequested)).'</td>';
				echo '<td valign="top">';
					echo nl2br($u->message);
					if(!empty($u->docs)){
						echo '<br/><b>Supporting Docs</b><br/><ul>';
						$dd = explode('|', $u->docs);
						foreach($dd AS $d){
							if(!empty($d))
								echo '<li><a href="'.$this->config->base_url().$dir.$d.'">'.$d.'</a></li>';
						}
						echo '</ul>';
					}
				echo '</td>';
				echo '<td>';
					if($u->status==1){
						if($this->access->accessFullHR==true){
							echo $this->textM->formfield('select', 'status', '<option value="1">Pending</option><option value="0">Resolved</option>', 'forminput', '', 'onChange="showUpdate('.$u->updateID.', this)"');
						}else{
							echo 'Pending';
						}							
					} 
					else{
						echo '<i>Updated By:</i> '.$u->updatedBy.' - '.$u->dateUpdated;
						if(!empty($u->updateNote)) echo '<br/><i>Update Note:</i> '.nl2br($u->updateNote);
					}
				echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	
	///SHOW PUBLISH BOX IF FROM ATTENDANCE DETAILS PUBLISH
	if(isset($_GET['publish']) && isset($dataLog->publishBy) && $dataLog->publishBy==''){
		echo '<script>';
		echo '$(function(){
			$("#btnpublish").hide();
			$("#formpublish").removeClass("hidden");
		})';
		echo '</script>';
	}
	
	//THIS IS FOR CHANGE TYPE VALUES
	$changeTimeIn = date('Y-m-d 00:00:00', strtotime($today));
	$changeTimeOut = $changeTimeIn;
	
	if(!empty($dataLog)){
		if($dataLog->timeIn!='0000-00-00 00:00:00') $changeTimeIn = $dataLog->timeIn;
		else $changeTimeIn = $dataLog->schedIn;
		
		if($dataLog->timeOut!='0000-00-00 00:00:00') $changeTimeOut = $dataLog->timeOut;
		else $changeTimeOut = $dataLog->schedOut;
	}
?>

<script type="text/javascript">
	$(function(){			
		$('select[name="changetype"]').change(function(){
			sss = $(this).val();
			if(sss==''){
				$('.trresolve').addClass('hidden');
			}else{
				$('.trresolve').removeClass('hidden');
				if(sss=='breaks'){
					$('input[name="inoutval"]').removeAttr('required');
					$('#divBreaks').show();	
					$('#divInOut').hide();			
				}else{
					$('input[name="inoutval"]').attr('required', 'required');
					$('#divInOut').show();
					$('#divBreaks').hide();
					
					if(sss=='timeIn') $('input[name="inoutval"]').val('<?= $changeTimeIn ?>');
					else if(sss=='timeOut') $('input[name="inoutval"]').val('<?= $changeTimeOut ?>');
					else if(sss=='schedIn') $('input[name="inoutval"]').val('<?= date('Y-m-d H:00:00', strtotime($changeTimeIn)) ?>');
					else if(sss=='schedOut') $('input[name="inoutval"]').val('<?= date('Y-m-d H:00:00', strtotime($changeTimeOut)) ?>');
				}
					
			}
		});
		
		$('#btnresolvelog').click(function(){
			$(this).hide();
			$('#tblleavedetails').hide();
			$('.formresolve').removeClass('hidden');
		});
		
		$('#btncancelresolve').click(function(){
			$('#btnresolvelog').show();
			$('#tblleavedetails').show();
			$('.formresolve').addClass('hidden');
			$('select[name="changetype"]').val('');
			$('.trresolve').addClass('hidden');
		});
		
		$('#unpublish').click(function(){
			if(confirm('Are you sure you want to unpublish this record?')){
				displaypleasewait();
				$.post('');
				$.post('<?= $this->config->item('career_uri') ?>',{submitType:'unpublish', slogID:'<?= $dataLog->slogID ?>'}, 
				function(){
					location.reload();
				});
			}
		});
		
		$('#btnaddbreak').click(function(){
			$(this).hide();
			$('#divaddbreak').show();
		});
		
		$('#btnpublish').click(function(){
			$(this).hide();
			$('#formpublish').removeClass('hidden');
			
			var tag = $("#tblpublishlog");
			$('html,body').animate({scrollTop: tag.offset().top},'slow');
		});
		
		$('#btncancelpublish').click(function(){
			$('#formpublish').addClass('hidden');
			$('#btnpublish').show();
		});
	});
	
	function showUpdate(id, t){
		if($(t).val()==0){
			$(t).val(1);
			alert('Please change logs on "RESOLVE LOG AND SCHEDULE" at the top.');
			$('#btnresolvelog').hide();
			$('.formresolve').removeClass('hidden');
			$('input[name="updateID"]').val(id);
		}
	}
	
	function showdetails(cl, t){
		txt = $(t).text();
		if(txt=='[Show]'){
			$(t).text('[Hide]');
			$('.'+cl).removeClass('hidden');
		}else{
			$(t).text('[Show]');
			$('.'+cl).addClass('hidden');
		}
	}
</script>