<?php
	
	$currentMedPerson = '';
	$currentFullFinance = '';
	$currentAll = '';
	if( $this->access->accessMedPerson ){
		$currentMedPerson = 'current';
	} else if( $this->access->accessFullFinance ){
		$currentFullFinance = 'current';
	} else {
		$currentAll = 'current';
	}
	
?>

<h2>HR HelpDesk</h2>
<hr/>

<ul class="tabs">
	<li class="tab-link <?php echo $currentMedPerson; ?>" data-tab="tab-1">New <?php echo '('. $cnt_medical .')'; ?></li>
	<li class="tab-link <?php echo $currentFullFinance; ?>" data-tab="tab-2">Active <?php echo '('. $cnt_accounting .')'; ?></li>
	<li class="tab-link <?php echo $currentAll; ?>" data-tab="tab-3">Resolved <?php echo '('. $cnt_all .')'; ?></li>
	<li class="tab-link <?php echo $currentAll; ?>" data-tab="tab-4">Cancelled <?php echo '('. $cnt_all .')'; ?></li>
</ul>

<div id="tab-1" class="tab-content <?php echo $currentMedPerson; ?>"><br/>

<?php echo $this->textM->reimbursementTableDisplay($data_query_medical, 'pending_med'); ?>
</div>

<div id="tab-2" class="tab-content <?php echo $currentFullFinance; ?>"><br/>
<h3>Pending Approval from Accounting <?php echo '('. $cnt_accounting .')'; ?></h3>
<?php echo $this->textM->reimbursementTableDisplay($data_query_accounting, 'pending_accounting'); ?>
</div>

<div id="tab-3" class="tab-content <?php echo $currentAll; ?>"><br/>

	
</div>

<div id="tab-4" class="tab-content <?php echo $currentMedPerson; ?>"><br/>
<h3>Pending Approval from Medical Personnel <?php echo '('. $cnt_medical .')'; ?></h3>
<?php echo $this->textM->reimbursementTableDisplay($data_query_medical, 'pending_med'); ?>
</div>


<script type="text/javascript">

	function showTbl(tbl, p){
		$('#tbl'+tbl).toggleClass('hidden');
		$(p).find('a').text($(p).find('a').text() == '[show]' ? '[hide]' : '[show]'); 
	}
</script>


