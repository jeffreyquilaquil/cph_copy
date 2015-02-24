<h2>Uploaded files</h2>
<hr/>
<table class="tableInfo">
<tr>
	<td><b>Add File</b></td>
	<td><form id="formUpload" action="" method="POST" enctype="multipart/form-data">
		<input type="file" name="fileToUpload" id="fileToUpload">   	
	</form></td>
</tr>
<?php
$dir  = 'uploads/others/';
if (is_dir($dir)) {
	$objects = scandir($dir); 
	$cc = 0;
	foreach ($objects as $object) { 
		if ($object != "." && $object != "..") { 
			$xx = $this->config->base_url().$dir.$object;
			echo '<tr id="tr_'.$cc.'">
				<td><img src="'.$xx.'" width="100px"/></td>
				<td>'.$xx.'</td>
				<td><img src="'.$this->config->base_url().'css/images/delete-icon.png" style="cursor:pointer;" onClick="del(\''.$dir.$object.'\', '.$cc.')"></td>
			</tr>';
			$cc++;
		} 
	} 
}
?>
</table>

<script type="text/javascript">
	$(function(){
		$('#fileToUpload').change(function(){
			$('#formUpload').submit();
		});	 
	});
	
	function del(s, x){
		if(confirm('Are you sure you want to delete this file?')){
			$('#tr_'+x).addClass('hidden');
			$.post('<?= $this->config->base_url() ?>uploadFiles/',{ submitType:'delete', fname:s});
		}
	}
</script>
