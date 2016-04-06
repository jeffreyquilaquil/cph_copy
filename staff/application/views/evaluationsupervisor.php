<h3><span id="h3pre"></span><?= $row->lname.', '.$row->fname.' <span class="errortext">('.ucfirst($row->empStatus).')</span>' ?></h3>
<hr/>

<div id="divFirst" class="">
	<?php if(isset($submitted)){ ?>
		<p>The probationary performance evaluation for <?= $row->lname.', '.$row->fname ?> has been forwarded to HR for review and printing. You will receive a notification once the document is printed and ready for claiming and issuance to employee.</p>
	<?php }else{ ?>

	<?php if($row->perStatus!=100 || count($evalData)==0 || $evalData->status==0){ ?>
	<p>The employment status of <?= $row->lname.', '.$row->fname ?> cannot be changed because one of the requirements above is not completed. Please complete the requirements below to change the employment status for employee:</p>
	<?php } ?>
	
	<table class="tableInfo">
		<tr class="trhead"><td width="50%">Requirement</td><td>Status</td></tr>
		<tr><td>Pre Employment Requirements Completed.</td><td><?= $row->perStatus.'%' ?></td></tr>
		<tr><td>Employee Self-Evaluations</td><td><?= ((count($evalData)==0)?'Pending':'Completed') ?></td></tr>
		<tr><td>90th day performance evaluation</td>
			<td>
			<?php
				if(count($evalData)==0) echo 'Pending Employee Evaluation';
				else if($evalData->status==1) echo 'Completed';
				else echo '<a href="javascript:void(0)" onClick="showEvaluation();">Click to perform 90th day Evaluation</a>';
			?>				
			</td>
		</tr>
	<?php if(count($evalData)>0 && $evalData->status==1){ ?>	
			<tr class="trhead"><td colspan=2>Evaluation Info</td></tr>
			<tr><td>Recommendation</td><td><?= $evalData->recommendation ?></td></tr>
	<?php 
			if($evalData->nextReviewDate!='0000-00-00') echo '<tr><td>Next Review Date</td><td>'.date('F d, Y', strtotime($evalData->nextReviewDate)).'</td></tr>';
			if($evalData->effectiveDate!='0000-00-00') echo '<tr><td>Effective Date of Recommendation</td><td>'.date('F d, Y', strtotime($evalData->effectiveDate)).'</td></tr>'; 
	?>
			<tr><td>PDF File</td><td><a href="<?= $this->config->base_url().'evaluationpdf/'.$evalData->evalID.'/' ?>" class="iframe"><img src="<?= $this->config->base_url().'css/images/pdf-icon.png' ?>"/></a></td></tr>		
	<?php } ?>
			
	</table>
	<?php } ?>
</div>

