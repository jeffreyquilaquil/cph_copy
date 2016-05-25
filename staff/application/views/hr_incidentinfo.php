
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
	}#remark_list {
    	background-color:#FF868E;
    	text-align:center;
    	padding:5px;
   	 	width:500px;
    	float:left;
    	-moz-border-radius: 2px;
        -webkit-border-radius: 2px;
         border-radius: 2px;
	}
</style>

<div> 

<?php foreach ($HrIncident as $key => $value): ?>
<?php endforeach ?>


<input type="hidden" id="tab_type" value="<?php echo $this->uri->segment(4); ?>">
<input type="hidden" id="categoryid" name="postid" value="<?php echo $value->cs_post_id; ?>">
<input type="hidden" id="hr_username" name="postid" value="<?php echo $this->user->username; ?>">
<input type="hidden" id="inci_datesubmited" value="<?php echo $value->cs_post_date_submitted; ?>">
<input type="hidden" id="inci_lastupdate" value="<?php echo $value->last_update; ?>">
<input type="hidden" id="cs_post_empID_fk" value="<?php echo $value->cs_post_empID_fk; ?>">

<!-- incident info form -->
<form id="custom_ans_form">
	<table class="tableInfo">
		<tr>
			<td colspan="2"><h2>HR Incident Number <?php echo $value->cs_post_id; ?></h2></td>	
		</tr>
		<tr>
			<td >Employee Name</td>
			<input type="hidden" id="fullname" value="<?php echo $value->fname." ". $value->lname; ?>">
			<td><?php echo $value->fname." ". $value->lname; ?> </td>
		</tr>
		<tr>
			<td>Department</td>
			<td><?php echo $value->dept; ?></td>
		</tr>
		<tr>
			<td>Position</td>
			<td><?php echo $value->title; ?></td>
		</tr>
		<tr>
			<td>Immediate Supervisor</td>
			<td><?php echo $value->supervisor; ?></td>
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

		<!-- when incidident is new, can add assign category and investigation required -->
		<?php if ($this->uri->segment(4)== 'new'){ ?>

		<!-- assigning of category -->
		<tr>
			<td>Assign Category</td>
			<td>
				<select id="assign_category" name="assign_category">
					<option></option>
					<?php foreach ($category as $key => $val): ?>
					<option value="<?php echo $val->categorys; ?>"><?php echo $val->categorys; ?></option>
					<?php endforeach ?>
				</select>
			</td>
		</tr>
		<!-- selection of investigation required-->
		<tr>
			<td valign="top">Investigation Required:</td>
			<td>
				<input id="r_yes" type="radio" name="investigation_required_radio" value="Yes" required>
				<label for="r_yes">Yes</label>
				<input id="r_no" type="radio" name="investigation_required_radio" value="No" checked="true">
				<label for="r_no">No</label>
				<br><br>
				<span class="note">
					Note to HR: If you are able to provide answer to the question within 24 hours,
					select <b>NO</b> if you need to involve or collect information from other departments,
					Select <b>YES</b>.
				</span>
			</td>
		</tr>

		<!-- when incident is either active, resolved, closed or displayed in employee dashboard-->
		<?php }elseif ($this->uri->segment(4) == 'active' || 
						$this->uri->segment(4) == 'resolved' || 
						$this->uri->segment(4) == 'cinc' || 
						$this->uri->segment(4) == 'emp') { ?>

				<tr>
					<td>Assign Category</td>
					<td><?php echo $value->assign_category; ?></td>
				</tr>
				<tr>
					<td>Investigation Required:</td>
					<td>
						<b><?php echo $value->invi_req; ?></b>	
						<br><br>
						<span class="note">
							Note to HR: If you are able to provide answer to the question within 24 hours,
							select <b>NO</b> if you need to involve or collect information from other departments,
							Select <b>YES</b>.
						</span>
					</td>
				</tr>
		<?php } ?>  

		<!-- array show conversations -->
		<?php foreach ($conversation as $key => $conve){
					// show hr messages
					if ($this->uri->segment(4) != 'emp' &&
						$conve->cs_msg_type == 2 || 
						$conve->cs_msg_type == 1 || 
						$conve->cs_msg_type == 0) { ?>
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

		<!-- show employee messages -->
		<?php } elseif($conve->cs_msg_type == 1 &&
					   $conve->cs_msg_type == 0 && 
					   $this->uri->segment(4) == 'emp'){ ?>
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
		<?php } } ?>
	</table>
	<br>

	<!-- when user access is full and his/her incident is new or active -->
	<?php if(($this->user->access == "full" || $this->user->access == "hr" || $this->user->access == "finance")&&
			($this->uri->segment(4) == 'new' || $this->uri->segment(4) == 'active')) { ?>

	<input type="hidden" id="incident_ownerID" value="<?php echo $this->user->empID; ?>">		
	<input type="hidden" id="incident_owner" value="<?php echo $this->user->username; ?>"> <!--this will be the  HR/Accouting or admin owner of the incidet reply-->
	
	<!-- message type tabs -->
	<ul class="tabs">
		<li class="dbold tab-link" data-tab="tab-1">Note</li>
		<li class="dbold tab-link current" data-tab="tab-2">Reply</li>
	</ul>
	<hr/>
	<!-- note tab-->
	<div id="tab-1" class="tab-content">
		<h2>Add A Note</h2>
		<form id="note_form">
			<textarea id="note_msg" class="hidden tiny" style="height:200px;"></textarea>
			<br>
			<input type="submit" id="note_submit" class="btngreen" value="Submit" style="float:right;">
		</form>
		<br>
	</div>

	<!-- reply tab -->
	<div id="tab-2" class="tab-content current"> 
		<table>
			<tr>
				<td colspan="2"><h2>Add A Reply</h2></td>
			</tr>
			<tr>
				<td>Resolution Options:</td>
				<td>	
					<select id="resolution_options" style="width: 100%">
						<option></option>
						<option>The answer can be found in employee.tatepublishing.net</option>
						<option>Send custom response</option>
						<option>This is not an HR inquiry. Redirect to another department</option>
						<option>Further Information (investigation) is required</option>
						<?php if($this->uri->segment(4) != "new"){?>
						<option>Resolved</option>
						<?php } ?>
						<option>Closed</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><textarea id="custom_msg" class="hidden tiny" style="height:200px;"></textarea></td>
			</tr>
			<tr>
				<td align="left">
					<div class="sup_docs_div"><input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx" /><br></div>
					<div class="add_docs_label"><a href="#" class="label_add_docs">+ Add another attachments</a></div>
					<span style="color:#555; text-decoration: italic; position: relative; top: 3px; ">Upload up to 5 documents</span>
				</td>
				<td align="right" valign="bottom"><input type="submit" id="submit_reply" class="btngreen" value="Send"></td>
			</tr>
		</table>

	<!-- when user acess is full and his/her incident is resolved and closed -->
	<?php }elseif($this->user->access == "full" && 
				 ($this->uri->segment(4) == 'resolved' ||
				  $this->uri->segment(4) == 'cinc')){?>

		<table>
			<tr>
				<td colspan="2"><h2><b>Add a message to re-open this incident</b></h2></td>
			</tr>
			<tr>
				<td colspan="2"><textarea id="custom_msg" class="hidden tiny" style="height:200px;"></textarea></td>
			</tr>
			<tr>
				<td align="left"> 
					<div class="sup_docs_div"><input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx" /><br></div>
					<div class="add_docs_label"><a href="#" class="label_add_docs">+ Add another attachments</a></div>
					<span style="color:#555; text-decoration: italic; position: relative; top: 3px; ">Upload up to 5 documents</span>
				</td>
				<td align="right" valign="bottom"><input type="submit" id="submit_reply_reopen" class="btngreen" value="Send"></td>
			</tr>
		</table>
			
	<!-- when user is an employee and his/her incident is open -->	
	<?php } elseif($this->uri->segment(4) == 'emp' && $this->uri->segment(5) == 'open'){?>

		<table>
			<tr>
				<td colspan="2"><h2><b>Add A Reply</b></h2></td>
			</tr>
			<tr>
				<td>Resolution Options:</td>
				<td>
					<select id="resolution_options" style="width: 100%">
						<option>Reply</option>
						<option>Cancel Incident</option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><textarea id="custom_msg" class="hidden tiny" style="height:200px;"></textarea></td>
			</tr>
			<tr>
				<td align="left">
					<div class="sup_docs_div"><input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx" /><br></div>
					<div class="add_docs_label"><a href="#" class="label_add_docs">+ Add another attachments</a></div>
					<span style="color:#555; text-decoration: italic; position: relative; top: 3px; ">Upload up to 5 documents</span>
				</td>
				<td align="right" valign="bottom"><input type="submit" id="submit_reply" class="btngreen" value="Submit"></td>
			</tr>
		</table>

	<!-- when user is an employee and his/her incident is resolved -->
	<?php } elseif($this->uri->segment(4) == 'emp' &&
				   $this->uri->segment(5) == 'resolved'){

			// when incident have a remark
			if($check_remark !=0 )foreach ($check_remark as $rem => $remark){}

			// when incident don't have a remark
			if($check_remark == 0 || ($remark->post_id == $this->uri->segment(3) && $remark->remark_status != 0)){ ?>
		<h3><font color="Darkred">Please Rate this Incident</font></h3>
		<div id="remark_list">
			<input id="lbl_vs" type="radio" name="radio_survey" value="Very satisfied" checked>
			<label for="lbl_vs"><font color="#fff">Very satisfied</font></label> &nbsp;&nbsp;&nbsp;
			<input id="lbl_s" type="radio" name="radio_survey" value="Satisfied">
			<label for="lbl_s"><font color="#fff">Satisfied</font></label> &nbsp;&nbsp;&nbsp;
			<input id="lbl_ds" type="radio" name="radio_survey" value="Dissatisfied">
			<label for="lbl_ds"><font color="#fff">Dissatisfied</font></label> &nbsp;&nbsp;&nbsp;
			<input id="lbl_vds" type="radio" name="radio_survey" value="Very Dissatisfied">
			<label for="lbl_vds"><font color="#fff">Very Dissatisfied</font></label> 
		</div>
		<br><br>
		<textarea id="remark_comment" class="hidden tiny" style="height:100px;"></textarea><br>
		<input style="float:right;" type="submit" class="btngreen" id="remarkbtn" name="" value="Submit">
		<br>

	<?php } ?>

		<br><br>
		<!-- re open incident toggle -->
		<a id="reopen_incident">Re-open this incident?</a>
		<div id="reopen_incident_form">
			<hr>
			<table>
				<tr>
					<td colspan="2"><h2><b>Add a message to re-open this incident</b></h2></td>
				</tr>
				<tr>
					<td colspan="2"><textarea id="custom_msg" class="hidden tiny" style="height:200px;"></textarea></td>
				</tr>
				<tr>
					<td align="left">
						<div class="sup_docs_div"><input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx" /><br></div>
						<div class="add_docs_label"><a href="#" class="label_add_docs">+ Add another attachments</a></div>
						<span style="color:#555; text-decoration: italic; position: relative; top: 3px; ">Upload up to 5 documents</span>
					</td>
					<td align="right" valign="bottom"><input type="submit" id="submit_reply_reopen" class="btngreen" value="Send"></td>
				</tr>
			</table>			
		</div>	
	
	<!-- when user is an employee and his/her incident is closed -->
	<?php } elseif($this->uri->segment(4) == 'emp' &&
				   $this->uri->segment(5) == 'closed'){?>	

			<!-- re open incident -->
			<table>
				<tr>
					<td colspan="2"><h2><b>Add a message to re-open this incident</b></h2></td>
				</tr>
				<tr>
					<td colspan="2"><textarea id="custom_msg" class="hidden tiny" style="height:200px;"></textarea></td>
				</tr>
				<tr>
					<td align="left">
						<div class="sup_docs_div"><input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx" /><br></div>
						<div class="add_docs_label"><a href="#" class="label_add_docs">+ Add another attachments</a></div>
						<span style="color:#555; text-decoration: italic; position: relative; top: 3px; ">Upload up to 5 documents</span>
					</td>
					<td align="right" valign="bottom"><input type="submit" id="submit_reply_reopen" class="btngreen" value="Send" style="float:right;"><br>	</td>
				</tr>
			</table>
			
	<?php } ?>
		
	</form>
