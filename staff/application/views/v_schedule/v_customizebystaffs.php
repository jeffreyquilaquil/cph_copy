<table style="width:100%;">
	<tr>
		<td width="50%" valign="top">
			<h3>Staff Schedules</h3>
			<hr/>
			<div id="schedDiv" width="100%">
				<div class="tacenter"><br/><br/><img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>"/><br/><br/></div>
			</div>
		</td>
		<td valign="top" width="50%">
	<?php
		if($pageType=='removeSched'){
	?>
		<div style="width:88%; margin-top:50px; padding:20px; border:1px solid #ccc;" class="tacenter">
			<b style="font-size:14px;" class="errortext tacenter">Are you sure you want to remove the staff schedules on the left side?</b><br/><br/>
			<form action="" method="POST" onSubmit="displaypleasewait();">				
			<?php
				echo $this->textM->formfield('textarea', 'reason', '', 'forminput', 'Type reason for removing schedule...', 'required');
				
				echo $this->textM->formfield('submit', '', 'Yes', 'btnclass btngreen').'&nbsp;&nbsp;';
				echo $this->textM->formfield('button', '', 'No', 'btnclass', '', 'onClick="parent.$.colorbox.close(); return false;"');
				
				echo $this->textM->formfield('hidden', 'submitType', 'removeSched');
				echo $this->textM->formfield('textarea', 'schedArr', '', 'hidden', '', 'id="tyrionID"');
			?>
			</form>
		</div>
	<?php
		}else{
	?>
		<div style="width:88%; padding-left:10px;">
			<h3>Set Schedule to:</h3>
				<hr/>
				<form action="" method="POST" onSubmit="displaypleasewait();">
				<?php
					$selval = '<option value=""></option>';
					foreach($timeArr AS $t){
						$selval .= '<optgroup label="'.$t['name'].'">';
						foreach($t AS $k=>$t2)
							if($k!='name'){
								$tt = explode('|', $t2);
								$selval .= '<option value="'.$t2.'">'.$tt[0].'</option>';
							}
								
						$selval .= '</optgroup><optgroup>';
					}					
					$selval .= '</optgroup>';
					
					echo $this->textM->formfield('select', 'timeV', $selval, 'forminput', '', 'required');
					echo $this->textM->formfield('textarea', 'schedArr', '', 'hidden', '', 'id="tyrionID"');
					echo $this->textM->formfield('hidden', 'submitType', 'setSched');
					echo '<br/><br/>';
					echo $this->textM->formfield('submit', 'setSched', 'Set Schedule', 'btnclass btngreen');
				?>
				</form>
		</div>
	<?php } ?>
		</td>
	</tr>
</table>

<script type="text/javascript">
$(function(){
	$('input[type="submit"]').attr('disabled', 'disabled');
	$('input[type="submit"]').removeClass('btngreen');
	
	$.post('<?= $this->config->item('career_uri') ?>', {submitType:'displaySched', arr:parent.$("#forcustomizedsched").val()},
	function(data){		
		dd = data.split('--^_^--');
		$('#schedDiv').html(dd[1]);
		$('#tyrionID').html(dd[0]);
		$('input[type="submit"]').removeAttr('disabled');
		$('input[type="submit"]').addClass('btngreen');
	});
});
</script>