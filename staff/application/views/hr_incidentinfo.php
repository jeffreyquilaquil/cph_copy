
<style type="text/css">
	.note{
		font-style: italic;
		font-size: x-small;
	}

	.btn_ans{
		background-color: #CCCCCC; 
		font-weight: bold;
		width: 100%;
		padding: 3px;
	}

	.btn_ans_small{
		background-color: #CCCCCC; 
		font-weight: bold; padding: 3px;
	}

	input[type=text]{

		width: 100%;

	}

	a{
		text-decoration: underline;
		cursor: pointer;
	}

	table{
		width: 100%;
	}

	.conversation{
		padding: 5px;
		color: #000000;
	}

	hr{
		border-top: 1px solid #ccc;
	}
</style>

<!-- ===== INCIDENT INFORMATION  ===== -->
<div id="hr_incidentinfo_form"> 

<?php foreach ($HrIncident as $key => $value): ?>
<?php endforeach ?>
<input type="hidden" id="tab_type" value="<?php echo $this->uri->segment(4); ?>">
<input type="hidden" id="categoryid" name="postid" value="<?php echo $value->cs_post_id; ?>">
<input type="hidden" id="hr_username" name="postid" value="<?php echo $this->user->username; ?>">
<form id="custom_ans_form">
	<table class="tableInfo">
		<tr>
			<td colspan="2">
				<h2>HR Incident Number <?php echo $value->cs_post_id; ?></h2>
			</td>	
		</tr>
		<tr>
			<td >Customer</td>
			<input type="hidden" id="fullname" value="<?php echo $value->fname." ". $value->lname; ?>">
			<td><?php echo $value->fname." ". $value->lname; ?> </td>
		</tr>
		<tr>
			<td>Date Submitted</td>
			<td><?php echo $value->cs_post_date_submitted; ?></td>
		</tr>
		<tr>
			<td>Subject</td>
			<td><?php echo $value->cs_post_subject; ?></td>
		</tr>
		<tr>
			<td>Customer selected priority Level</td>
			<td><?php echo $value->cs_post_urgency; ?></td>
		</tr>

		<?php if ($this->uri->segment(4)== 'new'){ ?>
		<tr>
			<td>Assign Category</td>
			<td>
				<select id="assign_category" name="assign_category">
						<option></option>
					<?php foreach ($category as $key => $val): ?>
						<option value="<?php echo $val->categorys; ?>"><?php echo $val->categorys; ?></option>
					<?php endforeach ?>
			</select>
			
			&nbsp;

			<a id="add_category">Add Category</a>
			
			<div id="show_add_category">
			
			<br>

				<form id="form" name="form">
					<input type="hidden" id="insedentid" name="postid" value="<?php echo $value->cs_post_id; ?>">
					Category name: <input id="newcategory" type="text" name="category_name" required style="width: 123px">
					
					&nbsp;

					<input id="submit" type="submit" value="Add">
				</form>	
			</div>
			</td>
		</tr>
		<tr>
			<td valign="top">Investigation Required:</td>
			<td>
				<input id="r_yes" type="radio" name="investigation_required_radio" value="Yes" required><label for="r_yes">Yes</label>
				<input id="r_no" type="radio" name="investigation_required_radio" value="No" checked="true"><label for="r_no">No</label>
				
				
				<br><br>

				<span class="note">
					Note to HR: If you are able to provide answer to the question within 24 hours,
					select <b>NO</b> if you need to involve or collect information from other departments,
					Select <b>YES</b>.
				</span>
			</td>
		</tr>
		<?php }elseif ($this->uri->segment(4) == 'active' || $this->uri->segment(4) == 'emp' || $this->uri->segment(4) == 'resolved' || $this->uri->segment(4) == 'cinc') { ?>

				<tr>
					<td>Assign Category</td><td><?php echo $value->assign_category; ?></td>
				</tr>
				<tr>
					<td>Investigation Required:</td><td><b><?php echo $value->invi_req; ?></b><br><br>
					<span class="note">
					Note to HR: If you are able to provide answer to the question within 24 hours,
					select <b>NO</b> if you need to involve or collect information from other departments,
					Select <b>YES</b>.
				</span>
					</td>
				</tr>
			<?php } ?>  

		<?php foreach ($conversation as $key => $conve){if ($this->user->access == "full" && $conve->cs_msg_type == 2 || $conve->cs_msg_type == 1 || $conve->cs_msg_type == 0) { ?>
		<tr>
			<td colspan="2">
				<div class="conversation">
					Message from: <?php echo strip_tags($conve->reply_empUser); ?>
					<br>
					Date Submitted: <?php echo strip_tags($conve->cs_msg_date_submitted); ?>
					<br><br>
					<?php echo $conve->cs_msg_text; ?>
				</div>
			</td>
		</tr>
		<?php } elseif($conve->cs_msg_type == 1 && $conve->cs_msg_type == 0 && $this->user->access != "full"){ ?>
		<tr>
			<td colspan="2">
				<div class="conversation">
					Message from: <?php echo strip_tags($conve->reply_empUser); ?>
					<br>
					Date Submitted: <?php echo strip_tags($conve->cs_msg_date_submitted); ?>
					<br><br> r  
					<?php echo $conve->cs_msg_text; ?>
				</div>
			</td>
		</tr>
		<?php } } ?>
	</table>
	<br>

