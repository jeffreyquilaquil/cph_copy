<?php
	if($visitID==$this->user->empID){
		echo '<div class="floatright">';
			 echo '<br/><a href="'.$this->config->base_url().'timecard/requestupdate/?d='.$today.'" class="iframe"><button class="btnclass btngreen">Request Update</button></a>';
		echo '</div>';
	}
	
	if(isset($_GET['back']) && $_GET['back']=='attendancedetails')
		echo '<a href="'.$this->config->base_url().'timecard/attendancedetails/?d='.$today.'" class="tanone"><button class="btnclass floatright btnorange"><< Back to Attendance Details</button></a>';
	
	echo '<h3>';
		if($visitID!=$this->user->empID) echo $row->fname.'\'s ';
		echo 'Log Details for '.date('l, F d, Y', strtotime($today));
		if(count($log)>0){
			if($log->publish_fk>0) echo ' <b class="errortext">PUBLISHED</b>';
			else if($this->access->accessFullHR==true && $log->timeOut!='0000-00-00 00:00:00') echo ' <button id="btnpublish" class="btnclass btngreen">Publish</button>';
		}
		
		///LINK TO TIMELOG PAGE
		if($this->access->accessFullHR==true) echo ' <a href="'.$this->config->base_url().'timecard/'.$visitID.'/?d='.$today.'" class="tanone" target="_blank"><button class="btnclass">Go to Timelog page</button></a>';
	echo '</h3><hr/>';
			
	if(isset($schedToday['sched']) || isset($schedToday['offset'])){
		if(isset($schedToday['sched'])) echo '<b>Schedule Today:</b> '.$schedToday['sched'].'<br/>';
		if(isset($schedToday['offset'])) echo '<b>Offset Schedule:</b> '.$schedToday['offset'].'<br/>';
	}else{
		echo '<b>Schedule Today:</b> NONE ';
	}	
	
	
	if($this->access->accessFullHR==true && (count($log)==0 || (count($log)>0 && $log->publish_fk==0)) && (!isset($schedToday['sched']) || (isset($schedToday['sched']) && $schedToday['sched']!='On Leave')))
		echo ' <a href="'.$this->config->base_url().'schedules/customizebyday/'.$visitID.'/'.$today.'/edit/" class="iframe errortext">+ Set Schedule</a>';


	if(count($log)==0){
		if(isset($schedToday['sched']) && $schedToday['sched']=='On Leave' && isset($schedToday['leaveID'])){
			echo '<p class="errortext">Click <a href="'.$this->config->base_url().'staffleaves/'.$schedToday['leaveID'].'/">here</a> to view details.</p>';
		}else echo '<p class="errortext">No Logs Recorded.</p>';
	}else if(!isset($schedToday['sched']) || ($schedToday['sched']!='On Leave' || ($schedToday['sched']=='On Leave' && $log->timeIn!='0000-00-00 00:00:00' && $log->timeOut!='0000-00-00 00:00:00'))){		
		//PUBLISH
		$timeDeduction = 0;
		$timePaid = $log->schedHour;
		if($log->offsetHour!=0) $timePaid -= $log->offsetHour;
		
		echo '<div style="padding:10px 0;" id="divpublish" class="'.(($log->publish_fk==0)?'hidden':'').'">';
		echo '<form action="" method="POST" onSubmit="displaypleasewait();">';
			echo '<table class="tableInfo" bgcolor="#ffd2d2">';
			if($log->publish_fk>0){
				echo '<tr class="trlabel"><td colspan=2>Published Details</td></tr>';
			}else{
				echo '<tr class="trlabel"><td colspan=2>Review Publish Details</td></tr>';
			}
				
			echo '<tr><td width="20%">Base Paid Hours</td><td>'.$timePaid.' Hours</td></tr>';	
			if($log->schedIn!='0000-00-00 00:00:00' && strtotime($log->schedIn)<strtotime($log->timeIn)){ ///IF LATE
				echo '<tr><td>Late</td><td>';
					$lateSec = strtotime($log->timeIn) - strtotime($log->schedIn);
					$lateHour = $this->timeM->hourDeduction($lateSec);
					
					$timeDeduction += $lateHour;
					echo $this->textM->convertTimeToMinHours($lateSec).'<br/>';
					echo '<span class="errortext">Deducted Hour: <b>'.$lateHour.'</b></span>';
				echo '</td></tr>';
			}
			
			$secBreak = $this->textM->convertTimeToSec($log->timeBreak);
			$oBreak = $this->timeM->timesetting('overBreak');
			if($secBreak > $oBreak){
				echo '<tr><td>Over Break</td><td>';
					$overSec = $secBreak - $oBreak;
					$overHour = $this->timeM->hourDeduction($overSec);
					
					$timeDeduction += $overHour;
					echo $this->textM->convertTimeToMinHours($overSec).'<br/>';
					echo '<span class="errortext">Deducted Hour: <b>'.$overHour.'</b></span>';						
				echo '</td></tr>';
			}
			
			if($log->timeOut!='0000-00-00 00:00:00' && strtotime($log->schedOut)>strtotime($log->timeOut)){ //IF EARLY OUT
				echo '<tr><td>Early Out</td><td>';
					$outSec = strtotime($log->schedOut) - strtotime($log->timeOut);
					$outHour = $this->timeM->hourDeduction($outSec);
					$timeDeduction += $outHour;
					echo $this->textM->convertTimeToMinHours($outSec).'<br/>';
					echo '<span class="errortext">Deducted Hour: <b>'.$outHour.'</b></span>';
					
				echo '</td></tr>';
			}
			
			if($timeDeduction>0){
				$timePaid -= $timeDeduction;
				echo '<tr><td>Total Deduction</td><td><b class="errortext">'.$timeDeduction.' Hour/s</b></td></tr>';
			} 
				
			if($timePaid<0 || ($log->timeIn=='0000-00-00 00:00:00' && $log->timeOut=='0000-00-00 00:00:00')) $timePaid = 0;
			
			//FOR OFFSET
			if($log->offsetIn!='0000-00-00 00:00:00' && $log->offsetOut!='0000-00-00 00:00:00'){				
				echo '<tr>';
					echo '<td>Offset Paid Hours</td>';
					echo '<td>';
						echo $log->offsetHour.' Hours';
						//add time paid if not late
						if(($log->timeIn!='0000-00-00 00:00:00' && $log->schedIn<$log->timeIn && $log->offTimeIn=='0000-00-00 00:00:00') || 
						($log->offTimeIn!='0000-00-00 00:00:00' && $log->offsetIn<$log->offTimeIn)){
							echo '<br/><b class="errortext">Late or no time in: Offset Forfeited</b>';
						}else{
							$timePaid += $log->offsetHour;
						}
						
					echo '</td>';
				echo '</tr>';
			}
			
			if(count($publish)>0){
				echo '<tr><td width="20%">Total Time Paid</td><td><b>'.$publish->timePaid.' Hours</b></td></tr>';
				echo '<tr><td width="20%">Date Published</td><td>'.date('F d, Y h:i a', strtotime($publish->datePublished)).'</td></tr>';
				echo '<tr><td>Published By</td><td>'.(($publish->publishedBy==0)?'System Published':$this->dbmodel->getSingleField('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$publish->publishedBy.'"')).'</td></tr>';
			}else{
				echo '<tr><td width="20%">Time Paid</td><td>'.$this->textM->formfield('number', 'timePaid', $timePaid, 'forminput', '', 'required min="0"').'</td></tr>';
				echo '<tr><td><br/></td><td>';					
					echo $this->textM->formfield('hidden', 'submitType', 'publishlog');
					echo $this->textM->formfield('hidden', 'tlogID', $log->tlogID);
					echo $this->textM->formfield('submit', '', 'Publish', 'btnclass btngreen');
					echo ' '.$this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'id="btncancelpublish"');
				echo '</td></tr>';
			}				
			echo '</table>';
		echo '</form>';
		echo '</div>';		
		
		echo '<table class="tableInfo" style="margin-top:10px;">';		
			echo '<tr class="trlabel"><td colspan=2>All Logs</td></tr>';
		//TIME IN
		echo '<tr>';
			echo '<td width="20%">Time In</td>';
			echo '<td>';
				echo '<div id="divTimeInFirst">';
						if($log->timeIn=='0000-00-00 00:00:00') echo '<b class="errortext">NO TIME IN</b>';
						else{
							echo '<b>'.date('h:i a', strtotime($log->timeIn)).'</b>';
							if($log->schedIn!='0000-00-00 00:00:00' && strtotime($log->schedIn.' +1 minute')<strtotime($log->timeIn))
								echo ' <b class="errortext">LATE</b>';
						}
						if($log->publish_fk==0 && $this->access->accessFullHR==true) echo ' <a href="javascript:void(0);" onClick="showEdit(\'divTimeIn\')">[Edit]</a>';
				echo '</div>';
				
				if($log->publish_fk==0 && $this->access->accessFullHR==true){
					echo '<div id="divTimeInSecond" class="width50 hidden">';
						if($log->timeIn!='0000-00-00 00:00:00') $eTimeIn = date('Y-m-d H:i:s', strtotime($log->timeIn));
						else if($log->schedIn!='0000-00-00 00:00:00') $eTimeIn = date('Y-m-d H:i:s', strtotime($log->schedIn));
						else $eTimeIn = date('Y-m-d H:i:s', strtotime($today));
					
						echo '<form action="" method="POST" onSubmit="displaypleasewait();">';
							echo $this->textM->formfield('text', 'timeVal', $eTimeIn, 'forminput', 'Time In Format (YYYY-MM-DD HH:MM:SS)', 'required');
							echo $this->textM->formfield('textarea', 'reason', '', 'forminput', 'Type reason here...', 'required rows=6');
							
							echo $this->textM->formfield('hidden', 'tlogID', $log->tlogID);
							echo $this->textM->formfield('hidden', 'submitType', 'editLog');
							echo $this->textM->formfield('hidden', 'timeType', 'timeIn');
							echo $this->textM->formfield('hidden', 'prev', 'Edited Time In: '.date('d M Y h:i a', strtotime($eTimeIn)));
							echo $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
							echo '&nbsp;'.$this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'onClick="hideEdit(\'divTimeIn\')"');
						echo '</form>';
					echo '</div>';
				}
				
			echo '</td>';
		echo '</tr>';
		
		//BREAKS
		echo '<tr>';
			echo '<td>Breaks</td>';
			echo '<td>';
			$break = json_decode($log->breaks);	
				echo '<div id="divBreaksFirst">';
					$out = 0;
					$compute = 0;
					if(count($break)>0){
						$b = '';
						foreach($break AS $b){						
							$b = strtotime($b);
							echo date('h:i a', $b);
							if($out%2==0){
								echo ' - ';
								$compute = $b;
							}else{
								$diff = $b - $compute;
								$compute = 0;
								
								echo ' (<i class="colorgray">'.trim($this->textM->convertTimeToMinHours($diff, false)).'</i>)<br/>';
							}
							$out++;
						}
						if($out%2!=0) echo ' <b class="errortext">MISSING BREAK IN</b><br/>';
						else if(!empty($b) && $log->timeOut!='0000-00-00 00:00:00' && (date('Y-m-d H:i:s', $b)==date('Y-m-d H:i:s', strtotime($log->timeOut)))) echo ' <i class="errortext">Missing Break In, copied time out log</i><br/>';
						
						echo '<br/>Total Breaks: <b>'.trim($this->textM->convertTimeToStr($log->timeBreak)).'</b>';
						if($log->timeBreak>'01:30:00') echo ' <b class="errortext">OVER BREAK</b>';
					}else{
						echo '<b class="errortext">NO BREAKS</b>';
					}
					
					
					if($log->publish_fk==0 && $this->access->accessFullHR==true) echo ' <a href="javascript:void(0);" onClick="showEdit(\'divBreaks\')">[Edit]</a>';
				echo '</div>';
				
				//FOR EDITING BREAKS
				if($log->publish_fk==0 && $this->access->accessFullHR==true){
					echo '<div id="divBreaksSecond" class="hidden">';	
					echo '<form action="" method="POST" onSubmit="displaypleasewait();">';
						$out2 = 0;
						$cnt = 0;
						if(count($break)>0){
							foreach($break AS $b){
								$btext = $this->textM->formfield('input', 'break[]', date('Y-m-d H:i:s', strtotime($b)), 'padding5px', 'YYYY-MM-DD HH:MM:SS', 'required' );
								if($cnt%2==0){
									echo '<div id="divBreakEdit'.$cnt.'">';
									echo $btext.' - ';
								}else{
									echo $btext;
									echo ' <a href="javascript:void(0);" onClick="removeBreak('.($cnt-1).')">Remove</a>';
									echo '</div>';	
								} 					
								$cnt++;
							}
							if($cnt%2!=0){
								echo $this->textM->formfield('input', 'break[]', '', 'padding5px', 'YYYY-MM-DD HH:MM:SS', 'required');
								echo ' <a href="javascript:void(0);" onClick="removeBreak('.($cnt-2).')">Remove</a>';
								echo '</div>';	
							}
						}						
						
						echo $this->textM->formfield('button', '', '+ Add', '', '', 'onClick="addBreak(this)"').'<br/>';
						echo $this->textM->formfield('textarea', 'reason', '', '', 'Type reason here...', 'required rows=5');
						echo $this->textM->formfield('hidden', '', $cnt, '', '', 'id="cntNum"');
						
						echo $this->textM->formfield('hidden', 'tlogID', $log->tlogID);
						echo $this->textM->formfield('hidden', 'submitType', 'editBreaks');
						echo $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
						echo '&nbsp;'.$this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'onClick="hideEdit(\'divBreaks\')"');
					echo '</form>';
					echo '</div>';
				}
			
			echo '</td>';
		echo '</tr>';
		
		//TIME OUT
		echo '<tr>';
			echo '<td>Time Out</td>';
			echo '<td>';
				echo '<div id="divTimeOutFirst">';
					if($log->timeOut=='0000-00-00 00:00:00') echo '<b class="errortext">NO TIME OUT</b>';
					else{
						echo '<b>'.date('h:i a', strtotime($log->timeOut)).'</b>';
						if($log->schedOut!='0000-00-00 00:00:00' && strtotime($log->schedOut)>strtotime($log->timeOut))
							echo ' <b class="errortext">EARLY OUT</b>';
					}
					
					if($log->publish_fk==0 &&  $this->access->accessFullHR==true) echo ' <a href="javascript:void(0);" onClick="showEdit(\'divTimeOut\')">[Edit]</a>';
				echo '</div>';
				
				if($log->publish_fk==0 && $this->access->accessFullHR==true){
					echo '<div id="divTimeOutSecond" class="width50 hidden">';
						if($log->timeOut!='0000-00-00 00:00:00') $eTimeOut = date('Y-m-d H:i:s', strtotime($log->timeOut));
						else if($log->schedOut!='0000-00-00 00:00:00') $eTimeOut = date('Y-m-d H:i:s', strtotime($log->schedOut));
						else $eTimeOut = date('Y-m-d H:i:s', strtotime($today));
					
						echo '<form action="" method="POST" onSubmit="displaypleasewait();">';
							echo $this->textM->formfield('text', 'timeVal', $eTimeOut, 'forminput', 'Time Out Format (YYYY-MM-DD HH:MM:SS)', 'required');
							echo $this->textM->formfield('textarea', 'reason', '', 'forminput', 'Type reason here...', 'required rows=6');
							
							echo $this->textM->formfield('hidden', 'tlogID', $log->tlogID);
							echo $this->textM->formfield('hidden', 'submitType', 'editLog');
							echo $this->textM->formfield('hidden', 'timeType', 'timeOut');
							echo $this->textM->formfield('hidden', 'prev', 'Edited Time Out: '.date('d M Y h:i a', strtotime($eTimeOut)));
							echo $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
							echo '&nbsp;'.$this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'onClick="hideEdit(\'divTimeOut\')"');
						echo '</form>';
					echo '</div>';
				}
			echo '</td>';
		echo '</tr>';
			
		//THIS IS FOR OFFSET
		if($log->offsetIn!='00:0000-00-00 00:00:00' && $log->offsetOut!='0000-00-00 00:00:00' && ($log->offTimeIn!='0000-00-00 00:00:00' || $log->offTimeOut!='0000-00-00 00:00:00')){
			echo '<tr><td class="trhead" colspan=2>Offset Details</td></tr>';
			echo '<tr>';
				echo '<td>Offset Time In</td>';
				echo '<td><b>'.(($log->offTimeIn!='0000-00-00 00:00:00')?date('h:i a', strtotime($log->offTimeIn)):'No Time In').'</b></td>';
			echo '</tr>';
			
			echo '<tr>';
				echo '<td>Offset Time Out</td>';
				echo '<td><b>'.(($log->offTimeOut!='0000-00-00 00:00:00')?date('h:i a', strtotime($log->offTimeOut)):'No Time Out').'</b></td>';
			echo '</tr>';	
		}
		
		//BIOMETRIC LOGS
		if(count($allLogs)>0){
			echo '<tr><td class="trhead" colspan=2>Biometric Logs <a class="cpointer fs11px" id="bioshow">[Show]</a></td></tr>';
			$breaknum = 0;
			foreach($allLogs AS $l){
				echo '<tr class="bioclass hidden">';
					echo '<td>';
						if($l->logtype=='D' || $l->logtype=='E'){
							if($breaknum%2!=0) echo 'Break Out';
							else echo 'Break In';
						}else echo $logtypeArr[$l->logtype];
					echo '</td>';					
					echo '<td>'.date('h:i a', strtotime($l->logtime)).'</td>';
				echo '</tr>';
				
				$breaknum++;
			}
		}
		
		echo '</table>';
	}
	
	
	if(count($updateRequests)>0){
		echo '<br/><br/><table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=3>Update History</td></tr>';
		echo '<tr class="trhead"><td>Date</td><td>Message</td><td>Status</td></tr>';
		foreach($updateRequests AS $u){
			echo '<tr '.(($u->status==1)?'style="background-color:#ffb2b2;"':'').'>';
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
							echo '<b class="errortext">If done editing log, please change this status to "UPDATED".</b>';
							echo '<form action="" method="POST" onSubmit="displaypleasewait();">';
								echo $this->textM->formfield('select', 'status', '<option value="1">Pending</option><option value="0">Updated</option>', 'forminput', '', 'onChange="showUpdate('.$u->updateID.', this)"');
							echo '<div id="divreq_'.$u->updateID.'" class="hidden">';
								echo $this->textM->formfield('textarea', 'updateNote', '', 'forminput', '', 'required rows=6');
								echo $this->textM->formfield('hidden', 'updateID', $u->updateID);
								echo $this->textM->formfield('hidden', 'submitType', 'updateReq');
								echo $this->textM->formfield('submit', '', 'Submit', 'btnclass');
							echo '</div>';
							echo '</form>';
						}else{
							echo 'Pending';
						}							
					} 
					else{
						if(!empty($u->type)){
							echo '<b>Updated</b><br/>						
							<i>Update Note:</i>'.nl2br($u->updateNote).'<br/>';
						}
						echo '<i>Updated By:</i>'.$u->updatedBy.' - '.$u->dateUpdated;
					}
				echo '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
