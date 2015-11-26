<?php
	$this->load->view('includes/header_searchemployee');
	
	if(!isset($_GET['show'])) $show = 'active';
	else $show = $_GET['show'];
?>

<h2>Manage Payroll</h2>
<hr/>

<ul class="tabs">
	<li class="tab-link current" data-tab="manage">Manage</li>
	<li class="tab-link" data-tab="prevpay">Previous Payrolls</li>
	<li class="tab-link" data-tab="items">Payroll Items</li>
	<li class="tab-link" data-tab="settings">Payroll Settings</li>
</ul>

<div id="manage" class="tab-content current positionrelative">	
<br/>

<div style="position:absolute; left:140px; z-index:10;">
	<b style="color:#333333">of</b>&nbsp;&nbsp;
	<select id="selectShow" class="padding5px">
		<option value="all" <?= (($show=='all')?'selected="selected"':'') ?>>All employees</option>
		<option value="active" <?= (($show=='active')?'selected="selected"':'') ?>>Active employees</option>
		<option value="pending" <?= (($show=='pending')?'selected="selected"':'') ?>>Pending separation</option>
		<option value="suspended" <?= (($show=='suspended')?'selected="selected"':'') ?>>Suspended employees</option>
		<option value="separated" <?= (($show=='separated')?'selected="selected"':'') ?>>Separated employees</option>
	</select>
</div>
<div id="silay" style="display:none; width:100%">
	<form id="formManage" action="<?= $this->config->base_url() ?>timecard/payrollmanagement/" method="POST" style="margin:10px 0px;">
	<?php
		echo $this->textM->formfield('selectoption', 'type', '', 'padding5px', '', 'required', $this->textM->constantArr('managePayOptions'));
		echo  ' <b>Type</b> '.$this->textM->formfield('selectoption', 'computationtype', '', 'padding5px', '', '', array('semi'=>'Semi-Monthly', 'monthly'=>'Monthly'));
		
		echo ' <b>for the period</b> ';
		echo $this->textM->formfield('text', 'start', date('F 26, Y', strtotime('-1 month')), 'datepick padding5px', '', 'required');
		echo ' <b>to</b> ';
		echo $this->textM->formfield('text', 'end', date('F 11, Y'), 'datepick padding5px', '', 'required');
		
		
		echo ' '.$this->textM->formfield('submit', '', 'Let\'s Go!', 'btnclass btngreen');
		echo '<a class="inline" href="#inline_content"><img src="'.$this->config->base_url().'css/images/icon-question1.png" width="16px"/></a>';
		
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
			echo '<td><a href="'.$this->config->base_url().'timecard/'.$staff->empID.'/timelogs/" target="_blank"><b>'.$staff->lname.', '.$staff->fname.'</b></a></td>';
			echo '<td>'.$staff->title.'</td>';
			echo '<td>'.$staff->dept.'</td>';
		echo '</tr>';
	}
?>
</table>
</div>

<!--------- PREVIOUS PAYROLLS ----------->
<div id="prevpay" class="tab-content">	
<?php
	if(count($dataPayrolls)==0){
		echo '<p>No payrolls generated yet.</p>';
	}else{
?>
	<table class="tableInfo">
		<tr class="trlabel">
			<td width="20%">Period Start</td>
			<td>Period End</td>
			<td>Number Generated</td>
			<td>Status</td>
			<td width="10%"><br/></td>
		</tr>
	<?php
		foreach($dataPayrolls AS $pay){
			echo '<tr>';
				echo '<td>'.date('F d, Y', strtotime($pay->payPeriodStart)).'</td>';
				echo '<td>'.date('F d, Y', strtotime($pay->payPeriodEnd)).'</td>';
				echo '<td>'.$pay->numGenerated.'</td>';
				echo '<td>'.$payrollStatusArr[$pay->status].'</td>';
				echo '<td>';
					echo '<ul class="dropmenu" style="margin:0px;">
							<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" class="cpointer"/>
								<ul class="dropleft">';
								echo '<li><a href="'.$this->config->base_url().'timecard/managepayrolldetail/'.$pay->payrollsID.'/">View Details</a></li>';
							if($pay->status==0)
								echo '<li><a href="javascript:void(0);" onClick="removePayroll('.$pay->payrollsID.')">Remove this payroll</a></li>';						
							echo '</ul>
							</li>
						</ul>';
				echo '</td>';
			echo '</tr>';
		}
	?>
	</table>
<?php } ?>
</div>

<!--------- PAYROLL ITEMS ----------->
<div id="items" class="tab-content">
<?php 
if(count($dataMainItems)==0 && count($dataAddItems)==0){
	echo '<br/><a href="'.$this->config->base_url().'timecard/manangepaymentitem/?pageType=addItem&type=1" class="iframe"><button class="btnclass btngreen">+ Add Payroll Item</button></a>';
}

if(count($dataMainItems)>0){ ?>
	<table class="tableInfo">
		<tr class="trlabel">
			<td colspan=7>Main Items <a href="<?= $this->config->base_url().'timecard/manangepaymentitem/?pageType=addItem&type=1' ?>" class="iframe edit">+ Add Main Item</a></td>
		</tr>
		<?php echo $this->textM->displayPaymentItems($dataMainItems) ?>
	</table>
	<br/>
<?php } if(count($dataAddItems)>0){ ?>
	<table class="tableInfo">
		<tr class="trlabel">
			<td colspan=7>Additional Items <a href="<?= $this->config->base_url().'timecard/manangepaymentitem/?pageType=addItem&type=0' ?>" class="iframe edit">+ Add New Item</a></td>
		</tr>
		<?php echo $this->textM->displayPaymentItems($dataAddItems) ?>
	</table>
<?php } ?>
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
			empIDs = checkIfSelected();
			if(empIDs==false){
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
		
		$('select[name="type"]').change(function(){			
			if($(this).val()=='addpayslipitem'){
				empIDs = checkIfSelected();
				if(empIDs==false){
					$(this).val('reviewattendance');
					alert('Please select employee.');
				}else{
					window.parent.jQuery.colorbox({href:"<?= $this->config->base_url().'timecard/mypayrollsetting/?empIDs=' ?>"+empIDs, iframe:true, width:"990px", height:"600px"});
					$(this).val('reviewattendance');
				}
			}
		});
		
		$('#selectShow').change(function(){
			displaypleasewait();
			window.location.href="<?= $this->config->base_url().'timecard/managetimecard/managepayroll/?show=' ?>"+$(this).val();
		});
		
	});	
	
	function checkIfSelected(){
		var empIDs = $('textarea[name="empIDs"]').text();
		var cars = [];
		$('.classCheckMe').each(function(){
			if($(this).is(':checked')){
				cars.push($(this).val());
				empIDs = empIDs+$(this).val()+',';
			}
		});
		
		if(cars.length==0){
			return false;			
		}else{
			return empIDs;
		}
	}
	
	function removePayroll(id){
		if(confirm('Are you sure you want to remove this payroll?')){
			displaypleasewait();
			$.post('<?= $this->config->base_url().'timecard/managepayroll/' ?>', {submitType:'removePayroll', payrollID:id},
			function(d){
				location.reload();
			})
		}
	}
</script>

