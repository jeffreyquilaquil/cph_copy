<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecard3 extends MY_Controller {
 
	public function __construct(){
		parent::__construct();		
		$this->load->model('timecardmodel', 'timeM');
	}
		
	public function _remap($method){
		$segment3 = $this->uri->segment(3);
		$segment4 = $this->uri->segment(4);
		$data['today'] = date('Y-m-d');
		$data['visitID'] = '';
		
		if(is_numeric($method)){
			$data['column'] = 'withLeft';
			$data['visitID'] = $method;
			$data['row'] = $this->dbmodel->getSingleInfo('staffs','empID,username, fname', 'empID="'.$method.'"');
						
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$segment4)) $data['today'] = $segment4;
			
			if(empty($segment3)){
				$this->timelogs($data);
			}else{
				$this->$segment3($data);
			}
		}else{
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$segment3)) $data['today'] = $segment3;
			if($method=='index')
				$this->timelogs($data);
			else
				$this->$method($data);
		}		
	}
	
	public function cronInsertingTostaffTimeLogByDate(){
		$logQuery = $this->dbmodel->getQueryResults('tcTimelogs', 'tcTimelogs.*, empID', 'isInserted=0', 'LEFT JOIN staffs ON idNum=staffs_idNum_fk');
		echo '<pre>';
		print_r($logQuery);
		
		foreach($logQuery AS $l){
			$logID = $this->dbmodel->getSingleField('tcTimeLogByDates', 'tlogID', 'empID_fk="'.$l->empID.'"');
		}
		
		
		//exit;
	}
	
	public function timelogs($data){		
		$data['content'] = 'tc_timelogs';	
				
		if($this->user!=false){	
			$data['tpage'] = 'timelogs';
			$ddtoday = date('Y-m-d');
			
			$data['schedToday'] = $this->timeM->getDaySched($ddtoday, $this->user->empID);
			$data['schedTodayArr'] = $this->timeM->getSchedArr($ddtoday, $data['schedToday']);
			
			if(!empty($_POST)){
				if($_POST['submitType']=='clockIn' || $_POST['submitType']=='takeAbreak' || $_POST['submitType']=='backToWork' || $_POST['submitType']=='clockOut'){
					$this->dbmodel->insertQuery('tcTimelogs', array('staffs_idNum_fk'=>$this->user->idNum, 'logtime'=>date('Y-m-d H:i:s'), 'logtype'=>$_POST['tval']));
				}
				exit;
			}
			
			$data['timelog'] = $this->timeM->getTimeLogs($data['schedTodayArr'], $this->user->idNum, $ddtoday);			
			$data['calendarLogs'] = $this->timeM->getMonthLogs(date('m', strtotime($data['today'])), $this->user->empID);
								
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function attendance($data){		
		$data['content'] = 'tc_attendance';		
	
		if($this->user!=false){	
			$data['tpage'] = 'attendance';	
								
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function calendar($data){		
		$data['content'] = 'tc_calendar';		
				
		if($this->user!=false){	
			$data['tpage'] = 'calendar';	
						
			$data['custTime'] = array();
			$tQuery = $this->dbmodel->getQueryResults('tcCustomSchedTime', 'timeID, timeValue', 'category>0 AND status=1');
			foreach($tQuery AS $t){
				$data['custTime'][$t->timeID] = $t->timeValue;
			}
								
			if(isset($data['row']->empID)) $empID = $data['row']->empID;
			else $empID = $this->user->empID;
			
			$calcondition = ' AND ("'.date('Y-m-01', strtotime($data['today'])).'" BETWEEN effectivestart AND effectiveend OR (effectivestart BETWEEN "'.date('Y-m-01', strtotime($data['today'])).'" AND "'.date('Y-m-t', strtotime($data['today'])).'") OR (effectivestart<="'.date('Y-m-01', strtotime($data['today'])).'" AND effectiveend="0000-00-00"))';							
			$data['calScheds'] = $this->dbmodel->getQueryResults('tcstaffSchedules','effectivestart, effectiveend, tcCustomSched.*', 'empID_fk="'.$empID.'" AND staffCustomSched_fk !=0'.$calcondition, 'LEFT JOIN tcCustomSched ON custschedID=staffCustomSched_fk');
			$data['calSchedTime'] = $this->dbmodel->getQueryResults('tcstaffSchedules', 'effectivestart, effectiveend, timeValue', 'empID_fk="'.$empID.'" AND staffCustomSched_fk !=0 AND status=1'.$calcondition, 'LEFT JOIN tcCustomSchedTime ON timeID=custschedID');
			
			$calHolidaysQuery = $this->dbmodel->getQueryResults('staffHolidays', 'holidayName, SUBSTR(holidayDate,-2) AS holidayDate, holidayType, holidayWork', '(holidaySched=0 OR (holidaySched=1 AND holidayDate LIKE "'.date('Y-m-', strtotime($data['today'])).'%")) AND SUBSTR(holidayDate,6) LIKE "'.date('m', strtotime($data['today'])).'-%"', '');
			$data['calHoliday'] = array();
			$holidayTypesArr = $this->textM->constantArr('holidayTypes');
			foreach($calHolidaysQuery AS $calHol){
				$holdayInt = $calHol->holidayDate;
				$data['calHoliday'][$holdayInt]['holidayTypeNum'] = $calHol->holidayType;
				$data['calHoliday'][$holdayInt]['holidayType'] = $holidayTypesArr[$calHol->holidayType];
				$data['calHoliday'][$holdayInt]['holidayName'] = $calHol->holidayName;
				$data['calHoliday'][$holdayInt]['holidayWork'] = $calHol->holidayWork;
			}
			
			$data['calLeaves'] = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, empID_fk, leaveType, leaveStart, leaveEnd, status, iscancelled', 'empID_fk="'.$empID.'" AND status!=3 AND status!=5 AND iscancelled!=1 AND (leaveStart LIKE "'.date('Y-m-', strtotime($data['today'])).'%" OR leaveEnd LIKE "'.date('Y-m-', strtotime($data['today'])).'%")');			
										
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function schedules($data){		
		$data['content'] = 'tc_schedules';		
				
		$data['listOfSchedule'] = $this->dbmodel->getQueryResults('tcstaffSchedules LEFT JOIN tcCustomSched ON  custschedID = staffCustomSched_fk LEFT JOIN tcCustomSchedTime ON timeID = tcCustomSchedTime ','*','empID_fk='.$this->user->empID);
		
		if($this->user!=false){	
			$data['tpage'] = 'schedules';	
									
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function scheduling2($data){		
		$data['content'] = 'tc_scheduling';		
	
		if($this->user!=false){	
			$data['tpage'] = 'scheduling';	
			$data['allStaffs'] = $this->dbmodel->getQueryResults('staffs', 'empID, username, fname, lname', 'active=1', '', 'lname');	
			
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function scheduling($data){		
		$data['content'] = 'tc_scheduling2';		
	
		if($this->user!=false){	
			if(isset($_POST) && !empty($_POST) && isset($_POST['submitType'])){
				if($_POST['submitType']=='Assign a Schedule'){
					if(!isset($_POST['assign'])){ 
						$data['errortext'] = 'No employee selected.';
					}else{
						$data['assignSched'] = true;
						$ids = '';
						foreach($_POST['assign'] AS $a):
							$ids .= $a.',';
						endforeach;
						$data['allStaffs'] = $this->dbmodel->getQueryResults('staffs', 'empID, username, lname, fname, newPositions.title, shift, dept, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS leader, staffholidaySched', 'empID IN ('.trim($ids,',').')', 'LEFT JOIN newPositions ON posId=position', 'lname');
					}
				}				
			}		
		
			$data['tpage'] = 'scheduling';			
						
			if(!isset($data['allStaffs']))
				$data['allStaffs'] = $this->allemployees(); 
								
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function payslips($data){		
		$data['content'] = 'tc_payslips';		
	
		if($this->user!=false){	
			$data['tpage'] = 'payslips';			
											
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function payrolls($data){		
		$data['content'] = 'tc_payrolls';		
	
		if($this->user!=false){	
			$data['tpage'] = 'payrolls';			
											
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function reports($data){		
		$data['content'] = 'tc_reports';		
	
		if($this->user!=false){	
			$data['tpage'] = 'reports';			
											
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
		
	public function allemployees(){			
		if(isset($_POST['includeinactive'])) $condition = '';
		else $condition = 'staffs.active=1';

	
		if($this->user->access==''){
			$ids = '';
			$myStaff = $this->commonM->getStaffUnder($this->user->empID, $this->user->level);				
			foreach($myStaff AS $m):
				$ids .= $m->empID.',';
			endforeach;
			if($ids!=''){
				$condition .= ((!empty($condition))?' AND ':'').'empID IN ('.rtrim($ids,',').')';
			}
		}
										
		return $this->dbmodel->getQueryResults('staffs', 'empID, username, lname, fname, newPositions.title, shift, dept, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS leader, staffHolidaySched', (($condition=="")?'1':$condition), 'LEFT JOIN newPositions ON posId=position', 'lname');
	}
	
	
}
?>