<h2>Reports</h2>
<hr/>

<?php if( isset($which_report) AND $which_report == 'upward_feedback' ) { ?>
	<div>
		<table class="tableInfo datatable">
			<thead>
				<tr>
					<td>Date Submitted</td>
					<td>Name of respondent</td>
					<td>Feedback for (name of leader)</td>
					<td>Feedback</td>
				</tr>
			</thead>
			<tbody>
				<?php if( isset($feedbacks) AND !empty($feedbacks) ){
					foreach($feedbacks as $feedback){
						echo '<tr>';
						echo '<td>'. date('F d, Y', strtotime($feedback->date_submitted) ) .'</td>';
						echo '<td>'. $feedback->respondent .'</td>';
						echo '<td>'. $feedback->supervisor .'</td>';
						echo '<td><textarea style="width: 250px; height: 100px;" disabled>'. $feedback->feedback .'</textarea></td>';
						echo '</tr>';
					}
				} ?>
			</tbody>
		</table>
	</div>
<?php } else { ?>
<ul class="tabs">
	<li class="tab-link current" data-tab="tab-1">Generated Leave Codes</li>
	<li class="tab-link" data-tab="tab-2">Compensation Reports</li>
	<li class="tab-link" data-tab="tab-3">Generate Attendance Report</li>
</ul>
<div id="tab-1" class="tab-content current">
<h3>Generated Leave Codes</h3>

		<form action="" method="POST">
		<table style="border:1px solid #000" cellpadding=5>
			<tr>
				<td>Date From:</td>
				<td><?= $this->textM->formfield('text', 'dateFrom', '', 'datepick', '', 'required width:250px;'); ?></td>
			</tr>
			<tr>
				<td>Date To:</td>
				<td><?= $this->textM->formfield('text', 'dateTo', '', 'datepick', '', 'required width:250px;'); ?></td>
			</tr>
			<tr>
				<td><br/></td>
				<td><button class="btnclass btngreen">Generate</button></td>
			</tr>
		</table>
		<?= $this->textM->formfield('hidden', 'submitType', 'genLeaveCodes'); ?>
		</form>

</div>
<div id="tab-2" class="tab-content">
<h3>Compensation Reports</h3>
<hr/>
As of <?php echo date('F 01, Y'); ?><br/><br/>

<h1>Php <?php echo $this->textM->convertNumFormat($average_wage_rankfile); ?></h1>
Average Rank and File Salary
<br/><br/><br/>
<h1>Php <?php echo $this->textM->convertNumFormat($average_wage_supervisor); ?></h1>
Average Leader Salary
<br/>
<br/><br/><br/>
<a href="#" class="past_calc">Click here to see previous calculations</a>
<div class="past_calc_div">
	<?php if( isset($past_calc) AND !empty($past_calc) ) {
		echo '<table class="tableInfo">';
		echo '<thead>
				<tr>
					<td>Month</td>
					<td>Total Leaders</td>
					<td>Averange Salary</td>
					<td>Total Rank and File</td>
					<td>Averange Salary</td>
				</tr>
			</thead>';
		echo '<tbody>';
		foreach( $past_calc as $row ){
			echo '<tr>';
			$average_supervisor = '';
			$average_supervisor = $this->textM->decryptText( $row->total_wage_supervisor ) / $row->total_supervisor;
			
			$average_rankfile = ''; 
			$average_rankfile = $this->textM->decryptText( $row->total_wage_rankfile ) / $row->total_rankfile;
			
			echo '<td>'. date('F, Y', strtotime($row->for_month) ).'</td>';
			echo '<td>'. $row->total_supervisor .'</td>';
			echo '<td>'. $this->textM->convertNumFormat( $average_supervisor ) .'</td>';
			echo '<td>'. $row->total_rankfile .'</td>';
			echo '<td>'. $this->textM->convertNumFormat( $average_rankfile ).'</td>';
			echo '</tr>';
		}
		echo '</tbody></table>
		<a href="#" class="hide">Hide</a>
		';
	} ?>
</div>
</div>
<div id="tab-3" class="tab-content">
<h3>Generate Attendance Reports</h3>

		<form action="" method="POST">
		<table style="border:1px solid #000" cellpadding=5>
			<tr>
				<td>Date From:</td>
				<td><?= $this->textM->formfield('text', 'dateFrom', '', 'datepick', '', 'required width:250px;'); ?></td>
			</tr>
			<tr>
				<td>Date To:</td>
				<td><?= $this->textM->formfield('text', 'dateTo', '', 'datepick', '', 'required width:250px;'); ?></td>
			</tr>
			<tr>
				<td><br/></td>
				<td><button class="btnclass btngreen">Generate</button></td>
			</tr>
		</table>
		<?= $this->textM->formfield('hidden', 'submitType', 'genAttendanceReports'); ?>
		</form>

</div>
<script>
$(function(){
	$('div.past_calc_div').hide();
	$('a.past_calc').click(function(){
		$('div.past_calc_div').show();
	});
	$('a.hide').click(function(){
		$('div.past_calc_div').hide();
	});
});
</script>

<?php } //end not upward feedback report ?>