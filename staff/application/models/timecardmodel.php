<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecardmodel extends CI_Model {
	function __construct() {
        // Call the Model constructor
        parent::__construct();			
    }
	
	//THIS IS FOR TIMECARD SETTINGS
	public function timesetting($fld){
		$val = '';
		
		if($fld == 'timeAllowedClockIn') $val = '-4 HOUR';
		else if($fld == 'timeAllowedClockOut') $val = '+4 HOUR';
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
					if(strtotime($dtoday)>=strtotime($sched->effectivestart) && ($sched->effectiveend=='0000-00-00' || strtotime($dtoday)<=strtotime($sched->effectiveend))){
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
					if(strtotime($dtoday)>=strtotime($sched->effectivestart) && ($sched->effectiveend=='0000-00-00' || strtotime($dtoday)<=strtotime($sched->effectiveend))){
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
			
			if(!empty($dayArr[$day]['sched']) && (($holiday->phWork==1 && $myHoliday==0) || ($holiday->usWork==1 && $myHoliday==1)))
				unset($dayArr[$day]['sched']);
		}
				
		//check for custom schedule. This will show schedule even if holiday with work
		$queryCustomSched = $this->dbmodel->getQueryResults('tcStaffScheduleByDates', 'dateToday, timeText, timeHours, status, workhome', 'empID_fk="'.$empID.'" AND status=1 AND dateToday>="'.$dateStart.'" AND dateToday<="'.$dateEnd.'"');		
		
		foreach($queryCustomSched AS $yeye){
			$d = date('j', strtotime($yeye->dateToday));
			$dayArr[$d]['sched'] = $yeye->timeText;	
			$dayArr[$d]['schedHour'] = $yeye->timeHours;	
			$dayArr[$d]['schedDate'] = $yeye->dateToday;	
			$dayArr[$d]['custom'] = true;	
			if($yeye->workhome==1) $dayArr[$d]['workhome'] = true;							
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
		
		$queryLeaves = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, leaveType, leaveStart, leaveEnd, offsetdates, status', 'empID_fk="'.$empID.'" AND iscancelled!=1 AND status NOT IN (3, 5) '.$conditionLeave);
		
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
					
					if(strtotime($leaveEnd)>strtotime($end)) $leaveSched = date('h:i a', strtotime($start)).' - '.date('h:i a', strtotime($end));
					else $leaveSched = date('h:i a', strtotime($start)).' - '.date('h:i a', strtotime($leaveEnd));
					
					if($leave->status==1 || $leave->status==2) $dayArr[$dayj]['leave'] = $leaveSched;
					else $dayArr[$dayj]['pendingleave'] = $leaveSched;					
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
							$dayArr[$karon]['leaveID'] = $leave->leaveID;
							if($leave->status==1 || $leave->status==2) $dayArr[$karon]['offset'] = date('h:i a', strtotime($s)).' - '.date('h:i a', strtotime($e));
							else $dayArr[$karon]['pendingoffset'] = date('h:i a', strtotime($s)).' - '.date('h:i a', strtotime($e));
							
							if(!isset($dayArr[$karon]['schedDate']))
								$dayArr[$karon]['schedDate'] = date('Y-m-d', strtotime($s));
						}
					}
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
							$dayArr[$k]['schedHour'] = 4;
						}else if($day['leave']==$secondstart4.' - '.$end || $day['leave']==$secondstart5.' - '.$end){
							$dayArr[$k]['sched'] = $start.' - '.$firstend4;
							$dayArr[$k]['schedHour'] = 4;
						}
					}									
				} 
			}
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
			$condition .= 'AND timeIn="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00"';
		}else if($type=='leave'){
			/* $empID_sched = array();
			$scheduld = $this->timeM->getNumDetailsAttendance($dateToday, 'scheduled', $condition);
			foreach($scheduld AS $s)
				$empID_sched[] = $s->empID_fk;
				 */
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
			$flds = ', timeIn, timeOut, timeBreak';
			$condition .= ' AND publish_fk=0 AND schedOut<"'.date('Y-m-d H:i:s').'"';				
		}else if($type=='published'){
			$condition .= ' AND publish_fk>0';	
		}else if($type=='unscheduled'){
			$dateoo = '0000-00-00 00:00:00';
			$query = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, logDate, empID_fk', 'logDate="'.$dateToday.'" AND schedIn="'.$dateoo.'" AND timeIn!="'.$dateoo.'" AND timeOut!="'.$dateoo.'"');
		}else if($type=='scheduled'){
			$dateoo = '0000-00-00 00:00:00';
			$query = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, logDate, empID_fk', 'logDate="'.$dateToday.'" AND (schedIn!="'.$dateoo.'" OR offsetIn!="'.$dateoo.'")');
		}
		
		if($query==''){
			$query = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, logDate, empID_fk, CONCAT(fname," ",lname) AS name, publish_fk '.$flds, 'logDate="'.$dateToday.'" '.$condition, 'LEFT JOIN staffs ON empID=empID_fk');
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
	
	/********
		If records no discrepancies, insert to update table
	********/
	public function publishLogs($today){
		$condition = 'publish_fk=0';
		$condition .= ' AND logDate="'.$today.'"';
		/////TIME IN
		$first = 'schedIn!="0000-00-00 00:00:00"';
		$first .= ' AND timeIn!="0000-00-00 00:00:00"';
		$first .= ' AND timeIn<=DATE_ADD(schedIn, INTERVAL '.$this->timeM->timesetting('overMinute15').')';
		
		//////TIME OUT
		$first .= ' AND schedOut!="0000-00-00 00:00:00"';		
		$first .= ' AND timeOut!="0000-00-00 00:00:00"';
		$first .= ' AND timeOut>=DATE_ADD(schedOut, INTERVAL -'.$this->timeM->timesetting('overMinute15').')';
		
		//////BREAKS
		$first .= ' AND (timeBreak<"'.$this->timeM->timesetting('overBreakTimePlus15').'" OR timeBreak="00:00:00")'; //NO BREAK OR time break less than 1 hour 30 mins
			
		
		//SECOND CONDITION PUBLISH WITH 0 TIME FOR ABSENT WITH SCHEDULE TODAY
		$second = 'schedIn!="0000-00-00 00:00:00" AND timeIn="0000-00-00 00:00:00"';
		$second .= ' AND schedOut!="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00"';
		$second .= ' AND schedOut<="'.date('Y-m-d H:i:s').'"';
		
		$condition .= ' AND (('.$first.') OR ('.$second.'))';
		
		$query = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, logDate, empID_fk, schedHour, timeIn, timeOut', $condition);
							
		if(count($query)>0){
			$logIDs = '';
			
			foreach($query AS $q){
				$insArr['empID_fk'] = $q->empID_fk;
				$insArr['publishDate'] = $q->logDate;
				if($q->timeIn=='0000-00-00 00:00:00' && $q->timeOut=='0000-00-00 00:00:00') $insArr['timePaid'] = 0;
				else $insArr['timePaid'] = $q->schedHour;
				
				$insArr['tcStaffDailyLogs_fk'] = $q->tlogID;
				$insArr['datePublished'] = date('Y-m-d H:i:s');
				
				$ins = $this->dbmodel->insertQuery('tcStaffPublished', $insArr);
				$this->dbmodel->updateQueryText('tcStaffDailyLogs', 'publish_fk='.$ins, 'tlogID="'.$q->tlogID.'"');
				$logIDs .= $q->tlogID.',';
			}
			
			if(!empty($logIDs)){
				$this->timeM->cntUpdateAttendanceRecord($today); //update tcAttendance Records		
			}				
		}
		
		echo '<pre>';
		print_r($query);
		echo '</pre>';
		exit;
	}
	
	public function hourDeduction($seconds){
		$hr = 0;
		$mins = $seconds/60;
		
		if($mins>=15 && $mins<=60) $hr = 1;
		else if($mins>=61 && $mins<=120) $hr = 2;
		else if($mins>=121 && $mins<=180) $hr = 3;
		else if($mins>=181 && $mins<=240) $hr = 4;
		else if($mins>=241) $hr = 8; //1 day work day
			
		return $hr;
	}
	
	public function insertToDailyLogs($empID, $today, $schedToday){
		$logID = '';
		$todaySmall = date('j', strtotime($today));
			
		if(isset($schedToday[$todaySmall])){
			$sArr = $schedToday[$todaySmall];	
			
			$insArr = array();
			if(isset($sArr['sched'])){
				$schedArr = $this->timeM->getSchedArr($today, $sArr['sched']);
				if(isset($schedArr['start']) && isset($schedArr['end'])){
					$insArr['schedIn'] = $schedArr['start'];
					$insArr['schedOut'] = $schedArr['end'];
					$insArr['schedHour'] = $sArr['schedHour'];
				}
			}
			
			//THIS IS TO CHECK IF THERE IS AN OFFSET
			if(isset($sArr['offset'])){
				$offArr = $this->timeM->getSchedArr($today, $sArr['offset']);
				if(isset($offArr['start']) && isset($offArr['end'])){
					$insArr['offsetIn'] = $offArr['start'];
					$insArr['offsetOut'] = $offArr['end'];
					$insArr['offsetHour'] = (strtotime($offArr['end'])-strtotime($offArr['start']))/3600;
					
					if(!isset($insArr['schedIn'])){ //if no schedule for today
						$insArr['logDate'] = $today;
						$insArr['empID_fk'] = $empID;
						
						$insArr['schedIn'] = $offArr['start'];
						$insArr['schedOut'] = $offArr['end'];
						$insArr['schedHour'] = $insArr['offsetHour'];
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
			
			if(!empty($insArr)){
				$insArr['logDate'] = $today;
				$insArr['empID_fk'] = $empID;
				$logID = $this->dbmodel->insertQuery('tcStaffDailyLogs', $insArr);
			}
		}
		
		return $logID;
	}
	
	
	
}
?>
	