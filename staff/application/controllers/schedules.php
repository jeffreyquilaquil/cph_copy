<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schedules extends MY_Controller {
 
	public function __construct(){
		parent::__construct();
		$this->load->model('ScheduleModel', 'scheduleM');
		$this->load->model('timecardmodel', 'timeM');		
	} 
	
	public function index(){
		$data['content'] = 'v_schedule/v_schedules';
		$data['column'] = 'withLeft';	
		
		$data['row'] = $this->user;	
		if($this->user!=false){
			if(!empty($_POST)){
				$tbl = '';
				$note = '';
				$addUp = '';
				$insArr = array();
				$upArr = array();
				$where = array();
				
				if($_POST['submitType']=='addtimecategory'){
					$tbl = 'tcCustomSchedTime';
					$insArr['timeName'] = $_POST['name'];
					$insArr['category'] = 0;
					$insArr['addInfo'] = $this->user->empID.'--'.strtotime(date('Y-m-d H:i:s')).'|';
					
					$note = 'You added time category '.$_POST['name'].'.';
				}else if($_POST['submitType']=='addtime'){
					$tbl = 'tcCustomSchedTime';
					$insArr['timeName'] = $_POST['name'];
					$insArr['timeValue'] = $_POST['start'].' - '.$_POST['end'];
					$insArr['timeHours'] = $_POST['timeHours'];
					$insArr['category'] = $_POST['cat'];
					$insArr['addInfo'] = $this->user->empID.'--'.strtotime(date('Y-m-d H:i:s')).'|';
					
					$note = 'You added time category '.$_POST['name'].'.';
				}else if($_POST['submitType']=='addHoliday'){
					unset($_POST['submitType']);
					unset($_POST['holidayID']);
					$tbl = 'staffHolidays';
					$insArr = $_POST;
					$insArr['addInfo'] = $this->user->empID.'--'.strtotime(date('Y-m-d H:i:s')).'|';
					
					$note = 'You added time holiday schedule '.$_POST['holidayName'].'.';	
					
					//update holiday type of tcAttendance if it exist
					$this->dbmodel->updateQueryText('tcAttendance', 'holidayType="'.$_POST['holidayType'].'"', 'dateToday="'.date('Y-').date('m-d', strtotime($_POST['holidayDate'])).'"');
				}else if($_POST['submitType']=='updateHoliday'){
					$tbl = 'staffHolidays';
					$where = array('holidayID'=>$_POST['holidayID']);
					unset($_POST['holidayID']);
					unset($_POST['submitType']);
					$upArr = $_POST;
					
					$addUp = 'addInfo';					
					$note = 'You updated time holiday schedule '.$_POST['holidayName'].'.';	

					//update holiday type of tcAttendance if it exist
					$this->dbmodel->updateQueryText('tcAttendance', 'holidayType="'.$_POST['holidayType'].'"', 'dateToday="'.date('Y-').date('m-d', strtotime($_POST['holidayDate'])).'"');
				}else if($_POST['submitType']=='deleteTime'){
					$tbl = 'tcCustomSchedTime';
					$where = array('timeID'=>$_POST['id']);
					$addUp = 'addInfo';
					$upArr['status'] = 0;
					
					$note = 'You deleted time option '.$_POST['id'].'.';
				}else if($_POST['submitType']=='updateTime'){					
					$tbl = 'tcCustomSchedTime';
					$where = array('timeID'=>$_POST['id']);
					$upArr['timeName'] = $_POST['timeName'];
					$upArr['timeHours'] = $_POST['timeHours'];
					$addUp = 'addInfo';
					
					if(!empty($_POST['start']) && !empty($_POST['end'])){
						$upArr['timeValue'] = $_POST['start'].' - '.$_POST['end'];
						$note = 'You updated time option ID: '.$_POST['id'].' to '.$_POST['start'].' - '.$_POST['end'];
					}else{
						$note = 'You updated time option ID: '.$_POST['id'];
					}										
				}else if($_POST['submitType']=='addCustomSched'){
					unset($_POST['submitType']);
					$tbl = 'tcCustomSched';
					$insArr = $_POST;
					$insArr['createdby'] = $this->user->empID;
					$insArr['datecreated'] = date('Y-m-d H:i:s');
					
					$note = 'You added custom schedule: '.$_POST['schedName'];
				}else if($_POST['submitType']=='updateCustomSched'){					
					$tbl = 'tcCustomSched';
					$where = array('custSchedID'=>$_POST['schedID']);
					unset($_POST['schedID']);
					unset($_POST['submitType']);
					$upArr = $_POST;
					
					$addUp = 'updateData';					
					$note = 'You added custom schedule: '.$_POST['schedName'].'.';
				}else if($_POST['submitType']=='deleteCustomSched'){
					$tbl = 'tcCustomSched';
					$where = array('custSchedID'=>$_POST['id']);
					$upArr['status'] = 2;
					
					$addUp = 'updateData';					
					$note = 'You deleted custom schedule '.$_POST['id'].'.';
				}else if($_POST['submitType']=='getHolidayData'){
					$beau = $this->dbmodel->getSingleInfo('staffHolidays', 'holidayName, holidayType, phWork, usWork, holidayDate, holidayPremium', 'holidayID="'.$_POST['id'].'"');
					foreach($beau AS $k){
						echo $k.'|';
					}
					exit;
				}else if($_POST['submitType']=='updateSettings'){
					unset($_POST['submitType']);
					$tbl = 'staffSettings';
					$where = array('settingID'=>$_POST['id']);
					$upArr['settingVal'] = $_POST['setVal'];
				}
				
				//update and insert defined above
				if(!empty($note)) $this->commonM->addMyNotif($this->user->empID,$note, 5);
				if(!empty($tbl) && count($insArr)>0) $this->dbmodel->insertQuery($tbl, $insArr);
				if(!empty($tbl) && count($upArr)>0 && count($where)>0) $this->dbmodel->updateQuery($tbl, $where, $upArr);
				reset($where);
				if(!empty($tbl) && count($where)>0 && !empty($addUp)){
					$this->dbmodel->updateConcat($tbl, key($where).'="'.$where[key($where)].'"', $addUp, $this->user->empID.'--'.strtotime(date('Y-m-d H:i:s')).'|');
				}
				exit;
			}
			$data['settingsQuery'] = $this->dbmodel->getQueryResults('staffSettings', '*');
			$data['timecategory'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', '*', 'category=0 AND status=1');		
			$data['alltime'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', '*', 'status=1', '', 'timeValue');
			$data['allCustomSched'] = $this->dbmodel->getQueryResults('tcCustomSched', '*', 'status!=2');
			$data['holidaySchedArr'] = $this->dbmodel->getQueryResults('staffHolidays', 'holidayID, holidayName, holidayType, holidaySched, phWork, usWork, holidayPremium, CONCAT("'.date('Y').'", SUBSTRING( holidayDate,5)) AS holidayDate', 'holidaySched=0 OR (holidaySched=1 AND holidayDate LIKE "'.date('Y').'%")', '', 'holidayDate');
			
			$data['schedTypeArr'] = $this->textM->constantArr('schedType');
			$data['allDayTypes'] = $this->textM->constantArr('holidayTypes');	
			$data['weekdayArray'] = $this->textM->constantArr('weekdayArray');	
			$data['timeArr'] = $this->commonM->getSchedTimeArray(); 
		}
		$this->load->view('includes/template', $data);	
	}

	public function holidayevents(){
		$data['content'] = 'v_schedule/v_holiday';
		$data['allDayTypes'] = $this->textM->constantArr('holidayTypes');
		
		$sday = $this->uri->segment(3);
		if(!empty($sday)){
			$exday = explode('-',$sday);
			$data['mmnum'] = (int)$exday[0];
			$data['mmday'] = $exday[1];		
		}		

		$this->load->view('includes/templatecolorbox', $data);	
	}
	
	public function setschedule(){
		$data['content'] = 'v_schedule/v_setschedule';
		$data['tpage'] = 'setschedule';	
		
		$data['row'] = $this->dbmodel->getSingleInfo('staffs','empID,username, fname, CONCAT(fname," ",lname) AS name', 'empID="'.$this->uri->segment(3).'"');
		
		if($this->user!=false && $this->access->accessFullHR==true && count($data['row'])>0){	
			if(!empty($_POST)){
				if(!empty($_POST['customSched'])){
					$cSched = explode(',', $_POST['customSched']);
					$insCust['schedName'] = 'Custom Schedule of '.$data['row']->username;
					$insCust['sunday'] = $cSched[0];
					$insCust['monday'] = $cSched[1];
					$insCust['tuesday'] = $cSched[2];
					$insCust['wednesday'] = $cSched[3];
					$insCust['thursday'] = $cSched[4];
					$insCust['friday'] = $cSched[5];
					$insCust['saturday'] = $cSched[6];
					$insCust['status'] = 0;
					$insCust['forEmp'] = '++'.$data['row']->empID.'++';
					$insCust['createdby'] = $this->user->empID;
					$insCust['datecreated'] = date('Y-m-d H:i:s');
					$insArr['tcCustomSched_fk'] = $this->dbmodel->insertQuery('tcCustomSched', $insCust);					
				}else{
					$insArr['tcCustomSched_fk'] = $_POST['schedTemplate'];
				}
				
				$insArr['empID_fk'] = $data['row']->empID;
				$insArr['workhome'] = $_POST['workhome'];
				$insArr['effectivestart'] = date('Y-m-d', strtotime($_POST['startDate']));
				if(!empty($_POST['endDate']))
					$insArr['effectiveend'] = date('Y-m-d', strtotime($_POST['endDate']));
				$insArr['assignby'] = $this->user->empID;
				$insArr['assigndate'] = date('Y-m-d H:i:s');
				
				$this->dbmodel->insertQuery('tcStaffSchedules', $insArr);
				
				if(isset($_POST['schedID'])){					
					$this->dbmodel->updateQuery('tcStaffSchedules', array('schedID'=>$_POST['schedID']), array('effectiveend'=>date('Y-m-d', strtotime($_POST['startDate'].' -1 day'))));
				}
				
				echo '<script>
						alert("Schedule has been added");
						parent.$.fn.colorbox.close();
						parent.window.location.reload();
					</script>';
				
				exit;
			}		
			
			$data['stime'] = $this->commonM->getSchedTimeArray();
			$data['timeArr'] = $this->commonM->customTimeArrayByCat();
			$data['schedTemplates'] = $this->dbmodel->getQueryResults('tcCustomSched', '*', 'status=1 OR (status=0 AND forEmp LIKE "%++'.$data['row']->empID.'++%")');
			$data['currentSched'] = $this->dbmodel->getSingleInfo('tcStaffSchedules', 'schedID, empID_fk, tcCustomSched_fk, effectivestart, effectiveend, sunday, monday, tuesday, wednesday, thursday, friday, saturday', 'empID_fk="'.$data['row']->empID.'" AND effectiveend="0000-00-00"', 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk');		
		}else{
			$data['access'] = false;
		}
	
		$this->load->view('includes/templatecolorbox', $data);
	}
		
	public function editschedule(){
		$data['content'] = 'v_schedule/v_editschedule';
		$data['tpage'] = 'editschedule';
				
		if($this->user!=false && $this->access->accessFullHR==true){	
			if(!empty($_POST)){			
				$this->dbmodel->updateQuery('tcStaffSchedules', array('schedID'=>$_POST['schedID']), array('effectiveend'=>date('Y-m-d', strtotime($_POST['enddate']))));
				echo '<script>
					alert("Schedule has been updated");
					parent.$.fn.colorbox.close();
					parent.window.location.reload();
				</script>';
				exit;
			}		
			$data['row'] = $this->dbmodel->getSingleInfo('tcStaffSchedules', 'schedID, empID_fk, CONCAT(fname," ",lname) AS name, tcCustomSched_fk, effectivestart, effectiveend, sunday, monday, tuesday, wednesday, thursday, friday, saturday', 'schedID="'.$this->uri->segment(3).'" AND effectiveend="0000-00-00"', 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk LEFT JOIN staffs ON empID_fk=empID');	
			$data['schedTimes'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', '*', 1, '', 'timeValue');
			$data['timeArr'] = $this->commonM->getSchedTimeArray();
		}else{
			$data['access'] = false;
		}
	
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function customizebyday(){
		$data['content'] = 'v_schedule/v_customizebyday';
		$data['empID'] = $this->uri->segment(3);
		$data['today'] = $this->uri->segment(4);
		$data['is_edit'] = $this->uri->segment(5);
		
		if($this->access->accessFullHR==false){
			$data['access'] = false;
		}else{
			$currentDateToday = date('Y-m-d');
			
			if(!empty($_POST)){	
				$dateArr = array();
				$dates = $_POST['dates'];			
				$arr['assignBy'] = $this->user->empID;
				$arr['status'] = 1;
								
				$arr['timeText'] = $_POST['timeText'];
				$arr['timeHours'] = $_POST['timeHours'];
						
				$ddtext = '';			
				foreach($dates AS $d){
					$ddtext .= '"'.date('Y-m-d', strtotime($d)).'",';
				}
				
				//query to check if date already exist				
				$query2 = $this->dbmodel->getQueryResults('tcStaffScheduleByDates', 'dateID, dateToday, timeText, assignBy, assignDate, updateData', 'empID_fk="'.$_POST['empID_fk'].'" AND dateToday IN ('.rtrim($ddtext,',').')');
				
								
				if(!empty($query2)){
					foreach($query2 AS $q){
						$dateArr[] = $q->dateToday;
                        $arr['updateData'] = $q->updateData.'Updated from: timeText:'.$q->timeText.', assignedBy: '.$q->assignBy.', assignedDate: '.$q->assignDate.'|';
                        
						$this->dbmodel->updateQuery('tcStaffScheduleByDates', array('dateID'=>$q->dateID), $arr);
						unset($arr['updateData']);
					}
				}
				
				$arr['empID_fk'] = $_POST['empID_fk'];
				$arr['workhome'] = $_POST['workhome'];
				foreach($dates AS $d2){
					$arr['dateToday'] = date('Y-m-d', strtotime($d2));
					if(!in_array($arr['dateToday'], $dateArr)){
						$this->dbmodel->insertQuery('tcStaffScheduleByDates', $arr);
					}
					
					//insert to staffdailylogs table if date is less or equal today and update tcAttendance record					
					$schdArr = $this->timeM->getSchedArr($arr['dateToday'], $arr['timeText']);
					if(isset($schdArr['start']) && isset($schdArr['end'])){
						if($currentDateToday>=$arr['dateToday']){
							$dailyLogID = $this->dbmodel->getSingleField('tcStaffLogPublish', 'slogID', 'slogDate="'.$arr['dateToday'].'" AND empID_fk="'.$data['empID'].'" AND showStatus=1');
							if(!empty($dailyLogID)){
								$upp['schedIn'] = $schdArr['start'];
								$upp['schedOut'] = $schdArr['end'];
								$upp['schedHour'] = $arr['timeHours'];
								$upp['datePublished'] = '0000-00-00 00:00:00';
								$upp['publishBy'] = '';
								$upp['publishTimePaid'] = 0;
								$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$dailyLogID), $upp);		
							}else{
								$schedToday = $this->timeM->getCalendarSchedule($arr['dateToday'], $arr['dateToday'], $data['empID'], true);
								$this->timeM->insertToDailyLogs($data['empID'], $arr['dateToday'], $schedToday);
							}
							$this->timeM->cntUpdateAttendanceRecord($arr['dateToday']);
						}
					}					
				}				

				echo '<script>
						alert("Schedule has been updated");
						parent.$.fn.colorbox.close();
						parent.window.location.reload();
					</script>';
				exit;
			}
			
			$data['schedToday'] = $this->timeM->getSchedToday($data['empID'], $data['today']);
		}
		
		$data['staffInfo'] = $this->dbmodel->getSingleInfo('staffs', 'empID, CONCAT(fname," ",lname) AS name', 'empID="'.$data['empID'].'"');
		 
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function customizebystaffs(){
		$data['content'] = 'v_schedule/v_customizebystaffs';
		
		if($this->access->accessFullHR==false){
			$data['access'] = false;
		}else{
			if(!empty($_POST)){
				if($_POST['submitType']=='displaySched'){
					$arr = explode('==||==', $_POST['arr']);
					
					$arr2 = array();
					foreach($arr AS $a){
						if(!empty($a)){
							$a2 = explode('|', $a);
							$a3 = array('id'=>$a2[1],'name'=>$a2[2]);
							if(!empty($a2[3])) $a3['sched'] = $a2[3];
							$arr2[$a2[0]][] = $a3; 
						}						
					}
					ksort($arr2);
					
					$ids = '';
					$sansa = '<ul>';
					foreach($arr2 AS $i=>$ar2){
						$sansa .= '<li style="padding-bottom:10px;"><b>'.date('F d, Y', strtotime($i)).'</b><ul>';
						foreach($ar2 AS $r2){
							$sansa .= '<li>'.$r2['name'].((isset($r2['sched']))?' - <i>'.$r2['sched'].'</i>':'').'</li>';
						}
						$sansa .= '</ul></li>';
					}
					$sansa .= '</ul>';
										
					echo serialize($arr2).'--^_^--'.$sansa;
					exit;
				}else if($_POST['submitType']=='setSched'){
					$arya = unserialize (trim($_POST['schedArr'])); 
					$adate = date('Y-m-d H:i:s');
										
					//loop check if dateExist update if exist else insert
					foreach($arya AS $k=>$a){
						foreach($a AS $a2){	
							$schedInfo = $this->dbmodel->getSingleInfo('tcStaffScheduleByDates', 'dateID, timeText, assignBy, assignDate, updateData', 'empID_fk="'.$a2['id'].'" AND dateToday="'.$k.'" AND status=1');
							
							$timeVE = explode('|', $_POST['timeV']);
							$newarr['assignBy'] = $this->user->empID;
							$newarr['timeText'] = $timeVE[0];
							if(isset($timeVE[2])) $newarr['timeHours'] = $timeVE[2];
							$newarr['assignDate'] = $adate;	
							
							if(!empty($schedInfo)){															
                                $newarr['updateData'] = $schedInfo->updateData.'Updated from: timeID:'.$schedInfo->timeText.', assignedBy: '.$schedInfo->assignBy.', assignedDate: '.$schedInfo->assignDate.'|';								
                                
								$this->dbmodel->updateQuery('tcStaffScheduleByDates', array('dateID'=>$schedInfo->dateID), $newarr);
							}else{
								$newarr['dateToday'] = $k;
								$newarr['empID_fk'] = $a2['id'];
								$this->dbmodel->insertQuery('tcStaffScheduleByDates', $newarr);
							}
							
							//CHECK IF ALREADY INSERTED IN tcStaffLogPublish UPDATE DETAILS IF EXISTS
							if($k<=date('Y-m-d')){
								$dailyLogID = $this->dbmodel->getSingleField('tcStaffLogPublish', 'slogID', 'slogDate="'.$k.'" AND empID_fk="'.$a2['id'].'" AND showStatus=1');
								if(!empty($dailyLogID)){
									$schedArr = $this->timeM->getSchedArr($k, $newarr['timeText']);
									$upp['schedIn'] = $schedArr['start'];
									$upp['schedOut'] = $schedArr['end'];
									$upp['schedHour'] = ((isset($newarr['timeHours']))?$newarr['timeHours']:0);
									$upp['datePublished'] = '0000-00-00 00:00:00';
									$upp['publishBy'] = '';
									$upp['publishTimePaid'] = 0;
									$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$dailyLogID), $upp);		
								}else{
									$schedToday = $this->timeM->getCalendarSchedule($k, $k, $a2['id'], true);
									$this->timeM->insertToDailyLogs($a2['id'], $k, $schedToday);
								}
								$this->timeM->cntUpdateAttendanceRecord($k);
							}							
						}
					}
					
					echo '<script>alert("Schedules have been set."); parent.$.fn.colorbox.close(); parent.location.reload(); </script>';
					exit;
				}else if($_POST['submitType']=='removeSched'){
					$dateToday = date('Y-m-d');
					$marjune = unserialize (trim($_POST['schedArr'])); 
					
					foreach($marjune AS $dateF=>$abel){
						foreach($abel AS $no){
							if(isset($no['sched'])){
								$dateId = $this->dbmodel->getSingleField('tcStaffScheduleByDates', 'dateID', 'empID_fk="'.$no['id'].'" AND dateToday="'.$dateF.'"');
								if(!empty($dateId)){
									$this->dbmodel->updateQueryText('tcStaffScheduleByDates', 'status=0', 'dateID="'.$dateId.'"');
								}else{
									$insArr['dateToday'] = $dateF;
									$insArr['empID_fk'] = $no['id'];									
									$insArr['timeText'] = $no['sched'];									
									$insArr['assignBy'] = $this->user->empID;									
									$insArr['assignDate'] = date('Y-m-d H:i:s');									
									$insArr['status'] = 0;									
									$insArr['updateData'] = 'Removed schedule';									
									$this->dbmodel->insertQuery('tcStaffScheduleByDates', $insArr);
								}
								
								////if updating schedule before today
								if($dateF<=$dateToday){
									$upArr['schedIn'] = '0000-00-00 00:00:00';
									$upArr['schedOut'] = '0000-00-00 00:00:00';
									$upArr['schedHour'] = 0;
									$upArr['publishND'] = 0;
									$upArr['publishTimePaid'] = 0;
									$upArr['publishDeduct'] = 0;
									$upArr['publishOT'] = 0;
									$upArr['publishHO'] = '';
									$upArr['publishHOND'] = '';
									$upArr['datePublished'] = 0;
									$upArr['publishNote'] = 0;
									$upArr['publishBy'] = 0;
									$upArr['status'] = 0;
									$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogDate'=>$dateF, 'empID_fk'=>$no['id']), $upArr);										
								}
								
								//insert to tcTimelogUpdates
								$this->timeM->addToLogUpdate($no['id'], $dateF, '<b>Removed schedule</b><br/>Reason:'.$_POST['reason']);
							}
						}
					}
					
					echo '<script>alert("Schedules have been removed."); parent.$.fn.colorbox.close(); parent.location.reload(); </script>';
					exit;
				}
			}
			$data['pageType'] = ((!isset($_GET['type']))?'customizedSched':$_GET['type']);
			$data['timeArr'] = $this->commonM->customTimeArrayByCat();
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	/*****
		Get dateID from tcStaffScheduleByDates check if it exist.
		Change status to 0 if exist
		Insert if not exist and set status to 0
	*****/
	public function removeSchedule(){
		$data['content'] = 'v_schedule/v_removeschedule';
		
		if($this->access->accessFullHR==false){
			$data['access'] = false;
		}else if($this->user!=false){
			if(!empty($_GET)){
				$data['schedData'] = $_GET;
			
				if(!empty($_POST)){
					if($_POST['submitType']=='removeSched'){
						$dInfo = $this->dbmodel->getSingleInfo('tcStaffScheduleByDates', '*', 'dateToday="'.$data['schedData']['date'].'" AND empID_fk="'.$data['schedData']['id'].'"');
						
						if(count($dInfo)>0){
                            $updata = $dInfo->updateData.'Updated from: timeText:'.$dInfo->timeText.', assignedBy: '.$dInfo->assignBy.', assignedDate: '.$dInfo->assignDate.' TO: inactiveStatus DUE to '.$_POST['reason'].' BY:'.$this->user->username.'|';
                            
							
							if($dInfo->status==1) $upArr['status'] = 2;
							else $upArr['status'] = 0;
							$upArr['updateData'] = $updata;
							$upArr['assignDate'] = date('Y-m-d H:i:s');
							
							$this->dbmodel->updateQuery('tcStaffScheduleByDates', array('dateID'=>$dInfo->dateID), $upArr);
						}else{
							$insArr['dateToday'] = $data['schedData']['date'];
							$insArr['empID_fk'] = $data['schedData']['id'];
							$insArr['assignBy'] = $this->user->empID;
							$insArr['assignDate'] = date('Y-m-d H:i:s');
							$insArr['timeText'] = $data['schedData']['sched'];
							$insArr['status'] = 0;
                            $insArr['updateData'] = 'Set INACTIVE from:'.$data['schedData']['sched'].' REASON:'.$_POST['reason'].' BY:'.$this->user->username.' |';
                            
							$this->dbmodel->insertQuery('tcStaffScheduleByDates', $insArr);
						}
						
						//update tclogpublished data to no schedule
						$updateSched['schedIn'] = '0000-00-00 00:00:00';
						$updateSched['schedOut'] = '0000-00-00 00:00:00';
						$updateSched['datePublished'] = '0000-00-00 00:00:00';
						$updateSched['publishBy'] = '';
						$updateSched['schedHour'] = 0;
						$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogDate'=>$data['schedData']['date'], 'empID_fk'=>$data['schedData']['id']), $updateSched);
						
						//insert to tcTimelogUpdates
						$this->timeM->addToLogUpdate($data['schedData']['id'], $data['schedData']['date'], '<b>Removed schedule</b><br/>Reason:'.$_POST['reason']);
						
						echo '<script>
							alert("Schedule has been removed");
							parent.$.fn.colorbox.close();
							parent.window.location.reload();
						</script>';
						exit;
					}
				}
			}
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
}
?>
