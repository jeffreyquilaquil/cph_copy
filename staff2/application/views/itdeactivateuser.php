<h2>Deactivate User</h2>
<table class="dTable display stripe hover">
	<thead>
	<tr>
		<th>Name</th>
		<th>Active</th>
	</tr>
	</thead>
<?php
	foreach($query AS $q):
		echo '<tr '.(($q->active==1)?'bgcolor="#ffe"':'').'>';
		echo '<td>'.$q->name.'</td>';
		echo '<td>';
			echo '<select class="padding5px" onChange="changeSetting('.$q->empID.', this)">
					<option value="1" '.(($q->active==1)?'selected':'').'>Yes</option>
					<option value="0" '.(($q->active==0)?'selected':'').'>No</option>
				</select>';
		echo '</td>';
		echo '</tr>';
	endforeach;
?>
</table>

<script type="text/javascript">
$(document).ready(function(){
	$('.dTable').dataTable();   
});

function changeSetting(id, tvalue){
	if(confirm('Changing the status will also change PT access.\nAre you sure you want to change?')){
		displaypleasewait();
		$.post('<?= $this->config->item('career_uri') ?>',{submitType:'activeStatus', empID:id, status:$(tvalue).val()}, 
		function(d){
			alert(d);
			window.location.reload();
		});
	}
}
</script>