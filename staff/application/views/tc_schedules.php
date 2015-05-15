<?php 
	$this->load->view('includes/header_timecard'); 
	// print_r($listOfSchedule);
?>
<br/>
<table class="dTable display stripe hover" border="0">
	<thead>
		<tr>
			<td>Schedule Label</td>
			<td>Start Date</td>
			<td>End Date</td>
		</tr>
	</thead>
	
	<?php 
		if(!empty($listOfSchedule)):
		foreach($listOfSchedule as $schedules) {
		echo '<tr>';
		echo '<td>';
					if($schedules->staffCustomSched_fk > 0){
						echo '<b>'.$schedules->schedName.'</b><br/>
						<table width="50%">
							';						
						if($schedules->sunday > 0)
							echo "<tr><td>Sunday - </td><td>".$this->staffM->getSingleField('staffCustomSchedTime','timeValue','timeID='.$schedules->sunday)."</td></tr>";
						if($schedules->monday > 0)
							echo "<tr><td>Monday - </td><td>".$this->staffM->getSingleField('staffCustomSchedTime','timeValue','timeID='.$schedules->monday)."</td></tr>";
						if($schedules->tuesday > 0)
							echo "<tr><td>Tuesday - </td><td>".$this->staffM->getSingleField('staffCustomSchedTime','timeValue','timeID='.$schedules->tuesday)."</td></tr>";
						if($schedules->wednesday > 0)
							echo "<tr><td>Wednesday - </td><td>".$this->staffM->getSingleField('staffCustomSchedTime','timeValue','timeID='.$schedules->wednesday)."</td></tr>";
						if($schedules->thursday > 0)
							echo "<tr><td>Thursday - </td><td>".$this->staffM->getSingleField('staffCustomSchedTime','timeValue','timeID='.$schedules->thursday)."</td></tr>";
						if($schedules->friday > 0)
							echo "<tr><td>Friday - </td><td>".$this->staffM->getSingleField('staffCustomSchedTime','timeValue','timeID='.$schedules->friday)."</td></tr>";
						if($schedules->saturday > 0)
							echo "<tr><td>Saturday - </td><td>".$this->staffM->getSingleField('staffCustomSchedTime','timeValue','timeID='.$schedules->saturday)."</td></tr>";
						
						echo '</table>';
					}
					else{
						echo '<b>'.$schedules->timeName.'</b><br/>';
						echo $schedules->timeValue;
					}
		echo '</td>
			<td>'.$schedules->effectivestart.'</td>
			<td>'.$schedules->effectiveend.'</td>
		</tr>';
		}
		
		endif;
	?>	
	

</table>



<script type="text/javascript">
	$(function(){				
		$('.dTable').dataTable({	
			// "bPaginate": false,
			"bFilter": false			
		});
	});
</script>