<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schedules extends MY_Controller {
 
	public function __construct(){
		parent::__construct();
	} 
	
	public function index(){
		$data['content'] = 'sched_schedules';
		$data['column'] = 'withLeft';	
		
		$data['row'] = $this->user;	
		if($this->user!=false){
			if(isset($_POST) && !empty($_POST)){
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
					$where = array('custschedID'=>$_POST['schedID']);
					unset($_POST['schedID']);
					unset($_POST['submitType']);
					$upArr = $_POST;
					
					$addUp = 'updateData';					
					$note = 'You added custom schedule: '.$_POST['schedName'].'.';
				}else if($_POST['submitType']=='deleteCustomSched'){
					$tbl = 'tcCustomSched';
					$where = array('custschedID'=>$_POST['id']);
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
			// $data['holidaySchedArr'] = $this->dbmodel->getQueryResults('staffHolidays', 'holidayID, holidayName, holidayType, staffHolidaySched, holidayWork, CONCAT("'.date('Y').'", SUBSTRING( holidayDate,5)) AS holidayDate', 'staffHolidaySched=0 OR (staffHolidaySched=1 AND holidayDate LIKE "'.date('Y').'%")', '', 'holidayDate');
			$data['holidaySchedArr'] = $this->dbmodel->getQueryResults('staffHolidays', 'holidayID, holidayName, holidayType, holidaySched, holidayWork, CONCAT("'.date('Y').'", SUBSTRING( holidayDate,5)) AS holidayDate', 'holidaySched=0 OR (holidaySched=1 AND holidayDate LIKE "'.date('Y').'%")', '', 'holidayDate');
			$data['schedTypeArr'] = $this->config->item('schedType');
			$data['allDayTypes'] = $this->config->item('holidayTypes');			
		}
		$this->load->view('includes/template', $data);	
	}

		
	public function holidayevents(){
		$data['content'] = 'sched_holiday';
		$data['allDayTypes'] = $this->config->item('holidayTypes');
		
		$sday = $this->uri->segment(3);
		if(!empty($sday)){
			$exday = explode('-',$sday);
			$data['mmnum'] = (int)$exday[0];
			$data['mmday'] = $exday[1];		
		}		

		$this->load->view('includes/templatecolorbox', $data);	
	}
	
	
	public function setstaffschedule() {
		$idd = $this->uri->segment(3);			
		$trimid = rtrim($idd,"_");
		$explode = explode("_",$trimid);
		// var_dump($explode);
		$size  = sizeof($explode);
		if(isset($_POST) && !empty($_POST)){ 			
			  if($_POST['submitType']=='setScheduleForStaff'){
				
				
				$presched = $_POST['presched'];
				$array_input = array();
				$array_input['effectivestart'] = date("Y-m-d",strtotime($_POST['effective_startdate']));
				$array_input['assignby'] = $this->user->empID;
				
				if($_POST['effective_enddate'] == "")
					$array_input['effectiveend'] = "0000-00-00";
				else
					$array_input['effectiveend'] = date("Y-m-d",strtotime($_POST['effective_enddate']));
					
				if($presched == 0) {
					$array_custom = array();
					$array_custom['schedName'] = $_POST['schedName'];
					$array_custom['schedType'] = $_POST['schedType'];
					$array_custom['sunday'] = $_POST['sunday'];
					$array_custom['monday'] = $_POST['monday'];
					$array_custom['tuesday'] = $_POST['tuesday'];
					$array_custom['wednesday'] = $_POST['wednesday'];
					$array_custom['thursday'] = $_POST['thursday'];
					$array_custom['friday'] = $_POST['friday'];
					$array_custom['saturday'] = $_POST['saturday'];
					$array_custom['datecreated'] = date('Y-m-d H:i:s');
					
					$newId = $this->dbmodel->insertQuery("tcCustomSched", $array_custom);															
					$array_input['staffCustomSched_fk'] = $newId;
														
					for($ctr = 0; $ctr < $size; $ctr++) {
						$array_input['empID_fk'] = $explode[$ctr];					
						$response = $this->scheduleM->insert_setscheduleforStaff("tcstaffSchedules", $array_input);						
					}
				}
				else{					
					$array_input['staffCustomSched_fk'] = $_POST['presched'];										
					for($ctr = 0; $ctr < $size; $ctr++) {
						$array_input['empID_fk'] = $explode[$ctr];			
						$response = $this->scheduleM->insert_setscheduleforStaff("tcstaffSchedules", $array_input);						
					}
				}
			}
			unset($_POST);
			exit; 
		}
		
		$idstring = "";
		for($counter = 0; $counter < $size; $counter++) {
			$staff_xplode = explode('~',$explode[$counter]);
			$idstring .= $staff_xplode[1].",";			
		}
		$idstring_trim = rtrim($idstring,",");		
		
		# display date -> staff
		$a_string = "";
		$array_value_dateindex = array();	
		for($counter = 0; $counter < $size; $counter++) {
			$staff_xplode = explode('~',$explode[$counter]);			
			if(array_key_exists($staff_xplode[0],$array_value_dateindex)) 
				$array_value_dateindex[$staff_xplode[0]] .= $staff_xplode[1]."|";			
			else
			$array_value_dateindex[$staff_xplode[0]] = $staff_xplode[1].'|';
		}
		
		// var_dump($array_value_dateindex);
		$name_object = array();
		
		foreach($array_value_dateindex as $key=>$value){			
		$idstring = "";
			$trimmedValue = rtrim($value,"|");	
			$staff_xplode = explode('|',$trimmedValue);				
			$sizeofExplode = sizeof($staff_xplode);
			for($ctr = 0; $ctr < $sizeofExplode; $ctr++)			
				$idstring .= $staff_xplode[$ctr].",";		
			
			$idstring_trim = rtrim($idstring,",");				
			if($idstring_trim != "")
				$name_object[$key] = $this->dbmodel->getQueryResults('staffs', 'CONCAT(fname," ",lname) as name, empID',' empID IN ('.$idstring_trim.')' );
		}
		
		$data['row'] = $name_object;									
		$data['timeNameList'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', 'timeName,timeID',' category = 0' );
		$query = "SELECT * FROM  tcCustomSched";
		$query_result = $this->dbmodel->dbQuery($query);
		$data['customSched'] = $query_result->result();		
		// $data['customSchedTime'] = $this->dbmodel->getQueryResults('staffs', 'CONCAT(fname," ",lname) as name',' empID IN ('.$idstring_trim.')' );
		$data['alltime'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', '*', 'status=1');		 
		$data['content'] = 'setstaffschedule';		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function setstaffsrecurringschedule() {
		$idd = $this->uri->segment(3);			
		$trimid = rtrim($idd,"_");
		$explode = explode("_",$trimid);
		// var_dump($explode);
		$size  = sizeof($explode);
		if(isset($_POST) && !empty($_POST)){ 			
			  if($_POST['submitType']=='setScheduleForStaff'){
				
				
				$presched = $_POST['presched'];
				$array_input = array();
				$array_input['effectivestart'] = date("Y-m-d",strtotime($_POST['effective_startdate']));
				$array_input['assignby'] = $this->user->empID;
				
				if($_POST['effective_enddate'] == "")
					$array_input['effectiveend'] = "0000-00-00";
				else
					$array_input['effectiveend'] = date("Y-m-d",strtotime($_POST['effective_enddate']));
					
				if($presched == 0) {
					$array_custom = array();
					$array_custom['schedName'] = $_POST['schedName'];
					$array_custom['schedType'] = $_POST['schedType'];
					$array_custom['sunday'] = $_POST['sunday'];
					$array_custom['monday'] = $_POST['monday'];
					$array_custom['tuesday'] = $_POST['tuesday'];
					$array_custom['wednesday'] = $_POST['wednesday'];
					$array_custom['thursday'] = $_POST['thursday'];
					$array_custom['friday'] = $_POST['friday'];
					$array_custom['saturday'] = $_POST['saturday'];
					$array_custom['datecreated'] = date('Y-m-d H:i:s');
					
					$newId = $this->dbmodel->insertQuery("tcCustomSched", $array_custom);															
					$array_input['staffCustomSched_fk'] = $newId;
														
					for($ctr = 0; $ctr < $size; $ctr++) {
						$array_input['empID_fk'] = $explode[$ctr];					
						$response = $this->scheduleM->insert_setscheduleforStaff("tcstaffSchedules", $array_input);						
					}
				}
				else{					
					$array_input['staffCustomSched_fk'] = $_POST['presched'];										
					for($ctr = 0; $ctr < $size; $ctr++) {
						$array_input['empID_fk'] = $explode[$ctr];			
						$response = $this->scheduleM->insert_setscheduleforStaff("tcstaffSchedules", $array_input);						
					}
				}
			}
			unset($_POST);
			exit; 
		}
		
		$idstring = "";
		for($counter = 0; $counter < $size; $counter++) {			
			$idstring .= $explode[$counter].",";			
		}
		$idstring_trim = rtrim($idstring,",");		
				
		$data['row'] = $this->dbmodel->getQueryResults('staffs', 'CONCAT(fname," ",lname) as name, empID ',' empID IN ('.$idstring_trim.')' );
		$data['timeNameList'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', 'timeName,timeID',' category = 0' );
		$query = "SELECT * FROM  tcCustomSched";
		$query_result = $this->dbmodel->dbQuery($query);
		$data['customSched'] = $query_result->result();		
		// $data['customSchedTime'] = $this->dbmodel->getQueryResults('staffs', 'CONCAT(fname," ",lname) as name',' empID IN ('.$idstring_trim.')' );
		$data['alltime'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', '*', 'status=1');		 
		$data['content'] = 'setstaffrescurringschedule';		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	public function getvalueofpredefinesched() {
		$id = $this->input->post("id");
		$response = $this->scheduleM->getCustomSchedDetails($id);
		echo json_encode($response);		
	
	}
	
	public function addScheduleTime(){
		$Timeschedule = "";
		$newcategoryname = "";
		$category_id = "";
		if($this->input->post("buttonsubmit") == "addScheduleButton"){
			// echo "addScheduleButton";
			$Timeschedule = $this->input->post("scheduleTime");
			
		}
		else if($this->input->post("buttonsubmit") == "AddNowbutton"){
			// $toecho = array(); 
			// $toecho['buttonsubmit'] = $this->input->post("buttonsubmit");
			// $toecho['newtimename'] = $this->input->post("newtimename");
			// $toecho['giventimename'] = $this->input->post("giventimename");
			// $toecho['starttime'] = $this->input->post("starttime");
			// $toecho['endtime'] = $this->input->post("endtime");
			
			// buttonsubmit:'AddNowbutton',
			// newcategoryname: valueofnewcategoryname,
			// newtimeschedname : $('#time_name').val(),
			// defineschedtime : predefinetimename,
			// starttime : $('#starttime').val(),
			// endtime : $('#endtime').val()
				
				
			// echo $this->input->post("defineschedtime");
			// $category_id = $this->dbmodel->getSingleField("tcCustomSchedTime", "category", "timeID=".$this->input->post("defineschedtime"));
			// echo $category_id;
			if($this->input->post("defineschedtime") == ""){				
				$newcatergoryInsert = array();
				$newcatergoryInsert['timeName'] = $this->input->post("newcategoryname");				
				$newcatergoryInsert['category'] = 0;
				$newcatergoryInsert['status'] = 1;
				$newcatergoryInsert['addInfo'] = $this->user->empID."--".strtotime(date('Y-m-d'))."|";
				$category_id = $this->dbmodel->insertQuery("tcCustomSchedTime",$newcatergoryInsert);		
			}
			else
				// $category_id = $this->dbmodel->getSingleField("tcCustomSchedTime", "category", "timeID=".$this->input->post("defineschedtime"));
				$category_id = $this->input->post("defineschedtime");
			
									
			$newTimeSched = array();
			$newTimeSched['timeName'] = $this->input->post("newtimeschedname");
			$newTimeSched['timeValue'] = $this->input->post("starttime")." - ".$this->input->post("endtime");
			$newTimeSched['category'] = $category_id;
			$newTimeSched['status'] = 1;
			$newTimeSched['addInfo'] = $this->user->empID."--".strtotime(date('Y-m-d'))."|";
			$Timeschedule = $this->dbmodel->insertQuery("tcCustomSchedTime",$newTimeSched);		
							
		}
		
		$sessionRowValue = $this->session->userdata('rowvaluesession');
		foreach($sessionRowValue as $key=>$value) {						
			foreach($value as $maonani) {							
				$inputdata = array();
				$inputdata['empID_fk'] = $maonani->empID;
				$inputdata['tcCustomSchedTime'] = $Timeschedule;
				$inputdata['effectivestart'] = $key;
				$inputdata['effectiveend'] = $key;
				$inputdata['assignby'] = $this->user->empID;
				$this->dbmodel->insertQuery("tcstaffSchedules",$inputdata);		
			}					
		}	
	}
	
	public function setschedule(){
		$data['content'] = 'sched_setschedule';
		$today = date('Y-m-d');
		
		if($this->user!=false){	
			$empID = $this->uri->segment(3);
			if(!empty($empID)){
				$data['info'] = $this->dbmodel->getSingleInfo('staffs', 'username, fname, lname', 'empID="'.$empID.'"');
				$data['currentSched'] = $this->dbmodel->getSingleInfo('tcstaffSchedules', 
						'schedID, tcCustomSched_fk',
						'empID_fk="'.$empID.'" AND effectivestart<="'.$today.'" AND effectiveend="0000-00-00"');
			}
			
			$data['customTime'] = $this->dbmodel->getQueryResults('tcCustomSchedTime', 'timeID, timeName, timeValue,category', 'status=1');
			$data['customSched'] = $this->dbmodel->getQueryResults('tcCustomSched', 'custschedID, schedType, schedName, sunday, monday, tuesday, wednesday, thursday, friday, saturday', 'status=1');
		}
		
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
}
?>