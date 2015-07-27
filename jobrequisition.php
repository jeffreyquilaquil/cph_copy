<?php
require 'config.php';

if(isset($_GET['position']) && $_GET['position']=='text'){
	$p = $db->selectSingleQueryArray('newPositions', 'org, dept, grp, subgrp,title','posID='.$_POST['pos']);
	echo $p['org'].' > '.$p['dept'].' > '.$p['grp'].' > '.$p['subgrp'].' > '.$p['title'];
	exit;
}

if(isset($_GET['create']) && $_GET['create']=='new'){
	$_POST['date_created'] = 'NOW()';	
	echo $db->insertQuery('newPositions', $_POST);
	exit;
}

if(isset($_GET['cur']) && isset($_GET['n'])){			
	$typeQ = $db->selectQueryArray('SELECT DISTINCT '.$_GET['n'].' AS tP FROM newPositions WHERE '.$_GET['cur'].' = "'.$_POST['typeVal'].'"');
		
	echo '<option value=""></option>';
	foreach($typeQ AS $t):
		echo '<option value="'.$t['tP'].'">'.$t['tP'].'</option>';
	endforeach;
	
	exit;
}



$added = false;
if(isset($_POST) && !empty($_POST)){
	$_POST['dateSubmitted'] = 'NOW()';
	$_POST['startDate'] = date('Y-m-d', strtotime($_POST['startDate']));
	if($_POST['shift']==''){
		$_POST['shift'] = date('H:s A', strtotime($_POST['startShift'].':00')).' '.date('H:s A', strtotime($_POST['endShift'].':00')).' PHL';
	} 
	unset($_POST['startShift']);
	unset($_POST['endShift']);
		
	if(!isset($is_inserted)){
		$is_inserted = true;
		$iID = $db->insertQuery('jobReqData', $_POST);	
		$db->updateQuery('jobReqData', array('jobReqID' => $iID), 'reqID = "'. $iID .'"');
		
		if( $_POST['num'] > 1 ){
			$_POST['jobReqID'] = $iID;
			for($i=1; $i< $_POST['num']; $i++ )
				$db->insertQuery('jobReqData', $_POST);  
		}
	}
	
	//send autoemail to HR
	$from = 'careers.cebu@tatepublishing.net';
	$to = 'hr.cebu@tatepublishing.net'; 
	$posName = $db->selectSingleQuery("newPositions", "title", "posID='".$_POST['positionID']."'");
	$subject = 'REQUISITION #'.$iID.' IS SUBMITTED BY '.$_POST['requestor'].' FOR THE POSITION OF '.strtoupper($posName);
	
	$body = '<p>This email notification is sent to <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a> for the purpose of creating a ticket monitoring the work of recruitment staff on the subject requisition number. With this, all requisitions submitted shall have a corresponding ticket in HR helpdesk. This ticket shall be resolved as soon as the requisition is closed.</p>';
	$body .= '<p>Notes to this ticket are not necessary until resolution as all applicant history shall be recorded in applicants\' profiles in careerph.</p>';
	$body .= '<p>Thank you very much team.</p>';
	$body .= '<p>Dear HR team (recruitment),</p>';
	$body .= '<p>Fyi...</p>';
	$body .= '<p><br/></p>';
	$body .= '<p>CareerPH</p>';
	
	sendEmail($from, $to, $subject, $body, 'CareerPH');
	$added = true;
	unset($_POST);
}


require "includes/header.php";
$username = $db->selectSingleQuery("pt_users", "username", "md5(username) = '".$_GET['key']."'");

if(empty($username) && isset($_SESSION['u']))
	$username = $_SESSION['u'];

