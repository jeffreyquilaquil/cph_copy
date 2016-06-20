<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<script type='text/javascript' src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<style>
	.page-header{margin:0px;}
	.container{margin-left: -10px;}
</style>
<div class='container'>
<h1 class='page-header'>Kudos Bonus</h2><br/>
	<div class='step' data-step='1'>
		HR Note: <br/><br/>
		•Leaders may submit for any employee except themselves. It does not have to be the immediate superior. A leader may submit a kudos request for an employee of another department.<br/>
		•Prior to submitting this form, please evaluate the reason for which you are sending the rewards request.<br/>
		•Note that Kudos Bonus rewards request may be denied based on the validity of the reason given.<br/>
		•The basic principle is that Kudos Bonus – or any other reward – must be given to staff for exceeding expectations.<br/>
		<br/>
		Here is how Bonus requests are evaluated:<br/>
		Based on <a href='http://employee.tatepublishing.net/hr/gift-check-rewards/'>http://employee.tatepublishing.net/hr/gift-check-rewards/</a><br/>
		Is there praise and honor received from author (vs. thanks and appreciation)? YES (100)<br/>
		Is the praise and honor specific to the work of the rep? YES (100)<br/>
		Is there exceeding expectations? YES (100)<br/><br/>
	</div>
	<div class='step' data-step='2'>
		<div class="panel panel-default panel-danger">
			<div class="panel-heading">Requestor Details</div>
			<div class="panel-body">
				<div class='form-inline'>
		    		<div class="form-group">
					    <label for="requestorName">Name</label>
					    <input type='hidden' data-namekey='<?=$id?>' id='receiverID' />
					    <input type='hidden' data-supkey='<?=$supervisor?>' id='supervisorID' />
					    <input type="text" class="form-control" data-requestor='<?=$this->user->empID?>' id="requestorName" value='<?=$this->user->name?>' disabled>
					</div>
				</div>
				<br/>
				<div class='form-group'>
					<label for="requestorReason">Why are you giving Kudos Bonus to this employee?</label>
					<p><small>* Clearly state the reason why you are giving the employee gift checks. Make sure that this is clear. This will be the basis for approval of request.</small></p>
					<textarea name='requestorReason' title="This is required" data-toggle="tooltip" data-placement="top" id='requestorReason' class='form-control' placeholder="Enter reason here"></textarea>
				</div>
				<div class='form-group'>
					<label for="requestorAmount">How much Kudos Bonus are you requesting?</label>
					<p><small>* Follow number format: Php 1,000.00</small></p>
					<input type='number' id='requestorAmount' title="This is required" data-toggle="tooltip" data-placement="top" name='requestorAmount' class='form-control' min=0 placeholder="00.00" />
				</div>
	  		</div>
		</div>
	</div>
	<div class='step' data-step='3'>
		<h1 class='text-center'>Kudos Bonus Request Sent!</h1>
		<p class='text-center'>Thank you! Your immediate supervisor and HR has been notified of your request.</p>
		<p class='text-center'>Kudos Bonus requests shall be subject to your immediate supervisor and HR’s approval.</p>
		<p class='text-center'>You will be notified once Kudos Bonus Request has been fully processed.</p>
		<br/>
	</div>
	<div class='stepButtons pull-right'>
		<button type="button" data-step="back" class="btn btn-sm btn-danger back">< Back</button>
		<button type="button" data-step='next' class="btn btn-sm btn-primary next">Next ></button>
		<button type="button" data-step='submit' class="btn btn-sm btn-primary submit">Submit</button>
		<button type="button" id='cboxClose' class="btn btn-sm btn-default">Cancel</button>
	</div>
</div>
<script function='text/javascript'>
	$(document).ready(function(){
		var step = 1;

		showSteps(step);

		function showSteps(step){
			$('.step').each(function(){
				var s = $(this);
				var numStep = $(this).data('step');
				if(numStep == step){
					$(s).show();
				}
				else{
					$(s).hide();
				}

				if(step == 1){
					$('.back').hide();
					$('.next').show();
					$('.submit').hide();
				}
				else{
					$('.back').show();
					$('.next').hide();
					$('.submit').show();
				}

				if(step == 3){
					$('.back, .next, .submit').hide();
					$('#cboxClose').text('Close');
				}

			});
		}

		function goSubmitKudos(){
			//remove first validation notification if any.
			//red border for invalid fields
			$('.form-group').removeClass('has-error');

			//check for form values
			var receiver = $('#receiverID').data('namekey');
			var supervisor = $('#supervisorID').data('supkey');
			var id = $('#requestorName').data('requestor');
			var res = $('#requestorReason').val();
			var amount = $('#requestorAmount').val();
			var error = '';

			if(res == ''){
				error += '#requestorReason ';
			}
			if (error != '')
				error += ', ';
			if(amount == '' )
				error += '#requestorAmount ';

			//error fiels will have red borders
			$(error).parent().addClass('has-error');
			$(error).focus();
			$(error).tooltip('show');

			//if no errors then submit
			if(error == ''){
				//set up form data
				var formData = new FormData();

				formData.append('kudosRequestorID', id);
				formData.append('kudosReceiverID', receiver);
				formData.append('kudosReceiverSupID', supervisor);
				formData.append('kudosReason', res);
				formData.append('kudosAmount', amount);

				$.ajax({
					url: '<?php echo $this->config->base_url();?>submitKudosRequest',
					data: formData,
					processData: false,
					contentType: false,
					type: 'POST',
					success: function(e){
						showSteps(step+1);
					}
				});
			}
			else
				return false;
		}

		$('#cboxClose').click(function(){
			parent.$.colorbox.close();
		});
		$('.stepButtons button').click(function(){
			var nStep = $(this).data('step');
			if(nStep == 'next')
				step += 1;
			else if(nStep == 'submit'){
				goSubmitKudos(step);
			}
			else	
				step -= 1;

			showSteps(step);
		});


	});
</script>