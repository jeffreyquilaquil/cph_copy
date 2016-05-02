<?php foreach ($EmployeeIncidentEvents as $key => $send): ?>
<?php endforeach ?>

<?php if ($this->uri->segment(4)=='send'){?>
	
<div id="send_message_to_hr_form">
	<table class="tableInfo">
	<tr>
		<td colspan="2"><h2>Send Email</h2></td>
	</tr>
	<tr>
		<td>From:</td>
		<td><?php echo $send->fname .' '. $send->lname;?></td>
	</tr>
	<tr>
		<td>To:</td>
		<td>hr.cebu@tatepublishing.net</td>
	</tr>
	<tr>
		<td>Subject:</td>
		<td><input type="text" name="" value="" placeholder=""></td>
	</tr>
	<tr>
		<td colspan="2"><textarea class="hidden tiny" style="height:200px; resize: none; font-size: left;"></textarea></td>
	</tr>
	<tr>
		<td colspan="2" align="right"><input type="submit" name="" value="Send" class="btngreen"></td>
	</tr>
	</table>
</div>
<?php } elseif ($this->uri->segment(4)=='cancel') { ?>	

<div id="cancel_incident_form"> 
	<table class="tableInfo">
		<tr>
			<td><h2>You are about to cancel HR incident number <?php echo $send->cs_post_id; ?><br><small>Please tell us why (optional):</small></h2></td>			
		</tr>
		<tr>
			<td><textarea style="height:200px; resize: none; font-size: left;"></textarea></td>
		</tr>
		<tr>
			<td align="right"><input type="button" name="" class="btngreen" value="Cancel Incident"></td>
		</tr>	
	</table>
</div>

<?php } ?>

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">

	$(function(){
		
		// ===== DISPLAY TOOLBAR IN TEXTAREA =====
		tinymce.init({
		selector: "textarea.tiny",	
		menubar : false,
		toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image"
		});	
	});

</script>