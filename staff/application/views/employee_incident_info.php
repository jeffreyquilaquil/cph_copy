<style type="text/css">
	 .datatable td{
	 	text-align: center;
	 }
	 
	 a{
	 	text-decoration: underline;
	 }
</style>
<div>
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
		  		<th>Status</th>
		  		<th>Cancel Incident</th>	
		  	</tr>
	  	</thead>
	  	<?php foreach ($EmployeeDashboard as $key => $rep): ?>
	  		<tr>
	      		<td><a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $rep->cs_post_id; ?>/emp" class="iframe"><?php echo $rep->cs_post_id; ?></a></td>
	      		<td><?php echo $rep->cs_post_date_submitted; ?></td>
	      		<td><?php echo $rep->cs_post_subject; ?></td>
	      		<td><?php 
	      		if ($rep->cs_post_status == 0) {
	      			echo "Open";
	      		}elseif($rep->cs_post_status == 1){
	      			echo "Assign";
	      		}elseif($rep->cs_post_status == 2){
	      			echo "Hold";
	      		}elseif($rep->cs_post_status == 3){
	      			echo "Resolve";
	      		}elseif($rep->cs_post_status == 4){
	      			echo "Closed";
	      		}

	      		 ?></td>		
	      		<td><a href="<?php echo $this->config->base_url(); ?>hr_cs/employee_incident_info_events/<?php echo $rep->cs_post_id; ?>/cancel" class="iframe">Cancel Incident</a></td>
	  		</tr>	
	  		<?php endforeach ?>     
	</table>
	</div>
</div>

<script type="text/javascript">
	
</script>
