<h3>Generate Last Pay</h3>
<hr/>
<?php
	if(count($dataStaffs)==0){
		echo '<p class="errortext">No employee selected.</p>';
	}else{
?>
<table class="tableInfo">
	<tr class="trhead">
		<td>Employee ID</td>
		<td>Employee Name</td>
		<td>Start Date</td>
		<td>End Date</td>
	</tr>
<?php
	foreach($dataStaffs AS $staff){
		echo '<tr>';
			echo '<td>'.$staff->idNum.'</td>';
			echo '<td>'.$staff->lname.', '.$staff->fname.'</td>';
			echo '<td>'.date('F d, Y', strtotime($staff->startDate)).'</td>';
			echo '<td>'.(($staff->endDate!='0000-00-00')?date('F d, Y', strtotime($staff->endDate)):'No yet determined').'</td>';
		echo '</tr>';
	}
?>
</table>
<br/>
<b style="font-size:14px;">Last Pay Details</b>

<?php } ?>