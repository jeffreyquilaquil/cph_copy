<?php
	if($type=='acknowledgment')
		echo '<h2>Coaching Acknowledgment</h2><hr/>';
	else
		echo '<h2>Coaching Evaluation</h2><hr/>';

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
		<td><b><?php
			$dToday = date('Y-m-d');
			if($row->status==0){
				if($row->coachedEval<=$dToday){
					echo 'Evaluation Due<br/>';
					if($row->selfRating!='') echo 'Self Rating Submitted<br/>';
					if($row->supervisorsRating!='') echo 'Supervisor\'s Rating Submitted<br/> For recommendation';
					if($type=='acknowledgment')
						echo 'Click <a href="'.$this->config->base_url().'coachingform/evaluate/'.$row->coachID.'/">here</a> to Evaluate';
				}else{
					echo 'Coaching Period in Progress';
				}
			}else{
				echo $this->staffM->coachingScore($row->finalRating);
			}
		?></b></td>
	</tr>
	<tr>
		<td>Coaching Form</td>
		<td><?= '<a href="'.$this->config->base_url().'coachingform/expectation/'.$row->coachID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a>' ?></td>
	</tr>
<?php if($row->status==1){ ?>	
	<tr>
		<td>Evaluation Form</td>
		<td><?= '<a href="'.$this->config->base_url().'coachingform/evaluation/'.$row->coachID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a>' ?></td>
	</tr>
<?php } ?>
	<tr><td colspan=2><br/></td></tr>	
</table>

