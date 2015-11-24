<?php
	$addItems = array();
	foreach($dataAddItems AS $add){
		$addItems[$add->payID] = $add->payName;
	}
	$addItems['other'] = 'Other. Not on the list.';
	
	echo '<h3>';
		if($pageType=='batch'){
			echo 'Batch Adding Payroll Item &nbsp;&nbsp;&nbsp;';
			echo $this->textM->formfield('selectoption', 'additionalItem', '', 'padding5px', 'Please select an item', 'style="width:250px; background-color:#ffb2b2"', $addItems);
			
			$hrefChange =  $this->config->base_url().'timecard/manangepaymentitem/?pageType=empUpdate&add=yes&empIDs='.$empIDs.'&payID=';
		}else{
			if($visitID==$this->user->empID) echo 'My Payroll Settings';
			else if(isset($row->fname)) echo $row->fname.'\'s Payroll Settings';
			else echo 'Payroll Settings';
			
			if($this->access->accessFullHR==true) echo '&nbsp;&nbsp;&nbsp;<button id="btnadd" class="btnclass btngreen">+ Add Payment Item</button>';		
			echo $this->textM->formfield('selectoption', 'additionalItem', '', 'hidden padding5px', 'Please select an item', 'style="width:250px; background-color:#ffb2b2"', $addItems);
			
			$hrefChange =  $this->config->base_url().'timecard/'.$visitID.'/manangepaymentitem/?pageType=empUpdate&add=yes&payID=';
		} 
		
	echo '</h3>';
	echo '<hr/>';
		
	if($pageType=='batch'){
		echo '<b>To the following staffs:</b>';
		echo '<ul>';
			foreach($dataStaffs AS $s)
				echo '<li>'.$s->name.'</li>';
		echo '</ul>';
	}else if(count($dataMyItems)>0){
		echo '<table class="tableInfo">';
			echo $this->textM->displayPaymentItems($dataMyItems, $visitID);
		echo '</table>';
	}
		
	
	
?>

<script type="text/javascript">
	$(function(){
		$('#btnadd').click(function(){
			$(this).addClass('hidden');
			$('select[name="additionalItem"]').removeClass('hidden');
		});
		
		$('select[name="additionalItem"]').change(function(){
			if($(this).val()!=''){
				displaypleasewait();
				if($(this).val()=='other')
					window.location.href = '<?= $this->config->base_url().'timecard/manangepaymentitem/?pageType=addItem&type=0' ?>';
				else
					window.location.href = '<?= $hrefChange ?>'+$(this).val();
			}
		});
	});
</script>