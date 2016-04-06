<?php	
	if($this->uri->segment(2)=='show'){
		$arr = array();
		foreach($query1 AS $q){
			$arr[$q->u] = (array)$q;
		}
		
		foreach($query AS $n){
			if(isset($arr[$n->username])){
				$arr[$n->username]['empID'] = $n->empID;
				$arr[$n->username]['username'] = $n->username;
				$arr[$n->username]['titleNEW'] = $n->title;
			}
		}
		
		echo '<pre>';
		unset($arr['acastilla']);
		unset($arr['jgarcia']);
		unset($arr['mabellana']);
		unset($arr['idiocson']);
		unset($arr['wyeager']);
		unset($arr['anowlin']);
		unset($arr['rsato']);
		unset($arr['lmarinastest']);
		unset($arr['gbacang']);
		foreach($arr AS $a){
			if($a['title']!=$a['titleNEW']){
				print_r($a);
			}
		}
	}else{
		echo '<form action="" method="POST">';
		echo $this->textM->formfield('text', 'eKey', '', '', 'eKey');
		echo $this->textM->formfield('title', 'title', '');
		echo $this->textM->formfield('submit', '', 'Submit');
		echo '</form>';	
		echo '<br/><br/>';
		echo '<form action="" method="POST">';
		echo $this->textM->formfield('text', 'eKey', '', '', 'eKey');
		echo $this->textM->formfield('title', 'title', 'Editor');
		echo $this->textM->formfield('submit', '', 'Submit');
		echo '</form>';	
		echo '<br/><br/>';
		echo '<form action="" method="POST">';
		echo $this->textM->formfield('text', 'eKey', '', '', 'eKey');
		echo $this->textM->formfield('title', 'title', 'Illustrator');
		echo $this->textM->formfield('submit', '', 'Submit');
		echo '</form>';	
		echo '<br/><br/>';
		echo '<form action="" method="POST">';
		echo $this->textM->formfield('text', 'eKey', '', '', 'eKey');
		echo $this->textM->formfield('title', 'title', 'A & R Representative');
		echo $this->textM->formfield('submit', '', 'Submit');
		echo '</form>';		
	}
	
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