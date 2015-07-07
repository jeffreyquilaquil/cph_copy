<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MyCrons extends MY_Controller {
 
	public function __construct(){
		parent::__construct();		
	} 
	
	public function index(){
		$this->cancelledLeavesUnattended24Hrs();
	}	
		
	/*****  Unattended leave or cancelled request will send email to immediate supervisor if no approval.  *****/
	public function cancelledLeavesUnattended24Hrs(){	
		$now = time();
		$date24hours = date('Y-m-d H:i:s', strtotime('-1 day'));
		$query = $this->dbmodel->getQueryResults('staffLeaves', 'leaveID, empID_fk, date_requested, iscancelled, datecancelled', '(approverID=0 AND date_requested<"'.$date24hours.'" AND status!=3 AND status!=5 AND iscancelled=0) OR (iscancelled=2 AND datecancelled<"'.$date24hours.'")'); 
		
		foreach($query AS $q):
			if($q->iscancelled==2) $thedate = $q->datecancelled;
			else $thedate = $q->date_requested;
				
			$datediff = $now - strtotime($thedate);
			$nutri = floor($datediff/(60*60*24));
			$grow0 = $this->dbmodel->getSingleInfo('staffs', 'email, CONCAT(fname," ",lname) AS name, supervisor', 'empID="'.$q->empID_fk.'"');
			
			//remove counting for weekends
			$hisdate = date('Y-m-d', strtotime($thedate));
			for($i=$nutri; $i>=0; $i--){
				$d = date('N l', strtotime($thedate.' +'.$i.' day'));				
				if($d==1 || $d==7)
					$nutri--;
			} 
			
			$supervisor = '';
			$supEmail = '';
			$sup = $grow0->supervisor;
			for($i=1; $i<=$nutri && $sup!=''; $i++){
				$grow1 = $this->commonM->getImSupervisor($sup);
				if(count($grow1)>0){
					$sup = $grow1->supervisor;
					$supervisor = $grow1->name;
					$supEmail = $grow1->supEmail;
				}				
			}
			
			$eBody = '<p>Hi,</p>';
			$eBody .= '<p>'.$supervisor.' has not taken action on Leave ID <a href="'.$this->config->base_url().'staffleaves/'.$q->leaveID.'/">#'.$q->leaveID.'</a> within '.($nutri*24).' hours from the time the leave was filed. In the event that '.$supervisor.' is on leave, you as '.(($sup=='')?'HR':'him/her immediate supervisor').' must take action on Leave ID <a href="'.$this->config->base_url().'staffleaves/'.$q->leaveID.'/">#'.$q->leaveID.'</a> otherwise this will be escalated to your immediate supervisor.</p>
				<p><br/></p>
				<p>Thank you very much.</p>
				<p>CareerPH</p>';
									
			if($supEmail=='')
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Tate Career PH Leave Needs Approval', $eBody, 'CAREERPH');
			else
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $supEmail, 'Tate Career PH Leave Needs Approval', $eBody, 'CAREERPH');
						 
			echo '<pre>';
			print_r($q);
			echo '</pre>';
		endforeach;		
		exit;
	}
	
	/***** This will run every hour to expire unused generated code for 24 hours *****/
	function expiregeneratedcode(){
		$codes = $this->dbmodel->getQueryResults('staffCodes', '*', 'status=1 AND dategenerated<="'.date('Y-m-d H:i:s', strtotime('-1 day')).'"');
		foreach($codes AS $c):			
			$this->dbmodel->updateQuery('staffCodes', array('codeID'=>$c->codeID), array('status'=>0));
			$this->commonM->addMyNotif($c->generatedBy, "The code $c->code you generated was unused and automatically expired.", 0, 1, 0);		
		endforeach;
	}
	
	/***** 
		- Reset leave credits value to 10+tateemployment years on the anniversary or employee's hire date and email accounting 
	*****/
	function resetAnnivLeaveCredits(){	
		//select active staffs with month and day start date is today
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, leaveCredits, CONCAT(fname," ",lname) AS name, startDate', 'active=1 AND startDate LIKE "%'.date('m-d').'"');
		foreach($query AS $q):		
			$diff = abs(strtotime(date('Y-m-d')) - strtotime($q->startDate));
			$years = floor($diff / (365*60*60*24));
			
			if($years>0){
				$current = 10+$years;
				$used = 0;
				//check for remaining leave credits
				$quer = $this->dbmodel->getQueryResults('staffLeaves', 'leaveCreditsUsed, status', 'empID_fk="'.$q->empID.'" AND status = 1 AND leaveCreditsUsed>0 AND hrapprover!=0 AND leaveStart>"'.date('Y').'-'.date('m-d', strtotime($q->startDate)).'"');
				foreach($quer AS $qq):
					$used += $qq->leaveCreditsUsed;
				endforeach;
			
				if($q->leaveCredits>0){
					$body = '<p>Hi,</p>
						<p><i>This is an automated message.</i><br/>
						*************************************************************************************</p>
						<p>Please be informed that today is the anniversary of '.$q->name.'. Leave credits has been reset and is now '.$current.'. Unused leave credits is '.$q->leaveCredits.'. Please facilitate conversion to cash. Thank you.</p>
						<p><br/></p>
						<p>Thanks!</p>
						<p>CAREERPH Auto-Email</p>';
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'accounting.cebu@tatepublishing.net', 'Employee\'s Anniversary', $body, 'CAREERPH' );
				}
				
				$hremail = '<p>Hi HR,</p>
						<p><i>This is an automated message.</i><br/>
						*************************************************************************************</p>
						<p>Please be informed that today is the anniversary of '.$q->name.'. Leave credits has been reset and is now '.($current-$used).'. Unused leave credits is '.$q->leaveCredits.''.(($used>0)?'. Used leave credits is '.$used.'':'').'.</p>
						<p><br/></p>
						<p>Thanks!</p>
						<p>CAREERPH Auto-Email</p>';
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Employee\'s Anniversary', $hremail, 'CAREERPH' );
				
				
				$this->dbmodel->updateQuery('staffs', array('empID'=>$q->empID), array('leaveCredits'=>($current-$used)));
								
				$nnote = 'CONGRATULATIONS! This day marks your '.$this->textM->ordinal($years).' year with Tate Publishing. During the time you have worked with us, you have significantly contributed to our company\'s success. We thank you for your enduring loyalty and diligence.<br/><br/>Your leave credits is automatically reset to '.(($used==0)?$current:($current-$used).' because you already used '.$used.' leave credits instead of '.$current.' leave credits').'.<br/><br/>We wish you happiness and success now and always.';
				$this->commonM->addMyNotif($q->empID, $nnote, 0, 1, 0);
			}
		endforeach;
		
		echo '<pre>';
		print_r($query);
		exit;
	}
		
	/*****
		- Automatically deactivate user in PT and careerph if access end date is set today
		- Cancel all in progress coaching
	*****/
	function accessenddate(){
		$dateToday = date('Y-m-d');
		$dateTodayText = date('Y-m-d h:i a');
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, endDate, accessEndDate, office, shift, staffs.active, newPositions.title', 'accessEndDate="'.$dateToday.'" OR endDate="'.$dateToday.'"', 'LEFT JOIN newPositions ON posID=position');
		
		//deactivate and send separation notice email if account is still active OR access end date is today OR empty access end date and end date is today
		foreach($query AS $uInfo):
			if($uInfo->active==1 || $uInfo->accessEndDate==$dateToday || ($uInfo->accessEndDate=='' && $uInfo->endDate==$dateToday)){
				$this->dbmodel->dbQuery('UPDATE staffs SET active="0" WHERE empID = "'.$uInfo->empID.'"');
				if($this->config->item('devmode')==false)
					$this->dbmodel->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$uInfo->username.'"');
				
					
				//send separation notice email					
				$this->emailM->emailSeparationNotice($uInfo);
				
				//cancel all in progress coaching of the employee
				$coaching = $this->dbmodel->getQueryResults('staffCoaching', 'coachID', 'empID_fk="'.$uInfo->empID.'" AND status!=1 AND status!=4');
				if(count($coaching)>0){
					foreach($coaching AS $c){
						$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$c->coachID), array('status'=>4, 'canceldata'=>'CANCELLED DUE TO TERMINATION<br/><i>careerPH '.$dateTodayText.'</i>'));
					}
				}
			}
		endforeach;
		echo count($query);

		exit;
	}
	
	public function updateStaffCIS(){
		$query = $this->dbmodel->getQueryResults('staffCIS', 'cisID, empID_fk, effectivedate, changes, dbchanges, preparedby, staffCIS.status, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby AND preparedby!=0) AS pName', 'status=1 AND effectivedate<="'.date('Y-m-d').'"', 'LEFT JOIN staffs ON empID=empID_fk');
		foreach($query AS $q):	
			$chtext = '';
			$changes = json_decode($q->dbchanges);
			if(isset($changes->title)) 
				unset($changes->title);
			if(isset($changes->position))
				$changes->levelID_fk = $this->dbmodel->getSingleField('newPositions', 'orgLevel_fk', 'posID="'.$changes->position.'"');
			if(isset($changes->sal))
				$changes->sal = $this->textM->encryptText($changes->sal);
			
			$this->dbmodel->updateQuery('staffs', array('empID'=>$q->empID_fk), $changes);	
			
			if(isset($changes->supervisor)){
				$this->commonM->addMyNotif($changes->supervisor, 'You are the new immediate supervisor of <a href="'.$this->config->base_url().'staffinfo/'.$q->username.'/">'.$q->name.'</a>.', 0, 1, 0);
			}
			
			$chQuery = json_decode($q->changes);
			foreach($chQuery AS $k=>$c):
				if($k!='salary'){
					$chtext .= 'Previous '.$this->textM->constantText('txt_'.$k).': '.$c->c.'<br/>';
					$chtext .= 'New '.$this->textM->constantText('txt_'.$k).': <b>'.$c->n.'</b><br/>';
				}
			endforeach;
				
			$this->dbmodel->updateQuery('staffCIS', array('cisID'=>$q->cisID), array('status'=>3));
			$this->commonM->addMyNotif($q->empID_fk, 'The CIS generated by '.$q->pName.' has been reflected to your employee details. Click <a href="'.$this->config->base_url().'cispdf/'.$q->cisID.'/" class="iframe">here</a> to check details.<br/>'.$chtext, 0, 1, 0);
			$this->commonM->addMyNotif($q->preparedby, 'The CIS you generated for <a href="'.$this->config->base_url().'staffinfo/'.$q->username.'/">'.$q->name.'</a> has been reflected to his/her employee details. Click <a href="'.$this->config->base_url().'cispdf/'.$q->cisID.'/" class="iframe">here</a> to check details.<br/>'.$chtext, 0, 1, 0);
		endforeach;
		echo count($query);
	}
	
	public function coachingEvaluation(){
		$dtoday = date('Y-m-d');
		$query = $this->dbmodel->getQueryResults('staffCoaching', 'coachID, empID_fk, coachedEval, status, supervisor, selfRating, supervisorsRating, CONCAT(fname," ",lname) AS name, email, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor) AS supEmail', 'coachedEval<="'.$dtoday.'" AND (status=0 OR status=2)', 'LEFT JOIN staffs ON empID=empID_fk');
		
		
		if(count($query)>0){
			foreach($query AS $q):
				//if evaluation is today send message to HR
				if($q->coachedEval==$dtoday){
					$hrEmail = 'Hello HR,<br/><br/>Please be informed that the evaluation of '.$q->name.' is due. Please use this ticket to monitor that the fully signed evaluation form is signed and on file. Click <a href="'.$this->config->base_url().'coachingform/hroptions/'.$q->coachID.'/">here</a> to view coaching details.<br/><br/>Thank you.';
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Coaching Evaluation Due', $hrEmail, 'CAREERPH');
				}
			
				if($q->selfRating==''){
					$mineEmail = 'Hello '.$q->name.',<br/><br/>Your self evaluation due already. Please <a href="'.$this->config->base_url().'coachingEvaluation/'.$q->coachID.'/" class="iframe"><b>click here</b></a> to provide.  You will receive this reminder daily unless the said evaluation is provided.<br/><br/>Thank you.';
					
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $q->email, 'Self-evaluation due for '.$q->name, $mineEmail, 'CAREERPH');
				}
				
				if($q->selfRating!='' && $q->supervisor!=0){
					$supervisorEmail = 'Hello,<br/><br/>The performance evaluation of '.$q->name.' is due already on '.date('F d, Y', strtotime($q->coachedEval)).'. Please <a href="'.$this->config->base_url().'coachingEvaluation/'.$q->coachID.'/" class="iframe"><b>click here</b></a> to '.(($q->status==2)?'finalize':'conduct').' evaluation. You will receive this reminder daily unless the said evaluation is provided.<br/><br/>Thank you.';
					
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $q->supEmail, 'Coach evaluation due for '.$q->name, $supervisorEmail, 'CAREERPH');
				}
			endforeach;	
		}
		echo 'Number of pending evaluations: '.count($query).'<br/>';
		
		//query for daily reminder to supervisors to get from HR printed docs for signing
		$printQ = $this->dbmodel->getQueryResults('staffCoaching', 'coachID, status, fname, email, supervisor, (SELECT CONCAT(fname," ", lname) AS cname FROM staffs s WHERE s.empID=staffCoaching.empID_fk) AS ename', '(status=0 AND HRoptionStatus = 1) OR (status=3 AND HRoptionStatus = 3)', 'LEFT JOIN staffs ON empID=(SELECT supervisor FROM staffs WHERE empID=empID_fk AND supervisor!=0)');
				
		foreach($printQ AS $p){
			$sBody = 'Hello '.$p->fname.',<br/><br/>The '.(($p->status==1)?'coaching':'evaluation').' form for '.$p->ename.' is printed. Please claim the form from HR. You will receive this reminder daily until the said signed '.(($p->status==1)?'coaching':'evaluation').' form is returned to HR.<br/><br/><br/>Thanks!';
			
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $p->email, 'Please  return to HR the signed '.(($p->status==1)?'coaching':'evaluation').' form for employee '.$p->ename.'', $sBody, 'CAREERPH');
		}
		
		echo 'Number of pending signed documents: '.count($printQ).'<br/>';
		exit;		
	}
	
	/***** This is run 12AM PHL time. 
		- This will notify immediate supervisors to return to HR the signed document of the NTE 
		- This will notify immediate supervisor and employee if no response after 5 business days to proceed CAR
	*****/
	public function ntePendings(){		
		//pending for upload of signed documents
		$query = $this->dbmodel->getQueryResults('staffNTE', 'nteID, empID_fk, fname, lname, status, (SELECT email FROM staffs WHERE empID=issuer LIMIT 1) AS email', '(status=1 AND nteprinted!="" AND nteuploaded="") OR (status=0 AND carprinted!="" AND caruploaded="")', 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
		
		foreach($query AS $q):
			$eBody = '<p>Hi,</p>
				<p>This is an auto-email to remind you that the printed copy of the '.(($q->status==1)?'NTE':'CAR').' document you generated for '.$q->fname.' '.$q->lname.' is still pending for upload. If you don\'t have the copy yet, please get it from HR.  Else if the document is fully signed please return it to HR. Ignore this message if you already returned the document to HR.</p>
				<p>&nbsp;</p>
				<p>Thanks!</p>';
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $q->email, 'Return copy of '.(($q->status==1)?'NTE':'CAR').' document', $eBody, 'CAREERPH');
		endforeach;
		
		//notify immediate supervisor and employee if no response within 5 days
		$queryIM = $this->dbmodel->getQueryResults('staffNTE', 'nteID, empID_fk, CONCAT(fname," ",lname) AS name, email, (SELECT email FROM staffs WHERE empID=issuer LIMIT 1) AS issueremail, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=issuer LIMIT 1) AS issuername, dateissued, DATE_ADD(dateissued, INTERVAL 7 DAY) AS dateplus5', 'status=1 AND responsedate="0000-00-00 00:00:00" AND DATE_ADD(dateissued, INTERVAL 7 DAY) <= "'.date('Y-m-d H:i:s').'"', 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
		
		$dtoday = date('Y-m-d');
		foreach($queryIM AS $qim):
			if(date('Y-m-d', strtotime($qim->dateplus5)) == $dtoday){
				$empEmail = '<p>Hi,</p>
						<p>On '.date('F d, Y', strtotime($qim->dateissued)).', an NTE was generated for you and you were given five (5) business days to respond. You have not responded within the said allotted period of time. You are therefore considered to have waived your right to be heard. '.$qim->issuername.' shall now be required to write a Corrective Action Report.</p>
						<p>&nbsp;</p>
						<p>Thanks!</p>';
				//send email to employee if today is the 5th day				
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $qim->email, 'NTE Update', $empEmail, 'CAREERPH');
			}
			
			//send email to immediate supervisor
			$reqEmail = '<p>Hi,</p>
					<p>On '.date('F d, Y', strtotime($qim->dateissued)).', an NTE was generated by you to '.$qim->name.' and the said employee was given five (5) business days to respond. '.$qim->name.' has not responded within the said allotted period of time. He/She is therefore considered to have waived his/her right to be heard. You are now required to generate the Corrective Action Report. Click <b><a href="'.$this->config->base_url().'detailsNTE/'.$qim->nteID.'/">here</a></b> to generate CAR. <span style="color:red;">Remember that you will receive a daily email reminder to generate the CAR from the date the explanation was due until the CAR is generated.</span>.</p>
					<p>&nbsp;</p>
					<p>Thanks!</p>';
			
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $qim->issueremail, 'NTE Update - You are required to generate CAR', $reqEmail, 'CAREERPH');
		endforeach;
		
		echo '<pre>';
		print_r($query);
		print_r($queryIM);
		exit;	
	}
	
	/***** This will delete all staff access logs more than 2 weeks	*****/
	public function deleteAccessLogsMoreThan2Weeks(){
		$this->dbmodel->dbQuery('DELETE FROM staffLogAccess WHERE timestamp < "'.date('Y-m-d', strtotime('-2 weeks')).'"');
	}
		
}