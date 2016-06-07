<?php 

if( isset($js) ){

	if( isset($upload_error) ){
		echo '<p class="errortext">'. $upload_error . '</p>';
	}

	echo '<script>parent.window.location.reload();</script>';
	exit();
}


echo '<h3>Upload Signed Last Pay Documentation</h3>';
echo '<hr/>';

if( isset($upload_error) ){
	echo '<p class="errortext">'. $upload_error . '</p>';
}
echo form_open_multipart( $this->config->base_url().'timecard/uploadlastpay');
echo form_hidden('lastPayID', $payInfo->lastpayID);

echo '<div class="uploadClass">';
echo form_upload(['name' => 'lastpay_doc[]', 'class' => 'doc']);
echo '</div>';

echo '<div>';
echo form_submit(['name' => 'submitType', 'value' => 'Upload', 'class' => 'btngreen']);
echo '</div>';


echo form_close();

?>
<script type="text/javascript">
	var sup_counter = 1;
	$(function(){
		
		$('input.doc').change(function(){
			if (sup_counter == 1) {
				$(this).after('<a href="#" class="insert">+ Add another document</a>');
			}
		});
		
	});
	$(document).on('click', '.insert', function(){
		console.log(sup_counter);
		sup_counter += 1;
		console.log(sup_counter);
		
			$('.uploadClass').append('<div><?php echo form_upload(['name' => 'lastpay_doc[]', 'class' => 'doc']); ?></div>');
			console.log('append');
		
	});

</script>