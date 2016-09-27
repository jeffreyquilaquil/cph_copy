<?php
if($updated==false && count($row)>0){
?>
<h2>Change in Status for <?= $row->name ?></h2>
<hr/>
<table class="tableInfo">
	<tr class="trlabel"><td colspan=2>Select the information to be updated</td></tr>
	<tr><td colspan=2>
		<input type="checkbox" id="holiday" <? if(isset($wonka->fieldname) && $wonka->fieldname=='holiday'){ echo 'checked'; } ?>><label for="holiday">Holiday Schedule</label> <br/>
		<input type="checkbox" id="title" <? if(isset($wonka->fieldname) && $wonka->fieldname=='title'){ echo 'checked'; } ?>> <label for="title">Position Title</label> <br/>
		<input type="checkbox" id="office" <? if(isset($wonka->fieldname) && $wonka->fieldname=='office'){ echo 'checked'; } ?>> <label for="office">Office branch</label> <br/>
		<input type="checkbox" id="shift" <? if(isset($wonka->fieldname) && $wonka->fieldname=='shift'){ echo 'checked'; } ?>> <label for="shift">Shift Schedule</label> <br/>
		<input type="checkbox" id="isup"> <label for="isup">Immediate Supervisor</label> <br/>
	<?php if($row->endDate!='0000-00-00'){ ?><input type="checkbox" id="enddate"> <label for="enddate"> Separation Date</label> <br/><? } ?>
		<input type="checkbox" id="empstatus" <? if(isset($wonka->fieldname) && $wonka->fieldname=='regDate'){ echo 'checked'; } ?>> <label for="empstatus"> Employment Status / Regularization Date</label> <br/>
		<input type="checkbox" id="salary" <? if(isset($wonka->fieldname) && $wonka->fieldname=='sal'){ echo 'checked'; } ?>> <label for="salary">Basic Salary</label> <br/>
	</td></tr>
<form action="" method="POST" onSubmit="return validateform();">
	<!----- Holiday Schedule ---->
	<tr class="trholiday trblank hidden"><td colspan="2"><br/></td></tr>
	<tr class="trlabel trholiday hidden">
		<td width="40%">Current holiday schedule</td>
		<td>Select new holiday schedule</td>
	</tr>
	<tr class="trholiday hidden">
		<td><?php echo $holidaySched_array[ $row->staffHolidaySched ]; ?></td>
		<td>
			<select class="forminput" name="staffHolidaySched" id="valholiday">
				<option value=""></option>
				<?php foreach( $holidaySched_array as $key => $val){
						echo '<option value="'.$key.'">'.$val.'</option>';
					}
				?>
			</select>
		</td>
	</tr>

	<!-------------------------------			TITLE				---------->
	<tr class="trtitle trblank hidden"><td colspan=2><br/></td></tr>
	<tr class="trlabel trtitle hidden">
		<td width="40%">Current position title</td>
		<td>Select new position title</td>
	</tr>
	<tr class="trtitle hidden">
		<td><?= $row->title ?></td>
		<td>
			<select class="forminput" name="position" id="valtitle">
			<option value=""></option>
			<?php
			foreach($departments AS $d):
				if($d->title!=$row->title && $d->active==1){
					echo '<option value="'.$d->posID.'|'.$d->title.'" '.((isset($wonka->fieldname) && $wonka->fieldname=='title' && $d->posID==$wonka->fieldvalue)?'selected':'').'>'.$d->title.' of ('.$d->org.'-'.$d->dept.'-'.$d->grp.'-'.$d->subgrp.')</option>';
				}
			endforeach;
			?>
			</select>		
		</td>
	</tr>
	<!-------------------------------				OFFICE			---------->
	<tr class="troffice trblank hidden"><td colspan=2><br/></td></tr>
	<tr class="trlabel troffice hidden">
		<td width="40%">Current office branch</td>
		<td>Select new office branch</td>
	</tr>
	<tr class="troffice hidden">
		<td width="40%"><?= ucfirst($row->office) ?></td>
		<td>
			<select name="office" id="valoffice" class="forminput">
				<option value=""></option>
			<?php
				$otype = $this->textM->constantArr('office');
				foreach($otype AS $o):
					echo '<option value="'.$o.'">'.$o.'</option>';
				endforeach;
			?>
			</select>
		</td>
	</tr>
	<!-------------------------------				SHIFT			---------->
	<tr class="trshift trblank hidden"><td colspan=2><br/></td></tr>
	<tr class="trlabel trshift hidden">
		<td width="40%">Current shift schedule</td>
		<td>Enter new shift schedule</td>
	</tr>
	<tr class="trshift hidden">
		<td width="40%"> <?= $row->shift ?> </td>
		<td><input type="text" name="shift" id="valshift" class="forminput" value="<?php if(isset($wonka->fieldname) && $wonka->fieldname=='shift'){ echo $wonka->fieldvalue; } ?>" placeholder="07:00am - 04:00pm Mon-Fri"/></td>
	</tr>
	<!-------------------------------				ISUP			---------->
	<tr class="trisup trblank hidden"><td colspan=2><br/></td></tr>
	<tr class="trlabel trisup hidden">
		<td width="40%">Current immediate supervisor</td>
		<td>Select new immediate supervisor</td>
	</tr>
	<tr class="trisup hidden">
		<td width="40%"><?= $row->supName ?></td>
		<td>
			<select class="forminput" name="supervisor" id="valisup">
				<option value=""></option>
			<?php
			foreach($supervisorsArr AS $s):
				echo '<option value="'.$s->empID.'|'.$s->name.'">'.$s->name.'</option>';
			endforeach;
			?>
			</select>	
		</td>
	</tr>
	<!-------------------------------			SALARY				---------->
	<tr class="trsalary trblank hidden"><td colspan=2><br/></td></tr>
	<tr class="trlabel trsalary hidden">
		<td width="40%">Current basic salary</td>
		<td>Select new basic salary</td>
	</tr>
<?php
	$curSal = $this->textM->convertNumFormat($this->textM->decryptText($row->sal));
	if(empty($curSal) || $curSal=='0.00' || $curSal=='0'){
		echo '<tr class="trsalary hidden"><td colspan=2 class="errortext">Current salary is empty. Please request HR to update current salary before you can file for salary change.
			<input type="hidden" name="salary" id="valsalary" value=""/></td></tr>';
	}else{
?>
	<tr class="trsalary hidden">
		<td width="40%">Php <?= $curSal ?></td>
		<td><input type="text" name="salary" id="valsalary" class="forminput" placeholder="10,000.00" value="<?= ((isset($wonka->fieldname) && $wonka->fieldname=='sal')?$this->staffM->convertNumFormat($this->textM->decryptText($wonka->fieldvalue)):'')?>"/></td>
	</tr>
	<tr class="trsalary hidden">
		<td width="40%">Enter justification (<i>this is required</i>)</td>
		<td><input type="text" class="forminput" value="" name="justification" id="justification" maxlength="100"/></td>
	</tr>
<?php } ?>
	<!-------------------------------			ENDDATE				---------->
	<tr class="trenddate trblank hidden"><td colspan=2><br/></td></tr>
	<tr class="trnote trenddate hidden">
		<td colspan=2>(Important Note: Before you change  the separation date, make sure that the appropriate documentation is on file. i.e. revised resignation letter with new separation date indicated, countersigned, updated performance evaluation doc with non-regularization date updated, updated notice of termination with updated separation date, etc.)</td>
	</tr>
	<tr class="trlabel trenddate hidden">
		<td width="40%">Current separation date<br/><i class="weightnormal">Separation date is the date after the last day of work.</i></td>
		<td>Select new separation date</td>
	</tr>
	<tr class="trenddate hidden">
		<td width="40%"><?= (($row->endDate=='0000-00-00')?'N/A - Active employee':date('F d, Y', strtotime($row->endDate))) ?></td>
		<td><input type="text" class="forminput datepick" value="" name="separationDate" id="valenddate"/></td>
	</tr>
	<!-------------------------------				EMPSTATUS			---------->
	<tr class="trempstatus trblank hidden"><td colspan=2><br/></td></tr>
	<tr class="trnote trempstatus hidden">
		<td colspan=2>(Important Note: Before you change  the employment status, make sure that the appropriate documentation is on file. i.e. regularization 90 day evaluation with correct ratings and matching recommendations. Evaluation docs must always have COMPLETE signatures, otherwise, this field may not be changed.)</td>
	</tr>
	<tr class="trlabel trempstatus hidden">
		<td width="40%">Date when evaluation was conducted<br/><i class="weightnormal">Date must be based on the performance evaluation form that is submitted (the date when the employee signed).</i></td>
		<td><input type="text" class="forminput datepick" value="" name="evalDate" id="valempstatus"/></td>
	</tr>
	<tr></tr>
	<tr class="trlabel trempstatus hidden">
		<td width="40%">Effective date of regularization<br/><i class="weightnormal">The first day of being a regular employee</i></td>
	<?php
		if(isset($wonka->fieldname) && $wonka->fieldname=='regDate')
			$rDate = date('F d, Y', strtotime($wonka->fieldvalue));
		else{
			$dto = date('d'); 
			if($dto>=11 && $dto<26){ $rDate = date('F 26, Y'); }
			else if($dto>=26){ $rDate = date('F 11, Y'); }
			else{ $rDate = date('F 11, Y'); }
		}
	?>
		<td><input type="text" class="forminput datepick" value="<?= $rDate  ?>" name="regDate" id="regDate"/></td>
	</tr>
	<!-------------------------------				CHANGE			---------->
	<tr class="trchange trblank hidden"><td colspan=2><br/></td></tr>
	<tr class="trlabel trchange treffdate hidden">
		<td width="40%">Effective date of this change<br/><i class="weightnormal">Note that the changes requested cannot be implemented until the CIS is fully signed.</i></td>
		<td><input type="text" class="forminput datepick" value="<?= date('F d, Y') ?>" name="effectiveDate" id="effectiveDate"/></td>
	</tr>
	<tr class="trchange hidden">
		<td><br/></td>
		<td>
			<i style="font-size:10px;">After submission, download and print the file, let the employee and supervisors sign it.  Then submit it to HR for final update.</i><br/>
			<input type="submit" value="Submit" class="btnclass btngreen"/>
		</td>
	</tr>
</form>
</table>

<script type="text/javascript">
	$(function(){
		showhide('title');
		showhide('office');
		showhide('shift');
		showhide('isup');
		showhide('enddate');
		showhide('empstatus');
		showhide('salary');
		showhide('holiday');
		
		$('#title').click(function(){ showhide('title'); });
		$('#office').click(function(){ showhide('office'); });
		$('#shift').click(function(){ showhide('shift'); });
		$('#isup').click(function(){ showhide('isup'); });
		$('#enddate').click(function(){ showhide('enddate'); });
		$('#empstatus').click(function(){ showhide('empstatus'); });
		$('#salary').click(function(){ showhide('salary'); });
		$('#holiday').click(function(){ showhide('holiday'); });
			
	});
	
	function showhide(id){
		v = false;
		if($('#'+id).is(':checked'))
			$('.tr'+id).removeClass('hidden');
		else
			$('.tr'+id).addClass('hidden');	
		
		if($('#title').is(':checked')){
			$('#effectiveDate').val('<? $dto = date('d'); if($dto>=11 && $dto<26){ echo date('F 26, Y'); }else if($dto>=26){ echo date('F 11, Y'); }else{ echo date('F 11, Y'); } ?>');
		}else{
			$('#effectiveDate').val('<?= date('F d, Y') ?>');
		}
		
		

		$('input[type=checkbox]').each(function(){
			if( this.checked ) v = true;
		});
		
		if(v==true) $('.trchange').removeClass('hidden');
		else $('.trchange').addClass('hidden');	
		
		if($('#empstatus').is(':checked')){
			$('.treffdate').addClass('hidden');
		}
		
	}
	
	function validateform(){
		err = true;
		if($('#empstatus').is(':checked')){
			$('#effectiveDate').val($('#regDate').val());
		}
		
		if($('#effectiveDate').val()=='') err = false;
		else{
			$('input[type=checkbox]').each(function(){
				id = $(this).attr('id');
				if( this.checked ){
					//var spattern = new RegExp(/^[0-9]{2}:[0-9]{2}[a-z]{2}\s-\s[0-9]{2}:[0-9]{2}[a-z]{2}\s[a-zA-Z]{3}-[a-zA-Z]{3}$/);
					
					if(id=='shift' && $('#valshift').val()=='')
						err = false;
					
					if(id=='empstatus' && $('#regDate').val()=='')
						err = false;
						
					if(id=='salary' && $('#justification').val()=='')
						err = false;
					
					if($('#val'+id).val()=='')
						err = false;				
				}
			});
		}
				
		if(err==false){
			alert('Please check inputted and selected values.');
			return false;
		}else{
			displaypleasewait();
			return true;
		}
		
	}
</script>

<?php 
}else if($updated==true && count($row)>0){ 
	echo '<h2>Change in Status for '.$row->name.'</h2><hr/>';
	
	
	if($row->status==0){
		echo '<p><i>Before clicking "Submit" button, please make sure that immediate supervisor and second level manager approved and signed the form. Scan the signed document and upload it. Also, the changes will reflect based on the effective date.</i></p>';
	}

	
echo '<table class="tableInfo">';
	
	$cnum=0;
	$cval = array();
	$changes = json_decode($row->changes);	
	if(isset($changes->staffHolidaySched)){
		echo '<tr class="trhead"><td colspan=2>Change in Holiday Schedule</td></tr>';
		echo '<tr><td width="30%">Current Info</td><td>'.$changes->staffHolidaySched->c.'</td></tr>';
		echo '<tr><td width="30%">New Info</td><td class="errortext">'.$changes->staffHolidaySched->n.'</td></tr>';
		echo '<tr><td colspan=2><br/></td></tr>';
	}
	if(isset($changes->position)){
		echo '<tr class="trhead"><td colspan=2>Change in Position Title</td></tr>';
		echo '<tr><td width="30%">Current Info</td><td>'.$changes->position->c.'</td></tr>';
		echo '<tr><td width="30%">New Info</td><td class="errortext">'.$changes->position->n.'</td></tr>';
		echo '<tr><td colspan=2><br/></td></tr>';
	}		
	if(isset($changes->office)){
		echo '<tr class="trhead"><td colspan=2>Change in Office Branch</td></tr>';
		echo '<tr><td width="30%">Current Info</td><td>'.strtoupper($changes->office->c).'</td></tr>';
		echo '<tr><td width="30%">New Info</td><td class="errortext">'.strtoupper($changes->office->n).'</td></tr>';
		echo '<tr><td colspan=2><br/></td></tr>';
	}		
	if(isset($changes->shift)){
		echo '<tr class="trhead"><td colspan=2>Change in Shift Schedule</td></tr>';
		echo '<tr><td width="30%">Current Info</td><td>'.$changes->shift->c.'</td></tr>';
		echo '<tr><td width="30%">New Info</td><td class="errortext">'.$changes->shift->n.'</td></tr>';
		echo '<tr><td colspan=2><br/></td></tr>';
	}
	if(isset($changes->supervisor)){
		echo '<tr class="trhead"><td colspan=2>Change in Immediate Supervisor</td></tr>';
		echo '<tr><td width="30%">Current Info</td><td>'.$changes->supervisor->c.'</td></tr>';
		echo '<tr><td width="30%">New Info</td><td class="errortext">'.$changes->supervisor->n.'</td></tr>';
		echo '<tr><td colspan=2><br/></td></tr>';
	}
	if(isset($changes->salary)){
		echo '<tr class="trhead"><td colspan=2>Change in Basic Salary</td></tr>';
		echo '<tr><td width="30%">Current Info</td><td>Php '.(($changes->salary->c!='')?number_format(str_replace(',','',$changes->salary->c),2):'').'</td></tr>';
		echo '<tr><td width="30%">New Info</td><td class="errortext">Php '.number_format(str_replace(',','',$changes->salary->n),2).'</td></tr>';
		echo '<tr><td width="30%">Justification for salary adjustment</td><td class="errortext">'.$changes->salary->com.'</td></tr>';
		echo '<tr><td colspan=2><br/></td></tr>';
	}
	
	if(isset($changes->separationDate)){
		echo '<tr class="trhead"><td colspan=2>Change in Separation Date</td></tr>';
		echo '<tr><td width="30%">Current Info</td><td>'.(($changes->separationDate->c=='0000-00-00')?'N/A - Active employee':$changes->separationDate->c).'</td></tr>';
		echo '<tr><td width="30%">New Info</td><td class="errortext">'.$changes->separationDate->n.'</td></tr>';
		echo '<tr><td colspan=2><br/></td></tr>';
	}
	
	if(isset($changes->empStatus)){
		echo '<tr class="trhead"><td colspan=2>Change in Employment Status</td></tr>';
		echo '<tr><td width="30%">Current Info</td><td>'.ucfirst($changes->empStatus->c).'</td></tr>';
		echo '<tr><td width="30%">New Info</td><td class="errortext">'.ucfirst($changes->empStatus->n).'</td></tr>';
		echo '<tr><td width="30%">Date when evaluation was conducted</td><td class="errortext">'.date('F d, Y', strtotime($changes->empStatus->evalDate)).'</td></tr>';
		echo '<tr><td width="30%">Effective date of regularization</td><td class="errortext">'.date('F d, Y', strtotime($changes->empStatus->regDate)).'</td></tr>';
		echo '<tr><td>Form</td><td><a class="iframe" href="'.$this->config->base_url().'cispdf/'.$row->cisID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td></tr>';
		echo '<tr><td colspan=2><br/></td></tr>';		
	}
	if( $row->preparedby != $this->user->empID ){
		if($row->signedDoc!='' && file_exists(UPLOADS.'CIS/'.$row->signedDoc)){
		echo '<tr><td>Signed Document</td><td><a href="'.$this->config->base_url().urlencode($this->textM->encryptText('CIS')).'&f='.urlencode($this->textM->encryptText($row->signedDoc)).'" class="iframe"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a><input type="hidden" value="1" id="signed"/></td></tr>';		
		}else{
			echo '<tr><td>Upload signed document</td>
				<td><input type="hidden" value="0" id="signed"/>
					<form id="signedForm" action="" method="POST" enctype="multipart/form-data">
						<input type="file" id="signedFile" name="signedFile"/>
						<input type="hidden" name="submitType" value="signedCIS"/>					
					</form>
					</td></tr>';
		}	
	}
	
	
	if($row->status==0){
		if( $row->preparedby == $this->user->empID ){
			echo '<tr><td>Approval</td><td><input type="radio" name="approval" value="4" id="approval_4"><label for="approval_4">Cancel</labe></td></tr>';
		} else {
			echo '<tr><td>Approval</td><td><input type="radio" name="approval" value="1" id="approval_1" checked> <label for="approval_1">Approve</label>&nbsp;&nbsp;&nbsp;<input type="radio" name="approval" value="2" id="approval_2"> <label for="approval_2">Disapprove</label></td></tr>';
		}
			echo '<tr id="efftr"><td>Effective date of this change</td><td><input type="text" value="'.date('F d, Y', strtotime($row->effectivedate)).'" name="effectivedate" id="effectivedate" class="forminput datepick"/></td></tr>';
			echo '<tr><td>Reason for approve/disapprove/cancellation</td><td><textarea class="forminput" name="reason" id="reason"></textarea></td></tr>';	
			echo '<tr><td><br/></td><td><input type="button" value="Submit" onClick="appdis()" class="btnclass btngreen"/></td></tr>';	
		
		
	}else{
		echo '<tr class="trhead"><td colspan=2>Details</td></tr>';
		echo '<tr><td>Status</td><td class="errortext">';
		if($row->status==2){	
			echo 'Change in status has been cancelled.';
		}else if($row->status==1){
			echo 'This is already approved but changes will reflect on '.date('F d, Y', strtotime($row->effectivedate)).'.';		
		}else if($row->status==3){
			echo 'This is already approved and changes has been reflected.';
		}
		echo '</td></tr>';
		
		echo '<tr><td>Date Requested</td><td>'.date('F d, Y', strtotime($row->datefiled)).'</td></tr>';
		echo '<tr><td>Requested By</td><td>'.$row->prepName.'</td></tr>';
		echo '<tr><td>Effective Date</td><td>'.date('F d, Y', strtotime($row->effectivedate)).'</td></tr>';
		if(!empty($row->updatedby)){
			$xx = explode('|', $row->updatedby);
			echo '<tr><td>Updated By</td><td>'.$xx[0].'</td></tr>';
			echo '<tr><td>Date Updated</td><td>'.date('F d, Y', strtotime($xx[1])).'</td></tr>';
		}
		if(!empty($row->reason)){ echo '<tr><td>Reason of approval/disapproval</td><td>'.$row->reason.'</td></tr>'; }
		
	}
	
?>		
	</table>
<script type="text/javascript">	
	$(function () { 
		$('input[name=approval]').change(function(){
			xx = $('input[name=approval]:checked').val();
			if(xx==1){
				$('#efftr').removeClass('hidden');
			}else{
				$('#efftr').addClass('hidden');
			}
		});

		$('#approval_4').click(function(){
			var previousValue = $(this).attr('previousValue');
		  var name = $(this).attr('name');

		  if (previousValue == 'checked')
		  {
		    $(this).removeAttr('checked');
		    $(this).attr('previousValue', false);
		  }
		  else
		  {
		    $("input[name="+name+"]:radio").attr('previousValue', false);
		    $(this).attr('previousValue', 'checked');
		  }
		});
		
		$('#signedFile').change(function(){
			displaypleasewait();
			$('#signedForm').submit();
		});
	});
	
	function appdis(){
		err = '';
		train = $('input[name=approval]:checked').val();
		if($('#reason').val()==''){
			err += 'Reason is empty.\n';
		}
		if($('#effectivedate').val()==''){
			err += 'Effective date is empty.\n';
		}
		
		if($('#signed').val()==0 && train==1) err += 'Please upload signed document.\n';
		
		if(err!='') alert(err);
		else{
			if(train==1){
				if(confirm('Are you sure you want to APPROVE this change in status for <?= $row->name ?>?')){
					displaypleasewait();
					
					$.post("<?= $this->config->base_url().'updatecis/'.$row->cisID.'/' ?>",{
						submitType:'approve',
						effectivedate:$('#effectivedate').val(),
						reason:$('#reason').val()
					},function(){
						location.reload();
					});
				}
			}else if( train == 2 ){
				if(confirm('Are you sure you want to DISAPPROVE this change in status for <?= $row->name ?>?')){
					displaypleasewait();					
					
					$.post("<?= $this->config->base_url().'updatecis/'.$row->cisID.'/' ?>",{
						submitType:'disapprove',
						effectivedate:$('#effectivedate').val(),
						reason:$('#reason').val()
					},function(){
						location.reload();
					});
				}
			} else if( train == 4 ){
				if(confirm('Are you sure you want to CANCEL this change in status for <?= $row->name ?>?')){
					displaypleasewait();					
					
					$.post("<?= $this->config->base_url().'updatecis/'.$row->cisID.'/' ?>",{
						submitType:'cancel',						
						reason:$('#reason').val()
					},function(){
						location.reload();
					});
				}
			}
		}
		
	}	
</script>
<?php
}else{
	echo 'No record for this employee.';
} ?>
