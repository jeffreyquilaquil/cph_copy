<?php
	$careerList = ['Applications Developer', 'IT Specialist'];
?>
<div style="float:right">
	<select id='slbCareer'>
		<?php
			foreach ($careerList as $key => $value) {
				echo "<option value='".$key."'>".$value."</option>";
			}
		?>
	</select>
	<button onclick="getTechnicalQuestions()">Go</button>
</div>

<script type="text/javascript">
	function getTechnicalQuestions(){
		$career = $('#slbCareer').val();

		$.ajax({
			url:"technicalQuestions/"+$career
		}).done(function(r){
			$('#technicalJob').html(r);
		})
	}
</script>

<div id='technicalJob'></div>