<style>
	.toFromForm div{
		padding: 5px;
	}
	img{
		height: auto;
		width: 200px;
		padding: 10px;
	}
	.card-selected{
		border: solid #58E000 3px;
	}
</style>
<div class='toFromForm'>
	<div>To: <input type='text' class='padding5px messageTo' placeholder='Enter Username' size='50'/></div>
	<div>From: 
		<select class='messageFrom'>
			<option selected>A Fan of Yours</option>
			<?php
				foreach($PTDeptArr as $dept){
					echo "<option>Someone in the ". $dept. " Department</option>";
				}
			?>
			<option>Someone from Day Shift</option>
			<option>Someone from Night Shift</option>
			<option>Someone seated near you</option>
		</select>
	</div>
</div>

<textarea id="txtAnn" class="hidden tiny" style="height:350px;"></textarea>
<br>
Choose a card: <br/><br/>
<?php
	for($i = 1; $i < 10; $i++){
		echo "<img data-img='".$i."' src='".$this->config->base_url()."includes/images/".$i.".jpg' />";
	}
?>
<br/>
<button onClick="insertUpdate('sendvalentines');" style="padding:3px;">Send</button>

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	$(function () { 
		tinymce.init({
			selector: "textarea.tiny",	
			menubar : false,
			toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image"
		});	
	});
	
	$('img').click(function(){
		$('img').removeClass('card-selected');
		$(this).addClass('card-selected');
	});

	function insertUpdate(d){
		var data = tinyMCE.get('txtAnn').getContent();
		var submitType = d;
		var from = $('.messageFrom option:selected').val();
		var to = $('.messageTo').val();
		var card = $(".card-selected").data('img');
		var datastrip = new FormData();//'submitType='+submitType+'&from='+from+'&to='+to+'message='

		datastrip.append('submitType', d);
		datastrip.append('from', from);
		datastrip.append('to', to);
		datastrip.append('message', data);
		datastrip.append('card', card);

		if(data == ''){
			alert('Message is empty');
		}else{
			$.ajax({
				type: 'POST',
				url: "<?= $this->config->base_url() ?>",
				data: datastrip,
				async: false,
				success: function(e){
					alert(e);
				},
				cache: false,
		        contentType: false,
		        processData: false
			});
		}
	}
</script>