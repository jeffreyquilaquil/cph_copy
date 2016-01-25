<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Itchecklist extends MY_Controller {
 
	public function __construct(){
		parent::__construct();		
		$this->load->model('timecardmodel', 'timeM');
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
		$time00 = '0000-00-00 00:00:00';
		$condition = 'publishBy="" AND showStatus=1';
		$updateArray = array();
		
		$condition .= ' AND (';
		///SCHEDULE NO DISCREPANCIES
		$condition .= ' (schedIn!="'.$time00.'" AND schedOut!="'.$time00.'"';
		$condition .= ' AND timeIn!="'.$time00.'" AND timeOut!="'.$time00.'"';
		$condition .= ' AND timeIn<=DATE_ADD(schedIn, INTERVAL '.$this->timeM->timesetting('overMinute15').')'; //late
		$condition .= ' AND timeOut>=DATE_ADD(schedOut, INTERVAL -'.$this->timeM->timesetting('overMinute15').')'; //early out
		$condition .= ' AND (timeBreak<"'.$this->timeM->timesetting('overBreakTimePlus15').'" OR timeBreak="00:00:00"))'; //NO BREAK OR time break less than 1 hour 30 mins
		
		//ON LEAVE
		$condition .= ' OR ';
		$condition .= ' (leaveID_fk>0 AND timeIn="'.$time00.'" AND timeOut="'.$time00.'")';
		
		//ABSENT
		$condition .= ' OR ';
		$condition .= ' (timeIn="'.$time00.'" AND timeOut="'.$time00.'" AND timeBreak="00:00:00" AND DATE_ADD(schedOut, INTERVAL '.$this->timeM->timesetting('outLate').')<"'.date('Y-m-d H:i:s').'")';
		
		$condition .= ')';
	
		
		$query = $this->dbmodel->getQueryResults('tcStaffLogPublish', 'slogID, empID_fk, slogDate, schedHour, offsetHour, schedIn, schedOut, timeIn, timeOut, leaveID_fk, staffHolidaySched', $condition, 'LEFT JOIN staffs ON empID=empID_fk'); //include leave status for approve with pay and not an offset set status=4 if offset
									
		if(count($query)>0){
			$dateToday = date('Y-m-d H:i:s');
			
			foreach($query AS $q){
				$runpublish = true;
				$pubHours = 0;
				
				$upArr = array();
				if($q->leaveID_fk>0){					
					$leave = $this->dbmodel->getSingleInfo('staffLeaves', 'leaveID, empID_fk, leaveType, leaveStart, iscancelled, leaveEnd, status, totalHours', 'leaveID="'.$q->leaveID_fk.'" AND iscancelled!=1 AND (leaveStart<="'.$q->schedIn.'") AND (status=1 OR status=2)');
									
					if(count($leave)>0 && $leave->iscancelled!=4){
						if($leave->leaveType==4 || $leave->status==2){
							$pubHours = 0; ///for offset
						}else if($leave->totalHours==4){
							$pubHours = 4; ///for half day leave
						}else if($leave->totalHours%8==0){
							$pubHours = 8; //for whole day leave
						}else{ ///if leave is a day and with half day							
							if($leave->leaveEnd<$q->schedOut || $leave->leaveStart>$q->schedIn) $pubHours = 4;
							else $pubHours = 8;
						}
					}else $runpublish = false;
				}

				if($q->timeIn==$time00 && $q->timeOut==$time00) $pubHours += 0;
				else{
					if($q->offsetHour>0 && ($q->timeIn>$q->schedIn || $q->timeOut<$q->schedOut)) $pubHours += ($q->schedHour - $q->offsetHour); //if offset and late 
					else $pubHours += $q->schedHour;
					
					$upArr['publishND'] = $this->payrollM->getNightDiffTime($q);
				}
					
				if($runpublish==true){
					$upArr['publishTimePaid'] = $pubHours;
					
					//CHECK FOR HOLIDAY
					$holiday = $this->payrollM->isHoliday($q->slogDate);
					if($holiday!=false){
						$showHoliday = true;
						if($holiday['type']!=4 && $holiday['type']!=0){
							if($q->staffHolidaySched==1 && $holiday['type']!=3) $showHoliday = false;
							else if($q->staffHolidaySched==0 && $holiday['type']==3) $showHoliday = false;
						}
						if($showHoliday==true)
							$upArr['publishHO'] = $this->payrollM->getHolidayHours($holiday['date'], $q);
					}	
					
					$upArr['datePublished'] = $dateToday;
					$upArr['publishBy'] = 'system';	
					$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$q->slogID), $upArr);
				
					$updateArray[$q->slogDate] = true;
				}
			}
		}
		
		//update attendance record 
		foreach($updateArray AS $k=>$u){
			$this->timeM->cntUpdateAttendanceRecord($k);
		}
		
		echo 'Updated';
		exit;		
	}
	
	
	public function unpublish($slogID, $today, $leaveID){
		//insert to tcTimelogUpdates
		$info = $this->dbmodel->getSingleInfo('tcStaffLogPublish', 'publishTimePaid, datePublished, publishBy', 'slogID="'.$slogID.'" AND showStatus=1');	
		$message = '<b>Unpublished log.</b>';
		if(count($info)>0){
			$message .= '<br/>Details:<br/>Time Paid: '.$info->publishTimePaid;
			$message .= '<br/>Prev Date Published: '.$info->datePublished;
			$message .= '<br/>Prev Published By: '.$info->publishBy;
		}				
		$this->timeM->addToLogUpdate($this->user->empID, $today, $message);
		
		//remove publish details
		$removePub['publishTimePaid'] = 0;
		$removePub['publishDeduct'] = 0;
		$removePub['publishND'] = 0;
		$removePub['datePublished'] = '0000-00-00 00:00:00';
		$removePub['publishBy'] = '';
		$removePub['publishNote'] = '';
		$removePub['leaveID_fk'] = $leaveID;
		
		$this->dbmodel->updateQuery('tcStaffLogPublish', array('slogID'=>$slogID), $removePub); ///REMOVE PUBLISH DETAILS
		$this->timeM->cntUpdateAttendanceRecord($today); //UPDATE ATTENDANCE RECORDS
	}
}
