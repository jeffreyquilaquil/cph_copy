<table id="dtable13" class="display stripe hover">
	<thead>
	<tr>
		<th>Employee Name</th>
		<th>Position</th>
		<th>Start Date</th>
		<th>File</th>
	</tr>	
	</thead>
	
	<tbody>
<?php
	foreach($queryData AS $data){
		echo '<tr>';
			echo '<td>'.$data->fullName.'</td>';
			echo '<td>'.$data->title.'</td>';
			echo '<td>'.date('F d, Y', strtotime($data->startDate)).'</td>';
			echo '<td><a href="'.$this->config->base_url().'timecard/computelastpay/?is_active=yes&show=pdf&which_pdf=bir&empID='.$data->empID.'&yearOfBIR='.$data->yearOfBIR.' " target="_blank"><img src="http://10.100.0.1/~fitt/cph/staff/css/images/pdf-icon.png" width="25px"/></a></td>';
		echo '</tr>';
	}
	
?>
	</tbody>
	<!-- <tfoot>
		<tr>
			<td colspan="4">
		<?php if( $this->access->accessFullFinance == true ){
			echo '<p>On selected items: <input type="submit" onClick="confirmMsg();" name="delete_tax_record" class="btnclass" value="Delete" /> <input type="button" name="regenerate" class="btnclass" disabled value="Regenerate" /></p>';
		}
		?>
			</td>
		</tr>
	</tfoot> -->
</table>

<script type="text/javascript">
	$(document).ready(function(){
			$('#dtable13').dataTable({});
	});
</script>