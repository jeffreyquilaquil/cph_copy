<div id="left-wrapper">
<center>
<?php	
if($this->user!=false && $this->uri->segment(1)=='schedules'){
	if(isset($_GET['page'])) $page = $_GET['page'];
	else $page = 'customtime';
	echo '<ul id="leftMenu" style="margin:0px;">';
		echo '<li><a id="customtime" class="schedLI '.(($page=='customtime')?'current':'').'" href="#" onClick="schedChange(this)">Custom Time</a></li>';
		echo '<li><a id="customsched" class="schedLI '.(($page=='customsched')?'current':'').'" href="#" onClick="schedChange(this)">Custom Schedules</a></li>';
		//echo '<li><a id="schedSettings" class="schedLI '.(($page=='schedSettings')?'current':'').'" href="#" onClick="schedChange(this)">Time Settings</a></li>';
		echo '<li><a id="holeventsched" class="schedLI '.(($page=='holeventsched')?'current':'').'" href="#" onClick="schedChange(this)">Holiday/Event Schedules</a></li>';
	echo '</ul>';
?>
	<script type="text/javascript">
		function schedChange(jeena){
			$('.schedLI').removeClass('current');
			$(jeena).addClass('current');
			//for contents
			$('.schedDiv').addClass('hidden');
			$('#'+$(jeena).attr('id')+'DIV').removeClass('hidden');
		}
	</script>
<?php
}elseif( isset($tpage) && $tpage=='manage13thmonth' ){
	echo '<ul id="leftMenu" style="margin:0px;">';
		if($this->access->accessFullFinance==true){
			if(!isset($pageyereport)) $pageyereport = $this->uri->segment(2);
			echo '<li><a href="'.$this->config->base_url().'timecard/manage13thmonth/" class="'.(($pageyereport=='manage13thmonth')?'current':'').'">Manage 
			13th Month</a></li>';
			echo '<li><a href="'.$this->config->base_url().'timecard/alphalist/?which=start" class="'.(($pageyereport=='alphalist')?'current':'').'">Generate Alphalist</a></li>';
			echo '<li><a href="'.$this->config->base_url().'timecard/manageTaxSummary/?which=start" class="'.(($pageyereport=='manageTaxSummary')?'current':'').'">Tax Summary</a></li>';
			echo '<li><a href="'.$this->config->base_url().'timecard/birmanagement/?year=2016" class="'.(($pageyereport=='birmanagement')?'current':'').'">BIR 2316 Management</a></li>';

		}			
	echo '</ul>';
}else if(isset($tpage) && $tpage=='managepayroll'){ //MANAGE TIMECARD LEFT CONTENT
	echo '<ul id="leftMenu" style="margin:0px;">';
		if($this->access->accessFullFinance==true){
			if(!isset($pagepayroll)) $pagepayroll = $this->uri->segment(2);
			
			echo '<li><a href="'.$this->config->base_url().'timecard/managepayroll/" class="'.(($pagepayroll=='managepayroll')?'current':'').'">Manage Payrolls</a></li>';
			echo '<li><a href="'.$this->config->base_url().'timecard/managepayroll/previouspayroll/" class="'.(($pagepayroll=='previouspayroll')?'current':'').'">Previous Payrolls</a></li>';
			echo '<li><a href="'.$this->config->base_url().'timecard/managepayroll/payrollitems/" class="'.(($pagepayroll=='payrollitems')?'current':'').'">Payroll Items</a></li>';
			echo '<li><a href="'.$this->config->base_url().'timecard/managepayroll/payrollsettings/" class="'.(($pagepayroll=='payrollsettings')?'current':'').'">Payroll Settings</a></li>';
		}			
	echo '</ul>';
} //for evaluations
else if( isset($tpage) AND $tpage == 'evaluations' ){
	$sidepage = $this->uri->segment(2);
	echo '<ul id="leftMenu" style="margin:0px;">';
	echo '<li><a href="'. $this->config->base_url().'evaluations/" class="'.(empty($sidepage)?'current':'').'">Management</a></li>';
	echo '<li><a href="'. $this->config->base_url().'evaluations/questionnaires/technicalQuestions" class="'.(($sidepage=='questionnaires' && $this->uri->segment(3)=='technicalQuestions')?'current':'').'">Technical Questionnaires</a></li>';
	echo '<li><a href="'.$this->config->base_url().'evaluations/questionnaires/behavioralQuestions"  class="'.(($sidepage=='questionnaires' && $this->uri->segment(3)=='behavioralQuestions')?'current':'').'">Behavioral Questionnaires</a></li>';
	if($this->access->accessFullHR == true){
		$reviewCount = $this->databasemodel->getSingleField('evalQuestions','count(question_id)', 'hrStatus = 0');
		echo '<li><a href="'.$this->config->base_url().'evaluations/review">Review Questions ['.$reviewCount.']</a></li>';
	}
	echo '</ul>';


}else if($this->user!=false && isset($row) && count($row)>0){
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
    		echo '<li><a href="'.$this->config->base_url().'hr_cs/" class="iframe">Ask a Question</a></li>';
			//echo '<li><a href="'.$this->config->base_url().'sendValentinesGreetings/" class="iframe">Send Personal Greetings</a></li>';
			echo '<li><a href="http://employee.tatepublishing.net/hr/code-of-conduct-and-policy-manual-2015-faqs/" target="_blank">Tate Code of Conduct</a></li>';
			echo '<li><a href="'.$this->config->base_url().'fileleave/" class="iframe">File for a Leave/Offset</a></li>';
			
			/*if(isset($row->empStatus) && $row->empStatus=='probationary'){
				echo '<li><a href="'.$this->config->base_url().'evaluationself/" class="iframe">Submit Self-Evaluation</a></li>';
			}*/
			//echo '<li><a href="'.$this->config->base_url().'changepassword/" class="iframe">Update My Password</a></li>';
			//echo '<li><a href="'.$this->config->base_url().'upsignature/" class="iframe">Update My Signature</a></li>';
			//echo '<li><a href="'.$this->config->base_url().'requestcoe/" class="iframe">Request for Certificate of Employment</a></li>';
			//echo '<li><a href="'.$this->config->base_url().'medrequest/" class="iframe">Request for Medicine Reimbursement</a></li>';
			
			
	
			//echo '<li><a href="'.$this->config->base_url().'changepassword/" class="iframe">Update My Password</a></li>';
			//echo '<li><a href="'.$this->config->base_url().'upsignature/" class="iframe">Update My Signature</a></li>';
			//echo '<li><a href="'.$this->config->base_url().'requestcoe/" class="iframe">Request for Certificate of Employment</a></li>';
			//echo '<li><a href="'.$this->config->base_url().'medrequest/" class="iframe">Request for Medicine Reimbursement</a></li>';
			echo '<li><a href="'.$this->config->base_url().'hdmf/" class="iframe">File a PAG-IBIG loan</a></li>';
		}
		if(($content=='staffinfo' || $this->uri->segment(1)=='timecard') && $this->user->username != $row->username && ($this->access->accessFullHR==true || $this->commonM->checkStaffUnderMe($row->username))){
			echo '<li><a href="'.$this->config->base_url().'staffinfo/'.$row->username.'/" '.(($content=='staffinfo')?'class="current"':'').'>'.trim($row->fname).'\'s Info</a></li>';
			echo '<li><a href="'.$this->config->base_url().'timecard/'.$row->empID.'/calendar/" '.(($this->uri->segment(1)=='timecard')?'class="current"':'').'>Timecard and Payroll</a></li>';
			echo '<li><a href="'.$this->config->base_url().'issueNTE/'.$row->empID.'/" class="iframe">Issue NTE</a></li>';
			echo '<li><a href="'.$this->config->base_url().'evaluations/generateEvaluation/'.$row->empID.'/" class="iframe">Generate Evaluation</a></li>';
			echo '<li><a href="'.$this->config->base_url().'generatecis/'.$row->empID.'/" class="iframe">Generate CIS</a></li>';		
			echo '<li><a href="'.$this->config->base_url().'setcoach/'.$row->empID.'/" class="iframe">Set/Add as Coach</a></li>';
			echo '<li><a href="'.$this->config->base_url().'generatecoaching/'.$row->empID.'/" class="iframe">Generate Coaching Form</a></li>';
			echo '<li><a href="'.$this->config->base_url().'generatewrittenwarning/'.$row->empID.'/" class="iframe">Generate Written Warning</a></li>';
		}else if($this->user->is_coach==1 && $this->user->username != $row->username){
			echo '<li><a href="'.$this->config->base_url().'generatecoaching/'.$row->empID.'/" class="iframe">Generate Coaching Form</a></li>';
		}
		
		if((($content!='staffinfo' && $this->uri->segment(1)!='timecard') || ($content=='staffinfo' && $current=='myinfo')) && ($this->access->accessFullHR==true || $this->user->level>0)){
			echo '<li><a href="'.$this->config->base_url().'generatecode/" class="iframe">Generate Code</a></li>';
			echo '<li><a href="'.$this->config->item('career_url').'/jobrequisition.php" target="_blank">Request for Job Requisition</a></li>';		
			
		}
				
		if($this->access->accessFull==true)
			echo '<li><a href="'.$this->config->base_url().'adminsettings/'.$row->empID.'/" class="iframe">Other Settings</a></li>';

		//Kudos Request
		
		if( $this->user->username != $row->username && $this->user->level > 0 ){
			echo '<li><a href="'.$this->config->base_url().'kudosrequest/'.$row->empID.'/" class="iframe">Request Kudos Bunos</a></li>'; 
		}
	
		echo '<li><a href="'.$this->config->base_url().'reportviolation/" class="iframe">Report a COC Violation</a></li>';	
		echo '<li><a href="'.$this->config->base_url().'referafriend/" class="iframe" style="padding:15px 10px;"><b>Refer a Friend to Work in Tate</b></a></li>';
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

<?php	
}
?>
</div>




