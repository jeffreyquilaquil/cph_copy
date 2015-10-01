<?php
	if($this->access->accessFullHR==true){
		$allStaffs = $this->dbmodel->getQueryResults('staffs', 'empID, fname, lname');
		
		echo '<div class="floatright" style="width:300px;">';
			echo $this->textM->formfield('text', '', '', 'forminput', 'Search Employee', 'id="searchTimeEmp" style="border:1px solid #800000;"');
			echo '<div id="divstaffs" style="position:absolute; width:300px; z-index:999;">';
				echo '<table id="timetable" class="hidden" style="background-color:#ccc; width:100%; border:1px solid #800000;">';
					foreach($allStaffs AS $a){
						echo '<tr class="timetabletr"><td onClick="gototimelogpage('.$a->empID.')">'.$a->fname.' '.$a->lname.'</td></tr>';
					}
				echo '</table>';
			echo '</div>';
		echo '</div>';
?>	
	<script type="text/javascript">
		$(function(){
			$("#searchTimeEmp").keyup(function(){ 
				var filter = $(this).val();
				$('#timetable').removeClass('hidden');
				
				$("#timetable tr").each(function(){ 
					if ($(this).text().search(new RegExp(filter, "i")) < 0) {
						$(this).fadeOut();				
					 } else {				
						$(this).show();
						//count++;
					}
				});
				
				if(filter=='') $('#timetable').addClass('hidden');
			});
		});
		
		function gototimelogpage(id){
			location.href='<?= $this->config->base_url().'timecard/' ?>'+id+'/timelogs/';
			displaypleasewait();
		}
	</script>
<?php
	}

	if(isset($row)){
		if($row->empID==$this->user->empID) echo '<h2>My Timecard and Payroll</h2>';
		else echo '<h2>'.$row->fname.'\'s Timecard and Payroll</h2>';		
	}else{
		echo '<h2>Timecard and Payroll</h2>';
	}
?>
<hr/>
<ul class="tabs2">
<?php
	if($visitID==$this->user->empID) $tnt='My ';
	else{
		$tnt = trim($row->fname);
		$tnt = strtok($tnt, ' ');
		
		if($this->access->accessFullHR && strlen($tnt)>6) 
			$tnt = substr($tnt,0,6).'..';
		
		$tnt .= '\'s ';
	}

	echo '<li class="tab-link '.(($tpage=='index' || $tpage=='timelogs')?'current':'').'" data-tab="timelogs">'.$tnt.'Time Logs</li> ';
	echo '<li class="tab-link '.(($tpage=='calendar')?'current':'').'" data-tab="calendar">'.$tnt.'Calendar</li> ';
	echo '<li class="tab-link '.(($tpage=='payslips')?'current':'').'" data-tab="payslips">'.$tnt.'Payslips</li> ';
	
	if($this->user->is_supervisor==1 || $this->access->accessFullHRFinance==true)
		echo '<li class="tab-link admin '.(($tpage=='attendance')?'current':'').'" data-tab="attendance">Attendance</li> ';
	
	if($this->access->accessFullHR==true){		
		echo '<li class="tab-link admin '.(($tpage=='scheduling')?'current':'').'" data-tab="scheduling">Scheduling</li> ';
	}
	if($this->access->accessFullHRFinance==true){
		echo '<li class="tab-link admin '.(($tpage=='payrolls')?'current':'').'" data-tab="payrolls">Payrolls</li> ';
		//echo '<li class="tab-link admin '.(($tpage=='reports')?'current':'').'" data-tab="reports">Reports</li> ';
	}
?>	
</ul>

<script type="text/javascript">
	$(function(){
		$('.tab-link').click(function(){
			location.href="<?= $this->config->base_url().'timecard/'.(($visitID!='' && $visitID!=$this->user->empID)?$visitID.'/':'').''?>"+$(this).attr('data-tab')+"/";
		});
	});
</script>

<?php
	if(isset($column) && $column=='withLeft' && count($row)==0){ echo '<br/>No record for this employee.'; exit; }
?>