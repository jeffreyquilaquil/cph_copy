<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schedules extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Manila");
		
		$this->load->model('Staffmodel', 'staffM');
		$this->load->model('Schedulemodel', 'scheduleM');			
		$this->db = $this->load->database('default', TRUE);					
		
		$this->user = $this->staffM->getLoggedUser();
		if($this->user!=false) $this->myaccess = explode(',',$this->user->access);		
		else $this->myaccess = array();
		
		$this->accessFull = false;
		$this->accessHR = false;
		$this->accessFinance = false;
		$this->accessFullHR = false;
		$this->accessFullHRFinance = false;
		
		if(in_array('full', $this->myaccess)) $this->accessFull = true;
		if(in_array('hr', $this->myaccess)) $this->accessHR = true;
		if(in_array('finance', $this->myaccess)) $this->accessFinance = true;		
		if(count(array_intersect($this->myaccess,array('full','hr'))) > 0) $this->accessFullHR = true;
		if(count(array_intersect($this->myaccess,array('full','hr','finance'))) > 0) $this->accessFullHRFinance = true;
	} 
	
	public function index(){
		$data['content'] = 'schedules';	
		$data['row'] = $this->user;	
		if($this->user!=false){
			if(isset($_POST) && !empty($_POST)){
				if($_POST['submitType']=='addtimecategory'){
					$this->staffM->insertQuery('staffCustomSchedTime', array('timeName'=>$_POST['name'], 'category'=>0, 'addInfo'=>$this->user->empID.'|'.date('Y-m-d H:i:s')));
					$this->staffM->addMyNotif($this->user->empID,'You added time category '.$_POST['name'].'.', 5);
				}else if($_POST['submitType']=='addtime'){
					$this->staffM->insertQuery('staffCustomSchedTime', array('timeName'=>$_POST['name'],'timeValue'=>$_POST['start'].' - '.$_POST['end'], 'category'=>$_POST['cat'], 'addInfo'=>$this->user->empID.'|'.date('Y-m-d H:i:s')));
					$this->staffM->addMyNotif($this->user->empID,'You added time category '.$_POST['name'].'.', 5);
				}else if($_POST['submitType']=='deleteTime'){
					$this->staffM->dbQuery('UPDATE staffCustomSchedTime SET status=0, addInfo=CONCAT(addInfo,"|'.$this->user->empID.'|'.date('Y-m-d H:i:s').'") WHERE timeID="'.$_POST['id'].'"');
					$this->staffM->addMyNotif($this->user->empID,'You deleted time option '.$_POST['id'].'.', 5);
				}else if($_POST['submitType']=='updateTime'){
					$this->staffM->dbQuery('UPDATE staffCustomSchedTime SET timeName="'.$_POST['timeName'].'", timeValue="'.$_POST['start'].' - '.$_POST['end'].'", addInfo=CONCAT(addInfo,"|'.$this->user->empID.'|'.date('Y-m-d H:i:s').'") WHERE timeID="'.$_POST['id'].'"');
					$this->staffM->addMyNotif($this->user->empID,'You updated time option ID: '.$_POST['id'].' to '.$_POST['start'].' - '.$_POST['end'], 5);
				}else if($_POST['submitType']=='addCustomSched'){
					unset($_POST['submitType']);
					$_POST['createdby'] = $this->user->empID;
					$_POST['datecreated'] = date('Y-m-d H:i:s');
					$this->staffM->insertQuery('staffCustomSched', $_POST);
					$this->staffM->addMyNotif($this->user->empID,'You added custom schedule: '.$_POST['schedName'], 5);
				}else if($_POST['submitType']=='updateCustomSched'){
					$id = $_POST['schedID'];
					unset($_POST['schedID']);
					unset($_POST['submitType']);
					$this->staffM->updateQuery('staffCustomSched', array('schedID'=>$id), $_POST);
					$this->staffM->dbQuery('UPDATE staffCustomSched SET updateData=CONCAT(updateData,"'.$this->user->empID.'--'.date('Y-m-d H:i:s').'|") WHERE schedID="'.$id.'"');
					$this->staffM->addMyNotif($this->user->empID,'You added custom schedule: '.$_POST['schedName'].'.', 5);
				}else if($_POST['submitType']=='deleteCustomSched'){
					$this->staffM->dbQuery('UPDATE staffCustomSched SET status=0, updateData=CONCAT(updateData,"|'.$this->user->empID.'|'.date('Y-m-d H:i:s').'|") WHERE schedID="'.$_POST['id'].'"');
					$this->staffM->addMyNotif($this->user->empID,'You deleted custom schedule '.$_POST['id'].'.', 5);
				}
				exit;
			}
			$data['timecategory'] = $this->staffM->getQueryResults('staffCustomSchedTime', '*', 'category=0 AND status=1');		
			$data['alltime'] = $this->staffM->getQueryResults('staffCustomSchedTime', '*', 'status=1');
			$data['allCustomSched'] = $this->staffM->getQueryResults('staffCustomSched', '*', 'status=1');
		}
		$this->load->view('includes/template', $data);	
	}	
	
}
?>