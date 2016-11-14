<?php

// var_dump($questions['technical']);
$technicalQuestions = $questions['technical'];
$behavioralQuestions = $questions['behavioral'];
#dd($behavioralQuestions, false);
if($uType == 2 && $status == 0){
	// Employee has not yet taken it's self evaluation
}else if($uType == 1 && $status == 1){
	// Evaluator has not yet taken the evaluation
}else{
	exit('You have already taken this evaluation');
}


?>

<style type="text/css">
	.mainTable{
		border-collapse: collapse;
		border: 2px solid black;
	}
	th{
		background: rgb(128,0,0);
		color: white;
		border: 1px solid black;
	}
	td{
		padding: 0px;
		text-align: center;	
	}
	.tbody > tr > td{
		border: 1px solid black;
	}
	.tdBot{
		border-bottom:1px solid black;
	}
	table{
		width: 100%;
	}
	.innertable{
		height: 100%;
		color:black;

	}
	.question_row:hover{
		background:#CCCCCC;
	}
</style>
<div>
<p id="p"></p>
<table id='tblTechnical' class='mainTable' style="color:black">
	<thead>
		<tr>
			<th colspan='9'>TECHNICAL GOALS AND OBJECTIVES</th>
		</tr>
		<tr>
			<th colspan='9'>RATINGS: 0=Did not Meet Expectations  1=Meets Expectations  2=Exceeds Expectations</th>
		</tr>
		<tr>
			<th>#</th>
			<th>Objective Goals</th>
			<th>Expectation</th>
			<th>Output Format</th>
			<th>Evaluation Question</th>
			<th>Wt.</th>
			<th>Employee's Remarks</th>
			<th>Emp Rtg</th>
			<th>Wtd. Score</th>
		</tr>
	</thead>
	<tbody class='tbody'>
	<?php
		$i = 1;
		foreach($technicalQuestions as $key => $value){
			
	?>
		<tr class="question_row technicalQuestions" data-id="<?php echo $value->question_id ?>">
			<th><?php echo $i; ?></th>
			<td><?php echo $value->goals; ?></td>
			<td><?php echo $value->details[0]->expectation; ?></td>
			<td><?php echo $value->details[0]->evaluator; ?></td>
			<td><?php echo $value->details[0]->question; ?></td>
			<td class='wt' data-val="<?php echo $value->details[0]->weight; ?>" data-id="<?php echo $value->details[0]->detail_id ?>"><?php echo $value->details[0]->weight; ?>%</td>
			<td><textarea class='employeeremarks'></textarea></td>
			<td>
				<select class="empRtg">
					<?php
						foreach( range(0, 2) as $val ){
							echo '<option value="'.$val.'">'.$val.'</option>';
						}
					?>
				</select>
			</td>
			<td><span class='wtScore'>0</span>%</td>
		</tr>
	<?php $i++;
	} ?>
	</tbody>
	<tbody>
		<tr>
			<td colspan="6"></td>
			<th colspan="2">Employee's Rating</th>
			<th><span class='ttlRtg'></span>%</th>
		</tr>
		<tr>
			<td colspan="6"></td>
			<th colspan="2">Employee's 20% Weighted Rating</th>
			<th><span class='ttlWtRtg'>0</span>%</th>
		</tr>
	</tbody>
