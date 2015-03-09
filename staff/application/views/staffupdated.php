<?php
if($edit==''){	
?>
	<ul class="tabs" style="text-align:right;">
		<li class="tab-link current" data-tab="tab-1">Personal and Job Details Update Requests<?= ((count($row)>0)?' ('.count($row).')':'')?></li>
		<li class="tab-link" data-tab="tab-2">Request to Recheck Leave Credits<?= ((count($rowLeave)>0)?' ('.count($rowLeave).')':'')?></li>
	</ul>
	<hr/>
	
	<div id="tab-1" class="tab-content current">
<?php if(count($row)==0){ echo 'No records found.'; }else{ ?>
	<table class="tableInfo">
		<thead>
		<tr>
			<td width="150px">Employee Name</td>
			<td>Updated Field</td>
			<td>Current Value</td>
			<td>Requested Update</td>
			<td>Date Update Requested</td>
			<td width="250px;">Notes</td>
			<td><br/></td>
		<tr>
		</thead>	
	<?php
		foreach($row AS $r){		
			$f = $r->fieldname;
			echo '
				<tr>
					<td><a href="'.$this->config->base_url().'staffinfo/'.$r->username.'/">'.$r->fname.' '.$r->lname.'</a></td>
					<td>'.$this->staffM->defineField($r->fieldname).'</td>
					<td>';
						if($f=='sal' || $f=='allowance')
							echo 'Php '.$r->$f;
						else if($f=='bankAccnt' || $f=='hmoNumber')
							echo $this->staffM->decryptText($r->$f);
						else if($f=='levelID_fk')
							echo $r->levelName;
						else if($f=='terminationType')
							echo $this->staffM->infoTextVal($f, $r->$f);
						else
							echo $r->$f;
						
					echo '</td>
					<td style="color:red;">'.$this->staffM->infoTextVal($r->fieldname, $r->fieldvalue).'</td>';
					
				echo '<td>'.date('d M Y, h:i a', strtotime($r->daterequested)).'</td>
					<td>'.$r->notes.'</td>
					<td>
						<ul class="dropmenu">
							<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" style="cursor:pointer;"/>
								<ul class="dropleft">';
								
								if($r->isJob==1 && $r->fieldname!='accessEndDate' && $r->fieldname!='endDate')
									echo '<li><a href="'.$this->config->base_url().'generatecis/'.$r->empID_fk.'/'.$r->updateID.'/" class="iframe">Generate CIS</a></li>';
								
							echo '<li><a href="javascript:void(0)" onClick="stars(\'Update\', '.$r->updateID.', \''.$r->empID_fk.'\', \''.$r->fieldname.'\', \''.$r->fieldvalue.'\')">Approve Request</a></li>';
							
							echo 	'<li><a class="iframe" href="'.$this->config->base_url().'staffupdated/addnote/'.$r->updateID.'/">Add Note</a></li>
									<li><a class="iframe" href="'.$this->config->base_url().'supportingdocs/'.$r->empID_fk.'/">Supporting Documents</a></li>
									<li><a class="iframe" href="'.$this->config->base_url().'sendEmail/'.$r->empID_fk.'/">Send Email</a></li>
									<li><a class="iframe" href="'.$this->config->base_url().'staffupdated/disapprove/'.$r->updateID.'/">Disapprove Request</a></li>
								</ul>
							</li>
						</ul>
					</td>
				</tr>
			';
		}
		echo '</table>';
	}
	?>
	</div>	
	<div id="tab-2" class="tab-content">
	<?php if(count($rowLeave)==0){ echo 'No records found.'; }else{ ?>
		<table class="tableInfo">
		<thead>
			<tr>
				<td>Employee Name</td>
				<td>Date Request Sent</td>
				<td width="60%;">Notes</td>
				<td width="50px"><br/></td>
			<tr>
		</thead>
		<?php
			foreach($rowLeave AS $r){
				echo '<tr>
						<td><a href="'.$this->config->base_url().'staffinfo/'.$r->username.'/">'.$r->fname.' '.$r->lname.'</a></td>
						<td>'.date('d M Y, H:i', strtotime($r->daterequested)).'</td>
						<td>'.$r->notes.'</td>
						<td>
							<ul class="dropmenu">
								<li><img src="'.$this->config->base_url().'css/images/settings-icon.png" style="cursor:pointer;"/>
									<ul class="dropleft">
										<li><a class="iframe" href="'.$this->config->base_url().'staffupdated/addnote/'.$r->updateID.'/">Add Note</a></li>
										<li><a class="iframe" href="'.$this->config->base_url().'staffupdated/sendemailleave/'.$r->updateID.'/'.$r->empID_fk.'/">Send Email</a></li>
										<li><a class="iframe" href="'.$this->config->base_url().'staffupdated/closerequest/'.$r->updateID.'/'.$r->empID_fk.'/">Close Request</a></li>
									</ul>
								</li>
							</ul>
						</td>
					<tr>';
			}
		?>
		</table>
	<?php } ?>
	</div>
	
<?php
}else if($edit=='addnote'){
	echo '<h2>Add Note</h2><hr/>';
	if($success){
		echo '<p class="errortext">Note has been added. Please refresh parent page.</p>';
	}else{
?>
	<form action="" method="POST" onSubmit="return validform('Note is empty.');">
	<table class="tableInfo">
		<tr><td><textarea class="forminput" name="notes" id="notes" rows=10></textarea></td></tr>
		<tr><td>
			<input type="submit" value="Add"/>
			<input type="hidden" name="updateID" value="<?= $updateID ?>"/>
			<input type="hidden" name="submitType" value="addnote"/>
		</td></tr>
	</table>
	</form>
<?php }
}else if($edit=='disapprove'){
	echo '<h2>Why do you want to disapprove the request?</h2><hr/>';
	if($success){
		echo '<p class="errortext">Note has been added. Please refresh parent page.</p>';
	}else{
 ?>
	<form action="" method="POST" onSubmit="return validform('Reason is empty');">
	<table class="tableInfo">
		<tr><td><textarea class="forminput" name="notes" id="notes" rows=10></textarea></td></tr>
		<tr><td>
			<input type="submit" value="Submit and Disapprove the Request"/>
			<input type="hidden" name="updateID" value="<?= $updateID ?>"/>
			<input type="hidden" name="empID_fk" value="<?= $row->empID_fk ?>"/>
			<input type="hidden" name="fieldN" value="<?= $row->fieldname ?>"/>
			<input type="hidden" name="fieldV" value="<?= $row->fieldvalue ?>"/>
			<input type="hidden" name="submitType" value="disapprove"/>
		</td></tr>
	</table>
	</form>
<?php
	} 
}else if($edit=='sendemailleave'){
?>
	<h2>Send Email</h2><hr/>
	<table class="tableInfo">
		<tr><td width="20%">Email Address</td><td><input class="forminput" type="text" id="email" value="<?= $email ?>" disabled="disabled"/></td></tr>
		<tr><td width="20%">Subject</td><td><input class="forminput" type="text" id="subject" value="CareerPH: Update on recheck leave request"/></td></tr>
		<tr><td width="20%">Message</td><td><textarea class="forminput" id="message" rows=10></textarea></td></tr>
		<tr><td colspan=2><button onClick="sendClose('send')">Send Email</button>&nbsp;&nbsp;<button onClick="sendClose('sendclose')">Send and Close Request</button></td></tr>
	</table>
<?php	
}else if($edit=='closerequest'){ ?>
	<h2>Close Request</h2><hr/>
	<table class="tableInfo">
		<tr><td width="20%">Add Note</td><td><textarea class="forminput" id="note" rows=10></textarea></td></tr>
		<tr><td colspan=2><button id="closeRequest">CLose Request</button>&nbsp;&nbsp;<button onClick="parent.$.colorbox.close()">Cancel</button></td></tr>
	</table>
<?php }?>

