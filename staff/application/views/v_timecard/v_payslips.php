<br/>
<?php
if(count($dataPayslips)==0){
	echo '<p>No payslips generated yet.</p>';
}else{
	echo '<table class="tableInfo">';
		foreach($dataPayslips AS $pay){
			echo '<tr>';
				echo '<td>'.date('F d', strtotime($pay->payPeriodStart)).' - '.date('F d, Y', strtotime($pay->payPeriodEnd)).'</td>';
				echo '<td>'.(($pay->status==0)?'No yet published':'<br/>').'</td>';
				echo '<td width="50px"><a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslipdetail/'.$pay->payslipID.'/"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"/></a></td>';
				echo '<td width="50px"><a href="'.$this->config->base_url().'timecard/'.$pay->empID_fk.'/payslipdetail/'.$pay->payslipID.'/?show=pdf" target="_blank"><img src="'.$this->config->base_url().'css/images/pdf-icon.png" width="25px"/></a></td>';
			echo '</tr>';   
		}
	echo '</table>';
}

if( count($data_13thMonth) > 0 ){
	echo '<table class="tableInfo">';
		foreach( $data_13thMonth as $data ){
			echo '<tr>';
				echo '<td>13th Month '.date('F d', strtotime($data->periodFrom)).' - '.date('F d, Y', strtotime($data->periodTo)).'</td>';
				echo '<td></td>';
				echo '<td width="50px"><a href="'.$this->config->base_url().'timecard/detail13thmonth/'.$data->tcmonthID.'/"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"/></a></td>';
				echo '<td width="50px"><a href="'.$this->config->base_url().'timecard/detail13thmonth/'.$data->tcmonthID.'/?show=pdf" target="_blank"><img src="'.$this->config->base_url().'css/images/pdf-icon.png" width="25px"/></a></td>';
			echo '</tr>';
		}
	echo '</table>';
}
