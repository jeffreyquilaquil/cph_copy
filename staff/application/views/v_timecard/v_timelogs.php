<?php 
	$this->load->view('includes/header_timecard'); 	
	
	$today = date('Y-m-d');
	$dataArr['content'] = array(); //array of elements that will display on calendar template
?>

<table border=0 class="attendancetbl">
	<tr>
		<td>
	<?php
		if(!empty($schedToday)){
			echo 'Your schedule today ('.date('l, F d, Y').') is '.$schedToday.'.<br/>';
		}else{
			echo 'You are unscheduled today ('.date('l, F d, Y').')<br/>';
		}
								
		if(isset($timelog['timeIn'])){
			$inText = 'You clocked in at <b>'.date('h:i a', strtotime($timelog['timeIn'])).'</b>.';
			
			if(!empty($schedTodayArr)){
				if($timelog['timeIn']>$schedTodayArr['start']){	//check if late						
					$inText .= ' This is '.$this->textM->convertTimeToMinHours(strtotime($timelog['timeIn']) - strtotime($schedTodayArr['start'])).' <span class="errortext"><b>LATE</b></span>.';				
				}else if($timelog['timeIn']<$schedTodayArr['start']){ //check if in early			
					$inText .= ' This is '.$this->textM->convertTimeToMinHours(strtotime($schedTodayArr['start']) - strtotime($timelog['timeIn'])).' <span class="errortext"><b>EARLY</b></span>.';		
				}
			}
			echo $inText.'<br/>';
		}
		
		$cntBreaks = 0;
		if(isset($timelog['breaks'])){			
			$cntBreaks = count($timelog['breaks']);
			
			$btext = '';
			$secbreak = 0;
			for($cnb=0; $cnb<$cntBreaks; $cnb++){
				if($cnb%2==0){
					$btext .= date('H:i:s', strtotime($timelog['breaks'][$cnb]));
				}else{
					$btext .= ' - '.date('H:i:s', strtotime($timelog['breaks'][$cnb])).'<br/>';
					$secbreak += strtotime($timelog['breaks'][$cnb]) - strtotime($timelog['breaks'][$cnb-1]);
				}
			}
			
			if($cntBreaks%2 != 0) 
				$btext .= ' - <span style="color:red;">in progress</span><br/>';
			
			echo 'Breaks Taken: <b>('.trim($this->textM->convertTimeToMinHours($secbreak)).')</b><br/>'.$btext;
			
		}
		
		if(isset($timelog['timeOut'])){
			$outText = 'You clock out at <b>'.date('h:i a', strtotime($timelog['timeOut'])).'</b>.';
			if(!empty($schedTodayArr)){
				if($timelog['timeOut']>$schedTodayArr['end']){	//check if late				
					$outText .= ' This is '.$this->textM->convertTimeToMinHours(strtotime($timelog['timeOut']) - strtotime($schedTodayArr['end'])).' <span class="errortext"><b>LATE</b></span>.';				
				}else if($timelog['timeOut']<$schedTodayArr['end']){ //check if in early
					$outText .= ' This is '.$this->textM->convertTimeToMinHours(strtotime($schedTodayArr['end']) - strtotime($timelog['timeOut'])).' <span class="errortext"><b>EARLY</b></span>.';
				}
			}
			echo $outText.'<br/>';
		}
	
		if(!isset($timelog['timeIn']) && !empty($schedToday)){
			echo '<br/><i class="errortext"><b>Your clock in is not yet recorded!</b><br/><i>It might be that the records are not yet sync or you forgot to log in.</i><br/>';
		}else if(isset($timelog['timeIn']) && !isset($timelog['timeOut'])){
			if(!empty($schedTodayArr)){
				echo '<br/><i class="colorgray">';
					$secDiff = strtotime($schedTodayArr['end'])-strtotime(date('Y-m-d H:i:s'));
					if($secDiff>0) echo 'You would be leaving early by:';
					else echo 'You will be clocking out late by:';
					
					echo '<br/><b id="spanLeaving"><br/></b>';
				echo '</i>';
				
				
				if($secDiff<0){
					$secDiff = strtotime(date('Y-m-d H:i:s'))-strtotime($schedTodayArr['end']);
					echo '<span id="spanSign" class="hidden">-1</span>';
				}else{
					echo '<span id="spanSign" class="hidden">1</span>';
				}
					
				echo '<span id="secCnt" class="hidden">'.$secDiff.'</span>';
			}		
			
			
			echo '<tr><td>';
			
			if($cntBreaks%2==0){
				echo '<button class="btnclass" onClick="logRecorder(this, \'takeAbreak\', \'D\');">Take a Break</button>';
			}else{
				echo '<button class="btnclass" onClick="logRecorder(this, \'backToWork\', \'E\');">Go Back to Work</button>';
				$bOut = $timelog['breaks'][$cntBreaks-1];
				echo '<br/>Breaks taken: <span id="spanBreak"></span>';
				echo '<span id="spanBreakCnt" class="hidden">'.(strtotime(date('Y-m-d H:i:s'))-strtotime($bOut)).'</span>';
			}
			echo '<br/><span id="ct"></span>';
			
			echo '<br/><button class="btnclass" onClick="logRecorder(this, \'clockOut\', \'Z\');">Clock Out</button>';
			
			echo '</td></tr>';
				
		}
		
		if(!isset($timelog['timeIn']))
			echo '<tr><td><button class="btnclass" onClick="logRecorder(this, \'clockIn\', \'A\');">Clock In</button></td></tr>';
		
		
		//this is for calendar logs
		foreach($calendarLogs AS $c){
			$num = ltrim(date('d',strtotime($c->logDate)), '0');
			$dataArr['content'][$num] = 'IN: '.date('h:i a', strtotime($c->timeIn)).'<br/>'.
										'OUT: '.date('h:i a', strtotime($c->timeOut));
			if(!empty($c->breaks)){
				$breaks = explode('|', $c->breaks);
				$cntBreaks = count($breaks);
				if($cntBreaks>0){
					$dataArr['content'][$num] .= '<br/><br/>BREAKS: ';
					for($h=0; $h<$cntBreaks; $h++){
						
						if($h%2==0) $dataArr['content'][$num] .= '<br/>';
						else $dataArr['content'][$num] .= ' - ';
						
						$dataArr['content'][$num] .= date('h:i a', strtotime($breaks[$h]));
					}
				}	
			}
						
		}
	?>
		<!--You clocked in at 07:14 AM. This is 14 minutes late.<br/>
		Breaks Taken: 45 minutes and 55 seconds.<br/> -->
		</td>
	</tr>
	
