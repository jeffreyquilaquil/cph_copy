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

<h2>Medical Reimbursement Requests</h2>
<hr/>

<ul class="tabs">
	<li class="tab-link <?php echo $currentMedPerson; ?>" data-tab="tab-1">Pending Approval from Medical Personnel</li>
	<li class="tab-link <?php echo $currentFullFinance; ?>" data-tab="tab-2">Pending Approval from Accounting</li>
	<li class="tab-link <?php echo $currentAll; ?>" data-tab="tab-3">All Requests</li>
</ul>

<div id="tab-1" class="tab-content <?php echo $currentMedPerson; ?>"><br/>
<h3>Pending Approval from Medical Personnel</h3>
<?php echo $this->textM->reimbursementTableDisplay($data_query, 'pending_med'); ?>
</div>

<div id="tab-2" class="tab-content <?php echo $currentFullFinance; ?>"><br/>
<h3>Pending Approval from Accounting</h3>
<?php echo $this->textM->reimbursementTableDisplay($data_query, 'pending_accounting'); ?>
</div>

<div id="tab-3" class="tab-content <?php echo $currentAll; ?>"><br/>
<h3>All Requests</h3>
<?php echo $this->textM->reimbursementTableDisplay($data_query, 'all'); ?>
</div>



