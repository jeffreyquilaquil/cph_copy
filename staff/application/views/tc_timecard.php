<h2>My Timecard and Payroll</h2>
<hr/>
<ul class="tabs">
	<li class="tab-link <?= (($tpage=='index' || $tpage=='timelogs')?'current':'') ?>" data-tab="timelogs">Time Logs</li>
	<li class="tab-link <?= (($tpage=='attendance')?'current':'') ?>" data-tab="attendance">Attendance</li>
	<li class="tab-link <?= (($tpage=='calendar')?'current':'') ?>" data-tab="calendar">Calendar</li>
	<li class="tab-link <?= (($tpage=='scheduling')?'current':'') ?>" data-tab="scheduling">Scheduling</li>
	<li class="tab-link <?= (($tpage=='payslips')?'current':'') ?>" data-tab="payslips">Payslips</li>
	<li class="tab-link <?= (($tpage=='payrolls')?'current':'') ?>" data-tab="payrolls">Payrolls</li>
	<li class="tab-link <?= (($tpage=='reports')?'current':'') ?>" data-tab="reports">Reports</li>
</ul>

<!-- TIMELOGS -->
<div id="timelogs" class="tab-content <?= (($tpage=='index' || $tpage=='timelogs')?'current':'') ?>">
<table border=0 class="attendancetbl">
	<tr><td colspan=2>
		Your schedule today (Friday, December 5, 2014) is 7:00 AM – 4:00 PM.<br/>
		You clocked in at 07:14 AM. This is 14 minutes late.<br/>
		Breaks Taken: 45 minutes and 55 seconds.<br/>
		You would be leaving early by : 2 hours 3 minutes<br/>
	</td></tr>
	<tr>
		<td><button class="padding5px">Clock Out</button></td>
		<td><button class="padding5px">Take a Break</button></td>
	</tr>
</table>
<?php 
	$this->load->view('tc_calendar', $timelogs);
?>
</div>

<!-- ATTENDANCE -->
<div id="attendance" class="tab-content <?= (($tpage=='attendance')?'current':'') ?>">
<?php 
	$this->load->view('tc_calendar', $attendance);
?>
</div>

<!-- CALENDAR -->
<div id="calendar" class="tab-content <?= (($tpage=='calendar')?'current':'') ?>">
<?php 
	$this->load->view('tc_calendar', $calendar);
?>
</div>

<!-- SCHEDULDING -->
<div id="scheduling" class="tab-content <?= (($tpage=='scheduling')?'current':'') ?>">	
<?php
	if(isset($assignSched) && $assignSched==true){
		echo '<table class="tableInfo">';
		foreach($allStaffs AS $at):
			echo '<tr>';
			echo '<td><div>
				<b>'.$at->lname.', '.$at->fname.'</b><br/>
				'.$at->title.'<br/>
				'.$at->holidaySched.'
				</div></td>';
			echo '<td>Assign to Custom Schedule:</td>';
			echo '</tr>';
		endforeach;
		echo '</table>';
	
?>
<?php
	}else{
?>
	<form id="formSched" action="<?= $this->config->base_url().'timecard/scheduling/'?>" method="POST">
	<input type="button" id="assignschedbutton" name="assignschedbutton" value="Assign a Schedule" class="padding5px floatright"/>
	<br/>
	
		
	<table class="dTable display stripe hover">
	<?php
	echo '<thead>';
		echo '<tr>';
		echo '<th>Name</th>';
		echo '<th>'.date('l').'<br/>'.date('d M Y').'</th>';
		echo '<th>'.date('l', strtotime('+1 day')).'<br/>'.date('d M Y', strtotime('+1 day')).'</th>';
		echo '<th>'.date('l', strtotime('+2 day')).'<br/>'.date('d M Y', strtotime('+2 day')).'</th>';
		echo '<th>'.date('l', strtotime('+3 day')).'<br/>'.date('d M Y', strtotime('+3 day')).'</th>';
		echo '<th>'.date('l', strtotime('+4 day')).'<br/>'.date('d M Y', strtotime('+4 day')).'</th>';
		echo '<th>'.date('l', strtotime('+5 day')).'<br/>'.date('d M Y', strtotime('+5 day')).'</th>';
		echo '<th>'.date('l', strtotime('+6 day')).'<br/>'.date('d M Y', strtotime('+6 day')).'</th>';
		echo '</tr>';
	echo '</thead>';

	foreach($allStaffs AS $a):
		echo '<tr>';
		echo '<td><input type="checkbox" name="assign[]" id=="'.$a->empID.'" value="'.$a->empID.'" class="hidden"/>
			'.$a->lname,', '.$a->fname.'</td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '</tr>';
		/* echo '<tr>';
		echo '<td><input type="checkbox" name="assign[]" value="'.$a->empID.'" class="hidden"/> '.$a->lname.'</td>';
		echo '<td>'.$a->fname.'</td>';
		echo '<td>'.$a->title.'</td>';
		echo '<td>'.$a->shift.'</td>';
		echo '<td>'.$a->dept.'</td>';
		echo '<td>'.$a->leader.'</td>';
		echo '<td>'.$a->holidaySched.'</td>';
		echo '</tr>'; */
	endforeach;
?>
	</form>
	</table>
<?php } ?>
</div>


<script type="text/javascript">
	$(function(){
		//$('.dTable').dataTable();
	
		
		$('.dTable').dataTable({
			"dom": 'lf<"toolbar">tip'
		});
		
		
		//"dom": '<"toolbar">frtip'
		$("div.toolbar").html('<br/><br/><br/><input type="checkbox" name="includeinactive" <?= ((isset($_POST['includeinactive']))?'checked':'')?>/> <b>include inactive employees</b><br/><input type="checkbox" id="selectAll"/> Select All<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>Or Select the employees below that need a Schedule Assignment.</i><br/><br/>');
				
		$('.dTable').on('click','tbody tr',function(){
			$(this).toggleClass('selected');
			var checkBoxes = $(this).find("input[name=assign\\[\\]]");
			checkBoxes.prop("checked", !checkBoxes.prop("checked"));
		});
		
		 		
		$("input[name='includeinactive']").click(function(){
			/* ppage = $(".tab-content.current").attr('id');
			if(ppage!=''){
				$(".tab-content.current").attr('id');
				window.location.href='<?= $this->config->base_url().'timecard/'?>'+ppage+'/';
			} */
			$('#formSched').submit();		
			
			
		});
		
		$('#selectAll').click(function(){
			if($(this).is(":checked")==true){
				$('.dTable tbody tr').addClass('selected');
				$("input[name=assign\\[\\]]").prop('checked', true);				
			}else{
				$('.dTable tbody tr').removeClass('selected');
				$("input[name=assign\\[\\]]").prop('checked', false);
			}
		});
		
		
		// $(".iframe2").colorbox({iframe:true, width:"990px", height:"600px"});
		$('#assignschedbutton').click(function(){			
			 var val = [];
			$(':checkbox:checked').each(function(i){
			  val[i] = $(this).val();
			});
			
			alert(val);
			var valuehere = "0";
			size = val.length;
			for(count = 0; count < size ; count++) {
				valuehere += val[count]+"_";
			}
			
			$.colorbox({width:"900px", height:"600px", iframe:true, href:'<?= $this->config->base_url().'schedules/setstaffschedule/' ?>'+valuehere});			
			// $.post('<?= $this->config->base_url().'schedules/setstaffschedule/' ?>'+valuehere, function(data){				
			// });		
			
		});
		
		<?php
			if(isset($errortext)){ echo 'alert("'.$errortext.'");'; }
		?>
		
	});
</script>