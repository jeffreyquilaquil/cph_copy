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
				}else if($_POST['submitType']=='updateHoliday'){
					$tbl = 'staffHolidays';
					$where = array('holidayID'=>$_POST['holidayID']);
					unset($_POST['holidayID']);
					unset($_POST['submitType']);
					$upArr = $_POST;
					
					$addUp = 'addInfo';					
					$note = 'You updated time holiday schedule '.$_POST['holidayName'].'.';						
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
					$upArr['status'] = 0;
					
					$addUp = 'updateData';					
					$note = 'You deleted custom schedule '.$_POST['id'].'.';
				}else if($_POST['submitType']=='getHolidayData'){
					$beau = $this->dbmodel->getSingleInfo('staffHolidays', 'holidayName, holidayType, holidayWork, holidayDate', 'holidayID="'.$_POST['id'].'"');
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
			$data['alltime'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', '*', 'status=1');
			$data['allCustomSched'] = $this->dbmodel->getQueryResults('tcCustomSched', '*', 'status=1');
			$data['holidaySchedArr'] = $this->dbmodel->getQueryResults('staffHolidays', 'holidayID, holidayName, holidayType, holidaySched, holidayWork, CONCAT("'.date('Y').'", SUBSTRING( holidayDate,5)) AS holidayDate', 'holidaySched=0 OR (holidaySched=1 AND holidayDate LIKE "'.date('Y').'%")', '', 'holidayDate');
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
				$insArr['effectivestart'] = date('Y-m-d', strtotime($_POST['startDate']));
				if(!empty($_POST['endDate']))
					$insArr['effectiveend'] = date('Y-m-d', strtotime($_POST['endDate']));
				$insArr['assignby'] = $this->user->empID;
				$insArr['assigndate'] = date('Y-m-d H:i:s');
				
				$this->dbmodel->insertQuery('tcstaffSchedules', $insArr);
				
				if(isset($_POST['schedID'])){					
					$this->dbmodel->updateQuery('tcstaffSchedules', array('schedID'=>$_POST['schedID']), array('effectiveend'=>date('Y-m-d', strtotime($_POST['startDate'].' -1 day'))));
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
			$data['currentSched'] = $this->dbmodel->getSingleInfo('tcstaffSchedules', 'schedID, empID_fk, tcCustomSched_fk, effectivestart, effectiveend, sunday, monday, tuesday, wednesday, thursday, friday, saturday', 'empID_fk="'.$data['row']->empID.'" AND effectiveend="0000-00-00"', 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk');		
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
				$this->dbmodel->updateQuery('tcstaffSchedules', array('schedID'=>$_POST['schedID']), array('effectiveend'=>date('Y-m-d', strtotime($_POST['enddate']))));
				echo '<script>
					alert("Schedule has been updated");
					parent.$.fn.colorbox.close();
					parent.window.location.reload();
				</script>';
				exit;
			}		
			$data['row'] = $this->dbmodel->getSingleInfo('tcstaffSchedules', 'schedID, empID_fk, CONCAT(fname," ",lname) AS name, tcCustomSched_fk, effectivestart, effectiveend, sunday, monday, tuesday, wednesday, thursday, friday, saturday', 'schedID="'.$this->uri->segment(3).'" AND effectiveend="0000-00-00"', 'LEFT JOIN tcCustomSched ON custSchedID=tcCustomSched_fk LEFT JOIN staffs ON empID_fk=empID');	
			$data['schedTimes'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', '*', 1);
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
		
		if(!empty($_POST)){
			$dates = $_POST['dates'];
			$arr['timeID_fk'] = $_POST['timeID_fk'];			
			$arr['assignBy'] = $this->user->empID;
			
			$ddtext = '';			
			foreach($dates AS $d){
				$ddtext .= '"'.date('Y-m-d', strtotime($d)).'",';
			}
			
			//query to check if date already exist
			$dateArr = array();
			$query2 = $this->dbmodel->getQueryResults('tcStaffScheduleByDates', 'dateID, dateToday, timeID_fk, assignBy, assignDate, updateData', 'empID_fk="'.$_POST['empID_fk'].'" AND dateToday IN ('.rtrim($ddtext,',').')');
			
			if(!empty($query2)){
				foreach($query2 AS $q){
					$dateArr[$q->dateToday] = true;
					$arr['updateData'] = $q->updateData.'Updated from: timeID:'.$q->timeID_fk.', assignedBy: '.$q->assignBy.', assignedDate: '.$q->assignDate.'|';
					$this->dbmodel->updateQuery('tcStaffScheduleByDates', array('dateID'=>$q->dateID), $arr);
					unset($arr['updateData']);
				}
			}
			
			$arr['empID_fk'] = $_POST['empID_fk'];
			foreach($dates AS $d2){
				$arr['dateToday'] = date('Y-m-d', strtotime($d2));
				
				if(!in_array($arr['dateToday'], $dateArr))					
					$this->dbmodel->insertQuery('tcStaffScheduleByDates', $arr);
			}

			echo '<script>
					alert("Schedule has been updated");
					parent.$.fn.colorbox.close();
					parent.window.location.reload();
				</script>';
			exit;
		}
		
		$data['schedToday'] = $this->timeM->getSchedToday($data['today'], $data['empID']);		
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
}
?>