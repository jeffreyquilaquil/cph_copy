
<div class="incident_info"> 



<input type="hidden" id="tab_type" value="<?php echo $this->uri->segment(4); ?>">
<input type="hidden" id="categoryid" name="postid" value="<?php echo $ticket->cs_post_id; ?>">
<input type="hidden" id="hr_username" name="postid" value="<?php echo $this->user->username; ?>">
<input type="hidden" id="inci_datesubmited" value="<?php echo $ticket->cs_post_date_submitted; ?>">
<input type="hidden" id="inci_lastupdate" value="<?php echo $ticket->last_update; ?>">
<input type="hidden" id="cs_post_empID_fk" value="<?php echo $ticket->cs_post_empID_fk; ?>">

<!-- incident info form -->
<form id="custom_ans_form">
	<table class="tableInfo">
		<tr>
			<td colspan="2"><h2>HR Incident Number <?php echo $ticket->cs_post_id; ?></h2></td>	
		</tr>
		<tr>
			<td >Employee Name</td>
			<input type="hidden" id="fullname" value="<?php echo $ticket->fname." ". $ticket->lname; ?>">
			<td><?php echo $ticket->fname." ". $ticket->lname; ?> </td>
		</tr>
		<tr>
			<td>Department</td>
			<td><?php echo $ticket->dept; ?></td>
		</tr>
		<tr>
			<td>Position</td>
			<td><?php echo $ticket->title; ?></td>
		</tr>
		<tr>
			<td>Immediate Supervisor</td>
			<td><?php echo $ticket->supervisor; ?></td>
		</tr>
		<tr>
			<td>Date Submitted</td>
			<td><?php echo date_format(date_create($ticket->cs_post_date_submitted), 'F d, Y G:i a'); ?></td>
		</tr>
		<tr>
			<td>Subject</td>
			<td><?php echo $ticket->cs_post_subject; ?></td>
		</tr>
		<tr>
			<td>Priority level</td>
			<td>
				<?php 
				echo '<div class="'. str_replace(' ', '', strtolower($ticket->cs_post_urgency) ).'">'.$ticket->cs_post_urgency.'</div>';
				?>
			</td>
		</tr>
		<!-- assigning of category -->
		<?php if( $this->user->empID != $ticket->cs_post_agent ){ ?>
		
		<tr>
			<td>Assigned to</td>
			<td>
				<?php echo $all_staff_empID[ $ticket->cs_post_agent ]->name; ?>
			</td>
		</tr>
		<?php } //end assigned to ?>
		<?php if($this->uri->segment(4)== 'new'){?> 
		
		<?php }elseif($this->uri->segment(4) == 'active' || $this->uri->segment(4) == 'resolved'){ ?>
			<tr>
				<td>Due date</td>
				<td><?php echo date_format(date_create($ticket->due_date), 'F d, Y'); ?></td>
			</tr>
		<?php } ?>

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
		
		<!-- choosing priority level & resolved date -->
		<tr>
			<td>Choose priority level & Resolved date</td>
			<td>
				<input id="lbl_urgent" type="radio" name="incident_urgency" value="Urgent" checked="checked">
				<label for="lbl_urgent" class="urgent">Urgent (1 day)</label>&nbsp;&nbsp;&nbsp;
				<input id="lbl_need_attention" type="radio" name="incident_urgency" value="Need Attention" >
				<label for="lbl_need_attention" class="need_attention">Need Attention (2 days)</label>&nbsp;&nbsp;&nbsp;
				<input id="lbl_not_urgent" type="radio" name="incident_urgency" value="Not Urgent" >
				<label for="lbl_not_urgent" class="not_urgent">Not Urgent (3 days)</label>&nbsp;&nbsp;&nbsp;
			</td>
		</tr>

		<!-- when incident is either active, resolved, closed or displayed in employee dashboard-->
		<?php }elseif ($this->uri->segment(4) == 'active' || 
						$this->uri->segment(4) == 'resolved' || 
						$this->uri->segment(4) == 'cinc' || 
						$this->uri->segment(4) == 'emp') { ?>

				<tr>
					<td>Assign Category</td>
					<td><?php if($value->assign_category==''){echo '-';}else{echo $value->assign_category;} ?></td>
				</tr>
		<?php } ?>  
	</table>

	<table>
	<!-- array show conversations -->
		<?php foreach ($conversation as $key => $conve){
		if ($conve->cs_msg_type == 0) {?>
		<!-- employee messages -->
		<tr>
			<td style="border: solid 1px #4a7ebb;">
				<div class="employee_msg_lbl">
				Message from: <?php echo $all_staff[ $conve->reply_empUser ]->name ; ?>
				<span style="float:right">Date Submitted: <?php echo date_format(date_create($conve->cs_msg_date_submitted), 'F d, Y G:i a'); ?></span>
				</div>
				<div class="messages">
				<?php 

				echo "<br>".$conve->cs_msg_text."<br>";
				
				$file = $conve->cs_msg_attachment;
				
				if($file != null){
					$docs_url = json_decode($conve->cs_msg_attachment);

					foreach ($docs_url as $l_file => $file) {
						$link_file = str_replace(FCPATH,'', $file);
						$file_link = $this->config->base_url()."".$link_file;
						$link = explode("/",$file_link);
						

						echo "<a href='".$this->config->base_url()."".$link_file."' target='_blank'>";
						echo $link[7];
						echo "</a></br>";
					}
				}else{
					
				} ?>
				</div>
			</td>
		</tr>

		<!-- hr messages-->
		<?php }elseif($conve->cs_msg_type == 1){ ?>
		<tr>
			<td style="border: solid 1px #be4b48;">
				<div class="hr_msg_lbl">
				Message from: <?php echo $all_staff[ $conve->reply_empUser ]->name ; ?>
				<span style="float:right;">Date Submitted: <?php echo date_format(date_create($conve->cs_msg_date_submitted), 'F d, Y G:ia'); ?></span>
				</div>	
				<div class="messages">		
				<?php 

				echo $conve->cs_msg_text."<br>";

					$file = $conve->cs_msg_attachment;
					if($file != null){
						$docs_url = json_decode($conve->cs_msg_attachment);
					echo "<b>FILE : </b>";
					foreach ($docs_url as $l_file=>$file) {
						$link_file = str_replace(FCPATH, '', $file);
						$file_link = $this->config->base_url()."".$link_file;
						$link = explode(".",$file_link);
						echo "<a href='".$this->config->base_url()."".$link_file."' target='_blank' style='margin-right: 5px; border:1px solid #000; text-decoration: none !important; background-color: #E4F5F8; padding:1px; border-radius: 2px;'>";
						echo "file.".$link[3].", ";
						echo "</a>";
					}
					
				}else{
					
				} echo "<br><br>"; ?>
				</div>
			</td>
		</tr>
		<!-- internal notes -->
		<?php } elseif($conve->cs_msg_type == 2){ 
			if($this->access->accessFull == true || $this->access->accessHR == true| $this->access->accessFinance == true){

		?>
		<tr>
			<td style="border: solid 1px black;">
				<div class="internal_notes_lbl">
				Message from: <?php echo $all_staff[ $conve->reply_empUser ]->name ; ?>
				<span style="float: right">Date Submitted: <?php echo date_format(date_create($conve->cs_msg_date_submitted), 'F d, Y G:ia'); ?></span>
				</div>
				<div class="messages">
				<?php 

					$file = $conve->cs_msg_attachment;

					if($file != null){
					$aReplace = array('["', '"]');
					$coordsReplaced = str_replace($aReplace , '', $file);
					$link = stripslashes($coordsReplaced);
					$exp = explode('","',$link);
					foreach ($exp as $link_file) {
						echo $this->config->base_url()."".$link_file."<br>";
					}
				}else{
					
				}
				echo $conve->cs_msg_text; ?>
				</div>
			</td>
		</tr>
	<?php } } } ?>
	</table>

	<br>

	<!-- When user access is full/hr/finance and his/her incident is new/active --> 
	<?php if(($this->access->accessFull == true || $this->access->accessHR == true || $this->access->accessFinance == true) &&
			(in_array( $this->uri->segment(4), ['new', 'active', 'onproc'] ) ) ) { ?>

	<input type="hidden" id="incident_ownerID" value="<?php echo $this->user->empID; ?>">	

	<!-- This will be the  HR/Accouting or admin owner of the incidet reply -->	
	<input type="hidden" id="incident_owner" value="<?php echo $this->user->username; ?>"> 
	
	<!-- message type tabs -->
	<ul class="tabs">
		<li class="dbold tab-link current" data-tab="tab-1">Reply</li>
		<li class="dbold tab-link" data-tab="tab-2">Note</li>
	</ul>
	<hr/>

	<!-- reply tab-->
	<div id="tab-1" class="tab-content current">
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
				<div class="sup_docs_div">
				<!--NEW REPLY-->
				<form enctype="multipart/form-data">
					<input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx" />
				</form>
				<br></div>
				<div class="add_docs_label"><a href="#" class="label_add_docs">+ Add another attachments</a></div>
				<span style="color:#555; text-decoration: italic; position: relative; top: 3px; ">Upload up to 5 documents</span>
			</td>
			<td align="right" valign="bottom"><input type="submit" id="submit_reply" class="btngreen" value="Send"></td>
		</tr>
	</table>

	<!-- when user acess is full and his/her incident is resolved and closed -->
	<?php }elseif($this->user->access == "full" && ($this->uri->segment(4) == 'resolved' ||  $this->uri->segment(4) == 'cinc')){?>

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
				<input id="reply_lbl" type="radio" name="inquiry_type" value="Reply" checked="checked">
				<label for="reply_lbl">Reply</label> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input id="canel_lbl" type="radio" name="inquiry_type" value="Cancel Incident">
				<label for="canel_lbl">Cancel Incident</label>
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
	<?php } elseif($this->uri->segment(4) == 'emp' && $this->uri->segment(5) == 'resolved'){

			// when incident have a remark
		if($check_remark != 0 )foreach ($check_remark as $rem => $remark){}

		// when incident don't have a remark
		if($check_remark == 0 || ($remark->post_id == $this->uri->segment(3) && $remark->remark_status != 0)){ ?>
		
		<h2>Please rate incident</h2>
		<div>
			<input id="lbl_vs" type="radio" name="radio_survey" value="Very Satisfied" checked>
			<label for="lbl_vs">Very satisfied</label> &nbsp;&nbsp;&nbsp;
			<input id="lbl_s" type="radio" name="radio_survey" value="Satisfied">
			<label for="lbl_s">Satisfied</label> &nbsp;&nbsp;&nbsp;
			<input id="lbl_ds" type="radio" name="radio_survey" value="Dissatisfied">
			<label for="lbl_ds">Dissatisfied</label> &nbsp;&nbsp;&nbsp;
			<input id="lbl_vds" type="radio" name="radio_survey" value="Very Dissatisfied">
			<label for="lbl_vds">Very Dissatisfied</label> 
		</div>
		<br>
		<textarea id="remark_comment" class="hidden tiny" style="height:100px;"></textarea>
		<br>
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
	<?php } elseif($this->uri->segment(4) == 'emp' && $this->uri->segment(5) == 'closed'){?>	

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

	</div>

	<!-- note tab -->
	<div id="tab-2" class="tab-content "> 		
	<h2>Add A Note</h2>
		<form method="POST" action="<?php echo $this->config->base_url(); ?>hr_cs/add_internal_note" target='_parent' enctype="multipart/form-data" target=''>
				
			<input type="hidden" name="incident_id" value="<?php echo $ticket->cs_post_id; ?>">
			<input type="hidden" name="reply_username" value="<?php echo $this->user->username; ?>">
			<input type="hidden" name= "assign_category" value="<?php echo $ticket->assign_category; ?>">

			<textarea class="hidden tiny" name= "internal_note_textarea" style="height:200px;"></textarea>
			<br>
			<input type="submit" class="btngreen" value="Submit" style="float:right;">
		</form>
	<br>	
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

	$('input[name="inquiry_type"]').on('change', function() {

		if ($('input[name="inquiry_type"]:checked').val() == "Cancel Incident") {
           tinyMCE.activeEditor.setContent(cancelled);
        }else if($('input[name="inquiry_type"]:checked').val() == "Reply"){
        	tinyMCE.activeEditor.setContent("");
        }
   		
		});

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

	    else{

	    		tinyMCE.activeEditor.setContent("");
	    		message = '';
	    }
    
	});

 	// display toolbar in textarea
	tinymce.init({
	selector: "textarea.tiny",	
	menubar : false,
	toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image "
	});	


	// Add internal note
	/*
	$("#note_submit").click(function() {

		var custom_ans = tinyMCE.get('note_msg').getContent();
		var hr_sname = $("#hr_username").val();
		var ins_id = $("#categoryid").val();
		var ass_categ = $("#assign_category option:selected").val();

		var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&assign_category=' + ass_categ; 
		
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

	*/

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
	    	var status = 1;
	    }

		if (tab_typ == 'new') {

			if(status == 4){
				var hd_own = $('#incident_owner').val();
				var ins_id = $("#categoryid").val();
				var custom_ans = tinyMCE.get('custom_msg').getContent();
				var status_cancel = 5;
				var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hd_own + '&reply=' + tab_typ + '&stat=' + status_cancel;
				
			}else{

				 //e.preventDefault(); //upload
        
        		//var formData = $(this).before("<input name='arr_attachments[]' type='file'/>");


				var hd_own = $('#incident_owner').val();
				var hd_ownID = $('#incident_ownerID').val();
				var inv_req = $('input[name="incident_urgency"]:checked').val();
				var ins_id = $("#categoryid").val();
				var ass_categ = $("#assign_category option:selected").val();
				var custom_ans = tinyMCE.get('custom_msg').getContent();

				var dataString = 'insedentid='+ ins_id + '&assign_category=' + ass_categ +'&custom_answer_msg='+ custom_ans + '&hr_username='+ hd_own + '&hr_userID='+ hd_ownID + '&inve_req=' + inv_req + '&reply=' + tab_typ + '&stat=' + status; // + '&upfile=' + formData

			}

			
			
		}else if(tab_typ == 'active' || tab_typ == 'emp' || tab_typ == 'resolved' || tab_typ == 'reopen' || tab_typ == 'cinc' || tab_typ == 'onproc'){
			var new_stat = '';
			if(status == 4){
				var new_stat = 5;
			}else{
				var new_stat = status;
			}

			var custom_ans = tinyMCE.get('custom_msg').getContent();
			var hr_sname = $("#hr_username").val();
			var ins_id = $("#categoryid").val();

			var dataString = 'insedentid='+ ins_id + '&custom_answer_msg='+ custom_ans + '&hr_username='+ hr_sname + '&reply=' + tab_typ + '&stat=' + new_stat;
			
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

	$('#extend_date_form').hide();
	$('#extend_date').click(function(){

		$('#extend_date_form').toggle();
	})
			
});

</script>
