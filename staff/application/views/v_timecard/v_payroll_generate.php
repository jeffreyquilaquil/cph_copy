<h3>Generate Payroll</h3>
<hr/>
<i class="colorgray fs11px"><b>Note: </b>
<br/>Semi-Monthly is from <b>26th</b> day of previous month to <b>10th</b> day of current month
<br/>Monthly is from <b>11th</b> to <b>25th</b> day of current month</i>
<form action="" method="POST" onSubmit="displaypleasewait();">
<?php
	$optiontype = array('semi'=>'Semi-Monthly', 'monthly'=>'Monthly');
	echo  ' <b>Type</b> '.$this->textM->formfield('selectoption', 'computationtype', '', 'padding5px', '', '', $optiontype);
	echo ' <b>From</b> '.$this->textM->formfield('text', 'payPeriodStart', date('F 26, Y', strtotime('-1 month')), 'padding5px datepick', '', 'required');
	echo ' <b>To</b> '.$this->textM->formfield('text', 'payPeriodEnd', date('F 10, Y'), 'padding5px datepick', '', 'required');
	echo $this->textM->formfield('submit', '', 'Generate Payroll', 'btnclass btngreen');
	
	echo $this->textM->formfield('hidden', 'submitType', 'generatepayroll', 'btnclass btngreen');	
?>


<!---------- DISPLAY ---------------->
<div id="divEmployees">
	<table class="tableInfo">
		<tr class="trhead">
			<td>Name</td>
			<td>Position</td>
			<td>Department</td>
		</tr>
<?php
	foreach($dataStaffs AS $s){
		echo '<tr>';
			echo '<td>'.$this->textM->formfield('checkbox', 'employee[]', $s->empID, 'checkMe').' '.$s->name.'</td>';
			echo '<td>'.$s->title.'</td>';
			echo '<td>'.$s->dept.'</td>';
		echo '</tr>';
	}
?>
	</table>
</div>

</form>
<?php
	if(isset($_GET['id'])){
		/* echo '<script type="text/javascript">';			
			echo '$.post("'.$this->config->item('career_uri').'",{submitType:"showEmp", empIDs:parent.$("#'.$_GET['id'].'").val()},function(data){
				$("#divEmployees").html("<b>Please select employee:</b>"+data);
			})';
		echo '</script>'; */
	}
?>

<script type="text/javascript">
	$(function(){		
		$('select[name="computationtype"]').change(function(){
			if($(this).val()=='monthly'){
				$('input[name="payPeriodStart"]').val('<?= date('F 11, Y') ?>');
				$('input[name="payPeriodEnd"]').val('<?= date('F 26, Y') ?>');
			}else{
				$('input[name="payPeriodStart"]').val('<?= date('F 26, Y', strtotime('-1 month')) ?>');
				$('input[name="payPeriodEnd"]').val('<?= date('F 10, Y') ?>');
			}
		});
	});
</script>