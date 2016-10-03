<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecard extends MY_Controller {
 
	public function __construct(){
		parent::__construct();		
		$this->load->model('timecardmodel', 'timeM');
		$this->load->model('payrollmodel', 'payrollM');
		$this->load->model('staffmodel', 'staffM');
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
				
	//runs everyday at 12am
	//get staff schedules and insert to tcStaffLogPublish
	//insert to tcAttendance for summary of results today
	//public function cronDailySchedulesAndAttendance(){
	public function cronDailySchedulesAndAttendance(){
		$today = date('Y-m-d');
		$todaySmall = date('j');
		$scheduled = 0;
		
		//$queryStaffs = $this->dbmodel->getQueryResults('staffs', 'empID', 'empID=309');	
		
		
		//CHECK FOR STAFFS TODAY SCHEDULES
		$staffID = '';
		$querySchd = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'empID_fk', 'slogDate="'.$today.'" AND showStatus=1');
		if(count($querySchd)>0){
			foreach($querySchd AS $s) $staffID .= $s->empID_fk.',';
		}
		if($staffID!=''){
			$staffID = ' AND empID NOT IN ('.rtrim($staffID, ',').')';
		}
				
		//STAFF SCHEDULES		
		$queryStaffs = $this->dbmodel->getQueryResults('staffs', 'empID', 'active=1 AND exclude_schedule=0'.$staffID);	
		
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
			$ins['numEmployees'] = $this->dbmodel->getSingleField('staffs', 'COUNT(empID)', 'active=1 AND exclude_schedule=0 AND startDate <= "'.$ins['dateToday'].'" AND (endDate="0000-00-00" OR endDate>="'.$ins['dateToday'].'")');
			
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
				$logID = $this->dbmodel->getSingleField('tcStaffLogPublish', 'slogID', 'empID_fk="'.$log->empID.'" AND showStatus=1 AND ("'.$log->logtime.'" BETWEEN DATE_ADD(schedIn, INTERVAL '.$timeAllowedClockIn.')  AND DATE_ADD(schedOut, INTERVAL '.$timeAllowedClockOut.'))'); //get log id belong to certain schedule
				
				if(empty($logID)){ //check if there is logs with no schedule on the same day
					$logID = $this->dbmodel->getSingleField('tcStaffLogPublish', 'slogID', 'empID_fk="'.$log->empID.'" AND slogDate="'.date('Y-m-d', strtotime($log->logtime)).'" AND showStatus=1');
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
					$logData = $this->dbmodel->getSingleInfo('tcStaffLogPublish', 'slogID, slogDate, timeIn, timeOut, breaks, timeBreak, numBreak, schedIn, schedOut, offsetIn, offsetOut, schedHour, offsetHour', 'slogID="'.$id.'" AND showStatus=1');
															
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
		$queryNoTimeIn = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'empID_fk, email, fname, schedIn, schedOut, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor) AS supEmail, leaveID_fk', 'schedIn="'.$dateToday.'" AND active=1 AND timeIn="0000-00-00 00:00:00" AND showStatus=1', 'LEFT JOIN staffs ON empID=empID_fk');	
		
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
		$queryNoClockOut = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'empID_fk, email, fname, schedIn, schedOut, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor) AS supEmail', 'schedOut="'.date('Y-m-d H:00:00', strtotime(' -4 hours')).'" AND active=1 AND timeIn!="0000-00-00 00:00:00" AND timeOut="0000-00-00 00:00:00" AND showStatus=1', 'LEFT JOIN staffs ON empID=empID_fk');
		
		
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
		
		$query = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, slogDate, empID_fk, email, fname, lname', 'publishBy="" AND slogDate BETWEEN "'.$dateStart.'" AND "'.$dateEnd.'" AND showStatus=1', 'LEFT JOIN staffs ON empID=empID_fk', 'slogDate');		
		if(count($query)>0){
			if($page=='hr') $this->emailM->emailTimecardUnpublishedLogs($dateStart, $dateEnd, $query, 'HR');
			else $this->emailM->emailTimecardUnpublishedLogs($dateStart, $dateEnd, $query);
		}
		exit;
	}
	
	public function timelogs($data){
		//$this->textM->aaa($data);
		$data['content'] = 'v_timecard/v_timelogs';
		$data['tpage'] = 'timelogs';
		$data['report_attendance'] = true;

		if( isset($_GET['d']) ){
			$data['report_start'] = date('Y-m-d', strtotime($_GET['d']) );
			$data['report_end'] = date('Y-m-t', strtotime($_GET['d']) );
		} else {
			$data['report_start'] = date('Y-m-d');
			$data['report_end'] = date('Y-m-t');
		}
		
								
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
			
			$data = $this->timeM->_getTimeLogs( $data );		
		}
	
		$this->load->view('includes/template', $data);
	}
	//attendance report download
	public function generate_attendance(){
		$data['visitID'] =$this->input->post('visitID');
		$data['report_start'] = date('Y-m-d', strtotime($this->input->post('report_start') ) );
		$data['report_end'] = date('Y-m-d', strtotime($this->input->post('report_end') ) );

		$this->timeM->getAttendanceReport($data);
		exit();
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
				//display all logs
				//if($his->scheduled!=0){
					
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
				//}
			}
						
			$jnum = date('j', strtotime($data['today']));
			if(!isset($data['dayEditOptionArr'][$jnum])){
				$data['dayEditOptionArr'][$jnum][] = array('link'=>$this->config->base_url().'timecard/attendancedetails/?d='.$data['today'], 'text'=>'View Details');
			}
					
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function calendar($data){
		//$this->textM->aaa($data);
		$data['content'] = 'v_timecard/v_calendar';	
		$data['tpage'] = 'calendar';		
		
		if($this->user!=false){
			$data['dayArr'] = array();
			$id = $this->uri->segment(2);
			if(is_numeric($id) && $this->commonM->checkStaffUnderMe($id)==false){
				header('Location:'.$this->config->base_url().'timecard/calendar/'.((isset($_GET['d']))?'?d='.$_GET['d']:''));
				exit;
			}
		
			$data = $this->timeM->_getCalendar( $data );
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
			
			$data['dataPayslips'] = $this->dbmodel->getQueryResults('tcPayslips', 'payslipID, payPeriodStart, payPeriodEnd, empID_fk, tcPayrolls.status', 'empID_fk="'.$data['visitID'].'" AND pstatus=1', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk AND status IN (1,2)', 'payDate DESC');
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
						
						$prevVal = $this->dbmodel->getSingleField('tcStaffLogPublish', 'breaks', 'slogID="'.$_POST['slogID'].'" AND showStatus=1');
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
					
					$prevVal = $this->dbmodel->getSingleField('tcStaffLogPublish', $_POST['changetype'], 'slogID="'.$_POST['slogID'].'" AND showStatus=1');
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
				$this->emailM->sendEmailEditedPayrollLogs($data['today'], $data['visitID']);
				
				//insert to tcTimelogUpdates
				$this->timeM->addToLogUpdate($id, $data['today'], '<b>Published. Time Paid: '.$pubArr['publishTimePaid'].' Hours</b>');				
			}else if($_POST['submitType']=='unpublish'){
				//insert to tcTimelogUpdates
				$info = $this->dbmodel->getSingleInfo('tcStaffLogPublish', 'publishTimePaid, datePublished, publishBy', 'slogID="'.$_POST['slogID'].'" AND showStatus=1');	
				
				$message = '<b>Unpublished log.</b>';
				if(count($info)>0){
					$message .= '<br/>Details:<br/>Time Paid: '.$info->publishTimePaid;
					$message .= '<br/>Prev Date Published: '.$info->datePublished;
					$message .= '<br/>Prev Published By: '.$info->publishBy;
				}				
				$this->timeM->addToLogUpdate($id, $data['today'], $message);
				$this->timeM->unpublishedLogs($_POST['slogID']);
				$this->emailM->sendEmailEditedPayrollLogs($data['today'], $data['visitID']);
				$this->timeM->cntUpdateAttendanceRecord($data['today']); //UPDATE ATTENDANCE RECORDS
				exit;
			}else if($_POST['submitType']=='doneChanging'){
				$upA['status'] = 0;
				$upA['updatedBy'] = $this->user->username;
				$upA['dateUpdated'] = date('Y-m-d H:i:s');
				$this->dbmodel->updateQuery('tcTimelogUpdates', array('updateID'=>$_POST['updateID']), $upA);
				$this->dbmodel->updateConcat('tcTimelogUpdates', 'updateID="'.$_POST['updateID'].'"', 'message', '<br/><i style="font-size:11px;"><u>Change status to DONE - by '.$this->user->username.'</u></i><br/>');
			}else if($_POST['submitType']=='removeLog'){				
				$this->timeM->addToLogUpdate($data['visitID'], $data['today'], '<b>Previous record deleted. <br/>Remove reason:</b> '.$_POST['removeReason']);
				
				$this->dbmodel->updateQueryText('tcStaffLogPublish', 'showStatus=0', 'slogID="'.$_POST['slogID'].'"');
			}
			
			$this->timeM->cntUpdateAttendanceRecord($data['today']); //UPDATE ATTENDANCE RECORDS
		}
		
		$data['schedToday'] = $this->timeM->getSchedToday($id, $data['today']);
		$data['dataLog'] = $this->dbmodel->getSingleInfo('tcStaffLogPublish', '*', 'empID_fk="'.$id.'" AND slogDate="'.$data['today'].'" AND showStatus=1');
		$data['dataBiometrics'] = $this->timeM->getLogsToday($data['visitID'], $data['today'], $data['schedToday']);
		$data['updateRequests'] = $this->dbmodel->getQueryResults('tcTimelogUpdates', '*', 'empID_fk="'.$id.'" AND logDate="'.$data['today'].'"', '', 'dateRequested DESC');
		
		$data['isUnder'] = $this->commonM->checkStaffUnderMe($data['row']->username);
		$data['staffHoliday'] = $this->dbmodel->getSingleField('staffs', 'staffHolidaySched', 'empID="'.$id.'"');
		$data['logtypeArr'] = $this->textM->constantArr('timeLogType');
		//$this->textM->aaa($data, false);
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
					if($_POST['submitType']=='removePayroll'){
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
					
					$data['dataStaffs'] = $this->dbmodel->getQueryResults('staffs', 'empID, lname, fname, username, title, dept, staffHolidaySched', 'office="PH-Cebu" '.$condition, 'LEFT JOIN newPositions ON posID=position', 'lname');
				}else if($data['pagepayroll']=='previouspayroll'){
					$data['pagePayTitle'] = 'Previous Payroll';
					
					$data['payrollStatusArr'] = $this->textM->constantArr('payrollStatusArr');
					$data['dataPayrolls'] = $this->dbmodel->getQueryResults('tcPayrolls', '*', 'status!=3 AND numGenerated>0', '', 'payPeriodEnd DESC');
				}else if($data['pagepayroll']=='payrollitems'){
					$data['pagePayTitle'] = 'Payroll Items';
					
					$data['payrollItemType'] = $this->textM->constantArr('payrollItemType');
					$payslip_items = $this->dbmodel->getQueryResults('tcPayslipItems', '*, 1 AS isMain, payAmount AS prevAmount', '1', '', 'payCategory, payName');
					foreach( $payslip_items as $item ){
						if( $item->mainItem == 1 ){
							$data['dataMainItems'][] = $item;
						}
						if( $item->mainItem == 0 ){
							$data['dataAddItems'][] = $item;
						}
					}
					//$this->textM->aaa($data);
					//$data['dataMainItems'] = $this->dbmodel->getQueryResults('tcPayslipItems', '*, 1 AS isMain, payAmount AS prevAmount', 'mainItem=1', '', 'payCategory, payName');				
					//$data['dataAddItems'] = $this->dbmodel->getQueryResults('tcPayslipItems', '*, 1 AS isMain, payAmount AS prevAmount', 'mainItem=0', '', 'payCategory, payName');	
				}else if($data['pagepayroll']=='payrollsettings'){
					$data['pagePayTitle'] = 'Payroll Settings';
					
					$data['dataPaySettings'] = $this->dbmodel->getQueryResults('staffSettings', '*', 'settingName IN ("minimumWage", "allowanceDailyRate")');
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
			// echo "<pre>";
			// var_dump($_POST);

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
					$genpayArr['empIDs'] =  ((is_array($_POST['empIDs']))?implode(',', $_POST['empIDs']):rtrim($_POST['empIDs'],','));
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

			if(is_array($_POST['empIDs']))
				$data['empIDs'] = implode(',', $_POST['empIDs']);
			else
				$data['empIDs'] = rtrim($_POST['empIDs'],',');

			//echo $data['empIDs'];

			$data['dataAttendance'] = array();	
			$dataEmps = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(lname, ", ",fname) AS name, staffHolidaySched', 'empID IN ('.$data['empIDs'].')', '', 'lname');

			foreach($dataEmps AS $emp){
				$data['dataAttendance'][$emp->empID]['name'] = $emp->name;
				$data['dataAttendance'][$emp->empID]['staffHolidaySched'] = $emp->staffHolidaySched;
			}
			
			$dataLogs = $this->dbmodel->getQueryResults('tcStaffLogPublish', 
				'slogID, slogDate, empID_fk, schedHour, timeIn, timeOut, schedIn, schedOut, publishTimePaid, publishND, publishHO, publishHOND, publishDeduct, publishBy, status', 
				'slogDate BETWEEN "'.$data['start'].'" AND "'.$data['end'].'" AND empID_fk IN ('.$data['empIDs'].') AND showStatus=1');	
			
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
		
		$data['dynamic_call'] = false;
		
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
					$data['dataItemInfo'] = $this->dbmodel->getSingleInfo('tcPayslipItemStaffs', 'tcPayslipItemStaffs.*, payName, payType, payCDto, payCategory, mainItem, tcPayslipItems.payAmount AS prevAmount', 'payStaffID="'.$_GET['staffPayID'].'"', 'LEFT JOIN tcPayslipItems ON payID=payID_fk');
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
				'payslipID, payrollsID, empID, monthlyRate, basePay, monthlyRate, earning, bonus, tcPayslips.allowance, adjustment, deduction, totalTaxable, net, payPeriodStart, payPeriodEnd, payType, payDate, fname, lname, idNum, startDate, endDate, bdate, title, tcPayrolls.status, levelName, staffHolidaySched', 
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
				$data['dataPay'] = $this->dbmodel->getQueryResults('tcPayslipDetails', 'payID, payCode, payValue, payType, payName, payCategory, numHR, payAmount', 'payslipID_fk="'.$payID.'" AND payValue!="0.00"',
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
							//exit;
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
						'empID_fk="'.$data['payInfo']->empID.'" AND slogDate BETWEEN "'.$data['payInfo']->payPeriodStart.'" AND "'.$data['payInfo']->payPeriodEnd.'" AND showStatus=1', 
						'', 
						'slogDate');
					
					foreach($dataDates AS $d){
						$data['dataDates'][$d->dateToday] = $d;
					}
				}
			}
		}
		//$this->output->enable_profiler(true);
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
						$infoArr = (array)$this->dbmodel->getSingleInfo('tcPayrolls', 'payrollsID, payPeriodStart, payPeriodEnd, payType', 'payrollsID="'.$_POST['payrollsID'].'"');	
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
			$data['totalNet'] = $this->dbmodel->getSingleField('tcPayslips', 'SUM(net)', 'payrollsID_fk="'.$data['info']->payrollsID.'" AND pstatus = 1 ');
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function regeneratepayslip(){
		if($this->user!=false){
			$id = $this->uri->segment(3);
			$info = array();
			$query = $this->dbmodel->getSingleInfo('tcPayslips', 'empID_fk, fname, email, payPeriodEnd, payPeriodStart, payrollsID, payType, status', 'payslipID="'.$id.'"', 'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk LEFT JOIN staffs ON empID=empID_fk');
			if(count($query)>0){
				foreach($query AS $k=>$v) $info[$k] = $v;
				$info['empIDs'] = $query->empID_fk;
				$this->payrollM->generatepayroll($info);
				$period = date('F d, Y', strtotime($query->payPeriodStart)).' - '.date('F d, Y', strtotime($query->payPeriodEnd));
				if($query->status==1){
					//$this->emailM->sendPublishPayrollEmail($period, 'accounting.cebu@tatepublishing.net', $query->fname, 1); //disable regenerate payslip
					$this->emailM->sendPublishPayrollEmail($period, $query->email, $query->fname, 1);
				}
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
				$data['dataUnpublished'] = $this->dbmodel->getQueryResults('tcStaffLogPublish', 
					'slogID, slogDate, schedIn, schedOut, timeIn, timeOut, timeBreak, empID_fk, CONCAT(fname," ",lname) AS name, username', 
					'publishBy="" AND slogDate!="'.$data['currentDate'].'" AND showStatus=1', 
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
					//notes
					$this->commonM->addMyNotif($_POST['empID_fk'], 'Last pay payslip has been removed.', 1, 0, $this->user->empID);
					//end notes
					exit;
				}else if($_POST['submitType']=='savecomputation'){ //saving computation				
					unset($_POST['submitType']);						
					foreach($_POST AS $k=>$p){
						$_POST[$k] = str_replace(',','',$p);
					}
					
					if(!empty($_POST['addOns'])) $_POST['addOns'] = addslashes(serialize($_POST['addOns']));
					else $_POST['addOns']='';
					
					if(!empty($_POST['addDeductions'])) $_POST['addDeductions'] = addslashes(serialize($_POST['addDeductions']));
					else $_POST['addDeductions']='';
					
					$_POST['generatedBy'] = $this->user->username;
					$_POST['dateGenerated'] = date('Y-m-d H:i:s');
																				
					$lastpayID = $this->dbmodel->getSingleField('tcLastPay', 'lastpayID', 'empID_fk="'.$_POST['empID_fk'].'"');
					if(!empty($lastpayID)){
						$this->dbmodel->updateQuery('tcLastPay', array('lastpayID'=>$lastpayID), $_POST);
					}else{
						$_POST['status'] = 1;
						$lastpayID = $this->dbmodel->insertQuery('tcLastPay', $_POST);
					}
					
					//notes
					$this->commonM->addMyNotif($_POST['empID_fk'], 'Last pay computation has been updated.', 1, 1, $this->user->empID);
					//end notes

					echo $lastpayID;
					exit;
				}
			}
							
			$data['pageType'] = 'showperiod';
			if(isset($_GET['payID'])){
				//FOR BIR Purposes
				if(isset($_GET['empID'])){
					$empID = $_GET['empID'];
				}
				if(isset($_GET['e']) AND $_GET['e'] == 'upload'){					
					$data['content'] = 'v_timecard/v_uploadlastpay';
				} else if( isset($_GET['e']) AND $_GET['e'] == 'scheddate' ){
					$data['content'] = 'v_timecard/v_updatelastpay';
					$data['which_to'] = $_GET['e'];
				} else if( isset($_GET['e']) AND $_GET['e'] == 'checkno' ){
					$data['content'] = 'v_timecard/v_updatelastpay';
					$data['which_to'] = $_GET['e'];
				}


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
			
			$data['staffInfo']	= $this->dbmodel->getSingleInfo('staffs', ' CONCAT(lname, ", ", fname, " ", mname) AS fullName, address, zip, empID, username, tin, idNum, fname, lname, bdate, startDate, active, endDate, taxstatus, sal, leaveCredits', 'empID="'.((isset($empID))?$empID:'').'"','LEFT JOIN taxStatusExemption ON taxstatus = taxStatus_fk');
			
			//compute leaveCredits
			$data['staffInfo']->originalLeaveCredits = $data['staffInfo']->leaveCredits;
			$data['staffInfo']->leaveCredits = $this->commonM->computeLastLeave( $data['staffInfo']->startDate, $data['staffInfo']->leaveCredits );
			
			if(count($data['staffInfo'])==0) $data['access'] = false;
			
			if(isset($_GET['periodFrom']) && isset($_GET['periodTo'])){
				$data['pageType'] = 'hideperiod';
				$data['periodFrom'] = $_GET['periodFrom'];
				$data['periodTo'] = $_GET['periodTo'];
			}
			
			if(isset($data['periodFrom']) && isset($data['periodTo'])){	
				$datum = $this->payrollM->getPayslipOnTimeRange($empID, $data['periodFrom'], $data['periodTo'], TRUE);
				
				$data['dateArr'] = $datum['dateArr'];
				$data['dataMonth'] = $datum['dataMonth'];
				$data['dataMonthItems'] = $datum['dataMonthItems'];
				$data['allowances'] = $datum['allowances'];
			}

			///THIS IS FOR THE PDF
			if(isset($_GET['show'])){
					
				$bdate = date('ymd', strtotime($data['staffInfo']->bdate));
				if($_GET['show']=='pdf' && $this->access->accessFullHRFinance==false){
					$acc = true;
					switch( $_GET['which_pdf'] ){
						case 'view': $url_string = 'timecard/computelastpay/?payID='.$_GET['payID']; break;
						case 'release': $url_string = 'timecard/computelastpay/?empID='.$_GET['empID']; break;
						case 'coe': $url_string = 'timecard/computelastpay/?empID='.$_GET['empID']; break;
						case 'bir': $url_string = 'timecard/computelastpay/?empID='.$_GET['empID']; break;
					}
					echo '<script>';
						echo 'var pass = prompt("This document is password protected. Please enter a password.", "");';
						echo 'if(pass=="'.$bdate.'"){
							window.location.href="'.$this->config->base_url().$url_string.'&show='.$this->textM->encryptText($bdate).'&which_pdf='.$_GET['which_pdf'].'";
						}else{ 
							alert("Failed to load document. Invalid password.");';
							$acc = false;
						echo '}';
					echo '</script>';
					
					if($acc==false) $data['access'] = false;
					else exit;
				}else{
					if(/*$bdate==$this->textM->decryptText($_GET['show'])*/$_GET['show']=='pdf' || $this->access->accessFullHRFinance==true){
					
						if( isset($_GET['empID']) AND !empty($_GET['empID']) ){
							$staff_details = $this->dbmodel->getSingleInfo('staffs', 'empID, CONCAT(fname, " ",lname) AS name,tin, CONCAT(fname, " ", mname, " ",lname) AS full_name, newPositions.title, startDate, endDate, sal AS salary, allowance, empStatus', 'empID="'.$_GET['empID'].'"', 'LEFT JOIN newPositions ON posID=position');
						}
						
						switch( $_GET['which_pdf'] ){
							case 'view': 								
								$this->payrollM->pdfLastPay($data);									
							break;
							case 'release': //release waiver and quitclam
								$staff_details->amount_in_words = $this->textM->convert_number_to_words($data['payInfo']->netLastPay);
								$staff_details->amount_in_figure = $this->textM->convertNumFormat($data['payInfo']->netLastPay);
								ob_clean();
								$this->payrollM->pdfReleaseClaim($staff_details);
								exit();
							break;
							case 'bir':
							 		//$this->textM->aaa($data);
									//for active employee
									if( isset($_GET['is_active']) ){
										$sDate = $data['staffInfo']->startDate;
										// if( $sDate < '2016-01-01' )
										// 	$sDate = '2016-01-01';
										$activeQuery = array();
										$datum = $this->payrollM->getPayslipOnTimeRange($empID, date('Y-01-01'), date('Y-12-31'),TRUE );

										$data['staffInfo']->endDate = date('Y-12-31');
										$activeQuery['dataQuery'][0] = $data['staffInfo'];
										$activeQuery['dataQuery'][0]->dateArr = $datum['dateArr'];
										$activeQuery['dataQuery'][0]->dataMonth = $datum['dataMonth'];
										$activeQuery['dataQuery'][0]->dataMonthItems = $datum['dataMonthItems'];
										$activeQuery['dataQuery'][0]->allowances = $datum['allowances'];

										// $data['dateArr'] = $datum['dateArr'];
										// $data['dataMonth'] = $datum['dataMonth'];
										// $data['dataMonthItems'] = $datum['dataMonthItems'];
										// $data['staffInfo']->endDate = date('Y-12-31');
										// $data['is_active'] = TRUE;

										$this->payrollM->pdfActiveBIR($activeQuery['dataQuery']);
									}
									// echo "<pre>";
									// var_dump($data);
									// echo "</pre>";
									else{
							 			$this->payrollM->pdfBIR($data);
							 		}
							break;
							case 'coe': //coe
								//call staff details and use staffmodel->genCOEpdf()								
								$staff_details->dateissued = date('F d, Y');
								$staff_details->purpose = 'End of Employment';
								$staff_details->last = true;
								$staff_details->coeID = $staff_details->empID;
								$this->staffM->genCOEpdfLast($staff_details);
							break;
						}
					} else {
						$data['access'] = false;
					}
				}									
			}
		}
		
		//$this->textM->aaa($_POST);
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function alphalist(){
		$data['content'] = 'v_timecard/v_alphalist';
		if( $this->user != FALSE ){
			if( $this->access->accessFullFinance == FALSE ) {
				$data['access'] = false;
			} else {
				$data['which'] = ( $_GET['which'] == 'end' ) ? 'separated' : 'active'; //possible value is end or start | end means separate employees | start means active
				$data['monthFullArray'] = $this->textM->constantArr('monthFullArray');
				$data['yearFullArray'] = $this->textM->constantArr('yearFullArray');				
				foreach( $_POST as $key => $val ){
					$data[ $key ] = $val;
				}
				

				//post
				if( isset($_POST) AND !empty($_POST) ){
					
					if( isset($_POST['which_report']) AND $_POST['which_report'] == 'gen_alphalist' ){
						$from_ = date('Y-m-d', strtotime( $data['from_year'].'-'.$data['from_month'].'-01'  ) );
						$to_ = date('Y-m-d', strtotime( $data['to_year'].'-'.$data['to_month'].'-31' ) );
						//initialize endDate
						//if employee is active, end date will be the same as $to_
						//else endDate will be their separation date: (initialize it to 0000-00-00)
						$endDate = '0000-00-00';
						$is_active = FALSE;


						$data = array();
						//check which to pull from
						switch( $_POST['which_from'] ){
							case 'separated': $data['dataQuery'] = $this->dbmodel->getQueryResults('tcLastPay', 'tcLastPay.*, empID, idNum, fname, lname, username, startDate, endDate, sal, tin', '1', 'LEFT JOIN staffs ON empID=empID_fk','lname ASC'); break;
							case 'active': $data['dataQuery'] = $this->dbmodel->getQueryResults('staffs', '*', 'active = 1 AND office = "PH-Cebu"', 'LEFT JOIN taxStatusExemption ON taxstatus = taxStatus_fk' , 'lname ASC');
								$endDate = $to_;
								$is_active = TRUE;
							break;
						}
						//$this->textM->aaa($data['dataQuery']);						
						foreach( $data['dataQuery'] as $key => $val ){
							$datum = $this->payrollM->getPayslipOnTimeRange($val->empID, $from_, $to_, TRUE);
							$data['dataQuery'][$key]->dateArr = $datum['dateArr'];
							$data['dataQuery'][$key]->dataMonth = $datum['dataMonth'];
							$data['dataQuery'][$key]->dataMonthItems = $datum['dataMonthItems'];
							$data['dataQuery'][$key]->allowances = $datum['allowances'];
						}

						//$this->textM->aaa($data);
						$filename = 'alphalist_'.$from_.'-'.$to_.'-'.$data['which'];

						if( !$is_active ){
							$this->payrollM->getAlphaList( $data['dataQuery'], $filename, $from_, $endDate, $is_active);
						}else{
							$this->payrollM->getAlphaListForAllEmployee( $data['dataQuery'], $filename);
						}
					}
				}
			}
		}
		//$this->output->enable_profiler(true);
		//$this->textM->aaa($data);
		$this->load->view('includes/template', $data);
	}

	public function uploadlastpay(){
		$data['content'] = 'v_timecard/v_uploadlastpay';
		//upload files
		if( $this->input->post('lastPayID') ){

			$last_pay_info = $this->dbmodel->getSingleInfo('tcLastPay', 'tcLastPay.*, idNum, fname, lname, username, startDate, endDate, sal', 'lastpayID ='. $this->input->post('lastPayID'), 'LEFT JOIN staffs ON empID=empID_fk' );
			

			if( isset($_FILES) AND !empty($_FILES) ){
				$files = $_FILES;					
				/* config data */
				$upload_config['upload_path'] = FCPATH .'uploads/lastpay_docs';
				$upload_config['allowed_types'] = 'gif|jpg|png|pdf';
				$upload_config['max_size']	= '2048';
				$upload_config['overwrite']	= 'FALSE';						
				$this->load->library('upload');

				//check how many to upload
				$upload_count = count( $_FILES['lastpay_doc']['name'] );
				for( $x = 0; $x < $upload_count; $x++ ){
					$_FILES['to_upload']['name'] = $files['lastpay_doc']['name'][$x];
					$_FILES['to_upload']['type'] = $files['lastpay_doc']['type'][$x];
					$_FILES['to_upload']['tmp_name'] = $files['lastpay_doc']['tmp_name'][$x];
					$_FILES['to_upload']['error'] = $files['lastpay_doc']['error'][$x];
					$_FILES['to_upload']['size'] = $files['lastpay_doc']['size'][$x];
					
					$ext = pathinfo( $_FILES['to_upload']['name'], PATHINFO_EXTENSION );
					$uniq_id = uniqid();
					$upload_config['file_name'] = $last_pay_info->empID_fk .'_'. $uniq_id .'_'. $x .'.'.$ext;
					
					$this->upload->initialize( $upload_config );
					
					if( ! $this->upload->do_upload('to_upload') ){
						$error_data[$x] = $this->upload->display_errors('<span>', '</span>');
					} else {
						$upload_data[$x] = $this->upload->data();
					}
				}
			}
			//save data
			if( isset($error_data) ){
				unset($data['upload_data']);
				$data['upload_error'] = implode('<br/>', $error_data);				
			} else if( isset($upload_data)  ){

				foreach( $upload_data as $data_upload ){
					$filenames[] = $data_upload['file_name'];
				}

				$filename = json_encode($filenames);
				$update_array['docs'] = $filename;
				$update_array['status'] = 4;
				if( $last_pay_info->status >= 4 ){
					unset($update_array['status']);
				}

				$this->dbmodel->updateQuery('tcLastPay', ['lastpayID' => $this->input->post('lastPayID')], $update_array);

				//notes
				$this->commonM->addMyNotif($last_pay_info->empID_fk, 'Last pay computation has been updated to `Released`.', 1, 1, $this->user->empID);
				//end notes

				$data['upload_error'] = 'Document has been uploaded.';
				$data['js'] = true;
			} 
			
		}
		//end upload files
		
		$this->load->view('includes/templatecolorbox', $data);		
	}

	public function managelastpay(){
		$data['content'] = 'v_timecard/v_managelastpay';
		$data['dataTableProperties'] = '{columnDefs:[{ targets: 6,  orderDataType: "dom-select"}, { targets: 3, orderDataType: "dom-text"}, {targets: 4, orderDataType: "dom-text"}]}';
		
		$data['status_labels'] = $this->textM->constantArr('last_pay_status');

		
		if($this->user!=false){
			if($this->access->accessFullHRFinance==false) $data['access'] = false;
			else{
				$data['error_data'] = '';
				$condition = '1';
				if( $this->input->post() ){

					$last_pay_info = $this->dbmodel->getSingleInfo('tcLastPay', 'tcLastPay.*, idNum, fname, lname, username, startDate, endDate, sal', 'lastpayID ='. $this->input->post('id'), 'LEFT JOIN staffs ON empID=empID_fk' );
					

					if( !empty($this->input->post('scheddate') ) ){
						$which_update = 'Release Date';
						$update_array = array('releasedDate' => date('Y-m-d H:i:s', strtotime($this->input->post('scheddate'))), 'status' => 1 );

						//unset status if backtracked
						if( $last_pay_info->status >= 1 ){
							unset($update_array['status']);
						}
						$note = 'Schedule of release date for the last pay has updated. <br/>Release date: '. date('Y-m-d', strtotime($this->input->post('scheddate') ) );
					} else if( !empty($this->input->post('checkno') ) ){
						$which_update = 'Check No';
						$update_array = array('checkNo' => $this->input->post('checkno'), 'status' => 3 );

						//unset status if backtracked but 5 is greater than 3
						if( $last_pay_info->status == 4 ){
							unset($update_array['status']);
						}


						$note = 'Check number for the last pay has updated. <br/>Check number: '. $this->input->post('checkno');
					} else {
						$update_array = array('status' => $this->input->post('status') );						
					}
					//update					
					$this->dbmodel->updateQuery('tcLastPay', 'lastpayID ='. $this->input->post('id'), $update_array );						


					//notes
					if( isset($note) ){
						$this->commonM->addMyNotif( $last_pay_info->empID_fk, $note, 1, 1, $this->user->empID );
					}

					if( isset($update_array['status']) AND !empty($update_array['status']) ){
						$this->commonM->addMyNotif($last_pay_info->empID_fk, 'Last pay computation has been updated to `'. $data['status_labels'][ $update_array['status'] ].'`.', 1, 1, $this->user->empID);
						//end notes
					}

					
					if( $this->input->is_ajax_request() ){
						echo json_encode([true]);	
						exit();
					} else {
						$data['success'] = $which_update;
						$data['content'] = 'v_timecard/v_updatelastpay';
					}
					
					$this->load->view('includes/templatecolorbox', $data);
					exit();
				}

				
				if( $this->input->post('date_range') ) {
					if( !empty($this->input->post('dateFrom')) AND !empty($this->input->post('dateTo')) ){
						$dateFrom = date('Y-m-d', strtotime( $this->input->post('dateFrom')) );
						$dateTo = date('Y-m-d', strtotime( $this->input->post('dateTo')) );
						$condition = '(dateGenerated BETWEEN "'.$dateFrom.'" AND "'.$dateTo.'")';
					} else {
						$data['error_data'] = '<span class="error">Please specify date range.</span>';
					}
				}

				$data['dataQuery'] = $this->dbmodel->getQueryResults('tcLastPay', 'tcLastPay.*, idNum, fname, lname, username, startDate, endDate, sal', $condition, 'LEFT JOIN staffs ON empID=empID_fk', 'dateGenerated DESC');				
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
				if( isset($_POST['delete_13th_record']) AND $_POST['delete_13th_record'] == 'Delete' ){
					$del_id = $_POST['id_'];
					$this->db->delete('tc13thMonth', array('tcmonthID' => $del_id) );
					$atext = 'Deleted table: tc13thMonth row: tcmonthID-'.$del_id;
					$this->commonM->addMyNotif($this->user->empID, $atext, 5);
				}
				$data['queryData'] = $this->dbmodel->getQueryResults('tc13thMonth', 'tc13thMonth.*, fname, lname, startDate, endDate', '1', 'LEFT JOIN staffs ON empID=empID_fk', 'dateGenerated, lname');
			}			
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function detail13thmonth($data){
		$data['content'] = 'v_timecard/v_13thmonthdetail';
		
		if($this->user!=false){
			if( isset($_GET['show']) AND $_GET['show'] == 'distro' AND $this->access->accessFullHRFinance==true ){
				$this->distro13thMonth();
				exit();
			} else {
						
				$id = $this->uri->segment(3);
				$data['dataInfo'] = $this->dbmodel->getSingleInfo('tc13thMonth', 'tc13thMonth.*, fname, lname, idNum, title, bdate', 'tcmonthID="'.$id.'"', 
					'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position');
				if(count($data['dataInfo'])==0) $data['access'] = false;
				else{
					$data['dataMonth'] = $this->payrollM->query13thMonth($data['dataInfo']->empID_fk, $data['dataInfo']->periodFrom, $data['dataInfo']->periodTo, $data['dataInfo']->includeEndMonth);
					
					///THIS IS FOR THE PDF
					if(isset($_GET['show'])){
						$bdate = date('ymd', strtotime($data['dataInfo']->bdate));
						if( isset($_GET['show']) AND $_GET['show'] == 'pdf' && $this->access->accessFullHRFinance==false){
							$which = $_GET['show'];
							$acc = true;
							echo '<script>';
								echo 'var pass = prompt("This document is password protected. Please enter a password.", "");';
								echo 'if(pass=="'.$bdate.'"){
									window.location.href="'.$this->config->base_url().'timecard/detail13thmonth/'.$id.'/?show='.$this->textM->encryptText($bdate).'&which='.$which.'";
								}else{ 
									alert("Failed to load document. Invalid password.");';
									$acc = false;
								echo '}';
							echo '</script>';
							if($acc==false) $data['access'] = false;
							else exit;
						}else{
							if ($bdate==$this->textM->decryptText($_GET['show']) || $this->access->accessFullHRFinance==true){
								$this->payrollM->pdf13thMonth($data['dataInfo'], $data['dataMonth']); break;												
								exit;
							}else{
								$data['access'] = false;
							}
						}									
					}
				}
			}
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	//distro for 13thmonth
	public function distro13thMonth(){
		//$tcmonthID = $_GET['tcid'];
				
		if($this->user==false || $this->access->accessFullFinance==false ){
			$data['access'] = false;
			$data['content'] = 'index';
			$this->load->view('includes/template', $data);
		}else{
			require_once('includes/excel/PHPExcel/IOFactory.php');
			$fileType = 'Excel5';
			$fileName = 'includes/templates/13th_month_report.xls';

			// Read the file
			$objReader = PHPExcel_IOFactory::createReader($fileType);
			$objPHPExcel = $objReader->load($fileName);
			
			/*$arrCell = array();			
			foreach (range('A', 'Z') as $char) array_push($arrCell, $char);
			foreach (range('A', 'Q') as $char) array_push($arrCell, 'A'.$char);*/
			
			$tcmonth_details = $this->dbmodel->getQueryArrayResults('tc13thMonth', 'tc13thMonth.*, CONCAT(fname," ", lname) AS "full_name"', '1', 'LEFT JOIN staffs ON empID = empID_fk');
									
			
			
			// Change the file
			$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A1', '13th MONTH DISTRIBUTION REPORT');
			$counter = 3;		
			foreach( $tcmonth_details as $tcpay ){
				$period_text = date('M', strtotime($tcpay->periodFrom)).' - '.date('M Y', strtotime($tcpay->periodTo) );
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$counter, $tcpay->empID_fk);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$counter, $tcpay->full_name);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$counter, $period_text);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$counter, $tcpay->totalBasic);
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$counter, $tcpay->totalDeduction);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$counter, $tcpay->totalAmount);
				$counter++;
			}
			
			
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
			ob_end_clean();
			// We'll be outputting an excel file
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="13th_Month_Distribution_Report.xls"');
			$objWriter->save('php://output');			
		}	
	} //end distro
	
	public function payrolldistributionreport(){
		$payrollsID = $this->uri->segment(3);
		$payslip_items = $this->dbmodel->getQueryResults('tcPayslipItems', '*, 1 AS isMain, payAmount AS prevAmount', '1', '', 'payCategory, payName ASC, isMain DESC');

		foreach( $payslip_items as $item ){
			$payslip_items_array[ $item->payID ] = $item;
		}
		$num_paylip_items = count( $payslip_items_array );
		//$this->textM->aaa($payslip_items_array, false);
		if($this->user==false || $this->access->accessFullFinance==false || !is_numeric($payrollsID)){
			$data['access'] = false;
			$data['content'] = 'index';
			$this->load->view('includes/template', $data);
		}else{
			$dataPayroll = $this->dbmodel->getSingleInfo('tcPayrolls', '*', 'payrollsID="'.$payrollsID.'"');
									
			$payInfo = $this->dbmodel->getQueryResults('tcPayslips', 
						'fname, lname, tcPayslips.empID_fk AS "empID_fk", lastPayID, endDate, idNum, payslipID, payrollsID, empID, tcPayslips.monthlyRate, basePay, earning, bonus, tcPayslips.allowance, adjustment, deduction, totalTaxable, net, payPeriodStart, payPeriodEnd, payType, payDate, startDate, bdate, title, tcPayrolls.status, levelName, staffHolidaySched, employerShare, eCompensation, payPeriodStart as "payroll_start", payPeriodEnd AS "payroll_end", dateFrom as "lastpay_start", dateTo as "lastpay_start"', 
						'payrollsID="'.$payrollsID.'" AND pstatus=1', 
						'LEFT JOIN tcPayrolls ON payrollsID=payrollsID_fk 
						LEFT JOIN staffs ON empID=empID_fk
						LEFT JOIN tcLastPay ON tcLastPay.empID_fk =  tcPayslips.empID_fk
						LEFT JOIN newPositions ON posID=position 
						LEFT JOIN orgLevel ON levelID=orgLevel_fk
						LEFT JOIN sssTable ON tcPayslips.monthlyRate BETWEEN minRange AND maxRange', 
						'lname');

			

			//get the separated employee to be used at the second tab
			$separated_employee = array();
			$payInfo_ = array();
			foreach( $payInfo as $pay_ ){			
				if( ( strcmp($pay_->endDate,'0000-00-00') !== 0 ) AND !empty($pay_->lastPayID) AND ( $pay_->endDate >= $pay_->payroll_start AND $pay_->endDate <= $pay_->payroll_end ) ){
					$separated_employee[ $pay_->empID_fk ] = $pay_;
				} else {
					$payInfo_[] = $pay_;
				}
			}	
			$payInfo = $payInfo_;

			require_once('includes/excel/PHPExcel/IOFactory.php');
			$fileType = 'Excel5';
			$fileName = 'includes/templates/payrollDistributionReport__.xls';

			// Read the file
			$objReader = PHPExcel_IOFactory::createReader($fileType);
			$objPHPExcel = $objReader->load($fileName);
			
			//based on template
			$column_start = 'C';
			$row_start = 3;

			
			// Change the file
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'PAYROLL DISTRIBUTION REPORT - '.date('F d, Y', strtotime($dataPayroll->payPeriodStart)).' - '.date('F d, Y', strtotime($dataPayroll->payPeriodEnd)));
			
			//headers based on payslip items details
			$header_array_sequence = $header_col =  array( 
				'12' => 'Regular Taken',
				'12HR' => 'Regular Taken (hours) ',
				'22' => 'Regular Worked',
				'22HR' => 'Regular Worked (hours)',
				'28' => 'Regular Hours Added',
				'28HR' => 'Regular Hours Added (hours)',
				'37' => 'Regular Hours Deducted',
				'37HR' => 'Regular Hours Deducted (hours)',
				'basePay' => 'Base Pay',
				'10' => 'ND:Regular',
				'10HR' => 'ND:Regular (hours)',
				'29' => 'Night Differentail Added',
				'29HR' => 'Night Differential (hours)',
				'35' => 'ND:Regular PHL Holiday',
				'35HR' => 'ND:Regular PHL Holiday (hours)',
				'34' => 'ND:Special PHL Holiday',
				'34HR' => 'ND:Special PHL Holiday (hours)',
				'25' => 'Regular PHL Holiday Worked',
				'25HR' => 'Regular PHL Holiday Worked (hours)',
				'24' => 'Special PHL Holiday Worked',
				'24HR' => 'Special PHL Holiday Worked (hours)',
				'26' => 'Regular US Holiday Worked',
				'26HR' => 'Regular US Holiday Worked (hours)',
				'27' => 'Regular Holiday Premium',
				'27HR' => 'Regular Holiday Premium (hours)',
				'11' => 'Over Time Worked',
				'11HR' => 'Over Time Worked (hours)',
				'42' => 'OT Hours Added',
				'42HR' => 'OT Hours Added (hours)',
				'41' => 'Job Position Adjustment',
				'16' => 'Performance Incentive',
				'earning' => 'Gross Pay',
				'9' => 'Pag-ibig Contribution',
				'8' => 'SSS Contribution',
				'7' => 'Philhealth Contribution',
				'totalTaxable' => 'Taxable Pay',
				'33' => 'Medicine Reimbursement',
				'2' => 'Clothing Allowance',
				'3' => 'Laundry Allowance',
				'4' => 'Meal Allowance',
				'5' => 'Medical Cash Allowance',
				'1' => 'Rice Allowances',
				'44' => 'Pro-rated Allowance',
				'47' => 'Training Allowance',
				'14' => 'Performance Bonus',
				'15' => 'Kudos Bonus',
				'31' => 'Discrepancy on Previous Bonus',
				'21' => 'Vacation Pay',
				'30' => 'Cost of Vaccines',
				'46' => 'Refund on Cost of Vaccines',
				'43' => 'Tax Refund',
				'20' => 'Tax Deficit',
				'18' => 'Sun Life',
				'17' => 'Healthcare Dependent/s',
				'19' => 'Pag-ibig Loan',
				'13' => 'SSS Loan',
				'39' => 'Loan Adjustment',
				'40' => 'Payslip Adjustment',
				'36' => '13th Month Adjustment',
				'38' => 'Cost of Community Tax Certificate',
				'45' => 'One Plus Shop',
				'48' => 'ID Replacement',
				'6' => 'BIR',
				'net' => 'Net Pay',
				'net_' => 'Cheque Payroll',
				'earning_' => 'Gross Compensation',
				'32' => 'Additional Pag-ibig Contribution',
				'eCompensation' => 'SSS Employer Compensation',
				'employerShare' => 'SSS Employer Share');
			foreach( $header_array_sequence as $key => $label ){
				$objPHPExcel->getActiveSheet()->setCellValue($column_start.'2', $label);
				$objPHPExcel->getActiveSheet()->getStyle($column_start.'2')->getFont()->setBold(true);	
				$objPHPExcel->getActiveSheet()->getColumnDimension($column_start)->setWidth(15);
				$column_start++;
			}
			$objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(55);
			
			//unset which isn't needed
			$data_from_payInfo = array('name', 'id_num', 'net', 'earning', 'eCompensation', 'employerShare', 'totalTaxable', 'basePay');
			foreach( $data_from_payInfo as $val ){
				unset( $header_array_sequence[ $val ] );
			}
			//end unset

			$data_excel = array();
			foreach( $payInfo as $pay_info ){
				$data_excel[ $pay_info->empID_fk ] = $this->payrollM->getEmployeePayrollDistro( $pay_info, $header_array_sequence );
			}

			//$this->textM->aaa($data_excel);
			$counter = 3;
			foreach($data_excel AS $data_){
			
				$id_num = sprintf("%03d", $data_['id_num']);
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$counter, $data_['name']); //employee name
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$counter, $id_num); //employee id
				
				$col_header = 'C';
				foreach( $header_col as $keys_ => $vals_ ){
					if( isset($data_[ $keys_ ] ) ){
						$objPHPExcel->getActiveSheet()->setCellValue($col_header.$counter, $data_[ $keys_ ]);						
					} else {
						$objPHPExcel->getActiveSheet()->setCellValue($col_header.$counter, 0);
					}
					$col_header++;
				}
				$counter++;
			}
			
			//TOTALS			
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$counter, 'TOTALS:');
			$last_row = $counter - 1;
			$col_header = 'C';
			foreach( $header_col as $payID => $payDetails ){
				$objPHPExcel->getActiveSheet()->setCellValue($col_header.$counter, '=SUM('.$col_header.'3:'.$col_header.$last_row.')');
				$col_header++;
			}
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.$counter.':'.$col_header.$counter)->getFont()->setBold(true);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$counter.':'.$col_header.$counter)->applyFromArray(
			array('fill' 	=> array(
										'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
										'color'		=> array('argb' => 'FFF000')
									)
				 )
			);

			//header for second page
			$header_array_sequence = $header_col =  array(
				'name' => 'Employee',
				'id_num' => 'Employee ID',
				'12' => 'Regular Taken',
				'22' => 'Regular Worked',				
				'10' => 'ND:Regular',
				'earning' => 'Gross Pay',				
				'9' => 'Pag-ibig Contribution',
				'8' => 'SSS Contribution',
				'7' => 'Philhealth Contribution',
				'totalTaxable' => 'Taxable Pay',
				'33' => 'Medicine Reimbursement',
				'44' => 'Pro-rated Allowance',
				'14' => 'Performance Bonus',
				'15' => 'Kudos Bonus',
				'31' => 'Discrepancy on Previous Bonus',
				'21' => 'Vacation Pay',
				'13thMonth' => 'Total 13th Month Pay',
				'taxFromPrevious' => 'Tax Refund Previous Year',
				'20' => 'Tax Deficit Previous Year',
				'18' => 'Sun Life',
				'17' => 'Healthcare Dependent/s',
				'47' => 'Training Allowance',
				'19' => 'Pag-ibig Loan',
				'13' => 'SSS Loan',
				'39' => 'Loan Adjustment',
				'40' => 'Payslip Adjustment',
				'36' => '13th Month Adjustment',				
				'30' => 'Cost of Vaccines',
				'46' => 'Refund on Cost of Vaccines',
				'38' => 'Cost of Community Tax Certificate',
				'45' => 'One Plus Shop',
				'48' => 'ID Replacement',
				'taxWithheld' => 'Total Tax Withheld',
				'taxDue' => 'Tax Due',
				'net' => 'Last Pay',
				'net_' => 'Cheque Payroll',
				'eCompensation' => 'SSS Employer Compensation',
				'employerShare' => 'SSS Employer Share');

			//second page
			// Add new sheet
			$objWorkSheet = $objPHPExcel->createSheet(1); //Setting index when creating

			//Write cells
			
			//header
			$objWorkSheet->mergeCells('A1:AL1');
			$objWorkSheet->setCellValue('A1', 'DISTRIBUTION REPORT FOR SEPARATED EMPLOYEE - ' . date('Y') )
				->getStyle('A1')
				->getAlignment()
				->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$objWorkSheet->getStyle('A1')
				->getFont()
				->setBold(true);
			$objWorkSheet->getRowDimension(1)->setRowHeight(28);

			$objWorkSheet->getRowDimension(2)->setRowHeight(28);
			$col_header = 'A';
			foreach( $header_col as $header ){
				$objWorkSheet->setCellValue($col_header.'2', $header);
				$objWorkSheet->getColumnDimension($col_header)->setAutoSize(true);
				$col_header++;
			}
			$objWorkSheet->freezePane('B3');
			//end header

			//body

			//traverse through $separated Employee
			
			$data_excel = array();
			foreach( $separated_employee as $empID => $details ){
				$data_excel[ $empID ] = $this->payrollM->getEmployeePayrollDistro( $details, $header_array_sequence, true );
			};
			
			$row_ = 3;
			foreach( $data_excel as $empID => $details ){
				$col_name = 'A';
				foreach( $header_array_sequence as $key => $val ){
					if( isset($details[ $key ]) ){
						$objWorkSheet->setCellValue($col_name.$row_, $details[ $key ]);	
					} else {
						$objWorkSheet->setCellValue($col_name.$row_, 0);	
					}
					
					$col_name++;
				}
				$row_++;
			}
			//end body

			//footer
			//TOTALS
			$objWorkSheet->setCellValue('A'.$row_, 'TOTALS:');
			$last_row = $row_ - 1;
			$col_header = 'C';
			unset($header_col['name']);
			unset($header_col['id_num']);
			
			if( count($data_excel) > 0 ){
				foreach( $header_col as $payID => $payDetails ){
					$objWorkSheet->setCellValue($col_header.$row_, '=SUM('.$col_header.'3:'.$col_header.$last_row.')');
					$col_header++;
				}	
			}			
			
			
			
			$objWorkSheet->getStyle('A'.$row_.':'.($col_header--).$row_)->getFont()->setBold(true);
			$objWorkSheet->getStyle('A'.$row_.':'.($col_header--).$row_)->applyFromArray(
			array('fill' 	=> array(
										'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
										'color'		=> array('argb' => 'FFF000')
									)
				 )
			);

			//end footer

			
			// Rename sheet
			$objWorkSheet->setTitle("Separated Employee");

			//end second page

							
			//$this->textM->aaa($arrCell);
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $fileType);
			ob_end_clean();
			// We'll be outputting an excel file
			header('Content-type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename="Payroll_Distribution_Report.xls"');
			$objWriter->save('php://output');			
			
		}	
	}
	
		
}
?>
