<?php  
if($this->user->access == "exec"){
			header("location: ".$this->config->base_url());
}else if($this->user->access == "inventory admin"){
	header("location: ".$this->config->base_url());
}else if($this->user->access == "med_person"){
	header("location: ".$this->config->base_url());
}else if($this->user->access == ""){
	header("location: ".$this->config->base_url());
}
?>

<style type="text/css">

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

	ul.settings_tabs{
			margin: 0px;
			padding: 0px;
			list-style: none;

	}
	ul.settings_tabs li{
		background-color: #CCCCCC;
		color: black;
		display: inline-block;
		padding: 8px 20px 8px 20px;	
		font-weight: bold;
		cursor: pointer;
	}

	ul.settings_tabs li.curr{
		background-color: #FFFFFF;
		border: solid 1.5px #ddd;
		
	}

	.tab-cont{
		display: none;
		padding: 15px 0px 15px 0px;
	}

	.tab-cont.curr{
		display: inherit;
	}
	.tab_indicator{
		font-weight: bold; color: darkred;
	}
 
</style>
	
 	<?php
	  	$NewIncident = array();
	  	$ActiveIncident = array();
	  	$ResolveIncident = array();
	  	$CancelIncident = array();
	  	$title = '';

	  	//dd($this->access);

	  	// Title & access in new tab
	  	
		
		if($this->access->accessHR == true ){
	  		$NewIncident = $NewIncidentHR;
	  		$ActiveIncident = $ActiveIncidentHR;
	  		$ResolveIncident = $ResolveIncidentHR;
	  		$CancelIncident = $CancelIncidentHR;
	  		$title = "HR HelpDesk";
	  	} 
	  	if($this->access->accessFinance == true ){
	  		$NewIncident = $NewIncidentAcc;
	  		$ActiveIncident = $ActiveIncidentAcc;
	  		$ResolveIncident = $ResolveIncidentAcc;
	  		$CancelIncident = $CancelIncidentAcc;
	  		$title = "Accounting HelpDesk";
	  	}
	  	if($this->access->accessFull == true){
			$NewIncident = $NewIncidentFull;
			$ActiveIncident = $ActiveIncidentFull;
			$ResolveIncident = $ResolveIncidentFull;
			$CancelIncident = $CancelIncidentFull;
			$title = "Admin HelpDesk";
		} 

	?>

<!-- hr help desk tabs -->
<ul class="tabs">

	<h2><?php echo $title; ?></h2>

	<li class="dbold tab-link current" data-tab="tab-0">My Ticket <span class="tab_indicator"><?php echo (count($MyTicket) > '0') ? '('.count($MyTicket).')' : '';  ?></span></li>
	<li class="dbold tab-link" data-tab="tab-1">New <span class="tab_indicator"><?php echo (count($NewIncident) > '0') ? '('.count($NewIncident).')' : '';  ?></span></li>
	<li class="dbold tab-link" data-tab="tab-2">Active <span class="tab_indicator"><?php echo (count($ActiveIncident) > '0') ? '('.count($ActiveIncident).')' : ''; ?></span></li>
	<li class="dbold tab-link" data-tab="tab-3">Resolved <span class="tab_indicator"><?php echo (count($ResolveIncident) > '0') ? '('.count($ResolveIncident).')' : ''; ?></span></li>
	<li class="dbold tab-link" data-tab="tab-4">Closed <span class="tab_indicator"><?php echo (count($CancelIncident) > '0') ? '('.count($CancelIncident).')' : ''; ?></span></li>

	<div class=" dbold options-right">
		<a class="other_links" id="active_hide" href="<?php echo $this->config->base_url(); ?>hr_cs/hr_custom_satisfaction">HR/Accounting Customer CSatResults</a>
		<a class="other_links" href="<?php echo $this->config->base_url(); ?>hr_cs/hr_generate_reports">Generate Reports</a>	
	</div>
</ul>

<hr/>

