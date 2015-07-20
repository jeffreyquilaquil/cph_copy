<h2>Coaching Details</h2><hr/>

<?php 
if(count($row)==0){
	echo 'No coaching record.';
}else{
?>
	<table class="tableInfo">
		<tr><td colspan=2 class="trhead">EMPLOYEE DETAILS</td></tr>
		<tr>
			<td width="30%">Employee Name</td>
			<td><?= $row->name ?></td>
		</tr>
		<tr>
			<td>Position Title</td>
			<td><?= $row->title ?></td>
		</tr>
		<tr>
			<td>Department</td>
			<td><?= $row->dept ?></td>
		</tr>
		<tr>
			<td>Reviewer</td>
			<td><?= $row->reviewer ?></td>
		</tr>
	<?php if(isset($row->supName)){ ?>
		<tr>
			<td>Immediate Supervisor</td>
			<td><?= $row->supName ?></td>
		</tr>
		<tr>
			<td>Immediate Supervisor Position Title</td>
			<td><?= $row->supTitle ?></td>
		</tr>
	<?php } if(isset($row->sup2ndName)){?>
		<tr>
			<td>2nd Level Supervisor</td>
			<td><?= $row->sup2ndName ?></td>
		</tr>
		<tr>
			<td>2nd Level Supervisor Position Title</td>
			<td><?= $row->sup2ndTitle ?></td>
		</tr>
	<?php } ?>
		<tr>
			<td>Date of Coaching</td>
			<td><?= date('F d, Y', strtotime($row->coachedDate)) ?></td>
		</tr>
		<tr>
			<td>Evaluation Date</td>
			<td><?= date('F d, Y', strtotime($row->coachedEval)) ?></td>
		</tr>
		<tr>
			<td>Area of Improvement Required</td>
			<td><?= $row->coachedImprovement ?></td>
		</tr>
		<tr>
			<td>Status</td>
			<td><b><?= $this->staffM->coachingStatus($row->coachID); ?></b></td>
		</tr>
	<?php 
		if($row->status==4){
			echo '<tr>';
			echo '<td>Cancel Reason</td>';
			echo '<td>'.$row->canceldata.'</td>';
			echo '</tr>';
		}else if($row->HRoptionStatus<2){ ?>
		<tr>
			<td>Coaching Form</td>
			<td><?= '<a href="'.$this->config->base_url().'coachingform/expectation/'.$row->coachID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a>' ?></td>
		</tr>
		<?php if($row->status==0 && $row->HRoptionStatus<2 && ($this->user->empID==$row->generatedBy || $this->access->accessFullHR==true)){ ?>
			<tr id="coachCancelBtn">
				<td><br/></td>
				<td><input type="button" value="Cancel this Coaching" class="btnclass" onClick="$('#coachCancelBtn').addClass('hidden'); $('#coachCancelWhy').removeClass('hidden'); "/></td>
			</tr>
			<tr id="coachCancelWhy" class="hidden">
				<td>Why do you want to cancel this coaching?</td>
				<td><textarea id="textCancel" class="inputform"></textarea><br/>
				<input id="cancelBtn" type="button" class="btnclass" value="Cancel"></td>
			</tr>
		<?php
			}
		}
		$fileloc = UPLOADS.'coaching/coachingform_'.$row->coachID.'.pdf';
		if($row->HRoptionStatus>=2 && file_exists($fileloc)){
			echo '<tr>';
			echo '<td>Signed Coaching Form</td>';
			echo '<td><a href="'.$this->config->base_url().$fileloc.'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a></td>';
			echo '</tr>';
		}
	?>
	<?php if($row->status==1){ ?>	
		<tr>
			<td>Evaluation Form</td>
			<td><?= '<a href="'.$this->config->base_url().'coachingform/evaluation/'.$row->coachID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a>' ?></td>
		</tr>
	<?php } ?>
	<?php
		$fileloc = UPLOADS.'coaching/coachingevaluation_'.$row->coachID.'.pdf';
		if($row->HRoptionStatus>=2 && file_exists($fileloc)){
			echo '<tr>';
			echo '<td>Signed Evaluation Form</td>';
			echo '<td><a href="'.$this->config->base_url().$fileloc.'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a></td>';
			echo '</tr>';
		}
	?>
		<tr><td colspan=2><br/></td></tr>	
	</table>

	<?php //this is for acknowledgment
	if($type=='acknowledgment' || $row->status==1){ ?>
		<table class="tableInfo">
			<tr><td colspan=2 class="trhead">COACHING DETAILS - Expectations</td></tr>		
			</tr>
			<tr style="background-color:#eee;">
				<td align="center" width="50%" >
					<b>Areas for Improvement</b><br/>
					<i style="color:#555;" class="fs11px">Job performance factors that the employee needs to be coached on or behaviour or conduct that needs to be corrected.</i>
				</td>
				<td align="center" width="50%">
					<b>Expected Outcome (GOALS)</b><br/>
					<i style="color:#555;" class="fs11px">Specific results expected from employee after the coaching period</i>
				</td>
			</tr>
		<?php
			$allAE = explode('--^_^--',$row->coachedAspectExpected);	
			for($s=0; $s<4; $s++){
				if(isset($allAE[$s])){
					$deta = explode('++||++',$allAE[$s]);
					
					echo '<tr>';
					echo '<td>'.$deta[0].'</td>';
					echo '<td>'.$deta[1].'</td>';
					echo '</tr>';
				}
			}
			
				echo '<tr><td colspan=2><br/></td></tr>';
				$support = explode('--^_^--',$row->coachedSupport);
				echo '<tr><td rowspan='.(count($support)+1).' align="center" style="background-color:#eee;"><b>Support from the Immediate Supervisor</b><br/><i style="color:#555;" class="fs11px">Immediate supervisor\'s action to support the employee in achieving the goals listed above.</i></td></tr>';
				
				for($p=0; $p<4; $p++){
					if(isset($support[$p])){
						echo '<tr>';
						echo '<td>'.$support[$p].'</td>';
						echo '</tr>';
					}
				}

			echo '<tr><td colspan=2><br/></td></tr>';
		echo '</table>';

	} //END OF ACKNOWLEDGMENTS details
	
	
	///// for EVALUATION

	//if($row->status!=0 && $row->status!=4){ 
	if(!empty($row->selfRating) || !empty($row->supervisorsRating)){ ?>
	<table class="tableInfo">
		<tr><td colspan=4 class="trhead">COACHING DETAILS - Evaluation</td></tr>
	<?php
		if($row->status==0 && $row->coachedEval>date('Y-m-d')){
			echo '<tr><td colspan2 class="errortext">Coaching period is still in progress</td></tr>';	
		}else if($row->status==0 && $row->coachedEval<=date('Y-m-d') && 
		(($this->user->empID==$row->supervisor && $row->dateSupAcknowledged=='0000-00-00') ||
			($this->user->empID==$row->empID_fk && $row->dateEmpAcknowledge=='0000-00-00')
		)
		){
			echo '<tr><td colspan2 class="errortext">Please acknowledge before evaluating.</td></tr>';
		}else{
	?>
		<tr style="background-color:#ddd;"><td colspan=4 class="trhead"><b>Instructions:</b> Both employee and immediate supervisor will rate employee’s performance on achieving the target job performance or behaviours that were identified during the coaching period. Immediate supervisor shall send this file first to employee for self-rating, and employee shall forward his filled up form to immediate supervisor for supervisor’s rating. Use the rating system below to rate your performance. Rate one (1) if the employee totally failed to meet the expected job performance/behaviour and 10 if the employee surpassed expectations. Rate 5 if the performance is just meeting expectation.</td></tr>
		<tr>
		<tr>
			<td colspan=4 align="center">
				<b>1</b> ------------------------------------------------------------------ <b>5</b> ------------------------------------------------------------------ <b>10</b><Br/>
				<i>Failed Expectations&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Meet expectations (minimum acceptable performance)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				Very Much Exceeded Expectations</i>
			</td>
		</tr>
		</tr>
		<tr style="background-color:#eee;">
			<td align="center" width="40%" >
				<b>Areas for Improvement</b><br/>
				<i style="color:#555;" class="fs11px">Job performance factors that the employee needs to be coached on or behaviour or conduct that needs to be corrected.</i>
			</td>
			<td align="center" width="40%">
				<b>Expected Outcome (GOALS)</b><br/>
				<i style="color:#555;" class="fs11px">Specific results expected from employee after the coaching period</i>
			</td>
			<td align="center" width="10%">
				<b>Employee’s Self - Rating</b><br/>
				<i style="color:#555;" class="fs11px">Employee rates his/her performance on this expected outcome</i>
			</td>
			<td align="center" width="10%">
				<b>Supervisor’s Rating</b><br/>
				<i style="color:#555;" class="fs11px">IS’s  rates employee’s performance on this expected outcome</i>
			</td>
		</tr>
	<?php	
		$allAE = explode('--^_^--',$row->coachedAspectExpected);
		$eRating = explode('|', $row->selfRating);
		$eRatingNote = explode('+++', $row->selfRatingNotes);
		$sRating = explode('|', $row->supervisorsRating);
		$sRatingNote = explode('+++', $row->supervisorsRatingNotes);
		for($s=0; $s<4; $s++){
			if(isset($allAE[$s])){
				$deta = explode('++||++',$allAE[$s]);
				
				echo '<tr>';
				echo '<td>'.$deta[0].'</td>';
				echo '<td>'.$deta[1].'</td>';
				echo '<td valign="top"><input type="text" name="empRating[]" value="'.((isset($eRating[$s]))?$eRating[$s]:'').'" class="padding5px tacenter" '.(($this->user->empID==$row->empID_fk && $row->selfRating=='')?'':'disabled="disabled"').'/><br/>'.((isset($eRatingNote[$s]) && !empty($eRatingNote[$s]))?'<i class="fs11px">NOTE: '.nl2br($eRatingNote[$s]).'</i>':'').'</td>';
				echo '<td valign="top"><input type="text" name="supRating[]" value="'.((isset($sRating[$s]))?$sRating[$s]:'').'" class="padding5px tacenter" '.(($this->user->empID==$row->supervisor && $row->supervisorsRating=='')?'':'disabled="disabled"').'/><br/>'.((isset($sRatingNote[$s]) && !empty($sRatingNote[$s])?'<i class="fs11px">NOTE: '.nl2br($sRatingNote[$s]).'</i>':'').'</td>';
				echo '</tr>';
			}
		}
		
		$erate = 0;
		$srate = 0;
		foreach($eRating AS $e):
			$erate += $e;
		endforeach;	
		
		foreach($sRating AS $s):
			$srate += $s;
		endforeach;
		
		$eRateAve = $erate/count($allAE);
		$sRateAve = $srate/count($allAE);
		
		if($row->selfRating!='' || $row->supervisorsRating!=''){
			echo '<tr class="weightbold">';
			echo '<td colspan=2 align=right>AVERAGES</td>';
			
			echo '<td class="tacenter">'.number_format($eRateAve, 2).'</td>';
			echo '<td class="tacenter">'.number_format($sRateAve, 2).'</td>';
			echo '</tr>';
		}
		
		if($row->selfRating!='' && $row->supervisorsRating!=''){
			echo '<tr class="weightbold">';
			echo '<td colspan=2 align=right>Weighed Average</td>';
			echo '<td class="tacenter">'.number_format(($eRateAve*0.20), 2).'</td>';
			echo '<td class="tacenter">'.number_format(($sRateAve*0.80), 2).'</td>';
			echo '</tr>';
			echo '<tr class="weightbold">';
			echo '<td colspan=2 align=right>TOTAL WEIGHED SCORE</td>';
			echo '<td class="tacenter" colspan=2>'.((!empty($row->finalRating))?$row->finalRating:'<i class="errortext">Pending</i>').'</td>';
			echo '</tr>';
		}
		
		if(($this->user->empID==$row->empID_fk && $row->selfRating=='') || ($this->user->empID==$row->supervisor && $row->supervisorsRating=='')){
			echo '<tr><td colspan=4 align="right">
				<input type="hidden" id="rating"/>
				<input type="hidden" id="subType" value="'.(($this->user->empID==$row->empID_fk)?'selfRating':'supervisorsRating').'"/>
				<input type="button" value="Submit Evaluation" class="btnclass" onClick="submitEval()">
			</td></tr>';
		}
	} //end of acknowledge first before evaluating
		echo '<tr><td colspan=4><br/></td></tr>';
	echo '</table>';


	} //end of type evaluate
	
	if($type=='hroptions'){ 
		$opt = array();
		if($row->HRstatusData!=''){
			$options = explode('-^_^-', $row->HRstatusData);
			
			$xcount = count($options)-1;
			for($i=0; $i<$xcount; $i++){
				$xx = explode('|', $options[$i]);
				$opt[$xx[0]] = $xx[1].'|'.$xx[2];
			}
		}
	?>
		<table class="tableInfo">
			<tr><td class="trhead" colspan=2>HR OPTIONS</td><tr>
			<tr>
				<td width="30%">Coaching form printed</td>				
				<td>
				<?php
					if(isset($opt[1])){ echo $opt[1]; }
					else if($row->status==0) echo '<input type="checkbox" id="cfprinted"/> Tick checkbox if done printing form';
				?>
				</td>				
			</tr>
		<?php if($row->HRoptionStatus>=1){ ?>
			<tr>
				<td>Upload signed coaching form</td>	
				<td>
				<?php
					if(isset($opt[2])){ echo $opt[2]; }
					else if($row->status==0) echo '<form action="" method="POST" id="uploadCFForm" enctype="multipart/form-data"><input type="file" name="cffile" id="uploadCF" value="Upload Signed Form"/><input type="hidden" name="submitType" value="uploadCF"/></form>';
				?>
				</td>	
			</tr>
		<?php } if($row->HRoptionStatus>=2){ ?>
			<tr>
				<td>Evaluation form printed</td>
				<td>
				<?php
					if(isset($opt[3])){ echo $opt[3]; }
					else echo '<input type="checkbox" id="efprinted"/> Tick checkbox if done printing form';
				?>
				</td>					
			</tr>
		<?php } if($row->HRoptionStatus>=3){ ?>
			<tr>
				<td>Upload signed evaluation form</td>
				<td>
				<?php
					if(isset($opt[4])){ echo $opt[4]; }
					else echo '<form action="" method="POST" id="uploadEFForm" enctype="multipart/form-data"><input type="file" name="cffile" id="uploadEF" value="Upload Signed Form"/><input type="hidden" name="submitType" value="uploadEF"/></form>';
				?>
				</td>	
			</tr>
		<?php } ?>
		</table>
<?php	
	}


} //end of no coaching record ?>

<script type="text/javascript">
	$(function(){
		$('#fileToUpload').change(function(){
			displaypleasewait();
			$('#formUpload').submit();
		});	

		$('#changerecommendation').change(function(){
			va = $(this).val();
			$('.trc').addClass('hidden');
			if(va==0){
				$('.trdateRegularization').removeClass('hidden');
			}else if(va==1){
				$('.trdateSeparation').removeClass('hidden');
			}else if(va==4 || va==5){
				$('.trNextEval').removeClass('hidden');
			}else if(va==6){
				$('.trJustification').removeClass('hidden');
			}else if(va==7){
				$('.trother').removeClass('hidden');
			}
		});

		$('#submitRecommendation').click(function(){
			valid = 0;
			recommendation = $('#changerecommendation').val();
			var cform = {};
			
			cform['finalRating'] = '<?= ((isset($fscore))?$fscore:'') ?>';
			cform['recommendation'] = recommendation;
			if(recommendation==0){
				if($('#dateRegularization').val()=='') valid = 1;
				else cform['effectiveDate'] = $('#dateRegularization').val();
			}else if(recommendation==1){
				if($('#dateSeparation').val()=='') valid = 1;
				else cform['effectiveDate'] = $('#dateSeparation').val();
			}else if(recommendation==4){
				if($('#dateNextEval').val()=='') valid = 1;
				else cform['effectiveDate'] = $('#dateNextEval').val();
			}else if(recommendation==5){
				if($('#dateNextEval').val()=='') valid = 1;
				else{
					var d1 = new Date($('#dateNextEval').val());
					var d2 = new Date('<?= date('F d, Y', strtotime($row->startDate.' +6 months')) ?>');
					if(d1 > d2){
						valid = 3;
					}else{
						cform['effectiveDate'] = $('#dateNextEval').val();
					}				
				}				
			}else if(recommendation==6){			
				if($('#justification').val()=='' || $('#dateTransfer').val()==''){
					valid = 1;
				}else{
					var d1 = new Date($('#dateTransfer').val());
					var d2 = new Date('<?= date('F d, Y', strtotime('+2 weeks')) ?>');
					if(d1 < d2){
						valid = 2;
					}
										
					cform['transferData'] = $('#postitle').val()+'|'+$('#imsupervisor').val()+'|'+$('#justification').val();
					cform['effectiveDate'] = $('#dateTransfer').val();
				}
			}else if(recommendation==7){
				if($('#other').val()=='') valid = 1;
				else cform['otherRemarks'] = $('#other').val();
			}
			
			if(valid==1){
				alert('All fields are required.');
			}else if(valid==2){
				alert('Date of transfer must not be less than 2 weeks.');
			}else if(valid==3){
				alert('Evaluation date must not be beyond 6 months from date of hire.');
			}else{
				displaypleasewait();
				cform['submitType'] = 'recommendations';
				$.post('<?= $this->config->item('career_uri') ?>',cform, 
				function(){ 
					if(recommendation==4){
						window.location.href="<?= $this->config->base_url().'generatecoaching/'.$row->empID_fk.'/' ?>"+encodeURIComponent($('#dateNextEval').val())+"/";
					}else{				
						location.reload();
					}
				});
			}
			
		});
	
		$('#cfprinted').click(function(){
			if($(this).is(':checked')){
				c = confirm('Are you sure you printed the coaching form?');
				if(c){
					displaypleasewait();
					$.post('<?= $this->config->item('career_uri') ?>', {submitType:'hroption', status:'1'},
					function(){
						location.reload();
					});
				}else{
					$(this).prop('checked', false); 
				}
				
			}
		});
		
		$('#uploadCF').change(function(){
			displaypleasewait();
			$('#uploadCFForm').submit();
		});
		
		$('#efprinted').click(function(){
			if($(this).is(':checked')){
				c = confirm('Are you sure you printed the evaluation form?');
				if(c){
					displaypleasewait();
					$.post('<?= $this->config->item('career_uri') ?>', {submitType:'hroptionevaluation', status:'3'},
					function(){
						location.reload();
					});
				}else{
					$(this).prop('checked', false); 
				}
				
			}
		});
		
		$('#uploadEF').change(function(){
			displaypleasewait();
			$('#uploadEFForm').submit();
		});
		
		$('#cancelBtn').click(function(){
			if($('#textCancel').val()=='') alert('Cancel reason is empty');
			else{
				displaypleasewait();
				$.post('<?= $this->config->item('career_uri') ?>', {submitType:'coachingCancel', reason:$('#textCancel').val()},
				function(){
					location.reload();
				});
			}
		});
	});
	
	function acknowledge(type){
		if($('#'+type).is(":checked")){
			displaypleasewait();
			$.post('<?= $this->config->item('career_uri') ?>',{submitType:'acknowledged', fld:type}, 
			function(){
				location.reload();
			});
		}
	}
	
	function submitEval(){
		$('#rating').val('');
		valid = true;
		$('input[name="<?= (($this->user->empID==$row->empID_fk)?'empRating':'supRating') ?>[]"]').each(function() {
			pag = $(this).val();
			if(!$.isNumeric(pag) || pag<1 || pag>10){
				valid = false;
			}else{
				$('#rating').val($('#rating').val()+pag+'|');
			}
		});
		if(valid==false){
			alert('All fields are required and should be a number from 1-10.');
		}else{
			displaypleasewait();
			$.post('<?= $this->config->item('career_uri') ?>',{submitType:'rating', fld:$('#subType').val(), rating:$('#rating').val()}, 
			function(){
				location.reload();
			});
		}
		
	}
</script>