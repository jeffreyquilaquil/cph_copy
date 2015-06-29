<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecard extends MY_Controller {
 
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
			$data['row'] = $this->dbmodel->getSingleInfo('staffs','empID,username, fname, CONCAT(fname," ",lname) AS name', 'empID="'.$method.'"');
						
			if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$segment4)) $data['today'] = $segment4;
			
			if(empty($segment3)){
				$this->timelogs($data);
			}else{
				$this->$segment3($data);
			}
		}else{
			$data['visitID'] = $this->user->empID;	
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
			$dateToday = date('Y-m-d');
								
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
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function schedules($data){		
		$data['content'] = 'tc_schedules';		
				
		if($this->user!=false){	
			$data['tpage'] = 'schedules';
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function scheduling($data){		
		$data['content'] = 'tc_scheduling';
		$data['tpage'] = 'scheduling';		
	
		if($this->user!=false){							
			if(!isset($data['allStaffs']))
				$data['allStaffs'] = $this->timeM->allemployees(); 
								
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
	
	public function addschedule($data){
		$data['content'] = 'tc_addschedule';
		$data['tpage'] = 'addschedule';	
		
		if($this->user!=false){	
			if(isset($_POST) && !empty($_POST)){
				echo '<pre>';
				print_r($_POST);
				exit;
			}		
			
			$data['scheduleTemplates'] = $this->dbmodel->getQueryResults('tcCustomSched', '*', 'status=1 OR (status=0 AND forEmp LIKE "%++'.$data['visitID'].'++%")');
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/templatecolorbox', $data);
	}
		
	
	
	
}
?>