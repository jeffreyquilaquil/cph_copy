<?php
	if(isset($submitted) && !empty($submitted)){
		echo '<div class="tacenter">';
		echo $submitted;
		echo '<a href="'.$this->config->base_url().'referafriend/">Refer another friend?</a>';
		echo '</div>';
	}else{
?>

<style type="text/css">
	.btnsub{border:1px solid #000; cursor:pointer; margin-bottom:5px;}
</style>
<br/>
<form action="" method="POST" id="myForm" onSubmit="return validate();">
<div id="1stform">
	<h3>Do you have friends you want to refer to apply to Tate?</h3>
	Please enter their details below:
	<table class="tableInfo">
		<tr>
			<td width="30%">Last Name</td>
			<td><input type="text" class="forminput" name="lastName" id="lastName" required/></td>
		</tr>
		<tr>
			<td>First Name</td>
			<td><input type="text" class="forminput" name="firstName" id="firstName" required/></td>
		</tr>
		<tr>
			<td>Your friend's email address</td>
			<td>
				<input type="email" class="forminput" name="email[]" required/><br/>
				<a href="javascript:void(0);" onClick="addemail(this);">Add another email address</a>
			</td>
		</tr>
		<tr>
			<td>Your friend's contact number</td>
			<td>
				<input type="text" class="forminput" name="number[]" required/><br/>
				<a href="javascript:void(0);" onClick="addcontact(this);">Add another contact number</a>
			</td>
		</tr>
		<tr>
			<td><br/></td>
			<td>
				<input type="hidden" value="0" id="wahtSub"/>
				<input type="submit" class="btnclass btnsub" value="Submit" style="background-color:green;"/>
			</td>
		</tr>
	</table>
</div>
<div id="2ndform" class="tacenter hidden">
	The system will now send an email to your friend <b id="fname"></b> inviting him/her to apply for any of the open positions in Tate Publishing.<br/>Your friend will also be notified that you referred him/her to apply.
	<h3>Do you want to continue?</h3>
	<input type="submit" class="btnclass btnsub btngreen" value="Yes. Please proceed." style="background-color:green;"/><br/>
	<input onClick="pleaseBack();" type="button" value="Wait, let me go back." class="btnclass btnsub btnred"/><br/>
	<input onClick="parent.$.fn.colorbox.close();" type="button" value="Cancel." class="btnclass btnsub btnred"/>

</form>

<script type="text/javascript">
	function addemail(t){
		$('<input type="email" class="forminput" name="email[]"/><br/>').insertBefore(t);
	}
	
	function addcontact(t){
		$('<input type="text" class="forminput" name="number[]"/><br/>').insertBefore(t);
	}
	
	function pleaseBack(){
		$('#wahtSub').val(0);
		$('#1stform').removeClass('hidden');
		$('#2ndform').addClass('hidden');
	}
	
	function validate(){
		valid = $('#myForm')[0].checkValidity();
		if(valid==true && $('#wahtSub').val()==0){
			$('#wahtSub').val(1);
			$('#fname').text($('#firstName').val());
			$('#2ndform').removeClass('hidden');
			$('#1stform').addClass('hidden');
			return false;
		}else{
			displaypleasewait();
			return true;
		}		
	}
</script>

<?php } ?>