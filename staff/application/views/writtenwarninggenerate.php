<h2>Generate Written Warning for <?= $row->name ?></h2>
<hr/>
<?php
	if(isset($submitted)){
?>
	<p>HR and <?= $row->name ?> has been informed that a written warning has been generated.</p>
	<p>Note that employee will be given an opportunity to request changes to the written warning that you have generated. You will be informed when changes are requested.</p>
	<p>You will be notified when the written warning is printed. You are responsible for routing the written warning to <?= $row->name ?> for signature.</p>

<?php
	}else{
?>

	<form action="" method="POST" onSubmit="displaypleasewait();">
	<table class="tableInfo">
		<tr>
			<td><b>When did the incident take place?</b></td>
			<td><?= $this->textM->formfield('text', 'dateIncident', '', 'datepick forminput', '', 'required'); ?></td>
		</tr>	
		<tr>
			<td width="40%" valign="top">
				<b>Details of the Incident</b><br/>
				What happened? What are you giving the written warning for? <font color="red">Important note:</font> Be very specific with details & avoid vague adverbs such as "too much", "so, ""very", "really".
			</td>
			<td><?= $this->textM->formfield('textarea', 'details', '', 'forminput', 'Type details here...', 'rows=5 required') ?></td>
		</tr>
		<tr>
			<td><b>Violation</b><br/>Which Code of Conduct violation is committed?</td>
			<td>
			<?php
				$vioArray[''] = '';
				foreach($violationList AS $list){
					$vioArray[$list->offenseID] = $list->offense;
				}
				echo $this->textM->formfield('selectoption', 'offenseID_fk', '', 'forminput', '', 'required', $vioArray);
			?>
			</td>
		</tr>
		<tr>
			<td><b>Offense Level</b></td>
			<td><img id="imgloading" src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25px" class="hidden"/>
				<?= $this->textM->formfield('number', 'offenseLevel', '', 'forminput') ?>
			</td>
		</tr>
		<tr>
			<td><br/></td>
			<td><?php
				echo $this->textM->formfield('submit', '', 'Submit', 'btnclass', '', 'disabled');	
				echo $this->textM->formfield('hidden', 'submitType', 'submitWarning');
			?></td>
		</tr>
	</table>
	</form>

	<script type="text/javascript">	
		$(function(){
			$('.datepick').datetimepicker({ 
				format:'F d, Y',
				maxDate:'<?= date('Y-m-d') ?>'
			});	
			
			$('select[name="offenseID_fk"]').change(function(){
				$('#imgloading').addClass('hidden');
				$('input[name="offenseLevel"]').addClass('hidden');
				$('input[type="submit"]').attr('disabled', 'disabled');
				$('input[type="submit"]').removeClass('btngreen');
				
				if($(this).val()!=''){
					$('#imgloading').removeClass('hidden');
					$.post('<?= $this->config->base_url().'generatewrittenwarning/'.$row->empID.'/' ?>', {submitType:'getOffdetails', offenseID:$(this).val()},
					function(d){
						if(d!=''){					
							$('input[name="offenseLevel"]').val($.trim(d));
							
							$('#imgloading').addClass('hidden');
							$('input[name="offenseLevel"]').removeClass('hidden');
							$('input[type="submit"]').removeAttr('disabled');
							$('input[type="submit"]').addClass('btngreen');
						}
					});
				}
			});
		});	
	</script>

<?php } ?>