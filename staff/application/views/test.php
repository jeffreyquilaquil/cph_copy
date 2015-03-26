<style>
	.odd{ background-color:#ccc;}
</style>

<?php
foreach($cstaffs AS $cs){
	$cstaf[$cs->username] = '<td>'.$cs->title.'</td><td>'.$cs->dept.'</td><td>'.$cs->grp.'</td><td>'.$cs->subgrp.'</td>';
}

foreach($staffs AS $s){
	$access = $s->pageAccessCompany; if(!empty($s->pageAccessCompany)){ $access .= ','; }
	$access .= $s->pageAccessMisc; if(!empty($s->pageAccessMisc)){ $access .= ','; } 
	$access .= $s->pageAccessAcquisitions; if(!empty($s->pageAccessAcquisitions)){ $access .= ','; }  
	$access .= $s->pageAccessEditing; if(!empty($s->pageAccessEditing)){ $access .= ','; } 
	$access .= $s->pageAccessDesign; if(!empty($s->pageAccessDesign)){ $access .= ','; } 
	$access .= $s->pageAccessDesignStatus; if(!empty($s->pageAccessDesignStatus)){ $access .= ','; } 
	$access .= $s->pageAccessIllustrations; if(!empty($s->pageAccessIllustrations)){ $access .= ','; } 
	$access .= $s->pageAccessAudio;  if(!empty($s->pageAccessAudio)){ $access .= ','; }
	$access .= $s->pageAccessMusic; if(!empty($s->pageAccessMusic)){ $access .= ','; } 
	$access .= $s->pageAccessMarketing;  if(!empty($s->pageAccessMarketing)){ $access .= ','; }
	$access .= $s->pageAccessProduction;  if(!empty($s->pageAccessProduction)){ $access .= ','; }
	$access .= $s->pageAccessPrint;  if(!empty($s->pageAccessPrint)){ $access .= ','; }
	$access .= $s->pageAccessPrint2;  if(!empty($s->pageAccessPrint2)){ $access .= ','; }
	$access .= $s->pageAccessDetailsMods;  if(!empty($s->pageAccessDetailsMods)){ $access .= ','; }
	$access .= $s->pageAccessDetailsMods2;  if(!empty($s->pageAccessDetailsMods2)){ $access .= ','; }
	$staff[$s->username] = ','.$access;
}

echo '<table>';
/* echo '<tr class="trhead">';
	echo '<td>page num</td>';
	echo '<td>page title</td>';
	echo '<td>page set</td>';
	echo '<td>page handle</td>';
	echo '<td>username</td>';
echo '</tr>'; */

$cnt = 0;
foreach($pages AS $p){
if($cnt%2==0) echo '<tr class="odd">';
else echo '<tr>';
	echo '<td>'.$p->pageNum.'</td>';
	echo '<td>'.$p->pageTitle.'</td>';
	echo '<td>'.$p->pageSet.'</td>';
	echo '<td>'.$p->pageHandle.'</td>';	
	echo '<td>';
	
	$xx = '';
	foreach($staff AS $k=>$s2):
		if(!empty($p->pageHandle)){
			if(strpos($s2,','.$p->pageHandle.',') !== false){
				$xx .= '"'.$k.'",';
				echo $k.'<br/>';
			}
		}
	endforeach;
	echo '</td>';
	echo '<td>';
	if($xx!=''){
		echo '<table>';
		$query = $this->staffM->getQueryResults('staffs', 'username, title, dept, grp, subgrp', 'username IN ('.rtrim($xx,',').')', 'LEFT JOIN newPositions ON posID=position', 'subgrp, grp, dept');
		
		foreach($query AS $q):
			echo '<tr>';
			echo '<td>'.$q->username.'</td>';
			echo '<td>'.$q->title.'</td>';
			echo '<td>'.$q->dept.'</td>';
			echo '<td>'.$q->grp.'</td>';
			echo '<td>'.$q->subgrp.'</td>';
			echo '</tr>';
		endforeach;
		echo '</table>';
	}else echo '<td><br/></td>';
	
	echo '</td>';
echo '</tr>';
$cnt++;
}

echo '</table>';
?>