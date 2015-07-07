<?php 
	$this->load->view('includes/header_timecard'); 
?>
<div style="margin-top:10px;">
	<button class="btnclass iframe" href="<?= $this->config->base_url().'schedules/setschedule/'.$visitID.'/' ?>">+ Add Schedule</button>
<?php
	if(count($schedData)==0){
		echo '<p class="errortext">No schedule yet. Please add schedule.</p>';
	}else{
		echo '<br/><br/><h3>Schedule History</h3>';
?>
		<table class="tableInfo">
			<thead>
				<tr class="trhead">
					<td width="30%">Effective Date</td>
					<td>Summary</td>
					<td>Actions</td>
				</tr>
			</thead>
		<?php
			$wShort = $this->textM->constantArr('weekShortArray');
			foreach($schedData AS $sd){
				if($sd->effectiveend=='0000-00-00') echo '<tr bgcolor="#ffb2b2">';
				else echo '<tr>';
				
					echo '<td>'.$sd->effectivestart.' '.(($sd->effectiveend!='0000-00-00')?'until '.$sd->effectiveend:'in perpetuity').'</td>';
					echo '<td>';
						$arr = array();
						foreach($wShort AS $k=>$v){							
							if($sd->$k!=0){
								$vv = $timeArr[$sd->$k];
								$arr[$vv] = ((isset($arr[$vv]))?$arr[$vv].',':'').ucfirst($v);
							}								
						}
						foreach($arr AS $a=>$v){
							echo '<b>'.$a.'</b> - <i>'.$v.'</i><br/>';
						}
					echo '</td>';
					
					if($sd->effectiveend=='0000-00-00') 
						echo '<td><button class="iframe" href="'.$this->config->base_url().'schedules/editschedule/'.$sd->schedID.'/">Edit</button></td>';
					else echo '<td></td>';
					
				echo '</tr>';
			}
		?>
		</table>
<?php
		echo '<br/><h3>Schedules</h3><hr/>';
		$this->load->view('includes/templatecalendar');	
	}
?>
		
</div>