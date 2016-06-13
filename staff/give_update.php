<div id="give_update_form">
	<table>
		<tr>
			<td>
				<h2>What Update You Want To Give?</h2>
				<span id="reg_update" class="resol_link">Regular Update</span>
				<span id="request" class="resol_link">Request/ Follow Up Required Information</span>
				<span id="resovle" class="resol_link">Resolve Incident</span>
			</td>
		</tr>
	</table>
</div>

<div id="reg_update_form">
	<table class="tableInfo">
		<tr>
			<td>
				<h2>HR Incident Number 000003</h2>
			</td>
		</tr>
		<tr>
			<td>Please write below you're the update you want to give to the customer:</td>
			
			<br>

			<textarea id="regular_update_msg" name="regular_update_msg" style="height:200px; resize: none;"></textarea>

			<br>

			<input id="found_answer_submit" type="submit" class="btn_ans" value="Resolve Incident">
		</tr>
	</table>
</div>

<div id="resovle_form">
	<table class="tableInfo">
		<tr>
			<td>
				<h2>HR Incident Number 000003</h2>

				<br>
				
				<small>You have owned responsibility for incident number 000003</small>
			</td>
		</tr>
		<tr>

			<br>
			<br>
			
			<div style="background: #CCCCCC; font-weight: bold; width: 100%; padding: 5px 0px 5px 5px">Resolution Options</div>
			
			<br>
			
			<span id="found_answer" class="resol_link">The answer can be found in employee.tatepublishing.net</span>
			
			<br>
			<br>
			
			<span id="custom_answer" class="resol_link">Send custom resolution response</span>
			
			<br>
			<br>
			
			<span id="not_found_answer" class="resol_link">This is not an HR inquiry. Redirect to another department.</span>
			
			<br>
			<br>

		</tr>
	</table>
</div>

<script type="text/javascript">

	$("#reg_update_form").hide();
	$("#resovle_form").hide();
	$("#give_update_form").show();

	$("#reg_update").click(function() {

		$("#give_update_form").hide();
		$("#reg_update_form").show();

	});

	$("#resovle").click(function() {

		$("#give_update_form").hide();
		$("#resovle_form").show();

	});
	
</script>