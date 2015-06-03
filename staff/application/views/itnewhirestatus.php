<h2>New Hire Status</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link current" data-tab="pending">Pending</li>
	<li class="tab-link" data-tab="done">Done</li>
</ul>

<div id="pending" class="tab-content current">
<?php
	if(count($inprogress)== 0) echo '<br/>None.';
	foreach($inprogress AS $in){
?>
		<table id="table_<?= $in->empID_fk ?>" class="tableInfo">
			<tr class="trlabel">
				<td colspan=3>
					<?= strtoupper($in->name).' (<i>'.$in->title.'</i>)' ?>
					&nbsp;&nbsp;&nbsp;<span onClick="showData(this, <?= $in->empID_fk ?>)" class="errortext cpointer">[Show]</span>
					<div style="float:right;">Status: 
						<select onChange="changeSeat(<?= $in->empID_fk ?>, 'status', this)">
							<option value="0" <? (($in->status==0)?'selected="selected"':'')?>>Pending</option>
							<option value="1" <? (($in->status==1)?'selected="selected"':'')?>>Done</option>
						</select>
					</div>
				</td>
			</tr>
			<tr class="trhead">
				<td colspan=3>Start Date: <i><?= date('F d, Y', strtotime($in->startDate)).' ('.$in->shift.')' ?></i></td>
			</tr>
		</table>		
		<table id="tabledisp_<?= $in->empID_fk ?>" class="tableInfo hidden">
			<tr class="trhead">
				<td colspan=3>Immediate Supervisor: <i><?= $in->imsupervisor ?></i></td>
			</tr>
			<tr>
				<td width="35%"><b>Email Creation</b></td>
				<td width="35%">- morning (Glaiza, Divi)<br/>
					- night (Marjune, Fitt)</td>
				<td>
					<?php
						if(!empty($in->emailCreation))
							echo 'By: '.$in->emailCreation;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'emailCreation\', this)"/> Created';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Zimbra Email Signature</b></td>
				<td><br/></td>
				<td>
					<?php
						if(!empty($in->emailSignature))
							echo 'By: '.$in->emailSignature;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'emailSignature\', this)"/> Created';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Updated Googled Docs Distribution List</b></td>
				<td><br/></td>
				<td>
					<?php
						if(!empty($in->emailDistList))
							echo 'By: '.$in->emailDistList;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'emailDistList\', this)"/> Updated';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Digital Signature</b></td>
				<td>- Coordinate with HR</td>
				<td>
					<?php
						if(!empty($in->digitalSignature))
							echo 'By: '.$in->digitalSignature;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'digitalSignature\', this)"/> Done';
					?>
				</td>
			</tr>
			<tr>
				<td><b>ID Picture</b></td>
				<td>- Coordinate with HR</td>
				<td>
					<?php
						if(!empty($in->picture))
							echo 'By: '.$in->picture;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'picture\', this)"/> Done';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Server Access</b></td>
				<td>- folder<br/>
					- samba
				</td>
				<td>
					<?php
						if(!empty($in->serverAccess))
							echo 'By: '.$in->serverAccess;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'serverAccess\', this)"/> Done';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Filezilla Access</b></td>
				<td><br/></td>
				<td>
					<?php
						if(!empty($in->filezillaAccess))
							echo 'By: '.$in->filezillaAccess;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'filezillaAccess\', this)"/> Done';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Assign HPBX Number</b></td>
				<td><br/></td>
				<td>
					<?php
						if(!empty($in->hpbxNumber))
							echo 'By: '.$in->hpbxNumber;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'hpbxNumber\', this)"/> Assigned';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Create PC Profile</b></td>
				<td>Seatplan From Ms Di <input style="text-align:center; padding:5px;" type="text" name="seatplan" value="<?= $in->seatplan ?>" onBlur="changeSeat(<?= $in->empID_fk ?>, 'seatplan' this)"/></td>
				<td>
					<?php
						if(!empty($in->pcProfile))
							echo 'By: '.$in->pcProfile;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'pcProfile\', this)"/> Created';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Assign XLite, hardphone and headset</b></td>
				<td>- if needed</td>
				<td>
					<?php
						if(!empty($in->xlitePhone))
							echo 'By: '.$in->xlitePhone;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$in->empID_fk.', \'xlitePhone\', this)"/> Assigned';
					?>
				</td>
			</tr>
		</table>
		<br/>
	<?php
	}
?>
</div>



