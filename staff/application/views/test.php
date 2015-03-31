<?php
echo '<table>';
echo '<tr>';
echo '<td>Name</td>';
echo '<td>HMO</td>';
echo '<td><br/></td>';
echo '</tr>';
foreach($staffs AS $s):
	$hmo = $this->staffM->decryptText($s->hmoNumber);
	echo '<tr id="tr'.$s->empID.'">';
	echo '<td>'.$s->lname.', '.$s->fname.'</td>';
	echo '<td><input type="text" id="hmo'.$s->empID.'" name="hmo" value="'.((!empty($hmo))?$hmo:'').'"/></td>';
	echo '<td><input type="button" value="Update" onClick="conver('.$s->empID.', this)"></td>';
	echo '</tr>';
endforeach;
?>

<script type="text/javascript">
	function conver(id, t){		
		$(t).hide();
		$.post('<?= $this->config->item('career_uri') ?>', {id:id, v:$('#hmo'+id).val()},
		function(){
			$('#tr'+id).css('background-color','#ccc');
		});
	}
</script>