<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Itchecklist extends MY_Controller {
 
	public function __construct(){
		parent::__construct();		
		$this->load->model('timecardmodel', 'timeM');
		$this->load->model('staffmodel', 'staffM');
	}
		
	public function index(){
		$data['content'] = 'v_itchecklist/v_itchecklist';
				
		$this->load->view('includes/template', $data);
	}
	
	public function deactivateuser( $db = "PT" ){
		$data['content'] = 'v_itchecklist/v_itdeactivateuser';
		if($this->user!=false){
			if($this->user->dept!='IT'){
				$data['access'] = false;
			}else{
                if(!empty($_POST)){
					if( isset($_POST['submitType']) AND $_POST['submitType'] =='activeStatus'){
                        if( isset($_POST['which_table']) AND $_POST['which_table'] == 'CPH' ){
                            $info = $this->dbmodel->getSingleInfo('staffs', 'empID, username, CONCAT(fname," ",lname) AS name', 'empID="'.$_POST['empID'].'"');
                            $db_string = 'CPH database';
                        } else {
                            $info = $this->dbmodel->getPTQueryResults('staff', 'uid AS empID, username, CONCAT(sFirst, " ", sLast) AS name', 'uid = '. $_POST['empID'], '', 'sFirst');
                            $info = $info[0];
                            $db_string = 'PT database';
                        }
                        if($_POST['status']==0){
                            $pt_active = 'N';
                            $cph_active = '0';
							$this->commonM->addMyNotif($this->user->empID, 'You deactivated the status of '.$info->username.'.', 5);
							
							$body = '<p>Hi Guyz,</p>
									<p>'.$this->user->fname.' deactivated the account of "'.$info->name.'" in "'.$db_string.'" via CareerPH Account Deactivation page. Please perform checklist below:<p>
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
                            $subject_email = 'USER DEACTIVATED';
							
							echo 'Status in careerph and PT has been change to "INACTIVE"';
						}else{
                            $pt_active = 'Y';
                            $cph_active = '1';
							$this->commonM->addMyNotif($this->user->empID, 'You activated the status of '.$info->username.'.', 5);
							
							$body = '<p>Hi Guyz,</p>
									<p>'.$this->user->fname.' activated the account of "'.$info->name.'" in "'.$db_string.'" via CareerPH Account Deactivation page.<p>
									<p><br/></p>
									<p>Thanks!</p>
									<p>CAREERPH</p>
								';
					        $subject_email = 'USER ACTIVATED';	
							echo 'Status in careerph and PT has been change to "ACTIVE"';
                        }
                        if( isset($_POST['which_table']) AND $_POST['which_table'] == 'CPH' ){
							$this->dbmodel->dbQuery('UPDATE staffs SET active="'.$cph_active.'" WHERE empID = "'.$_POST['empID'].'"');
                        } else {
                            $this->dbmodel->ptdbQuery('UPDATE staff SET active="'.$pt_active.'" WHERE username = "'.$info->username.'"');
                        }

							$this->emailM->sendEmail('careers.cebu@tatepublishing.net', 'it.cebu@tatepublishing.net,hr-list.cebu@tatepublishing.net', $subject_email, $body, 'CareerPH Auto-Email' );
						exit;
					}
                }
                    if( $db == "CPH" ){
        				    $data['query'] = $this->dbmodel->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, active', '1', 'lname');
                    } else {
        				$data['query'] = $this->dbmodel->getPTQueryResults('staff', 'uid AS empID, username, CONCAT(sFirst," ",sLast) AS name, IF(active = "Y", "1", "0") AS active', '1', 'sFirst');
                    }
			}
		}
	    $data['selected'] = $db;	
		$this->load->view('includes/template', $data);
	}

	public function newhirestatus(){
		$data['content'] = 'v_itchecklist/v_itnewhirestatus';
		
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
	
	
	public function test(){
		$query = $this->dbmodel->getQueryResults('tcPayslips', '*', 'payrollsID_fk=8');
		foreach($query AS $q){
			echo '<pre>';
			print_r($q);
			echo '</pre>';
		}
	}
}