<?php //this is for acknowledgment
if($type=='acknowledgment'){ ?>
<table class="tableInfo">
	<tr><td colspan=2 class="trhead">COACHING DETAILS</td></tr>		
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
	
	if($type=='acknowledgment'){
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
	}

	echo '<tr><td colspan=2><br/></td></tr>';
echo '</table>';

}else if($type=='evaluate'){ ?>
<table class="tableInfo">
	<tr><td colspan=4 class="trhead">COACHING DETAILS</td></tr>
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
	$sRating = explode('|', $row->supervisorsRating);
	for($s=0; $s<4; $s++){
		if(isset($allAE[$s])){
			$deta = explode('++||++',$allAE[$s]);
			
			echo '<tr>';
			echo '<td>'.$deta[0].'</td>';
			echo '<td>'.$deta[1].'</td>';
			echo '<td><input type="text" name="empRating[]" value="'.((isset($eRating[$s]))?$eRating[$s]:'').'" class="padding5px tacenter" '.(($this->user->empID==$row->empID_fk && $row->selfRating=='')?'':'disabled="disabled"').'/></td>';
			echo '<td><input type="text" name="supRating[]" value="'.((isset($sRating[$s]))?$sRating[$s]:'').'" class="padding5px tacenter" '.(($this->user->empID==$row->coachedBy && $row->supervisorsRating=='')?'':'disabled="disabled"').'/></td>';
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
	}
	
	if(($this->user->empID==$row->empID_fk && $row->selfRating=='') || ($this->user->empID==$row->coachedBy && $row->supervisorsRating=='')){
		echo '<tr><td colspan=4 align="right">
			<input type="hidden" id="rating"/>
			<input type="hidden" id="subType" value="'.(($this->user->empID==$row->empID_fk)?'selfRating':'supervisorsRating').'"/>
			<input type="button" value="Submit Evaluation" class="btnclass" onClick="submitEval()">
		</td></tr>';
	}
	echo '<tr><td colspan=4><br/></td></tr>';
echo '</table>';

	if($row->selfRating!='' && $row->supervisorsRating!=''){
		echo '<table class="tableInfo">';
		echo '<tr class="trhead"><td colspan=2>EVALUATION SCORES AND RECOMMENDATION</td></tr>';
		echo '<tr style="background-color:#eee;"><td colspan=2>Due to the fact that the immediate supervisor is more objectively aware of the employee’s relative contribution to the organization, the immediate supervisor rating will constitute 80% of the total average score, While the employee’s self-rating is 20%.</td></tr>';
		$fscore = number_format((($eRateAve*0.20) + ($sRateAve*0.80)), 2);
		echo '<tr>
				<td width="30%">Employee’s Total Weighed Average Score</td>
				<td><b>'.$fscore.'</b></td>
			</tr>';
			echo '<tr>
				<td width="30%">Final Rating</td>
				<td><b>'.$this->staffM->coachingScore($fscore).'</b></td>
			</tr>';
			
			$recomArr = $this->txtM->definevar('coachingrecommendations');
		?>
			<tr>
				<td>Recommendation</td>
				<td>
				<?php
					if($row->status==0){
						echo '<select class="forminput" id="changerecommendation">';
						foreach($recomArr AS $k=>$r):
							echo '<option value="'.$k.'">'.$r.'</option>';
						endforeach;
						echo '</select>';
					}else{
						echo $recomArr[$row->recommendation];
					}
				?>					
				</td>
			</tr>
			
			<tr class="trc trdateRegularization <?= ((($row->status==1 && $row->recommendation==0) || $row->status==0)?'':'hidden') ?>">
				<td>Effective Date of Regularization</td>
				<td><?= (($row->status==0)?'<input type="text" id="dateRegularization" class="forminput datepick" />':date('F d, Y', strtotime($row->effectiveDate))) ?></td>
			</tr>
			<tr class="trc trdateSeparation hidden">
				<td>Effective Date of Separation</td>
				<td><?= (($row->status==0)?'<input type="text" id="dateSeparation" class="forminput datepick" />':date('F d, Y', strtotime($row->effectiveDate))) ?></td>
			</tr>			
			<tr class="trc trNextEval hidden">
				<td>Next Evaluation Period</td>
				<td><input type="text" id="dateNextEval" class="forminput datepick" /></td>
			</tr>
			
			<tr class="trc trJustification hidden">
				<td>Justification</td>
				<td><input type="text" id="justification" class="forminput" /></td>
			</tr>
			<tr class="trc trJustification hidden" style="background-color:#eee;">
				<td colspan=2><b>Transfer employee to</b></td>
			</tr>
			<tr class="trc trJustification hidden">
				<td>Position Title</td>
				<td><select id="postitle" class="forminput">
				<?php
					$posTitle = $this->staffM->getQueryResults('newPositions', 'posID, title', 'active=1');
					foreach($posTitle AS $t):
						echo '<option value="'.$t->posID.'">'.$t->title.'</option>';
					endforeach;
				?>
				</select></td>
			</tr>
			
			<tr class="trc trJustification hidden">
				<td>Immediate Supervisor</td>
				<td><select id="imsupervisor" class="forminput">
				<?php
					$imsupervisor = $this->staffM->getQueryResults('staffs', 'empID, CONCAT(fname," ",lname) AS name', 'active=1 AND levelID_fk>0', '', 'lname ASC');
					foreach($imsupervisor AS $i):
						echo '<option value="'.$i->empID.'">'.$i->name.'</option>';
					endforeach;
				?>
				</select></td>
			</tr>
			<tr class="trc trJustification hidden">
				<td>Effective Date of Transfer</td>
				<td><input type="text" id="dateTransfer" class="forminput datepick" /></td>
			</tr>
			
			<tr class="trc trother hidden">
				<td>Specify recommendation</td>
				<td><input type="text" id="other" class="forminput" /></td>
			</tr>
			
		<?php if($row->status==0){
			echo '<tr><td><br/></td><td><input type="button" value="Submit Scores and Recommendation" class="btnclass" id="submitRecommendation"/></td></tr>';
		}else{
			echo '<tr><td>Evaluated By</td><td>'.$this->staffM->getSingleField('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$row->evaluatedBy.'"').'</td></tr>';
			echo '<tr><td>Date Evaluated</td><td>'.date('F d, Y', strtotime($row->dateEvaluated)).'</td></tr>';
		}?>
		
			<tr><td colspan=2><br/></td></tr>
		</table>
<?php
	}

} //end of type evaluate
?>


<?php if($type=='acknowledgment'){ ?>
<table class="tableInfo">
	<tr><td class="trhead" colspan=2>ACKNOWLEDGMENTS<br/><i style="color:#555;" class="weightnormal">(Tick checkbox to acknowledge)<i/></td></tr>
<?php
	if($this->user->empID==$row->empID_fk || $this->user->empID==$row->supervisor || $this->user->empID==$row->coachedBy || (isset($row->supSupervisor) && $this->user->empID==$row->supSupervisor)){
		$signature = UPLOAD_DIR.$this->user->username.'/signature.png';
		if(!file_exists($signature)){
			echo '<form id="formUpload" action="'.$this->config->base_url().'upsignature/" method="POST" enctype="multipart/form-data">';
			echo '<tr>
				<td class="errortext" width="40%">No signature on file.<br/>Please upload signature with transparent background first before acknowledging.</td>
				<td> 
					<input type="file" name="fileToUpload" id="fileToUpload"/>
					<input type="hidden" name="page" value="'.$_SERVER['REQUEST_URI'].'"/>
				</td>
			</tr>';
			echo '</form>';
		}
	}
?>
	<tr>
		<td width="25%">Immediate Supervisor's Acknowledgment</td>
		<td>
		<?php
			if($row->dateSupAcknowledged=='0000-00-00'){
				if($this->user->empID==$row->supervisor || $this->user->empID==$row->coachedBy){
					echo '<input onClick="acknowledge(\'dateSupAcknowledged\')" id="dateSupAcknowledged" type="checkbox" '.((file_exists($signature))?'':'disabled="disabled"').'/> <span style="color:red;">I have discussed and thoroughly explained the Coaching Form to the employee.</span>';
				}else{
					echo '<i>Pending Acknowledgment</i>';
				}
			}else{
				echo '<b>'.$row->supName.':</b> I have discussed and thoroughly explained the Coaching Form to the employee.<br/>
					<i>Signed: '.date('F d, Y', strtotime($row->dateSupAcknowledged	)).'</i>';
			}
		?>
		</td>
	</tr>
	<tr>
		<td>Second Level Manager's Acknowledgment</td>
		<td>
		<?php
			if($row->date2ndMacknowledged=='0000-00-00'){
				if(isset($row->supSupervisor) && $this->user->empID==$row->supSupervisor){
					echo '<input onClick="acknowledge(\'date2ndMacknowledged\')" id="date2ndMacknowledged" type="checkbox" '.((file_exists($signature))?'':'disabled="disabled"').'/> <span style="color:red;">I have reviewed and approved the Coaching Form.</span>';
				}else{
					echo '<i>Pending Acknowledgment</i>';
				}
			}else{
				echo '<b>'.$row->sup2ndName.':</b> I have reviewed and approved the Coaching Form.<br/>
					<i>Signed: '.date('F d, Y', strtotime($row->date2ndMacknowledged )).'</i>';
			}
		?>
		</td>
	</tr>
	<tr>
		<td>Employee's Acknowledgment Receipt of COACHING</td>
		<td>
		<?php
			if($row->dateEmpAcknowledge=='0000-00-00'){
				if($this->user->empID==$row->empID_fk){
					echo '<input onClick="acknowledge(\'dateEmpAcknowledge\')" id="dateEmpAcknowledge" type="checkbox" '.((file_exists($signature))?'':'disabled="disabled"').'/> <span style="color:red;">I acknowledge that the expectations listed above have been thoroughly discussed to me and that I commit to working with my immediate supervisor to meet the expectations set forth in this document. I understand that my failure to meet the agreed goals/changes/improvements listed above can constitute to poor performance and may lead to further disciplinary measures up to and including termination of employment.</span>';
				}else{
					echo '<i>Pending Acknowledgment</i>';
				}
			}else{
				echo '<b>'.$row->name.':</b> I acknowledged that the expectations listed above have been thoroughly discussed to me and that I commit to working with my immediate supervisor to meet the expectations set forth in this document. I understand that my failure to meet the agreed goals/changes/improvements listed above can constitute to poor performance and may lead to further disciplinary measures up to and including termination of employment.<br/>
					<i>Signed: '.date('F d, Y', strtotime($row->dateEmpAcknowledge)).'</i>';
			}
		?>
		</td>
	</tr>
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
			}else if(recommendation==4 || recommendation==5){
				if($('#dateNextEval').val()=='') valid = 1;
				else cform['effectiveDate'] = $('#dateNextEval').val();
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
				else cform['other'] = $('#other').val();
			}
			
			if(valid==1){
				alert('All fields are required.');
			}else if(valid==2){
				alert('Date of transfer must not be less than 2 weeks.');
			}else{
				displaypleasewait();
				cform['submitType'] = 'recommendations';
				$.post('<?= $this->config->item('career_uri') ?>',cform, 
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