<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecard extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		$this->db = $this->load->database('default', TRUE);
		$this->load->model('Staffmodel', 'staffM');	
		
		date_default_timezone_set("Asia/Manila");
		session_start();
		
		$this->user = $this->staffM->getLoggedUser();
		$this->access = $this->staffM->getUserAccess();				
	}

	public function _remap($method){
		$this->index($method);	
	} 
	
	public function index($segment2){	
		$data['content'] = 'tc_timecard';	
			
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
						$data['allStaffs'] = $this->staffM->getQueryResults('staffs', 'empID, username, lname, fname, newPositions.title, shift, dept, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS leader, holidaySched', 'empID IN ('.trim($ids,',').')', 'LEFT JOIN newPositions ON posId=position', 'lname');
					}
				}				
			}					
		
			$data['today'] = date('Y-m-d');
			if($segment2 == 'today'){
				$data['today'] = $this->uri->segment(2);
			}else if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$this->uri->segment(3))){
				$data['today'] = $this->uri->segment(3);
			}
								
			$data['tpage'] = $segment2; //calendar, timelogs, attendance
			$data['timelogs'] = array('type'=>'timelogs');
			$data['attendance'] = array('type'=>'attendance');
			$data['calendar'] = array('type'=>'calendar'); 
			
			$data['custTime'] = array();
			$tQuery = $this->staffM->getQueryResults('staffCustomSchedTime', 'timeID, timeValue', 'category>0 AND status=1');
			foreach($tQuery AS $t){
				$data['custTime'][$t->timeID] = $t->timeValue;
			}
			
			/** start FOR CALENDAR **/
			$calcondition = ' AND ("'.date('Y-m-01', strtotime($data['today'])).'" BETWEEN effectivestart AND effectiveend OR (effectivestart BETWEEN "'.date('Y-m-01', strtotime($data['today'])).'" AND "'.date('Y-m-t', strtotime($data['today'])).'") OR (effectivestart<="'.date('Y-m-01', strtotime($data['today'])).'" AND effectiveend="0000-00-00"))';							
			$data['calScheds'] = $this->staffM->getQueryResults('staffSchedules','effectivestart, effectiveend, staffCustomSched.*', 'empID_fk="'.$this->user->empID.'" AND staffCustomSched_fk !=0'.$calcondition, 'LEFT JOIN staffCustomSched ON custschedID=staffCustomSched_fk');
			$data['calSchedTime'] = $this->staffM->getQueryResults('staffSchedules', 'effectivestart, effectiveend, timeValue', 'empID_fk="'.$this->user->empID.'" AND staffCustomSchedTime!=0 AND status=1'.$calcondition, 'LEFT JOIN staffCustomSchedTime ON timeID=staffCustomSchedTime');
			
			$calHolidaysQuery = $this->staffM->getQueryResults('staffHolidays', 'holidayName, SUBSTR(holidayDate,-2) AS holidayDate, holidayType, holidayWork', '(holidaySched=0 OR (holidaySched=1 AND holidayDate LIKE "'.date('Y-m-', strtotime($data['today'])).'%")) AND SUBSTR(holidayDate,6) LIKE "'.date('m', strtotime($data['today'])).'-%"', '');
			$data['calHoliday'] = array();
			$holidayTypesArr = $this->config->item('holidayTypes');
			foreach($calHolidaysQuery AS $calHol){
				$data['calHoliday'][$calHol->holidayDate]['holidayTypeNum'] = $calHol->holidayType;
				$data['calHoliday'][$calHol->holidayDate]['holidayType'] = $holidayTypesArr[$calHol->holidayType];
				$data['calHoliday'][$calHol->holidayDate]['holidayName'] = $calHol->holidayName;
				$data['calHoliday'][$calHol->holidayDate]['holidayWork'] = $calHol->holidayWork;
			}
			
			$data['calLeaves'] = $this->staffM->getQueryResults('staffLeaves', 'leaveID, empID_fk, leaveType, leaveStart, leaveEnd, status, iscancelled', 'empID_fk="'.$this->user->empID.'" AND status!=3 AND iscancelled!=1 AND (leaveStart LIKE "'.date('Y-m-', strtotime($data['today'])).'%" OR leaveEnd LIKE "'.date('Y-m-', strtotime($data['today'])).'%")');						
			/** end FOR CALENDAR **/
			
			if(!isset($data['allStaffs']))
				$data['allStaffs'] = $this->allemployees(); 			
		}
		
				
		$this->load->view('includes/template', $data);
	}
	
	public function timelogs(){
		
		$this->load->view('includes/template', $data);
	}
	
	public function allemployees(){			
		if(isset($_POST['includeinactive'])) $condition = '';
		else $condition = 'staffs.active=1';

	
		if($this->user->access==''){
			$ids = '';
			$myStaff = $this->staffM->getStaffUnder($this->user->empID, $this->user->level);				
			foreach($myStaff AS $m):
				$ids .= $m->empID.',';
			endforeach;
			if($ids!=''){
				$condition .= ((!empty($condition))?' AND ':'').'empID IN ('.rtrim($ids,',').')';
			}
		}
										
		return $this->staffM->getQueryResults('staffs', 'empID, username, lname, fname, newPositions.title, shift, dept, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS leader, staffHolidaySched', (($condition=="")?'1':$condition), 'LEFT JOIN newPositions ON posId=position', 'lname');
	}
	
	
}
?>