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
		if( ($info->accessEndDate!='0000-00-00' && strtotime($info->accessEndDate)<=strtotime($dateToday)) ||
			($info->accessEndDate=='0000-00-00' && $info->endDate!='0000-00-00' && strtotime($info->endDate)<=strtotime($dateToday))
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
		$corps .= '<p>Please contact the employee\'s immediate supervisor or the department head to ensure that emails and phone calls are forwarded/redirected to the appropriate individual.</p>';
		
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
		$corps .= '<p>IT Staff, please prepare to terminate this employee\'s access to all company systems on the access end date above.</p>';
		
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
			$corps .= '<p>Please terminate all access for the above named employee immediately. This employee\'s access has been scheduled to end today, '.date('F d, Y', strtotime($acdate)).'.</p>';
		}else{
			$datediff = strtotime($dateToday) - strtotime($acdate);
			$ago = floor($datediff/(60*60*24));
			
			$corps .= '<p>Please terminate all access for the above named employee immediately. This employee\'s access has been scheduled to on '.date('F d, Y', strtotime($acdate)).', which was '.$ago.' days ago.</p>';
		}
		$this->emailM->sendEmail($de, $sur, $sujet, $corps, 'CareerPH', $cc);		
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
}
?>