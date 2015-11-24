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
	
	if($info->status<2)
		echo '<button onClick="regeneratePayroll('.$info->payrollsID.');">Regenerate Payroll</button>';
?>
</div>

<b>Pay Date: </b><?= date('F d, Y', strtotime($info->payDate)) ?><br/>
<b>Pay Type: </b><?= (($info->payType=='semi')?'Semi-Monthly':'Monthly') ?><br/>
<b>Number Generated: </b><?= $info->numGenerated ?><br/>
<b>Total Gross: </b>Php <?= number_format($totalGross,2) ?><br/>
<b>Total Deduction: </b>Php <?= number_format($totalDeduction,2) ?><br/>
<b>Total NET: </b>Php <?= number_format($totalNet,2) ?><br/>
<hr/>
<table class="datatable display stripe hover">
	<thead>
	<tr style="text-align:left;">
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
			echo '<td><a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslips/">'.$pay->name.'</a></td>';
			echo '<td>'.number_format($pay->earning,2).'</td>';
			echo '<td>'.number_format($pay->deduction,2).'</td>';
			echo '<td>'.number_format($pay->net,2).'</td>';
			echo '<td align="right">
					<a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslipdetail/'.$pay->payslipID.'/" target="_blank"><img src="'.$this->config->base_url().'css/images/icon-options-edit.png" width="20px"/></a>
					&nbsp;&nbsp;&nbsp;
					<a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslipdetail/'.$pay->payslipID.'/" target="_blank"><img src="'.$this->config->base_url().'css/images/pdf-icon.png" width="20px"/></a>				
				</td>';
		echo '</tr>';		
	}
?>	
</table>

<script type="text/javascript">
	function changeStatus(t){
		if(confirm('Are you sure you want to change the payroll status?')){
			displaypleasewait();
			$.post('<?= $this->config->item('career_uri') ?>', {submitType:'changestatus', status:$(t).val()}, 
			function(){
				location.reload();
			});
			
		}else $(t).val('<?= $info->status ?>');
	}
	
	function regeneratePayroll(id){
		displaypleasewait();
		$.post('<?= $this->config->base_url().'timecard/regeneratepayroll/' ?>'+id+'/',{},function(){
			location.reload();
		})
	}
</script>