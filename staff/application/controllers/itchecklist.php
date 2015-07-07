<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Itchecklist extends MY_Controller {
 
	public function __construct(){
		parent::__construct();				
	}
		
	public function index(){
		$data['content'] = 'v_itchecklist/v_itchecklist';
				
		$this->load->view('includes/template', $data);
	}
	
	public function deactivateuser(){
		$data['content'] = 'v_itchecklist/v_itdeactivateuser';
		
		if($this->user!=false){
			if($this->user->dept!='IT'){
				$data['access'] = false;
			}else{
				if(!empty($_POST)){
					if($_POST['submitType']=='activeStatus'){
						$info = $this->dbmodel->getSingleInfo('staffs', 'empID, username, CONCAT(fname," ",lname) AS name', 'empID="'.$_POST['empID'].'"');
						if($_POST['status']==0){
							$this->dbmodel->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$info->username.'"');
							$this->dbmodel->dbQuery('UPDATE staffs SET active="0" WHERE empID = "'.$_POST['empID'].'"');
							$this->commonM->addMyNotif($this->user->empID, 'You deactivated the status of '.$info->username.'.', 5);
							
							$body = '<p>Hi Guyz,</p>
									<p>'.$this->user->fname.' deactivated the account of "'.$info->name.'" in CareerPH Deactivate user page. Please perform checklist below:<p>
									<ul>
										<li>Lock email</li>
										<li>Change samba password</li>										
										<li>Remove in wifi list</li>
										<li>Remove groups & change password for cebu staff storage</li>
										<li>Check work equipments</li>
										<li>Change password of computer\'s profile account</li>
										<li>Disable HPBX (for calls)</li>								
									</ul>
									<p>Ignore if mentioned above already done.</p>
									<p><br/></p>
									<p>Thanks!</p>
									<p>CAREERPH</p>
								';
							$this->emailM->sendEmail('careers.cebu@tatepublishing.net', 'it.cebu@tatepublishing.net', 'USER DEACTIVATED', $body, 'CareerPH Auto-Email' );
							
							echo 'Status in careerph and PT has been change to "INACTIVE"';
						}else{
							$this->dbmodel->ptdbQuery('UPDATE staff SET active="Y" WHERE username = "'.$info->username.'"');
							$this->dbmodel->dbQuery('UPDATE staffs SET active="1" WHERE empID = "'.$_POST['empID'].'"');
							$this->commonM->addMyNotif($this->user->empID, 'You activated the status of '.$info->username.'.', 5);
							
							$body = '<p>Hi Guyz,</p>
									<p>'.$this->user->fname.' activated the account of "'.$info->name.'" in CareerPH Deactivate user page.<p>
									<p><br/></p>
									<p>Thanks!</p>
									<p>CAREERPH</p>
								';
							$this->emailM->sendEmail('careers.cebu@tatepublishing.net', 'it.cebu@tatepublishing.net', 'USER DEACTIVATED', $body, 'CareerPH Auto-Email' );
							
							echo 'Status in careerph and PT has been change to "ACTIVE"';
						}
						
						exit;
					}
				}
			
				$data['query'] = $this->dbmodel->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, active', '1', 'lname');
			}
		}
		
		$this->load->view('includes/template', $data);
	}

	public function newhirestatus(){
		$data['content'] = 'v_itchecklist/V_itnewhirestatus';
		
		if($this->user!=false){
			if($this->user->dept!='IT'){
				$data['access'] = false;
			}else{
				if(!empty($_POST)){
						if($_POST['type']=='seatplan' || $_POST['type']=='status'){
							$retvalue = $_POST['sval'];
						}else{
							$retvalue = $this->user->username.', '.date('Y-m-d H:i:s');
						}
						
						$this->dbmodel->updateQuery('staffNewEmployees', array('empID_fk'=>$_POST['empID_fk']), array($_POST['type']=>$retvalue));	
						echo $retvalue;
					exit;
				}
				
				$data['inprogress'] = $this->dbmodel->getQueryResults('staffNewEmployees', 'staffNewEmployees.*, CONCAT(fname," ",lname) AS name, title, startDate, shift, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor LIMIT 1) AS imsupervisor', 'status=0', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'startDate DESC');
				$data['done'] = $this->dbmodel->getQueryResults('staffNewEmployees', 'staffNewEmployees.*, CONCAT(fname," ",lname) AS name, title, startDate, shift, (SELECT CONCAT(fname," ",lname) AS n FROM staffs s WHERE s.empID=staffs.supervisor LIMIT 1) AS imsupervisor', 'status=1', 'LEFT JOIN staffs ON empID=empID_fk LEFT JOIN newPositions ON posID=position', 'startDate DESC');
			}	
		}
		$this->load->view('includes/template', $data);
	}
}