<!-- my ticket tab -->
<div id="tab-0" class="tab-content current">
	<table class="datatable">
		<thead>
			<tr>
				<th>Incident #</th>
				<th>Subject</th>
				<th>Customer</th>
				<th>Priority</th>
				<th>Last Update</th>
				<th>Due Date</th>
				<th>Status</th>
				<th>Mark</th>
				
				<th>Extend Due Date</th>
				<th>Reassign</th>
			</tr>
		</thead>

			<?php 


			foreach ($MyTicket as $myticket_key => $myticket) { ?>

			<tr>
				<td class="td_hover">
				<?php if (date('Y-m-d') >= $myticket->due_date AND $myticket->cs_post_status < 3 ) {
					echo "<span style='color:red;'>&#9873;</span>";
				}?>

      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $myticket->cs_post_id; ?>/active/<?php echo $myticket->cs_post_empID_fk; ?>" class="iframe"><?php echo sprintf("%'.06d", $myticket->cs_post_id); ?></a>
      			</td>
				<td><?php echo $myticket->cs_post_subject; ?></td>
				<td><?php echo $myticket->fname." ".$myticket->lname; ?></td>
				<td>
					<?php if($myticket->cs_post_urgency=='Urgent'){ 
					   			echo "<div class='urgent'>$myticket->cs_post_urgency</div>";
							}elseif($myticket->cs_post_urgency=='Need Attention'){
								echo "<div class='need_attention'>$myticket->cs_post_urgency</div>";
							}elseif($myticket->cs_post_urgency=='Not Urgent'){
								echo "<div class='not_urgent'>$myticket->cs_post_urgency</div>";
							}
					?>
				</td>
				<td><?php echo date_format(date_create($myticket->last_update), 'F d, Y G:ia'); ?></td>
				<td><?php echo ($myticket->due_date == '0000-00-00 00:00:00') ? '--' : date('F d, Y G:ia', strtotime($myticket->due_date)); ?></td>
				
				<?php 
					$status = ['Open', 'Active', 'Hold', 'Resolved','Closed','Closed'];
					echo '<td>'.$status[ $myticket->cs_post_status ].'</td>';
				?>	

				<?php 
				if( isset($myticket->remark) AND !empty($myticket->remark) ){
					echo "<td>". $myticket->remark."</td>";
				}else{
					echo "<td>Unrated</td>";
				}
				?>
				
				
				<td>
				<?php if($myticket->cs_post_status == 0 || $myticket->cs_post_status == 1){ ?>
					<a style="cursor: pointer;" id="extend_date<?php echo $myticket->cs_post_id; ?>">Extend Date</a>
				<?php }else { ?>
						....
				<?php } ?>
					<span id="extend_date_form<?php echo $myticket->cs_post_id; ?>">
						<br>
						<small>Plase select number of additional days:</small>
						<input type="number" id="add_days_<?php echo $myticket->cs_post_id; ?>" min="1" max="5">
						<input type="submit" id="btn_add_days_<?php echo $myticket->cs_post_id; ?>" class="add_days_btn btngreen" data-btn="<?php echo $myticket->cs_post_id; ?>" value="Submit">
						<!-- <input type="number" id="add_days<?php echo $myticket->cs_post_id; ?>" min="1" max="5">
						<input type="hidden" id="inci_id<?php echo $myticket->cs_post_id; ?>" value="<?php echo $myticket->cs_post_id; ?>">
						<input type="hidden" id="due_D<?php echo $myticket->cs_post_id; ?>" value="<?php echo $myticket->due_date; ?>">
						<input type="submit" class="add_days_btn btngreen" data-btn="<?php echo $myticket->cs_post_id; ?>" value="Submit"> -->
					</span>
				</td>
				<td>
				<?php if($myticket->cs_post_status == 0 || $myticket->cs_post_status == 1){?>
					<a id="reassign<?php echo $myticket->cs_post_id ?>" style="cursor: pointer;">Reassign</a>
				<?php }else { ?>
						....
				<?php } ?>
					<div id="reassign_form<?php echo $myticket->cs_post_id; ?>">
						<ul style="list-style: none; margin: 0px; padding: 0px;">
						
							<li>
								<small>Please select who:</small>
								<select name="" id="redirect_select<?php echo $myticket->cs_post_id; ?>" style="width: 200px;">
									<?php if($this->access->accessFull == true) { ?>
											<option value=""></option>
										<?php foreach ($getHRlist as $key_hr => $value_hr){ ?>
											<option value="<?php echo $myticket->cs_post_id.','.$value_hr->username.','.$value_hr->empID.','.$myticket->hr_own_empUSER.','.$value_hr->fname." ".$value_hr->lname; ?>"><?php echo $value_hr->fname." ".$value_hr->lname; ?></option>
										<?php } ?>
										<?php foreach ($getACClist as $key_acc => $value_acc){ ?>
											<option value="<?php echo $myticket->cs_post_id.','.$value_acc->username.','.$value_acc->empID.','.$myticket->hr_own_empUSER.','.$value_acc->fname." ".$value_acc->lname; ?>"><?php echo $value_acc->fname." ".$value_acc->lname; ?></option>
										<?php } ?>
										
									<?php } else if ($this->access->accessHR == true) { ?>
											<option value=""></option>
										<?php foreach ($getHRlist as $key_hr => $value_hr){ 
											  
											  if($this->user->username == $value_hr->username){?>
											  <?php }else{?>
							        			<option value="<?php echo $myticket->cs_post_id.','.$value_hr->username.','.$value_hr->empID.','.$myticket->hr_own_empUSER.','.$value_hr->fname." ".$value_hr->lname; ?>"><?php echo $value_hr->fname." ".$value_hr->lname; ?></option>
											  <?php }?>
											  
										<?php } ?>
										<option value="<?php echo $myticket->cs_post_id.','.$this->user->username.','.$value_hr->empID.','.$myticket->hr_own_empUSER.',Finance'; ?>">Accounting</option>
										
									<?php }else if($this->access->accessFinance == true){?>
											<option value=""></option>
										<?php foreach ($getACClist as $key_acc => $value_acc){

											 if($this->user->username == $value_acc->username){?>
											  <?php }else{?>
							        			<option value="<?php echo $myticket->cs_post_id.','.$value_acc->username.','.$value_acc->empID.','.$myticket->hr_own_empUSER.','.$value_acc->fname." ".$value_acc->lname; ?>"><?php echo $value_acc->fname." ".$value_acc->lname; ?></option>
											  <?php }?>
											
										<?php } ?>
										<option value="<?php echo $myticket->cs_post_id.','.$this->user->username.','.$value_acc->empID.','.$myticket->hr_own_empUSER.',HR'; ?>">HR</option>
										
									<?php } ?>
								</select>
							</li>
							

							<li><input type="submit" class="redirect_btn btngreen" data-btn="<?php echo $myticket->cs_post_id; ?>" value="Submit" style="float:right;"></li>
						</ul>	
					</div>
				</td>		
			</tr>
		<?php } ?>    	 
	</table>
