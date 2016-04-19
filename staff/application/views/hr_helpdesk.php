<style type="text/css">
	th{
	 	background-color: #CCCCCC;
	 }
	 td{
	 	text-align: center;
	 }

	 a{
		text-decoration: none;
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

	.td_hover {
	    position: relative;
	    display: block;
	    
	}

	.td_hover .show_details {
	    visibility: hidden;
	    width: 200px;
	    background-color: black;
	    color: #fff;
	    text-align: center;
	    border-radius: 6px;
	    padding: 20px;
	    position: absolute;
	    z-index: 1;
	    top: -5px;
	    left: 110%;
	}


	.td_hover:hover .show_details {
	    visibility: visible;
	}
	.dbold{
		font-weight: bold;
	}

</style>

<h2>HR HelpDesk</h2>
<ul class="tabs">
	<li class="dbold tab-link current" id="new_tab" data-tab="tab-1">New <font color="darkred" style="font-weight: bold;">( <?php echo count($HrHelpDesk)?> )</font></li>
	<li class="dbold tab-link" id="active_tab" data-tab="tab-2">Active</li>
	<li class="dbold tab-link" data-tab="tab-3">Resolved</li>
	<li class="dbold tab-link" data-tab="tab-4">Cancelled</li>

	<div class=" dbold options-right">
		<a class="other_links" id="activ_hide" href="<?php echo $this->config->base_url(); ?>hr_cs/hr_custom_satisfaction">HR Customer CSatResults</a>
		<a class="other_links" href="#">Generate Reports</a>	
		
	</div>
	
</ul>


<hr/>
<div id="tab-1" class="tab-content current">  <!--========= NEW TAB ==============-->
  <br>

      <table id="new" class="datatable">
      	<thead>
      		<th>Incident #</th>
      		<th>Customer</th>
      		<th>Date Submitted</th>
      		<th>Subject</th>
      		<th>Priority</th>
      	</thead>

      	<?php foreach ($HrHelpDesk as $key => $value) { ?>
      		<tr>
	      		<td class="td_hover">
	      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident?id=<?php echo $value->cs_post_id; ?>" class="iframe">
	      				<?php echo $value->cs_post_id; ?> 
	      			</a>
      			<div class="show_details">
      				Details of the incident
      			</div>
	      		</td>
	      		<td><?php echo $value->fname; ?></td>
	      		<td><?php  echo $value->cs_post_date_submitted; ?></td>
	      		<td><?php echo $value->cs_post_subject ?></td>
	      		<td><?php echo $value->cs_post_urgency; ?></td>
      		</tr>	     

      		<?php } ?> 		
  
      </table>


      <br>
      <h2>SETTINGS</h2><br>
	
	<div class=" dbold settings-bottom">
		<a class="other_links" href="#"><b>Add Categories</b></a>
  		<a class="other_links" href="#"><b>Edit HR User Permissions</b></a>
  		<a class="other_links" href="#"><b>Edit Message Templates</b></a>
  		<a class="other_links" href="#"><b>Add a Redirection Department</b></a>
	</div>    	
</div>

<div id="tab-2" class="tab-content"> <!--=========ACTIVE TAB ==============-->

  <br>

      <table id="new" class="datatable">
      	<thead>
      		<th>Incident #</th>
      		<th>Customer</th>
      		<th>Date Submitted</th>
      		<th>Category</th>
      		<th>Subject</th>
      		<th>Priority</th>
      		<th>Assigned SLA</th>
      		<th>Owner</th>
      		<th>Last Update</th>
      		<th>Give Update</th>
      	</thead>

      	
      		<tr>
	      		<td class="td_hover">
	      			Insedent #
      			<div class="show_details">
      				Details of the incident
      			</div>
	      		</td>
	      		<td>fname</td>
	      		<td>date submitid</td>
	      		<td>ctegory</td>
	      		<td>subject</td>
	      		<td>priority</td>
	      		<td>assigned SAL</td>
	      		<td>Owner</td>
	      		<td>Last Update</td>
	      		<td>Give Update</td>
      		</tr>	     
	
  
      </table>


      <br>
      <h2>SETTINGS</h2><br>
	
	<div class=" dbold settings-bottom">
		<a class="other_links" href="#">Add Categories</a>
  		<a class="other_links" href="#">Edit HR User Permissions</a>
  		<a class="other_links" href="#">Edit Message Templates</a>
	</div> 

</div>

<div id="tab-3" class="tab-content">
C
</div>

<div id="tab-4" class="tab-content">

D
</div>

<script type="text/javascript">
	
	$(document).ready(function(){

	$("#activ_hide").show();

	// ===== SHOW FOUND ANSWER FORM =====
	$("#active_tab").click(function(){

	$("#activ_hide").hide();
	
	});
	$("#new_tab").click(function(){

	$("#activ_hide").show();
	
	});


	});
</script>
