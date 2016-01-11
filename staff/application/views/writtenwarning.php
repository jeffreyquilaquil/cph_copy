<?php
	if($type=='requestchange'){
?>
		<form action="" method="POST" onSubmit="displaypleasewait();">
			<h3>You are here because you think some of the details in the written warning issued is incorrect. <b>Please re-write the details of the incident below and add the details you thought were missing or are different.</b></h3><hr/>
			<b>Details of the incident:</b><br/>
			<?= $this->textM->formfield('textarea', 'wrEdited', $info->wrDetails, 'forminput', '', 'rows=5') ?>
			<br/><br/>
			<?= $this->textM->formfield('submit', '', 'Submit new details of the incident', 'btnclass btngreen forminput') ?>
			<?= $this->textM->formfield('hidden', 'submitType', 'requestchange') ?>
		</form>
		
		<p class="tacenter">Please note that the changes you have entered shall be reviewed by <?= $info->supName ?>.</p>
		<a href="<?= $this->config->base_url().'writtenwarning/'.$this->uri->segment(2).'/notsign/' ?>"><button class="btnclass btngreen forminput">I am not signing this written warning at all.</button></a>
<?php
	}else if($type=='accept'){
?>
		<form action="" method="POST" onSubmit="displaypleasewait();">
			<h3>Please be informed that <?= $info->name ?> requested changes to the details of the incident.</h3><hr/>
			<p><b>Original Details of the Incident:</b><br/><?= nl2br($info->wrDetails) ?></p>
			<p><b>Employee's Edited Details of the Incident:</b><br/><textarea name="wrEdited" class="forminput" rows=8><?= stripslashes($info->wrEdited) ?></textarea></p>
			<p>If you agree to the employee's added items, please click the submit button below. If not, please edit the text first before clicking.</p>
		
<?php
			echo $this->textM->formfield('submit', '', 'Done. Accept details now.', 'btnclass btngreen forminput');
			echo $this->textM->formfield('hidden', 'submitType', 'accept');
		echo '</form>';
	}else if($type=='notsign'){
		echo '<form action="" method="POST" onSubmit="displaypleasewait();">';
?>
		<h3>Please tell us why you are refusing to sign the written warning or why you do not deserve to be given a written warning for the incident described:</h3><hr/>
		<p><b>Employee response:</b><br/><?= $this->textM->formfield('textarea', 'wrResponse', '', 'forminput', 'Type response here...', 'rows=8 required') ?></p>
		<p>Note that refusing to sign the written warning and submitting a response above shall not absolve you from any COC violation comitted. Once your response is submitted, the management shall evaluate the merit of the facts described in the written warning, as well as the merit of your response.</p>
<?php		
		echo $this->textM->formfield('submit', '', 'Submit response', 'btnclass btngreen forminput');
		echo $this->textM->formfield('hidden', 'submitType', 'respond');
		echo '</form>';
	}else if($type=='deliberate'){
		if(isset($demerit)){
			if($demerit==true){
				echo '<form action="" method="POST" onSubmit="displaypleasewait();">';
					echo '<p>You are about to <b>DEMERIT</b> employee\'s response. Employee will be notified. Discrediting employee\'s response means that there is sufficient documentation and facts to believe beyond reasonable doubt that employee has indeed committed a violation of the COC.</p>';
					echo '<p>The employee will be notified of your decision on the written warning:</p>';
					echo $this->textM->formfield('textarea', 'wrDeliberation', $info->wrDeliberation, 'forminput', '', 'required  rows=8');
					echo $this->textM->formfield('submit', '', 'Done. Complete Written Warning Sequence.', 'btnclass btngreen forminput');
					echo $this->textM->formfield('hidden', 'submitType', 'nomerit');
				echo '</form>';
			}else{
				echo '<form action="" method="POST" onSubmit="displaypleasewait();">';
					echo '<p>You are about to give <b>MERIT</b> to employee\'s response. Immediate supervisor ('.$info->supName.') will be notified of your deliberation notes. Giving merit to employee\'s response means that there is No sufficient documentation and facts to believe beyond reasonable doubt that employee has indeed committed a violation of the COC.</p>';
					echo '<p>The employee and immediate supervisor will be notified of your decision on the written warning:</p>';
					echo $this->textM->formfield('textarea', 'wrDeliberation', $info->wrDeliberation, 'forminput', '', 'required rows=8');
					echo $this->textM->formfield('submit', '', 'Done. Complete Written Warning Sequence.', 'btnclass btngreen forminput');
					echo $this->textM->formfield('hidden', 'submitType', 'merit');
				echo '</form>';
			}
		}else{
		
?>
			<form id="formDeliberate" action="" method="POST" onSubmit="displaypleasewait();">
				<p>HR please deliberate on the written warning. You are to decide whether employee has merit and indeed does not deserve to be given written warning or if the facts are well supported and that the employee deserves to be given written warning as per COC.</p>
				<p><b>Original Details of the Incident:</b><br/><?= nl2br($info->wrDetails) ?></p>
				<p><b>Employee's response</b> (why he/she is not signing the doc):<br/><?= ((empty($info->wrEdited))?'None':nl2br($info->wrEdited)) ?></p>
				<p><b>Management's Evaluation:</b><br/>Please put your deliberation notes below. Please write properly. <i>Both immediate supervisor and employee will be notified of your deliberation notes.</i></p>
				<?= $this->textM->formfield('textarea', 'wrDeliberation', '', 'forminput', 'Type deliberation note here...', 'required rows=8') ?>
				<br/>
<?php
				echo $this->textM->formfield('submit', '', 'Employee Response NO MERIT', 'btnclass btngreen forminput', '', 'onClick="typeValue(0)"').'<br/>';
				echo $this->textM->formfield('submit', '', 'Employee Response CONSIDERED. Written Warning VOID.', 'btnclass btngreen forminput', '', 'onClick="typeValue(1)"');
				echo $this->textM->formfield('hidden', 'submitType', 'deliberate');
				echo $this->textM->formfield('hidden', 'subType');
			echo '</form>';
?>
			<script type="text/javascript">
				function typeValue(type){
					$('input[name="subType"]').val(type);
				}
			</script>
<?php
		}
	}
?>
