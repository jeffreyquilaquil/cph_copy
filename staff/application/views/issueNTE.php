<h2>Issue an NTE for <?= $row->name ?></h2>
<hr/>
<?php
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

if($prevID!=''){ ?>
<b>Details of Last Issued NTE</b>
<table class="tableInfo">
	<tr>
		<td width="30%">Offense Number</td>
		<td><?= $this->staffM->ordinal($prev->offenselevel).' Offense' ?></td>
	</tr>
	<tr>
		<td>Date NTE was issued</td>
		<td><?= date('F d, Y', strtotime($prev->dateissued)) ?></td>
	</tr>
	<tr>
		<td>AWOL Dates</td>
		<td>
		<?php
			$aw = explode('|', $prev->offensedates);
			foreach($aw AS $a):
				echo date('F d, Y',strtotime($a)).'<br/>';
			endforeach;
		?>
		</td>
	</tr>
	<tr>
		<td>Prescribed Sanction</td>
		<td><?= $sanctionArr[$prev->offenselevel] ?></td>
	</tr>
	<tr>
		<td>Sanction Issued</td>
		<td>
	<?php 
		if($prev->status==0)
			echo $prev->sanction;
		else
			echo '<b>CAR not yet generated</b>';
	?>	
		</td>
	</tr>
<?php if($prev->suspensiondates != '' && $prev->sanction !='Termination'){ ?>
		<tr>
			<td>Suspension Dates</td>
			<td>
		<?php
			$sdates = explode('|', $prev->suspensiondates);
			foreach($sdates AS $s):
				echo date('F d, Y',strtotime($s)).'<br/>';
			endforeach;
		?>
			</td>
		</tr>
<?php }
	$nlevel = $prev->offenselevel + 1;
	if($nlevel>3) $nextsanction = 'Termination';
	else $nextsanction = $sanctionArr[$nlevel];
?>
	<tr>
		<td>Any subsequent case will merit</td>
		<td><?= $nextsanction ?></td>
	</tr>
</table>
<br/><br/>
<b>Issue an NTE</b>
<hr/>
<?php
}
?>
<form action="" method="POST" onSubmit="return checkvalues();">
	<table class="tableInfo">
		<tr>
			<td width="30%">NTE Type</td>
			<td>
				<select name="type" class="forminput" id="type">
					<option value="AWOL">AWOL</option>
					<option value="tardiness">Excessive Tardiness</option>
				</select>
		</td>
		</tr>
		<tr class="trawol">
			<td>Is this what offense?</td>
			<td>
				<select name="offenselevel" class="forminput">
					<option value="1">1st</option>
					<option value="2">2nd</option>
					<option value="3">3rd</option>
				</select>
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
		
		<tr class="trtardiness hidden">
			<td>Is this what offense?</td>
			<td>
				<select name="offenselevel" class="forminput">
					<option value="1">1st</option>
					<option value="2">2nd</option>
					<option value="3">3rd</option>
					<option value="4">4th</option>
				</select>
			</td>
		</tr>
		<tr class="trtardiness hidden">
			<td>List up to 6 dates and times of tardiness<br/>
				(within one month) here:
			</td>
			<td width="100%">
				<input type="text" name="tdates[0]" class="padding5px datepick" placeholder="Date 1" style="width:60%; float:left;"/><input type="text" name="ttime[0]" class="padding5px" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[1]" class="padding5px datepick" placeholder="Date 2" style="width:60%; float:left;"/><input type="text" name="ttime[1]" class="padding5px" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[2]" class="padding5px datepick" placeholder="Date 3" style="width:60%; float:left;"/><input type="text" name="ttime[2]" class="padding5px" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[3]" class="padding5px datepick" placeholder="Date 4" style="width:60%; float:left;"/><input type="text" name="ttime[3]" class="padding5px" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[4]" class="padding5px datepick" placeholder="Date 5" style="width:60%; float:left;"/><input type="text" name="ttime[4]" class="padding5px" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
				<input type="text" name="tdates[5]" class="padding5px datepick" placeholder="Date 6" style="width:60%; float:left;"/><input type="text" name="ttime[5]" class="padding5px" placeholder="hh:mm" style="float:left; margin-left:10px;"/> <br/>
			</td>
		</tr>
		
		
		<tr>
			<td colspan=2>
				<input type="hidden" name="empID_fk" value="<?= $row->empID ?>"/>
				<input type="hidden" name="submitType" value="issueNTE"/>
				<input type="submit" value="Submit"/>
				<input type="button" value="Cancel" onClick="parent.$.colorbox.close(); return false;"/>
			</td>
		</tr>
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
	