<style class="text/css">
	#dtable13 tfoot tr th{ text-align:left; padding-left:0px; }
	#dtable13 tfoot tr th select{ padding:3px; }
	ul.dropleft{ right:20px; top:-10px; }
</style>
<?php if($this->access->accessFullFinance == true){
	echo '<span style="text-align: right; float:right;"><button class="btnclass" onClick="distro();">Download Distribution Report</button></span>';
}
?>
<h2>BIR 2316</h2>

Select Year: 
<select name='yearchange' style="padding:2px">
<?php
	for($i = 2016; $i < 2020; $i++){
		echo "<option value='$i'>$i</option>";
	}
?>
</select>

<hr/>

<form class='frm_bir2316' name="frm_13th_month" action="" method="post">

</form>
<script type="text/javascript">
$(function(){

	callAjax("<?=date('Y')?>");

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
		
		callAjax(year);
	});

	function callAjax(year){
		$.ajax({
			url: '<?php echo $this->config->base_url()?>timecard/birmanagement_ajax',
			data: 'year='+year,
			type: "POST",
			success: function (e){
				console.log(e);
				$('.frm_bir2316').children().remove();
				$('.frm_bir2316').append(e);
			}
		});
	}

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
			myhref = "<?= $this->config->base_url().'timecard/generate13thmonth/?empIDs=' ?>"+empIDs;
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
	var year = $('select[name="yearchange"]').val();
	window.location.href="<?= $this->config->base_url() ?>timecard/birmanagement/?show=distro&year="+year;
}
 
</script>