</table>
<br>
<table id="tblBehavioral" class='mainTable' style="color:black">
	<thead>
		<tr><th colspan="9">BEHAVIORAL GOALS AND OBJECTIVES</th></tr>
		<tr>
			<th>#</th>
			<th>Objective Goals</th>
			<th>Expectation</th>
			<th>Evaluator</th>
			<th>Evaluation Question</th>
			<th>Wt.</th>
			<th>Employee's Remarks</th>
			<th>Emp Rtg</th>
			<th>Wtd. Score</th>
		</tr>
	</thead>
	<tbody class='tbody'>
		<?php 
			$i = 1;
			$evaluators = ['Team Mate', 'Leaders and Clients', 'Immediate Supervisor'];
			foreach($behavioralQuestions as $key => $value){

		 ?>
		 	<tr class='question_row behavioralQuestions' data-id="<?php echo $value->question_id ?>">
		 		<th><?php echo $i ?></th>
		 		<td><?php echo $value->goals; ?></td>
		 		<td>
		 			<table class='innertable'>
		 				<?php 
		 					$row=0;
		 					$row_count = count($value->details) - 1;
		 					$expectationArr = [];

		 					foreach($value->details as $details){
		 						array_push($expectationArr, $details->expectation);
		 					}
		 					$expectationArr = array_unique($expectationArr);

		 					foreach($expectationArr as $expectationVal){
		 						$tdClass = ($row < (count($expectationArr)-1) ? 'tdBot':'');
		 						echo '<tr><td class="expectation '.$tdClass.'" data-row="'.$row.'">'.$expectationVal.'</td></tr>';
		 						$row++;
		 					}
		 				 ?>
		 			</table>
		 		</td>
		 		<td>
		 			<table class='innertable'>
		 				<?php $row = 0;
		 					foreach ($value->details as $details) {
		 						$tdClass = ($row < $row_count ? 'tdBot':'');
		 						echo '<tr><td  class="'.$tdClass.'">'.$evaluators[$details->evaluator].'</td></tr>';
		 						$row++;
		 					}
		 				 ?>
		 			</table>
		 		</td>
		 		<td>
		 			<table class='innertable'>
		 				<?php 
		 					$row=0;
		 					$questionArr = [];

		 					foreach($value->details as $details){
		 						array_push($questionArr, $details->question);
		 					}
		 					$questionArr = array_unique($questionArr);

		 					foreach ($questionArr as $questionVal) {
	 							$tdClass = ($row < (count($expectationArr)-1) ? 'tdBot' : '');
	 							echo '<tr><td class="'.$tdClass.'" data-row="'.$row.'">'.$expectationVal.'</td></tr>';
	 							$row++;
		 					}
		 				 ?>
		 			</table>
		 		</td>
		 		<td>
		 			<table class='innertable'>
		 				<?php 
		 					$row = 0;
		 					foreach($value->details as $details){
			 					if($details->nop == 0){
			 						$nopFilter = "opWt";
			 					}else{
			 						$nopFilter = "";
			 					}
		 						$tdClass = ($row < $row_count ? 'tdBot':'');
		 						echo '<tr><td class="'.$tdClass.'" ><span class="wt '.$nopFilter.'" data-row="'.$row.'" data-val="'.$details->weight.'" data-id="'.$details->detail_id.'" data-qoutient="">'.$details->weight.'</span>%</td></tr>';
		 						$row++;
		 					}
		 				 ?>
		 			</table>
		 		</td>
		 		<td>
		 			<table class='innertable'>
		 				<?php 
		 					$row = 0;
		 					foreach($value->details as $details){
		 						$tdClass = ($row < $row_count ? 'tdBot':'');
		 						echo '<tr><td class="'.$tdClass.'"><textarea class="employeeremarks" data-row="'.$row.'"></textarea></td></tr>';
		 						$row++;
		 					}
		 				 ?>
		 			</table>
		 		</td>
		 		<td>
		 			<table class='innertable'>
		 				<?php 
		 					$row = 0;
		 					foreach ($value->details as $details) {
		 					$tdClass = ($row < $row_count ? 'tdBot':'');
		 					if($details->nop == 1){
		 						$rtg = range(0,3);
		 						$nopFilter = "nop";
		 					}else{
		 						$rtg = range(0, 2);
		 						$nopFilter = "";
		 					}
		 				?>
		 					<tr>
		 						<td class=" <?php echo $tdClass ?>">
		 							<select data-row="<?php echo $row ?>" class="empRtg <?php echo $nopFilter ?>" data-rtg="" data-qoutient="">
		 								<?php 
		 									foreach($rtg as $val){
		 										echo '<option value="'.$val.'">'.$val.'</option>';
		 									}
		 								 ?>
		 							</select>
		 						</td>
		 					</tr>
		 				<?php
		 					$row++;
		 					}
		 				 ?>
		 			</table>
		 		</td>
		 		<td>
		 			<table class='innertable'>
			 			<?php 
			 				$row=0;
			 				foreach ($value->details as $details) {
			 					$nopFilter = ($details->nop == 0 ? 'opWtScore' : '');
			 					$tdClass = ($row < $row_count ? 'tdBot':'');
			 					echo '<tr><td class="'.$tdClass.'"><span class="wtScore '.$nopFilter.'" data-row="'.$row.'">0</span>%</td></tr>';
			 					$row++;
			 				}
			 			 ?>
		 			</table>
		 		</td>
		 	</tr>
		 <?php  
		 	$i++;
			 } 

			 if($uType == 1){
			 ?>
			 <tr>
			 	<th colspan="9"><strong>Evaluator Remarks</strong></th>
			 </tr>
			 <tr>
			 	<td colspan="9" style="padding: 8px;"><textarea id="evalRemarks" class="forminput" placeholder="Please enter your remarks here."></textarea></td>
			 </tr>
			 <?php
			 }
		 ?>
	</tbody>
	<tbody>
		<tr>
			<td colspan="6"></td>
			<th colspan="2">Employee's Rating</th>
			<th><span class='ttlRtg'></span>%</th>
		</tr>
		<tr>
			<td colspan="6"></td>
			<th colspan="2">Employee's 20% Weighted Rating</th>
			<th><span class='ttlWtRtg'></span>%</th>
		</tr>
	</tbody>
