<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecard extends MY_Controller {
 
	public function __construct(){
		parent::__construct();		
		$this->load->model('timecardmodel', 'timeM');
	}
		
	public function _remap($method){
		$segment3 = $this->uri->segment(3);
		$segment4 = $this->uri->segment(4);
		$data['currentDate'] = date('Y-m-d'); //this is the exact date today		
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
		$this->timeM->cntUpdateAttendanceRecord('2015-10-01');
		$this->timeM->cntUpdateAttendanceRecord('2015-10-02');
		$this->timeM->cntUpdateAttendanceRecord('2015-10-03');
		$this->timeM->cntUpdateAttendanceRecord('2015-10-05');
		$this->timeM->cntUpdateAttendanceRecord('2015-10-06');
		$this->timeM->cntUpdateAttendanceRecord('2015-10-07');
	}
		
	//runs everyday at 12am
	//get staff schedules and insert to tcStaffDailyLogs
	//insert to tcAttendance for summary of results today
	public function cronDailySchedulesAndAttendance(){
		$today = date('Y-m-d');
		$todaySmall = date('j');
		$scheduled = 0;
		
		//CHECK FOR STAFFS TODAY SCHEDULES
		$staffID = '';
		$querySchd = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'empID_fk', 'logDate="'.$today.'"');
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
			$logIDD = $this->timeM->insertToDailyLogs($staff->empID, $today, $schedToday); //inserting to tcStaffDailyLogs table
			if(!empty($logIDD)) $scheduled++;
		}
		
		//INSERT TO TCATTENDANCE TABLE IF NOT EXIST ELSE UPDATE Records
		$attLog = $this->dbmodel->getSingleInfo('tcAttendance', '*', 'dateToday="'.$today.'"');
		if(count($attLog)==0){
			$ins['dateToday'] = $today;
			$ins['scheduled'] = $scheduled;
			$ins['unpublished'] = $scheduled;
			
			$ins['holidayID_fk'] = $this->dbmodel->getSingleField('staffHolidays', 'holidayID', 'holidayDate="'.$ins['dateToday'].'" OR holidayDate="0000-'.date('m-d', strtotime($ins['dateToday'])).'"');
			$ins['numEmployees'] = $this->dbmodel->getSingleField('staffs', 'COUNT(empID)', 'active=1 AND startDate <= "'.$ins['dateToday'].'" AND (endDate="0000-00-00" OR endDate>="'.$ins['dateToday'].'")');
			
			//leaves
			$queryLeaves = $this->timeM->getNumDetailsAttendance($ins['dateToday'], 'leave');	
			$ins['scheduledLeave'] = count($queryLeaves);
			
			//offset
			$queryOffset = $this->timeM->getNumDetailsAttendance($ins['dateToday'], 'offset');
			$ins['scheduledOffset'] = count($queryOffset);
			
			if($scheduled>0)
				$this->dbmodel->insertQuery('tcAttendance', $ins);	///INSERT TO TCATTENDANCE TABLE if there there is scheduled work today
		}else{
			$this->timeM->cntUpdateAttendanceRecord($today);
		}
	}
	
	
	/**********
		This cron runs every 5 minutes.
			- check if there are logs
			- check if there is entry on tcStaffDailyLogs insert 1 if none. Inserted data will serve as clocked in with no schedule (this needs resolve)
			- insert to tcStaffDailyLogs
			- changed tcTimelogs isInserted to 1
	**********/
	public function cronDailyLogs(){
		//Additional hours set here
		$timeAllowedClockIn = $this->timeM->timesetting('timeAllowedClockIn');
		$timeAllowedClockOut = $this->timeM->timesetting('timeAllowedClockOut');
		
		echo $timeAllowedClockIn.'<br/>'.$timeAllowedClockOut.'<br/>'; exit;
		
		$logArr = array();
		$date00 = '0000-00-00 00:00:00';
		$logIDInserted = array();
		$withTimeOut = false;
		
		$logQuery = $this->dbmodel->getQueryResults('tcTimelogs', 'tcTimelogs.*, empID, username', 'isInserted=0', 'LEFT JOIN staffs ON idNum=staffs_idNum_fk', 'logtime');		
		if(count($logQuery)>0){
			foreach($logQuery AS $log){
				$logID = $this->dbmodel->getSingleField('tcStaffDailyLogs', 'tlogID', 'empID_fk="'.$log->empID.'" AND ("'.$log->logtime.'" BETWEEN DATE_ADD(schedIn, INTERVAL '.$timeAllowedClockIn.')  AND DATE_ADD(schedOut, INTERVAL '.$timeAllowedClockOut.'))'); //get log id belong to certain schedule
				
				if(empty($logID)){ //check if there is logs with no schedule on the same day
					$logID = $this->dbmodel->getSingleField('tcStaffDailyLogs', 'tlogID', 'empID_fk="'.$log->empID.'" AND logDate="'.date('Y-m-d', strtotime($log->logtime)).'"');
				}
				
				if(empty($logID)){ //if still empty insert records
					$logdate = date('Y-m-d', strtotime($log->logtime));
					$schedText[date('j', strtotime($log->logtime))] = $this->timeM->getSchedToday($log->empID, $logdate, true);								
					$logID = $this->timeM->insertToDailyLogs($log->empID, $logdate, $schedText); //inserting to tcStaffDailyLogs table
					$this->timeM->cntUpdateAttendanceRecord($logdate); //update records
				}
				
				if(!empty($logID))
					$logArr[$logID][] = array('baselogid'=>$log->logID, 'logtime'=>$log->logtime, 'type'=>$log->logtype);			
			}
		
			if(count($logArr)>0){
				foreach($logArr AS $id=>$trying){
					$logData = $this->dbmodel->getSingleInfo('tcStaffDailyLogs', 'tlogID, logDate, timeIn, timeOut, breaks, schedIn, schedOut, offsetIn, offsetOut, schedHour, offsetHour', 'tlogID="'.$id.'"');
					
					$updateArr = array();
					if(empty($logData->breaks)) $breaks = array();
					else $breaks = json_decode($logData->breaks);
									
					if(count($trying)>0){
						foreach($trying AS $d){
							if($d['type']=='A'){
								if($logData->timeIn==$date00) $updateArr['timeIn'] = $d['logtime']; //for time in
								else if($logData->offsetHour>0 && $logData->schedIn!=$logData->offsetOut && $logData->schedOut!=$logData->offsetIn){
									if(strtotime($d['logtime']) >= strtotime($logData->offsetIn.' '.$timeAllowedClockIn)){
										$updateArr['offTimeIn'] = $d['logtime']; //for offset
									}
								}  
							} 						
							
							else if($d['type']=='Z'){ //for time out
								if($logData->timeOut==$date00 && $logData->timeIn!=$date00) $updateArr['timeOut'] = $d['logtime'];
								else if($logData->offsetHour>0 && $logData->schedIn!=$logData->offsetOut && $logData->schedOut!=$logData->offsetIn){
									if(strtotime($d['logtime']) >= strtotime($logData->offsetOut) && strtotime($d['logtime']) <= strtotime($logData->offsetOut.' '.$timeAllowedClockOut) ){
										$updateArr['offTimeOut'] = $d['logtime']; //for offset
									}
								}
							}else if($logData->timeIn!=$date00) array_push($breaks, $d['logtime']);
							
							$logIDInserted[] = $d['baselogid'];	
						}

						//if timeout but missing break in
						if(isset($updateArr['timeOut']) && count($breaks)%2!=0) 
							array_push($breaks, $updateArr['timeOut']);
						
						if(count($breaks)%2==0){
							$compute = 0;
							$timebreaks = 0;
							foreach($breaks AS $b){
								if($compute==0) $compute = strtotime($b);
								else{
									$timebreaks += strtotime($b) - $compute;
									$compute = 0;
								}
							}
							$updateArr['timeBreak'] = $this->textM->convertTimeToMinHours($timebreaks,true);
						}
						
						if(!empty($breaks)){					
							$updateArr['numBreak'] = count($breaks);
							$updateArr['breaks'] = json_encode($breaks);
						}
						
						if(isset($updateArr['timeOut'])) $withTimeOut = true;
																					
						if(!empty($updateArr))
							$this->dbmodel->updateQuery('tcStaffDailyLogs', array('tlogID'=>$id), $updateArr);	
					}				
				}
			}
		
			//change value of isInserted to 1 meaning log is inserted
			if(count($logIDInserted)>0){
				$this->dbmodel->updateQueryText('tcTimelogs', 'isInserted="1"', 'logID IN ('.implode(',', $logIDInserted).')');
				echo 'Successfully inserted.';
			}
		}
		exit;		
	}
	
	/*********
		This cron will run every hour to update records in tcAttendance
		check previous date if time is less than or equal to 10AM today
		and PUBLISH data
	*********/
	public function cronDailyAttendanceRecord(){
		$strtoday11 = strtotime(date('Y-m-d 11:00:00'));
		$strtoday = strtotime(date('Y-m-d H:i:s'));
		
		if($strtoday<=$strtoday11){
			$today = date('Y-m-d', strtotime('-1 day'));
			$this->timeM->publishLogs($today);			
		}
		
		//today attendance logs, check first if there are schedules today less than current hour
		$today = date('Y-m-d');
		$staffToday = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID', 'logDate="'.$today.'" AND schedIn<="'.date('Y-m-d H:i:s').'"');
		if(count($staffToday)>0){
			$this->timeM->publishLogs($today);		
		}
	}
		
	/****
		This cron runs every hour to check if employee clocked in and clocked out. 
		If not, then it will send an employee reminding to clock in or clock out.
	****/
	public function cronTimecardLogsEmails(){
		//SEND EMAIL TO EMPLOYEES WITH NO TIME IN YET BUT schedIn is the current hour
		$queryNoTimeIn = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'empID_fk, email, fname, schedIn, schedOut, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor) AS supEmail', 'schedIn="'.date('Y-m-d H:00:00').'" AND active=1 AND timeIn="0000-00-00 00:00:00"', 'LEFT JOIN staffs ON empID=empID_fk');		
		if(count($queryNoTimeIn)>0){
			foreach($queryNoTimeIn AS $timein) $this->emailM->emailTimecard('notimein', $timein);
		}		
		
		//SEND EMAIL TO EMPLOYEES IF NO CLOCK OUT YET AFTER 4 HOURS
		$queryNoClockOut = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'empID_fk, email, fname, schedIn, schedOut, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor) AS supEmail', 'schedOut="'.date('Y-m-d H:00:00', strtotime(' -4 hours')).'" AND active=1 AND timeIn!="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00"', 'LEFT JOIN staffs ON empID=empID_fk');
		if(count($queryNoClockOut)>0){
			foreach($queryNoClockOut AS $timeOut) $this->emailM->emailTimecard('noclockout2hours', $timeOut);
		}
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
			
		if(date('d')==6){ //check if day today is 11th of the month
			$dateStart = date('Y-m-26', strtotime('-1 month'));
			$dateEnd = date('Y-m-10');
		}else{ //if today is 26th of the month
			$dateStart = date('Y-m-11');
			$dateEnd = date('Y-m-25');
		}
		
		$query = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, logDate, empID_fk, email, fname, lname', 'publish_fk=0 AND logDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'"', 'LEFT JOIN staffs ON empID=empID_fk', 'logDate');
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
			$segment2 = $this->uri->segment(2);
			if(is_numeric($segment2) && ($this->access->accessFullHR==false || $segment2==$this->user->empID)){
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
					exit;
				}
			} 
			
			//////////VARIABLE DECLARATIONS	
			$data['logtypeArr'] = $this->textM->constantArr('timeLogType');								
			$data['dayArr'] = array();
			$publishArr = array();
			$date00 = '0000-00-00 00:00:00';
			$dateTimeToday = date('Y-m-d H:i:s');			
			$dateStart = date('Y-m-01', strtotime($data['today']));
			$dateEnd = date('Y-m-t', strtotime($data['today']));
			$dateMonthToday = date('Y-m', strtotime($data['today']));											
			$EARLYCIN = $this->timeM->timesetting('earlyClockIn');
			$OUTL8 = $this->timeM->timesetting('outLate');
			$strCurDate = strtotime($data['currentDate']);
			//////////END OF VARIABLE DECLARATIONS	
			
			////CHECK FIRST IF TIME TODAY IS LESS THAN 10AM IF IT IS GET PREVIOUS SCHEDULE TO CHECK IF SHIFT STILL IN PROGRESS
			if(strtotime($dateTimeToday)<strtotime(date('Y-m-d 13:00:00'))){
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
				$data['schedArr'] = $this->timeM->getSchedArr($data['today'], ((isset($data['schedToday']['sched']))?$data['schedToday']['sched']:''));
			}
			
			//get all logs today
			$data['allLogs'] = $this->timeM->getLogsToday($data['visitID'], $data['currentDate'], $data['schedToday']);
				
			//GET PUBLISHED RECORDS
			$dataPublished = $this->dbmodel->getQueryResults('tcStaffPublished', 'DAY(publishDate) AS day, timePaid', 'empID_fk="'.$data['visitID'].'" AND publishDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'"');
			foreach($dataPublished AS $p){
				$publishArr[$p->day] = $p->timePaid;
			}
			
			//this is to check if no staff have schedule on the day but no logged time					
			$querySchedule = $this->timeM->getCalendarSchedule($dateStart, $dateEnd, $data['visitID']);	
			foreach($querySchedule AS $k=>$q){
				if($strCurDate>strtotime($q['schedDate']) && !empty($q['sched'])){
					$data['dayArr'][$k] = '<span class="errortext">NO LOG RECORDED</span>';
					/* if($q['sched']=='On Leave') $data['dayArr'][$k] = '<span class="errortext">ON LEAVE</span>';
					else $data['dayArr'][$k] = '<span class="errortext">NO LOG RECORDED</span>'; */
				}
			}
						
			//this is for logs for the calendar month			
			$data['dateLogs'] = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, empID_fk, logDate, DAY(logDate) AS dayLogDate, schedIn, schedOut, timeIn, timeOut, breaks, timeBreak, numBreak, offsetIn, offsetOut, publish_fk', 'empID_fk="'.$data['visitID'].'" AND logDate BETWEEN "'.$dateMonthToday.'-01" AND "'.$dateMonthToday.'-31"');
		
			foreach($data['dateLogs'] AS $dl){
				$content = '';
				$err = '';
				$isOnLeave = false;
				if(isset($querySchedule[$dl->dayLogDate]['sched']) && $querySchedule[$dl->dayLogDate]['sched']=='On Leave') $isOnLeave = true;
				
				//check if there is schedule
				if($isOnLeave===true) $err .= '<a href="'.$this->config->base_url().'staffleaves/'.$querySchedule[$dl->dayLogDate]['leaveID'].'/" class="iframe tanone"><div class="daysbox dayonleave">On-Leave<br>'.$querySchedule[$dl->dayLogDate]['leave'].'</div></a><br/>';
				else if($dl->schedIn==$date00 && $dl->schedOut==$date00 && $dl->offsetIn==$date00 && $dl->offsetOut==$date00) $err .= 'UNSCHEDULED<br/>';
					
				//check for time in, late or early in			
				if($dl->timeIn!=$date00){
					$content .= '<b>In:</b> '.date('h:i a', strtotime($dl->timeIn)).'<br/>';
					
					if($dl->schedIn!=$date00){
						if($dl->timeIn > date('Y-m-d H:i:s', strtotime($dl->schedIn.' +1 minutes'))) $err .= 'LATE<br/>';
						else if(strtotime($dl->timeIn)<= strtotime($dl->schedIn.' '.$EARLYCIN) && $dl->offsetIn==$date00)  $err .= 'EARLY IN<br/>';
					}
				}else if($dl->schedIn>=$dateTimeToday) $err .= 'NO TIME IN YET<br/>';
				else if($dl->timeIn==$date00 && $dl->timeOut==$date00 && $isOnLeave===false) $err .= 'ABSENT<br/>';
				else if($isOnLeave===false) $err .= 'NO TIME IN<br/>';
								
				//check for clock out
				if($dl->timeOut!=$date00){
					$content .= '<b>Out:</b> '.date('h:i a', strtotime($dl->timeOut)).'<br/>';
					
					if($dl->schedOut!=$date00){
						if($dl->timeOut < $dl->schedOut) $err .= 'EARLY OUT<br/>';
						else if(strtotime($dl->timeOut)>= strtotime($dl->schedOut.' '.$OUTL8) && $dl->offsetOut==$date00)  $err .= 'OUT LATE<br/>';
					}					
				}else if($dl->timeIn!=$date00){
					if($dl->timeOut==$date00 && $dl->schedOut>=$dateTimeToday) $err .= '<span style="color:green;">SHIFT IN PROGRESS</span><br/>';
					else $err .= 'NO CLOCK OUT<br/>';
				}
				
				if(!empty($dl->breaks)){										
					$content .= '<b>Break:</b> '.$this->textM->convertTimeToMinStr($dl->timeBreak).'<br/>'; 
					if($dl->timeBreak>'01:30:00') $err .= 'OVER BREAK<br/>';
					if($dl->numBreak%2!=0) $err .= 'MISSING BREAK IN<br/>';
					
				}else if($dl->timeIn!=$date00 && $dl->timeOut!=$date00) $err .= 'NO BREAKS<br/>';
				
				if(!empty($err) || !empty($content)){
					$numday = $dl->dayLogDate;										
					$data['dayArr'][$numday] = '<span class="errortext">'.$err.'</span>'.$content;
					if($dl->publish_fk>0) $data['dayArr'][$numday] = '<div class="daysbox daysched">PUBLISHED TO PAYROLL <br/>'.((isset($publishArr[$numday]) && $publishArr[$numday]>0)?'<b class="coloryellow">'.$publishArr[$numday].' HOURS</b>':'').'</div>'.$data['dayArr'][$numday];
				}
			}
			
			//ADDING dayEditOptionArr FOR EDIT DROPDOWN
			$checkIfUser = (($data['visitID']==$this->user->empID)?true:false);
			foreach($data['dayArr'] AS $h=>$m){
				if($checkIfUser) 
					$data['dayEditOptionArr'][$h][] = array('link'=>$this->config->base_url().'timecard/requestupdate/?d='.$dateMonthToday.'-'.$h, 'text'=>'Request Update');
				
				$data['dayEditOptionArr'][$h][] = array('link'=>$this->config->base_url().'timecard/'.$data['visitID'].'/viewlogdetails/?d='.$dateMonthToday.'-'.$h, 'text'=>'View Details');
			}
			
			//checking if there are pending requests
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
					if(isset($data['dayArr'][$k])) $data['dayArr'][$k] = $ttext.$data['dayArr'][$k];
					else $data['dayArr'][$k] = $ttext;
				}
			}				
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function attendance($data){	
		$data['content'] = 'v_timecard/v_attendance';	
		$data['tpage'] = 'attendance';
	
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
						
						$hisText .= '<b>Published: '.$his->published.'</b><br/>';
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
			$segment2 = $this->uri->segment(2);
			if(is_numeric($segment2) && ($this->access->accessFullHR==false || $segment2==$this->user->empID)){
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
			for($i=1; $i<=$dEnd; $i++){
				$dtoday = date('Y-m-d', strtotime($year.'-'.$month.'-'.$i));
				if($this->access->accessFullHR===true && strtotime($dtoday)>=$strcurrrentdate)
					$data['dayEditOptionArr'][$i][] = array('link'=>$this->config->base_url().'schedules/customizebyday/'.$data['visitID'].'/'.$dtoday.'/', 'text'=>'Edit Schedule');
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
					if($this->access->accessFullHR==true && $dayCurrentDate<=strtotime($yoyo['schedDate'])){
						$sched .= '<div class="daysbox dayEditable '.((isset($yoyo['custom']))?'daycustomsched':'daysched').'" onClick="checkRemove(\''.$yoyo['schedDate'].'\', \''.$yoyo['sched'].'\', '.((isset($yoyo['custom']))?1:0).')">'.$yoyo['sched'].' '.((isset($yoyo['workhome']))?'<br/>WORK HOME':'').'</div>';
					}else{
						$sched .= '<div class="daysbox '.((isset($yoyo['custom']))?'daycustomsched':'daysched').'">'.$yoyo['sched'].' '.((isset($yoyo['workhome']))?'<br/>WORK HOME':'').'</div>';
					}
				}
								
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
			$data['dataPayslips'] = $this->dbmodel->getQueryResults('tcPayslips', 'payslipID, payPeriod', 'empID_fk="'.$data['visitID'].'"', '', 'dategenerated DESC');
			
			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function payrolls($data){		
		$data['content'] = 'v_timecard/v_payrolls';		
		$data['tpage'] = 'payrolls';
	
		if($this->user!=false){	
			//$this->checkAccessPage($data['tpage']);
			
			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function reports($data){		
		$data['content'] = 'v_timecard/v_reports';		
		$data['tpage'] = 'reports';
		
		if($this->user!=false){	
			//$this->checkAccessPage($data['tpage']);
			
			
		}
		
		$this->load->view('includes/template', $data);
	}
	
	/* public function checkAccessPage($timecardpage){		
		if(is_numeric($this->uri->segment(2)) && ($this->uri->segment(2)!=$this->user->empID || $timecardpage=='scheduling' || $timecardpage=='attendance')){
			header('Location:'.$this->config->base_url().'timecard/'.$timecardpage.'/');
			exit;
		}
	} */
	
	
	public function viewalllogs(){
		$data['content'] = 'v_timecard/v_viewalllogs';
		$id = $this->uri->segment(3);
		$data['logText'] = $this->textM->constantArr('timeLogTypeText');			
		$info = $this->dbmodel->getSingleInfo('tcStaffDailyLogs', 'tcStaffDailyLogs.*, idNum, email, fname', 'tlogID="'.$id.'"', 'LEFT JOIN staffs ON empID=empID_fk');
				
		if(!empty($_POST)){
			if(empty($info->note)) $note = array();
			else $note = json_decode($info->note, true);
			
			if(isset($_POST['submitType']) && $_POST['submitType']=='reqUpdate'){				
				$upNote['type'] = 'updaterequest';
				$upNote['reason'] = 'Sent message to '.$_POST['email'].'<br/><i><b>Content:</b></i> '.$_POST['message'];		
				$upNote['by'] = $this->user->username;
				$upNote['dateEdited'] = date('Y-m-d H:i:s');
				
				array_push($note, $upNote);
				$upArr['note'] = json_encode($note);
				$upArr['requestUpdate'] = 0;
				$this->dbmodel->updateQuery('tcStaffDailyLogs', array('tlogID'=>$id), $upArr);				
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $_POST['email'], 'Update on Timelog Request', nl2br($_POST['message']));
			}else{
				$upArr = array();				
				$newdate = date('Y-m-d H:i:s', strtotime($_POST['datetext'].' '.$_POST['hh'].':'.$_POST['mm'].':00 '.$_POST['ampm']));			
				if($_POST['type']=='timeIn'){
					$upArr['timeIn'] = $newdate;
				}else if($_POST['type']=='timeOut'){
					$upArr['timeOut'] = $newdate;
				}else if($_POST['type']=='breakOut' || $_POST['type']=='breakIn'){
					$breaks = json_decode($info->breaks, true);
					
					$getIndex = 0;
					foreach($breaks AS $i=>$b){
						if($b[1]==$_POST['prevVal']){
							$getIndex = $i;
							break;
						}
					}
					$breaks[$getIndex] = array($_POST['type'], $newdate);
					$upArr['breaks'] = json_encode($breaks);
				}
				
				if(!empty($upArr)){
					$upNote['type'] = $_POST['type'];
					$upNote['prevVal'] = $_POST['prevVal'];
					$upNote['newVal'] = $newdate;
					$upNote['reason'] = $_POST['reason'];
					$upNote['by'] = $this->user->username;
					$upNote['dateEdited'] = date('Y-m-d H:i:s');
					
					array_push($note, $upNote);
					$upArr['note'] = json_encode($note);
									
					$this->dbmodel->updateQuery('tcStaffDailyLogs', array('tlogID'=>$id), $upArr);
				}
			}	
			
			header('Location:'.$this->config->base_url().'timecard/viewalllogs/'.$id.'/');
			exit;
		}
	
		$allLogs = array();
		if(count($info)>0){
			if($info->timeIn!='0000-00-00 00:00:00') $allLogs[] = array('timeIn', $info->timeIn);
			if($info->timeOut!='0000-00-00 00:00:00') $allLogs[] = array('timeOut', $info->timeOut);
			
			if(!empty($info->breaks)){
				$breaks = json_decode($info->breaks, true);
				
				foreach($breaks AS $b){
					$allLogs[] = array($b[0], $b[1]);
				}				
			}
		}

		if(count($allLogs)>0){
			foreach ($allLogs as $key => $val) {
				$time[$key] = $val[1];
			}
			array_multisort($time, SORT_ASC, $allLogs);	
		}
		
				
		$data['logInfo'] = $info;
		$data['allLogs'] = $allLogs;
		$data['logtypeArr'] = $this->textM->constantArr('timeLogType');
		$data['uploadDir'] = 'uploads/timecard/timeloguploaddocs/';
		$this->load->view('includes/templatecolorbox', $data);
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
			$ins['empID_fk'] = $this->user->empID;
			$ins['docs'] = rtrim($docs, '|');
			$ins['type'] = 'request';
			$ins['dateRequested'] = date('Y-m-d H:i:s');
			
			$this->dbmodel->insertQuery('tcTimelogUpdates', $ins);	
			$data['requested'] = true;	
		}
			
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
	public function managetimecard($data){
		$data['content'] = 'v_timecard/v_manage_unpublishedlogs';	
		$data['tpage'] = 'managetimecard';
		$data['column'] = 'withLeft';
		unset($data['timecardpage']); //unset timecardpage value so that timecard header will not show
		
		if($this->user!=false){
			if($this->access->accessFullHR==false){
				$data['access'] = false;
			}else{
				$data['proudpage'] = $this->uri->segment(3);
				if(empty($data['proudpage'])) $data['proudpage'] = 'unpublishedlogs';
				
				$data['dataUnpublished'] = $this->dbmodel->getQueryResults('tcStaffDailyLogs', 'tlogID, logDate, empID_fk, CONCAT(fname," ",lname) AS name, username', 'publish_fk=0 AND logDate!="'.$data['currentDate'].'"', 'LEFT JOIN staffs ON empID=empID_fk');
				$data['timelogRequests'] = $this->dbmodel->getQueryResults('tcTimelogUpdates', 'logDate, message, dateRequested, empID_fk, CONCAT(fname," ",lname) AS name, username', 'status=1', 'LEFT JOIN staffs ON empID=empID_fk');
				
				if($data['proudpage']=='logpendingrequest'){
					$data['content'] = 'v_timecard/v_manage_logpendingreq';
				}				
			}
		}
		
		$this->load->view('includes/template', $data);
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
			}else if($_POST['submitType']=='editLog' || $_POST['submitType']=='editBreaks'){
				if($_POST['submitType']=='editBreaks'){					
					$newBreak = array();
					$totalBreak = 0;
					$compute = 0;
					foreach($_POST['break'] AS $b){
						if(!empty($b)){
							array_push($newBreak, $b);
							if($compute==0) $compute = strtotime($b);
							else{
								$totalBreak += strtotime($b) - $compute;
								$compute = 0;
							}
						}
					}
					
					$prevBreak = $this->dbmodel->getSingleField('tcStaffDailyLogs', 'breaks', 'tlogID="'.$_POST['tlogID'].'"');
					$prevBreak = json_decode($prevBreak);
					
					$upArr['timeBreak'] = $this->textM->convertTimeToMinHours($totalBreak,true);
					$upArr['numBreak'] = count($newBreak);
					$upArr['breaks'] = json_encode($newBreak);
					$this->dbmodel->updateQuery('tcStaffDailyLogs', array('tlogID'=>$_POST['tlogID']), $upArr);
					
					
					$edited = '';
					$added = '';
					$pcount = count($prevBreak);
					$ncount = count($newBreak);
					for($p=0; $p<$pcount || $p<$ncount; $p++){
						if(isset($prevBreak[$p]))
							$pbreak = date('Y-m-d H:i', strtotime($prevBreak[$p]));
												
						if(isset($newBreak[$p]) && !isset($prevBreak[$p]))
							$added .= $newBreak[$p].', ';
						
						if(isset($newBreak[$p]) && isset($prevBreak[$p]) && $newBreak[$p]!=$pbreak)
							$edited .= 'from '.$pbreak.' to '.$newBreak[$p].', ';
					}
					 
					$insUpdate['message'] = '';
					if($edited!='') $insUpdate['message'] .= 'Edited breaks '.rtrim($edited, ', ').'<br/>';
					if($added!='') $insUpdate['message'] .= 'Added breaks '.rtrim($added, ', ').'<br/>';
					if(!empty($insUpdate['message'])) $insUpdate['message'] .= '<br/>';
					$insUpdate['message'] .= 'Reason: '.$_POST['reason'];
				}else{
					$this->dbmodel->updateQueryText('tcStaffDailyLogs', '`'.$_POST['timeType'].'` = "'.$_POST['timeVal'].'"', 'tlogID="'.$_POST['tlogID'].'"');
					$insUpdate['message'] = $_POST['prev'].' to '.date('d M Y h:i a', strtotime($_POST['timeVal'])).'<br/>Reason:'.$_POST['reason'];
				}
				
				//INSERT LOG HISTORY
				$insUpdate['empID_fk'] = $id;
				$insUpdate['logDate'] = $data['today'];				
				$insUpdate['dateRequested'] = date('Y-m-d H:i:s');
				$insUpdate['dateUpdated'] = date('Y-m-d H:i:s');
				$insUpdate['updatedBy'] = $this->user->username;
				$insUpdate['status'] = 0;
				$this->dbmodel->insertQuery('tcTimelogUpdates', $insUpdate);
				
				//updateAttendaceRecord
				$this->timeM->cntUpdateAttendanceRecord($data['today']);
			}else if($_POST['submitType']=='addSched'){
				$updArr['schedIn'] = date('Y-m-d H:i:s', strtotime($_POST['schedIn']));
				$updArr['schedOut'] = date('Y-m-d H:i:s', strtotime($_POST['schedOut']));
				$updArr['schedHour'] = $_POST['schedHour'];
								
				$this->dbmodel->updateQuery('tcStaffDailyLogs', array('tlogID'=>$_POST['tlogID']), $updArr);
				//updateAttendaceRecord
				$tLogDate = $this->dbmodel->getSingleField('tcStaffDailyLogs', 'logDate', 'tlogID="'.$_POST['tlogID'].'"');
				if(!empty($tLogDate)) $this->timeM->cntUpdateAttendanceRecord($tLogDate);
			
				//INSERT LOG HISTORY
				$insUpdate['message'] = 'Added schedule In:'.$_POST['schedIn'].' Out:'.$_POST['schedOut'];
				$insUpdate['empID_fk'] = $id;
				$insUpdate['logDate'] = $data['today'];				
				$insUpdate['dateRequested'] = date('Y-m-d H:i:s');
				$insUpdate['dateUpdated'] = date('Y-m-d H:i:s');
				$insUpdate['updatedBy'] = $this->user->username;
				$insUpdate['status'] = 0;
				$this->dbmodel->insertQuery('tcTimelogUpdates', $insUpdate);
				
				//CHECK CURRENT SCHEDULE ON tcStaffScheduleByDates
				$schedID = $this->dbmodel->getSingleField('tcStaffScheduleByDates', 'dateID', 'dateToday="'.$data['today'].'" AND empID_fk="'.$data['visitID'].'"');
				$arr['timeText'] = date('h:i a', strtotime($_POST['schedIn'])).' - '.date('h:i a', strtotime($_POST['schedOut']));
				$arr['timeHours'] = $_POST['schedHour'];
				$arr['status'] = 1;
				$arr['assignBy'] = $this->user->empID;
				$arr['assignDate'] = date('Y-m-d H:i:s');
				if(!empty($schedID)){
					$this->dbmodel->updateQuery('tcStaffScheduleByDates', array('dateID'=>$schedID), $arr);
				}else{
					$arr['dateToday'] = $data['today'];
					$arr['empID_fk'] = $data['visitID'];
					$this->dbmodel->insertQuery('tcStaffScheduleByDates', $arr);
				}
			}else if($_POST['submitType']=='publishlog'){
				$publishedID = $this->dbmodel->getSingleField('tcStaffDailyLogs', 'publish_fk', 'tlogID="'.$_POST['tlogID'].'"');
				if(!isset($publishedID) || (isset($publishedID) && $publishedID==0)){ //if unpublished insert published record
					$insPublish['empID_fk'] = $data['visitID'];
					$insPublish['publishDate'] = $data['today'];
					$insPublish['tcStaffDailyLogs_fk'] = $_POST['tlogID'];
					$insPublish['timePaid'] = $_POST['timePaid'];
					$insPublish['datePublished'] = date('Y-m-d H:i:s');
					$insPublish['publishedBy'] = $this->user->empID;
					$publishID = $this->dbmodel->insertQuery('tcStaffPublished', $insPublish);	
					$this->dbmodel->updateQueryText('tcStaffDailyLogs', 'publish_fk='.$publishID , 'tlogID="'.$_POST['tlogID'].'"'); //update log to published primary key
				}else{ //update record if already published
					$upPublish['timePaid'] = $_POST['timePaid'];
					$upPublish['datePublished'] = date('Y-m-d H:i:s');
					$upPublish['publishedBy'] = $this->user->empID;
					
					$this->dbmodel->updateQuery('tcStaffPublished', array('publishedID'=>$publishedID), $upPublish);
				}
				
				//timelog update details
				$inUp['status'] = 0;
				$inUp['empID_fk'] = $data['visitID'];
				$inUp['logDate'] = $data['today'];
				$inUp['message'] = 'Published logs. Time Paid: '.$_POST['timePaid'].' Hours';
				$inUp['updatedBy'] = $this->user->username;
				$inUp['dateRequested'] = date('Y-m-d H:i:s');
				$inUp['dateUpdated'] = date('Y-m-d H:i:s');
				$this->dbmodel->insertQuery('tcTimelogUpdates', $inUp);	
				
				//Add my notification
				$this->commonM->addMyNotif($this->user->empID, 'You published logs of '.$data['row']->name. ' for '.$data['today'], 5);
				
				//update tcAttendance Records
				$this->timeM->cntUpdateAttendanceRecord($data['today']);
			}else if($_POST['submitType']=='updatePublished'){
				$publish = $this->dbmodel->getSingleInfo('tcStaffPublished', '*', 'publishedID="'.$_POST['publishedID'].'"');
				
				$upArr['timePaid'] = $_POST['newTimePaid'];
				$upArr['datePublished'] = date('Y-m-d H:i:s');
				$upArr['publishedBy'] = $this->user->empID;
				$this->dbmodel->updateQuery('tcStaffPublished', array('publishedID'=>$_POST['publishedID']), $upArr); //UPDATE PUBLISH RECORD
				
				//INSERT HISTORY ON tcTimelogUpdates
				$ins['empID_fk'] = $publish->empID_fk;
				$ins['logDate'] = $publish->publishDate;
				$ins['status'] = 0;
				$ins['message'] = 'Updated Time Paid From '.$publish->timePaid.' Hours to '.$_POST['newTimePaid'].' Hours';
				$ins['dateRequested'] = date('Y-m-d H:i:s');
				$ins['dateUpdated'] = date('Y-m-d H:i:s');
				$ins['updatedBy'] = $this->user->username;
				$ins['updateNote'] = $_POST['reasonUpdate'];
				$this->dbmodel->insertQuery('tcTimelogUpdates', $ins);
			}
		}
		
		$data['schedToday'] = $this->timeM->getSchedToday($id, $data['today']);
		$data['updateRequests'] = $this->dbmodel->getQueryResults('tcTimelogUpdates', '*', 'empID_fk="'.$id.'" AND logDate="'.$data['today'].'"', '', 'dateRequested DESC');
		$data['log'] = $this->dbmodel->getSingleInfo('tcStaffDailyLogs', '*', 'empID_fk="'.$id.'" AND logDate="'.$data['today'].'"');
		$data['allLogs'] = $this->timeM->getLogsToday($data['visitID'], $data['today'], $data['schedToday']);
		$data['publish'] = $this->dbmodel->getSingleInfo('tcStaffPublished', '*', 'empID_fk="'.$id.'" AND publishDate="'.$data['today'].'"');
						
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
	
	public function attendancedetails2($data){
		$data['content'] = 'v_timecard/v_attendancedetails2';
		
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
	
	
}
?>