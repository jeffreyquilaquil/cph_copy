<?php
	$allLogsText = '';
	$logtimein = '';
	$breakstaken = 0;
	$breakout = '';
	$breakouttext = '';
	$breaktext = '';
	$logtimeout = '';
	$islate = false;
	
		
	if(!empty($allLogs)){
		$allLogsText .= '<table width="25%" class="hidden tblalltimein">';
		foreach($allLogs AS $a){
			$allLogsText .= '<tr class="border-bottom-black"><td>'.$logtypeArr[$a->logtype].'</td><td align="center">'.date('h:i a', strtotime($a->logtime)).'</td></tr>';
		
			if(empty($logtimein) && $a->logtype=='A') $logtimein = $a->logtime;
			if($a->logtype=='D' && empty($breakouttext)){
				$breakout = $a->logtime;
				$breakouttext = $a->logtime;
			}
			if($a->logtype=='E' AND !empty($breakout)){
				$breakstaken += strtotime($a->logtime) - strtotime($breakout);
				$breakouttext = '';
			}
			if($a->logtype=='Z' && empty($logtimeout)){
				$logtimeout = $a->logtime;
			}
		}
		$allLogsText .= '</table>';		
	}
		
	$schedT = '';
	if(isset($schedToday['sched']))
		$schedT = $schedToday['sched'];	
?>


<table width="100%" border=1 cellpadding=0 cellspacing=0>
	<tr>
		<td>
		<div style="padding:10px;">
			<h3><?= date('l, F d, Y') ?></h3>
			<?php
				echo 'Schedule today: <b>'.((empty($schedT))?'NONE':$schedT).'</b><br/>';
								
				if(!empty($schedT) && $schedT!='On Leave' && empty($logtimein)){
					if($visitID==$this->user->empID) echo '<span class="errortext weightbold">'.(($visitID==$this->user->empID)?'You':$row->fname.'\'').' do not have time in yet.</span>';
					else echo '<span class="errortext weightbold">No time in yet.</span>';
				}else if(!empty($logtimein)){
					echo (($visitID==$this->user->empID)?'You':$row->fname).' clocked in at <b>'.date('h:i a', strtotime($logtimein)).'</b>.';
					
					if(isset($schedArr['start']) && isset($schedArr['end'])){
						$strstart = strtotime($schedArr['start']);
						$strlogtime = strtotime($logtimein);
						
						if($strlogtime > $strstart){
							$diff = strtotime($logtimein) - strtotime($schedArr['start']);
							$ltype = 'LATE';
							$islate = true;
						}else{
							$diff = strtotime($schedArr['start']) - strtotime($logtimein);
							$ltype = 'EARLY';
						}
						echo ' This is '.$this->textM->convertTimeToMinHours($diff).' <b class="errortext">'.$ltype.'</b>.';
					}
					echo '<br/>';
				}
				
				if($breakstaken>0){					
					$breaktext = $this->textM->convertTimeToMinHours($breakstaken);
					echo 'Breaks Taken: '.$breaktext.'<br/>';
				} 
				
				if(!empty($breakouttext)) echo '<span class="errortext">Pending Break In. Break out time: <b>'.date('h:i a', strtotime($breakouttext)).'</b></span>';
			
				if(!empty($logtimeout)) echo 'You clocked out at <b>'.date('h:i a', strtotime($logtimeout)).'</b>.';
				
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
		if($visitID==$this->user->empID && !empty($logtimein) && empty($logtimeout)){
			echo '<td width="30%" valign="middle" align="center">';
				if(empty($breakouttext))
					echo '<button class="btnclass btngreen" onClick="goonbreak(this, \'D\')">TAKE A BREAK</button><br/>
					<span class="errortext">If you BREAK OUT using biometric, please wait for few minutes for it to reflect here.</span>';
				else
					echo '<button class="btnclass btnred" onClick="goonbreak(this, \'E\')">GO BACK TO WORK</button>';
			echo '</td>';
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
		$.post('<?= $this->config->item('career_uri') ?>', {submitType:'recordbreak', logtype:hihi},function(){
			if(hihi=='D') alert('Your break out has been recorded.\nPlease don\'t forget to Go Back To Work later.\nEnjoy your break!');
			else alert('Your break in has been recorded.');
			location.reload();
		})
	}
</script>