<?php
	if($this->access->accessFullHRFinance==true){
?>
		<button id="btnupload" type="button" class="floatright btnclass btngreen">Upload Payslips</button>
		<form id="pform" action="" method="POST" enctype="multipart/form-data" onSubmit="displaypleasewait();">
			<input type="file" name="pfile[]" multiple="multiple" class="pfile hidden" onChange="submitUploadPay();"/>
			<input type="hidden" name="submitType" value="uploadPay"/>
		</form>
<?php
		
	}
?>


<h2><?= $staffInfo->fname.'\'s Previous Payslips' ?></h2>
<hr/>
<?php
	if(isset($notUploaded) && !empty($notUploaded)){
		echo '<b class="errortext">Not Uploaded Files</b><br/>'.$notUploaded.'<hr/>';
	}

	if(count($dataPayslips)==0){
		echo '<p>No previous payslips uploaded.</p>';
	}else{ ?>
		<table class="tableInfo">
			<tr class="trlabel">
				<td>Pay Date</td>
				<td>Details</td>
			</tr>
	<?php
		foreach($dataPayslips AS $data){
			echo '<tr>';
				echo '<td>'.$data->paydate.'</td>';
				echo '<td>
						<a href="'.$this->config->base_url().UPLOADS.'/prevPayslips/'.$data->filename.'" target="_blank">
							<img width="20px" src="'.$this->config->base_url().'css/images/pdf-icon.png">
						</a>
					</td>';
			echo '</tr>';
		}
	?>
		</table>
<?php		
	}
?>




<script type="text/javascript">
	$(function(){
		$('#btnupload').click(function(){
			$('.pfile').trigger('click');
		});
	});
	
	function submitUploadPay(){
		$('#pform').submit();
	}
</script>	
