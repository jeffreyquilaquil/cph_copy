<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MyCrons extends CI_Controller {
 
	public function __construct(){
		parent::__construct();
		$this->load->model('Staffmodel', 'staffM');	
		$this->load->model('Textdefinemodel', 'txtM');
		$this->db = $this->load->database('default', TRUE);	
		date_default_timezone_set("Asia/Manila");	
	} 
	
	public function index(){
		$this->cancelledLeavesUnattended24Hrs();
	}	
	
	public function getImSupervisor($supervisor){
		$grow = $this->staffM->getSingleInfo('staffs', 'email, CONCAT(fname," ",lname) AS name, supervisor, (SELECT s.email FROM staffs s WHERE s.empID=staffs.supervisor LIMIT 1) AS supEmail', 'empID="'.$supervisor.'"');
		
		return $grow;		
	}
	
	/***** Unattended leave or cancelled request will send send to immediate supervisor if no approval. *****/
	public function cancelledLeavesUnattended24Hrs(){	
		$now = time();
		$date24hours = date('Y-m-d H:i:s', strtotime('-1 day'));
		$query = $this->staffM->getQueryResults('staffLeaves', 'leaveID, empID_fk, date_requested, iscancelled, datecancelled', '(approverID=0 AND date_requested<"'.$date24hours.'" AND status!=3 AND status!=5 AND iscancelled=0) OR (iscancelled=2 AND datecancelled<"'.$date24hours.'")'); 
		
		foreach($query AS $q):
			if($q->iscancelled==2)
				$datediff = $now - strtotime($q->datecancelled);
			else
				$datediff = $now - strtotime($q->date_requested);
			$nutri = floor($datediff/(60*60*24));
			$grow0 = $this->staffM->getSingleInfo('staffs', 'email, CONCAT(fname," ",lname) AS name, supervisor', 'empID="'.$q->empID_fk.'"');
						
			$supervisor = '';
			$supEmail = '';
			$sup = $grow0->supervisor;
			for($i=1; $i<=$nutri && $sup!=''; $i++){
				$grow1 = $this->getImSupervisor($sup);
				if(count($grow1)>0){
					$sup = $grow1->supervisor;
					$supervisor = $grow1->name;
					$supEmail = $grow1->supEmail;
				}				
			}
			echo '<pre>';
			print_r($q);
			echo '</pre>';
			
			$eBody = '<p>Hi,</p>';
			$eBody .= '<p>'.$supervisor.' has not taken action on Leave ID <a href="'.$this->config->base_url().'staffleaves/'.$q->leaveID.'/">#'.$q->leaveID.'</a> within '.($nutri*24).' hours from the time the leave was filed. In the event that '.$supervisor.' is on leave, you as '.(($sup=='')?'HR':'him/her immediate supervisor').' must take action on Leave ID <a href="'.$this->config->base_url().'staffleaves/'.$q->leaveID.'/">#'.$q->leaveID.'</a> otherwise this will be escalated to your immediate supervisor.</p>
				<p><br/></p>
				<p>Thank you very much.</p>
				<p>CareerPH</p>';
									
			if($supEmail=='')
				$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Tate Career PH Leave Needs Approval', $eBody, 'CAREERPH');
			else
				$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', $supEmail, 'Tate Career PH Leave Needs Approval', $eBody, 'CAREERPH');
			
		endforeach;		
		exit;
	}
	
	/***** This will run every hour to expire unused generated code for 24 hours *****/
	function expiregeneratedcode(){
		$codes = $this->staffM->getQueryResults('staffCodes', '*', 'status=1 AND dategenerated<="'.date('Y-m-d H:i:s', strtotime('-1 day')).'"');
		foreach($codes AS $c):
			$this->db->where('codeID', $c->codeID);
			$this->db->update('staffCodes', array('status'=>0));
			$this->addMyNotif($c->generatedBy,"The code $c->code you generated was unused and automatically expired.", 0, 1);			
		endforeach;
	}
	
	/***** Reset leave credits value to 10+tateemployment years on the anniversary or employee's hire date and email accounting *****/
	function resetAnnivLeaveCredits(){		
		$query = $this->staffM->getQueryResults('staffs', 'empID, leaveCredits, CONCAT(fname," ",lname) AS name, startDate', 'active=1 AND startDate LIKE "%'.date('m-d').'"');
		foreach($query AS $q):			
			$diff = abs(strtotime(date('Y-m-d')) - strtotime($q->startDate));
			$years = floor($diff / (365*60*60*24));
			
			if($q->leaveCredits>0){
				$body = '<p>Hi,</p>
					<p><i>This is an automated message.</i><br/>
					*************************************************************************************</p>
					<p>Please be informed that today is the anniversary of '.$q->name.'. Leave credits has been reset and is now '.(10+$years).'. Unused leave credits is '.$q->leaveCredits.'. Please facilitate conversion to cash. Thank you.</p>
					<p><br/></p>
					<p>Thanks!</p>
					<p>CAREERPH Auto-Email</p>';
				$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', 'accounting.cebu@tatepublishing.net', 'Employee\'s Anniversary', $body, 'CAREERPH' );
			}
			
			$hremail = '<p>Hi HR,</p>
					<p><i>This is an automated message.</i><br/>
					*************************************************************************************</p>
					<p>Please be informed that today is the anniversary of '.$q->name.'. Leave credits has been reset and is now '.(10+$years).'. Unused leave credits is '.$q->leaveCredits.'.</p>
					<p><br/></p>
					<p>Thanks!</p>
					<p>CAREERPH Auto-Email</p>';
			$this->staffM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Employee\'s Anniversary', $hremail, 'CAREERPH' );
			
			
			$this->staffM->updateQuery('staffs', array('empID'=>$q->empID), array('leaveCredits'=>($years+10)));
			
			$nnote = 'CONGRATULATIONS! This day marks your '.$this->staffM->ordinal($years).' year with Tate Publishing. During the time you have worked with us, you have significantly contributed to our company\'s success. We thank you for your enduring loyalty and diligence.<br/><br/>Your leave credits is automatically reset to '.($years+10).'. <br/><br/>We wish you happiness and success now and always.';
			$this->addMyNotif($q->empID, $nnote, 0, 1);
			
		endforeach;
		
	}
		
	function accessenddate(){
		$dateToday = date('Y-m-d');
		$query = $this->staffM->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, endDate, accessEndDate, office, shift, newPositions.title', 'staffs.active=1 AND (accessEndDate="'.$dateToday.'" OR endDate="'.date('Y-m-d').'")', 'LEFT JOIN newPositions ON posID=position');
		
		foreach($query AS $uInfo):
			$this->staffM->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$uInfo->username.'"');
			$this->staffM->dbQuery('UPDATE staffs SET active="0" WHERE empID = "'.$uInfo->empID.'"');
				
			//send email
			$ebody = '<p><b>Employee Separation Notice:</b></p>';
			$ebody .= '<p>Employee: <b>'.$uInfo->name.'</b></p>';
			
			$ebody .= '<p>';
			$ebody .= 'Position: <b>'.$uInfo->title.'</b><br/>';
			
			if($uInfo->accessEndDate==$dateToday) $ebody .= 'Access End Date: <b>'.date('F d, Y', strtotime($uInfo->accessEndDate)).'</b><br/>';	
			if($uInfo->endDate!='0000-00-00') $ebody .= 'Separation Date: <b>'.date('F d, Y', strtotime($uInfo->endDate)).'</b><br/>';	
						
			$ebody .= 'Shift: <b>'.$uInfo->shift.'</b><br/>';
			$ebody .= 'Office Branch : <b>'.$uInfo->office.'</b><br/>';											
			$ebody .= '</p>';											
			
			$ebody .= '<p><b>IT Staff:</b> Please terminate this employee\'s access to Email, ProjectTracker, and the phone system on the date of separation. Further, collect any equipment issued or checked out to the employee on their last day of work. Please coordinate with the employee\'s immediate supervisor to establish forwarding of phone and email if applicable.</p>';
			
			$ebody .= '<p>Thanks!</p>';
				
			$this->staffM->sendEmail('hr.cebu@tatepublishing.net', 'helpdesk.cebu@tatepublishing.net', 'Separation Notice for '.$uInfo->name, $ebody, 'Tate Publishing Human Resources (CareerPH)');
			
		endforeach;

		exit;
	}
	
	public function updateStaffCIS(){
		$query = $this->staffM->getQueryResults('staffCIS', 'cisID, empID_fk, effectivedate, changes, dbchanges, preparedby, staffCIS.status, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby AND preparedby!=0) AS pName', 'status=1 AND effectivedate<="'.date('Y-m-d').'"', 'LEFT JOIN staffs ON empID=empID_fk');
		foreach($query AS $q):	
			$chtext = '';
			$changes = json_decode($q->dbchanges);
			if(isset($changes->position)){
				$changes->levelID_fk = $this->staffM->getSingleField('newPositions', 'orgLevel_fk', 'posID="'.$changes->position.'"');
			}			
			$this->staffM->updateQuery('staffs', array('empID'=>$q->empID_fk), $changes);	
			
			if(isset($changes->supervisor)){
				$this->addMyNotif($changes->supervisor, 'You are the new immediate supervisor of <a href="'.$this->config->base_url().'staffinfo/'.$q->username.'/">'.$q->name.'</a>.', 0, 1);
			}
			
			$chQuery = json_decode($q->changes);
			foreach($chQuery AS $k=>$c):
				if($k!='salary'){
					$chtext .= 'Previous '.$this->txtM->defineField($k).': '.$c->c.'<br/>';
					$chtext .= 'New '.$this->txtM->defineField($k).': <b>'.$c->n.'</b><br/>';
				}
			endforeach;
				
			$this->staffM->updateQuery('staffCIS', array('cisID'=>$q->cisID), array('status'=>3));
			$this->addMyNotif($q->empID_fk, 'The CIS generated by '.$q->pName.' has been reflected to your employee details. Click <a href="'.$this->config->base_url().'cispdf/'.$q->cisID.'/" class="iframe">here</a> to check details.<br/>'.$chtext, 0, 1);
			$this->addMyNotif($q->preparedby, 'The CIS you generated for <a href="'.$this->config->base_url().'staffinfo/'.$q->username.'/">'.$q->name.'</a> has been reflected to his/her employee details. Click <a href="'.$this->config->base_url().'cispdf/'.$q->cisID.'/" class="iframe">here</a> to check details.<br/>'.$chtext, 0, 1);
		endforeach;
		echo count($query);
	}
	
	/***** Add notification *****/
	function addMyNotif($empID, $ntexts, $ntype=0, $isNotif=0){
		$insArr['empID_fk'] = $empID;
		$insArr['sID'] = 0;
		$insArr['ntexts'] = addslashes($ntexts);
		$insArr['dateissued'] = date('Y-m-d H:i:s');
		$insArr['ntype'] = $ntype;
		$insArr['isNotif'] = $isNotif;
		$this->staffM->insertQuery('staffMyNotif', $insArr);
	}
}