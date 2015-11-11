<a href="<?= $this->config->base_url() ?>timecard/managetimecard/managepayroll/"><button class="btnclass floatright"><< Back to Manage Payroll</button></a>
<h3>Payrolls for Period: <?= $info->payPeriodStart.' to '.$info->payPeriodEnd ?></h3>
<b>Pay Date: </b><?= date('F d, Y', strtotime($info->payDate)) ?>
<hr/>
<table class="tableInfo">
	<tr class="trlabel">
		<td>Name</td>
		<td>Gross Pay</td>
		<td>Deductions</td>
		<td>Net Pay</td>
		<td width="10%"><br/></td>
	</tr>
<?php
	foreach($dataPayroll AS $pay){
		echo '<tr>';
			echo '<td><a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslips/">'.$pay->name.'</a></td>';
			echo '<td>'.number_format($pay->earnings,2).'</td>';
			echo '<td>'.number_format($pay->deductions,2).'</td>';
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