<form id="formSupEvaluation" action="" method="POST" onSubmit="return validateSubmit();">
<div id="divSecond" class="hidden">
	<p>You are completing the performance evaluation for the employee below. Please check the details below:</p>
	<table width="70%" border=1 cellpadding=5 cellspacing=0>
		<tr>
			<td width="40%"><b>Employee Name</b></td>
			<td><?= $row->lname.', '.$row->fname ?></td>
		</tr>
		<tr>
			<td><b>Position Title</b></td>
			<td><?= $row->title ?></td>
		</tr>
		<tr>
			<td><b>Department</b></td>
			<td><?= $row->dept ?></td>
		</tr>
		<?php if(isset($firstsup)){ ?>
		<tr>
			<td><b>Immediate Supervisor (IS)</b></td>
			<td><?= $firstsup->name ?></td>
		</tr>
		<tr>
			<td><b>IS Position Title</b></td>
			<td><?= $firstsup->title ?></td>
		</tr>
		<?php } if(isset($secondsup)){ ?>
		<tr>
			<td><b>2nd Level Immediate Supervisor (IS)</b></td>
			<td><?= $secondsup->name ?></td>
		</tr>
		<tr>
			<td><b>2nd Level IS Position Title</b></td>
			<td><?= $secondsup->title ?></td>
		</tr>
		<?php } ?>
	</table>
	<br/>
	<table width="70%" border=1 cellpadding=5 cellspacing=0>
		<tr>
			<td><b>Review Period</b></td>
			<td><?= $this->textM->convertTimeToYMD(strtotime($row->startDate.' +90 days') - strtotime($row->startDate)) ?></td>
		</tr>
		<tr>
			<td width="40%"><b>From</b></td>
			<td><?= date('F d, Y', strtotime($row->startDate)) ?></td>
		</tr>
		<tr>
			<td><b>To</b></td>
			<td><?= date('F d, Y', strtotime($row->startDate.' +90 days')) ?></td>
		</tr>
	</table>
	<br/><br/>
	<b>Is the Immediate Supervisor the one doing the performance review?</b><br/>
	<?= $this->textM->formfield('button', '', 'Yes', 'btnclass weightbold', '', 'style="color:green" id="btnYesSupervisor"'); ?>
	<?= $this->textM->formfield('button', '', 'No', 'btnclass weightbold', '', 'style="color:red" id="btnNoSupervisor"'); ?>
	
	<div id="yesSupervisor" class="whoEval hidden"><br/>
		<b>When are you discussing this performance evaluation to the employee (Review Date)?</b><br/>
		<table width="70%" border=1 cellpadding=5 cellspacing=0>
			<tr>
				<td width="40%"><b>Review Date</b></td>
				<td><?= $this->textM->formfield('text', 'reviewDate', date('F d, Y', strtotime($row->startDate.' +90 days')), 'forminput reviewDate'); ?></td>
			</tr>
		</table>
	</div>
	
	<div id="noSupervisor" class="whoEval hidden"><br/>
		<b>Who is doing the performance evaluation for this probationary employee?</b><br/>
		<?php
			$selectSup = '<option value=""></option>';
			foreach($querySupervisor AS $qs){
				$selectSup.= '<option value="'.$qs->empID.'" '.(($qs->empID==$this->user->empID)?'selected="selected"':'').'>'.$qs->name.'</option>';
			}
		?>
		<?= $this->textM->formfield('select', 'reviewerEmpID', $selectSup, 'forminput'); ?>
		<br/><br/>
		<b>Why is the IS not doing the 90th day performance evaluation?</b><br/>
		<?= $this->textM->formfield('textarea', 'reasonNotIS', '', 'forminput'); ?>
	</div>
	<p align="right">
		<?= $this->textM->formfield('hidden', '', '', '', '', 'id="isSupVal"'); ?>
		<?= $this->textM->formfield('button', '', 'Next: Performance Evaluation', 'btnclass hidden','','id="btnNextPE"'); ?>
	</p>
</div>

<div id="divThird" class="hidden">
<?php	
	$arrPerf = array('dedicationToExcellence', 'proactiveness', 'teamwork','communication', 'reliability', 'professionalism','flexibility');
	
	foreach($evalData AS $k=>$r){
		if(in_array($k, $arrPerf)){
			$evalData->$k = explode(',', $r);
		}
	}
?>
	<p id="giveRatingText">Give a rating for the following performance indicators:</p>			
	<div id="divEval1" class="divSelfEval">
		<?= $this->textM->evaluationTable(1, 1, $evalData) ?>
		<br/><p align="right"><?= $this->textM->formfield('button', '', 'Next: Evaluation Continuation', 'btnclass','','onClick="checkValues(1)" style="text-align:right;"'); ?></p>
	</div>
	<div id="divEval2" class="divSelfEval hidden">
		<?= $this->textM->evaluationTable(1, 2, $evalData) ?>
		<br/>
		<?= $this->textM->formfield('button', '', 'Back: Evaluation Part 1', 'btnclass','','onClick="backEval(1)"'); ?>
		<?= $this->textM->formfield('button', '', 'Next: Evaluation Continuation', 'btnclass floatright','','onClick="checkValues(2)"'); ?>
	</div>
	<div id="divEval3" class="divSelfEval hidden">
		<?= $this->textM->evaluationTable(1, 3, $evalData) ?>
		<br/>
		<?= $this->textM->formfield('button', '', 'Back: Evaluation Part 2', 'btnclass','','onClick="backEval(2)"'); ?>
		<?= $this->textM->formfield('button', '', 'Next: Achievements, Strengths, Areas of Improvement, Goals', 'btnclass floatright', '', 'onClick="checkValues(3)"'); ?>
	</div>				
	<div id="divEval4" class="divSelfEval hidden">
		<table width="100%">
			<tr>
				<td width="50%" valign="top"><b>Achievements</b><br/>
					Write in the box below your achievements within the review period
					<?= $this->textM->formfield('textarea', 'achievements', '', 'forminput', '', 'rows=5'); ?>
				</td>
				<td valign="bottom">Employee says:<br/>
					<?= $this->textM->formfield('textarea', '', $evalData->achievements, 'forminput','','disabled rows=5'); ?>
				</td>
			</tr>
			
			<tr>
				<td width="50%" valign="top"><b>Strengths</b><br/>
					Write in the box below your strengths
					<?= $this->textM->formfield('textarea', 'strengths', '', 'forminput', '', 'rows=5'); ?>
				</td>
				<td valign="bottom">Employee says:<br/>
					<?= $this->textM->formfield('textarea', '', $evalData->strengths, 'forminput','','disabled rows=5'); ?>
				</td>
			</tr>
			
			<tr>
				<td width="50%" valign="top"><b>Areas of Improvement</b><br/>
					Write in the box below the areas in which you can still improve in.
					<?= $this->textM->formfield('textarea', 'areasOfImprovement', '', 'forminput', '', 'rows=5'); ?>
				</td>
				<td valign="bottom">Employee says:<br/>
					<?= $this->textM->formfield('textarea', '', $evalData->areasOfImprovement, 'forminput','','disabled rows=5'); ?>
				</td>
			</tr>
			
			<tr>
				<td width="50%" valign="top"><b>Goals in the next review period</b><br/>
					Write in the box below your goals to be achieved in the next review period.
					<?= $this->textM->formfield('textarea', 'goals', '', 'forminput', '', 'rows=5'); ?>
				</td>
				<td valign="bottom">Employee says:<br/>
					<?= $this->textM->formfield('textarea', '', $evalData->goals, 'forminput','','disabled rows=5'); ?>
				</td>
			</tr>
		</table>
		
		<br/><br/>
		<?= $this->textM->formfield('button', '', 'Back: Evaluation Part 3', 'btnclass','','onClick="backEval(3)"'); ?>
		<?= $this->textM->formfield('button', '', 'Next: Calculate Performance Rating', 'btnclass floatright','','onClick="calculatePerformance()"'); ?>
	</div>
