<?php
if($segment2==''){
?>
<style type="text/css">
	.dvstyle{ padding:30px; border:1px solid #ccc; border-radius:10px; width:45%; cursor:pointer; }
	.dvstyle:hover{ background-color:#dedede; }
</style>
<center style="margin:40px 0;">
	<div class="dvstyle" onClick="location.href='<?= $this->config->base_url() ?>fileleave/leave/'">PAID TIME OFF(PTO) / LEAVE REQUEST</div>
	<div class="dvstyle" onClick="location.href='<?= $this->config->base_url() ?>fileleave/offset/'">OFFSETTING / CHANGE OF WORK SCHEDULE</div>
</center>
<?
}else{

if($segment2=='offset'){
	echo '<h2>OFFSETTING / CHANGE OF WORK SCHEDULE FORM</h2>';
	echo '<hr/>';
	echo '<p>The Offsetting / Change of Work Schedule Form is used by full-time regular staff in accordance with company policy. Work can be rendered outside of the regular office hours (except Sundays) to offset absences or undertimes incurred due to emergency or health reasons, if approved by the Director or Chief Business Development Officer. The benefit of offsetting of work schedule can only be availed for a maximum of two (2) times in a month and must be completed within seven (7) business days from the time that the absence/undertime is incurred. Only employees with zero (0) time off credits can render work outside of the regular office hours to offset absences or undertimes. For detailed information regarding the administration of paid time off, refer to the Code of conduct and/or the Employee Handbook. For information on requesting a leave of absence contact the Human Resources Department.</p>';
	
	$num = 0;
	foreach($numOffset AS $n):
		$num += $n->totalHours;
	endforeach;
	
	if($num>=16){
		echo '<p class="errortext">You have already requested for '.$num.' hours of offset for the month of '.date('F').', you may not file any more offset request for additional absences in '.date('F').'.</p>';
	}
	
}else{
	echo '<h2>PAID TIME OFF(PTO) / LEAVE REQUEST FORM</h2>';
	echo '<hr/>';
	echo '<p>The Paid Time Off (PTO) / Leave Request Form is used by full-time reqular staff in accordance with company policy. All time off requests require the approval of your immediate supervisor. Vacation Leaves must be filed two weeks in advance and *Sick Leaves must be filed within 24 hours from returning to work. For detailed information regarding the administration of paid time off, refer to the Code of Conduct and/or the Employee Handbook.</p>';
}	

	if($submitted){
			echo '<p class="errortext">Your leave has been submitted.</p>
			<script>parent.location.reload();</script>';
	}else{	
		if(!empty($errortxt)) echo '<p class="errortext"><b>Please check your inputted values:</b><br/>'.$errortxt.'</p><hr/>';
		
		if($segment2=='leave' || ($segment2=='offset' && $num<16)){
			echo '<table class="tableInfo">';
			
			$signature = UPLOAD_DIR.$this->user->username.'/signature.png';
			if(!file_exists($signature)){
				echo '<form id="formUpload" action="'.$this->config->base_url().'upsignature/" method="POST" enctype="multipart/form-data">';
				echo '<tr>
					<td class="errortext" width="40%">No signature on file.<br/>Please upload signature with transparent background first before filing a leave.</td>
					<td> 
						<input type="file" name="fileToUpload" id="fileToUpload"/>
						<input type="hidden" name="page" value="'.$_SERVER['REQUEST_URI'].'"/>
					</td>
				</tr>';
				echo '</form>';
			}else{
				if(file_exists($signature)){ 
					clearstatcache();
					echo '<tr>';
					echo '<td>Signature</td>';
					echo '<td><img src="'.$this->config->base_url().$signature.'"/><br/><a href="'.$this->config->base_url().'upsignature/'.str_replace('/','-',$_SERVER['REQUEST_URI']).'/'.'">Click Here to Change your Signature</a></td>';					
					echo '</tr>';
					
					echo '<tr>
							<td width="30%">Available Leave Credits</td>
							<td><b>'.$this->user->leaveCredits.'</b></td>
						</tr>';
				}
				
				echo '<form class="leaveForm" action="" method="POST" onSubmit="return validateForm();" enctype="multipart/form-data">';
				if($segment2=='offset'){ ?>
					<tr class="hidden"><td colspan="2">
						<input type="hidden" name="code" value="4"/>
						<input type="hidden" name="leaveType" value="4"/>
						<input type="hidden" name="submitType" value="offset"/>
					</td></tr>
					<tr>
						<td width="30%">Please specify reason of absence</td>
						<td><input type="text" id="reason" name="reason" class="forminput" maxlength="150" value="<?= ((isset($_POST['reason']))?$_POST['reason']:'') ?>" placeholder="Note that the approval of this leave request may depend on the reason provided"/></td>
					</tr>
					<tr>
						<td>Start Date and Time of Absence</td>
						<td><input id="leaveStart" name="leaveStart" type="text" class="forminput datetimeoffset" placeholder="Month day, year hour:min" value="<?= ((isset($_POST['leaveStart']))?$_POST['leaveStart']:'') ?>"></td>
					</tr>
					<tr>
						<td>End Date and Time of Absence</td>
						<td><input id="leaveEnd" name="leaveEnd" type="text" class="forminput datetimeoffset" placeholder="Month day, year hour:min" value="<?= ((isset($_POST['leaveEnd']))?$_POST['leaveEnd']:'') ?>"></td>
					</tr>
					<tr>
						<td>Total Number of Hours to Compensate</td>
						<td><input id="totalHours" name="totalHours" type="text" class="forminput" placeholder="Ex.: 4, 8, 16" value="<?= ((isset($_POST['totalHours']))?$_POST['totalHours']:'') ?>"/></td>
					</tr>
					<tr>
						<td>Schedule of Work to Compensate Absence<br/><i style="color:#555;">(Select up to 7 days)</i></td>
						<td>
							<table class="tableInfo" id="tableSched">
								<tr>
									<td width="35%"><b>Date and Start Time</b></td>
									<td width="35%"><b>Date and End Time</b></td>
									<td width="30%"><br/></td>
								</tr>
							<?php
								for($u=0; $u<7; $u++){										
									echo '<tr id="tr'.$u.'" ';						
									
									if(($u>0 && !isset($_POST['offdate'])) || ($u>0 && (isset($_POST['offdate']) && empty($_POST['offdate'][$u]['start']) && empty($_POST['offdate'][$u]['end'])))){
										echo 'class="hidden"';			
									}
									
									echo '><td><input name="offdate['.$u.'][start]" type="text" class="forminput datetimeoffset" placeholder="Month day, year hour:min" value="'.((isset($_POST["offdate"][$u]["start"]))?$_POST["offdate"][$u]["start"]:'').'"></td>
										<td><input name="offdate['.$u.'][end]" type="text" class="forminput datetimeoffset" placeholder="Month day, year hour:min" value="'.((isset($_POST["offdate"][$u]["end"]))?$_POST["offdate"][$u]["end"]:'').'"></td>';
										
									echo '<td id="td'.$u.'">';
									if($u>0) echo '<input type="button" value="- Remove" onClick="removetr('.$u.')"/>';
									if($u<6) echo '<input type="button" value="+ Add" class="add" onClick="addtr('.($u+1).', this)"/>';
										
									echo '</td></tr>';
								}				
							?>							
							</table>
						</td>
					</tr>
					<tr>
						<td>Upload Supporting Documents<br/><i style="color:#555;">Upload up to 5 documents</i></td>
						<td>
							<div id="divsupdocsBox">
								<input type="checkbox" id="noDocs" name="noDocs" <?= ((isset($_POST['noDocs']))?'checked':'')?>/> I DON'T HAVE SUPPORTING DOCUMENTATION<br/>
								<i style="color:#555;">Note that leaves that do not have valid supporting documentation shall be UNPAID. Absences without formal leave applications submitted are considered AWOL</i><br/><br/>
							</div>
							<div id="divsupdocs">
								<input type="file" name="supDocs[]" id="f0" style="width:100%;"/>
								<input type="file" name="supDocs[]" id="f1" style="width:100%;" class="hidden"/>
								<input type="file" name="supDocs[]" id="f2" style="width:100%;" class="hidden"/>
								<input type="file" name="supDocs[]" id="f3" style="width:100%;" class="hidden"/>
								<input type="file" name="supDocs[]" id="f4" style="width:100%;" class="hidden"/>
							</div>
							<div id="paternityNote" class="hidden"><br/>
								<input type="button" id="chooseSupDocs" value="Choose from uploaded Personnel Files"/> <img id="chooseImg" class="hidden" src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="20px"/><br/>
								<input type="hidden" name="fromUploaded" value=""/>
								<div id="uploadedFiles"></div>
								<i style="color:#555;">Note that supporting documents are required for Paternity Leaves: (a) Marriage Certificate (b) Ultrasound Report of Lawful Wife.<br/>
								If you do not have the above required documents, please file Vacation leave instead.</i>
							</div>
						</td>
					</tr>
					<tr>
						<td>Additional Notes<br/><i style="color:#555;">(Optional)</i></td>
						<td><textarea name="notesforHR" class="forminput"><?= ((isset($_POST['notesforHR']))?$_POST['notesforHR']:'') ?></textarea></td>
					</tr>				
				<?php
				}else{ //end of segment2 offset and start of segment2 leave
				?>
				<tr class="hidden"><td colspan="2"><input type="hidden" name="submitType" value="leave"/></td></tr>
				<tr>
					<td width="30%">Type of Leave Requested</td>
					<td>
						<select name="leaveType" id="leaveType" class="forminput">
							<option value="1" <?= ((isset($_POST['leaveType']) && $_POST['leaveType']==1)? 'selected="selected"': '') ?>>Vacation Leave</option>
							<option value="2" <?= ((isset($_POST['leaveType']) && $_POST['leaveType']==2)? 'selected="selected"': '') ?>>Sick Leave</option>
							<option value="3" <?= ((isset($_POST['leaveType']) && $_POST['leaveType']==3)? 'selected="selected"': '') ?>>Emergency Leave</option>
							<option value="5" <?= ((isset($_POST['leaveType']) && $_POST['leaveType']==5)? 'selected="selected"': '') ?>>Paternity Leave</option>
							<option value="6" <?= ((isset($_POST['leaveType']) && $_POST['leaveType']==6)? 'selected="selected"': '') ?>>Maternity Leave</option>
							<option value="7" <?= ((isset($_POST['leaveType']) && $_POST['leaveType']==7)? 'selected="selected"': '') ?>>Solo Parent Leave</option>
							<option value="8" <?= ((isset($_POST['leaveType']) && $_POST['leaveType']==8)? 'selected="selected"': '') ?>>Special Leave for Women</option>
						</select>
					</td>
				</tr>
				<tr>
					<td id="sreasontd">Please specify reason<br/>
						<i style="color:#555;">Note that the approval of this leave request may depend on the reason provided:</i>
					</td>
					<td><input id="reason" name="reason" type="text" class="forminput" maxlength="50" value="<?= ((isset($_POST['reason'])) ? $_POST['reason']:'' ) ?>" placeholder=""></td>
				</tr>
				<tr>
					<td>Start of Leave</td>
					<td><input id="leaveStart" name="leaveStart" type="text" class="forminput datetimepick" value="<?= ((isset($_POST['leaveStart'])) ? $_POST['leaveStart']:'' ) ?>" placeholder="Month day, year hour:min"></td>
				</tr>
				<tr>
					<td>End of Leave</td>
					<td><input id="leaveEnd" name="leaveEnd" type="text" class="forminput datetimepick" value="<?= ((isset($_POST['leaveEnd'])) ? $_POST['leaveEnd']:'' ) ?>" placeholder="Month day, year hour:min"></td>
				</tr>
				<tr>
					<td>Total Number of Hours</td>
					<td><input id="totalHours" name="totalHours" type="text" class="forminput" value="<?= ((isset($_POST['totalHours'])) ? $_POST['totalHours']:'' ) ?>" placeholder="Ex.: 4, 8, 16"/></td>
				</tr>
				<tr id="trcode" class="hidden">
					<td>Code <i style="color:#555;">(Optional)</i>
					</td>
					<td><input type="text" name="code" id="code" class="forminput"/><br/>
						<i style="color:#555;"><span style="color:#555;" id="codetxt"></span></i>
					</td>
				</tr>
				<tr id="upsupdocs" class="hidden">
					<td>Upload Supporting Documents<br/><i style="color:#555;">Upload up to 5 documents</i></td>
					<td>
						<div id="divsupdocsBox">
							<input type="checkbox" id="noDocs" name="noDocs" <?= ((isset($_POST['noDocs']))?'checked':'')?>/> I DON'T HAVE SUPPORTING DOCUMENTATION<br/>
							<i style="color:#555;">Note that leaves that do not have valid supporting documentation shall be UNPAID. Absences without formal leave applications submitted are considered AWOL</i><br/><br/>
						</div>
						<div id="divsupdocs">
							<input type="file" name="supDocs[]" id="f0" style="width:100%;"/>
							<input type="file" name="supDocs[]" id="f1" style="width:100%;" class="hidden"/>
							<input type="file" name="supDocs[]" id="f2" style="width:100%;" class="hidden"/>
							<input type="file" name="supDocs[]" id="f3" style="width:100%;" class="hidden"/>
							<input type="file" name="supDocs[]" id="f4" style="width:100%;" class="hidden"/>
						</div>
						<div id="paternityNote" class="hidden"><br/>
							<input type="button" id="chooseSupDocs" value="Choose from uploaded Personnel Files"/> <img id="chooseImg" class="hidden" src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="20px"/><br/>
							<input type="hidden" name="fromUploaded" value=""/>
							<div id="uploadedFiles"></div>
							<i style="color:#555;">Note that supporting documents are required for Paternity Leaves: (a) Marriage Certificate (b) Ultrasound Report of Lawful Wife.<br/>
							If you do not have the above required documents, please file Vacation leave instead.</i>
						</div>
					</td>
				</tr>
				<tr>
					<td>Additional Notes<br/><i style="color:#555;">(Optional)</i></td>
					<td><textarea name="notesforHR" class="forminput"><?= ((isset($_POST['notesforHR'])) ? $_POST['notesforHR']:'' ) ?></textarea></td>
				</tr>
			<?php	
				} //end of segment2 leave
				
				echo '<tr>
					<td><br/></td>
					<td>					
						<input type="submit" class="submitLeave padding5px" value="Submit Leave"/> <img id="imgLoading" src="'.$this->config->base_url().'css/images/small_loading.gif'.'" width="25" class="hidden"/>
					</td>
				</tr>';
				
				echo '</form>';
			
			} //end of if signature exist
			
			
			echo '</table>';
		}
	}	
	
} //end of segment2 offset or leave
?>

<script type="text/javascript">
	$(function(){ 
		codeText($('#leaveType').val());
		$('.datetimeoffset').datetimepicker({ 
			format:'F d, Y H:00',
			minDate:koiStart(new Date()),
			maxDate:koiEnd(new Date())
		});	
		
		$('#leaveStart').blur(function(){
			if($(this).val()!=''){
				$('.datetimeoffset').datetimepicker({ 
					format:'F d, Y H:00',
					minDate:koiStart(new Date($(this).val())),
					maxDate:koiEnd(new Date($(this).val()))
				});
				codeText($('#leaveType').val());
			}			
		});
				
		$('#fileToUpload').change(function(){
			displaypleasewait();
			$('#formUpload').submit();
		});	
		
		if($('#leaveType').val()<=3) $('#trcode').removeClass('hidden');
			
		$('#leaveType').change(function(){
			if($('#leaveType').val()<=3) $('#trcode').removeClass('hidden');
			else $('#trcode').addClass('hidden');
			codeText($('#leaveType').val());
		});
		
		$('input[name="supDocs\[\]"]').change(function(){
			id = $(this).attr('id');
			if(id=='f0') $('#f1').removeClass('hidden');
			else if(id=='f1') $('#f2').removeClass('hidden');
			else if(id=='f2') $('#f3').removeClass('hidden');
			else if(id=='f3') $('#f4').removeClass('hidden');
		});
		
		
		$('#noDocs').click(function(){
			if($(this).is(":checked")==true){
				$('#divsupdocs').addClass('hidden');
			}else{
				$('#divsupdocs').removeClass('hidden');
			}			
		});
		
		$('#chooseSupDocs').click(function(){				
			if($('#uploadedFiles').hasClass('hidden')){
				$('#uploadedFiles').removeClass('hidden');
				$('#chooseSupDocs').hide();
			}else{	
				$('#chooseImg').show();
				$.post('<?= $this->config->item('career_uri') ?>',{submitType:'chooseFromUploaded'}, 
				function(d){
					$('#uploadedFiles').removeClass('hidden');
					$('#uploadedFiles').html(d+'<input type="button" onClick="closeUploaded();" value="Close"/>');
					$('#chooseImg').hide();	
					$('#chooseSupDocs').hide();
				});
			}
		});
				
		
	});
		
	function koiStart(newdate){
		if(newdate.getDay()<=2) newdate.setDate(newdate.getDate() - 11);
		else newdate.setDate(newdate.getDate() - 9);
	
		return newdate.getFullYear()+'/'+(newdate.getMonth()+ 1)+'/'+newdate.getDate();
	}
	
	function addSupVal(v, d){
		curv = $('input[name="fromUploaded"]').val();
		if($(d).is(':checked')==true){			
			$('input[name="fromUploaded"]').val(curv+v+'|');
		}else{
			newval = curv.replace(v+'|', '');
			$('input[name="fromUploaded"]').val(newval);
		}
				
	}
	
	function koiEnd(newdate){
		if(newdate.getDay()<=3) newdate.setDate(newdate.getDate() + 9);
		else newdate.setDate(newdate.getDate() + 11);
		
		return newdate.getFullYear()+'/'+(newdate.getMonth()+ 1)+'/'+newdate.getDate();
	}
		
	function addtr(id, th){
		$(th).remove();
		$('#tableSched #tr'+id).removeClass('hidden');
	}
	
	function removetr(d){
		$('#tableSched #tr'+d).addClass('hidden');
		id = d-1;
		$('#tr'+id+' #td'+id).append('<input type="button" value="+ Add" class="add" onClick="addtr('+d+', this)"/>');
		$('#tr'+d+' input').val('');
	}
	
	function codeText(leave){
		if(leave==1)
			$('#codetxt').text('The Code is required for vacation leave applications submitted less than 2 weeks to the date of absence. Request for this code from your immediate supervisor before submitting a vacation leave request.');
		else if(leave==2)
			$('#codetxt').text('The Code is required for sick leave applications submitted prior to date of absence. Request for this code from your immediate supervisor before submitting a scheduled sick leave request. Be prepared with required supporting documentation.');
		else if(leave==3)
			$('#codetxt').text('The Code is required for emergency leave applications submitted prior to date of absence. Request for this code from your immediate supervisor before submitting an emergency leave request. Be prepared with required supporting documentation.');
		
		ltype = $('#leaveType').val();
		if(ltype==2 || ltype==3 || ltype==5){
			$('#upsupdocs').removeClass('hidden');
			$('#divsupdocsBox').removeClass('hidden');
			$('#divsupdocs').removeClass('hidden');	
			
			if($('#leaveStart').val()!=''){
				var startDate = new Date($('#leaveStart').val());
				var today = new Date();

				if (startDate > today){
					$('#divsupdocsBox').addClass('hidden');
					$('#divsupdocs').removeClass('hidden');
				}else if($('#noDocs').is(':checked')==true){
					$('#divsupdocsBox').removeClass('hidden');
					$('#divsupdocs').addClass('hidden');
				}
			}else if($('#noDocs').is(':checked')==true){
				$('#divsupdocs').addClass('hidden');
			}
			
			if(ltype==5){
				$('#divsupdocsBox').addClass('hidden');
				$('#divsupdocs').removeClass('hidden');
				$('#paternityNote').show();
				$('#sreasontd').html('Child Number<br/><i style="color:#555;">Example:<br/>Eldest Child = 1<br/>Second Child = 2<br/>Third Eldest = 3</i>');
			}else{
				$('#paternityNote').hide();
				$('#sreasontd').html('Please specify reason<br/><i style="color:#555;">Note that the approval of this leave request may depend on the reason provided:</i>');
			}
		}else{
			$('#upsupdocs').addClass('hidden');
		}
	}
	
	function validateForm(){
		valid = true;
		errtxt = '';
		hours = true;
		if($('#reason').val()=='' ||
			$('#leaveStart').val()=='' ||
			$('#leaveEnd').val()=='' ||
			$('#totalHours').val()==''
		) valid = false;
		
		if($('#totalHours').val()!='' && (!$.isNumeric($('#totalHours').val()) || $('#totalHours').val()%4 !=0)){
			valid = false;
			errtxt = 'Total hours is invalid.\n';
		}
		
		if($('#leaveType').val()==5 && $('#reason').val()!='' && (!$.isNumeric($('#reason').val()) || $('#reason').val()==0)){
			valid = false;
			errtxt = 'Child number is invalid.\n';
		}
		
		if(valid){
			if($('input[name=submitType]').val()=='offset' && ($('input[name="offdate\[0\]\[start\]"]').val()=='' || $('input[name="offdate\[0\]\[end\]"]').val()=='')){
				valid = false;
			}else if($('#leaveType').val()==''){
				valid = false;
			}
		}
					
		if(valid==false){
			alert('Please check required fields.\n'+errtxt);
		}else{
			$('input[type="submit"]').attr('disabled', 'disabled');
			$('#imgLoading').show();
		}
		
		return valid;
	}
	
	function closeUploaded(){
		$('#chooseSupDocs').show();
		$('#uploadedFiles').addClass('hidden');
		$('input[name="fromUploaded"]').val('');
		$(".upFile").prop('checked', false); 
	}
</script>
