<h3>Details of <?= date('l F d, Y', strtotime($today)) ?> <?= '<b class="errortext">Unpublished: '.count($queryUnPublished).'</b>' ?></h3>
<hr/>
<?php
//////UNPUBLISHED
if(count($queryUnPublished)>0){
	echo '<table class="tableInfo" style="background-color:#bdff98;">';
		echo '<tr class="trlabel"><td colspan=6>UNPUBLISHED ('.count($queryUnPublished).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Time In</td>
				<td>Time Out</td>
				<td>Time Breaks</td>
				<td>Status</td>
				<td width="50px"><br/></td>
			</tr>';
		
		$overBreak = $this->timeM->timesetting('overBreakTime');
		foreach($queryUnPublished AS $unpublished){				
			echo '<tr>';
				echo '<td><a href="'.$this->config->base_url().'timecard/timelogs/'.$unpublished->empID_fk.'/?d='.$unpublished->slogDate.'" target="_parent">'.$unpublished->name.'</a></td>';
				echo '<td>'.(($unpublished->timeIn=='0000-00-00 00:00:00')?'None':date('h:i a', strtotime($unpublished->timeIn))).'</td>';
				echo '<td>'.(($unpublished->timeOut=='0000-00-00 00:00:00')?'None':date('h:i a', strtotime($unpublished->timeOut))).'</td>';							
				echo '<td>'.(($unpublished->timeBreak=='00:00:00')?'None':$this->textM->convertTimeToMinStr($unpublished->timeBreak)).'</td>';	
				echo '<td>';
					///STATUS
					$err = '';
					if($unpublished->timeIn=='0000-00-00 00:00:00' && $unpublished->timeOut=='0000-00-00 00:00:00') $err .= 'ABSENT,';
					else{
						if($unpublished->timeIn=='0000-00-00 00:00:00' && $unpublished->timeOut!='0000-00-00 00:00:00') $err .= ' NO TIME IN,';
						if($unpublished->timeIn!='0000-00-00 00:00:00' && $unpublished->timeOut=='0000-00-00 00:00:00') $err .= ' NO TIME OUT,';						
						if($unpublished->timeIn>$unpublished->schedIn) $err .= ' LATE,';
						if($unpublished->timeBreak>$overBreak) $err .= ' OVER BREAK,';
					}
					echo '<b class="errortext">'.rtrim($err, ',').'</b>';
				echo '</td>';			
				echo '<td><a href="'.$this->config->base_url().'timecard/'.$unpublished->empID_fk.'/viewlogdetails/?d='.$unpublished->slogDate.'&back=attendancedetails&publish=show"><button>Publish</button></a></td>';
			echo '</tr>';
		}
	echo '</table><br/>';
}

