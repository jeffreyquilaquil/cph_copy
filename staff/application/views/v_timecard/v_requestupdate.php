<?php
	echo '<h3>Request Changes for '.date('l, F d, Y', strtotime($today)).'</h3><hr/>';
	if(isset($requested)){
		echo '<p class="errortext">Request sent.</p>';
?>
	<script>
	$(function(){
		parent.$("html").click(function(){
			parent.location.reload();
		});
	});
	</script>
<?php
	}else{
		echo '<form action="" method="POST" enctype="multipart/form-data" onSubmit="disableSubmitBtn()">';
			echo '<b>Message to HR</b><br/>';
			echo $this->textM->formfield('textarea', 'message', '', 'forminput', 'Type message here...', 'rows=10 required');
			echo '<br/><br/>';
			echo '<b>Upload Supporting Documents</b><br/>';
			echo '* '.$this->textM->formfield('file', 'docs[]', '', '', '', 'required').'<br/>';
			echo '&nbsp;&nbsp;&nbsp;'.$this->textM->formfield('file', 'docs[]').'<br/>';
			echo '&nbsp;&nbsp;&nbsp;'.$this->textM->formfield('file', 'docs[]').'<br/>';
			echo '<br/>';
			echo '<div class="taright">'.$this->textM->formfield('submit', '', 'Submit Request', 'btnclass btngreen').'</div>';
			echo $this->textM->formfield('hidden', 'logDate', $today);
		echo '</form>';
	}	
?>

<script type="text/javascript">
	function disableSubmitBtn(){
		displaypleasewait();
	}
</script>