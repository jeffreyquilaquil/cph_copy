<h2>Staff CIS</h2>
<hr/>

<?php
	$tmp = ['pending' => 'New Requested CIS', 'approved' => 'Approved CIS', 'done' => 'Done', 'disapproved' => 'Disapproved', 'supervisor' => 'Generated CIS', 'cancelled' => 'Cancelled'];

	echo '<ul class="tabs">';
	$cnt = 1;
	foreach( $tmp as $tab_key => $tab_label ){
		if( isset(${$tab_key}) ){
			echo '<li class="tab-link '.(($cnt==1 OR $cnt==5)?'current':'').'" data-tab="tab-'.$cnt.'">'.$tab_label.'('.count(${$tab_key}).')</li>';	
		}
		
		$cnt++;
	}
	
	echo '</ul> ';

	$cnt = 1;
	foreach( $tmp as $tab_key => $tab_label ){
		if( isset(${$tab_key})){
		echo '<div id="tab-'.$cnt.'" class="tab-content '.(($cnt==1 OR $cnt==5)?'current':'').'">';
		echo '<br/>';
		
			echo $this->textM->displaycis( ${$tab_key}, 0 );
		

		echo '</div>';
		}
		$cnt++;
	}
	?>
