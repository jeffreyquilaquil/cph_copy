<h2>Admin Settings for <?= $row->name ?></h2>
<hr/>
<?php
if($updated!=''){
	echo '<div class="tacenter"><span class="errortext">'.$updated.'</span><hr/></div>';
}
?>
<table class="tableInfo">
	<tr>
		<td width="30%">Access Type</td>
		<td>		
		<form action="" method="POST" onSubmit="displaypleasewait();">
			<input type="checkbox" name="access[]" value="hr" <? if(strpos($row->access,'hr')!==false){ echo 'checked="checked"'; } ?>/> HR<br/>
			<input type="checkbox" name="access[]" value="finance" <? if(strpos($row->access,'finance')!==false){ echo 'checked="checked"'; } ?>/> Finance<br/>
			<input type="checkbox" name="access[]" value="full" <? if(strpos($row->access,'full')!==false){ echo 'checked="checked"'; } ?>/> Full<br/>
			<input type="hidden" name="submitType" value="accesstype">
			<input type="submit" value="Submit">
		</form>
		</td>
	</tr>
	<tr>
		<td>Is a supervisor?</td>
		<td id="isSuptd">
		<?php
			echo '<select name="is_supervisor" class="forminput">';
				foreach($this->staffM->definevar('yesno01') AS $k=>$o):
					echo '<option value="'.$k.'" '.(($row->is_supervisor==$k)?'selected':'').'>'.$o.'</option>';
				endforeach;
			echo '<select><br/>';
		?>
		</td>
	</tr>
</table>

<script type="text/javascript">
	$(function(){
		$('select[name=is_supervisor]').change(function(){
			if(confirm('Are you sure you want to change <?= $row->name ?> to supervisor?')){
				$('<img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25px"/>').insertBefore(this);
				$(this).hide();
				$.post('<?= $this->config->item('career_uri') ?>',{submitType:'isSup', is_supervisor:$(this).val()},
				function(){					
					$('#isSuptd img').remove();
					$('select[name=is_supervisor]').show();
					alert('Is a supervisor changed.');
				});
			}
		});
	});
</script>