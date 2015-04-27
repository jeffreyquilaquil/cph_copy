<h2>Manage Staffs</h2><hr/>
<div style="text-align:right;" id="dfields"><a id="dispOther" href="javascript:void(0)">Display Other Fields</a><br/><br/></div>
<div class="hidden" id="ofields">
<form action="" method="POST">
<table width="100%">
	<tr>
		<td><input type="checkbox" name="flds[]" value="username" <?= ((in_array('username',$fvalue)) ? 'checked':'') ?> /> Username</td>
		<td><input type="checkbox" name="flds[]" value="email" <?= ((in_array('email',$fvalue)) ? 'checked':'') ?>/> Company Email</td>
		<td><input type="checkbox" name="flds[]" value="pemail" <?= ((in_array('pemail',$fvalue)) ? 'checked':'') ?>/> Personal Email</td>
		<td><input type="checkbox" name="flds[]" value="gender" <?= ((in_array('gender',$fvalue)) ? 'checked':'') ?>/> Gender</td>
		<td><input type="checkbox" name="flds[]" value="idNum" <?= ((in_array('idNum',$fvalue)) ? 'checked':'') ?>/> Payroll ID</td>	
	</tr>
	<tr>
		<td><input type="checkbox" name="flds[]" value="title" <?= ((in_array('title',$fvalue)) ? 'checked':'') ?>/> Title</td>
		<td><input type="checkbox" name="flds[]" value="dept" <?= ((in_array('dept',$fvalue)) ? 'checked':'') ?>/> Department</td>
		<td><input type="checkbox" name="flds[]" value="grp" <?= ((in_array('grp',$fvalue)) ? 'checked':'') ?>/> Group</td>
		<td><input type="checkbox" name="flds[]" value="levelName" <?= ((in_array('levelName',$fvalue)) ? 'checked':'') ?>/> Org Level</td>
		<td><input type="checkbox" name="flds[]" value="supervisor" <?= ((in_array('supervisor',$fvalue)) ? 'checked':'') ?>/> Immediate Supervisor</td>
	</tr>
	<tr>
		<td><input type="checkbox" name="flds[]" value="startDate" <?= ((in_array('startDate',$fvalue)) ? 'checked':'') ?>/> Start Date</td>
		<td><input type="checkbox" name="flds[]" value="regDate" <?= ((in_array('regDate',$fvalue)) ? 'checked':'') ?>/> Regularization Date</td>			
		<td><input type="checkbox" name="flds[]" value="endDate" <?= ((in_array('endDate',$fvalue)) ? 'checked':'') ?>/> Effective Separation Date</td>
		<td><input type="checkbox" name="flds[]" value="shift" <?= ((in_array('shift',$fvalue)) ? 'checked':'') ?>/> Shift</td>
		<td><input type="checkbox" name="flds[]" value="empStatus" <?= ((in_array('empStatus',$fvalue)) ? 'checked':'') ?>/> Employee Status</td>			
	</tr>
	<tr>
		<td><input type="checkbox" name="flds[]" value="bdate" <?= ((in_array('bdate',$fvalue)) ? 'checked':'') ?>/> Birthday</td>
		<td><input type="checkbox" name="flds[]" value="phone" <?= ((in_array('phone',$fvalue)) ? 'checked':'') ?>/> Phone Number</td>
		<td><input type="checkbox" name="flds[]" value="address" <?= ((in_array('address',$fvalue)) ? 'checked':'') ?>/> Address</td>			
		<td><input type="checkbox" name="flds[]" value="maritalStatus" <?= ((in_array('maritalStatus',$fvalue)) ? 'checked':'') ?>/> Marital Status</td>
		<td><input type="checkbox" name="flds[]" value="leaveCredits" <?= ((in_array('leaveCredits',$fvalue)) ? 'checked':'') ?>/> Leave Credits</td>
		<td><br/></td>
	</tr>
	<tr>
		<td><input type="checkbox" name="flds[]" value="active" <?= ((in_array('active',$fvalue)) ? 'checked':'') ?>/> Active</td>
		<td><input type="checkbox" name="flds[]" value="accessEndDate" <?= ((in_array('accessEndDate',$fvalue)) ? 'checked':'') ?>/> Access End Date</td>
		<td><input type="checkbox" name="flds[]" value="terminationType" <?= ((in_array('terminationType',$fvalue)) ? 'checked':'') ?>/> Termination Reason</td>
		<td><br/></td>
		<td><br/></td>
	</tr>
</table>
	<input type="submit" name="submitType" value="Submit Selection"/>&nbsp;&nbsp;&nbsp;
	<a id="sall" href="javascript:void(0)">Select All</a>&nbsp;&nbsp;&nbsp;
	<a id="dall" href="javascript:void(0)">Deselect All</a>&nbsp;&nbsp;&nbsp;
	<a id="shide" href="javascript:void(0)">Hide</a>&nbsp;&nbsp;&nbsp;
	<input type="submit" name="submitType" value="Generate Employee Report"/>&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="includeinactive" <?= ((isset($_POST['includeinactive'])) ? 'checked':'') ?>/> Include Separated Employees
