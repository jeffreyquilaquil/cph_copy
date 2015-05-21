<?php
echo '<table class="tableInfo">';
	/* echo '<tr>';
	echo '<td>ID</td>';
	echo '<td>SSS</td>';
	echo '<td>TIN</td>';
	echo '<td>Philhealth</td>';
	echo '<td>HDMF</td>';
	echo '<td>Sal</td>';
	echo '</tr>';
	foreach($query1 AS $s):	

		echo '<tr>';
		echo '<td>'.$s->empID.'</td>';

		echo '<td>'.$this->txtM->encryptText($s->sss).'</td>';
		echo '<td>'.$this->txtM->encryptText($s->tin).'</td>';
		echo '<td>'.$this->txtM->encryptText($s->philhealth).'</td>';
		echo '<td>'.$this->txtM->encryptText($s->hdmf).'</td>';
		
		$sal = $s->sal;
		if($sal=='0.00') $sal = '';
		else $sal = str_replace(',','', str_replace('.00','',($sal)));
		
		echo '<td>'.$this->txtM->encryptText($sal).'</td>';
		echo '</tr>';
		
		$upArr['sss'] = $this->txtM->encryptText($s->sss);
		$upArr['tin'] = $this->txtM->encryptText($s->tin);
		$upArr['philhealth'] = $this->txtM->encryptText($s->philhealth);
		$upArr['hdmf'] = $this->txtM->encryptText($s->hdmf);
		$upArr['sal'] = $this->txtM->encryptText($sal);
		$upArr['encrypHaha'] = 1;
		$this->staffM->updateQuery('staffs', array('empID'=>$s->empID), $upArr);			
	endforeach; */
	
	echo '<tr>';
	echo '<td>ID</td>';
	echo '<td>Bank Accnt</td>';
	echo '<td>Bank Accnt</td>';
	echo '<td>HMO</td>';
	echo '<td>HMO</td>';
	echo '</tr>';
	foreach($query2 AS $s):	

		echo '<tr>';
		echo '<td>'.$s->empID.'</td>';

		echo '<td>'.$this->staffM->decryptText($s->bankAccnt).'</td>';
		echo '<td>'.$this->txtM->encryptText($this->staffM->decryptText($s->bankAccnt)).'</td>';
		echo '<td>'.$this->staffM->decryptText($s->hmoNumber).'</td>';
		echo '<td>'.$this->txtM->encryptText($this->staffM->decryptText($s->hmoNumber)).'</td>';
		echo '</tr>';
		
		/* $upArr['bankAccnt'] = $this->txtM->encryptText($this->staffM->decryptText($s->bankAccnt));
		$upArr['hmoNumber'] = $this->txtM->encryptText($this->staffM->decryptText($s->hmoNumber));
		$this->staffM->updateQuery('staffs', array('empID'=>$s->empID), $upArr); */	
	endforeach;
?>

<script type="text/javascript">
	/* function conver(id, t){		
		$(t).hide();
		$.post('<?= $this->config->item('career_uri') ?>', {id:id, v:$('#hmo'+id).val()},
		function(){
			$('#tr'+id).css('background-color','#ccc');
		});
	} */
</script>