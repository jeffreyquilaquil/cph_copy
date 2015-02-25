<h2>Staff Leaves</h2>
<hr/>

<ul class="tabs">
<?php
	$current = 'tab-1';
	if($this->user->level>0 || $this->user->is_supervisor==1 || count(array_intersect($this->myaccess,array('full','hr')))>0){
		echo '<li class="tab-link current" data-tab="tab-1">On-Leave Today ('.count($tquery).')</li>';
		echo '<li class="tab-link" data-tab="tab-2">Pending Immediate Supervisor\'s Approval ('.( count($imquery) + count($imcancelledquery) ).')</li>';
	}
	if(count(array_intersect($this->myaccess,array('full','hr')))>0){
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
	echo $this->staffM->leaveTableDisplay($tquery);
} ?>
</div>

<div id="tab-2" class="tab-content <?= (($current=='tab-2')?'current':'') ?>">
<br/>
<?php if(count($imquery)==0 && count($imcancelledquery)==0){
	echo 'No pending leaves for approval.';
}else{ 
	if(count($imquery)>0){
		echo '<h3>Pending Leaves for Approval</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($imquery);
		echo '<br/><br/>';
	}
	if(count($imcancelledquery)>0){
		echo '<h3>Pending Cancelled Leaves for Approval</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($imcancelledquery);
		echo '<br/><br/>';
	}
	
} ?>
</div>

<div id="tab-3" class="tab-content <?= (($current=='tab-3')?'current':'') ?>">
<br/>
<?php if(count($hrquery)==0){
	echo 'No pending leaves for approval.';
}else{ 
	echo $this->staffM->leaveTableDisplay($hrquery);
} ?>
</div>

<div id="tab-4" class="tab-content <?= (($current=='tab-4')?'current':'') ?>">
<br/>
<?php if(count($allpending)==0 && count($allapproved)==0 && count($allapprovedNopay)==0 && count($alldisapproved)==0 && count($allcancelled)==0){
	echo 'No leaves on file.';
}else{ 
	if(count($allpending)>0){
		echo '<h3>All Pending Leaves for Approval</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($allpending);
		echo '<br/><br/>';
	}
	if(count($allapproved)>0){
		echo '<h3>All Approved WITH Pay Leaves</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($allapproved);
		echo '<br/><br/>';
	}
	if(count($allapprovedNopay)>0){
		echo '<h3>All Approved WITHOUT Pay Leaves</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($allapprovedNopay);
		echo '<br/><br/>';
	}
	if(count($alldisapproved)>0){
		echo '<h3>All Disapproved Leaves</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($alldisapproved);
		echo '<br/><br/>';
	}
	if(count($allcancelled)>0){
		echo '<h3>All Cancelled Leaves</h3><hr/>';
		echo $this->staffM->leaveTableDisplay($allcancelled);
		echo '<br/><br/>';
	}
	
} ?>
</div>