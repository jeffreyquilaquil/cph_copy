<h2>Supporting Documents</h2>
<hr/>
<?php
if(count($docs)==0){
	echo '<p>No files uploaded. Click <a href="'.$this->config->base_url().'sendEmail/'.$this->uri->segment(2).'/">here</a> to send email.</p>';
}else{ ?>
	<table class="tableInfo">
		<tr class="trhead">
			<td>Date Uploaded</td>
			<td>Uploaded By</td>
			<td>Document Name</td>
			<td width="130px">View/Download File</td>
		</tr>
	<?php
		foreach($docs AS $d){
			echo '<tr>';
			echo '<td>'.date('d M Y', strtotime($d->dateUploaded)).'</td>';
			echo '<td>'.$d->uploader.'</td>';
			echo '<td>'.$d->fileName.'</td>';
			
			if(strpos($d->fileName,'.jpg') !== false || strpos($d->fileName,'.gif') !== false || strpos($d->fileName,'.png') !== false || strpos($d->fileName,'.pdf') !== false)
				echo '<td align="center"><a href="'.$this->config->base_url().UPLOAD_DIR.$d->username.'/'.$d->fileName.'"><img src="'.$this->config->base_url().'css/images/view-icon2.png"/></a></td>';
			else
				echo '<td align="center"><a href="'.$this->config->base_url().UPLOAD_DIR.$d->username.'/'.$d->fileName.'"><img src="'.$this->config->base_url().'css/images/download-icon.gif"/></a></td>';
			
			
			echo '</tr>';
		}
	?>
	</table>
<?php
}
?>
