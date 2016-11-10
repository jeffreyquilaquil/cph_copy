<h2>Review Questions</h2>
<div style="float:right">
	<select id="slbCareer">
		<?php 
			$careerType = '';
			if(is_numeric($this->uri->segment(3))){
				$careerType = $this->uri->segment(3);
			}

			foreach($jobDesc as $job){
				$selected = ($careerType == $job->job_type ? 'selected' : '');
				echo '<option value="'.$job->job_type.'" '.$selected.'>'.$job->title.'['.$job->rowCount.']</option>';
			}
		 ?>
	</select>&nbsp;
	<button class="btnclass" onclick="getQuestions()">Go</button>
</div>
<form method="POST" action="">
<?php 
	$this->textM->renderTable($question['headers'],'',$question,true,true);
 ?>
 <input type="hidden" name="questionId" id="questionId">
</form>
<script type="text/javascript">
	function getQuestions(){
		$career = $("#slbCareer").val();
		window.location.href = $career;
	}

	$(document).on('click','.btnApprove', function(){
		displaypleasewait();
		$('#questionId').val($(this).data('id'));
		$('form').submit();
	});
</script>