<script type="text/javascript">
	$(function(){
		$('#closeRequest').click(function(){
			if($('#note').val()==''){
				alert('Note is empty.');
			}else{
				displaypleasewait();
				$.post('<?= $this->config->base_url() ?>staffupdated/',{
					submitType:'requestclose',
					note:$('#note').val(),
					empID: '<?= $this->uri->segment(4) ?>',
					updateID: '<?= $updateID ?>'
				},function(){
					alert('Request has been closed.');
					parent.$.colorbox.close();
					parent.location.reload();
				});
			}
		});
	});

	function sendClose(gollum){
		thorin = '';
		if($('#subject').val()=='') thorin += 'Subject is empty.\n';
		if($('#message').val()=='') thorin += 'Message is empty.\n';

		if(thorin!=''){ 
			alert(thorin);
		}else{
			displaypleasewait();
			if(gollum=='send'){
				$.post('<?= $this->config->base_url() ?>staffupdated/',{
					submitType: 'sendEmail',
					email: $('#email').val(),
					subject: $('#subject').val(),
					message: $('#message').val(),
					empID: '<?= $this->uri->segment(4) ?>',
					updateID: '<?= $updateID ?>'
				},function(){
					alert('Message has been sent.');
					parent.$.colorbox.close();
				});
			}else{
				$.post('<?= $this->config->base_url() ?>staffupdated/',{
					submitType: 'sendEmailClose',
					email: $('#email').val(),
					subject: $('#subject').val(),
					message: $('#message').val(),
					empID: '<?= $this->uri->segment(4) ?>',
					updateID: '<?= $updateID ?>'
				},function(){
					alert('Message has been sent and close.');
					parent.$.colorbox.close();
					parent.location.reload();
				});
			}
		}
	}
	
	
	function stars(type, id, emp, f, fv){
		displaypleasewait();
		$.post("<?= $this->config->item('career_uri') ?>",{
			submitType:type,
			updateID:id,
			empID:emp,
			fieldN:f,
			fieldV:fv
		},function(){
			if(type=='Update')
			alert('Request has been updated.');
			location.reload();
		});
	}
	
	function validform(err){
		if($('#notes').val()==''){
			alert(err);
			return false;
		}else{
			return true;
		}
	}
</script>
