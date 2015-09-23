<div style="width:100%">
<?php
	$prevYear = date('Y', strtotime($today.' -1 year'));
	$nextYear = date('Y', strtotime($today.' +1 year'));
?>
	<a href="<?= $this->config->base_url() ?>timecard/payslips/?d=<?= $prevYear.'-01-01' ?>"><button class="btnclass btnorange"><<<?= $prevYear ?></button></a>
	<a href="<?= $this->config->base_url() ?>timecard/payslips/?d=<?= $nextYear.'-01-01' ?>"><button class="btnclass btnorange floatright"><?= $nextYear ?> >></button></a>
</div>
<br/>

<?php
	if(count($dataPayslips)==0){
		echo '<p class="errortext">No Paylips Generated.</p>';
	}else{
?>
	<form action="" method="POST">
		<b><?= date('Y', strtotime($today)) ?> PAY PERIODS</b> <button class="btnclass btngreen">Download</button>

		<table class="tableInfo">
			<tr class="trhead"><td>
				<?= $this->textM->formfield('checkbox', '', '', '', '', 'id="selectAll"'); ?> Select All<br/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Or Select on one or more payslips to download.
			</td></tr>
		<?php
			foreach($dataPayslips AS $d){	
				echo '<tr><td>'.$this->textM->formfield('checkbox', 'payslip[]', $d->payslipID, 'payslipname').' '.$d->payPeriod.'</td></tr>';	
			}
		?>
		</table>
	</form>
<?php } ?>

<script type="text/javascript">
	$(function(){
		$('#selectAll').click(function(){
			if(this.checked) {
				$('.payslipname').each(function() {
					this.checked = true;                        
				});
			}else{
				$('.payslipname').each(function() {
					this.checked = false;                        
				});
			}
		});
	});
</script>