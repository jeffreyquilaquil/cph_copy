<?php  
if($this->user->access != "full"){
			header("location: ".$this->config->base_url());
		}
?>

<style type="text/css">

	th{
	 	background-color: #CCCCCC;
	 }
	 td{
	 	text-align: center;
	 }

	 a.other_links:link, a.other_links:visited, a.other_links:hover, a.other_links:active{
	 	color: black;
	 	text-decoration: none;
	 	background-color: #CCCCCC; 
	 	padding: 8px 20px 8px 20px;	 
	 }


	 .settings-bottom{
	 	margin-bottom: 5px;
	 }
 
	 .options-right{
	 	float: right; 
	 	margin-top: 12.5px;
	 }

	.dbold{
		font-weight: bold;
	}

</style>

<h2>HR HelpDesk</h2>
<ul class="tabs">
	<li class="dbold tab-link current" id="new_tab" data-tab="tab-1">New <font color="darkred" style="font-weight: bold;">( <?php echo count($NewIncident)?> )</font></li>
	<li class="dbold tab-link" id="active_tab" data-tab="tab-2">Active <font color="darkred" style="font-weight: bold;">( <?php echo count($ActiveIncident)?> )</font></li>
	<li class="dbold tab-link" data-tab="tab-3">Resolved</li>
	<li class="dbold tab-link" data-tab="tab-4">Cancelled</li>

	<div class=" dbold options-right">
		<a class="other_links" id="active_hide" href="<?php echo $this->config->base_url(); ?>hr_cs/hr_custom_satisfaction">HR Customer CSatResults</a>
		<a class="other_links" href="#">Generate Reports</a>	
	</div>
</ul>

<hr/>

<!-- ====== NEW TAB ====== --> 
<div id="tab-1" class="tab-content current">

<br>

	<table class="datatable">
	  	<thead>
		  	<tr>
			  	<th>Incident #</th>
		  		<th>Subject</th>	
		  		<th>Customer</th>
		  		<th>Priority</th>	
		  		<th>Date Submitted</th>
		  		
		  		
		  	</tr>
	  	</thead>

	  	<?php foreach ($NewIncident as $key => $value) { ?>

	  		<tr>
	      		<td class="td_hover">
                    <a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $value->cs_post_id; ?>/new" class="iframe" data-balloon-length="large" data-balloon="<?php echo strip_tags($value->cs_msg_text) ?>" data-balloon-pos="right"><?php echo "$value->cs_post_id";?></a>		
	      		</td>
	      		<td><?php echo $value->cs_post_subject ?></td>
	      		<td><?php echo $value->fname; ?></td>
	      		<td><?php echo $value->cs_post_urgency; ?></td>
	      		<td><?php  echo $value->cs_post_date_submitted; ?></td>      		
	  		</tr>	     

	  	<?php } ?> 		
	    
	</table>

      <br>

      <h2>SETTINGS</h2>

      <br>
	
	<div class=" dbold settings-bottom">
		<a class="other_links" href="#"><b>Add Categories</b></a>
		<a class="other_links" href="#"><b>Edit HR User Permissions</b></a>
		<a class="other_links" href="#"><b>Edit Message Templates</b></a>
		<a class="other_links" href="#"><b>Add a Redirection Department</b></a>
	</div>    	
</div>

<!-- ===== ACTIVE TAB ===== -->
<div id="tab-2" class="tab-content"> 

<br>

	<table id="new" class="datatable">
		<thead>
			<tr>
				<th>Incident #</th>
				<th>Subject</th>
				<th>Customer</th>
				<th>Priority</th>
				<th>Last Update</th>
							
			</tr>
		</thead>
			<?php foreach ($ActiveIncident as $active_key => $active_val) { ?>
				<tr>
					<td class="td_hover">
	      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $active_val->cs_post_id; ?>/active" class="iframe"><?php echo $active_val->cs_post_id;?></a>
	      			</td>
					<td><?php echo $active_val->cs_post_subject; ?></td>
					<td><?php echo $active_val->fname." ".$active_val->lname; ?></td>
					<td><?php echo $active_val->cs_post_urgency; ?></td>
					<td><?php echo $active_val->last_update; ?></td>
									
				</tr>
			<?php } ?>     
	</table>

      <br>

      <h2>SETTINGS</h2><br>
	
	<div class=" dbold settings-bottom">
		<a class="other_links" href="#">Add Categories</a>
  		<a class="other_links" href="#">Edit HR User Permissions</a>
  		<a class="other_links" href="#">Edit Message Templates</a>
	</div> 
</div>

<!-- ===== RESOLVE TAB ===== -->
<div id="tab-3" class="tab-content">

<br>

	<table id="new" class="datatable">
		<thead>
			<tr>
				<th>Incident #</th>
				<th>Subject</th>
				<th>Customer</th>
				<th>Priority</th>
				<th>Last Update</th>
							
			</tr>
		</thead>
			<?php foreach ($ResolveIncident as $active_key => $resolve_val) { ?>
				<tr>
					<td class="td_hover">
	      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $resolve_val->cs_post_id; ?>/resolved" class="iframe"><?php echo $resolve_val->cs_post_id;?></a>
	      			</td>
					<td><?php echo $resolve_val->cs_post_subject; ?></td>
					<td><?php echo $resolve_val->fname." ".$resolve_val->lname; ?></td>
					<td><?php echo $resolve_val->cs_post_urgency; ?></td>
					<td><?php echo $resolve_val->last_update; ?></td>
									
				</tr>
			<?php } ?>     
	</table>

      <br>

</div>
<!--===== CANCELLED TAB ===== -->
<div id="tab-4" class="tab-content">


<br>

	<table id="new" class="datatable">
		<thead>
			<tr>
				<th>Incident #</th>
				<th>Subject</th>
				<th>Customer</th>
				<th>Priority</th>
				<th>Last Update</th>
							
			</tr>
		</thead>
			<?php foreach ($CancelIncident as $active_key => $resolve_val) { ?>
				<tr>
					<td class="td_hover">
	      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $resolve_val->cs_post_id; ?>/cinc" class="iframe"><?php echo $resolve_val->cs_post_id;?></a>
	      			</td>
					<td><?php echo $resolve_val->cs_post_subject; ?></td>
					<td><?php echo $resolve_val->fname." ".$resolve_val->lname; ?></td>
					<td><?php echo $resolve_val->cs_post_urgency; ?></td>
					<td><?php echo $resolve_val->last_update; ?></td>
									
				</tr>
			<?php } ?>     
	</table>

      <br>



</div>

<script type="text/javascript">
	
$(document).ready(function(){

	$("#active_hide").show();

	// ===== SHOW FOUND ANSWER FORM =====
	$("#active_tab").click(function(){

	$("#active_hide").hide();

	});

	$("#new_tab").click(function(){

	$("#active_hide").show();

	});

});

</script>
