<?php 
	if($page=='edit'){
		echo '<h2>Edit Position</h2><hr/>';
	}else{
		echo '<h2>Add New Position</h2><hr/>';
	}

if(isset($added)){
	echo '<p class="errortext">New position "<b>'.$added.'</b>" has been added.</p><hr/>';
}else if(isset($edited)){
	echo '<p class="errortext">Position "<b>'.$edited.'</b>" has been edited.</p><hr/>';
	exit;
}

?>
<p><i style="color:#555;">Click <a href="#" onClick="parent.$.fn.colorbox.close(); parent.location='<?= $this->config->base_url().'allpositions/' ?>'">here</a> to view all positions.</i></p>
<form action="" method="POST" onSubmit="return validateForm();">
<table class="tableInfo">
	<tr>
		<td width="20%">Name of the position</td>
		<td class="tload" width="25px"></td>
		<td><?php echo $this->textM->formfield('text', 'title', ((isset($row->title))?$row->title:''), 'forminput', '', 'required') ?></td>
	</tr>
	<tr class="trorg">
		<td class="tlabel">Organization</td>
		<td class="tload"></td>
		<td> 
		<?php
			echo '<select class="forminput" name="org" onChange="changeInput(\'org\', \'dept\')">';
			echo '<option></option>';
		
			foreach($org AS $o):
				echo '<option value="'.$o->org.'" '.(($page=='edit' && $row->org==$o->org)?'selected="selected"':'').'>'.$o->org.'</option>';
			endforeach;
			echo '</select>';
		?>	
		</td>
	</tr>
	<tr class="trdept <?= (($page=='add')?'hidden':'') ?>">
		<td class="tlabel">PT Department</td>
		<td class="tload"></td>
		<td><?php
			$dt = ((isset($row->dt))?$row->dt:'');
			$selectDT = '<option value=""></option>';
			foreach($PTDeptArr AS $k=>$v){
				$selectDT .= '<option value="'.$k.'" '.(($dt==$k)?'selected="selected"':'').'>'.$v.'</option>';
			} 
			echo $this->textM->formfield('select', 'dt', $selectDT, 'forminput', '', 'required');
		?></td>
	</tr>
	<tr class="trdept <?= (($page=='add')?'hidden':'') ?>">
		<td class="tlabel">Department</td>
		<td class="tload"></td>
		<td>
		<?php
			$dval = '';
			if($page=='edit' && isset($depts)){
				foreach($depts AS $d):
					$dval .= '<option value="'.$d->dept.'" '.(($row->dept==$d->dept)?'selected="selected"':'').'>'.$d->dept.'</option>';
				endforeach;
			}
			
			echo $this->textM->formfield('select', 'dept', $dval, 'forminput', '', 'onChange="changeInput(\'dept\', \'grp\')"');
		?>		
		</td>
	</tr>
	<tr class="trgrp <?= (($page=='add')?'hidden':'') ?>">
		<td class="tlabel">Group</td>
		<td class="tload"></td>
		<td>
		<?php
			$gval = '';
			if($page=='edit' && isset($grps)){
				foreach($grps AS $g):
					$gval .= '<option value="'.$g->grp.'" '.(($row->grp==$g->grp)?'selected="selected"':'').'>'.$g->grp.'</option>';
				endforeach;
			}
			
			echo $this->textM->formfield('select', 'grp', $gval, 'forminput', '', 'onChange="changeInput(\'grp\', \'subgrp\')"');
		?>
		</td>
	</tr>
	<tr class="trsubgrp <?= (($page=='add')?'hidden':'') ?>">
		<td class="tlabel">Sub-group</td>
		<td class="tload"></td>
		<td>
		<?php
			$sval = '';
			if($page=='edit' && isset($subgrps)){
				foreach($subgrps AS $s):
					$sval .= '<option value="'.$s->subgrp.'" '.(($row->subgrp==$s->subgrp)?'selected="selected"':'').'>'.$s->subgrp.'</option>';
				endforeach;
			}
			
			echo $this->textM->formfield('select', 'subgrp', $gval, 'forminput');
		?>
		</td>
	</tr>
	<tr class="trall <?= (($page=='add')?'hidden':'') ?>">
		<td class="tlabel">Level</td>
		<td class="tload"></td>
		<td>
		<?php
			$lval = '';
			foreach($orgLevel AS $lv):
				$lval .= '<option value="'.$lv->levelID.'" '.(($page=='edit' && $row->orgLevel_fk==$lv->levelID)?'selected="selected"':'').'>'.$lv->levelName.'</option>';
			endforeach;
			echo $this->textM->formfield('select', 'orgLevel_fk', $lval, 'forminput');
		?>
		</td>
	</tr>
	<tr class="trall <?= (($page=='add')?'hidden':'') ?>">
		<td class="tlabel">Job Description</i>
		</td>
		<td class="tload"></td>
		<td>
			<?= $this->textM->formfield('textarea', 'desc', (($page=='edit')?$row->desc:''), '' , '', 'rows="10"') ?>
			<br/>
			<i style="color:#555;">A position cannot be created without a complete job description. This helps HR find the most suitable candidates, and also will help the respective PHL department in managing the new employee.
		</td>
	</tr>
