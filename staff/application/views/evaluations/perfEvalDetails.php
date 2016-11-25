<style type="text/css">
	td{
		border-bottom: 1px solid #ccc;
	}
</style>

<?php 
	$Lpane = ['Employee Name','Position Title','Department','Evaluator','Evaluator Position Title','Immediate Supervisor','Immediate Supervisor','2nd Level Supervisor','2nd Level Supervisor Position Title','Evaluation Date'];
	$Rpane = [$employee->name,$employee->title,$employee->dept,$evaluator->name,$evaluator->title,$supervisor->name,$supervisor->title,$supervisor2->name,$supervisor2->title,date('F d, Y', strtotime($employee->evalDate))];
 ?>
<h2>Evaluation Details</h2>
<hr>
<form acrion="" method="POST" enctype="multipart/form-data" onSubmit="disableSubmitBtn()">
<table style="table-collapse:collapse;">
	<tr style="background:#ccc;height: 25px;">
		<td colspan="2"><strong>EMPLOYEE DETAILS</strong></td>
	</tr>
	<?php 
		for($i = 0;$i<count($Rpane);$i++){
			echo "<tr>";
				echo '<td>'.$Lpane[$i].'</td>';
				echo '<td>'.$Rpane[$i].'</td>';
			echo "</tr>";
		}
	 ?>

	<?php 
		if($employee->status != 4){
			$checked = "";
			if($employee->hrPrintDate != "0000-00-00 00:00:00"){
				$checked = "checked";
				echo "<input type='hidden' name='printDate' value='".$employee->hrPrintDate."'>";
			}
			$tdTitles = ['EMPLOYEE DETAILS', 'Evaluation form printed','<input type="checkbox" name="fprinted" id="fprinted" '.$checked.'> Tick checkbox if done printing form.', 'Upload signed coaching form', '<input type="file" name="fupload" id="fupload" >'];
			$button = '<input type="submit" class="btnclass" value="Update"  style="float:right">';
		}else{
			$tdTitles = ['CANCELLATION DETAILS','Status','<strong>CANCELLED</strong>', 'Cancel Reason', $employee->cancelReason];
			$button = "";
		}
	 ?>
	<tr><td>&nbsp;</td><td></td></tr>
	<tr style="background:#ccc;height: 25px;">
		<td colspan="2"><strong><?php echo $tdTitles[0] ?></strong></td>
	</tr>
	<tr>
		<td><?php echo $tdTitles[1] ?></td>
		<td><?php echo $tdTitles[2] ?></td>
	</tr>
	<tr>
		<td><?php echo $tdTitles[3] ?></td>
		<td><?php echo $tdTitles[4] ?></td>
	</tr>
</table>

<br>
<?php echo $button ?>
</form>
<div><br></div>

<script type="text/javascript">

	function disableSubmitBtn(){
		displaypleasewait();
	}
</script>