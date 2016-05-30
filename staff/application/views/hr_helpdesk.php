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

	th{
	 	background-color: #CCCCCC;
	 }
	 .datatable td{
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


 
</style>

 	<?php
	  	$NewIncident = array();
	  	$ActiveIncident = array();
	  	$ResolveIncident = array();
	  	$CancelIncident = array();
	  	$title = '';

	  	// Title & access in new tab
	  	if($this->user->access == "hr"){
	  		$NewIncident = $NewIncidentHR;
	  		$title = "HR HelpDesk";
	  	}else if($this->user->access == "finance"){
	  		$NewIncident = $NewIncidentAcc;
	  		$title = "Accounting HelpDesk";
	  	}else if($this->user->access == "full"){
			$NewIncident = $NewIncidentFull;
			$title = "Admin HelpDesk";
		}

		// Access in active tab
		if($this->user->access == "hr"){
	  		$ActiveIncident = $ActiveIncidentHR;
	  	}else if($this->user->access == "finance"){
	  		$ActiveIncident = $ActiveIncidentAcc;	
	  	}else if($this->user->access == "full"){
			$ActiveIncident = $ActiveIncidentFull;
		}

		// Access in resolved tab
		if($this->user->access == "hr"){
	  		$ResolveIncident = $ResolveIncidentHR;
	  	}else if($this->user->access == "finance"){
	  		$ResolveIncident = $ResolveIncidentAcc;	
	  	}else if($this->user->access == "full"){
			$ResolveIncident = $ResolveIncidentFull;
		}

		// Access in close tab
		if($this->user->access == "hr"){
	  		$CancelIncident = $CancelIncidentHR;
	  	}else if($this->user->access == "finance"){
	  		$CancelIncident = $CancelIncidentAcc;	
	  	}else if($this->user->access == "full"){
			$CancelIncident = $CancelIncidentFull;
		}
	?>

<!-- hr help desk tabs -->
<ul class="tabs">

	<h2><?php echo $title; ?></h2>

	<li class="dbold tab-link current" data-tab="tab-0">My Ticket</li>
	<li class="dbold tab-link" data-tab="tab-1">New <span style="font-weight: bold; color: darkred;"><?php if(count($NewIncident)=='0'){}else{echo '('.count($NewIncident).')'; } ?></span></li>
	<li class="dbold tab-link" data-tab="tab-2">Active</li>
	<li class="dbold tab-link" data-tab="tab-3">Resolved</li>
	<li class="dbold tab-link" data-tab="tab-4">Closed</li>

	<div class=" dbold options-right">
		<a class="other_links" id="active_hide" href="<?php echo $this->config->base_url(); ?>hr_cs/hr_custom_satisfaction">HR/Accounting Customer CSatResults</a>
		<a class="other_links" href="#">Generate Reports</a>	
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
				<th>Owner</th>
				<th>Extend Due Date</th>
				<th>Reassign</th>


			</tr>
		</thead>
			<?php foreach ($MyTicket as $myticket_key => $myticket) { ?>
			<tr>
				<td class="td_hover">
				<?php if (date('Y-m-d') >= $myticket->due_date) {
					echo "<span style='color:red;'>&#9873;</span>";
				}?>

      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $myticket->cs_post_id; ?>/active/<?php echo $myticket->cs_post_empID_fk; ?>" class="iframe"><?php echo $myticket->cs_post_id;?></a>
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
				<td><?php echo date_format(date_create($myticket->due_date),'F d, Y'); ?></td>
				
				<?php 
					$status = ['Open', 'Active', 'Hold', 'Resolved','Closed'];
					echo '<td>'.$status[ $myticket->cs_post_status ].'</td>';
				?>	

				<?php 
				if($myticket->remark != ''){
					echo "<td>". $myticket->remark."</td>";
				}else{
					echo "<td>Unrated</td>";
				}
				?>
				<td><?php echo $myticket->hr_own_empUSER; ?></td>
				<td>
				<?php if($myticket->cs_post_status == 0 || $myticket->cs_post_status == 1){?>
					<a style="cursor: pointer;" id="extend_date<?php echo $myticket->cs_post_id; ?>">Extend Date</a>
				<?php }else { ?>
						....
				<?php } ?>
					<span id="extend_date_form<?php echo $myticket->cs_post_id; ?>">
						<br>
						<small>Plase select number of additional days:</small>
						<input type="number" id="add_days<?php echo $myticket->cs_post_id; ?>" min="1" max="5">
						<input type="hidden" id="inci_id<?php echo $myticket->cs_post_id; ?>" value="<?php echo $myticket->cs_post_id; ?>">
						<input type="hidden" id="due_D<?php echo $myticket->cs_post_id; ?>" value="<?php echo $myticket->due_date; ?>">
						<input type="submit" class="btngreen" id="add_days_btn<?php echo $myticket->cs_post_id; ?>" value="Submit">
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
								<select name="" id="redirect_select" style="width: 200px;">
									<?php if ($this->access->myaccess[0] == "hr") { ?>
											<option value=""></option>
										<?php foreach ($getHRlist as $key_hr => $value_hr): ?>
											<option value="<?php echo $myticket->cs_post_id.','.$value_hr->username.','.$value_hr->empID.','.$myticket->hr_own_empUSER.','.$value_hr->fname." ".$value_hr->lname; ?>"><?php echo $value_hr->fname." ".$value_hr->lname; ?></option>
										<?php endforeach ?>
										<option value="<?php echo $myticket->cs_post_id.','.$this->user->username.','.$value_hr->empID.','.$myticket->hr_own_empUSER.',Finance'; ?>">Finance</option>
										
									<?php }else if($this->access->myaccess[0] == "finance"){?>
											<option value=""></option>
										<?php foreach ($getACClist as $key_acc => $value_acc): ?>
											<option value="<?php echo $myticket->cs_post_id.','.$value_acc->username.','.$value_acc->empID.','.$myticket->hr_own_empUSER.','.$value_acc->fname." ".$value_acc->lname; ?>"><?php echo $value_acc->fname." ".$value_acc->lname; ?></option>
										<?php endforeach ?>
										<option value="<?php echo $myticket->cs_post_id.','.$this->user->username.','.$value_acc->empID.','.$myticket->hr_own_empUSER.',HR'; ?>">HR</option>
										
									<?php }else if($this->access->myaccess[0] == "full"){?>
											<option value=""></option>
										<?php foreach ($getFULLlist as $key_full => $value_full): ?>
											<option value="<?php echo $myticket->cs_post_id.','.$value_full->username.','.$value_full->empID.','.$myticket->hr_own_empUSER.','.$value_full->fname." ".$value_full->lname; ?>"><?php echo $value_full->fname." ".$value_full->lname; ?></option>
										<?php endforeach ?>
										<option value="<?php echo $myticket->cs_post_id.','.$this->user->username.','.$value_full->empID.','.$myticket->hr_own_empUSER.',HR'; ?>">HR</option>
										<option value="<?php echo $myticket->cs_post_id.','.$this->user->username.','.$value_full->empID.','.$myticket->hr_own_empUSER.',Finance'; ?>">Finance</option>
										
									<?php }?>
								</select>
							</li>
							

							<li><input type="button" class="btngreen" id="redirect_btn" name="" value="Submit" style="float:right;"></li>
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
		  		<th>Reassign</th>
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
	      		<td><?php echo $value->fname; ?></td>
	      		<td><?php echo $value->cs_post_urgency; ?></td>
	      		<td><?php  echo $value->cs_post_date_submitted; ?></td> 
	      		<td><select name="" id="redirect_select" style="width: 200px;">
	      				<option>HR</option>
	      				<option>Finance</option>
	      			</select>
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
      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $active_val->cs_post_id; ?>/active/<?php echo $active_val->cs_post_empID_fk; ?>" class="iframe"><?php echo $active_val->cs_post_id;?></a>
      			</td>
				<td><?php echo $active_val->cs_post_subject; ?></td>
				<td><?php echo $active_val->fname." ".$active_val->lname; ?></td>
				<td><?php echo $active_val->cs_post_urgency; ?></td>
				<td><?php echo $active_val->last_update; ?></td>
				<td><?php echo $active_val->hr_own_empUSER; ?></td>				
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
					<td><?php echo $resolve_val->last_update; ?></td>
					<td><?php echo $resolve_val->hr_own_empUSER; ?></td>				
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
			</tr>
		</thead>
		<!-- array show incident #, subject, customer, priority and last update -->
		<?php foreach ($CancelIncident as $active_key => $resolve_val) { ?>
			<tr>
				<td class="td_hover">
      			<a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $resolve_val->cs_post_id; ?>/cinc/<?php echo $resolve_val->cs_post_empID_fk; ?>" class="iframe"><?php echo $resolve_val->cs_post_id;?></a>
      			</td>
				<td><?php echo $resolve_val->cs_post_subject; ?></td>
				<td><?php echo $resolve_val->fname." ".$resolve_val->lname; ?></td>
				<td><?php echo $resolve_val->cs_post_urgency; ?></td>
				<td><?php echo $resolve_val->last_update; ?></td>
				<td><?php echo $resolve_val->hr_own_empUSER; ?></td>		
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
   					<input type="text" id="lbl_new_dept_name" name="" value="" placeholder="" style="width: 100%">
   				</td>
   			</tr>
   			<tr>
   				<td colspan="2">
   					<label for="lbl_new_dept_emailadd">What is the email address/es that the employees can contact?</label>
			   		<br>
			   		<input type="text" id="lbl_new_dept_emailadd" name="" value="" placeholder="" style="width: 100%">
   				</td>
   			</tr>
   			<tr>
   				<td align="left"><a id="see_all_dept" style="text-decoration: underline">See All Redirection Departments</a></td>
   				<td align="right"><input type="submit" class="btngreen" name="" value="Submit"></td>	
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
   					<tr>
   						<td></td>
   						<td></td>
   						<td></td>
   						<td></td>
   					</tr>
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

	// Redirection of owner
	$("#redirect_btn").click(function() {

		var redirect = $("#redirect_select option:selected").val();
		var dataredirect = 'redirect_to='+ redirect;

		if (redirect == '') {
			alert("Please Select!");
		} else {
				
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/Redirect",
				data: dataredirect,
				cache: false,
					success: function(result){
					alert("Success!");
					 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                     close();
					}
				});
			}
	});

	// Additional days of owner incident
	<?php foreach ($MyTicket as $k => $t): ?>
	
	$("#add_days_btn<?php echo $t->cs_post_id; ?>").click(function() {

		var due = $('#due_D<?php echo $t->cs_post_id; ?>').val();
		var i_id = $('#inci_id<?php echo $t->cs_post_id; ?>').val();
		var addDay = $("#add_days<?php echo $t->cs_post_id; ?>").val();

		var dataredirect = 'add_days='+ addDay + '&inci_id=' + i_id + '&due_date=' + due;

		if (addDay == '') {
			alert("Please Select number of days!");
		} else {
				
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/AdditionalDays",
				data: dataredirect,
				cache: false,
					success: function(result){
					alert("Success! Due: "+ due + " ID : " + i_id + " NumDays: " + addDay);
					 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                     close();
					}
				});
			}
	});
	<?php endforeach ?>



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
	

});

</script>
