<h3>Generate Tax Summary</h3>
<hr/>
<span class='hideTable'><a href='#'>Show Employee List</a></span><br/>
<table class="tableInfo empList">
	<tr class="trhead">
		<td>Employee ID</td>
		<td>Employee Name</td>
		<td>Start Date</td>
	</tr>
<?php
	foreach($dataStaffs AS $staff){
		echo '<tr>';
			echo '<td>'.$staff->idNum.'</td>';
			echo '<td>'.$staff->lname.', '.$staff->fname.'</td>';
			echo '<td>'.date('F d, Y', strtotime($staff->startDate)).'</td>';
		echo '</tr>';
	}
?>
</table>
<br/>
<?php
	$yearOption = array();
	$monthOption = array();
	$yearToday = date('Y', strtotime('+1 year'));
	for($y=2014; $y<=$yearToday; $y++){
		$yearOption[$y] = $y;
	}	
	
	for($m=1;$m<=12;$m++){
		$month_name = date('F', strtotime('2015-'.$m.'-01'));
		$monthOption[$month_name] = $month_name;
	}
	
	$fromMonth = 'January';
	$fromYear = date('Y');
	$toMonth = date('F');
	$toYear = date('Y');
	
	if(isset($genInfo)){
		$fromMonth = date('F', strtotime($genInfo->periodFrom));
		$fromYear = date('Y', strtotime($genInfo->periodFrom));
		$toMonth = date('F', strtotime($genInfo->periodTo));
		$toYear = date('Y', strtotime($genInfo->periodTo));
	}

?>

<form action="" method="POST" onSubmit="displaypleasewait();">
<table class="tableInfo">
	<tr class="trlabel">
		<td colspan=2>13th Month Details</td>
	</tr>
	<tr>
		<td width="200px">Period From</td>
		<td><?php
			echo $this->textM->formfield('text', 'monthFrom', $fromMonth, 'padding5px', '', 'disabled style="width:139px;"');
			echo '&nbsp;&nbsp;';
			echo $this->textM->formfield('selectoption', 'yearFrom', $fromYear, 'padding5px', '', 'style="width:150px;"', $yearOption);
		?></td>
	</tr>
	<tr>
		<td>Period To</td>
		<td><?php
			echo $this->textM->formfield('selectoption', 'monthTo', $toMonth, 'padding5px', '', 'style="width:150px;"', $monthOption); 
			echo '&nbsp;&nbsp;';
			echo $this->textM->formfield('selectoption', 'yearTo', $toYear, 'padding5px', '', 'style="width:150px;"', $yearOption);
		?></td>
	</tr>
	<tr>
		<td><br/></td>
		<td><?php
			echo $this->textM->formfield('hidden', 'submitType', 'generate', 'btnclass btngreen');
			echo $this->textM->formfield('submit', '', 'Generate', 'btnclass btngreen');
			echo '&nbsp;&nbsp;';
			echo $this->textM->formfield('button', '', 'Cancel', 'btnclass', '', 'onClick="parent.$.colorbox.close();"');	
			if(isset($errorText)) echo '<p class="errortext">'.$errorText.'</p>';
		?></td>
	</tr>
</table>
</form>
<script type="text/javascript">
	$(document).ready(function(){
		$('.empList').hide();
		$('.hideTable a').click(function(){
			$('.empList').toggle();
		});
	});
</script>