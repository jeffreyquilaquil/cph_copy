<?php
	$timeOption = '<option></option>';
	$arrCat = array();
	$timeArr = array();
	$weekArr = $this->config->item('weekdayArray');
	
	foreach($customTime AS $ct){
		if($ct->category==0) $arrCat[$ct->timeID]['name'] = $ct->timeName;
		else $arrCat[$ct->category][$ct->timeID] = $ct->timeValue;
		$timeArr[$ct->timeID] = $ct->timeValue;
	}
	
	foreach($arrCat AS $kvals){
		$timeOption .= '<optgroup label="'.$kvals['name'].'">';
		foreach($kvals AS $k=>$v){
			if($k!='name')
				$timeOption .= '<option value="'.$k.'">'.$v.'</option>';
		}						
		$timeOption .= '</optgroup>';
	}
?>
<h3>Set Schedule for <?= ((isset($info->fname))?$info->fname.' '.$info->lname:'') ?></h3>
<hr/>
<table class="tableInfo">
	<tr>
		<td width="30%">What do you want to do?</td>
		<td colspan=2>
			<input type="hidden" id="choosenbtn" value=""/>
			<button class="btnclass chobtn" onClick="choose('time', this)">Choose from Predefined Time</button><br/>
			<button class="btnclass chobtn" onClick="choose('sched', this)">Choose from Predefined Schedules</button><br/>
			<button class="btnclass chobtn" onClick="choose('add', this)">Add Custom Recurring Schedule</button>
		</td>
	</tr>
	<tr class="act actmain hidden">
		<td>Effective Date</td>
		<td>From <input type="text" class="padding5px datepick"/></td>
		<td>To <input type="text" class="padding5px datepick"/> <span class="colorgray">Leave empty if indefinite</span></td>
	</tr>
	<tr class="act ptime hidden">
		<td>Predefined Times</td>
		<td colspan=2>
		<select class="forminput" id="ptimes">
			<?php echo $timeOption; ?>
		</select>
		</td>
	</tr>
	<tr class="act ptime hidden">
		<td>Is Saturday Included?</td>
		<td colspan=2><input type="checkbox" name="saturday"/></td>
	</tr>
	<tr class="act ptime hidden">
		<td>Is Sunday Included?</td>
		<td colspan=2><input type="checkbox" name="sunday"/></td>
	</tr>
	
	<tr class="act psched hidden">
		<td>Predefined Schedules</td>
		<td colspan=2>
		<select class="forminput" id="psched">
			<option></option>
		<?php
			foreach($customSched AS $cs){
				echo '<option value="'.$cs->custschedID.'">'.$cs->schedName.'</option>';
			}
		?>
		</select>		
		</td>
	</tr>
	<tr class="act psched hidden">
		<td colspan=3>
	<?php
		foreach($customSched AS $c2){
			echo '<table id="sched_'.$c2->custschedID.'" class="tableInfo schedTable hidden"><tr class="trlabel" align="center">';
				foreach($weekArr AS $w){
					echo '<td>'.ucfirst($w).'</td>';
				}
				echo '</tr><tr align="center">';
				
				foreach($weekArr AS $w){
					echo '<td>'.((isset($timeArr[$c2->$w]))?$timeArr[$c2->$w]:'----------').'</td>';
				}					
			echo '</tr></table>';
		}
	?>	
		</td>
	</tr>
	
	<tr class="act addS hidden">
		<td>Custom Schedule Label</td>
		<td colspan=2><input type="text" class="forminput"/></td>
	</tr>
	<tr class="act addS hidden">
		<td>Every</td>
		<td colspan=2>
			<select id="schedType" class="forminput" style="width:30%;">
				<option value=0></option>
				<option value=1>First</option>
				<option value=2>Second</option>
				<option value=3>Third</option>
				<option value=4>Fourth</option>
				<option value=5>Last</option>
			</select>
			&nbsp;&nbsp;&nbsp;(Optional. Select an option if specific day of the month)
		</td>
	</tr>
	<tr class="act addS hidden">
		<td>Schedule</td>
		<td colspan=2>
			<table width="100%">
			<?php
				foreach($weekArr AS $sq){
					echo '<tr>
						<td style="width:80px;">'.ucfirst($sq).'</td>
						<td><select name="'.$sq.'" class="forminput">
								'.$timeOption.'
							</select>
						</td></tr>';
				}
			?>
			</table>
		</td>
	</tr>
	
	<tr class="act actmain hidden">
		<td><br/></td>
		<td colspan=2><button class="btnclass" id="submit">Submit</button></td>
	</tr>
	
</table>

<script type="text/javascript">
	function choose(choosen, t){		
		$('.chobtn').removeAttr('disabled');
		$(t).attr('disabled', 'disabled');
		
		$('.act').addClass('hidden');
		if(choosen!='') $('.actmain').removeClass('hidden');
		
		$('#choosenbtn').val(choosen);
		if(choosen=='time'){
			$('.ptime').removeClass('hidden');
		}else if(choosen=='sched'){
			$('.psched').removeClass('hidden');
		}else{
			$('.addS').removeClass('hidden');
		}
	}
</script>