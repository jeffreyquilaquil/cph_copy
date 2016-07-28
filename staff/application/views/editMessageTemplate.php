<?php foreach ($editMessageText as $k_editMessageText => $v_editMessageText) {
	echo '<h1>Edit Message Template (' .$v_editMessageText->messageType. ')</h1>';

	echo '<input type="hidden" id="messageID" value="' .$v_editMessageText->id. '">';
	echo '<textarea id= "edditedMessageText" class="hidden tiny" style="height:200px;">'.$v_editMessageText->messageText.'</textarea><br>';
	echo '<div style="text-align: right;">
		  <input type="submit" id="submit_confirm" class="btn btngreen" value="Confirm">
		  </div>
		  ';
} ?>

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	
	$(document).ready(function(){
		
		// display toolbar in textarea
		tinymce.init({
		selector: "textarea.tiny",	
		menubar : false,
		toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image "
		});	
		
	});

	$(function(){

		$('#submit_confirm').click(function(){
			
			var messageID = $('#messageID').val();
			var data_messageID = 'messageID=' + messageID;
			var edditedMessageText = tinyMCE.activeEditor.getContent();
			var data_edditedMessageText = 'edditedMessageText=' + edditedMessageText;

			if(edditedMessageText == ''){
				alert('Textbox is empty!');
			}
			else{

				$.ajax({
					url: '<?php echo $this->config->base_url(); ?>hr_cs/editMessageTemplate',
					type: 'POST',
					dataType: 'JSON',
					data: data_messageID + data_edditedMessageText,
					cache: false,
						success: function(result){
						alert("Editting of this template is done!");
						window.parent.location.href = "<?php echo $this->config->base_url(); ?>hr_cs/HrHelpDesk";
	                    close();
					}
				});

			}
			
		});
	});

</script>

