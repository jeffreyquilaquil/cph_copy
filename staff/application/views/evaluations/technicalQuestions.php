<style type="text/css">
	#tblTechnicalQuestions thead{
		background: rgb(128, 0, 0);
		color:white;
		padding: 25px;
	}
	#tblTechnicalQuestions th{
		border:2px solid white;
	}
	.td{
		border:1px solid black;
		padding: 0px 0px 0px 0px;
	}
	.question_row:hover{
		background:rgb(93,197,240);
	}
</style>

<?php
	$careerList = [
		'Application Developer', 'IT Specialist'
	]
?>

<div style="float:right">
	<select id="slbCareer">
		<?php
			foreach ($careerList as $key => $value) {
				echo "<option value='".$key."'>".$value."</option>";
			}
		?>
	</select>

	<button class='btnclass' onclick="getQuestions()">Go</button>
</div>

<form method="POST" action="" onsubmit="submitValues()">
<input type="hidden" id="jobType" value="<?php echo $this->uri->segment(4) ?>">
<input type="hidden" id="questionId">
<input type="hidden" id="detailId">
<table id='tblTechnicalQuestions' style="border-collapse: collapse;">
	<thead>
		<tr><th colspan='8'>TECHNICAL GOALS AND OBJECTIVES</th></tr>
		<tr>
			<th>Objective Goals</th>
			<th>Expectation</th>
			<th>Output Format</th>
			<th>Evaluation Question</th>
			<th>Wt.</th>
			<th>Wtd. Score</th>
		</tr>
	</thead>

	<tbody id="tblQuestions">
		<?php
#			dd($questions, false);
			$i = 0;
			foreach ($questions as $row) {
				$detail = $row->details;
				
		?>
			<tr class='question_row' data-question_id="<?php echo $row->question_id ?>" data-detail_id="<?php echo $detail[$i]->detail_id ?>">
				<td class='td goals'><?php echo $row->goals?></td>
				<td class='td expectation'><?php echo $detail[$i]->expectation;?></td>
				<td class='td evaluator'><?php echo $detail[$i]->evaluator?></td>
				<td class='td question'><?php echo $row->question?></td>
				<td class='td weight'><?php echo $detail[$i]->weight?>%</td>
				<td class='td weight_score'><?php echo $detail[$i]->weight_score?>%</td>
			</tr>
		<?php
			}
		?>
		<tr>
			<td></td>
			<td></td>
			<td></td>
			<td colspan='3' align='right'>
			<input type="button" value="Add Question" class='btnclass pull-right' id='btnAddQuestion' onclick='addQuestion()'>
			</td>
		</tr>
	</tbody>
</table>

<br><br>

<table id="tblAddQuestion" style="display:none;border-collapse:collapse">
	<tr>
		<td>
			<label>Objective Goals</label>
		</td>
		<td colspan='5'>
			<textarea cols='106' id='txtObjective'></textarea>
		</td>
	</tr>
	<tr>
		<td> 
			<label> Expectation </label>
		</td> 
		<td colspan='5'> 
			<textarea cols='106' id='txtExpectation'></textarea> 
		</td>
	</tr>
	<tr>
		<td>
			<label>Evaluation Question</label>
		</td>
		<td colspan='5'>
			<textarea cols='106' id='txtEvaluation'></textarea>
		</td>
	</tr>
	<tr>
		<td>
			<label>Output Format</label>
		</td>
		<td>
			<textarea id='txtFormat'></textarea>
		</td> 
		<td>
			<label>Weight</label>
		</td>
		<td>
			<input type='number' id='txtWeight'>
		</td>
		<td>
			<label>Weight Score</label>
		</td>
		<td>
			<input type='number' id='txtWeightScore'>
		</td>
	</tr>
</table>
<button type="button" class='btnclass' id='btnAdd' style='float:right;display:none;' onclick="submitForm('addQuestions')">Submit</button>
<button type="button" class='btnclass' id='btnUpdate' style='float:right;display:none;' onclick="submitForm('updateQuestions')">Update</button>
</form>

<script type="text/javascript">
	function getQuestions(){
		$career = $("#slbCareer").val();
		window.location.href = $career;
	//	window.location.href = window.location.href.slice(1, -1) + $career;
	}

	function addQuestion(){
		$('#tblAddQuestion, #btnAdd').css('display','table');
		$('#btnAddQuestion').css('display', 'none');
	}

	function submitForm(submitAction){
		var data = $('#txtObjective').val()+"/"+$("#txtEvaluation").val()+"/"+$("#txtExpectation").val()+"/"+$("#txtFormat").val()+"/"+$("#txtWeight").val()+"/"+$("#txtWeightScore").val()+"/"+$("#jobType").val();

		if(submitAction == 'updateQuestions'){
			data += "/"+$('#detailId').val()+"/"+$('#questionId').val()
		}

		$.ajax({
			url:'../../'+submitAction+"/technical/"+data
		}).done(function(r){

		});
	}

	$(document).on('dblclick', '.question_row', function(){
		$('#txtObjective').val($(this).find('.goals').text());
		$('#txtExpectation').val($(this).find('.expectation').text());
		$('#txtEvaluation').val($(this).find('.question').text());
		$('#txtFormat').val($(this).find('.evaluator').text());
		$('#txtWeight').val($(this).find('.weight').text().slice(0,-1));
		$('#txtWeightScore').val($(this).find('.weight_score').text().slice(0,-1));
		$("#detailId").val($(this).data('detail_id'));
		$("#questionId").val($(this).data('question_id'));

		$('#tblAddQuestion, #btnUpdate').css('display','table');
		$('#btnAddQuestion').css('display', 'none');
	});
</script>