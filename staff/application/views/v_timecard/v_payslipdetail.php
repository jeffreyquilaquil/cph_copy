<?php
	$hourlyRate = $this->payrollM->getDailyHourlyRate($payInfo->monthlyRate, 'hourly');
	
	$detailArr = array();
	foreach($dataPay AS $d){
		$detailArr[$d->itemType][] = $d;
	}
?>
<a href="<?= $this->config->base_url().'timecard/'.$visitID.'/payslips/' ?>"><button class="floatright btnclass"><< Back to All Payslips</button></a>
<h3>Payroll Info</h3>
<hr/>

<table class="tableInfo">
	<tr class="trlabel"><td colspan=2>Payroll</td></tr>
	<tr>
		<td width="20%">Period</td>
		<td><?= date('F d, Y', strtotime($payInfo->payPeriodStart)).' - '.date('F d, Y', strtotime($payInfo->payPeriodEnd)) ?></td>
	</tr>
	<tr>
		<td>Hourly Rate</td>
		<td><?= $hourlyRate ?></td>
	</tr>
	<tr>
		<td>Gross Pay</td>
		<td><?= $this->textM->convertNumFormat($payInfo->basePay) ?></td>
	</tr>
	<tr>
		<td>Total Taxable</td>
		<td><?= $this->textM->convertNumFormat($payInfo->totalTaxable) ?></td>
	</tr>
	<tr>
		<td>Deductions</td>
		<td><?= $this->textM->convertNumFormat($payInfo->deductions) ?></td>
	</tr>
	<tr>
		<td>Net Pay</td>
		<td><b><?= $this->textM->convertNumFormat($payInfo->net) ?></b></td>
	</tr>
</table>

<br/>
<table class="tableInfo">
	<tr class="trlabel"><td colspan=4>Payroll Items</td></tr>
	<tr class="trhead">
		<td>Type</td>
		<td>Name</td>
		<td>Hours</td>
		<td>Amount</td>
	</tr>
	<?php
		foreach($dataPay AS $d){
			echo '<tr>';
				echo '<td>'.$payItemArr[$d->itemType].'</td>';
				echo '<td>'.$d->itemName.'</td>';
				echo '<td>'.(($d->itemHR>0)?number_format($d->itemHR,1):'-').'</td>';
				echo '<td>';
					if($d->itemType==3 || $d->itemType==6  || $d->itemType==7 ) echo '-';
					echo $this->textM->convertNumFormat($d->payValue);
				echo '</td>';
			echo '<tr>';
		}
	?>
</table>

<br/>
<table class="tableInfo tacenter">
	<tr class="trlabel taleft"><td colspan=6>Hours Worked</td></tr>
	<tr class="trhead">
		<td align="left" width="15%">Date</td>
		<td>Worked Hours</td>
		<td>Taken Hours</td>
		<td>Type</td>
		<td>ND</td>
		<td><br/></td>
	</tr>
<?php
	$workHR = 0;
	$takenHR = 0;
	$ndHR = 0;	
	$unpublished = false;
	$workArr = array();
	foreach($dataWorked AS $w){
		$workArr[$w->slogDate] = $w;
	}
	
	foreach($dataDates AS $d){
		$w = '';
		if(isset($workArr[$d->dateToday])) $w = $workArr[$d->dateToday];
		
		if(isset($w->publishBy) && empty($w->publishBy)){
			echo '<tr bgcolor="#ffb2b2">';
			$unpublished = true;			
		}else echo '<tr>';
		
			echo '<td align="left">'.$d->dateToday.'</td>';
			if(!empty($w)){				
				echo '<td>'.(($w->publishTimePaid==0)?'-':number_format($w->publishTimePaid, 1)).'</td>';
				echo '<td>'.(($w->publishDeduct==0)?'-':number_format($w->publishDeduct, 1)).'</td>';
				echo '<td>'.$holidayArr[$d->holidayType].'</td>';
				echo '<td>'.(($w->publishND==0)?'-':number_format($w->publishND, 1)).'</td>';
				echo '<td align="right" width="80px"><a href="'.$this->config->base_url().'timecard/'.$w->empID_fk.'/viewlogdetails/?d='.$d->dateToday.'" class="iframe">View Details</a></td>';
				
				$workHR += $w->publishTimePaid;
				$takenHR += $w->publishDeduct;
				$ndHR += $w->publishND;
			}else{
				echo '<td><br/></td><td><br/></td><td><br/></td><td><br/></td><td><br/></td>';
			}
		echo '</tr>';
	}
	echo '<tr class="trhead">';
		echo '<td align="left">Summary</td>';
		echo '<td>'.number_format($workHR, 1).'</td>';
		echo '<td>'.number_format($takenHR, 1).'</td>';
		echo '<td><br/></td>';
		echo '<td>'.number_format($ndHR, 1).'</td>';
		echo '<td><br/></td>';
	echo '</tr>';
?>
</table>
<?php
	if($unpublished === true) echo '<i>Pink background is unpublished</i>';
?>




