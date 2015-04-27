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
			
			if($data['tpage']=='calendar'){
				$calcondition = ' AND ("'.date('Y-m-01', strtotime($data['today'])).'" BETWEEN shiftstart AND shiftend OR (shiftstart BETWEEN "'.date('Y-m-01', strtotime($data['today'])).'" AND "'.date('Y-m-t', strtotime($data['today'])).'") OR (shiftstart<="'.date('Y-m-01', strtotime($data['today'])).'" AND shiftend="0000-00-00"))';
				
				$data['calScheds'] = $this->staffM->getQueryResults('staffSchedules','shiftstart, shiftend, staffCustomSched.*', 'empID_fk="'.$this->user->empID.'" AND staffCustomSched_fk !=0'.$calcondition, 'LEFT JOIN staffCustomSched ON custschedID=staffCustomSched_fk');
				$data['calSchedTime'] = $this->staffM->getQueryResults('staffSchedules', 'shiftstart, shiftend, timeValue', 'staffCustomSchedTime!=0 AND status=1'.$calcondition, 'LEFT JOIN staffCustomSchedTime ON timeID=staffCustomSchedTime');
			}
				
			if(!isset($data['allStaffs']))
				$data['allStaffs'] = $this->allemployees(); 			
		}
		
				
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
										
		return $this->staffM->getQueryResults('staffs', 'empID, username, lname, fname, newPositions.title, shift, dept, (SELECT CONCAT(fname," ",lname) AS name FROM staffs s WHERE s.empID=staffs.supervisor AND staffs.supervisor!=0 LIMIT 1) AS leader, holidaySched', (($condition=="")?'1':$condition), 'LEFT JOIN newPositions ON posId=position', 'lname');
	}
	
	
}
?>