</div>

<div id="divFourth" class="hidden">
	<div style="padding-right:20px;">
		<p><b>You have reached the last part of rating the performance indicators.</b></p>
		<?php
			$selfRating = $this->textM->convertNumFormat($evalData->evalRating);
			$selfRatingPer = $this->textM->convertNumFormat($selfRating * 0.20);
		?>
		
		<table class="tableInfo" border=1>
			<tr class="trhead">
				<td><br/></td>
				<td align="center">Rating</td>
				<td align="center">%</td>
			</tr>
			<tr>
				<td width="80%">The employee has given him/herself an average rating of</td>
				<td align="center"><?= $selfRating ?></td>
				<td align="center"><?= $selfRatingPer  ?></td>
			</tr>
			<tr>
				<td>You have given the employee an average rating of</td>
				<td align="center"><span id="rating"></span></td>
				<td align="center"><span id="ratingPercent"></span></td>
			</tr>
		</table>
		
		<center>
			<br/>
			<div style="border:1px solid #ccc; width:40%; padding:20px;">
				<b>Total Final Rating</b><br/>
				<span id="finalRatingText">0.00</span>
			</div>
			
			<br/>Based on the scoring metrics below, the employee's scored a final rating of:
			<div style="padding:10px; font-size:14px; font-weight:bold; border:1px solid #ccc; width:70%; margin:auto; background-color:yellow;" id="divScoreText"></div>
		</center>
		
		<br/><b>Recommendation</b><br/>
		What action do you recommend for this employee:
		<?php
			$arrRec = $this->textM->constantArr('recommendations');
			$selectRec = '<option value=""></option>';
			foreach($arrRec AS $m=>$ar){
				$selectRec .= '<option value="'.$m.'">'.$ar.'</option>';
			}						
		?>
		<?= $this->textM->formfield('select', '', $selectRec, 'forminput','','id="recommendID" required') ?>
		
		<div id="divSpecify" class="recClass hidden" style="padding-top:15px;">
			<b>Please specify your recommendation below:</b><br/>
			<?= $this->textM->formfield('text', 'recommendation','', 'forminput') ?>
		</div>
		
		<div id="divNextReview" class="recClass hidden" style="padding-top:15px;">
			<b>Next Review Date</b><br/>
			When will the employee be evaluated next?<br/>
			This date must NOT be earlier than the review date, and must not be later than the employee's 6th month, which is on <?= date('F d, Y', strtotime($row->startDate.' +180 days'))?>.
			<?= $this->textM->formfield('text', 'nextReviewDate','', 'forminput reviewDate') ?>
		</div>
		
		<div id="divEffectiveDate" class="recClass hidden" style="padding-top:15px;">
			<b>Effective Date of Recommendation</b><br/>
			When is the recommendation date effective?<br/>		
			This date must NOT be earlier than the review date
			<?= $this->textM->formfield('text', 'effectiveDate','', 'forminput reviewDate') ?>
		</div>
		
		<div id="divSeparation" class="recClass hidden" style="padding-top:15px;">
			<b>Effective Date of Separation</b><br/>
			The date after the employee’s last day of work.<br/>
			This date must NOT be earlier than the review date:
			<?= $this->textM->formfield('text', 'effectiveSeparationDate','', 'forminput reviewDate') ?>
			
			<br/><br/>
			<b>Would you recommend the employee to another department?</b><br/>
			<input type="radio" name="wremployee" value="1"/> Yes
			<input type="radio" name="wremployee" value="0"/> No
			<br/><br/>
			
			<b id="textNo" class="recRadio hidden">Why not?</b>
			<b id="textYes" class="recRadio hidden">Which department/s would you recommend the employee to? And why?</b>
			<?= $this->textM->formfield('textarea', 'recommendationRemarks', '', 'forminput hidden'); ?>
		</div>
		
		
		
		<center>
			<br/><br/>
		<?php
			echo $this->textM->formfield('hidden', 'evalRating', '');
			echo $this->textM->formfield('hidden', 'finalRating', '');
			echo $this->textM->formfield('hidden', 'submitType', 'submitSelfEval');
			echo $this->textM->formfield('submit', '', 'Submit Evaluation', 'btnclass btngreen', '', 'style="padding:20px;"');
			echo '<br/>'.$this->textM->formfield('button', '', 'Back: Review Evaluation', 'btnclass','','onClick="backEval(4)"');
			//echo '<br/>OR<br/>';
			//echo '<a href="">Click here to review the PDF file before submitting to <br/>HR for review and printing</a>';
		?>
		</center>
	</div>
	<br/>
	<hr/>		
	<div class="fs11px">
		<h3><b>Scoring Matrix</b></h3>
		The final score will be the average of all scores obtained in the performance evaluation. The minimum acceptable rating for continued employment is Good (Meets Expectation).
		<table border=1 cellpadding=5 cellspacing=0>
			<tr>
				<td width="25%" align="center"><b>8.00 - 10.00</b></td>
				<td><b>Excellent (Exceeds expectations).</b><br/>Performance consistently exceeded expectations in all essential areas of responsibility, and the quality of work overall was excellent. Goals were met.</td>
			</tr>
			<tr>
				<td align="center"><b>5.00 - 7.99</b></td>
				<td><b>Good (Meets expectations).</b><br/>Performance consistently meet expectations in all essential areas of responsibility, at times possibly exceeding expectations, and the quality of work overall was very good. The most critical goals were met.</td>
			</tr>
			<tr>
				<td align="center"><b>3.00 - 4.99</b></td>
				<td><b>Fair (Improvement needed).</b><br/>Performance did not consistently meet expectations; performance failed to meet expectations in one or more essential areas of responsibility, and/or one or more of the most critical goals were not met.</td>
			</tr>
			<tr>
				<td align="center"><b>0 - 2.99</b></td>
				<td><b>Poor (Unsatisfactory).</b><br/>Performance was consistently below expectations in most essential areas of responsibility, and/or reasonable progress toward critical goals was not made. Significant improvement is needed in one or more important areas.</td>
			</tr>					
		</table>
	</div>
