<?php
	$cntEvals = count($queryEvaluations);
?>

<button class="btnclass floatright <?= (($cntEvals==0)?'hidden':'') ?>" id="btnEvaluate">Evaluate</button>
<h3>Self-Evaluation</h3><hr/>

<?php
echo '<div id="evalMain">';
	if(isset($inserted)){
		echo '<p class="errortext">Self-evaluation has been submitted.</p>';
	}
	
	if($cntEvals>0){
		echo '<h3>Evaluation History</h3>';
		echo '<table class="tableInfo">';
			echo '<tr class="trhead">
					<td>Evaluation ID</td>
					<td>Review From</td>
					<td>Review To</td>
					<td>Status</td>
					<td>Reviewer</td>
					<td>Final Rating</td>	
					<td>Date Self Evaluated</td>					
				</tr>';
			foreach($queryEvaluations AS $q){
				echo '<tr>';
					echo '<td>'.$q->evalID.'</td>';
					echo '<td>'.date('F d, Y', strtotime($q->reviewFrom)).'</td>';
					echo '<td>'.date('F d, Y', strtotime($q->reviewTo)).'</td>';
					echo '<td>'.(($q->status==0)?'Self-Evaluated':'Evaluated').'</td>';
					echo '<td>'.$q->reviewer.'</td>';
					echo '<td><b>'.((!empty($q->finalRating))?$this->textM->getScoreMatrix($q->finalRating):'').'</b></td>';					
					echo '<td>'.date('F d, Y', strtotime($q->dateSelfEvaluated)).'</td>';
				echo '</tr>';
			}
		echo '</table>';
	}
	
echo '</div>';
	
	$reviewFrom = '';
	$reviewTo= '';
	if($cntEvals==0){
		$reviewFrom = date('F d, Y', strtotime($this->user->startDate));
		$reviewTo= date('F d, Y', strtotime($this->user->startDate.' +90 days'));
	}
	