<?php  if($this->user->access == "full" && $this->uri->segment(4) != 'resolved' && $this->uri->segment(4) != 'cinc') { ?>
			
		
	<ul class="tabs">
		<li class="dbold tab-link" id="new_tab" data-tab="tab-1">Note</li>
		<li class="dbold tab-link" id="active_tab" data-tab="tab-2">Reply</li>
	</ul>
	<hr/>
	<!-- Notes -->
	<div id="tab-1" class="tab-content">
		<h2>Add A Note</h2>
		<form id="note_form">
			<textarea id="note_msg" class="hidden tiny" style="height:200px;"></textarea>
			<br>
			<input type="submit" id="note_submit" class="btngreen" value="Submit" style="float:right;">
		</form>
		<br>
	</div>

	<!-- Reply  -->
	<div id="tab-2" class="tab-content"> 
		<h2>Add A Reply</h2>
		Resolution Options:
			<select id="resolution_options">
				<option></option>
				<option>The answer can be found in employee.tatepublishing.net</option>
				<option>Send custom response</option>
				<option>This is not an HR inquiry. Redirect to another department</option>
				<option>Further Information (investigation) is required</option>
				<option>Resolved</option>
				<option>Closed</option>
			</select>
		<br><br>

		<?php } ?>

			<textarea id="custom_msg" class="hidden tiny" style="height:200px;"></textarea><br>

		<input type="submit" id="submit_reply" class="btngreen" value="Submit" style="float:right;"><br>
	
		
	</div>
</div>

