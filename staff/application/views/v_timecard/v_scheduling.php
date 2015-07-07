<?php 
	$this->load->view('includes/header_timecard'); 
		
	
?>
<br/>

<div style="float:right;">
	<span style="font-size:14px; font-weight:bold;">Action:</span>&nbsp;&nbsp;&nbsp;
	<select class="padding5px" id="actionselect">
		<option value=""></option>
		<option value="customizedSched">Customize Schedules</option>
	</select>
	<textarea class="hidden" id="forcustomizedsched" rows=10 cols=100></textarea>
</div>
<table class="tableInfo schedTable" border=1 width="98%">
	<thead>
		<tr class="trhead tacenter">
			<td>Names</td>
		<?php
			echo '<td width="9%">SUNDAY<br/>'.$sunday.'</td>';
			echo '<td width="9%">MONDAY<br/>'.$monday.'</td>';
			echo '<td width="9%">TUESDAY<br/>'.$tuesday.'</td>';
			echo '<td width="9%">WEDNESDAY<br/>'.$wednesday.'</td>';
			echo '<td width="9%">THURSDAY<br/>'.$thursday.'</td>';
			echo '<td width="9%">FRIDAY<br/>'.$friday.'</td>';
			echo '<td width="9%">SATURDAY<br/>'.$saturday.'</td>';
		?>
			<td><br/></td>
		</tr>
	</thead>
<?php
	foreach($allStaffs AS $all){
		echo '<tr>
			<td><a href="'.$this->config->base_url().'timecard/'.$all->empID.'/schedules/" target="_blank">'.$all->lname.', '.$all->fname.'</a></td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$sunday.'\', this)">'.((isset($schedData[$all->empID][$sunday]))?$schedData[$all->empID][$sunday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$monday.'\', this)">'.((isset($schedData[$all->empID][$monday]))?$schedData[$all->empID][$monday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$tuesday.'\', this)">'.((isset($schedData[$all->empID][$tuesday]))?$schedData[$all->empID][$tuesday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$wednesday.'\', this)">'.((isset($schedData[$all->empID][$wednesday]))?$schedData[$all->empID][$wednesday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$thursday.'\', this)">'.((isset($schedData[$all->empID][$thursday]))?$schedData[$all->empID][$thursday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$friday.'\', this)">'.((isset($schedData[$all->empID][$friday]))?$schedData[$all->empID][$friday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$saturday.'\', this)">'.((isset($schedData[$all->empID][$saturday]))?$schedData[$all->empID][$saturday]:'').'</td>
			<td align="center"><button class="btnclass iframe" href="'.$this->config->base_url().'schedules/setschedule/'.$all->empID.'/">Set Schedule</button></td>
		</tr>';
	}
?>	
</table>

<script type="text/javascript">
	$(function(){
		$('.schedTable').dataTable({
			"bSort" : false
		});
		
		$('#actionselect').change(function(){
			v = $(this).val();
			if(v=='customizedSched'){
				$.colorbox({iframe:true, width:"990px", height:"600px", href:"<?= $this->config->base_url() ?>schedules/customizebyday/320/2015-07-04/" });
			}
		});
	});

	function kailangan(id, dateToday, kooo){
		txt = $('#forcustomizedsched').val();
		ikaw = id+'|'+dateToday+'|'+$(kooo).text()+'==||==';
		if($(kooo).hasClass('bggray')==false){
			$(kooo).addClass('bggray');
			$('#forcustomizedsched').val(txt+ikaw);
		}else{
			$(kooo).removeClass('bggray');			
			$('#forcustomizedsched').val(txt.replace(ikaw,''));
		}			
	}
</script>