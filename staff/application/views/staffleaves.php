<h2>Staff Leaves</h2>
<hr/>

<ul class="tabs">
<?php
	$current = 'tab-1';
	if($this->user->level>0 || $this->access->accessFullHR==true){
		echo '<li class="tab-link '.(($this->user->access!='hr')?'current':'').'" data-tab="tab-1">On-Leave Today ('.count($tquery).')</li>';
		echo '<li class="tab-link" data-tab="tab-2">Pending Immediate Supervisor\'s Approval ('.( count($imquery) + count($imcancelledquery) ).')</li>';
	}
	if($this->access->accessFullHR==true){
		if($this->user->access=='hr')
			$current = 'tab-3';		
		echo '<li class="tab-link '.(($this->user->access=='hr')?'current':'').'" data-tab="tab-3">Pending HR\'s Approval ('.count($hrquery).')</li>';
	}
	
	echo '<li class="tab-link" data-tab="tab-4">All Leaves ('.(count($allpending) + count($allapproved) + count($allapprovedNopay) + count($alldisapproved) + count($allcancelled)).')</li>';	
?>
</ul>

<div id="tab-1" class="tab-content <?= (($current=='tab-1')?'current':'') ?>">
<br/>
<?php if(count($tquery)==0){
	echo 'None.';
}else{ 
	echo $this->staffM->leaveTableDisplay($tquery, 'tquery');
} ?>
</div>

<div id="tab-2" class="tab-content <?= (($current=='tab-2')?'current':'') ?>">
<br/>
<?php if(count($imquery)==0 && count($imcancelledquery)==0){
	echo 'No pending leaves for approval.';
}else{ 
	if(count($imquery)>0){
		echo '<h3>Pending Leaves for Approval</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($imquery, 'imquery');
		echo '<br/><br/>';
	}
	if(count($imcancelledquery)>0){
		echo '<h3>Pending Cancelled Leaves for Approval</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($imcancelledquery, 'imcancelledquery');
		echo '<br/><br/>';
	}
	
} ?>
</div>

<div id="tab-3" class="tab-content <?= (($current=='tab-3')?'current':'') ?>">
<br/>
<?php if(count($hrquery)==0){
	echo 'No pending leaves for approval.';
}else{ 
	echo $this->staffM->leaveTableDisplay($hrquery, 'hrquery');
} ?>
</div>

<div id="tab-4" class="tab-content <?= (($current=='tab-4')?'current':'') ?>">
<br/>
<?php if(count($allpending)==0 && count($allapproved)==0 && count($allapprovedNopay)==0 && count($alldisapproved)==0 && count($allcancelled)==0){
	echo 'No leaves on file.';
}else{ 
	if(count($allpending)>0){
		echo '<div class="cpointer" onClick="showTbl(\'allpending\', this)"><h3>All Pending Leaves for Approval ('.count($allpending).')  <a class="fs11px">[show]</a></h3><hr/></div>';
		echo $this->staffM->leaveTableDisplay($allpending, 'allpending');
		echo '<br/><br/>';
	}
	if(count($allapproved)>0){
		echo '<div class="cpointer" onClick="showTbl(\'allapproved\', this)"><h3>All Approved WITH Pay Leaves ('.count($allapproved).')  <a class="fs11px">[show]</a></h3><hr/></div>';
		echo $this->staffM->leaveTableDisplay($allapproved, 'allapproved');
		echo '<br/><br/>';
	}
	if(count($allapprovedNopay)>0){
		echo '<div class="cpointer" onClick="showTbl(\'allapprovedNopay\', this)"><h3>All Approved WITHOUT Pay Leaves ('.count($allapprovedNopay).')  <a class="fs11px">[show]</a></h3><hr/></div>';
		echo $this->staffM->leaveTableDisplay($allapprovedNopay, 'allapprovedNopay');
		echo '<br/><br/>';
	}
	if(count($alldisapproved)>0){
		echo '<div class="cpointer" onClick="showTbl(\'alldisapproved\', this)"><h3>All Disapproved Leaves ('.count($alldisapproved).')  <a class="fs11px">[show]</a></h3><hr/></div>';
		echo $this->staffM->leaveTableDisplay($alldisapproved, 'alldisapproved');
		echo '<br/><br/>';
	}
	if(count($allcancelled)>0){
		echo '<div class="cpointer" onClick="showTbl(\'allcancelled\', this)"><h3>All Cancelled Leaves ('.count($allcancelled).')  <a class="fs11px">[show]</a></h3><hr/></div>';
		echo $this->staffM->leaveTableDisplay($allcancelled, 'allcancelled');
		echo '<br/><br/>';
	}
	
} ?>
</div>
<script type="text/javascript">
	function showTbl(tbl, p){
		$('#tbl'+tbl).toggleClass('hidden');
		$(p).find('a').text($(p).find('a').text() == '[show]' ? '[hide]' : '[show]'); 
	}
</script>