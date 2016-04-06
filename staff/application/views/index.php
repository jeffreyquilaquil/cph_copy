<? //middle-wrapper ?>
<div id="middle-wrapper">	
	<div id="wrapper-index-mbody" style="height:590px;">
	<div style="float:right;">
		<input type="text" class="padding5px" id="searchindex" placeholder="What do you need to know?" size=50/> <input type="button" value="Search" id="insearch" style="padding:3px;"/>
	</div>
	
	<?php if($this->access->accessFullHR==true){ ?>
		<div id="clbtn" style="float:left;">
			<button id="createAnn" style="padding:3px;">Create New</button>&nbsp;&nbsp;<button id="editAnn" style="padding:3px;">Edit</button>
		</div>
		<div id="txtdiv" class="hidden">
			<textarea id="txtAnn" class="hidden tiny" style="height:350px;"><?= $announcement ?></textarea>
			<p><i>For inserting images, click <a href="<?= $this->config->base_url() ?>uploadFiles/" class="iframe">here</a> to upload image, copy the link then click Insert/edit image button, paste the link of the image in "Source" input box. Dimensions should be less than 560px.</i></p>
			<button id="ccreate" class="hidden" onClick="insertUpdate('announcement');" style="padding:3px;">Create</button><button id="cupdate" onClick="insertUpdate('updateAnn');" class="hidden" style="padding:3px;">Update</button>&nbsp;&nbsp;<button id="ccancel" style="padding:3px;">Cancel</button>
		</div>	
		
	<?php }
		echo '<div id="announcementDiv" style="overflow-y:auto; height:550px; float:left; margin-top:5px;">'.$announcement.'</div>';
	?>
	
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
<?php  if( isset($view_feedback) AND !empty($view_feedback) ){ //upward feedback 
?>
<div class="upward_feedback" style="text-align: center; padding: 8px">
<h1 style="color: #fff; font-weight: bold;">Upward Feedback</h1>
<?php if( isset($confirm) AND !empty($confirm) ){
	echo '<p style="color: #f00; font-weight: 400;" class="notice">'. $confirm .'</p>';
} ?>
	
	<p>Our leaders are our Company's key drivers of success. Let us know your feedback about our leaders&mdash;your leader. Your response are confidential and shall be used in general to develop targetted training programs for our leaders.</p>
	<form name="frm_leaderFeedback" action="" method="post">
		<input type="hidden" name="submitType" value="leaderFeedback" />
		<textarea name="txt_leaderFeedback" value="" style="width: 100%; height: 250px;"></textarea>
		<p>This is a feedback for<br/>(pick the name of the leader)</p>
		<select name="sel_leader">
			<option value="0" selected>Choose...</option>
			<?php if( isset($leaders) AND !empty($leaders) ){
				foreach( $leaders as $row ){
					echo '<option value="'. $row->empID .'">'. $row->name .'</option>';
				}
			} ?>
		</select><br/><br/>
		<input type="submit" value="Submit" name="btm_submit" class="btnclass" />
	</form>
</div>
<script>
	$(function(){
		$('form[name="frm_leaderFeedback"]').submit(function(e){
			if( $('select[name="sel_leader"]').val() == 0 ){
				alert('Please select the name of the leader.');
				e.preventDefault();
			} else if( $('textarea[name="txt_leaderFeedback"]').val() == '' ){
				alert('Please write your feedback.');
				e.preventDefault();
			}
			
		});
		
		setInterval( function(){
			$('p.notice').fadeOut(3000);
		}, 2000 );
		
	});
</script>

<?php } //end upward feedback ?>

<?php if($this->user->perStatus<100){ ?>
	<div style="padding:10px;" class="tacenter">
		Your PER is<br/>
		<b style="font-size:24px;"><?= $this->user->perStatus ?>%</b><br/>
		complete
		<hr/>
		Submit the following documents asap (<i>click to upload</i>).
		<hr/>
		<ol class="perOL">
		<?php
			$uploaded = array();
			foreach($perUploaded AS $p) array_push($uploaded, $p->perID_fk);
			
			$forVerify = array();
			foreach($perForVerify AS $v) array_push($forVerify, $v->perID_fk);
			
			foreach($requirements AS $req){
				if(!in_array($req->perID, $uploaded)){
					echo '<li>';
						echo '<a href="javascript:void(0);" onClick="uploadPER('.$req->perID.', \''.$req->perName.'\')">'.$req->perName.' '.((!empty($req->perDescShort))?'<span class="weightnormal">('.$req->perDescShort.')</span>':'').'</a>';
						if(in_array($req->perID, $forVerify)) echo '<i class="colorgray"> File submitted. Pending for verification.</i>';
					echo '</li>';
				}
			}
		?>
		</ol>
		<hr/>
		<form id="formPER" class="hidden" action="" method="POST" enctype="multipart/form-data">
		<?php
			echo $this->textM->formfield('file', 'pfile', '', '', '', 'id="fuploadPER"');
			echo $this->textM->formfield('hidden', 'perTypeID');
			echo $this->textM->formfield('hidden', 'pTypeName');
			echo $this->textM->formfield('hidden', 'submitType', 'uploadPER');
		?>
		</form>
	</div>
	<script type="text/javascript">
		$(function(){
			$('#fuploadPER').change(function(){
				$('#formPER').submit();
				displaypleasewait();
			});
		});
		
		function uploadPER(id, name){
			$('input[name="perTypeID"]').val(id);
			$('input[name="pTypeName"]').val(name);
			$('#fuploadPER').trigger('click');
		}
	</script>
<?php } ?>
	<!--
	<div class="wrapper-box">
		<input type="text" class="padding5px" id="searchindex"/><br/>
		<input type="button" value="Search" id="insearch"/>
	</div>	
	<div class="wrapper-box">
		<h2 class="wheadtext">QUICK LINKS</h2>
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
		<button class="btnclass">Take a Break</button><br/>
		Click here to see your time records.
	</div>
	-->
</div>

<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	$(function () { 
		tinymce.init({
			selector: "textarea.tiny",	
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
			$('#announcementDiv').addClass('hidden');
			tinyMCE.activeEditor.setContent('');			
		});		
		$('#editAnn').click(function(){
			$('#txtdiv').removeClass('hidden');			
			$('#cupdate').removeClass('hidden');			
			$('#clbtn').addClass('hidden');
			$('#announcementDiv').addClass('hidden');
			tinyMCE.activeEditor.setContent($('#txtAnn').val());
		});
		
		$('#ccancel').click(function(){
			$('#txtdiv').addClass('hidden');
			$('#clbtn').removeClass('hidden');		
			$('#ccreate').addClass('hidden');	
			$('#cupdate').addClass('hidden');	
			$('#announcementDiv').removeClass('hidden');
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
