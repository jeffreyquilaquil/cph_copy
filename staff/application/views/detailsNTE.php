<h2>NTE Details of <?= $row->name ?></h2>
<hr/>
<?php	
	echo '<table class="tableInfo">';
	
	if($row->status==2){
		echo '<tr>
			<td>Status</td>
			<td class="errortext">Cancelled</td>
		</tr>';
	}else if($row->status==3){
		echo '<tr>
			<td>Status</td>
			<td class="errortext">Response accepted</td>
		</tr>';
	}
	
	$sanction = $sanctionArr[$row->offenselevel];
	$nlevel = $row->offenselevel + 1;
	$nextsanction = $sanctionArr[$nlevel];
	
	/* if($row->offenselevel>3) $sanction = 'Termination';
	else $sanction = $sanctionArr[$row->offenselevel];

	$nlevel = $row->offenselevel + 1;
	if($nlevel>3) $nextsanction = 'Termination';
	else $nextsanction = $sanctionArr[$nlevel]; */
	
?>

	<tr>
		<td width="30%">Offense Type</td>
		<td><?= ucfirst($row->type) ?></td>
	</tr>
	<tr>
		<td>Offense Number</td>
		<td><?= $this->textM->ordinal($row->offenselevel).' Offense' ?></td>
	</tr>
	<tr>
		<td>Date NTE Issued</td>
		<td><?= date('F d, Y', strtotime($row->dateissued)) ?></td>
	</tr>
	<tr>
		<td><?= (($row->type=='AWOL') ? 'AWOL': 'Tardiness')?> Dates</td>	
		<td>
			<?php
				$ddates = explode('|', $row->offensedates);
				foreach($ddates AS $d):
					if($row->type=='AWOL')
						echo date('F d, Y', strtotime($d)).'<br/>';
					else
						echo date('F d, Y H:i', strtotime($d)).'<br/>';
				endforeach;
			?>
		</td>
	</tr>
	<tr>
		<td>Prescribed Sanction</td>
		<td><?= $sanctionArr[$row->offenselevel] ?></td>
	</tr>
	<tr>
		<td>Any subsequent case will merit</td>
		<td><?= $nextsanction ?></td>
	</tr>
	<tr>
		<td>NTE Issuer</td>
		<td><?= $row->issuerName ?></td>
	</tr>
	<?php
	if($row->responsedate!='0000-00-00 00:00:00'){
		echo '<tr><td>Date Responded</td><td>'.date('F d, Y', strtotime($row->responsedate)).'</td></tr>';
		echo '<tr><td>Response</td><td>'.((!empty($row->response))?nl2br(stripslashes($row->response)):'None').'</td></tr>';		
	}else if($row->carissuer==0){
		echo '<tr>
				<td>Response</td>
				<td><b>NOT YET RESPONDED</b></td>
			</tr>';
	}
		
	if($row->status==2){
		$cc = explode('|', $row->canceldata);
		if(isset($cc[0])){
			echo '<tr>
					<td>Cancelled by</td>
					<td>'.$this->dbmodel->getSingleField('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$cc[0].'"').'</td>
				</tr>';
		}
		if(isset($cc[1])){
			echo '<tr>
					<td>Cancelled by</td>
					<td>'.date('F d, Y H:i', strtotime($cc[1])).'</td>
				</tr>';
		}
		if(isset($cc[2])){
			echo '<tr>
					<td>Cancel Reason</td>
					<td>'.$cc[2].'</td>
				</tr>';
		}
	}
	
	if(!empty($row->nteuploaded)){
		$exx = explode('|',$row->nteuploaded);
		if(isset($exx[2]) && file_exists(UPLOADS.'NTE/'.$exx[2])){
			echo '<tr>
				<td>NTE File Uploaded</td>
				<td><a class="iframe" href="'.$this->config->base_url().UPLOADS.'NTE/'.$exx[2].'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>
			</tr>';
		}		
	}else{
		echo '<tr>
				<td>NTE Form</td>
				<td><a href="'.$this->config->base_url().'ntepdf/'.$row->nteID.'/I/nform/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>
			</tr>';
	}
	
	echo '<tr id="canceltr" class="hidden">
			<td><b>Why do you want to cancel?</b></td>
			<td><textarea class="forminput" id="cancelv"></textarea></td>
		</tr>';

	$signature = UPLOAD_DIR.$this->user->username.'/signature.png';
	if(!file_exists($signature)){
		echo '<form id="formUpload" action="'.$this->config->base_url().'upsignature/" method="POST" enctype="multipart/form-data">';
		echo '<tr>
			<td width="30%"><span class="errortext">No signature on file.</span><br/>Please upload signature to respond and acknowledge NTE.</td>
			<td> 
				<input type="file" name="fileToUpload" id="fileToUpload"/>
				<input type="hidden" name="page" value="'.$_SERVER['REQUEST_URI'].'"/>
			</td>
		</tr>';
		echo '</form>';
	}else{
		if($this->user->empID==$row->empID_fk && $row->responsedate=='0000-00-00 00:00:00'){
			echo '<form method="POST" action="" onSubmit="return checkacknowledge();">
				<tr><td><b>Response</b></td><td><textarea rows="10" class="forminput" name="response" id="responsev"></textarea></td></tr>
				<tr><td><br/></td><td><input type="hidden" name="submitType" value="aknowledge"/><input type="submit" value="Acknowledge and Submit"/></td></tr>
				</form>';
		}		
	}

	if(($this->user->access!='' || $this->user->empID==$row->issuer ) && $row->status==1 && $row->empID_fk!=$this->user->empID){
		echo '<tr><td colspan=2><button onClick="checkCancel('.$row->nteID.')" class="btnclass">Cancel NTE</button></td></tr>';
	}

	if(
		$row->status==0 || 
		$row->status==3 || 
		($row->status==1 && ($this->access->accessFullHR==true || $row->issuer==$this->user->empID))
	){
?>
	<tr>
		<td colspan=2><h2>Corrective Action Report</h2></td>
	</tr>
<?php if($row->status==1){ ?>
	<tr id="gencartr">
		<td colspan=2>
	<?php 
		if($this->access->accessFullHR==true || $row->issuer==$this->user->empID){
			echo '<button id="generateC" class="btnclass btngreen">Generate CAR</button>';
		}else{
			echo 'CAR not yet generated.';
		}
	?>
		</td>
	</tr>
	<tr id="gencarshowtr" class="hidden"><td colspan=2>
	
	<?php
	$signature = UPLOAD_DIR.$this->user->username.'/signature.png';
	if(!file_exists($signature)){
		echo '<form id="formUpload" action="'.$this->config->base_url().'upsignature/" method="POST" enctype="multipart/form-data">';
		echo '<tr>
			<td width="30%"><span class="errortext">No signature on file.</span><br/>Please upload signature with transparent background first before you issue an NTE.</td>
			<td> 
				<input type="file" name="fileToUpload" id="fileToUpload"/>
				<input type="hidden" name="page" value="'.$_SERVER['REQUEST_URI'].'"/>
			</td>
		</tr>';
		echo '</form>';
	}else{
	?>
	<form action="" method="POST" onSubmit="return validateForm();">
	<input type="hidden" id="nteID" name="nteID" value="<?= $row->nteID ?>"/>
	<input type="hidden" id="sancLevel" value="<?= $row->offenselevel ?>"/>
	<input type="hidden" id="origsanc" value="<?= $sanction ?>"/>
	<input type="hidden" name="sanction" id="sanction" value="<?= $sanction ?>"/>
	<input type="hidden" name="submitType" value="issuecar"/>

	<table class="tableInfo">
	<?php
		if($row->responsedate=='0000-00-00 00:00:00' || empty($row->response)){
			echo '<tr>
					<td  class="formlabel" width="30%">Did the employee respond to NTE?</td>
					<td>
						<select name="respond" id="respond" class="forminput">
							<option value="no" '.(($row->responsedate!='0000-00-00 00:00:00' && empty($row->response))?'selected':'').'>No</option>
							<option value="yes">Yes</option>						
						</select>
					</td>
				</tr>';
			echo '<tr class="trsatisfactory hidden">
					<td class="formlabel">Response</td>
					<td><textarea name="response" id="response" class="forminput" rows=8>'.stripslashes($row->response).'</textarea></td>
				</tr>';
		}
		
		echo '<tr class="trsatisfactory '.(($row->responsedate!='0000-00-00 00:00:00' && empty($row->response))?'hidden':'').'">
				<td class="formlabel" width="30%">Was the response satisfactory?</td>
				<td>
					<select name="satisfactory" id="satisfactory" class="forminput">
						<option value="0" '.(($row->responsedate!='0000-00-00 00:00:00' && empty($row->response))?'selected':'').'>No</option>
						<option value="1">Yes</option>					
					</select>
				</td>
			</tr>';
	?>
		<tr class="psanctiontr">
			<td class="formlabel" width="30%">The prescribed sanction for this offense is <u><b><?= $sanction ?></b></u>. Do you want to proceed with the prescribed sanction?</td>
			<td>
				<select name="psanction" id="psanction" class="forminput">
					<option value="1">YES. Proceed with prescribed sanction.</option>
					<option value="0">NO. Give a lighter sanction.</option>
				</select>		
			</td>
		</tr>
	<?php if($row->offenselevel<3){ ?>
		<tr class="yesproceed">
			<td class="formlabel">Select number of suspension dates</td>
			<td>
			<?php
				if($row->offenselevel==1) $ndates = 4;
				else $ndates = 10;
				for($i=1; $i<=$ndates; $i++){
					echo '<input type="text" class="datepick forminput suspensionDates" name="sdates[]" placeholder="Date '.$i.'"/>';
				}
			?>
			</td>
		</tr>
	<?php }else{ ?>
			<tr class="yesproceed">
				<td class="formlabel">Select effective date of separation. <br/>
				<span style="color:#aaa">(REMEMBER: Effective Date of Separation is the day after the last working day of the employee.)</span></td>
				<td><input type="text" class="datepick forminput" name="endDate" id="endDate" value=""/></td>
			</tr>
			<tr class="yesproceed">
				<td class="formlabel">Please clearly narrate the results of the administrative hearing leading to the decision to terminate</td>
				<td><textarea name="whyterminate" id="whyterminate"></textarea></td>
			</tr>
	<?php } ?>
		<tr class="noproceed">
			<td class="formlabel">In one complete sentence, provide the reason for giving lighter sanction</td>
			<td><textarea name="reasonsanction" id="reasonsanction"></textarea></td>
		</tr>
	<?php
		$numoptions = 4;
		if($sanction=='Termination' || $sanction=='5-10 Days Suspension')
			$numoptions = 10;
	?>	
		<tr class="noproceed">
			<td class="formlabel">Select the sanction do you wish to give to employee</td>
			<td>
				<select id="lightsanc" class="forminput">
					<option value="verbal warning">verbal warning</option>
					<option value="written warning">written warning</option>
					<option value="1 day suspension">1 day suspension</option>
				<?php
					for($k=2; $k<=$numoptions; $k++){
						echo '<option value="'.$k.' days suspension">'.$k.' days suspension</option>';
					}
				?>
				</select>
			</td>
		</tr>
		<tr class="noproceed" id="selectD">
			<td class="formlabel">Select Date/s</td>
			<td>
			<?php
				for($k=1; $k<=$numoptions; $k++){
					echo '<input type="text" id="date'.$k.'" class="datepick forminput" name="sdates[]" placeholder="Date '.$k.'"/>';
				}
			?>
			</td>
		</tr>
		<tr class="planimpclass">
			<td class="formlabel"><span id="plantext">Plan for Improvement</span></td>
			<td><textarea class="forminput" name="planImp" id="planImp"></textarea></td>
		</tr>
		<tr>
			<td colspan=2 align="right">
				<input type="submit" value="Submit" class="padding5px"/>
				<input type="button" value="Cancel" class="padding5px" id="genCancel"/>
			</td>
		</tr>
	</table>
	</form>
	<?php } ?>
	
	
	</td></tr>
	
<?php }else{ 

	if($row->status==3){
		echo '<tr>
				<td>Processed by</td>
				<td>'.$row->carName.'</td>
			</tr>
			<tr>
				<td>Processed date</td>
				<td>'.date('F d, Y', strtotime($row->cardate)).'</td>
			</tr>
			<tr>
				<td>Processed note</td>
				<td>'.$row->planImp.'</td>
			</tr>';
	}else{
?>
	<tr>
		<td>Date CAR Issued</td>
		<td><?= date('F d, Y',strtotime($row->cardate)) ?></td>
	</tr>
	<tr>
		<td>CAR Issuer</td>
		<td><?= $row->carName ?></td>
	</tr>
	<tr>
		<td>Sanction</td>
		<td><b style="color:red;"><?= $row->sanction ?></b></td>
	</tr>
<?php if(!empty($row->reasonsanction)){ ?>
	<tr>
		<td>Reason for giving lighter sanction</td>
		<td><?= $row->reasonsanction ?></td>
	</tr>
<?php }
	if(!empty($row->suspensiondates)){
?>
	<tr>
		<td>Suspension Dates</td>
		<td>
	<?php
		$sD = explode('|', $row->suspensiondates);
		foreach($sD AS $s):
			echo date('F d, Y', strtotime($s)).'<br/>';
		endforeach;
	?>
		</td>
	</tr>
<?php } ?>
	<tr>
		<td>Plan for Improvement</td>
		<td><?= $row->planImp ?></td>
	</tr>
<?php
	}
	
	if(!empty($row->caruploaded)){
		$xc = explode('|',$row->caruploaded);
		if(isset($xc[2]) && file_exists(UPLOADS.'NTE/'.$xc[2])){
			echo '<tr>
				<td>CAR File Uploaded</td>
				<td><a class="iframe" href="'.$this->config->base_url().UPLOADS.'NTE/'.$xc[2].'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>
			</tr>';
		}
		
	}else{
		echo '<tr>
			<td>CAR Form</td>
			<td><a href="'.$this->config->base_url().'ntepdf/'.$row->nteID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></a></td>
		</tr>';
	}
?>

	
<?php } ?>
</table>
<?php }
?>

