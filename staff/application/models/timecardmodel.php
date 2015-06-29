<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecardmodel extends CI_Model {
	function __construct() {
        // Call the Model constructor
        parent::__construct();			
    }
	
	public function getTimeSettings(){
		$setArr = array();
		$setQuery = $this->dbmodel->getQueryResults('staffSettings', 'settingName, settingVal');
		foreach($setQuery AS $s){
			$setArr[$s->settingName] = $s->settingVal;
		}
		return $setArr;
	}
	
	public function getDaySched($cdate, $empID){
		$schedToday = '';
		$checkBelow = true;
		
		//check for leaves
		$leaves = $this->dbmodel->getSingleInfo('staffLeaves', 'leaveID, leaveType, status', 'empID_fk="'.$empID.'" AND status<3 AND iscancelled!=1 AND "'.$cdate.'" BETWEEN DATE_FORMAT(leaveStart, "%Y-%m-%d") AND DATE_FORMAT(leaveEnd, "%Y-%m-%d")');
		if(count($leaves)>0){
			$schedToday = 'On-Leave';
			if($leaves->status==0){
				$schedToday = 'Pending leave request';
			}else{
				if($leaves->status==1) $schedToday = 'On-leave With Pay';
				else $schedToday = 'On-leave Without Pay';
				$checkBelow = false;
			}
		}
		
		if($checkBelow){
			//check for schedule
			$condition = 'empID_fk="'.$empID.'"';
			$condition .= ' AND ("'.$cdate.'" BETWEEN effectivestart AND effectiveend';
			$condition .= ' OR (effectiveend="0000-00-00" AND effectivestart<="'.$cdate.'") )';
			$schedQ = $this->dbmodel->getSingleInfo('tcstaffSchedules', '*', $condition, '', 'assigndate DESC'); 
			if(!empty($schedQ)){
				if($schedQ->staffCustomSched_fk!=0){				
					$schedT = $this->dbmodel->getSingleInfo('tcCustomSchedTime', 'timeValue', 'custschedID="'.$schedQ->staffCustomSched_fk.'"', 'LEFT JOIN tcCustomSched ON timeID='.strtolower(date('l', strtotime($cdate))).'');				
				}else if($schedQ->staffCustomSchedTime!=0){
					$schedT = $this->dbmodel->getSingleInfo('tcCustomSchedTime', 'timeValue', 'timeID="'.$schedQ->staffCustomSchedTime.'"');
				}
				
				if(isset($schedT->timeValue) && !empty($schedT->timeValue)){
					$schedToday = $schedT->timeValue;
					$checkBelow = false;
				}			
			}
		}
		
		return $schedToday;	
	}
	
	//this will determine the start and end of schedule whether it advance to another date
	//returns array values start and end
	public function getSchedArr($dateToday, $schedText){
		$schedArr = array();
				
		$rang = explode(' - ', $schedText);
		if(count($rang)==2){
			$schedArr['start'] = date('Y-m-d H:i:s', strtotime($dateToday.' '.$rang[0]));
			$schedArr['end'] = date('Y-m-d H:i:s', strtotime($dateToday.' '.$rang[1]));
			
			if($schedArr['start']>$schedArr['end'])
				$schedArr['end'] = date('Y-m-d H:i:s', strtotime($schedArr['end'].' +1 day'));
				
		}		
		return $schedArr;		
	}
	
	//Get all logs for the given date schedule and returns an array of logs
	//$sched is an array that contains start and end
	//$idNum is the employee original ID number not the empID from database
	public function getTimeLogs($sched, $idNum, $dateToday){
		$logArr = array();
		if(count($sched)==2){
			$setArr = $this->getTimeSettings();			
			$aIn = date('Y-m-d H:i:s', strtotime($sched['start'].' '.$setArr['timeAllowedClockIn']));
			$aOut = date('Y-m-d H:i:s', strtotime($sched['end'].' '.$setArr['timeAllowedClockOut']));
						
			$tlogs = $this->dbmodel->getQueryResults('tcTimelogs', 'logType, logtime', 'staffs_idNum_fk="'.$idNum.'" AND logtime BETWEEN "'.$aIn.'" AND "'.$aOut.'"');
		}else{						
			$tlogs = $this->dbmodel->getQueryResults('tcTimelogs', 'logType, logtime', 'staffs_idNum_fk="'.$idNum.'" AND logtime LIKE "'.$dateToday.'%"');
		}
		
		if(!empty($tlogs)){
			foreach($tlogs AS $t){
				if($t->logType=='A' && (!isset($logArr['timeIn']) || (isset($logArr['timeIn']) && $logArr['timeIn']>$t->logtime))){
					$logArr['timeIn'] = $t->logtime;
				}
				if($t->logType=='Z' && (!isset($logArr['timeOut']) || (isset($logArr['timeOut']) && $logArr['timeOut']>$t->logtime))){
					$logArr['timeOut'] = $t->logtime;
				}
				if($t->logType=='D' || $t->logType=='E')
					$logArr['breaks'][] = $t->logtime;
			}
		}
		
		return $logArr;
	}
	
	//get all logs in a month
	public function getMonthLogs($month, $userID){
		return $this->dbmodel->getQueryResults('tcTimeLogByDates', '*', 'empID_fk="'.$userID.'" AND logDate LIKE "%-'.$month.'-%"');	
	}
}
?>
	