<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Tate Publishing And Enterprises (Philippines), INC</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	
	<link href="<?= $this->config->base_url() ?>css/main.style.css" rel="stylesheet" type="text/css" />
	<script src="<?= $this->config->base_url() ?>js/jquery.js" type="text/javascript"></script>
</head>
<body class="templatenone">
	<?php $this->load->view($content); ?>
</body>
<script type="text/javascript">
	$(function(){
		$('a').click(function(e){
			e.preventDefault();
			if($(this).hasClass('iframe')){
				parent.$.colorbox({href:$(this).attr('href'), iframe:true, open:true, width:"990px", height:"600px"});
			}else{
				parent.window.location.href=$(this).attr('href');
			}
			
		});
	});
</script>
</html>