</div>

<!-- new tab -->
<div id="tab-1" class="tab-content">

<br>

	<table class="datatable">
	  	<thead>
		  	<tr>
			  	<th>Incident #</th>
		  		<th>Subject</th>	
		  		<th>Customer</th>
		  		<th>Priority</th>	
		  		<th>Date Submitted</th>
		  		<th>Assign</th>
		  	</tr>
	  	</thead>


	  	<!-- array show incidient #, subject, customer, priority and date submited-->
	  	<?php foreach ($NewIncident as $key => $value) { ?>

	  		<tr>
	      		<td class="td_hover">
                    <a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $value->cs_post_id; ?>/new/<?php echo $value->cs_post_empID_fk; ?>" 
                    		class="iframe" 
                    		data-balloon-length="large" 
                    		data-balloon="<?php if($value->cs_msg_text==''){echo "No message";}else{echo strip_tags($value->cs_msg_text);} ?>" 
                    		data-balloon-pos="right"><?php echo "$value->cs_post_id";?></a>		
	      		</td>
	      		<td><?php echo $value->cs_post_subject ?></td>
	      		<td><?php echo $value->fname.' '.$value->lname;?></td>
	      		<td><?php echo $value->cs_post_urgency; ?></td>
	       		<td><?php echo date_format(date_create($value->cs_post_date_submitted), 'F d, Y G:ia'); ?></td>
	      		<td style="width: 100px;">
	      		<a id="new_reassign<?php echo $value->cs_post_id ?>" style="cursor: pointer;">Reassign</a>
	      		<div id="new_reassign_form<?php echo $value->cs_post_id; ?>">
					<ul style="list-style: none; margin: 0px; padding: 0px;">
						<li>
							<small>Please select who:</small>
							<select name="" id="new_redirect_select<?php echo $value->cs_post_id; ?>">
								<option value = '--'>--</option>
								<?php if($this->access->myaccess[0] == "full"){ ?>
								
								<?php foreach ($getFULLlist as $key_full => $value_full){ ?>
											<option value="<?php echo $value_full->username.','.$value_full->empID.','.$this->user->username.',Full,'.$value_full->fname.' '.$value_full->lname; ?>"><?php echo $value_full->fname." ".$value_full->lname; ?></option>
								<?php } ?>
											
								<?php }

								else if($this->access->myaccess[0] == "hr"){ ?>
									<option value="<?php echo $value->cs_post_id.",".$this->user->username.",1"; ?>">Accounting</option>
								
								<?php }

								else if($this->access->myaccess[0] == "finance"){ ?>
									<option value="<?php echo $value->cs_post_id.",".$this->user->username.",0"; ?>">HR</option>

								<?php } ?>
							</select><br>
							<input type="button" class="btn_new_redirect btngreen" data-btn="<?php echo $value->cs_post_id; ?>" value="Submit" style="float:right;">
						</li>
					
					</ul>
				</div>

	      		</td>	
	  		</tr>	     

	  	<?php } ?> 		
	    
	</table>  
      
