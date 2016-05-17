<h2>Staff CIS</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Newly Requested CIS (<?= count($pending) ?>)</li>
	<li class="tab-link" data-tab="tab-2">Approved CIS (<?= count($approved) ?>)</li>
	<li class="tab-link" data-tab="tab-3">Done (<?= count($done) ?>)</li>
	<li class="tab-link" data-tab="tab-4">Disapproved (<?= count($disapproved) ?>)</li>
</ul>
<div id="tab-1" class="tab-content current">	
	<br/>
	<?php
		echo $this->textM->displaycis($pending, 0);
	?>	
</div>

<div id="tab-2" class="tab-content">	
	<br/><i>These are approved CIS but not yet the effective date.</i><br/><br/>
	<?php
		echo $this->textM->displaycis($approved, 1);
	?>
</div>

<div id="tab-3" class="tab-content">
	<br/>
	<?php
		echo $this->textM->displaycis($done, 3);
	?>
</div>
<div id="tab-4" class="tab-content">
	<br/>
	<?php
		echo $this->textM->displaycis($disapproved, 2);
	?>
</div>