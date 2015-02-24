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
		echo '<tr><td colspan=2><b>Previous Generated COE</b></td></tr>';
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
	<tr><td>Separation Date</td><td class="weightbold"><?= (($row->endDate=='0000-00-00')?'N/A':date('F d, Y', strtotime($row->endDate))) ?></td></tr>	
	<tr><td>Monthly Basic Salary</td><td class="weightbold"><?= 'Php '.$row->sal ?></td></tr>
	
	<form action="" method="POST">
	<?php if($toupdate==false){ ?>
		<tr><td>Note to HR</td><td><textarea class="forminput" name="note"></textarea></td></tr>
		<tr><td><br/></td><td><input type="hidden" name="submitType" value="request"/><input type="submit" value="Request COE"/></td></tr>
	<?php }else if($toupdate==true && count(array_intersect($this->myaccess,array('full','hr')))>0){ ?>
		<tr><td>Date of Issuance</td><td><input type="text" name="dateissued" class="forminput datepick" value="<?= date('F d, Y') ?>"/></td></tr>
		<tr><td><br/></td><td>
			<i>Please validate information before clicking Generate button. For discrepancies, update PT first before generating COE. Click <a href="javascript:void(0);" onClick="window.parent.location.href='<?= $this->config->base_url() ?>staffinfo/<?= $row->username ?>/'">here</a> to visit employee's profile.</i><br/>
			<input type="hidden" name="submitType" value="generate"/>
			<input type="submit" value="Generate COE" onClick="displaypleasewait();"/>
		</td></tr>
	<?php } ?>
	</form>
	
</table>
<?php } ?>
