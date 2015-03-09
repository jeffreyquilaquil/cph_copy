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
		<li class="tab-link" data-tab="tab-2">Notes</li>
	</ul>

<!----------------------- START OF TAB 1 ----------------------->		
	<div id="tab-1" class="tab-content current">
<!----------------------- PENDING UPDATED DETAILS ----------------------->		
	<?php
	if($current == 'myinfo' && count($updatedVal)>0){ ?>
	<table class="tableInfo" style="background-color:#fffaaa;">
		<tr class="trlabel"><td colspan=4>Pending Update Requests</td></tr>
		<tr class="trhead"><td>Fields</td><td>Details</td><td>Date Update Requested</td><td><br/></td></tr>
<?php	foreach($updatedVal AS $u){
			echo '<tr>
					<td>'.$this->staffM->defineField($u->fieldname).'</td><td>';
					
				if($u->fieldname=='title') 
					echo $this->staffM->getSingleField('newPositions', 'title', 'posID="'.$u->fieldvalue.'"');
				else if($u->fieldname=='supervisor')
					echo $this->staffM->getSingleField('staffs', 'CONCAT(fname," ",lname) AS name', 'empID="'.$u->fieldvalue.'"');
				else if($u->fieldname=='levelID_fk')
					echo $this->staffM->getSingleField('orgLevel', 'levelName AS name', 'levelID="'.$u->fieldvalue.'"');
				else{
					if($u->fieldname=='sal' || $u->fieldname=='allowance') echo 'Php '.$u->fieldvalue;
					else if($u->fieldname=='bankAccnt' || $u->fieldname=='hmoNumber') echo $this->staffM->decryptText($u->fieldvalue);
					else echo $u->fieldvalue;
				}				
				
			echo '</td><td>'.date('d M Y H:i',strtotime($u->timestamp)).'</td>
					<td><input type="button" value="Cancel" onClick="cancelRequest('.$u->updateID.', \''.$this->staffM->defineField($u->fieldname).'\', \''.$u->fieldvalue.'\')"></td>
				</tr>';
		} 
		echo '<tr><td colspan=4><br/></td></tr></table>';
	} 
?>
<!----------------------- PERSONAL DETAILS ----------------------->			
		<table class="tableInfo" style="position:relative;">
			<?php 		
			echo '<tr class="trlabel" id="pdetails">';
			echo '<td colspan=2>Personal Details ';		
				if(($current=='myinfo' || count(array_intersect($this->myaccess,array('full','hr')))>0)){
					echo '<a href="javascript:void(0)" class="edit" onClick="reqUpdate(\'pdetails\')" id="pdetailsupb">';
					echo ((count(array_intersect($this->myaccess,array('full','hr')))==0)?'Request an ':'');
					echo 'Update</a>';
				}
				
				echo '<div style="padding:10px; background-color:#fff; right:0; top:26px; text-align:center; border:1px solid #ccc; float:right; position:absolute;">';
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
				echo '</div>';		
			echo '</td></tr>';		
			
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
					<td colspan=2 align="right">
						<button id="pbtnsubmit" class="padding5px">Submit</button>&nbsp;&nbsp;<button onClick="reqUpdateCancel(\'pdetails\')" class="padding5px">Cancel</button>
					</td>
				</tr>';
			?>
			<tr><td colspan=2><br/></td></tr>
		</table>
		
<!----------------------- JOB DETAILS ----------------------->			
		<?php	
		echo '<table class="tableInfo" id="jobtbl">';
			echo '<tr class="trlabel" id="jdetails">';
			echo '<td colspan=2>Job Details &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay(\'jobtbl\', this)" class="droptext">Show</a>]';		
				if(($current=='myinfo' || count(array_intersect($this->myaccess,array('full','hr')))>0)){
					echo '<a href="javascript:void(0)" class="edit hidden" onClick="reqUpdate(\'jdetails\')" id="jdetailsupb">';
					echo ((count(array_intersect($this->myaccess,array('full','hr')))==0)?'Request an ':'');
					echo 'Update</a>';
				}
			echo '</td></tr>';
		echo '</table>';
		
		echo '<table class="tableInfo hidden" id="jobtblData">';		
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
			echo $this->staffM->displayInfo('jdetails', 'levelID_fk', $row->levelID_fk, true);				
			echo $this->staffM->displayInfo('jdetails', 'endDate', (($row->endDate!='0000-00-00')? date('F d, Y',strtotime($row->endDate)) : ''), true, 'First day employee is no longer connected with Tate');
		
		if(count(array_intersect($this->myaccess,array('full','hr')))>0 || $this->user->level>0 || $this->user->is_supervisor==1){
			echo $this->staffM->displayInfo('jdetails', 'accessEndDate', (($row->accessEndDate!='0000-00-00')? date('F d, Y',strtotime($row->accessEndDate)) : ''), true, 'First day of no access');
		}
			echo $this->staffM->displayInfo('jdetails', 'empStatus', $row->empStatus, true);
			echo $this->staffM->displayInfo('jdetails', 'regDate', (($row->regDate!='0000-00-00')? date('F d, Y',strtotime($row->regDate)) : ''), true);
									
			echo '<tr class="jdetailslast hidden">
					<td colspan=2 align="right">
						<button id="jbtnsubmit"  class="padding5px">Submit</button>&nbsp;&nbsp;<button onClick="reqUpdateCancel(\'jdetails\')" class="padding5px">Cancel</button>
					</td>
				</tr>';		
		?>
		<tr><td colspan=2><br/></td></tr>
	</table>