<?php
	if($page=='edit'){
		echo '<tr>';
		echo '<td class="tlabel">Is Active?</td>';
		echo '<td class="tload"></td>';
		
		$sval = '<option value="1" '.(($row->active==1)?'selected="selected"':'').'>Yes</option>
				<option value="0" '.(($row->active==0)?'selected="selected"':'').'>No</option>';
		
		echo '<td>'.$this->textM->formfield('select','active', $sval, 'forminput').'</td>';
		echo '</tr>';
	}
?>
	
	<tr class="trall <?= (($page=='add')?'hidden':'') ?>">
		<td class="tlabel">Required Test</i>
		</td>
		<td class="tload"></td>
		<td>
		<?php
			$cr = ceil(count($requiredTestArr)/2);
			if(isset($row->requiredTest) && !empty($row->requiredTest)) $rTestarr = explode(',', $row->requiredTest);
			echo '<div style="float:left; width:45%;">';
			$n=0;
			foreach($requiredTestArr AS $k=>$r):
				if($n==$cr) echo '</div><div style="float:left;">';
				echo '<input type="checkbox" value="'.$k.'" name="rtest[]" '.((isset($rTestarr) && in_array($k, $rTestarr))?'checked':'').'/> '.$r.'<br/>';
				$n++;
			endforeach;
			echo '</div>'
		?>
		</td>
	</tr>	
	<tr class="trall <?= (($page=='add')?'hidden':'') ?>">
		<td class="tlabel">Required Skills</i>
		</td>
		<td class="tload"></td>
		<td>
		<?php
			$cr = ceil(count($requiredSkillsArr)/2);
			if(isset($row->requiredSkills	) && !empty($row->requiredSkills	)) $rSkillarr = explode('|', $row->requiredSkills	);
			echo '<div style="float:left; width:45%;">';
			$n=0;
			foreach($requiredSkillsArr AS $r):
				if($n==$cr) echo '</div><div style="float:left;">';
				echo '<input type="checkbox" value="'.$r->skillID.'" name="rskill[]" '.((isset($rSkillarr) && in_array($r->skillID, $rSkillarr))?'checked':'').'/> '.$r->skillName.'<br/>';
				$n++;
			endforeach;
			echo '</div>'
		?>
		</td>
	</tr>

	<tr class="trall <?= (($page=='add')?'hidden':'') ?>">
		<td colspan=3 align="right">
		<?php
			if($page=='edit'){
				echo $this->textM->formfield('hidden', 'submitType', 'editposition');
			}else{
				echo $this->textM->formfield('hidden', 'submitType', 'addposition');
			}
			
			echo $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
		?>
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
			displaypleasewait();
			return true;
		}	
	}
</script>	