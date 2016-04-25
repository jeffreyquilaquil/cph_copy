<h2>Manage Staffs</h2><hr/>
<div style="text-align:right;" id="dfields"><a id="dispOther" href="javascript:void(0)">Display Other Fields</a><br/><br/></div>
<div class="hidden" id="ofields">
<form action="" method="POST">
	<table width="100%">
	<?php 	
		$fields_array = array('username' => 'Username', 'email' => 'Company Email', 'pemail' => 'Personal Email', 'gender' => 'Gender', 'idNum' => 'Payroll ID', 'title' => 'Title', 'dept' => 'Department', 'grp' => 'Group', 'levelName' => 'Org Level', 'supervisor' => 'Immediate Supervisor', 'startDate' => 'Start Date', 'regDate' => 'Regularization Date', 'endDate' => 'Effective Separation Date', 'shift' => 'Shift', 'empStatus' => 'Employee Status', 'bdate' => 'Birthdate',  'phone' => 'Phone', 'address' => 'Address', 'maritalStatus' => 'Marital Status', 'leaveCredits' => 'Leave credits', 'active' => 'Active', 'accessEndDate' => 'Access End Date', 'terminationType' => 'Termination Type', 'sal' => 'Salary', 'gov_record' => 'Gov. Numbers (TIN, SSS, HDMF)', 'bankAccnt' => 'Bank Account Number', 'hmoNumber' => 'HMO',  );

		$hr_fields_array = array('hmoNumber', 'sal', 'bankAccnt', 'gov_record', 'terminationType', 'accessEndDate');

		$number_of_col = 6;
		$counter = 1;
		foreach( $fields_array as $key => $val ){
			$display = false;
			if( $counter == 1 ) echo '<tr>';
				if( in_array($key, $hr_fields_array ) AND $this->access->accessFullHR == true ){
						echo '<td>';
								echo  '<input type="checkbox" name="flds[]" value="'.$key.'" id="'.$key.'"';
								if( in_array($key, $fvalue) ) echo ' checked ';
								echo  '/> <label for="'.$key.'">'.$val.'</label>';
							echo  '</td>';	
							
					
				} else if( ($this->access->accessMedPerson == true OR $this->user->level > 0) AND !in_array($key, $hr_fields_array) ){
					echo '<td>';
							echo '<input type="checkbox" name="flds[]" value="'.$key.'" id="'.$key.'"';
							if( in_array($key, $fvalue) ) echo ' checked ';
							echo '/> <label for="'.$key.'">'.$val.'</label>';
					echo '</td>';	
				} else if( !in_array($key, $hr_fields_array) ){
					echo '<td>';
							echo '<input type="checkbox" name="flds[]" value="'.$key.'" id="'.$key.'"';
							if( in_array($key, $fvalue) ) echo ' checked ';
							echo '/> <label for="'.$key.'">'.$val.'</label>';
					echo '</td>';	
				}
				
				

			$counter++;
			if( $counter == $number_of_col ){
				echo '</tr>';
				$counter = 1;
			}
		}

		?>
	</table>

</table>
	<input type="submit" name="submitType" value="Submit Selection" class="btnclass"/>&nbsp;&nbsp;&nbsp;
	<a id="sall" href="javascript:void(0)">Select All</a>&nbsp;&nbsp;&nbsp;
	<a id="dall" href="javascript:void(0)">Deselect All</a>&nbsp;&nbsp;&nbsp;
	<a id="shide" href="javascript:void(0)">Hide</a>&nbsp;&nbsp;&nbsp;
	<input type="submit" name="submitType" value="Generate Employee Report" class="btnclass"/>&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="includeinactive" <?= ((isset($_POST['includeinactive'])) ? 'checked':'') ?>/> Include Separated Employees
</form>
<hr/><br/>
</div>

<table class="dTable display stripe hover">
	<thead>
	<tr bgcolor="#fff">
	<?php
		echo '<th>Name</th>';
		$cntFeel = count($fvalue);
		for($i=0;$i<$cntFeel;$i++){
			echo '<th>'.$this->textM->constantText('txt_'.$fvalue[$i]).'</th>';
		}
		echo '<th width="20px"><br/></th>';
	?>
	</tr>
	</thead>
<?php
	foreach($query AS $row){		
		echo '<tr>';
		echo '<td><a href="'.$this->config->base_url().'staffinfo/'.trim($row->username).'/" target="_blank">'.$row->name.'</a></td>';
		$cntGood = count($fvalue);
		for($i=0;$i<$cntGood;$i++){
			echo '<td>';
				if($fvalue[$i]=='email' || $fvalue[$i]=='pemail') echo '<a href="mailto:'.$row->$fvalue[$i].'">'.$row->$fvalue[$i].'</a>';
				else if($fvalue[$i]=='phone'){ echo $row->phone1; if($row->phone2!=''){ echo ', '.$row->phone2; } }
				else if($fvalue[$i]=='address'){ echo $row->address; if($row->city!=''){ echo ', '.$row->city; } if($row->country!=''){ echo ', '.$row->country; } if($row->zip!=''){ echo ', '.$row->zip; } }
				else if($fvalue[$i]=='gender') echo (($row->gender=='F')?'Female':'Male');
				else if($fvalue[$i]=='active') echo (($row->active=='1')?'Yes':'No');
				else if($fvalue[$i]=='username') echo strtolower($row->$fvalue[$i]);
				else if($fvalue[$i]=='terminationType') echo $this->staffM->infoTextVal('terminationType', $row->$fvalue[$i]);
				else if($fvalue[$i]=='sal') echo $this->textM->convertDecryptedText('sal',$row->$fvalue[$i]);
				else echo ucfirst($row->$fvalue[$i]);	
			echo '</td>';
		}		
		echo '<td>
				<ul class="dropmenu">
					<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" class="cpointer"/>
						<ul class="dropleft">
							<li><a href="'.$this->config->base_url().'staffinfo/'.trim($row->username).'/" target="_blank">Visit Page</a></li>
							<li><a href="'.$this->config->base_url().'sendEmail/'.$row->empID.'/" class="iframe2">Send Email</a></li>';
						
							echo '<li><a class="iframe2" href="'.$this->config->base_url().'issueNTE/'.$row->empID.'/">Issue NTE</a></li>
							<li><a class="iframe2" href="'.$this->config->base_url().'generatecis/'.$row->empID.'/">Generate CIS</a></li>';
							
						echo '<li><a class="iframe2" href="'.$this->config->base_url().'generatecoaching/'.$row->empID.'/">Generate Coaching</a></li>';
						echo '<li><a href="'.$this->config->base_url().'generatewrittenwarning/'.$row->empID.'/" class="iframe">Generate Written Warning</a></li>';
						
						if($this->access->accessFull==true){
							echo '<li><a class="iframe2" href="'.$this->config->base_url().'adminsettings/'.$row->empID.'/">Admin Settings</a></li>';
							echo '<li><a class="iframe2" href="'.$this->config->base_url().'schedules/setschedule/'.$row->empID.'/">Set Schedule</a></li>';
						}
						if($this->access->accessFullFinance==true){
							echo '<li><a class="iframe2" href="'.$this->config->base_url().'timecard/computelastpay/?empID='.$row->empID.'">Compute Last Pay</a></li>';
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
