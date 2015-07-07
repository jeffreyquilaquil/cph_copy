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
		$data['today'] = date('Y-m-d'); //this is the customized date according to passed url
		$data['visitID'] = $this->user->empID;
		
		if(preg_match("/^[0-9]{4}-([0-9]{1,2})-([0-9]{1,2})$/",$segment3)) 
			$data['today'] = $segment3;
		else if(preg_match("/^[0-9]{4}-([0-9]{1,2})-([0-9]{1,2})$/",$segment4)) 
			$data['today'] = $segment4;
	
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
		$data['content'] = 'v_timecard/v_timelogs';	
				
		if($this->user!=false){	
			$data['tpage'] = 'timelogs';
			$dateToday = date('Y-m-d');
								
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function attendance($data){		
		$data['content'] = 'v_timecard/v_attendance';		
	
		if($this->user!=false){	
			$data['tpage'] = 'attendance';	
								
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function calendar($data){		
		$data['content'] = 'v_timecard/v_calendar';		
		$data['tpage'] = 'calendar';
		
		if($this->user!=false){	
														
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function schedules($data){
		$data['content'] = 'v_timecard/v_schedules';	
		$data['tpage'] = 'schedules';
				
		if($this->user!=false){	
			$data['timeArr'] = $this->commonM->getSchedTimeArray();			
			$data['schedData'] = $this->dbmodel->getQueryResults('tcstaffSchedules', 'schedID, empID_fk, tcCustomSched_fk, effectivestart, effectiveend, schedName, sunday, monday, tuesday, wednesday, thursday, friday, saturday', 'empID_fk="'.$data['visitID'].'"', 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk'); //for schedule history
			
			//getting calendar schedule
			$data['dayArr'] = $this->timeM->getScheduleArrayByDates($data['visitID'], $data['today']);
						
			//this is for link on the dates
			$data['dayEditOptionArr'] = array();
			$month = date('m', strtotime($data['today']));
			$year = date('Y', strtotime($data['today']));
			$dEnd = date('t', strtotime($data['today']));
			for($i=1; $i<=$dEnd; $i++){
				$dtoday = date('Y-m-d', strtotime($year.'-'.$month.'-'.$i));
				if(strtotime($dtoday)>=strtotime($data['currentDate']))
					$data['dayEditOptionArr'][$i] = $this->config->base_url().'schedules/customizebyday/'.$data['visitID'].'/'.$dtoday.'/';
			}
		}
				
		$data['datelink'] = $this->config->base_url().'timecard/'.$data['visitID'].'/schedules/';
		$this->load->view('includes/template', $data);
	}
	
	public function scheduling($data){		
		$data['content'] = 'v_timecard/v_scheduling';
		$data['tpage'] = 'scheduling';		
	
		if($this->user!=false){	
			$data['allStaffs'] = $this->dbmodel->getQueryResults('staffs', 'empID, fname, lname', 'active=1', '', 'lname'); 
			//get start and end of the week
			$start_week = strtotime("last sunday midnight",strtotime($data['today']));
			$end_week = strtotime("next saturday",strtotime($data['today']));
			$data['start'] = date("Y-m-d",$start_week); 
			$data['end'] = date("Y-m-d",$end_week);	
			
			//variable declarations
			$data['schedData'] = array();
			$time = $this->commonM->getSchedTimeArray();
			$sunday = strtotime($data['start']);
			$monday = strtotime($data['start'].' +1 day');
			$tuesday = strtotime($data['start'].' +2 day');
			$wednesday = strtotime($data['start'].' +3 day');
			$thursday = strtotime($data['start'].' +4 day');
			$friday = strtotime($data['start'].' +5 day');
			$saturday = strtotime($data['start'].' +6 day');
			
			$data['sunday'] = $data['start'];
			$data['monday'] = date('Y-m-d', $monday);		
			$data['tuesday'] = date('Y-m-d', $tuesday);		
			$data['wednesday'] = date('Y-m-d', $wednesday);		
			$data['thursday'] = date('Y-m-d', $thursday);		
			$data['friday'] = date('Y-m-d', $friday);		
			$data['saturday'] = date('Y-m-d', $saturday);		
			
			//get staff template schedules
			$query = $this->dbmodel->getQueryResults('tcstaffSchedules', 'empID_fk, sunday, monday, tuesday, wednesday, thursday, friday, saturday, effectivestart, effectiveend',$this->textM->getTodayBetweenSchedCondition($data['start'], $data['end']), 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk');
						
			foreach($query AS $q){
				$arr = array();	
				$eStart = strtotime($q->effectivestart);
				if($q->effectiveend=='0000-00-00') $eEnd = strtotime($data['end']);
				else $eEnd = strtotime($q->effectiveend);
				
				if($q->sunday!=0 && $sunday>=$eStart && $sunday<=$eEnd) $arr[$data['sunday']] = $time[$q->sunday];
				if($q->monday!=0 && $monday>=$eStart && $monday<=$eEnd) $arr[$data['monday']] = $time[$q->monday];
				if($q->tuesday!=0 && $tuesday>=$eStart && $tuesday<=$eEnd) $arr[$data['tuesday']] = $time[$q->tuesday];
				if($q->wednesday!=0 && $wednesday>=$eStart && $wednesday<=$eEnd) $arr[$data['wednesday']] = $time[$q->wednesday];
				if($q->thursday!=0 && $thursday>=$eStart && $thursday<=$eEnd) $arr[$data['thursday']] = $time[$q->thursday];
				if($q->friday!=0 && $friday>=$eStart && $friday<=$eEnd) $arr[$data['friday']] = $time[$q->friday];
				if($q->saturday!=0 && $saturday>=$eStart && $saturday<=$eEnd) $arr[$data['saturday']] = $time[$q->saturday];				
				
				$data['schedData'][$q->empID_fk] = $arr;
			}
			
			//get schedules by dates
			$query2 = $this->dbmodel->getQueryResults('tcStaffScheduleByDates', 'empID_fk, dateToday, timeValue', 'dateToday BETWEEN "'.$data['start'].'" AND "'.$data['end'].'"', 'LEFT JOIN tcCustomSchedTime ON timeID=timeID_fk');
			
			foreach($query2 AS $q2){
				$data['schedData'][$q2->empID_fk][$q2->dateToday] = $q2->timeValue;
			}
			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function payslips($data){		
		$data['content'] = 'v_timecard/v_payslips';		
	
		if($this->user!=false){	
			$data['tpage'] = 'payslips';			
											
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function payrolls($data){		
		$data['content'] = 'v_timecard/v_payrolls';		
	
		if($this->user!=false){	
			$data['tpage'] = 'payrolls';			
											
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
	public function reports($data){		
		$data['content'] = 'v_timecard/v_reports';		
	
		if($this->user!=false){	
			$data['tpage'] = 'reports';			
											
			$data['dataArr'] = array();			
		}
	
		$this->load->view('includes/template', $data);
	}
	
}
?>