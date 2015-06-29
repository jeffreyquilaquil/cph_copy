<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Emailmodel extends CI_Model {
	
    function __construct() {
        // Call the Model constructor
        parent::__construct();	
    }
	
	function sendEmail( $from, $to, $subject, $body, $fromName='' ){
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
		}
				
		
		$body = '<div style="font-family:Open Sans,Helvetica Neue,Helvetica,Arial,sans-serif; font-size:14px;">'.$body.'</div>';
		$fields = array(
			'from' => $from,
			'sendTo' => $to,
			'subject' => $subject,
			'body' => $body
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
	
	/******
		$uInfo should have name, title, accessEndDate, endDate, shift, office
	******/
	public function emailSeparationNotice($uInfo){
		if(isset($uInfo->name) && isset($uInfo->title) && isset($uInfo->accessEndDate) && isset($uInfo->endDate) && isset($uInfo->shift) && isset($uInfo->office)){
			$dateToday = date('Y-m-d');
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
				
			$this->emailM->sendEmail('hr.cebu@tatepublishing.net', 'helpdesk.cebu@tatepublishing.net', 'Separation Notice for '.$uInfo->name, $ebody, 'Tate Publishing Human Resources (CareerPH)');
			
			//send email to Andrew and Marjune
			$this->emailM->sendEmail('hr.cebu@tatepublishing.net', 'it.security@tatepublishing.net', 'Separation Notice for '.$uInfo->name, $ebody, 'Tate Publishing Human Resources (CareerPH)');
			
		}
	}

}
?>