<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Itchecklist extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		$this->db = $this->load->database('default', TRUE);
		$this->ptDB = $this->load->database('projectTracker', TRUE);
		$this->load->model('Staffmodel', 'staffM');	
		date_default_timezone_set("Asia/Manila");
		
		$this->user = $this->staffM->getLoggedUser();
		$this->access = $this->staffM->getUserAccess();		
	}
		
	public function index(){
		$data['content'] = 'itchecklist';
		
		if($this->user!=false){
			
		}
		
		$this->load->view('includes/template', $data);
	}
	
	public function deactivateuser(){
		$data['content'] = 'itdeactivateuser';
		
		if($this->user!=false){
			if($this->user->dept!='IT'){
				$data['access'] = false;
			}else{
				if(isset($_POST) && !empty($_POST)){
					if($_POST['submitType']=='activeStatus'){
						$info = $this->staffM->getSingleInfo('staffs', 'empID, username, CONCAT(fname," ",lname) AS name', 'empID="'.$_POST['empID'].'"');
						if($_POST['status']==0){
							$this->staffM->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$info->username.'"');
							$this->staffM->dbQuery('UPDATE staffs SET active="0" WHERE empID = "'.$_POST['empID'].'"');
							$this->staffM->addMyNotif($this->user->empID, 'You deactivated the status of '.$info->username.'.', 5);
							
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
							$this->staffM->sendEmail('careers.cebu@tatepublishing.net', 'it.cebu@tatepublishing.net', 'USER DEACTIVATED', $body, 'CareerPH Auto-Email' );
							
							echo 'Status in careerph and PT has been change to "INACTIVE"';
						}else{
							$this->staffM->ptdbQuery('UPDATE staff SET active="Y" WHERE username = "'.$info->username.'"');
							$this->staffM->dbQuery('UPDATE staffs SET active="1" WHERE empID = "'.$_POST['empID'].'"');
							$this->staffM->addMyNotif($this->user->empID, 'You activated the status of '.$info->username.'.', 5);
							
							$body = '<p>Hi Guyz,</p>
									<p>'.$this->user->fname.' activated the account of "'.$info->name.'" in CareerPH Deactivate user page.<p>
									<p><br/></p>
									<p>Thanks!</p>
									<p>CAREERPH</p>
								';
							$this->staffM->sendEmail('careers.cebu@tatepublishing.net', 'helpdesk.cebu@tatepublishing.net', 'USER DEACTIVATED', $body, 'CareerPH Auto-Email' );
							
							echo 'Status in careerph and PT has been change to "ACTIVE"';
						}
						
						exit;
					}
				}
			
				$data['query'] = $this->staffM->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, active', '1', 'lname');
			}
		}
		
		$this->load->view('includes/template', $data);
	}

}
