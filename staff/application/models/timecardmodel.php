<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timecardmodel extends CI_Model {
	function __construct() {
        // Call the Model constructor
        parent::__construct();			
    }
	
	public function getSchedToday($today, $empID){
		$scheduleToday = '';
		//get schedule from tcStaffScheduleByDates
		$query1 = $this->dbmodel->getSingleInfo('tcStaffScheduleByDates', 'timeValue', 'empID_fk="'.$empID.'" AND dateToday="'.$today.'"', 'LEFT JOIN tcCustomSchedTime ON timeID=timeID_fk');
		if(isset($query1->timeValue)){
			$scheduleToday = $query1->timeValue;
		}else{
			$weekday = strtolower(date('l', strtotime($today)));
			$condition = 'empID_fk="'.$empID.'" AND (';
			$condition .= '("'.$today.'" BETWEEN effectivestart AND effectiveend)';
			$condition .= ' OR("'.$today.'" >= effectivestart AND effectiveend="0000-00-00")';
			$condition .= ')';			
			$query2 = $this->dbmodel->getSingleInfo('tcstaffSchedules', 'timeValue', $condition, 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk LEFT JOIN tcCustomSchedTime ON timeID='.$weekday.'');
			if(isset($query2->timeValue))
				$scheduleToday = $query2->timeValue;			
		}
		
		return $scheduleToday;		
	}
	
	//getting calendar schedule
	public function getScheduleArrayByDates($empID, $dateToday, $dateStart='', $dateEnd=''){
		$dayArr = array();
		$timeArrayVal = $this->commonM->getSchedTimeArray();
		$month = date('m', strtotime($dateToday));
		$year = date('Y', strtotime($dateToday));
		$dEnd = date('t', strtotime($dateToday));
		
		if(empty($dateStart)) $dateStart = date('Y-m-01', strtotime($dateToday));
		if(empty($dateEnd)) $dateEnd = date('Y-m-t', strtotime($dateToday));		
		
		$condition = 'empID_fk="'.$empID.'"';
		$condition .= ' AND '.$this->textM->getTodayBetweenSchedCondition($dateStart, $dateEnd);
					
		$fromStaffSchedQuery = $this->dbmodel->getQueryResults('tcstaffSchedules', 
							'schedID, tcCustomSched_fk, effectivestart, effectiveend, sunday, monday, tuesday, wednesday, thursday, friday, saturday', 
							$condition, 
							'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk', 'assigndate');
		
		foreach($fromStaffSchedQuery AS $sched){
			for($i=1; $i<=$dEnd; $i++){
				$dtoday =	date('Y-m-d', strtotime($year.'-'.$month.'-'.$i));
				if(strtotime($dtoday)>=strtotime($sched->effectivestart) && ($sched->effectiveend=='0000-00-00' || strtotime($dtoday)<=strtotime($sched->effectiveend))){
					$weekType = strtolower(date('l', strtotime($dtoday)));
					if(!empty($timeArrayVal[$sched->$weekType]))
						$dayArr[$i] = '<span class="daysched">'.$timeArrayVal[$sched->$weekType].'</span>';	
				}
			}
		}
		
		//get results from schedule templates and custom schedules by date
		$fromByDatesQuery = $this->dbmodel->getQueryResults('tcStaffScheduleByDates', 'dateToday, timeValue', 'empID_fk="'.$empID.'" AND dateToday BETWEEN "'.$dateStart.'" AND "'.date('Y-m-t', strtotime($dateToday)).'"', 'LEFT JOIN tcCustomSchedTime ON timeID=timeID_fk');
		foreach($fromByDatesQuery AS $d){
			$dayArr[date('j', strtotime($d->dateToday))] = '<span class="daysched">'.$d->timeValue.'</span>';
		}
		
		return $dayArr;
	}
	
}
?>
	