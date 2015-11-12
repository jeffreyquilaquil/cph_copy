<h2>Manage Payroll</h2>
<hr/>

<ul class="tabs">
	<li class="tab-link current" data-tab="manage">Manage</li>
	<li class="tab-link" data-tab="prevpay">Previous Payrolls</li>
	<li class="tab-link" data-tab="items">Payroll Items</li>
	<li class="tab-link" data-tab="settings">Payroll Settings</li>
</ul>

<div id="manage" class="tab-content current">	
<br/>

<div id="silay" style="display:none; width:100%">
	<form id="formManage" action="<?= $this->config->base_url() ?>timecard/payrollmanagement/" method="POST" style="margin:10px 0px;">
	<?php
		echo $this->textM->formfield('selectoption', 'type', 'generatepayslip', 'padding5px', '', '', $this->textM->constantArr('managePayOptions'));
		echo  ' <b>Type</b> '.$this->textM->formfield('selectoption', 'computationtype', '', 'padding5px', '', '', $this->textM->constantArr('payrollType'));
		
		/* echo ' <b>for the period</b> ';
		echo $this->textM->formfield('text', 'start', date('F 26, Y', strtotime('-1 month')), 'datepick padding5px', '', 'required');
		echo ' <b>to</b> ';
		echo $this->textM->formfield('text', 'end', date('F 11, Y'), 'datepick padding5px', '', 'required'); */
		
		echo ' <b>for the period</b> ';
		echo $this->textM->formfield('text', 'start', date('F d, Y', strtotime('2015-09-26')), 'datepick padding5px', '', 'required');
		echo ' <b>to</b> ';
		echo $this->textM->formfield('text', 'end', date('F d, Y', strtotime('2015-10-10')), 'datepick padding5px', '', 'required');
		
		
		echo ' '.$this->textM->formfield('submit', '', 'Let\'s Go!', 'btnclass btngreen');
		echo '<a class="inline" href="#inline_content"><img src="'.$this->config->base_url().'css/images/icon-question1.png" width="20px"/></a>';
		
		echo $this->textM->formfield('textarea', 'empIDs', '', 'hidden');
	?>
		</form>
		<div style="padding-top:5px;">
			<a class="cpointer" id="selectAll">Select All</a> | <a class="cpointer" id="deselectAll">Deselect All</a>
		</div>
		
		
</div>

<div style="display:none">
	<div id="inline_content" class="colorblack">
		<h3>Payroll Info</h3>
		<hr/>
		<ul>
			<li><i>Semi-Monthly</i> is from <b>26th</b> day of previous month to <b>10th</b> day of current month</li>
			<li><i>Monthly</i> is from <b>11th</b> to <b>25th</b> day of current month</li>
		</ul>		
	</div>
</div>

<table id="dtable" class="dTable display stripe hover">
<thead>
	<tr bgcolor="#fff">
		<th></th>
		<th>Name</th>
		<th>Title</th>
		<th>Department</th>
	</tr>
	</thead>
<?php
	foreach($dataStaffs AS $staff){
		echo '<tr>';
			echo '<td>'.$this->textM->formfield('checkbox', 'checkMe[]', $staff->empID, 'classCheckMe').'</td>';
			echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$staff->username.'/" target="_blank"><b>'.$staff->lname.', '.$staff->fname.'</b></a></td>';
			echo '<td>'.$staff->title.'</td>';
			echo '<td>'.$staff->dept.'</td>';
		echo '</tr>';
	}
?>
</table>
</div>

