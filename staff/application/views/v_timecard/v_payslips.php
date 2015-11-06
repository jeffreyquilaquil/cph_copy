<br/>
<table class="tableInfo">
<?php
	$ifVisitID = '';
	if(isset($visitID)) $ifVisitID = $visitID.'/';
	foreach($dataPayslips AS $pay){
		echo '<tr>';
			echo '<td>'.date('F d', strtotime($pay->payPeriodStart)).' - '.date('F d, Y', strtotime($pay->payPeriodEnd)).'</td>';
			if($this->access->accessFullHRFinance==true)
				echo '<td width="15%"><a href="'.$this->config->base_url().'timecard/'.$ifVisitID.'payslipdetail/'.$pay->payslipID.'/"><img src="'.$this->config->base_url().'css/images/icon-options-edit.png" width="25px"/></a></td>';
			echo '<td width="15%"><a href="" target="_blank"><img src="'.$this->config->base_url().'css/images/pdf-icon.png" width="25px"/></a></td>';
		echo '</tr>';
	}
?>
</table>
