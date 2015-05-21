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
	<tr <?= (($this->access->accessHR==false)?'class="hidden"':'')?>>
		<td class="weightbold">From<br/><a id="sendfromHR" class="fs11px cpointer">[Click here to send from HR]</a></td>
		<td>
			<input type="text" class="forminput" name="from" value="<?= $this->user->email ?>" id="from"/>
			<input type="hidden" name="fromName" value="<?= $this->user->name ?>"/>
		</td>
	</tr>
	<tr>
		<td class="weightbold">Subject</td>
		<td><input type="text" class="forminput" name="subject" value="<?= $subject ?>" id="subject"/></td>
	</tr>
	<tr>
		<td class="weightbold">To<br/><i class="weightnormal" style="font-size:10px;">(Separate email addresses with comma)</i></td>
		<td><input type="text" class="forminput" name="to" value="<?= $to ?>" id="to"/></td>
	</tr>
	<tr>
		<td class="weightbold">Message</td>
		<td><textarea name="message" id="message" style="height:160px;"><?= $message ?></textarea></td>
	</tr>
	<tr>
		<td><br/></td>
		<td>
			<input type="submit" value="Send Email" class="btnclass"/>
		</td>
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
	
	$('#sendfromHR').click(function(){
		$('#from').val('hr.cebu@tatepublishing.net');
		$('input[name=fromName]').val('HR Cebu');
	});
});
tinymce.init({
	selector: "textarea",
	relative_urls : false,
	remove_script_host : false,
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
	if($('#from').val()=='' || $('#subject').val()=='' || $('#to').val()=='' || tinyMCE.get('message').getContent()==''){
		validtxt = 'Please input all fields\n';
		alert(validtxt);
		return false;
	}else{
		return true;
	}
}
</script>

<?php } ?>