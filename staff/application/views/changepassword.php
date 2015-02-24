<?php
if($this->uri->segment(2)=='required') echo '<h2>Username and password is the same. Change password is required.</h2>';
else echo '<h2>Update password</h2>';
echo '<hr/>';

if($updated){
	echo '<span class="errortext">Password has been updated.</span>';
}else{
	if(!empty($error)){
		echo '<span class="errortext">'.$error.'</span>';
	} 
?>
<table class="tableInfo">
<form action="" method="POST" onSubmit="return validateform();">
	<tr>
		<td width="30%">Current Password</td>
		<td><input type="password" class="forminput" name="curpassword" id="curpassword" value="<?= ((isset($_POST['curpassword']))?$_POST['curpassword']:'') ?>"/></td>
	</tr>
	<tr>
		<td>New Password</td>
		<td><input type="password" class="forminput" name="newpassword" id="newpassword" value="<?= ((isset($_POST['newpassword']))?$_POST['newpassword']:'') ?>"/></td>
	</tr>
	<tr>
		<td>Confirm Password</td>
		<td><input type="password" class="forminput" name="confirmpassword" id="confirmpassword" value="<?= ((isset($_POST['confirmpassword']))?$_POST['confirmpassword']:'') ?>"/></td>
	</tr>
	<tr>
		<td><br/></td>
		<td><input type="submit" value="Submit" class="btnclass"/></td>
	</tr>
</form>
</table>

<script type="text/javascript">
	function validateform(){
		if($('#curpassword').val()=='' || $('#newpassword').val()=='' || $('#confirmpassword').val()==''){
			alert('Please fill up all the fields.');
			return false;
		}else{
			return true;
		}
	}
</script>
<?php } ?>