if(empty($username) || !in_array($username, $_SESSION['authorized'])){
	echo 'Sorry, you do not have access to this page.';
}else{

?>
<style type="text/css">
	button{padding:5px;}
	.hide{display:none;}
	.pintr{cursor:default;}
</style>
<div style="text-align:center;"><h2>Job Requisition</h2></div>
<?php if($added==false){ ?>
<div style="text-align:center; margin-bottom:15px;">
	<button id="btnStep1" type="button" class="btn btn-primary btn-xs pintr">Step 1 ></button>&nbsp;&nbsp;&nbsp;
	<button id="btnStep2" type="button" class="btn btn-default btn-xs pintr" disabled="disabled">Step 2 ></button>&nbsp;&nbsp;&nbsp;
	<button id="btnStep3" type="button" class="btn btn-default btn-xs pintr" disabled="disabled">Step 3</button>
	<h4 id="stepTexts">Type of Request</h4>
</div>
<?php } ?>
<table border=0 cellspacing=0 cellpadding=0 width="100%">
	<tr>
		<td align="center">
			<div style="background-color:#fff; width:50%; border-radius:10px; padding:15px 0;">	
				<?php if($added){ ?>
				<div>
					Congratulations! New Job Requisition has been added.<br/><br/>
					<button class="btn btn-primary btn-sm" onClick="window.location.href=window.location.href">Add another job request?</button>
				</div> <? }else{ ?>
				<table id="step1" width="90%" style="text-align:center;" cellpadding=5 cellspacing=5>
					<tr>
						<td><b>Is this a new position?</b></td>
					</tr>
					<tr>
						<td align="center">
							<button class="btn btn-danger" onClick="funcRequestType('new')">Yes, this is a new position</button>
							<p style="width:50%;">Select this if the position you are requesting is a position that is not yet in Cebu as of today. For a list of positions that are currently in Cebu <a class='inline' href="#inline_content">click here</a>.</p>
							<div style='display:none'>
								<div id='inline_content' style='padding:10px; background:#fff; font-size:12px;'>
									<h3>List of all positions in Cebu</h3>
									<table border=1 width="100%" cellpadding=5 cellspacing=5>
									<?php
										$pos = $db->selectQuery("newPositions", "posID,title,dept, org", "active=1 ORDER BY org, dept, title ASC");
										$dept = '';
										$cnt = 0;
										foreach( $pos AS $p ):
											if($dept != $p['dept']){
												if($cnt%2==0) echo '<tr bgcolor="#dcdcdc">';
												else echo '<tr>';
												
												echo '<td width="40%"><b>'.$p['org'].' > '.$p['dept'].'</b></td><td>';
												$dept = $p['dept'];
												$cnt++;
											}
											echo $p['title'].'<br/>';
										endforeach;
									?>	
										</td></tr>
									</table>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<button class="btn btn-info" onClick="funcRequestType('existing')">No, this is a backfill/replacement for an existing position</button>
						</td>
					</tr>				
				</table>
				<?php } ?>

				<div id="step2" class="hide">
					<table id="formNew" class="formNew hide" width="90%">
						<tr>
							<td colspan="2">
								<?php
									$org = $db->selectQueryArray('SELECT DISTINCT org FROM newPositions');
								?>
									<table width="100%">
										<tr align="center">
											<td colspan=2><b>Create new open position</b></td>
										</tr>
										<tr>
											<td width="50%">Name of the new position:</td>
											<td><input type="text" name="title" id="title" class="form-control"/></td>
										</tr>
										<tr>
											<td>Organization:</td>
											<td>
												<select class="form-control" name="org" id="org" onChange="getOrgVal('org', 'dept')">
													<option value=""></option>
													<?php
														foreach($org AS $o):
															echo '<option value="'.$o['org'].'">'.$o['org'].'</option>';
														endforeach;
													?>
												</select>
											</td>
										</tr>
										<tr id="tr_dept" class="formNew hide">
											<td>Department:</td>
											<td>
												<select class="form-control" name="dept" id="dept" onChange="getOrgVal('dept', 'grp')"></select>
											</td>
										</tr>
										<tr id="tr_grp" class="formNew hide">
											<td>Group:</td>
											<td>
												<select class="form-control" name="grp" id="grp" onChange="getOrgVal('grp', 'subgrp')"></select>
											</td>
										</tr>
										<tr id="tr_subgrp" class="formNew hide">
											<td>Sub-group:</td>
											<td>
												<select class="form-control" name="subgrp" id="subgrp"></select>
											</td>
										</tr>
										<tr id="description" class="formNew hide">
											<td>
												Job Description:<br/>
												<span style="color:red; font-weight:bold;">A position cannot be created without a complete job description. This helps HR find the most suitable candidates, and also will help the respective PHL department in managing the new employee.</span>
											</td>
											<td><textarea class="form-control" name="desc" id="desc" rows=10></textarea></td>
										</tr>
										<tr id="next" class="formNew hide">
											<td colspan="2" align="right"><button onClick="validateNew()">Next ></button></td>
										</tr>
									</table>
							</td>						
						</tr>
					</table>
					
					<div id="formExisting" class="formExisting hide">						
						<div id="formExistingHead" style="font-weight:bold;">	
							<div id="formExistingBody1">
								<table width="60%">
									<tr>
										<td>Choose position:</td>
										<td>
											<select id="selpositionID" class="form-control" onChange="positionText();">
												<option value=""></option>
											<?php
												$dept = '';
												$pos = $db->selectQuery("newPositions", "posID,title,dept", "active=1 ORDER BY dept, title ASC");
												foreach( $pos AS $p ){
													if($p['dept'] != $dept){
														if($dept!='')
															echo '</optgroup>';
															
														echo '<optgroup label="'.$p['dept'].'">';
														$dept = $p['dept'];
													}
													echo '<option value="'.$p['posID'].'">'.$p['title'].'</option>';										
												}							
											?>
											</select>
										</td>
										<tr><td colspan=2><br/></td></tr>
										<tr  align="right">
											<td colspan=2><button id="choosePos">Next ></button></td>
										</tr>
									</tr>
								</table>							
							</div>
						</div>
					</div>
				</div><!-- end fo step2 div-->
				
				<form id="mainForm" action="" method="POST" onSubmit="return validateJobForm();">
					<input type="hidden" name="requestType" id="requestType" value=""/>
					<input type="hidden" name="positionID" id="positionID" value=""/>
					<input type="hidden" name="requestor" value="<?= $username ?>"/>					
				<div id="step3" class="hide">
					<table width="90%">
						<tr>
							<td colspan=2 align="center"><b><span id="formHeadText"></span></b></td>
						</tr>
						<tr>
							<td>How many slots are you opening up?</td>
							<td>
								<select name="num" id="num" class="form-control">
									<?php
										for($i=1;$i<=10;$i++){
											echo '<option value="'.$i.'">'.$i.'</option>';
										}
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Target start date:</td>
							<td><input type="text" name="startDate" id="startDate" class="form-control"/></td>
						</tr>
						<tr>
							<td>Immediate Supervisor:</td>
							<td>
								<select name="supervisor" id="supervisor" class="form-control">
									<option value=""></option>
									<?php
										$immediateSup = $db->selectQueryArray('SELECT CONCAT(fname," ",lname) AS name, lname, fname, username FROM staffs WHERE levelID_fk>0 ORDER BY lname');										
										foreach($immediateSup AS $s):
											if($s['username']==$_SESSION['u'])
												echo '<option value="'.$s['name'].'" selected>'.$s['lname'].', '.$s['fname'].'</option>';
											else
												echo '<option value="'.$s['name'].'">'.$s['lname'].', '.$s['fname'].'</option>';
											
										endforeach;
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Minimum Salary Offer:</td>
							<td>
								<select name="minSal" id="minSal" class="form-control"/>
									<option value=""></option>
									<?php
										$salaryQ = $db->selectQuery('salaryRange', '*');
										foreach($salaryQ AS $sal):
											echo '<option value="'.$sal['salID'].'">'.$sal['salaryAllowance'].'</option>';
										endforeach;
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Maximum Salary Offer:</td>
							<td>
								<select name="maxSal" id="maxSal" class="form-control"/>
									<option value=""></option>
									<?php
										foreach($salaryQ AS $sal):
											echo '<option value="'.$sal['salID'].'">'.$sal['salaryAllowance'].'</option>';
										endforeach;
									?>
								</select>
							</td>
						</tr>
						<tr>
							<td>Shift:</td>
							<td>
								<select name="shift" id="shift" class="form-control" onChange="checkShift()"/>
									<option value="">Custom Shift</option>
									<option value="7:00 AM - 4:00 PM PHL">7:00 AM - 4:00 PM PHL</option>
									<option value="9:00 PM - 6:00 AM PHL">9:00 PM - 6:00 AM PHL</option>									
								</select>
							</td>
						</tr>
						<tr id="customShift">
							<td><br/></td>
							<td>
								<?php
									$shiftval = '';
									$ampm = 'AM';
									for($i=0, $j=0; $i<24; $i++, $j++){
										if($i==12) $ampm = 'PM';
										if($i==13) $j = 1;											
										
										if($j<10) $shiftval .= '<option value="'.$i.'">0'.$j.':00 '.$ampm.'</option>';
										else $shiftval .= '<option value="'.$i.'">'.$j.':00 '.$ampm.'</option>';
									}
								?>
								<select id="startShift" name="startShift" class="form-control"><?= $shiftval ?></select>
								<select id="endShift" name="endShift" class="form-control">	<?= $shiftval ?></select>
							</td>
						</tr>
						<tr>
							<td><b>FINAL INTERVIEWER:</b><br/><i>Who will conduct the final interview for potential hires?</i></td>
							<td>
							<?php 
								$staffs = $ptDb->selectQueryArray('SELECT CONCAT(sFirst, " ", sLast) AS name, sFirst, sLast, username, office FROM staff WHERE active="Y" ORDER BY sLast');
								$interviewr = '<select class="form-control" name="interviewer"><option value="">Select interviewer</option>';
								foreach($staffs AS $s):
									$interviewr .= '<option value="'.$s['name'].'">'.$s['sLast'].', '.$s['sFirst'].'</option>';
								endforeach;									
								$interviewr .= '</select>';
								echo $interviewr;
							?>
							</td>
						</tr>
						<tr>
							<td>Additional Remarks for HR:</td>
							<td><textarea name="remarks" class="form-control" rows=10></textarea></td>
						</tr>
						<tr>
							<td colspan=2 align="right">
								<!--<button class="btn btn-danger btn-sm" id="submitBtn">Submit New Requisition</button>-->
								<input id="submtBtn" type="submit" class="btn btn-danger btn-sm" value="Submit New Requisition"/>
							</td>
						</tr>
					</table>
				</div><!-- end of step3 div-->
				</form><!-- end of form-->
				
			</div>
		</td>
	</tr>	
</table>
<script type="text/javascript">
	$(function(){
		$(".inline").colorbox({inline:true, width:"50%"});
		
		$('#choosePos').click(function(){
			if($('#selpositionID').val()==''){
				alert('Please choose position');
			}else{
				$('#positionID').val($('#selpositionID').val());
				changeStep(3);
			}
		});
		
		$('#startDate').datetimepicker({
			format:'F d, Y',
			timepicker:false
		}); 
		
		$('#subgrp').change(function(){
			$('#description').removeClass('hide');
			$('#next').removeClass('hide');
		});
		
	});
	
	function validateJobForm(){
		valid = '';
		if($('#num').val()=='')
			valid += '-  How many slots\n';
		if($('#startDate').val()=='')
			valid += '-  Start Date\n';
		if($('#supervisor').val()=='')
			valid += '-  Immediate supervisor\n';
		if($('#minSal').val()=='')
			valid += '-  Mininum Salary\n';
		if($('#maxSal').val()=='')
			valid += '-  Maximum Salary\n';
		if($('#shift').val()=='' && (Math.abs($('#startShift').val() - $('#endShift').val() ) != 9 )){
			valid += '-  Valid start and end shift\n';
		}
		if($('select[name="interviewer"]').val()=='')
			valid += '-  Interviewer is empty\n';
					
		if(valid!=''){
			alert('Please fill up missing/invalid fields:\n'+valid);
			return false;
		}else{
			$('#submtBtn').attr('disabled', 'disabled');
			return true;
		}
	}
	
	function changeStep(step){
		if(step==2){
			$('#stepTexts').html('');
			$('#step1').addClass('hide');
			$('#step2').removeClass('hide');				
			$('#btnStep2').removeAttr('disabled');
			$('#btnStep2').addClass('btn-primary');
		}else if(step==3){
			$('#stepTexts').html('Submission');
			$('#step2').addClass('hide');
			$('#step3').removeClass('hide');				
			$('#btnStep3').removeAttr('disabled');
			$('#btnStep3').addClass('btn-primary');
		}
	
	}
	
	function funcRequestType(t){
		changeStep(2);
		
		$('#requestType').val(t);
		
		if(t=='new'){
			$('#formNew').removeClass('hide');
		}else{
			$('#formExisting').removeClass('hide');
		}			
	}
	
	function positionText(){		
		$.post("jobrequisition.php?position=text",
		{
			pos:$('#selpositionID').val()
		},
		function(data){
			$('#formHeadText').html(data);
		});		
	}
	
	function checkShift(){
		var sh = $('#shift').val();
		if(sh==''){
			$('#customShift').css('display', '');
		}else{
			$('#customShift').css('display', 'none');
		}
	}
	
	function validateNew(){
		valText = '';
		if($('#title').val()=='')
			valText += '-  New position\n';
		if($('#desc').val()=='')
			valText += '-  Job Description\n';
		if($('#org').val()=='')
			valText += '-  Organization\n';
		if($('#dept').val()=='')
			valText += '-  Department\n';
		if($('#grp').val()=='')
			valText += '-  Group\n';
		if($('#subgrp').val()=='')
			valText += '-  Sub-group\n';
		
		if(valText!=''){
			alert('Please input missing fields\n'+valText);
		}else{			
			$.post("jobrequisition.php?create=new",
			{
				title:$('#title').val(),
				desc:$('#desc').val(),
				org:$('#org').val(),
				dept:$('#dept').val(),
				grp:$('#grp').val(),
				subgrp:$('#subgrp').val(),
				user:'<?= $username ?>'
			},
			function(data){
				$('#positionID').val(data);
				changeStep(3);
				$.post("jobrequisition.php?position=text", { pos:data },
				function(data2){ 
					$('#formHeadText').html(data2);
				});				
			}
		); 
			
		}
	}
	
	function getOrgVal(c, n){
		$.post("jobrequisition.php?cur="+c+"&n="+n,
			{typeVal:$('#'+c).val()},
			function(data){ 
				$('#tr_'+n).removeClass('hide');
				$('#'+n).html(data); 
			}
		); 
		$('#'+c+'Val').val($('#'+c).val());
	}
	
</script>
<?php 
}

require "includes/footer.php";
?>
