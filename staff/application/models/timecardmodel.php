<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecardmodel extends CI_Model {
	function __construct() {
        // Call the Model constructor
        parent::__construct();	
		$this->load->model('payrollmodel', 'payrollM');				
    }
	
	//THIS IS FOR TIMECARD SETTINGS
	public function timesetting($fld){
		$val = '';
		
		if($fld == 'timeAllowedClockIn') $val = '-4 HOUR';
		else if($fld == 'timeAllowedClockOut') $val = '+5 HOUR';
		else if($fld == 'earlyClockIn') $val = '-2 HOUR';
		else if($fld == 'outLate') $val = '+2 HOUR';
		else if($fld == 'overBreak') $val = '5400';
		else if($fld == 'overBreakTime') $val = '01:30:00';
		else if($fld == 'overBreakTimePlus15') $val = '01:45:00';
		else if($fld == 'over15mins') $val = '900';
		else if($fld == 'overMinute15') $val = '15 MINUTE';
		
		return $val;
	}
		
	function getTodayBetweenSchedCondition($start, $end){
		if($start==$end){
			return '( (("'.$start.'" BETWEEN effectivestart AND effectiveend) AND effectiveend!="0000-00-00")
				 OR (effectiveend="0000-00-00" AND effectivestart<="'.$start.'")
				 OR(effectiveend="0000-00-00" AND effectivestart >= "'.$start.'" AND effectivestart <= "'.$end.'")		
				 )';
		}else{
			return '( (("'.$start.'" OR "'.$end.'" BETWEEN effectivestart AND effectiveend) AND effectiveend!="0000-00-00")
				 OR (effectiveend="0000-00-00" AND effectivestart<="'.$start.'")
				 OR(effectiveend="0000-00-00" AND effectivestart >= "'.$start.'" AND effectivestart <= "'.$end.'")		
				 )';
		}
	}
		
	//getting schedules based on starting and end date
	//returns array of schedule format: $dayArr[datenum] with array sched, custom if custom sched
	public function getCalendarSchedule($dateStart, $dateEnd, $empID, $single=false){
		$dayArr = array();
		$dateStart = date('Y-m-d', strtotime($dateStart));
		$dateEnd = date('Y-m-d', strtotime($dateEnd));
		
		$month = date('m', strtotime($dateStart));
		$year = date('Y', strtotime($dateStart));
	
		$strdateStart = strtotime($dateStart);
		$strdateEnd = strtotime($dateEnd);
				
		if($single===true){
			$ival = date('j', strtotime($dateStart));
			if($dateStart!=$dateEnd) $dEnd = date('j', strtotime($dateEnd));
			else $dEnd = $ival;
		}else{
			$ival = date('j', strtotime($dateStart));
			//$dEnd = date('t', strtotime($dateStart));
			$dEnd = date('j', strtotime($dateEnd));
		}
		
		//check for template schedule
		$timeArrayVal = $this->commonM->getSchedTimeArray();		
		$timeHourArrayVal = $this->commonM->getSchedHourArray();		
		$queryMainSched = $this->dbmodel->getQueryResults('tcStaffSchedules', 
							'schedID, tcCustomSched_fk, effectivestart, effectiveend, sunday, monday, tuesday, wednesday, thursday, friday, saturday, workhome', 
							'empID_fk="'.$empID.'" AND '.$this->timeM->getTodayBetweenSchedCondition($dateStart, $dateEnd), 
							'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk', 'assigndate');
		
		//if dateStart is greater than dateEnd
		if($ival>=$dEnd){
			foreach($queryMainSched AS $sched){
				for($start=$strdateStart; $start<=$strdateEnd; ){	
					$dtoday = date('Y-m-d', $start);
					if(strtotime($dtoday)>=strtotime($sched->effectivestart) && ($sched->effectiveend=='0000-00-00' || strtotime($dtoday)<strtotime($sched->effectiveend))){
						$weekType = strtolower(date('l', strtotime($dtoday)));
						if(!empty($timeArrayVal[$sched->$weekType])){
							$i = date('j', strtotime($dtoday));
							$dayArr[$i]['sched'] = $timeArrayVal[$sched->$weekType];
							$dayArr[$i]['schedHour'] = $timeHourArrayVal[$sched->$weekType];
							$dayArr[$i]['schedDate'] = $dtoday;	
							
							if($sched->workhome==1) $dayArr[$i]['workhome'] = true;	
						}
					}					
					$start = strtotime($dtoday.' +1 day');
				}
			}
		}else{
			foreach($queryMainSched AS $sched){
				for($i=$ival; $i<=$dEnd; $i++){
					$dtoday =	date('Y-m-d', strtotime($year.'-'.$month.'-'.$i));
					if(strtotime($dtoday)>=strtotime($sched->effectivestart) && ($sched->effectiveend=='0000-00-00' || strtotime($dtoday)<strtotime($sched->effectiveend))){
						$weekType = strtolower(date('l', strtotime($dtoday)));
						if(!empty($timeArrayVal[$sched->$weekType])){
							$dayArr[$i]['sched'] = $timeArrayVal[$sched->$weekType];
							$dayArr[$i]['schedHour'] = $timeHourArrayVal[$sched->$weekType];
							$dayArr[$i]['schedDate'] = $dtoday;		
							
							if($sched->workhome==1) $dayArr[$i]['workhome'] = true;	
						}							
					}
				}
			}
		}
				
		
		
		//CHECK FOR LEAVES
		if($dateStart==$dateEnd){
			$conditionLeave = ' AND ("'.$dateStart.'" BETWEEN leaveStart AND leaveEnd OR leaveStart LIKE "'.$dateStart.'%" OR offsetdates LIKE "%'.date('Y-m-d', strtotime($dateStart)).'%")';
		}else{
			$dEnd = date('Y-m-d 23:59:00', strtotime($dateEnd));
			$conditionLeave = ' AND (';
				$conditionLeave .= 'leaveStart BETWEEN "'.$dateStart.'" AND "'.$dEnd.'"';
				$conditionLeave .= ' OR leaveEnd BETWEEN "'.$dateStart.'" AND "'.$dEnd.'"';
				
				$conditionLeave .= ' OR "'.$dateStart.'" BETWEEN leaveStart AND leaveEnd';
				$conditionLeave .= ' OR "'.$dEnd.'" BETWEEN leaveStart AND leaveEnd';
				
				$conditionLeave .= ' OR offsetdates LIKE "%'.date('Y-m-', strtotime($dateStart)).'%"';
				$conditionLeave .= ' OR offsetdates LIKE "%'.date('Y-m-', strtotime($dateEnd)).'%"';
			$conditionLeave .= ')';
		}
		
		$queryLeaves = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, leaveType, leaveStart, leaveEnd, offsetdates, status, iscancelled, isrefiled, totalHours', 'empID_fk="'.$empID.'" AND iscancelled!=1 AND status NOT IN (3, 5) '.$conditionLeave);

		
		$leavestrend = strtotime($dateEnd.' +1 day');		
		foreach($queryLeaves AS $leave){
			$start = date('Y-m-d H:i:s', strtotime($leave->leaveStart));
			$end = date('Y-m-d H:i:s', strtotime($leave->leaveEnd));								
			$leaveEnd = date('Y-m-d H:i:s', strtotime($start.' +9 hours'));			
						
			while(strtotime($leaveEnd)<=strtotime($end) || strtotime($start)<=strtotime($end)){
				$dayj = date('j', strtotime($start)); 
							 
				if(isset($dayArr[$dayj]['sched']) && strtotime($start)>=$strdateStart && (strtotime($leaveEnd)<=$leavestrend || strtotime($start)<=$leavestrend)){
					if(!isset($dayArr[$dayj]['schedDate'])) $dayArr[$dayj]['schedDate'] = date('Y-m-d', strtotime($start));	
					$dayArr[$dayj]['leaveID'] = $leave->leaveID;
					$dayArr[$dayj]['leaveStatus'] = $leave->status;
					$dayArr[$dayj]['leaveStatusText'] = $this->textM->getLeaveStatusText($leave->status, $leave->iscancelled, $leave->isrefiled);
										
					if($leave->status==1){
						if($leave->totalHours==4) $dayArr[$dayj]['schedHour'] = 4;
						else $dayArr[$dayj]['schedHour'] = $dayArr[$dayj]['schedHour'];
					}else $dayArr[$dayj]['schedHour'] = 0;
					
					if(strtotime($leaveEnd)>strtotime($end)) $leaveSched = date('h:i a', strtotime($start)).' - '.date('h:i a', strtotime($end));
					else $leaveSched = date('h:i a', strtotime($start)).' - '.date('h:i a', strtotime($leaveEnd));
					
					if($leave->status==1 || $leave->status==2) $dayArr[$dayj]['leave'] = $leaveSched;
					else $dayArr[$dayj]['pendingleave'] = $leaveSched;	

					if($dayArr[$dayj]['leaveStatusText']=='Additional Information Required'){
						$dayArr[$dayj]['pendingleave'] = $leaveSched;
						unset($dayArr[$dayj]['leave']);
					}
				}
				
				$start = date('Y-m-d H:i:s', strtotime($start.' +1 day'));
				$leaveEnd = date('Y-m-d H:i:s', strtotime($start.' +9 hours'));
			}
			
			if($leave->leaveType==4){
				$offset = explode('|', $leave->offsetdates);
				foreach($offset AS $o){
					if(!empty($o)){
						list($s, $e) = explode(',', $o);
						if(date('Y-m-d', strtotime($s))>=$dateStart && date('Y-m-d', strtotime($e))<=$dateEnd){
							$karon = date('j', strtotime($s));
							$dayArr[$karon]['leaveID'][] = $leave->leaveID;
							if($leave->status==1 || $leave->status==2) $dayArr[$karon]['offset'][] = date('h:i a', strtotime($s)).' - '.date('h:i a', strtotime($e));
							else $dayArr[$karon]['pendingoffset'] = date('h:i a', strtotime($s)).' - '.date('h:i a', strtotime($e));
							
							if(!isset($dayArr[$karon]['schedDate']))
								$dayArr[$karon]['schedDate'] = date('Y-m-d', strtotime($s));
						}
					}
				}
			}
		}
				
		//CHECK SUSPENSION
		$querySuspend = $this->dbmodel->getQueryResults('staffNTE', 'nteID, suspensiondates', 'status=0 AND empID_fk="'.$empID.'" AND (suspensiondates LIKE "%'.date('Y-m', strtotime($dateStart)).'%" OR suspensiondates LIKE "%'.date('Y-m', strtotime($dateEnd)).'%")');
		foreach($querySuspend AS $qs){
			$suspend = explode('|', $qs->suspensiondates);
			foreach($suspend AS $s){
				if($s>=$dateStart && $s<=$dateEnd){
					$dayArr[date('j', strtotime($s))]['suspend'] = $qs->nteID;
				}
			}
		}
		
		foreach($dayArr AS $k=>$day){			
			if(isset($day['schedDate']) && ($day['schedDate']<$dateStart || $day['schedDate']>$dateEnd)){
				unset($dayArr[$k]);
			}else if(isset($day['leave']) && isset($day['sched'])){
				if($day['leave']==$day['sched']){
					$dayArr[$k]['sched'] = 'On Leave';
				}else{
					$schedArr = $this->getSchedArr($day['schedDate'], $day['sched']);
					if(isset($schedArr['start']) && isset($schedArr['end'])){
						$start = date('h:i a', strtotime($schedArr['start']));
						$firstend4 = date('h:i a', strtotime($schedArr['start'].' +4 hours'));
						$firstend5 = date('h:i a', strtotime($schedArr['start'].' +5 hours'));
						
						$secondstart4 = date('h:i a', strtotime($schedArr['end'].' -4 hours'));
						$secondstart5 = date('h:i a', strtotime($schedArr['end'].' -5 hours'));
						$end = date('h:i a', strtotime($schedArr['end']));
						
						if($day['leave']==$start.' - '.$firstend4 || $day['leave']==$start.' - '.$firstend5){
							$dayArr[$k]['sched'] = $secondstart4.' - '.$end;
							if($dayArr[$k]['schedHour']==4 || $dayArr[$k]['leaveStatus']==1) $dayArr[$k]['schedHour'] = 8;
							else $dayArr[$k]['schedHour'] = 4;
						}else if($day['leave']==$secondstart4.' - '.$end || $day['leave']==$secondstart5.' - '.$end){
							$dayArr[$k]['sched'] = $start.' - '.$firstend4;
							if($dayArr[$k]['schedHour']==4) $dayArr[$k]['schedHour'] = 8;
							else $dayArr[$k]['schedHour'] = 4;
						}
					}									
				} 
			}
			
			if(isset($dayArr[$k]['suspend']) && isset($dayArr[$k]['schedHour']))
				$dayArr[$k]['schedHour'] = 0;
		}
		//check for holidays
		$holidayTypeArr = $this->textM->constantArr('holidayTypes');
		if($single===true) $holidayCondition = '(holidayDate LIKE "'.date('0000-m-d', strtotime($dateStart)).'%") OR holidayDate LIKE "'.date('Y-m-d', strtotime($dateStart)).'%"';
		else $holidayCondition = '(holidayDate LIKE "'.date('0000-m-', strtotime($dateStart)).'%") OR holidayDate LIKE "'.date('Y-m-', strtotime($dateStart)).'%"';
		
		//get staff holiday schedule PH-0 US-1
		$myHoliday = $this->dbmodel->getSingleField('staffs', 'staffHolidaySched', 'empID="'.$empID.'"');
		$queryHoliday = $this->dbmodel->getQueryResults('staffHolidays', 'holidayID, holidayName, holidayType, phWork, usWork, holidayDate', $holidayCondition);	
	
		foreach($queryHoliday AS $holiday){
			$day = date('j', strtotime($holiday->holidayDate));
			$dayArr[$day]['holiday'] = $holiday->holidayName;
			$dayArr[$day]['holidayType'] = $holidayTypeArr[$holiday->holidayType];
			$dayArr[$day]['schedDate'] = $year.date('-m-d', strtotime($holiday->holidayDate));			
			
			//to show all leaves even if holidays
			if(!empty($dayArr[$day]['sched']) && (($holiday->phWork==0 && $myHoliday==0) || ($holiday->usWork==0 && $myHoliday==1))){
				unset($dayArr[$day]['sched']);
			}
		}
						
		//check for custom schedule. This will show schedule even if holiday with work
		$queryCustomSched = $this->dbmodel->getQueryResults('tcStaffScheduleByDates', 'dateToday, timeText, timeHours, status, workhome', 'empID_fk="'.$empID.'" AND dateToday>="'.$dateStart.'" AND dateToday<="'.$dateEnd.'"');		
		
		foreach($queryCustomSched AS $yeye){
			$d = date('j', strtotime($yeye->dateToday));
			if($yeye->status==1){
				$dayArr[$d]['sched'] = $yeye->timeText;	
				$dayArr[$d]['schedHour'] = $yeye->timeHours;	
				$dayArr[$d]['schedDate'] = $yeye->dateToday;	
				$dayArr[$d]['custom'] = true;	
				if($yeye->workhome==1) $dayArr[$d]['workhome'] = true;	
			}else if($yeye->status==0) unset($dayArr[$d]);		
		}
		
		return $dayArr;	
	}
	
	public function getSchedToday($empID, $dateToday){
		$todayArr = array();
		$day = date('j', strtotime($dateToday));
		
		$schedule = $this->getCalendarSchedule($dateToday, $dateToday, $empID, true);
		if(isset($schedule[$day])) $todayArr = $schedule[$day];
				
		return $todayArr;
	}
	
	//this will determine the start and end of schedule whether it advance to another date
	//returns array values start and end
	public function getSchedArr($dateToday, $schedText){
		$schedArr = array();
				
		$rang = explode(' - ', $schedText);
		if(count($rang)==2){
			$schedArr['start'] = date('Y-m-d H:i:s', strtotime($dateToday.' '.$rang[0]));
			$schedArr['end'] = date('Y-m-d H:i:s', strtotime($dateToday.' '.$rang[1]));
			
			if($schedArr['start']>$schedArr['end'])
				$schedArr['end'] = date('Y-m-d H:i:s', strtotime($schedArr['end'].' +1 day'));
				
		}		
		return $schedArr;		
	}
	
	public function getLogsToday($empID, $dateToday, $schedToday=''){
		$dateToday = date('Y-m-d', strtotime($dateToday));
		if(empty($schedToday)) $schedToday = $this->timeM->getSchedToday($empID, $dateToday);
		
		if(!empty($schedToday['sched']) && $schedToday['sched']!='On Leave'){		
			$schedArr = $this->timeM->getSchedArr($dateToday, $schedToday['sched']);
			if(isset($schedArr['start']) && isset($schedArr['end'])){
				$datecondition = 'logtime BETWEEN "'.date('Y-m-d H:i:s', strtotime($schedArr['start'].' '.$this->timeM->timesetting('timeAllowedClockIn'))).'" AND "'.date('Y-m-d H:i:s', strtotime($schedArr['end'].' '.$this->timeM->timesetting('timeAllowedClockOut'))).'"';
			}
		}
		
		//query all logs today
		if(!isset($datecondition)) $datecondition = 'logtime LIKE "'.$dateToday.'%"';			
		$staffIDNum = $this->dbmodel->getSingleField('staffs', 'idNum', 'empID="'.$empID.'"');
		$allLogs = $this->dbmodel->getQueryResults('tcTimelogs', 'logID, logtime, logtype', 'staffs_idNum_fk="'.$staffIDNum.'" AND '.$datecondition, '', 'logtime');
		
		return $allLogs;
	}
	
	public function getNumDetailsAttendance($dateToday, $type, $condition=''){
		$flds = '';
		
		$query = '';
		if($type=='absent'){
			$flds = ', schedIn, schedOut, timeIn, timeOut';
			if($dateToday==date('Y-m-d')) $condition .= ' AND schedIn<="'.date('Y-m-d H:i:s').'"';
			$condition .= 'AND timeIn="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00" AND (leaveID_fk=0 OR (leaveID_fk>0 AND (SELECT leaveType FROM staffLeaves WHERE leaveID_fk=leaveID)=4))';
		}else if($type=='leave'){
			$query = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, empID_fk, leaveStart, leaveEnd, leaveType, CONCAT(fname," ",lname) AS name', '"'.$dateToday.'" BETWEEN leaveStart AND leaveEnd AND (status=1 OR status=2) AND iscancelled!=1 '.$condition, 'LEFT JOIN staffs ON empID=empID_fk', 'leaveStart');			
		}else if($type=='offset'){			
			$query = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, empID_fk, leaveStart, leaveEnd, offsetdates, CONCAT(fname," ",lname) AS name', 'leaveType=4 AND offsetdates LIKE "%'.$dateToday.'%" AND (status=1 OR status=2) AND iscancelled!=1'.$condition, 'LEFT JOIN staffs ON empID=empID_fk', 'leaveStart');	
		}else if($type=='shiftinprogress'){
			$flds = ', schedIn, schedOut, timeIn';
			$condition .= ' AND timeIn!="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00"';
		}else if($type=='earlyBird'){
			$hourEarly = '02:00:00'; //2 hours
			$flds = ', schedIn, timeIn, TIMEDIFF(schedIn, timeIn) AS hourEarly';
			$condition .= ' AND timeIn!="0000-00-00 00:00:00" AND schedIn!="0000-00-00 00:00:00" AND TIMEDIFF(schedIn, timeIn)>="'.$hourEarly.'"';			
		}else if($type=='noclockin'){
			$flds = ', schedOut, schedIn, timeIn, timeOut';
			$condition .= ' AND schedIn!="0000-00-00 00:00:00" AND schedOut!="0000-00-00 00:00:00" AND timeIn="0000-00-00 00:00:00" AND timeOut!="0000-00-00 00:00:00"';	
		}else if($type=='noclockout'){
			$flds = ', schedOut, schedIn, timeIn';
			$condition .= ' AND schedOut!="0000-00-00 00:00:00" AND timeIn!="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00" AND schedOut<"'.date('Y-m-d H:i:s').'"';	
		}else if($type=='earlyclockout'){
			$flds = ', schedOut, timeOut, TIMEDIFF(schedOut, timeOut) AS hourEarly';
			$condition .= ' AND schedOut!="0000-00-00 00:00:00" AND timeOut!="0000-00-00 00:00:00" AND schedOut>timeOut';
		}else if($type=='late'){
			$flds = ', schedIn, timeIn, TIMEDIFF(timeIn, schedIn) AS hourLate';
			$condition .= ' AND timeIn!="0000-00-00 00:00:00" AND schedIn!="0000-00-00 00:00:00" AND TIMEDIFF(timeIn, schedIn)>"00:01:00"';		
		}else if($type=='overbreak'){
			$flds = ', schedOut, timeBreak';
			$condition .= ' AND timeBreak>"'.$this->timeM->timesetting('overBreakTime').'"';	
		}else if($type=='unpublished'){
			$flds = ', timeIn, timeOut, timeBreak, schedIn, schedOut';
			$condition .= ' AND publishBy="" AND schedOut<"'.date('Y-m-d H:i:s').'"';				
		}else if($type=='published'){
			$condition .= ' AND publishBy!=""';
		}else if($type=='unscheduled'){
			$dateoo = '0000-00-00 00:00:00';
			$query = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, slogDate, empID_fk', 'slogDate="'.$dateToday.'" AND schedIn="'.$dateoo.'" AND timeIn!="'.$dateoo.'" AND timeOut!="'.$dateoo.'" AND showStatus=1');
		}else if($type=='scheduled'){
			$dateoo = '0000-00-00 00:00:00';
			$query = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, slogDate, empID_fk', 'slogDate="'.$dateToday.'" AND (schedIn!="'.$dateoo.'" OR offsetIn!="'.$dateoo.'") AND showStatus=1');
		}
		
		if($query==''){
			$query = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, slogDate, empID_fk, CONCAT(fname," ",lname) AS name, publishBy '.$flds, 'slogDate="'.$dateToday.'"  AND showStatus=1 '.$condition.' AND office="PH-Cebu"', 'LEFT JOIN staffs ON empID=empID_fk');
		}
		
		return $query;
	}
	
	public function cntUpdateAttendanceRecord($today){
		$attLog = $this->dbmodel->getSingleInfo('tcAttendance', '*', 'dateToday="'.$today.'"');
						
		if(count($attLog)>0){
			$upArr = array();
			$cntLate = count($this->timeM->getNumDetailsAttendance($today, 'late'));
			$cntAbsent = count($this->timeM->getNumDetailsAttendance($today, 'absent'));			
			$cntEarlyBird = count($this->timeM->getNumDetailsAttendance($today, 'earlyBird'));
			$cntOverBreak = count($this->timeM->getNumDetailsAttendance($today, 'overbreak'));
			$cntEarlyClockOut = count($this->timeM->getNumDetailsAttendance($today, 'earlyclockout'));			
			$cntNoClockIn = count($this->timeM->getNumDetailsAttendance($today, 'noclockin'));
			$cntNoClockOut = count($this->timeM->getNumDetailsAttendance($today, 'noclockout'));
			$cntPublished = count($this->timeM->getNumDetailsAttendance($today, 'published'));
			$cntUnPublished = count($this->timeM->getNumDetailsAttendance($today, 'unpublished'));
			$cntUnscheduled = count($this->timeM->getNumDetailsAttendance($today, 'unscheduled'));
			$cntScheduled = count($this->timeM->getNumDetailsAttendance($today, 'scheduled'));
			
			if($attLog->late!=$cntLate) $upArr['late'] = $cntLate;
			if($attLog->absent!=$cntAbsent) $upArr['absent'] = $cntAbsent;
			if($attLog->earlyIn!=$cntEarlyBird) $upArr['earlyIn'] = $cntEarlyBird;
			if($attLog->overBreak!=$cntOverBreak) $upArr['overBreak'] = $cntOverBreak;
			if($attLog->earlyOut!=$cntEarlyClockOut) $upArr['earlyOut'] = $cntEarlyClockOut;
			if($attLog->missingClockIn!=$cntNoClockIn) $upArr['missingClockIn'] = $cntNoClockIn;
			if($attLog->missingClockOut!=$cntNoClockOut) $upArr['missingClockOut'] = $cntNoClockOut;
			if($attLog->published!=$cntPublished) $upArr['published'] = $cntPublished;
			if($attLog->unpublished!=$cntUnPublished) $upArr['unpublished'] = $cntUnPublished;
			if($attLog->unscheduled!=$cntUnscheduled) $upArr['unscheduled'] = $cntUnscheduled;
			if($attLog->scheduled!=$cntScheduled) $upArr['scheduled'] = $cntScheduled;
						
			if(count($upArr)>0) $this->dbmodel->updateQuery('tcAttendance', array('attendanceID'=>$attLog->attendanceID), $upArr);
		}
	}
	
	public function cntUpdateFinalizeAttendance($today){
		$attLog = $this->dbmodel->getSingleInfo('tcAttendance', '*', 'dateToday="'.$today.'"');
						
		if(count($attLog)>0){
			$upArr = array();	
			$cntPublished = count($this->timeM->getNumDetailsAttendance($today, 'published'));
			$cntUnPublished = count($this->timeM->getNumDetailsAttendance($today, 'unpublished'));
			
			if($attLog->published!=$cntPublished) $upArr['published'] = $cntPublished;
			if($attLog->unpublished!=$cntUnPublished) $upArr['unpublished'] = $cntUnPublished;
						
			if(count($upArr)>0) $this->dbmodel->updateQuery('tcAttendance', array('attendanceID'=>$attLog->attendanceID), $upArr);		
		}
	}
	
	/********
		If records no discrepancies, insert to update table
	********/
	public function publishLogs(){
		$time00 = '0000-00-00 00:00:00';
		$condition = 'publishBy="" AND showStatus=1';
		$updateArray = array();
		
		$condition .= ' AND (';
		///SCHEDULE NO DISCREPANCIES
		$condition .= ' (schedIn!="'.$time00.'" AND schedOut!="'.$time00.'"';
		$condition .= ' AND timeIn!="'.$time00.'" AND timeOut!="'.$time00.'"';
		$condition .= ' AND timeIn<=DATE_ADD(schedIn, INTERVAL '.$this->timeM->timesetting('overMinute15').')'; //late
		$condition .= ' AND timeOut>=DATE_ADD(schedOut, INTERVAL -'.$this->timeM->timesetting('overMinute15').')'; //early out
		$condition .= ' AND (timeBreak<"'.$this->timeM->timesetting('overBreakTimePlus15').'" OR timeBreak="00:00:00"))'; //NO BREAK OR time break less than 1 hour 30 mins
		
		//ON LEAVE
		$condition .= ' OR ';
		$condition .= ' (leaveID_fk>0 AND timeIn="'.$time00.'" AND timeOut="'.$time00.'")';
		
		//ABSENT
		$condition .= ' OR ';
		$condition .= ' (timeIn="'.$time00.'" AND timeOut="'.$time00.'" AND timeBreak="00:00:00" AND DATE_ADD(schedOut, INTERVAL '.$this->timeM->timesetting('outLate').')<"'.date('Y-m-d H:i:s').'")';
		
		$condition .= ')';
	
		
		$query = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, empID_fk, slogDate, schedHour, offsetHour, schedIn, schedOut, timeIn, timeOut, leaveID_fk, staffHolidaySched', $condition, 'LEFT JOIN staffs ON empID=empID_fk'); //include leave status for approve with pay and not an offset set status=4 if offset
									
		if(count($query)>0){
			$dateToday = date('Y-m-d H:i:s');
			
			foreach($query AS $q){
				$runpublish = true;
				$pubHours = 0;
				
				$upArr = array();
				if($q->leaveID_fk>0){					
					$leave = $this->dbmodel->getSingleInfo('staffLeaves', 'leaveID, empID_fk, leaveType, leaveStart, iscancelled, leaveEnd, status, totalHours', 'leaveID="'.$q->leaveID_fk.'" AND iscancelled!=1 AND (leaveStart<="'.$q->schedIn.'") AND (status=1 OR status=2)');
									
					if(count($leave)>0 && $leave->iscancelled!=4){
						if($leave->leaveType==4 || $leave->status==2){
							$pubHours = 0; ///for offset
						}else if($leave->totalHours==4){
							$pubHours = 4; ///for half day leave
						}else if($leave->totalHours%8==0){
							$pubHours = 8; //for whole day leave
						}else{ ///if leave is a day and with half day							
							if($leave->leaveEnd<$q->schedOut || $leave->leaveStart>$q->schedIn) $pubHours = 4;
							else $pubHours = 8;
						}
					}else $runpublish = false;
				}

				if($q->timeIn==$time00 && $q->timeOut==$time00) $pubHours += 0;
				else{
					if($q->offsetHour>0 && ($q->timeIn>$q->schedIn || $q->timeOut<$q->schedOut)) $pubHours += ($q->schedHour - $q->offsetHour); //if offset and late 
					else $pubHours += $q->schedHour;
					
					$upArr['publishND'] = $this->payrollM->getNightDiffTime($q);
				}
					
				if($runpublish==true){
					$upArr['publishTimePaid'] = $pubHours;
					
					//CHECK FOR HOLIDAY
					$holiday = $this->payrollM->isHoliday($q->slogDate);
					if($holiday!=false){
						$showHoliday = true;
						if($holiday['type']!=4 && $holiday['type']!=0){
							if($q->staffHolidaySched==1 && $holiday['type']!=3) $showHoliday = false;
							else if($q->staffHolidaySched==0 && $holiday['type']==3) $showHoliday = false;
						}
						if($showHoliday==true)
							$upArr['publishHO'] = $this->payrollM->getHolidayHours($holiday['date'], $q);
					}	
					
					$upArr['datePublished'] = $dateToday;
					$upArr['publishBy'] = 'system';	
					$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$q->slogID), $upArr);
				
					$updateArray[$q->slogDate] = true;
				}
			}
		}
		
		//update attendance record 
		foreach($updateArray AS $k=>$u){
			$this->timeM->cntUpdateAttendanceRecord($k);
		}
		
		echo 'Updated';
		exit;		
	}
	
	public function unpublishedLogs($slogID, $removePub=array()){
		//remove publish details
		$removePub['publishTimePaid'] = 0;
		$removePub['publishDeduct'] = 0;
		$removePub['publishND'] = 0;
		$removePub['datePublished'] = '0000-00-00 00:00:00';
		$removePub['publishBy'] = '';
		$removePub['publishNote'] = '';
		
		$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$slogID), $removePub); ///REMOVE PUBLISH DETAILS		
	}
	
	
	public function hourDeduction($seconds){
		$hr = 0;
		$mins = $seconds/60;
		
		if($mins>=15 && $mins<=60) $hr = 1;
		else if($mins>=61 && $mins<=120) $hr = 2;
		else if($mins>=121 && $mins<=180) $hr = 3;
		else if($mins>=181 && $mins<=300) $hr = 4;
		else if($mins>=301) $hr = 8; //1 day work day
			
		return $hr;
	}
	
	public function insertToDailyLogs($empID, $today, $schedToday){
		$logID = '';
		$insArr = array();
		$todaySmall = date('j', strtotime($today));
		
		if(isset($schedToday[$todaySmall])){ 		
			$sArr = $schedToday[$todaySmall];	
			
			if(isset($sArr['sched'])){				
				if($sArr['sched']=='On Leave' && isset($sArr['leave'])) $schedArr = $this->timeM->getSchedArr($today, $sArr['leave']);
				else $schedArr = $this->timeM->getSchedArr($today, $sArr['sched']);
									
				if(isset($schedArr['start']) && isset($schedArr['end']) && !isset($schedArr['suspend'])){
					$insArr['schedIn'] = $schedArr['start'];
					$insArr['schedOut'] = $schedArr['end'];
					$insArr['schedHour'] = $sArr['schedHour'];
				}
			}
						
			//THIS IS TO CHECK IF THERE IS AN OFFSET
			if(isset($sArr['offset'])){
				//offset is an array
				foreach( $sArr['offset'] as $offset ){
					$offArr = $this->timeM->getSchedArr($today, $offset);
					if(isset($offArr['start']) && isset($offArr['end'])){
						$insArr['offsetIn'] = $offArr['start'];
						$insArr['offsetOut'] = $offArr['end'];
						$insArr['offsetHour'] = (strtotime($offArr['end'])-strtotime($offArr['start']))/3600;
						if($insArr['offsetHour']==9) $insArr['offsetHour']=8; 
						
						if(!isset($insArr['schedIn'])){ //if no schedule for today
							$insArr['slogDate'] = $today;
							$insArr['empID_fk'] = $empID;
							
							$insArr['schedIn'] = $offArr['start'];
							$insArr['schedOut'] = $offArr['end'];
							$insArr['schedHour'] = $insArr['offsetHour'];
							
							unset($insArr['offsetIn']);
							unset($insArr['offsetOut']);
							unset($insArr['offsetHour']);
						}else{
							if($insArr['schedOut']==$offArr['start']){
								$insArr['schedOut'] = $offArr['end']; //if timeOut equals to start of offset
								$insArr['schedHour'] += $insArr['offsetHour'];
							} 
							if($insArr['schedIn']==$offArr['end']){
								$insArr['schedIn'] = $offArr['start']; //if timeIn equals to end of offset
								$insArr['schedHour'] += $insArr['offsetHour'];
							}
						}
					}	
				}
								
			}
			
			///INSERTION
			$insArr['slogDate'] = $today;
			$insArr['empID_fk'] = $empID;
			if(isset($sArr['leaveID'])) $insArr['leaveID_fk'] = $sArr['leaveID'];
				
			$logID = $this->dbmodel->getSingleField('tcStaffLogPublish', 'slogID', 'empID_fk="'.$empID.'" AND slogDate="'.$today.'" AND showStatus=1'); //check if not exist insert if not	
			if(is_numeric($logID)){
				//remove publish details
				$insArr['publishTimePaid'] = 0;
				$insArr['publishDeduct'] = 0;
				$insArr['publishND'] = 0;
				$insArr['datePublished'] = '0000-00-00 00:00:00';
				$insArr['publishBy'] = '';
				$insArr['publishNote'] = '';		
				$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$logID), $insArr);
			}else
				$logID = $this->dbmodel->insertQuery('tcStaffLogPublish', $insArr);				
		}
		
		
		return $logID;
	}
	
	public function addToLogUpdate($empID, $logDate, $message){
		$ins['empID_fk'] = $empID;
		$ins['logDate'] = $logDate;
		$ins['message'] = addslashes($message);
		$ins['status'] = 0;
		$ins['dateRequested'] = date('Y-m-d H:i:s');
		$ins['updatedBy'] = $this->user->username;
		$ins['dateUpdated'] = $ins['dateRequested'];
		$this->dbmodel->insertQuery('tcTimelogUpdates', $ins);
	}
	
	public function updateStaffLog($today, $empID){
		$today = date('Y-m-d', strtotime($today));
		
		if($today<=date('Y-m-d')){
			$schedToday = $this->timeM->getCalendarSchedule($today, $today, $empID, true);
			$logIDD = $this->timeM->insertToDailyLogs($empID, $today, $schedToday); //inserting to tcStaffLogPublish table
			$this->timeM->cntUpdateAttendanceRecord($today);
		}
	}

	public function _getCalendar( $data ){
		if( isset($data['report_start']) AND isset($data['report_end']) ){
			$dateStart = date('Y-m-01', strtotime($data['report_start']));
			$data['today'] = $data['report_start'];
			$data['currentDate'] = $data['report_start'];
			$dateEnd = date('Y-m-t', strtotime($data['report_end']));	
		} else {
			$dateStart = date('Y-m-01', strtotime($data['today']));
			$dateEnd = date('Y-m-t', strtotime($data['today']));
		}
			
			//for schedule history
			$data['timeArr'] = $this->commonM->getSchedTimeArray();			
			$data['schedData'] = $this->dbmodel->getQueryResults('tcStaffSchedules', 'schedID, empID_fk, tcCustomSched_fk, effectivestart, effectiveend, schedName, sunday, monday, tuesday, wednesday, thursday, friday, saturday, workhome', 'empID_fk="'.$data['visitID'].'"', 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk', 'assigndate DESC'); 
									
			//this is for link on the dates
			$data['dayEditOptionArr'] = array();
			$month = date('m', strtotime($data['today']));
			$year = date('Y', strtotime($data['today']));
			$dEnd = date('t', strtotime($data['today']));
			$strcurrrentdate = strtotime($data['currentDate']);
			$canfilelessthan5days = strtotime($data['currentDate'].' -5 days'); //enable file leave/offset option if date is greater than 7 days before today
			for($i=1; $i<=$dEnd; $i++){
				$dtoday = date('Y-m-d', strtotime($year.'-'.$month.'-'.$i));
				$strdatetoday = strtotime($dtoday);
				
				//if($this->access->accessFullHR===true && $strdatetoday>=$strcurrrentdate)
				if($this->access->accessFullHR===true)
					$data['dayEditOptionArr'][$i][] = array('link'=>$this->config->base_url().'schedules/customizebyday/'.$data['visitID'].'/'.$dtoday.'/', 'text'=>'Edit Schedule');
				
				if($this->user->empID==$data['visitID'] && $strdatetoday>=$canfilelessthan5days)
					$data['dayEditOptionArr'][$i][] = array('link'=>$this->config->base_url().'fileleave/', 'text'=>'File Leave/Offset');
			}
			
			$data['datelink'] = $this->config->base_url().'timecard/'.$data['visitID'].'/calendar/';
				
			//getting calendar schedule			
			$dayCurrentDate = strtotime($data['currentDate']);
			$querySchedule = $this->timeM->getCalendarSchedule($dateStart, $dateEnd, $data['visitID']);
			
			foreach($querySchedule AS $k=>$yoyo){
				$sched = '';
				if(isset($yoyo['holiday'])){
					$sched = '<div class="daysbox dayholiday"><b>'.$yoyo['holidayType'].':</b><br/>'.$yoyo['holiday'].'</div>';	
				}
				
				if(isset($yoyo['sched']) && $yoyo['sched']!='On Leave'){
					//if($this->access->accessFullHR==true && $dayCurrentDate<=strtotime($yoyo['schedDate'])){
						$sched .= '<div class="daysbox dayEditable ';							
							if(isset($yoyo['custom'])) $sched .= 'daycustomsched';
							else if(isset($yoyo['suspend'])) $sched .= 'daysuspend';
							else $sched .= 'daysched';							
						$sched .= '" onClick="checkRemove(\''.$yoyo['schedDate'].'\', \''.$yoyo['sched'].'\', '.((isset($yoyo['custom']))?1:0).')">'.$yoyo['sched'].' '.((isset($yoyo['workhome']))?'<br/>WORK HOME':'').'</div>';
					/* }else{
						$sched .= '<div class="daysbox '.((isset($yoyo['custom']))?'daycustomsched':'daysched').'">'.$yoyo['sched'].' '.((isset($yoyo['workhome']))?'<br/>WORK HOME':'').'</div>';
					} */
				}
				
				if(isset($yoyo['suspend'])){
					$sched .= '<a href="'.$this->config->base_url().'detailsNTE/'.$yoyo['suspend'].'/" class="iframe tanone"><div class="daysbox dayonleave">SUSPENDED</div></a>';
				}else{
					if(isset($yoyo['leave'])){
						$sched .= '<a href="'.$this->config->base_url().'staffleaves/'.$yoyo['leaveID'].'/" class="iframe tanone"><div class="daysbox dayonleave">On-Leave<br/>'.$yoyo['leave'].'</div></a>';
					}
					
					if(isset($yoyo['pendingleave'])){
						$sched .= '<a href="'.$this->config->base_url().'staffleaves/'.$yoyo['leaveID'].'/" class="iframe tanone"><div class="daysbox daypendingleave">Pending Leave<br/>'.$yoyo['pendingleave'].'</div></a>';
					}
					
					if(isset($yoyo['offset'])){
						$c = 0;
						foreach( $yoyo['offset'] as $off ){
							$sched .= '<a href="'.$this->config->base_url().'staffleaves/'.$yoyo['leaveID'][$c].'/" class="iframe tanone"><div class="daysbox dayoffset">Offset<br/>'.$off.'</div></a>';	
							$c++;
						}
						
					}
					
					if(isset($yoyo['pendingoffset'])){
						$sched .= '<a href="'.$this->config->base_url().'staffleaves/'.$yoyo['leaveID'].'/" class="iframe tanone"><div class="daysbox daypendingleave">Pending Offset<br/>'.$yoyo['pendingoffset'].'</div></a>';
					}
				}			
								
				if(!empty($sched) && isset($yoyo['schedDate']) && $yoyo['schedDate']<=$dateEnd && $yoyo['schedDate']>=$dateStart)
					$data['dayArr'][$k] = $sched;	
			}	
			return $data;
	}

	public function _getTimeLogs( $data ){
		if( isset($data['report_start']) AND isset($data['report_end']) ){
			$dateStart = date('Y-m-01', strtotime($data['report_start']));
			$data['today'] = $data['report_start'];
			$data['currentDate'] = $data['report_start'];
			$dateEnd = date('Y-m-t', strtotime($data['report_end']));	
		} else {
			$dateStart = date('Y-m-01', strtotime($data['today']));
			$dateEnd = date('Y-m-t', strtotime($data['today']));
		}

		//////////VARIABLE DECLARATIONS	
			$data['logtypeArr'] = $this->textM->constantArr('timeLogType');								
			$data['dayArr'] = array();
			$displayArray = array();
			
			$date00 = '0000-00-00 00:00:00';
			$dateTimeToday = date('Y-m-d H:i:s');			
			$dateStart = date('Y-m-01', strtotime($data['today']));
			$dateEnd = date('Y-m-t', strtotime($data['today']));
			$dateMonthToday = date('Y-m', strtotime($data['today']));											
			$EARLYCIN = $this->timeM->timesetting('earlyClockIn');
			$OUTL8 = $this->timeM->timesetting('outLate');
			//////////END OF VARIABLE DECLARATIONS	
			
			////CHECK FIRST IF TIME TODAY IS LESS THAN 10AM IF IT IS GET PREVIOUS SCHEDULE TO CHECK IF SHIFT STILL IN PROGRESS
			if(date('m', strtotime($data['currentDate'])) == date('m', strtotime($data['today'])) && strtotime($dateTimeToday)<strtotime(date('Y-m-d 13:00:00'))){
				$rara = date('Y-m-d', strtotime($data['currentDate'].' -1 day'));
				$schedToday = $this->timeM->getSchedToday($data['visitID'], $rara);
				$schedArr = $this->timeM->getSchedArr($rara, ((isset($schedToday['sched']))?$schedToday['sched']:''));
												
				if(isset($schedArr['end']) && strtotime($dateTimeToday)<=strtotime($schedArr['end'].' '.$this->timeM->timesetting('timeAllowedClockOut'))){
					$data['schedToday'] = $schedToday;
					$data['schedArr'] = $schedArr;
					$data['today'] = $schedToday['schedDate'];
					$data['currentDate'] = $schedToday['schedDate'];
				}
			}			
			if(empty($data['schedToday'])){
				$data['schedToday'] = $this->timeM->getSchedToday($data['visitID'], $data['currentDate']);
				$data['schedArr'] = $this->timeM->getSchedArr($data['currentDate'], ((isset($data['schedToday']['sched']))?$data['schedToday']['sched']:''));
			}
			
			
			//get all logs today
			$data['allLogs'] = $this->timeM->getLogsToday($data['visitID'], $data['currentDate'], $data['schedToday']);				
						
			//this is for on leave			
			$querySchedule = $this->timeM->getCalendarSchedule($dateStart, $dateEnd, $data['visitID']);		

			foreach($querySchedule AS $leave){
				if(isset($leave['leaveID'])){
					$datej = date('j', strtotime($leave['schedDate']));
					$displayArray[$datej]['leaveID'] = $leave['leaveID'];
					if(isset($leave['leave'])) $displayArray[$datej]['leave'] = $leave['leave'];
					if(isset($leave['pendingleave'])) $displayArray[$datej]['pendingleave'] = $leave['pendingleave']; //for pending leave
					if(isset($leave['offset'])) $displayArray[$datej]['offset'] = $leave['offset']; //for offset
					if(isset($leave['pendingoffset'])) $displayArray[$datej]['pendingoffset'] = $leave['pendingoffset']; //for pending offset
					if(isset($leave['leaveStatusText'])) $displayArray[$datej]['leaveStatusText'] = $leave['leaveStatusText']; //leave status text
				}
			}
				
			//this is for logs for the calendar month			
			$dateLogs = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, empID_fk, slogDate, DAY(slogDate) AS dayLogDate, schedIn, schedOut, timeIn, timeOut, breaks, timeBreak, numBreak, offsetIn, offsetOut, publishBy, publishTimePaid, leaveID_fk, status', 'empID_fk="'.$data['visitID'].'" AND slogDate BETWEEN "'.$dateMonthToday.'-01" AND "'.$dateMonthToday.'-31" AND showStatus=1');
	
			foreach($dateLogs AS $dl){
				$numDay = $dl->dayLogDate;
				
				//check for time in, late or early in			
				if($dl->timeIn!=$date00){
					$displayArray[$numDay]['timeIn'] = date('h:i a', strtotime($dl->timeIn));
										
					if($dl->schedIn!=$date00){
						if($dl->timeIn > date('Y-m-d H:i:s', strtotime($dl->schedIn.' +1 minutes'))) $displayArray[$numDay]['err'][] = 'isLate';
						else if(strtotime($dl->timeIn)<= strtotime($dl->schedIn.' '.$EARLYCIN) && $dl->offsetIn==$date00)  $displayArray[$numDay]['green'][] = 'isEarlyIn';
					}
				}else if($dl->timeIn==$date00 && $dl->schedIn>=$dateTimeToday && $dl->schedOut>=$dateTimeToday) 
					$displayArray[$numDay]['err'][] = 'noTimeInYet';
				else if($dl->timeIn==$date00 && $dl->schedIn!=$date00 && $dl->timeOut==$date00 && !isset($displayArray[$numDay]['leave'])) 
					$displayArray[$numDay]['err'][] = 'isAbsent';
				else if(!isset($displayArray[$numDay]['leave']) && $dl->schedIn!=$date00) 
					$displayArray[$numDay]['err'][] = 'isNoTimeIn';
				
				//check if paid off
				if($dl->timeIn==$date00 && $dl->timeOut==$date00 && $dl->publishTimePaid>0 && $dl->leaveID_fk==0){
					$displayArray[$numDay]['green'][] = 'isPaidOff';
				}
					
																
				//check for clock out
				if($dl->timeOut!=$date00){
					$displayArray[$numDay]['timeOut'] = date('h:i a', strtotime($dl->timeOut));
					
					if($dl->schedOut!=$date00){
						if($dl->timeOut < $dl->schedOut) $displayArray[$numDay]['err'][] = 'isEarlyOut';
						else if(strtotime($dl->timeOut)>= strtotime($dl->schedOut.' '.$OUTL8) && $dl->offsetOut==$date00)  $displayArray[$numDay]['green'][] = 'isOutLate';
					}					
				}else if($dl->timeIn!=$date00){
					if($dl->timeOut==$date00 && $dl->schedOut>=$dateTimeToday) $displayArray[$numDay]['green'][] = 'shiftInProgress';
					else$displayArray[$numDay]['err'][] = 'isNoClockOut';
				}
				
				if(!empty($dl->breaks)){
					$displayArray[$numDay]['breaks'] = $dl->timeBreak;
					
					if($dl->timeBreak>'01:30:00') $displayArray[$numDay]['err'][] = 'isOverBreak';
					if($dl->numBreak%2!=0) $displayArray[$numDay]['err'][] = 'isMissingBreakIn';
					
				}else if($dl->timeIn!=$date00 && $dl->timeOut!=$date00) $displayArray[$numDay]['green'][] = 'isNoBreak';
				
				if(!empty($dl->publishBy))
					$displayArray[$numDay]['publish'] = $dl->publishTimePaid;
				
				$displayArray[$numDay]['status'] = $dl->status;
			}
												
			//this is for the display
			$leaveStatArr = $this->textM->constantArr('leaveStatus');
			foreach($displayArray AS $k=>$d){
				$want = '';
				if(isset($d['publish'])) $want .= '<div class="daysbox daysched">PUBLISHED<br/>'.(($d['publish']>0)?'<b class="coloryellow">'.$d['publish'].' HOURS</b>':'').'</div>';
				if(isset($d['leaveID'])){
					if(isset($d['leave'])){
						$want .= '<a href="'.$this->config->base_url().'staffleaves/'.$d['leaveID'].'/" class="iframe tanone"><div class="daysbox dayonleave">On Leave<br/>'.$d['leave'];
						$leaveInfo = $this->dbmodel->getSingleInfo('staffLeaves', 'leaveType, status', 'leaveID='.$d['leaveID']);
						if($leaveInfo->leaveType==4) $want .= '<br/><b>(offset)</b>';
						else $want .= '<br/><b>('.$leaveStatArr[$leaveInfo->status].')</b>';
						
						$want .= '</div></a>';
					}
					if(isset($d['pendingleave'])) $want .= '<a href="'.$this->config->base_url().'staffleaves/'.$d['leaveID'].'/" class="iframe tanone"><div class="daysbox daypendingleave">Pending Leave<br/>'.$d['pendingleave'].'</div></a>';
					if(isset($d['offset'])) {
						$c_ = 0;
						foreach( $d['offset'] as $offset ){
							$want .= '<a href="'.$this->config->base_url().'staffleaves/'.$d['leaveID'][$c_].'/" class="iframe tanone"><div class="daysbox dayoffset">Offset<br/>'.$offset.'</div></a>';	
							$c_++;
						}
						
					}
					if(isset($d['pendingoffset'])) $want .= '<a href="'.$this->config->base_url().'staffleaves/'.$d['leaveID'].'/" class="iframe tanone"><div class="daysbox daypendingleave">Pending Offset<br/>'.$d['pendingoffset'].'</div></a>';
				}
				
				if(isset($d['err'])){
					$want .= '<span class="errortext">';
					foreach($d['err'] AS $err){
						if($err=='isLate') $want .= 'LATE<br/>';
						else if($err=='noTimeInYet' && !isset($d['leave'])) $want .= 'NO TIME IN YET<br/>';
						else if($err=='isAbsent') $want .= 'ABSENT<br/>';
						else if($err=='isNoTimeIn') $want .= 'NO TIME IN<br/>';
						else if($err=='isEarlyOut') $want .= 'EARLY OUT<br/>';
						else if($err=='isNoClockOut') $want .= 'NO CLOCK OUT<br/>';
						else if($err=='isOverBreak') $want .= 'OVER BREAK<br/>';
						else if($err=='isMissingBreakIn') $want .= 'MISSING BREAK IN<br/>';
					}
					$want .= '</span>';
				}
				
				if(isset($d['green'])){
					$want .= '<div style="color:green">';
						foreach($d['green'] AS $g){
							if($g=='isPaidOff'){
								$want .= 'PAID OFF<br/>';
								$want = str_replace('ABSENT<br/>','', $want);
							} 
							else if($g=='shiftInProgress') $want .= 'SHIFT IN PROGRESS<br/>';
							else if($g=='isEarlyIn') $want .= 'EARLY IN<br/>';
							else if($g=='isOutLate') $want .= 'OUT LATE<br/>';
							else if($g=='isNoBreak') $want .= 'NO BREAK<br/>';
						}						
					$want .= '</div>';
				}
				
				if(isset($d['timeIn'])) $want .= '<b>IN: </b>'.$d['timeIn'].'<br/>';
				if(isset($d['timeOut'])) $want .= '<b>OUT: </b>'.$d['timeOut'].'<br/>';
				if(isset($d['breaks'])) $want .= '<b>BREAK: </b>'.$d['breaks'].'<br/>';
				
				$data['dayArr'][$k] = $want;				
			}
												
			//ADDING dayEditOptionArr FOR EDIT DROPDOWN
			$checkIfUser = (($data['visitID']==$this->user->empID)?true:false);
			if(date('m') != date('m', strtotime($data['today']))) $daddyEnd = date('t', strtotime($data['today']));
			else $daddyEnd = date('j', strtotime($data['today']));
				
			for($daddy=1; $daddy<=$daddyEnd; $daddy++){
				if($checkIfUser && isset($displayArray[$daddy]['status']) && $displayArray[$daddy]['status']==0)
					$data['dayEditOptionArr'][$daddy][] = array('link'=>$this->config->base_url().'timecard/requestupdate/?d='.$dateMonthToday.'-'.(($daddy<10)?'0':'').$daddy, 'text'=>'Request Update');
				
				$data['dayEditOptionArr'][$daddy][] = array('link'=>$this->config->base_url().'timecard/'.$data['visitID'].'/viewlogdetails/?d='.$dateMonthToday.'-'.(($daddy<10)?'0':'').$daddy, 'text'=>'View Details');
			}
			
			/* foreach($data['dayArr'] AS $h=>$m){
				if($checkIfUser && isset($displayArray[$h]['status']) && $displayArray[$h]['status']==0){					
					$data['dayEditOptionArr'][$h][] = array('link'=>$this->config->base_url().'timecard/requestupdate/?d='.$dateMonthToday.'-'.$h, 'text'=>'Request Update');
				}					
				
				$data['dayEditOptionArr'][$h][] = array('link'=>$this->config->base_url().'timecard/'.$data['visitID'].'/viewlogdetails/?d='.$dateMonthToday.'-'.$h, 'text'=>'View Details');
			} */
			
			
			//CHECK FOR PENDING LOG REQUEST
			$updateRequests = $this->dbmodel->getQueryResults('tcTimelogUpdates', '*', 'empID_fk="'.$data['visitID'].'" AND logDate BETWEEN "'.$dateMonthToday.'-01" AND "'.$dateMonthToday.'-31" AND status=1');
			$updateArr = array();
			if(count($updateRequests) > 0){		
				foreach($updateRequests AS $u){
					$uday = date('j', strtotime($u->logDate));
					if(isset($updateArr[$uday])) $updateArr[$uday]++;
					else $updateArr[$uday] = 1;
				}
				
				foreach($updateArr AS $k=>$u2){
					$ttext = '<b class="errortext">Pending ('.$u2.')</b><br/>';
					if(isset($data['dayArr'][$k])) $data['dayArr'][$k] = $data['dayArr'][$k].$ttext;
					else $data['dayArr'][$k] = $ttext;
				}
			}	
		return $data;
	}

	public function getAttendanceReport($data){

		ini_set('memory_limit', '128M');
		$format = '%h:%i:%s';

		$data_array = array();

		if( !isset($data['visitID']) AND empty($data['visitID']) ){
			$all_staff = $this->dbmodel->getQueryResults('staffs', 'empID', 'active = 1 AND office = "PH-Cebu" AND exclude_schedule = 0 AND idNum > 0');
			foreach( $all_staff as $staff ){
				$data['visitID'][] = $staff->empID;
			}			
		} 
		$empID_fk_string = implode(', ', $data['visitID']);	
		//this is for logs for the calendar month			
		$dateLogs = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'empID, fname, lname, idNum, mname, shiftSched, slogID, empID_fk, slogDate, DAY(slogDate) AS dayLogDate, schedIn, schedOut, timeIn, timeOut, breaks, timeBreak, numBreak, offsetIn, offsetOut, publishBy, publishTimePaid, leaveID_fk, status', 'empID_fk IN ('.$empID_fk_string.') AND slogDate BETWEEN "'.$data['report_start'].'" AND "'.$data['report_end'].'" AND showStatus=1 ORDER BY idNum ASC, slogDate ASC', 'LEFT JOIN staffs ON empID = empID_fk ');

		foreach( $dateLogs as $staff_logs ){			
			$data_array[ $staff_logs->empID ]['fname'] = $staff_logs->fname;
			$data_array[ $staff_logs->empID ]['lname'] = $staff_logs->lname;
			$data_array[ $staff_logs->empID ]['mname'] = $staff_logs->mname;
			$data_array[ $staff_logs->empID ]['idNum'] = $staff_logs->idNum;
			$data_array[ $staff_logs->empID ]['staff_logs'][ $staff_logs->slogDate ] = $staff_logs;			
		}
		//check for holidays
		$holidays = $this->dbmodel->getQueryResults('staffHolidays', 'holidayType, holidayDate, holidaySched', '1');
		foreach( $holidays as $key => $val ){
			$holidays[ $val->holidayDate ] = $val->holidaySched;
		}

		//filter data and output
		$data_excel = array();
		foreach( $data_array as $empID => $staff_logs ){
			//$this->textM->aaa($staff_logs, false);
			$data_excel[ $empID ]['fname'] = $staff_logs['fname'];
			$data_excel[ $empID ]['lname'] = $staff_logs['lname'];
			$data_excel[ $empID ]['mname'] = $staff_logs['mname'];
			$data_excel[ $empID ]['idNum'] = $staff_logs['idNum'];

			foreach( $staff_logs['staff_logs'] as $date => $log ){

				$isHoliday = false;
				//recurring holidays 
				$r_holidays = date('0000-m-d', strtotime($date) );
				//one time holidays
				if( isset($holidays[ $date ]) ){
					$isHoliday = true;
				} else if( isset($holidays[$r_holidays]) AND $holidays[$r_holidays] == 0 ){
					$isHoliday = true;
				}

				$excel_array = new stdClass;

				
				if( ($log->leaveID_fk > 0 OR $isHoliday == false) AND $log->timeIn != '0000-00-00 00:00:00' ){
					$excel_array->schedIn = $log->schedIn;
					$excel_array->schedOut = $log->schedOut;
					$excel_array->timeIn = $log->timeIn;
					if( $log->timeOut != '0000-00-00 00:00:00' ){
						$excel_array->timeOut = $log->timeOut;
						$excel_array->total_time_at_work = $this->commonM->dateDifference( $log->timeIn, $log->timeOut, $format);						
						$excel_array->over_schedule = $this->commonM->dateDifference( $log->schedOut, $log->timeOut, $format);
					}
					if( $log->numBreak > 0 ){
						$excel_array->timeBreak = $log->timeBreak;
					}
					if( $log->numBreak > 0 AND isset($excel_array->total_time_at_work) ){					
						$excel_array->total_time_less_break = $this->commonM->dateDifference( $excel_array->total_time_at_work, abs($log->timeBreak), $format);
					}
					
					if( strtotime($log->timeIn) > strtotime($log->schedIn) ){
						$excel_array->late_total = $this->commonM->dateDifference( $log->schedIn, $log->timeIn, $format);	
					}
					

					$excel_array->night_diff = $this->payrollM->getNightDiffTime( $log );

					$data_excel[ $empID ]['staff_logs'][ $date ] = $excel_array;
				}

			}
		}

		//$this->textM->aaa($data_excel);
		//$this->output->enable_profiler(true);
		
		//excel reports
		require_once('includes/excel/PHPExcel/IOFactory.php');
		$fileType = 'Excel5';
		$fileName = 'includes/templates/attendance_report_template.xls';

		// Read the file
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($fileName);

		$vars = array('timeBreak', 'total_time_at_work', 'over_schedule', 'total_time_less_break', 'late_total');

		// Change the file
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->mergeCells('A1:P1');
		$objPHPExcel->getActiveSheet()->getStyle('A1:P1')->getFont()->setBold(true);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'ATTENDANCE REPORT - '.date('F d, Y', strtotime($data['report_start'])).' - '.date('F d, Y', strtotime($data['report_end'])));
		//we are starting at row 2
		$cnt = 2;			
		foreach( $data_excel as $empID => $staff_logs ){
			//headers
			$objPHPExcel->getActiveSheet()->getStyle('A'.$cnt.':N'.$cnt)->getFont()->setBold(true);
			//header 1
			$objPHPExcel->getActiveSheet()->mergeCells('A'.$cnt.':D'.$cnt);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, ucwords( $staff_logs['fname'].' '.$staff_logs['lname'] ) );
			$objPHPExcel->getActiveSheet()->mergeCells('E'.$cnt.':F'.$cnt);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, 'Scheduled' );
			$objPHPExcel->getActiveSheet()->mergeCells('G'.$cnt.':H'.$cnt);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt, 'Actual' );
			$objPHPExcel->getActiveSheet()->mergeCells('I'.$cnt.':M'.$cnt);			
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, 'Summary' );			

			$cnt++;
			$objPHPExcel->getActiveSheet()->getStyle('A'.$cnt.':P'.$cnt)->getFont()->setBold(true);
			//header 2
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, 'Employee Number' );
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, 'Week Day' );
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, 'Week Day No' );
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, 'Date' );
			//scheduled
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, 'Time In' );
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt, 'Time Out' );
			
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt, 'Time In' );
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$cnt, 'Time Out' );

			$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, 'Break Total' );
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, 'Total Time At Work' );
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, 'Total Time Less Break' );
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$cnt, 'Late Total' );
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$cnt, 'Hours Over Schedule Total' );
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$cnt, 'Night Differential' );

			$cnt++;
			//end of headers
			if( isset($staff_logs['staff_logs']) ){
				foreach( $staff_logs['staff_logs'] as $date => $log ){
					

					$empID = sprintf("%'.03d", $staff_logs['idNum']);
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$cnt, $empID );
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$cnt, date('D', strtotime($date) ) );
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$cnt, date('N', strtotime($date) ) );
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$cnt, $date  );

					$objPHPExcel->getActiveSheet()->setCellValue('E'.$cnt, date('h:i a', strtotime($log->schedIn) ) );
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$cnt, date('h:i a', strtotime($log->schedOut) ) );
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$cnt, date('h:i a', strtotime($log->timeIn) ) );
					if( isset($log->timeBreak) ){
						$objPHPExcel->getActiveSheet()->setCellValue('H'.$cnt, date('h:i a', strtotime($log->timeOut) ) );
					}


					if( isset($log->timeBreak) ){
						$objPHPExcel->getActiveSheet()->setCellValue('I'.$cnt, $log->timeBreak );
					}
					if( isset($log->total_time_at_work) ){
						$objPHPExcel->getActiveSheet()->setCellValue('J'.$cnt, $log->total_time_at_work );
					}
					if( isset($log->total_time_less_break) ){
						$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, $log->total_time_less_break );
					} else {
						$objPHPExcel->getActiveSheet()->setCellValue('K'.$cnt, $log->total_time_at_work );
					}
					if( isset($log->late_total) ){
						$objPHPExcel->getActiveSheet()->setCellValue('L'.$cnt, $log->late_total );
					}
					if( isset($log->over_schedule) ){
						$objPHPExcel->getActiveSheet()->setCellValue('M'.$cnt, $log->over_schedule );
					}
					
					if( $log->night_diff > 0 ){
						$objPHPExcel->getActiveSheet()->setCellValue('N'.$cnt, $log->night_diff );	
					}
					

					
					$cnt++;
				}
			}
			



			$cnt+=2;

		}



		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
		ob_end_clean();
		// We'll be outputting an excel file
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="Attendance_Report-'.date('Y-m-d', strtotime($data['report_start'])).'-'.date('Y-m-d', strtotime($data['report_end'])).'.xls"');
		$objWriter->save('php://output');
		
	}
	
	
}
?>
	