?>
<form id="formRating" action="" method="POST" class="<?= (($cntEvals!=0)?'hidden':'') ?>" onSubmit="disableSubmit()">
	<div id="divEvalForm">		
		<div id="tblReviewDate" class="divSelfEval">
		<?php
			if($cntEvals==0)
				echo $this->textM->formfield('hidden', 'reviewType', '90th');
		?>
			<table width="50%" >
				<tr>
					<td><b>Review From</b></td>
					<td><?= $this->textM->formfield('text', 'reviewFrom', $reviewFrom, 'forminput datepick'); ?></td>
				</tr>
				<tr>
					<td><b>Review To</b></td>
					<td><?= $this->textM->formfield('text', 'reviewTo', $reviewTo, 'forminput datepick'); ?></td>
				</tr>		
			</table>
			<br/>
			<i><b>Note:</b> This will be your 90th day Evaluation.</i>
			<hr/>
		</div>
		
		<p id="giveRatingText">Give a rating for the following performance indicators:</p>			
		<div id="divEval1" class="divSelfEval">
			<?= $this->textM->evaluationTable(0, 1) ?>
			<br/><p align="right"><?= $this->textM->formfield('button', '', 'Next: Evaluation Continuation', 'btnclass','','onClick="checkValues(1)" style="text-align:right;"'); ?></p>
		</div>
		<div id="divEval2" class="divSelfEval hidden">
			<?= $this->textM->evaluationTable(0, 2) ?>
			<br/>
			<?= $this->textM->formfield('button', '', 'Back: Evaluation Part 1', 'btnclass','','onClick="backEval(1)"'); ?>
			<?= $this->textM->formfield('button', '', 'Next: Evaluation Continuation', 'btnclass floatright','','onClick="checkValues(2)"'); ?>
		</div>
		<div id="divEval3" class="divSelfEval hidden">
			<?= $this->textM->evaluationTable(0, 3) ?>
			<br/>
			<?= $this->textM->formfield('button', '', 'Back: Evaluation Part 2', 'btnclass','','onClick="backEval(2)"'); ?>
			<?= $this->textM->formfield('button', '', 'Next: Achievements, Strengths, Areas of Improvement, Goals', 'btnclass floatright', '', 'onClick="checkValues(3)"'); ?>
		</div>				
		<div id="divEval4" class="divSelfEval hidden">
			<b>Achievements</b><br/>
			Write in the box below your achievements within the review period
			<?= $this->textM->formfield('textarea', 'achievements', '', 'forminput','','rows=5'); ?>
			<br/><br/>
			<b>Strengths</b><br/>
			Write in the box below your strengths
			<?= $this->textM->formfield('textarea', 'strengths', '', 'forminput','','rows=5'); ?>
			<br/><br/>
			<b>Areas of Improvement</b><br/>
			Write in the box below the areas in which you can still improve in.
			<?= $this->textM->formfield('textarea', 'areasOfImprovement', '', 'forminput','','rows=5'); ?>
			<br/><br/>
			<b>Goals in the next review period</b><br/>
			Write in the box below your goals to be achieved in the next review period.
			<?= $this->textM->formfield('textarea', 'goals', '', 'forminput','','rows=5'); ?>
			<br/><br/>
			<?= $this->textM->formfield('button', '', 'Back: Evaluation Part 3', 'btnclass','','onClick="backEval(3)"'); ?>
			<?= $this->textM->formfield('button', '', 'Next: Calculate Performance Rating', 'btnclass floatright','','onClick="calculatePerformance()"'); ?>
		</div>			
	</div>

	<div id="divEvalRating" class="divEvalForm hidden">
		<table>
			<tr>
				<td width="50%">
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
				</td>
				<td width="50%">
					<div class="tacenter" style="padding:20px;">
						<p><b>You have reached the last part of rating the performance indicators.</b></p>
						<div style="border:1px solid #ccc; padding:10px; width:90%; margin:auto;">
							You have an average rating of<br/>
							<h3><b id="rating"></b></h3>
						</div>
						<br/><b>Based on the scoring metrics on the left, you scored a rating of:</b>
						<div style="padding:10px; font-size:14px; font-weight:bold; border:1px solid #ccc; width:70%; margin:auto; background-color:yellow;" id="divScoreText"></div>
						
						<br/><br/>
						<?php
							echo $this->textM->formfield('hidden', 'evalRating', '');
							echo $this->textM->formfield('hidden', 'submitType', 'submitSelfEval');
							echo $this->textM->formfield('submit', '', 'Submit Self-Evaluation', 'btnclass btngreen', '', 'style="padding:20px;"');
							echo '<br/>'.$this->textM->formfield('button', '', 'Back: Review Evaluation', 'btnclass','','onClick="backEval(4)"');
						?>
					</div>
				</td>
			</tr>
		</table>
	</div>
</form>

<script type="text/javascript">
	$(function(){
		$('#btnEvaluate').click(function(){
			$('#formRating').removeClass('hidden');
			$(this).addClass('hidden');
			$('#evalMain').addClass('hidden');
		});
		
	});
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
	
	function calculatePerformance(){
		f = 0;
		su = 0;
		$('#formRating input[type="number"]').each(function(){
			su += parseInt($(this).val());
			f++;
		});
		
		rating = su/f;
		
		$('#rating').text(parseFloat(rating).toFixed(2));
		$('input[name="evalRating"]').val(parseFloat(rating).toFixed(2));
		$('#divScoreText').text(getScoreMatrix(rating));
		
		$('#divEvalForm').addClass('hidden');
		$('#divEvalRating').removeClass('hidden');
	}
	
	function backEval(id){
		$('#divEval'+id).removeClass('hidden');
		$('#giveRatingText').removeClass('hidden');
		$('#divEval'+(id+1)).addClass('hidden');
		
		if(id==1){
			$('#tblReviewDate').removeClass('hidden');
		}else if(id==4){
			$('#divEvalForm').removeClass('hidden');
			$('#divEvalRating').addClass('hidden');
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
	
	function disableSubmit(){
		$('input[type="submit"]').attr('disabled', 'disabled');
	}
</script>