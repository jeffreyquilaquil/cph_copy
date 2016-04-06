<?php 
	echo '<h3>'.date('l, F d, Y', strtotime($logInfo->logDate)).'</h3><hr/>';
	
	if(isset($_GET['edit'])){
		$id = $_GET['edit'];
		
		if($_GET['edit']=='add'){
			$typeval = '';
		}else{
			$typeval = $allLogs[$id][0];
		}	
		
?>		
	<div id="divEditLog">
	<form action="" method="POST">
	<?php
		if(empty($typeval)){
			echo '<b>Add Log</b>';
		}else{
			echo '<b>Edit '.$typeval.' from '.date('F d, Y h:i a', strtotime($allLogs[$id][1])).'</b>';
		}
	?>
		
		<br/><br/>
		TO <select name="type" required class="padding5px">
			<option value="timeIn" <?= (($typeval=='timeIn')?'selected="selected"':'') ?>>Time In</option>
			<option value="timeOut" <?= (($typeval=='timeOut')?'selected="selected"':'') ?>>Time Out</option>
			<option value="breakIn" <?= (($typeval=='breakIn')?'selected="selected"':'') ?>>Break In</option>
			<option value="breakOut" <?= (($typeval=='breakOut')?'selected="selected"':'') ?>>Break Out</option>
		</select>
		
		<?= $this->textM->formfield('text', 'datetext', ((empty($typeval))?date('F d, Y', strtotime($logInfo->logDate)):date('F d, Y', strtotime($allLogs[$id][1]))), 'datepick padding5px');  ?>
		<?php
			if(isset($allLogs[$id][1])){
				$hour = date('h', strtotime($allLogs[$id][1]));
				$min = date('i', strtotime($allLogs[$id][1]));		
				$ap = date('a', strtotime($allLogs[$id][1]));	
			}else{
				$hour = '';
				$min = '';
				$ap = '';
			}
				
		?>		
		<select name="hh" required class="padding5px">
			<option value="">HH</option>
		<?php
			for($f=1; $f<=12; $f++){
				if($f<10) $v = '0'.$f;
				else $v = $f;
				
				echo '<option value="'.$v.'" '.(($hour==$v)?'selected="selected"':'').'>'.$v.'</option>';
			}
		?>
		</select>
		
		<select name="mm" required class="padding5px">
			<option value="">MM</option>
		<?php
			for($g=0; $g<=59; $g++){
				if($g<10) $h = '0'.$g;
				else $h = $g;
				
				echo '<option value="'.$h.'" '.(($min==$h)?'selected="selected"':'').'>'.$h.'</option>';
			}
		?>
		</select>
		
		<select name="ampm" required class="padding5px">
			<option value="am" <?= (($ap=='am')?'selected="selected"':'') ?>>am</option>
			<option value="pm" <?= (($ap=='pm')?'selected="selected"':'') ?>>pm</option>
		</select>
		<?php
			echo $this->textM->formfield('textarea', 'reason', '', 'forminput', 'Type reason here...', 'required rows=5');
			echo $this->textM->formfield('hidden', 'prevVal', ((isset($allLogs[$id][1]))?$allLogs[$id][1]:''));
			echo  $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
			echo  '&nbsp;&nbsp;'.$this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'onClick="window.location.href=\''.$this->config->base_url().'timecard/viewalllogs/'.$logInfo->tlogID.'.\'"');
		?>
	</form>
	</div>
<?php }else{
	if(count($allLogs)>0){
		if($this->access->accessFullHR==true)
			echo '<b>ALL LOGS &nbsp;&nbsp;<a href="'.$this->config->base_url().'timecard/viewalllogs/'.$logInfo->tlogID.'/?edit=add">+ Add Log</a></b>';
		else echo '<b>ALL LOGS</b>';
		
		echo '<div id="divLogInfo">';
			echo '<table class="tableInfo">';
			foreach($allLogs AS $k=>$a){
				echo '<tr>';
					echo '<td width="30%">'.$logText[$a[0]].'</td>';
					echo '<td>';
						echo date('h:i a', strtotime($a[1]));
					echo '</td>';
					
					if($this->access->accessFullHR==true)
						echo '<td><a href="'.$this->config->base_url().'timecard/viewalllogs/'.$logInfo->tlogID.'/?edit='.$k.'">Edit</a></td>';
					else echo '<td><br/></td>';
					
				echo '</tr>';
			}
			echo '</table>';
		echo '</div><br/>';
	}
	
	
	$shadow = json_decode($logInfo->note, true);
		
	if(!empty($shadow)){
		foreach ($shadow as $key => $val) {
			$edited[$key] = $val['dateEdited'];
		}
		array_multisort($edited, SORT_DESC, $shadow); //sorting array
		
		echo '<h3>HISTORY</h3>';
		
		//echo '<pre>'; print_r($shadow); echo '</pre>';
		
		if($logInfo->requestUpdate==1 && $this->access->accessFullHR==true){
			echo '<button id="btnrequestupdate" class="btnclass btngreen" onClick="showmessage()">Please click here if done with requested update from staff</button>';
			echo '<div id="sendmessage" class="padding5px hidden">';
			echo '<form action="" method="POST">';
				echo '<b>Send message to '.$logInfo->fname.'</b>';
				echo $this->textM->formfield('textarea', 'message', '', 'forminput', 'Type message here...', 'rows=6 required');
				echo $this->textM->formfield('hidden', 'email', $logInfo->email);
				echo $this->textM->formfield('hidden', 'submitType', 'reqUpdate');
				echo $this->textM->formfield('submit', '', 'Submit & Update', 'btnclass btngreen');
				echo $this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'onClick="hideMessage()"');
			echo '</form>';
			echo '</div>';
		}
		
		foreach($shadow AS $s){
			echo '<div class="loghistory '.$s['type'].'">';
				echo '<b>'.date('d M Y h:i a', strtotime($s['dateEdited'])).' by '.$s['by'].'</b><br/>';
				if($s['type']=='updaterequest'){
					echo 'Requested Update<br/>';
					echo '<i><b>Message:</b></i> '.nl2br($s['reason']).'<br/>';
					if(!empty($s['docs'])){
						echo '<i><b>Supporting Documents</b></i><br/>';
						$xx = explode('|', $s['docs']);
						foreach($xx AS $x){
							echo '<a target="_blank" href="'.$this->config->base_url().$uploadDir.$x.'">'.$x.'</a><br/>';
						}
					}					
				}else{
					if(empty($s['prevVal'])){
						echo 'Added '.$logText[$s['type']].' '.$s['newVal'].'<br/>';
					}else{
						echo 'Edited '.$logText[$s['type']].'<br/>';
						echo 'From '.$s['prevVal'].' to '.$s['newVal'].'<br/>';
					}
					echo '<i>Note:</i> '.nl2br($s['reason']);
				}
				
			echo '</div>';
		}
	}	
} ?>

<?php 	
	echo '<pre>';
	//print_r($allLogs);
	echo '</pre>';
?>

<script type="text/javascript">
	function showmessage(){
		$('.loghistory').addClass('hidden');
		$('#btnrequestupdate').addClass('hidden');
		$('#sendmessage').removeClass('hidden');
	}
	function hideMessage(){
		$('.loghistory').removeClass('hidden');
		$('#btnrequestupdate').removeClass('hidden');
		$('#sendmessage').addClass('hidden');
	}
</script>