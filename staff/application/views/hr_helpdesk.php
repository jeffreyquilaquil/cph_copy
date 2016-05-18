<?php  
if($this->user->access != "full"){
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

<h2>HR HelpDesk</h2>
<ul class="tabs">
	<li class="dbold tab-link current" id="new_tab" data-tab="tab-1">New <font color="darkred" style="font-weight: bold;">( <?php echo count($NewIncident)?> )</font></li>
	<li class="dbold tab-link" id="active_tab" data-tab="tab-2">Active <font color="darkred" style="font-weight: bold;">( <?php echo count($ActiveIncident)?> )</font></li>
	<li class="dbold tab-link" data-tab="tab-3">Resolved</li>
	<li class="dbold tab-link" data-tab="tab-4">Closed</li>

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
                    <a href="<?php echo $this->config->base_url(); ?>hr_cs/HrIncident/<?php echo $value->cs_post_id; ?>/new" 
                    		class="iframe" 
                    		data-balloon-length="large" 
                    		data-balloon="<?php if($value->cs_msg_text==''){echo "No message";}else{echo strip_tags($value->cs_msg_text);} ?>" 
                    		data-balloon-pos="right"><?php echo "$value->cs_post_id";?></a>		
	      		</td>
	      		<td><?php echo $value->cs_post_subject ?></td>
	      		<td><?php echo $value->fname; ?></td>
	      		<td><?php echo $value->cs_post_urgency; ?></td>
	      		<td><?php  echo $value->cs_post_date_submitted; ?></td>      		
	  		</tr>	     

	  	<?php } ?> 		
	    
	</table>

     

      
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

</div>

<h2>SETTINGS</h2>

	<ul class="settings_tabs">
		<li class="tab-link current" dt-tab="tb-1">Add Categories</li>
		<li class="tab-link" dt-tab="tb-2">Edit HR User Permissions</li>
		<li class="tab-link" dt-tab="tb-3">Edit Message Templates</li>
		<li class="tab-link" dt-tab="tb-4">Add a Redirection Department</li>
	</ul>

	<div id="tb-1" class="tab-cont">
	    <label for="lbl_category_name">Enter category name: </label>
	    <input id="category_name_txt" type="text" name="" value="" placeholder="">
    	<input class="btngreen" type="submit" id="add_categ_btn" name="" value="Submit">
    </div>

    <div id="tb-2" class="tab-cont">
   
    </div>

    <div id="tb-3" class="tab-cont">
     
    </div>

    <div id="tb-4" class="tab-cont">
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

	// ====== INSERT NEW CATEGORY =====
	$("#add_categ_btn").click(function() {

		var category = $("#category_name_txt").val();

		var datacategorys = 'category_name='+ category;
		
		if (category == '') {
			alert("Insert new category!");
		} else {

			// ===== AJAX CODE TO SUBMIT FORM =====
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

});

</script>