<script type="text/javascript">
	$(function(){ 
		$('#fileToUpload').change(function(){
			$('#formUpload').submit();
		});	
		
		$('#generateC').click(function(){
			$('#gencarshowtr').removeClass('hidden');
			$('#gencartr').addClass('hidden');
		});
		
		$('#genCancel').click(function(){
			$('#gencarshowtr').addClass('hidden');
			$('#gencartr').removeClass('hidden');
		});
	});	
	
	function checkCancel(id){
		if($('#canceltr').hasClass('hidden'))
			$('#canceltr').removeClass('hidden');
		else{
			if($('#cancelv').val()==''){
				alert('Please input why do you want to cancel.');
			}else{
				displaypleasewait();
				$.post('<?= $this->config->base_url() ?>detailsNTE/'+id+'/',
					{submitType:'cancel',
					cancelv: $('#cancelv').val()
					}, 
					function(){	
						location.reload();
				});
			}
		}
	}
	
	function checkacknowledge(){
		if($('#responsev').val()==''){
			if(confirm('Are you sure you want to submit without response?')){
				displaypleasewait();
				return true;
			}else
				return false;
		}else{
			displaypleasewait();
			return true;
		}
	}
	
	
	$(function(){ 	
		$('.noproceed').addClass('hidden');
		if($('#origsanc').val() == 'Termination'){
			$('.planimpclass').addClass('hidden');
		}
		
		$('#fileToUpload').change(function(){
			$('#formUpload').submit();
		});	
				
	
		$('#respond').change(function(){
			if($(this).val()=='no'){
				$('.trsatisfactory').addClass('hidden');
				$('.psanctiontr').removeClass('hidden');
			}else{
				$('.trsatisfactory').removeClass('hidden');	
				$('.psanctiontr').addClass('hidden');
			}				
		});
		
		$('#satisfactory').change(function(){
			if($(this).val()==1){
				$('.psanctiontr').addClass('hidden');
				$('.yesproceed').addClass('hidden');
				$('.noproceed').addClass('hidden');
				$('#plantext').text('Note');
			}else{
				$('.psanctiontr').removeClass('hidden');
				if($('#psanction').val()==1){
					$('.yesproceed').removeClass('hidden');
					$('.noproceed').addClass('hidden');
				}else{
					$('.yesproceed').addClass('hidden');
					$('.noproceed').removeClass('hidden');
					$('#selectD').addClass('hidden');
				}
				$('#plantext').text('Plan for Improvement');
			}
		});
		 
		$('#psanction').change(function(){
			if($(this).val()=='0'){
				$('.yesproceed').addClass('hidden');
				$('.noproceed').removeClass('hidden');
				$('#selectD').addClass('hidden');
				$('#sanction').val($('#lightsanc').val());
				if($('#origsanc').val() == 'Termination'){
					$('.planimpclass').removeClass('hidden');
				}
			}else{
				$('.noproceed').addClass('hidden');
				$('.yesproceed').removeClass('hidden');	
				$('#sanction').val($('#origsanc').val());
				if($('#origsanc').val() == 'Termination'){
					$('.planimpclass').addClass('hidden');
				}
			}
		});
				
		$('#lightsanc').change(function(){
			sval = $(this).val();
			$('#sanction').val(sval);
				
			if(sval.contains("suspension")){
				$('#selectD').removeClass('hidden');	
				for(i=1; i<=10; i++){
					$('#date'+i).addClass('hidden');
				}
				
				sval = sval.replace(" days suspension", "");
				$('#date1').removeClass('hidden');
				if(sval>1){
					for(s=2; s<=sval; s++){
						$('#date'+s).removeClass('hidden');
					}
				}
			}else{
				$('#selectD').addClass('hidden');		
			}			
		});
		
	});
	
	function validateForm(){ 
		validtxt = '';
		if($('#respond').val()=='yes' && $('#response').val()==''){
			validtxt += 'Response is empty.\n';
		}
		
		if($('#satisfactory').val()==0){ 
			if($('#psanction').val()=='1'){
				slevel = $('#sancLevel').val();
				if(slevel==1 || slevel==2){
					cnt = 0;
					$('.suspensionDates').each(function(){
						if($(this).val() != '')
							cnt++;
					});
					
					if(cnt==0){
						validtxt += 'Please input suspension dates.\n';
					}else{
						if(slevel==1 && cnt<4){
							tt = confirm('Are you sure to submit suspension dates less than 4 days?');
							if(tt==false){
								validtxt += 'Suspension dates less than 4 days.\n';
							}
						}else if(slevel==2 && cnt<5){
							validtxt += 'Suspension dates less than 5 days.\n';
						}else if(slevel==2 && cnt<10){
							tt = confirm('Are you sure to submit suspension dates less than 10 days?');
							if(tt==false){
								validtxt += 'Suspension dates less than 10 days.\n';
							}
						}
					}
				}
			}else{
				if($('#reasonsanction').val()==''){
					validtxt += 'Reason for giving lighter sanction is empty.\n';
				}
				if($('#lightsanc').val()>2){
					lnum = $('#lightsanc').val() - 2;
					for(num=1; num<=lnum; num++){
						if($('#date'+num).val()==''){
							validtxt += 'Date '+num+' is empty.\n';
						}
					}
				}
				
				sval = $('#lightsanc').val();						
				if(sval.contains("suspension")){			
					
					if($('#date1').val()==''){
						validtxt += 'Date 1 is empty.\n';
					}
					
					sval = sval.replace(" days suspension", "");
					for(s=2; s<=sval; s++){
						if($('#date'+s).val()==''){
							validtxt += 'Date '+s+' is empty.\n';
						}
					}
				}
			}
		}
		
		if($('#sanction').val() == 'Termination'){
			if($('#endDate').val()==''){
				validtxt += 'Date of separation is empty.\n';
			}
			if($('#whyterminate').val()==''){
				validtxt += 'Reason for termination is empty.\n';
			}
		}
		
		if($('#sanction').val() != 'Termination' && $('#planImp').val()=='' && $('#satisfactory').val()==0){
			validtxt += 'Plan for improvement is empty.\n';
		}
		
		if($('#satisfactory').val()==1 && $('#planImp').val()==''){
			validtxt += 'Note is empty.\n';
		}
		
		if(validtxt != '') {
			alert(validtxt);
			return false;
		}else{
			displaypleasewait();
			return true;
		}			
	}
</script>