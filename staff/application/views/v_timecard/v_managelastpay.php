<h2>Manage Last Pay</h2>
<hr/>
<table class="datatable display stripe hover">
<thead>
	<tr>
		<th>Employee ID</th>
		<th>Employee Name</th>
		<th>Start Date</th>
		<th>End Date</th>
		<th>Tax Refund</th>
		<th>Add Ons Total</th>
		<th>Deductions Total</th>
		<th>NET Last Pay</th>
		<th><br/></th>
	</tr>
</thead>
<?php
	foreach($dataQuery AS $data){
		echo '<tr>';
			echo '<td>'.$data->idNum.'</td>';
			echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$data->username.'/">'.$data->lname.', '.$data->fname.'</a></td>';
			echo '<td>'.date('d-M-Y', strtotime($data->startDate)).'</td>';
			echo '<td>'.date('d-M-Y', strtotime($data->endDate)).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->taxRefund).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->addTotal).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->deductTotal).'</td>';
			echo '<td><b>PHP '.$this->textM->convertNumFormat($data->netLastPay).'</b></td>';
			echo '<td align="right">
					<a class="iframe" href="'.$this->config->base_url().'timecard/computelastpay/?payID='.$data->lastpayID.'"><img width="20px" src="'.$this->config->base_url().'css/images/icon-options-edit.png"></a>
					&nbsp;&nbsp;&nbsp;
					<a target="_blank" href="'.$this->config->base_url().'timecard/computelastpay/?payID='.$data->lastpayID.'&show=pdf"><img width="20px" src="'.$this->config->base_url().'css/images/pdf-icon.png"></a>				
				</td>';
		echo '</tr>';
	}
?>
</table>