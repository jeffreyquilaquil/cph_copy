<a class="iframe" href="<?= $this->config->base_url().'addnewposition/' ?>"><button style="float:right;" class="padding5px">Add New Position</button></a>
<h2>List of All Positions</h2>
<hr/>
<table class="tableInfo">
	<tr class="trhead">
		<td>Position ID</td>
		<td>Organization</td>
		<td>Department</td>
		<td>Group</td>
		<td>Sub Group</td>
		<td>Title</td>
		<td>Org Level</td>
		<td>Status</td>
	</tr>
<?php
	foreach($positions AS $p):
		echo '<tr class="trbd">
				<td style="position:relative;">'.$p->posID;
			
			echo '<div id="ddesc'.$p->posID.'" style="position:absolute; border:1px solid #000; border-radius:10px; width:450px; background-color:#fff; display:none; padding:10px; z-index:99;">
				<b>'.$p->title.' Description</b><button onClick="$(\'#ddesc'.$p->posID.'\').hide();" style="float:right;">Close</button><hr/>
				'.nl2br($p->desc).'<br/>
				<button onClick="$(\'#ddesc'.$p->posID.'\').hide();" style="float:right;">Close</button>
			</div>';
				
		echo 	'</td>
				<td>'.$p->org.'</td>
				<td>'.$p->dept.'</td>
				<td>'.$p->grp.'</td>
				<td>'.$p->subgrp.'</td>
				<td><b>'.$p->title.'</b><br/>'.(($p->desc!='')?'<a onClick="showDesc('.$p->posID.')" class="cpointer"><u>view description</u></a>':'').'</td>
				<td>'.$p->levelName.'</td>
				<td>'.(($p->active==1)?'Active':'Inactive').'</td>
			</tr>';
	endforeach;
?>
</table>

<script type="text/javascript">	
	function showDesc(id){
		$('#ddesc'+id).show();
	}
</script>
