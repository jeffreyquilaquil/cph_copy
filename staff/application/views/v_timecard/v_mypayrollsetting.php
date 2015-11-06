<h3><?= $row->fname.'\'s Payroll Setting' ?></h3>
<hr/>
<table class="tableInfo">
<?php
	$selectOption = array('monthly'=>'Monthly', 'semi'=>'Semi-Monthly');
	foreach($payItems AS $item){
		echo '<tr>';
			echo '<td width="25%">'.$item->itemName.'</td>';
			echo '<td>'.$this->textM->formfield('selectoption', 'item_'.$item->itemID, $item->itemPeriod, 'forminput', '', 'disabled', $selectOption).'</td>';
			echo '<td><button type="button" onclick="edit('.$item->itemID.')">Edit</button></td>';
		echo '</tr>';
	}
?>
</table>

<script type="text/javascript">
	$(function(){
		
	});
	
	function edit(id){
		$('select[name="item_'+id+'"]').removeAttr('disabled');
	}
</script>