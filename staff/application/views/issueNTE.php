<h2>NTE for <?= $row->name ?></h2>
<hr/>
<?php
$awolnum = 1;
$tardynum = 1;

if($ntegenerated){
	echo 'NTE successfully generated.  Click <a href="'.$this->config->base_url().'detailsNTE/'.$insid.'/">here</a> to view NTE details page or <a href="'.$this->config->base_url().'ntepdf/'.$insid.'/D/">here</a> to download the file.';
}else{

$signature = UPLOAD_DIR.$this->user->username.'/signature.png';
if(!file_exists($signature)){
	echo '<form id="formUpload" action="'.$this->config->base_url().'upsignature/" method="POST" enctype="multipart/form-data">';
	echo '<tr>
		<td width="40%"><span class="errortext">No signature on file.</span><br/>Please upload signature with transparent background first before you issue an NTE.</td>
		<td> 
			<input type="file" name="fileToUpload" id="fileToUpload"/>
			<input type="hidden" name="page" value="'.$_SERVER['REQUEST_URI'].'"/>
		</td>
	</tr>';
	echo '</form>';
}else{
	//NTE history
	if(count($prevNTE) > 0){
		echo '<table class="tableInfo tacenter">';
			echo '<tr class="trhead">
					<td colspan=8 align="center">NTE HISTORY FOR THE LAST 6 MONTHS AND AWOL FOR THE LAST 1 YEAR</td>
				</tr>';
			echo '<tr class="formlabel">';
				echo '<td>NTE ID</td>';
				echo '<td>Type</td>';
				echo '<td>Offense Level</td>';
				echo '<td>Dates</td>';
				echo '<td>Date Issued</td>';
				echo '<td>Issued By</td>';
				echo '<td>Status</td>';
				echo '<td>Sanction</td>';
			echo '</tr>';
			foreach($prevNTE AS $p){
				if($p->type=='AWOL'){
					if($p->status==1) $awolnum = 0;
					else if($p->status==0) $awolnum++;
				}else if($p->type=='tardiness'){
					if($p->status==1) $tardynum = 0;
					else if($p->status==0) $tardynum++;						
				}
				
				echo '<tr>';
					echo '<td><a href="'.$this->config->base_url().'detailsNTE/'.$p->nteID.'/">'.$p->nteID.'</a></td>';
					echo '<td>'.ucfirst($p->type).'</td>';
					echo '<td>'.$this->textM->ordinal($p->offenselevel).'</td>';
					echo '<td>';
						$piolo = explode('|', $p->offensedates);
						foreach($piolo AS $o){
							if($p->type=='AWOL')
								echo date('F d, Y', strtotime($o)).'<br/>';
							else
								echo date('F d, Y h:i a', strtotime($o)).'<br/>';
						}
					echo '</td>';
					echo '<td>'.date('F d, Y h:i a', strtotime($p->dateissued)).'</td>';
					echo '<td>'.$p->issuedBy.'</td>';
					echo '<td>'.$nteStat[$p->status].'</td>';
					echo '<td>'.$p->sanction.'</td>';
				echo '</tr>';
			}
			
			
		echo '</table>';
	}
?>

<form action="" method="POST" onSubmit="return checkvalues();">
	<table class="tableInfo">
		<tr class="trhead">
			<td colspan=2>ISSUE AN NTE</td>
		</tr>
		<tr>
			<td width="30%">NTE Type</td>
			<td>
				<select name="type" class="forminput" id="type">
					<option value="AWOL">AWOL</option>
					<option value="tardiness">Excessive Tardiness</option>
				</select>
			</td>
		</tr>
	<?php 
		if($awolnum==0){
			echo '<tr class="trawol">
					<td colspan=2><p class="errortext">There is pending NTE for CAR. You need to generate CAR first before you can issue another NTE for AWOL.</p></td>
				</tr>';
		}else{
	?>		
		<tr class="trawol">
			<td>Is this what offense?</td>
			<td>
				<input id="offlevelord" type="text" class="forminput" disabled="disabled" value="<?= $this->textM->ordinal($awolnum) ?>"/>
				<input id="offlevel" type="hidden" name="offenselevelawol" value="<?= $awolnum ?>"/>
			</td>
		</tr>
		<tr class="trawol">
			<td>List up to 6 dates of AWOL<br/>
				(absence without an official leave filed) here:
			</td>
			<td>
				<input type="text" name="offensedates[]" class="forminput datepick" placeholder="Date 1"/><br/>
				<input type="text" name="offensedates[]" class="forminput datepick" placeholder="Date 2"/><br/>
				<input type="text" name="offensedates[]" class="forminput datepick" placeholder="Date 3"/><br/>
				<input type="text" name="offensedates[]" class="forminput datepick" placeholder="Date 4"/><br/>
				<input type="text" name="offensedates[]" class="forminput datepick" placeholder="Date 5"/><br/>
				<input type="text" name="offensedates[]" class="forminput datepick" placeholder="Date 6"/>
			</td>
		</tr>
		<?php } 
		
		if($tardynum==0){
			echo '<tr class="trtardiness hidden">
					<td colspan=2><p class="errortext">There is pending NTE for CAR. You need to generate CAR first before you can issue another NTE for Tardiness.</p></td>
				</tr>';
		}else{
		?>
		<tr class="trtardiness hidden">
			<td>Is this what offense?</td>
			<td>
				<input id="offlevelord" type="text" class="forminput" disabled="disabled" value="<?= $this->textM->ordinal($tardynum) ?>"/>
				<input id="offlevel" type="hidden" name="offenseleveltardy" class="forminput" value="<?= $tardynum ?>"/>
			</td>
		</tr>
		<tr class="trtardiness hidden">
			<td>List up to 6 dates and times of tardiness<br/>
				(within one month) here:
			</td>
			<td width="100%">
				<input type="text" name="tdates[0]" class="padding5px datepick" placeholder="Date 1" style="width:60%; float:left;"/><input type="text" name="ttime[0]" class="padding5px dtime" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[1]" class="padding5px datepick" placeholder="Date 2" style="width:60%; float:left;"/><input type="text" name="ttime[1]" class="padding5px dtime" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[2]" class="padding5px datepick" placeholder="Date 3" style="width:60%; float:left;"/><input type="text" name="ttime[2]" class="padding5px dtime" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[3]" class="padding5px datepick" placeholder="Date 4" style="width:60%; float:left;"/><input type="text" name="ttime[3]" class="padding5px dtime" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[4]" class="padding5px datepick" placeholder="Date 5" style="width:60%; float:left;"/><input type="text" name="ttime[4]" class="padding5px dtime" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[5]" class="padding5px datepick" placeholder="Date 6" style="width:60%; float:left;"/><input type="text" name="ttime[5]" class="padding5px dtime" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
			</td>
		</tr>
		
		<?php 
		}

		if($awolnum!=0 || $tardynum!=0){ ?>	
		<tr>
			<td colspan=2>
				<input type="hidden" name="empID_fk" value="<?= $row->empID ?>"/>
				<input type="hidden" name="submitType" value="issueNTE"/>
				<input type="submit" value="Submit" class="btnclass btngreen"/>
				<input type="button" value="Cancel" onClick="parent.$.colorbox.close(); return false;" class="btnclass"/>
			</td>
		</tr>
	<?php } ?>
	</table>
</form>
<?php } ?> 
<?php } ?> 
<script type="text/javascript">
	$(function(){		
		$('#fileToUpload').change(function(){
			$('#formUpload').submit();
		});
		
		$('.datepick').datetimepicker({ 
			format:'F d, Y',
			maxDate:'<?= date('Y-m-d') ?>'
		});	
		
		$('.dtime').datetimepicker({ 
			datepicker:false,
			format:'H:i'
		});	
		
		$('#type').change(function(){
			if($(this).val()=='tardiness'){
				$('.trawol').addClass('hidden');
				$('.trtardiness').removeClass('hidden');
			}else{
				$('.trawol').removeClass('hidden');
				$('.trtardiness').addClass('hidden');
			}
		});
	});
	
	function checkvalues(){
		valid=true;
		if($('#type').val()=='AWOL'){
			valid=false;
			$('input[name="offensedates\[\]"]').each(function() {
				if($(this).val()!='') valid=true;
			});
			if(valid==false){
				alert('Please input offense dates.');
			}
		}else if($('#type').val()=='tardiness'){ 
			xx = 0;
			var ss = new RegExp(/^[0-9]{2}:[0-9]{2}$/);
			for(i=0; i<6; i++){
				var tdates = $('input[name="tdates\['+i+'\]"]').val();
				var ttime = $('input[name="ttime\['+i+'\]"]').val();
				var datetoday = new Date();
				if(tdates!=''){
					xx++;
				}
				if(tdates != '' && ttime=='' ||  tdates == '' && ttime!='' || ttime!='' && ss.test(ttime)==false){
					valid = false;
				}
				
			}
			if(valid==false) alert('Please check your inputted values. Use 24-hour format and date should be on or before today.');
			if(xx==0){ 
				alert('Please input date and time of tardiness.');
				valid=false;
			}
		}
		if(valid==true) displaypleasewait();
		
		return valid;
	}
</script>	
	