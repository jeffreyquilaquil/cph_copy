<?php
if($segment2==''){
?>
<style type="text/css">
	.dvstyle{ padding:30px; border:1px solid #ccc; border-radius:10px; width:45%; cursor:pointer; }
	.dvstyle:hover{ background-color:#dedede; }
</style>
<center style="margin:40px 0;">
	<div class="dvstyle" onClick="location.href='<?= $this->config->base_url() ?>fileleave2/leave/'">PAID TIME OFF(PTO) / LEAVE REQUEST</div>
	<div class="dvstyle" onClick="location.href='<?= $this->config->base_url() ?>fileleave2/offset/'">OFFSETTING / CHANGE OF WORK SCHEDULE</div>
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
	
	echo '<div class="errortext hidden"></div>';
	
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
			}
			
			if($segment2=='offset'){ ?>
				<tr>
					<td width="30%">Available Leave Credits</td>
					<td><b><?= $this->user->leaveCredits ?></b></td>
				</tr>			
				<form class="leaveForm" action="" method="POST">
				<tr>
					<td width="30%">Please specify reason of absence</td>
					<td><input type="text" name="reason" class="forminput" maxlength="150" value="<?= ((isset($_POST['reason']))?$_POST['reason']:'') ?>" placeholder="Note that the approval of this leave request may depend on the reason provided"/></td>
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
					<td>Additional Notes<br/><i style="color:#555;">(Optional)</i></td>
					<td><textarea name="notesforHR" class="forminput"><?= ((isset($_POST['notesforHR']))?$_POST['notesforHR']:'') ?></textarea></td>
				</tr>
				<tr>
					<td><br/></td>
					<td>
						<input type="hidden" name="submitType" value="offset"/>
						<input type="button" class="submitLeave padding5px" value="Submit Leave"/> <img id="imgLoading" src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25" class="hidden"/>
					</td>
				</tr>
			</form>
			<?php
			}else{ //end of segment2 offset and start of segment2 leave
			
			} //end of segment2 leave
		
		} //end of if signature exist
		
		
		echo '</table>';
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
		
		$('.submitLeave').click(function(){	
			checkEmp = true;
			if($('input[name=submitType]').val()=='offset'){
				if($('input[name=reason]').val()=='' ||
					$('input[name="leaveStart"]').val()=='' ||
					$('input[name="leaveEnd"]').val()=='' ||
					$('input[name="totalHours"]').val()=='' ||
					$('input[name="offdate\[0\]\[start\]"]').val()=='' ||
					$('input[name="offdate\[0\]\[end\]"]').val()==''					
				) checkEmp = false;
			}
			
			if(checkEmp==false) alert('Please check required fields.');
			else{
				$('#imgLoading').removeClass('hidden');
				$('.submitLeave').attr('disabled', 'disabled');
				
				$.post('<?= $this->config->item('career_uri') ?>',
				$('.leaveForm').serializeArray(),
				function(err){
					if(err!='submitted'){
						$(window).scrollTop(0);
						$('.errortext').removeClass('hidden');
						$('.errortext').html('<b>Please check errors:</b><ul>'+err+'</ul><hr/>');
						
						$('#imgLoading').addClass('hidden');
						$('.submitLeave').removeAttr('disabled');
					}else{
						$('.tableInfo').addClass('hidden');
						$('.errortext').removeClass('hidden');
						$('.errortext').html('<b>Your leave has been submitted. You can check your filed leaves on My HR Info > Time Off Details.</b>');
					}					
				});
			}
			
		});
	
	});
	
	function koiStart(newdate){
		if(newdate.getDay()<=2) newdate.setDate(newdate.getDate() - 11);
		else newdate.setDate(newdate.getDate() - 9);
	
		return newdate.getFullYear()+'/'+(newdate.getMonth()+ 1)+'/'+newdate.getDate();
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
			if($('#leaveStart').val()!=''){
				var startDate = new Date($('#leaveStart').val());
				var today = new Date();

				if (startDate > today){
					$('#divsupdocsBox').addClass('hidden');
					$('#divsupdocs').removeClass('hidden');
				}else if($('#noDocs').is(':checked')==true){
					$('#divsupdocsBox').removeClass('hidden');
					$('#divsupdocs').addClass('hidden');
				}else{
					$('#divsupdocsBox').removeClass('hidden');
					$('#divsupdocs').removeClass('hidden');
				}
			}else if($('#noDocs').is(':checked')==true){
				$('#divsupdocs').addClass('hidden');
			}
			
			if(ltype==5){
				$('#notePaternity').removeClass('hidden');
				$('#divsupdocsBox').addClass('hidden');
				$('#sreasontd').html('Child Number<br/><i style="color:#555;">Example:<br/>Eldest Child = 1<br/>Second Child = 2<br/>Third Eldest = 3</i>');
			}else{
				$('#notePaternity').addClass('hidden');
				$('#sreasontd').html('Please specify reason<br/><i style="color:#555;">Note that the approval of this leave request may depend on the reason provided:</i>');
			}			
		}else{
			$('#upsupdocs').addClass('hidden');
		}
	}
</script>