echo '<h3>DETAILS</h3>';
echo '<i>* Colored red are not yet published.</i>';
/////////////LATE
if(count($queryLate)>0){	
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>Late ('.count($queryLate).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Sched In</td>
				<td>Time In</td>
				<td>Late</td>
				<td width="50px"><br/></td>
				<td width="125px"><br/></td>
			</tr>';
		foreach($queryLate AS $late){
			echo '<tr '.(($late->publishBy=="")?'style="background-color:#ffb2b2;"':'').'>';
				echo '<td>'.$late->name.'</td>';
				echo '<td>'.date('h:i a', strtotime($late->schedIn)).'</td>';
				echo '<td>'.date('h:i a', strtotime($late->timeIn)).'</td>';
				echo '<td><b style="color:red;">'.trim($this->textM->convertTimeToMinStr($late->hourLate)).'</b></td>';
				
				echo $this->textM->displayAttAdditional($late);
			echo '</tr>';
		}
	echo '</table><br/>';
}

//////OVER BREAK
if(count($queryOverBreak)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=5>Over Break ('.count($queryOverBreak).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Total Break</td>
				<td>Over Break</td>
				<td width="50px"><br/></td>
				<td width="125px"><br/></td>
			</tr>';
		
		$strOver = strtotime($this->timeM->timesetting('overBreakTime'));
		foreach($queryOverBreak AS $over){
			echo '<tr '.(($over->publishBy=="")?'style="background-color:#ffb2b2;"':'').'>';
				echo '<td>'.$over->name.'</td>';
				echo '<td>'.trim($this->textM->convertTimeToMinStr($over->timeBreak)).'</td>';
				
				$oHour = $this->textM->convertTimeToMinHours(strtotime($over->timeBreak) - $strOver, true);
				echo '<td><b style="color:red;">'.trim($this->textM->convertTimeToMinStr($oHour)).'</b></td>';
				
				echo $this->textM->displayAttAdditional($over);
			echo '</tr>';
		}
	echo '</table><br/>';
}

//////EARLY CLOCK OUT
if(count($queryEarlyClockOut)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>Early Clock Out ('.count($queryEarlyClockOut).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Sched Out</td>
				<td>Time Out</td>
				<td>Early By</td>
				<td width="50px"><br/></td>
				<td width="125px"><br/></td>
			</tr>';
			
		foreach($queryEarlyClockOut AS $earlyout){
			echo '<tr '.(($earlyout->publishBy=="")?'style="background-color:#ffb2b2;"':'').'>';
				echo '<td>'.$earlyout->name.'</td>';
				echo '<td>'.date('h:i a', strtotime($earlyout->schedOut)).'</td>';
				echo '<td>'.date('h:i a', strtotime($earlyout->timeOut)).'</td>';
				echo '<td>'.trim($this->textM->convertTimeToMinStr($earlyout->hourEarly)).'</td>';								
				echo $this->textM->displayAttAdditional($earlyout);			
			echo '</tr>';
		}
	echo '</table><br/>';
}

//////NO CLOCK IN
if(count($queryNoClockIn)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>No Clock In ('.count($queryNoClockIn).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Sched In</td>
				<td width="50px"><br/></td>
				<td width="125px"><br/></td>
			</tr>';
			
		foreach($queryNoClockIn AS $noin){
			echo '<tr '.(($noin->publishBy=="")?'style="background-color:#ffb2b2;"':'').'>';
				echo '<td>'.$noin->name.'</td>';
				echo '<td>'.date('h:i a', strtotime($noin->schedIn)).'</td>';
								
				echo $this->textM->displayAttAdditional($noin);						
			echo '</tr>';
		}
	echo '</table><br/>';
}

//////NO cLOCK OUT
if(count($queryNoClockOut)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>No Clock Out ('.count($queryNoClockOut).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Sched Out</td>
				<td>Time In</td>
				<td width="50px"><br/></td>
				<td width="125px"><br/></td>
			</tr>';
			
		foreach($queryNoClockOut AS $noout){
			echo '<tr '.(($noout->publishBy=="")?'style="background-color:#ffb2b2;"':'').'>';
				echo '<td>'.$noout->name.'</td>';
				echo '<td>'.date('h:i a', strtotime($noout->schedOut)).'</td>';
				echo '<td>'.date('h:i a', strtotime($noout->timeIn)).'</td>';								
				echo $this->textM->displayAttAdditional($noout);				
		}
	echo '</table><br/>';
}

//////ABSENT
if(count($queryAbsent)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>Absent ('.count($queryAbsent).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Sched In</td>
				<td>Sched Out</td>
			</tr>';
			
		foreach($queryAbsent AS $absent){
			echo '<tr>';
				echo '<td>'.$absent->name.'</td>';
				echo '<td>'.date('h:i a', strtotime($absent->schedIn)).'</td>';
				echo '<td>'.date('h:i a', strtotime($absent->schedOut)).'</td>';				
		}
	echo '</table><br/>';
}

//////EARLY BIRD
if(count($queryEarlyBird)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>Early Birds ('.count($queryEarlyBird).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Sched In</td>
				<td>Time In</td>
				<td>Early By</td>
			</tr>';
			
		foreach($queryEarlyBird AS $early){
			echo '<tr>';
				echo '<td>'.$early->name.'</td>';
				echo '<td>'.date('h:i a', strtotime($early->schedIn)).'</td>';
				echo '<td>'.date('h:i a', strtotime($early->timeIn)).'</td>';
				echo '<td>'.$this->textM->convertTimeToMinStr($early->hourEarly).'</td>';
			echo '</tr>';
		}
	echo '</table><br/>';
}

//////ON LEAVES
if(count($queryLeave)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>On Leave ('.count($queryLeave).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Leave Type</td>
				<td>Leave Start</td>
				<td>Leave End</td>
				<td><br/></td>
			</tr>';
		
		$leaveTypeArr = $this->textM->constantArr('leaveType');
		foreach($queryLeave AS $leave){
			echo '<tr>';
				echo '<td>'.$leave->name.'</td>';
				echo '<td>'.$leaveTypeArr[$leave->leaveType].'</td>';
				echo '<td>'.date('d M Y h:i a', strtotime($leave->leaveStart)).'</td>';
				echo '<td>'.date('d M Y h:i a', strtotime($leave->leaveEnd)).'</td>';
				echo '<td align="right"><a href="'.$this->config->base_url().'timecard/'.$leave->empID_fk.'/viewlogdetails/?d='.$today.'&back=attendancedetails"><button>View Details</button></a></td>';
			echo '</tr>';
		}
	echo '</table><br/>';
}

//////OFFSET
if(count($queryOffset)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>Offset Today ('.count($queryOffset).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Offset In</td>
				<td>Offset Out</td>
			</tr>';
		
		foreach($queryOffset AS $offset){
			echo '<tr>';
				echo '<td>'.$offset->name.'</td>';
				
				$off = explode('|', $offset->offsetdates);
				foreach($off AS $o){
					if(!empty($o)){
						list($star, $en) = explode(',', $o);
						if(date('Y-m-d', strtotime($star))==$today){
							echo '<td>'.date('h:i a', strtotime($star)).'</td>';
							echo '<td>'.date('h:i a', strtotime($en)).'</td>';
						}
					}
				}
			echo '</tr>';
		}
	echo '</table><br/>';
}

//////SHIFT IN PROGRESS
if($today==$currentDate && count($queryInProgress)>0){
	echo '<table class="tableInfo">';
		echo '<tr class="trlabel"><td colspan=6>Shift In Progress ('.count($queryInProgress).')</td></tr>';
		echo '<tr class="trhead">
				<td width="200px">Name</td>
				<td>Sched In</td>
				<td>Time In</td>
				<td>Sched Out</td>
			</tr>';
		
		foreach($queryInProgress AS $inprogress){
			echo '<tr>';
				echo '<td>'.$inprogress->name.'</td>';
				echo '<td>'.date('h:i a', strtotime($inprogress->schedIn)).'</td>';
				echo '<td>'.date('h:i a', strtotime($inprogress->timeIn)).'</td>';
				echo '<td>'.date('h:i a', strtotime($inprogress->schedOut)).'</td>';				
			echo '</tr>';
		}
	echo '</table><br/>';
}
	

?>