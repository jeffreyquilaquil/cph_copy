<h3>Below are the applicants who applied through the referral link of <?= strtoupper($row->lname.', '.$row->fname) ?></h3>
<hr/>
HR: Please process each profile and change the status of those paper screened out. Only those who pass the HR interview will be considered valid and eligible for Php 100.00 referral bonus.<br/><br/>
<table class="tableInfo datatable">
	<thead>
		<tr class="trhead">
			<td>ID</td>
			<td>Applicant Name</td>
			<td>Email Address</td>
			<td>Contact Number</td>
			<td>Position Applied</td>
			<td>Status</td>
			<td>MRB Status</td>
		</tr>
	</thead>
<?php
	foreach($queryReferrals AS $q){
		echo '<tr>';
			echo '<td><a onclick="parent.$.fn.colorbox.close();" target="_parent" href="'.$this->config->item('career_url').'/view_info.php?id='.$q->id.'">'.$q->id.'</a></td>';
			echo '<td>'.$q->fname.' '.$q->lname.'</td>';			
			echo '<td>'.$q->email.'</td>';
			echo '<td>'.$q->mnumber.'</td>';
			echo '<td>'.$q->title.'</td>';
			echo '<td>'.$q->processType.' <a href="'.$this->config->item('career_url').'/editstatus.php?id='.$q->id.'" class="iframe floatright"><img src="'.$this->config->item('career_url').'/images/edit_icon.png"></a></td>';
			echo '<td>';
				if(!is_null($q->mrbID)){
					if($q->mrbID==0) echo '<i>Pending</i>';
					else echo '<a href="'.$this->config->base_url().'referrralreleasedetails/'.$q->mrbID.'/">'.sprintf('%04d',$q->mrbID).'</a>';
				}
			echo '</td>';
		echo '</tr>';
	}
?>
</table>

<?php if(count($queryInvalid) > 0){ ?>
	<br/>
	<h3>Other (Invalid) Referrals</h3><hr/>
	<table class="tableInfo datatable">
		<thead>
			<tr class="trhead">
				<td>ID</td>
				<td>Applicant Name</td>
				<td>Email Address</td>
				<td>Contact Number</td>
				<td>Position Applied</td>
				<td>Status</td>
			</tr>
		</thead>
	<?php
		foreach($queryInvalid AS $q2){
			echo '<tr>';
				echo '<td><a onclick="parent.$.fn.colorbox.close();" target="_parent" href="'.$this->config->item('career_url').'/view_info.php?id='.$q2->id.'">'.$q2->id.'</a></td>';
				echo '<td>'.$q2->fname.' '.$q2->lname.'</td>';			
				echo '<td>'.$q2->email.'</td>';
				echo '<td>'.$q2->mnumber.'</td>';
				echo '<td>'.$q2->title.'</td>';
				echo '<td>';
					if(!empty($q2->processText)) echo $q2->processText;
					else echo $q2->processType;
				echo '</td>';
			echo '</tr>';
		}
	?>
	</table>
<?php } ?>
