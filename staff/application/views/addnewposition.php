<h2>Add New Position</h2>
<hr/>
<?php if(isset($added)){
	echo '<p class="errortext">New position "<b>'.$added.'</b>" has been added.</p><hr/>';
}?>
<p><i style="color:#555;">Click <a href="<?= $this->config->base_url().'allpositions/' ?>" class="iframe">here</a> to view all positions.</i></p>
<form action="" method="POST" onSubmit="return validateForm();">
<table class="tableInfo">
	<tr>
		<td width="20%">Name of the position</td>
		<td class="tload" width="25px"></td>
		<td><input type="text" name="title" class="forminput"/></td>
	</tr>
	<tr class="trorg">
		<td class="tlabel">Organization</td>
		<td class="tload"></td>
		<td> 
		<?php
			echo '<select class="forminput" name="org" onChange="changeInput(\'org\', \'dept\')">';
			echo '<option></option>';
		
			foreach($org AS $o):
				echo '<option value="'.$o->org.'">'.$o->org.'</option>';
			endforeach;
			echo '</select>';
		?>	
		</td>
	</tr>
	<tr class="trdept hidden">
		<td class="tlabel">Department</td>
		<td class="tload"></td>
		<td><select class="forminput" name="dept" onChange="changeInput('dept', 'grp')"></select></td>
	</tr>
	<tr class="trgrp hidden">
		<td class="tlabel">Group</td>
		<td class="tload"></td>
		<td><select class="forminput" name="grp" onChange="changeInput('grp', 'subgrp')"></select></td>
	</tr>
	<tr class="trsubgrp hidden">
		<td class="tlabel">Sub-group</td>
		<td class="tload"></td>
		<td><select class="forminput" name="subgrp"></select></td>
	</tr>
	<tr class="trall hidden">
		<td class="tlabel">Level</td>
		<td class="tload"></td>
		<td>
		<?php
			echo '<select name="orgLevel_fk" class="forminput">';
			foreach($orgLevel AS $lv):
				echo '<option value="'.$lv->levelID.'">'.$lv->levelName.'</option>';
			endforeach;
			echo '</select>';
		?>
		</td>
	</tr>
	<tr class="trall hidden">
		<td class="tlabel">Job Description</i>
		</td>
		<td class="tload"></td>
		<td>
			<textarea name="desc" rows="10"></textarea><br/>
			<i style="color:#555;">A position cannot be created without a complete job description. This helps HR find the most suitable candidates, and also will help the respective PHL department in managing the new employee.
		</td>
	</tr>
	<tr class="trall hidden">
		<td colspan=3 align="right">
			<input type="hidden" name="submitType" value="addposition"/>
			<input type="submit" value="Add Position" class="padding5px"/>
		</td>
	</tr>
</table>
</form>

<script type="text/javascript">
	$(function(){
		$('select[name=subgrp]').change(function(){
			$('.trall').removeClass('hidden');
		});
	});

	function changeInput(oldtype, newtype){
		$('.tr'+oldtype+' td.tload').append('<img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25px"/>');
		$('select[name='+newtype+']').html('');		
		$.post('<?= $this->config->item('career_uri') ?>',{submitType:'grpdepts', oldtype:oldtype, newtype:newtype, tval:$('select[name='+oldtype+']').val()}, 
		function(d){
			$('.tr'+newtype).removeClass('hidden');
			$('select[name='+newtype+']').html(d);
			$('.tr'+oldtype+' td.tload img').remove();
		});
	}
	
	function validateForm(){
		if($('input[name=title]').val()=='' || $('select[name=org]').val()=='' || 
			$('select[name=dept]').val()=='' || $('select[name=grp]').val()=='' || $('select[name=subgrp]').val()=='' || $('textarea[name=desc]').val().length==0){
			alert('All fields are required.');
			return false;
		}else{
			$('input[type=submit]').attr('disabled','disabled');
			return true;
		}	
	}
</script>	