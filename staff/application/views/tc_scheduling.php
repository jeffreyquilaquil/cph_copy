<?php
	$this->load->view('includes/header_timecard'); 
	
	// echo "alert".date('Y-m-d', strtotime('2015-05-28','+1 day'));
	
	if(isset($assignSched) && $assignSched==true){
		echo '<table class="tableInfo">';
		foreach($allStaffs AS $at):
			echo '<tr>';
			echo '<td><div>
				<b>'.$at->lname.', '.$at->fname.'</b><br/>
				'.$at->title.'<br/>
				'.$at->staffholidaySched.'
				</div></td>';
			echo '<td>Assign to Custom Schedule:</td>';
			echo '</tr>';
		endforeach;
		echo '</table>';
	
?>
<?php
	}else{
	
?>



	<!--<form id="formSched" action="<?= $this->config->base_url().'timecard/scheduling/'?>" method="POST">	
	<input type="submit" name="submitType" value="Assign a Schedule" class="padding5px floatright"/><br/>
	<table class="dTable display stripe hover">-->
	<input type="button" id="assignschedbutton" name="assignschedbutton" value="Assign a Schedule" class="padding5px floatright"/>	
	<table class="dTable display stripe hover" border="1">
	<?php
	echo '<thead>';
		echo '<tr>';
		echo '<th>Name</th>';
		echo '<th>'.date('l').'<br/>'.date('d M Y').'</th>';
		echo '<th>'.date('l', strtotime('+1 day')).'<br/>'.date('d M Y', strtotime('+1 day')).'</th>';
		echo '<th>'.date('l', strtotime('+2 day')).'<br/>'.date('d M Y', strtotime('+2 day')).'</th>';
		echo '<th>'.date('l', strtotime('+3 day')).'<br/>'.date('d M Y', strtotime('+3 day')).'</th>';
		echo '<th>'.date('l', strtotime('+4 day')).'<br/>'.date('d M Y', strtotime('+4 day')).'</th>';
		echo '<th>'.date('l', strtotime('+5 day')).'<br/>'.date('d M Y', strtotime('+5 day')).'</th>';
		echo '<th>'.date('l', strtotime('+6 day')).'<br/>'.date('d M Y', strtotime('+6 day')).'</th>';
		echo '</tr>';
	echo '</thead>';
		
	foreach($allStaffs AS $a):
		echo '<tr>';
		echo '<td class="nameId"><input type="checkbox" name="staffnames[]" value="'.$a->empID.'" class="hidden"/>
			'.$a->lname,', '.$a->fname.'</td>';
		echo '<td class="tddate"><input type="checkbox" name="assign[]" value="'.date('Y-m-d').'~'.$a->empID.'" class="hidden" /></td>';
		echo '<td class="tddate"><input type="checkbox" name="assign[]" value="'.date('Y-m-d', strtotime('+1 day')).'~'.$a->empID.'" class="hidden" /></td>';
		echo '<td class="tddate"><input type="checkbox" name="assign[]" value="'.date('Y-m-d', strtotime('+2 day')).'~'.$a->empID.'" class="hidden" /></td>';
		echo '<td class="tddate"><input type="checkbox" name="assign[]" value="'.date('Y-m-d', strtotime('+3 day')).'~'.$a->empID.'" class="hidden" /></td>';
		echo '<td class="tddate"><input type="checkbox" name="assign[]" value="'.date('Y-m-d', strtotime('+4 day')).'~'.$a->empID.'" class="hidden" /></td>';
		echo '<td class="tddate"><input type="checkbox" name="assign[]" value="'.date('Y-m-d', strtotime('+5 day')).'~'.$a->empID.'" class="hidden"/></td>';
		echo '<td class="tddate"><input type="checkbox" name="assign[]" value="'.date('Y-m-d', strtotime('+6 day')).'~'.$a->empID.'" class="hidden"/></td>';
		echo '</tr>';
	endforeach;
?>
	</form>
	</table>
<?php } ?>

