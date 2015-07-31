<?php
	$disabled = '';
	$lc = $row->totalHours/8;
	$resumework = date('Y/m/d', strtotime('+3 days'));
	
?>
<h2>Leave Details of <?= $row->name ?></h2>
<hr/>
<?php if($this->uri->segment(3)!='' && $this->uri->segment(3)=='updated' ){ 
	echo '<p class="errortext tacenter weightbold">Leave details has been updated.</p>';
	if($this->uri->segment(4)=='maternityresume')
		echo '<p><b>Note:</b> Your cancellation request is now routed to HR to validate the Fit to Work Certificate uploaded. HR has 24 hours to validate your fit to work certificate. If determined valid, the request shall be forwarded to your immediate supervisor for approval. Your immediate supervisor has 24 hours to approve the request. If approved less than 24 hours from the intended date of return, the PayrollHero schedule for the said date may no longer be updated and the approved return date shall automatically change to the succeeding working day.</p>';
	echo '<hr/>';
}else{ ?>
<table class="tableInfo">
<?php if($row->matStatus>=1){ ?>
		<tr class="trhead">
			<td colspan=2><center>REQUEST TO SHORTEN MATERNITY LEAVE DETAILS</center></td>
		</tr>
		<tr>
			<td>Request Status</td>
			<td><b><?= $statusMaternityArr[$row->matStatus] ?></b>
		<?php
			if($this->user->empID==$row->empID_fk && ($row->matStatus==3 || $row->matStatus==5)){
				echo '<button id="btnShortenLeave" class="btnclass">Request to Shorten Maternity Leave</button>';
			}
		?>
			</td>
		</tr>
		<tr>
			<td>Date Requested</td>
			<td><?= date('F d, Y', strtotime($row->matDateRequested)) ?></td>
		</tr>
		<tr>
			<td>Intended Date to Resume</td>
			<td><?
				$dateResumeMinus1 = strtotime(date('Y-m-d', strtotime($row->matDateResume.' -1 day')));
				$dateToday = strtotime(date('Y-m-d'));
				
				$dateResume = date('F d, Y', strtotime($row->matDateResume));
				
				if($dateToday>=$dateResumeMinus1){
					$dateResume = date('F d, Y', strtotime('+2 days'));
					echo '<strike>'.date('F d, Y', strtotime($row->matDateResume)).'</strike> <span class="errortext">('.$dateResume.')</span>';
				}else{
					echo $dateResume;
				}
				
			?></td>
		</tr>
		<tr>
			<td>Fit to Work Certification from OB Gyn</td>
			<td><a href="<?= $this->config->base_url().'uploads/leaves/'.$row->matFile ?>">View file</a></td>
		</tr>
<?php
	if((($row->matStatus==1 || $row->matStatus==4) && $this->access->accessFullHR===true) || ($row->matStatus==2 && $this->user->empID==$row->approverID) ){
?>
	<tr><td colspan=2><br/></td></tr>
	<tr class="trhead"><td colspan=2><center>APPROVALS</center></td></tr>
	
	<form action="" method="POST">
	<?php if($row->matStatus==4 && $this->access->accessFullHR===true){ ?>
		<tr>
			<td><br/></td>
			<td class="errortext"><input type="checkbox" name="submitType" value="schedUpdated"/> PayrollHero schedule updated</td>
		</tr>
	<?php }else{ ?>
		<tr>
			<td>
		<?php
			if($row->matStatus==1 && $this->access->accessFullHR===true){
				echo 'Validate Fit To Work Certificate Uploaded';
				echo '<input type="hidden" name="stype" value="HRApproval"/>';
			}else if($row->matStatus==2 && $this->user->empID==$row->approverID){
				echo 'Approve Request to Shorten Maternity Leave?';
				echo '<input type="hidden" name="stype" value="ISApproval"/>'; 
			}
			
		?></td>
			<td>
				<input type="hidden" name="submitType" value="submitShortenLeave"/>
				<input type="hidden" name="dateResume" value="<?= date('Y-m-d', strtotime($dateResume)) ?>"/>
				<input type="radio" name="approveCert" value=1 required onClick="requireNote(0)"> Approve 
				<input type="radio" name="approveCert" value=0 required onClick="requireNote(1)"> Disapprove 
			</td>
		</tr>
		<tr>
			<td>Note</td>
			<td><textarea class="forminput" name="approveNote"></textarea></td>
		</tr>
	<?php } ?>
		<tr>
			<td><br/></td>
			<td><input type="submit" class="btnclass" value="Submit"/></td>
		</tr>
	</form>
<?php
}
	
	if(!empty($row->matHistory)){ ?>
		<tr><td colspan=2><br/></td></tr>
		<tr class="trhead"><td colspan=2><center>REQUEST APPROVAL HISTORY</center></td></tr>
		<tr><td colspan=2><?= nl2br($row->matHistory) ?></td></tr>
<?php
	}
?>
		<tr><td colspan=2><br/></td></tr>
		<tr class="trhead"><td colspan=2><center>LEAVE DETAILS</center></td></tr>
<?php } ?>

	<tr>
		<td>Status</td>
		<td><b><?= $this->textM->getLeaveStatusText($row->status, $row->iscancelled) ?></b>&nbsp;&nbsp;
	<?php		
		if($this->user->empID == $row->empID_fk){
			if($row->leaveType==4){
				$ccc=true;
				$od = explode('|', rtrim($row->offsetdates,'|'));
				$cntMove = count($od);
				for($o=0; $o<$cntMove; $o++){
					list($start, $end) = explode(',',$od[$o]);
					if($start<date('Y-m-d H:i')){
						$ccc = false;
					}
				}	
			}
						
			if($row->status<5 && ($row->iscancelled==0 || $row->iscancelled==4) && 
				(($row->leaveType<4 && strtotime(date('Y-m-d H:i',strtotime($row->leaveStart))) > strtotime(date('Y-m-d H:i'))) || 
				($row->leaveType==4 && $ccc==true)) &&
				$row->status!=3
			){
				echo '<button id="canceThisRequest" class="btnclass">Cancel</button>';
			}
			
			if($row->leaveType==6 && $row->matStatus==0 && (strtotime($row->leaveEnd)>strtotime(date('Y-m-d H:i:s')))){
				echo '<button id="btnShortenLeave" class="btnclass">Request to Shorten Maternity Leave</button>';
			}

			if($row->status==3 && $row->approverID==0){
				echo '<button onClick="resubmitForm();">Resubmit</button>';
			}
		}		
	?>
		</td>
	</tr>
	<tr>
		<td width="30%">Leave ID</td>
		<td><?= $row->leaveID ?></td>
	</tr>
	<tr>
		<td width="30%">Leave Type</td>
		<td><b><i><?= $leaveTypeArr[$row->leaveType] ?></i></b></td>
	</tr>
	<tr>
		<td><?= (($row->leaveType==4)?'Start Date and Time of Absence':'Leave Start') ?></td>
		<td><?= date('F d, Y h:i a', strtotime($row->leaveStart)) ?></td>
	</tr>
	<tr>
		<td><?= (($row->leaveType==4)?'Start End and Time of Absence':'Leave End') ?></td>
		<td><?= date('F d, Y h:i a', strtotime($row->leaveEnd)) ?></td>
	</tr>
	<tr>
		<td>Total Number of Hours<?= (($row->leaveType==4)?' to Compensate':' ') ?></td>
		<td><?= $row->totalHours.' hours ('.$lc.' days)' ?></td>
	</tr>
<?php
	if($row->leaveType==4){
		echo '<tr><td>Schedule of Work to Compensate Absence</td><td>';
			echo '<table class="tableInfo">
				<tr>
					<td width="50%"><b>Start Date and Time</b></td>
					<td width="50%"><b>End Date and Time</b></td>
				</tr>
			';
			
		$od = explode('|', rtrim($row->offsetdates,'|'));
		$cntBad = count($od);
		for($o=0; $o<$cntBad; $o++){
			list($start, $end) = explode(',',$od[$o]);
			echo '<tr>
					<td>'.date('F d, Y, h:i a', strtotime($start)).'</td>
					<td>'.date('F d, Y, h:i a', strtotime($end)).'</td>
				</tr>';
		}
		
		echo '</table>';
		echo '</td></tr>';		
	}
?>
	<tr>
		<td>Reason</td>
		<td><?= (($row->leaveType==5)?$this->textM->ordinal($row->reason).' child':$row->reason) ?></td>
	</tr>
	<tr>
		<td>Date Requested</td>
		<td><?= date('F d, Y h:i a', strtotime($row->date_requested)) ?></td>
	</tr>
<?php 
	if(!empty($row->code)){
		echo '<tr><td>Code Used</td><td>'.$row->code.'</td></tr>';
		$vreason = $this->dbmodel->getSingleField('staffCodes', 'why', 'code="'.trim($row->code).'"');
		if(!empty($vreason))
			echo '<tr><td>Valid Reason for Code</td><td>'.$vreason.'</td></tr>';
	}
	if(!empty($row->notesforHR)){ ?>
	<tr>
		<td>Additional Notes</td>
		<td><?= $row->notesforHR ?></td>
	</tr>
<?php } ?>	
	<tr>
		<td>Immediate Supervisor</td>
		<td><?= $row->supName ?></td>
	</tr>
	<tr>
		<td>Department</td>
		<td><?= $row->dept ?></td>
	</tr>
<?php if($row->leaveType==2 || $row->leaveType==3 || $row->leaveType==5 || ($row->leaveType==4 && !empty($row->supDocs))){ ?>
	<tr>
		<td>Supporting Documents</td>
		<td>
	<?php		
		$ama = explode('|', $row->supDocs);			
		foreach($ama AS $a):
			if(strpos($a, 'upDocDV++')!==false){
				$a = str_replace('upDocDV++','', $a);
				echo '<div><a href="'.$this->config->base_url().UPLOAD_DIR.$row->username.'/'.$a.'"><button>View file</button></a>';
			}else if($a!='' && file_exists(UPLOADS.'leaves/'.$a)){
				echo '<div><a href="'.$this->config->base_url().UPLOADS.'leaves/'.$a.'"><button>View file</button></a>';
				
				$belongto = explode('_',$a);
				if($belongto[1]==$this->user->empID)
					echo '&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onClick="removeDoc(\''.$a.'\', this)">Remove</a>';
				echo '</div>';
			}			
		endforeach;
		if(count($ama)>1) echo '<br/>';
	?>
		<a id="addfile" href="javascript:void(0)">+ Add Supporting Document</a>
			<form id="pfformi" action="" method="POST" enctype="multipart/form-data">
				<input type="file" name="supDocs" id="supDocs" class="hidden"/>
				<input type="hidden" name="submitType" value="uploadSD"/>
			</form>
		</td>
	</tr>
<?php } ?>
	<tr>
		<td>View Leave File</td>
		<td><a href="<?= $this->config->base_url() ?>leavepdf/<?= $row->leaveID ?>/" onClick="displaypleasewait();"><img src="<?= $this->config->base_url() ?>css/images/pdf-icon.png"/></a></td>
	</tr>	
	<tr><td colspan=2><br/></td></tr>
<?php
	if(count($leaveHistory) > 0){
		echo '<tr class="trhead">
			<td colspan=2><center>LEAVE HISTORY FOR THE MONTH OF '.strtoupper(date('F')).'</center></td>
		</tr>
		<tr>
			<td colspan=2>
				<table class="tableInfo">
					<tr class="formlabel">
						<td>Leave ID</td>
						<td>Type of Leave</td>
						<td>Leave Start</td>
						<td>Leave End</td>
						<td align="center">Total Number of Hours</td>
						<td>Status</td>
					</tr>';
				foreach($leaveHistory AS $rr){
					echo '<tr>
							<td><a class="iframe" href="'.$this->config->base_url().'staffleaves/'.$rr->leaveID.'/">'.$rr->leaveID.'</a></td>
							<td>'.$leaveTypeArr[$rr->leaveType].'</td>
							<td>'.$rr->leaveStart.'</td>
							<td>'.$rr->leaveEnd.'</td>
							<td align="center">'.$rr->totalHours.'</td>
							<td>'.$this->textM->getLeaveStatusText($rr->status, $rr->iscancelled).'</td>
						</tr>';
				}				
			echo '</table>
			</td>
		</tr>';
	}
?>
	<tr class="trhead">
		<td colspan=2><center>APPROVALS</center></td>
	</tr>
	<?php
	if($row->empStatus=='regular'){
		echo '<tr><td>Current Leave Credits</td><td>'.$row->leaveCredits.'</td></tr>';
		if($leaveReset==true){
			echo '<tr><td>Leave credits on '.date('F d', strtotime($row->startDate)).', '.date('Y').'</td><td>'.($current).'</td></tr>';
		}
	}
	
	if($row->hrapprover!=0){
		echo '<tr>
				<td>Total Leave Credits Deducted</td>
				<td>'.$row->leaveCreditsUsed.'</td>
			</tr>';
	}
	
	if($row->empStatus=='probationary'){
		echo '<tr style="color:red;"><td colspan=2>Employee status is <b>probationary</b>.</td></tr>';
	}else if($row->leaveCredits==0 && $row->leaveType<4){
		echo '<tr style="color:red;"><td colspan=2>No more available leave credits with pay for '.$row->fname.'. ';
		
		if($leaveReset==true)
			echo 'But leave credits will reset on his/her anniversary date on '.date('F d', strtotime($row->startDate)).', '.date('Y').'.';
		else
			echo 'You can either <b>"approve without pay"</b> or <b>"disapprove"</b> and let him/her file for an offset.';
		
		echo '</td></tr>';
	}else if($row->hrapprover==0 && $lc > $row->leaveCredits && $row->leaveType!=4 && $row->leaveType!=5){
		echo '<tr style="color:red;"><td colspan=2>Requested number of days leave is more than the remaining leave credits. You can either <b>"approve without pay"</b> or <b>"disapprove"</b> the request and let '.$row->fname.' file 2 separate leaves for with and without pay.</td></tr>';
	} 
	
	if(!file_exists(UPLOAD_DIR . $this->user->username.'/signature.png') && ($row->approverID==0 || $row->hrapprover==0)){
		echo '<form id="formUpload" action="'.$this->config->base_url().'upsignature/" method="POST" enctype="multipart/form-data">';
		echo '<tr>
				<td class="errortext" width="40%">No signature on file.<br/>Please upload signature first before approving leave. Signature must be transparent png file.</td>
				<td> 
					<input type="file" name="fileToUpload" id="fileToUpload"/>
					<input type="hidden" name="page" value="'.$_SERVER['REQUEST_URI'].'"/>
				</td>
			</tr>';
		echo '</form>';
		$disabled = 'disabled="disabled"';
	}
		
?>
	<tr><td colspan=2><br/></td></tr>
	
	<tr bgcolor="#eee"><td colspan=2><h3>Immediate Supervisor</h3></td></tr>
<?php 	
	if($row->dateApproved!='0000-00-00'){
		$aName = $this->dbmodel->getSingleField('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$row->approverID.'"');
		echo '<tr>
				<td>Date Approved</td>
				<td>'.date('F d, Y', strtotime($row->dateApproved)).'</td>
			</tr>';
		echo '<tr>
				<td>Approved By</td>
				<td>'.$aName.'</td>
			</tr>';
		if(!empty($row->remarks)){
		echo '<tr>
				<td>Remarks</td>
				<td>'.$row->remarks.'</td>
			</tr>';	
		}
	}else{
		if(($row->iscancelled==0 || $row->iscancelled==4)&& ($this->access->accessFull==true || $this->commonM->checkStaffUnderMeByID($row->empID_fk)==true)){
			echo '<form action="" method="POST" onSubmit="return validateIS();">';
			echo '<tr>
					<td>Please check one:';
					if($row->leaveType==5){
						echo '<br/><span class="colorgray">Paternity Leaves cannot be without pay.</span>';
					}
				echo  '</td>
					<td>';
					
				//status is regualar AND total number of hours less than total of leave credits OR type is offset or paternity leave OR anniversary date is less than start date
				if($row->empStatus=='regular' && ($lc <= $row->leaveCredits || ($row->leaveType==4 || $row->leaveType==5) || $leaveReset==true))
					echo '<input type="radio" name="approve" value="1" '.$disabled.' '.(($row->status=='1')?'checked':"").'> Approved '.(($row->leaveType!=4)?'With Pay':'').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				if($row->leaveType!=4 && $row->leaveType!=5)
					echo '<input type="radio" '.(($row->leaveType==6)?'checked':'').' name="approve" value="2" '.$disabled.' '.(($row->status=='2')?'checked':"").'> Approved Without Pay&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				
				echo '<input type="radio" name="approve" value="3" '.$disabled.' '.(($row->status=='3')?'checked':"").'> Disapproved';
					
				echo '</td>
				</tr>';
			echo '<tr>
					<td>Remarks:</td>
					<td><input type="text" name="remarks" id="isremarks" class="forminput" '.$disabled.' value="'.str_replace('"','\'',$row->remarks).'"/></td>
				</tr>';		
			echo '<tr>
					<td><br/></td>
					<td>
						<input type="hidden" name="submitType" value="svisor"/>
						<input type="hidden" name="leaveCreditsUsed" id="leaveCreditsUsed" value=""/>
						<input type="submit" value="Submit" '.$disabled.'/>
					</td>
				</tr>';		
			echo '</form>';
		}else if($row->iscancelled==0 || $row->iscancelled==4){
			echo '<tr><td colspan=2>Pending Immediate Supervisor\'s approval</td></tr>';
		}		
	}
	
if($row->status!=3 || ($row->status==3 && $row->hrapprover!=0)){
?>	
	<tr><td colspan=2><br/></td></tr>	
	<tr bgcolor="#eee"><td colspan=2><h3>Human Resources Department</h3></td></tr>
<?php if($row->dateApproved=='0000-00-00' && ($row->iscancelled==0 || $row->iscancelled==4)){
		echo '<tr><td colspan=2>Pending Supervisor\'s Approval</td></tr>';
	}else if($row->hrdateapproved!='0000-00-00'){
		$hrName = $this->dbmodel->getSingleField('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$row->hrapprover.'"');
		echo '<tr>
				<td>Approved By</td>
				<td>'.$hrName.'</td>
			</tr>';
		echo '<tr>
				<td>Date Approved</td>
				<td>'.date('F d, Y', strtotime($row->hrdateapproved)).'</td>
			</tr>';	
		if(!empty($row->hrremarks)){
			echo '<tr>
				<td>Remarks</td>
				<td>'.$row->hrremarks.'</td>
			</tr>';	
		}
	}else if(($row->iscancelled==0 || $row->iscancelled==4) && $this->access->accessFullHR==true){
?>
	<form action="" method="POST" onSubmit="return validateHR();">	
	<tr>
		<td>Change approval status</td>
		<td>
			<select id="status" name="status" class="forminput">
			<?php
				echo '<option value="1" '.(($row->status==1)?'selected="selected"':'').'>Approved with pay</option>';
				
			if($row->leaveType!=5 && $row->leaveType!=4)
				echo '<option value="2" '.(($row->status==2)?'selected="selected"':'').'>Approved without pay</option>';
				
				echo '<option value="3" '.(($row->status==3)?'selected="selected"':'').'>Disapproved</option>';
				echo '<option value="4" '.(($row->status==4)?'selected="selected"':'').'>Additional Information Required</option>';
			?>
			</select>
		</td>
	</tr>
<?php if($row->leaveType!=4 && $row->leaveType!=5 && $row->leaveType!=6){ ?>
	<tr class="hidedisapprove">
		<td>Number of Leave Credits to Deduct<br/>
		<?php 
			$remain = $current - $row->leaveCreditsUsed;	
		?>
			<input type="hidden" id="current" value="<?= $current ?>"/>
			<input type="hidden" id="remaining" name="remaining" value="<?= $remain ?>"/>
			<i style="color:#333;">Total Remaining Leave Credits: <b id="totalleave"><?= $remain ?></b></i>
		</td>
		<td><input type="text" id="leaveCreditsUsed" name="leaveCreditsUsed" value="<?= $row->leaveCreditsUsed ?>" class="forminput"/></td>
	</tr>		
<?php } ?>
	<tr class="hidedisapprove">
		<td>Updates</td>
		<td>
			<input name="HR_leave_credits_updated" id="HR_leave_credits_updated" type="checkbox" <?= $disabled ?> <?= (($row->leaveType==4 || $row->leaveType==5)?'class="hidden" checked':'')?>/><?= (($row->leaveType!=4 && $row->leaveType!=5)?'Leave Credits is correct<br/>':'')?>
			<span id="paysspan" <?= (($row->status==2)?'class="hidden"':'') ?>><input name="HR_payrollhero_updated" id="HR_payrollhero_updated" type="checkbox" <?= $disabled ?>/>PayrollHero schedule updated</span>
		</td>
	</tr>	
	<tr class="trremarks">
		<td>Remarks:</td>
		<td>
			<textarea id="hrremarks" name="remarks" class="forminput" <?= $disabled ?>></textarea>
		</td>
	</tr>
	
	
	<tr class="sendEmail hidden">
		<td colspan=2><b>Send Email</b></td>
	</tr>	
	<tr class="sendEmail hidden">
		<td>Subject</td>
		<td><input name="subjectEmail" type="text" class="forminput" value="Additional Information is Required to Process Leave Request"/></td>
	</tr>
	<tr class="sendEmail hidden">
		<td>To</td>
		<td>
			<input type="hidden" name="toEmail" value=""/>
			<input type="text" class="forminput toEmail" value="<?= $row->email ?>"/><br/>
			<input type="text" class="forminput toEmail" value="<?= $row->supEmail ?>"/><br/>
			<input type="text" class="forminput toEmail" value="<?= 'hr.cebu@tatepublishing.net' ?>"/><br/>
			<input type="button" id="addEmailBox" value="Add"/>
		</td>
	</tr>
	<tr class="sendEmail hidden">
		<td>Message</td>
		<td>
			<textarea name="message" id="message" class="mceEditor" style="height:160px;">
				<p>Hello,</p>
				<p>Additional information is required to process your leave request:<br/>
				Please upload supporting documents:</p>
				<p><a href="<?= $this->config->base_url().'staffinfo/'.$row->username.'/#personalfiletbl' ?>">Click Here to Upload Documents to Your Staff Page</a><br/>
				After uploading the required documents, send confirmation to hr.cebu@tatepublishing.net</p>		
			</textarea>
		</td>
	</tr>
	
	<tr>
		<td><br/></td>
		<td>
			<input type="hidden" name="oldstatus" id="oldstatus" value="<?= $row->status ?>"/>
			<input type="hidden" name="submitType" value="hr"/>
			<input type="submit" value="Submit" <?= $disabled ?>/>
			&nbsp;&nbsp;<input type="button" id="saveHRRemarks" value="Save remark" <?= $disabled ?>/>
		</td>
	</tr>
	</form>
<?php }else if($row->iscancelled==0 || $row->iscancelled==4){
		echo '<tr><td colspan=2>No Access.</td></tr>';
} ?>	
	<tr><td colspan=2><br/></td></tr>

<?php } 

	if(!empty($row->hrAddRemarks)){
		echo '<tr bgcolor="#eee"><td colspan=2><h3>HR Additional Remarks</h3></td></tr>';
		
		$ramsey = array_reverse(explode('||', $row->hrAddRemarks));		
		foreach($ramsey AS $r){
			$ram2 = explode('>>', $r);
			if(isset($ram2[0]) && isset($ram2[1]))
				echo '<tr><td>'.$ram2[0].'</td><td>'.$ram2[1].'</td></tr>';
		}
		echo '<tr><td colspan=2><br/></td></tr>';
	}

	if(!empty($row->addInfo)){
		echo '<tr bgcolor="#eee"><td colspan=2><h3>Additional Information Required</h3></td></tr>';
		echo '<tr><td colspan=2>'.$row->addInfo.'</td></tr>';
	}
?>	
<tr class="trhead trcdetails <?= (($row->canceldata=='')?'hidden':'') ?>">
	<td colspan=2><center id="canceldetails">CANCEL DETAILS</center></td>
</tr>
<?php
	if($row->iscancelled!=0 && $row->iscancelled!=4 && $row->datecancelled!= '0000-00-00 00:00:00'){
		echo '<tr><td>Date cancelled</td><td>'.date('F d, Y H:i', strtotime($row->datecancelled)).'</td></tr>';
	}
	if($row->canceldata!=''){
		echo '<tr><td>Reason</td><td>'.nl2br($row->cancelReasons).'</td></tr>';	
		
		$potter = array_reverse(explode('^_^', $row->canceldata));
		if(count($potter)>0){
			echo '<tr><td>Cancel History:</td><td>';
			foreach($potter AS $p):
				if($p!='') echo $p.'<br/>';
			endforeach;
			echo '</td></tr>';
		}
	}
	
	if($row->iscancelled==2 && ($this->user->empID == $row->approverID || $this->user->empID == $row->supervisor)){
		echo '<form action="" method="POST" onSubmit="return validatecapprover();">';
		echo '<tr><td><br/></td><td><input type="radio" name="capprover" value="1"> Approve &nbsp;&nbsp;&nbsp;<input type="radio" name="capprover" value="0"> Disapprove</td></tr>';
		echo '<tr id="disapprovecanceltr" class="hidden"><td>Note:</td><td><input type="text" class="forminput" id="disnote" name="disnote"/></td></tr>';
		echo '<tr><td><br/></td><td><input type="hidden" name="submitType" value="cancelApprover"/> <input type="submit" value="Submit"/></td></tr>';
		echo '</form>';
	}
	
	if($row->iscancelled==3 && $this->access->accessFullHR==true){
		echo '<tr bgcolor="#eee"><td colspan=2><h3>Human Resources Cancel Approval</h3></td></tr>';
		echo '<tr><td>Current leave credits</td><td>'.$row->leaveCredits.'</td></tr>';
		echo '<tr><td>Leave credits deducted</td><td>'.$row->leaveCreditsUsed.'</td></tr>';
		echo '<form action="" method="POST" onSubmit="return validateHRcancel('.$row->status.');">';	
		echo '<tr><td>On submission leave credits is</td><td><input type="text" name="leaveCredits" id="leaveCredits" value="'.($row->leaveCredits + $row->leaveCreditsUsed).'" class="forminput"/></td></tr>';

		if($row->status!=2){
			echo '<tr><td>Update</td><td><input name="HR_payrollhero_updated" id="HR_payrollhero_updated" type="checkbox"/>PayrollHero schedule updated</td></tr>';
		}
		
		echo '<tr><td><br/></td><td><input type="hidden" name="submitType" value="cancelHRapprove"/> <input class="btnclass btngreen" type="submit" value="Submit"/></td></tr>';
		echo '</form>';
	}
	
	if($row->status > 0){
		echo '<tr class="trcdetails hidden"><td colspan="2"><i>Leave cancellation will still require immediate supervisor approval.</i></td></tr>';
	}		
	echo '
		<form action="" method="POST" onSubmit="return validateCancel();" class="trcdetails hidden">
		<tr class="trcdetails hidden">
			<td>Why do you want to cancel this leave request?</td><td><textarea class="forminput" id="cancelReasons" name="cancelReasons"></textarea></td>
		</tr>
		<tr class="trcdetails hidden">
			<td><br/></td><td><input type="hidden" name="submitType" value="cancel"/><input type="submit" value="Submit"/></td>
		</tr>
		</form>
	';
	
	if($row->leaveType==6){
		$checkdatenum = date('N');
		if($checkdatenum<=2 || $checkdatenum==7) $resumework = date('Y/m/d', strtotime('+3 days'));
		else if($checkdatenum==6) $resumework = date('Y/m/d', strtotime('+4 days'));
		else $resumework = date('Y/m/d', strtotime('+5 days'));		
?>
	
	<tr class="trhead trshortenleave hidden">
		<td colspan=2><center>SHORTEN MATERNITY LEAVE</center></td>
	</tr>
	<form class="trshortenleave hidden" action="" method="POST" enctype="multipart/form-data">
		<tr class="trshortenleave hidden">
			<td>Upload Fit to Work Certification From Your OB Gyn<br/>
				<i class="colorgray">You will not be allowed to resume working without this document</i>
			</td>
			<td><input type="file" name="filecert" required/></td>
		</tr>
		<tr class="trshortenleave hidden">
			<td>When do you intend to resume working?<br/>
				<i class="colorgray">The soonest day you can resume work is on: <?= date('F d, Y', strtotime($resumework)) ?>. Your selected date of return shall be honored only if the immediate supervisor approves of it more than 24 hours from the intended date of return. Otherwise, if approved less than 24 hours from the intended date of return, the PayrollHero schedule for the said date may no longer be updated and the approved return date shall automatically change to the succeeding working day.</i>
			</td>
			<td><input type="text" class="datepickM forminput" name="dateResume" value="<?= date('F d, Y', strtotime($resumework)) ?>" required/></td>
		</tr>
		<tr class="trshortenleave hidden">
			<td><br/></td>
			<td>
				<input type="hidden" name="submitType" value="maternityresume"/>
				<input type="submit" value="Submit" class="btnclass"/>
			</td>
		</tr>
	</form>
<?php
	}
?>	
	
</table>
<?php } 
?>
<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	$(function(){
		tinymce.init({
			mode : "specific_textareas",
			editor_selector : "mceEditor",
			menubar : false,
			relative_urls: false,
			convert_urls: false,
			remove_script_host : false,
			plugins: [
				"link",
				"code",
				"table"
			],
			toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code"
		});
		
		$('.datepickM').datetimepicker({ 
			format:'F d, Y',
			minDate:'<?= $resumework ?>'
		});	
		
		$('#fileToUpload').change(function(){
			$('#formUpload').submit();
		});	
		
		$('input[name=approve]').click(function(){
			if($(this).val()==1){
				$('#leaveCreditsUsed').val('<?= $lc ?>');							
			}else{
				$('#leaveCreditsUsed').val(0);				
			}	
				
		});

		$('#status').change(function(){			
			if($(this).val()==4){
				$('.sendEmail').removeClass('hidden');
				$('.trremarks').addClass('hidden');
				$('.hidedisapprove').addClass('hidden');
				$('input[type="submit"]').val('Submit & Send');
			}else{
				$('.sendEmail').addClass('hidden');
				$('.hidedisapprove').removeClass('hidden');
				$('#paysspan').removeClass('hidden');
				
				$('#leaveCreditsUsed').val(0);
			
				if($(this).val()==1){
					$('#leaveCreditsUsed').val('<?= $lc ?>');
				}else if($(this).val()==2){
					$('#paysspan').addClass('hidden');
				}else if($(this).val()==3){
					$('.hidedisapprove').addClass('hidden');					
				}	
				
				$('#remaining').val(parseFloat($('#current').val())-parseFloat($('#leaveCreditsUsed').val()));		
				$('#totalleave').html($('#remaining').val());	
				$('input[type="submit"]').val('Submit');
			}
		});
		
		$('#leaveCreditsUsed').blur(function(){
			$('#remaining').val(parseFloat($('#current').val()) - parseFloat($('#leaveCreditsUsed').val()));
			$('#totalleave').html($('#remaining').val());
		});
		
		$("input:radio[name='capprover']").change(function(){
			if($(this).val()==0)
				$('#disapprovecanceltr').removeClass('hidden');
			else
				$('#disapprovecanceltr').addClass('hidden');				
		});
		
		$('#canceThisRequest').click(function(){
			$('.trcdetails').removeClass('hidden');
			$("html, body").animate({ scrollTop: $(document).height() }, "slow");			
		});
		
		$('#addfile').click(function(){
			$('#supDocs').trigger('click');
		});
		$('#supDocs').change(function(){
			displaypleasewait();
			$('#pfformi').submit();				
		});
		
		$('#addEmailBox').click(function(){
			$('<input type="text" class="forminput toEmail" value=""/><br/>').insertBefore('#addEmailBox');
		});
		
		//HR additional remarks
		$('#saveHRRemarks').click(function(){
			var rem = $('#hrremarks').val();
			if(rem!=''){
				$(this).attr('disabled','disabled');
				$('<?= '<img src="'.$this->config->base_url().'css/images/small_loading.gif" style="width:20px;"/>' ?>').insertAfter(this);
				$.post('<?= $this->config->item('career_uri') ?>', {submitType:'hrAdditionalRemarks', remarks:rem},
				function(){
					location.reload();
				});
			}else{
				alert('Remarks is empty.');
			}
		});
		
		$('#btnShortenLeave').click(function(){
			$('.trshortenleave').removeClass('hidden');
			$("html, body").animate({ scrollTop: $(document).height() }, "slow");			
		});
		
	});
	
	function validateIS(){
		valid = true;
		
		if($("input:radio[name='approve']").is(":checked")==false){
			alert('Please check approvals.');
			valid = false;
		}else if($("input:radio[name='approve']:checked").val()==3 && $('#isremarks').val()==''){
			alert('Please input remarks.');
			valid = false;
		}
		if(valid==true) displaypleasewait();
		
		return valid;
	}
	
	function validateHR(){
		validtxt = '';
		emF = true;
		var emailPattern = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
		
		if($('#status').val()==4){
			$('.toEmail').each(function(){
				eMs = $('input[name="toEmail"]').val();
				if($(this).val()!=''){
					if(emailPattern.test($(this).val())==false) emF = false;
					else $('input[name="toEmail"]').val(eMs+','+$(this).val());
				}
			});
					
			if(emF==false){
				validtxt += '- Check email addresses there is an invalid format.\n';
			}
			
			if(tinyMCE.get('message').getContent()=='' || $('input[name="subjectEmail"]').val()=='' || $('input[name="toEmail"]').val()=='' ) 
				validtxt += '- All fields are required.\n';
		}else{
			if(($('#status').val()==1 && ($('#HR_leave_credits_updated:checked').length==0 || $('#HR_payrollhero_updated:checked').length==0)) ||
				($('#status').val()==2 && $('#HR_leave_credits_updated:checked').length==0)
			){
				validtxt += '- Please check all checkboxes.\n';
			}
			
			if($('#status').val()!=$('#oldstatus').val() && $('#hrremarks').val()==''){
				validtxt += '- Remarks should not be empty when changing approve status.\n';
			}		
			if($('#remaining').val()<0){
				validtxt += '- Total leave credits is below 0.';
			}
		}
		
		if(validtxt != ''){
			alert(validtxt);
			return false;
		}else{	
			displaypleasewait();
			return true;
		}
	}
	
	function validateCancel(){
		if($('#cancelReasons').val()==''){
			alert('Reason for cancelling leave is empty.');
			return false;
		}else{
			displaypleasewait();
			return true;
		}
	}
	
	function validatecapprover(){
		if($("input:radio[name='capprover']").is(":checked")==false){
			alert('Please check approvals.');
			return false;
		}else if($("input:radio[name='capprover']:checked").val()==0 && $('#disnote').val()==''){
			alert('Please input note.');
			return false;	
		}else{
			displaypleasewait();
			return true;
		}
	}
	
	function resubmitForm(){
		$.post("<?= $this->config->base_url() ?>staffleaves/<?= $this->uri->segment(2)?>/",{
			submitType:'resubmit'
		},function(){
			displaypleasewait();
			location.reload();
		});
	}
	
	function validateHRcancel(status){
		if($('#leaveCredits').val()=='' || (status!=2 && $('#HR_payrollhero_updated:checked').length==0)){
			alert('Please check approvals.');
			return false;
		}else{
			displaypleasewait();
			return true;
		}
	}
	
	function removeDoc(f, d){
		if(confirm('Are you sure you want to remove this file?')){
			$(d).html('<?= '<img src="'.$this->config->base_url().'css/images/small_loading.gif" style="width:20px;"/>' ?>');
			$.post('<?= $this->config->item('career_uri') ?>',{submitType:'removeDoc', fname:f}, function(f){				
				$(d).parent('div').hide();
			});
		}
	}
	
	function requireNote(v){
		if(v==1){
			$('textarea[name="approveNote"]').attr('required', 'required');
		}else{
			$('textarea[name="approveNote"]').removeAttr('required');
		}
	}
	
</script>