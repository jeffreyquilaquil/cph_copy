<a href="<?= $this->config->base_url() ?>timecard/managetimecard/managepayroll/"><button class="btnclass floatright"><< Back to Manage Payroll</button></a>
<h3>Payrolls for Period: <?= date('F d, Y', strtotime($info->payPeriodStart)).' to '.date('F d, Y', strtotime($info->payPeriodEnd)) ?></h3>
<hr/>

<div style="float:right; width:250px;">
<?php
	echo '<b>Status:</b>';
	echo '<select class="forminput" style="background-color:green; color:#fff;" onChange="changeStatus(this)">';
		if($info->status==0)
			echo '<option value="0" '.(($info->status==0)?'selected="selected"':'').'>Generated</option>';
		if($info->status<=1)
			echo '<option value="1" '.(($info->status==1)?'selected="selected"':'').'>'.(($info->status==1)?'Published':'Publish').'</option>';
		echo '<option value="2" '.(($info->status==2)?'selected="selected"':'').'>'.(($info->status==2)?'Finalized':'Finalize').'</option>';
	echo '</select>';
	
	if($info->status<2){
		echo '<form action="" method="POST" onSubmit="return validateAction();">';
			echo '<select name="selectAction" class="padding5px">';
				echo '<option value="">Select action</option>';
				echo '<option value="regeneratepay">Regenerate Payslips</option>';
				echo '<option value="deletepay">Delete Payslips</option>';
			echo '</select>';
			echo $this->textM->formfield('submit', '', 'Go', 'btnclass btnred');
			
			echo $this->textM->formfield('textarea', 'empIDs', '', 'hidden');
			echo $this->textM->formfield('hidden', 'payrollsID', $info->payrollsID, 'hidden');			
			echo $this->textM->formfield('hidden', 'submitType', 'action'); 
		echo '</form>';
	}
?>
</div>
<div style="width:70%">
	<table class="tableInfo tacenter">
		<tr class="trhead">
			<td>Pay Date</td>
			<td>Pay Type</td>
			<td>Number Generated</td>
		</tr>
		<tr>
			<td><?= date('F d, Y', strtotime($info->payDate)) ?></td>
			<td><?= (($info->payType=='semi')?'Semi-Monthly':'Monthly') ?></td>
			<td><?= $info->numGenerated ?></td>
		</tr>
		<tr class="trhead">
			<td>Total Gross</td>
			<td>Total Deduction</td>
			<td>Total NET</td>
		</tr>
		<tr>
			<td>Php <?= number_format($totalGross,2) ?></td>
			<td>Php <?= number_format($totalDeduction,2) ?></td>
			<td style="border:2px solid red;"><b>Php <?= number_format($totalNet,2) ?></b></td>
		</tr>
	</table>
</div>
<hr/>

<table id="dtable" class="dTable display stripe hover">
	<thead>
	<tr style="text-align:left;">
		<th width="10px"><br/></th>
		<th>Name</th>
		<th>Gross Pay</th>
		<th>Deductions</th>
		<th>Net Pay</th>
		<th width="10%"><br/></th>
	</tr>
	</thead>
<?php
	foreach($dataPayroll AS $pay){
		echo '<tr>';
		echo '<td><input type="checkbox" class="classCheckMe" value="'.$pay->empID_fk.'"/></td>';
			echo '<td>';
				
				echo '<a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslips/">'.$pay->name.'</a>';
			echo '</td>';
			echo '<td>'.number_format($pay->earning,2).'</td>';
			echo '<td>'.number_format($pay->deduction,2).'</td>';
			echo '<td>'.number_format($pay->net,2).'</td>';
			echo '<td align="right">
					<a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslipdetail/'.$pay->payslipID.'/" target="_blank"><img src="'.$this->config->base_url().'css/images/icon-options-edit.png" width="20px"/></a>
					&nbsp;&nbsp;&nbsp;
					<a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslipdetail/'.$pay->payslipID.'/?show=pdf" target="_blank"><img src="'.$this->config->base_url().'css/images/pdf-icon.png" width="20px"/></a>				
				</td>';
		echo '</tr>';		
	}
?>	
</table>

<script type="text/javascript">
	$(function(){
		var oTable = $('.dTable').dataTable({
						"dom": '<"toolbar">l<"searchbox" f>tip'
					});
		$("div.toolbar").html('<div style="margin-top:8px; padding-right:5px;"><a class="cpointer" id="selectAll">Select All</a> | <a class="cpointer" id="deselectAll">Deselect All</a></div>');
		$("div.toolbar").css('float', 'left');
		
		$('.toolbar #selectAll').click(function(){
			$('.classCheckMe').prop('checked', true);
		});
		
		$('.toolbar #deselectAll').click(function(){
			$('.classCheckMe').prop('checked', false);
		});
	});
	
	function changeStatus(t){
		if(confirm('Are you sure you want to change the payroll status?')){
			displaypleasewait();
			$.post('<?= $this->config->item('career_uri') ?>', {submitType:'changestatus', status:$(t).val()}, 
			function(){
				location.reload();
			});
			
		}else $(t).val('<?= $info->status ?>');
	}
		
	function checkIfSelected(){
		var empIDs = $('textarea[name="empIDs"]').text();
		var cars = [];
		$('.classCheckMe').each(function(){
			if($(this).is(':checked')){
				cars.push($(this).val());
				empIDs = empIDs+$(this).val()+',';
			}
		});
		
		if(cars.length==0){
			return false;			
		}else{
			return empIDs;
		}
	}
	
	function validateAction(){
		darna = true;
		selected = $('select[name="selectAction"]').val();
		if(selected==''){
			alert('Please choose action.')
			darna = false;
		}else{
			isSelected = checkIfSelected();
			if(isSelected==false){
				alert('Please select employee.');
				darna = false;
			}else{
				if(selected=='deletepay'){
					if(!confirm('Are you sure you want to delete these payslips?')){
						darna = false;
						$('select[name="selectAction"]').val('');
					} 					
				}
			
				if(darna==true){
					$('textarea[name="empIDs"]').text(isSelected);
					displaypleasewait();
				}
			}
		}
		
		return darna;
	}
</script>