<h3>Edit Schedule of <?= $row->name ?></h3>
<hr/>
<form action="" method="POST" onSubmit="return validateForm();">
<table class="tableInfo">
	<tr>
		<td width="20%">Start Date</td>
		<td><input id="startdate" type="text" class="forminput" value="<?= date('F d, Y', strtotime($row->effectivestart)) ?>" disabled/></td>
	</tr>
	<tr>
		<td>End Date</td>
		<td><input id="enddate" type="text" class="forminput datepick" name="enddate" required /></td>
	</tr>	
	<tr>
		<td><br/></td>
		<td>
			<input type="hidden" name="schedID" value="<?= $row->schedID ?>"/>
			<input type="submit" value="Update" class="btnclass"/> <input type="button" class="btnclass" value="Cancel" onClick="parent.$.colorbox.close();"/>
		</td>
	</tr>	
	<tr>
		<td colspan=2>
		<?php
			$weekArr = $this->textM->constantArr('weekdayArray');
			$timeArr = array();
			$stime = array();
			foreach($schedTimes AS $t):
				if($t->category==0)
					$timeArr[$t->timeID]['name'] = $t->timeName;
				else
					$timeArr[$t->category][$t->timeID] = $t->timeValue.'|'.$t->timeName;
				$stime[$t->timeID] = $t->timeValue;
			endforeach;
			
			echo '<br/><h3>Current recurring schedule</h3>';
			echo '<table class="tableInfo tacenter">';
				echo '<tr class="trlabel">';
					foreach($weekArr AS $w) echo '<td>'.ucfirst($w).'</td>';
				echo '</tr>';
				echo '<tr>';
					foreach($weekArr AS $w) 
					echo '<td>'.(($row->$w!=0)?$stime[$row->$w]:'').'</td>';
				echo '</tr>';
			echo '</table>';
		?>
		</td>
	</tr>
</table>
</form>

<script type="text/javascript">
	function validateForm(){
		if($('#startdate').val() > $('#enddate').val()){
			alert('Invalid. End date is before start date.')
			return false;
		}else{
			return true;
		}
	}
</script>