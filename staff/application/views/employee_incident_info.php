<style type="text/css">
	 .datatable td{
	 	text-align: center;
	 }
	 
	 a{
	 	text-decoration: underline;
	 }
</style>
<div id="employee_incident_info_form">
	<h2>HR HelpDesk</h2>
	<ul class="tabs">
		<li class="dbold tab-link current" id="new_tab" data-tab="tab-1">My Incidents</li>
	</ul>
	<hr>
	<form method="POST" action="<?php echo $this->config->base_url() ?>/hr_cs/employee_dashboard">
	<input type="hidden" name="EmpId" value="<?php echo $this->user->empID ?>">
	</form>
	<div id="tab-1" class="tab-content current">
		<table class="datatable">
	  	<thead>
		  	<tr>
			  	<th>Incident #</th>
		  		<th>Date Submitted</th>
		  		<th>Subject</th>
		  		<th>Send Message to HR</th>
		  		<th>Cancel Incident</th>	
		  	</tr>
	  	</thead>
	  	<?php foreach ($EmployeeDashboard as $key => $rep): ?>
	  		<tr>
	      		<td><?php echo $rep->cs_post_id; ?></td>
	      		<td><?php echo $rep->cs_post_date_submitted; ?></td>
	      		<td><?php echo $rep->cs_post_subject; ?></td>
	      		<td><a id="send_message_to_hr">Send Message to HR</a></td>
	      		<td><a id="cancel_incident">Cancel Incident</a></td>
	  		</tr>	
	  		<?php endforeach ?>     
	</table>
	</div>
</div>

<div id="send_message_to_hr_form">
	<table class="tableInfo">
	<tr>
		<td colspan="2"><h2>Send Email</h2></td>
	</tr>
	<tr>
		<td>From:</td>
		<td>sample</td>
	</tr>
	<tr>
		<td>To:</td>
		<td>hr.cebu@tatepublishing.net</td>
	</tr>
	<tr>
	<td>Subject:</td>
	<td><input type="text" name="" value="" placeholder=""></td>
	</tr>
	<tr>
		<td colspan="2"><textarea class="hidden tiny" style="height:200px; resize: none; font-size: left;"></textarea></td>
	</tr>
	<tr>
		<td align="left"><a id="back_to_home">Back</a></td>
		<td align="right"><input type="submit" name="" value="Send" class="btngreen"></td>
	</tr>
	</table>
</div>
	
<div id="cancel_incident_form"> 
	<table class="tableInfo">
		<tr>
			<td colspan="2"><h2>You are about tp cancel HR incident number 000003<br><small>Please tell us why (optional):</small></h2></td>			
		</tr>
		<tr>
			<td colspan="2"><textarea style="height:200px; resize: none; font-size: left;"></textarea></td>
		</tr>
		<tr>
			<td align="left"><a id="back_to_home">Back</a></td>
			<td align="right"><input type="button" name="" class="btngreen" value="Cancel Incident"></td>
		</tr>	
	</table>
</div>

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

	$(function(){
				
		$('#cancel_incident_form').hide();
		$('#cancel_incident').click(function() {
			
			$('#employee_incident_info_form').hide();
			$('#cancel_incident_form').show();
		});

		$('#send_message_to_hr_form').hide();
		$('#send_message_to_hr').click(function() {
			
			$('#employee_incident_info_form').hide();
			$('#send_message_to_hr_form').show();
		});

		$('a#back_to_home').click(function() {
			
			$('#send_message_to_hr_form').hide();
			$('#cancel_incident_form').hide();
			$('#employee_incident_info_form').show();
		});

		// ===== DISPLAY TOOLBAR IN TEXTAREA =====
		tinymce.init({
		selector: "textarea.tiny",	
		menubar : false,
		toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image"
		});	
	});

</script>