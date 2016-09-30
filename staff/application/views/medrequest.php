<?php 
	$display_subBtn = true;
	
	if( $submitted === true ){
		echo '<p class="errortext">'.$confirm_msg.'</p>';
		//echo '<script>parent.location.reload();</script>';
	} else { ?>

<form class="leaveForm" action="<?php echo $this->config->base_url(); ?>medrequest/" method="POST" enctype="multipart/form-data" onSubmit="return checkForm();">
<input type="hidden" name="empID" value="<?php echo $employee_info->empID; ?>" />
<?php if( $pageview_type == "file" ) {
	echo '<input type="hidden" name="from_page" value="file" />';	
} ?>
	<style>
		.required{ color: red; }
	</style>
	<h2><?php echo $header; ?></h2>
	<hr/>
	<p>Please fill out the form below with the supporting documents to request for medicine reimbursement. Kindly take note that your request will not be processed if you do not submit the original prescription plus official receipt and complete this form.</p>
	
	<?php if( isset($error) AND !empty($error) ){
        echo '<p class="errortext">'.nl2br($error).'</p>';
	} ?>
	
	
	<p class="required">* Required</p>
	<table class="tableInfo">
		<?php if($pageview_type == 'approval') {
			echo '<tr>
				<td>Request ID:</td><td>'.$employee_info->medrequestID.'</td>
			</tr>
			<tr>
				<td>Submission Date:</td><td>'.$employee_info->date_submitted.'</td>
			</tr>
			';
			
		} ?>
		<tr>
			<td>ID No. <span class="required">*</td>
			<td><input type="text" name="emp_id" value="<?php echo ( isset($_POST['emp_id']) ) ? $_POST['emp_id'] : $employee_info->idNum; ?>" class="forminput" disabled="disabled" /> </td>
		</tr>
		<tr>
			<td>Employee Name <span class="required">*</td>
			<td><input type="text" name="emp_name" value="<?php echo ( isset($_POST['emp_name']) ) ? $_POST['emp_name'] : $employee_info->name; ?>"  class="forminput" disabled="disabled" /> </td>
		</tr>
		<tr>
			<td>Prescription Date <span class="required">*</td>
			<td><input type="text" name="prescription_date" value="<?php 
			if( isset($_POST['prescription_date']) ) echo $_POST['prescription_date'];
			elseif (isset($employee_info->prescription_date)) echo date('F d, Y H:m', strtotime( $employee_info->prescription_date ) ); ?>"  class="forminput datetimepick" <?php echo $disabled; ?> /> </td>
		</tr>
		<tr>
			<td>Requested amount <span class="required">*</td>
			<td><input type="number" name="requested_amount" step="0.01" value="<?php 
			if( isset($_POST['requested_amount']) ) echo $_POST['requested_amount'];
			elseif (isset($employee_info->requested_amount)) echo $employee_info->requested_amount; ?>" min="1" class="forminput" <?php echo $disabled; ?> /> </td>
		</tr>
<?php if (isset($employee_info->supporting_docs_url) AND !empty($employee_info->supporting_docs_url) ) { ?>
		<tr>
			<td>Supporting Documents</td>
			<td>
				<?php 
					$docs_url = json_decode( stripslashes($employee_info->supporting_docs_url) );
					foreach( $docs_url as $key => $url ){
						$_url = str_replace(FCPATH.'/uploads/', '', $url);
						$filename = pathinfo($_url);
						$_url_ = 'attachment.php?u='.urlencode($this->textM->encryptText('medreqeusts')).'&f='.urlencode($this->textM->encryptText($filename['filename'].'.'.$filename['extension']));
						echo '<a href="'. $this->config->base_url() . $_url_ .'" target="_blank" style="margin-right: 5px;"><img src="'. $this->config->base_url() .'css/images/pdf-icon.png" /></a>';
					}
				?>
			</td>
		</tr>
<?php } else { ?>
		<tr>
			<td>Upload Supporting Documents<br/><i style="color:#555;">Upload up to 5 documents</i></td>
			<td>
				<div class="sup_docs_div">
					<input type="file" name="supporting_docs[]" class="sup_docs" /><br/>
				</div>
				<div class="add_docs_label">
					<a href="#" class="label_add_docs">+ Add another documents</a>
				</div>
			</td>
		</tr>
<?php } ?>

<?php if( isset($med_request_history) AND !empty($med_request_history) ) { ?>
		<tr><td></td></tr>
		<tr class="trhead">
			<td colspan="2" align="center">MEDICINE REIMBURSEMENT REQUEST HISTORY</td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="tableInfo">
					<thead>
						<tr class="formLabel">
							<td>Request ID</td>
							<td>Submission Date</td>
							<td>Prescription Date</td>
							<td>Requested Amount</td>
							<td>Approved Amount</td>
							<td>Supporting Docs</td>
							<td>Status</td>
						</tr>
					</thead>
					<tbody>
						<?php foreach( $med_request_history as $key => $val ) { ?>
						<tr
							<?php if( $val->medrequestID == $med_id ) { 
								echo 'style="background-color: #ffb3b3;"';
							} ?>
						>
							<td><a href="medrequest/<?php echo $val->medrequestID; ?>"><?php echo $val->medrequestID; ?></a></td>
							<td><?php echo date('Y-m-d', strtotime($val->date_submitted) ); ?></td>
							<td><?php echo date('Y-m-d', strtotime($val->prescription_date) ); ?></td>
							<td><?php echo $val->requested_amount; ?></td>
							<td><?php echo $val->approved_amount; ?></td>
							<td>
								<?php 
								if( !empty($val->supporting_docs_url) ){
									$sup_docs = json_decode($val->supporting_docs_url);
									foreach( $sup_docs as $key => $url ){						
										$_url = str_replace(FCPATH, '', $url);
										echo '<a href="'. $this->config->base_url() . $_url .'" target="_blank" style="margin-right: 5px;"><img src="'. $this->config->base_url() .'css/images/pdf-icon.png" /></a>';
									}
								} else {
									echo '&nbsp;';
								}								
								?>
							</td>
							<td><?php echo $status_labels[ $val->status ]; ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</td>
		</tr>
<?php } ?>

<?php if( isset($self) AND $self == true ) {
	echo '<tr class="trhead">
			<td colspan="2" align="center">APPROVALS
				
			</td>			
		</tr>';
	
	if( $employee_info->status > 1 ) {
	$display_subBtn = false;
	?>
<tr bgcolor="#eee">	
			<td colspan="2">
				<h3>Medical Personnel</h3>
			</td>
		</tr>
		<tr>
					<td>Status:</td>
					<td><?php echo $status_labels[ $employee_info->status ]; ?></td>
				</tr>
				<?php if( $employee_info->status == 1 ){
					echo '<tr><td>Approved amount:</td>
					<td>'.$employee_info->approved_amount.'</td></tr>';
				} ?>
				<tr>
					<td>Remarks:</td>
					<td><?php echo wordwrap($employee_info->medperson_remarks, 75, "<br/>"); ?></td>
				</tr>

<?php } if( $employee_info->status_accounting > 1 ){
$display_subBtn = false;
	?>
	<tr bgcolor="#eee">	
			<td colspan="2">
				<h3>Accounting</h3> 
			</td>
		</tr>
	<tr>
					<td>Status:</td>
					<td><?php echo $status_labels[ $employee_info->status_accounting ]; ?></td>
				</tr>				
				<tr>
					<td>Remarks:</td>
					<td><?php echo wordwrap($employee_info->accounting_remarks, 75, "<br/>"); ?></td>
				</tr>
<?php } }//end self ?>

<?php if( $pageview_type == 'approval' ) { ?>
		<tr class="trhead">
			<td colspan="2" align="center">APPROVALS
				<input type="hidden" name="medrequestID" value="<?php echo $med_id; ?>" />
			</td>			
		</tr>
		<tr bgcolor="#eee">	
			<td colspan="2">
				<h3>Medical Personnel</h3>
			</td>
		</tr>
		<?php if( $this->access->accessMedPerson == true ) { 
			if( in_array($employee_info->status, array(1, 3) ) ){
				$display_subBtn = false;
			?>
				<tr>
					<td>Status:</td>
					<td><?php echo $status_labels[ $employee_info->status ]; ?></td>
				</tr>
				<?php if( $employee_info->status == 1 ){
					echo '<tr><td>Approved amount:</td>
					<td>'.$employee_info->approved_amount.'</td></tr>';
				} ?>
				<tr>
					<td>Remarks:</td>
					<td><?php echo wordwrap($employee_info->medperson_remarks, 75, "<br/>"); ?></td>
				</tr>				
		<?php } else {	?>
		
		<input type="hidden" name="from_page" value="med_person" />		
		<tr>
			<td>Please check one:</td>
			<td>
			<?php $status_array = array(1 => 'Approve', 3 => 'Disapprove');
				foreach( $status_array as $key => $val ){
					echo '<input type="radio" name="status_medperson" id="status_medperson_'.$key.'" value="'.$key.'"';
					if( $employee_info->status == $key ){
						echo ' checked ';
					}
					echo '/><label for="status_medperson_'.$key.'">'.$val.'</label>';
				}
			?>
				
			</td>
		</tr>
		<tr id="approved_amount_row">
			<td>Approved amount:</td>
			<td><input type="number" name="approved_amount" step="0.01" class="forminput" value="<?php echo ( isset($employee_info->approved_amount) ) ? $employee_info->approved_amount : ''; ?>" /></td>
		</tr>
		<tr>
			<td>Remarks</td>
			<td>
				<input type="text" name="remarks_med_person" class="forminput" value="<?php echo ( isset($employee_info->medperson_remarks) ) ? $employee_info->medperson_remarks : ''; ?>" />
			</td>
		</tr>
		<?php } //end of status filter  
		} //end of med_person flag ?>
		<?php if( $this->access->accessFinance == true ){ ?>	
			<?php if( $employee_info->status_accounting == 2 ) { //approved by accounting 
				$display_subBtn = false;
				
			?>
				<tr>
						<td>Status:</td>
						<td><?php echo $status_labels[ $employee_info->status ]; ?></td>
					</tr>
					<tr>
						<td>Remarks:</td>
						<td><?php echo wordwrap($employee_info->medperson_remarks, 75, "<br/>"); ?></td>
					</tr>
					<tr>
						<td>Approved Amount:</td>
						<td><?php echo $employee_info->approved_amount; ?></td>
					</tr>
					<tr bgcolor="#eee">	
						<td colspan="2">
							<h3>Accounting</h3>
						</td>
					</tr>
				<tr>
					<td>Status:</td>
					<td><?php echo $status_labels[ $employee_info->status_accounting ]; ?></td>
				</tr>
				<tr>
					<td>Remarks:</td>
					<td><?php echo wordwrap($employee_info->accounting_remarks, 75, "<br/>"); ?></td>
				</tr>
				<tr bgcolor="#eee">
					<td colspan="2"><h4>Payslip Item</h4></td>
				</tr>
				<tr>
		
					<td>Item Name:</td>
					<td><b><?php echo $payslip_details->payName; ?></b></td>
				</tr>
				<tr>
					<td>Amount:</td>
					<td><?php echo $payslip_details->payAmount; ?></td>
				</tr>
				<tr>
					<td>Pay in:</td>
					<td><?php echo $payslip_details->payPeriod; ?></td>
				</tr>
				<tr>
					<td>Pay on:</td>
					<td><?php 
						if( $payslip_details->payPeriod == 'once' ){
							echo $payslip_details->payStart;
						} else {
							echo 'from: '. $payslip_details->payStart .' to: '. $payslip_details->payEnd;
						}
					 ?></td>
				</tr>
						
				
			<?php } //end of status 2
			 else if( $employee_info->status_accounting == 4 OR $employee_info->status == 0 ){ 
			 $display_subBtn = false;
			 
				if( $employee_info->status_accounting == 4 ){
			 ?> 
					<tr>
						<td>Status:</td>
						<td><?php echo $status_labels[ $employee_info->status ]; ?></td>
					</tr>
					<tr>
						<td>Remarks:</td>
						<td><?php echo wordwrap($employee_info->medperson_remarks, 75, "<br/>"); ?></td>
					</tr>
					<?php if($employee_info->status == 1){ ?>
							<tr>
						<td>Approved Amount:</td>
						<td><?php echo $employee_info->approved_amount; ?></td>
					</tr>
					<?php } ?>
					
					<tr bgcolor="#eee">	
						<td colspan="2">
							<h3>Accounting</h3>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td>Status:</td>
					<td><?php echo $status_labels[ $employee_info->status_accounting ]; ?></td>
				</tr>
				<tr>
					<td>Remarks:</td>
					<td><?php echo wordwrap($employee_info->accounting_remarks, 75, "<br/>"); ?></td>
				</tr>
		<?php	} else { ?>
					<input type="hidden" name="from_page" value="full_finance" />
					<tr>
						<td>Status:</td>
						<td><?php echo $status_labels[ $employee_info->status ]; ?></td>
					</tr>
					<tr>
						<td>Remarks:</td>
						<td><?php echo wordwrap($employee_info->medperson_remarks, 75, "<br/>"); ?></td>
					</tr>
					<?php if($employee_info->status == 1){ ?>
							<tr>
						<td>Approved Amount:</td>
						<td><?php echo $employee_info->approved_amount; ?></td>
					</tr>
					<?php } ?>
					
					<tr bgcolor="#eee">	
						<td colspan="2">
							<h3>Accounting</h3>
						</td>
					</tr>
					<tr>
						<td>Please check one:</td>
						<td>
						<?php $status_array = array(2 => 'Approve', 4 => 'Disapprove');
							foreach( $status_array as $key => $val ){
								echo '<input type="radio" name="status_accounting" id="status_accounting_'.$key.'" value="'.$key.'"';
								if( $employee_info->status == $key ){
									echo ' checked ';
								}
								echo '/><label for="status_accounting_'.$key.'">'.$val.'</label>';
							}
						?>
							
						</td>
					</tr>
		<tr>
			<td>Remarks</td>
			<td>
				<input type="text" name="remarks_accounting" class="forminput" />
			</td>
		</tr>
		<tr id="payroll_item_row">
			<td colspan="2">
				<?php echo $payroll_item_html; ?>
			</td>
		</tr>
		<?php } //end status flag ?> 
		
		
		<?php } //end finance flag ?>
<?php } //end approval flag  ?>		

<?php if( $display_subBtn == true ) { ?>
		<tr>
			<td colspan="2" align="center"><input type="submit" name="submit" value="Submit" class="btnclass" /></td>
		</tr>
<?php } ?>
	</table>
</form>
<?php if( $pageview_type == 'approval' ) { ?>
	<?php if( $this->access->accessMedPerson ) { ?>
	<script>
		$(function(){
			$('#approved_amount_row').hide();
			$('input[name="status_medperson"]').change(function(){
				var that = $(this);
				if( that.val() == 1 ){
					$('#approved_amount_row').show();
				}
				if( that.val() == 3 ){
					$('#approved_amount_row').hide();
				}
			});
		});
	</script>
	<?php } ?>
	
	<?php if( $this->access->accessFullFinance ) { ?>
	<script>
			$(function(){
				$('#payroll_item_row').hide();
				$('input[name="status_accounting"]').change(function(){
					var that = $(this);
					if( that.val() == 2 ){
						$('#payroll_item_row').show();
					}
					if( that.val() == 4 ){
						$('#payroll_item_row').hide();
					}
				});	
			});
	</script>
	<?php } ?>
	
<?php } ?>


<?php if( $pageview_type == 'file' ) { ?>
	<script>
	$(function(){
		var sup_counter = 1;
		$('a.label_add_docs').hide();
		
		$('a.label_add_docs').click(function(){
			sup_counter += 1;			
			if( sup_counter <= 5 ){				
				$('.sup_docs_div').append('<input type="file" name="supporting_docs[]" class="sup_docs" /><br/>');
				if( sup_counter == 5 ){
					$('a.label_add_docs').hide();								
				}
			} 
		});
		$('.sup_docs').change(function(){
			if( sup_counter = 1 ){
				$('a.label_add_docs').show();
			}			
		});
	});
	function checkForm(){
		
		var id_no = $('input[name="emp_id"]').val();
		var emp_name = $('input[name="emp_name"]').val();
		var prescription_date = $('input[name="prescription_date"]').val();
		var requested_amount = $('input[name="requested_amount"]').val();
		var sup_docs = $('input[name="supporting_docs[]"]').val();
		var valid = true;
		var error_text = '';
		if( prescription_date == '' ){
			error_text += 'Please specify the prescription date.\n';
			console.log('Please specify the prescription date.');
			valid = false;
		}
		if( requested_amount == '' ){			
			error_text += 'Please specify the requested amount.\n';
			console.log('Please specify the requested amount.');
			valid = false;
		}
		if( $.isEmptyObject( sup_docs ) ){
			
			error_text += 'Please provide supporting documents.\n';
			console.log('Please provide supporting documents.');
			valid = false;
		}
		
		if( valid == false ){
			alert(error_text);
		}
		
		return valid;
	}
	
	</script>
<?php } ?>
	
<?php } //end of not submission ?>