</table>
<br>
<button class="btnclass" style="float:right" onclick="submitForm()">Submit</button>
</div>
<br><br>
<script type="text/javascript">
var staffType = "<?php echo $this->uri->segment(2) ?>";
	$(function(){
			// if(typeof parent.$.colorbox.close() == 'function'){
			// 	console.log('wala ni sya bay');
			// }

		$('#tblTechnical .empRtg').change(function(){
			$row = $(this).parents('#tblTechnical tr');
			$row.find('.wtScore').text( $(this).val() * parseInt($row.find('.wt').data('val')));

			$.fn.totalRtg('#tblTechnical');
		});


		$('#tblBehavioral .empRtg').change(function(){	
			$row = $(this).parents('#tblBehavioral tr');
			$rowNo = $(this).data('row');
			var opCount = $('.opWt').length;
			
			if($(this).hasClass('nop') && $(this).val() == 3){
				var origWt = $row.find('.wt[data-row="'+$rowNo+'"]').data('val');
				var weightQoutient = parseInt(origWt) / parseInt(opCount);
				weightQoutient = parseFloat(weightQoutient.toFixed(2));
				
				$('.opWt').each(function(){
					var text =  parseFloat($(this).data('val')) + weightQoutient;
					$(this).text( Math.round(text) );
					$(this).attr('data-qoutient', text);

					var $detailRow = $(this).parents('#tblBehavioral tr');
					var rtg = $detailRow.find('.empRtg[data-row="'+ $(this).data('row') +'"]').val();
					$detailRow.find('.wtScore[data-row="'+ $(this).data('row') +'"]').text( Math.round(rtg * text) );
				});

				$row.find('.wt[data-row="'+$rowNo+'"]').attr('data-val', 0);
				$(this).attr('data-qoutient', weightQoutient);
				$row.find('.wt[data-row="'+$rowNo+'"]').text('0');
				$row.find('.wtScore[data-row="'+$rowNo+'"]').text( 0 );

				// Add this class to indicate that its weight have been subdivided
				$(this).addClass('opp');			
			}else if($(this).hasClass('nop') && $(this).hasClass('opp')){
				
				var qoutient = $(this).data('qoutient');
				$row.find('.wt[data-row="'+$rowNo+'"]').attr('data-val', (qoutient * opCount) );
				$row.find('.wt[data-row="'+$rowNo+'"]').text( Math.round(qoutient * opCount) );

				$('.opWt').each(function(){
					var text =  $(this).data('qoutient') - qoutient;
					$(this).text( Math.round(text) );

					var $detailRow = $(this).parents('#tblBehavioral tr');
					var rtg = $detailRow.find('.empRtg[data-row="'+ $(this).data('row') +'"]').val();
					$detailRow.find('.wtScore[data-row="'+ $(this).data('row') +'"]').text( Math.round(rtg * text) );				
				});

				$(this).attr('data-rtg', 0); 
				$(this).removeClass('opp');

				$row.find('.wtScore[data-row="'+$rowNo+'"]').text( $(this).val() * $row.find('.wt[data-row="'+$rowNo+'"]').data('val') );
			}else{
				$row.find('.wtScore[data-row="'+$rowNo+'"]').text( $(this).val() * $row.find('.wt[data-row="'+$rowNo+'"]').data('val') );
			}


	

		//	$row.find('.wtScore[data-row="'+$rowNo+'"]').text( $(this).val() * parseInt() );
			
			$.fn.totalRtg('#tblBehavioral');
		});


		$.fn.totalRtg = function(table){
			var ttlRtg = null;
			$(table+' .wtScore').each(function(){
				ttlRtg+= parseInt($(this).text());
			});
			$(table+' .ttlRtg').text(ttlRtg);
			$(table+' .ttlWtRtg').text( ((ttlRtg * .2).toFixed(2)) ) ;
		};
		$.fn.totalRtg('#tblBehavioral');
		$.fn.totalRtg('#tblTechnical');
	});

	function submitForm(){
	//	console.log("Hello there old guy")
		var $data = {};

		var wtScoreArr = [];
		var wtArr = [];
		var remarksArr = [];
		var detailIdArr = [];
		var questionIdArr = [];
		var ratingArr = [];
		 $('.technicalQuestions').each(function(){
			remarksArr.push($(this).find('.employeeremarks').val());
			wtScoreArr.push($(this).find('.wtScore').text());
			wtArr.push($(this).find('.wt').text());
			detailIdArr.push($(this).find('.wt').data('id'));
			questionIdArr.push($(this).data('id'));
			ratingArr.push($(this).find('.empRtg').val());
		});
		technical = {
			'remarksArr' : remarksArr,
			'wtScoreArr' : wtScoreArr,
			'wtArr' : wtArr,
			'detailIdArr' : detailIdArr,
			'questionIdArr' : questionIdArr,
			'ratingArr' : ratingArr,
		}

		var wtScoreArr = [];
		var wtArr = [];
		var remarksArr = [];
		var detailIdArr = [];
		var questionIdArr = [];
		var ratingArr = [];
		$('.behavioralQuestions .wtScore').each(function(){
			var rowNo = $(this).data('row');
			$parent = $(this).parents('.behavioralQuestions');
			remarksArr.push($parent.find('.employeeremarks[data-row="'+rowNo+'"]').val());
			wtScoreArr.push($parent.find('.wtScore[data-row="'+rowNo+'"]').text());
			wtArr.push($parent.find('.wt[data-row="'+rowNo+'"]').text());
			detailIdArr.push($parent.find('.wt[data-row="'+rowNo+'"]').data('id'));
			questionIdArr.push($parent.data('id'));
			ratingArr.push($parent.find('.empRtg[data-row="'+rowNo+'"]').val());
		});

		behavioral = {
			'remarksArr' : remarksArr,
			'wtScoreArr' : wtScoreArr,
			'wtArr' : wtArr,
			'detailIdArr' : detailIdArr,
			'questionIdArr' : questionIdArr,
			'ratingArr' : ratingArr,
		}

		$data = {
			'technical':technical,
			'behavioral':behavioral,
			'empId':"<?php echo $empId ?>",
			'evaluator':"<?php echo $evaluator ?>",
			'staffType' : staffType,
			'notifyId' : "<?php echo $notifyId ?>",
			'evalRemarks' : $('#evalRemarks').val(),
			'empRating' : {
				'technical': $('#tblTechnical .ttlRtg').text(),
				'behavioral' : $('#tblBehavioral .ttlRtg').text(),
			},
			'emp20Rtg' : {
				'technical' : $('#tblTechnical .ttlWtRtg').text(),
				'behavioral' : $('#tblBehavioral .ttlWtRtg').text(),
			}
		}

//		console.log($data);

		displaypleasewait();
		$.ajax({
			url:'../../../../evaluations/saveEvaluation',
			type:'POST',
			data:{'data':$data},
			async:'false',
		}).done(function(r){
			
			alert("The evaluation score has been recorded");

			window.close();
		//	console.log(r);
		//	parent.$.colorbox.close();
		}).error(function(r){
			console.log('error');
			console.log(r);
		});	
	}
</script>