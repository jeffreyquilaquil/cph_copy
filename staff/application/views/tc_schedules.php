<?php 
	$this->load->view('includes/header_timecard'); 
?>
<br/>
<button class="btnclass iframe" href="<?= $this->config->base_url().'timecard/'.$visitID.'/addschedule/' ?>">Add Schedule</button>



<script type="text/javascript">
	/* $(function(){				
		$('.dTable').dataTable({
			"bFilter": false			
		});
	}); */
</script>