</form>
<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {

var message = '';

	// ====== ADD CATEGORY =====
	$('#show_add_category').hide();
	$('#add_category').click(function(){

		$('#show_add_category').toggle();

			if ($.trim($(this).text()) === 'Hide Category') {
				
				$(this).text('Add Category');
			}

			else{
				$(this).text('Hide Category');
			}
	});

	// ===== RESOLUTION OPTIONS ===== 
	var full_name = $("#fullname").val();

	var found = 'Hello <b>'+ full_name +'! </b><br><br> The answer to your inquiry/report can be found in the link below: <br><br> (INSERT LINK HERE)';
	var custom = 'Hello <b>'+ full_name +'! </b><br><br> This is the message that the HR will write on the box as a custom response to the employee.';
	var not_found = 'Hello <b>'+ full_name +'! </b><br><br> (INSERT CUSTOM MESSAGE HERE) <br><br> Upon evaluation, it is determined that your incident/inquiry may be best addressed by the (INSERT REDIRECT DEPARTMENT HERE). Their email address is (INSERT REDIRECT DEPARTMENT EMAIL ADDRESS HERE) which is CC in this email. <br><br> Please communicate with the (INSERT REDIRECT DEPARTMENT HERE) for the resolution of the incident.';
	var further = 'Hello <b>'+ full_name +'! </b><br><br> Please provide a copy of (INSERT NEEDED REQUIRMENTS HERE) <br><br> Thank you very much! <br><br> (INSERT HR COMPLETE NAME HERE) <br> (INSERT HR POSITION HERE) <br><br><b><u> Important Note </u> </b><br> If any responses/information/document is required from you, please reply within three (3) business days. If no response is received from you within three (3) business days, This incident will automatically closed. If no response/information/document is required from you, no action is required and you will received regular updates about this incident until its resolution';
	var resolve = 'Hello <b>'+ full_name +'! </b><br><br> (Your Incident is now resolved)';
	var closed = 'Hello <b>'+ full_name +'! </b><br><br> (Your Incident is now Closed)';
		
	$( "#resolution_options" ).change(function() {
	    if ($( "#resolution_options" ).val() == 'The answer can be found in employee.tatepublishing.net') { 
				tinyMCE.activeEditor.setContent(found);
				
	    }
	    else if($( "#resolution_options" ).val() == 'Send custom response') {
	    		tinyMCE.activeEditor.setContent(custom);
	    		
	    }
	    else if($( "#resolution_options" ).val() == 'This is not an HR inquiry. Redirect to another department'){
	    		tinyMCE.activeEditor.setContent(not_found);
	    		
	    }
	    else if($( "#resolution_options" ).val() == 'Further Information (investigation) is required'){
	    		tinyMCE.activeEditor.setContent(further);
	    		
	    }else if($( "#resolution_options" ).val() == 'Resolved'){
	    		tinyMCE.activeEditor.setContent(resolve);
	    		
	    }else if($( "#resolution_options" ).val() == 'Closed'){
	    		tinyMCE.activeEditor.setContent(closed);
	    		
	    }
	    else if($( "#resolution_options" ).val() == ''){

	    		tinyMCE.activeEditor.setContent("");
	    		message = '';
	    }
    
	});



 	// ===== DISPLAY TOOLBAR IN TEXTAREA =====
	tinymce.init({
	selector: "textarea.tiny",	
	menubar : false,
	toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image"
	});	


// ====== INSERT NEW CATEGORY =====
	$("#submit").click(function() {

		var category = $("#newcategory").val();

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
					$('#form')[0].reset(); // ===== TO RESET FORM FIELDS =====
					 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrIncident";
                     close();
					}
				});
			}
	});

	// ====== SUBMIT NOTE  =====
	$("#note_submit").click(function() {

		var custom_ans = tinyMCE.get('note_msg').getContent();
			var hr_sname = $("#hr_username").val();
			var ins_id = $("#categoryid").val();
			var inv_req = $('input[name="investigation_required_radio"]:checked').val();
			var ass_categ = $("#assign_category option:selected").val();

			var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&assign_category=' + ass_categ + '&inve_req=' + inv_req;

		
		if (custom_ans == '') {

				alert("Some Field is Empty!");
			}else{

			// ===== AJAX CODE TO SUBMIT FORM =====
				$.ajax({
				type: "POST",
				url: "<?php echo $this->config->base_url(); ?>hr_cs/submit_notes",
				data: dataString,
				cache: false,
					success: function(result){
					alert("Success!");
					$('#note_form')[0].reset(); // ===== TO RESET FORM FIELDS =====
					window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                    close();
				}
			});
		}
		return false;
	});

	// ===== SUBMITING REPLY =====
	$("#submit_reply").click(function() {

	
		var tab_typ = $('#tab_type').val();
		var status ='';

		if($( "#resolution_options option:selected" ).val() == 'Resolved'){
	    	var	status = 3;
	    		
	    }else if($( "#resolution_options option:selected" ).val() == 'Closed'){
	    	var status = 4;
	    		
	    }

		if (tab_typ == 'new') {

			var inv_req = $('input[name="investigation_required_radio"]:checked').val();
			var hr_sname = $("#hr_username").val();
			var ins_id = $("#categoryid").val();
			var ass_categ = $("#assign_category option:selected").val();
			var custom_ans = tinyMCE.get('custom_msg').getContent();

			var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ +'&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&inve_req=' + inv_req + '&reply=' + tab_typ + '&stat=' + status;
			
		}else if(tab_typ == 'active'){
			var custom_ans = tinyMCE.get('custom_msg').getContent();
			var hr_sname = $("#hr_username").val();
			var ins_id = $("#categoryid").val();

			var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&reply=' + tab_typ + '&stat=' + status;
			
		}else if(tab_typ == 'emp'){
			var custom_ans = tinyMCE.get('custom_msg').getContent();
			var hr_sname = $("#hr_username").val();
			var ins_id = $("#categoryid").val();

			var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&reply=' + tab_typ;

		
		}else if(tab_typ == 'resolved' || tab_typ == 'reopen' || tab_typ == 'cinc'){
			var custom_ans = tinyMCE.get('custom_msg').getContent();
			var hr_sname = $("#hr_username").val();
			var ins_id = $("#categoryid").val();

			var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&reply=' + tab_typ;

		
		}
			if (custom_ans == '') {

				alert("Some Field is Empty!");
			}else{
					

					// ===== AJAX CODE TO SUBMIT FORM =====
					$.ajax({
					type: "POST",
					url: "<?php echo $this->config->base_url(); ?>hr_cs/custom_answer_solution",
					data: dataString,
					cache: false,
						success: function(result){
						alert("Success! "+ status);
						$('#custom_ans_form')[0].reset(); // ===== TO RESET FORM FIELDS =====

						if (tab_typ == 'emp') {
							//alert("<?php echo $this->config->base_url(); ?>hr_cs/employee_dashboard/<?php echo $this->user->empID;?>/");
	                    	 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/employee_dashboard/<?php echo $this->user->empID;?>/";
	                        close();
	                    }else if (tab_typ == 'reopen') {
							//alert("<?php echo $this->config->base_url(); ?>hr_cs/employee_dashboard/<?php echo $this->user->empID;?>/");
	                    	 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/employee_dashboard/<?php echo $this->user->empID;?>/";
	                        close();
	                    }else{
	                    	//alert("<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk");
							 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
	                        close();


	                    }

						}
					});
				}
				return false;
		});

			
});

</script>