<!--------- PREVIOUS PAYROLLS ----------->
<div id="prevpay" class="tab-content">	
	<table class="tableInfo">
		<tr class="trlabel">
			<td width="20%">Period Start</td>
			<td>Period End</td>
			<td>Number Generated</td>
			<td width="10%"><br/></td>
		</tr>
	<?php
		foreach($dataPayrolls AS $pay){
			echo '<tr>';
				echo '<td>'.date('F d, Y', strtotime($pay->payPeriodStart)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($pay->payPeriodEnd)).'</td>';
				echo '<td>'.$pay->numGenerated.'</td>';
				echo '<td>';
					echo '<ul class="dropmenu" style="margin:0px;">
							<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" class="cpointer"/>
								<ul class="dropleft">
									<li><a href="'.$this->config->base_url().'timecard/managepayrolldetail/'.$pay->payrollsID.'/">View Details</a></li>
								</ul>
							</li>
						</ul>';
				echo '</td>';
			echo '</tr>';
		}
	?>
	</table>
<?php //} ?>
</div>

<!--------- PAYROLL ITEMS ----------->
<div id="items" class="tab-content">
	<table class="tableInfo">
		<tr class="trlabel">
			<td>Item Type</td>
			<td>Item Name</td>
			<td>Item Default Value</td>
		</tr>
	<?php
		foreach($dataItems AS $item){
			echo '<tr>';	
				echo '<td>'.strtolower($payrollItemType[$item->itemType]).'</td>';
				echo '<td>'.$item->itemName.'</td>';
				echo '<td>'.$this->textM->convertNumFormat($item->itemValue).'</td>';
			echo '</tr>';
		}
	?>
	</table>
</div>

<!--------- PAYROLL SETTINGS ----------->
<div id="settings" class="tab-content">
<table class="tableInfo">
	<tr>
		<td width="25%">Cebu Minimum Wage</td>
		<td><span id="minwage">Php <?= $this->textM->convertNumFormat($dataMinWage) ?></span>
			<form id="formeditwage" class="hidden" action="" method="POST">
				<?php
					echo $this->textM->formfield('number', 'settingVal', $dataMinWage, 'forminput', '', 'step="any" required');
					echo $this->textM->formfield('submit', '', 'Update', 'btnclass');
					
					echo $this->textM->formfield('hidden', 'submitType', 'updateMinWage');
				?>
			</form>
		</td>
		<td align="right"><img id="editminwage" src="<?= $this->config->base_url() ?>css/images/icon-options-edit.png" width="25px" class="cpointer"/></td>
	</tr>
</table>
</div>

<script type="text/javascript">
	$(function(){		
		var oTable = $('.dTable').dataTable({
						"dom": 'l<"searchbox" f><"toolbar">tip'
					});
		
		$("div.toolbar").html($('#silay').html());
		$("div.toolbar").css('float', 'left');
		$("div.searchbox").css('float', 'right');
		$("div.dataTables_filter input").css('width', '250px');
		$("div.dataTables_filter input").attr('placeholder', 'Type Employee\'s Name');
		
		$('.toolbar .datepick').datetimepicker({
			format:'F d, Y', timepicker:false
		});
		
		$('#editminwage').click(function(){
			$('#minwage').hide();
			$('#formeditwage').show();
		});
		
		$('.toolbar #selectAll').click(function(){
			$('.classCheckMe').prop('checked', true);
		});
		
		$('.toolbar #deselectAll').click(function(){
			$('.classCheckMe').prop('checked', false);
		});
		
		$('.toolbar #formManage').submit(function(){
			var empIDs = $('textarea[name="empIDs"]').text();
			var cars = [];
			$('.classCheckMe').each(function(){
				if($(this).is(':checked')){
					cars.push($(this).val());
					empIDs = empIDs+$(this).val()+',';
				}
			});
			
			if(cars.length==0){
				alert('Please select employee.');
				return false;
			}else{
				$('textarea[name="empIDs"]').text(empIDs);
				displaypleasewait();
				return true;
			}
		});
		
		
		$('select[name="computationtype"]').change(function(){
			if($(this).val()=='monthly'){
				$('input[name="start"]').val('<?= date('F 11, Y') ?>');
				$('input[name="end"]').val('<?= date('F 25, Y') ?>');
			}else{
				$('input[name="start"]').val('<?= date('F 26, Y', strtotime('-1 month')) ?>');
				$('input[name="end"]').val('<?= date('F 10, Y') ?>');
			}
		});
		
	});	
</script>

