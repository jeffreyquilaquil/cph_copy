<?php $this->load->view('includes/header_searchemployee'); ?>

<h2>Unpublished Logs <?= ((count($dataUnpublished)>0)?'('.count($dataUnpublished).')':'') ?></h2>
<hr/>
<?php
	if(count($dataUnpublished)==0){
		echo '<p>No unpublish logs.</p>';
	}else{
		$pubByDate = array();
		foreach($dataUnpublished AS $d){
			$pubByDate[$d->slogDate][] = $d;
		}
		
		$date00 = '0000-00-00 00:00:00';
		$overBreak = $this->timeM->timesetting('overBreakTime');
		$late15 = $this->timeM->timesetting('over15mins');
				
		foreach($pubByDate AS $d8=>$dbyd){
			echo '<table class="tableInfo">';
				echo '<tr class="trlabel"><td colspan=4>'.date('l, F d, Y', strtotime($d8)).' ('.count($dbyd).') &nbsp;&nbsp;<a href="javascript:void(0);" onClick="toggleDisplay(\''.$d8.'\', this);" class="droptext">[Show]</a></td></tr>';
				echo '<tr class="trhead hidden show_'.$d8.'">
						<td width="30%">Name</td>
						<td width="25%">Schedule</td>
						<td width="40%">Time In/Out</td>
						<td>Details</td>
					</tr>';
				foreach($dbyd AS $d){
					$sched = '';
					$err = '';
					
					if($d->schedIn==$date00 && $d->schedOut==$date00) $sched = 'None';
					else $sched = date('h:i a', strtotime($d->schedIn)).' - '.date('h:i a', strtotime($d->schedOut));
					
					$timeOut = '';
					if($d->timeIn==$date00 && $d->timeOut==$date00) $err .= ' ABSENT,';
					else{
						if($d->timeIn!=$date00){
							$timeOut =  date('h:i a', strtotime($d->timeIn));
							if((strtotime($d->timeIn)-strtotime($d->schedIn)>$late15)) $err .= ' LATE,';
						} 
						if($d->timeOut!=$date00) $timeOut .=  ' - '.date('h:i a', strtotime($d->timeOut));
						else $err .= ' NO TIME OUT,';
						
						if($d->timeBreak>$overBreak) $err .= ' OVER BREAK,';
					}	
					
					
					
					echo '<tr class="hidden show_'.$d8.'">';
						echo '<td><a href="'.$this->config->base_url().'timecard/'.$d->empID_fk.'/timelogs/" target="_blank">'.$d->name.'</a></td>';
						echo '<td>'.$sched.'</td>';							
						echo '<td>';
							echo $timeOut;
							if(!empty($err)) echo '<span class="errortext">'.rtrim($err, ',').'</span>';
						echo '</td>';
						echo '<td><a href="'.$this->config->base_url().'timecard/'.$d->empID_fk.'/viewlogdetails/?d='.$d->slogDate.'" class="iframe"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>';							
					echo '</tr>';
				}
			echo '</table>';
		}
		
	}
?>

<script type="text/javascript">
	function toggleDisplay(d8, jun){
		if($(jun).text()=='[Show]'){
			$(jun).text('[Hide]');
			$('.show_'+d8).removeClass('hidden');
		}else{
			$(jun).text('[Show]');
			$('.show_'+d8).addClass('hidden');
		}
	}
</script>

