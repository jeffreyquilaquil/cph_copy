<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Tate Publishing And Enterprises (Philippines), INC</title>
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	<link rel="shortcut icon" href="<?= $this->config->item('career_url') ?>/img/favicon.ico" type="image/x-icon">
	
<?php
	//CSS
	echo '<link href="'.$this->config->base_url().'css/jquery.dataTables.css" rel="stylesheet" type="text/css" />';
	echo '<link href="'.$this->config->base_url().'css/jquery.datetimepicker.css" rel="stylesheet" type="text/css" />';
	echo '<link href="'.$this->config->base_url().'css/colorbox.css" rel="stylesheet" type="text/css" />';
	echo '<link href="'.$this->config->base_url().'css/main.style.css" rel="stylesheet" type="text/css" />';
	
	if(isset($showtemplatefull))
		echo '<link href="'.$this->config->base_url().'css/templatefull.style.css" rel="stylesheet" type="text/css" />';
	
	//SCRIPTS
	echo '<script src="'.$this->config->base_url().'js/jquery.js" type="text/javascript"></script>';
?>	
	<script type="text/javascript">
		BASEURL = '<?= $this->config->base_url() ?>';
		CAREERURI = '<?= $this->config->item('career_uri') ?>';
	</script>
</head>
<body>
<div id="container" style="position:relative;">
	<?php
		if(!isset($content)) $content = 'index';
		
		$hdata['cnotif'] = 0;
		if($this->user!=false){
			$notifQ = $this->dbmodel->getQueryResults('staffMyNotif', 'notifID', 'empID_fk="'.$this->user->empID.'" AND isNotif=1'); 
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
							if(isset($timecardpage)) $this->load->view('includes/header_timecard');
							
							$this->load->view($content);
					echo '</div>';					
				}else{
					echo '<div id="contentfull">';
						if(isset($timecardpage)) $this->load->view('includes/header_timecard');
						
						$this->load->view($content);
					echo '</div>';
				}
			}				
		?>		
	</div>
	<footer>
		<div class="tacenter" style="padding:20px 0 5px;">
	<?php
		if($this->user!=false){
			if($this->user->dept=='IT')
				echo '<a href="'.$this->config->base_url().'includes/documentation.pdf" target="_blank">Documentation by Ludivina Mariñas 2016</a>';
			else
				echo '<a href="#">by Ludivina Mariñas 2016</a>';
		}
	?>
	</div>
	</footer>	
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
	
	if($_SERVER['HTTP_HOST']=='129.3.252.99')
		$this->output->enable_profiler($this->config->item('showProfiler'));


//LOAD ADDITIONAL SCRIPTS
//echo '<script src="'.$this->config->base_url().'js/jquery.dataTables.js" type="text/javascript"></script>';
echo '<script src="'.$this->config->base_url().'js/jquery.dataTables.min.js" type="text/javascript"></script>';
echo '<script src="'.$this->config->base_url().'js/jquery.datetimepicker.js" type="text/javascript"></script>';
echo '<script src="'.$this->config->base_url().'js/jquery.colorbox.js" type="text/javascript"></script>';
?>
<script type="text/javascript">
	$(function(){
		$(".iframe").colorbox({iframe:true, width:"990px", height:"600px"});
		$(".iframesmall").colorbox({iframe:true, width:"40%", height:"60%"});
		$(".inline").colorbox({inline:true, width:"40%", height:"60%"});
		$('.datatable').dataTable();		
		
		$('.datetimepick').datetimepicker({ format:'F d, Y H:00' });
		$('.datepick').datetimepicker({ format:'F d, Y', timepicker:false });
		$('.timepick').datetimepicker({ format:'H:i', datepicker:false });
				
		$('.datepick').change(function(){
			$(this).datetimepicker('hide'); 
		});
		
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