<h2><?= $coach->fname.' '.$coach->lname ?> Coach to:</h2>
<hr/>
<?php
	if(count($coachedStaffs)>0){
		echo '<ul>';
			foreach($coachedStaffs AS $c){
				echo '<li>'.$c->fname.' '.$c->lname.'&nbsp;&nbsp;&nbsp;&nbsp; <a href="javascript:void(0);" onClick="removeMeFrom(this, '.$c->empID.')">Remove</a></li>';
			}
		echo '</ul>';
	}
	echo '<br/><b>Please input and select employee name from dropdown to add '.$coach->fname.' as his/her coach.</b><br/>';
	echo $this->textM->formfield('text', '', '', 'forminput', 'Type employee name then select', 'id="filter"');	
?>

<table class="tableInfo hidden" id="emps">
<?php
	foreach($staffs AS $s){
		echo '<tr><td class="cpointer" onClick="addCoach('.$s->empID.', \''.$s->fname.' '.$s->lname.'\')">'.$s->fname.' '.$s->lname.'</td></tr>';
	}

?>
</table>
<?php
	echo '<ul id="ulcoach"></ul>';
	
?>
<form action="" method="POST" onSubmit="return validateForm();">
<?php
	echo $this->textM->formfield('textarea', 'ids', '', 'hidden');	
	echo $this->textM->formfield('submit', 'submitType', 'Submit', 'btnclass btngreen');	
?>
</form>


<script type="text/javascript">
	$(function(){
		$("#filter").keyup(function(){ 
			var filter = $(this).val(), count = 0;
			$('#emps').removeClass('hidden');

			$("#emps td").each(function(){ 
				if ($(this).text().search(new RegExp(filter, "i")) < 0) {
					$(this).fadeOut();				
				} else {				
					$(this).show();
					count++;
				}
			});
		});
	});
	
	function addCoach(id, name){
		$('#filter').val('');
		$('#emps').addClass('hidden');
		v = $('textarea[name="ids"]').val();
		$('textarea[name="ids"]').val(v+'++'+id+'++');
		
		$('#ulcoach').append('<li>'+name+'&nbsp;&nbsp;&nbsp;&nbsp; <a href="javascript:void(0);" onClick="removeMe(this, '+id+');">Remove</a></li>');
	}
	
	function removeMe(t, id){
		$(t).parent('li').addClass('hidden');
		v = $('textarea[name="ids"]').val();
		cc = v.replace('++'+id+'++', '');
		$('textarea[name="ids"]').val(cc);
	}
	
	function validateForm(){
		if($('textarea[name="ids"]').val()==''){
			alert('Please select employee.');
			return false;	
		}
		else
			return true;
	}
	
	function removeMeFrom(t, id){
		$(t).parent('li').fadeOut();			
		$.post('<?= $this->config->item('career_uri') ?>', {id:id, submitType:'remove'},
		function(){
			location.reload();
		})
	}
</script>
