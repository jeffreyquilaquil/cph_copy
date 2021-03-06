<a href="<?= $this->config->base_url().'probaddrequirement/' ?>" style="float:right; margin-top:10px;">Add PER Requirement</a>
<h3><?= 'PER Status of '.$row->lname.', '.$row->fname.' ('.$row->perStatus.'%)' ?></h3>
<hr/>
<div id="mainPerDiv">
	<table class="tableInfo" border=1 bordercolor="#ccc">
		<thead>
		<tr class="trhead">
			<td width="50%">Requirement</td> 
			<td width="20%">Actions</td>
			<td width="30%">Remarks</td>
		</tr>
		</thead>
	<?php
		$hasPrev = FALSE;
		foreach($queryPerStatus AS $qp){
			echo '<tr>';
				echo '<td><b>'.$qp->perName.'</b><br/>'.$qp->perDesc.'</td>';
				echo '<td>';
					if(isset($arrHistory['action'][$qp->perID]['text'])){
						echo $arrHistory['action'][$qp->perID]['text'];
						//var_dump($arrHistory['action'][$qp->perID]['tcPrevious2316_ID']);
						if(isset($arrHistory['action'][$qp->perID]['naVal']) && $arrHistory['action'][$qp->perID]['naVal']==1)
							echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="'.$this->config->base_url().'css/images/check.png'.'" height="20px">N/A';
						if( $qp->perID == 6 && !$arrHistory['action'][$qp->perID]['tcPrevious2316_ID'] ){
							echo '<br/><a href="javascript:void(0)" onClick="showValidateDiv('.$qp->perID.')"> Previous BIR 2316</a>';
							$hasPrev = TRUE;
						}
					}else{
						echo '<a href="javascript:void(0)" onClick="showValidateDiv('.$qp->perID.')">Validate</a>';
						if($qp->enableNA==1){
							echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" onClick="showMyDiv('.$qp->perID.', this)"/> N/A';
						}
					}
				echo '</td>';
				echo '<td>';
					if(isset($arrHistory['remark'][$qp->perID])) echo implode('<br/><br/>', $arrHistory['remark'][$qp->perID]).'<br/><br/>';
					echo '<a id="ashow'.$qp->perID.'" href="javascript:void()" onClick="showAddRemarks('.$qp->perID.')">Add Remarks</a>
						<div id="divAddRemarks'.$qp->perID.'" class="hidden">
							<form class="formRemarks" action="" method="POST">
							'.$this->textM->formfield('textarea', 'remarks', '', 'forminput', '', 'required').'							
							'.$this->textM->formfield('hidden', 'perID', $qp->perID).'
							'.$this->textM->formfield('hidden', 'submitType', 'addRemarks').'
							'.$this->textM->formfield('submit', '', '+ Add Remarks', 'submitRemarkBtn').'
						</form>
						</div>
					</td>';
			echo '</tr>';
		}
	?>
	</table>
	<br/>
	
<?php if(count($queryHistory)>0){ ?>
		<h3>History</h3>
		<hr/>
		<table class="tableInfo" border=1 bordercolor="#ccc">
	<?php
		foreach($queryHistory AS $h){
			echo '<tr class="colorgray">';
				echo '<td width="20%">'.date('m/d/Y h:i a', strtotime($h->dateAdded)).'<br/>'.$h->adder.'</td>';
				echo '<td>';
					if($h->perType==0){
						echo '<b>Remarks Added</b><br/>'.$h->perValue;						
					}else{
						echo '<b>Validated</b><br/>';
						$lapis = explode('+++', $h->perValue);
						echo $lapis[0];
					}	
				echo '</td>';
			echo '</tr>';
		}
	?>
		</table>
		
<?php } ?>	
	
</div>

