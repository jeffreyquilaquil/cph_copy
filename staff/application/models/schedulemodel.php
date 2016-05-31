<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schedulemodel extends CI_Model {
	function __construct() {
        // Call the Model constructor
        parent::__construct();			
    }	
	

	function getCustomSchedDetails($id) {
		$this->load->model('Staffmodel', 'staffM');
		$query = "SELECT * FROM  tcCustomSched WHERE custschedID =".$id;
		$query_result = $this->dbmodel->dbQuery($query);
		$customSchedDetails = $query_result->result();
		
		$weekarray = array();		
		$weekarray['sunday'] = $customSchedDetails[0]->sunday;
		$weekarray['monday'] = $customSchedDetails[0]->monday;
		$weekarray['tuesday'] = $customSchedDetails[0]->tuesday;
		$weekarray['wednesday'] = $customSchedDetails[0]->wednesday;
		$weekarray['thursday'] = $customSchedDetails[0]->thursday;
		$weekarray['friday'] = $customSchedDetails[0]->friday;
		$weekarray['saturday'] = $customSchedDetails[0]->saturday;
		$weekarray['schedType'] = $customSchedDetails[0]->schedType;
		return $weekarray;		
	
	}
	
	function insert_setscheduleforStaff($table, $array_input) {
		// $this->load->model('Staffmodel', 'staffM');
		$update_where = "effectiveend ='0000-00-00' AND empID_fk ='".$array_input['empID_fk']."' ";
		$date = $array_input['effectivestart'];
		$date1 = str_replace('-', '/', $date);
		$daybefore = date('Y-m-d',strtotime($date1 . "-1 days"));
		$update_data = array();
		$update_data['effectiveend'] = $daybefore;
		$getexistingperpetual = $this->dbmodel->updateQuery("staffSchedules", $update_where, $update_data);
        // $query = "UPDATE staffSchedules SET effectiveend = '".$daybefore."' WHERE effectiveend ='0000-00-00' AND empID_fk ='".$array_input['empID_fk']."'";
		// echo $query;
		// exit;
		// $query_result = $this->dbmodel->dbQuery($query);
		// $customSchedDetails = $query_result->result();		
		$setSchedNewId = $this->dbmodel->insertQuery("staffSchedules", $array_input);
		
	}
		
	/****
		sample return value "07:00 am - 04:00 pm" OR "09:00 pm - 06:00 am"
	***/
	function getTodaySched($empID, $dateToday){
		$sched = '';
		$schedArr = array();
		$dateToday = date('Y-m-d', strtotime($dateToday));
		$nameToday = strtolower(date('l', strtotime($dateToday)));
			
		//leaves
		$lquery = $this->dbmodel->getSingleInfo('staffLeaves', 'status','empID_fk="'.$empID.'" AND "'.$dateToday.'" BETWEEN leaveStart AND leaveEnd AND iscancelled=0 AND status BETWEEN 1 AND 2');
		if(count($lquery)>0){
			if($lquery->status==1) $sched = 'Paid Day Off';
			else $sched = 'Unpaid Day Off';
		}else{				
			//schedules
			$fquery = $this->dbmodel->getSingleInfo('staffSchedules','timeValue','empID_fk="'.$empID.'" AND tcCustomSchedTime!=0 AND ("'.$dateToday.'" BETWEEN effectivestart AND effectiveend OR (effectivestart<="'.$dateToday.'" AND effectiveend="0000-00-00"))','LEFT JOIN tcCustomSchedTime ON timeID=tcCustomSchedTime','assigndate DESC');
			
			if(count($fquery)>0){
				$sched = $fquery->timeValue;
			}else{
				$squery = $this->dbmodel->getSingleInfo('staffSchedules','timeValue','empID_fk="'.$empID.'" AND (("'.$dateToday.'" BETWEEN effectivestart AND effectiveend) OR (effectivestart<="'.$dateToday.'" AND effectiveend="0000-00-00"))','LEFT JOIN tcCustomSched ON staffCustomSched_fk = custschedID LEFT JOIN tcCustomSchedTime ON timeID = tcCustomSched.'.$nameToday.'','assigndate DESC');
				if(count($squery)>0) $sched = $squery->timeValue;
			}
		}
				
		return $sched;
	}
	
	//automatically assign end date of schedule when setting end date to employee
	public function endSchedule($empID, $endDate){		
		$this->dbmodel->updateQueryText('tcStaffSchedules', 'effectiveend="'.$endDate.'"', 'empID_fk="'.$empID.'" AND effectivestart<="'.$endDate.'" AND effectiveend="0000-00-00"'); //update staff schedules
		$this->dbmodel->updateQueryText('tcStaffScheduleByDates', 'status=0', 'empID_fk="'.$empID.'" AND dateToday>"'.$endDate.'"'); //update custom schedules

		//insert into tcLastPay
		$this->dbmodel->insertQuery('tcLastPay', ['empID_fk' => $empID, 'generatedBy' => 'system', 'dateGenerated' => date('Y-m-d H:i:s')]);
	}
	
}
?>