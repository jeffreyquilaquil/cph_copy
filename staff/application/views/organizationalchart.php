<h2>Tate Cebu Organizational Chart</h2>
<div class="orgchart">
<?php
	echo '<ul>';
	foreach($upper AS $u){ //echo $u->supervisor;
		//echo '<li><u>'.$u->title.'</u>, <i>'.$u->name.'</i></li>';
		echo $this->txtM->getEmps($all, $u->supervisor, 0);
	}
	echo '</ul>';
	
?>
</div>
<script type="text/javascript">
	$(function(){
		$('ul.ul_0').removeClass('hidden');
		$('ul.ul_1').removeClass('hidden');
		$('ul.ul_2').removeClass('hidden');
		
		$('ul.ul_0 li.li_0 .tpointer').html('-');
		$('ul.ul_1 li.li_1 .tpointer').html('-');
	});
	
	function toggleDisplay(id){
		ptext = $('#pointer_'+id).text()
		if(ptext == '-'){
			$('.emp_'+id).addClass('hidden');
			$('#pointer_'+id).text('+');
		}else{
			$('.emp_'+id).removeClass('hidden');
			$('#pointer_'+id).text('-');
		}
	}
</script>
