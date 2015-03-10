<h2>Request for Certificate of Employment</h2>
<hr/>
<?php
	if(isset($inserted) && $inserted){
		echo '<p>Hello '.$row->fname.'!</p>';
		echo '<p>Your request for COE has been forwarded to HR.<br/>';
		echo 'HR shall validate the information in the COE and shall print and sign the COE.<br/>';
		echo 'Once the COE is validated, you will be informed to claim the document in the HR office.<br/>';
		echo 'In case there are any discrepancy in the information in your HR file, please notify HR right away by emailing <a href="mailto:hr.cebu@tatepublishing.net">hr.cebu@tatepublishing.net</a><br/>';
		echo '</p>';
		echo '<p>Thank you very much!<br/><br/>Best regards,<br/>Tate Publishing HR</p>';		
	}else{	
echo '<table class="tableInfo">';	

	if(isset($prevRequests) && count($prevRequests)>0){
		echo '<tr class="trhead"><td colspan=2>Previous Generated COE</td></tr>';
		foreach($prevRequests AS $p):
			echo '<tr><td>Issued last '.date('F d, Y', strtotime($p->dateissued)).'</td><td><a href="'.$this->config->base_url().'requestcoe/'.$p->coeID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td></tr>';
		endforeach;
		echo '<tr><td colspan=2><br/></td></tr>';
	}
?>

	<tr><td width="30%">Name of Employee</td><td class="weightbold"><?= $row->name ?></td></tr>
	<tr><td>Current Position Title</td><td class="weightbold"><?= $row->title ?></td></tr>
	<tr><td>Employee Status</td><td class="weightbold"><?= ucfirst($row->empStatus) ?></td></tr>
	<tr><td>Hire Date</td><td class="weightbold"><?= date('F d, Y',strtotime($row->startDate)) ?></td></tr>
	
	<form action="" method="POST" onSubmit="return validateForm()">
	<?php if($toupdate==false){ ?>
		<tr><td>Purpose of the Request</td><td><textarea class="forminput" name="purpose"></textarea></td></tr>
		<tr><td>Note for HR</td><td><textarea class="forminput" name="notesforHR"></textarea></td></tr>
		<tr><td><br/></td><td><input type="hidden" name="submitType" value="request"/><input type="submit" value="Request" class="padding5px"/></td></tr>
	<?php }else if($toupdate==true && $this->user->accessFullHR==true){ 
			if($row->endDate!='0000-00-00'){
				echo '<tr><td>Separation Date</td><td class="weightbold">'.date('F d, Y',strtotime($row->endDate)).'</td></tr>';
			}
			$sal = (double)str_replace(',','',$row->sal);
			$allowance = (double)str_replace(',','',$row->allowance);
			echo '<tr><td>Annual Salary</td><td class="weightbold">Php '.number_format(($sal*12),2).' <span class="weightnormal">(Php '.number_format($sal,2).' Monthly)</span></td></tr>';			
			echo '<tr><td>Annual Allowance</td><td class="weightbold">Php '.number_format(($allowance*12), 2).' <span class="weightnormal">(Php '.number_format($allowance,2).' Monthly)</span></td></tr>';
			
			echo '<tr><td>Date Requested</td><td class="weightbold">'.date('F d, Y', strtotime($row->daterequested)).'</td></tr>';			
			echo '<tr><td>Purpose of Request</td><td class="weightbold">'.$row->purpose.'</td></tr>';
			echo '<tr><td>Note to HR</td><td class="weightbold">'.$row->notesforHR.'</td></tr>';
			echo '<tr><td>Date of Issuance</td><td class="weightbold">'.date('F d, Y').'</td></tr>';
	?>		
		<tr><td><br/></td><td>
			<i>Please validate information before clicking Generate button. For discrepancies, update PT first before generating COE. Click <a href="javascript:void(0);" onClick="window.parent.location.href='<?= $this->config->base_url() ?>staffinfo/<?= $row->username ?>/'">here</a> to visit employee's profile.</i><br/>
			<input type="hidden" name="submitType" value="generate"/>
			<input type="submit" value="Generate COE" class="padding5px"/>
			<input type="button" value="Cancel" onClick="cancelCOE(<?= $row->coeID ?>)" class="padding5px"/>
		</td></tr>
	<?php } ?>
	</form>
	
</table>
<?php } ?>

<script type="text/javascript">
	function validateForm(){
		if($('input[type=submit]').val()=='Request' && $('textarea[name=purpose]').val().length==0){
			alert('Purpose of request is empty.');
			return false;
		}else{
			return true;
		}
	}
	
	function cancelCOE(id){ 
		$('<img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25px"/>').insertAfter('input[value=Cancel]');
		if(confirm('Are you sure you want to cancel this request? \nIf yes, please send a message after cancelling this request.')){
			$.post('<?= $this->config->item('career_uri') ?>', {submitType:'cancelRequest', coeID:id}, 
			function(){
				alert('Request has been cancelled.');
				parent.$.colorbox.close();
				parent.location.reload();
			});
		}
	}
</script>
