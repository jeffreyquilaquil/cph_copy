<h2>Forgot Password</h2>
<hr/>
<p>Enter username or email to reset password</p>
<table class="tableInfo">
	<tr><td width="20%">Username</td><td><input id="username" type="text" class="forminput"/></td></tr>
	<tr><td>Email</td><td><input id="email" type="text" class="forminput"/></td></tr>
	<tr><td><br/></td><td valign="center"><button id="btnsubmit" class="padding5px">Submit</button> <img id="loadimg" class="hidden" src="<?= $this->config->base_url().'css/images/small_loading.gif'?>" width="30px"/></td></tr>
</table>

<script type="text/javascript">
	$(function(){
		$('#btnsubmit').click(function(){
			username = $('#username').val();
			email = $('#email').val();
			
			if((username=='' && email=='') || (username!='' && email!='')){
				alert('Please input EITHER username OR email.');
			}else{
				$(this).attr('disabled', 'disabled');
				$('#loadimg').removeClass('hidden');
				$.post('<?= $this->config->item('career_uri') ?>', {username:username, email:email}, 
				function(d){					
					parent.$.fn.colorbox.close();
					alert(d);
				});
			}
		});
	});
</script>
