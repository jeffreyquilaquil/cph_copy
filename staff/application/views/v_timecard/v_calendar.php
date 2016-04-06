<div style="margin-top:10px;">
<?php
	if($this->access->accessFullHR==true){
		echo '<a href="'.$this->config->base_url().'schedules/setschedule/'.$visitID.'/" class="iframe"><button class="btnclass btngreen">+ Add Schedule</button></a><hr/>';
	}
		
			
	$this->load->view('includes/templatecalendar');	
	
	if(count($schedData)>0){
		echo '<br/><h3>Schedule History</h3>';
	?>
		<table class="tableInfo">
			<thead>
				<tr class="trhead">
					<td width="30%">Effective Date</td>
					<td>Summary</td>
					<td><?= (($this->access->accessFullHR===true)?'Actions':'<br/>') ?></td>
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
						
						if($sd->workhome==1) echo '<span class="errortext">Work from home</span>';
					echo '</td>';
					
					if($this->access->accessFullHR && $sd->effectiveend=='0000-00-00') 
						echo '<td><button class="iframe" href="'.$this->config->base_url().'schedules/editschedule/'.$sd->schedID.'/">Edit</button></td>';
					else echo '<td><br/></td>';
					
				echo '</tr>';
			}
		?>
		</table>	
	<?php } ?>
</div>

<script type="text/javascript">
	function checkRemove(d, sched, iscustom){
		txt = '';
		if(iscustom==1) txt = 'Are you sure you want to remove this custom schedule?\nRemoving this schedule will inherit current recurring schedule if set.';
		else txt = 'Are you sure you want to remove this schedule?';
		
		if(txt != ''){
			if(confirm(txt)){
				sumpay = '?id=<?= $visitID ?>&date='+d+'&sched='+sched+'&custom='+iscustom;
				$.colorbox({iframe:true, href:'<?= $this->config->base_url().'schedules/removeSchedule/' ?>'+sumpay, width:"990px", height:"600px" });
			}
		}
	}
</script>