<h3>Edit Schedule of <?= $row->name ?></h3>
<hr/>
<form action="" method="POST" onSubmit="return validateForm();">
<table class="tableInfo">
	<tr>
		<td width="20%">Start Date</td>
		<td><input id="startdate" type="text" class="forminput" value="<?= date('F d, Y', strtotime($row->effectivestart)) ?>" disabled/></td>
	</tr>
	<tr>
		<td>End Date</td>
		<td><input id="enddate" type="text" class="forminput datepick" name="enddate" required /></td>
	</tr>
	<tr>
		<td><br/></td>
		<td>
			<input type="hidden" name="schedID" value="<?= $row->schedID ?>"/>
			<input type="submit" value="Update" class="btnclass"/>
		</td></tr>	
</table>
</form>

<script type="text/javascript">
	function validateForm(){
		if($('#startdate').val() > $('#enddate').val()){
			alert('Invalid. End date is before start date.')
			return false;
		}else{
			return true;
		}
	}
</script>