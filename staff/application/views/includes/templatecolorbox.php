<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Tate Publishing And Enterprises (Philippines), INC</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	
	<link href="<?= $this->config->base_url() ?>css/main.style.css" rel="stylesheet" type="text/css" />
	<link href="<?= $this->config->base_url() ?>css/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />
	<link href="<?= $this->config->base_url() ?>css/jquery.dataTables.css" rel="stylesheet" type="text/css" />';
	<script src="<?= $this->config->base_url() ?>js/jquery.js" type="text/javascript"></script>
	<script src="<?= $this->config->base_url() ?>js/jquery.datetimepicker.js" type="text/javascript"></script>
</head>
<body>
<div id="colorboxcontainer">
	<div id="please_wait" class="hidden" style="text-align:center;">
		<img src="<?= $this->config->base_url() ?>css/images/please_wait.gif"/>
	</div>
	<div id="colorboxcontent">
	<?php		
		if($this->user==false){
			if($content == 'forgotpassword')
				$this->load->view('forgotpassword');
			else
				$this->load->view('includes/login');
		}else if(isset($access) && $access==false)
			echo 'Sorry you do not have access to this page.';		
		else
			$this->load->view($content);				
	?>	
	</div>
</div>
<?php
	if($_SERVER['HTTP_HOST']=='129.3.252.99')
		$this->output->enable_profiler($this->config->item('showProfiler'));

echo '<script src="'.$this->config->base_url().'js/jquery.dataTables.min.js" type="text/javascript"></script>';
?>

<script type="text/javascript">
	$( function () {
		$('.datetimepick').datetimepicker({ format:'F d, Y H:00' });		
		$('.datepick').datetimepicker({ format:'F d, Y', timepicker:false });
		$('.timepick').datetimepicker({ format:'H:i', datepicker:false });
		
		$('.datepick').change(function(){
			$(this).datetimepicker('hide'); 
		});
	});
	
	function displaypleasewait(){
		$('#please_wait').removeClass('hidden');
		$('#colorboxcontent').addClass('hidden');
	}

	$(document).ready(function(){
		$('.datatable').DataTable();
	})
</script>
</body>
</html>