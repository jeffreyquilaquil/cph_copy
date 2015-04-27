<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schedules extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Manila");
		
		$this->load->model('Staffmodel', 'staffM');
		$this->load->model('Schedulemodel', 'scheduleM');			
		$this->db = $this->load->database('default', TRUE);	
		session_start();		
		
		$this->user = $this->staffM->getLoggedUser();
		$this->access = $this->staffM->getUserAccess();
	} 
	
	public function index(){		
		$data['content'] = 'sched_schedules';	
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
					$tbl = 'staffCustomSchedTime';
					$insArr['timeName'] = $_POST['name'];
					$insArr['category'] = 0;
					$insArr['addInfo'] = $this->user->empID.'--'.strtotime(date('Y-m-d H:i:s')).'|';
					
					$note = 'You added time category '.$_POST['name'].'.';
				}else if($_POST['submitType']=='addtime'){					
					$tbl = 'staffCustomSchedTime';
					$insArr['timeName'] = $_POST['name'];
					$insArr['timeValue'] = $_POST['start'].' - '.$_POST['end'];
					$insArr['category'] = $_POST['cat'];
					$insArr['addInfo'] = $this->user->empID.'--'.strtotime(date('Y-m-d H:i:s')).'|';
					
					$note = 'You added time category '.$_POST['name'].'.';
				}else if($_POST['submitType']=='deleteTime'){
					$tbl = 'staffCustomSchedTime';
					$where = array('timeID'=>$_POST['id']);
					$addUp = 'addInfo';
					$upArr['status'] = 0;
					
					$note = 'You deleted time option '.$_POST['id'].'.';
				}else if($_POST['submitType']=='updateTime'){					
					$tbl = 'staffCustomSchedTime';
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
					$tbl = 'staffCustomSched';
					$insArr = $_POST;
					$insArr['createdby'] = $this->user->empID;
					$insArr['datecreated'] = date('Y-m-d H:i:s');
					
					$note = 'You added custom schedule: '.$_POST['schedName'];
				}else if($_POST['submitType']=='updateCustomSched'){					
					$tbl = 'staffCustomSched';
					$where = array('schedID'=>$_POST['schedID']);
					unset($_POST['schedID']);
					unset($_POST['submitType']);
					$upArr = $_POST;
					
					$addUp = 'updateData';					
					$note = 'You added custom schedule: '.$_POST['schedName'].'.';
				}else if($_POST['submitType']=='deleteCustomSched'){
					$tbl = 'staffCustomSched';
					$where = array('schedID'=>$_POST['id']);
					$upArr['status'] = 0;
					
					$addUp = 'updateData';					
					$note = 'You deleted custom schedule '.$_POST['id'].'.';
				}
				
				//update and insert defined above
				if(!empty($note)) $this->staffM->addMyNotif($this->user->empID,$note, 5);
				if(!empty($tbl) && count($insArr)>0) $this->staffM->insertQuery($tbl, $insArr);
				if(!empty($tbl) && count($upArr)>0 && count($where)>0) $this->staffM->updateQuery($tbl, $where, $upArr);
				reset($where);
				if(!empty($tbl) && count($where)>0 && !empty($addUp)){
					$this->staffM->updateConcat($tbl, key($where).'="'.$where[key($where)].'"', $addUp, $this->user->empID.'--'.strtotime(date('Y-m-d H:i:s')).'|');
				}
				exit;
			}
			$data['timecategory'] = $this->staffM->getQueryResults('staffCustomSchedTime', '*', 'category=0 AND status=1');		
			$data['alltime'] = $this->staffM->getQueryResults('staffCustomSchedTime', '*', 'status=1');
			$data['allCustomSched'] = $this->staffM->getQueryResults('staffCustomSched', '*', 'status=1');
			$data['schedTypeArr'] = $this->config->item('schedType');
		}
		$this->load->view('includes/template', $data);	
	}	
	
	public function setstaffschedule() {
		
		$id = $this->uri->segment(3);	
		$data['row'] = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) as name',' empID="'.$id.'" ');								
		$data['content'] = 'setstaffschedule';
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
}
?>