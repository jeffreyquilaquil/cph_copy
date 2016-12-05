<style class="text/css">
	#dtable13 tfoot tr th{ text-align:left; padding-left:0px; }
	#dtable13 tfoot tr th select{ padding:3px; }
	ul.dropleft{ right:20px; top:-10px; }
</style>
<?php if($this->access->accessFullFinance == true){
	echo '<span style="text-align: right; float:right;"><button class="btnclass" onClick="distro();">Download Distribution Report</button></span>';
}
?>
<h2>Generated 13th Month</h2>

<hr/>

Select Year: 
<select name='yearchange' style="padding:2px">
<?php
	$year = isset($_GET['year'])? $_GET['year']:date('Y');

	for($i = 2016; $i < 2020; $i++){
		$selected = '';
		if($year == $i){
			$selected = 'selected';
		}
		echo "<option $selected value='$i'>$i</option>";
	}
?>
</select>

<?php
	$totalBasic = 0;
	$totaladjustment = 0;
	$total13thmonth = 0;
	$totalPublished = 0;

	foreach($queryData AS $data){
		$totalBasic += $data->totalBasic;
		$totaladjustment += $data->totalDeduction;
		$total13thmonth += $data->totalAmount;
		if($data->tc13thMonthPublished > 0)
			$totalPublished += $data->totalAmount;
	}
?>

<div style="width:70%">
	<table class="tableInfo tacenter">
		<tr class="trhead">
			<td>Year</td>
			<td>Number Generated</td>
			<td>Total Basic Pay</td>
		</tr>
		<tr>
			<td><?=$year?></td>
			<td><?=count($queryData)?></td>
			<td>Php <?=$this->payrollM->formatNum($totalBasic)?></td>
		</tr>
		<tr class="trhead">
			<td>Total Adjustments</td>
			<td>Total 13th Month</td>
			<td>Total Published 13th Month</td>
		</tr>
		<tr>
			<td>Php <?=$this->payrollM->formatNum($totaladjustment)?></td>
			<td style="border:2px solid red;"><b>Php <?=$this->payrollM->formatNum($total13thmonth)?></b></td>
			<td style="border:2px solid red;"><b>Php <?=$this->payrollM->formatNum($totalPublished)?></b></td>
		</tr>
	</table>
</div>

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
		<th>Total Basic Pay</th>
		<th>Total Adjustments</th>
		<th>13th Month Amount</th>
		<th>Period</th>
		<th class="hiddend"><br/></th>
	</tr>	
	</thead>
	
	<tbody>
<?php
	foreach($queryData AS $data){
		$style = '';
		if( $data->tc13thMonthPublished > 0 )
			$style = 'style= "background-color:#ccffcc"';

		echo '<tr '.$style.'>';
			echo '<td><input type="checkbox" class="classCheckMe" name="id_[]" data-empid="'.$data->empID_fk.'" value="'.$data->tcmonthID.'" /></td>';
			echo '<td>'.$data->lname.', '.$data->fname.'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->totalBasic).'</td>';
			echo '<td>'.$this->textM->convertNumFormat($data->totalDeduction).'</td>';
			

			echo '<td><b>Php '.$this->textM->convertNumFormat($data->totalAmount).'</b></td>';
			echo '<td>'.date('M', strtotime($data->periodFrom)).' - '.date('M Y', strtotime($data->periodTo)).'</td>';
			echo '<td>';
				echo '<ul class="dropmenu">';
					echo '<li><img src="'.$this->config->base_url().'css/images/icon-options-edit.png" width="20px" class="cpointer"/>';
						echo '<ul class="dropleft">';
							echo '<li><a href="javascript:void(0);" onClick="regenerateMonth('.$data->empID_fk.', '.$data->tcmonthID.')">Regenerate 13th Month</a></li>';
							echo '<li><a href="'.$this->config->base_url().'timecard/detail13thmonth/'.$data->tcmonthID.'/" class="iframe">View Details</a></li>';
							echo '<li><a href="'.$this->config->base_url().'timecard/detail13thmonth/'.$data->tcmonthID.'/?show=pdf" class="iframe">View PDF</a></li>';
							
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
			echo '<p>On selected items: <input type="submit" onClick="confirmMsg();" name="delete_13th_record" class="btnclass" value="Delete" /> <input type="button" name="regenerate" class="btnclass" value="Regenerate" /> <input type="button" name="publish" class="btnclass" value="Publish (all)" /> </p>';
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

	$('select[name="yearchange"]').change(function(){
		var year = $(this).val();

		var myhref = "<?= $this->config->base_url().'timecard/manage13thmonth/?year=' ?>"+year;
		window.location.href = myhref;
	});

	$('input[name="publish"]').click(function(){
		var ids =  checkIfSelected('payID');
		var payID = 'all';
		if(ids){
			payID = ids;
		}

		var p = confirm('Confirm Publish?');
		if(p){
			var year = $('select[name="yearchange"]').val();

			var myhref = "<?= $this->config->base_url().'timecard/manage13thmonth/?year=' ?>"+year+"&payID="+payID+"&method=publish";
			//alert(myhref);
			window.location.href = myhref;
			//alert(payID);
		}
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
		if(countCheck > 0){
			$('input[name="publish"]').val('Publish ('+countCheck+')');
		}
		else{
			$('input[name="publish"]').val('Publish (all)');	
		}
	}

	$('#deselectAll').click(function(){
		$('.classCheckMe').prop('checked', false);
		countChecked();
	});
	$('input[name="regenerate"]').click(function(){
		empIDs = checkIfSelected('empID');
		if(empIDs==false){
			alert('Please select employee first.');
		}else{
			myhref = "<?= $this->config->base_url().'timecard/generate13thmonth/?empIDs=' ?>"+empIDs;
			window.parent.jQuery.colorbox({href:myhref, iframe:true, width:"990px", height:"600px"});
		}
	});
	
	$("#dtable13 tfoot tr th select:first").css( "width", "150px" );
});

function checkIfSelected(idToCheck){
	var id = '';
	var cars = [];
	$('.classCheckMe').each(function(){
		if($(this).is(':checked')){
			cars.push($(this).val());
			if(idToCheck == 'payID'){
				id = id+$(this).val()+',';
			}
			else{
				id = id+$(this).data('empid')+',';
			}
		}
	});
	
	if(cars.length==0){
		return false;			
	}else{
		return id;
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