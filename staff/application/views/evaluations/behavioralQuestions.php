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
		background:#CCCCCC;

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
				<button type="button" class="btnclass" id='btnAddExpectation' onclick="addExpectation(true)" style="width:50%">Add Expectation</button>

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
		</tr>
	</thead>
	<tbody id='tbl_tbody'>
		<?php
			$evaluatorArr = ['Team Leader', 'Leaders and Clients', 'Immediate Supervisor'];
			foreach ($questions as $row) {
				$details = $row->details;
		?>
			<tr class='question_row td_hover' data-id="<?php echo $row->question_id?>">
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

							echo "<tr><td class='{$tdClass} txtExpectationtxt row".$i."' >".$expectation_val."</td></tr>";
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
// This stands for the number of rows.
var i = 1; 
var detailsIdArr = [];
var questionId = null;
var haha = 1;

	// Append this row whenever the "add evaluator" button is clicked.
	function addInlineEvaluator(i){
		// Whenever there is an update, and new rows have to be added,
		// this function executes to add into the number of detail id's so it can be parsed
		// and the system will know on what to update and what to insert.
		detailsIdArr.push("add"); 

		return "<tr class='bottRow'> <td><label>Evaluator</label></td> <td> <select name='slbEvaluator' class='slbEvaluator row"+i+"'> <option value='0'>Team Leader</option> <option value='1'>Leaders and Clients</option> <option value='2'>Immediate Supervisor</option> </select> </td> <td> <label>Weight</label> <input type='number' class='wt row"+i+"' min='1' max='99' colspan='2'> </td><td></td></tr>";
	}

	// Set the input field for the text area, also include the evaluator, width, Score width.
	function addInlineExpectation(i){
		return "<tr> <td>Expectation</td> <td colspan='3'><textarea cols='106' class='txtExpectation row"+i+" forSave'></textarea></td> </tr>"+addInlineEvaluator(i);
	}

	function addQuestion(){
		var firstRow = '<tr> <td><label>Objective Goals</label></td> <td colspan="3"><textarea cols="106" id="txtObjective"></textarea></td> </tr> <tr> <td><label>Evaluation Question</label></td> <td colspan="3"><textarea cols="106" id="txtEvaluation"></textarea></td> </tr>';

		firstRow += addInlineExpectation(0);
		
		$('#tblAddQuestion').css('display','table');
		$(firstRow).appendTo("#addQuestionTbl");

		 $("#btnAddQuestion, #btnUpdate").css('display','none');
	}

	// Append the expectation field, disable the add evaluator button
	function addExpectation(disable, iii=i){
		var inlineExpectation = addInlineExpectation(iii);
		 $('#btnAddEvaluator').prop('disabled',disable);
		 $(inlineExpectation).appendTo('#addExpectationTbl');
		 i++
	}

	// Append the evaluator field, disable the add expectation button
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

			var data = "txtObjective="+$('#txtObjective').val()+
			"&txtEvaluation="+$('#txtEvaluation').val()+
			"&txtExpectation="+expectation+
			"&txtEvaluator="+evaluator+
			"&txtWeight="+weight+
			'&questionType=behavioral';

			if(submitType == 'updateQuestions'){
				data += "&detailsId="+detailsIdArr+"&questionId="+questionId;
			}

			$.ajax({
				data:data,
				type:'POST',
				url:'../'+submitType,
				cache:false,
				dataType:'json',
			}).done(function(response){
				alert("The question list has been updated.");
				//location.reload();

				if(submitType == 'addQuestions'){
					var row = setRow(response);
					$(row).appendTo('#tbl_tbody').fadeIn('slow');
				}

				if(submitType == 'updateQuestions'){
					var row = setRow(response);
					// replace current row with the updated row;
					$(".question_row[data-id='"+response[0].question_id+"']").replaceWith(row);
				}

				$("#tblAddQuestion textarea, #tblAddQuestion input").val('');
			});
		}
	}


	function passDataToInput(x, row, addExpectation){
		$('.slbEvaluator.row'+x).val($(row).find('.txtEvaluator.row'+x).data('val'));
		$('.wt.row'+x).val($(row).find('.txtWeight.row'+x).data('val'));
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

		// Check if the length of character is greater than 2, then
		// Slice the last character


	});

	// Since attribute maxlength don't work for number type textfield
	// check field if character is greater than two then slice.
	// $("input[type='number']").on("click", function(e){
	$(document).on('keyup','input[type="number"]',function(){
		var $field = $(this),
		val = this.value;

		if(val.length > 2) {
			val = val.slice(0,2);
			$field.val(val);
		}
	});

	function setRow(r){
		var evaluatorArr = ['Team Leader', 'Leaders and Clients', 'Immediate Supervisor'];
		var row = '<tr class="question_row" data-id="'+r[0].question_id+'" style="background:#a9fb88;">>'+
		 '<td class="td txtGoals">'+r[0].goals+'</td>'+
		 '<td class="td txtQuestion">'+r[0].question+'</td>'+
		 '<td class="td">'+
		 '<table>';

		var expectation = null,
			i = 0,
			details_count = Object.keys(r[1]).length,
			row_count = details_count -1;
	//		console.log(details_count);

		var expectationArr = [];
		for($x = 0; $x < details_count; $x++){
			expectationArr.push(r[1][$x].expectation);
		}

		 expectationArr = uniqueList(expectationArr);
		 expectationCount = expectationArr.length -1;

		for(var i in expectationArr){
		//	tdClass = ((i < expectationCount) ? 'tdBot' : '');
			if(i < expectationCount){
				tdClass = 'tdBot';
			}else{
				tdClass = '';
			}
		  row += "<tr><td class='"+tdClass+" txtExpectationtxt row"+i+"'>"+expectationArr[i]+"</td></tr>";
		};
		row += "</table></td>";

		row += "<td class='td'><table>";
		for(i = 0; i < r[1].length;i++){
			tdClass = (i < row_count ? 'tdBot' : '');
			row+="<tr><td class='"+tdClass+" txtEvaluator row"+i+"' data-val='"+r[1][i].evaluator+"' data-id='"+r[1][i].detail_id+"'>"+evaluatorArr[r[1][i].evaluator]+"</td></tr>";
		}
		row += "</table></td>";

		row += "<td class='td'><table>";
		for(i = 0; i < r[1].length;i++){
			tdClass = (i < row_count ? 'tdBot' : '');
			row += "<tr><td class='"+tdClass+" txtWeight row"+i+"' data-val='"+r[1][i].weight+"'>"+r[1][i].weight+"</td></tr>";
		}
		row += "</table></td>";
		row += '</tr>';
		return row;
	}

	  function uniqueList(list){
 		var result = [];
		$.each(list, function(i, e){
			if($.inArray(e, result) == -1) result.push(e);
		});
 	   return result;
	}
</script>