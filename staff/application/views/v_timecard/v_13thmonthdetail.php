<h3>13th Month Details</h3>
<hr/>
<table class="tableInfo">
	<tr class="trlabel">
		<td>Employee ID</td>
		<td>Employee Name</td>
		<td>Period</td>
		<td>Total Basic Pay</td>
		<td>Total Deductions</td>
		<td>13th Month Pay</td>
	</tr>
	<tr>
		<td><?= $dataInfo->idNum ?></td>
		<td><b><?= $dataInfo->lname.', '.$dataInfo->fname ?></b></td>
		<td><?= date('M', strtotime($dataInfo->periodFrom)).' - '.date('M Y', strtotime($dataInfo->periodTo)) ?></td>
		<td><?= $this->textM->convertNumFormat($dataInfo->totalBasic) ?></td>
		<td><?= $this->textM->convertNumFormat($dataInfo->totalDeduction) ?></td>
		<td><b style="font-size:14px;">PHP <?= $this->textM->convertNumFormat($dataInfo->totalAmount) ?></b>
			<?= '<b id="berror" class="hidden"><i class="errortext fs11px"><br/>Please regenerate pay</i></b>' ?>
		</td>
	</tr>
</table>
<br/>
<table class="tableInfo">
	<tr class="trhead">
		<td width="25%">Pay Dates</td>
		<td width="25%">Basic Pay</td>
		<td width="25%">Deductions</td>
		<td width="25%">13th Month Pay</td>
	</tr>
<?php
	$basePay = 0;
	$deduction = 0;
	$pay = 0;
	foreach($dataMonth AS $dates=>$me){
		echo '<tr>';
			echo '<td>'.date('d-M-Y', strtotime($dates)).'</td>';
		if(!empty($me)){
			echo '<td>'.$this->textM->convertNumFormat($me->basePay).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($me->deduction).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($me->pay).'</td>';
			
			$basePay += $me->basePay;
			$deduction += ($me->deduction-$me->adj);
			$pay += $me->pay;
		}else{
			echo '<td>0.00</td>';
			echo '<td>0.00</td>';
			echo '<td>0.00</td>';			
		}
		echo '</tr>';
	}
?>
	<tr class="weightbold">
		<td style="border-top:2px solid #000;">TOTAL</td>
		<td style="border-top:2px solid #000;"><?= $this->textM->convertNumFormat($basePay) ?></td>
		<td style="border-top:2px solid #000;"><?= $this->textM->convertNumFormat($deduction) ?></td>
		<td style="border-top:2px solid #000; font-size:14px;">
		<?php
			$total = $this->textM->convertNumFormat(($basePay-$deduction)/12);
			echo 'PHP '.$total;
			if($total!=$this->textM->convertNumFormat($dataInfo->totalAmount)){
				echo '<br/><i class="errortext fs11px">Please regenerate pay</i>';
				echo '<script> $("#berror").removeClass("hidden"); </script>';
			}
		?>
		</td>
	</tr>
</table>
<br/>
<i><b>Date Generated:</b> <?= date('F d, Y h:i a', strtotime($dataInfo->dateGenerated)) ?></i>&nbsp;&nbsp;&nbsp;
<button class="btnclass btngreen" onClick="regenerateMonth(<?= $dataInfo->empID_fk ?>,<?= $dataInfo->tcmonthID ?>);">Regenerate</button>


<script type="text/javascript">
	function regenerateMonth(empID, id){
		displaypleasewait();
		$.post('<?= $this->config->base_url().'timecard/generate13thmonth/?empIDs=' ?>'+empID, {submitType:'regenerate', monthID:id }, function(){
			location.reload();
		});
	}
</script>