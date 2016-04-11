<?php
	if(isset($_POST['submit']) ){
		$dateFrom = $_POST['periodFromYear'].'-01';
		$dateTo = $_POST['periodToYear'].'-'.$_POST['periodToMonth'];

		$dateFrom = date('Y-m', strtotime($dateFrom));
		$dateTo = date('Y-m', strtotime($dateTo));

		$staffs = $this->dbmodel->getQueryResults('tcLastPay', '*', $where='DATE_FORMAT(endDate,"%Y-%m") BETWEEN "'.$dateFrom.'" AND "'.$dateTo.'"', $join='LEFT JOIN staffs ON empID = empID_fk', $orderby='', $trace=false);
		echo "<pre>";
		var_dump($staffs);
		echo "</pre>";
	}
?>
<h3>Generate Alphalist</h3>
<table class="tableInfo">
	<tr class="trlabel"><td>Alphalist Details</td></tr>
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

		echo '<tr>';
			echo '<td>';
				echo '<form method="POST" action="" onSubmit="displaypleasewait();">';
					echo '<b>From </b> January '.$this->textM->formfield('selectoption', 'periodFromYear', date('Y'), 'padding5px', '', '', $yearOption);
					echo ' <b>to</b> ';
					echo $this->textM->formfield('selectoption', 'periodToMonth', date('F'), 'padding5px', '', '', $monthOption);
					echo '&nbsp;';
					echo $this->textM->formfield('selectoption', 'periodToYear', date('Y'), 'padding5px', '', '', $yearOption);
					echo '&nbsp;&nbsp;';
					echo $this->textM->formfield('submit', 'submit', 'Submit', 'btnclass btngreen');
				echo '<form>';
			echo '</td>';
		echo '</tr>';
	?>
</table>