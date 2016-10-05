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
	#tblAddQuestion td{
		height: 40px;
	}
	textarea{
		width:95%;
		height: 95%;
	}
	input[type='number']{
		width: 90%;
	}
</style>

<p></p>

<div style="float:right">
	<select id="slbCareer">
		<?php

			foreach ($positions as $value) {
				$selected = ($value->posID == $this->uri->segment(4) ? 'selected' : '');
				echo "<option value='".$value->posID."' {$selected}>".$value->title."</option>";
			}
		?>
	</select>

	<button class='btnclass' onclick="getQuestions()">Go</button>
	<input type="button" value="Add Question" class='btnclass pull-right' id='btnAddQuestion' onclick='addQuestion()'>
</div>


<?php
	$csrf = array(
		'name' => $this->security->get_csrf_token_name(),
		'hash' => $this->security->get_csrf_hash()
		);

echo form_open("evaluations/addQuestions", array('name'=> 'frmQuestion', 'id'=>'frmQuestion', 'onsubmit'=> ''));
$hiddenArray = array(
	$csrf['name']=>$csrf['hash'],
	'posID'=>$this->uri->segment(4),
	'questionId'=>'',
	'detailId'=>'',
	'questionType'=>'technical',

	);
echo form_hidden($hiddenArray);
echo validation_errors("<span class='error'","</span>");
?>
<table id="tblAddQuestion" style="display:none;border-collapse:collapse">
	<tr>
		<td width="20%">
			<label>Objective Goals</label>
		</td>
		<td colspan='5'>
			<?php echo form_textarea(array('id'=>'txtObjective', 'name'=>'txtObjective', 'required'=> 'required')) ?>
		</td>
	</tr>
	<tr>
		<td> 
			<label> Expectation </label>
		</td> 
		<td colspan='5' style='padding: 0px'> 
			<?php echo form_textarea(array('id'=>'txtExpectation', 'name'=>'txtExpectation', 'required'=> 'required')) ?>
		</td>
	</tr>
	<tr>
		<td>
			<label>Evaluation Question</label>
		</td>
		<td colspan='5'>
			<?php echo form_textarea(array('id'=>'txtEvaluation', 'name'=>'txtEvaluation', 'required'=> 'required')) ?>
		</td>
	</tr>
	<tr>
		<td>
			<label>Output Format</label>
		</td>
		<td colspan='5'>
		<?php echo form_textarea(array('id'=>'txtFormat', 'name'=>'txtFormat', 'required'=> 'required')); ?>
		</td>
	</tr>
	<tr>

		<td>
			
		</td> 
		<td>
			<label>Weight</label>
		</td>
		<td>
			<?php echo form_input(array('type'=>'number', 'id'=>'txtWeight', 'name'=>'txtWeight', 'required'=> 'required', 'maxlength'=>2, 'min'=> 1, 'max'=>99)) ?>
		</td>
		<td width="10%">
			<label>Weight Score</label>
		</td>
		<td>
			<?php echo form_input(array('type'=>'number', 'id'=>'txtWeightScore', 'name'=>'txtWeightScore', 'required'=> 'required', 'maxlength'=>2, 'min'=> 1, 'max'=>99))?>
		</td>
	</tr>
</table>
<button type="button" class='btnclass' id='btnSubmit' style='float:right;display:none;' onclick='submitForm("addQuestions")'">Submit</button>
<?php echo form_close(); ?>
<br><br>

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
	</tbody>
</table>

<br><br>
<script type="text/javascript">
	function getQuestions(){
		$career = $("#slbCareer").val();
		window.location.href = $career;
	//	window.location.href = window.location.href.slice(1, -1) + $career;
	}

	function addQuestion(){
		$('#tblAddQuestion, #btnSubmit').css('display','table');
		$('#btnAddQuestion').css('display', 'none');
	}

	function submitForm(submitAction){
		// Alert if form fields are empty
		check = true;
		$("#tblAddQuestion textarea, #tblAddQuestion input").each(function(){
			if($(this).val() == ""){
				check = false;
				alert("All fields must not be empty");
				return false;
			}
		});
		
		if(check){
			var data = "txtObjective="+$('#txtObjective').val()+
			"&txtEvaluation="+$("#txtEvaluation").val()+
			"&txtExpectation="+$("#txtExpectation").val()+
			"&txtFormat="+$("#txtFormat").val()+
			"&txtWeight="+$("#txtWeight").val()+
			"&txtWeightScore="+$("#txtWeightScore").val()+
			"&posID="+$("input[name='posID']").val()+
			'&questionType=technical';

			if(submitAction == 'updateQuestions'){
				data += "&detailsId="+$("input[name='detailId']").val()+"&questionId="+$('input[name="questionId"]').val()
			}

			console.log(data);

			$.ajax({
				type:'POST',
				data:data,
				url:'../../'+submitAction,
				dataType:'json'
			}).done(function(r){
				alert("The question list has been updated.");
				if(submitAction == 'addQuestions'){
					var row = setRow(r);
					// Add the created data into the questions table
					$(row).appendTo('#tblQuestions').fadeIn('slow');
				}

				if(submitAction == 'updateQuestions'){
					var row = setRow(r);
					// replace current row with the updated row.
					$(".question_row[data-question_id='"+r[0].question_id+"']").replaceWith(row).fadeIn('slow');
				}
				$("#tblAddQuestion textarea, #tblAddQuestion input").val('');
			});
		 }
	}
	
	// Pass data from table row to the form fields.
	$(document).on('dblclick', '.question_row', function(){
		$('#btnSubmit').attr('onclick','submitForm("updateQuestions")');
		$('#btnSubmit').text('Update');

		$('#txtObjective').val($(this).find('.goals').text());
		$('#txtExpectation').val($(this).find('.expectation').text());
		$('#txtEvaluation').val($(this).find('.question').text());
		$('#txtFormat').val($(this).find('.evaluator').text());
		$('#txtWeight').val($(this).find('.weight').text().slice(0,-1));
		$('#txtWeightScore').val($(this).find('.weight_score').text().slice(0,-1));
		$("input[name='detailId']").val($(this).data('detail_id'));
		$('input[name="questionId"]').val($(this).data('question_id'));

		$('#tblAddQuestion, #btnSubmit').css('display','table');
		$('#btnAddQuestion').css('display', 'none');
	});

	// If input field is greater than 2, remove last character
	$(document).on('keyup','input[type="number"]',function(){
		var $field = $(this),
		val = this.value;

		if(val.length > 2) {
			val = val.slice(0,2);
			$field.val(val);
		}
	});

	// placeholder for the passed data from the controller.
	function setRow(r){
		var row = '<tr class="question_row" data-question_id="'+r[0].question_id+'" data-detail_id="'+r[1][0].detail_id+'" style="background:rgb(93,197,240);">'+
			'<td class="td goals">'+r[0].goals+'</td>'+
			'<td class="td expectation">'+r[1][0].expectation+'</td>'+
			'<td class="td evaluator">'+r[1][0].evaluator+'</td>'+
			'<td class="td question">'+r[0].question+'</td>'+
			'<td class="td weight">'+r[1][0].weight+'%</td>'+
			'<td class="td weight_score">'+r[1][0].weight_score+'%</td></tr>';
		return row;
	}
</script>