</div>

<!-- active tab-->
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
				<th>Owner</th>		
			</tr>
		</thead>

		<!-- array show incident #, subject, customer, priority and last update -->
		<?php foreach ($ActiveIncident as $active_key => $active_val) { ?>
			<tr>
				<td class="td_hover">
      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $active_val->cs_post_id; ?>/onproc/<?php echo $active_val->cs_post_empID_fk; ?>" class="iframe"><?php echo $active_val->cs_post_id;?></a>
      			</td>
				<td><?php echo $active_val->cs_post_subject; ?></td>
				<td><?php echo $active_val->fname." ".$active_val->lname; ?></td>
				<td><?php echo $active_val->cs_post_urgency; ?></td>
				<td><?php echo date_format(date_create($active_val->last_update), 'F d, Y G:ia'); ?></td>
				<td><?php echo $all_staff_empID[$active_val->cs_post_agent]->name; ?></td>				
			</tr>
		<?php } ?>    	 
	</table>
	 
</div>

<!-- resolved tab -->
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
				<th>Owner</th>							
			</tr>
		</thead>

			<!-- array show incident #, subject, customer, priority and last update -->
			<?php foreach ($ResolveIncident as $active_key => $resolve_val) { ?>
				<tr>
					<td class="td_hover">
	      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $resolve_val->cs_post_id; ?>/resolved/<?php echo $resolve_val->cs_post_empID_fk; ?>" class="iframe"><?php echo $resolve_val->cs_post_id;?></a>
	      			</td>
					<td><?php echo $resolve_val->cs_post_subject; ?></td>
					<td><?php echo $resolve_val->fname." ".$resolve_val->lname; ?></td>
					<td><?php echo $resolve_val->cs_post_urgency; ?></td>
					<td><?php echo date_format(date_create($resolve_val->last_update), 'F d, Y G:ia'); ?></td>
					<td><?php echo $all_staff_empID[$resolve_val->cs_post_agent]->name; ?></td>						
				</tr>
			<?php } ?>     
	</table>

</div>

<!-- closed tab -->
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
				<th>Owner</th>
				<th>Status</th>
			</tr>
		</thead>
		<!-- array show incident #, subject, customer, priority and last update -->
		<?php foreach ($CancelIncident as $active_key => $resolve_val) { ?>
			<tr>
				<td class="td_hover">
      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $resolve_val->cs_post_id; ?>/closed_cancel/<?php echo $resolve_val->cs_post_empID_fk; ?>" class="iframe"><?php echo $resolve_val->cs_post_id;?></a>
      			</td>
				<td><?php echo $resolve_val->cs_post_subject; ?></td>
				<td><?php echo $resolve_val->fname." ".$resolve_val->lname; ?></td>
				<td><?php echo $resolve_val->cs_post_urgency; ?></td>
				<td><?php echo date_format(date_create($resolve_val->last_update), 'F d, Y G:ia'); ?></td>
				<td><?php echo $all_staff_empID[$resolve_val->cs_post_agent]->name; ?></td>		
			<?php if($resolve_val->cs_post_status == 5){ ?>
				<td>Closed Cancel</td>	
			<?php } ?>
			</tr>
		<?php } ?>   
		<!--This is for the Close Transfer-->
		<?php foreach ($Canceltransfer as $transfer_key => $c_transfer) { ?>
			<tr>
				<td class="td_hover">
      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $c_transfer->cs_post_id; ?>/closed_transfer/<?php echo $c_transfer->cs_post_empID_fk; ?>" class="iframe"><?php echo $c_transfer->cs_post_id;?></a>
      			</td>
				<td><?php echo $c_transfer->cs_post_subject; ?></td>
				<td><?php echo $c_transfer->fname." ".$c_transfer->lname; ?></td>
				<td><?php echo $c_transfer->cs_post_urgency; ?></td>
				<td><?php echo date_format(date_create($c_transfer->last_update), 'F d, Y G:ia'); ?></td>
				<td><?php echo $all_staff[$c_transfer->hr_own_empUSER]->name; ?></td>
				<td>Closed Transfer</td>	

			</tr>
		<?php } ?> 


	</table>

