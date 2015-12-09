<?php
	echo '<h3>';
		if($pageType=='addItem') echo 'Add New Payroll Settings';
		else{			
			if($pageType=='empUpdate'){
				if(isset($row->fname)){
					echo $row->fname.'\'s Payroll Settings Detail &nbsp;&nbsp;<a href="'.$this->config->base_url().'timecard/'.$visitID.'/mypayrollsetting/"><button><< Back to All Payroll Settings</button></a>';
				}else echo 'Batch Adding Payroll Item';				
			}else echo 'Payroll Settings Detail';
		}
		
		if($this->access->accessFullFinance==true && !isset($updatedText)) echo ' <button id="btnUpdate">Update</button>';
	echo '</h3>';		
	echo '<hr/>';
		
	if(isset($_GET['empIDs'])){
		if(isset($dataStaffs) AND count($dataStaffs)>0){
			echo '<b>For the following staffs:</b><br/>';
			$sname = '';
			foreach($dataStaffs AS $s)
				$sname .= $s->name.', ';
			
			echo '<i>'.rtrim($sname, ', ').'</i><br/><br/>';
		}
	} 

	
	if(isset($updatedText)){
		echo '<p class="errortext">'.$updatedText.'</p>'; 
		exit;
	}
	
	if(isset($_GET['type'])) $dataItemInfo->mainItem = $_GET['type'];
	
	$arrPeriod = $this->textM->constantArr('payPeriod');
	$arrPayGoingTo = $this->textM->constantArr('payGoingTo');
	$arrPayCategory = $this->textM->constantArr('payCategory');
	$arrStatus = array('1'=>'Active', '0'=>'Inactive');
	$arrMainItem = array('0'=>'Additional Item', '1'=>'Main Item (available for all payslips)');
			
	$amountOptions = $this->textM->constantArr('payAmountOptions');
	foreach($amountOptions AS $k=>$am)
		$arrPayAmountOptions[$k] = (($k!='specific amount')?'computed base on ':'').$am;
						
	echo '<form id="formUpdate" action="" method="POST" onSubmit="displaypleasewait();">';
	echo '<table class="tableInfo">';			
		echo '<tr>';
			echo '<td width="25%">Item Name</td>';
			echo '<td>';
				if($pageType=='empUpdate') echo '<b>'.$dataItemInfo->payName.'</b>';
				else echo $this->textM->formfield('text', 'payName', $dataItemInfo->payName, 'forminput', '', 'required disabled');					
			echo '</td>';
		echo '</tr>';
		
		echo '<tr>';
			echo '<td width="25%">Item Group</td>';
			echo '<td>';
				if($pageType=='empUpdate') echo $arrMainItem[$dataItemInfo->mainItem];
				else echo $this->textM->formfield('selectoption', 'mainItem', $dataItemInfo->mainItem, 'forminput', '', 'required disabled', $arrMainItem); 				
			echo '</td>';
		echo '</tr>';
		
		echo '<tr>';
			echo '<td>Item Category</td>';
			echo '<td>';
				if($pageType=='empUpdate') echo $arrPayCategory[$dataItemInfo->payCategory];
				else echo $this->textM->formfield('selectoption', 'payCategory', $dataItemInfo->payCategory, 'forminput', '', 'required disabled', $arrPayCategory);
			echo '</td>';				
		echo '</tr>';
		
		echo '<tr>';
			echo '<td>Item Type</td>';
			echo '<td>';
				if($pageType=='empUpdate') echo $dataItemInfo->payType;
				else echo $this->textM->formfield('selectoption', 'payType', $dataItemInfo->payType, 'forminput', 'Please select option', 'required disabled', array('credit'=>'credit', 'debit'=>'debit'));
		echo '</td>';
		
		echo '<tr>';
			echo '<td>';
				echo (($dataItemInfo->payType=='credit')?'Added':'Deducted').' to';
			echo '</td>';
			echo '<td>';
				if($pageType=='empUpdate') echo $arrPayGoingTo[$dataItemInfo->payCDto];
				else echo $this->textM->formfield('selectoption', 'payCDto', $dataItemInfo->payCDto, 'forminput', 'Please select option', 'required disabled', $arrPayGoingTo);
			echo '</td>';
		echo '</tr>';
		
		echo '<tr>';
			echo '<td>Item Amount</td>';
			echo '<td>';
				echo $this->textM->formfield('selectoption', 'payAmount', ((is_numeric(str_replace(',','',$dataItemInfo->payAmount)))?'specific amount':$dataItemInfo->payAmount), 'forminput', 'Please select option', 'required disabled', $arrPayAmountOptions);
				echo '<div id="divPayAmount" class="'.((is_numeric(str_replace(',','',$dataItemInfo->payAmount)))?'':'hidden').'" style="margin-top:5px;">Php '.$this->textM->formfield('text', 'inputPayAmount', $this->textM->convertNumFormat($dataItemInfo->payAmount), 'forminput', '', 'required disabled style="width:90%"').'</div>';
			
				echo '<div id="divPayPercent" class="'.(($dataItemInfo->payAmount!='hourly')?'hidden':'').'" style="margin-top:5px;">';
					if($pageType=='addItem' || $pageType=='updateItem') echo $this->textM->formfield('number', 'payPercent', ((!empty($dataItemInfo->payPercent))?$dataItemInfo->payPercent:'0'), 'forminput', '', 'disabled style="width:100px"').' %';
					else echo 'number of hours x '.((!empty($dataItemInfo->payPercent))?$dataItemInfo->payPercent:0). '%';
					
				echo '</div>';
				if($pageType=='addItem' || $pageType=='updateItem'){
					
				}				
			echo '</td>';
		echo '</tr>';
		
		echo '<tr>';
			echo '<td>Pay Period</td>';
			echo '<td>';
				echo $this->textM->formfield('selectoption', 'payPeriod', $dataItemInfo->payPeriod, 'forminput', 'Please select option', 'required disabled', $arrPeriod);
				
				echo '<div id="divOnce" '.(($dataItemInfo->payPeriod=='once')?'':'class="hidden"').'>';
						$payOnce = '';
						if(isset($_GET['once'])) $payOnce = date('F d, Y', strtotime($_GET['once']));
						if(!empty($dataItemInfo->payStart) && $dataItemInfo->payStart!='0000-00-00') $payOnce = date('F d, Y', strtotime($dataItemInfo->payStart));
				
						echo ' On '.$this->textM->formfield('text', 'payStartOnce', $payOnce, 'forminput datepick', '', 'disabled style="width:90%; margin-top:5px;" '.(($dataItemInfo->payPeriod=='once' && $pageType=='empUpdate')?'required':''));
				echo '</div>';
												
				echo '<div id="divNotOnce" '.(($dataItemInfo->payPeriod!='once' && $dataItemInfo->payStart!='0000-00-00' && $dataItemInfo->payEnd!='0000-00-00')?'':'class="hidden"').' style="padding-top:5px;">';
						echo 'From '.$this->textM->formfield('text', 'payStart', (($dataItemInfo->payStart!='0000-00-00')?date('F d, Y', strtotime($dataItemInfo->payStart)):''), 'forminput datepick', '', 'disabled style="width:40%"');
						echo ' To '.$this->textM->formfield('text', 'payEnd', (($dataItemInfo->payEnd!='0000-00-00')?date('F d, Y', strtotime($dataItemInfo->payEnd)):''), 'forminput datepick', '', 'disabled style="width:40%"');
						echo '<br/><i class="fs11px colorgray">Leave dates blank if undetermined</i>';
				echo '</div>';	
				
			echo '</td>';
		echo '</tr>';
		
		if($pageType=='empUpdate' && $dataItemInfo->payAmount=='hourly'){
			echo '<tr>';
				echo '<td>Number of Hours</td>';
				echo '<td>'.$this->textM->formfield('number', 'payAmountHourly', '0', 'forminput', '', 'style="border:2px solid #660808;"').'</td>';
			echo '</tr>';
		}
		
		if($pageType!='addItem' && !isset($_GET['add'])){
			echo '<tr>';
				echo '<td>Status</td>';
				echo '<td>';
					echo $this->textM->formfield('selectoption', 'status', $dataItemInfo->status, 'forminput', '', 'required disabled', $arrStatus);
				echo '</td>';
			echo '</tr>';
		}
		
		
		echo '<tr id="trsubmission" class="hidden">';
			echo '<td><br/></td><td>';
				if($pageType=='addItem'){
					echo $this->textM->formfield('submit', '', 'Add Item', 'btnclass btngreen');
					echo $this->textM->formfield('hidden', 'submitType', 'addItem');
				}else{
					if(isset($_GET['add'])){
						echo $this->textM->formfield('submit', '', 'Add Employee Item', 'btnclass btngreen');
						echo $this->textM->formfield('hidden', 'submitType', 'empAddItem');
						
						echo $this->textM->formfield('hidden', 'payID', $dataItemInfo->payID);
					}else{
						echo $this->textM->formfield('submit', '', 'Update', 'btnclass btngreen');
						echo $this->textM->formfield('hidden', 'submitType', 'updateItem');
					
						if(isset($dataItemInfo->payID)) echo $this->textM->formfield('hidden', 'payID', $dataItemInfo->payID);
						else{
							echo $this->textM->formfield('hidden', 'payID', $dataItemInfo->payID_fk);
							echo $this->textM->formfield('hidden', 'payStaffID', $dataItemInfo->payStaffID);
						}
					}					
				}
			
			echo '</td>';
		echo '</tr>';
	echo '</table>';
	echo '</form>';
		
	if($pageType=='addItem' || $pageType=='empUpdate'){
		echo '<script>';
			echo '$(function(){
				$("#divOnce").addClass("hidden");
				$("#divNotOnce").addClass("hidden");
				removeUpdates(); 
			});';
		echo '</script>';
	}	