<div id="done" class="tab-content">
<?php
	if(count($done)== 0) echo '<br/>None.';
	foreach($done AS $do){
?>
		<table id="table_<?= $do->empID_fk ?>" class="tableInfo">
			<tr class="trlabel">
				<td colspan=3>
					<?= strtoupper($do->name).' (<i>'.$do->title.'</i>)' ?>
					&nbsp;&nbsp;&nbsp;<span onClick="showData(this, <?= $do->empID_fk ?>)" class="errortext cpointer">[Show]</span>
					<div style="float:right;">Status: 
						<select onChange="changeSeat(<?= $do->empID_fk ?>, 'status', this)">
							<option value="0">Pending</option>
							<option value="1" <? if($do->status==1) echo 'selected="selected"'; ?>>Done</option>
						</select>
					</div>
				</td>
			</tr>
			<tr class="trhead">
				<td colspan=3>Start Date: <i><?= date('F d, Y', strtotime($do->startDate)).' ('.$do->shift.')' ?></i></td>
			</tr>
		<table id="tabledisp_<?= $do->empID_fk ?>" class="tableInfo hidden">
			<tr class="trhead">
				<td colspan=3>Immediate Supervisor: <i><?= $do->imsupervisor ?></i></td>
			</tr>
			<tr>
				<td width="35%"><b>Email Creation</b></td>
				<td width="35%">- morning (Glaiza, Divi)<br/>
					- night (Marjune, Fitt)</td>
				<td>
					<?php
						if(!empty($do->emailCreation))
							echo 'By: '.$do->emailCreation;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'emailCreation\', this)"/> Created';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Zimbra Email Signature</b></td>
				<td><br/></td>
				<td>
					<?php
						if(!empty($do->emailSignature))
							echo 'By: '.$do->emailSignature;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'emailSignature\', this)"/> Created';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Updated Googled Docs Distribution List</b></td>
				<td><br/></td>
				<td>
					<?php
						if(!empty($do->emailDistList))
							echo 'By: '.$do->emailDistList;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'emailDistList\', this)"/> Updated';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Digital Signature</b></td>
				<td>- Coordinate with HR</td>
				<td>
					<?php
						if(!empty($do->digitalSignature))
							echo 'By: '.$do->digitalSignature;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'digitalSignature\', this)"/> Done';
					?>
				</td>
			</tr>
			<tr>
				<td><b>ID Picture</b></td>
				<td>- Coordinate with HR</td>
				<td>
					<?php
						if(!empty($do->picture))
							echo 'By: '.$do->picture;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'picture\', this)"/> Done';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Server Access</b></td>
				<td>- folder<br/>
					- samba
				</td>
				<td>
					<?php
						if(!empty($do->serverAccess))
							echo 'By: '.$do->serverAccess;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'serverAccess\', this)"/> Done';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Filezilla Access</b></td>
				<td><br/></td>
				<td>
					<?php
						if(!empty($do->filezillaAccess))
							echo 'By: '.$do->filezillaAccess;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'filezillaAccess\', this)"/> Done';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Assign HPBX Number</b></td>
				<td><br/></td>
				<td>
					<?php
						if(!empty($do->hpbxNumber))
							echo 'By: '.$do->hpbxNumber;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'hpbxNumber\', this)"/> Assigned';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Create PC Profile</b></td>
				<td>Seatplan From Ms Di <input style="text-align:center; padding:5px;" type="text" name="seatplan" value="<?= $do->seatplan ?>" onBlur="changeSeat(<?= $do->empID_fk ?>, 'seatplan' this)"/></td>
				<td>
					<?php
						if(!empty($do->pcProfile))
							echo 'By: '.$do->pcProfile;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'pcProfile\', this)"/> Created';
					?>
				</td>
			</tr>
			<tr>
				<td><b>Assign XLite, hardphone and headset</b></td>
				<td>- if needed</td>
				<td>
					<?php
						if(!empty($do->xlitePhone))
							echo 'By: '.$do->xlitePhone;
						else
							echo '<input type="checkbox" onClick="changeStatus('.$do->empID_fk.', \'xlitePhone\', this)"/> Assigned';
					?>
				</td>
			</tr>
		</table>
		<br/>
	<?php
	}
?>

</div>

<script type="text/javascript">
	function changeStatus(id, fld, ths){
		if($(ths).is(':checked')){
			if(confirm('Are you sure you are done with this?')){
				$(ths).parent('td').attr('id', 'td_'+fld+id);
				$(ths).parent('td').html('<img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25"/>');
				$.post('<?= $this->config->item('career_uri') ?>',{empID_fk:id, type:fld}, 
				function(retvalue){
					$('#td_'+fld+id).parent('tr').css('background-color', '#ddd');
					$('#td_'+fld+id).html('By: '+retvalue);
				});
			}else{
				$(ths).prop('checked',false);
			}
		}else{
			$(ths).prop('checked',false);
		}
	}
	
	function changeSeat(id, type, ths){
		$('<img id="img_'+id+'" src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="15"/>').insertAfter(ths);
		$.post('<?= $this->config->item('career_uri') ?>',{empID_fk:id, type:type, sval:$(ths).val()},
		function(){
			$('#img_'+id).hide();
			
			if(type=='status'){
				$('#table_'+id).hide();
			}
		});
	}
	
	function showData(t, id){;
		$('#tabledisp_'+id).toggle();
		$(t).text(function(i, text){
			return text === "[Show]" ? "[Hide]" : "[Show]";
		})
	}
</script>