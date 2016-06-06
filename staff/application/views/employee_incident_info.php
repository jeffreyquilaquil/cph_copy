<style type="text/css">
	 .datatable td{
	 	text-align: center;
	 }
	 
	 a{
	 	text-decoration: underline;
	 }
	 #nummark {
    	background-color:Darkred;
    	text-align:center;
    	padding:5px;
   	 	width:400px;
    	float:right;
    	-moz-border-radius: 50px;
        -webkit-border-radius: 50px;
         border-radius: 50px;
	}
	 
</style>

<?php 
if ($this->user->empID != $this->uri->segment(3)) {
	header("location: ".$this->config->base_url());
}
foreach ($reamark_status as $key_n_mark => $num_mark){} ?>

<div>
	<h2>HR/Accounting HelpDesk</h2>
	<?php if($num_mark->num_rate != 0){ ?>
	<div id="nummark">
		<h3 style="color: white;">You have <b><?php echo $num_mark->num_rate ?></b> Incident that already resolved please put a remark</h3>
	</div>
	<br>
	<?php } ?>
	
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
		  		<th>Owner</th>
		  	</tr>
	  	</thead>
	  	<!-- array show incident #, date submitted, subject and status -->
	  	<?php foreach ($EmployeeDashboard as $key => $rep): ?>
	  		<tr>
				<td>
				<?php if($rep->cs_post_status == 0 || $rep->cs_post_status == 1){?>
				<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $rep->cs_post_id; ?>/emp/open/<?php echo $rep->cs_post_empID_fk; ?>" class="iframe"><?php echo $rep->cs_post_id; ?></a>
				<?php }elseif($rep->cs_post_status == 3){

					if($rep->rate_status == 0){
						echo "&#10067;";
					} ?>
				<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $rep->cs_post_id; ?>/emp/resolved/<?php echo $rep->cs_post_empID_fk; ?>" class="iframe"><?php echo $rep->cs_post_id; ?></a>

				<?php }elseif($rep->cs_post_status == 4 || $rep->cs_post_status == 5 ){?>
				<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $rep->cs_post_id; ?>/emp/closed/<?php echo $rep->cs_post_empID_fk; ?>" class="iframe"><?php echo $rep->cs_post_id; ?></a>
				<?php } ?>
				</td>
	      		<td><?php echo date_format(date_create($rep->cs_post_date_submitted), 'F d, Y G:ia'); ?></td>
	      		<td><?php echo $rep->cs_post_subject; ?></td>
	      		<td>
		      		<?php 

		      		if ($rep->cs_post_status == 0) {
		      			echo "Open";
		      		}elseif($rep->cs_post_status == 1){
		      			echo "Assign";
		      		}elseif($rep->cs_post_status == 2){
		      			echo "Hold";
		      		}elseif($rep->cs_post_status == 3){
		      			echo "Resolved";
		      		}elseif($rep->cs_post_status == 4){
		      			echo "Closed";
		      		}elseif($rep->cs_post_status == 5){
		      			echo "Closed";
		      		}

		      		?>
		      	</td>	
		      	<td><?php echo $rep->hr_own_empUSER; ?></td>
	  		</tr>	
	  		<?php endforeach ?>     
	</table>
	</div>
</div>
