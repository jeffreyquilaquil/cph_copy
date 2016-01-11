<?php
	if($contentpage==''){
		echo '<h2>Written Warning Management</h2><hr/>';

		if(count($dataQuery)==0){
			echo '<p class="errortext">No record yet.</p>';
		}else{
	?>
			<table class="tableInfo">
				<tr class="trlabel">
					<td width="150px">Employee Name</td>
					<td width="130px">Date Generated</td>
					<td>Offense Committed</td>
					<td>Offense Level</td>
					<td>Status</td>
					<td width="50px">Details</td>
				</tr>
			<?php
				foreach($dataQuery AS $june){
					echo '<tr ';
						if($june->wrStatus==2 || $june->wrStatus==7) echo 'class="bggray"';
						else if($june->wrStatus==4) echo 'bgcolor="yellow"';
					echo '>';
						echo '<td><a href="'.$this->config->base_url().'staffinfo/'.$june->username.'/" target="_blank">'.$june->name.'</a></td>';
						echo '<td>'.date('M d, Y h:i a', strtotime($june->dateissued)).'</td>';
						echo '<td>'.$june->offense.'</td>';
						echo '<td align="center">'.$june->level.'</td>';
						echo '<td>';
							if($june->wrStatus==4) echo '<a href="'.$this->config->base_url().'writtenwarning/'.$june->nteID.'/deliberate/" class="iframe">'.$wrStatusArr[$june->wrStatus].'</a>';
							else echo $wrStatusArr[$june->wrStatus];
						echo '</td>';
						echo '<td align="right"><a href="'.$this->config->base_url().'writtenmanagement/'.$june->nteID.'/" class="iframe"><img src="'.$this->config->base_url().'css/images/view-icon.png"></a></td>';
					echo '</tr>';
				}
			?>
			</table>
<?php
		}
	}else{ ///DETAILS PAGE
?>
		<table class="tableInfo">
			<tr class="trlabel"><td colspan=2>Written Warning Info</td></tr>
			<tr class="trhead"><td class="weightbold">Written Warning Status</td><td><?= $wrStatusArr[$info->wrStatus] ?></td></tr>
			<tr><td width="20%" class="weightbold">NTE ID</td><td><?= $info->nteID ?></td></tr>
			<tr><td class="weightbold">Employee Name</td><td><?= $info->name ?></td></tr>
			<tr><td class="weightbold">Offense Category</td><td><?= $info->category ?></td></tr>
			<tr><td class="weightbold">Offense</td><td><b>Level <?= $info->level.'</b>: '.$info->offense ?></td></tr>			
			<tr><td class="weightbold">Offense Level</td><td><?= $info->offenselevel ?></td></tr>
			<tr><td class="weightbold">Issued By</td><td><?= $info->supName ?></td></tr>
			<tr><td class="weightbold">Date Issued</td><td><?= date('M d, Y h:i a', strtotime($info->dateissued)) ?></td></tr>
			<tr><td class="weightbold">Details of the incident</td><td><?= nl2br($info->wrDetails) ?></td></tr>
		<?php
			if(!empty($info->wrEdited)) echo '<tr><td class="weightbold">Employee\'s Revision</td><td>'.nl2br($info->wrEdited).'</td></tr>';
			if(!empty($info->wrResponse)) echo '<tr><td class="weightbold">Employee\'s Response</td><td>'.nl2br($info->wrResponse).'</td></tr>';
			if(!empty($info->wrDeliberation)) echo '<tr><td class="weightbold">HR\'s Deliberation</td><td>'.nl2br($info->wrDeliberation).'</td></tr>';
		
			if($info->wrStatus==7){
				
			}else{
				echo '<tr><td class="weightbold">NTE Form</td><td><a href="'.$this->config->base_url().'writtenmanagement/'.$info->nteID.'/pdf/" target="_blank"><img src="'.$this->config->base_url().'css/images/pdf-icon.png"></a></td></tr>';
				echo '<tr><td class="weightbold">Upload Signed Form</td><td>';
					echo $this->textM->formfield('file', 'writtenFile');
				echo '</td></tr>';
			}
		?>
			
		</table>
<?php
	}
?>