</div>

</form>

<script type="text/javascript">
	$(function(){
		$('.reviewDate').datetimepicker({ 
			format:'F d, Y',
			timepicker:false,
			minDate: '<?= date('Y/m/d', strtotime($row->startDate.' +90 days')) ?>'
		});
		
		$('#btnYesSupervisor').click(function(){
			$('.whoEval').addClass('hidden');
			$('#yesSupervisor').removeClass('hidden');
			$('#btnNextPE').removeClass('hidden');
			$('#isSupVal').val(1);
		});
		
		$('#btnNoSupervisor').click(function(){
			$('.whoEval').removeClass('hidden');
			$('#noSupervisor').removeClass('hidden');
			$('#btnNextPE').removeClass('hidden');
			$('#isSupVal').val(0);
		});
		
		$('#btnNextPE').click(function(){
			valid = true;
			v = $('#isSupVal').val();
			
			if($('input[name="reviewDate"]').val()=='')
				valid = false;
			
			if(v==0 && ($('select[name="reviewerEmpID"]').val()=='' || $('textarea[name="reasonNotIS"]').val()=='')){
				valid = false;
			}
				
			if(valid==false){
				alert('All fields are required.')
			}else{
				$('#h3pre').text('Performance Evaluation for ');
				$('#divSecond').addClass('hidden');
				$('#divThird').removeClass('hidden');
			}
		});
		
		$('#recommendID').change(function(){
			r = $(this).val();
			$('.recClass').addClass('hidden');
			if(r==9){
				$('input[name="recommendation"]').val('');
				$('#divSpecify').removeClass('hidden');
				$('#divNextReview').removeClass('hidden');
				$('#divEffectiveDate').removeClass('hidden');
			}else{
				$('input[name="recommendation"]').val($('#recommendID option:selected').text());
				
				if(r==6) $('#divNextReview').removeClass('hidden');
				else if(r==3) $('#divSeparation').removeClass('hidden');
				else if(r==1) $('#divEffectiveDate').removeClass('hidden');					
			}
		});
		
		$('input[name="wremployee"]').click(function(){
			ii = $(this).val();
			$('.recRadio').addClass('hidden');
			$('textarea[name="recommendationRemarks"]').removeClass('hidden');
			if(ii==0)
				$('#textNo').removeClass('hidden');
			else
				$('#textYes').removeClass('hidden')
		});
	});
	
	function showEvaluation(){
		$('#divFirst').addClass('hidden');
		$('#divSecond').removeClass('hidden');
	}
	
	function checkValues(id){
		valid = true;
		$('#divEval'+id+' input[type="number"]').each(function(){
			v = $(this).val();
			if(v=='' || v<1 || v>10){
				valid = false;
			}
		});
		
		if(id==1){
			if($('input[name="reviewFrom"]').val()=='' || $('input[name="reviewTo"]').val()=='' )
				valid = false;
		}
		
		if(valid==false){
			alert('All fields are required and rating should be between 1 to 10.');
		}else{
			$('.divSelfEval').addClass('hidden');
			$('#divEval'+(id+1)).removeClass('hidden');	
			
			if((id+1)==4){
				$('#giveRatingText').addClass('hidden');
			}
		}
	}
	
	function backEval(id){
		$('#divEval'+id).removeClass('hidden');
		$('#giveRatingText').removeClass('hidden');
		$('#divEval'+(id+1)).addClass('hidden');
		
		if(id==1){
			$('#tblReviewDate').removeClass('hidden');
		}else if(id==4){
			$('#divThird').removeClass('hidden');
			$('#divFourth').addClass('hidden');
		}		
	}
	
	function getScoreMatrix(score){
		$sc = '';
		if(score>=8)
			sc = 'Excellent (Exceeds expectations)';
		else if(score>=5 && score<8)
			sc = 'Good (Meets expectations).';
		else if(score>=3 && score<5)
			sc = 'Fair (Improvement needed).';
		else
			sc = 'Poor (Unsatisfactory)';
			
		return sc;
	}
	
	
	function calculatePerformance(){
		f = 0;
		su = 0;
		$('input[type="number"]').each(function(){
			su += parseInt($(this).val());
			f++;
		});
		
		rating = su/f;
		
		$('#rating').text(parseFloat(rating).toFixed(2));
		$('#ratingPercent').text(parseFloat(rating*0.80).toFixed(2));
		$('input[name="evalRating"]').val(parseFloat(rating).toFixed(2));
		
		rating = (rating*0.80) + parseFloat('<?= $selfRatingPer ?>');
		$('#divScoreText').text(getScoreMatrix(rating));
		$('#finalRatingText').text(parseFloat(rating).toFixed(2));
		$('input[name="finalRating"]').val(parseFloat(rating).toFixed(2));
		
		$('#divFourth').removeClass('hidden');
		$('#divThird').addClass('hidden');
	}
	
	function validateSubmit(){
		validtext = '';
		mahal = $('#recommendID').val();
		
		if(mahal==1 && $('input[name="effectiveDate"]').val()==''){
			validtext += '-  Effective date of recommendation\n';
		}else if(mahal==3){
			if($('input[name="effectiveSeparationDate"]').val()=='') validtext += '-  Effective date of separation\n';
			if($('input[name="wremployee"]:checked').length ==0) validtext += '-  Recommend to other department\n';
			if($('textarea[name="recommendationRemarks"]').val()=='') validtext += '-  Reason\n';			
		}
		else if(mahal==6 && $('input[name="nextReviewDate"]').val()==''){
			validtext += '-  Next review date\n';
		}else if(mahal==9){
			if($('input[name="recommendation"]').val()=='')
				validtext += '-  Specify your recommendation\n';
			if($('input[name="nextReviewDate"]').val()=='')
				validtext += '-  Next review date\n';
			if($('input[name="effectiveDate"]').val()=='')
				validtext += '-  Effective date of recommendation\n';
		}
		
		if(validtext!=''){
			alert('Please input required fields:\n'+validtext);
			return false;
		}else{
			$('input[type="submit"]').attr('disabled', 'disabled');
			return true;
		}
	}
	
	
</script>