?>
<script type="text/javascript">
	$(function(){
		$('select[name="payAmount"]').change(function(){
			$('#divPayAmount').addClass('hidden');
			$('#divPayPercent').addClass('hidden');
			
			if($(this).val()=='specific amount') $('#divPayAmount').removeClass('hidden');
			else if($(this).val()=='hourly') $('#divPayPercent').removeClass('hidden');
		});
		
		$('select[name="payPeriod"]').change(function(){
			$('input[name="payStartOnce"]').val('');
			$('input[name="payStart"]').val('');
			$('input[name="payEnd"]').val('');
			
			$('input[name="payStartOnce"]').removeAttr('required');
			$('input[name="payStart"]').removeAttr('required');
			$('input[name="payEnd"]').removeAttr('required');
			
			if($(this).val()==''){
				$('#divOnce').addClass('hidden');
				$('#divNotOnce').addClass('hidden');
			}else if($(this).val()=='once'){
				$('#divOnce').removeClass('hidden');
				$('#divNotOnce').addClass('hidden'); 
				pageType = '<?= ((isset($_GET['pageType']))?$_GET['pageType']:'') ?>';
				if(pageType!='addItem' && pageType!='updateItem')
					$('#divOnce input[name="payStartOnce"]').attr('required', 'required');
			}else{
				$('#divOnce').addClass('hidden');
				$('#divNotOnce').removeClass('hidden');
			}
		});
		
		$('select[name="payCategory"]').change(function(){
			if($(this).val()==6)
				$('select[name="payType"]').val("debit");
			else
				$('select[name="payType"]').val();
		});
				
		$('#btnUpdate').click(function(){
			removeUpdates();
		});		
	});
	
	function removeUpdates(){
		$('#btnUpdate').hide();
		$('#trsubmission').removeClass('hidden');
		$('.forminput').removeAttr('disabled');
		
		payPeriod = $('select[name="payPeriod"]').val();
		if(payPeriod!=''){
			if(payPeriod=='once') $('#divOnce').removeClass('hidden');
			else $('#divNotOnce').removeClass('hidden');
		}
	}	
</script>