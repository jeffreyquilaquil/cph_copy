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
		echo '<tr>';
			echo '<td><input type="checkbox" class="classCheckMe" name="id_[]" value="'.$data->tcmonthID.'" /></td>';
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
			echo '<p>On selected items: <input type="submit" onClick="confirmMsg();" name="delete_13th_record" class="btnclass" value="Delete" /></p>';
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
	<?php /*
        initComplete: function () {
            this.api().columns().every( function () {
                var column = this;
                var select = $('<select><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );
 
                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    });
 
                column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                } );
            } );
        } 
    });*/ ?>
	
	$("#dtable13 tfoot tr th select:first").css( "width", "150px" );
});

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