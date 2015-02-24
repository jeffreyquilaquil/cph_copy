<h2>Upload Signature</h2>
<hr/>
<p class="errortext"><?= $errortxt ?></p>
<?php
	if(file_exists($dir.'/signature.png')){
		echo 'This is your current signature:<br/><img src="'.$this->config->base_url().$dir.'/signature.png" style="border:1px solid #000; padding:10px; background-color:#ccc;"/><br/><br/>';
	}
?>

<form id="formUpload" action="" method="POST" enctype="multipart/form-data">
    Upload signature:<br/>
    <input type="file" name="fileToUpload" id="fileToUpload">   	
	<input type="hidden" name="page" value="<? if(isset($_POST['page']) && !empty($_POST['page'])){ echo $_POST['page']; } ?>"/>
	<br/><span style="font-size:10px;" class="colorgray">Please upload transparent png file.</span>
</form>

<script type="text/javascript">
	$(function(){
		$('#fileToUpload').change(function(){
			$('#formUpload').submit();
		});	 
	});
</script>
