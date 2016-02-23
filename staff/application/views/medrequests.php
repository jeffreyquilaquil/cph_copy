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
	<li class="tab-link <?php echo $currentMedPerson; ?>" data-tab="tab-1">Pending Approval from Medical Personnel <?php echo '('. $cnt_medical .')'; ?></li>
	<li class="tab-link <?php echo $currentFullFinance; ?>" data-tab="tab-2">Pending Approval from Accounting <?php echo '('. $cnt_accounting .')'; ?></li>
	<li class="tab-link <?php echo $currentAll; ?>" data-tab="tab-3">All Requests <?php echo '('. $cnt_all .')'; ?></li>
</ul>

<div id="tab-1" class="tab-content <?php echo $currentMedPerson; ?>"><br/>
<h3>Pending Approval from Medical Personnel <?php echo '('. $cnt_medical .')'; ?></h3>
<?php echo $this->textM->reimbursementTableDisplay($data_query_medical, 'pending_med'); ?>
</div>

<div id="tab-2" class="tab-content <?php echo $currentFullFinance; ?>"><br/>
<h3>Pending Approval from Accounting <?php echo '('. $cnt_accounting .')'; ?></h3>
<?php echo $this->textM->reimbursementTableDisplay($data_query_accounting, 'pending_accounting'); ?>
</div>

<div id="tab-3" class="tab-content <?php echo $currentAll; ?>"><br/>

<div class="cpointer" onClick="showTbl('cnt_medical', this)">
	<h3>Waiting on Approval from Medical Personnel <?php echo '('. $cnt_medical .')'; ?>  <a class="fs11px">[show]</a></h3><hr/>
</div>
		<?php echo $this->textM->reimbursementTableDisplay($data_query_medical, 'cnt_medical', true); ?>
		<br/><br/>

		
<div class="cpointer" onClick="showTbl('cnt_disapproved_medical', this)">
	<h3>Approved by Medical Personnel <?php echo '('. $cnt_accounting .')'; ?>  <a class="fs11px">[show]</a></h3><hr/>
</div>
		<?php echo $this->textM->reimbursementTableDisplay($data_disapproved_medical, 'cnt_disapproved_medical', true); ?>
		<br/><br/>
		
<div class="cpointer" onClick="showTbl('cnt_approved_accounting', this)">
	<h3>Approved by Accounting <?php echo '('. $cnt_approved_accounting .')'; ?>  <a class="fs11px">[show]</a></h3><hr/>
</div>
		<?php echo $this->textM->reimbursementTableDisplay($data_approved_accounting, 'cnt_approved_accounting', true); ?>
		<br/><br/>
		
<div class="cpointer" onClick="showTbl('cnt_disapproved_accounting', this)">
	<h3>Disapprove by Accounting <?php echo '('. $cnt_disapproved_accounting .')'; ?>  <a class="fs11px">[show]</a></h3><hr/>
</div>
		<?php echo $this->textM->reimbursementTableDisplay($data_disapproved_accounting, 'cnt_disapproved_accounting', true); ?>
		<br/><br/>
		
		
</div>
<script type="text/javascript">

	function showTbl(tbl, p){
		$('#tbl'+tbl).toggleClass('hidden');
		$(p).find('a').text($(p).find('a').text() == '[show]' ? '[hide]' : '[show]'); 
	}
</script>