?>

<script type="text/javascript">	
	$(function(){
		$('#editText').click(function(){
			$('#formeditsched').removeClass('hidden');
			$(this).addClass('hidden');
		});
		$('#btnEditCancel').click(function(){
			$('#editText').removeClass('hidden');
			$('#formeditsched').addClass('hidden');
		});
		
		$('#btnpublish').click(function(){
			$(this).addClass('hidden');
			$('#divpublish').removeClass('hidden');			
		});
		$('#btncancelpublish').click(function(){
			$('#divpublish').addClass('hidden');
			$('#btnpublish').removeClass('hidden');			
		});
		
		$('#bioshow').click(function(){
			txt = $(this).text();
			if(txt=='[Show]'){
				$(this).text('[Hide]');
				$('.bioclass').removeClass('hidden');
			}else{
				$(this).text('[Show]');
				$('.bioclass').addClass('hidden');
			}
		});
	});

	function showEdit(divi){
		$('#'+divi+'First').addClass('hidden');
		$('#'+divi+'Second').removeClass('hidden');
	}
	
	function hideEdit(divi){
		$('#'+divi+'First').removeClass('hidden');
		$('#'+divi+'Second').addClass('hidden');
	}
	
	function removeBreak(id){
		$('#divBreakEdit'+id).addClass('hidden');
		$('#divBreakEdit'+id+' input').attr('disabled', 'disabled');
	}
	
	function showUpdate(id, t){
		if($(t).val()==0){
			$('#divreq_'+id).removeClass('hidden');
		}else{
			$('#divreq_'+id).addClass('hidden');
		}
	}
	
	function addBreak(t){
		cnt = $('#cntNum').val();
		content = '<div id="divBreakEdit'+cnt+'"><input type="input" class="padding5px" name="break[]" placeholder="YYYY-MM-DD HH:MM:SS" required> - <input type="input" class="padding5px" name="break[]" placeholder="YYYY-MM-DD HH:MM:SS" required> <a onclick="removeBreak('+cnt+')" href="javascript:void(0);">Remove</a></div>';
		$(content).insertBefore(t);
		$('#cntNum').val((parseInt(cnt)+1));
	}
</script>
