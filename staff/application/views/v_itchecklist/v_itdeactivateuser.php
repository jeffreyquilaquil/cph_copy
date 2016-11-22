<h2>Deactivate User</h2>
<br/>
<label for="which_table">From which access:</label>
<select name="which_table" id="which_table">
<option value="PT" <?php  if( $selected == 'PT' ) echo ' selected '; ?> >PT</option>
<option value="CPH" <?php if( $selected == 'CPH') echo ' selected '; ?> >CPH</option>
</select>
<table class="dTable display stripe hover">
	<thead>
	<tr>
		<th>Name</th>
		<th>Active</th>
	</tr>
    </thead>
<tbody>
<?php
	foreach($query AS $q):
		echo '<tr '.(($q->active==1)?'bgcolor="#ffe"':'').'>';
		echo '<td>'.$q->name.'</td>';
		echo '<td>';
			echo '<select class="padding5px" onChange="changeSetting('.$q->empID.', this)">
					<option value="1" '.(($q->active==1)?'selected':'').'>Yes</option>
					<option value="0" '.(($q->active==0)?'selected':'').'>No</option>
					<option value="2" '.(($q->active==2)?'selected':'').'>Float</option>
				</select>';
		echo '</td>';
		echo '</tr>';
	endforeach;
?>
</tbody>
</table>

<script type="text/javascript">
$(document).ready(function(){
    $('.dTable').dataTable();   
    $('#which_table').change( function(){
<?php
        $url = str_replace( array('/PT','/CPH'), '', $this->config->item('career_uri') );
?>
        var url = '<?= $url; ?>/' + $(this).val();
        
        document.location.replace(url);
    });
});


function changeSetting(id, tvalue){
	if(confirm('Changing the status will also change PT access.\nAre you sure you want to change?')){
		displaypleasewait();
		$.post('<?= $this->config->item('career_uri') ?>',{submitType:'activeStatus', empID:id, status:$(tvalue).val(), which_table: $('#which_table').val() }, 
		function(d){
			alert(d);
			window.location.reload();
		});
	}
}
</script>
