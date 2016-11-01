<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $this->config->item('app_name'); ?></title>
	<link rel="stylesheet" href="<?php echo $this->config->base_url(); ?>css/bootstrap.css">
	<style>
		.choice {list-style-type: none;}
		section{padding:10px;font-family: Arial Helvetica sans-serif; background-color:#fff; color:#000; font-size: 16px;}
		pre{text-align:left;}
	</style>
</head>
<body>
	<?php if( !$hide_nav ): ?>
	<div class="navbar navbar-default navbar-fixed-top">
		<div class="container">
			<a href="#" class="navbar-brand">Tate Publishing and Enterprises (Philippines) Inc.</a>		
			
		</div>
	</div>
	<?php endif; ?>
	<div class="container" style="margin-top:70px;">
		
		<section class="container">
			<?php $this->load->view( $content ); ?>

			
		</section>
		<footer>
			
		</footer>

	</div>
		
</body>
<script src="<?php echo $this->config->base_url(); ?>js/jquery.js"></script>
<script src="<?php echo $this->config->base_url(); ?>js/bootstrap.min.js"></script>
</html>