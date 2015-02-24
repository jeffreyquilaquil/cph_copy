<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Tate Publishing And Enterprises (Philippines), INC</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	
	<link href="<?= $this->config->base_url() ?>css/main.style.css" rel="stylesheet" type="text/css" />
	<link href="<?= $this->config->base_url() ?>css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<link href="<?= $this->config->base_url() ?>css/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />
	<link href="<?= $this->config->base_url() ?>css/colorbox.css" rel="stylesheet" type="text/css" />
	
	<script src="<?= $this->config->base_url() ?>js/jquery.js" type="text/javascript"></script>
	<script src="<?= $this->config->base_url() ?>js/jquery.dataTables.js" type="text/javascript"></script>
	<script src="<?= $this->config->base_url() ?>js/jquery.datetimepicker.js" type="text/javascript"></script>
	<script src="<?= $this->config->base_url() ?>js/jquery.colorbox.js" type="text/javascript"></script>
</head>
<body>
<div id="container" style="position:relative;">
	<?php
		if(!isset($content)) $content = 'index';
		
		$hdata['cnotif'] = 0;
		if($this->user!=false){
			$notifQ = $this->staffM->getQueryResults('staffMyNotif', 'notifID', 'isNotif=1 AND empID_fk='.$this->user->empID); 
			$hdata['cnotif'] = count($notifQ);
		}
		
		$this->load->view('includes/header', $hdata);
			
	?>
	<div id="wrapper">
		
		<?php
			if($this->user==false){
				if($content == 'forgotpassword')
					$this->load->view('forgotpassword');
				else
					$this->load->view('includes/login');
			}else if(isset($access) && $access==false){
				echo '<div id="contentfull">Sorry you do not have access to this page.</div>';
			}else{
				if($content=='index'){
					$this->load->view('includes/leftcontent');
					$this->load->view($content);
				}else if( isset($column) && $column=='withLeft' ){	
					$this->load->view('includes/leftcontent');
					echo '<div id="content2right">';
							$this->load->view($content);
					echo '</div>';					
				}else{
					echo '<div id="contentfull">';
						$this->load->view($content);
					echo '</div>';
				}
			}				
		?>		
	</div>
	<footer></footer>	
</div>
<?php
	if($this->session->userdata('popupnotification') && $content=='index'){
		if($hdata['cnotif']>0){
			echo '<script type="text/javascript">';
			echo '$(function(){ window.parent.jQuery.colorbox({href:"'.$this->config->base_url().'detailsnotifications/", iframe:true, width:"990px", height:"600px"}); });';
			echo '</script>';
		}
		$this->session->unset_userdata('popupnotification');
	}
?>
<script type="text/javascript">
	$(function(){
		$(".iframe").colorbox({iframe:true, width:"990px", height:"600px" });
		
		$('.datetimepick').datetimepicker({ format:'F d, Y H:00' });
		$('.datepick').datetimepicker({ format:'F d, Y', timepicker:false });
		$('.timepick').datetimepicker({ format:'H:i', datepicker:false });
		
		$('#insearch').click(function(){
			window.open('http://employee.tatepublishing.net/?s='+$('#searchindex').val(),'_blank');
			$('#searchindex').val('');
		});
		
		$('ul.tabs li').click(function(){
			var tab_id = $(this).attr('data-tab');

			$('ul.tabs li').removeClass('current');
			$('.tab-content').removeClass('current');

			$(this).addClass('current');
			$("#"+tab_id).addClass('current');
		});			
	});	

	function displaypleasewait(){
		window.parent.jQuery.colorbox({href:"<?= $this->config->base_url ()?>css/images/please_wait.gif", iframe:true, width:"650px", height:"400px"});
	}
</script>
</body>
</html>