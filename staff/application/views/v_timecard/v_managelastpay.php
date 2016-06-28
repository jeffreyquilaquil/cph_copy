

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
		<th>End Date</th>
		<th>Scheduled Release Date</th>
		<th>Check No.</th>		
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
			
			echo '<td>'.date('d-M-Y', strtotime($data->endDate)).'</td>';
			echo '<td>
					<input type="text" value="'.(( $data->releasedDate == '0000-00-00 00:00:00') ? 'YYYY-MM-DD': date('Y-m-d', strtotime($data->releasedDate)) ).'" class="date_picker scheddate" id="scheddate_'.$data->lastpayID.'" disabled data-id="'.$data->lastpayID.'" />
					<a href="#" class="editField" data-which="scheddate" data-id="'.$data->lastpayID.'">Edit</a>
				</td>';
			echo '<td>
					<input type="text" value="'.$data->checkNo.'" data-id="'.$data->lastpayID.'" id="checkno_'.$data->lastpayID.'" class="checkno" disabled>
					<a href="#" class="editField" data-which="checkno" data-id="'.$data->lastpayID.'">Edit</a>
				</td>';
					
			echo '<td><b>PHP '.$this->textM->convertNumFormat($data->netLastPay).'</b></td>'; 
			
			echo '<td>'. $this->textM->formfield('selectoption', 'status', $data->status, '', '', 'data-lastpayid="'.$data->lastpayID.'"', $status_labels).'<br/>';
			if( $data->status == 4 ){
				$docs = json_decode($data->docs);
				foreach( $docs as $doc ){
					echo '<a href="'. $this->config->base_url().'uploads/lastpay_docs/'.$doc.'" target="_blank"><img src="'.$this->config->base_url().'css/images/pdf-icon.png" />';
				}
			}
			echo '</td>';
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
 	$('.date_picker').datetimepicker({ format:'Y-m-d', timepicker:false });



 	var prev;
 	var prev_input;
 	$('.btnclass').click(function(){
 		window.location = '<?php echo $this->config->base_url(); ?>timecard/alphalist/?which=end';		
 	});

 	$('select[name="status"]').focus(function(){
 		prev = $(this).children('option').filter(':selected').val();
 	});

 	$('input[type="text"]').focus(function(){
 		prev_input = $(this).val();
 	})

 	$('a.editField').click(function(e){
 		e.preventDefault();
 		var that = $(this);
 		var id = that.data('id');
 		var which = that.data('which');
 		console.log(which);
 		console.log(id);
 		$('input#'+which+'_'+id).removeAttr('disabled').focus();
 	});

 	

 	$('select[name="status"]').change(function(){
 		var that = $(this);
 		
 		console.log(prev);
 		var r = confirm('Proceed in updating the status to ' + that.children('option').filter(':selected').text() + '?');

 		if( r == true ){
 			if( that.val() == 1 ){
 				$.colorbox({iframe:true, width:"990px", height:"600px", href: "<?php echo $this->config->base_url().'timecard/computelastpay/?e=scheddate&payID='; ?>" + that.data('lastpayid'),onClosed: function(){ that.val( prev ); } });
 			} else if( that.val() == 4 ){
	 			$.colorbox({iframe:true, width:"990px", height:"600px", href: "<?php echo $this->config->base_url().'timecard/computelastpay/?e=upload&payID='; ?>" + that.data('lastpayid'),onClosed: function(){ that.val( prev ); } });
	 		} else if( that.val() == 3 ){	 			
 				$.colorbox({iframe:true, width:"990px", height:"600px", href: "<?php echo $this->config->base_url().'timecard/computelastpay/?e=checkno&payID='; ?>" + that.data('lastpayid'),onClosed: function(){ that.val( prev ); } });
	 		} else {
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
	 		}	
 		} else {

 			that.val( prev );
 		} 		
 	});

	$('.scheddate').blur(function(){
		console.log(prev_input);
		var that = $(this);
		var r = confirm('Schedule release date?');
		var id = that.data('id');
		if( r == true ){
			$.ajax({
				url: '<?php echo $this->config->base_url().'timecard/managelastpay' ?>',
				type: 'POST',
				dataType: 'JSON',
				data: { id: id, scheddate: that.val() },
				success: function(data){
					alert('Release date has been scheduled');
					window.location.reload();
				}
			});
		} else {
			that.val(prev_input);
			that.attr('disabled', true);
		}
	});

	$('.checkno').blur(function(){
		var that = $(this);
		var r = confirm('Set check number?');
		var id = that.data('id');
		console.log(id);
		if( r == true ){
			$.ajax({
				url: '<?php echo $this->config->base_url().'timecard/managelastpay' ?>',
				type: 'POST',
				dataType: 'JSON',
				data: { id: id, checkno: that.val() },
				success: function(data){
					alert('Check number has updated.');
					window.location.reload();
				}
			});
		} else {
			that.val(prev_input);
			that.attr('disabled', true);
		}
	});



 });
 </script>

