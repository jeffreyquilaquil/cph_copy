<h2>All Staffs</h2>
<hr/>	
<table class="tableInfo">

<?php	
	$sArr = array();
	foreach($staffs AS $s):
		$sArr[$s->username]['empID'] = $s->empID;	
		$sArr[$s->username]['name'] = $s->name;		
		$sArr[$s->username]['username'] = $s->username;		
		$sArr[$s->username]['regDate'] = $s->regDate;		
		$sArr[$s->username]['empStatus'] = $s->empStatus;		
	endforeach;
	
	foreach($staffsPT AS $st):
		if(array_key_exists($st->u, $sArr)){
			$sArr[$st->u]['reg_date'] = $st->reg_date;	
		}
	endforeach;
	
	
	foreach($sArr AS $k=>$val):
		echo '<tr id="tr_'.$val['empID'].'" '.((isset($val['reg_date']) && $val['reg_date']!=$val['regDate'])?'bgcolor="yellow"':'').'>';
		echo '<td>'.$val['empID'].'</td>';
		echo '<td>'.$val['name'].'</td>';
		echo '<td>'.$val['username'].'</td>';
		echo '<td>'.$val['empStatus'].'</td>';
		echo '<td>'.$val['regDate'].'</td>';
		echo '<td>'.((isset($val['reg_date']))?$val['reg_date']:'').'</td>';
		echo '<td>';
		echo '<button onClick="updateHim('.$val['empID'].', \''.$val['reg_date'].'\')">Update</button>';
		echo '</td>';
		echo '</tr>';
	endforeach;
	
?>
	
</table>

<script type="text/javascript">
	function updateHim(id, d){
		$.post('<?= $this->config->item('career_uri') ?>',{empID:id, regDate:d}, function(){
			$('#tr_'+id).css('background-color', '#555');
		});
	}
</script>



