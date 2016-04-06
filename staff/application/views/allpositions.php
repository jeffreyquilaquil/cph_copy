<?php 
if($page=='details'){
?>
<a class="iframe" href="<?= $this->config->base_url().'addnewposition/'.$row->posID.'/' ?>"><button style="float:right;" class="btnclass">Edit Position</button></a>
<h2>Position Details</h2>
<hr/>
<table class="tableInfo">
	<tr>
		<td width="30%">Name of Position</td>
		<td><?= $row->title ?></td>
	</tr>
	<tr>
		<td>Organization</td>
		<td><?= $row->org ?></td>
	</tr>
	<tr>
		<td>Department</td>
		<td><?= $row->dept ?></td>
	</tr>
	<tr>
		<td>PT Department</td>
		<td><?= ((isset($PTDeptArr[$row->dt]))?$PTDeptArr[$row->dt]:'') ?></td>
	</tr>
	<tr>
		<td>Group</td>
		<td><?= $row->grp ?></td>
	</tr>
	<tr>
		<td>Sub-group</td>
		<td><?= $row->subgrp ?></td>
	</tr>
	<tr>
		<td>Level</td>
		<td><?= $row->levelName ?></td>
	</tr>	
	<tr>
		<td>Is Active</td>
		<td><?= (($row->active==0)?'No':'Yes') ?></td>
	</tr>	
	<tr>
		<td>Description</td>
		<td><?= ((empty($row->desc))?'none':$row->desc) ?></td>
	</tr>
	<tr>
		<td>Required Test</td>
		<td>
		<?php
			if($row->requiredTest==''){
				echo 'none';
			}else{
				$xExplode = explode(',', $row->requiredTest);
				foreach($xExplode AS $x):
					echo $txt[$x].'<br/>';
				endforeach;
			}
		?>
		</td>
	</tr>
	<tr>
		<td>Required Skills</td>
		<td>
		<?php
			if($row->requiredSkills==''){
				echo 'none';
			}else{
				$xExplode = explode('|', $row->requiredSkills);
				foreach($xExplode AS $x):
					echo $skills[$x].'<br/>';
				endforeach;
			}
		?>
		</td>
	</tr>
	<tr>
		<td>Date Created</td>
		<td><?= (($row->date_created!='0000-00-00 00:00:00')?date('Y-m-d H:i', strtotime($row->date_created)):'') ?></td>
	</tr>
	<tr>
		<td>Created By</td>
		<td><?= ((!empty($row->user))?$row->user:'') ?></td>
	</tr>
</table>

<?php }else{ ?>
<h2>List of All Positions</h2><hr/>

<div style="position:absolute; right:45px; z-index:99;">
	<a class="iframe" href="<?= $this->config->base_url().'addnewposition/' ?>">
		<button class="btnclass">Add New Position</button>
	</a>
</div>

<table class="tableInfo datatable">
	<thead>
		<tr class="trhead">
			<td>ID</td>
			<td>Organization</td>
			<td>Department</td>
			<td>PT Department</td>
			<td>Group</td>
			<td>Sub Group</td>
			<td>Title</td>
			<td>Org Level</td>
			<td>Status</td>
			<td><br/></td>
		</tr>
	</thead>	
<?php
	foreach($positions AS $p):
		echo '<tr class="trbd">
				<td style="position:relative;">'.$p->posID.'</td>
				<td>'.$p->org.'</td>
				<td>'.$p->dept.'</td>
				<td>'.((isset($PTDeptArr[$p->dt]))?$PTDeptArr[$p->dt]:'').'</td>				
				<td>'.$p->grp.'</td>
				<td>'.$p->subgrp.'</td>
				<td><b>'.$p->title.'</b></td>
				<td>'.$p->levelName.'</td>
				<td>'.(($p->active==1)?'Active':'Inactive').'</td>
				<td><a href="'.$this->config->base_url().'allpositions/'.$p->posID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/btn-view-details.png"></a></td>
			</tr>';
	endforeach;
?>
</table>
<?php } ?>
