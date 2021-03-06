<?php
	$allLogsText = '';
	$logtimein = '';
	$breakstaken = 0;
	$breakout = '';
	$breakouttext = '';
	$breaktext = '';
	$logtimeout = '';
	$islate = false;
	
	$breaknum = 1;
	
		
	if(!empty($allLogs)){
		$allLogsText .= '<table width="25%" class="hidden tblalltimein">';
		foreach($allLogs AS $a){
			if($a->logtype=='D' || $a->logtype=='E'){
				if($breaknum%2!=0) $logtypeText = 'Start Break';
				else $logtypeText = 'End Break';
			}else $logtypeText = $logtypeArr[$a->logtype];
			
			$allLogsText .= '<tr class="border-bottom-black"><td>'.$logtypeText.'</td><td align="center">'.date('h:i a', strtotime($a->logtime)).'</td></tr>';
		
			if(empty($logtimein) && $a->logtype=='A'){ //TIME IN
				$logtimein = $a->logtime;
			}
			
			//FOR BREAKS
			if(!empty($logtimein) && ($a->logtype=='D' || $a->logtype=='E')){
				if($breaknum%2==0){
					$breakstaken += strtotime($a->logtime) - strtotime($breakout);
					$breakouttext = '';
				}else{
					$breakout = $a->logtime;
					$breakouttext = $a->logtime;
				}
				$breaknum++;
			}
			
			if($a->logtype=='Z'){ //TIME OUT
				$logtimeout = $a->logtime;
			}
		}
		
		
		
		$allLogsText .= '</table>';		
	}
		
	$schedT = '';
	if(isset($schedToday['sched']))
		$schedT .= $schedToday['sched'];
?>


