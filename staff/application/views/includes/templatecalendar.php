<?php
	$dateToday = date('Y-m-d', strtotime($today));
	$dayToday = date('j', strtotime($today));
	$monthToday = date('m', strtotime($today));
	$yearToday = date('Y', strtotime($today));
	$lastDay = date('t', strtotime($dateToday));
	$weekStart = date('w', strtotime(date('Y-m-01', strtotime($today))));
	
	$monthArr = $this->textM->constantArr('monthArray');
	$weekArr = $this->textM->constantArr('weekdayArray');
	
	if(!isset($datelink))
		$datelink = $_SERVER['REQUEST_URI'];
	$yearMinus = date('Y', strtotime($dateToday.' -1 year'));
	$yearPlus = date('Y', strtotime($dateToday.' +1 year'));
?>
<table border=0 class="calendartbl">
	<tr class="calendarheader" align="center">
		<td class="cpointer" onClick="location.href='<?= $datelink.$yearMinus.'-'.$monthToday.'-'.$dayToday.'/' ?>'"><< <?= $yearMinus ?></td>
	<?php	
		for($m=1; $m<=12; $m++){
			echo '<td class="cpointer '.(($m==$monthToday)?'trhead':'').'" onClick="location.href=\''.$datelink.$yearToday.'-'.$m.'-'.$dayToday.'/\'">'.strtoupper(date('M', strtotime($yearToday.'-'.$m.'-'.$dayToday.''))).'</td>';
		}
	?>
		<td class="cpointer" onClick="location.href='<?= $datelink.$yearPlus.'-'.$monthToday.'-'.$dayToday.'/' ?>'"><< <?= $yearPlus ?></td>
	</tr>
</table>

<table border=0 class="calendartbl">	
<?php
	//JULY 2015
	echo '<tr class="calendarheader" style="border-bottom:2px solid #000;">';
		echo '<td colspan=7><div style="padding-left:10px;"><b>'.strtoupper(date('F Y', strtotime($dateToday))).'</div></td>';
	echo '<tr>';

	//SUNDAY, MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY, SATURDAY
	echo '<tr class="calendarheader trhead" align="center">';	
	foreach($weekArr AS $w){
		echo '<td width="14.28%">'.strtoupper($w).'</td>';
	}
	echo '</tr>';

	//DATES PER DAY
	echo '<tr>';
		
		for($i=1, $day=1; $day<=$lastDay; $i++){
			if($weekStart>=$i) echo '<td><br/></td>';
			else{				
				echo '<td>';
					//if there is link on dayEditOptionArr
					if(isset($dayEditOptionArr[$day])){
						echo '<a href="'.$dayEditOptionArr[$day].'" class="iframe dayEdit">
								<div class="daynum '.(($day==$dayToday)?'istoday':'').'">'.$day.'</div>
							</a>';
					}else{
						echo '<div class="daynum '.(($day==$dayToday)?'istoday':'').'">'.$day.'</div>';
					}					
					
					//CONTENT FROM $dayArr array
					echo '<div class="daycontent">'.((isset($dayArr[$day]))?$dayArr[$day]:'').'</div>';

					
				echo '</td>';
				
				$day++;
			} 

			if($i%7==0) echo '</tr><tr>';
		}
	
		
	echo '</tr>';
	
	
?>
</table>