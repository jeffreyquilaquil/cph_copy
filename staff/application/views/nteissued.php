<h2>NTE Issued</h2>
<hr/>
<?php
$cntAllActive = count($allActive);
if($this->access->accessFullHR==true){
	$cntPendingPrint = count($pendingPrint);
	$cntPendingUpload = count($pendingUpload);
}
?>

<?php if($this->access->accessFullHR==true){ ?>
	<ul class="tabs">
		<li class="tab-link current" data-tab="pendingprinttab">Pending for Printing (<span id="cntPendingPrint"><?= $cntPendingPrint ?></span>)</li>
		<li class="tab-link" data-tab="pendinguploadtab">Pending for Upload (<span id="cntPendingUpload"><?= $cntPendingUpload ?></span>)</li>
		<li class="tab-link" data-tab="allactivetab <?= (($this->access->accessFullHR==false)?'current':'')?>">All Active NTE's (<span id="cntPendingUpload"><?= $cntAllActive ?></span>)</li>
	</ul>
<?php } ?>	


<?php if($this->access->accessFullHR==true){ ?>
	<div id="pendingprinttab" class="tab-content current">
	<?php
		if($cntPendingPrint==0){
			echo '<br/>No pending NTE for printing.';
		}else{
	?>
	<table class="tableInfo">
		<tr class="trhead">
			<td>Employee</td>
			<td>NTE Type</td>
			<td>Level of Offense</td>
			<td>Date Issued</td>
			<td>Issued By</td>
			<td>Type</td>
			<td>File</td>
			<td>Printed</td>
		</tr>
	<?php
		foreach($pendingPrint AS $n):
			echo '
				<tr id="trpendingprint_'.$n->nteID.'">
					<td>'.$n->name.'</td>
					<td>'.ucfirst($n->type).'</td>
					<td>'.$this->textM->ordinal($n->offenselevel).' Offense</td>
					<td>'.date('F d, Y', strtotime($n->dateissued)).'</td>
					<td>'.$n->issuerName.'</td>
					<td>'.(($n->status==1)?'NTE':'CAR').'</td>
					<td><a class="iframe" href="'.$this->config->base_url().'ntepdf/'.$n->nteID.'/"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"/></a></td>
					<td><input type="checkbox" onClick="ntePrinted('.$n->nteID.', this)"/></td>
				</tr>
			';
		endforeach;
	?>
	</table>
	<?php
		}
	?>
	</div>


	<div id="pendinguploadtab" class="tab-content">
	<?php
		if($cntPendingUpload==0){
			echo '<br/>No pending NTE for upload. If counter above is not 0 please refresh the page.';
		}else{
	?>
	<table class="tableInfo">
		<tr class="trhead">
			<td>Employee</td>
			<td>NTE Type</td>
			<td>Level of Offense</td>
			<td>Date Issued</td>
			<td>Issued By</td>
			<td>Printed By</td>
			<td>Date Printed</td>
			<td>Type</td>
			<td>Upload</td>
		</tr>
	<?php
		foreach($pendingUpload AS $u):
			if($u->status==1)
				$whoprinted = explode('|', $u->nteprinted);
			else
				$whoprinted = explode('|', $u->carprinted);
			echo '
				<tr id="trnteupload_'.$u->nteID.'">
					<td>'.$u->name.'</td>
					<td>'.ucfirst($u->type).'</td>
					<td>'.$this->textM->ordinal($u->offenselevel).' Offense</td>
					<td>'.date('F d, Y', strtotime($u->dateissued)).'</td>
					<td>'.$u->issuerName.'</td>
					<td>'.$whoprinted[0].'</td>
					<td>'.date('F d, Y',strtotime($whoprinted[1])).'</td>
					<td>'.(($u->status==1)?'NTE':'CAR').'</td>
					<td><input type="button" value="Upload" onClick="nteUpload('.$u->nteID.')"/></td>
				</tr>
			';
		endforeach;
		
	?>
	</table>
	<form id="signNTE" action="" method="POST" enctype="multipart/form-data" class="hidden">
		<input type="file" id="signedFile" name="signedFile"/>
		<input type="hidden" id="nteID" name="nteID"/>
		<input type="hidden" name="submitType" value="signedNTE"/>					
	</form>

	<?php
		}
	?>
	</div>

<?php } //end of code if user is not full or HR?>

<br/>
<div id="allactivetab" class="tab-content <?= (($this->access->accessFullHR==false)?'current':'')?>">
<?php
	if($cntAllActive==0){
		echo '<br/>No NTE records.';
	}else{
?>
<table class="tableInfo datatable">
<thead>
	<tr class="trhead">
		<td>Employee</td>
		<td>NTE Type</td>
		<td>Level of Offense</td>
		<td>Date Issued</td>
		<td>Issued By</td>
		<td>Sanction</td>
		<td>Details</td>
	</tr>
</thead>
<?php
	foreach($allActive AS $a):
		echo '
			<tr id="trpendingprint_'.$a->nteID.'">
				<td>'.$a->name.'</td>
				<td>'.ucfirst($a->type).'</td>
				<td>'.$this->textM->ordinal($a->offenselevel).' Offense</td>
				<td>'.date('F d, Y', strtotime($a->dateissued)).'</td>
				<td>'.$a->issuerName.'</td>
				<td>'.(($a->status==3)?'None. Response Accepted':$a->sanction).'</td>
				<td><a class="iframe" href="'.$this->config->base_url().'detailsNTE/'.$a->nteID.'/"><img src="'.$this->config->base_url().'css/images/view-icon.png"></a></td>
			</tr>
		';
	endforeach;
?>
</table>
<?php
	}
?>
</div>

<script type="text/javascript">
	$(function(){
		$('#signedFile').change(function(){
			$('#trnteupload_'+$('#nteID').val()+' td:last').html('<img src="<?= $this->config->base_url() ?>css/images/small_loading.gif" width="25"/>');
			$('#signNTE').submit();
		});
	});

	function ntePrinted(id, ths){
		if($(ths).is(':checked')){
			if(confirm('Are you sure you already printed the document?')){				
				$('#trpendingprint_'+id+' td:last').html('<img src="<?= $this->config->base_url().'css/images/small_loading.gif' ?>" width="25"/>');
				$.post('<?= $this->config->item('career_uri') ?>',{submitType:'nteprinted',nteID:id},
				function(){
					$('#trpendingprint_'+id).hide();
					location.reload();
				}); 
			}else{
				$(ths).prop('checked', false); 
			}
		}
	}
	
	function nteUpload(id){
		$('#signedFile').trigger('click');
		$('#nteID').val(id);		
	}
	
</script>

