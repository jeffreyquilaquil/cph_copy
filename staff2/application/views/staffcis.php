<h2>Staff CIS</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Newly Requested CIS</li>
	<li class="tab-link" data-tab="tab-2">Approved CIS</li>
	<li class="tab-link" data-tab="tab-3">Done</li>
</ul>
<div id="tab-1" class="tab-content current">		
	<?php
		echo $this->staffM->displaycis($pending, 0);
	?>	
</div>

<div id="tab-2" class="tab-content">
	<?php
		echo $this->staffM->displaycis($approved, 1);
	?>
</div>

<div id="tab-3" class="tab-content">
	<?php
		echo $this->staffM->displaycis($done, 3);
	?>
</div>