<?php 
	// echo "<pre>";
	// 	var_dump($queryPerStatus);
	foreach($queryPerStatus AS $q2){ ?>
	<div id="<?= 'div_'.$q2->perID ?>" class="hidDiv hidden" style="padding:10px;">
	<form action="" method="POST" enctype="multipart/form-data" onSubmit="return validateForm(<?= $q2->perID ?>, <?= $q2->enableNA ?>)">
	<?php

		echo 'To validate employee\'s '.strtolower($q2->perName).' '.$q2->perDesc;
		echo '<br/><br/>';
		if(!$hasPrev){
			echo $this->textM->formfield('file', 'fileupload', '', 'hidden', '', 'id="file'.$q2->perID.'" onChange="fileChange('.$q2->perID.')"');
			echo $this->textM->formfield('button', '', 'Upload '.$q2->perName, 'btnclass', '', 'id="btnupload'.$q2->perID.'" onClick="browseFile('.$q2->perID.')"');
			echo '<span id="filetext'.$q2->perID.'"></span>';
			if($q2->enableNA==1){
				echo '<br/><br/><input type="checkbox" onClick="showMyDiv('.$q2->perID.', this)"/> N/A';
			}
		}
		else{
			echo "<input type='hidden' name='hasPrev' value='1'/>";
		}
		
		if($q2->perID == 6){
			echo '<br/><br/><strong>21. Gross Compensation Income from Present Employer</strong><br/>';
			echo $this->textM->formfield('number', 'bir21', '', 'forminput', '00.00', 'id="bir21_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>30A. Amount of Taxes Withheld from Present Employer</strong><br/>';
			echo $this->textM->formfield('number', 'bir30b', '', 'forminput', '00.00', 'id="bir30b_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>31. Total Amount of Taxes Withheld As Adjusted</strong><br/>';
			echo $this->textM->formfield('number', 'bir31', '', 'forminput', '00.00', 'id="bir31_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>37. 13th Month Pay and Other Benefits</strong><br/>';
			echo $this->textM->formfield('number', 'bir37', '', 'forminput', '00.00', 'id="bir37_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>38. De Minimis Benefits</strong><br/>';
			echo $this->textM->formfield('number', 'bir38', '', 'forminput', '00.00', 'id="bir38_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>39. SSS, GSIS, PHIC & Pag-ibig Contributions, & Union Dues <small>(Employee share only)</small></strong><br/>';
			echo $this->textM->formfield('number', 'bir39', '', 'forminput', '00.00', 'id="bir39_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>40. Salaries & Other Forms of Compensation</small></strong><br/>';
			echo $this->textM->formfield('number', 'bir40', '', 'forminput', '00.00', 'id="bir40_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>41. Total Non-Taxable/Exempt Compensation Income</strong><br/>';
			echo $this->textM->formfield('number', 'bir41', '', 'forminput', '00.00', 'id="bir41_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>42. Basic Salary</strong><br/>';
			echo $this->textM->formfield('number', 'bir42', '', 'forminput', '00.00', 'id="bir42_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>47A.  Adjustment or Additional Premiums </strong><br/>';
			echo $this->textM->formfield('number', 'bir47a', '', 'forminput', '00.00', 'id="bir47a_'.$q2->perID.'" step="any"');

			echo '<br/><br/><strong>51. Taxable 13th Month Pay and Other Benefits </strong><br/>';
			echo $this->textM->formfield('number', 'bir51', '', 'forminput', '00.00', 'id="bir51_'.$q2->perID.'" step="any"');


			echo '<br/><br/><strong>55. Total Taxable Compensation Income</strong><br/>';
			echo $this->textM->formfield('number', 'bir55', '', 'forminput', '00.00', 'id="bir55_'.$q2->perID.'" step="any"');
		}

		echo '<br/><br/>Input link below if employee already submitted the file.<br/>'.$this->textM->formfield('text', 'filelink', '', 'forminput', 'http://', 'id="fileLink_'.$q2->perID.'"');
		
		
		echo '<br/><br/>';
		echo 'Add remarks below (optional)';
		echo '<br/>';
		echo $this->textM->formfield('textarea', 'remarks', '', 'forminput');
		echo '<br/><br/>';
		echo $this->textM->formfield('hidden', 'perID', $q2->perID);
		echo $this->textM->formfield('hidden', 'perName', $q2->perName);
		echo $this->textM->formfield('hidden', 'submitType', 'validate');
		echo $this->textM->formfield('submit', '', 'Submit File and Validate', 'btnclass btngreen');
		echo '&nbsp;&nbsp;'.$this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'onClick="hideDiv();"');
	?>
	</form>
	</div>
	<?php if($q2->enableNA==1){ ?>
		<div id="<?= 'div2_'.$q2->perID ?>" class="hidDiv hidden" style="padding:10px;">
			<form action="" method="POST">
			<?php
				echo 'Are you sure '.$q2->perName.' does not apply to '.$row->lname.', '.$row->fname.'?';
				echo '<br/><br/>';
				echo 'Add remarks below (optional)';
				echo '<br/>';
				echo $this->textM->formfield('textarea', 'remarks', '', 'forminput');
				echo '<br/><br/>';
				echo $this->textM->formfield('hidden', 'perID', $q2->perID);
				echo $this->textM->formfield('hidden', 'perName', $q2->perName);
				echo $this->textM->formfield('hidden', 'naVal', '1');
				echo $this->textM->formfield('hidden', 'submitType', 'validate');
				echo $this->textM->formfield('submit', '', 'Yes', 'btnclass btngreen');
				echo '&nbsp;&nbsp;'.$this->textM->formfield('button', '', 'No', 'btnclass', '', 'onClick="hideDiv();"');
			?>
			</form>
		</div>
<?php } ?>
<?php } ?>

<script type="text/javascript">
	$(function(){
		$('.formRemarks').submit(function(){
			$('.submitRemarkBtn').attr('disabled', 'disabled');
		});
	});
	
	function showValidateDiv(id){
		$('#mainPerDiv').addClass('hidden');
		$('#div_'+id).removeClass('hidden');
	}
	
	function hideDiv(){
		$('.hidDiv').addClass('hidden');
		$('#mainPerDiv').removeClass('hidden');
	}
	
	function browseFile(id){
		$('#file'+id).trigger('click');
	}
	
	function fileChange(id){
		$('#btnupload'+id).attr('disabled', 'disabled');
		$('#filetext'+id).text($('#file'+id).val());
	}
	
	function showAddRemarks(id){
		$('#divAddRemarks'+id).removeClass('hidden');
		$('#ashow'+id).addClass('hidden');
	}
	
	function showMyDiv(id, t){
		$('#mainPerDiv').addClass('hidden');
		$('#div_'+id).addClass('hidden');
		$('#div2_'+id).removeClass('hidden');
		$(t).prop('checked', false); 
	}
	
	//return false if enable NA is 0 and no file uploaded and checkbox submitted is unticked
	function validateForm(id, type){
		valid = true;
		
		if(type==0 && $('#fileLink_'+id).val()=='' && $('#file'+id).val()==''){
			alert('Please upload file or input file link.');
			valid = false;
		}else if($('#file'+id).val()=='' && $('#fileLink_'+id).val()==''){
			if(confirm('Are you sure you want to submit without uploading file?'))
				valid = true;
			else
				valid = false;
		}
		
		if(valid==true){
			displaypleasewait();
		}
		return valid;
	}
</script>
