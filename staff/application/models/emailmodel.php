<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Emailmodel extends CI_Model {
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();	
    }
	
	function sendEmail( $from, $to, $subject, $body, $fromName='', $CC='', $BCC='' ){
		$url = 'https://pt.tatepublishing.net/api.php?method=sendGenericEmail';
		  /*
		   * from = sender's email
		   * fromName = sender's name
		   * BCC = cc's
		   * replyTo = reply to email address
		   * sendTo = recipient email address
		   * subject = email subject
		   * body = email body
		   */
		
		if($this->config->item('devmode')===true){
			$subject = $subject.' to-'.$to;
			$to = $this->config->item('toEmail');
			
			if(!empty($CC)){
				$subject .= '==CC: -'.$CC;
				$CC = '';
			}
			
			if(!empty($BCC)){
				$subject .= '==BCC: -'.$BCC;
				$BCC = '';
			}
		}
				
		
		$body = '<div style="font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:14px;">'.$body.'</div>';
		$fields = array(
			'from' => $from,
			'sendTo' => $to,
			'subject' => $subject,
			'body' => $body,
			'CC' => $CC,
			'BCC' => $BCC
		);

		if( !empty($fromName) ){
			$fields['fromName'] = $fromName;
		}
		//build the urlencoded data
		$postvars='';
		$sep='';
		foreach($fields as $key=>$value) { 
		   $postvars.= $sep.urlencode($key).'='.urlencode($value); 
		   $sep='&'; 
		}
		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,count($fields));
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		
		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
	}
	
	//send email notification notice
	public function emailSeparationNotice($empID){
		$dateToday = date('Y-m-d');
		$info = $this->dbmodel->getSingleInfo('staffs', 'empID, username, CONCAT(fname," ",lname) AS name, dept, title, accessEndDate, endDate, terminationType, (SELECT email FROM staffs  s WHERE s.empID=staffs.supervisor) AS supEmail', 'empID="'.$empID.'"','LEFT JOIN newPositions ON posID=position');
		
		//On separation date send Separation Date Alert
		if($info->endDate==$dateToday){
			$this->emailM->emailSeparationDateAlert($info);
		}
		
		//On Access End Date send access End date alert
		if(($info->accessEndDate!='0000-00-00' && $info->accessEndDate==$dateToday) ||
			($info->accessEndDate=='0000-00-00' && $info->endDate==$dateToday)
		){
			$this->emailM->emailAccessEndDateAlert($info);
		}
		
		//send end of employment notice if access or end date entered and date is after today
		if(($info->endDate!='0000-00-00' && strtotime($info->endDate)>strtotime($dateToday)) ||
			($info->accessEndDate!='0000-00-00' && strtotime($info->accessEndDate)>strtotime($dateToday))
		){
			$this->emailM->emailEndEmploymentNotice($info);
		}
		
		//send urgent terminate all access for employee email if separation or access date entered before today
		if( ($info->accessEndDate!='0000-00-00' && strtotime($info->accessEndDate)<strtotime($dateToday)) ||
			($info->accessEndDate=='0000-00-00' && $info->endDate!='0000-00-00' && strtotime($info->endDate)<strtotime($dateToday))
		){
			$this->emailM->emailUrgentTerminateAllAccess($info);
		}	
	}
	
	//send email if end date is today
	public function emailSeparationDateAlert($info){
		$de = 'kent.ybanez@tatepublishing.net';
		$sur = 'it.security@tatepublishing.net';
		$sujet = 'Separation Date Alert ('.$info->name.')';
		
		$termArr = $this->textM->constantArr('terminationType');
		$corps = '<h2 style="color:red;">Separation Date Alert ('.date('F d, Y', strtotime($info->endDate)).')</h2>';
		$corps .= '<b>Employee Name:</b> '.$info->name.'<br/>';
		$corps .= '<b>Department:</b> '.$info->dept.'<br/>';
		$corps .= '<b>Position:</b> '.$info->title.'<br/>';
		$corps .= '<b>Access Ended:</b> '.(($info->accessEndDate!='0000-00-00')?date('F d, Y', strtotime($info->accessEndDate)):date('F d, Y', strtotime($info->endDate))).'<br/>';
		if($info->terminationType!=0) $corps .= '<b>Termination Reason:</b> '.$termArr[$info->terminationType];
		
		$corps .= '<br/><br/>';
		$corps .= '<p>Please verify that all access has been terminated for the above employee and that all email and phone forwarding is functioning properly.</p>';
		$corps .= '<p><br/><br/><i>THIS IS FROM CAREERPH</i></p>';
		
		$this->emailM->sendEmail($de, $sur, $sujet, $corps, 'CareerPH');		
	}
	
	//send email if access end date is today AND deactivate staff and PT accesses
	public function emailAccessEndDateAlert($info){
		$de = 'kent.ybanez@tatepublishing.net';
		$sur = 'it.security@tatepublishing.net';
		$sujet = 'Access End Date Alert for '.$info->name;
		
		$termArr = $this->textM->constantArr('terminationType');
		$adate = (($info->accessEndDate!='0000-00-00')?$info->accessEndDate:$info->endDate);
		
		$corps = '<h2 style="color:red;">Access End Date Alert ('.date('F d, Y', strtotime($adate)).')</h2>';
		$corps .= '<b>Employee Name:</b> '.$info->name.'<br/>';
		$corps .= '<b>Department:</b> '.$info->dept.'<br/>';
		$corps .= '<b>Position:</b> '.$info->title.'<br/>';
		if($info->endDate!='0000-00-00') $corps .= '<b>Separation Date:</b> '.date('F d, Y', strtotime($info->endDate)).'<br/>';
		if($info->terminationType!=0) $corps .= '<b>Termination Reason:</b> '.$termArr[$info->terminationType];
		
		$corps .= '<br/><br/>';
		$corps .= '<b>Access Termination Checklist</b><br/>';
		$corps .= 'IT Staff, please verify that the following tasks are completed today:<br/>';
		$corps .= '<ul>';
		$corps .= '<li>PT Account is deactivated</li>';
		$corps .= '<li>CareerPH account is deactivated</li>';
		$corps .= '<li>Email account is deactivated (if applicable)</li>';
		$corps .= '<li>Phone account is deactivated (if applicable)</li>';
		$corps .= '<li>Access to servers is revoked</li>';
		$corps .= '<li>Access to workstation is revoked</li>';
		$corps .= '</ul>';
		$corps .= '<p><br/>Please contact the employee\'s immediate supervisor or the department head to ensure that emails and phone calls are forwarded/redirected to the appropriate individual.</p>';		
		$corps .= '<p><br/><br/><i>THIS IS FROM CAREERPH</i></p>';
		
		$this->emailM->sendEmail($de, $sur, $sujet, $corps, 'CareerPH');
		
		//deactivate staff and PT accesses
		$this->dbmodel->updateQuery('staffs', array('empID'=>$info->empID), array('active'=>'0'));
		if($this->config->item('devmode')==false) $this->dbmodel->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$info->username.'"');
	}
	
	//send email if access and end date entered after today
	public function emailEndEmploymentNotice($info){
		$de = 'kent.ybanez@tatepublishing.net';
		$sur = 'helpdesk.cebu@tatepublishing.net,'.$info->supEmail;
		$sujet = 'End of Employment Notice';		
		
		$termArr = $this->textM->constantArr('terminationType');
		$corps = '<h2 style="color:red;">End of Employment Notice</h2>';
		$corps .= '<b>Employee Name:</b> '.$info->name.'<br/>';
		$corps .= '<b>Department:</b> '.$info->dept.'<br/>';
		$corps .= '<b>Position:</b> '.$info->title.'<br/>';
		if($info->endDate!='0000-00-00') $corps .= '<b>Separation Date:</b> '.date('F d, Y', strtotime($info->endDate)).'<br/>';
		if($info->terminationType!=0) $corps .= '<b>Termination Reason:</b> '.$termArr[$info->terminationType];
				
		$corps .= '<p style="color:red;"><b>Access End Date:</b> '.(($info->accessEndDate=='0000-00-00')?date('F d, Y', strtotime($info->endDate)):date('F d, Y', strtotime($info->accessEndDate))).'</p>';
		$corps .= '<p><br/>IT Staff, please prepare to terminate this employee\'s access to all company systems on the access end date above.</p>';
		$corps .= '<p><br/><br/><i>THIS IS FROM CAREERPH</i></p>';
		
		$this->emailM->sendEmail($de, $sur, $sujet, $corps, 'CareerPH');
	}
	
	//send email if access and end date entered on or before
	public function emailUrgentTerminateAllAccess($info){
		$de = 'kent.ybanez@tatepublishing.net';
		$sur = 'it.security@tatepublishing.net,helpdesk.cebu@tatepublishing.net';
		$cc = 'marianne.velasco@tatepublishing.net,diana.bartulin@tatepublishing.net,curtis.winkle@tatepublishing.net';
		$sujet = 'URGENT: Terminate All Access For '.$info->name.' Immediately';
				
		$termArr = $this->textM->constantArr('terminationType');
		$dateToday = date('Y-m-d');
		$acdate = (($info->accessEndDate!='0000-00-00')?$info->accessEndDate:$info->endDate);
		
		$corps = '<h2 style="color:red;">ALERT: Terminate All Access For '.$info->name.' Immediately</h2>';
		$corps .= '<b>Employee Name:</b> '.$info->name.'<br/>';
		$corps .= '<b>Department:</b> '.$info->dept.'<br/>';
		$corps .= '<b>Position:</b> '.$info->title.'<br/>';
		if($info->endDate!='0000-00-00') $corps .= '<span style="color:red;"><b>Separation Date:</b> '.date('F d, Y', strtotime($info->endDate)).'</span><br/>';
		$corps .= '<span style="color:red;"><b>Access End Date:</b> '.date('F d, Y', strtotime($acdate)).'</span><br/>';
		if($info->terminationType!=0) $corps .= '<b>Termination Reason:</b> '.$termArr[$info->terminationType];
		if($acdate==$dateToday){
			$corps .= '<p><br/><br/>Please terminate all access for the above named employee immediately. This employee\'s access has been scheduled to end today, '.date('F d, Y', strtotime($acdate)).'.</p>';
		}else{
			$datediff = strtotime($dateToday) - strtotime($acdate);
			$ago = floor($datediff/(60*60*24));
			
			$corps .= '<p><br/><br/>Please terminate all access for the above named employee immediately. This employee\'s access has been scheduled to on '.date('F d, Y', strtotime($acdate)).', which was '.$ago.' days ago.</p>';
		}
		
		$corps .= '<p><br/><br/><i>THIS IS FROM CAREERPH</i></p>';
		$this->emailM->sendEmail($de, $sur, $sujet, $corps, 'CareerPH', $cc);	

		//deactivate staff and PT accesses
		$this->dbmodel->updateQuery('staffs', array('empID'=>$info->empID), array('active'=>'0'));
		if($this->config->item('devmode')==false) $this->dbmodel->ptdbQuery('UPDATE staff SET active="N" WHERE username = "'.$info->username.'"');		
	}

	public function emailForgotPassword($email, $fname, $username){
		$body = '<p>Hi '.$fname.',</p>
				<p>Click <a href="'.$this->config->base_url().'forgotpassword/'.md5($username).'/'.'">here</a> to reset password.</p>
				<p><br/></p>
				<p>Thanks!</p>
				<p>CareerPH</p>
		';
		$this->emailM->sendEmail( 'careers.cebu@tatepublishing.net', $email, 'CareerPH Forgot Password', $body, 'CareerPH' );
	}
	
	function sendDeActivateEmail($active, $name){
		$abody = '<p>Hi,</p>';
									
		if($active==1){
			$subject = 'ACTIVATED PT USER';
			$abody .= $this->user->name.' ACTIVATED the account of "'.$name.'". Please check if this is correct.';
		}else{
			$subject = 'DEACTIVATED PT USER';
			$abody .= $this->user->name.' DEACTIVATED the account of "'.$name.'". Please check if this is correct.';
		}
		
		$abody .= '<p><br/></p>
				<p>Thanks!</p>
				<p>CAREERPH</p>';
		
		$this->emailM->sendEmail('careers.cebu@tatepublishing.net', 'hr.cebu@tatepublishing.net', $subject, $abody, 'CAREERPH', 'helpdesk.cebu@tatepublishing.net');
	}
	
	
	
	//TIMECARD EMAILSSSSSSSSS
	public function emailTimecard($type, $row){
		$from = 'careers.cebu@tatepublishing.net';
		if($type=='notimein'){
			$subject = 'CareerPH Timecard No Time In Reminder';
			$to = $row->email;
			$cc = $row->supEmail;
			
			$body = '<p><b>Please ignore if you are not part of the test group.</b><br/></p>';
			$body .= '<p>Hi '.trim($row->fname).',</p>';
			$body .= '<p>This is an auto email to remind you that you do not have time in yet for today\'s schedule <b>'.date('h:i a', strtotime($row->schedIn)).' - '.date('h:i a', strtotime($row->schedOut)).'</b>.</p>';
			$body .= '<p><i>Kindly ignore this message if you already clocked in.</i></p>';
			$body .= '<p>&nbsp;</p>';
			$body .= '<p>Thanks!<br/>CAREERPH</p>';
		}else if($type=='noclockout2hours'){
			$subject = 'CareerPH Timecard No Time Out Reminder';
			$to = $row->email;
			$cc = $row->supEmail;
			
			$body = '<p><b>Please ignore if you are not part of the test group.</b><br/></p>';
			$body .= '<p>Hi '.trim($row->fname).',</p>';
			$body .= '<p>This is an auto email to remind you that you do not have time out recorded for today\'s schedule <b>'.date('h:i a', strtotime($row->schedIn)).' - '.date('h:i a', strtotime($row->schedOut)).'</b>.</p>';
			$body .= '<p><i>Kindly ignore this message if you already clocked out or click <a href="'.$this->config->base_url().'timecard/timelogs/">here</a> to view your timelogs.</i></p>';
			$body .= '<p>&nbsp;</p>';
			$body .= '<p>Thanks!<br/>CAREERPH</p>';
		}
		
		if(!empty($to)){
			$this->emailM->sendEmail($from, $to, $subject, $body, 'CareerPH', $cc, 'ludivina.marinas@tatepublishing.net');
		}
			
	}
	
	public function emailTimecardCheckLogforPay($period, $deadline){
		$subject = 'DEADLINE to Resolve Attendance for Payroll Calculation';
		$from = 'careers.cebu@tatepublishing.net';
		$to = 'dayshift.cebu@tatepublishing.net,nightshift.cebu@tatepublishing.net';
		$cc = 'leaders.cebu@tatepublishing.net';
		
		$body = '<p>Hi All,</p>';
		$body .= '<p>Please be reminded to double-check your time card logs on "<a href="'.$this->config->base_url().'timecard/">Timecard and Payroll</a>" > "<a href="'.$this->config->base_url().'timecard/timelogs/">My Time Logs</a>"</a> page in CareerPH. Any status that does not equal to "Published to Payroll (Number of Hours)" from <b>'.$period.'</b> may lead to deductions. So even if you have already passed your forms through careerph.tatepublishing.net, please follow up with us until ALL your time logs show "Published to Payroll (Number of Hours)".</p>';
		$body .= '<p>If you have dates where your attendance is not published to payroll, please send message to HR by clicking "Request Update" on the date options or email <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a> AND CC:<a href="mailto:accounting.cebu@tatepublishing.net">accounting.cebu@tatepublishing.net</a> on or before <b>'.$deadline.'</b> to ensure that you do not have any payroll discrepancies for the upcoming pay day.</p>';
		$body .= '<p>Send email to <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a> if you have any questions.</p>';
		$body .= '<p>&nbsp;</p>';
		$body .= '<p>Thanks!<br/>CareerPH</p>';
		
		//$this->emailM->sendEmail($from, $to, $subject, $body, 'CareerPH', $cc );
		$this->emailM->sendEmail($from, 'ludivina.marinas@tatepublishing.net', $subject.'---'.$to.'--cc--'.$cc, $body, 'CareerPH' );
	}
	
	public function emailTimecardUnpublishedLogs($dateStart, $dateEnd, $query, $type=''){
		$empArr = array();
		foreach($query AS $q){
			$empArr[$q->empID_fk]['email'] = $q->email;
			$empArr[$q->empID_fk]['fname'] = $q->fname;
			$empArr[$q->empID_fk]['lname'] = $q->lname;
			$empArr[$q->empID_fk]['slogDate'][] = $q->slogDate;
		}
		
		if(count($empArr)>0){
			$subject = 'CareerPH Timecard Reminder - Unpublished Logs';
			$from = 'careers.cebu@tatepublishing.net';
			
			if($type=='HR'){ //sending message reminder to HR
				$to = 'hr.cebu@tatepublishing.net';
				$body = '<p>Hi HR,</p>';
				$body .= '<p>Please check unpublished logs for pay period <b>'.strtoupper(date('M d', strtotime($dateStart)).' - '.date('M d', strtotime($dateEnd))).'</b> for the following employees:</p>';
				$body .= '<ul>';
				foreach($empArr AS $k=>$emp){
					$body .= '<li>';
						$body .= $emp['fname'].' '.$emp['lname'];
						$body .= '<ul>';
							foreach($emp['slogDate'] AS $log)
								$body .= '<li><a href="'.$this->config->base_url().'timecard/'.$k.'/viewlogdetails/?d='.$log.'">'.date('F d, Y', strtotime($log)).'</a></li>';
						$body .= '</ul>';
					$body .= '</li>';
				}	
				$body .= '</ul>';
				
				$body .= '<p>&nbsp;</p>';
				$body .= '<p>Thanks!<br/>CareerPH</p>';
				
				$this->emailM->sendEmail($from, 'ludivina.marinas@tatepublishing.net', $subject.'---'.$to, $body, 'CareerPH', '', 'ludivina.marinas@tatepublishing.net'); //SEND EMAIL
			}else{ //sending messages to staffs
				foreach($empArr AS $emp){
					$to = $emp['email'];
					
					$log = '<ul>';
					foreach($emp['slogDate'] AS $l){
						$log .= '<li>'.date('F d, Y', strtotime($l)).'</li>';
					}
					$log .= '</ul>';
					
					$body = '<p><b>Please ignore if you are not part of the test group.</b><br/><br/></p>';
					$body .= '<p>Hi '.$emp['fname'].',</p>';
					$body .= '<p>Please be reminded to double-check your time card logs on "<a href="'.$this->config->base_url().'timecard/">Timecard and Payroll</a>" > "<a href="'.$this->config->base_url().'timecard/timelogs/">My Time Logs</a>"</a> page in CareerPH. Logs on the dates below are not yet published to payroll for the pay period <b>'.strtoupper(date('M d', strtotime($dateStart)).' - '.date('M d', strtotime($dateEnd))).'</b>.</p>';
					$body .= $log;
					$body .= '<p>If you have dates where your attendance is not published to payroll and need updates, please send message to HR by clicking "Request Update" on the date options or email <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a> AND CC:<a href="mailto:accounting.cebu@tatepublishing.net">accounting.cebu@tatepublishing.net</a> immediately to ensure that you do not have any payroll discrepancies for the upcoming pay day.</p>';
					$body .= '<p>Send email to <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a> if you have any questions.</p>';
					$body .= '<p>&nbsp;</p>';
					$body .= '<p>Thanks!<br/>CareerPH</p>';
					
					$this->emailM->sendEmail($from, 'ludivina.marinas@tatepublishing.net', $subject.'---'.$to, $body, 'CareerPH', '', 'ludivina.marinas@tatepublishing.net'); //SEND EMAIL
				}
			}		
		}
	}
	
	public function sendPublishPayrollEmail($period, $to, $name){
		$from = 'careers.cebu@tatepublishing.net';
		$cc = 'accounting.cebu@tatepublishing.net';
		$subject = 'YOUR HARD WORK HAS PAID OFF! Now don\'t spend it all at once :)';
		
		$body = '<p>Hi '.$name.',</p>';
		$body .= '<p>Your payslip for the payroll period of '.$period.' is ready for viewing. You can access this payslip through CareerPH Timecard and Payroll "<a href="'.$this->config->base_url().'timecard/payslips/">My Payslips</a>" page.</p>';
		$body .= '<p>Kindly review your payslip and report any discrepancies by replying to this email immediately. To ensure prompt resolution, please remember to include important details in your email, such as the specific item on the payslip that is incorrect and any other supporting details that can validate your claim.</p>';
		$body .= '<p><b>IMPORTANT:</b> Discrepancies on incentive / bonus amounts will only be entertained from managers and team leaders. If you are eligible for an incentive / bonus and it is not reflected on your payslip or if the incentive / bonus amount on your payslip is wrong, please escalate your concern to your team leader so they can validate it prior to forwarding to Accounting/HR.</p>';
		$body .= '<p>&nbsp;</p>';
		$body .= '<p>Thanks,</p>';
		$body .= '<p>Tate Publishing and Enterprises (Philippines), Inc.</p>';		
		
		$this->emailM->sendEmail( $from, $to, $subject, $body,'CareerPH', $cc);
	}
}
?>