<?php
	$from_month = (isset($from_month)) ? $from_month : '';
	$from_year = (isset($from_year)) ? $from_year : date('Y');
	$to_month = (isset($to_month)) ? $to_month : '';
	$to_year = (isset($to_year)) ? $to_year : date('Y');
?>
<style type="text/css">
	form ul { list-style-type: none; padding-left: 0; margin-left: 0;  }
	form li { display: inline; margin-right: 5px;}
	label{ font-weight: bold; }
</style>
<h2>Tax Summary</h2>
<hr/>
<div style="background-color: #660808; color: #fff; display: block; width: 99%; padding: 5px; font-size: 14px; font-weight: bold; overflow: hidden;">Alphalist Details</div>
<div class="">
	<form name="frm_alphalist" id="frm_alphalist" method="post" action="<?php echo $this->config->base_url().'timecard/taxsummary/';?>">
		<input type="hidden" name="empID" value="<?=$_GET['empIDs']?>" />
		
		<ul>
			<li><label for="from_month">From:</label></li>
			<li><?php echo $this->textM->formfield('selectoption', 'from_month', $from_month, '', '', 'id="from_month"', $monthFullArray); ?></li>
			<li><?php echo $this->textM->formfield('selectoption', 'from_year', $from_year, '', '', 'id="from_year"', $yearFullArray); ?></li>
			<li><label from="to_month">to</label></li>
			<li><?php echo $this->textM->formfield('selectoption', 'to_month', $to_month, '', '', 'id="to_month"', $monthFullArray); ?></li>
			<li><?php echo $this->textM->formfield('selectoption', 'to_year', $to_year, '', '', 'id="to_year"', $yearFullArray); ?></li>
			<li><button name="btnSubmit_" value="Submit" class="btnclass" id="btnSubmit">Generate Tax Summary</button></li>
		</ul>
	</form>
	
</div>
<script type="text/javascript">
	$(function(){
		$('#btnSubmit').click(function(e){
			e.preventDefault();
			var from_month = parseInt($('#from_month').val());
			var to_month = parseInt($('#to_month').val());
			var from_year = parseInt($('#from_year').val());
			var to_year = parseInt($('#to_year').val());

			
			if( from_month > to_month ){
				alert('`From` Month should not be greater than the `To` Month');
			} else if( from_year > to_year ){
				alert('`From` Year should not be greater than the `To` Year');
			} else {
				$('#frm_alphalist').submit();
			}
		});

	});
</script>