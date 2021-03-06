<?php
	$this->load->view('includes/header_searchemployee');
	
	if(!isset($_GET['show'])) $show = 'active';
	else $show = $_GET['show'];
?>

<h2><?= $pagePayTitle ?></h2>
<hr/>

<?php if($pagepayroll=='managepayroll'){ ?>
<div id="manage" class="positionrelative"><br/>
	<div style="position:absolute; left:140px; z-index:10;">
		<b style="color:#333333">of</b>&nbsp;&nbsp;
		<select id="selectShow" class="padding5px">
			<option value="all" <?= (($show=='all')?'selected="selected"':'') ?>>All employees</option>
			<option value="active" <?= (($show=='active')?'selected="selected"':'') ?>>Active employees</option>
			<option value="pending" <?= (($show=='pending')?'selected="selected"':'') ?>>Pending separation</option>
			<option value="suspended" <?= (($show=='suspended')?'selected="selected"':'') ?>>Suspended employees</option>
			<option value="separated" <?= (($show=='separated')?'selected="selected"':'') ?>>Separated employees</option>
			<option value="floating" <?= (($show=='floating')?'selected="selected"':'') ?>>Floating employees</option>
		</select>
	</div>
	<div id="silay" style="display:none; width:100%">
		<form id="formManage" action="<?= $this->config->base_url() ?>timecard/payrollmanagement/" method="POST" style="margin:10px 0px;">
		<?php
			$today = date('Y-m-d');
			$day = date('d');
			$select = 'semi';
			if( $day > 10 AND $day < 25 ){
				$select = 'monthly';
			}
			
			echo $this->textM->formfield('selectoption', 'type', '', 'padding5px', '', 'required', $this->textM->constantArr('managePayOptions'));
			echo  ' <b>Type</b> '.$this->textM->formfield('selectoption', 'computationtype', $select, 'padding5px', '', '', array('semi'=>'Semi-Monthly', 'monthly'=>'Monthly'));
			
			echo ' <b>for the period</b> ';
			
			///FOR SEMI
			$semiArr = $this->payrollM->getMonthlyPeriod('semi');
			echo '<select id="semiSelect" class="padding5px" name="periodDate">';
				foreach($semiArr AS $k=>$semi){
					
					echo '<option value="'.$semi['start'].'|'.$semi['end'].'" ';
					if( strtotime($today) >= strtotime($semi['start']) AND strtotime($today) <= strtotime($semi['end']) ){
						echo ' selected="selected" ';
					}
					//(($k==3)?'selected="selected"':'');
					echo '>'.date('M d', strtotime($semi['start'])).' - '.date('M d, Y', strtotime($semi['end'])).'</option>';
				}
			echo '</select>';
			
			///FOR MONTHLY
			$monthlyArr = $this->payrollM->getMonthlyPeriod('monthly');
			echo '<select id="monthlySelect" class="padding5px" name="periodDate">';
				foreach($monthlyArr AS $k=>$monthly){
					echo '<option value="'.$monthly['start'].'|'.$monthly['end'].'" ';

					if( strtotime($today) >= strtotime($monthly['start']) AND strtotime($today) <= strtotime($monthly['end']) ){
						echo ' selected="selected" ';
					}
					echo '>'.date('M d', strtotime($monthly['start'])).' - '.date('M d, Y', strtotime($monthly['end'])).'</option>';
				}
			echo '</select>';		
			
			echo ' '.$this->textM->formfield('submit', '', 'Let\'s Go!', 'btnclass btngreen btnletgo');
			echo '&nbsp;&nbsp;<a class="inline" href="#inline_content"><img src="'.$this->config->base_url().'css/images/icon-question1.png" width="16px"/></a>';
			
			echo $this->textM->formfield('textarea', 'empIDs', '', 'hidden');
		?>
		</form>
		
		<div class='selectionDiv' style="padding-top:5px;">
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
			<th><br/></th>
		</tr>
		</thead>
	<?php
		
		foreach($dataStaffs AS $staff){
			echo '<tr>';
				echo '<td>'.$this->textM->formfield('checkbox', 'checkMe[]', $staff->empID, 'classCheckMe').'</td>';
				echo '<td><a href="'.$this->config->base_url().'timecard/'.$staff->empID.'/timelogs/" target="_blank"><b>'.$staff->lname.', '.$staff->fname.'</b></a></td>';
				echo '<td>'.$staff->title.'</td>';
				echo '<td>'.$staff->dept.'</td>';
				echo '<td>';
					echo '<ul class="dropmenu">';
						echo '<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" class="cpointer"/>';
							echo '<ul class="dropleft">';
								echo '<li><a href="'.$this->config->base_url().'timecard/computelastpay/?empID='.$staff->empID.'" class="iframe">Compute Last Pay</a></li>';
								echo '<li><a href="'.$this->config->base_url().'timecard/generate13thmonth/?empIDs='.$staff->empID.'" class="iframe">Generate 13th Month</a></li>';
								echo '<li><a href="'.$this->config->base_url().'timecard/computelastpay/?is_active=yes&show=pdf&which_pdf=bir&empID='.$staff->empID.'" target="_blank">BIR 2316</a></li>';
								echo '<li><a href="'.$this->config->base_url().'timecard/taxsummary/?empIDs='.$staff->empID.'">Tax Summary</a></li>';
							echo '</ul>';
						echo '</li>';
					echo '</ul>';
				echo '</td>';
			echo '</tr>';
		}
	?>
	</table>
</div>
<script>
	$(function(){
		<?php if($select == 'monthly'): ?>
			$('#semiSelect').hide();
			$('#semiSelect').removeAttr('name');
		<?php else: ?>
			$('#monthlySelect').hide();
			$('#monthlySelect').removeAttr('name');
		<?php endif; ?>
	});	
</script>
<?php } ?>
<!--------- PREVIOUS PAYROLLS ----------->
<?php if($pagepayroll=='previouspayroll'){ ?>
<div id="prevpay">	
<?php
	if(count($dataPayrolls)==0){
		echo '<p>No payrolls generated yet.</p>';
	}else{
?>
	<table class="tableInfo">
		<tr class="trlabel">
			<td width="20%">Period Start</td>
			<td>Period End</td>
			<td>Pay Type</td>
			<td>Pay Date</td>
			<td align="center">Number Generated</td>
			<td>Status</td>
			<td width="10%"><br/></td>
		</tr>
	<?php
		foreach($dataPayrolls AS $pay){
			echo '<tr>';
				echo '<td>'.date('d-M-Y', strtotime($pay->payPeriodStart)).'</td>';
				echo '<td>'.date('d-M-Y', strtotime($pay->payPeriodEnd)).'</td>';
				echo '<td>'.(($pay->payType=='semi')?'Semi-Monthly':'Monthly').'</td>';
				echo '<td>'.date('d-M-Y', strtotime($pay->payDate)).'</td>';
				echo '<td align="center">'.$pay->numGenerated.'</td>';
				echo '<td>'.$payrollStatusArr[$pay->status].'</td>';
				echo '<td>';
					echo '<ul class="dropmenu" style="margin:0px;">
							<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" class="cpointer"/>
								<ul class="dropleft">';
								echo '<li><a href="'.$this->config->base_url().'timecard/managepayrolldetail/'.$pay->payrollsID.'/">View Details</a></li>';
								echo '<li><a href="'.$this->config->base_url().'timecard/payrolldistributionreport/'.$pay->payrollsID.'/">Download Payroll Distribution Report</a></li>';
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
<?php } ?>

<!--------- PAYROLL ITEMS ----------->
<?php if($pagepayroll=='payrollitems'){ ?>
<div id="items">
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
<?php } ?>

<!--------- PAYROLL SETTINGS ----------->
<?php if($pagepayroll=='payrollsettings' && count($dataPaySettings)>0){ ?>
<div id="settings">
<table class="tableInfo">
<?php
	foreach($dataPaySettings AS $sett){
		echo '<tr>';
			echo '<td>'.$sett->settingNote.'</td>';
			echo '<td>Php '.$this->textM->convertNumFormat($sett->settingVal).'</td>';
		echo '</tr>';
	}
?>
</table>
</div>
<?php } ?>

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
			countChecked();
		});
	
		$('.classCheckMe').change(function(){
			countChecked();
		});

		function countChecked(){
			var countCheck = 0;
			$('.classCheckMe').each(function(){
				if( $(this).is(':checked') ){
					countCheck++;
				}
			});
			$('.selectionLabel').remove();
			$('.selectionDiv').append('<div class="selectionLabel"><strong><i>'+countCheck+' Selected<i></strong></div>');
		}

		$('.toolbar #deselectAll').click(function(){
			$('.classCheckMe').prop('checked', false);
			countChecked();
		});
		
		$('.toolbar #formManage').submit(function(){
			empIDs = checkIfSelected();
			var payDate = $('.toolbar select[name="periodDate"] option:selected').text();
			console.log(payDate);
			var con = confirm('Do you want to generate payroll for '+ payDate +'?');
			if( con == true ){
				if(empIDs==false){
					alert('Please select employee first.');
					return false;
				}else{
					$('textarea[name="empIDs"]').text(empIDs);
					displaypleasewait();
					return true;
				}
			} else {
				return false;
			}
	
		});
		
		
		$('select[name="computationtype"]').change(function(){
			$('.toolbar #semiSelect').removeAttr('name');
			$('.toolbar #monthlySelect').removeAttr('name');
			console.log($(this).val());
			if($(this).val()=='monthly'){
				/*$('.toolbar #semiSelect').addClass('hidden');
				$('.toolbar #monthlySelect').removeClass('hidden');*/
				$('.toolbar #monthlySelect').show();
				$('.toolbar #semiSelect').removeAttr('name');
				$('.toolbar #semiSelect').hide();
				
				$('.toolbar #monthlySelect').attr('name', 'periodDate');
			}else{
				/*$('.toolbar #semiSelect').removeClass('hidden');
				$('.toolbar #monthlySelect').addClass('hidden');*/
				$('.toolbar #monthlySelect').hide();
				$('.toolbar #monthlySelect').removeAttr('name');

				$('.toolbar #semiSelect').show();				
				$('.toolbar #semiSelect').attr('name', 'periodDate');
			}
		});
		
		$('select[name="type"]').change(function(){	
			var myval = $(this).val();
			if(myval=='addpayslipitem' || myval=='generate13thmonth' || myval=='generatetaxsummary'){
				empIDs = checkIfSelected();
				if(empIDs==false){
					$(this).val('reviewattendance');
					alert('Please select employee first.');
				}else{
					if(myval=='generate13thmonth')
						myhref = "<?= $this->config->base_url().'timecard/generate13thmonth/?empIDs=' ?>"+empIDs;
					else if(myval=='addpayslipitem')
						myhref = "<?= $this->config->base_url().'timecard/mypayrollsetting/?empIDs=' ?>"+empIDs;
					else
						myhref = "<?= $this->config->base_url().'timecard/generatetaxsummary/?empIDs=' ?>"+empIDs;
					
					window.parent.jQuery.colorbox({href:myhref, iframe:true, width:"990px", height:"600px"});
					$(this).val('reviewattendance');
				}
			}
		});
		
		$('#selectShow').change(function(){
			displaypleasewait();
			window.location.href="<?= $this->config->base_url().'timecard/managepayroll/?show=' ?>"+$(this).val();
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

