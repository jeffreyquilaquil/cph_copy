<h3><?= 'Payrolls for period: '.date('F d, Y', strtotime($dataInfo->payPeriodStart)).' to '.date('F d, Y', strtotime($dataInfo->payPeriodEnd)) ?></h3>
<hr/>
<?php
	if(count($dataPayslips)==0){
		echo '<p>No payslip generated yet.</p>';
	}else{
		foreach($dataPayslips AS $pay){
			
		}
	}
?>