<!----------------------- COMPENSATION DETAILS ----------------------->	
<?php 
	if(count(array_intersect($this->myaccess,array('full','hr','finance')))>0 || $this->user->empID=$row->empID){
	echo '<table class="tableInfo" id="compensationtbl">';
		echo '<tr class="trlabel" id="cdetails">';
		echo '<td colspan=2>Compensation Details &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay(\'compensationtbl\', this)" class="droptext">Show</a>]';		
			if(($current=='myinfo' || count(array_intersect($this->myaccess,array('full','hr','finance')))>0)){
				echo '<a href="javascript:void(0)" class="edit hidden" onClick="reqUpdate(\'cdetails\')" id="cdetailsupb">';
				echo ((count(array_intersect($this->myaccess,array('full','hr','finance')))==0)?'Request an ':'');
				echo 'Update</a>';
			}
		echo '</td></tr>';
	echo '</table>';	
?>			
	<table class="tableInfo hidden" id="compensationtblData">
	<?php	
		echo $this->staffM->displayInfo('cdetails', 'sal', $this->staffM->convertNumFormat($row->sal), true, 'Ex. 10,000.00');
		echo $this->staffM->displayInfo('cdetails', 'allowance', $this->staffM->convertNumFormat($row->allowance), true, 'Ex. 2,500.00');	
		echo $this->staffM->displayInfo('cdetails', 'bankAccnt', $row->bankAccnt, true);	
		echo $this->staffM->displayInfo('cdetails', 'hmoNumber', $row->hmoNumber, true);	
		echo '<tr class="cdetailslast hidden">
				<td colspan=2 align="right">
					<button id="cbtnsubmit" class="padding5px">Submit</button>&nbsp;&nbsp;<button onClick="reqUpdateCancel(\'cdetails\')" class="padding5px">Cancel</button>
				</td>
			</tr>';	
	?>
	</table>
<?php } ?>	
<!----------------------- PERSONAL FILES ----------------------->	
	<table class="tableInfo" id="personalfiletbl">
		<tr class="trlabel">
			<td>
				Personal Files &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('personalfiletbl', this)" class="droptext">Show</a>]
					<? if(!in_array("exec", $this->myaccess)){ ?><a href="javascript:void(0)" class="edit" id="addfile">+ Add File</a><? } ?>
				<form id="pfformi" action="" method="POST" enctype="multipart/form-data">
					<input type="file" name="pfilei" id="pfilei" class="hidden"/>
					<input type="hidden" name="submitType" value="uploadPF"/>
				</form>
			</td>
		</tr>		
	</table>
	<?php
	echo '<table class="tableInfo hidden" id="personalfiletblData">';
		if(count($pfUploaded)==0){
			echo '<tr><td colspan=3>No files uploaded.</td></tr>';
		}else{
			echo '<tr class="trhead">
					<td>Date Uploaded</td>								
					<td>Document Name</td>
					<td width="150px"><br/></td>
				</tr>';
			foreach($pfUploaded AS $p){
				echo '<tr>';
				echo '<td>'.date('d M Y', strtotime($p->dateUploaded)).'</td>';			
				echo '<td>
						<span class="upClass_'.$p->upID.'">'.(($p->docName!='')?$p->docName:$p->fileName).'</span>
						<input id="uploadDoc_'.$p->upID.'" type="text" value="'.(($p->docName!='')?$p->docName:$p->fileName).'" class="forminput hidden uploadDoc'.$p->upID.'"/>
					</td>';
					
				echo '<td align="right">';
				
					if(strpos($p->fileName,'.jpg') !== false || strpos($p->fileName,'.gif') !== false || strpos($p->fileName,'.png') !== false || strpos($p->fileName,'.pdf') !== false){
						echo '<a class="iframe" href="'.$this->config->base_url().UPLOAD_DIR.$row->username.'/'.$p->fileName.'"><img src="'.$this->config->base_url().'css/images/view-icon2.png"/></a>';
					}else{
						echo '<a href="'.$this->config->base_url().UPLOAD_DIR.$row->username.'/'.$p->fileName.'"><img src="'.$this->config->base_url().'css/images/download-icon.gif"/></a>';
					}
					
					if((count(array_intersect($this->myaccess,array('full','hr')))>0)){
						echo '<img src="'.$this->config->base_url().'css/images/view-icon.png" onClick="editUploadDoc('.$p->upID.', 0)" class="cpointer upClass_'.$p->upID.'"/>
							<button class="uploadDoc'.$p->upID.' hidden" onClick="editUploadDoc('.$p->upID.', 1)">Update</button>
							<img id="uploadDocimg'.$p->upID.'" src="'.$this->config->base_url().'css/images/small_loading.gif'.'" width="25" class="hidden"/>';
						echo '<img src="'.$this->config->base_url().'css/images/delete-icon.png" style="cursor:pointer;" onClick="delFile('.$p->upID.', \''.$p->fileName.'\')"/>';
					}
				
				echo '</td>';
				
				echo '</tr>';
			}
		}
	echo '</table>';
	
		/* echo '<tr><td colspan=4><br/></td></tr>';
		echo '<tr class="trlabel"><td colspan=6>Performance Track Records</td></tr>';
		trDisplay4('Action', 'Date Submitted by Manager', 'Date approved by second level manager', 'Date acknowledged by employee');
		echo '<tr><td colspan=4><br/></td></tr>'; */
	?>
	
