<?php
	if($type=='action'){
		echo $this->textM->formfield('button', '', 'View Details', 'btnclass btnred floatright', '', 'id="btnviewdetails"');
		echo '<h3>Quick Action</h3>';
	} 
	else echo '<h3>Incident Report Details</h3>';
	echo '<hr/>';

	if($type=='action'){	
		$repStatus = '';
		
		if(isset($actionsaved)){
			echo '<p class="errortext">Action Saved!</p>';
		}else{		
			foreach($reportStatus AS $k=>$stat){
				if($k!=0)
					$repStatus .= '<option value="'.$k.'" '.(($k==$details->status)?'selected="selected"':'').'>'.(($k!=$details->status)?'Change Status to ':'').''.$stat.'</option>';
			}
			$repStatus .= '<option value="0">Cancel</option>';
			$repStatus .= '<option value="Note">Add Note Only</option>';
		?>
			<form action="" method="POST" onSubmit="showloading();">
			<table class="tableInfo">
				<tr>
					<td width="25%"><b>Action:</b></td>			
					<td><?= $this->textM->formfield('select', 'status', $repStatus, 'forminput') ?></td>
				</tr>
				<tr>
					<td><b>Note</b></td>
					<td><?= $this->textM->formfield('textarea', 'statusNote', '', 'forminput', '', 'rows=5 required'); ?></td>
				</tr>
				<tr>
					<td><br/></td>
					<td>
						<?= $this->textM->formfield('hidden', 'submitType', 'changeStatus') ?>
						<?= $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen') ?>
					</td>
				</tr>
			</table>
			</form>
			<table class="tableInfo <?= (($type=='action')?'hidden':'') ?>">
				<tr><td><br/></td></tr>
				<tr class="trhead"><td>Incident Report Details</td></tr>
			</table>
			
<?php	}	
	}
?>	
	<table class="tableInfo <?= (($type=='action')?'hidden':'') ?>">
		<tr>
			<td width="30%"><b>Status</b></td>
			<td>
			<?php
				echo $reportStatus[$details->status].'&nbsp;&nbsp;';
				if($type!='action' && $this->access->accessFullHR==true) echo '<button onClick="window.location.href=\''.$this->config->base_url().'incidentreportaction/action/'.$details->reportID.'/\'">Update</button>';
			?>
			</td>
		</tr>
		<tr>
			<td width="30%"><b>When did the incident take place?</b></td>
			<td><?= date('F d, Y', strtotime($details->when)) ?></td>
		</tr>
		<tr>
			<td><b>Where did the incident take place?</b></td>
			<td>
			<?php
				echo '<span class="spanwhere">'.$details->where.'</span>';
				if($this->access->accessFullHR===true){
					echo ' <button class="spanwhere" onClick="showEditWhere()">Edit</button>';
					echo '<form id="formEditWhere" class="hidden" action="" method="POST">';
						echo $this->textM->formfield('text', 'where', $details->where, 'forminput', '', 'required');
						echo $this->textM->formfield('hidden', 'prevwhere', $details->where);						
						echo $this->textM->formfield('hidden', 'submitType', 'editwhere');
						echo $this->textM->formfield('submit', '', 'Submit', 'btnclass btngreen');
						echo $this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'onClick="hideEditWhere()"');
					echo '</form>';
				}
			?>
			</td>
		</tr>
		<tr>
			<td><b>What happened?</b></td>
			<td><?= nl2br($details->what) ?></td>
		</tr>
		<tr>
			<td><b>What code of conduct policy is violated?</b></td>
			<td>
			<?php
				if(isset($offensesData)){
					foreach($offensesData AS $o){
						echo '- Level '.$o->level.' (<i>'.$o->category.'</i>) - '.$o->offense.'<br/>';
					}
				}
			?>
			</td>
		</tr>
		<tr>
			<td><b>Is this reported to immediate supervisor?</b></td>
			<td><?= (($details->reported==1)?'Yes':'No') ?></td>
		</tr>
		<tr>
			<td><b><?= (($details->reported==0)?'Why it is not reported to immediate supervisor?':'What action did the immediate supervisor do about it?') ?></b></td>
			<td><?= nl2br($details->whatISaction) ?></td>
		</tr>
		<tr>
			<td><b>Proof / evidence to support the report?</b></td>
			<td><?= nl2br($details->proof) ?></td>
		</tr>
		<tr>
			<td><b>Witnesses</b></td>
			<td><?= nl2br($details->witnesses) ?></td>
		</tr>
		<tr>
			<td><b>Other details</b></td>
			<td><?= nl2br($details->otherdetails) ?></td>
		</tr>
	<?php if(!empty($details->docs)){ ?>
		<tr>
			<td><b>Documents / Files / Photos</b></td>
			<td>
			<?php
				$docs = explode('|', $details->docs);
				foreach($docs AS $d){
					if(!empty($d)){
						echo '<a href="'.$this->config->base_url().$dir.$d.'" class="iframe">'.$d.'</a><br/>';
					}
				}
			?>
			</td>
		</tr>
	<? } if(!empty($details->whyExcludeIS)){ ?>
		<tr>
			<td><b>Reason why immediate supervisor was excluded from knowing about this case</b></td>
			<td><?= $details->whyExcludeIS ?></td>
		</tr>
	<?php } ?>
		<tr>
			<td><b>Reported by</b></td>
			<td><?php
				if(empty($details->alias)) echo $details->name;
				else{
					if($this->access->accessFullHR==false) echo $details->alias;
					else $details->name.' AS '.$details->alias;
				}
			?></td>
		</tr>
		<tr>
			<td><b>Date Reported</b></td>
			<td><?= date('F d, Y h:i a', strtotime($details->dateSubmitted)) ?></td>
		</tr>
		<tr>
			<td><b>Incident Report Form</b></td>
			<td><a href="<?= $this->config->base_url().'incidentreportform/'.$details->reportID.'/' ?>"><img src="<?= $this->config->base_url() ?>css/images/pdf-icon.png"/></a></td>
		</tr>
	<?php if(count($statusHistory)>0){ ?>
		<tr><td colspan=2><br/></td></tr>
		<tr class="trhead"><td colspan=2>Status History</td></tr>		
	<?php 
		foreach($statusHistory AS $s){
			echo '<tr>';
				echo '<td>'.((is_numeric($s->status))?$reportStatus[$s->status]:$s->status).'</td>';
				echo '<td>Note: <i>'.nl2br($s->statusNote).'</i><br/>By:'.$s->updatedBy.' '.date('d M y h:i a', strtotime($s->dateUpdated)).'</td>';
			echo '</tr>';
		}
	} ?>
	</table>


<script type="text/javascript">
	$(function(){
		$('#btnviewdetails').click(function(){
			$('.tableInfo').removeClass('hidden');
			$(this).addClass('hidden');
		});
	});
	
	function showloading(){
		$('input[type="submit"]').attr('disabled', 'disabled');
	}
	
	function showEditWhere(){
		$('.spanwhere').addClass('hidden');
		$('#formEditWhere').removeClass('hidden');
	}
	
	function hideEditWhere(){
		$('.spanwhere').removeClass('hidden');
		$('#formEditWhere').addClass('hidden');
	}
</script>

