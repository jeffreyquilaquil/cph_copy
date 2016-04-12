<style>

	.radio-pos{
		margin-left: 50px;
		margin-top: 10px;
	}

	.button {
	    background-color: #50F060;
	    border: none; 
	    color: black;
	    padding: 7px 35px 7px 35px;
	    margin-top:5px;
	    text-decoration: none;
	    display: inline-block;
	
}

</style>

	<div>
	<form method="POST" action="<?php echo $this->config->base_url(); ?>hr_cs/askhr" enctype="multipart/form-data">
		<table class="tableInfo">

			<tr>
				<td colspan="2" style="font-style: italic">
					<img src="https://app01.tatepublishing.net/~shem/staff/css/images/logo.png" style="float: left; margin-right: 8px;">							
					<h3>Welcome to HR!</h3>
					WAIT! Before sending your question to HR, please check <a href="#">employee.tatepublishing.net</a> first. Your question may already
					be answered there. If your question is already answered in employee.tatepublishing.net, HR shall reply with the link to the answer.
				</td>
				<td>
				</td>
			</tr>
			<tr>
				<td>
					What's your inquiry/report to HR about? 
				</td>
				<td align="center">
					<input type="text" name="cs_post_subject" style="width: 100%">
				</td>
			</tr>
			<tr>
				<td colspan="2">
					Select the urgency of this request: <br>

						<input type="radio" class="radio-pos" name="cs_post_urgency" value="Urgent"> I am not able to continue working/department work will stop if this inquiry is not resolvd. <br>
						<input type="radio" class="radio-pos" name="cs_post_urgency" value="Need Attention"> This can wait, but work will be delayed if this is not resolved soon <br>
						<input type="radio" class="radio-pos"name="cs_post_urgency" value="Not Urgent"> Take your time. I can wait for this information.	
				</td>
			</tr>
			<tr>
				<td colspan="2">
					Explain the details of your inquiry in the box below:<br>
						<textarea class="hidden tiny" name="askHR_details" style="height:350px;"></textarea>
				</td>
			</tr>
			<tr>
				<td>
					<div class="sup_docs_div">
						<input type="file" name="arr_attachments[]" class="sup_docs" accept=".jpg, .png, .doc, .docx" /><br>
					</div>
					<div class="add_docs_label">
						<a href="#" class="label_add_docs">+ Add another attachments</a>
					</div>
				</td>
				<td valign="bottom" align="right">
					<input type="submit" class="button" value="SUBMIT INQUIRY">
				</td>
			</tr>
			
		</table>
	</form>			
	</div>


<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

// ====== DISPLAY TOOLBARS IN TEXTAREA =====
$(function () { 
		
	tinymce.init({
		selector: "textarea.tiny",	
		menubar : false,
		toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image"
	});	
});

// ===== CALL MANY ADD ATTACHMENTS ======
$(function(){
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


