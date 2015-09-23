<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecardmodel extends CI_Model {
	function __construct() {
        // Call the Model constructor
        parent::__construct();			
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
	
	public function getTimeSettings(){
		$setArr = array();
		$setQuery = $this->dbmodel->getQueryResults('staffSettings', 'settingName, settingVal');
		foreach($setQuery AS $s){
			$setArr[$s->settingName] = $s->settingVal;
		}
		return $setArr;
	}
	
	//getting schedules based on starting and end date
	//returns array of schedule format: $dayArr[datenum] with array sched, custom if custom sched
	public function getCalendarSchedule($dateStart, $dateEnd, $empID, $single=false){
		$dayArr = array();
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
			$dEnd = date('t', strtotime($dateStart));
		}
		
		//check for template schedule
		$timeArrayVal = $this->commonM->getSchedTimeArray();		
		$timeHourArrayVal = $this->commonM->getSchedHourArray();		
		$queryMainSched = $this->dbmodel->getQueryResults('tcStaffSchedules', 
							'schedID, tcCustomSched_fk, effectivestart, effectiveend, sunday, monday, tuesday, wednesday, thursday, friday, saturday', 
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
						}
							
					}
				}
			}
		}		
		
		//check for holidays
		$holidayTypeArr = $this->textM->constantArr('holidayTypes');
		if($single===true) $holidayCondition = '(holidayDate LIKE "'.date('0000-m-d', strtotime($dateStart)).'%") OR holidayDate LIKE "'.date('Y-m-d', strtotime($dateStart)).'%"';
		else $holidayCondition = '(holidayDate LIKE "'.date('0000-m-', strtotime($dateStart)).'%") OR holidayDate LIKE "'.date('Y-m-', strtotime($dateStart)).'%"';
		
		$queryHoliday = $this->dbmodel->getQueryResults('staffHolidays', 'holidayID, holidayName, holidayType, holidayWork, holidayDate', $holidayCondition);		
		foreach($queryHoliday AS $holiday){
			$day = date('j', strtotime($holiday->holidayDate));
			$dayArr[$day]['holiday'] = $holiday->holidayName;
			$dayArr[$day]['holidayType'] = $holidayTypeArr[$holiday->holidayType];
			$dayArr[$day]['schedDate'] = $year.date('-m-d', strtotime($holiday->holidayDate));			
			
			if($holiday->holidayWork==0 && !empty($dayArr[$day]['sched']))
				unset($dayArr[$day]['sched']);
		}
				
		//check for custom schedule. This will show schedule even if holiday with work
		$queryCustomSched = $this->dbmodel->getQueryResults('tcStaffScheduleByDates', 'dateToday, timeText, timeHours, status', 'empID_fk="'.$empID.'" AND dateToday>="'.$dateStart.'" AND dateToday<="'.$dateEnd.'"');		
		
		foreach($queryCustomSched AS $yeye){
			$d = date('j', strtotime($yeye->dateToday));
			if($yeye->status!=2){
				$dayArr[$d]['sched'] = $yeye->timeText;	
				$dayArr[$d]['schedHour'] = $yeye->timeHours;	
				$dayArr[$d]['schedDate'] = $yeye->dateToday;	
				if($yeye->status==0 && isset($dayArr[$d]['sched'])) 
					unset($dayArr[$d]['sched']);				
			}							
		}
		
		
		//check if for leaves PENDING PENDING PENDING		
		if($dateStart==$dateEnd){
			$conditionLeave = ' AND ("'.$dateStart.'" BETWEEN leaveStart AND leaveEnd OR leaveStart LIKE "'.$dateStart.'%" OR offsetdates LIKE "%'.date('Y-m-d', strtotime($dateStart)).'%")';
		}else{
			$conditionLeave = ' AND (leaveStart BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'" OR leaveEnd BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'" OR offsetdates LIKE "%'.date('Y-m-', strtotime($dateStart)).'%" OR offsetdates LIKE "%'.date('Y-m-', strtotime($dateEnd)).'%")';
		}
		
		$queryLeaves = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, leaveType, leaveStart, leaveEnd, offsetdates', 'empID_fk="'.$empID.'" AND iscancelled!=1 AND (status=1 OR status=2) '.$conditionLeave);	
							
		foreach($queryLeaves AS $leave){
			$start = date('Y-m-d H:i:s', strtotime($leave->leaveStart));
			$end = date('Y-m-d H:i:s', strtotime($leave->leaveEnd));								
			$leaveEnd = date('Y-m-d H:i:s', strtotime($start.' +9 hours'));
							
			while(strtotime($leaveEnd)<=strtotime($end) || strtotime($start)<=strtotime($end)){
				$dayj = date('j', strtotime($start));
				
				$curDate = date('Y-m-d', strtotime($start));					
				if(strtotime($curDate) >= $strdateStart){
					$dayArr[$dayj]['leaveID'] = $leave->leaveID;
					if(strtotime($leaveEnd)>strtotime($end)){
						$dayArr[date('j', strtotime($start))]['leave'] = date('h:i a', strtotime($start)).' - '.date('h:i a', strtotime($end));
					}else{
						$dayArr[date('j', strtotime($start))]['leave'] = date('h:i a', strtotime($start)).' - '.date('h:i a', strtotime($leaveEnd));
					}
					
					if(!isset($dayArr[$dayj]['schedDate'])) 
						$dayArr[$dayj]['schedDate'] = $curDate;
				}
				
				$start = date('Y-m-d H:i:s', strtotime($start.' +1 day'));
				$leaveEnd = date('Y-m-d H:i:s', strtotime($start.' +9 hours'));
			}
			
			if($leave->leaveType==4){
				$offset = explode('|', $leave->offsetdates);
				foreach($offset AS $o){
					if(!empty($o)){
						list($s, $e) = explode(',', $o);
						$karon = date('j', strtotime($s));
						$dayArr[$karon]['offset'] = date('h:i a', strtotime($s)).' - '.date('h:i a', strtotime($e));
						$dayArr[$karon]['leaveID'] = $leave->leaveID;
					}
				}
			}
		}
		
		foreach($dayArr AS $k=>$day){
			if(isset($day['leave']) && isset($day['sched'])){
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
		
		$schedQuery = $this->getCalendarSchedule($dateToday, $dateToday, $empID, true);
		
		if(isset($schedQuery[$day])){
			$sched = $schedQuery[$day];
			if(isset($sched['sched'])) $todayArr['sched'] = $sched['sched'];
			if(isset($sched['leave'])){
				if(isset($sched['sched'])) $todayArr['leave'] = $sched['leave'];
				else $todayArr['sched'] = 'On Leave';
				$todayArr['leaveID'] = $sched['leaveID'];
			}
			
			if(isset($sched['schedHour'])) $todayArr['schedHour'] = $sched['schedHour'];
		}
		
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
		$settingArr = $this->timeM->getTimeSettings();	
		if(empty($schedToday)) $schedToday = $this->timeM->getSchedToday($empID, $dateToday);
		
		if(!empty($schedToday['sched']) && $schedToday['sched']!='On Leave'){		
			$schedArr = $this->timeM->getSchedArr($dateToday, $schedToday['sched']);
			if(isset($schedArr['start']) && isset($schedArr['end'])){
				$datecondition = 'logtime BETWEEN "'.date('Y-m-d H:i:s', strtotime($schedArr['start'].' '.$settingArr['timeAllowedClockIn'])).'" AND "'.date('Y-m-d H:i:s', strtotime($schedArr['end'].' '.$settingArr['timeAllowedClockOut'])).'"';
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
			$query = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, empID_fk, leaveStart, leaveEnd, CONCAT(fname," ",lname) AS name', '"'.$dateToday.'" BETWEEN leaveStart AND leaveEnd AND status NOT IN (0, 3, 4, 5) AND iscancelled!=1', 'LEFT JOIN staffs ON empID=empID_fk');	
		}else if($type=='shiftinprogress'){
			$flds = ', schedIn, schedOut, timeIn';
			$condition = ' AND timeIn!="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00"';
		}else if($type=='earlyBird'){
			$hourEarly = '02:00:00'; //2 hours
			$flds = ', schedIn, timeIn, TIMEDIFF(TIME(schedIn), TIME(timeIn)) AS hourEarly';
			$condition = ' AND timeIn!="0000-00-00 00:00:00" AND schedIn!="0000-00-00 00:00:00" AND TIMEDIFF(TIME(schedIn), TIME(timeIn))>="'.$hourEarly.'"';			
		}else if($type=='noclockout'){
			$flds = ', schedOut, schedIn, timeIn';
			$condition = ' AND schedOut!="0000-00-00 00:00:00" AND timeIn!="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00" AND schedOut<"'.date('Y-m-d H:i:s').'"';	
		}else if($type=='earlyclockout'){
			$flds = ', schedOut, timeOut, TIMEDIFF(TIME(schedOut), TIME(timeOut)) AS hourEarly';
			$condition = ' AND schedOut!="0000-00-00 00:00:00" AND timeOut!="0000-00-00 00:00:00" AND schedOut>timeOut';
		}else if($type=='late'){
			$flds = ', schedIn, timeIn, TIMEDIFF(TIME(timeIn), TIME(schedIn)) AS hourLate';
			$condition = ' AND timeIn!="0000-00-00 00:00:00" AND schedIn!="0000-00-00 00:00:00" AND TIMEDIFF(TIME(timeIn), TIME(schedIn))>"00:01:00"';		
		}else if($type=='overbreak'){
			$over = '01:30:00';
			$flds = ', schedOut, timeBreak';
			$condition = ' AND timeBreak>"'.$over.'"';	
		}else if($type=='unpublished'){
			$condition = ' AND published=0 AND schedOut<"'.date('Y-m-d H:i:s').'"';				
		}else if($type=='published'){
			$condition = ' AND published=1';	
		}
		
		if($query==''){
			$query = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, logDate, empID_fk, CONCAT(fname," ",lname) AS name, published'.$flds, 'logDate="'.$dateToday.'" '.$condition, 'LEFT JOIN staffs ON empID=empID_fk');
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
			$cntNoClockOut = count($this->timeM->getNumDetailsAttendance($today, 'noclockout'));
			$cntPublished = count($this->timeM->getNumDetailsAttendance($today, 'published'));
			$cntUnPublished = count($this->timeM->getNumDetailsAttendance($today, 'unpublished'));
			
			if($attLog->late!=$cntLate) $upArr['late'] = $cntLate;
			if($attLog->absent!=$cntAbsent) $upArr['absent'] = $cntAbsent;
			if($attLog->earlyIn!=$cntEarlyBird) $upArr['earlyIn'] = $cntEarlyBird;
			if($attLog->overBreak!=$cntOverBreak) $upArr['overBreak'] = $cntOverBreak;
			if($attLog->earlyOut!=$cntEarlyClockOut) $upArr['earlyOut'] = $cntEarlyClockOut;
			if($attLog->missingClockOut!=$cntNoClockOut) $upArr['missingClockOut'] = $cntNoClockOut;
			if($attLog->published!=$cntPublished) $upArr['published'] = $cntPublished;
			if($attLog->unpublished!=$cntUnPublished) $upArr['unpublished'] = $cntUnPublished;
			
			if(count($upArr)>0) $this->dbmodel->updateQuery('tcAttendance', array('attendanceID'=>$attLog->attendanceID), $upArr);
		}
	}
	
	public function publishLogs($today){
		$condition = 'published=0';
		$condition .= ' AND logDate="'.$today.'"';
		$condition .= ' AND schedIn!="0000-00-00 00:00:00"';
		$condition .= ' AND schedOut!="0000-00-00 00:00:00"';
		$condition .= ' AND timeIn!="0000-00-00 00:00:00"';
		$condition .= ' AND timeOut!="0000-00-00 00:00:00"';
		$condition .= ' AND timeBreak!="00:00:00"';
		$condition .= ' AND timeBreak<"01:31:00"'; //time break less than 1 hour 30 mins
		$condition .= ' AND timeOut>schedOut'; //time out greater than sched out
		$condition .= ' AND timeIn<=schedIn'; //not late
		
		$query = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, logDate, empID_fk, schedHour', $condition);
		if(count($query)>0){
			$logIDs = '';
			
			foreach($query AS $q){
				$insArr['empID_fk'] = $q->empID_fk;
				$insArr['publishDate'] = $q->logDate;
				$insArr['timePaid'] = $q->schedHour;
				$insArr['tcStaffDailyLogs_fk'] = $q->tlogID;
				$insArr['datePublished'] = date('Y-m-d H:i:s');
				
				$ins = $this->dbmodel->insertQuery('tcStaffPublished', $insArr);
				$logIDs .= $q->tlogID.',';
			}
			
			if(!empty($logIDs))
				$this->dbmodel->updateQueryText('tcStaffDailyLogs', 'published=1', 'tlogID IN('.rtrim($logIDs, ',').')');
		}
		
		//update tcAttendance Records
		$this->timeM->cntUpdateAttendanceRecord($today);		
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
	
	
	
}
?>
	