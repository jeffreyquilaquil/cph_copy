<style type="text/css">
	#tblBehavioralQuestions thead{
		background: rgb(128,0,0);
		color: white;
		padding:25px;
	}
	#tblBehavioralQuestions th{
		border:2px solid white;
	}
	.bottRow{
		border-bottom:2px solid black;
	}
	td{
		text-align: center;
	}
	.td{
		border:1px solid black;
		padding: 0px 0px 0px 0px;
	}
	input{
		width: 45%;
	}
	select{
		height: 100%;
	}
	button{
		float:right;
	}
	.tdBot{
		border-bottom:1px solid black;
	}
	.question_row:hover{
		background:rgb(93,197,240);
	}

</style>
<table id='tblAddQuestion' style="display:none;border-collapse:collapse">
	<tbody id="addQuestionTbl">

	</tbody>

	<tbody id='addEvaluatorTbl'>

	</tbody>

	<tbody id='addExpectationTbl'>
		
	</tbody>
	<tbody>
		<tr>
			<td colspan="3"></td>
			<td align="center">
				<button type="button" class="btnclass" id='btnAddExpectation' onclick="addExpectation(true)" style="width:50%">Add Expectation</button>| 
				<button type="button" class="btnclass" id='btnAddEvaluator' onclick="addEvaluators(true)">Add Evaluator</button> <br>
				<button type="button" class="btnclass" id="btnSubmit" style="width:85%" onclick="submitForm('addQuestions')" >Submit</button>
				<button type="button" class="btnclass" id='btnUpdate' style="width:85%" onclick="submitForm('updateQuestions')">Update</button>
			</td>
		</tr>
	</tbody>
</table>
<br>
<form method="POST" action="" onsubmit="submitValues()">
	<input type="button" value='Add Question' id='btnAddQuestion' class='btnclass' onclick='addQuestion()'" style="float:right">
	<br>
<table id='tblBehavioralQuestions' style='border-collapse: collapse;'>
	<thead>
		<tr><th colspan='8'>BEHAVIORAL GOALS AND OBJECTIVES</th></tr>
		<tr>
			<th>Objective Goals</th>
			<th width="30%">Evaluation Question</th>
			<th width="30%">Expectation</th>
			<th width="20%">Evaluator</th>
			<th>Wt.</th>
			<th>Wtd. Score</th>
		</tr>
	</thead>
	<tbody id='tbl_tbody'>
		<?php
			$evaluatorArr = ['Team Leader', 'Leaders and Clients', 'Immediate Supervisor'];
			foreach ($questions as $row) {
				$details = $row->details;
		?>
			<tr class='question_row' data-id="<?php echo $row->question_id?>">
				<td class='td txtGoals'><?php echo $row->goals ?></td>
				<td class='td txtQuestion'><?php echo $row->question?></td>
				<td class='td'>
					<table>
					<?php
						 $expectation = null;
						 $i = 0;	
						$details_count = count($details);
						$row_count = $details_count-1;

						$expectationArr = [];
						foreach ($details as $value) {
							array_push($expectationArr, $value->expectation);
						}
						$expectationArr = array_unique($expectationArr);
						$expectationCount = count($expectationArr) -1;

						foreach ($expectationArr as $expectation_val) {
							$tdClass = ($i < $expectationCount ? 'tdBot' : '');

						echo "<tr><td class='{$tdClass} txtExpectationtxt row".$i."' >".$expectation_val."</tr></td>";
						$i++;
						}
					?>
					</table>
				</td>
				<td class='td'>
					<table>
						<?php
							$i = 0;
							foreach ($details as $value) {
								$tdClass = ($i < $row_count ? 'tdBot' : '');
								echo "<tr><td class='{$tdClass} txtEvaluator row".$i."' data-val='".$value->evaluator."' data-id='".$value->detail_id."'>".$evaluatorArr[$value->evaluator]."</td></tr>";
							$i++;
							}
						?>
					</table>
				</td>
				<td class="td">
					<table>
						<?php
							$i = 0;
							foreach($details as $value){
								$tdClass = ($i < $row_count ? 'tdBot' : '');
								echo "<tr><td class='{$tdClass} txtWeight row".$i."' data-val='".$value->weight."'>".$value->weight."%</td></tr>";
							$i++;
							}
						?>
					</table>
				</td>
				<td class="td">
					<table>
						<?php
							$i = 0;
							foreach($details as $value){
								$tdClass = ($i < $row_count ? 'tdBot' : '');
								echo "<tr><td class='{$tdClass} txtWeightScore row".$i."' data-val='".$value->weight_score."'>".$value->weight_score."%</td></tr>";
							$i++;
							}
						?>
					</table>
				</td>			
			</tr>

		<?php
			}	
		?>
	</tbody>
	<tbody>
		<tr>
			<td></td>
			<td>
			</td>
			<td>
			</td>
			<td colspan="3" align='right'>

			</td>
		</tr>
	</tbody>
</table>
</form>
<br><br>


