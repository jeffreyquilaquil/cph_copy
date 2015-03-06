<div id="left-wrapper">
<center>
<?php
if($this->user!=false && count($row)>0){
	$fname = '';

	if($content=='index'){
		echo 'Welcome, '.$row->name.'!<br/>';
	}
	$fname = UPLOAD_DIR . $row->username.'/'.$row->username.'.jpg';
	
	if(file_exists($fname)){
		echo '<img src="'.$this->config->base_url().$fname.'" width="200px"/>';
		if($this->user->username == $row->username)
			echo '<div style="width:78%; background-color:#000; opacity:0.6; padding:5px; color:#fff; margin-top:-24px; cursor:pointer;" id="upImage">Change Profile</div>';
	}else{
		echo '<div style="height:200px; width:80%; margin:auto; background-color:#ddd; position:relative;">
				<br/><br/><br/>
				<img src="'.$this->config->base_url().'css/images/logo.png"/>	
			</div>';
			
		if($this->user->username == $row->username)	
			echo '<div style="width:78%; background-color:#000; opacity:0.6; padding:5px; color:#fff; margin-top:-24px; cursor:pointer;" id="upImage">Upload Profile</div>';
	}
	if($this->user->username == $row->username){
		echo '<form id="pfform" action="'.$this->config->base_url().'" method="POST" enctype="multipart/form-data">
				<input type="file" name="pfile" id="pfile" class="hidden"/>
				<input type="hidden" name="submitType" value="uploadProfile"/>
			</form>';
		echo "<script type='text/javascript'>
				$('#upImage').click(function(){
					$('#pfile').trigger('click');
				});
				$('#pfile').change(function(){
					displaypleasewait();
					$('#pfform').submit();				
				});
			</script>";
	}
	
	echo '<ul id="leftMenu">';
		if($this->user->username == $row->username){
			echo '<li><a href="'.$this->config->base_url().'fileleave/" class="iframe">File for a Leave/Offset</a></li>';
			echo '<li><a href="'.$this->config->base_url().'changepassword/" class="iframe">Update My Password</a></li>';
			echo '<li><a href="'.$this->config->base_url().'upsignature/" class="iframe">Update My Signature</a></li>';
			echo '<li><a href="'.$this->config->base_url().'requestcoe/" class="iframe">Request COE</a></li>';
		}
		if($content=='staffinfo' && (count(array_intersect($this->myaccess, array('full', 'hr')))>0 && $this->user->username != $row->username || ($this->user->level>0 || $this->user->is_supervisor==1))){
			echo '<li><a href="'.$this->config->base_url().'issueNTE/'.$row->empID.'/" class="iframe">Issue NTE</a></li>';
			echo '<li><a href="'.$this->config->base_url().'generatecis/'.$row->empID.'/" class="iframe">Generate CIS</a></li>';	
			//echo '<li><a href="'.$this->config->base_url().'schedules/">Schedules</a></li>';				
		}
		if($content!='staffinfo' && (count(array_intersect($this->myaccess, array('full', 'hr')))>0 || $this->user->level>0 || $this->user->is_supervisor==1)){
			echo '<li><a href="'.$this->config->base_url().'generatecode/" class="iframe">Generate Code</a></li>';		
		}
				
		if(in_array('full',$this->myaccess))
			echo '<li><a href="'.$this->config->base_url().'adminsettings/'.$row->empID.'/" class="iframe">Other Settings</a></li>';
	
	echo '</ul>';	
	echo '</center>';
	
	if(count(array_intersect($this->myaccess, array('full', 'hr')))>0 || $this->user->level>0 || $this->user->is_supervisor==1){
?>
	<div class="wrapper-box">
		<b id="sendEmailDiv" class="cpointer">Send Email to Staff</b>
		<input type="text" id="filter" value="" style="width:95%" class="hidden padding5px" placeholder="Employee's Name"/>
		<div class="staffEmails hidden" style="background-color:#fff; color:#000;"></div>
		
	</div>

<?php } ?>

<?php if($content!='index' && $this->user->empID !=0){ ?>
	<div class="wrapper-box">
		<input type="text" class="padding5px" id="searchindex"/><br/>
		<input type="button" value="Search" id="insearch"/>
	</div>
<?php } 
}
?>

</div>

<script>
$(document).ready(function(){
	$('#sendEmailDiv').click(function(){
		$('#sendEmailDiv').removeClass('cpointer');
		$('#filter').removeClass('hidden');
		
		$.post('<?= $this->config->base_url().'getStaffEmails/' ?>', function(d){
			$('.staffEmails').html(d);
		});
		
	});
	
    $("#filter").keyup(function(){ 
        var filter = $(this).val(), count = 0;
		$('.staffEmails').removeClass('hidden');
		
        $(".staffEmails td").each(function(){ 
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).fadeOut();
             } else {				
                $(this).show();
                count++;
            }
        });
    });	
});

function sendEmailOpen(id){ 
	$('#filter').val('');
	$('.staffEmails').addClass('hidden');
	window.parent.jQuery.colorbox({href:"<?= $this->config->base_url ().'sendemail/' ?>"+id+"/fromHR/", iframe:true, width:"990px", height:"600px"});
}
</script>


