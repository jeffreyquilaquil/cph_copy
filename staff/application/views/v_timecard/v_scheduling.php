<div style="float:right;">
	<span style="font-size:14px; font-weight:bold;">Action:</span>&nbsp;&nbsp;&nbsp;
	<select class="padding5px" id="actionselect">
		<option value=""></option>
		<option value="customizedSched">Customize Schedules</option>
	</select>
	<textarea class="hidden" id="forcustomizedsched"></textarea>
</div>
<table class="tableInfo schedTable" border=1 width="98%">
	<thead>
		<tr class="trhead tacenter">
			<td>Names</td>
		<?php
			echo '<td width="9%">SUNDAY<br/>'.date('d M', strtotime($sunday)).'</td>';
			echo '<td width="9%">MONDAY<br/>'.date('d M', strtotime($monday)).'</td>';
			echo '<td width="9%">TUESDAY<br/>'.date('d M', strtotime($tuesday)).'</td>';
			echo '<td width="9%">WEDNESDAY<br/>'.date('d M', strtotime($wednesday)).'</td>';
			echo '<td width="9%">THURSDAY<br/>'.date('d M', strtotime($thursday)).'</td>';
			echo '<td width="9%">FRIDAY<br/>'.date('d M', strtotime($friday)).'</td>';
			echo '<td width="9%">SATURDAY<br/>'.date('d M', strtotime($saturday)).'</td>';
		?>
			<td>
				<button onClick="window.location.href='<?= $_SERVER['REDIRECT_URL'].'?startweek='.$prev ?>'"><< Prev</button><br/>
				<button onClick="window.location.href='<?= $_SERVER['REDIRECT_URL'].'?startweek='.$next ?>'">Next >></button>
			</td>
		</tr>
	</thead>