<table width="100%" border=1 cellpadding=0 cellspacing=0>
	<tr>
		<td>
		<div style="padding:10px;">
			<h3><?php
				if(isset($schedToday['schedDate']) && $schedToday['schedDate']!=date('Y-m-d')) echo date('l, F d, Y', strtotime($schedToday['schedDate']));
				else echo date('l, F d, Y');
			?></h3>
			<?php
				if(empty($schedT) && !isset($schedToday['offset'])) echo 'Today\'s Schedule: <b>NONE</b><br/>';
				else if(!empty($schedT)) echo 'Today\'s Schedule: <b>'.$schedT.'</b><br/>';
				
				if(isset($schedToday['offset']))
					if( is_array($schedToday['offset']) ){
						foreach( $schedToday['offset'] as $offset ){
							echo 'Offset Schedule: <b>'.$offset.'</b><br/>';		
						}	
					}
					
								
				if(!empty($schedT) && $schedT!='On Leave' && empty($logtimein)){
					if($visitID==$this->user->empID) echo '<span class="errortext weightbold">'.(($visitID==$this->user->empID)?'You':$row->fname.'\'').' do not have time in yet.</span> ';
					else echo '<span class="errortext weightbold">No time in yet.</span> <br/>';
				}else if(!empty($logtimein)){ //IF LOGGED IN
					echo (($visitID==$this->user->empID)?'You':$row->fname).' clocked in at <b>'.date('h:i a', strtotime($logtimein)).'</b>.';
					
					if(isset($schedArr['start'])){
						$strstart = strtotime($schedArr['start']);
						$strlogtime = strtotime($logtimein);
						
						if($strlogtime > strtotime($schedArr['start'].' +1 minute')){
							$diff = $strlogtime - $strstart;
							$ltype = 'LATE';
							$islate = true;
						}else if(date('H:i', $strstart) != date('H:i', $strlogtime)){
							$diff = $strstart - $strlogtime;
							$ltype = 'EARLY';
						}
						
						if(isset($diff)) 
							echo ' This is '.$this->textM->convertTimeToMinHours($diff).' <b class="errortext">'.$ltype.'</b>.';
					}
					echo '<br/>';
				}
				
				
				//BREAKS
				if($breakstaken>0){
					$breaktext = $this->textM->convertTimeToMinHours($breakstaken);
					echo 'Breaks Taken: '.$breaktext;
					if($breakstaken>$this->timeM->timesetting('overBreak')) echo ' <b class="errortext">OVER BREAK</b>';
					echo '<br/>';
				} 
				
				if(!empty($breakouttext)) echo '<span class="errortext">Pending End Break. Start break time: <b>'.date('h:i a', strtotime($breakouttext)).'</b></span><br/>';
			
				///CHECK IF LOGGED OUT
				if(!empty($logtimeout)){
					echo 'You clocked out at <b>'.date('h:i a', strtotime($logtimeout)).'</b>.';
					
					if(isset($schedArr['end'])){
						$strend = strtotime($schedArr['end']);
						$strlogout = strtotime($logtimeout);
						
						if(isset($strlogout) && $strlogout < $strend){
							$diffEnd = $strend - $strlogout;
							echo ' This is '.$this->textM->convertTimeToMinHours($diffEnd).' <b class="errortext">EARLY OUT</b>.<br/>';
						}
						
					}
				} 
				
				if(!empty($allLogsText)){
					echo '<br/><br/>
						<b>TODAY\'S LOGS</b> <button id="btnshowall" onClick="$(\'.tblalltimein\').removeClass(\'hidden\'); $(this).hide();">Show</button>
						<button class="hidden tblalltimein" onClick="$(\'.tblalltimein\').addClass(\'hidden\'); $(\'#btnshowall\').show();">Hide</button>';
					echo $allLogsText;
				}
		?>
		</div>
		</td>
	<?php
		if($visitID==$this->user->empID){
			if(isset($schedToday['workhome']) && empty($logtimein)){
				echo '<td width="30%" valign="middle" align="center">';
					echo '<button class="btnclass btngreen" onClick="goonbreak(this, \'A\')">CLOCK IN</button>';
				echo '</td>';
			}else if((!empty($logtimein) && empty($logtimeout))){
				echo '<td width="30%" valign="middle" align="center">';
					if(empty($breakouttext)){
						echo '<button class="btnclass btngreen" onClick="goonbreak(this, \'D\')">TAKE A BREAK</button><br/>
						<span class="errortext">If you TAKE A BREAK using biometric, please wait for few minutes for it to reflect here.</span>';
						
						if(isset($schedToday['workhome'])) echo '<br/><br/><button class="btnclass" onClick="goonbreak(this, \'Z\')">CLOCK OUT</button>';
						
					}else echo '<button class="btnclass btnred" onClick="goonbreak(this, \'E\')">GO BACK TO WORK</button>';
				echo '</td>';
			}
		}
	?>	
	</tr>
</table>
<?php

	$data = array();
	$ttext = '';
	if(date('Y-m', strtotime($currentDate)) == date('Y-m', strtotime($today))){
		if(!empty($schedT) && $schedT!='On Leave' && empty($logtimein)){
			$ttext = 'No time in yet';
		}if(!empty($logtimein) && empty($logtimeout)){
			$ttext = 'Shift in progress';		
		}else if(!empty($logtimein)){
			if($islate) $ttext .= '<span class="errortext">LATE</span><br/>';
			$ttext .= 'In: '.date('h:i a', strtotime($logtimein)).'<br/>';
			if(!empty($logtimeout)) $ttext .= 'Out: '.date('h:i a', strtotime($logtimeout)).'<br/>';
		}
		
		if(!empty($ttext)) $data['dayArr'] = $dayArr + array(date('j') => '<div style="text-align:left; padding:5px;">'.$ttext.'</div>');
	}
	
	echo '<div id="divtimelog">';
		$this->load->view('includes/templatecalendar'); 
	echo '</div>';
?>

<script type="text/javascript">
	function goonbreak(t, hihi){
		$(t).attr('disabled', 'disabled');
		displaypleasewait();
		$.post('<?= $this->config->item('career_uri') ?>', {submitType:'recordbreak', logtype:hihi},function(){
			if(hihi=='D') alert('Your start break has been recorded.\nPlease don\'t forget to Go Back To Work later.\nEnjoy your break!');
			else if(hihi=='A') alert('Your clock in has been recorded.');
			else if(hihi=='Z') alert('Your clock out has been recorded.');
			else alert('Your break in has been recorded.');
			location.reload();
		})
	}
</script>