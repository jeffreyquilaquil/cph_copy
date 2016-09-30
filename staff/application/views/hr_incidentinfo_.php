<?php if (isset($redirect) AND $redirect == true) { ?>
	<p>Post has submitted.</p>
	
<?php exit();} ?>
<style type="text/css">
	ul.canned_msg{
		list-style-type: none; display: inline;	

	}
	ul.canned_msg li {
		display: inline; margin-right: 10px;
	}
	.sup_docs { display: block;}
</style>


<div class="incident_info"> 

	<form name="hr_incident" method="post" enctype="multipart/form-data" action="">
		<h2>HR Incident Number <?php echo $ticket->cs_post_id; ?>
			<?php if( $ticket->cs_post_status == 3 ){
				echo '<b style="color:red;">(Resolved)</b>';
			} else if( $ticket->cs_post_status == 5) {
				echo '<b style="color:red;">(Cancelled)</b>';
			}?>

		</h2>
		<input type="hidden" name="ticket_id" value="<?php echo $ticket->cs_post_id; ?>" />
		<input type="hidden" name="who_posted" value="<?php echo $this->user->empID; ?>" />
		<input type="hidden" name="reply_empUser" value="<?php echo $this->user->username; ?>" />
		<table class="tableInfo">
			
			<tr>
				<td >Employee Name</td>			
				<td><?php echo $ticket->fname." ". $ticket->lname; ?></td>
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
				<td><?php echo date('F d, Y G:i a', strtotime($ticket->cs_post_date_submitted) ); ?></td>
			</tr>
			<tr>
				<td>Subject</td>
				<td><?php echo $ticket->cs_post_subject; ?></td>
			</tr>
			
			<?php
					//display due date for HR and accounting
					if( ($ticket->cs_post_agent == $this->user->empID) OR $this->access->accessFull == true ){
						echo '<tr><td>Due Date</td><td>'. date('F d, Y G:i a', strtotime($ticket->due_date)) .'</td></tr>';
					}
			?>
			<tr>
				<td>Category</td>
			<?php
				//check who viewed
				if( ( $this->user->empID == $ticket->cs_post_agent ) OR $this->access->accessFull == true ){
					echo '<td>';
					echo '<select name="assign_category">';
						echo '<option value="">--</option>';
						foreach( $categories as $key => $category ){
							echo '<option value="'.$category.'"';
								echo ( $category == $ticket->assign_category ) ? ' selected ': '';
							echo '>'.$category.'</option>';
						}
					//echo '</select><span class="updated">Updated!</span>';
					echo '</td>';
				} else if( $this->user->empID == $ticket->cs_post_empID_fk ) {
					echo '<td>'.(isset($ticket->assign_category) ? $ticket->assign_category : 'Unassigned').'</td>';
				} 
				
			?>
			</tr>
			<tr>
				<td>Urgency</td>
				<td>
					<?php 
					if( ( $this->user->empID == $ticket->cs_post_agent ) OR $this->access->accessFull == true ){

						$urgency_labels = ['Urgent', 'Need Attention', 'Not Urgent'];
						foreach ($urgency_labels as $key => $value) {
							$labels = $this->commonM->slugify($value);
							echo '<input id="'.$labels.'" type="radio" name="urgency" value="'.$value.'"';
							if( $ticket->cs_post_urgency == $value ){
								echo ' checked ';
							}
							echo '/> <label for="'.$labels.'" class="'.$labels.'">'.$value.'</label>';
						}
					} else if( $this->user->empID == $ticket->cs_post_empID_fk ) {
						if( $ticket->cs_post_urgency == '' ){
							echo 'Unassigned';
						} else {
							$labels = $this->commonM->slugify($ticket->cs_post_urgency);
							echo '<label for="'.$labels.'" class="'.$labels.'">'.$ticket->cs_post_urgency.'</label>';
						}
					}
					?>
				</td>
			</tr>		
		</table>

	<?php //all ticket thread

		foreach ($conversations as $key => $conversation) {
			//check what type of note has posted
			switch ($conversation->cs_msg_type) {
				case 0:	$div_class = 'employee_msg_lbl'; break;
				case 1: $div_class = 'hr_msg_lbl'; break;				
				default: $div_class = 'internal_notes_lbl';	break;
			}
			if( $conversation->cs_msg_type == 2 AND $this->user->empID == $ticket->cs_post_empID_fk ){
//				break;
			} else {
				echo '<div style="margin-top: 5px; display: block; border: solid 1px #ccc;">';
				echo '<div class="'.$div_class.'">';
					echo 'Message from: '. ( empty($conversation->reply_empUser) ? 'CPH' : $all_staff[ $conversation->reply_empUser ]->name );
					echo '<span style="float: right;">Date Submitted: '. date('F d, Y G:i a', strtotime( $conversation->cs_msg_date_submitted) ).'</span>';
				echo '</div>';
				echo '<div class="message" style="padding: 8px; text-align: justify;">';
					echo $conversation->cs_msg_text;
				echo '</div>';
					$attachments = json_decode( $conversation->cs_msg_attachment );
					
					if( isset($attachments) AND !empty($attachments) ){
						echo '<div class="attachments" style="padding: 5px;">';
						echo 'Attachment'.((count($attachments) > 1)?'s:':':').'  ';
							foreach ($attachments as $key => $value) {
								$file_name = pathinfo($value, PATHINFO_FILENAME);
								$file_ext = pathinfo($value, PATHINFO_EXTENSION);
								
								$href = $this->config->base_url().'attachment.php?u='.urlencode($this->textM->encryptText('cs_hr_attachments').'&f=';
								
									$href .= urlencode($this->textM->encryptText($file_name.'.'.$file_ext));
									$a = $file_name.'.'.$file_ext;
								

								echo '<a href="'.$href.'" target="_blank">';
								echo $a;
								echo '</a>';
							}
						echo '</div>';		
					}
				
				echo '</div>';
			}
			
		}

	//end ticket thread ?>
	<br/>
	<?php if( empty($ticket->post_id) ){ ?>

		
	<?php 
		//if resolved then add stats
		if( $ticket->cs_post_status == 3 AND ($this->user->empID == $ticket->cs_post_empID_fk) ){
			echo '<h2>Please rate the support staff\'s performance on this ticket</h2>';
			$ratings = [ 5 => 'Very Satisfied', 4 => 'Satisfied', 3 => 'Neutral', 2 => 'Dissatified', 1 => 'Very Dissatified'];
			echo '<ul class="canned_msg">';
			foreach ($ratings as $key => $rating) {
				echo '<li>';
					echo '<input type="radio" id="rating_'.$key.'" name="rating" value="'. $key .'" />';
					echo '<label for="rating_'.$key.'">'.$rating.'</label>';
				echo '</li>';
			}
			echo '</ul>';
			echo '<br/><br/>';
			echo '<textarea name="remark" class="hidden tiny" style="height:200px;"></textarea>';
			echo '<div style="text-align: right;">
		<input type="submit" id="submit_reply_rating" class="btn btngreen" value="Submit">
	</div>';
			
		} else {


			//notes posting
			//TODO check who is currently viewing the ticket
				//if poster display only Reply tab
				//if HR or Accouning display both Note tab and Reply tab
			if( $ticket->cs_post_status < 3){
				if ($this->user->empID == $ticket->cs_post_empID_fk ) {

					//display Reply tab only
					echo '<ul class="tabs">
						<li class="dbold tab-link current" data-tab="tab-1">Reply</li>
					</ul>';
				} else {
					//display both NOte and Reply Tab
					echo '<ul class="tabs">
						<li class="dbold tab-link current" data-tab="tab-1">Reply</li>
						<li class="dbold tab-link" data-tab="tab-2">Note</li>
					</ul>';
				}
			}
		}


	 ?>	
	 <hr/>
	 <?php if( $ticket->cs_post_status < 3 ){ ?>
	 	<div id="tab-1" class="tab-content current">
	 	<h2>Post a reply</h2>

	 	<?php if ($this->user->empID != $ticket->cs_post_empID_fk ) {
	 		echo '<p><span style="margin-right: 10px;">Canned message:</span>
	 		<select name="resolution" id="resolution_options">
				<option value="0">---</option>
				<option value="1">The answer can be found in employee.tatepublishing.net</option>
				<option value="3">This is not an HR inquiry. Redirect to another department</option>
				<option value="4">Further Information (investigation) is required</option>				
				<option value="5">Resolved</option>				
				<option value="6">Closed</option>
			</select></p>';
	 	} else {
	 		echo '<p>
	 		<ul class="canned_msg">
	 			<li><input id="reply_lbl" type="radio" name="inquiry_type" value="0" checked="checked"><label for="reply_lbl">Reply</label></li>
	 			<li><input id="canel_lbl" type="radio" name="inquiry_type" value="1"><label for="canel_lbl">Cancel Incident</label></li>
			</ul></p>';
	 	}?>


	 	<textarea id="reply_msg" name="reply_msg" class="hidden tiny" style="height:200px;"></textarea>

	 </div>
	 <div id="tab-2" class="tab-content">
	 	<h2>Post internal note</h2>
	 	<textarea id="note_msg" name="note_msg" class="hidden tiny" style="height:200px;"></textarea>
	 </div> 
	 <br/>	
	 <div style="float: right;">
		<input type="submit" id="submit_reply" class="btn btngreen" value="Submit">
	</div>
	 <div>
	 	<div class="sup_docs_div">	
			<input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx" />
		</div>
		<div class="add_docs_label"><a href="#" class="label_add_docs">+ Add another attachments</a></div>
		<span style="color:#555; text-decoration: italic; position: relative; top: 3px; ">Upload up to 5 documents</span>
	</div> 
	 <?php } //end 3 ?>
	 	

	 


	</form>



<?php } ?>


<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	$(function(){
		//check ratings
		$('#submit_reply_rating').click(function(e){
			e.preventDefault();
			var rating = parseInt( $('form input[name="rating"]:checked').val() );

			var remark = $('textarea[name="remark"]').val();
console.log(rating);
			if( isNaN(rating) ){
				alert('Please provide your rating.');
			} else if( (rating == 1 || rating == 2 || rating == 'undefined') && remark == '' ){
				alert('Please provide your remark on your rating');
			} else {
				$('form').submit();
			}
		});


		// display toolbar in textarea
		tinymce.init({
		selector: "textarea.tiny",	
		menubar : false,
		toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image "
		});	

		// display many attachments
		var sup_counter = 1;
		$('a.label_add_docs').hide();
		$('.updated').hide();
		$('a.label_add_docs').click(function(e){
			e.preventDefault();
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

		$('input[name="inquiry_type"]').on('change', function() {

			if ($('input[name="inquiry_type"]:checked').val() == 1) {
           		tinyMCE.get('reply_msg').setContent('<p>Hi <?php //echo $ticket->reply_empUser; ?>,</p><p>This request is considered cancelled.</p><p>Thanks!</p>', {format: 'html'});
        	}else if($('input[name="inquiry_type"]:checked').val() == 0){
        		tinyMCE.get('reply_msg').setContent('');
        	}   		
		});

		$('select#resolution_options').change(function(){
			var that = $(this);
			var content = '<p>Hello,</p>';
			var selected = parseInt(that.val());
			
			switch( selected ){
				case 1: content = content + '<p>The answer to your inquiry/report can be found in the link below:</p><p>(INSERT LINK HERE)</p>';	break;
				case 3: content = content + '<p>(INSERT CUSTOM MESSAGE HERE)</p><p>Upon evaluation, it is determined that your incident/inquiry may be best addressed by the (INSERT REDIRECT DEPARMENT HERE). Their email address in (INSERT REDIRECT DEPARTMENT EMAIL ADDRESS HERE) which is CC in this email.</p><p>Please communicate with the (INSERT REDIRECT DEPARTMENT HERE) for the resolution of the incident.'; break;
				case 4: content = content + '<p>Please provide a copy of (INSERT NEEDED REQUIREMENTS HERE)</p><p>Thank you very much!</p><p>(INSERT HR COMPLETE NAME HERE) <br> (INSERT HR POSITION HERE)</p> <br/><p><b><u> Important Note </u></b><br> If any responses/information/document is required from you, please reply within three (3) business days. If no response is received from you within three (3) business days, This incident will automatically closed. If no response/information/document is required from you, no action is required and you will received regular updates about this incident until its resolution</p>'; break;
				case 5: content = content + '<p>Your inquiry is now resolved.'; break;
				case 6: content = content + '<p>Your inquiry is now closed.'; break;
				default: content = '';	break;
			}
			tinyMCE.get('reply_msg').setContent( content, {format: 'html'});
			
		});

		$('select[name="assign_category"').change(function(){	
			var that = $(this);
			
			$.ajax({
				url: '<?php echo $this->config->base_url().'hr_cs/update/'.$ticket->cs_post_id ?>',
				type: 'POST',
				dataType: 'JSON',
				data: {  category: that.val(), which: 'category' },
				success: function(data){
					alert('Category has been updated!');
				}
			});
		});

		$('input[name="urgency"]').on('change', function() {
			var that = $(this);
			$.ajax({
				url: '<?php echo $this->config->base_url().'hr_cs/update/'.$ticket->cs_post_id ?>',
				type: 'POST',
				dataType: 'JSON',
				data: { urgency: that.val(), which: 'urgency' },
				success: function(data){
					alert('Ticket urgency has been updated!');
				}
			});
		});
		
	});

	
</script>