<?php
	$scurdate = strtotime($currentDate);
	foreach($allStaffs AS $all){
		echo '<tr>
			<td><a href="'.$this->config->base_url().'timecard/'.$all->empID.'/calendar/" target="_blank">'.$all->lname.', '.$all->fname.'</a></td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$sunday.'\', \''.$all->name.'\', this)">'.((isset($schedData[$all->empID][$sunday]))?$schedData[$all->empID][$sunday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$monday.'\', \''.$all->name.'\', this)">'.((isset($schedData[$all->empID][$monday]))?$schedData[$all->empID][$monday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$tuesday.'\', \''.$all->name.'\', this)">'.((isset($schedData[$all->empID][$tuesday]))?$schedData[$all->empID][$tuesday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$wednesday.'\', \''.$all->name.'\', this)">'.((isset($schedData[$all->empID][$wednesday]))?$schedData[$all->empID][$wednesday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$thursday.'\', \''.$all->name.'\', this)">'.((isset($schedData[$all->empID][$thursday]))?$schedData[$all->empID][$thursday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$friday.'\', \''.$all->name.'\', this)">'.((isset($schedData[$all->empID][$friday]))?$schedData[$all->empID][$friday]:'').'</td>
			<td align="center" onClick="kailangan('.$all->empID.', \''.$saturday.'\', \''.$all->name.'\', this)">'.((isset($schedData[$all->empID][$saturday]))?$schedData[$all->empID][$saturday]:'').'</td>
			<td align="center"><a class="iframe" href="'.$this->config->base_url().'schedules/setschedule/'.$all->empID.'/"><button>Schedule</button></a></td>
		</tr>';
		
		/* echo '<tr>
			<td><a href="'.$this->config->base_url().'timecard/'.$all->empID.'/calendar/" target="_blank">'.$all->lname.', '.$all->fname.'</a></td>
			<td align="center" '.(($scurdate<=strtotime($sunday))?'onClick="kailangan('.$all->empID.', \''.$sunday.'\', \''.$all->name.'\', this)':'bgcolor="#ddd" onClick="alert(\'Editing schedule of previous date is invalid.\')"').' ">'.((isset($schedData[$all->empID][$sunday]))?$schedData[$all->empID][$sunday]:'').'</td>
			<td align="center" '.(($scurdate<=strtotime($monday))?'onClick="kailangan('.$all->empID.', \''.$monday.'\', \''.$all->name.'\', this)':'bgcolor="#ddd" onClick="alert(\'Editing schedule of previous date is invalid.\')"').' ">'.((isset($schedData[$all->empID][$monday]))?$schedData[$all->empID][$monday]:'').'</td>
			<td align="center" '.(($scurdate<=strtotime($tuesday))?'onClick="kailangan('.$all->empID.', \''.$tuesday.'\', \''.$all->name.'\', this)':'bgcolor="#ddd" onClick="alert(\'Editing schedule of previous date is invalid.\')"').' ">'.((isset($schedData[$all->empID][$tuesday]))?$schedData[$all->empID][$tuesday]:'').'</td>
			<td align="center" '.(($scurdate<=strtotime($wednesday))?'onClick="kailangan('.$all->empID.', \''.$wednesday.'\', \''.$all->name.'\', this)':'bgcolor="#ddd" onClick="alert(\'Editing schedule of previous date is invalid.\')"').' ">'.((isset($schedData[$all->empID][$wednesday]))?$schedData[$all->empID][$wednesday]:'').'</td>
			<td align="center" '.(($scurdate<=strtotime($thursday))?'onClick="kailangan('.$all->empID.', \''.$thursday.'\', \''.$all->name.'\', this)':'bgcolor="#ddd" onClick="alert(\'Editing schedule of previous date is invalid.\')"').' ">'.((isset($schedData[$all->empID][$thursday]))?$schedData[$all->empID][$thursday]:'').'</td>
			<td align="center" '.(($scurdate<=strtotime($friday))?'onClick="kailangan('.$all->empID.', \''.$friday.'\', \''.$all->name.'\', this)':'bgcolor="#ddd" onClick="alert(\'Editing schedule of previous date is invalid.\')"').' ">'.((isset($schedData[$all->empID][$friday]))?$schedData[$all->empID][$friday]:'').'</td>
			<td align="center" '.(($scurdate<=strtotime($saturday))?'onClick="kailangan('.$all->empID.', \''.$saturday.'\', \''.$all->name.'\', this)':'bgcolor="#ddd" onClick="alert(\'Editing schedule of previous date is invalid.\')"').' ">'.((isset($schedData[$all->empID][$saturday]))?$schedData[$all->empID][$saturday]:'').'</td>
			<td align="center"><a class="iframe" href="'.$this->config->base_url().'schedules/setschedule/'.$all->empID.'/"><button>Schedule</button></a></td>
		</tr>'; */
	}
?>	
</table>

<script type="text/javascript">
	$(function(){
		$(".iframe").colorbox({iframe:true, width:"990px", height:"600px" });
		$('.schedTable').dataTable({
			"bSort" : false
		});
		
		$('#actionselect').change(function(){
			if($('#forcustomizedsched').val()==''){
				$(this).val('');
				alert('Please select staff to schedule.');
			}else{
				v = $(this).val();
				if(v=='customizedSched'){
					$(this).val('');	
					$.colorbox({iframe:true, width:"990px", height:"600px", href:"<?= $this->config->base_url() ?>schedules/customizebystaffs/" });
				}
			}
		});
	});

	function kailangan(id, dateToday, nm, kooo){
		txt = $('#forcustomizedsched').val();
		ikaw = dateToday+'|'+id+'|'+nm+'|'+$(kooo).text()+'==||==';
		if($(kooo).hasClass('bggray')==false){
			$(kooo).addClass('bggray');
			$('#forcustomizedsched').val(txt+ikaw);
		}else{
			$(kooo).removeClass('bggray');			
			$('#forcustomizedsched').val(txt.replace(ikaw,''));
		}			
	}
</script>