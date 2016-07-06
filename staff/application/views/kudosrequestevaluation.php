<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<style>
	.page-header{margin:0px;}
	.container{margin-left: -10px;}
	.form-horizontal{margin-left: 10px;}
	#colorboxcontainer{width:100%;}
	.container{width:100%;}
	.fa-spin {
	  -webkit-animation: fa-spin 2s infinite linear;
	  animation: fa-spin 2s infinite linear;
	}
	@keyframes fa-spin {
	  0% {
	    -webkit-transform: rotate(0deg);
	    transform: rotate(0deg);
	  }
	  100% {
	    -webkit-transform: rotate(359deg);
	    transform: rotate(359deg);
	  }
	}
</style>
<div class='container'>
	<input type='hidden' id='evalStatus' value='<?=$kudosRequestStatus?>' />
	<input type='hidden' id='evalID' value='<?=$requestID?>' />
	<input type='hidden' id='evalAns' value='<?=$kudosEvaluation?>' />
	<input type='hidden' id='evalReceiver' value='<?=$evaluationContent[0]['kudosReceiverID']?>' />

	<h3 class='page-header'>Kudos Bonus Request from <?=$evaluationContent[0]['requestor']?></h3><br/>
	<?php
		if($evaluationContent[0]['re_evaluation'] == 1){
			echo '<div class="alert alert-danger" role="alert"><strong>Please re-evaluate the request.</strong></div>';
		}
	?>
	<!-- Preview -->
	<div class="panel panel-default panel-danger">
		<div class="panel-heading">Requestor Details</div>
		<div class="panel-body">
			<p><strong>Reason of Kudos Bonus.</strong></p>
			<P><?=str_replace("\n",'<br/>',str_replace("\r\n", "<br/>", $evaluationContent[0]['kudosReason'])); ?></P>
			<p><strong>Recommendation amount of Kudos Bonus.</strong></p> 
			<input type='number' id='requestorAmount' value="<?=$evaluationContent[0]['kudosAmount']?>" title="This is required" data-toggle="tooltip" data-placement="top" name='requestorAmount' class='form-control' min=0 placeholder="00.00" />
		</div>
	</div>
	<!-- End of preview -->

	<div class='form-horizontal'>
		<div class='form-group'>
			Praise and honor received from author or supervisor?
			<div>
				<label>
		          <input type="radio" name='eval1' value='yes'> Yes
		        </label>
		        <label>
		          <input type="radio" name='eval1' value='no' checked="checked"> No
		        </label>
		    </div>
		</div>
		<div class='form-group'>
			Is the Kudos specific to cover design? 
			<div>
				<label>
		          <input type="radio" name='eval2' value='yes'> Yes
		        </label>
		        <label>
		          <input type="radio" name='eval2' value='no'  checked="checked"> No
		        </label>
		    </div>
		</div>
		<div class='form-group'>
			Did the cover design have one(1) or zero (0) revisions? 
			<div>
				<label>
		          <input type="radio" name='eval3' value='yes'> Yes
		        </label>
		        <label>
		          <input type="radio" name='eval3' value='no'  checked="checked"> No
		        </label>
		    </div>
		</div>
		<div class='form-group'>
			Is the praise specifically due to employee’s work?
			<div>
				<label>
		          <input type="radio" name='eval4' value='yes'> Yes
		        </label>
		        <label>
		          <input type="radio" name='eval4' value='no'  checked="checked"> No
		        </label>
		    </div>  
		</div>
		<div class='form-group'>
			Did it exceed expectations?(e.g I am blown away, impressive)
			<div>
				<label>
		          <input type="radio" name='eval5' value='yes'> Yes
		        </label>
		        <label>
		          <input type="radio" name='eval5' value='no' checked="checked"> No
		        </label>
		    </div>  
		</div>
	</div>
	<div class='form-group' id='disapprovedEvaluation' hidden>
		<label for='reasonForDisapproving'>Reason for disapproving</label>
		<input id='reasonForDisapproving' type='text' title="A valid reason is required" data-toggle="tooltip" class='form-control' />
	</div>
	<button type='button' class='btn btn-sm btn-danger' data-eval='disapproved'>Disapproved</button>
	<?php 
		$btn = "Request APPROVED, queue for HR's Evaluation";
		if($kudosRequestStatus == 2){
			$btn = "Request APPROVED, queue for Accounting's Approval";
		}
		else if($kudosRequestStatus == 3){
			$btn = 'Approved';
		}
	?>
	<div class='payslipSettings'>
		<br/>
		<div class="panel panel-default panel-success">
			<div class="panel-heading">Payslip Settings Details</div>
			<div class="panel-body">
				<table class='table table-stripped'>
					<tr><td>Item Name</td><td><input type='text' class='form-control' value='Kudos Bonus' disabled /></td></tr>
					<tr><td>Item Group</td><td><input type='text' data-value='0' class='form-control' value='Additional Item' disabled /></td></tr>
					<tr><td>Item Category</td><td><input type='text' data-value='5' class='form-control' value='Bonus' disabled /></td></tr>
					<tr><td>Item Type</td><td><input type='text' data-value='credit' class='form-control' value='Credit' disabled /></td></tr>
					<tr><td>Added to</td><td><input type='text' data-value='net' class='form-control' value='Net' disabled ></td></tr>
					<tr><td>Item amount</td><td>Specific Amount:<input type='text' data-value='specific amount' class='form-control' value='<?=$evaluationContent[0]['kudosAmount']?>' disabled ></td></tr> 
					<tr><td>Pay Period</td><td>Once:<input type='text' data-value='once' id='payPeriod' class='form-control'></td></tr>
					<tr><td>Status</td><td><input type='text' data-value='1' class='form-control' value='Active' disabled /></td></tr>
				</table>
				<!--<p><strong>Reason of Kudos Bonus.</strong></p>
				<P><?=$evaluationContent[0]['kudosReason']?></P>
				<p><strong>Recommendation amount of Kudos Bonus.</strong></p> 
				<input type='number' id='requestorAmount' value="<?=$evaluationContent[0]['kudosAmount']?>" title="This is required" data-toggle="tooltip" data-placement="top" name='requestorAmount' class='form-control' min=0 placeholder="00.00" />-->
			</div>
		</div>
	</div>
	<button type='button' class='btn btn-sm btn-success' data-eval='approved'><?=$btn?></button>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		//initialize evaluation
		var evaluations = $('#evalAns').val();
		$('.payslipSettings').hide();
		var appClicked = 0;
		initEval(evaluations);
		$('#payPeriod').datetimepicker({ format:'Y-m-d', timepicker:false });

		var formData = new FormData();

		function initEval(evaluations){
			var split = evaluations.split('|');
			if(evaluations != ''){
				for(var i = 1; i < 6; i++){
					$('input[name="eval'+i+'"]').each(function(){
						if( $(this).val() == split[i-1] )
							$(this).attr('checked','checked');
					});
				}
			}
		}

		function ajaxSubmit(formData){
			$.ajax({
				url: '<?php echo $this->config->base_url();?>submitKudosRequest',
				data: formData,
				processData: false,
				contentType: false,
				type: 'POST',
				success: function(e){
					alert(e);
					parent.location.reload(true);
					parent.$.colorbox.close();
				}
			});
		}
		function addSpin(a){
			$(a).append(' <span class="fa-spin glyphicon glyphicon-refresh" aria-hidden="true"></span>');
			$(a).attr('disabled','disabled');
		}

		$('button').click(function(){
			$eval = $(this).data('eval');
			
			formData.append('submitType', 'updateStatus');
			formData.append('kudosRequestID', $('#evalID').val());

			//remove has-error class
			$('.has-error').removeClass('has-error');

			//disapproved button
			if($eval == 'disapproved'){
				$("#disapprovedEvaluation").show();
				//check if remarks is not emtpy
				var remark = $('#reasonForDisapproving').val();
				if(remark != ''){
					//append to formdata
					formData.append('kudosRequestStatus', 5);
					formData.append('reasonForDisapproving', $('#reasonForDisapproving').val() );
					formData.append('re_evaluation', 0);
					//add spinning effect
					addSpin($(this));

					ajaxSubmit(formData);
				}
				else{
					$('#disapprovedEvaluation').addClass('has-error');
					$('#reasonForDisapproving').focus();
					$('#reasonForDisapproving').tooltip('show');
				}
			}else{
				if(appClicked < 1 && $(this).text() == 'Approved'){
					appClicked = 1;
					//if approved is clicked for the first time show payslip settings details
					$('.payslipSettings').show();
					//append payment settings
					//payStaffID	empID_fk	payID_fk 	payAmount	payPeriod	payStart	payEnd	status
					formData.append('empID_fk', $('#evalReceiver').val() );
					formData.append('payID_fk', 15 );
					formData.append('payAmount', $('#requestorAmount').val() );
					formData.append('payPeriod', 'once');
					formData.append('status', 1);

				}else{
					var t = true;
					var evalStatus = parseInt($('#evalStatus').val());
					var ans = '';
					$('input[type="radio"]:checked').each(function(){
						ans += $(this).val();
						ans += '|';
					});
					formData.append('kudosRequestStatus', evalStatus+=1);
					formData.append('kudosEvaluation', ans);
					formData.append('re_evaluation', 0);
					formData.append('kudosAmount',$('#requestorAmount').val());

					for(var pair of formData.entries()) {
					    console.log(pair[0]+ ', '+ pair[1]); 
					}

					if(appClicked == 1){ 
						if($('#payPeriod').val() != ''){
							formData.append('payStart', $('#payPeriod').val() );
							formData.append('payEnd', $('#payPeriod').val() );
						}
						else{
							alert('Please input Pay Period');
							t = false;
						}
					}
					if(t){
						addSpin($(this));
						ajaxSubmit(formData);
					}
				}
			}
		});
	});
</script>
