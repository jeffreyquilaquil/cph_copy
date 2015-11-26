<br/>
<a href="<?= $this->config->base_url().'timecard/'.$visitID.'/payslips/' ?>"><button class="floatright btnclass"><< Back to All Payslips</button></a>

<?php
	echo '<h3>Payroll Info';
		if($this->access->accessFullHRFinance==true && $payInfo->status<2) echo '&nbsp;&nbsp;<button onClick="regenerate()">Regenerate Payslip</button>';
	echo '</h3>';
	echo '<hr/>';
	
	if($this->access->accessFullFinance==false && $visitID==$this->user->empID)
		echo '<b><a href="'.$this->config->base_url().'sendEmail/payslipinquiry/'.$payInfo->payslipID.'/'.$payInfo->empID.'/" class="iframe colorgreen">If you  have questions, click here to send email to accounting.</a></b> <hr/>';
	
	

	if(count($payInfo)==0){
		echo 'No payslip record.';
	}else{
		$hourlyRate = $this->payrollM->getDailyHourlyRate($payInfo->monthlyRate, 'hourly');
		
		$detailArr = array();
		foreach($dataPay AS $d){
			$detailArr[$d->payType][] = $d;
		}
?>
<table class="tableInfo">
	<tr class="trlabel"><td colspan=2>Payroll</td></tr>
	<tr>
		<td width="20%">Employee Name</td>
		<td><?= $payInfo->lname.', '.$payInfo->fname ?></td>
	</tr>
	<tr>
		<td width="20%">Period</td>
		<td><?= date('F d, Y', strtotime($payInfo->payPeriodStart)).' - '.date('F d, Y', strtotime($payInfo->payPeriodEnd)).' <b>('.(($payInfo->payType=='semi')?'Semi-Monthly':'Monthly').')</b>' ?></td>
	</tr>
	<tr>
		<td>Monthly Pay</td>
		<td><?= $this->textM->convertNumFormat($payInfo->monthlyRate).' (<i>'.$hourlyRate.'/hour</i>)' ?></td>
	</tr>
	<tr>
		<td>Gross Pay</td>
		<td><?= $this->textM->convertNumFormat($payInfo->earning) ?></td>
	</tr>
	<tr>
		<td>Total Taxable</td>
		<td><?= $this->textM->convertNumFormat($payInfo->totalTaxable) ?></td>
	</tr>
	<tr>
		<td>Deductions</td>
		<td><?= $this->textM->convertNumFormat($payInfo->deduction) ?></td>
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
	<tr>
		<td>pay</td>
		<td>Base Pay</td>
		<td>-</td>
		<td><?= $this->textM->convertNumFormat($payInfo->basePay) ?></td>
	</tr>
	<?php
		foreach($dataPay AS $d){
			echo '<tr>';
				echo '<td>'.$payCatArr[$d->payCategory].'</td>';
				echo '<td>'.$d->payName.'</td>';
				echo '<td>'.(($d->numHR>0)?number_format($d->numHR,1):'-').'</td>';
				echo '<td>';
					echo (($d->payType=="debit")?'-':'').$this->textM->convertNumFormat($d->payValue);
				echo '</td>';
			echo '<tr>';
		}
	?>
</table>

<br/>
<table class="tableInfo tacenter">
	<tr class="trlabel taleft"><td colspan=7>Hours Worked</td></tr>
	<tr class="trhead">
		<td align="left" width="15%">Date</td>
		<td>Worked Hours</td>
		<td>Taken Hours</td>
		<td>ND</td>
		<td>OT</td>
		<td>Type</td>
		<td width="30px"><br/></td>
	</tr>
<?php
	$workHR = 0;
	$takenHR = 0;
	$ndHR = 0;	
	$otHR = 0;	
	$unpublished = false;
	$workArr = array();
	foreach($dataWorked AS $w){
		$workArr[$w->slogDate] = $w;
	}
	
	foreach($dataDates AS $d){
		$w = '';
		if(isset($workArr[$d->dateToday])) $w = $workArr[$d->dateToday];
		
		if(isset($w->publishBy) && empty($w->publishBy)){
			echo '<tr style="background-color:#ffb2b2">';
			$unpublished = true;			
		}else echo '<tr>';
		
			echo '<td align="left">'.$d->dateToday.'</td>';
			if(!empty($w)){				
				echo '<td>'.(($w->publishTimePaid==0)?'-':number_format($w->publishTimePaid, 1)).'</td>';
				echo '<td>'.(($w->publishDeduct==0)?'-':number_format($w->publishDeduct, 1)).'</td>';				
				echo '<td>'.(($w->publishND==0)?'-':number_format($w->publishND, 1)).'</td>';
				echo '<td>'.(($w->publishOT==0)?'-':number_format($w->publishOT, 1)).'</td>';
				echo '<td>'.$holidayArr[$d->holidayType].'</td>';
				echo '<td align="right"><a href="'.$this->config->base_url().'timecard/'.$w->empID_fk.'/viewlogdetails/?d='.$d->dateToday.'" class="iframe"><img src="'.$this->config->base_url().'css/images/view-icon2.png" width="25px"/></a></td>';				
				
				$workHR += $w->publishTimePaid;
				$takenHR += $w->publishDeduct;
				$ndHR += $w->publishND;
				$otHR += $w->publishOT;
			}else{
				echo '<td><br/></td><td><br/></td><td><br/></td><td><br/></td><td><br/></td><td><br/></td>';
			}
		echo '</tr>';
	}
	echo '<tr class="trhead">';
		echo '<td align="left">Summary</td>';
		echo '<td>'.number_format($workHR, 1).'</td>';
		echo '<td>'.number_format($takenHR, 1).'</td>';		
		echo '<td>'.number_format($ndHR, 1).'</td>';
		echo '<td>'.number_format($otHR, 1).'</td>';
		echo '<td><br/></td>';
		echo '<td><br/></td>';
	echo '</tr>';
?>
</table>
<?php
	if($unpublished === true) echo '<i>Pink background is unpublished</i>';

}
?>

<script type="text/javascript">
	function regenerate(){
		displaypleasewait();
		$.post('<?= $this->config->base_url().'timecard/regeneratepayslip/'.$payslipID.'/' ?>',{}, 
		function(){
			location.reload();
		});
	}
</script>




