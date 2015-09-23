<?php
	if(isset($submitted)){
?>
<div class="padding5px">
	<b>You have successfully reported a violation of the policies of the Code of Conduct to HR.<br/>
	<span class="errortext">Your case number is COC<?= sprintf('%04d', $reportID) ?></span></b>
	
	<br/><br/>
	<b>What will happen next:</b><br/>
	Your report is now sent to HR for review and initial evaluation. Either of the following things can happen next:<br/>
	<ul>
		<li>You will receive a <b>request for more information</b> should it be determined that the information provided is not enough to prove a violation of the Code of Conduct.</li>
		<li>A dismissal of the case, should it be determined that the information provided are not sufficient to prove a violation of the Code of Conduct.</li>
		<li>A resolution, if the misconduct is proved and discipline is served.</li>
	</ul>
	<br/>
	<b>Immediate Supervisor’s Accountability</b><br/>
	Remember that, unless there is a valid reason why the immediate supervisors involved (yours and that of the employee being reported, whichever applies) would not be capable to address and resolve the misconduct, they shall also be disciplined for failing to do their basic duties, and such will be documented in their personnel files.
	<br/><br/>
	<?php if(isset($_POST['anonymous'])){ ?>
		<i>Confidential: For confidentiality purposes, you shall be assigned a unique alias with which HR shall use when speaking and discussing the case you have submitted. Your assigned alias is <?= $_POST['alias'] ?>.</i><br/>
	<?php } ?>
	<b class="errortext">Please keep this information to yourself only.</b>
</div>

<?php }else{ ?>
	<div class="padding5px">
		<h3>You are about to report a violation of the policies of the Code of Conduct.</h3>
		<hr/>	
		<form id="formreport" action="" method="POST" enctype="multipart/form-data" onSubmit="return checkSubmit();">
			<?= $this->textM->formfield('hidden', '', '1', '', '', 'id="step"'); ?>
			<div id="div1">	
				<p>Please provide the details of the incident in the below box. Be as detailed as possible. Please back up your opinions with facts and if possible, undeniable proof.</p>
				<div class="paddingtop10px">
					<b>When did the incident take place?</b>
					<?= $this->textM->formfield('text', 'when', '', 'forminput datepick', '', 'required'); ?>
				</div>
				<div class="paddingtop10px">
					<b>Where did the incident take place?</b>
					<?= $this->textM->formfield('text', 'where', '', 'forminput', '', 'required'); ?>
				</div>
				<div class="paddingtop10px">
					<b>What happened?</b> <span class="colorgray">(Maximum of 1000 chars)</span>
					<?= $this->textM->formfield('textarea', 'what', '', 'forminput', '', 'required rows=6 maxlength="1000"'); ?>
				</div>
				<div class="paddingtop10px">
					<b>Have you reported this to your immediate supervisor yet?</b><br/>
					<?php
						echo $this->textM->formfield('button', '', 'Yes', 'btnclass btngreen', '', 'id="btnreportedyes" onClick="btnreported(1)"');
						echo '&nbsp;&nbsp;';
						echo $this->textM->formfield('button', '', 'No', 'btnclass btnred', '', 'id="btnreportedno"  onClick="btnreported(0)"');
					?>
				</div>
				<div id="divreported" class="paddingtop10px hidden">
					<div id="textreportedyes">
						<b>What action did your immediate supervisor do about it?</b><br/>
						<i class="fs11px colorgray">Immediate supervisors are the first line of defense against misconduct. As such, unless the complaint is against your immediate supervisor, or if your immediate supervisor failed to take action on the misconduct, no complaint must be escalated to HR. Note that immediate supervisors who fail to report misconduct or fail to properly and effectively address misconduct shall be subject to coaching or discipline for negligence of duty.</i>
					</div>
					
					<div id="textreportedno" class="hidden"><b>Why did you not report to your immediate supervisor?</b></div>
					
					<?= $this->textM->formfield('textarea', 'whatISaction', '', 'forminput', '', 'required'); ?>
					<?= $this->textM->formfield('hidden', 'reported'); ?>
					<?= $this->textM->formfield('submit', '', 'Next', 'btnclass btngreen btnnext') ?>
				</div>
			</div>
			
			<div id="div2" class="hidden">		
				<div class="paddingtop10px">
					<b>What code of conduct policy is violated?</b> <i class="fs11px colorgray">(You can select more than 1)</i>
					<table class="tableInfo">
				<?php
					foreach($offensesData AS $o){
						echo '<tr>';
							echo '<td>'.$this->textM->formfield('checkbox', 'whatViolation[]', $o->offenseID).'</td>';
							echo '<td>'.$o->offense.'</td>';
						echo '</tr>';
					}
				?>	
					</table>
					<br/><br/>
					<?= $this->textM->formfield('submit', '', 'Next', 'btnclass btngreen btnnext') ?>
					<?= $this->textM->formfield('submit', '', 'Back', 'btnclass btnback') ?>
				</div>
			</div>		
			<div id="div3" class="hidden">		
				<div class="paddingtop10px">
					<b>Do you have proof / evidence to support the report?</b> <span class="colorgray">(Maximum of 1000 chars)</span><br/>
					<i class="fs11px colorgray">To properly address misconduct, HR requires thorough documentation and evidence to support an allegation. Just like any judiciary system, any employee reported shall be considered innocent until proven guilty and burden of proof, shall be upon you as the complainant. Without sufficient proof and evidence, a report of misconduct may be dismissed for lack of merit.</i><br/>
					<?= $this->textM->formfield('textarea', 'proof', '', 'forminput', '', 'rows=8 maxlength="1000"'); ?>
				</div>
				<div class="paddingtop10px divLast">
					<b>Documents / Files / Photos</b><br/>
					<i class="fs11px colorgray">Please upload screenshots/files/photos that you think will be helpful as proof and will be useful in the investigation of the misconduct.</i><br/>
					<?= $this->textM->formfield('file', 'docs[]') ?><br/>
					<?= $this->textM->formfield('file', 'docs[]') ?><br/>
					<?= $this->textM->formfield('file', 'docs[]') ?><br/>
					<?= $this->textM->formfield('file', 'docs[]') ?><br/>
					<?= $this->textM->formfield('file', 'docs[]') ?>
				</div>
				<div class="paddingtop10px">
					<?= $this->textM->formfield('submit', '', 'Next', 'btnclass btngreen btnnext') ?>
					<?= $this->textM->formfield('submit', '', 'Back', 'btnclass btnback') ?>
				</div>
			</div>
			
			<div id="div4" class="hidden">
				<div class="paddingtop10px">
					<b>Are there other people who can attest as witnesses to the incident?</b><br/>
					<?php
						echo $this->textM->formfield('button', '', 'Yes', 'btnclass btngreen', '', 'id="btnwitnessesyes" onClick="btnWitnesses(1)"');
						echo '&nbsp;&nbsp;';
						echo $this->textM->formfield('button', '', 'No', 'btnclass btnred', '', 'id="btnwitnessesno" onClick="btnWitnesses(0)"');
					?>
				</div>
				<div id="divwitnesses2" class="paddingtop10px hidden">
					<b>Witnesses</b><br/>
					<i class="fs11px colorgray">List down the names of all the witnesses in the box below.</i><br/>
					<?= $this->textM->formfield('textarea', 'witnesses', '', 'forminput'); ?>
				</div>
				<div class="paddingtop10px divLast">
					<b>Other details</b> <span class="colorgray">(Maximum of 800 chars)</span><br/>
					<i class="fs11px colorgray">Please write in the box below other details that are not covered in the previous questions in the box below.</i><br/>
					<?= $this->textM->formfield('textarea', 'otherdetails', '', 'forminput', '', 'rows=6 maxlength="800"'); ?>
				</div>
				<div class="paddingtop10px">
					<?= $this->textM->formfield('submit', '', 'Next', 'btnclass btngreen btnnext') ?>
					<?= $this->textM->formfield('submit', '', 'Back', 'btnclass btnback') ?>
				</div>
			</div>
			
			<div id="div5" class="hidden">
				<div class="paddingtop10px">
				<?php
					$alias = $this->textM->getAlias();
					echo $this->textM->formfield('hidden', 'alias', $alias);
				?>
					<b id="balias" class="floatright errortext hidden">Assigned Alias: <u><?= $alias ?></u></b>
					<?= $this->textM->formfield('checkbox', 'submitanonymous', '', '', '', 'id="submitanonymous"') ?> <b class="errortext">Submit Anonymously</b><br/>
					<i class="fs11px colorgray">Click on the button below if you want to submit anonymously. When you submit anonymously, you will be assigned an alias that will appear in all reports and printouts that will need to be seen by the employees that you are reporting against, or employees that will be determined liable, in the process of the investigation. However, for data integrity purposes, your identity shall be recorded in the system (careerph login) and after receiving this report you will be asked to come to HR to officially sign the incident report.
					<br/><br/>Only click on the button below if you wish to submit anonymously. If you are comfortable to disclose your identity (which is ideal, and also recommended if there is undeniable proof), then please do not submit anonymously.</i>
				</div>
				<div class="paddingtop10px">
					<?= $this->textM->formfield('checkbox', 'donotccIS', '', '', '', 'id="donotccIS"') ?> <b class="errortext">Do not CC my immediate supervisor</b><br/>
					<i class="fs11px colorgray">To serve as control and to make sure that all details submitted are correct, and to also make sure all our leaders are involved in all transactions that involve their teams, BY DEFAULT, immediate supervisors of employees submitting this report shall be copied and informed of the submission of this report and they shall have full visibility on all the contents of the report.
					<br/><br/>If you click the box above, a valid reason must be provided by you as to why your immediate supervisor is being excluded from this report. If the reason is found to be not valid, you will be informed and more information may be required. If the reason is found to be valid, then HR shall approve exclusion of your immediate supervisor from the report and the case shall proceed as normal.
					<br/><br/>If the box above is checked, the report shall be signed by immediate supervisor’s supervisor, unless the circumstances necessitate otherwise.</i>
				</div>
				<div id="divwhyExclude" class="paddingtop10px hidden">
					<b>Why are you excluding your immediate supervisor from knowing about this case?</b><br/>
					<i class="fs11px colorgray">Please write in the box below other details that are not covered in the previous questions in the box below.</i><br/>
					<?= $this->textM->formfield('textarea', 'whyExcludeIS', '', 'forminput'); ?>
				</div>
				<div class="paddingtop10px">
					<?= $this->textM->formfield('submit', '', 'SUBMIT INCIDENT REPORT TO HR', 'btnclass btngreen btnnext', '', 'id="btnSubmitForm"'); ?>
					<?= $this->textM->formfield('submit', '', 'Back', 'btnclass btnback') ?>
					
					
					<?= $this->textM->formfield('hidden', '', '', 'submitType') ?>
				</div>
			</div>
		</form>
	</div>
<?php } ?>
<script type="text/javascript">	
	$(function(){
		$('.datepick').datetimepicker({ 
			format:'F d, Y',
			maxDate:'<?= date('Y-m-d') ?>'
		});	
		
		$('#donotccIS').click(function(){
			$('textarea[name="whyExcludeIS"]').removeAttr('required');
			$('#divwhyExclude').addClass('hidden');
			
			if($(this).is(':checked')){
				$('textarea[name="whyExcludeIS"]').attr('required','required');
				$('#divwhyExclude').removeClass('hidden');
			}
		});
		
		$('#submitanonymous').click(function(){
			$('#balias').addClass('hidden');
			if($(this).is(':checked')){
				$('#balias').removeClass('hidden');
			}
		});
		
		$('.btnnext').click(function(){
			$('.submitType').val('Next');
		});
		
		$('.btnback').click(function(){
			$('.submitType').val('Back');
		});
	});
	
	function btnreported(v){
		$('#divreported').removeClass('hidden');
		$('#divreported div').addClass('hidden');
		$('#btnreportedyes').addClass('btngreen');
		$('#btnreportedno').addClass('btnred');
		$('input[name="reported"]').val(v);		
		
		if(v==1){
			$('#textreportedyes').removeClass('hidden');
			$('#btnreportedno').removeClass('btnred');
		}else{
			$('#textreportedno').removeClass('hidden');
			$('#btnreportedyes').removeClass('btngreen');
		}
	}
	
	function btnWitnesses(v){
		$('#btnwitnessesyes').addClass('btngreen');
		$('#btnwitnessesno').addClass('btnred');
		$('#divwitnesses2').addClass('hidden');
		$('textarea[name="witnesses"]').removeAttr('required');
				
		if(v==1){
			$('#divwitnesses2').removeClass('hidden');
			$('textarea[name="witnesses"]').attr('required', 'required');
			$('#btnwitnessesno').removeClass('btnred');
		}else if(v==0){			
			$('#btnwitnessesyes').removeClass('btngreen');
		}
	}
		
	function checkSubmit(){
		step = $('#step').val();
		type = $('.submitType').val();
		
		if(step==2){
			v = '';
			$('input[name="whatViolation\[\]"]').each(function(){
				if($(this).is(':checked'))
					v += $(this).val();
			});
			if(v==''){
				alert('Please select violation.');
				return false;
			}
		}
		
		if(type=='Back'){
			$('#div'+step).addClass('hidden');		
			stepMinus = parseInt(step)-1;
			
			$('#step').val(stepMinus);
			$('#div'+stepMinus).removeClass('hidden');
						
			return false;
		}else{
			if(step==5){
				$('#btnSubmitForm').attr('disabled', 'disabled');
				$('<img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25px"/>').insertAfter('#btnSubmitForm');
				return true;
			}else{			
				$('#div'+step).addClass('hidden');		
				stepPlus = parseInt(step)+1;
				
				$('#step').val(stepPlus);
				$('#div'+stepPlus).removeClass('hidden');
							
				return false;
			}
		}
		
				
	}
</script>
