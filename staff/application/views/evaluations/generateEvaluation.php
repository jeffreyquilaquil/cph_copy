<style type="text/css">
	tr{
		height: 40px;
	}
	.hr{
		border-bottom: 2px solid #CCC;
	}
	.hr:hover{
		background: #CCC;
		border: 1px solid #CCC;
	}
	.htBot{

	}
</style>
<?php 
#	dd($evaluator);
 ?>
<h1>Generate Evaluation Form for <?php echo $info->fname." ".$info->lname; ?></h1>
<hr>
<form action="saveEvaluationDate" method="POST">
<input type="hidden" name="empID" id="empID" value="<?php echo $this->uri->segment(3) ?>">
<input type="hidden" name="curDate" id="curDate" value="<?php echo date('F d, Y') ?>">
<table style="border-collapse: collapse;">
 	<tr class='hr'>
 		<td width="50%"><b>When will this evaluation be discussed to the employee?</b></td>
 		<td>
 			<input id="evalDate" name="evalDate" type="text" class="forminput datepick" placeholder="Month day, year">
 		</td>
 	</tr>
 	<tr class='hr'>
 		<td width="50#"><b>Who is evaluating the employee</b></td>
 		<td>
 			<select class="forminput" id="evaluator" name="evaluator">
 				<?php 	$posTitle = '';
 					foreach ($evaluator as $evalInfo) {
 						$selected ='';
 					
 						if($info->supervisor == $evalInfo->empID){
 							$selected = 'selected';
 							$posTitle = $evalInfo->title;
 						}
 						echo '<option value="'.$evalInfo->empID.'" posTitle="'.$evalInfo->title.'" '.$selected.'>'.$evalInfo->name.'</option>';
 					}
 				 ?>
 			</select>
 		</td>
 	</tr>
 	<tr class='hr'>
 		<td width="50%"><b>What is the position title of the evaluator</b></td>
 		<td width="50%"><input type="text" id="evalTitle" name="evalTitle" class="forminput" disabled value="<?php echo $posTitle; ?>" ></td>
 	</tr>
 	<tr>
 		<td colspan="2" align="center"><input type="button" value="Notify Employee to Enter Self-Rating" onclick="submitEval()"></td>
 	</tr>
 </table>
</form>
 <script type="text/javascript">
 $(function(){
 
 	$('#evaluator').change(function(){
 		var selected = $(this).find('option:selected');
 		$('#evalTitle').val($(selected).attr('posTitle'));
 	});

 });


function submitEval(){
	var nullCheck = true;
	var dateCheck = true;
	if($('#evalDate').val() == ""){
		nullCheck = false;
		alert("all fields must not be empty");
		return false;
	}

	if($('#evalDate').val() < $('#curDate').val()){
		alert('Evaluation date must be greater or equal than the current date.');
		dateCheck = false;
	}

	if(nullCheck && dateCheck){
		var data = "evalDate="+$('#evalDate').val()+
			"&evaluator="+$('#evaluator').val()+
			"&evaluatorName="+$('#evaluator option:selected').text()+
			"&empID="+$('#empID').val();

		displaypleasewait();
		$.ajax({
			type:'POST',
			url:'../../saveEvaluationDate',
			data:data,
		}).done(function(r){
			alert("Performance Evaluation has been generated");
			parent.$.colorbox.close();
		}).error(function(r){
		})
	}
}
 </script>