<!----------------------- TIME OFF DETAILS ----------------------->		
	<table class="tableInfo" id="timeOff">
		<tr class="trlabel">
			<td>Time Off Details &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('timeOff', this)" class="droptext">Show</a>]
			<?php if($this->user->empID==$row->empID){ echo '<a class="edit iframe" href="'.$this->config->base_url().'fileleave/">File for a Leave/Offset</a>'; } ?>
			</td>
		</tr>
		<tr class="trhead"><td>Available Leave Credits : <?= $row->leaveCredits ?><?php if($this->user->empID==$row->empID && (count(array_intersect($this->myaccess,array('full','hr')))==0)){ echo '&nbsp;&nbsp;&nbsp;<a class="edit" href="javascript:void(0)" id="rupdateTO">Request HR to Recheck Leave Credits</a>'; } if(count(array_intersect($this->myaccess,array('full','hr')))>0){ echo '&nbsp;&nbsp;&nbsp;<a class="edit" href="javascript:void(0)" id="updateLC">Update</a>';} ?></td></tr>
		
		<tr class="toTRclass hidden">
			<td colspan=8>Note to HR:<input type="text" class="forminput" id="noteHR"/></td>
		</tr>
		<tr class="toTRclass hidden">
			<td colspan=8><button id="tosendtoHR">Send to HR</button></td>
		</tr>
		
		<tr class="updateLCtr hidden">
			<td colspan=8>Input correct leave credits:&nbsp;&nbsp;&nbsp;<input type="text" style="width:65%; padding:5px;" id="correctLC"/>&nbsp;&nbsp;&nbsp;<button id="updateLCButton">Update</button></td>
		</tr>		
	</table>
	
	<table class="tableInfo hidden" id="timeOffData">
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
					<td>';
					
					if($t->iscancelled==4){
						echo '<a href="'.$this->config->base_url().'sendemail/addinfoleavesubmitted/'.$t->leaveID.'/'.'" class="iframe">'.$this->staffM->getLeaveStatusText($t->status, $t->iscancelled).'<br/>Click here to confirm submission of required information</a>';
					}else{
						echo $this->staffM->getLeaveStatusText($t->status, $t->iscancelled);
					}
				echo '</td>
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
		<?php //echo '<tr class="trlabel"><td colspan=4>Attendance Logs</td></tr>' ?>
	</table>

	</div><? //end of tab-1 ?>
<!----------------------- END OF TAB 1 ----------------------->		
<!----------------------- START OF TAB 2 NOTES ----------------------->		
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
		
		<div id="loadImg" style="text-align:center; margin-top:20px;"><img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>"/></div>
		<iframe id="iframeNotes" src="<?= $this->config->base_url().'notes/'.$row->empID.'/' ?>" frameBorder="0" style="width:100%; height:500px;" onLoad="document.getElementById('loadImg').style.display='none';"></iframe>
	</div>	
<!----------------------- END OF TAB 2 ----------------------->		
<?php } ?>
<script type="text/javascript" src="<?= $this->config->base_url() ?>js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
	EMPID = '<?= $row->empID ?>';
	LEAVECREDITS = '<?= $row->leaveCredits ?>';
	RNAME = '<?= $row->name ?>';
</script>
<script type="text/javascript" src="<?= $this->config->base_url() ?>js/staffinfo.js"></script>