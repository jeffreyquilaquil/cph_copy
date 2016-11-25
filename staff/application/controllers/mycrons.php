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
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Tate Career PH Leave Needs Approval', $eBody, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
			else
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $supEmail, 'Tate Career PH Leave Needs Approval', $eBody, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
						 
			echo '<pre>';
			print_r($q);
			echo '</pre>';
		endforeach;	

		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS cancelledLeavesUnattended24Hrs '.date('Y-m-d H:i:s'), 'RESULTS: '.count($query).'<pre>'.print_r($query, true).'</pre>', 'CAREERPH');
		
		exit;
	}
	
	/***** This will run every hour to expire unused generated code for 24 hours *****/
	function expiregeneratedcode(){
		$codes = $this->dbmodel->getQueryResults('staffCodes', '*', 'status=1 AND dategenerated<="'.date('Y-m-d H:i:s', strtotime('-1 day')).'"');
		foreach($codes AS $c):			
			$this->dbmodel->updateQuery('staffCodes', array('codeID'=>$c->codeID), array('status'=>0));
			$this->commonM->addMyNotif($c->generatedBy, "The code $c->code you generated was unused and automatically expired.", 0, 1, 0);		
		endforeach;
		
		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS expiregeneratedcode '.date('Y-m-d H:i:s'), 'RESULTS: '.count($codes).'<pre>'.print_r($codes, true).'</pre>', 'CAREERPH');
		
		exit;
	}
	
	/***** 
		- Reset leave credits value to 10+tateemployment years on the anniversary or employee's hire date and email accounting 
	*****/
	function resetAnnivLeaveCredits(){	
		//select active staffs with month and day start date is today
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, leaveCredits, CONCAT(fname," ",lname) AS name, startDate', 'active=1 AND startDate LIKE "%'.date('m-d').'" AND office="PH-Cebu"');
		

		foreach($query AS $q):		

		//for staffStatusHistory
			$insert_array = array();
			$insert_array['empID_fk'] = $q->empID;
			$insert_array['field'] = 'leaveCredits';
			$insert_array['value'] = $q->leaveCredits;
			$insert_array['date_effective'] = date('Y-m-d H:i:s');
			$insert_array['date_added'] = date('Y-m-d H:i:s');

			$this->dbmodel->insertQuery('staffStatusHistory', $insert_array);
		//end staffStatusHistory

			$diff = abs(strtotime(date('Y-m-d')) - strtotime($q->startDate));
			$years = floor($diff / (365*60*60*24));
			$convertible = $years - 1;
				
			if($years>0){
				$current = 10+$years;
				$used = 0;
				//check for remaining leave credits
				$quer = $this->dbmodel->getQueryResults('staffLeaves', 'leaveCreditsUsed, status', 'empID_fk="'.$q->empID.'" AND status = 1 AND leaveCreditsUsed>0 AND hrapprover!=0 AND leaveStart>"'.date('Y').'-'.date('m-d', strtotime($q->startDate)).'"');
				foreach($quer AS $qq):
					$used += $qq->leaveCreditsUsed;
				endforeach;
				$convertible = $q->leaveCredits - $convertible;
			
				if($q->leaveCredits>0){
					$body = '<p>Hi,</p>
						<p><i>This is an automated message.</i><br/>
						*************************************************************************************</p>
						<p>Please be informed that today is the anniversary of '.$q->name.'. Leave credits has been reset and is now '.$current.'. Unused leave credits is '.$q->leaveCredits.'. '.(($convertible > 0 )?'Convertible leave credits: '. $convertible : '').' Please facilitate conversion to cash. Thank you.</p>
						<p><br/></p>
						<p>Thanks!</p>
						<p>CAREERPH Auto-Email</p>';
						
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'accounting.cebu@tatepublishing.net', 'Employee\'s Anniversary', $body, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net' );
				}
				//Unused leave credits is '.$q->leaveCredits.''.(($used>0)?'. Used leave credits is '.$used.'':'').'.</p>
				$hremail = '<p>Hi HR,</p>
						<p><i>This is an automated message.</i><br/>
						*************************************************************************************</p>
						<p>Please be informed that today is the anniversary of '.$q->name.'. Leave credits has been reset and is now '.($current).'. 
						<p><br/></p>
						<p>Thanks!</p>
						<p>CAREERPH Auto-Email</p>';
						
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Employee\'s Anniversary', $hremail, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net' );
				
				
				$this->dbmodel->updateQuery('staffs', array('empID'=>$q->empID), array('leaveCredits'=>($current)));
								
				//$nnote = 'CONGRATULATIONS! This day marks your '.$this->textM->ordinal($years).' year with Tate Publishing. During the time you have worked with us, you have significantly contributed to our company\'s success. We thank you for your enduring loyalty and diligence.<br/><br/>Your leave credits is automatically reset to '.(($used==0)?$current:($current-$used).' because you already used '.$used.' leave credits instead of '.$current.' leave credits').'.<br/><br/>We wish you happiness and success now and always.';
				$nnote = 'CONGRATULATIONS! This day marks your '.$this->textM->ordinal($years).' year with Tate Publishing. During the time you have worked with us, you have significantly contributed to our company\'s success. We thank you for your enduring loyalty and diligence.<br/><br/>Your leave credits is automatically reset to '.($current).'.<br/><br/>We wish you happiness and success now and always.';
				
				$this->commonM->addMyNotif($q->empID, $nnote, 0, 1, 0);
			}
		endforeach;
		//$this->output->enable_profiler(true);
		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS resetAnnivLeaveCredits '.date('Y-m-d H:i:s'), 'RESULTS: '.count($query).'<pre>'.print_r($query, true).'</pre>', 'CAREERPH');
		
		exit;
	}
		
	/*****
		- Automatically deactivate user in PT and careerph if access end date is set today
		- Cancel all in progress coaching
	*****/
	function accessenddate(){
		$dateToday = date('Y-m-d');
		$dateTodayText = date('Y-m-d h:i a');
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, dept, title, accessEndDate, endDate, terminationType, (SELECT email FROM staffs  s WHERE s.empID=staffs.supervisor) AS supEmail, (SELECT CONCAT(fname, " ", lname) FROM staffs  s WHERE s.empID=staffs.supervisor) AS "supName"', 'accessEndDate="'.$dateToday.'" OR endDate="'.$dateToday.'"', 'LEFT JOIN newPositions ON posID=position');
				
		//deactivate and send separation notice email if account is still active OR access end date is today OR empty access end date and end date is today
		foreach($query AS $uInfo):
						
			if($uInfo->endDate==$dateToday){
				$this->emailM->emailSeparationDateAlert($uInfo);
			}
			
			if($uInfo->accessEndDate==$dateToday || ($uInfo->accessEndDate=='0000-00-00' && $uInfo->endDate==$dateToday)){
				$this->emailM->emailAccessEndDateAlert($uInfo);
			}
			
			//cancel all in progress coaching of the employee
			$coaching = $this->dbmodel->getQueryResults('staffCoaching', 'coachID', 'empID_fk="'.$uInfo->empID.'" AND status!=1 AND status!=4');
			if(count($coaching)>0){
				foreach($coaching AS $c){
					$this->dbmodel->updateQuery('staffCoaching', array('coachID'=>$c->coachID), array('status'=>4, 'canceldata'=>'CANCELLED DUE TO TERMINATION<br/><i>careerPH '.$dateTodayText.'</i>'));
				}
			}
			
		endforeach;
		echo count($query);

		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS accessenddate '.date('Y-m-d H:i:s'), 'RESULTS: '.count($query).'<pre>'.print_r($query, true).'</pre>', 'CAREERPH');
		
		//advance notice
		$today = date_create(date('Y-m-d'));		
		$twodays = date_add($today, date_interval_create_from_date_string('2 days') );
		$twodays = date_format( $twodays, 'Y-m-d' );
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, dept, gender, title, accessEndDate, endDate, terminationType, (SELECT email FROM staffs  s WHERE s.empID=staffs.supervisor) AS supEmail, (SELECT CONCAT(fname, " ", lname) FROM staffs  s WHERE s.empID=staffs.supervisor) AS "supName"', 'accessEndDate="'.$twodays.'" OR endDate="'.$twodays.'"', 'LEFT JOIN newPositions ON posID=position');

		foreach( $query as $info ){
			$this->emailM->emailSeparationDateAdvanceNotice( $info );
		}
		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS accessenddate advance notice'.date('Y-m-d H:i:s'), 'RESULTS: '.count($query).'<pre>'.print_r($query, true).'</pre>', 'CAREERPH');
		


		exit;
	}
	
	public function updateStaffCIS(){
		$query = $this->dbmodel->getQueryResults('staffCIS', 'cisID, empID_fk, effectivedate, changes, dbchanges, preparedby, staffCIS.status, CONCAT(fname," ",lname) AS name, username, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=preparedby AND preparedby!=0) AS pName', 'status=1 AND effectivedate<="'.date('Y-m-d').'"', 'LEFT JOIN staffs ON empID=empID_fk');

		foreach($query AS $q):
			$chtext = '';
			$changes = json_decode($q->dbchanges);

			//for staffStatusHistory
			$status_change = json_decode($q->changes);
			foreach($status_change as $status_old => $status_old_val ){
				$insert_array = array();
				$insert_array['empID_fk'] = $q->empID_fk;
				$insert_array['field'] = $status_old;
				$insert_array['value'] = $status_old_val->c;
				$insert_array['date_effective'] = $q->effectivedate;
				$insert_array['date_added'] = date('Y-m-d H:i:s');

				$this->dbmodel->insertQuery('staffStatusHistory', $insert_array);
			}
			//end

			if(isset($changes->title)) 
				unset($changes->title);
			if(isset($changes->position)){
				$changes->levelID_fk = $this->dbmodel->getSingleField('newPositions', 'orgLevel_fk', 'posID="'.$changes->position.'"');
				
				$nposdata = $this->dbmodel->getSingleInfo('newPositions', 'title, dt', 'posID="'.$changes->position.'"');
				$this->dbmodel->ptdbQuery('UPDATE eData SET title="'.$nposdata->title.'", dt="'.$nposdata->dt.'" WHERE u="'.$q->username.'"');
			}
				
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
		
		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS updateStaffCIS '.date('Y-m-d H:i:s'), 'RESULTS: '.count($query).'<pre>'.print_r($query, true).'</pre>', 'CAREERPH');
		exit;
	}
	
	public function coachingEvaluation(){
		$dtoday = date('Y-m-d');
		$query = $this->dbmodel->getQueryResults('staffCoaching', 'coachID, empID_fk, coachedEval, status, supervisor, selfRating, supervisorsRating, CONCAT(fname," ",lname) AS name, email, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor) AS supEmail, (SELECT fname FROM staffs s WHERE s.empID=staffs.supervisor) AS supName', 'coachedEval<="'.$dtoday.'" AND (status=0 OR status=2) AND active=1', 'LEFT JOIN staffs ON empID=empID_fk');
		
		$toMeEmailContent = '';
		
		if(count($query)>0){
			foreach($query AS $q):
				//if evaluation is today send message to HR
				if($q->coachedEval==$dtoday){
					$hrEmail = 'Hello HR,<br/><br/>Please be informed that the evaluation of '.$q->name.' is due. Please use this ticket to monitor that the fully signed evaluation form is signed and on file. Click <a href="'.$this->config->base_url().'coachingform/hroptions/'.$q->coachID.'/">here</a> to view coaching details.<br/><br/>Thank you.';
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', 'Coaching Evaluation Due', $hrEmail, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
					
					$toMeEmailContent .= 'hr.cebu@tatepublishing.net --- Coaching Evaluation Due<br/>';
				}
			
				//stop notification of JVilla
				if($q->selfRating=='' && $q->empID_fk!=250){
					$mineEmail = 'Hello '.$q->name.',<br/><br/>Your self evaluation due already. Please <a href="'.$this->config->base_url().'coachingEvaluation/'.$q->coachID.'/" class="iframe"><b>click here</b></a> to provide.  You will receive this reminder daily unless the said evaluation is provided.<br/><br/>Thank you.';
					
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $q->email, 'Self-evaluation due for '.$q->name, $mineEmail, 'CAREERPH', $q->supEmail, 'hrcebu.notify@tatepublishing.net');
					
					$toMeEmailContent .= $q->email.' --- Self-evaluation due for '.$q->name.'<br/>';					
					$this->commonM->addMyNotif($q->empID_fk, $mineEmail, 2, 1, 0);	
				}
				
				if($q->selfRating!='' && $q->supervisor!=0){
					$supervisorEmail = 'Hello '.$q->supName.',<br/><br/>The performance evaluation of '.$q->name.' is due already on '.date('F d, Y', strtotime($q->coachedEval)).'. Please <a href="'.$this->config->base_url().'coachingEvaluation/'.$q->coachID.'/" class="iframe"><b>click here</b></a> to '.(($q->status==2)?'finalize':'conduct').' evaluation. You will receive this reminder daily unless the said evaluation is provided.<br/><br/>Thank you.';
					
					$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $q->supEmail, 'Coach evaluation due for '.$q->name, $supervisorEmail, 'CAREERPH', $q->email, 'hrcebu.notify@tatepublishing.net');
					
					$toMeEmailContent .= $q->supEmail.' --- Coach evaluation due for '.$q->name.'<br/>';
					$this->commonM->addMyNotif($q->supervisor, $supervisorEmail, 2, 1, 0);	
				}
			endforeach;	
		}
		echo 'Number of pending evaluations: '.count($query).'<br/>';
		
		//query for daily reminder to supervisors to get from HR printed docs for signing
		$printQ = $this->dbmodel->getQueryResults('staffCoaching', 'coachID, status, fname, email, supervisor, (SELECT CONCAT(fname," ", lname) AS cname FROM staffs s WHERE s.empID=staffCoaching.empID_fk) AS ename', 'active=1 AND (status=0 AND HRoptionStatus = 1) OR (status=3 AND HRoptionStatus = 3)', 'LEFT JOIN staffs ON empID=(SELECT supervisor FROM staffs WHERE empID=empID_fk AND supervisor!=0)');
				
		foreach($printQ AS $p){
			$sBody = 'Hello '.$p->fname.',<br/><br/>The '.(($p->status==1)?'coaching':'evaluation').' form for '.$p->ename.' is printed. Please claim the form from HR. You will receive this reminder daily until the said signed '.(($p->status==1)?'coaching':'evaluation').' form is returned to HR.<br/><br/><br/>Thanks!';
			
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $p->email, 'Please  return to HR the signed '.(($p->status==1)?'coaching':'evaluation').' form for employee '.$p->ename, $sBody, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
			
			$toMeEmailContent .= $p->email.' --- Please  return to HR the signed '.(($p->status==1)?'coaching':'evaluation').' form for employee '.$p->ename.'<br/>';
		}
		
		
		if(!empty($toMeEmailContent)){
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS '.date('Y-m-d').' - coachingEvaluation', $toMeEmailContent, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
		}
		
		echo 'Number of pending signed documents: '.count($printQ).'<br/>';
		
		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS coachingEvaluation pending evaluations '.date('Y-m-d H:i:s'), 'RESULTS: '.count($query).'<pre>'.print_r($query, true).'</pre>', 'CAREERPH');
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS coachingEvaluation pending signed documents '.date('Y-m-d H:i:s'), 'RESULTS: '.count($printQ).'<pre>'.print_r($printQ, true).'</pre>', 'CAREERPH');
		
		exit;		
	}
	
	/***** This is run 12AM PHL time. 
		- This will notify immediate supervisors to return to HR the signed document of the NTE 
		- This will notify immediate supervisor and employee if no response after 5 business days to proceed CAR
	*****/
	public function ntePendings(){		
		//pending for upload of signed documents
		$query = $this->dbmodel->getQueryResults('staffNTE', 'nteID, empID_fk, fname, lname, status, (SELECT email FROM staffs WHERE empID=issuer LIMIT 1) AS email', '(status=1 AND nteprinted!="" AND nteuploaded="") OR (status=0 AND carprinted!="" AND caruploaded="")', 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
		
		$toMeEmailContent = '';
		
		foreach($query AS $q):
			$eBody = '<p>Hi,</p>
				<p>This is an auto-email to remind you that the printed copy of the '.(($q->status==1)?'NTE':'CAR').' document you generated for '.$q->fname.' '.$q->lname.' is still pending for upload. If you don\'t have the copy yet, please get it from HR.  Else if the document is fully signed please return it to HR. Ignore this message if you already returned the document to HR.</p>
				<p>&nbsp;</p>
				<p>Thanks!</p>';
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $q->email, 'Return copy of '.(($q->status==1)?'NTE':'CAR').' document', $eBody, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
			
			$toMeEmailContent .= $q->email.' --- Return copy of '.(($q->status==1)?'NTE':'CAR').' document<br/>';
		endforeach;
		
		//notify immediate supervisor and employee if no response within 5 days
		$queryIM = $this->dbmodel->getQueryResults('staffNTE', 'nteID, empID_fk, CONCAT(fname," ",lname) AS name, email, (SELECT email FROM staffs WHERE empID=issuer LIMIT 1) AS issueremail, (SELECT CONCAT(fname," ",lname) AS n FROM staffs WHERE empID=issuer LIMIT 1) AS issuername, dateissued, DATE_ADD(dateissued, INTERVAL 7 DAY) AS dateplus5', 'active = 1 AND status=1 AND responsedate="0000-00-00 00:00:00" AND DATE_ADD(dateissued, INTERVAL 7 DAY) <= "'.date('Y-m-d H:i:s').'"', 'LEFT JOIN staffs ON empID=empID_fk', 'dateissued DESC');
		
		$dtoday = date('Y-m-d');
		foreach($queryIM AS $qim):
			if(date('Y-m-d', strtotime($qim->dateplus5)) == $dtoday){
				$empEmail = '<p>Hi,</p>
						<p>On '.date('F d, Y', strtotime($qim->dateissued)).', an NTE was generated for you and you were given five (5) business days to respond. You have not responded within the said allotted period of time. You are therefore considered to have waived your right to be heard. '.$qim->issuername.' shall now be required to write a Corrective Action Report.</p>
						<p>&nbsp;</p>
						<p>Thanks!</p>';
				//send email to employee if today is the 5th day				
				$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $qim->email, 'NTE Update', $empEmail, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
				
				$toMeEmailContent .= $qim->email.' --- NTE Update<br/>';
			}
			
			//send email to immediate supervisor
			$reqEmail = '<p>Hi,</p>
					<p>On '.date('F d, Y', strtotime($qim->dateissued)).', an NTE was generated by you to '.$qim->name.' and the said employee was given five (5) business days to respond. '.$qim->name.' has not responded within the said allotted period of time. He/She is therefore considered to have waived his/her right to be heard. You are now required to generate the Corrective Action Report. Click <b><a href="'.$this->config->base_url().'detailsNTE/'.$qim->nteID.'/">here</a></b> to generate CAR. <span style="color:red;">Remember that you will receive a daily email reminder to generate the CAR from the date the explanation was due until the CAR is generated.</span></p>
					<p>&nbsp;</p>
					<p>Thanks!</p>';
			
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $qim->issueremail, 'NTE Update - You are required to generate CAR', $reqEmail, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
			
			$toMeEmailContent .= $qim->issueremail.' --- NTE Update - You are required to generate CAR<br/>';
		endforeach;
		
		if(!empty($toMeEmailContent)){
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS '.date('Y-m-d').' - ntePendings', $toMeEmailContent, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
		}
		
		echo '<pre>';
		print_r($query);
		print_r($queryIM);
		
		
		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS ntePendings query'.date('Y-m-d H:i:s'), 'RESULTS: '.count($query).'<pre>'.print_r($query, true).'</pre>', 'CAREERPH');
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS ntePendings queryIM'.date('Y-m-d H:i:s'), 'RESULTS: '.count($queryIM).'<pre>'.print_r($queryIM, true).'</pre>', 'CAREERPH');
		
		exit;	
	}
	
	/***** This will delete all staff access logs more than 2 weeks	*****/
	public function deleteAccessLogsMoreThan2Weeks(){
		$this->dbmodel->dbQuery('DELETE FROM staffLogAccess WHERE timestamp < "'.date('Y-m-d', strtotime('-2 weeks')).'"');
	}
	
	public function email90thdayemployment(){
		$toMeEmailContent = '';
		$start90days = date('Y-m-d', strtotime('-90 days'));
		
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, fname, lname, email, startDate, evalID, (SELECT email FROM staffs s WHERE s.empID=staffs.supervisor AND supervisor!=0) AS imEmail', 'active=1 AND startDate<="'.$start90days.'" AND empStatus="probationary" AND evalID IS NULL', 'LEFT JOIN staffEvaluation ON empID_fk=empID');
				
		foreach($query AS $q){
			$eBody = '<p>Hello '.trim($q->fname.' '.$q->lname).'!</p><p>';
				if($q->startDate==$start90days)
					$eBody .= 'Today is your 90th day in Tate Publishing and you ';
				else
					$eBody .= 'You ';
				
				$eBody .= 'are up for probationary performance evaluation! To begin this process please submit your self-evaluation in Careerph:<br/>
				<a href="'.$this->config->base_url().'evaluationself/">careerph.tatepublishing.net</a></p>

				<p>You will continue to receive this reminder until you submit your self evaluation.<br/>
				Remember that you can be regularized as early as now but your immediate supervisor cannot take action unless you send your self-evaluations.</p>
				<p>Thank you very much for all your hardwork! Good luck on your 90th day review!</p>
				<p><br/></p>
				<p><b>Tate Publishing HR</b></p>';
				
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $q->email, 'CareerPH - Submit your Self-Evaluation', $eBody, 'CAREERPH', $q->imEmail, 'hrcebu.notify@tatepublishing.net');
			
			$toMeEmailContent .= $q->email.' --- CareerPH - Submit your Self-Evaluation<br/>';
		}
		echo count($query);
		
		if(!empty($toMeEmailContent)){
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS '.date('Y-m-d').' - email90thdayemployment', $toMeEmailContent, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
		}
		
		///DEV LOGS
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS email90thdayemployment '.date('Y-m-d H:i:s'), 'RESULTS: '.count($query).'<pre>'.print_r($query, true).'</pre>', 'CAREERPH');
		exit;
	}
	
	public function emailPreEmploymentRequirements(){
		$toMeEmailContent = '';
		$reqArr = array();
		$queryRequirements = $this->dbmodel->getQueryResults('staffPerRequirements', 'perID, perName, perDesc');
		foreach($queryRequirements AS $r){
			$reqArr[$r->perID] = '<b>'.$r->perName.'</b> ('.$r->perDesc.')';
		}
		
		$query = $this->dbmodel->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) AS name, email, perStatus', 'perStatus<100 AND active=1 AND office="PH-Cebu"');
		
		foreach($query AS $q){
			$reqArr2 = $reqArr;
			$eBody = '<p>Hello '.$q->name.',</p>
				<p>We are strengthening the integrity of our employee records. In this process, it is found that your preemployment requirements is only '.$q->perStatus.'% complete.</p>
				<p>Below are the documents still missing:</p> <ol>';
				
				if($q->perStatus!=0){
					$queryReq = $this->dbmodel->getQueryResults('staffPerEmpStatus', 'perID_fk', 'empID_fk="'.$q->empID.'" AND perType=1');
					foreach($queryReq AS $rr) 
						unset($reqArr2[$rr->perID_fk]);
				}				
					
				foreach($reqArr2 AS $r){
					$eBody .= '<li>'.$r.'</li>';
				}
				
			$eBody .= '</ol><p>If you have already submitted the above documents to HR, please reply to this email to confirm so, otherwise, please provide the documents to HR asap. You will regularly receive this reminder until your preemployment requirements is 100% complete.</p>
				<p>&nbsp;</p>
				<p>Thank you very much for understanding.</p>
				<p>Tate Publishing HR</p>';
			
			$this->emailM->sendEmail( 'hr.cebu@tatepublishing.net', $q->email, 'CareerPH - Pre-Employment Requirements', $eBody, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
			
			$notif = 'From: hr.cebu@tatepublishing.net<br/>					
					To: '.$q->email.'<br/>
					Subject: CareerPH - Pre-Employment Requirements<br/>
					Message: <br/>'.$eBody.'<br/></br><i>---- Message sent from CareerPH ----</i>';
			
			$this->commonM->addMyNotif($q->empID, $notif, 0, 1, 0);				
			$toMeEmailContent .= $q->email.' --- CareerPH - Pre-Employment Requirements<br/>';			
		}
		
		if(!empty($toMeEmailContent)){
			$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', 'ludivina.marinas@tatepublishing.net', 'CRON RESULTS '.date('Y-m-d').' - emailPreEmploymentRequirements', $toMeEmailContent, 'CAREERPH', '', 'hrcebu.notify@tatepublishing.net');
		}
		
	}
	
	/*
		Reports that will run every month that will calculate the employees compensation
	*/
	public function compensationReports(){
		
		$query = "SELECT * FROM staffs WHERE active = 1 AND office = 'PH-Cebu'";
		$all_results = $this->dbmodel->getQueryArrayResults('staffs', '*', 'active = 1 AND office = "PH-Cebu"');
		
		$staff_array = array();		
		//if we have results the calculate was are needed
		if( !empty($all_results) ){			
			//when for when is the report
			$staff_array['for_month'] = date('Y-m-d');			
			//when the report was generated
			$staff_array['date_generated'] = date('Y-m-d H:i:s');			
			//get total number of employees
			$staff_array['total_employees'] = count($all_results);			
			//traverse the array then find the treasure
			foreach( $all_results as $staff ){				
				//compute all supervisors, rank and file, and their compensation
				if( $staff->is_supervisor == 1 ){
					$staff_array['total_wage_supervisor'] += $this->textM->decryptText( $staff->sal );
					$staff_array['total_supervisor'] += 1;
				} else {
					$staff_array['total_wage_rankfile'] += $this->textM->decryptText( $staff->sal );
					$staff_array['total_rankfile'] += 1;
				}
			}	
		}		
		if( !empty($staff_array) ){
			$staff_array['total_wage_rankfile'] = $this->textM->encryptText( $staff_array['total_wage_rankfile'] );
			$staff_array['total_wage_supervisor'] = $this->textM->encryptText( $staff_array['total_wage_supervisor'] );
			$this->dbmodel->insertQuery('staffCompensationReports', $staff_array);
		}
		exit();
	}//end of compensation reports
	
	
	public function nearDueEvaluations(){
		$results = $this->dbmodel->getQueryResults('staffEvaluationNotif', 'notifyId, empId, evaluatorId, evalDate, status','evalDate between NOW() and DATE_ADD(NOW(), INTERVAL 10 DAY) AND STATUS = 0 OR STATUS  = 1');
		
		foreach($results as $value){
			$employee = $this->dbmodel->getSingleInfo('staffs','CONCAT(fname," ",lname) as "name", email, supervisor', 'empId = '.$value->empId);
			$evaluator = $this->dbmodel->getSingleInfo('staffs','CONCAT(fname," ",lname) as "name", email', 'empID = '.$value->evaluatorId);
			$supervisor1 = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) as "name", email, supervisor','empId = '.$employee->supervisor);
			$supervisor2 = $this->dbmodel->getSingleInfo('staffs', 'CONCAT(fname," ",lname) as "name", email', 'empId = '.$supervisor1->supervisor);
			$cc = [$evaluator->email, $supervisor1->email, $supervisor2->email];
			$cc = implode(',', $cc);
			if($value->status == 0){
				$to = $employee->email;
			
				$body = 'Hi '.$employee->name.',<br><br>
					You are due for a performance evaluation on '.$value->evalDate.'. Please <a href="'.$this->config->base_url().'evaluations/performanceeval/2/'.$value->empId.'/'.$value->evaluatorId.'/'.$value->notifyId.'" target="_blank">click here</a> to conduct self-evaluation. You will receive this reminder daily unless the said evaluation is provided.<br><br>Thank you.';
				$to = $employee->email;
				$this->commonM->addMyNotif($value->empId,'Hi '.$employee->name.',<br>You are due for a performance evaluation on '.date('F d, Y', strtotime($value->evalDate)).'. Please <a href="evaluations/performanceeval/2/'.$value->empId.'/'.$value->evaluatorId.'/'.$value->notifyId.'">click here</a> to conduct self-evaluation. You will recieve this reminder daily unless the said evaluation is provided.',2, 0,0);
			}else{
				$body = 'Hello '.$evaluator->name.',<br><br>
					The performance evaluation of '.$employee->name.' is due already on '.date('F d, Y', strtotime($value->evalDate)).'. Please <a href="'.$this->config->base_url().'evaluations/performanceeval/1/'.$value->empId.'/'.$value->evaluatorId.'/'.$value->notifyId.'" target="_blank">click here</a> to conduct evaluation. You will receive this reminder daily unless the said evaluation is provided.<br><br>Thank you.';
				$to = $evaluator->email;
				$this->commonM->addMyNotif($value->evaluatorId, $employee->name.' has entered his self-rating for his performance evaluation. Please <a href="evaluations/performanceeval/1/'.$value->empId.'/'.$value->evaluatorId.'/'.$value->notifyId.'">click here</a> to enter evaluators rating',2, 0,0);
			}
			$from = 'careers.cebu@tatepublishing.net';
			$fromName = 'CAREERPH';
			$subject = "Performance evaluation due for ".$employee->name;
			$this->emailM->sendEmail($from, $to, $subject, $body, $fromName, $cc);
		}
		exit();
	}
}