<h2>Manage Timecard - Unpublished Logs</h2>
<hr/>
<?php
	if(count($dataUnpublished)==0){
		echo '<p>No unpublish logs.</p>';
	}else{
		$pubByDate = array();
		foreach($dataUnpublished AS $d){
			$pubByDate[$d->logDate][] = $d;
		}
		
		foreach($pubByDate AS $d8=>$dbyd){
			echo '<table class="tableInfo">';
				echo '<tr class="trlabel"><td>'.date('F d, Y', strtotime($d8)).' ('.count($dbyd).')</td></tr>';
				echo '<tr><td><ul>';
					foreach($dbyd AS $d){
						echo '<li><a href="'.$this->config->base_url().'timecard/'.$d->empID_fk.'/viewlogdetails/?d='.$d->logDate.'" class="iframe">'.$d->name.'</a></li>';
					}
				echo '</ul></td></tr>';
			echo '</table>';
		}
		
	}
?>

