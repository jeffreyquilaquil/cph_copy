<div style="float:right;">
	<button class="btnclass" onClick="expand();">- Expand All</button>
	<button class="btnclass" onClick="collapse();">- Collapse All</button>
</div>
<h2>Tate Cebu Organizational Chart</h2>
<hr/>

<div class="orgchart">
<?php
	echo '<ul>';
	foreach($upper AS $u){ 
		echo $this->txtM->getEmps($all, $u->supervisor, 0);
	}
	echo '</ul>';
	
?>
</div>
<script type="text/javascript">
	$(function(){
		collapse();				
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
	
	function collapse(){
		$('.uldisp').addClass('hidden');
		$('.tpointer').text('+');
		
		$('li.li_0 .tpointer').text('-');
		$('li.li_1 .tpointer').text('-');
	}
	
	function expand(){
		$('.uldisp').removeClass('hidden');
		$('.tpointer').text('-');
	}
</script>
