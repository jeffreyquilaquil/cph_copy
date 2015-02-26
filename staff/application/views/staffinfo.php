<style>
	.nTD{ width:65px; }
</style>
<?php
	if(count($row)==0){
		echo 'No staff record found.';
	}else{
		if($current=='myinfo') echo '<h2 style="float:left;">My Info</h2>';
		else echo '<h2 style="float:left;">'.$row->fname.' '.$row->lname.'\'s Info</h2>';
		echo '<br/>';
		
		function trDisplay2($label, $text, $field, $editable, $vals='', $placeholder=''){
			if($editable){				
				echo '<tr bgcolor="#ccc"><td width="30%">'.$label.'</td>';
				if(is_array($vals)){
					echo '<td>';
					echo '<select name="'.$field.'" id="'.$field.'" class="forminput"><option value=""></option>';
						foreach($vals AS $k=>$v){
							if($k==$text)
								echo '<option value="'.$k.'" selected>'.$v.'</option>';
							else
								echo '<option value="'.$k.'">'.$v.'</option>';
						}
					echo '</select>';
					echo '</td>';
				}else{
					$datepick = array('bdate', 'startDate', 'endDate', 'accessEndDate', 'regDate');
					if(in_array($field,$datepick)) $class = 'datepick';
					else $class='';
					echo '<td><input type="text" name="'.$field.'" id="'.$field.'" value="'.$text.'" placeholder="'.$placeholder.'" class="forminput '.$class.'"/></td>';
				}
				echo '</tr>';
						
			}else{
				if($field == 'email' || $field == 'pemail') $text = '<a href="mailto:'.$text.'">'.$text.'</a>';
				echo '<tr>
						<td width="20%">'.$label.'</td>
						<td>'.$text.'</td>
					</tr>';
			}
		}
		function trDisplay4($td1, $td2, $td3, $td4){
			echo '<tr>
					<td>'.$td1.'</td>
					<td>'.$td2.'</td>
					<td>'.$td3.'</td>
					<td>'.$td4.'</td>
				</tr>';
		}	
	if(in_array('hr',$this->myaccess) || $this->user->level>0 || $this->user->is_supervisor==1){
		echo '<button style="position:absolute; right:175px; padding:5px; cursor:pointer;" onClick="window.parent.jQuery.colorbox({href:\''.$this->config->base_url().'sendemail/'.$row->empID.'/'.((in_array('hr',$this->myaccess))?'fromHR/':'').'\', iframe:true, width:\'990px\', height:\'600px\'});">Send Email to this Staff</button>';
	} ?>

	<ul class="tabs" style="text-align:right;">
		<li class="tab-link current" data-tab="tab-1">Info</li>
		<li class="tab-link" data-tab="tab-2" id="getNotes" onClick="getNotes('<?= $row->empID ?>', '<?= $row->username ?>');">Notes</li>
	</ul>
	
	<div id="tab-1" class="tab-content current">
	<?php
	if($current == 'myinfo' && count($updatedVal)>0){ ?>
	<table class="tableInfo" style="background-color:#fffaaa;">
		<tr class="trlabel"><td colspan=4>Pending Update Requests</td></tr>
		<tr class="trhead"><td>Fields</td><td>Details</td><td>Date Update Requested</td><td><br/></td></tr>
<?php	foreach($updatedVal AS $u){
			echo '<tr>
					<td>'.$this->staffM->defineField($u->fieldname).'</td><td>';
					
				if($u->fieldname=='title') echo $this->staffM->getSingleField('newPositions', 'title', 'posID="'.$u->fieldvalue.'"');
				else echo $u->fieldvalue;
				
			echo '</td><td>'.date('d M Y H:i',strtotime($u->timestamp)).'</td>
					<td><input type="button" value="Cancel" onClick="cancelRequest('.$u->updateID.', \''.$this->staffM->defineField($u->fieldname).'\', \''.$u->fieldvalue.'\')"></td>
				</tr>';
		} 
		echo '<tr><td colspan=4><br/></td></tr></table>';
	} 
?>
	
		<table class="tableInfo" style="position:relative;">
		<tr class="trlabel" id="pdetails"><td colspan=2>Personal Details <?php if(($current=='myinfo' || count(array_intersect($this->myaccess,array('full','hr')))>0)){ ?><a href="javascript:void(0)" class="edit" onClick="reqUpdate('pdetails')" id="pdetailsupb"><?= ((count(array_intersect($this->myaccess,array('full','hr')))==0)?'Request an ':'') ?>Update</a><?php } ?>
		<div style="padding:10px; background-color:#fff; right:0; top:26px; text-align:center; border:1px solid #ccc; float:right; position:absolute;">			
		<?php 
			$ptimg = 'http://staffthumbnails.s3.amazonaws.com/'.$row->username.'.jpg';
			if(!(@file_get_contents($ptimg, 0, NULL, 0, 1)))
				$ptimg = $this->config->base_url().'css/images/logo.png';
			echo '<a href="'.$ptimg.'" class="imgiframe" title="PT profile picture"/><img src="'.$ptimg.'" width="80px"></a><br/>';
			
			if(count(array_intersect($this->myaccess,array('full','hr')))>0){ 
				echo '<a href="javascript:void(0)" id="updatePTpic">Update</a>';
				echo '<form id="PTpform" action="" method="POST" enctype="multipart/form-data">';
				echo '<input type="file" name="PTpicture" id="PTpicture" class="hidden"/>';
				echo '<input type="hidden" name="submitType" value="uploadPTpicture"/>';
				echo '</form>';
			}		
		?>
		</div>
		</td></tr>
		<?php		
			echo $this->staffM->displayInfo('pdetails', 'username', $row->username, false);
			echo $this->staffM->displayInfo('pdetails', 'lname', $row->lname, true);
			echo $this->staffM->displayInfo('pdetails', 'fname', $row->fname, true);
			echo $this->staffM->displayInfo('pdetails', 'mname', $row->mname, true);
			echo $this->staffM->displayInfo('pdetails', 'suffix', $row->suffix, true);
			echo $this->staffM->displayInfo('pdetails', 'pemail', $row->pemail, true);
			
			echo $this->staffM->displayInfo('pdetails', 'address1', $row->address.' '.$row->city.' '.$row->country.', '.$row->zip, true,'','pdetailsshow'); 
			echo $this->staffM->displayInfo('pdetails', 'address', $row->address, true,'','pdetailshide');
			echo $this->staffM->displayInfo('pdetails', 'city', $row->city, true,'','pdetailshide');
			echo $this->staffM->displayInfo('pdetails', 'country', $row->country, true,'','pdetailshide');
			echo $this->staffM->displayInfo('pdetails', 'zip', $row->zip, true,'','pdetailshide');
			
			echo $this->staffM->displayInfo('pdetails', 'phone', rtrim($row->phone1.', '.$row->phone2,', '), true,'','pdetailsshow');
			echo $this->staffM->displayInfo('pdetails', 'phone1', $row->phone1, true,'','pdetailshide');
			echo $this->staffM->displayInfo('pdetails', 'phone2', $row->phone2, true,'','pdetailshide');
						
			echo $this->staffM->displayInfo('pdetails', 'bdate', (($row->bdate!='0000-00-00')? date('F d, Y',strtotime($row->bdate)) : ''), true);
			echo $this->staffM->displayInfo('pdetails', 'gender', $row->gender, true);
			echo $this->staffM->displayInfo('pdetails', 'maritalStatus', ucfirst($row->maritalStatus), true);
			
			echo $this->staffM->displayInfo('pdetails', 'spouse', ucfirst($row->spouse), true);			
			echo $this->staffM->displayInfo('pdetails', 'dependents', ucfirst($row->dependents), true);			
					
			echo $this->staffM->displayInfo('pdetails', 'sss', $row->sss, true,'00-0000000-0');
			echo $this->staffM->displayInfo('pdetails', 'tin', $row->tin, true,'000-000-000-0000');
			echo $this->staffM->displayInfo('pdetails', 'philhealth', $row->philhealth, true,'00-000000000-0');
			echo $this->staffM->displayInfo('pdetails', 'hdmf', $row->hdmf, true,'0000-0000-0000');
			
			echo $this->staffM->displayInfo('pdetails', 'email', $row->email, false);
			echo $this->staffM->displayInfo('pdetails', 'skype', $row->skype, true);
			echo $this->staffM->displayInfo('pdetails', 'google', $row->google, true);
			
				
			echo '<tr class="pdetailstr pdetailslast hidden"><td colspan=2><i>Please upload documents on My Info page > Personal File to support the request.</i></td></tr>';			
			echo '<tr class="pdetailslast hidden">
					<td colspan=2>
						<button id="pbtnsubmit">Submit</button>&nbsp;&nbsp;<button onClick="reqUpdateCancel(\'pdetails\')">Cancel</button>
					</td>
				</tr>';
		?>
		<tr><td colspan=2><br/></td></tr>
		<tr class="trlabel" id="jdetails"><td colspan=2>Job Details <?php if($this->user->access!='exec'){ ?><a href="javascript:void(0)" class="edit" onClick="reqUpdate('jdetails')" id="jdetailsupb"><?= ((count(array_intersect($this->myaccess,array('full','hr')))==0)?'Request an ':'') ?>Update</a><? } ?></td></tr>
		<?php	
			if(count(array_intersect($this->myaccess,array('full','hr')))>0){
				echo $this->staffM->displayInfo('jdetails', 'idNum', $row->idNum, true);
				echo $this->staffM->displayInfo('jdetails', 'active', $row->active, true);
				echo $this->staffM->displayInfo('jdetails', 'office', ucfirst($row->office), true);
			}else{
				echo $this->staffM->displayInfo('jdetails', 'idNum', $row->idNum, false);
				echo $this->staffM->displayInfo('jdetails', 'office', ucfirst($row->office), false);
			}
			
			
			echo $this->staffM->displayInfo('jdetails', 'shift', $row->shift, true, 'Ex. 07:00am - 04:00pm Mon-Fri');
			echo $this->staffM->displayInfo('jdetails', 'startDate', (($row->startDate!='0000-00-00')? date('F d, Y',strtotime($row->startDate)) : ''), true);			
			echo $this->staffM->displayInfo('jdetails', 'supervisor', $row->supervisor, true);
			echo $this->staffM->displayInfo('jdetails', 'department', $row->department, false);
			echo $this->staffM->displayInfo('jdetails', 'title', $row->title, true);
			echo $this->staffM->displayInfo('jdetails', 'endDate', (($row->endDate!='0000-00-00')? date('F d, Y',strtotime($row->endDate)) : ''), true, 'First day employee is no longer connected with Tate');
		
		if(count(array_intersect($this->myaccess,array('full','hr')))>0 || $this->user->level>0 || $this->user->is_supervisor==1){
			echo $this->staffM->displayInfo('jdetails', 'accessEndDate', (($row->accessEndDate!='0000-00-00')? date('F d, Y',strtotime($row->accessEndDate)) : ''), true, 'First day of no access');
		}
			echo $this->staffM->displayInfo('jdetails', 'empStatus', $row->empStatus, true);
			echo $this->staffM->displayInfo('jdetails', 'regDate', (($row->regDate!='0000-00-00')? date('F d, Y',strtotime($row->regDate)) : ''), true);
			if(count(array_intersect($this->myaccess,array('full','hr')))>0)
				echo $this->staffM->displayInfo('jdetails', 'sal', $row->sal, true, 'Ex. 10,000.00');
			else
				echo $this->staffM->displayInfo('jdetails', 'sal', $row->sal, true, 'Ex. 10,000.00', 'hidden');
			
			echo '<tr class="jdetailslast hidden">
					<td colspan=2>
						<button id="jbtnsubmit">Submit</button>&nbsp;&nbsp;<button onClick="reqUpdateCancel(\'jdetails\')">Cancel</button>
					</td>
				</tr>';		
		?>
		<tr><td colspan=2><br/></td></tr>
	</table>
	
	<table class="tableInfo" id="personalfiletbl">
		<tr class="trlabel"><td colspan=5>Personal File <? if(!in_array("exec", $this->myaccess)){ ?><a href="javascript:void(0)" class="edit" id="addfile">+ Add File</a><? } ?></td></tr>
		<form id="pfformi" action="" method="POST" enctype="multipart/form-data">
			<input type="file" name="pfilei" id="pfilei" class="hidden"/>
			<input type="hidden" name="submitType" value="uploadPF"/>
		</form>
	<?php
		if(count($pfUploaded)==0){
			echo '<tr><td colspan=5>No files uploaded.</td></tr>';
		}else{
			echo '<tr class="trhead">
					<td width="20%">Date Uploaded</td>
					<td width="30%">Uploaded By</td>
					<td width="30%">Document Name</td>					
					<td width="15%">View/Download File</td>
					<td width="5%"><br/></td>
				</tr>';
			foreach($pfUploaded AS $p){
				echo '<tr>';
				echo '<td>'.date('d M Y', strtotime($p->dateUploaded)).'</td>';
				echo '<td>'.$p->uploader.'</td>';
				echo '<td>'.$p->fileName.'</td>';
				if(strpos($p->fileName,'.jpg') !== false || strpos($p->fileName,'.gif') !== false || strpos($p->fileName,'.png') !== false || strpos($p->fileName,'.pdf') !== false)
					echo '<td align="center"><a class="iframe" href="'.$this->config->base_url().UPLOAD_DIR.$row->username.'/'.$p->fileName.'"><img src="'.$this->config->base_url().'css/images/view-icon2.png"/></a></td>';
				else
					echo '<td align="center"><a href="'.$this->config->base_url().UPLOAD_DIR.$row->username.'/'.$p->fileName.'"><img src="'.$this->config->base_url().'css/images/download-icon.gif"/></a></td>';
				
				if($this->user->empID==$p->uploadedBy){
					echo '<td><img src="'.$this->config->base_url().'css/images/delete-icon.png" style="cursor:pointer;" onClick="delFile('.$p->upID.', \''.$p->fileName.'\')"/></td>';
				}else{
					echo '<td><br/></td>';
				}
				echo '</tr>';
			}
		}
		
	?>
		
		<tr><td colspan=4><br/></td></tr>
		<tr class="trlabel"><td colspan=5>Performance Track Records</td></tr>
	<?php
		trDisplay4('Action', 'Date Submitted by Manager', 'Date approved by second level manager', 'Date acknowledged by employee');
	?>
		<tr><td colspan=4><br/></td></tr>
	</table>

	<table class="tableInfo">
		<tr class="trlabel"><td colspan=8>Time Off Details <?php if($this->user->empID==$row->empID){ echo '<a class="edit iframe" href="'.$this->config->base_url().'fileleave/">File for a Leave/Offset</a>'; } ?></td></tr>
		<tr class="trhead"><td colspan=8>Available Leave Credits : <?= $row->leaveCredits ?><?php if($this->user->empID==$row->empID){ echo '&nbsp;&nbsp;&nbsp;<a class="edit" href="javascript:void(0)" id="rupdateTO">Request HR to Recheck Leave Credits</a>'; } if(count(array_intersect($this->myaccess,array('full','hr')))>0){ echo '&nbsp;&nbsp;&nbsp;<a class="edit" href="javascript:void(0)" id="updateLC">Update</a>';} ?></td></tr>
		
		<tr class="toTRclass hidden">
			<td colspan=8>Note to HR:<input type="text" class="forminput" id="noteHR"/></td>
		</tr>
		<tr class="toTRclass hidden">
			<td colspan=8><button id="tosendtoHR">Send to HR</button></td>
		</tr>
		
		<tr class="updateLCtr hidden">
			<td colspan=8>Input correct leave credits:&nbsp;&nbsp;&nbsp;<input type="text" style="width:65%; padding:5px;" id="correctLC"/>&nbsp;&nbsp;&nbsp;<button id="updateLCButton">Update</button></td>
		</tr>
		
<?php if(count($timeoff)>0){ ?>	
		<tr class="trhead">
			<td>Leave ID</td>			
			<td>Type of Leave</td>			
			<td>Start of Leave</td>			
			<td>End of Leave</td>			
			<td>Total Number of Hours</td>			
			<td>Status</td>			
			<td>View</td>			
			<td>Edit</td>			
		</tr>
	<?php
		foreach($timeoff AS $t):						
			echo '
				<tr>
					<td>'.$t->leaveID.'</td>
					<td>'.$leaveTypeArr[$t->leaveType].'</td>
					<td>'.date('M d, Y h:i a', strtotime($t->leaveStart)).'</td>
					<td>'.date('M d, Y h:i a', strtotime($t->leaveEnd)).'</td>
					<td align="center">'.$t->totalHours.' hours</td>
					<td>'.$this->staffM->getLeaveStatusText($t->status, $t->iscancelled).'</td>
					<td><a class="iframe" href="'.$this->config->base_url().'leavepdf/'.$t->leaveID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>
					<td><a class="iframe" href="'.$this->config->base_url().'staffleaves/'.$t->leaveID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
				</tr>
			';
		endforeach;
	?>
		<tr><td colspan=6><br/></td></tr>
	</table>
<?php }
	if(count($disciplinary)>0){
?>	
	<table class="tableInfo">
		<tr class="trlabel" id="disciplinary"><td colspan=8>Disciplinary Records</td></tr>
		<tr class="trhead">	
			<td>Action</td>
			<td>Type</td>
			<td>Level of Offense</td>
			<td>Date Issued</td>
			<td>Issued By</td>
			<td>CAR</td>
			<td>Edit</td>
		</tr>
	<?php
		foreach($disciplinary AS $dis):
			echo '
				<tr>	
					<td>NTE</td>
					<td>'.$dis->type.'</td>
					<td>'.$this->staffM->ordinal($dis->offenselevel).' Offense</td>
					<td>'.date('M d, Y', strtotime($dis->dateissued)).'</td>
					<td>'.$dis->issuerName.'</td>';
			if($dis->status==0)
				echo '<td><a class="iframe" href="'.$this->config->base_url().'ntepdf/'.$dis->nteID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>';
			else
				echo '<td>Not yet generated.</td>';
			echo 	'<td><a class="iframe" href="'.$this->config->base_url().'detailsNTE/'.$dis->nteID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>
				</tr>
			';
		endforeach;		
	?>
		
		<tr><td colspan=6><br/></td></tr>
	</table>
<?php } ?>

	<table class="tableInfo">
		<tr class="trlabel"><td colspan=4>Attendance Logs</td></tr>
	</table>
	</div><? //end of tab-1 ?>
	<div id="tab-2" class="tab-content">
		<table class="tableInfo">
			<tr class="trheadnote trlabel"><td>
				<select id="nselection" class="">
					<option value="">All</option>
					<option value="4">Disciplinary</option>
					<option value="3">Time Off</option>
					<option value="2">Performance</option>
					<option value="1">Salary</option>
					<option value="5">Actions</option>
					<option value="0">Other</option>
				</select>
				<a href="javascript:void(0)" class="edit" onClick="addnote('show');">+ Add Note</a>
			</td></tr>
			<tr class="traddnote hidden trlabel"><td>Add Note</td></tr>
			<tr class="traddnote hidden">
				<td>
					<form action="" method="POST">
						<textarea name="ntexts" class="forminput" rows="2"></textarea><br/>
						Type: <select name="ntype" id="ntypeselect">
							<option value="0">Other</option>
							<option value="4">Disciplinary</option>
							<option value="3">Time Off</option>
							<option value="2">Performance</option>
							<option value="1">Salary</option>					
						</select> 
						<span id="acctype">
						Access Type: <select name="accesstype" id="accesstype">
							<option value="assoc">All</option>							
							<option value="exec">Exec and up</option>	
							<option value="full">Only full access</option>
						</select> 
						</span>
						<input type="hidden" name="submitType" value="addnote"/>
						<input type="hidden" name="empID_fk" value="<?= $row->empID ?>"/>
						<input type="submit" value="Add Note"/> <input type="button" value="Cancel" onClick="addnote('hide')"/>
					</form>
				</td>
			</tr>
			<tr class="traddnote hidden"><td><br/></td></tr>
		</table>
		
		<div id="myNotes"></div>
		<div id="loadingNotes" style="text-align:center; width:100%; padding-top:20px;"><img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>"/></div>
		
	</div>	

<?php } ?>
<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	$(function () { 
		$(".imgiframe").colorbox({rel:'PT profile picture'});
		tinymce.init({
			selector: "textarea",	
			menubar : false,
			plugins: [
				"link",
				"code",
				"table"
			],
			toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table code"
		});

		$('.pdetailshide').addClass('hidden');
		$('.jdetailshide').addClass('hidden');
				
		if($('#maritalStatus').val()=='Single'){
			$('#spouse').parents('tr').addClass('hidden');
			$('#dependents').parents('tr').addClass('hidden');
		}
		
		$('#maritalStatus').change(function(){
			if($('#maritalStatus').val() == 'Single'){
				$('#spouse').parents('tr').addClass('hidden');
				$('#dependents').parents('tr').addClass('hidden');
				$('#spouse').val('');
				$('#dependents').val('');
			}else{
				$('#spouse').parents('tr').removeClass('hidden');
				$('#dependents').parents('tr').removeClass('hidden');
			}
		});
		
		$('#title').change(function(){
			alert('Request for changing position title will also request change in department if position belongs to different department.');
		});
		
		$('#nselection').change(function(){
			v = $(this).val();
			$('.nnotes').removeClass('hidden');
			if(v!=''){
				for(i=0; i<=5; i++){
					if(v!=i)
						$('.nstat_'+i).addClass('hidden');
				}
			}
		});
		
		$('#ntypeselect').change(function(){
			if($(this).val()=='0'){
				$('#acctype').removeClass('hidden');
			}else{
				$('#acctype').addClass('hidden');
				$('#accesstype').val('');
			}
		});
		
		$('#updatePTpic').click(function(){
			$('#PTpicture').trigger('click');
		});
		$('#PTpicture').change(function(){
			displaypleasewait();
			$('#PTpform').submit();				
		});
		
		$('#addfile').click(function(){
			$('#pfilei').trigger('click');
		});
		$('#pfilei').change(function(){
			displaypleasewait();
			$('#pfformi').submit();				
		});
		
		$('#rupdateTO').click(function(){
			$('.toTRclass').removeClass('hidden');
		});
		
		$('#updateLC').click(function(){
			$('.updateLCtr').removeClass('hidden');
		});
		
		$('#updateLCButton').click(function(){
			hobbit = $('#correctLC').val();
			if(hobbit==''){
				alert('Correct leave credit is empty.');
			}else if($.isNumeric(hobbit)==false){
				alert('Inputted is invalid.');
			}else{
				displaypleasewait();
				$.post('<?= $this->config->item('career_uri') ?>',{
					submitType:'editleavecredits',					
					newleavecredits:$('#correctLC').val(),
					oldleavecredit:'<?= $row->leaveCredits ?>',
					empID:'<?= $row->empID ?>',
					empName:'<?= $row->name ?>'
				},function(){
					alert('Leave credits has been updated.');
					parent.location.reload();
				});
			}
		});
		
		
				
		$('#pbtnsubmit').click(function(){
			valid = true;
			var validText = '';
			
			var ssspattern = new RegExp(/^[0-9]{2}-[0-9]{7}-[0-9]{1}$/);
			var tinpattern = new RegExp(/^[0-9]{3}-[0-9]{3}-[0-9]{3}-[0-9]{4}$/); 
			var phpattern = new RegExp(/^[0-9]{2}-[0-9]{9}-[0-9]{1}$/);  
			var hdmfpattern = new RegExp(/^[0-9]{4}-[0-9]{4}-[0-9]{4}$/);
			
			if($('#lname').val()==''){ validText += '\t- Last Name is empty.\n'; valid = false; }
			if($('#fname').val()==''){ validText += '\t- First Name is empty.\n'; valid = false; }
			
			if($('#sss').val() != '' &&  ssspattern.test($('#sss').val())==false){
				validText += '\t- SSS Invalid\n';
				valid = false;	
			}
			if($('#tin').val() != '' &&  tinpattern.test($('#tin').val())==false){
				validText += '\t- TIN Invalid\n';
				valid = false;	
			}
			if($('#philhealth').val() != '' &&  phpattern.test($('#philhealth').val())==false){
				validText += '\t- Philhealth Invalid\n';
				valid = false;	
			}
			if($('#hdmf').val() != '' &&  hdmfpattern.test($('#hdmf').val())==false){
				validText += '\t- HDMF Invalid\n';
				valid = false;	
			}
			
			if(validText != ''){
				valid = false;
				alert('Please check missing/error values: \n'+validText);
			}
			
			if(valid){
				displaypleasewait();
				$.post("<?= $this->config->item('career_uri') ?>",{
					submitType:'pdetails',
					empID:'<?= $row->empID ?>',
					lname:$('#lname').val(),
					fname:$('#fname').val(),
					mname:$('#mname').val(),
					suffix:$('#suffix').val(),
					pemail:$('#pemail').val(),
					address:$('#address').val(),
					city:$('#city').val(),
					country:$('#country').val(),
					zip:$('#zip').val(),
					phone1:$('#phone1').val(),
					phone2:$('#phone2').val(),
					bdate:$('#bdate').val(),
					gender:$('#gender').val(),
					maritalStatus:$('#maritalStatus').val(),
					spouse:$('#spouse').val(),
					dependents:$('#dependents').val(),
					sss:$('#sss').val(),
					tin:$('#tin').val(),
					philhealth:$('#philhealth').val(),
					hdmf:$('#hdmf').val(),
					skype:$('#skype').val(),
					google:$('#google').val()
				},function(){
					location.reload();
				});
			}
		});
		
		$('#jbtnsubmit').click(function(){
			validtxt = '';
			/* var spattern = new RegExp(/^[0-9]{2}:[0-9]{2}[a-z]{2}\s-\s[0-9]{2}:[0-9]{2}[a-z]{2}\s[a-zA-Z]{3}-[a-zA-Z]{3}$/);
			if($('#shift').val()!='' && spattern.test($('#shift').val())==false){
				validtxt = 'Shift Schedule is Invalid. Valid format sample is: 07:00am - 04:00pm Mon-Fri\n';
			} */
			
			if($('#empStatus').val()=='regular' && $('#regDate').val()==''){
				validtxt = 'Please input regularization date.';
			}
			
			if(validtxt!=''){
				alert(validtxt);
			}else{
				displaypleasewait();
				$.post("<?= $this->config->item('career_uri') ?>",{
					submitType:'jdetails',
					empID:'<?= $row->empID ?>',
					office:$('#office').val(),
					shift:$('#shift').val(),
					startDate:$('#startDate').val(),
					supervisor:$('#supervisor').val(),
					title:$('#title').val(),
					empStatus:$('#empStatus').val(),
					regDate:$('#regDate').val(),
					endDate:$('#endDate').val(),
					accessEndDate:$('#accessEndDate').val(),
					sal:$('#sal').val(),
					active:$('#active').val()
				},function(){
					location.reload();
				});
			}
		});
		
		$('#tosendtoHR').click(function(){
			if($('#noteHR').val()==''){
				alert('Please input note to HR');
			}else{
				displaypleasewait();
				$.post("<?= $this->config->item('career_uri') ?>",{
					submitType:'uLeaveC',
					note:$('#noteHR').val()
				}, function(){
					location.reload();
				});
			}
		});
			 
	}); 
		
	function addnote(d){
		if(d=='show'){
			$('.traddnote').removeClass('hidden');
			$('.trheadnote').addClass('hidden');
		}else{
			$('.traddnote').addClass('hidden');
			$('.trheadnote').removeClass('hidden');
		}
	}
	
	function delFile(id, fname){
		if(confirm('Are you sure you want to delete this file?')){
			displaypleasewait();
			$.post("<?= $this->config->item('career_uri') ?>",{
				submitType:'delFile',
				upID:id,
				fileName:fname
			},function(){
				alert('File has been deleted.');
				location.reload();
			});
		}
	}
	
	function cancelRequest(id, f, fname){
		if(confirm('Are you sure you want to cancel this request?')){
			displaypleasewait();
			$.post("<?= $this->config->base_url() ?>myinfo/",{
				submitType:'cancelRequest',
				updateID:id,
				fld:f,
				fname:fname
			},function(){
				alert('Request has been cancelled.');
				location.reload();
			});
		}
	}	
	
	function reqUpdate(fld){
		$('.'+fld+'tr').attr('bgcolor','#ccc');
		$('.'+fld+'input').removeClass('hidden');
		$('.'+fld+'last').removeClass('hidden');
		$('.'+fld+'hide').removeClass('hidden');
		
		$('.'+fld+'fld').addClass('hidden');	
		$('.'+fld+'show').addClass('hidden');
		$('#'+fld+'upb').addClass('hidden');
	}
		
	function reqUpdateCancel(fld){
		$('.'+fld+'tr').attr('bgcolor','');
		$('.'+fld+'input').addClass('hidden');
		$('.'+fld+'last').addClass('hidden');
		$('.'+fld+'hide').addClass('hidden');
		
		$('.'+fld+'fld').removeClass('hidden');	
		$('.'+fld+'show').removeClass('hidden');
		$('#'+fld+'upb').removeClass('hidden');
	}
	
	function getNotes(empID, username){
		$.post('<?= $this->config->base_url().'myNotes/' ?>',{empID:empID, username:username}, 
		function(notes){
			$('#myNotes').html(notes);
			$('#loadingNotes').addClass('hidden');
			$('#getNotes').removeAttr('onClick');
		});		
	}
	
	function addGetNotes(empID, username, halo, t){
		$(t).html('<img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>"/>');
		$(t).removeAttr('onClick');
		$.post('<?= $this->config->base_url().'myNotes/' ?>',{empID:empID, username:username, halo:halo}, 
		function(notes){
			$('#myNotes').append(notes);
			$(t).addClass('hidden');
		});
	}
			
</script>
