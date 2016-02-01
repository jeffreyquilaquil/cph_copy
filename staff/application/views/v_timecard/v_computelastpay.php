<?php
	if($pageType=='showpay'){
		echo '<h3>Last Pay Details</h3>';
		
		$salary = $payInfo->monthlyRate;
		$dailyRate = $this->payrollM->getDailyHourlyRate($salary, 'daily');
		$leaveAmount = $payInfo->addLeave * $dailyRate;			
	}else{
		echo '<h3>Compute Last Pay</h3>';
		
		$salary = $this->textM->decryptText($staffInfo->sal);		
		$dailyRate = $this->payrollM->getDailyHourlyRate($salary, 'daily');
		$leaveAmount = $staffInfo->leaveCredits * $dailyRate;			
	}
	
	$hourlyRate = $this->payrollM->getDailyHourlyRate($salary, 'hourly');
	
	$totalIncome = 0;
	$totalSalary = 0;
	$totalDeduction = 0;
	$totalTaxable = 0;
	$totalTaxWithheld = 0;
	$total13th = 0;
	$totalNet = 0;
	$personalExemption = 50000;	
?>
	<hr/>
	<table class="tableInfo">
		<tr class="trhead">
			<td>Employee ID</td>
			<td>Employee Name</td>
			<td>Start Date</td>
			<td>End Date</td>
			<td>Salary</td>
		</tr>
	<?php
		echo '<tr>';
			echo '<td>'.$staffInfo->idNum.'</td>';
			echo '<td>'.$staffInfo->lname.', '.$staffInfo->fname.'</td>';
			echo '<td>'.date('F d, Y', strtotime($staffInfo->startDate)).'</td>';
			echo '<td>'.(($staffInfo->endDate!='0000-00-00')?date('F d, Y', strtotime($staffInfo->endDate)):'<b class="errortext">Not yet determined</b>').'</td>';
			echo '<td>'.$this->textM->convertNumFormat($salary).' <span class="colorgray">('.$dailyRate.'/daily)</span></td>';
		echo '</tr>';
	?>
	</table>
	<br/>

	<?php
		if($pageType=='showperiod'){
			if($staffInfo->endDate=='0000-00-00'){
				echo '<hr/><p class="errortext">Please input <b>Separation Date</b> first. Click <a href="'.$this->config->base_url().'staffinfo/'.$staffInfo->username.'/" target="_blank">here</a> to visit staff info page.</p>';
			}else{
				$yearOption = array();
				$monthOption = array();
				$yearToday = date('Y', strtotime('+1 year'));
				for($y=2014; $y<=$yearToday; $y++){
					$yearOption[$y] = $y;
				}	
				
				for($m=1;$m<=12;$m++){
					$month_name = date('F', strtotime('2015-'.$m.'-01'));
					$monthOption[$month_name] = $month_name;
				}
				echo '<table class="tableInfo">';
				echo '<tr class="trlabel"><td>Last Pay Details</td></tr>';
				echo '<tr>';
					echo '<td>';
						echo '<form method="POST" action="" onSubmit="displaypleasewait();">';
							echo '<b>From </b> January '.$this->textM->formfield('selectoption', 'periodFromYear', date('Y'), 'padding5px', '', '', $yearOption);
							echo ' <b>to</b> ';
							echo $this->textM->formfield('selectoption', 'periodToMonth', date('F'), 'padding5px', '', '', $monthOption);
							echo '&nbsp;';
							echo $this->textM->formfield('selectoption', 'periodToYear', date('Y'), 'padding5px', '', '', $yearOption);
							echo '&nbsp;&nbsp;';
							echo $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
							echo $this->textM->formfield('hidden', 'submitType', 'submitPeriod');
						echo '<form>';
					echo '</td>';
				echo '</tr>';
				echo '</table>';
			}					
		}else{
			$payArr = array();
			foreach($dataMonth AS $m){
				$payArr[$m->payDate] = $m;
			}
			
			echo '<table class="tableInfo">';
				echo '<tr class="trlabel"><td colspan=8>Monthly Details&nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" class="droptext" onClick="showHide(\'trtaxIncome\', this)">Hide</a>]</td></tr>';
				echo '<tr class="trhead">';
					echo '<td>Payslip Date</td>';
					echo '<td>Gross Income</td>';
					echo '<td>Basic Salary</td>';
					echo '<td>Attendance Deduction</td>';
					echo '<td>Taxable Income</td>';
					echo '<td>Tax Withheld</td>';
					echo '<td>13th Month Pay</td>';
					echo '<td>NET Pay</td>';
				echo '</tr>';
				
			$month13 = 0;
			foreach($dateArr AS $date){
				echo '<tr class="trtaxIncome">';
					echo '<td>'.date('d-M-Y', strtotime($date)).'</td>';
					if(isset($payArr[$date])){
						echo '<td>'.$this->textM->convertNumFormat($payArr[$date]->earning).'</td>';
						echo '<td>'.$this->textM->convertNumFormat($payArr[$date]->basePay).'</td>';
						echo '<td>'.(($payArr[$date]->deductions>0)?'-':'').$this->textM->convertNumFormat($payArr[$date]->deductions).'</td>';
						echo '<td>'.$this->textM->convertNumFormat($payArr[$date]->totalTaxable).'</td>';
						echo '<td>'.$this->textM->convertNumFormat($payArr[$date]->incomeTax).'</td>';
						
						//13th month computation = (basepay-deduction)/12 NO 13th month if end date before Jan 25
						if($staffInfo->endDate>=date('Y').'-01-25'){
							$month13 = ($payArr[$date]->basePay - $payArr[$date]->deductions)/12;
						}
						
						echo '<td>'.$this->textM->convertNumFormat($month13).'</td>'; 
						echo '<td><b><a href="javascript:void(0);" title="Click to remove" onClick="requestRemove('.$payArr[$date]->payslipID.')">'.$this->textM->convertNumFormat($payArr[$date]->net).'</a></b></td>';
						
						$totalIncome += $payArr[$date]->earning;
						$totalSalary += $payArr[$date]->basePay;
						$totalDeduction += $payArr[$date]->deductions;
						$totalTaxable += $payArr[$date]->totalTaxable;					
						$totalTaxWithheld += $payArr[$date]->incomeTax;					
						$total13th += $month13;					
						$totalNet += $payArr[$date]->net;						
					}else{
						echo '<td>0.00</td>';
						echo '<td>0.00</td>';
						echo '<td>0.00</td>';
						echo '<td>0.00</td>';
						echo '<td>0.00</td>';
						echo '<td>0.00</td>';
						echo '<td>0.00</td>';
					}
				echo '</tr>';
			}
			echo '<tr class="weightbold" style="background-color:#ddd;">';
				echo '<td>TOTALS</td>';
				echo '<td>'.$this->textM->convertNumFormat($totalIncome).'</td>';
				echo '<td>'.$this->textM->convertNumFormat($totalSalary).'</td>';
				echo '<td>'.$this->textM->convertNumFormat($totalDeduction).'</td>';
				echo '<td>'.$this->textM->convertNumFormat($totalTaxable).'</td>';
				echo '<td>'.$this->textM->convertNumFormat($totalTaxWithheld).'</td>';
				echo '<td>'.$this->textM->convertNumFormat($total13th).'</td>';
				echo '<td>'.$this->textM->convertNumFormat($totalNet).'</td>';
			echo '</tr>';

			echo '<tr class="trtaxIncome">';
				echo '<td colspan=8><i class="errortext">*** Click net pay if you want to remove generated payslip.</i></td>';
			echo '</tr>';
			echo '</table>';
		
			if($pageType=='showpay'){
				$totalTaxable = $payInfo->taxFromCurrent;
				$totalTaxIncome = $payInfo->taxFromPrevious + $payInfo->taxFromCurrent;
			}
			
			///COMPUTATION FOR TAX DUE
			echo '<br/>';		
			echo '<table class="tableInfo">';
				echo '<tr class="trlabel">';
					echo '<td colspan=2>Computation of Tax Due</td>';
				echo '</tr>';
				
				echo '<tr>';
					echo '<td width="270px">Taxable Compensation from Previous Employer</td>';
					if($pageType=='showpay') echo '<td>'.$this->textM->convertNumFormat($payInfo->taxFromPrevious).'</td>'; 
					else echo '<td>'.$this->textM->formfield('number', 'taxFromPrevious', '0.00', 'padding5px', '', 'step="any"').'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Current Taxable Income</td>';
					echo '<td>'.$this->textM->convertNumFormat($totalTaxable).'</td>';
				echo '</tr>';
				echo '<tr class="trcompute">';
					echo '<td>Total Taxable Income</td>';
					echo '<td id="val_totalTaxableIncome">'.((isset($totalTaxIncome))?$this->textM->convertNumFormat($totalTaxIncome):'0.00').'</td>';
				echo '</tr>';
				
				echo '<tr>';
					echo '<td colspan=2>LESS-EXEMPTION:</td>';
				echo '</tr>';
				
				echo '<tr>';
					echo '<td>Personal Exemption</td>';
					echo '<td>-'.$this->textM->convertNumFormat($personalExemption).'</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td>Dependents</td>';
					echo '<td>';
						
						$dependents = $this->payrollM->getTaxStatus($staffInfo->taxstatus, 'num');
						if($dependents=='') echo '0';
						else{
							echo '-'.$this->textM->convertNumFormat($dependents*25000);
							echo '&nbsp;&nbsp;&nbsp;('.$dependents.' x 25,000)';
							$personalExemption += ($dependents*25000);
						}
					echo '</td>';
				echo '</tr>';
				
				///////COMPUTATION
				if($pageType=='showpay' && count($dataBracket)>0){
					$taxBracket = $dataBracket->minRange;
					$excessTax = $payInfo->taxNetTaxable-$dataBracket->minRange;
					$percenta = $dataBracket->excessPercent/100;
					$excessPer = $excessTax * $percenta;
				}
				echo '<tr class="trcompute">';
					echo '<td>NET Taxable Income</td>';
					echo '<td id="val_netTaxableIncome">'.(($pageType=='showpay')?$this->textM->convertNumFormat($payInfo->taxNetTaxable):'0.00').'</td>';
				echo '</tr>';
				echo '<tr class="trcompute">';
					echo '<td>Tax Bracket</td>';
					echo '<td id="val_taxBracket">'.((isset($taxBracket))?$this->textM->convertNumFormat($taxBracket):'0.00').'</td>';
				echo '</tr>';
				echo '<tr class="trcompute">';
					echo '<td>Excess of Tax Base</td>';
					echo '<td id="val_excess">'.((isset($percenta))?'-'.$this->textM->convertNumFormat($excessTax):'0.00').'</td>';
				echo '</tr>';
				echo '<tr class="trcompute">';
					echo '<td>Multiply By</td>';
					echo '<td id="val_multiply">'.((isset($percenta))?$this->textM->convertNumFormat($percenta):'0.00').'</td>';
				echo '</tr>';
				echo '<tr class="trcompute">';
					echo '<td>Percentage of Excess</td>';
					echo '<td id="val_percentExcess">'.((isset($excessPer))?$this->textM->convertNumFormat($excessPer):'0.00').'</td>';
				echo '</tr>';
				echo '<tr class="trcompute">';
					echo '<td>Add Basic Tax</td>';
					echo '<td id="val_addbasic">'.((isset($dataBracket->baseTax))?$this->textM->convertNumFormat($dataBracket->baseTax):'0.00').'</td>';
				echo '</tr>';
				echo '<tr class="trcompute" style="background-color:#bbb;">';
					echo '<td>Tax Due for '.date('Y', strtotime($periodTo)).'</td>';
					echo '<td id="val_taxDue">'.(($pageType=='showpay')?$this->textM->convertNumFormat($payInfo->taxDue):'0.00').'</td>';
				echo '</tr>';	

				///START WITHHOLDING TAX ALLOCATION
				echo '<tr class="trTaxAlloc trlabel">';
					echo '<td colspan=2>Withholding Tax Allocation</td>';
				echo '</tr>';			
				echo '<tr class="trTaxAlloc">';
					echo '<td width="270px">Income Tax Withheld</td>';
					echo '<td>'.$this->textM->convertNumFormat($totalTaxWithheld).'</td>';
				echo '</tr>';
				echo '<tr class="trTaxAlloc">';
					echo '<td>Income Tax Due for the Year</td>';
					echo '<td id="val_yearDue">'.(($pageType=='showpay')?$this->textM->convertNumFormat($payInfo->taxDue):'0.00').'</td>';
				echo '</tr>';
				echo '<tr id="trtaxRefund" class="trTaxAlloc weightbold" style="background-color:#bbb;">';
					echo '<td>Tax <span class="txtRefund">Refund</span> for the year '.date('Y', strtotime($periodTo)).'</td>';
					echo '<td id="val_taxRefund">'.(($pageType=='showpay')?$this->textM->convertNumFormat($payInfo->taxRefund):'0.00').'</td>';
				echo '</tr>';			
				///END WITHHOLDING TAX ALLOCATION
				
				///COMPUTE TAX BUTTON
				if($pageType!='showpay'){
					echo '<tr>';
						echo '<td><br/></td>';
						echo '<td id="tdtaxdue"><button class="btnclass btngreen" onClick="compute(\'tdtaxdue\');">Compute Tax Due</button>
							<img class="hidden" src="'.$this->config->base_url().'css/images/small_loading.gif" width="20px"/>
						</td>';
					echo '</tr>';
				}
				
				///ADD ONS
				if($pageType=='showpay'){
					$total13th = $payInfo->add13th;
					$leaveAmount = $payInfo->addLeave * $dailyRate;
				}
				echo '<tr class="trAddOns"><td colspan=2><br/></td></tr>';
				echo '<tr class="trlabel trAddOns"><td colspan=2>Add Ons &nbsp;&nbsp;'.(($pageType!='showpay')?'<button type="button" id="btnAddOn">+ Add</button>':'').'</td></tr>';
				echo '<tr class="trAddOns">';
					echo '<td>13th Month Pay</td>';
					echo '<td>'.$this->textM->convertNumFormat($total13th).'</td>';
				echo '</tr>';
				echo '<tr class="trAddOns">';
					echo '<td>Unused Leave Credits</td>';
					echo '<td>';					
						echo $this->textM->convertNumFormat($leaveAmount).'&nbsp;&nbsp;&nbsp;<span class="colorgray">('.(($pageType=='showpay')?$payInfo->addLeave:$staffInfo->leaveCredits).' remaining leave credits x '.$dailyRate.' daily rate)</span>';
					echo '</td>';
				echo '</tr>';
				echo '<tr class="trAddOns">';
					echo '<td>Unpaid Salary</td>';
					echo '<td>';
					if($pageType=='showpay'){
						echo $this->textM->convertNumFormat($payInfo->addUnpaid*$hourlyRate).' <span class="colorgray">('.$payInfo->addUnpaid.' hours x '.$hourlyRate.')</span>';
					}else{
						echo $this->textM->formfield('number', 'unpaidSal', '0.00', 'padding5px', '', 'step="any" style="width:50px"').' hours x '.$hourlyRate.' = <b id="unpaid">0.00</b>&nbsp;&nbsp;&nbsp;&nbsp;[<a href="'.$this->config->base_url().'timecard/'.$staffInfo->empID.'/timelogs/" target="_blank">Visit Timelogs</a>]';
					}
				echo '</tr>';
				
				///ADDITIONAL ADD ONS
				if(!empty($payInfo->addOns)){
					$addArr = unserialize(stripslashes($payInfo->addOns));
					foreach($addArr AS $k=>$add){
						echo '<tr class="trAddOns">';
							echo '<td>'.ucwords($k).'</td>';
							echo '<td>'.$this->textM->convertNumFormat($add).'</td>';
						echo '</tr>';
					}
				}
				
				echo '<tr id="trTotalAddOn" class="weightbold trAddOns" style="background-color:#bbb;">';
					echo '<td>Add Ons Total</td>';
					echo '<td id="totalAddOn">'.(($pageType=='showpay')?$this->textM->convertNumFormat($payInfo->addTotal):$this->textM->convertNumFormat($total13th + $leaveAmount)).'</td>';
				echo '</tr>';
				
				///DEDUCTIONS
				echo '<tr class="trDeductions"><td colspan=2><br/></td></tr>';
				echo '<tr class="trlabel trDeductions"><td colspan=2>Deductions &nbsp;&nbsp;'.(($pageType!='showpay')?'<button type="button" id="btnDeductions">+ Add</button>':'').'</td></tr>';
				if($pageType!='showpay' || ($pageType=='showpay' && $payInfo->deductMaxicare>0)){
					echo '<tr class="trDeductions">';
						echo '<td>Maxicare</td>';
						if($pageType=='showpay') echo '<td>'.$this->textM->convertNumFormat($payInfo->deductMaxicare).'</td>';
						else echo '<td>'.$this->textM->formfield('number', 'maxicare', '0.00', 'inputdeduct padding5px', '', 'step="any"').'</td>';
					echo '</tr>';
				}
				
				if($pageType!='showpay' || ($pageType=='showpay' && $payInfo->deductArrears>0)){
					echo '<tr class="trDeductions">';
						echo '<td>Payment of Arrears</td>';
						if($pageType=='showpay') echo '<td>'.$this->textM->convertNumFormat($payInfo->deductArrears).'</td>';
						else echo '<td>'.$this->textM->formfield('number', 'paymentArrears', '0.00', 'inputdeduct padding5px', '', 'step="any"').'</td>';
					echo '</tr>';
				}
				
				if($pageType!='showpay' || ($pageType=='showpay' && $payInfo->deductResti>0)){
					echo '<tr class="trDeductions">';
						echo '<td>Financial Restitutions</td>';
						if($pageType=='showpay') echo '<td>'.$this->textM->convertNumFormat($payInfo->deductResti).'</td>';
						else echo '<td>'.$this->textM->formfield('number', 'restitution', '0.00', 'inputdeduct padding5px', '', 'step="any"').'</td>';
					echo '</tr>';
				}
				
				///ADDITIONAL DEDUCTIONS
				if(!empty($payInfo->addDeductions)){
					$dedArr = unserialize(stripslashes($payInfo->addDeductions));
					foreach($dedArr AS $d=>$ded){
						echo '<tr class="trAddOns">';
							echo '<td>'.ucwords($d).'</td>';
							echo '<td>'.$this->textM->convertNumFormat($ded).'</td>';
						echo '</tr>';
					}
				}
				
				echo '<tr id="trTotalDeduct" class="weightbold trDeductions" style="background-color:#bbb;">';
					echo '<td>Deductions Total</td>';
					echo '<td id="totalDeductions">'.(($pageType=='showpay')?$this->textM->convertNumFormat($payInfo->deductTotal):'0.00').'</td>';
				echo '</tr>';
				
				////TOTALS
				echo '<tr class="trLastPay"><td colspan=2><br/></td></tr>';
				echo '<tr class="trlabel trLastPay"><td colspan=2>Last Pay Totals</td></tr>';
				echo '<tr class="trLastPay">';
					echo '<td>Tax <span class="txtRefund">'.((!isset($payInfo) || $payInfo->taxRefund>0)?'Refund':'Deficit').'</span> for the Year '.date('Y', strtotime($periodTo)).'</td>';
					echo '<td class="cls_taxRefund">'.((isset($payInfo->taxRefund))?$this->textM->convertNumFormat($payInfo->taxRefund):'0.00').'</td>';
				echo '</tr>';
				echo '<tr class="trLastPay">';
					echo '<td>Add Ons</td>';
					echo '<td class="cls_totalAddOn">'.$this->textM->convertNumFormat(((isset($payInfo->addTotal))?$payInfo->addTotal:$total13th + $leaveAmount)).'</td>';
				echo '</tr>';
				echo '<tr class="trLastPay">';
					echo '<td>Deductions</td>';
					echo '<td class="cls_totalDeductions">'.((isset($payInfo->deductTotal))?'-'.$this->textM->convertNumFormat($payInfo->deductTotal):'0.00').'</td>';
				echo '</tr>';			
				echo '<tr class="weightbold trLastPay" style="background-color:#bbb; font-size:16px;">';
					echo '<td>NET LAST PAY</td>';
					echo '<td id="netLastPay">'.((isset($payInfo->netLastPay))?$this->textM->convertNumFormat($payInfo->netLastPay):'0.00').'</td>';
				echo '</tr>';
				
				if($pageType=='showpay'){
					echo '<tr>';
						echo '<td>Generated By</td>';
						echo '<td><i>'.$payInfo->generatedBy.' '.date('d-M-Y h:i a', strtotime($payInfo->dateGenerated)).'</i></td>';
					echo '</tr>';
				}
				
				///BUTTON SAVING COMPUTATION
				if($pageType!='showpay' && $this->access->accessFullFinance==true){
					echo '<tr class="trLastPay">';
						echo '<td><br/></td>';
						echo '<td><button class="btnclass btngreen" onClick="savecomputation();">Save Last Pay Computation</button></td>';
					echo '</tr>';
				}
			echo '</table>';
			
			
			
			
			echo '<script type="text/javascript">';
			echo '$(function(){';
				if($pageType=='showpay'){
					echo 'computeLastPay();';
					echo 'showHideTR(1);';
				}else{
					echo 'computeLastPay();';
					echo 'showHideTR(0);';
				}			
			echo '})';
			echo '</script>'; 
		} 
