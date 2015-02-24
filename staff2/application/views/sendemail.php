<?php
	echo '<h2>Send Email';
	if(isset($row->name)) echo ' to '.$row->name;
	echo '</h2><hr/><br/>';
	
	if($sent){
		echo '<p class="errortext">Message sent.</p>';
	}else{
?>


<table class="tableInfo">
	<!--<tr>
		<td width="20%" class="weightbold">Custom Emails</td>
		<td>
			<select id="custemails" class="forminput">
				<option value=""></option>
			<?php
				/* foreach($emailTemplates AS $e):
					echo '<option value="'.$e->emailID.'|'.$e->emailName.'">'.$e->emailName.'</option>';
				endforeach; */
			?>
			</select>
		</td>
	</tr>-->
<form action="" method="POST" onSubmit="return validateform()">
	<tr>
		<td class="weightbold">Subject</td>
		<td><input type="text" class="forminput" name="subject" value="" id="subject"/></td>
	</tr>
	<tr>
		<td class="weightbold">To<br/><i class="weightnormal" style="font-size:10px;">(Separate email addresses with comma)</i></td>
		<td><input type="text" class="forminput" name="to" value="<?= ((isset($row->email))? $row->email : '') ?>" id="to"/></td>
	</tr>
	<tr>
		<td class="weightbold">Message</td>
		<td><textarea name="message" id="message" style="height:160px;"></textarea></td>
	</tr>
	<tr>
		<td><br/></td>
		<td><input type="submit" value="Send Email"/></td>
	</tr>
</form>
</table>

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
$(function () { 	
	$('#custemails').change(function(){
		if($(this).val()!=''){
			xx = $(this).val().split('|');
			$('#subject').val(xx[1]);
			$('#message').val(xx[1]);	
				
		}else{
			$('#subject').val('');
		}
	});
});
tinymce.init({
	selector: "textarea",	
	menubar : false,
	plugins: [
		"link",
		"code",
		"table"
	],
	toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code"
});

function validateform(){
	validtxt = '';
	if($('#subject').val()=='' || $('#to').val()=='' || tinyMCE.get('message').getContent()==''){
		validtxt = 'Please input all fields\n';
		alert(validtxt);
		return false;
	}else{
		return true;
	}
}
</script>

<?php } ?>