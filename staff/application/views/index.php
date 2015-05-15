<? //middle-wrapper ?>
<div id="middle-wrapper">
	<div id="wrapper-index-mbody" style="overflow-y:auto; height:550px;">
	<?php if($this->access->accessFullHR==true){ ?>
		<div id="clbtn">
			<button id="createAnn">Create New</button>&nbsp;&nbsp;<button id="editAnn">Edit</button>
			<div><?= $announcement ?></div>
		</div>
		<div id="txtdiv" class="hidden">
			<textarea id="txtAnn" class="hidden" style="height:250px;"><?= $announcement ?></textarea>
			<p><i>For inserting images, click <a href="<?= $this->config->base_url() ?>uploadFiles/" class="iframe">here</a> to upload image, copy the link then click Insert/edit image button, paste the link of the image in "Source" input box. Dimensions should be less than 560px.</i></p>
			<button id="ccreate" class="hidden" onClick="insertUpdate('announcement');">Create</button><button id="cupdate" onClick="insertUpdate('updateAnn');" class="hidden">Update</button>&nbsp;&nbsp;<button id="ccancel">Cancel</button>
		</div>	
		
	<?php }else{
		echo $announcement;
	} ?>
	</div>
	<div style="text-align:right; padding:10px;">
		If you have any questions or concerns: <input type="button" style="background-color:#6b8e00;" onClick="window.location.href='mailto:hr.cebu@tatepublishing.net'" value="Click Here to email HR."/>
	</div>
	<!--<div class="wrapper-mid-box">				
		<h2 class="wheadtext">IMPORTANT ANNOUNCEMENTS</h2>
		<h3 class="wheadtext">TATE CHRISTMAS PARTY 2014</h3>
		December 6, 2014  6:00 PM<br/>
		Cityscape Hotel, Mandaue<br/>
		Dress Code: Red and White<br/>
		CLICK HERE TO RSVP
	</div>-->
</div>

<? //right-wrapper ?>
<div id="right-wrapper">
	<div class="wrapper-box">
		<input type="text" class="padding5px" id="searchindex"/><br/>
		<input type="button" value="Search" id="insearch"/>
	</div>
	<div class="wrapper-box">
		<h2 class="wheadtext">QUICK LINKS</h2>
		<a class="iframe tanone" href="<?= $this->config->base_url() ?>requestcoe/">Print Certificate of Employment</a><br/><br/>
		<a class="iframe tanone" href="<?= $this->config->base_url() ?>fileleave/">File for a Leave/Offset</a><br/><br/>
		<a href="mailto:accounting.cebu@tatepublishing.net" class="tanone">Payroll Inquiry</a><br/><br/>
		<a href="mailto:hr.cebu@tatepublishing.net" class="tanone">Report an Incident</a>		
	</div>
	<div class="wrapper-box">
		<h2 class="wheadtext">TIMEKEEPING AND PAYROLL</h2>
		Your schedule today is:<br/>
		<h2 class="wheadtext">7:00 AM - 4:00 PM</h2><br/>

		Your time in today is:<br/>
		<h2 class="wheadtext">6:58 AM</h2>
		<button>Take a Break</button><br/>
		<button style="padding:10px;">CLOCK OUT</button><br/>
		Click here to see your time records.
	</div>
</div>

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	$(function () { 
		tinymce.init({
			selector: "textarea",	
			menubar : false,
			plugins: [
				"link",
				"image",
				"code",
				"table"
			],
			toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code image"
		});	

		
		$('#createAnn').click(function(){
			$('#txtdiv').removeClass('hidden');
			$('#ccreate').removeClass('hidden');	
			$('#clbtn').addClass('hidden');				
			tinyMCE.activeEditor.setContent('');			
		});		
		$('#editAnn').click(function(){
			$('#txtdiv').removeClass('hidden');			
			$('#cupdate').removeClass('hidden');			
			$('#clbtn').addClass('hidden');
			tinyMCE.activeEditor.setContent($('#txtAnn').val());
		});
		
		$('#ccancel').click(function(){
			$('#txtdiv').addClass('hidden');
			$('#clbtn').removeClass('hidden');		
			$('#ccreate').addClass('hidden');	
			$('#cupdate').addClass('hidden');	
		});
				
	});
	
	function insertUpdate(d){
		if(tinyMCE.get('txtAnn').getContent()==''){
			alert('Announcement is empty.');
		}else{
			displaypleasewait();
			$.post('<?= $this->config->base_url() ?>',{
				submitType: d,
				aVal: tinyMCE.get('txtAnn').getContent()
			},function(){
				location.reload();
			});
		}
	}
</script>