</div>

<h2>SETTINGS</h2>
	<!-- settings tab -->
	<ul class="settings_tabs">
		<li class="tab-link current" dt-tab="tb-1">Add Categories</li>
		<li class="tab-link" dt-tab="tb-2">Edit HR User Permissions</li>
		<li class="tab-link" dt-tab="tb-3">Edit Message Templates</li>
		<li class="tab-link" dt-tab="tb-4">Add a Redirection Department</li>
	</ul>

	<!-- add categories tab -->
	<div id="tb-1" class="tab-cont">

	    <label for="lbl_category_name">Enter category name: </label>
	    <input id="category_name_txt" type="text" name="" value="" placeholder="">
    	<input class="btngreen" type="submit" id="add_categ_btn" name="" value="Submit">

    	<h3>Current Categories</h3>
    	<ul>
    		<?php foreach ($categories as $category) {
    			echo '<li>'.$category.'</li>';
    		} ?>
    	</ul>
    </div>

    <!-- edit hr permission tab --> 
    <div id="tb-2" class="tab-cont">
   
    </div>

    <!-- edit message template tab -->
    <div id="tb-3" class="tab-cont">
     
    </div>

    <!-- add redirection department tab -->
    <div id="tb-4" class="tab-cont">

    	<!-- add redirection department -->
   		<table class="tableInfo">
   			<tr>
   				<td colspan="2"><h2>Add a Redirection Department</h2></td>
   			</tr>
   			<tr>
   				<td colspan="2">
   					<label for="lbl_new_dept_name">Name of the department/team customers can be redirected to:</label>
   					<br>
   					<input type="text" id="new_dept_name" name="" value="" placeholder="" style="width: 100%">
   				</td>
   			</tr>
   			<tr>
   				<td colspan="2">
   					<label for="lbl_new_dept_emailadd">What is the email address/es that the employees can contact?</label>
			   		<br>
			   		<input type="text" id="new_dept_emai" name="" value="" placeholder="" style="width: 100%">
   				</td>
   			</tr>
   			<tr>
   				<td align="left"><a id="see_all_dept" style="text-decoration: underline">See All Redirection Departments</a></td>
   				<td align="right"><input type="submit" class="btngreen" id="add_dep_btn" name="" value="Submit"></td>	
   			</tr>
   		</table>

   		<!-- see all department -->
   		<div id="see_all_dept_form">
   			<table class="datatable" width="100%">
   				<h2>All Redirection Departments</h2>
   				<thead>	
	   				<tr>
	   					<td>Department Name</td>
	   					<td>Email Address</td>
	   					<td>Edit</td>
	   					<td>Delete</td>
	   				</tr>
   				</thead>
   				<?php foreach ($department_email as $key_d) { ?>
   					<tr>
   						<td><?php echo $key_d->department; ?></td>
   						<td><?php echo $key_d->email; ?></td>
   						<td>
   						<input type="hidden" class="btngreen" id="id_edit_department<?php echo $key_d->dept_emil_id; ?>" value="<?php echo $key_d->dept_emil_id; ?>">
   						<input type="submit" class="btngreen" id="edit_department_btn<?php echo $key_d->dept_emil_id; ?>" value="Edit">
   						</td>
   						<td>
   						<input type="hidden" class="btngreen" id="id_delete_department<?php echo $key_d->dept_emil_id; ?>" value="<?php echo $key_d->dept_emil_id; ?>">
   						<input type="submit" class="btnred" id="delete_department_btn<?php echo $key_d->dept_emil_id; ?>" value="Delete">
   						</td>
   					</tr>
   					<?php } ?>
   			</table>
   		</div>
    </div>

<script type="text/javascript">