<script type="text/javascript">
var i = 1;
var detailsIdArr = [];
var questionId = null;
var haha = 1;

	function addInlineEvaluator(i){
		detailsIdArr.push("add");
		return "<tr class='bottRow'> <td><label>Evaluator</label></td> <td> <select name='slbEvaluator' class='slbEvaluator row"+i+"'> <option value='0'>Team Leader</option> <option value='1'>Leaders and Clients</option> <option value='2'>Immediate Supervisor</option> </select> </td> <td> <label>Weight</label> <input type='number' class='wt row"+i+"'> </td> <td> <label>Weighted Score</label> <input type='number' class='wtScore row"+i+"'> </td> </tr>";

	}

	function addInlineExpectation(i){
		return "<tr> <td>Expectation</td> <td colspan='3'><textarea cols='106' class='txtExpectation row"+i+" forSave'></textarea></td> </tr>"+addInlineEvaluator(i);
	}

	function addQuestion(){
		
		var firstRow = '<tr> <td><label>Objective Goals</label></td> <td colspan="3"><textarea cols="106" id="txtObjective"></textarea></td> </tr> <tr> <td><label>Evaluation Question</label></td> <td colspan="3"><textarea cols="106" id="txtEvaluation"></textarea></td> </tr><tr> <td>Expectation</td> <td colspan="3"><textarea cols="106" class="txtExpectation forSave Expectation row0"></textarea></td> </tr> <tr class="bottRow"> <td><label>Evaluator</label></td> <td> <select name="slbEvaluator" class="slbEvaluator row0"> <option value="0">Team Leader</option> <option value="1">Leaders and Clients</option> <option value="2">Immediate Supervisor</option> </select> </td> <td> <label>Weight</label> <input type="number" class="wt row0"> </td> <td> <label>Weighted Score</label> <input type="number" class="wtScore row0"> </td> </tr>';
		
		$('#tblAddQuestion').css('display','table');
		$(firstRow).appendTo("#addQuestionTbl");

		 $("#btnAddQuestion, #btnUpdate").css('display','none');
	}

	function addExpectation(disable, iii=i){
		var inlineExpectation = addInlineExpectation(iii);
		 $('#btnAddEvaluator').prop('disabled',disable);
		 $(inlineExpectation).appendTo('#addExpectationTbl');
		 i++
	}

	function addEvaluators(disable, iii=i){
		var inlineEvaluator = addInlineEvaluator(iii);
		$(inlineEvaluator).appendTo('#addEvaluatorTbl');
		$('#btnAddExpectation').prop('disabled',disable);
		i++;
	}

	function submitForm(submitType){
		var check = true;
		$i = 1;
		$("#tblAddQuestion textarea, #tblAddQuestion input").each(function(){
			
			if($(this).val() == ""){
				check = false;
				alert("All fields must not be empty");
				return false;
			}
		});

		if(check){
			var expectation = [];
			$('.forSave').each(function(){
				expectation.push($(this).val());
			});
			expectation = expectation.join('__');

			var evaluator = [];
			$('.slbEvaluator').each(function(){
				evaluator.push($(this).val());
			});

			var weight = [];
			$('.wt').each(function(){
				weight.push($(this).val()); 
			});

			var weightScore = [];
			$('.wtScore').each(function(){
				weightScore.push($(this).val());
			});

			var data = "txtObjective="+$('#txtObjective').val()+
			"&txtEvaluation="+$('#txtEvaluation').val()+
			"&txtExpectation="+expectation+
			"&txtEvaluator="+evaluator+
			"&txtWeight="+weight+
			"&txtWeightScore="+weightScore+
			'&questionType=behavioral';

			if(submitType == 'updateQuestions'){
				data += "&detailsId="+detailsIdArr+"&questionId="+questionId;
			}

			$.ajax({
				data:data,
				type:'POST',
				url:'../'+submitType,
				cache:false
			}).done(function(response){
				alert("The question list has been updated.");
				location.reload();
			//	$('p').html(response);
			});
		}
	}

	function passDataToInput(x, row, addExpectation){
		$('.slbEvaluator.row'+x).val($(row).find('.txtEvaluator.row'+x).data('val'));
		$('.wt.row'+x).val($(row).find('.txtWeight.row'+x).data('val'));
		$('.wtScore.row'+x).val($(row).find('.txtWeightScore.row'+x).data('val'));
		if (addExpectation) {

			$('.txtExpectation.row'+x).val($(row).find('.txtExpectationtxt.row'+x).text());
		}
	}


	$(document).on('dblclick', '.question_row', function(){
		i=1; 	detailsIdArr=[];
		$('#addQuestionTbl *, #addEvaluatorTbl *, #addExpectationTbl *').remove();
		questionId = $(this).data('id');
		addQuestion();
		$("#btnAddQuestion, #btnSubmit").css('display','none');
		$("#btnUpdate").css("display",'block');
		$('#txtObjective').val($(this).find('.txtGoals').text());
		$('#txtEvaluation').val($(this).find('.txtQuestion').text());
		$('.forSave.row0').val($(this).find('.txtExpectation.row0').text());

		$expectation_count = $(this).find('.txtExpectationtxt').length;
		$evaluator_count = $(this).find('.txtEvaluator').length;
		$(this).find('.txtEvaluator').each(function(){
			detailsIdArr.push($(this).data('id'));
		});

		var x = 0;
		passDataToInput(x, $(this), true);

		// if The numbers of rows for expectations are more than one.
		if($expectation_count > 1){
			for (var ii = 1; ii < $evaluator_count; ii++) {
				x++;
				addExpectation(false,ii, false);
				passDataToInput(x, $(this), true); 
			}
		// If the numbers of rows for the Evaluator are more than one.
		}else if($evaluator_count > 1){
			for (var ii = 1; ii < $evaluator_count; ii++) {
				x++;
				addEvaluators(false,ii, false);
				passDataToInput(x, $(this), false);
			}
		}else{
			// Nothing happens
		}
	})
</script>