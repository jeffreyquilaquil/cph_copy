<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecard extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Manila");				
		$this->db = $this->load->database('default', TRUE);	
		$this->load->model('Staffmodel', 'staffM');			
		
		$this->user = $this->staffM->getLoggedUser();
		if($this->user!=false){
			$this->myaccess = explode(',',$this->user->access);
		}
		else $this->myaccess = array();			
	}

	public function _remap($method){
		$this->index($method);	
	} 
	
	public function index($segment2){	
		$data['content'] = 'timecard';	
			
		if($this->user!=false){		
			if($this->user->access=='' && $this->user->level==0){
				$data['access'] = false;
			}else{	
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
									
				$data['tpage'] = $segment2;
				$data['timelogs'] = array('type'=>'timelogs');
				$data['attendance'] = array('type'=>'attendance');
				$data['calendar'] = array('type'=>'calendar'); 
					
				if(!isset($data['allStaffs']))
					$data['allStaffs'] = $this->allemployees(); 
			}
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