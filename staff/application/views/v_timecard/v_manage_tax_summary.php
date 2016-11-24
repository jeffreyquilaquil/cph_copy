<style class="text/css">
	#dtable13 tfoot tr th{ text-align:left; padding-left:0px; }
	#dtable13 tfoot tr th select{ padding:3px; }
	ul.dropleft{ right:20px; top:-10px; }
</style>
<?php if($this->access->accessFullFinance == true){
	echo '<span style="text-align: right; float:right;"><button class="btnclass" onClick="distro();">Download Distribution Report</button></span>';
}
?>
<h2>Generated Tax Summary</h2>

<hr/>
<div class='selectionDiv' style="padding-top:5px;">
	<a class="cpointer" id="selectAll">Select All</a> | <a class="cpointer" id="deselectAll">Deselect All</a>
</div>
<br/>
<form name="frm_13th_month" action="" method="post">
<table id="dtable13" class="display stripe hover">
	<thead>
	<tr>
		<th>&nbsp;</th>
		<th>Employee Name</th>
		<th>Total Taxable Income</th>
		<th>Income Tax withheld</th>
		<th>Tax Due for the Year</th>
		<th>Tax Refund/Deficit of the Year</th>
		<th class="hiddend"><br/></th>
	</tr>	
	</thead>
	
	<tbody>
<?php
	foreach($queryData AS $data){
		echo '<tr>';
			echo '<td><input type="checkbox" class="classCheckMe" name="id_[]" data-empid="'.$data->empID_fk.'" value="'.$data->tcTaxSummary_ID .'" /></td>';
			echo '<td>'.$data->fullName.'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->totalTaxableIncome).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->incomeTaxWithheld).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->taxDue).'</td>';
			echo '<td>'.$data->taxRefund.'</td>';
			echo '<td>';
				echo '<ul class="dropmenu">';
					echo '<li><img src="'.$this->config->base_url().'css/images/icon-options-edit.png" width="20px" class="cpointer"/>';
						echo '<ul class="dropleft">';
							echo '<li><a href="'.$this->config->base_url().'timecard/taxsummary/'.$data->empID_fk.'/" class="iframe">View Details</a></li>';
						echo '</ul>';
					echo '</li>';
				echo '</ul>';
				
			echo '</td>';
		echo '</tr>';
	}
	
?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="7">
		<?php if( $this->access->accessFullFinance == true ){
			echo '<p>On selected items: <input type="submit" onClick="confirmMsg();" name="delete_tax_record" class="btnclass" value="Delete" /> <input type="button" name="regenerate" class="btnclass" value="Regenerate" /></p>';
		}
		?>
			</td>
		</tr>
	</tfoot>
</table>
</form>
<script type="text/javascript">
$(function(){
	$('#dtable13').dataTable({});
	$('#selectAll').click(function(){
		$('.classCheckMe').prop('checked', true);
		countChecked();
	});

	$('.classCheckMe').change(function(){
		countChecked();
	});

	function countChecked(){
		var countCheck = 0;
		$('.classCheckMe').each(function(){
			if( $(this).is(':checked') ){
				countCheck++;
			}
		});
		$('.selectionLabel').remove();
		$('.selectionDiv').append('<div class="selectionLabel"><strong><i>'+countCheck+' Selected<i></strong></div>');
	}

	$('#deselectAll').click(function(){
		$('.classCheckMe').prop('checked', false);
		countChecked();
	});
	$('input[name="regenerate"]').click(function(){
		empIDs = checkIfSelected();
		if(empIDs==false){
			alert('Please select employee first.');
		}else{
			myhref = "<?= $this->config->base_url().'timecard/generatetaxsummary/?empIDs=' ?>"+empIDs;
			window.parent.jQuery.colorbox({href:myhref, iframe:true, width:"990px", height:"600px"});
		}
	});
	
	$("#dtable13 tfoot tr th select:first").css( "width", "150px" );
});

function checkIfSelected(){
	var empIDs = '';
	var cars = [];
	$('.classCheckMe').each(function(){
		if($(this).is(':checked')){
			cars.push($(this).val());
			empIDs = empIDs+$(this).data('empid')+',';
		}
	});
	
	if(cars.length==0){
		return false;			
	}else{
		return empIDs;
	}
}

function confirmMsg(){
	return confirm("Are you sure to delete the item?");
}

function regenerateMonth(empID, id){
	displaypleasewait();
	$.post('<?= $this->config->base_url().'timecard/generate13thmonth/?empIDs=' ?>'+empID, {submitType:'regenerate', monthID:id }, function(){
		window.parent.jQuery.colorbox({href:"<?= $this->config->base_url() ?>timecard/detail13thmonth/"+id+"/", iframe:true, width:"990px", height:"600px"});
	});
}

function distro(){
	window.location.href="<?= $this->config->base_url() ?>timecard/detail13thmonth/?show=distro&which=distro";
}
 
</script>