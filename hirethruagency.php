<?php
	if(!empty($_POST)){
		require 'config.php';
		
		$upArr['hiredDate'] = date('Y-m-d');
		$upArr['startDate'] = date('Y-m-d', strtotime($_POST['startDate']));
		$upArr['endDate'] = date('Y-m-d', strtotime($_POST['endDate']));
		$upArr['agencyID'] = $_POST['agencyID'];			
		$db->updateQuery('applicants', $upArr, 'id='.$_GET['id']);
		
		//send confirmation email to agency
		sendEmail('hr.cebu@tatepublishing.net', $_POST['agencyTo'], $_POST['agencySubject'], $_POST['agencyConfirmEmailText'], 'CareerPH Tate Publishing', $_POST['agencyCC']);
		
		//send confirmation email to employee
		sendEmail('hr.cebu@tatepublishing.net', $_POST['empTo'], $_POST['empSubject'], $_POST['empConfirmEmailText'], 'CareerPH Tate Publishing', $_POST['empCC']);
		
		echo 'Agency confirmation email sent to '.$_POST['agencyTo'].'<br/>
			Applicant confirmation email sent to '.$_POST['empTo'];			
		echo '<hr/>';
		echo '<div style="text-align:left; color:red;">
				<b>Important Note:</b> The status of this application will not move to HIRED until the following documents are uploaded:
				<b><ol>
					<li>Letter of Endorsement from Agency</li>
					<li>Signed Company Policy and Guidelines</li>
				</ol></b>
				Remember that IT, Hiring Manager and Accounting will only be notified to prepare for hire when the status is changed to HIRE. The requisition will also not be closed until the status of the applicant is HIRED. Therefore it is important that you upload these files ASAP (before the applicant\'s start date).
			</div>';
		exit;
	}
?>
<form action="hirethruagency.php?id=<?= $_GET['id'] ?>" method="POST" onSubmit="assignValue();">
	<table>
		<tr>
			<td width="40%"><b>Basic Salary Offer</b><br/>
				<i>(The amount the employee should be getting. Actual cost billed by Agency to Tate shall be higher, to include service charges, etc.)</i></td>
			<td valign="top">
				<select name="salOffer" class="form-control" required id="salOffer">
					<option value=""></option>
					<option value="10,000.00">Php 10,000.00</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Contract Start Date:</b><br/>
				<i>Make sure that this start date is agreed with the applicant.</i></td>
			<td valign="top">
				<input type="text" id="cstart" name="startDate" class="form-control datepick" required/>
			</td>
		</tr>
		<tr>
			<td>
				<b>Contract End Date:</b><br/>
				<i>The default End Date is six months from the start date. The effective separation date is automatically set to the date following this date.</i>
			</td>
			<td valign="top">
				<input type="text" id="cend" name="endDate" class="form-control datepick" required/>
			</td>
		</tr>
		<tr>
			<td><b>Please Select Agency:</b></td>
			<td valign="top">							
				<select name="agencyID" class="form-control" onChange="agencySelect(this);" required>
					<option value=""></option>
				<?php
					$aQuery = $db->selectQuery('agencies', '*');
					foreach($aQuery AS $a){
						echo '<option value="'.$a['agencyID'].'">'.$a['agencyName'].'</option>';
					}
				?>
				</select>
			<a id="addagencylink" href="addagency.php">+ Add Agency</a></td>
		</tr>
	</table>
	<img id="loading" style="display:none;" src="images/loading.gif"/>
	<div id="divContentEmail" style="text-align:left; display:none;">
		<hr/>
		Agency Contact Person:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="contactPersonText"></span><br/>
		Agency Email Address:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="contactEmailText"></span><br/>
		Agency Contact Numbers:&nbsp;&nbsp;&nbsp;&nbsp;<span class="contactNosText"></span>
		
		<div id="emaildiv">
			<hr/>
			<b>Send Confirmation Email to Agency</b><br/><br/>
			<table style="width:100%">
				<tr>
					<td width="10%">To:</td>
					<td><input type="hidden" name="agencyTo" value="" class="contactEmailInput"/><span class="contactEmailText"></span></td>
				</tr>
				<tr>
					<td>Cc:</td>
					<td><input type="text" name="agencyCC" placeholder="Insert email addresses, comma separated" class="form-control"/></td>
				</tr>
				<tr>
					<td>Subject:</td>
					<td><input type="text" name="agencySubject" value="We Would Like to Hire <?= $info['fname'].' '.$info['lname'] ?>" class="form-control"/></td>
				</tr>
				<tr><td colspan=2>
					<div id="agencyConfirmEmail" style="border:1px solid #ccc; padding:10px;">
						Dear <span class="agencyNameText"></span>,<br/><br/>
						This is an email notification to inform your good office that our Company would like to proceed with hiring <?= $info['fname'].' '.$info['lname'] ?>.<br/><br/>
						Please give him the basic salary offer of Php <span id="salOfferText"></span>.<br/>
						We would like to have him/her begin working by <span class="startDateText"></span>.<br/>
						Contract End Date: <span class="endDateText"></span><br/><br/>
						Please email <a href="mailto:accounting.cebu@tatepublishing.net">accounting.cebu@tatepublishing.net</a> and <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a> the cost calculation for this hire.<br/><br/>
						<?= $info['fname'].' '.$info['lname'] ?> has been informed to proceed to your office for the job offer.<br/><br/>
						<b>Please send us the (a) Letter of Endorsement as well as the (b) signed Company Policy and Guidelines so that we may finalize the hire on our end.</b><br/><br/><br/>
						Thank you very much.<br/>
						Tate Publishing HR
					</div>
				</td></tr>
			</table>
			
			<hr/>
			<b>Confirmation Email to Employee</b><br/><br/>
			<table style="width:100%">
				<tr>
					<td width="10%">To:</td>
					<td><input type="hidden" name="empTo" value="<?= $info['email'] ?>"/><?= $info['email'] ?></td>
				</tr>
				<tr>
					<td>Cc:</td>
					<td><input type="text" name="empCC" placeholder="Insert email addresses, comma separated" class="form-control"/></td>
				</tr>
				<tr>
					<td>Subject:</td>
					<td><input type="text" name="empSubject" value="Congratulations <?= $info['fname'].' '.$info['lname'] ?>! You're hired in Tate Publishing" class="form-control"/></td>
				</tr>
				<tr><td colspan=2>
					<div id="empConfirmEmail" style="border:1px solid #ccc; padding:10px;">
						Dear <?= $info['fname'].' '.$info['lname'] ?>,<br/><br/>
						We are pleased to inform you that you passed the recruitment process for the position of <?= $position ?> and we would like to offer you the said position.<br/><br/>
						As discussed to you, this position is a contractual position and you will be hired through <span class="agencyNameText"></span>. They have been notified as this development and you are requested to visit their office for the formality of the process.<br/><br/>
						Our desired start date is on <span class="startDateText"></span>. We are excited to have you onboard!<br/><br/>
						The following things need to be accomplished on or before your start date:
						<ul>
							<li>Biometrics enrolled</li>
							<li>Timecard prepared <i>(you will need a time card to record your time in and out of the office. The agency shall need this when processing your payroll)</i></li>
							<li>Company ID produced <i>(your Recruitment Specialist shall take care of this)</i></li>
							<li>Read and understand the COC and sign the Acknowledgement Receipt.</li>
							<li><b>Please send us the following documents from AGENCY so that your company accounts in Tate may be created:
								<ol>
									<li>Letter of Endorsement</li>
									<li>Signed Company Policy and Guidelines</li>
								</ol>
								</b>
							</li>
						</ul>
						Your recruitment specialist James shall guide you through all the things that need to be processed in your boarding. If you have questions or need clarification, please do not hesitate to call James at 032 3182586, 09173015686 and 09325931586.<br/><br/>
						Thank you very much and congratulations!<br/><br/><br/>
						Best Regards,<br/>
						Tate Publishing HR
					</div>
				</td></tr>
			</table>
		</div>
		<br/>
		<div style="text-align:left; color:red;">
			<b>Important Note:</b> The status of this application will not move to HIRED until the following documents are uploaded:
			<b><ol>
				<li>Letter of Endorsement from Agency</li>
				<li>Signed Company Policy and Guidelines</li>
			</ol></b>
			Remember that IT, Hiring Manager and Accounting will only be notified to prepare for hire when the status is changed to HIRE. The requisition will also not be closed until the status of the applicant is HIRED. Therefore it is important that you upload these files ASAP (before the applicant's start date).
		</div>
		<br/>
		<textarea name="agencyConfirmEmailText" style="display:none;"></textarea>
		<textarea name="empConfirmEmailText" style="display:none;"></textarea>
		<input class="btn btn-primary" type="submit" value="Noted. Submit"/>
		<br/><br/>
	</div>	
</form>
<script type="text/javascript">
$(function(){
	$('#salOffer').change(function(){
		if($(this).val()==''){
			$('#salOfferText').text('');
		}else{
			$('#salOfferText').text($(this).val());
		}
	});
	
	$('#rep').click(function(){
		$('#reprofile').toggle('slow');
		$('#cancelApplication').hide();
	});
	
	$('#cancelApp').click(function(){
		$('#cancelApplication').toggle('slow');
		$('#reprofile').hide();
	});
	
	$('#cstart').change(function(){	
		$('.xdsoft_datetimepicker').hide();
		var second_date = new Date($('#cstart').val());
		second_date.setMonth(second_date.getMonth()+6);
	
		var monthNames = ["January", "February", "March", "April", "May", "June",
		  "July", "August", "September", "October", "November", "December"
		];

		var d = new Date(second_date);
		cval =  monthNames[d.getMonth()]+' '+d.getDate()+', '+d.getFullYear();

		$('#cend').val(cval);
		
		$('.startDateText').text($(this).val());
		$('.endDateText').text(cval);
	});

	$('#cend').change(function(){
		$('.xdsoft_datetimepicker').hide();
		$('.endDateText').text($(this).val());
	});
});

function agencySelect(t){
	if($(t).val()==''){
		$('#divContentEmail').css('display','none');
		$('#addagencylink').css('display', 'inline');
	}else{
		if($('#salOffer').val()=='' || $('#cstart').val()=='' || $('#cend').val()==''){
			alert('All fields are required');
			$(t).val('');
		}else{
			$('#addagencylink').css('display', 'none');
			$('#loading').css('display', 'inline');
			$.post('editstatus.php?pos=agencySelect',{postType:'agencycontact', id:$(t).val()}, 
			function(ff){
				arr = [];
				var json = $.parseJSON(ff);				
				$(json).each(function(i,val){
					$.each(val,function(k,v){
						arr[k] = v;   
					});
				});
				
				$('.contactPersonText').text(arr['contactPerson']);
				$('.contactEmailText').text(arr['contactEmail']);
				$('.contactEmailInput').val(arr['contactEmail']);
				$('.contactNosText').text(arr['contactNos']);
				$('.agencyNameText').text(arr['agencyName']);
				
				$('#loading').css('display', 'none');
				$('#divContentEmail').css('display','block');
			});
		}		
	}
}

function assignValue(){
	$('textarea[name="agencyConfirmEmailText"]').text($('#agencyConfirmEmail').html());
	$('textarea[name="empConfirmEmailText"]').text($('#empConfirmEmail').html());
}
</script>