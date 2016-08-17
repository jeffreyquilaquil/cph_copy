<style>
	.nTD{ width:65px; }
</style>
<?php
	if(count($row)==0){
		echo 'No staff record found.';
	}else{
		if($current=='myinfo') echo '<h2 style="float:left;">My Info</h2>';
		else{
			echo '<div style="float:left;"><h2>'.$row->fname.' '.$row->lname.'\'s Info ';
				if($row->active==0) echo '<span class="errortext"><b>[Not Active]</b></span>';
			echo '</h2></div>';
			
			if(($this->access->accessFullHR==true || $this->commonM->checkStaffUnderMe($row->username)) && $row->empStatus=='probationary') echo '<div style="float:left; padding:5px; text-align:center;" class="errortext"><a href="'.$this->config->base_url().'evaluationsupervisor/'.$row->empID.'/" class="iframe"><u style="color:red; font-size:165%;">Probationary</u></a><br/><i>Click to change</i></div>';
			
		}
		echo '<br/>';
		//echo '<h2 style="float:left;">'..(($row->active==0)?'<span class="errortext"><b>[Not Active]</b></span>':'').'</h2>';
			
	if($this->access->accessHR==true || $this->user->level>0){
		echo '<button style="position:absolute; right:175px; padding:5px; cursor:pointer;" onClick="window.parent.jQuery.colorbox({href:\''.$this->config->base_url().'sendemail/'.$row->empID.'/\', iframe:true, width:\'990px\', height:\'600px\'});">Send Email to this Staff</button>';
	} 
	
	echo '<ul class="tabs" style="text-align:right;">';
	echo '<li class="tab-link current" data-tab="tab-1">Info</li> ';	
	if($current=='myinfo' || $this->access->accessFullHR==true || $isUnderMe==true || $this->access->accessExec==true){
		echo '<li class="tab-link" data-tab="tab-2">Notes</li>';	
	}
	echo '</ul>';	
	
	?>	

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
					<td>'.$this->textM->constantText('txt_'.$u->fieldname).'</td>';
				echo '<td>'.$this->staffM->infoTextVal($u->fieldname, $u->fieldvalue).'</td>';
				echo '<td>'.date('d M Y H:i',strtotime($u->daterequested)).'</td>
					<td><input type="button" value="Cancel" onClick="cancelRequest('.$u->updateID.', \''.$u->fieldname.'\', \''.$u->fieldvalue.'\')"></td>
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
				if($this->access->accessFullHR==true || $current=='myinfo'){
					echo '<a href="javascript:void(0)" class="edit" onClick="reqUpdate(\'pdetails\')" id="pdetailsupb">';
					echo (($this->access->accessFullHR==false)?'Request an ':'');
					echo 'Update</a>';
				}
				
				echo '<div class="pdetailsfld" style="padding:10px; background-color:#fff; right:0; top:26px; text-align:center; border:1px solid #ccc; float:right; position:absolute;">';
					$ptimg = 'http://staffthumbnails.s3.amazonaws.com/'.$row->username.'.jpg';
					if(!(@file_get_contents($ptimg, 0, NULL, 0, 1)))
						$ptimg = $this->config->base_url().'css/images/logo.png';
					echo '<a href="'.$ptimg.'" class="imgiframe" title="PT profile picture"/><img src="'.$ptimg.'" width="80px"></a><br/>';
					
					if($this->access->accessFullHR==true){ 
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
			
			if($this->user->empID==$row->empID || $this->access->accessFullHR==true){
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
				echo $this->staffM->displayInfo('pdetails', 'maiden_name', ucfirst($row->maiden_name), true, '', 'pdetailshide');				
				echo $this->staffM->displayInfo('pdetails', 'dependents', ucfirst($row->dependents), true);	
				echo $this->staffM->displayInfo('pdetails', 'sss', $row->sss, true,'00-0000000-0');
				echo $this->staffM->displayInfo('pdetails', 'tin', $row->tin, true,'000-000-000-0000');
				echo $this->staffM->displayInfo('pdetails', 'philhealth', $row->philhealth, true,'00-000000000-0');
				echo $this->staffM->displayInfo('pdetails', 'hdmf', $row->hdmf, true,'0000-0000-0000');

				echo $this->staffM->displayInfo('pdetails', 'emergency_person', $row->emergency_person, true);
				echo $this->staffM->displayInfo('pdetails', 'emergency_number', $row->emergency_number, true,'0000-000-0000');
				echo $this->staffM->displayInfo('pdetails', 'emergency_address', $row->emergency_address, true );
			}
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
			echo '<td colspan=2>Job Details &nbsp;&nbsp;&nbsp;';
			
			if($current=='myinfo' || $this->access->accessFullHR==true)
				echo '[<a href="javascript:void(0);" onClick="toggleDisplay(\'jobtbl\', this)" class="droptext">Show</a>]';	
				
				if($current=='myinfo' || $this->access->accessFullHR==true || $isUnderMe==true){
					echo '<a href="javascript:void(0)" class="edit '.(($current=='staffinfo' && $this->access->accessFullHR==false && $isUnderMe==true)?'':'hidden').'" onClick="reqUpdate(\'jdetails\')" id="jdetailsupb">';
					echo (($this->access->accessFullHR==false)?'Request an ':'');
					echo 'Update</a>';
				}
			echo '</td></tr>';
		echo '</table>';
		
		if($current=='myinfo' || $this->access->accessFullHR==true)
			echo '<table class="tableInfo hidden" id="jobtblData">';	
		else
			echo '<table class="tableInfo" id="jobtblData">';	
			
			if($this->access->accessFullHR==true){
				echo $this->staffM->displayInfo('jdetails', 'idNum', $row->idNum, true);
				echo $this->staffM->displayInfo('jdetails', 'active', $row->active, true);
				echo $this->staffM->displayInfo('jdetails', 'office', ucfirst($row->office), true);
				echo $this->staffM->displayInfo('jdetails', 'staffHolidaySched', ucfirst($row->staffHolidaySched), true);
			}else{
				echo $this->staffM->displayInfo('jdetails', 'idNum', $row->idNum, false);
				echo $this->staffM->displayInfo('jdetails', 'active', (($row->active==1)?'Yes':'No'), false);
				echo $this->staffM->displayInfo('jdetails', 'office', ucfirst($row->office), false);
				echo $this->staffM->displayInfo('jdetails', 'staffHolidaySched', ucfirst($row->staffHolidaySched), false);
			}
			
			
			echo $this->staffM->displayInfo('jdetails', 'shift', $row->shift, true, 'Ex. 07:00am - 04:00pm Mon-Fri');
			echo $this->staffM->displayInfo('jdetails', 'shiftSched', $row->shiftSched, true);
						
			echo $this->staffM->displayInfo('jdetails', 'supervisor', $row->supervisor, true);			
			echo $this->staffM->displayInfo('jdetails', 'department', $row->department, false);
			echo $this->staffM->displayInfo('jdetails', 'title', $row->title, true);			
			echo $this->staffM->displayInfo('jdetails', 'levelID_fk', $row->levelID_fk, true);
			
			if(count($coachedNames)>0){
				$cul = '<ul>';
				foreach($coachedNames AS $cc){
					$cul .= '<li><a href="'.$this->config->base_url().'generatecoaching/'.$cc->empID.'/" class="iframe">'.$cc->name.'</a></li>';
				}
				$cul .= '</ul>';
				if($this->user->username != $row->username && ($this->access->accessFullHR==true || $this->commonM->checkStaffUnderMe($row->username))){
					$cul .= '<a href="'.$this->config->base_url().'setcoach/'.$row->empID.'/" class="iframe">Add Names</a>';
				}
				echo $this->staffM->displayInfo('jdetails', 'coachedOf', $cul, false);
			}
			
			echo $this->staffM->displayInfo('jdetails', 'empStatus', $row->empStatus, true);
			if($row->agencyID_fk!=0){
				$agencyName = $this->dbmodel->getSingleField('agencies', 'agencyName', 'agencyID="'.$row->agencyID_fk.'"');
				echo $this->staffM->displayInfo('jdetails', 'agencyID_fk', $agencyName, false);
			}
			
			if($this->user->empID==$row->empID || $this->access->accessFullHR==true || $isUnderMe==true){
				echo $this->staffM->displayInfo('jdetails', 'startDate', (($row->startDate!='0000-00-00')? date('F d, Y',strtotime($row->startDate)) : ''), true);
			}			
			if($current=='myinfo' || $this->access->accessFullHR==true || $isUnderMe==true){
				echo $this->staffM->displayInfo('jdetails', 'regDate', (($row->regDate!='0000-00-00')? date('F d, Y',strtotime($row->regDate)) : ''), true);			
				echo $this->staffM->displayInfo('jdetails', 'endDate', (($row->endDate!='0000-00-00')? date('F d, Y',strtotime($row->endDate)) : ''), true, 'First day employee is no longer connected with Tate');
				echo $this->staffM->displayInfo('jdetails', 'accessEndDate', (($row->accessEndDate!='0000-00-00')? date('F d, Y',strtotime($row->accessEndDate)) : ''), true, 'First day of no access');
				echo $this->staffM->displayInfo('jdetails', 'terminationType', $row->terminationType, true);
			}
			
									
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
	if($this->access->accessFullHRFinance==true || $current=='myinfo' || $isUnderMe==true){
	echo '<table class="tableInfo" id="compensationtbl">';
		echo '<tr class="trlabel" id="cdetails">';
		echo '<td colspan=2>Compensation Details &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay(\'compensationtbl\', this)" class="droptext">Show</a>]';		
			if($current=='myinfo' || $this->access->accessFullHR==true || $isUnderMe==true){
				echo '<a href="javascript:void(0)" class="edit hidden" onClick="reqUpdate(\'cdetails\')" id="cdetailsupb">';
				echo (($this->access->accessFullHR==false)?'Request an ':'');
				echo 'Update</a>';
			}
		echo '</td></tr>';
	echo '</table>';	
?>			
	<table class="tableInfo hidden" id="compensationtblData">
	<?php	
		echo $this->staffM->displayInfo('cdetails', 'sal', $row->sal, true, 'Ex. 10,000.00');
		echo $this->staffM->displayInfo('cdetails', 'allowance', $this->textM->convertNumFormat($row->allowance), true, 'Ex. 2,500.00');	
		
		if($this->access->accessFullHRFinance==true || $current=='myinfo'){
			echo $this->staffM->displayInfo('cdetails', 'taxstatus', (($row->taxstatus!=0)?$row->taxstatus:''), true);	
			echo $this->staffM->displayInfo('cdetails', 'bankAccnt', $row->bankAccnt, true);	
			echo $this->staffM->displayInfo('cdetails', 'hmoNumber', $row->hmoNumber, true);
		}
		echo '<tr class="cdetailslast hidden">
				<td colspan=2 align="right">
					<button id="cbtnsubmit" class="padding5px">Submit</button>&nbsp;&nbsp;<button onClick="reqUpdateCancel(\'cdetails\')" class="padding5px">Cancel</button>
				</td>
			</tr>';	
	?>
	</table>
<?php } 
if( $current=='myinfo' || $this->access->accessFullHR==true || $this->access->accessMedPerson == true ){
?>	
<!----------------------- PERSONAL FILES ----------------------->	
	<table class="tableInfo" id="personalfiletbl">
		<tr class="trlabel">
			<td>
				Personal Files &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('personalfiletbl', this)" class="droptext">Show</a>]
					<? if(!in_array("exec", $this->access->myaccess)){ ?><a href="javascript:void(0)" class="edit" onClick="addFile('personalfile')" id="addfile">+ Add File</a><? } ?>
				<form class="pfformi" action="" method="POST" enctype="multipart/form-data">
					<input type="file" name="pfilei[]" multiple="multiple" class="pfilei hidden" onChange="formSubmitfile('personalfile')"/>
					<input type="hidden" name="submitType" value="uploadPF"/>
				</form>
			</td>
		</tr>		
	</table>
	<?php
	$upArr = array();
	foreach($pfUploaded AS $pf){
		$xxArr = array();
		$xxArr['upID'] = $pf->upID;
		$xxArr['type'] = 'pf';
		$xxArr['docName'] = $pf->docName;
		$xxArr['fileName'] = $pf->fileName;
		$xxArr['dateUploaded'] = $pf->dateUploaded;
		$upArr[] = $xxArr;
	}	
	
	foreach($upArr as $key => $upr) {
		$volume[$key]  = $upr['dateUploaded'];
	}
	
	if(!empty($upArr) && !empty($volume)){
		array_multisort($volume, SORT_DESC, $upArr);
	}
		
	echo '<table class="tableInfo hidden" id="personalfiletblData">';
		if(count($upArr)==0){
			echo '<tr><td colspan=3>No files uploaded.</td></tr>';
		}else{
			$upArrCnt = count($upArr);
			echo '<tr class="trhead">
					<td>Date Uploaded</td>								
					<td>Document Name</td>
					<td width="220px"><br/></td>
				</tr>';
			for($uu=0; $uu<$upArrCnt; $uu++){
				echo '<tr>';
					echo '<td>'.date('d M Y', strtotime($upArr[$uu]['dateUploaded'])).'</td>';
					echo '<td>
							<span class="upClass_'.$upArr[$uu]['upID'].'">'.(($upArr[$uu]['docName']!='')?$upArr[$uu]['docName']:$upArr[$uu]['fileName']).'</span>
							<input id="uploadDoc_'.$upArr[$uu]['upID'].'" type="text" value="'.(($upArr[$uu]['docName']!='')?$upArr[$uu]['docName']:$upArr[$uu]['fileName']).'" class="forminput hidden uploadDoc'.$upArr[$uu]['upID'].'"/>
						</td>';
					echo '<td align="right">';
					
							
					
					if($this->access->accessFullHR==true && $upArr[$uu]['type']=='pf'){
						echo '<button onClick="editUploadDoc('.$upArr[$uu]['upID'].', 0)" class="upClass_'.$upArr[$uu]['upID'].'">Update</button>
						<img id="uploadDocimg'.$upArr[$uu]['upID'].'" src="'.$this->config->base_url().'css/images/small_loading.gif'.'" width="25" class="hidden"/>
						<button class="uploadDoc'.$upArr[$uu]['upID'].' hidden" onClick="editUploadDoc('.$upArr[$uu]['upID'].', 1)">Update</button>';
						echo '<button onClick="delFile('.$upArr[$uu]['upID'].', \''.$upArr[$uu]['fileName'].'\')">Delete</button>';		
					}
					
					
					if($upArr[$uu]['type']=='NTE' || $upArr[$uu]['type']=='CAR')
						$fileUrl = $this->config->base_url().'uploads/NTE/'.$upArr[$uu]['fileName'];
					else if($upArr[$uu]['type']=='coaching')
						$fileUrl = $this->config->base_url().'uploads/coaching/'.$upArr[$uu]['fileName'];
					else
						$fileUrl = $this->config->base_url().UPLOAD_DIR.$row->username.'/'.$upArr[$uu]['fileName'];
					
					$ext = strtolower(pathinfo($upArr[$uu]['fileName'], PATHINFO_EXTENSION));
					if(in_array($ext, array('jpg', 'png', 'gif', 'pdf', 'bmp'))){
						echo '<a class="iframe" href="'.$fileUrl.'"><button>View</button></a>';
					}else{
						echo '<a href="'.$fileUrl.'"><button>Download</button></a>';
					}					
					
					echo '</td>';
				echo '</tr>';
			}
		}
	echo '<table>';
}

if($current=='myinfo' || $this->access->accessFullHR==true){
?>	
<!----------------------- DISCIPLINARY MEASURES ----------------------->	
	<table class="tableInfo" id="disciplinarytbl">
		<tr class="trlabel">
			<td>
				Disciplinary Measures &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('disciplinarytbl', this)" class="droptext">Show</a>]
					<? if(!in_array("exec", $this->access->myaccess)){ ?><a href="javascript:void(0)" class="edit" onClick="addFile('disciplinary')">+ Add File</a><? } ?>
				<form class="pfformi" action="" method="POST" enctype="multipart/form-data">
					<input type="file" name="pfilei[]" multiple="multiple" class="pfilei hidden" onChange="formSubmitfile('disciplinary')"/>
					<input type="hidden" name="typeVal" value="disciplinary"/>
					<input type="hidden" name="submitType" value="uploadPF"/>
				</form>
			</td>
		</tr>		
	</table>
	<?php
	$upArr = array();
	foreach($disciplinaryUploaded AS $pf){
		$xxArr = array();
		$xxArr['upID'] = $pf->upID;
		$xxArr['type'] = 'pf';
		$xxArr['docName'] = $pf->docName;
		$xxArr['fileName'] = $pf->fileName;
		$xxArr['dateUploaded'] = $pf->dateUploaded;
		$upArr[] = $xxArr;
	}
	foreach($nteUploadedFiles AS $nU){
		if($nU->nteuploaded!=''){
			$nEx = explode('|', $nU->nteuploaded);
			if(count($nEx)==3){
				$xxArr = array();
				$xxArr['upID'] = 0;
				$xxArr['type'] = 'NTE';
				$xxArr['docName'] = 'NTE Uploaded File';
				$xxArr['fileName'] = $nEx[2];
				$xxArr['dateUploaded'] = $nEx[1];
				$upArr[] = $xxArr;
			}
		}		
		if($nU->caruploaded!=''){
			$cEx = explode('|', $nU->caruploaded);
			if(count($cEx)==3){
				$xxArr = array();
				$xxArr['upID'] = 0;
				$xxArr['type'] = 'CAR';
				$xxArr['docName'] = 'CAR Uploaded File';
				$xxArr['fileName'] = $cEx[2];
				$xxArr['dateUploaded'] = $cEx[1];
				$upArr[] = $xxArr;
			}
		}
	}	
		
	$volume = array();	
	foreach($upArr as $key => $upr) {
		$volume[$key]  = $upr['dateUploaded'];
	}
	
	if(!empty($upArr) && !empty($volume)){
		array_multisort($volume, SORT_DESC, $upArr);
	}
		
	echo '<table class="tableInfo hidden" id="disciplinarytblData">';
		if(count($upArr)==0){
			echo '<tr><td colspan=3>No files uploaded.</td></tr>';
		}else{
			$upArrCnt = count($upArr);
			echo '<tr class="trhead">
					<td>Date Uploaded</td>								
					<td>Document Name</td>
					<td width="220px"><br/></td>
				</tr>';
			for($uu=0; $uu<$upArrCnt; $uu++){
				echo '<tr>';
					echo '<td>'.date('d M Y', strtotime($upArr[$uu]['dateUploaded'])).'</td>';
					echo '<td>
							<span class="upClass_'.$upArr[$uu]['upID'].'">'.(($upArr[$uu]['docName']!='')?$upArr[$uu]['docName']:$upArr[$uu]['fileName']).'</span>
							<input id="uploadDoc_'.$upArr[$uu]['upID'].'" type="text" value="'.(($upArr[$uu]['docName']!='')?$upArr[$uu]['docName']:$upArr[$uu]['fileName']).'" class="forminput hidden uploadDoc'.$upArr[$uu]['upID'].'"/>
						</td>';
					echo '<td align="right">';
					
							
					
					if($this->access->accessFullHR==true && $upArr[$uu]['type']=='pf'){
						echo '<button onClick="editUploadDoc('.$upArr[$uu]['upID'].', 0)" class="upClass_'.$upArr[$uu]['upID'].'">Update</button>
						<img id="uploadDocimg'.$upArr[$uu]['upID'].'" src="'.$this->config->base_url().'css/images/small_loading.gif'.'" width="25" class="hidden"/>
						<button class="uploadDoc'.$upArr[$uu]['upID'].' hidden" onClick="editUploadDoc('.$upArr[$uu]['upID'].', 1)">Update</button>';
						echo '<button onClick="delFile('.$upArr[$uu]['upID'].', \''.$upArr[$uu]['fileName'].'\')">Delete</button>';		
					}
					
					
					if($upArr[$uu]['type']=='NTE' || $upArr[$uu]['type']=='CAR')
						$fileUrl = $this->config->base_url().'uploads/NTE/'.$upArr[$uu]['fileName'];
					else if($upArr[$uu]['type']=='coaching')
						$fileUrl = $this->config->base_url().'uploads/coaching/'.$upArr[$uu]['fileName'];
					else
						$fileUrl = $this->config->base_url().UPLOAD_DIR.$row->username.'/'.$upArr[$uu]['fileName'];
					
					$ext = strtolower(pathinfo($upArr[$uu]['fileName'], PATHINFO_EXTENSION));
					if(in_array($ext, array('jpg', 'png', 'gif', 'pdf', 'bmp'))){
						echo '<a class="iframe" href="'.$fileUrl.'"><button>View</button></a>';
					}else{
						echo '<a href="'.$fileUrl.'"><button>Download</button></a>';
					}					
					
					echo '</td>';
				echo '</tr>';
			}
		}
	echo '<table>';
}


if($current=='myinfo' || $this->access->accessFullHR==true){
?>	
<!----------------------- PERFORMANCE RELATED DOCUMENTS ----------------------->	
	<table class="tableInfo" id="performancetbl">
		<tr class="trlabel">
			<td>
				Performance-Related Documents &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('performancetbl', this)" class="droptext">Show</a>]
					<? if(!in_array("exec", $this->access->myaccess)){ ?><a href="javascript:void(0)" class="edit" onClick="addFile('performance')">+ Add File</a><? } ?>
				<form class="pfformi" action="" method="POST" enctype="multipart/form-data">
					<input type="file" name="pfilei[]" multiple="multiple" class="pfilei hidden" onChange="formSubmitfile('performance')"/>
					<input type="hidden" name="typeVal" value="performance"/>
					<input type="hidden" name="submitType" value="uploadPF"/>
				</form>
			</td>
		</tr>		
	</table>
	<?php
	$upArr = array();
	foreach($performanceUploaded AS $pf){
		$xxArr = array();
		$xxArr['upID'] = $pf->upID;
		$xxArr['type'] = 'pf';
		$xxArr['docName'] = $pf->docName;
		$xxArr['fileName'] = $pf->fileName;
		$xxArr['dateUploaded'] = $pf->dateUploaded;
		$upArr[] = $xxArr;
	}
	
	foreach($coachingUploadedFiles AS $cUp){
		$cfLoc = UPLOADS.'coaching/coachingform_'.$cUp->coachID.'.pdf';
		$evalLoc = UPLOADS.'coaching/coachingevaluation_'.$cUp->coachID.'.pdf';
		$exCp = explode('-^_^-', $cUp->HRstatusData);
		
		$fullData = explode('-^_^-', $cUp->HRstatusData);	
		$exData = array();
		$cntRara = count($fullData);
		for($x=0; $x<$cntRara; $x++){
			$haha = explode('|', $fullData[$x]);
			$cnthehe = count($haha);
			for($h=0; $h<$cnthehe; $h++)
				$exData[$haha[0]][] = $haha[$h];
		}
		
		if(file_exists($cfLoc)){
			$xxArr = array();
			$xxArr['upID'] = 0;
			$xxArr['type'] = 'coaching';
			$xxArr['docName'] = 'Uploaded Coaching Form';
			$xxArr['fileName'] = 'coachingform_'.$cUp->coachID.'.pdf';
			
			if(isset($exData[2][2]))
				$xxArr['dateUploaded'] = $exData[2][2];
			$upArr[] = $xxArr;
		}
		
		if(file_exists($evalLoc)){
			$xxArr = array();
			$xxArr['upID'] = 0;
			$xxArr['type'] = 'coaching';
			$xxArr['docName'] = 'Uploaded Evaluation Form';
			$xxArr['fileName'] = 'coachingevaluation_'.$cUp->coachID.'.pdf';
			
			if(isset($exData[4][2]))
				$xxArr['dateUploaded'] = $exData[4][2];
			$upArr[] = $xxArr;
		}
	}	
		
	$volume = array();	
	foreach($upArr as $key => $upr) {
		$volume[$key]  = $upr['dateUploaded'];
	}
	
	if(!empty($upArr) && !empty($volume)){
		array_multisort($volume, SORT_DESC, $upArr);
	}
		
	echo '<table class="tableInfo hidden" id="performancetblData">';
		if(count($upArr)==0){
			echo '<tr><td colspan=3>No files uploaded.</td></tr>';
		}else{
			$upArrCnt = count($upArr);
			echo '<tr class="trhead">
					<td>Date Uploaded</td>								
					<td>Document Name</td>
					<td width="220px"><br/></td>
				</tr>';
			for($uu=0; $uu<$upArrCnt; $uu++){
				echo '<tr>';
					echo '<td>'.date('d M Y', strtotime($upArr[$uu]['dateUploaded'])).'</td>';
					echo '<td>
							<span class="upClass_'.$upArr[$uu]['upID'].'">'.(($upArr[$uu]['docName']!='')?$upArr[$uu]['docName']:$upArr[$uu]['fileName']).'</span>
							<input id="uploadDoc_'.$upArr[$uu]['upID'].'" type="text" value="'.(($upArr[$uu]['docName']!='')?$upArr[$uu]['docName']:$upArr[$uu]['fileName']).'" class="forminput hidden uploadDoc'.$upArr[$uu]['upID'].'"/>
						</td>';
					echo '<td align="right">';
					
							
					
					if($this->access->accessFullHR==true && $upArr[$uu]['type']=='pf'){
						echo '<button onClick="editUploadDoc('.$upArr[$uu]['upID'].', 0)" class="upClass_'.$upArr[$uu]['upID'].'">Update</button>
						<img id="uploadDocimg'.$upArr[$uu]['upID'].'" src="'.$this->config->base_url().'css/images/small_loading.gif'.'" width="25" class="hidden"/>
						<button class="uploadDoc'.$upArr[$uu]['upID'].' hidden" onClick="editUploadDoc('.$upArr[$uu]['upID'].', 1)">Update</button>';
						echo '<button onClick="delFile('.$upArr[$uu]['upID'].', \''.$upArr[$uu]['fileName'].'\')">Delete</button>';		
					}
					
					
					if($upArr[$uu]['type']=='NTE' || $upArr[$uu]['type']=='CAR')
						$fileUrl = $this->config->base_url().'uploads/NTE/'.$upArr[$uu]['fileName'];
					else if($upArr[$uu]['type']=='coaching')
						$fileUrl = $this->config->base_url().'uploads/coaching/'.$upArr[$uu]['fileName'];
					else
						$fileUrl = $this->config->base_url().UPLOAD_DIR.$row->username.'/'.$upArr[$uu]['fileName'];
					
					$ext = strtolower(pathinfo($upArr[$uu]['fileName'], PATHINFO_EXTENSION));
					if(in_array($ext, array('jpg', 'png', 'gif', 'pdf', 'bmp'))){
						echo '<a class="iframe" href="'.$fileUrl.'"><button>View</button></a>';
					}else{
						echo '<a href="'.$fileUrl.'"><button>Download</button></a>';
					}					
					
					echo '</td>';
				echo '</tr>';
			}
		}
	echo '<table>';
}

if($this->access->accessFullHR==true || $current=='myinfo' || $isUnderMe==true){
?>	
<!----------------------- TIME OFF DETAILS ----------------------->		
	<table class="tableInfo" id="timeOff">
		<tr class="trlabel">
			<td>Time Off Details &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('timeOff', this)" class="droptext">Show</a>]
			<?php if($this->user->empID==$row->empID){ echo '<a class="edit iframe" href="'.$this->config->base_url().'fileleave/">File for a Leave/Offset</a>'; } ?>
			</td>
		</tr>
		<tr class="trhead"><td>Available Leave Credits : <?= $row->leaveCredits ?><?php if($this->user->empID==$row->empID && $this->access->accessFullHR==false){ echo '&nbsp;&nbsp;&nbsp;<a class="edit" href="javascript:void(0)" id="rupdateTO">Request HR to Recheck Leave Credits</a>'; } if($this->access->accessFullHR==true){ echo '&nbsp;&nbsp;&nbsp;<a class="edit" href="javascript:void(0)" id="updateLC">Update</a>';} ?></td></tr>
		
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
						echo '<a href="'.$this->config->base_url().'sendemail/addinfoleavesubmitted/'.$t->leaveID.'/'.'" class="iframe">'.$this->textM->getLeaveStatusText($t->status, $t->iscancelled, $t->isrefiled).'<br/>Click here to confirm submission of required information</a>';
					}else{
						if($t->matStatus>0) echo $this->textM->getLeaveMaternityStatusText($t->matStatus);
						else echo $this->textM->getLeaveStatusText($t->status, $t->iscancelled, $t->isrefiled);
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
	<table class="tableInfo" id="disRec">
		<tr class="trlabel">
			<td>Disciplinary Records &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('disRec', this)" class="droptext">Show</a>]</td>
		</tr>		
	</table>
	
	<table class="tableInfo hidden" id="disRecData">
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
					<td>'.ucfirst($dis->type).'</td>
					<td>'.$this->textM->ordinal($dis->offenselevel).' Offense</td>
					<td>'.date('M d, Y', strtotime($dis->dateissued)).'</td>
					<td>'.$dis->issuerName.'</td>';
				echo '<td>';
					if($dis->status==1) echo 'Not yet generated.';
					else if(!empty($dis->caruploaded)){
						$xc = explode('|', $dis->caruploaded);
						if(isset($xc[2]) && file_exists(UPLOADS.'NTE/'.$xc[2])){
							echo '<a class="iframe" href="'.$this->config->base_url().UPLOADS.'NTE/'.$xc[2].'"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a>';
						}else echo '<a class="iframe" href="'.$this->config->base_url().'ntepdf/'.$dis->nteID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a>';
					}else echo '<a class="iframe" href="'.$this->config->base_url().'ntepdf/'.$dis->nteID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a>';
				echo '</td>';
			
				echo '<td><a class="iframe" href="'.$this->config->base_url().'detailsNTE/'.$dis->nteID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"/></a></td>';
			echo '</tr>';
		endforeach;		
	?>
		
		<tr><td colspan=6><br/></td></tr>
	</table>
<?php } 
}
?>

<!----------------------- PERFORMANCE TRACK RECORDS ----------------------->
<?php if(count($perfTrackRecords)>0 && ($this->access->accessFullHR==true || $current=='myinfo' || $isUnderMe==true || $row->coach==$this->user->empID)){ ?>
	<table class="tableInfo" id="perfTrack">
		<tr class="trlabel">
			<td>Performance Track Records &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('perfTrack', this)" class="droptext">Show</a>]</td>
		</tr>		
	</table>
	<table class="tableInfo hidden" id="perfTrackData">
		<tr class="trhead">
			<td>Generated On</td>
			<td>Evaluation Date</td>
			<td>Coach</td>
			<td>Performance Evaluation Result</td>
			<td>Coaching Form</td>
			<td>Eval Form</td>
		</tr>
	<?php
		foreach($perfTrackRecords AS $per):
			echo '<tr>';
				echo '<td>'.date('M d, Y H:i a', strtotime($per->dateGenerated)).'</td>';
				echo '<td>'.date('M d, Y', strtotime($per->coachedEval)).'</td>';
				echo '<td>'.$per->coachedByName.'</td>';
			$stat = $this->staffM->coachingStatus($per->coachID, $per);
				if(strpos($stat,'Evaluation Due.') !== false)
					echo '<td width="200px" bgcolor="#ff3232">'.$stat.'</td>';	
				else
					echo '<td width="200px">'.$stat.'</td>';	
				
				$cformLoc = UPLOADS.'coaching/coachingform_'.$per->coachID.'.pdf';
				if($per->HRoptionStatus>=2 && file_exists($cformLoc)){
					echo '<td align="center"><a href="'.$this->config->base_url().$cformLoc.'" class="iframe"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a></td>';
				}else{
					echo '<td align="center"><a href="'.$this->config->base_url().'coachingform/expectation/'.$per->coachID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a></td>';
				}
								
				if($per->status==0) 
					echo '<td><br/></td>';
				else{
					$eformLoc = UPLOADS.'coaching/coachingevaluation_'.$per->coachID.'.pdf';
					if($per->HRoptionStatus>=2 && file_exists($eformLoc)){
						echo '<td align="center"><a href="'.$this->config->base_url().$eformLoc.'" class="iframe"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a></td>';
					}else{
						echo '<td align="center"><a href="'.$this->config->base_url().'coachingform/evaluation/'.$per->coachID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a></td>';
					}
				}
					
			echo '</tr>';
		endforeach;
		
		/* foreach($perfTrackRecords AS $per):
			echo '<tr>';
			echo '<td>'.date('M d, Y H:i a', strtotime($per->dateGenerated)).'</td>';
			echo '<td>'.date('M d, Y', strtotime($per->coachedEval)).'</td>';
			echo '<td>'.$per->coachedByName.'</td>';
			echo '<td '.(($dToday>=$per->coachedEval && $per->status==0)?'style="background-color:red;"':'').'>'.$this->staffM->coachingStatus($per).'</td>';			
			echo '<td align="center"><a href="'.$this->config->base_url().'coachingform/acknowledgment/'.$per->coachID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/view-icon.png"></a></td>';
			echo '</tr>';
		endforeach; */
	?>
	</table>
<?php } 
	if($current=='myinfo' || $this->access->accessFullHR==true){
		$cntpayslips = count($dataPayslips);
?>	
<!----------------------- DISCIPLINARY MEASURES ----------------------->	
	<table class="tableInfo" id="prevpaytbl">
		<tr class="trlabel">
			<td>
				Previous Payslips <?= (($cntpayslips>0)?'('.$cntpayslips.')':'') ?> &nbsp;&nbsp;&nbsp;[<a href="javascript:void(0);" onClick="toggleDisplay('prevpaytbl', this)" class="droptext">Show</a>]
					<? if(!in_array("exec", $this->access->myaccess)){ ?><a href="javascript:void(0)" class="edit" onClick="addFile('prevpay')">+ Add File</a><? } ?>
				<form class="pfformi" action="" method="POST" enctype="multipart/form-data">
					<input type="file" name="pfilei[]" multiple="multiple" class="pfilei hidden" onChange="formSubmitfile('prevpay')"/>
					<input type="hidden" name="typeVal" value="prevpay"/>
					<input type="hidden" name="submitType" value="uploadPrevPay"/>
				</form>
			</td>
		</tr>		
	</table>
<?php 
	echo '<table class="tableInfo hidden" id="prevpaytblData">';
		if($cntpayslips==0){
			echo '<tr><td colspan=3>No files uploaded.</td></tr>';
		}else{
			echo '<tr class="trhead">
					<td>Filename</td>								
					<td align="right">File</td>
				</tr>';
			foreach($dataPayslips AS $slip){
				echo '<tr>';
					echo '<td>'.$slip.'</td>';
					echo '<td align="right"><a href="'.$this->config->base_url().$payslipDIR.$slip.'" target="_blank"><img src="'.$this->config->base_url().'css/images/pdf-icon.png" width="25px"/></a></td>';
				echo '</tr>';
			}
		}
	echo '</table>';

	} ?>
	</div>
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
		
	<?php
		//if($this->config->base_url()!='http://careerph.tatepublishing.net/~divi/staff/'){
			echo '<div id="loadImg" style="text-align:center; margin-top:20px;"><img src="'.$this->config->base_url().'css/images/small_loading.gif"/></div>';
			echo '<iframe id="iframeNotes" src="'.$this->config->base_url().'notes/'.$row->empID.'/" frameBorder="0" style="width:100%; height:500px;" onLoad="document.getElementById(\'loadImg\').style.display=\'none\';"></iframe>';
		//}
	?>		
		
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
