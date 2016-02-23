<h2>Admin Settings for <?= $row->name ?></h2>
<hr/>
<?php
if($updated!=''){
	echo '<div class="tacenter"><span class="errortext">'.$updated.'</span><hr/></div>';
}
?>
<form action="" method="POST" onSubmit="displaypleasewait();">
<table class="tableInfo">
	<tr>
		<td width="30%">Access Type</td>
		<td>		
		
			<input type="checkbox" name="access[]" value="hr" <? if(strpos($row->access,'hr')!==false){ echo 'checked="checked"'; } ?>/> HR<br/>
			<input type="checkbox" name="access[]" value="finance" <? if(strpos($row->access,'finance')!==false){ echo 'checked="checked"'; } ?>/> Finance<br/>
			<input type="checkbox" name="access[]" value="full" <? if(strpos($row->access,'full')!==false){ echo 'checked="checked"'; } ?>/> Full<br/>
			<input type="checkbox" name="access[]" value="med_person" <? if(strpos($row->access,'med_person')!==false){ echo 'checked="checked"'; } ?>/> Medical Personnel<br/>			
			
		
		</td>
	</tr>
	<tr>
		<td width="30%"><label>Exclude Schedule?</lable><br/>(applicable to staff we don't want to track the schedule and attendance)</td>
		<td>
			<input type="radio" name="exclude_schedule" value="0" <? echo ($row->exclude_schedule == 0 ) ? 'checked' : ''; ?> />No
			<input type="radio" name="exclude_schedule" value="1" <? echo ($row->exclude_schedule == 1 ) ? 'checked' : ''; ?> />Yes
			
		</td>
	</tr>
	<tr>
		<td width="30%">&nbsp;</td>
		<td><input type="hidden" name="submitType" value="accesstype">
			<input type="submit" value="Submit" class="btnclass"></td>
	</tr>
</table>
</form>
