<h2>All Staffs</h2>
<hr/>	
<?= count($staffs) ?>
<table class="tableInfo">
<?php
$seg2 = $this->uri->segment(2);
$sarr = array('sFirst', 'sLast', 'u', 'title', 'sup', 'supName');
foreach($staffs AS $s):
	echo '<tr id="tr'.$s->eKey.'">';
	foreach($s AS $k=>$ss):
		if(in_array($k,$sarr))
			echo '<td>'.$ss.'</td>';
	endforeach;
	
	echo '</tr>';
endforeach;
?>
	
</table>

<script type="text/javascript">
	function change(id){
		$('#tr'+id).css('background-color','green');	
		$.post("<?= $this->config->item('career_uri') ?>",{
			eKey:id,
			submitType:"update"
		},function(){
			$('#tr'+id).css('background-color','#bbb');
		});
	}
	
	function insert(id){
		$('#tr'+id).css('background-color','green');	
		$.post("<?= $this->config->item('career_uri') ?>",{
			eKey:id,
			submitType:"insert"
		},function(){ 
			$('#tr'+id).css('background-color','#bbb');
		});
	}
		
 </script>



