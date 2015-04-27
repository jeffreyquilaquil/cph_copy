<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Schedules extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		date_default_timezone_set("Asia/Manila");
		
		$this->load->model('Staffmodel', 'staffM');
		$this->load->model('Textdefinemodel', 'txtM');
		$this->load->model('Schedulemodel', 'scheduleM');			
		$this->db = $this->load->database('default', TRUE);	
		session_start();		
		
		$this->user = $this->staffM->getLoggedUser();
		$this->access = $this->staffM->getUserAccess();
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
	
	public function setstaffschedule() {
		
		$id = $this->uri->segment(3);	
		$data['row'] = $this->staffM->getSingleInfo('staffs', 'CONCAT(fname," ",lname) as name',' empID="'.$id.'" ');								
		$data['content'] = 'setstaffschedule';
		$this->load->view('includes/templatecolorbox', $data);
	}
	
	
}
?>