</div>

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {

	// add category
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

	// resolution message templates
	var message = '';
	var full_name = $("#fullname").val();

	var found = 'Hello <b>'+ full_name +'! </b><br><br> The answer to your inquiry/report can be found in the link below: <br><br> (INSERT LINK HERE)';
	var custom = 'Hello <b>'+ full_name +'! </b><br><br> This is the message that the HR will write on the box as a custom response to the employee.';
	var not_found = 'Hello <b>'+ full_name +'! </b><br><br> (INSERT CUSTOM MESSAGE HERE) <br><br> Upon evaluation, it is determined that your incident/inquiry may be best addressed by the (INSERT REDIRECT DEPARTMENT HERE). Their email address is (INSERT REDIRECT DEPARTMENT EMAIL ADDRESS HERE) which is CC in this email. <br><br> Please communicate with the (INSERT REDIRECT DEPARTMENT HERE) for the resolution of the incident.';
	var further = 'Hello <b>'+ full_name +'! </b><br><br> Please provide a copy of (INSERT NEEDED REQUIRMENTS HERE) <br><br> Thank you very much! <br><br> (INSERT HR COMPLETE NAME HERE) <br> (INSERT HR POSITION HERE) <br><br><b><u> Important Note </u> </b><br> If any responses/information/document is required from you, please reply within three (3) business days. If no response is received from you within three (3) business days, This incident will automatically closed. If no response/information/document is required from you, no action is required and you will received regular updates about this incident until its resolution';
	var resolve = 'Hello <b>'+ full_name +'! </b><br><br> (Your Incident is now resolved)';
	var closed = 'Hello <b>'+ full_name +'! </b><br><br> (Your Incident is now Closed)';
	var cancelled = 'Hello <b>HR NAME</b>' + '!<br><br><br> I want to cancel my incident.';
		
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
		    		
	    }else if($( "#resolution_options" ).val() == 'Cancel Incident'){
	    		tinyMCE.activeEditor.setContent(cancelled);
	    		
	    }

	    else{

	    		tinyMCE.activeEditor.setContent("");
	    		message = '';
	    }
    
	});

 	// display toolbar in textarea
	tinymce.init({
	selector: "textarea.tiny",	
	menubar : false,
	toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image"
	});	


	// add note
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

			$.ajax({
			type: "POST",
			url: "<?php echo $this->config->base_url(); ?>hr_cs/submit_notes",
			data: dataString,
			cache: false,
				success: function(result){
				alert("Success!");
				
				window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
                close();
				}
			});
		}

		return false;
	});

	// add survey
	$("#remarkbtn").click(function() {

		var custom_ans = tinyMCE.get('remark_comment').getContent();
		var hr_sname = $("#hr_username").val();
		var postempID = $("#cs_post_empID_fk").val();
		var datesub = $("#inci_datesubmited").val();
		var lastup =$("#inci_lastupdate").val();
		var ins_id = $("#categoryid").val();
		var remark = $('input[name="radio_survey"]:checked').val();

		var dataString = 'insedentid='+ ins_id + '&remark='+ remark + '&date_submit='+ datesub + '&last_update='+ lastup + '&post_emp_id='+ postempID + '&remark_msg='+ custom_ans + '&hr_username='+ hr_sname;
	
		if (remark == 'Very Dissatisfied' && custom_ans == '') {
			alert("Write a comment about to your remark");
		}else{

			$.ajax({
			type: "POST",
			url: "<?php echo $this->config->base_url(); ?>hr_cs/remark",
			data: dataString,
			cache: false,
				success: function(result){
				alert("Success! "+remark);
				window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/employee_dashboard/<?php echo $this->user->empID; ?>";
                close();
				}
			});
		}

		return false;
	});

	// re open incident
	$("#submit_reply_reopen").click(function() {

		
			var custom_ans = tinyMCE.get('custom_msg').getContent();
			var hr_sname = $("#hr_username").val();
			var ins_id = $("#categoryid").val();
			var status = 0;
			var surv_stat = 1;

			var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&survstatus=' + surv_stat + '&stat=' + status;

		if (custom_ans == '') {

			alert("Some Field is Empty!");
		}
		else{

			$.ajax({
			type: "POST",
			url: "<?php echo $this->config->base_url(); ?>hr_cs/remark_update",
			data: dataString,
			cache: false,
				success: function(result){
				alert("Success!");
				window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/employee_dashboard/<?php echo $this->user->empID; ?>";
                close();
				}
			});
		}
		return false;
	});

	// add reply
	$("#submit_reply").click(function() {
	
		var tab_typ = $('#tab_type').val();
		var status ='';

		// change status of the incident 
		if($("#resolution_options option:selected" ).val() == 'Resolved'){
	    	var	status = 3;
	    		
	    }else if($( "#resolution_options option:selected" ).val() == 'Closed'){
	    	var status = 4;
	    		
	    }else if($("#resolution_options option:selected" ).val() == 'Answered my question, thanks to HR (Resolved)'){
	    	var status = 3;
	    		
	    }else if($("#resolution_options option:selected" ).val() == 'Found the answer of my question on my own (Close)'){
	    	var status = 4;
	    		
	    }else{
	    	var status = 0;
	    }

		if (tab_typ == 'new') {

			var hd_own = $('#incident_owner').val();
			var hd_ownID = $('#incident_ownerID').val();
			var stat = 1;
			var inv_req = $('input[name="investigation_required_radio"]:checked').val();
			var ins_id = $("#categoryid").val();
			var ass_categ = $("#assign_category option:selected").val();
			var custom_ans = tinyMCE.get('custom_msg').getContent();

			var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ +'&custom_answer_msg='+ custom_ans + '&hr_username='+ hd_own + '&hr_userID='+ hd_ownID + '&inve_req=' + inv_req + '&reply=' + tab_typ + '&stat=' + stat;
			
		}else if(tab_typ == 'active' || tab_typ == 'emp' || tab_typ == 'resolved' || tab_typ == 'reopen' || tab_typ == 'cinc'){
			var custom_ans = tinyMCE.get('custom_msg').getContent();
			var hr_sname = $("#hr_username").val();
			var ins_id = $("#categoryid").val();

			var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&reply=' + tab_typ + '&stat=' + status;
			
		}
			if (custom_ans == '') {

				alert("Some Field is Empty!");
			}else{
					
					$.ajax({
					type: "POST",
					url: "<?php echo $this->config->base_url(); ?>hr_cs/custom_answer_solution",
					data: dataString,
					cache: false,
						success: function(result){
						alert("Success! ");
						$('#custom_ans_form')[0].reset();

							if (tab_typ == 'emp') {
		                    	 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/employee_dashboard/<?php echo $this->user->empID;?>/";
		                        close();
		                    }else if (tab_typ == 'reopen') {
		                    	 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/employee_dashboard/<?php echo $this->user->empID;?>/";
		                        close();
		                    }else{
								 window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
		                        close();
		                    }

						}
					});
			}
			return false;
		});

	// re open incident toggle
	$('#reopen_incident_form').hide();
	$('#reopen_incident').click(function(){
		$('#reopen_incident_form').toggle();
	});

	// display many attachments
	var sup_counter = 1;
	$('a.label_add_docs').hide();

	$('a.label_add_docs').click(function(){
	
	sup_counter += 1;			
		if( sup_counter <= 5 ){				
			$('.sup_docs_div').append('<input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx"/><br/>');
				if( sup_counter == 5 ){
					$('a.label_add_docs').hide();								
				}
		} 
	});

	$('.sup_docs').change(function(){
		if( sup_counter = 1 ){
			$('a.label_add_docs').show();
		}			
	});
			
});

</script>