</form>
<hr/><br/>
</div>

<table class="dTable display stripe hover">
	<thead>
	<tr>
	<?php
		echo '<th>Name</th>';
		for($i=0;$i<count($fvalue);$i++){
			echo '<th>'.$this->config->item('txt_'.$fvalue[$i]).'</th>';
		}
		echo '<th><br/></th>';
	?>
	</tr>
	</thead>
<?php
	foreach($query AS $row){		
		echo '<tr>';
		echo '<td><a href="'.$this->config->base_url().'staffinfo/'.trim($row->username).'/" target="_blank">'.$row->name.'</a></td>';
		for($i=0;$i<count($fvalue);$i++){
			echo '<td>';
				if($fvalue[$i]=='email' || $fvalue[$i]=='pemail') echo '<a href="mailto:'.$row->$fvalue[$i].'">'.$row->$fvalue[$i].'</a>';
				else if($fvalue[$i]=='phone'){ echo $row->phone1; if($row->phone2!=''){ echo ', '.$row->phone2; } }
				else if($fvalue[$i]=='address'){ echo $row->address; if($row->city!=''){ echo ', '.$row->city; } if($row->country!=''){ echo ', '.$row->country; } if($row->zip!=''){ echo ', '.$row->zip; } }
				else if($fvalue[$i]=='gender') echo (($row->gender=='F')?'Female':'Male');
				else if($fvalue[$i]=='active') echo (($row->active=='1')?'Yes':'No');
				else if($fvalue[$i]=='username') echo strtolower($row->$fvalue[$i]);
				else if($fvalue[$i]=='terminationType') echo $this->staffM->infoTextVal('terminationType', $row->$fvalue[$i]);
				else echo ucfirst($row->$fvalue[$i]);	
			echo '</td>';
		}		
		echo '<td>
				<ul class="dropmenu">
					<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" class="cpointer"/>
						<ul class="dropleft">
							<li><a href="'.$this->config->base_url().'staffinfo/'.trim($row->username).'/" target="_blank">Visit Page</a></li>
							<li><a href="'.$this->config->base_url().'sendEmail/'.$row->empID.'/" class="iframe2">Send Email</a></li>
							<li><a class="iframe2" href="'.$this->config->base_url().'issueNTE/'.$row->empID.'/">Issue NTE</a></li>
							<li><a class="iframe2" href="'.$this->config->base_url().'generatecis/'.$row->empID.'/">Generate CIS</a></li>
							<li><a class="iframe2" href="'.$this->config->base_url().'generatecoaching/'.$row->empID.'/">Generate Coaching</a></li>';
						if($this->access->accessFull==true){
							echo '<li><a class="iframe2" href="'.$this->config->base_url().'adminsettings/'.$row->empID.'/">Admin Settings</a></li>
							<li><a class="iframe2" href="'.$this->config->base_url().'schedules/setstaffschedule/'.$row->empID.'/">Set Schedule</a></li>';
						}
							
				echo 	'</ul>
					</li>
				</ul>
			</td>
		</tr>'; 
	}
?>
</table>

<script type="text/javascript">
$(document).ready(function(){
	$(".iframe2").colorbox({iframe:true, width:"990px", height:"600px"});

	$('.dTable').dataTable({
		"dom": 'lf<"toolbar">tip'
	});	
	
	$("div.toolbar").html('<br/><br/><br/><form id="turninactive" action="" method="POST"><input type="checkbox" name="includeinactive" <?= ((isset($_POST['includeinactive']) && $_POST['includeinactive']=='on')?'checked':'')?>/> <b>include separated employees</b></form><br/>');
	
	$('input[name=includeinactive]').click(function(){
		if($('#ofields').hasClass('hidden'))
			$('#turninactive').submit();
			
		if($(this).is(':checked'))
			$('input[name=includeinactive]').prop("checked", true);
		else	
			$('input[name=includeinactive]').prop("checked", false);
	});
	
	$('#sall').click(function(){
		$('input[name=flds\\[\\]]').each(function(){    
			this.checked = true;    
		});
	});
	$('#dall').click(function(){
		$('input[name=flds\\[\\]]').each(function(){    
			this.checked = false;    
		});
	});
	$('#dispOther').click(function(){
		$('#turninactive').addClass('hidden');
		$('#dfields').addClass('hidden');
		$('#ofields').removeClass('hidden');
	});
	$('#shide').click(function(){
		$('#ofields').addClass('hidden');
		$('#dfields').removeClass('hidden');
		$('#turninactive').removeClass('hidden');
	});
});
</script>