</table>
<?php 
	$this->load->view('tc_calendartemplate', $dataArr);
?>

<script type="text/javascript">
	$(function(){
		if($("#spanLeaving").length>0){
			setInterval(function(){ 
				computeLeaving(); 
			}, 1000);
		}
		
		if($("#spanBreak").length>0){
			setInterval(function(){ 
				computeBreaks(); 
			}, 1000);
		}
	});
	
	function logRecorder(t, type, tval){
		$(t).attr('disabled', 'disabled');
		$.post('<?= $this->config->item('career_uri') ?>',{submitType:type, tval:tval},
		function(){
			alert('Recorded!');
			location.reload();
		});
	}
	
	function computeLeaving(){	
		txt = '';
		hiho = $('#secCnt').text();
		txt = convertTimeToMinHours(hiho);
		$("#spanLeaving").text(txt);
		screw = parseInt(hiho)-parseInt($('#spanSign').text());
		$('#secCnt').text(screw);		
	}
	
	function computeBreaks(){
		brktime = $('#spanBreakCnt').text();
		$("#spanBreak").text(convertTimeToMinHours(brktime));
		$('#spanBreakCnt').text(parseInt(brktime)+1);	
	}
	
	
	function convertTimeToMinHours(tdiff){
		tText = '';
		hours = Math.floor(tdiff/3600);
		remainder = tdiff - (hours * 3600);
		minutes = Math.floor(remainder/60);
		remainder = remainder - (minutes * 60);
		seconds = remainder;

		if(hours>0 && hours==1) tText += hours+' hour ';
		else if(hours>0 && hours>1) tText += hours+' hours ';

		if(minutes==1) tText += minutes+' minute ';
		else if(minutes>1) tText += minutes+' minutes ';
		
		if(seconds==1) tText += seconds+' second ';
		else if(seconds>1) tText += seconds+' seconds ';

		return tText;
	}
	
</script>