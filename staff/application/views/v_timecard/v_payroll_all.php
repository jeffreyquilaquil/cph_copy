<h3><?= 'Payrolls for period: '.date('F d, Y', strtotime($dataInfo->payPeriodStart)).' to '.date('F d, Y', strtotime($dataInfo->payPeriodEnd)) ?></h3>
<hr/>
<?php
	if(count($dataPayslips)==0){
		echo '<p>No payslip generated yet.</p>';
	}else{
		echo '<table class="tableInfo">';
			echo '<tr class="trlabel">';
				echo '<td>Employee Name</td>';
				echo '<td>Gross Pay</td>';
				echo '<td>Net Pay</td>';
				echo '<td><br/></td>';
			echo '</tr>';
			foreach($dataPayslips AS $pay){
				echo '<tr>';
					echo '<td>'.$pay->name.'</td>';
					echo '<td>'.$this->textM->convertNumFormat($pay->earnings).'</td>';
					echo '<td>'.$this->textM->convertNumFormat($pay->net).'</td>';
					echo '<td></td>';
				echo '</tr>';
			}
		echo '</table>';
	}
?>