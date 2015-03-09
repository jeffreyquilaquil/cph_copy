<h2>Generate Code</h2>
<hr/>
<?php 
	if($this->accessFullHR==false && $this->user->level==0){
		echo 'You do not have permission to access this page.';
	}else{
?>
<table class="tableInfo">
	<tr class="trnote"><td>Generate code to bypass the condition of filing vacation leave less than 2 weeks. Code can only be used once.</td></tr>
	<tr><td valign="center"><button id="generate">Generate code</button>  <img id="loadgif" class="hidden" src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="20px"/></td></tr>
	<tr id="codetr" class="hidden"><td>Generated code: <b id="code"></b></td></tr></table>
<script type="text/javascript">
	$(function(){
		$('#generate').click(function(){
			$('#loadgif').removeClass('hidden');
			$.post('<?= $this->config->item('career_url').$_SERVER['REQUEST_URI'] ?>',{submitType:'gencode'},function(d){
				$('#code').html(d);
				$('#generate').html('Regenerate code');
				$('#codetr').removeClass('hidden');
				$('#loadgif').addClass('hidden');
			});
		});
	});
</script>
<?php } 
	if(count($codes)>0){
		echo '<br/><br/>';
		echo '<h3>Generated Codes</h3>';
		echo '<table class="tableInfo">';
		echo '<tr class="trhead">
				<td>Generated Code</td>
				<td>Date Generated</td>
				<td>Used By</td>
				<td>Date Used</td>
				<td>Type</td>
				<td>Status</td>
			</tr>';
		foreach($codes AS $c):
			echo '<tr '.(($c->usedBy==0)?'bgcolor="#eee"':'').'>
				<td><b>'.$c->code.'</b></td>
				<td>'.date('d M y H:i', strtotime($c->dategenerated)).'</td>
				<td>'.$c->useByName.'</td>
				<td>'.(($c->dateUsed!='0000-00-00 00:00:00')?date('d M y H:i', strtotime($c->dateUsed)):'').'</td>
				<td>'.$c->type.'</td>
				<td>';
				if($c->status==0) echo 'Expired';
				else if($c->status==1) echo 'Active';
				else echo 'Redeemed';
			echo '</td>
			</tr>';
		endforeach;
		echo '<table>';
	}
?>
