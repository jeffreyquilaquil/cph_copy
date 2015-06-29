<?php 
	$this->load->view('includes/header_timecard'); 
?>
<br/>
<table class="dTable">
	<thead>
		<td>Name</td>
		<td><br/></td>
	</thead>
<?php
	foreach($allStaffs AS $all){
		echo '<tr>
			<td>'.$all->lname.', '.$all->fname.'</td>
			<td><button class="btnclass iframe" href="'.$this->config->base_url().'schedules/setschedule/'.$all->empID.'/">Set Schedule</button></td>
		</tr>';
	}
?>	
</table>

<script type="text/javascript">
	$(function(){
		$('.dTable').dataTable({
			"pageLength": 5,
			"ordering": false
		});
	});
	
	
</script>