$(document).ready(function(){

	// see all department slide toggle 
	$('#see_all_dept_form').hide();
	$('#see_all_dept').click(function(){

		$('#see_all_dept_form').slideToggle();

			if ($.trim($(this).text()) === 'Hide See All Redirection Departments') {
				
				$(this).text('See All Redirection Departments');
			}

			else{
				$(this).text('Hide See All Redirection Departments');
			}
	});

	<?php foreach ($department_email as $id_d) { ?>
		// Delete Department
		$("#delete_department_btn<?php echo $id_d->dept_emil_id; ?>").click(function() {

			var id_dep = $("#id_delete_department<?php echo $id_d->dept_emil_id; ?>").val();
			var datacategorys = 'id_delete='+ id_dep;
			
			if (id_dep == '') {
				alert("No ID Selected!");
			} else {
					
					$.ajax({
					type: "POST",
					url: "<?php echo $this->config->base_url(); ?>hr_cs/DeleteDeparment",
					data: datacategorys,
					cache: false,
						success: function(result){
						alert("Success!");
						 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
	                     close();
						}
					});
				}
		});
	<?php } ?>

	// insert new category
	$("#add_categ_btn").click(function() {

		var category = $("#category_name_txt").val();
		var datacategorys = 'category_name='+ category;
		
		if (category == '') {
			alert("Insert new category!");
		} else {
				
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/addcategory",
				data: datacategorys,
				cache: false,
					success: function(result){
					alert("Success!");
					 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                     close();
					}
				});
			}
	});

	// Insert new department
	$("#add_dep_btn").click(function() {

		var new_name = $("#new_dept_name").val();
		var new_email = $("#new_dept_emai").val();

		var datacategorys = 'name_department='+ new_name +'&email_department=' + new_email;
		
		if (new_name == '' || new_email == '') {
			alert("Error field in empty!");
		} else {
				
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/addnewdeparment",
				data: datacategorys,
				cache: false,
					success: function(result){
					alert("Success!");
					 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                     close();
					}
				});
			}
	});

	
		
	// Redirect my ticket
	$(".redirect_btn").click(function() {
		
		var btn_unique_id = $(this).data("btn");
		var redirect_b = $('#redirect_select' +btn_unique_id+ ' option:selected').val();
		var dataredirect_a = 'redirect_to='+ redirect_b;

		console.log(dataredirect_a);

		if (redirect_b == '') {
			alert("Please Select!");
		} else {
				
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/Redirect",
				data: dataredirect_a,
				cache: false,
					success: function(result){
					alert("Success!");
					 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                     close();
					}
				});
			}
	});



	// Redirect new ticket 
	$('.btn_new_redirect').click(function() {
		
		var btn_unique_id = $(this).data("btn");
		var redirect_b = $('#new_redirect_select' +btn_unique_id+ ' option:selected').val();
		
		var dataredirect_a = 'redirect_to=' + btn_unique_id + "," + redirect_b;

		console.log(dataredirect_a);

		if (redirect_b == '--') {
			alert("Please Select!");
		} else {
				
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/Redirect",
				data: dataredirect_a,
				cache: false,
					success: function(result){
					alert("Success!");
					 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                     close();
					}
				});

				
			}

	});

	// Extend due date
	
	
	$(".add_days_btn").click(function() {

		var that = $(this);
		
		var id = that.data('btn');
		var addDay = $('#add_days_'+id).val();	
		$('#btn_add_days_'+id).attr('disabled', 'disabled');
		if (addDay == '') {
			alert("Please Select number of days!");
			$('#btn_add_days_'+id).removeAttr('disabled');
		} else if( addDay > 5 ){
			alert('You can only extend the due date up to 5 days.');		
			$('#btn_add_days_'+id).removeAttr('disabled');
		} else {
				
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/AdditionalDays",
				data: { add_days: addDay, inci_id : id },
				cache: false,

				success: function(result){
					console.log(result);
					alert("Done extending date!");
					 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                     close();
					}
				});
			}
	});



	<?php foreach ($MyTicket as $jk => $jv): ?>
		$('#reassign_form<?php echo $jv->cs_post_id; ?>').hide();
		$('#reassign<?php echo $jv->cs_post_id;  ?>').click(function(){

		$('#reassign_form<?php echo $jv->cs_post_id; ?>').toggle();

		});	

		$('#extend_date_form<?php echo $jv->cs_post_id; ?>').hide();
		$('#extend_date<?php echo $jv->cs_post_id; ?>').click(function(){

			$('#extend_date_form<?php echo $jv->cs_post_id; ?>').toggle();
		})
	<?php endforeach ?>

	<?php foreach ($NewIncident as $new => $inc): ?>
		$('#new_reassign_form<?php echo $inc->cs_post_id; ?>').hide();
		$('#new_reassign<?php echo $inc->cs_post_id;  ?>').click(function(){

		$('#new_reassign_form<?php echo $inc->cs_post_id; ?>').toggle();

		});	
	<?php endforeach ?>
	

});




</script>
