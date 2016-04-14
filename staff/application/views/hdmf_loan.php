<?php 
$disabled = '';
if( isset($upload) OR isset($accounting) ){
	$disabled = 'disabled';
} ?>

<style>
	.error{ padding-left: 10px; color: #ff4242; }
	ul li{list-style-type:  none;}
	li {padding: 5px;}
</style>

<h2>Pag-IBIG Loan</h2>
<hr/>
<?php $error = validation_errors();
	if( !empty($error) ){
		echo $error.'<hr/>';
	} 
	if( isset($upload_error) AND !empty($upload_error) ){
		echo '<div class="error"><strong>Upload Error:</strong><br/>'.$upload_error.'</div>';
	}
 ?>
<?php if( isset($msg) AND !empty($msg) ){
	echo '<div class="error">'.$msg.'</div>';
	echo '<script> location.ref="'.$this->config->base_url().'"</script>';
} else { ?>

<form action="" method="POST" enctype="multipart/form-data">
<?php if( isset($payroll_item_html) ) {
	echo '<input type="hidden" name="empID_fk" value="'.$empID.'" />';
	echo $payroll_item_html;
	echo '<br/>';
	echo '<h3>Loan Details</h3><hr/>';
}
	
?>



<table class="tableInfo">
	<?php if( isset($upload) ){ ?>
	<tr>
		<td>Upload voucher:</td>
		<td><input type="file" name="loan_voucher" /><input type="hidden" name="loan_id" value="<?php echo $loan_id; ?>" /><input type="hidden" name="username" value="<?php echo $username; ?>" /</td>
	</tr>
<?php } else if( $disabled == '' ){ ?>
	<tr>
		<td width="30%">Loan Type</td>
		<td>		
			<?php $type_array = array('new', 'renew');
			foreach( $type_array as $val ){
				echo '<input type="radio" name="loan_type" value="'.$val.'" id="'.$val.'_loan_type" '.$disabled.' '.( ($loan_type == $val) ? ' checked ':'').' />'.ucwords($val).'</label>';
			}
			
			?>
		</td>
	</tr>
	<tr>
		<td>Loan Amount</td>
		<td>
			<ul>
			<?php $amt_array = array(60 => 'Max of 60% (24-59 mos.)', 70 => 'Max of 70% (60-119 mos.)', 80 => 'Max of 80% (at least 120 mos.)', 90 => 'Others, please specify:');
				foreach( $amt_array as $key => $val ){
					echo '<li><input type="radio" name="loan_amt" value="'.$key.'" id="loan_amt_'.$key.'" '.$disabled.' '.( ($loan_amt == $key) ? ' checked ':'').' />'.$val.'</li>';
				}
			?>
			</ul>

		</td>
	</tr>
	<tr>
		<td>Loan Purpose</td>
		<td>
			<select name="loan_purpose" <?php echo $disabled; ?>>
				<?php 
					$c = 1;
					echo '<option value="0">Please select</option>';
					foreach( $loan_purpose_array as $key => $purpose ){
						echo '<option value="'.$key.'" '.( ($loan_purpose == $key) ? ' selected ':'').'>'.$purpose.'</purpose>';
						$c++;
					}
				 ?>
			</select>
		</td>
	</tr>
	<!--<tr>
		<td>Date of Pag-IBIG membership</td>
		<td><input type="type" name="membership_date" class="datepick" /></td>		
	</tr>-->
	<tr>
		<td>Birthplace</td>
		<td><input type="type" name="birth_place" value="<?php echo $birth_place; ?>" <?php echo $disabled; ?> /></td>		
	</tr>
	<tr>
		<td>Mother's maiden name</td>
		<td><input type="type" name="mo_maiden_name" value="<?php echo $mo_maiden_name; ?>" <?php echo $disabled; ?> /></td>		
	</tr>
	
	<tr>
		<td colspan="2">
		<table class="tableInfo">
			<tr>
				<td colspan="4"><strong>Employment history from date of Pag-IBIG Membership<strong></td>		
			</tr>
			<tr>
				<td style="font-weight: bold; text-align: center;">Name of Employer</td>
				<td style="font-weight: bold; text-align: center;">Address</td>
				<td style="font-weight: bold; text-align: center;">From (Mo./Yr.)</td>
				<td style="font-weight: bold; text-align: center;">To (Mo./Yr.)</td>
			</tr>
			<tr>
				<td><input type="text" name="employer_1[]" value="<?php echo $employer_1[0]; ?>" <?php echo $disabled; ?> /></td>
				<td><input type="text" name="employer_1[]" value="<?php echo $employer_1[1]; ?>" <?php echo $disabled; ?> /></td>
				<td><input type="text" name="employer_1[]" value="<?php echo $employer_1[2]; ?>" class="datepick" <?php echo $disabled; ?> /></td>
				<td><input type="text" name="employer_1[]" value="<?php echo $employer_1[3]; ?>" class="datepick" <?php echo $disabled; ?> /></td>
			</tr>
			<tr>
				<td><input type="text" name="employer_2[]" value="<?php echo $employer_2[0]; ?>" <?php echo $disabled; ?> /></td>
				<td><input type="text" name="employer_2[]" value="<?php echo $employer_2[1]; ?>"  <?php echo $disabled; ?> /></td>
				<td><input type="text" name="employer_2[]" value="<?php echo $employer_2[2]; ?>"  class="datepick" <?php echo $disabled; ?> /></td>
				<td><input type="text" name="employer_2[]" value="<?php echo $employer_2[3]; ?>" class="datepick" <?php echo $disabled; ?> /></td>
			</tr>
		</table>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td style="padding: 8px;" colspan="3"><input type="submit" value="Submit" name="submit" class="btnclass btnred" /></td>
	</tr>
	
</table>
</form>
<?php } //end else if $msg ?>