?>

<script type="text/javascript">
	$(function(){		
		$('input[name="unpaidSal"]').blur(function(){
			getTotalAddOn();
		});
		
		$('.inputdeduct').blur(function(){
			getTotalDeduct();
		});
		
		$('#btnAddOn').click(function(){
			$('<tr class="trAddOnAdd trAddOns"><td><?= $this->textM->formfield('text', 'addOnName[]', '', 'forminput', 'Add On Name') ?></td><td><?= $this->textM->formfield('number', 'addOnAmount[]', '', 'forminput', 'Add On Amount', 'style="width:250px;" onBlur="getTotalAddOn()"') ?> <button type="button" onClick="removeAddOn(this)">- Remove</button></td></tr>').insertBefore('#trTotalAddOn');
		});
		
		$('#btnDeductions').click(function(){
			$('<tr class="trAddDeduct trDeductions"><td><?= $this->textM->formfield('text', 'deductName[]', '', 'forminput', 'Deduction Name') ?></td><td><?= $this->textM->formfield('number', 'deductAmount[]', '', 'forminput inputdeduct', 'Deduction Amount', 'style="width:250px;" onBlur="getTotalDeduct()"') ?> <button type="button" onClick="removeDeduct(this)">- Remove</button></td></tr>').insertBefore('#trTotalDeduct');
		});
	});
	
	function getTotalAddOn(){
		dailyRate = parseFloat('<?= $hourlyRate ?>');
		total = parseFloat('<?= $total13th + $leaveAmount ?>');
		
		unpaidSal = $('input[name="unpaidSal"]').val();
		if(unpaidSal==''){
			unpaidSal = 0;
			$('input[name="unpaidSal"]').val(0);
		}
		unpaidSal = unpaidSal * dailyRate;		
		total += parseFloat(unpaidSal); ///UNPAID SALARY
		$('#unpaid').text(unpaidSal.toLocaleString());	
		
		///ADD ONS
		$('input[name="addOnAmount\[\]"]').each(function(){
			if($.isNumeric($(this).val())){
				total += parseFloat($(this).val(), 2);
			}
		});
		
		total = parseFloat(total);
		$('#totalAddOn').text(total.toLocaleString()); 
		$('.cls_totalAddOn').text(total.toLocaleString());
		
		computeLastPay();
	}
	
	function getTotalDeduct(){
		deductions = 0;
		//if($(this).val()=='') $(this).val(0);
		
		deductions = parseFloat($('input[name="maxicare"]').val()) + parseFloat($('input[name="paymentArrears"]').val()) + parseFloat($('input[name="restitution"]').val());
		
		///additional deductions
		$('input[name="deductAmount\[\]"]').each(function(){
			if($.isNumeric($(this).val())){
				deductions += parseFloat($(this).val(), 2);
			}
		});
		
		deductions = parseFloat(deductions);
		$('#totalDeductions').text(deductions.toLocaleString());
		$('.cls_totalDeductions').text(deductions.toLocaleString());
		computeLastPay();
	}
	
	function removeAddOn(t){
		$(t).closest("tr").remove();
		getTotalAddOn();
	}
	
	function removeDeduct(t){
		$(t).closest("tr").remove();
		getTotalDeduct();
	}
	
	
	
	function showHideTR(show){
		if(show==1){ //show
			$('.trcompute').removeClass('hidden');
			$('.trTaxAlloc').removeClass('hidden');
			$('.trAddOns').removeClass('hidden');
			$('.trDeductions').removeClass('hidden');
			$('.trLastPay').removeClass('hidden');
		}else{
			$('.trcompute').addClass('hidden');
			$('.trTaxAlloc').addClass('hidden');
			$('.trAddOns').addClass('hidden');
			$('.trDeductions').addClass('hidden');
			$('.trLastPay').addClass('hidden');
		}
	}
	
	function computeLastPay(){
		pay = 0;
		pay = myParse($('#totalAddOn').text()) - myParse($('#totalDeductions').text());
		
		if($('#val_taxRefund').text()!=''){
			pay = pay + myParse($('#val_taxRefund').text());
		} 		
		
		$('#netLastPay').text(pay.toLocaleString());
	}
	
	function myParse(num){
		num = num.replace(',','');
		return parseFloat(num);
	}
		
	function showHide(cls, t){
		perfect = $(t).text();
		
		if(perfect=='Show'){
			$(t).text('Hide');
			$('.'+cls).removeClass('hidden');
		}else{
			$(t).text('Show');
			$('.'+cls).addClass('hidden');
		}
	}

	
	function compute(td){
		if($('input[name="taxFromPrevious"]').val()=='') prevTaxIncome = 0;
		else prevTaxIncome = parseFloat($('input[name="taxFromPrevious"]').val());
		
		currTaxIncome = parseFloat('<?= $totalTaxable ?>');
		totalTax = prevTaxIncome + currTaxIncome;
		netTax = totalTax-'<?= $personalExemption ?>';
			
		if(netTax>0){
			$('#'+td+' img').removeClass('hidden');
			$('#'+td+' button').removeClass('btngreen');
			$('#'+td+' button').attr('disabled', 'disabled');
			
			$.post('<?= $this->config->base_url().'timecard/computelastpay/' ?>',{submitType:'getTaxBracket', netTax:netTax},function(info){
				if(info!=''){
					showHideTR(1);
					$('#'+td+' img').addClass('hidden');
					$('#'+td+' button').addClass('btngreen');
					$('#'+td+' button').removeAttr('disabled');
					
					info = info.split('|');
					taxBracket = parseFloat(info[2]);
					excess = netTax-taxBracket;
					multiply = info[0]/100;
					percentExcess = excess*multiply;
					addBasic = info[1];
					taxDue = parseFloat(percentExcess)+parseFloat(addBasic);
					refund = parseFloat('<?= $totalTaxWithheld ?>') - taxDue;
					
					$('#val_totalTaxableIncome').text(totalTax.toLocaleString());
					$('#val_netTaxableIncome').text(netTax.toLocaleString());
					
					$('#val_taxBracket').text('-'+taxBracket.toLocaleString());
					$('#val_excess').text(excess.toLocaleString());
					$('#val_addbasic').text(addBasic.toLocaleString());					
					$('#val_multiply').text(multiply.toLocaleString());
					$('#val_percentExcess').text(percentExcess.toLocaleString());
					$('#val_taxDue').text(taxDue.toLocaleString());
					$('#val_yearDue').text(taxDue.toLocaleString());
					$('#val_taxRefund').text(refund.toLocaleString()); $('.cls_taxRefund').text($('#val_taxRefund').text());
					
					if(refund<0) $('.txtRefund').text('Deficit');
					else $('.txtRefund').text('Refund');
					
					$('#'+td+' button').text('Recompute Tax Due');
					computeLastPay();
				}
			});
		}else{
			showHideTR(1);
			
			$('#val_taxBracket').text('0.00');
			$('#val_excess').text('0.00');
			$('#val_addbasic').text('0.00');				
			$('#val_multiply').text('0.00');
			$('#val_percentExcess').text('0.00');
			$('#val_taxDue').text('0.00');
			$('#val_yearDue').text('0.00');
			$('#val_yearDue').text('0.00');
			refund = parseFloat('<?= $totalTaxWithheld ?>');
			$('#val_taxRefund').text(refund.toLocaleString()); $('.cls_taxRefund').text($('#val_taxRefund').text());
			$('#val_totalTaxableIncome').text(totalTax.toLocaleString());
			$('#val_netTaxableIncome').text(netTax.toLocaleString());
			
			if(refund<0) $('.txtRefund').text('Deficit');
			else $('.txtRefund').text('Refund');
			
			$('#'+td+' button').text('Recompute Tax Due');			
			computeLastPay();
		}	
	}
	
	function requestRemove(id){
		if(confirm('Are you sure you want to remove this payslip?')){
			displaypleasewait();
			$.post('<?= $this->config->base_url().'timecard/computelastpay/' ?>', {submitType:'removePayslip', payslipID:id},function(){
				location.reload();
			})
		}
	}
	
	function savecomputation(){
		var checker = true;
		var newAddOn = {};
		var newDeduction = {};
		$('input[name="addOnName\[\]"]').each(function(e, t){
			fval = $(this).val();
			$('input[name="addOnAmount\[\]"]').each(function(a, z){
				sval = $(z).val();
				if(a==e){
					if((fval=='' && sval!='') || (fval!='' && sval==''))
						checker = false;
					else if(fval!='' && sval!='')
						newAddOn[fval] = sval;
				}
			});
		});
		
		$('input[name="deductName\[\]"]').each(function(e, t){
			ffval = $(this).val();
			$('input[name="deductAmount\[\]"]').each(function(a, z){
				ssval = $(z).val();
				if(a==e){
					if((ffval=='' && ssval!='') || (ffval!='' && ssval==''))
						checker = false;
					else if(ffval!='' && ssval!='')
						newDeduction[ffval] = ssval;
				}
			});
		});
		
		if(checker==false) alert('Please check add ons and deductions details.');
		else if(confirm('Are you sure you want to save this last pay computation?')){
			displaypleasewait(); 
			$.post('<?= $this->config->base_url().'timecard/computelastpay/' ?>',{
				submitType:'savecomputation',
				empID_fk:'<?= $staffInfo->empID ?>',
				dateFrom:'<?= $periodFrom ?>',
				dateTo:'<?= $periodTo ?>',
				monthlyRate:'<?= $salary ?>',
				taxFromPrevious:$('input[name="taxFromPrevious"]').val(),
				taxFromCurrent:'<?= $totalTaxable ?>',
				taxNetTaxable:$('#val_netTaxableIncome').text(),
				taxWithheld:'<?= $totalTaxWithheld ?>',
				taxDue:$('#val_taxDue').text(),
				taxRefund:$('#val_taxRefund').text(),
				add13th:'<?= $total13th ?>',
				addLeave:'<?= $staffInfo->leaveCredits ?>',
				addUnpaid:$('input[name="unpaidSal"]').val(),
				addTotal:$('#totalAddOn').text(),
				deductMaxicare:$('input[name="maxicare"]').val(),
				deductArrears:$('input[name="paymentArrears"]').val(),
				deductResti:$('input[name="restitution"]').val(),
				deductTotal:$('#totalDeductions').text(),
				netLastPay:$('#netLastPay').text(),
				addOns:newAddOn,
				addDeductions:newDeduction
			},function(id){
				window.location.href='<?= $this->config->base_url() ?>timecard/computelastpay/?payID='+id;
			});
		}
	}
</script>