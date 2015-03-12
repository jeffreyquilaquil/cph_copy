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
			echo '<li><a href="http://employee.tatepublishing.net/hr/code-of-conduct-and-policy-manual-2015-faqs/" target="_blank">Tate Code of Conduct</a></li>';
			echo '<li><a href="'.$this->config->base_url().'fileleave/" class="iframe">File for a Leave/Offset</a></li>';
			echo '<li><a href="'.$this->config->base_url().'changepassword/" class="iframe">Update My Password</a></li>';
			echo '<li><a href="'.$this->config->base_url().'upsignature/" class="iframe">Update My Signature</a></li>';
			echo '<li><a href="'.$this->config->base_url().'requestcoe/" class="iframe">Request COE</a></li>';
		}
		if($content=='staffinfo' && ($this->access->accessFullHR==true && $this->user->username != $row->username || $this->user->level>0)){
			echo '<li><a href="'.$this->config->base_url().'issueNTE/'.$row->empID.'/" class="iframe">Issue NTE</a></li>';
			echo '<li><a href="'.$this->config->base_url().'generatecis/'.$row->empID.'/" class="iframe">Generate CIS</a></li>';
			//echo '<li><a href="'.$this->config->base_url().'schedules/">Schedules</a></li>';				
		}
		if($content!='staffinfo' && ($this->access->accessFullHR==true || $this->user->level>0)){
			echo '<li><a href="'.$this->config->base_url().'generatecode/" class="iframe">Generate Code</a></li>';		
		}
		if($this->access->accessFullHR==true || $this->user->level>0){
			echo '<li><a href="'.$this->config->item('career_url').'/jobrequisition.php" target="_blank">Request for Job Requisition</a></li>';	
		}
				
		if($this->access->accessFull==true)
			echo '<li><a href="'.$this->config->base_url().'adminsettings/'.$row->empID.'/" class="iframe">Other Settings</a></li>';
	
	echo '</ul>';	
	echo '</center>';
	
?>
	<div class="wrapper-box">
		<b id="sendEmailDiv" class="cpointer">Send Email to Staff</b>
		<input type="text" id="filter" value="" style="width:95%" class="hidden padding5px" placeholder="Employee's Name"/>
		<div class="staffEmails hidden" style="background-color:#fff; color:#000;"></div>		
	</div>

	<div class="wrapper-box">
		<b>Search for a Tate Employee</b>
		<input type="text" id="filter2" value="" style="width:95%" class="padding5px" placeholder="Employee's Name"/>
		<div class="allStaffs hidden" style="background-color:#fff; color:#000;"></div>		
	</div>
	
	<?php if($content!='index' && $this->user->empID !=0){ ?>
	<div class="wrapper-box">
		<input type="text" class="padding5px" id="searchindex" style="width:95%"/><br/>
		<input type="button" value="Search" id="insearch"/>
	</div>
<?php } ?>

<?php	
}
?>

</div>

<script>
$(document).ready(function(){
	$.post('<?= $this->config->base_url().'getStaffEmails/' ?>', function(d){
		$('.staffEmails').html(d);
	});
	$.post('<?= $this->config->base_url().'getAllStaffsForSearch/' ?>', function(d){
		$('.allStaffs').html(d);
	});
	
	$('#sendEmailDiv').click(function(){
		$('#sendEmailDiv').removeClass('cpointer');
		$('#filter').removeClass('hidden');				
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
	
	$("#filter2").keyup(function(){ 
        var filter = $(this).val(), count = 0;
		$('.allStaffs').removeClass('hidden');
		
        $(".allStaffs td").each(function(){ 
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
	window.parent.jQuery.colorbox({href:"<?= $this->config->base_url ().'sendemail/' ?>"+id+"/", iframe:true, width:"990px", height:"600px"});
}

function visitStaffPage(username){
	$('.allStaffs').addClass('hidden');	
	$('#filter2').val('');	
	window.open('<?= $this->config->base_url().'staffinfo/'?>'+username+'/', '_blank');
}
</script>


