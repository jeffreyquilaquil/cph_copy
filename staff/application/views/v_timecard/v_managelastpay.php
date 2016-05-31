<div style="float:right;">
	<button class="btnclass">Generate Alphalist</button>
</div>
<h2>Manage Last Pay</h2>
<hr/>

	<style>
		ul li { list-style-type: none; }
		li { display: inline; }
		label { font-weight: bold;}
		.error{ color: red;}
	</style>
<div style="float: right; text-align: center;">
	<?php echo $error_data; ?>
	<form action="" method="post">
		<ul>
			<li><label for="dateFrom">Pull records from: </label><input type="text" class="datepick" id="dateFrom" name="dateFrom" /></li>
			<li><label for="dateTo">to: </label><input type="text" class="datepick" name="dateTo" id="dateTo" /></li>
			<li><button type="submit" class="btngreen" name="date_range">Let's Go</button></li>
		</ul>
	</form>
</div>
<table class="datatable display stripe hover">
<thead>
	<tr>
		<th>Employee ID</th>
		<th>Employee Name</th>
		<th>Start Date</th>
		<th>End Date</th>
		<th>Tax Refund</th>
		<th>Add Ons Total</th>
		<th>Deductions Total</th>		
		<th>NET Last Pay</th>
		<th>Status</th>
		<th><br/></th>
	</tr>
</thead>
<?php
	foreach($dataQuery AS $data){
		echo '<tr>';
			echo '<td>'.$data->idNum.'</td>';
			echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$data->username.'/">'.$data->lname.', '.$data->fname.'</a></td>';
			echo '<td>'.date('d-M-Y', strtotime($data->startDate)).'</td>';
			echo '<td>'.date('d-M-Y', strtotime($data->endDate)).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->taxRefund).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->addTotal).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->deductTotal).'</td>';			
			echo '<td><b>PHP '.$this->textM->convertNumFormat($data->netLastPay).'</b></td>'; 
			
			echo '<td>'. $this->textM->formfield('selectoption', 'status', $data->status, '', '', 'data-lastpayid="'.$data->lastpayID.'"', $status_labels) .'</td>';
			echo '<td align="right">
					<ul class="dropmenu">
						<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" class="cpointer"/>
							<ul class="dropleft">
								<li><a href="'.$this->config->base_url().'timecard/computelastpay/?payID='.$data->lastpayID.'" target="_blank">View Details</a></li>
								<li><a href="'.$this->config->base_url().'timecard/computelastpay/?empID='.$data->empID_fk.'" class="iframe">Re-compute Last Pay</a></li>
								<li><a href="'.$this->config->base_url().'timecard/computelastpay/?payID='.$data->lastpayID.'&show=pdf&which_pdf=view" target="_blank">View PDF File</a></li>';
			echo 				'<li><a href="'.$this->config->base_url().'timecard/computelastpay/?empID='.$data->empID_fk.'&show=pdf&which_pdf=release&payID='.$data->lastpayID.'" target="_blank">Release Waiver and Quit Claim</a></li>
								<li><a href="'.$this->config->base_url().'timecard/computelastpay/?empID='.$data->empID_fk.'&show=pdf&which_pdf=coe" target="_blank">Certificate of Employment</a></li>
								<li><a href="'.$this->config->base_url().'timecard/computelastpay/?payID='.$data->lastpayID.'&show=pdf&which_pdf=bir&empID='.$data->empID_fk.'" target="_blank">BIR 2316</a></li>';
			echo '</ul>
						</li>
					</ul>				
				</td>';
		echo '</tr>';
	}
?>
</table>
<script>
 $(function(){
 	$('.btnclass').click(function(){
 		window.location = '<?php echo $this->config->base_url(); ?>timecard/alphalist/?which=end';		
 	});

 	$('select[name="status"]').change(function(){
 		$.ajax({
 			type: 'POST',
 			url: '<?php echo $this->config->base_url()."timecard/managelastpay/"; ?>',
 			data: { 'status': $(this).val(), 'id': $(this).data('lastpayid') },
 			success: function(data){
 				console.log(data);
 				alert('Status has updated');
 				window.location.reload();
 			}
 		});
 	});
 });
 </script>

