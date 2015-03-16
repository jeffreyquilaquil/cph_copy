<h2>Coaching Acknowledgment</h2>
<hr/>
<?php
	if(count($row)==0){
		echo 'No coaching record.';
	}else{
?>

<table class="tableInfo">
	<tr><td colspan=2 class="trhead">EMPLOYEE DETAILS</td></tr>
	<tr>
		<td width="30%">Employee Name</td>
		<td><?= $row->name ?></td>
	</tr>
	<tr>
		<td>Position Title</td>
		<td><?= $row->title ?></td>
	</tr>
	<tr>
		<td>Department</td>
		<td><?= $row->dept ?></td>
	</tr>
	<tr>
		<td>Reviewer</td>
		<td><?= $row->reviewer ?></td>
	</tr>
<?php if(isset($row->supName)){ ?>
	<tr>
		<td>Immediate Supervisor</td>
		<td><?= $row->supName ?></td>
	</tr>
	<tr>
		<td>Immediate Supervisor Position Title</td>
		<td><?= $row->supTitle ?></td>
	</tr>
<?php } if(isset($row->sup2ndName)){?>
	<tr>
		<td>2nd Level Supervisor</td>
		<td><?= $row->sup2ndName ?></td>
	</tr>
	<tr>
		<td>2nd Level Supervisor Position Title</td>
		<td><?= $row->sup2ndTitle ?></td>
	</tr>
<?php } ?>
	<tr>
		<td>Date of Coaching</td>
		<td><?= date('F d, Y', strtotime($row->coachedDate)) ?></td>
	</tr>
	<tr>
		<td>Evaluation Date</td>
		<td><?= date('F d, Y', strtotime($row->coachedEval)) ?></td>
	</tr>
	<tr>
		<td>Area of Improvement Required</td>
		<td><?= $row->coachedImprovement ?></td>
	</tr>
	<tr><td colspan=2><br/></td></tr>
	
</table>
<table class="tableInfo">
	<tr><td colspan=2 class="trhead">COACHING DETAILS</td></tr>
	<tr style="background-color:#eee;">
		<td align="center" width="50%" >
			<b>Areas for Improvement</b><br/>
			<i style="color:#555;" class="fs11px">Job performance factors that the employee needs to be coached on or behaviour or conduct that needs to be corrected.</i>
		</td>
		<td align="center" width="50%">
			<b>Expected Outcome (GOALS)</b><br/>
			<i style="color:#555;" class="fs11px">Specific results expected from employee after the coaching period</i>
		</td>
	</tr>
<?php
	$allAE = explode('--^_^--',$row->coachedAspectExpected);	
	for($s=0; $s<4; $s++){
		if(isset($allAE[$s])){
			$deta = explode('++||++',$allAE[$s]);
			
			echo '<tr>';
			echo '<td>'.$deta[0].'</td>';
			echo '<td>'.$deta[1].'</td>';
			echo '</tr>';
		}
	}
	echo '<tr><td colspan=2><br/></td></tr>';
	
	$support = explode('--^_^--',$row->coachedSupport);
	echo '<tr><td rowspan='.(count($support)+1).' align="center" style="background-color:#eee;"><b>Support from the Immediate Supervisor</b><br/><i style="color:#555;" class="fs11px">Immediate supervisor\'s action to support the employee in achieving the goals listed above.</i></td></tr>';
	
	for($p=0; $p<4; $p++){
		if(isset($support[$p])){
			echo '<tr>';
			echo '<td>'.$support[$p].'</td>';
			echo '</tr>';
		}
	}

	echo '<tr><td colspan=2><br/></td></tr>';
?>	
</table>

<table class="tableInfo">
	<tr><td class="trhead" colspan=2>ACKNOWLEDGMENTS<br/><i style="color:#555;" class="weightnormal">(Tick checkbox to acknowledge)<i/></td></tr>
<?php
	if($this->user->empID==$row->empID_fk || $this->user->empID==$row->supervisor || (isset($row->supSupervisor) && $this->user->empID==$row->supSupervisor)){
		$signature = UPLOAD_DIR.$this->user->username.'/signature.png';
		if(!file_exists($signature)){
			echo '<form id="formUpload" action="'.$this->config->base_url().'upsignature/" method="POST" enctype="multipart/form-data">';
			echo '<tr>
				<td class="errortext" width="40%">No signature on file.<br/>Please upload signature with transparent background first before acknowledging.</td>
				<td> 
					<input type="file" name="fileToUpload" id="fileToUpload"/>
					<input type="hidden" name="page" value="'.$_SERVER['REQUEST_URI'].'"/>
				</td>
			</tr>';
			echo '</form>';
		}
	}
?>
	<tr>
		<td width="25%">Immediate Supervisor's Acknowledgment</td>
		<td>
		<?php
			if($row->dateSupAcknowledged=='0000-00-00'){
				if($this->user->empID==$row->supervisor){
					echo '<input onClick="acknowledge(\'acknowledgeIS\')" id="acknowledgeIS" type="checkbox" '.((file_exists($signature))?'':'disabled="disabled"').'/> I have discussed and thoroughly explained the Coaching Form to the employee.';
				}else{
					echo '<i>Pending Acknowledgment</i>';
				}
			}else{
				echo '<b>'.$row->supName.':</b> I have discussed and thoroughly explained the Coaching Form to the employee.<br/>
					Signed: '.date('F d, Y', strtotime($row->dateSupAcknowledged	)).'
				';
			}
		?>
		</td>
	</tr>
	<tr>
		<td>Second Level Manager's Acknowledgment</td>
		<td>
		<?php
			if($row->date2ndMacknowledged	=='0000-00-00'){
				if(isset($row->supSupervisor) && $this->user->empID==$row->supSupervisor){
					echo '<input onClick="acknowledge(\'acknowledge2ndSup\')" id="acknowledge2ndSup" type="checkbox" '.((file_exists($signature))?'':'disabled="disabled"').'/> I have reviewed and approved the Coaching Form.';
				}else{
					echo '<i>Pending Acknowledgment</i>';
				}
			}else{
				echo '<b>'.$row->name.':</b> I have reviewed and approved the Coaching Form.<br/>
					Signed: '.date('F d, Y', strtotime($row->date2ndMacknowledged	)).'
				';
			}
		?>
		</td>
	</tr>
	<tr>
		<td>Employee's Acknowledgment Receipt of COACHING</td>
		<td>
		<?php
			if($row->dateEmpAcknowledge=='0000-00-00'){
				if($this->user->empID==$row->empID_fk){
					echo '<input onClick="acknowledge(\'acknowledgeEmp\')" id="acknowledgeEmp" type="checkbox" '.((file_exists($signature))?'':'disabled="disabled"').'/> I acknowledge that the expectations listed above have been thoroughly discussed to me and that I commit to working with my immediate supervisor to meet the expectations set forth in this document. I understand that my failure to meet the agreed goals/changes/improvements listed above can constitue to poor performance and may lead to further disciplinary measures up to and including termination of employment.';
				}else{
					echo '<i>Pending Acknowledgment</i>';
				}
			}
		?>
		</td>
	</tr>
</table>


<?php } ?>

<script type="text/javascript">
	$(function(){
		$('#fileToUpload').change(function(){
			displaypleasewait();
			$('#formUpload').submit();
		});				
	});
	
	function acknowledge(type){
		if($('#'+type).is(":checked")){
			displaypleasewait();
			$.post('<?= $this->config->item('career_uri') ?>',{submitType:type}, 
			function(){
				location.reload();
			});
		}
	}
</script>