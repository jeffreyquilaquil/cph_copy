<h2>Staff Evaluations</h2>
<hr>

<ul class="tabs">
	<?php 
	#	$evaluations = $evaluations[0];
		$counter = 0;
		foreach ($tabs as $value) {
			$current = ($counter == 0 ? 'current' : '');
			$count = count($evaluations[$counter]);
			echo "<li class='tab-link ".$current."' data-tab='tab-".$counter."'>".$value." [".$count."]</li>";
			$counter++;
		}
	 ?>
</ul>

<?php
$counter = 0;
$th = ['Evaluation ID',"Employee's Name","Date Generated", "Evaluation Date", 'Immediate Supervisor', 'Status', 'Action'];
foreach (range(0,4) as $value) {
	$current = ($counter == 0 ? 'current' : '');
	echo '<div id="tab-'.$counter.'" class="tab-content '.$current.'"><br>';
	$count = count($evaluations[$value]);
	echo '<h3>'.ucwords($value).' ('.$count.')</h3>';

	$this->textM->renderTable($th, '',$evaluations[$value], true, true);

	echo '</div>';
	$counter++;
} 
?>