<script type="text/javascript">
	$(function(){				
		$('.dTable').dataTable({
			// "dom": 'lf<"toolbar">tip',			
			"bPaginate": false,
			"bFilter": false			
		});
		
		
		//"dom": '<"toolbar">frtip'
		// $("div.toolbar").html('<br/><br/><br/><input type="checkbox" id="selectAll"/> Select All<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>Or Select the employees below that need a Schedule Assignment.</i><br/><br/>');
				
		// $('.dTable').on('click','input[name=assign0\\[\\]]',function(){
			// $(this).toggleClass('selected');
			// var checkBoxes = $(this).find("input[name=assign0\\[\\]]");
			// checkBoxes.prop("checked", !checkBoxes.prop("checked"));			
		// });
		
		$('.tddate').click(function(){
			var checkedBox = $(this).find("input[name=assign\\[\\]]");		
			if(checkedBox.prop("checked")) {								
				$(this).removeClass('tddate');
			}
			else {				
				$(this).addClass('tddate');
			}
			
			if($("input[name=staffnames\\[\\]]").is(':checked')){
				alert("sorry  names are checked");	
				$(this).removeClass('nameId');
			}		
			else{		
				$(this).removeClass('sorting_1');
				$(this).addClass('selected');
				var checkBoxes = $(this).find("input[name=assign\\[\\]]");				
				checkBoxes.prop("checked", !checkBoxes.prop("checked"));				
			}
		});
		
		$('.nameId').click(function(){	
			var checkedBox = $(this).find("input[name=staffnames\\[\\]]");		
			if(checkedBox.prop("checked")) {								
				$(this).removeClass('nameId');
			}
			else {				
				$(this).addClass('nameId');
			}				
			if($("input[name=assign\\[\\]]").is(':checked')){
				alert("sorry  dates are checked");			
				$(this).removeClass('tddate');
			}
			else{
				$(this).removeClass('sorting_1');
				$(this).addClass('selected');
				var checkBoxes = $(this).find("input[name=staffnames\\[\\]]");										
				checkBoxes.prop("checked", !checkBoxes.prop("checked"));
			}
		});
		
		
		
		// $('.nameId').click(function(){						
			// $(this).removeClass('sorting_1');
			// $(this).addClass('selected');
			// var checkBoxes = $(this).find("input[name=staffnames\\[\\]]");										
			// checkBoxes.prop("checked", !checkBoxes.prop("checked"));
		// });
		
		// $('.dTable').on('click','tbody tr td',function(){
			// $(this).toggleClass('selected');
			// var checkBoxes = $(this).find("input[name=assign\\[\\]]");
			// checkBoxes.prop("checked", !checkBoxes.prop("checked"));			
		// });
		
		 		
		// $("input[name='includeinactive']").click(function(){
			/* ppage = $(".tab-content.current").attr('id');
			if(ppage!=''){
				$(".tab-content.current").attr('id');
				window.location.href='<?= $this->config->base_url().'timecard/'?>'+ppage+'/';
			} */
			// $('#formSched').submit();		
			
			
		// });
		
		$('#selectAll').click(function(){
			if($(this).is(":checked")==true){
				$('.dTable tbody tr').addClass('selected');
				$("input[name=assign\\[\\]]").prop('checked', true);				
			}else{
				$('.dTable tbody tr').removeClass('selected');
				$("input[name=assign\\[\\]]").prop('checked', false);
			}
		});
		
			
		$('#assignschedbutton').click(function(){						
			 var val = [];
			 var staff_val = [];
			// $(':checkbox:checked').each(function(i){
			  // val[i] = $(this).val();
			// });
			
			$('input[name=assign\\[\\]]:checkbox:checked').each(function(i){
			  val[i] = $(this).val();
			});
			
			$('input[name=staffnames\\[\\]]:checkbox:checked').each(function(i){
			  staff_val[i] = $(this).val();
			});
			
			
			if(val.length > 0){
				var valuehere = "";
				size = val.length;
				for(count = 0; count < size ; count++) {
					valuehere += val[count]+"_";
				}
										
				$.colorbox({width:"900px", height:"600px", iframe:true, href:'<?= $this->config->base_url().'schedules/setstaffschedule/' ?>'+valuehere});						
			}				
			else{
				var valuehere = "";
				size = staff_val.length;
				for(count = 0; count < size ; count++) {
					valuehere += staff_val[count]+"_";
				}
				$.colorbox({width:"900px", height:"600px", iframe:true, href:'<?= $this->config->base_url().'schedules/setstaffsrecurringschedule/' ?>'+valuehere});						
			}
				
			
			
		
			
			// $.post('<?= $this->config->base_url().'schedules/setstaffschedule/' ?>'+valuehere, function(data){				
			// });		
		// setstaffsrecurringchedule
		});
		
		<?php
			if(isset($errortext)){ echo 'alert("'.$errortext.'");'; }
		?>
		
	});
</script>