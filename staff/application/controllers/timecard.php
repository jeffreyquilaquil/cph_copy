<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecard extends MY_Controller {
 
	public function __construct(){
		parent::__construct();		
		$this->load->model('timecardmodel', 'timeM');
		$this->load->model('payrollmodel', 'payrollM');
	}
		
	public function _remap($method){
		$segment3 = $this->uri->segment(3);
		$segment4 = $this->uri->segment(4);
		$data['currentDate'] = date('Y-m-d'); //this is the exact date today		
		$data['currentDatetime'] = date('Y-m-d H:i:s'); //this is the exact date today		
		$data['visitID'] = (($this->user!=false)?$this->user->empID:'');
		$data['timecardpage'] = true;
						
		//this is the customized date according to passed url
		if(isset($_GET['d'])) $data['today'] = $_GET['d'];
		else $data['today'] = date('Y-m-d'); 
			
		if(is_numeric($method)){
			$data['column'] = 'withLeft';
			$data['visitID'] = $method;
			$data['row'] = $this->dbmodel->getSingleInfo('staffs','empID,username, fname, CONCAT(fname," ",lname) AS name', 'empID="'.$method.'"');
						
			if(empty($segment3)){
				$this->timelogs($data);
			}else{
				$this->$segment3($data);
			}
		}else{
			if($method=='index')
				$this->timelogs($data);
			else
				$this->$method($data);
		}	
	}
		
	public function timetest(){	
		$this->timeM->cntUpdateAttendanceRecord('2015-11-09');
		$this->timeM->cntUpdateAttendanceRecord('2015-11-10');
		$this->timeM->cntUpdateAttendanceRecord('2015-11-11');
		$this->timeM->cntUpdateAttendanceRecord('2015-11-12');
		$this->timeM->cntUpdateAttendanceRecord('2015-11-13');
		$this->timeM->cntUpdateAttendanceRecord('2015-11-14');
		$this->timeM->cntUpdateAttendanceRecord('2015-11-16');
		$this->timeM->cntUpdateAttendanceRecord('2015-11-17');
		//$this->timeM->publishLogs();
		exit;
	}
		
	//runs everyday at 12am
	//get staff schedules and insert to tcStaffLogPublish
	//insert to tcAttendance for summary of results today
	//public function cronDailySchedulesAndAttendance(){
	public function cronDailySchedulesAndAttendance(){
		$today = date('Y-m-d');
		$todaySmall = date('j');
		$scheduled = 0;
		
		//CHECK FOR STAFFS TODAY SCHEDULES
		$staffID = '';
		$querySchd = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'empID_fk', 'slogDate="'.$today.'"');
		if(count($querySchd)>0){
			foreach($querySchd AS $s) $staffID .= $s->empID_fk.',';
		}
		if($staffID!=''){
			$staffID = ' AND empID NOT IN ('.rtrim($staffID, ',').')';
		}
		
		//STAFF SCHEDULES		
		$queryStaffs = $this->dbmodel->getQueryResults('staffs', 'empID', 'active=1'.$staffID);		
		foreach($queryStaffs AS $staff){
			$schedToday = $this->timeM->getCalendarSchedule($today, $today, $staff->empID, true);
			$logIDD = $this->timeM->insertToDailyLogs($staff->empID, $today, $schedToday); //inserting to tcStaffLogPublish table
			if(!empty($logIDD)) $scheduled++;
		}
		
		//INSERT TO TCATTENDANCE TABLE IF NOT EXIST ELSE UPDATE Records
		$attLog = $this->dbmodel->getSingleInfo('tcAttendance', '*', 'dateToday="'.$today.'"');
		if(count($attLog)==0){
			$ins['dateToday'] = $today;
			$ins['scheduled'] = $scheduled;
			$ins['unpublished'] = $scheduled;
			
			$ins['holidayType'] = $this->dbmodel->getSingleField('staffHolidays', 'holidayType', 'holidayDate="'.$ins['dateToday'].'" OR holidayDate="0000-'.date('m-d', strtotime($ins['dateToday'])).'"');
			$ins['numEmployees'] = $this->dbmodel->getSingleField('staffs', 'COUNT(empID)', 'active=1 AND startDate <= "'.$ins['dateToday'].'" AND (endDate="0000-00-00" OR endDate>="'.$ins['dateToday'].'")');
			
			//leaves
			$queryLeaves = $this->timeM->getNumDetailsAttendance($ins['dateToday'], 'leave');	
			$ins['scheduledLeave'] = count($queryLeaves);
			
			//offset
			$queryOffset = $this->timeM->getNumDetailsAttendance($ins['dateToday'], 'offset');
			$ins['scheduledOffset'] = count($queryOffset);
			
			//if($scheduled>0)
			$this->dbmodel->insertQuery('tcAttendance', $ins);	///INSERT TO TCATTENDANCE TABLE if there there is scheduled work today
		}else{
			$this->timeM->cntUpdateAttendanceRecord($today);
		}
	}
	
	
	/**********
		This cron runs every 5 minutes.
			- check if there are logs
			- check if there is entry on tcStaffLogPublish insert 1 if none. Inserted data will serve as clocked in with no schedule (this needs resolve)
			- insert to tcStaffLogPublish
			- changed tcTimelogs isInserted to 1
	**********/
	public function cronDailyLogs(){
		//Additional hours set here
		$timeAllowedClockIn = $this->timeM->timesetting('timeAllowedClockIn');
		$timeAllowedClockOut = $this->timeM->timesetting('timeAllowedClockOut');
				
		$logArr = array();
		$date00 = '0000-00-00 00:00:00';
		$logIDInserted = array();
		
		$logQuery = $this->dbmodel->getQueryResults('tcTimelogs', 'tcTimelogs.*, empID, username', 'isInserted=0', 'LEFT JOIN staffs ON idNum=staffs_idNum_fk', 'logtime');
	
		if(count($logQuery)>0){			
			foreach($logQuery AS $log){
				$logID = $this->dbmodel->getSingleField('tcStaffLogPublish', 'slogID', 'empID_fk="'.$log->empID.'" AND ("'.$log->logtime.'" BETWEEN DATE_ADD(schedIn, INTERVAL '.$timeAllowedClockIn.')  AND DATE_ADD(schedOut, INTERVAL '.$timeAllowedClockOut.'))'); //get log id belong to certain schedule
				
				if(empty($logID)){ //check if there is logs with no schedule on the same day
					$logID = $this->dbmodel->getSingleField('tcStaffLogPublish', 'slogID', 'empID_fk="'.$log->empID.'" AND slogDate="'.date('Y-m-d', strtotime($log->logtime)).'"');
				}
				
				if(empty($logID)){ //if still empty insert records
					$logdate = date('Y-m-d', strtotime($log->logtime));
					$schedText[date('j', strtotime($log->logtime))] = $this->timeM->getSchedToday($log->empID, $logdate, true);								
					$logID = $this->timeM->insertToDailyLogs($log->empID, $logdate, $schedText); //inserting to tcStaffLogPublish table
					$this->timeM->cntUpdateAttendanceRecord($logdate); //update records
				}
				
				if(!empty($logID))
					$logArr[$logID][] = array('baselogid'=>$log->logID, 'logtime'=>$log->logtime, 'type'=>$log->logtype);			
			}
	
			if(count($logArr)>0){
				foreach($logArr AS $id=>$trying){
					$logData = $this->dbmodel->getSingleInfo('tcStaffLogPublish', 'slogID, slogDate, timeIn, timeOut, breaks, timeBreak, numBreak, schedIn, schedOut, offsetIn, offsetOut, schedHour, offsetHour', 'slogID="'.$id.'"');
															
					$updateArr = array();									
					if(count($trying)>0){
						//breaks record
						$updateArr['breaks'] = $logData->breaks;
						$updateArr['numBreak'] = $logData->numBreak;
												
						foreach($trying AS $d){
							if($d['type']=='A'){
								if($logData->timeIn==$date00 && !isset($updateArr['timeIn'])) $updateArr['timeIn'] = $d['logtime']; //for time in
								else if($logData->offsetHour>0 && $logData->schedIn!=$logData->offsetOut && $logData->schedOut!=$logData->offsetIn){
									if(strtotime($d['logtime']) >= strtotime($logData->offsetIn.' '.$timeAllowedClockIn)){
										$updateArr['offTimeIn'] = $d['logtime']; //for offset
									}
								}  
							}else if($d['type']=='Z'){ //for time out								
								if($logData->offsetHour>0 && $logData->schedIn!=$logData->offsetOut && $logData->schedOut!=$logData->offsetIn){
									if(strtotime($d['logtime']) >= strtotime($logData->offsetOut) && strtotime($d['logtime']) <= strtotime($logData->offsetOut.' '.$timeAllowedClockOut) ){
										$updateArr['offTimeOut'] = $d['logtime']; //for offset
										$updateArr['timeOut'] = $d['logtime'];	
									}else $updateArr['timeOut'] = $d['logtime'];								
								}else if(strtotime($d['logtime']) >= strtotime($logData->offsetOut.' '.$timeAllowedClockOut)) $updateArr['timeOut'] = $d['logtime'];
							}else if($logData->timeIn!=$date00 || isset($updateArr['timeIn'])){ //this is for breaks and there is time in recorded
								$updateArr['breaks'] .= $d['logtime'].'|';
								$updateArr['numBreak']++;
							}
							
							$logIDInserted[] = $d['baselogid'];	
						}
												
						//if timeout but missing break in
						if((isset($updateArr['timeOut']) || $logData->timeOut!=$date00) && $updateArr['numBreak']%2!=0){
							$updateArr['breaks'] .= ((isset($updateArr['timeOut']))?$updateArr['timeOut']:$logData->timeOut).'|';
							$updateArr['numBreak']++;
						}
						
						if($updateArr['numBreak']%2==0){
							$compute = 0;
							$timebreaks = 0;
							$breaks = explode('|', rtrim($updateArr['breaks'], '|'));
							foreach($breaks AS $b){
								if($compute==0) $compute = strtotime($b);
								else{
									$timebreaks += strtotime($b) - $compute;
									$compute = 0;
								}
							}
							$updateArr['timeBreak'] = $this->textM->convertTimeToMinHours($timebreaks,true);
						}
						
						 if(!empty($updateArr))
							$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$id), $updateArr);
					}				
				}
			}
		
			//change value of isInserted to 1 meaning log is inserted
			if(count($logIDInserted)>0){
				$this->dbmodel->updateQueryText('tcTimelogs', 'isInserted="1"', 'logID IN ('.implode(',', $logIDInserted).')');
				echo 'Successfully inserted.';
			}
			
			$this->cronDailyAttendanceRecord();////CALL TO PUBLISH AND UPDATE RECORDS
		}
		exit;		
	}
	
	
	/*********
		This cron will run every hour to update records in tcAttendance
		check previous date if time is less than or equal to 10AM today
		and PUBLISH data
	*********/
	public function cronDailyAttendanceRecord(){
		$this->timeM->publishLogs();	
	}
		
	/****
		This cron runs every 30 minutes of an hour to check if employee clocked in and clocked out. 
		If not, then it will send an employee reminding to clock in or clock out.
	****/
	public function cronTimecardLogsEmails(){
		///GET SCHEDULES STAFFS
		$staffs = array();
		$staffWithSched = $this->dbmodel->getQueryResults('tcStaffSchedules', 'DISTINCT empID_fk');
		foreach($staffWithSched AS $s)
			array_push($staffs, $s->empID_fk);
	
		$dateToday = date('Y-m-d H:00:00');
		//SEND EMAIL TO EMPLOYEES WITH NO TIME IN YET BUT schedIn is the current hour
		$queryNoTimeIn = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'empID_fk, email, fname, schedIn, schedOut, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor) AS supEmail, leaveID_fk', 'schedIn="'.$dateToday.'" AND active=1 AND timeIn="0000-00-00 00:00:00"', 'LEFT JOIN staffs ON empID=empID_fk');	
		
		if(count($queryNoTimeIn)>0){
			foreach($queryNoTimeIn AS $timein){
				if(in_array($timein->empID_fk, $staffs)){
					$email = true;
					if($timein->leaveID_fk>0){ ////check for leaves if paid or not paid dont send email
						$leave = $this->dbmodel->getSingleInfo('staffLeaves', 'leaveID, empID_fk, leaveType, leaveStart, leaveEnd, status, totalHours', 'leaveID="'.$timein->leaveID_fk.'" AND iscancelled!=1 AND (leaveStart<="'.$timein->schedIn.'") AND (status=1 OR status=2)');
						if(count($leave)>0) $email = false;
					}
					
					if($email===true)
						$this->emailM->emailTimecard('notimein', $timein);
				}
					
			}
		}		
		
		//SEND EMAIL TO EMPLOYEES IF NO CLOCK OUT YET AFTER 4 HOURS
		$queryNoClockOut = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'empID_fk, email, fname, schedIn, schedOut, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor) AS supEmail', 'schedOut="'.date('Y-m-d H:00:00', strtotime(' -4 hours')).'" AND active=1 AND timeIn!="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00"', 'LEFT JOIN staffs ON empID=empID_fk');
		
		
		if(count($queryNoClockOut)>0){
			foreach($queryNoClockOut AS $timeOut){
				if(in_array($timein->empID_fk, $staffs))
					$this->emailM->emailTimecard('noclockout2hours', $timeOut);
			} 
		}
		
		exit;
	}
	
	/******
		This cron runs every 9th and 24th of the month to remind employees to check their logs for the current pay period
		For 15th pay send on 9th of the month for pay period - from 26-10 deadline is 11th
		For 30th pay send on 24th of the month for pay period - from 11-25 deadline is 26th
	******/
	public function cronEmailReviewLogReminder(){
		if(date('d')==9){ //check if day today is 9th of the month
			$period = date('M 26', strtotime(' -1 month')).' - '.date('M 10');
			$deadline = date('l, F 11, Y 07:00 A');
		}else{
			$period = date('M 11').' - '.date('M 25');
			$deadline = date('l, F 26, Y 07:00 A');
		}
		
		$this->emailM->emailTimecardCheckLogforPay($period, $deadline); //SENDING TO DAYSHIFT AND NIGHT SHIFT CC LEADERS	
		exit;
	}
	
	/**********
		This is a cron that will send email to employees and HR for unpublished logs
		Runs on 11th and 26th of the month 7AM for HR and 10AM for employees for 15th and 30th pay period
		15th pay for 26-10
		30th pay for 11-25
	**********/
	public function cronEmailUnpublishedLogs(){
		$page = $this->uri->segment(3);
			
		if(date('d')==11){ //check if day today is 11th of the month
			$dateStart = date('Y-m-26', strtotime('-1 month'));
			$dateEnd = date('Y-m-10');
		}else{ //if today is 26th of the month
			$dateStart = date('Y-m-11');
			$dateEnd = date('Y-m-25');
		}
		
		$query = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, slogDate, empID_fk, email, fname, lname', 'publishBy="" AND slogDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'"', 'LEFT JOIN staffs ON empID=empID_fk', 'slogDate');		
		if(count($query)>0){
			if($page=='hr') $this->emailM->emailTimecardUnpublishedLogs($dateStart, $dateEnd, $query, 'HR');
			else $this->emailM->emailTimecardUnpublishedLogs($dateStart, $dateEnd, $query);
		}
		exit;
	}
	
	public function timelogs($data){
		$data['content'] = 'v_timecard/v_timelogs';
		$data['tpage'] = 'timelogs';
								
		if($this->user!=false){
			$id = $this->uri->segment(2);
			if(is_numeric($id) && $this->commonM->checkStaffUnderMe($id)==false){
				header('Location:'.$this->config->base_url().'timecard/timelogs/');
				exit;
			}
			
			if(!empty($_POST)){
				if($_POST['submitType']=='recordbreak'){
					$insLog['staffs_idNum_fk'] = $this->user->idNum;
					$insLog['logtime'] = date('Y-m-d H:i:s');
					$insLog['dateInserted'] = date('Y-m-d H:i:s');
					$insLog['logtype'] = $_POST['logtype'];
					$this->dbmodel->insertQuery('tcTimelogs', $insLog);
					
					if($_POST['logtype']=='Z') $this->cronDailyLogs(); ///CALL TO INSERT LOG
					exit;
				}
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
				}
			}
				
			//this is for logs for the calendar month			
			$dateLogs = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, empID_fk, slogDate, DAY(slogDate) AS dayLogDate, schedIn, schedOut, timeIn, timeOut, breaks, timeBreak, numBreak, offsetIn, offsetOut, publishBy, publishTimePaid, leaveID_fk, status', 'empID_fk="'.$data['visitID'].'" AND slogDate BETWEEN "'.$dateMonthToday.'-01" AND "'.$dateMonthToday.'-31"');
		
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
					if(isset($d['offset'])) $want .= '<a href="'.$this->config->base_url().'staffleaves/'.$d['leaveID'].'/" class="iframe tanone"><div class="daysbox dayoffset">Offset<br/>'.$d['offset'].'</div></a>';
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
			$str5daysbefore = strtotime($data['currentDate'].' -5 days'); //strtotime 7 days before today
			foreach($data['dayArr'] AS $h=>$m){
				if($checkIfUser && isset($displayArray[$h]['status']) && $displayArray[$h]['status']==0){					
					$data['dayEditOptionArr'][$h][] = array('link'=>$this->config->base_url().'timecard/requestupdate/?d='.$dateMonthToday.'-'.$h, 'text'=>'Request Update');
				}					
				
				$data['dayEditOptionArr'][$h][] = array('link'=>$this->config->base_url().'timecard/'.$data['visitID'].'/viewlogdetails/?d='.$dateMonthToday.'-'.$h, 'text'=>'View Details');
			}
			
			
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
		}
	
		$this->load->view('includes/template', $data);
	}
	
	
	public function attendance($data){
		$data['content'] = 'v_timecard/v_attendance';	
		$data['tpage'] = 'attendance';
		
		if(is_numeric($this->uri->segment(2))==true || is_numeric($this->uri->segment(3))==true){
			header('Location:'.$this->config->base_url().'timecard/attendance/'.((isset($_GET['d']))?'?d='.$_GET['d']:''));
			exit;
		}
	
		if($this->user!=false){
			$condition = '';
			if($this->access->accessFullHR==false){ //CHECK STAFF UNDER LOGGED IN
				$ids = ''; //empty value for staffs with no under yet
				$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);						
				foreach($myStaff AS $m):
					$ids .= $m->empID.',';
				endforeach;
				if(!empty($ids))
					$condition = ' AND empID IN ('.$ids.$this->user->empID.')';
			}
			
			if($data['currentDate']==$data['today']){
				$contento = '';
				$cntLate = count($this->timeM->getNumDetailsAttendance($data['today'], 'late', $condition));
				$cntAbsent = count($this->timeM->getNumDetailsAttendance($data['today'], 'absent', $condition));			
				$cntInProgress = count($this->timeM->getNumDetailsAttendance($data['today'], 'shiftinprogress', $condition));
				$cntEarlyBird = count($this->timeM->getNumDetailsAttendance($data['today'], 'earlyBird', $condition));
				$cntOverBreak = count($this->timeM->getNumDetailsAttendance($data['today'], 'overbreak', $condition));
				$cntEarlyClockOut = count($this->timeM->getNumDetailsAttendance($data['today'], 'earlyclockout', $condition));			
				$cntNoClockOut = count($this->timeM->getNumDetailsAttendance($data['today'], 'noclockout', $condition));			
				$cntLeave = count($this->timeM->getNumDetailsAttendance($data['today'], 'leave', $condition));
				$cntOffset = count($this->timeM->getNumDetailsAttendance($data['today'], 'offset', $condition));
				$cntPublished = count($this->timeM->getNumDetailsAttendance($data['today'], 'published', $condition));
				
				if($cntLate>0) $contento .= '<b class="errortext">Late: '.$cntLate.'</b><br/>';
				if($cntAbsent>0) $contento .= '<b class="errortext">Absent: '.$cntAbsent.'</b><br/>';
				if($cntOverBreak>0) $contento .= '<b class="errortext">Over Break: '.$cntOverBreak.'</b><br/>';
				if($cntEarlyClockOut>0) $contento .= '<b class="errortext">Early Clock Out: '.$cntEarlyClockOut.'</b><br/>';
				if($cntNoClockOut>0) $contento .= '<b class="errortext">No Clock Out: '.$cntNoClockOut.'</b><br/>';
				if($cntInProgress>0) $contento .= '<b style="color:green;">Shift In Progress: '.$cntInProgress.'</b><br/>';
				if($cntEarlyBird>0) $contento .= '<b style="color:blue;">Early Birds: '.$cntEarlyBird.'</b><br/>';
				if($cntLeave>0) $contento .= '<b style="color:blue;">On Leave: '.$cntLeave.'</b><br/>';
				if($cntOffset>0) $contento .= '<b style="color:blue;">On Offset: '.$cntOffset.'</b><br/>';
				if($cntPublished>0) $contento .= '<b>Published: '.$cntPublished.'</b><br/>';
				
				$numDate = date('j', strtotime($data['today']));
				$data['dayArr'][$numDate] =  '<div class="taleft padding5px">'.$contento.'</div>';
			}
		
			$attHistory = $this->dbmodel->getQueryResults('tcAttendance', '*', 'dateToday LIKE "'.date('Y-m-', strtotime($data['today'])).'%"');
			
			$strToday = strtotime(date('Y-m-d', strtotime($data['currentDate'])));			
			foreach($attHistory AS $his){
				if($his->scheduled!=0){
					$hisNum = date('j', strtotime($his->dateToday));
					$data['dayEditOptionArr'][$hisNum][] = array('link'=>$this->config->base_url().'timecard/attendancedetails/?d='.$his->dateToday, 'text'=>'View Details');
						
					$hisText = '';					
					if(strtotime($his->dateToday)<$strToday){
						if($his->unpublished>0){
							if($this->access->accessFullHR==true) $hisText .= '<b style="color:red;">UNPUBLISHED: '.$his->unpublished.'</b><br/>';
							else{ $cntUnpublished = count($this->timeM->getNumDetailsAttendance($his->dateToday, 'unpublished', $condition));
								if($cntUnpublished>0) $hisText .= '<b style="color:red;">UNPUBLISHED: '.$cntUnpublished.'</b><br/>'; }
						}
						
						if($his->absent>0){
							if($this->access->accessFullHR==true) $hisText .= '<b style="color:#888;">Absent: '.$his->absent.'</b><br/>';
							else{ $cntAbsent = count($this->timeM->getNumDetailsAttendance($his->dateToday, 'absent', $condition));
								if($cntAbsent>0) $hisText .= '<b style="color:#888;">Absent: '.$cntAbsent.'</b><br/>'; }
						}
						
						if($his->overBreak>0){
							if($this->access->accessFullHR==true) $hisText .= '<b style="color:#888;">Overbreak: '.$his->overBreak.'</b><br/>';
							else{ $cntOverbreak = count($this->timeM->getNumDetailsAttendance($his->dateToday, 'overbreak', $condition));
								if($cntOverbreak>0) $hisText .= '<b style="color:#888;">Overbreak: '.$cntOverbreak.'</b><br/>'; }
						}
						
						if($his->late>0){
							if($this->access->accessFullHR==true) $hisText .= '<b style="color:#888;">Late: '.$his->late.'</b><br/>';
							else{ $cntLate = count($this->timeM->getNumDetailsAttendance($his->dateToday, 'late', $condition));
								if($cntLate>0) $hisText .= '<b style="color:#888;">Late: '.$cntLate.'</b><br/>'; }
						}
						
						if($his->earlyOut>0){
							if($this->access->accessFullHR==true) $hisText .= '<b style="color:#888;">Early Out: '.$his->earlyOut.'</b><br/>';
							else{ $cntEarlyOut = count($this->timeM->getNumDetailsAttendance($his->dateToday, 'earlyclockout', $condition));
								if($cntEarlyOut>0) $hisText .= '<b style="color:#888;">Early Out: '.$cntEarlyOut.'</b><br/>'; }
						}
						
						if($his->earlyIn>0){
							if($this->access->accessFullHR==true) $hisText .= '<b style="color:#888;">Early In: '.$his->earlyIn.'</b><br/>';
							else{ $cntEarlyIn = count($this->timeM->getNumDetailsAttendance($his->dateToday, 'earlyBird', $condition));
								if($cntEarlyIn>0) $hisText .= '<b style="color:#888;">Early In: '.$cntEarlyIn.'</b><br/>'; }
						}
						
						if($his->missingClockOut>0){
							if($this->access->accessFullHR==true) $hisText .= '<b style="color:#888;">Missing Out: '.$his->missingClockOut.'</b><br/>';
							else{ $cntMissingOut = count($this->timeM->getNumDetailsAttendance($his->dateToday, 'noclockout', $condition));
								if($cntMissingOut>0) $hisText .= '<b style="color:#888;">Missing Out: '.$cntMissingOut.'</b><br/>'; }
						}
						
						if($his->published>0){
							if($this->access->accessFullHR==true) $hisText .= '<b style="color:#888;">Published: '.$his->published.'</b><br/>';
							else{ $cntPublished = count($this->timeM->getNumDetailsAttendance($his->dateToday, 'noclockout', $condition));
								if($cntPublished>0) $hisText .= '<b style="color:#888;">Published: '.$cntPublished.'</b><br/>'; }
						}
					}
					
					if(!empty($hisText)){
						$data['dayArr'][$hisNum] =  '<div class="taleft padding5px">'.$hisText.'</div>';
					}
				}
			}
						
			$jnum = date('j', strtotime($data['today']));
			if(!isset($data['dayEditOptionArr'][$jnum])){
				$data['dayEditOptionArr'][$jnum][] = array('link'=>$this->config->base_url().'timecard/attendancedetails/?d='.$data['today'], 'text'=>'View Details');
			}
					
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function calendar($data){
		$data['content'] = 'v_timecard/v_calendar';	
		$data['tpage'] = 'calendar';		
		
		if($this->user!=false){
			$data['dayArr'] = array();
			$id = $this->uri->segment(2);
			if(is_numeric($id) && $this->commonM->checkStaffUnderMe($id)==false){
				header('Location:'.$this->config->base_url().'timecard/calendar/'.((isset($_GET['d']))?'?d='.$_GET['d']:''));
				exit;
			}
		
			$dateStart = date('Y-m-01', strtotime($data['today']));
			$dateEnd = date('Y-m-t', strtotime($data['today']));
			
			//for schedule history
			$data['timeArr'] = $this->commonM->getSchedTimeArray();			
			$data['schedData'] = $this->dbmodel->getQueryResults('tcStaffSchedules', 'schedID, empID_fk, tcCustomSched_fk, effectivestart, effectiveend, schedName, sunday, monday, tuesday, wednesday, thursday, friday, saturday, workhome', 'empID_fk="'.$data['visitID'].'"', 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk'); 
									
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
						$sched .= '<a href="'.$this->config->base_url().'staffleaves/'.$yoyo['leaveID'].'/" class="iframe tanone"><div class="daysbox dayoffset">Offset<br/>'.$yoyo['offset'].'</div></a>';
					}
					
					if(isset($yoyo['pendingoffset'])){
						$sched .= '<a href="'.$this->config->base_url().'staffleaves/'.$yoyo['leaveID'].'/" class="iframe tanone"><div class="daysbox daypendingleave">Pending Offset<br/>'.$yoyo['pendingoffset'].'</div></a>';
					}
				}			
								
				if(!empty($sched) && isset($yoyo['schedDate']) && $yoyo['schedDate']<=$dateEnd && $yoyo['schedDate']>=$dateStart)
					$data['dayArr'][$k] = $sched;	
			}	
		}	
		
		$this->load->view('includes/template', $data);
	}
	
	public function scheduling($data){	
		$data['content'] = 'v_timecard/v_scheduling';
		$data['tpage'] = 'scheduling';	
		$data['column'] = 'noLeft';
			
		if($this->user!=false){	
			$segment2 = $this->uri->segment(2);
			if(is_numeric($segment2)){
				header('Location:'.$this->config->base_url().'timecard/scheduling/');
				exit;
			}
		
			if($this->access->accessFullHR==false){
				$data['access'] = false;
			}else{					
				//get start and end of the week			
				$dTo = $data['today'];
				if(isset($_GET['startweek'])){
					$dTo = $_GET['startweek'];
				}
				
				$start_week = strtotime("last sunday midnight",strtotime($dTo));
				$end_week = strtotime("next saturday",strtotime($dTo));
				
				$data['start'] = date("Y-m-d",$start_week); 
				$data['end'] = date("Y-m-d",$end_week);	
				
				$data['prev'] = date('Y-m-d', strtotime($dTo .' -1 week'));
				$data['next'] = date('Y-m-d', strtotime($dTo .' +1 week'));
				
				$data['sunday'] = $data['start'];
				$data['monday'] = date('Y-m-d', strtotime($data['start'].' +1 day'));		
				$data['tuesday'] = date('Y-m-d', strtotime($data['start'].' +2 day'));		
				$data['wednesday'] = date('Y-m-d', strtotime($data['start'].' +3 day'));		
				$data['thursday'] = date('Y-m-d', strtotime($data['start'].' +4 day'));		
				$data['friday'] = date('Y-m-d', strtotime($data['start'].' +5 day'));		
				$data['saturday'] = date('Y-m-d', strtotime($data['start'].' +6 day'));
					
				$empArr = array();
				$schedArr = array();
				$data['allStaffs'] = $this->dbmodel->getQueryResults('staffs', 'empID, fname, lname, CONCAT(fname," ",lname) AS name', 'active=1', '', 'lname');
				
				foreach($data['allStaffs'] AS $staffs){
					$empArr[] = $staffs->empID;
				}			
												
				foreach($empArr AS $emp){
					$query = $this->timeM->getCalendarSchedule($data['start'], $data['end'], $emp, true);				
					if(!empty($query)){
						foreach($query AS $q){						
							$qtext = '';
							if(isset($q['leave'])) $qtext .= '<span class="errortext"><b>On Leave</b> ('.$q['leave'].')</span><br/>'; 						
							if(isset($q['sched']) && $q['sched']!='On Leave') $qtext .= $q['sched'].'<br/>';  
							if(isset($q['offset'])) $qtext .= '<span class="colorgreen"><b>Offset:</b> '.$q['offset'].'</span><br/>';  
								
							if(!empty($qtext))
								$schedArr[$emp][$q['schedDate']] = $qtext;
						}						
					}							
				}
				
				$data['schedData'] = $schedArr;							
			}
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function payslips($data){		
		$data['content'] = 'v_timecard/v_payslips';	
		$data['tpage'] = 'payslips';
		
		if($this->user!=false){
			//if($this->access->accessFullHRFinance==false) $condition = ' AND tcPayrolls.status>0';
			//else $condition = '';
			
			$data['dataPayslips'] = $this->dbmodel->getQueryResults('tcPayslips', 'payslipID, payPeriodStart, payPeriodEnd, empID_fk, tcPayrolls.status', 'empID_fk="'.$data['visitID'].'" AND pstatus=1', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk', 'payDate DESC');
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function payrolls($data){
		header('Location:'.$this->config->base_url().'timecard/managepayroll/');
		exit;
		/* $data['content'] = 'v_timecard/v_payrolls';		
		$data['tpage'] = 'payrolls';
	
		if($this->user!=false){	
			
			
			
		}
	
		$this->load->view('includes/template', $data); */
	}
	
	public function reports($data){		
		$data['content'] = 'v_timecard/v_reports';		
		$data['tpage'] = 'reports';
		$data['column'] = 'withLeft';
		unset($data['timecardpage']); //unset timecardpage value so that timecard header will not show
		
		if($this->user!=false){	
			
			
		}
		
		$this->load->view('includes/template', $data);
	}
	
	
	function requestupdate($data){
		$data['content'] = 'v_timecard/v_requestupdate';
		
		if(!empty($_POST)){			
			$docs = '';
			$dir = 'uploads/timecard/timeloguploaddocs/';
			$id = $this->user->empID;			
			
			$dfiles = $_FILES['docs'];
			for($m=0; $m<3; $m++){
				if(!empty($dfiles['name'][$m])){
					$fname = $id.'_doc_0'.$m.'_'.date('ymdHis').'.'.$this->textM->getFileExtn($dfiles['name'][$m]);
					$docs .= $fname.'|';
					
					move_uploaded_file($dfiles['tmp_name'][$m], $dir.$fname);	
				}
			}
			
			$ins = $_POST;
			$ins['message'] = addslashes($_POST['message']);
			$ins['empID_fk'] = $this->user->empID;
			$ins['docs'] = rtrim($docs, '|');
			$ins['type'] = 'request';
			$ins['dateRequested'] = date('Y-m-d H:i:s');
			
			$this->dbmodel->insertQuery('tcTimelogUpdates', $ins);	
			$data['requested'] = true;	
		}
			
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
	
	public function viewlogdetails($data){
		$data['content'] = 'v_timecard/v_viewlogdetails';
		$id = $data['visitID'];
		
		$data['dir'] = 'uploads/timecard/timeloguploaddocs/';
			
		if(!empty($_POST)){
			if($_POST['submitType']=='updateReq'){				
				$upArr['status'] = $_POST['status'];
				$upArr['updateNote'] = $_POST['updateNote'];
				$upArr['updatedBy'] = $this->user->username;
				$upArr['dateUpdated'] = date('Y-m-d H:i:s');
				$this->dbmodel->updateQuery('tcTimelogUpdates', array('updateID'=>$_POST['updateID']), $upArr);
				header('Location:'.$this->config->item('career_uri'));
				exit;
			}else if($_POST['submitType']=='resolvelog'){
				$message = '';
				$updatePub = array();				
				if($_POST['changetype']=='breaks'){
					$br = '';
					$numb = 0;
					$totalBreak = 0;
					$compute = 0;
										
					foreach($_POST['breakval'] AS $bb){
						if(!empty($bb)){
							$br .= $bb.'|';
							
							if($compute==0){
								$compute = strtotime($bb);
								$message .= ', ';
							}else{
								$totalBreak += strtotime($bb) - $compute;
								$compute = 0;
								$message .= ' - ';
							}
							
							$message .= date('h:i a', strtotime($bb));
							
							$numb++;
						}
					}
					$updatePub['timeBreak'] = $this->textM->convertTimeToMinHours($totalBreak,true);
					$updatePub['numBreak'] = $numb;
					$updatePub['breaks'] = $br;
					
					if($numb>0){
						$message = 'Updated break to: '.trim($message, ', ');
						
						$prevVal = $this->dbmodel->getSingleField('tcStaffLogPublish', 'breaks', 'slogID="'.$_POST['slogID'].'"');
						if(empty($prevVal)) $message .= ' from none';
						else{
							$message .= ' from ';
							$pval = explode('|', $prevVal);
							foreach($pval AS $p) $message .= date('h:i a', strtotime($p)).', ';
							
							$message = rtrim($message, ', ');
						}
					} 
				}else{
					$updatePub[$_POST['changetype']] = $_POST['inoutval'];
					$message = 'Updated '.$this->textM->constantText($_POST['changetype']).' to '.date('h:i a', strtotime($_POST['inoutval']));
					
					$prevVal = $this->dbmodel->getSingleField('tcStaffLogPublish', $_POST['changetype'], 'slogID="'.$_POST['slogID'].'"');
					if(!empty($prevVal)){
						if($prevVal=='0000-00-00 00:00:00') $message .= ' from none';
						else $message .= ' from '.date('h:i a', strtotime($prevVal));
					} 
				}
				
				if(!empty($updatePub)){
					if(!empty($_POST['slogID'])){
						$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$_POST['slogID']), $updatePub); ///update records
						
						//check if there is pending request
						if(!empty($_POST['updateID'])){
							$upA['status'] = 0;
							$upA['updatedBy'] = $this->user->username;
							$upA['dateUpdated'] = date('Y-m-d H:i:s');
							$this->dbmodel->updateQuery('tcTimelogUpdates', array('updateID'=>$_POST['updateID']), $upA);
						}
						
						//insert to tcTimelogUpdates
						$this->timeM->addToLogUpdate($id, $data['today'], $message.'<br/>Reason: '.$_POST['reason']);						
					}					
				}
				$data['alerttext'] = 'Please don\'t forget to PUBLISH.';
			}else if($_POST['submitType']=='publishlog'){
				$pubArr['publishTimePaid'] = $_POST['publishTimePaid'];
				$pubArr['publishND'] = $_POST['publishND'];
				
				if(isset($_POST['publishHO'])) $pubArr['publishHO'] = $_POST['publishHO'];
				if(isset($_POST['publishHOND'])) $pubArr['publishHOND'] = $_POST['publishHOND'];
				if(isset($_POST['publishOT'])) $pubArr['publishOT'] = $_POST['publishOT'];
				
				$pubArr['publishDeduct'] = $_POST['publishDeduct'];
				$pubArr['publishNote'] = $_POST['publishNote'];
				$pubArr['datePublished'] = date('Y-m-d H:i:s');
				$pubArr['publishBy'] = $this->user->username;
				$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$_POST['slogID']), $pubArr);
				
				//insert to tcTimelogUpdates
				$this->timeM->addToLogUpdate($id, $data['today'], '<b>Published. Time Paid: '.$pubArr['publishTimePaid'].' Hours</b>');				
			}else if($_POST['submitType']=='unpublish'){
				//insert to tcTimelogUpdates
				$info = $this->dbmodel->getSingleInfo('tcStaffLogPublish', 'publishTimePaid, datePublished, publishBy', 'slogID="'.$_POST['slogID'].'"');	
				$message = '<b>Unpublished log.</b>';
				if(count($info)>0){
					$message .= '<br/>Details:<br/>Time Paid: '.$info->publishTimePaid;
					$message .= '<br/>Prev Date Published: '.$info->datePublished;
					$message .= '<br/>Prev Published By: '.$info->publishBy;
				}				
				$this->timeM->addToLogUpdate($id, $data['today'], $message);
				
				//remove publish details
				$removePub['publishTimePaid'] = 0;
				$removePub['publishDeduct'] = 0;
				$removePub['publishND'] = 0;
				$removePub['datePublished'] = '0000-00-00 00:00:00';
				$removePub['publishBy'] = '';
				$removePub['publishNote'] = '';
				
				$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$_POST['slogID']), $removePub); ///REMOVE PUBLISH DETAILS
				$this->timeM->cntUpdateAttendanceRecord($data['today']); //UPDATE ATTENDANCE RECORDS
				exit;
			}else if($_POST['submitType']=='doneChanging'){
				$upA['status'] = 0;
				$upA['updatedBy'] = $this->user->username;
				$upA['dateUpdated'] = date('Y-m-d H:i:s');
				$this->dbmodel->updateQuery('tcTimelogUpdates', array('updateID'=>$_POST['updateID']), $upA);
				$this->dbmodel->updateConcat('tcTimelogUpdates', 'updateID="'.$_POST['updateID'].'"', 'message', '<br/><i style="font-size:11px;"><u>Change status to DONE - by '.$this->user->username.'</u></i><br/>');
			}
			
			$this->timeM->cntUpdateAttendanceRecord($data['today']); //UPDATE ATTENDANCE RECORDS
		}
		
		$data['schedToday'] = $this->timeM->getSchedToday($id, $data['today']);
		$data['dataLog'] = $this->dbmodel->getSingleInfo('tcStaffLogPublish', '*', 'empID_fk="'.$id.'" AND slogDate="'.$data['today'].'"');
		$data['dataBiometrics'] = $this->timeM->getLogsToday($data['visitID'], $data['today'], $data['schedToday']);
		$data['updateRequests'] = $this->dbmodel->getQueryResults('tcTimelogUpdates', '*', 'empID_fk="'.$id.'" AND logDate="'.$data['today'].'"', '', 'dateRequested DESC');
		
		$data['isUnder'] = $this->commonM->checkStaffUnderMe($data['row']->username);
		$data['staffHoliday'] = $this->dbmodel->getSingleField('staffs', 'staffHolidaySched', 'empID="'.$id.'"');
		$data['logtypeArr'] = $this->textM->constantArr('timeLogType');
		$this->load->view('includes/templatecolorbox', $data);		
	}
	
	public function attendancedetails($data){
		$data['content'] = 'v_timecard/v_attendancedetails';
		
		$condition = '';
		if($this->access->accessFullHR==false){ //CHECK STAFF UNDER LOGGED IN
			$ids = ''; //empty value for staffs with no under yet
			$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);						
			foreach($myStaff AS $m):
				$ids .= $m->empID.',';
			endforeach;
			if(!empty($ids))
				$condition = ' AND empID IN ('.$ids.$this->user->empID.')';
		}
		
		$data['queryUnscheduled'] = $this->timeM->getNumDetailsAttendance($data['today'], 'unscheduled', $condition);
		$data['queryLate'] = $this->timeM->getNumDetailsAttendance($data['today'], 'late', $condition);
		$data['queryAbsent'] = $this->timeM->getNumDetailsAttendance($data['today'], 'absent', $condition);
		$data['queryLeave'] = $this->timeM->getNumDetailsAttendance($data['today'], 'leave', $condition);
		$data['queryOffset'] = $this->timeM->getNumDetailsAttendance($data['today'], 'offset', $condition);
		$data['queryInProgress'] = $this->timeM->getNumDetailsAttendance($data['today'], 'shiftinprogress', $condition);
		$data['queryEarlyBird'] = $this->timeM->getNumDetailsAttendance($data['today'], 'earlyBird', $condition);
		$data['queryEarlyClockOut'] = $this->timeM->getNumDetailsAttendance($data['today'], 'earlyclockout', $condition);
		$data['queryNoClockIn'] = $this->timeM->getNumDetailsAttendance($data['today'], 'noclockin', $condition);
		$data['queryNoClockOut'] = $this->timeM->getNumDetailsAttendance($data['today'], 'noclockout', $condition);
		$data['queryOverBreak'] = $this->timeM->getNumDetailsAttendance($data['today'], 'overbreak', $condition);
		$data['queryUnPublished'] = $this->timeM->getNumDetailsAttendance($data['today'], 'unpublished', $condition);
				
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function managepayroll($data){
		$data['content'] = 'v_timecard/v_manage_payroll';
		$data['tpage'] = 'managepayroll';
		$data['column'] = 'withLeft';
		unset($data['timecardpage']); //unset timecardpage value so that timecard header will not show
	
		if($this->user!=false){
			if($this->access->accessFullFinance==false) $data['access'] = false;
			else{
				if(isset($_POST) && !empty($_POST)){
					if($_POST['submitType']=='updateMinWage'){
						$this->dbmodel->updateQueryText('staffSettings', 'settingVal="'.$_POST['settingVal'].'"', 'settingName="minimumWage"');
						$this->commonM->addMyNotif($this->user->empID, 'Updated minimum wage to Php '.$this->textM->convertNumFormat($_POST['settingVal']).'.', 5);
					}else if($_POST['submitType']=='removePayroll'){
						$this->dbmodel->updateQueryText('tcPayrolls', 'status=3', 'payrollsID='.$_POST['payrollID']);
						$this->payrollM->staffLogStatus($_POST['payrollID']);
						exit;
					}
				}
				
				$data['pagepayroll'] = $this->uri->segment(3);
				if(empty($data['pagepayroll'])){
					$data['pagepayroll'] = 'managepayroll';
					$data['pagePayTitle'] = 'Manage Payroll';
					
					$condition = '';
					if(!isset($_GET['show']) || $_GET['show']=='active') $condition = ' AND staffs.active=1';
					else{
						$show = $_GET['show'];
						if($show=='pending') $condition = ' AND endDate>="'.$data['currentDate'].'"';
						else if($show=='suspended'){
							$empArr = array();
							$querNTE = $this->dbmodel->getQueryResults('staffNTE', 'empID_fk', 'status=0 AND suspensiondates LIKE "%'.$data['currentDate'].'%"');
							if(count($querNTE) > 0){
								foreach($querNTE AS $o)
									$empArr[] = $o->empID_fk;
							}
							
							if(count($empArr)>0) $condition = ' AND empID IN ('.implode(',', $empArr).')';
							else $condition = ' AND empID=0';						
						}
						else if($show=='separated') $condition = ' AND staffs.active=0';
					}
					
					if($this->config->item('timeCardTest')==true){
						$condition .= ' AND empID IN ('.implode(',', $this->commonM->getTestUsers()).') ';
					}
					$data['dataStaffs'] = $this->dbmodel->getQueryResults('staffs', 'empID, lname, fname, username, title, dept, staffHolidaySched', 'office="PH-Cebu" '.$condition, 'LEFT JOIN newPositions ON posID=position', 'lname');					
				}else if($data['pagepayroll']=='previouspayroll'){
					$data['pagePayTitle'] = 'Previous Payroll';
					
					$data['payrollStatusArr'] = $this->textM->constantArr('payrollStatusArr');
					$data['dataPayrolls'] = $this->dbmodel->getQueryResults('tcPayrolls', '*', 'status!=3 AND numGenerated>0', '', 'payPeriodEnd DESC');
				}else if($data['pagepayroll']=='payrollitems'){
					$data['pagePayTitle'] = 'Payroll Items';
					
					$data['payrollItemType'] = $this->textM->constantArr('payrollItemType');
					$data['dataMainItems'] = $this->dbmodel->getQueryResults('tcPayslipItems', '*, 1 AS isMain, payAmount AS prevAmount', 'mainItem=1', '', 'payCategory, payName');				
					$data['dataAddItems'] = $this->dbmodel->getQueryResults('tcPayslipItems', '*, 1 AS isMain, payAmount AS prevAmount', 'mainItem=0', '', 'payCategory, payName');	
				}else if($data['pagepayroll']=='payrollsettings'){
					$data['pagePayTitle'] = 'Payroll Settings';
					
					$data['dataMinWage'] = $this->dbmodel->getSingleField('staffSettings', 'settingVal', 'settingName="minimumWage"');
				}
			}
		}
		
		$this->load->view('includes/template', $data);		
	}
	
	
	public function payrollmanagement(){
		if(empty($_POST)){
			header('Location: '.$this->config->base_url().'timecard/managepayroll/');
			exit;
		}else{
			if($this->access->accessFullHRFinance==false) $data['access'] = false;
			
			if(isset($_POST['type']) && $_POST['type']=='generatepayslip') $_POST['submitType'] = 'generatepayslip';
			
			if(isset($_POST['periodDate'])){
				list($_POST['start'], $_POST['end']) = explode('|', $_POST['periodDate']);
			}
				
			if(isset($_POST['submitType'])){
				if($_POST['submitType']=='save' || $_POST['submitType']=='saveandgenerate'){				
					foreach($_POST['log'] AS $id=>$log)
						$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$id), $log);
					
					if($_POST['submitType']=='saveandgenerate'){						
						$_POST['submitType'] = 'generatepayslip';
					}
				}else if($_POST['submitType'] == 'changeHoliday'){
					$this->dbmodel->updateQueryText('tcAttendance', 'holidayType="'.$_POST['holidayType'].'"', 'dateToday="'.$_POST['dtoday'].'"');
					if($_POST['holidayType']==0)
						$this->dbmodel->updateQueryText('tcStaffLogPublish', 'publishHO="", publishHOND=""', 'slogDate="'.$_POST['dtoday'].'" OR slogDate="'.date('Y-m-d', strtotime($_POST['dtoday'].' -1 day')).'"');
				}

				if($_POST['submitType'] == 'generatepayslip'){
					///THIS IS FOR PAYROLL GENERATION
					$genpayArr['empIDs'] =  ((is_array($_POST['empIDs']))?implode(',', $_POST['empIDs']):$_POST['empIDs']);
					$genpayArr['dateStart'] =  date('Y-m-d', strtotime($_POST['start']));
					$genpayArr['dateEnd'] =  date('Y-m-d', strtotime($_POST['end']));
					
					$payrollInfo = $this->dbmodel->getSingleInfo('tcPayrolls', 'payrollsID, payType', 'payPeriodStart="'.$genpayArr['dateStart'].'" AND payPeriodEnd="'.$genpayArr['dateEnd'].'" AND status!=3');
										
					if(count($payrollInfo)>0){
						$genpayArr['payrollsID'] = $payrollInfo->payrollsID;
						$genpayArr['payType'] = $payrollInfo->payType;
					}else{
						$genpayArr['payType'] = $_POST['computationtype'];
						if($genpayArr['payType']=='semi') $genpayArr['payDate'] = date('Y-m-15', strtotime($genpayArr['dateEnd']));
						else  $genpayArr['payDate'] = date('Y-m-t', strtotime($genpayArr['dateEnd']));
						$genpayArr['payrollsID'] = $this->dbmodel->insertQuery('tcPayrolls', array('payPeriodStart'=>$genpayArr['dateStart'], 'payPeriodEnd'=>$genpayArr['dateEnd'], 'payType'=>$_POST['computationtype'], 'payDate'=>$genpayArr['payDate']));							
					}
					$this->payrollM->generatepayroll($genpayArr);
					header('Location:'.$this->config->base_url().'timecard/managepayrolldetail/'.$genpayArr['payrollsID'].'/');
					exit;
				}				
			}
			
			$data['content'] = 'v_timecard/v_payroll_management';
			$data['showtemplatefull'] = true;
			$data['managePayOptionsArr'] = $this->textM->constantArr('managePayOptions');
			
			$data['type'] = $_POST['type'];
			$data['computationtype'] = $_POST['computationtype'];
			$data['start'] = date('Y-m-d', strtotime($_POST['start']));
			$data['end'] = date('Y-m-d', strtotime($_POST['end']));
			$data['empIDs'] = rtrim($_POST['empIDs'], ',');
			
			$data['dataAttendance'] = array();	
			$dataEmps = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(lname, ", ",fname) AS name, staffHolidaySched', 'empID IN ('.$data['empIDs'].')', '', 'lname');
			foreach($dataEmps AS $emp){
				$data['dataAttendance'][$emp->empID]['name'] = $emp->name;
				$data['dataAttendance'][$emp->empID]['staffHolidaySched'] = $emp->staffHolidaySched;
			}
			
			$dataLogs = $this->dbmodel->getQueryResults('tcStaffLogPublish', 
				'slogID, slogDate, empID_fk, schedHour, timeIn, timeOut, schedIn, schedOut, publishTimePaid, publishND, publishHO, publishHOND, publishDeduct, publishBy, status', 
				'slogDate BETWEEN "'.$data['start'].'" AND "'.$data['end'].'" AND empID_fk IN ('.$data['empIDs'].')');	
			
			foreach($dataLogs AS $d){
				$data['dataAttendance'][$d->empID_fk]['dates'][$d->slogDate] = $d;
			}
			
			$data['dataDates'] = array();
			for($d=$data['start']; $d<=$data['end']; ){
				$data['dataDates'][$d] = '';
				$d = date('Y-m-d', strtotime($d.' +1 day'));
			}					
			$dataDates = $this->dbmodel->getQueryResults('tcAttendance', 'attendanceID, dateToday, holidayType', 'dateToday BETWEEN "'.$data['start'].'" AND "'.$data['end'].'"', '', 'dateToday');
			foreach($dataDates AS $d){
				$data['dataDates'][$d->dateToday] = $d;
			}			
			
			$this->load->view('includes/template', $data);
		}		
	}
	
	
	public function generatepayroll(){
		$data['content'] = 'v_timecard/v_payroll_generate';
		
		if($this->access->accessFullHRFinance==false) $data['access'] = false;
		
		if(isset($_POST['submitType'])){
			if($_POST['submitType']=='showEmp'){
				/* $us = '<table class="tableInfo"';
					$us .= '<tr></tr>';
				$us .= '</table>';
				$query = $this->dbmodel->getQueryResults('staffs', 'CONCAT(lname,", ",fname) AS name, empID', 'empID IN ('.$_POST['empIDs'].')', '', 'lname');
				foreach($query AS $q){
					$us .= $this->textM->formfield('checkbox', 'employee[]', $q->empID, 'checkMe').' '.$q->name.'<br/>';
				}
				echo $us; */
				exit;
			}else if($_POST['submitType']=='generatepayroll'){
				echo '<pre>';
				print_r($_POST);
				echo '</pre>';
				exit;
			}
		}
		
		$data['dataStaffs'] = $this->dbmodel->getQueryResults('staffs', 'CONCAT(lname,", ",fname) AS name, empID, title, dept', '1', 'LEFT JOIN newPositions ON posID=position', 'lname');
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
	public function allpayrolls(){
		$data['content'] = 'v_timecard/v_payroll_all';
		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false) $data['access'] = false;
			else{
				$id = $this->uri->segment(3);
				if(is_numeric($id)){
					$data['dataInfo'] = $this->dbmodel->getSingleInfo('tcPayrolls', '*', 'payrollsID="'.$id.'"');
					$data['dataPayslips'] = $this->dbmodel->getQueryResults('tcPayslips', 'payslipID, earning, net, CONCAT(lname,", ",fname) AS name', 
							'payrollsID_fk="'.$data['dataInfo']->payrollsID.'" AND pstatus=1',
							'LEFT JOIN staffs ON empID=empID_fk');
				}
			}			
		}
		
		$this->load->view('includes/template', $data);
	}
	
	/* public function addpaymentitems(){
		$data['content'] = 'v_timecard/v_manange_ addpaymentitems';
		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false) $data['access'] = false;
			
			if(isset($_POST['submitType'])){
				if($_POST['submitType']=='addItem' || $_POST['submitType']=='updateItem'){
					$upArr = $_POST;
									
					if($upArr['payAmount']=='specific amount') $upArr['payAmount'] = $upArr['inputPayAmount'];
					if(!empty($upArr['payStart'])) $upArr['payStart'] = date('Y-m-d', strtotime($upArr['payStart']));
					if(!empty($upArr['payEnd'])) $upArr['payEnd'] = date('Y-m-d', strtotime($upArr['payEnd']));
					if($upArr['payPeriod']=='once' && !empty($upArr['payStartOnce'])){
						$upArr['payStart'] = date('Y-m-d', strtotime($upArr['payStartOnce']));
						$upArr['payEnd'] = $upArr['payStart'];
					}
					
					unset($upArr['submitType']);
					unset($upArr['payID']);
					unset($upArr['inputPayAmount']);
					unset($upArr['payStartOnce']);
							
					if($_POST['submitType']=='addItem'){					
						$payID = $this->dbmodel->insertQuery('tcPayslipItems', $upArr);
					}else{
						$payID = $_POST['payID'];
						$this->dbmodel->updateQuery('tcPayslipItems', array('payID'=>$_POST['payID']), $upArr);
					}
					
					header('Location:'.$this->config->base_url().'timecard/addpaymentitems/?payID='.$payID);
					exit;
				}
			}
			
			if(isset($_GET['payID'])){
				$data['pageType'] = 'viewItem';
				$data['dataItemInfo'] = $this->dbmodel->getSingleInfo('tcPayslipItems', '*', 'payID="'.$_GET['payID'].'"');		
			}else{
				$data['pageType'] = 'addItem';
				
				$query = $this->dbmodel->dbQuery('SELECT COLUMN_NAME, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME="tcPayslipItems"');
				foreach($query->result() AS $q){
					$data['dataItemInfo'][$q->COLUMN_NAME] = '';
				} 
				$data['dataItemInfo'] = (object) $data['dataItemInfo'];				
			}			
		}		
		
		$this->load->view('includes/templatecolorbox', $data);
	} */
	
	public function manangepaymentitem($data){
		$data['content'] = 'v_timecard/v_manange_paymentitems';
		
		if($this->user!=false){
			if(isset($_GET['pageType']) && $_GET['pageType']=='empUpdate' && !isset($_GET['payID']) && !isset($_GET['staffPayID'])){
				header('Location:'.$this->config->base_url().'timecard/'.$data['visitID'].'/mypayrollsetting/');
				exit;
			}
			
			if($this->access->accessFullHRFinance==false) $data['access'] = false;
			$data['pageType'] = ((isset($_GET['pageType']))?$_GET['pageType']:'addItem');
						
			if(isset($_POST['submitType'])){				
				////submitType == addItem, updateItem, empAddItem
				$arrPages = array("addItem", "updateItem", "empAddItem");
				if(!in_array($_POST['submitType'], $arrPages)) exit;
				
				$upArr = $_POST;
								
				if($upArr['payAmount']=='specific amount') $upArr['payAmount'] = $upArr['inputPayAmount'];
				if($upArr['payAmount']=='hourly' && isset($upArr['payAmountHourly'])) $upArr['payAmount'] = $upArr['payAmountHourly'];
				if(!empty($upArr['payStart'])) $upArr['payStart'] = date('Y-m-d', strtotime($upArr['payStart']));
				if(!empty($upArr['payEnd'])) $upArr['payEnd'] = date('Y-m-d', strtotime($upArr['payEnd']));
				if($upArr['payAmount']=='regularHoliday') $upArr['payPercent'] = $upArr['selectPayPercent'];
				if($upArr['payAmount']=='specialHoliday') $upArr['payPercent'] = 2;
				if($upArr['payPeriod']=='once' && !empty($upArr['payStartOnce'])){
					$upArr['payStart'] = date('Y-m-d', strtotime($upArr['payStartOnce']));
					$upArr['payEnd'] = $upArr['payStart'];
				}				
				
				unset($upArr['submitType']);
				unset($upArr['payID']);
				unset($upArr['inputPayAmount']);
				unset($upArr['payAmountHourly']);
				unset($upArr['payStartOnce']);
				unset($upArr['selectPayPercent']);
											
				if($_POST['submitType']=='addItem'){					
					$payID = $this->dbmodel->insertQuery('tcPayslipItems', $upArr);
					
					header('Location: '.$this->config->base_url().'timecard/manangepaymentitem/?pageType=updateItem&payID='.$payID);
					exit;
				}else if($_POST['submitType']=='updateItem' && $data['pageType']=='empUpdate'){
					if(isset($upArr['payStaffID'])){
						unset($upArr['payStaffID']);
						$this->dbmodel->updateQuery('tcPayslipItemStaffs', array('payStaffID'=>$_POST['payStaffID']), $upArr);
						
						$data['updatedText'] = 'Item has been updated';
					}else{
						$upArr['empID_fk'] = $data['visitID'];
						$upArr['payID_fk'] = $_POST['payID'];							
						$insID = $this->dbmodel->insertQuery('tcPayslipItemStaffs', $upArr);
						header('Location: '.$this->config->base_url().'timecard/'.$data['visitID'].'/mypayrollsetting/');
						exit;
					}
				}else if($_POST['submitType']=='empAddItem'){
					if(isset($_GET['empIDs'])){
						$empIDs = explode(',', $_GET['empIDs']);
						foreach($empIDs AS $e){
							$upArr['empID_fk'] = $e;
							$upArr['payID_fk'] = $_POST['payID'];							
							$insID = $this->dbmodel->insertQuery('tcPayslipItemStaffs', $upArr);
						}
						echo 'Items has been added.';
						exit;
					}else{
						$upArr['empID_fk'] = $data['visitID'];
						$upArr['payID_fk'] = $_POST['payID'];							
						$insID = $this->dbmodel->insertQuery('tcPayslipItemStaffs', $upArr);
						
						header('Location: '.$this->config->base_url().'timecard/'.$data['visitID'].'/mypayrollsetting/');
						exit;							
					}
				}else{
					$payID = $_POST['payID'];
					$this->dbmodel->updateQuery('tcPayslipItems', array('payID'=>$_POST['payID']), $upArr);
					
					header('Location:'.$_SERVER['REQUEST_URI']);
					exit;
				}				
			}
		
			if($data['pageType']=='addItem'){
				$query = $this->dbmodel->dbQuery('SELECT COLUMN_NAME, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME="tcPayslipItems"');
				foreach($query->result() AS $q){
					$data['dataItemInfo'][$q->COLUMN_NAME] = '';
				} 
				$data['dataItemInfo'] = (object) $data['dataItemInfo'];	
			}else{
				if(isset($_GET['staffPayID'])){
					$data['dataItemInfo'] = $this->dbmodel->getSingleInfo('tcPayslipItemStaffs', 'tcPayslipItemStaffs.*, payName, payType, payCDto, payCategory, 	mainItem, tcPayslipItems.payAmount AS prevAmount', 'payStaffID="'.$_GET['staffPayID'].'"', 'LEFT JOIN tcPayslipItems ON payID=payID_fk');
				}else{
					$data['dataItemInfo'] = $this->dbmodel->getSingleInfo('tcPayslipItems', '*', 'payID="'.$_GET['payID'].'"');	
				}				
			}

			if(isset($_GET['empIDs'])){
				$data['dataStaffs'] = $this->dbmodel->getQueryResults('staffs', 'CONCAT(fname," ",lname) AS name', 'empID IN ('.trim($_GET['empIDs']).')');
			}
		}		
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function mypayrollsetting($data){
		$data['content'] = 'v_timecard/v_mypayrollsetting';
		
		if($this->user!=false){
			$data['pageType'] = '';
			if($this->access->accessFullHRFinance==false && $this->user->empID!=$data['visitID']) $data['access'] = false;
			
			$data['dataAddItems'] = $this->dbmodel->getQueryResults('tcPayslipItems', '*', 'mainItem=0', '', 'payName'); //additional items
			$data['dataMyItems'] = $this->payrollM->getPaymentItems($data['visitID']);
			
			if(isset($_GET['empIDs'])){
				$data['empIDs'] = trim($_GET['empIDs'], ',');	
				$data['pageType'] = 'batch';
				$data['dataStaffs'] = $this->dbmodel->getQueryResults('staffs',  'CONCAT(fname," ",lname) AS name', 'empID IN ('.$data['empIDs'].')');
			} 
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function payslipdetail($data){
		$data['content'] = 'v_timecard/v_payslipdetail';	
		$data['tpage'] = 'payslips';
		
		if($this->user!=false){				
			$empID = $this->uri->segment(2);
			if(!is_numeric($empID)){
				$empID = $this->user->empID;
				$payID = $this->uri->segment(3);
			}else $payID = $this->uri->segment(4);			
			if($empID==$this->user->empID) unset($data['column']);
						
			$data['payInfo'] = $this->dbmodel->getSingleInfo('tcPayslips', 
				'payslipID, payrollsID, empID, monthlyRate, basePay, monthlyRate, earning, bonus, tcPayslips.allowance, adjustment, deduction, totalTaxable, net, payPeriodStart, payPeriodEnd, payType, payDate, fname, lname, idNum, startDate, bdate, title, tcPayrolls.status, levelName, staffHolidaySched', 
				'payslipID="'.$payID.'" AND empID_fk="'.$empID.'"', 
				'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position LEFT JOIN orgLevel ON levelID=orgLevel_fk');
			
			if($this->access->accessFullHRFinance==false && $data['payInfo']->empID!=$this->user->empID && $this->commonM->checkStaffUnderMe($data['row']->username)==false){
				$data['access'] = false;
			}			
			
			$data['payslipID'] = $payID;			
			$data['payCatArr'] = $this->textM->constantArr('payCategory');
			$data['holidayArr'] = $this->textM->constantArr('holidayTypes');
			$data['dataAddItems'] = $this->dbmodel->getQueryResults('tcPayslipItems', '*, 1 AS isMain', 'mainItem=0', '', 'payCategory, payName');	
			
			if(count($data['payInfo'])>0){
				$data['dataPay'] = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payID, payValue, payType, payName, payCategory, numHR, payAmount', 'payslipID_fk="'.$payID.'" AND payValue!="0.00"',
					'LEFT JOIN tcPayslipItems ON payID=payItemID_fk', 'payCategory, payAmount, payType');
				
				if(!isset($data['access']) && isset($_GET['show'])){
					$bdate = date('ymd', strtotime($data['payInfo']->bdate));
					if($_GET['show']=='pdf' && $this->access->accessFullHRFinance==false){
						$acc = true;
						echo '<script>';
							echo 'var pass = prompt("This document is password protected. Please enter a password.", "");';
							echo 'if(pass=="'.$bdate.'"){
								window.location.href="'.$this->config->base_url().'timecard/'.$empID.'/payslipdetail/'.$payID.'/?show='.$this->textM->encryptText($bdate).'";
							}else{ 
								alert("Failed to load document. Invalid password.");';
								$acc = false;
							echo '}';
						echo '</script>';
						if($acc==false) $data['access'] = false;
						else exit;						
					}else{
						if($bdate==$this->textM->decryptText($_GET['show']) || $this->access->accessFullHRFinance==true){
							$this->payrollM->pdfPayslip($empID, $payID);
							exit;
						}else{
							$data['access'] = false;
						}
					}									
				}else{
					$data['dataDates'] = array();
					for($d=$data['payInfo']->payPeriodStart; $d<=$data['payInfo']->payPeriodEnd; ){
						$data['dataDates'][$d] = '';
						$d = date('Y-m-d', strtotime($d.' +1 day'));
					}					
					$dataDates = $this->dbmodel->getQueryResults('tcAttendance', 'attendanceID, dateToday, holidayType', 'dateToday BETWEEN "'.$data['payInfo']->payPeriodStart.'" AND "'.$data['payInfo']->payPeriodEnd.'"', '', 'dateToday');
					
					$data['dataWorked'] = $this->dbmodel->getQueryResults('tcStaffLogPublish', 
						'slogID, slogDate, empID_fk, publishTimePaid, publishND, publishDeduct, publishOT, publishHO, publishHOND, publishBy', 
						'empID_fk="'.$data['payInfo']->empID.'" AND slogDate BETWEEN "'.$data['payInfo']->payPeriodStart.'" AND "'.$data['payInfo']->payPeriodEnd.'"', 
						'', 
						'slogDate');
					
					foreach($dataDates AS $d){
						$data['dataDates'][$d->dateToday] = $d;
					}
				}
			}
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function managepayrolldetail(){
		$data['content'] = 'v_timecard/v_managepayrolldetail';
		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false) $data['access'] = false;
			$id = $this->uri->segment(3);
			$data['info'] = $this->dbmodel->getSingleInfo('tcPayrolls', '*', 'payrollsID='.$id);
			
			if(!empty($_POST)){
				if($_POST['submitType']=='changestatus'){
					if($_POST['status']==1){ ///publish payroll, send email to each staffs then change status
						$queryStaffs = $this->dbmodel->getQueryResults('tcPayslips', 'empID, fname, email', 'payrollsID_fk="'.$id.'" AND pstatus=1', 'LEFT JOIN staffs ON empID=empID_fk');
						if(count($queryStaffs)>0){
							$period = date('F d, Y', strtotime($data['info']->payPeriodStart)).' - '.date('F d, Y', strtotime($data['info']->payPeriodEnd));
							foreach($queryStaffs AS $s){
								$this->emailM->sendPublishPayrollEmail($period, $s->email, $s->fname);
							}
						}
					}
					
					if($_POST['status']==2) $this->payrollM->staffLogStatus($id, 'final');
					else $this->payrollM->staffLogStatus($id);
					
					$this->dbmodel->updateQueryText('tcPayrolls', 'status="'.$_POST['status'].'"', 'payrollsID="'.$id.'"');
					exit;
				}else if($_POST['submitType']=='action'){
					if($_POST['selectAction']=='regeneratepay'){
						$infoArr = array();			
						$infoArr = (array)$this->dbmodel->getSingleInfo('tcPayrolls', 'payrollsID, payPeriodStart, payPeriodEnd, payType');	
						$infoArr['empIDs'] = $_POST['empIDs'];
						$this->payrollM->generatepayroll($infoArr);
					}else if($_POST['selectAction']=='deletepay'){					
						$this->dbmodel->updateQueryText('tcPayslips LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk', 'pstatus=0', 'payrollsID="'.$_POST['payrollsID'].'" AND pstatus=1 AND empID_fk IN ('.trim($_POST['empIDs'],',').')');
						
						//number generated
						$upq = '';
						$cntGenerated = $this->dbmodel->getSingleField('tcPayslips', 'COUNT(payslipID)', 'payrollsID_fk="'.$_POST['payrollsID'].'" AND pstatus=1');
						if($cntGenerated==0){
							$upq = ', status=3';
							$this->payrollM->staffLogStatus($_POST['payrollsID']);
						} 
						$this->dbmodel->updateQueryText('tcPayrolls', 'numGenerated="'.$cntGenerated.'"'.$upq, 'payrollsID="'.$_POST['payrollsID'].'"');
					}
				}				
			}	
			
			$data['dataPayroll'] = $this->dbmodel->getQueryResults('tcPayslips', 'payslipID, empID_fk, earning, deduction, net, CONCAT(lname,", ",fname) AS name', 'payrollsID_fk='.$id.' AND pstatus=1', 'LEFT JOIN staffs ON empID=empID_fk', 'lname');
			$data['totalGross'] = $this->dbmodel->getSingleField('tcPayslips', 'SUM(earning)', 'payrollsID_fk="'.$data['info']->payrollsID.'"');
			$data['totalDeduction'] = $this->dbmodel->getSingleField('tcPayslips', 'SUM(deduction)', 'payrollsID_fk="'.$data['info']->payrollsID.'"');
			$data['totalNet'] = $this->dbmodel->getSingleField('tcPayslips', 'SUM(net)', 'payrollsID_fk="'.$data['info']->payrollsID.'"');
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function regeneratepayslip(){
		if($this->user!=false){
			$id = $this->uri->segment(3);
			$info = array();
			$query = $this->dbmodel->getSingleInfo('tcPayslips', 'empID_fk, payPeriodEnd, payPeriodStart, payrollsID, payType', 'payslipID="'.$id.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk');
			if(count($query)>0){
				foreach($query AS $k=>$v) $info[$k] = $v;
				$info['empIDs'] = $query->empID_fk;
				$this->payrollM->generatepayroll($info);
			}			
		}
	}
	
	public function regeneratepayroll(){
		if($this->user!=false){
			$id = $this->uri->segment(3);
			$info = array();
						
			$genArr = array();
			$info = $this->dbmodel->getSingleInfo('tcPayrolls', 'payrollsID, payPeriodStart, payPeriodEnd, payType', 'payrollsID='.$id);
			foreach($info AS $k=>$v) $genArr[$k] = $v;
				
			$genArr['empIDs'] = '';
			$query = $this->dbmodel->getQueryResults('tcPayslips', 'empID_fk', 'payrollsID_fk="'.$id.'"');
			if(count($query)>0){
				foreach($query AS $e) $genArr['empIDs'] .= $e->empID_fk.',';
				
				$genArr['empIDs'] = rtrim($genArr['empIDs'], ',');
				$this->payrollM->generatepayroll($genArr);
			}			
		}
	}
		
	
	public function unpublishedlogs($data){
		$data['content'] = 'v_timecard/v_manage_unpublishedlogs';	
		unset($data['timecardpage']);
		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false){
				$data['access'] = false;
			}else{				
				$condUsers = '';
				if($this->config->item('timeCardTest')==true){ ///////////////TEST USERS ONLY REMOVE THIS IF LIVE TO ALL
					$testUsers = $this->commonM->getTestUsers();
					$condUsers = ' AND empID_fk IN ('.implode(',', $testUsers).')';
				}
				
				$data['dataUnpublished'] = $this->dbmodel->getQueryResults('tcStaffLogPublish', 
					'slogID, slogDate, schedIn, schedOut, timeIn, timeOut, timeBreak, empID_fk, CONCAT(fname," ",lname) AS name, username', 
					'publishBy="" AND slogDate!="'.$data['currentDate'].'"'.$condUsers, 
					'LEFT JOIN staffs ON empID=empID_fk');
			}
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function logpendingrequest($data){
		$data['content'] = 'v_timecard/v_manage_logpendingreq';
		unset($data['timecardpage']); //unset timecardpage value so that timecard header will not show
		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false && $this->user->level==0){
				$data['access'] = false;
			}else{
				$condition = '';
				if($this->access->accessFullHR==false){
					$ids = '"",'; //empty value for staffs with no under yet
					$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
					foreach($myStaff AS $m):
						$ids .= $m->empID.',';
					endforeach;
					$condition .= ' AND empID_fk IN ('.rtrim($ids,',').')';
				}
				
				$data['timelogRequests'] = $this->dbmodel->getQueryResults('tcTimelogUpdates', 'logDate, message, dateRequested, empID_fk, CONCAT(fname," ",lname) AS name, username', 'status=1'.$condition, 'LEFT JOIN staffs ON empID=empID_fk');
			}
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function computelastpay(){
		$data['content'] = 'v_timecard/v_computelastpay';
		
		if($this->user!=false){
			if(!empty($_POST['submitType'])){
				if($_POST['submitType']=='submitPeriod'){						
					header('Location:'.$this->config->base_url().'timecard/computelastpay/?empID='.$_GET['empID'].'&periodFrom='.$_POST['periodFromYear'].'-01-01'.'&periodTo='.date('Y-m-t', strtotime($_POST['periodToYear'].'-'.$_POST['periodToMonth'].'-01')));
					exit;
				}else if($_POST['submitType']=='getTaxBracket'){
					$info = $this->dbmodel->getSingleInfo('taxTable', 'excessPercent, baseTax, minRange', 'taxType="yearly" AND "'.$_POST['netTax'].'" BETWEEN minRange AND maxRange');
					if(count($info)>0){
						echo $info->excessPercent.'|'.$info->baseTax.'|'.$info->minRange;
					}
					exit;
				}else if($_POST['submitType']=='removePayslip'){
					$this->dbmodel->updateQueryText('tcPayslips', 'pstatus=0', 'payslipID="'.$_POST['payslipID'].'"');
					exit;
				}else if($_POST['submitType']=='savecomputation'){ //saving computation
					unset($_POST['submitType']);						
					foreach($_POST AS $k=>$p){
						$_POST[$k] = str_replace(',','',$p);
					}
										
					$_POST['generatedBy'] = $this->user->username;
					$_POST['dateGenerated'] = date('Y-m-d H:i:s');
					$lastpayID = $this->dbmodel->getSingleField('tcLastPay', 'lastpayID', 'empID_fk="'.$_POST['empID_fk'].'"');
					if(!empty($lastpayID)){
						$this->dbmodel->updateQuery('tcLastPay', array('lastpayID'=>$lastpayID), $_POST);
					}else{
						$lastpayID = $this->dbmodel->insertQuery('tcLastPay', $_POST);
					}
					
					echo $lastpayID;
					exit;
				}
			}
							
			$data['pageType'] = 'showperiod';
			if(isset($_GET['payID'])){
				$data['pageType'] = 'showpay';
				$data['payInfo'] = $this->dbmodel->getSingleInfo('tcLastPay', '*', 'lastpayID="'.$_GET['payID'].'"');
				if(count($data['payInfo'])==0) $data['access'] = false;
				else{
					$empID = $data['payInfo']->empID_fk;
					$data['periodFrom'] = $data['payInfo']->dateFrom;
					$data['periodTo'] = $data['payInfo']->dateTo;
					$data['dataBracket'] = $this->dbmodel->getSingleInfo('taxTable', 'excessPercent, baseTax, minRange', 'taxType="yearly" AND "'.$data['payInfo']->taxNetTaxable.'" BETWEEN minRange AND maxRange');
				}					
			}else if(isset($_GET['empID'])){
				$empID = $_GET['empID'];
			}				
			
			$data['staffInfo']	= $this->dbmodel->getSingleInfo('staffs', 'empID, username, idNum, fname, lname, bdate, startDate, endDate, taxstatus, sal, leaveCredits', 'empID="'.$empID.'"');
			if(count($data['staffInfo'])==0) $data['access'] = false;
			
			if(isset($_GET['periodFrom']) && isset($_GET['periodTo'])){
				$data['pageType'] = 'hideperiod';
				$data['periodFrom'] = $_GET['periodFrom'];
				$data['periodTo'] = $_GET['periodTo'];
			}
			
			if(isset($data['periodFrom']) && isset($data['periodTo'])){			
				$data['dateArr'] = $this->payrollM->getArrayPeriodDates($data['periodFrom'], $data['periodTo']);	
				$data['dataMonth'] = $this->dbmodel->getQueryResults('tcPayslips', 'payslipID, payDate, basePay, totalTaxable, earning, deduction, net, (SELECT SUM(payValue) FROM tcPayslipDetails LEFT JOIN tcPayslipItems ON payID=payItemID_fk WHERE payslipID_fk=payslipID AND payCategory=0 AND payType="debit") AS deductions, (SELECT payValue FROM tcPayslipDetails LEFT JOIN tcPayslipItems ON payID=payItemID_fk WHERE payslipID_fk=payslipID AND payAmount="taxTable") AS incomeTax', 
				'empID_fk="'.$empID.'" AND payDate BETWEEN "'.$data['periodFrom'].'" AND "'.$data['periodTo'].'" AND status!=3 AND pstatus=1', 
				'LEFT JOIN tcPayrolls ON payrollsID_fk=payrollsID');
			}
							
			///THIS IS FOR THE PDF
			if(isset($_GET['show'])){
				$bdate = date('ymd', strtotime($data['staffInfo']->bdate));
				if($_GET['show']=='pdf' && $this->access->accessFullHRFinance==false){
					$acc = true;
					echo '<script>';
						echo 'var pass = prompt("This document is password protected. Please enter a password.", "");';
						echo 'if(pass=="'.$bdate.'"){
							window.location.href="'.$this->config->base_url().'timecard/computelastpay/?payID='.$_GET['payID'].'&show='.$this->textM->encryptText($bdate).'";
						}else{ 
							alert("Failed to load document. Invalid password.");';
							$acc = false;
						echo '}';
					echo '</script>';
					
					if($acc==false) $data['access'] = false;
					else exit;
				}else{
					if($bdate==$this->textM->decryptText($_GET['show']) || $this->access->accessFullHRFinance==true){
						$this->payrollM->pdfLastPay($data);
						exit;
					}else{
						$data['access'] = false;
					}
				}									
			}
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function managelastpay(){
		$data['content'] = 'v_timecard/v_managelastpay';
		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false) $data['access'] = false;
			else{
				$data['dataQuery'] = $this->dbmodel->getQueryResults('tcLastPay', 'tcLastPay.*, idNum, fname, lname, username, startDate, endDate, sal', '1', 'LEFT JOIN staffs ON empID=empID_fk');				
			}
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function generate13thmonth(){
		$data['content'] = 'v_timecard/v_13thmonthgenerate';
		
		if($this->user!=false){
			if($this->access->accessFullFinance==false) $data['access'] = false;
			else{
				$empIDs = $_GET['empIDs']; ///from URL separated with comma
				if(!empty($_POST)){
					if($_POST['submitType']=='regenerate'){
						$mInfo = $this->dbmodel->getSingleInfo('tc13thMonth', 'periodFrom, periodTo, includeEndMonth', 'tcmonthID="'.$_POST['monthID'].'"');
						$dateFrom = $mInfo->periodFrom;
						$dateTo = $mInfo->periodTo;
						$_POST['includeEndMonth'] = $mInfo->includeEndMonth;
					}else{
						$dateFrom = date('Y-m-d', strtotime($_POST['yearFrom'].'-01-01'));
						$dateTo = date('Y-m-d', strtotime($_POST['monthTo'].' 01, '.$_POST['yearTo']));
					}
					
					if($dateFrom>$dateTo){
						$data['errorText'] = 'Invalid Inputs. Please try again.';
					}else{
						$data['generated'] = $this->payrollM->generate13thmonth($dateFrom, $dateTo, $empIDs, $_POST['includeEndMonth']);
						if($_POST['submitType']=='generate'){							
							echo '<script> parent.window.location.href="'.$this->config->base_url().'timecard/manage13thmonth/";</script>';
						}
						exit;
					}
				}
				
				$data['dataStaffs']	= $this->dbmodel->getQueryResults('staffs', 'empID, idNum, fname, lname, startDate', 'empID IN ('.rtrim($empIDs, ',').')');
				if(isset($_GET['tcmonthID'])){
					$data['genInfo'] = $this->dbmodel->getSingleInfo('tc13thMonth', 'periodFrom, periodTo', 'tcmonthID="'.$_GET['tcmonthID'].'"');
				}
			}
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function manage13thmonth(){
		$data['content'] = 'v_timecard/v_13thmonthmanage';
		$data['tpage'] = 'managepayroll';
		$data['column'] = 'withLeft';
		unset($data['timecardpage']); //unset timecardpage value so that timecard header will not show
		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false) $data['access'] = false;
			else{
				$data['queryData'] = $this->dbmodel->getQueryResults('tc13thMonth', 'tc13thMonth.*, fname, lname, startDate, endDate', '1', 'LEFT JOIN staffs ON empID=empID_fk', 'dateGenerated, lname');
			}
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function detail13thmonth($data){
		$data['content'] = 'v_timecard/v_13thmonthdetail';
		
		if($this->user!=false){
			$id = $this->uri->segment(3);
			$data['dataInfo'] = $this->dbmodel->getSingleInfo('tc13thMonth', 'tc13thMonth.*, fname, lname, idNum, title, bdate', 'tcmonthID="'.$id.'"', 
				'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
			if(count($data['dataInfo'])==0) $data['access'] = false;
			else{
				$data['dataMonth'] = $this->payrollM->query13thMonth($data['dataInfo']->empID_fk, $data['dataInfo']->periodFrom, $data['dataInfo']->periodTo, $data['dataInfo']->includeEndMonth);
				
				///THIS IS FOR THE PDF
				if(isset($_GET['show'])){
					$bdate = date('ymd', strtotime($data['dataInfo']->bdate));
					if($_GET['show']=='pdf' && $this->access->accessFullHRFinance==false){
						$acc = true;
						echo '<script>';
							echo 'var pass = prompt("This document is password protected. Please enter a password.", "");';
							echo 'if(pass=="'.$bdate.'"){
								window.location.href="'.$this->config->base_url().'timecard/detail13thmonth/'.$id.'/?show='.$this->textM->encryptText($bdate).'";
							}else{ 
								alert("Failed to load document. Invalid password.");';
								$acc = false;
							echo '}';
						echo '</script>';
						if($acc==false) $data['access'] = false;
						else exit;
					}else{
						if($bdate==$this->textM->decryptText($_GET['show']) || $this->access->accessFullHRFinance==true){
							$this->payrollM->pdf13thMonth($data['dataInfo'], $data['dataMonth']);
							exit;
						}else{
							$data['access'] = false;
						}
					}									
				}
			}
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function payrolldistributionreport(){
		$payID = $this->uri->segment(3);
		
		if($this->user==false || $this->access->accessFullHR==false || !is_numeric($payID)){
			$data['access'] = false;
			$data['content'] = 'index';
			$this->load->view('includes/template', $data);
		}else{
			require_once('includes/excel/PHPExcel/IOFactory.php');
			$fileType = 'Excel5';
			$fileName = 'includes/templates/payrollDistributionReport.xls';

			// Read the file
			$objReader = PHPExcel_IOFactory::createReader($fileType);
			$objPHPExcel = $objReader->load($fileName);
			
			
			$dataPay = $this->dbmodel->getQueryResults('tcPayrolls', 'payrollsID, payPeriodStart, payPeriodEnd, payslipID, empID_fk, monthlyRate, basePay, totalTaxable, earning, deduction, tcPayslips.allowance, adjustment, advance, benefit, bonus, net, CONCAT(lname,", ",fname) AS name, idNum', 
					'payrollsID="'.$payID.'"',
					'LEFT JOIN tcPayslips ON payrollsID=payrollsID_fk LEFT JOIN staffs ON empID=empID_fk');
					
			$payArr = array();
			$needHourArr = array('regularTaken', 'nightDiff', 'overTime', 'specialPHLHoliday', 'regularPHLHoliday', 'regularUSHoliday', 'regularHoliday', 'regHourAdded', 'nightDiffAdded', 'nightDiffSpecialHoliday', 'nightDiffRegHoliday');
			foreach($dataPay AS $paid){
				$payArr[$paid->empID_fk] = (array)$paid;
				
				$payItems = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payCode, payValue, numHR', 'payslipID_fk="'.$paid->payslipID.'"', 'LEFT JOIN tcPayslipItems ON payID=payItemID_fk');
				
				foreach($payItems AS $item){
					$payArr[$paid->empID_fk][$item->payCode] = $item->payValue;
					if(in_array($item->payCode, $needHourArr)) $payArr[$paid->empID_fk][$item->payCode.'HR'] = $item->numHR;
				}
			}
			
			echo '<table>';
			foreach($payArr AS $pay){
				echo '<tr>';
					echo '<td>'.$pay['name'].'</td>'; ///employee
					echo '<td>'.$pay['idNum'].'</td>'; //employee ID
					echo '<td>'.((isset($pay['regularTaken']))?$pay['regularTaken']:'0.00').'</td>'; //regular taken
					echo '<td>'.((isset($pay['regularTakenHR']))?$pay['regularTakenHR']:'0.00').'</td>'; //regular taken hours
					
					//regular worked hours
					if(isset($pay['basePayHR']) && $pay['basePayHR']>0){
						echo '<td>'.((isset($pay['basePay']))?$pay['basePay']:'0.00').'</td>'; //regular worked
						echo '<td>'.((isset($pay['basePayHR']))?$pay['basePayHR']:'0.00').'</td>'; //regular worked hours
						echo '<td>0.00</td>'; //base pay
					}else{
						echo '<td>0.00</td>'; //regular worked
						echo '<td>0.00</td>'; //regular worked hours
						echo '<td>'.((isset($pay['basePay']))?$pay['basePay']:'0.00').'</td>'; //base pay
					}
					
				echo '</tr>';
			}
			
			echo '</table>';
			
				echo '<pre>';
				print_r($payArr);
				echo '</pre>';
				exit;
			
			
			/* 

			// Change the file
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A1', 'Hello')
						->setCellValue('A6', 'LUDIVINA MARINAS')
						->setCellValue('B1', 'World!');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
			ob_end_clean();
			// We'll be outputting an excel file
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="Payroll_Distribution_Report.xls"');
			$objWriter->save('php://output');
			
			 */
		}		
	}
	
	public function testExcel2(){
		require_once('includes/excel/PHPExcel/IOFactory.php');

		$fileType = 'Excel5';
		$fileName = 'includes/reporttest.xls';

		// Read the file
		$objReader = PHPExcel_IOFactory::createReader($fileType);
		$objPHPExcel = $objReader->load($fileName);

		// Change the file
		$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('A1', 'Hello')
					->setCellValue('A6', 'LUDIVINA MARINAS')
					->setCellValue('B1', 'World!');

		// Write the file
		/* $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
		$objWriter->save($fileName); */
		
		
		#echo date('H:i:s') . " Write to Excel2007 format\n";
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
		ob_end_clean();
		// We'll be outputting an excel file
		header('Content-type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="payroll.xls"');
		$objWriter->save('php://output');
	}
		
}
?>
