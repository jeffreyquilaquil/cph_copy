<?php
require 'config.php';
require "includes/header.php";
?>
<div class="container">	
<?php
	$infoQuery = $db->selectQueryArray('SELECT jobReqData.*, org, dept, grp, subgrp, title, (SELECT count(*) FROM jobReqData AS j WHERE status=1 AND j.jobReqID= jobReqData.jobReqID) AS numHired FROM jobReqData	LEFT JOIN newPositions ON posID = positionID WHERE status = 0 ORDER BY dateSubmitted DESC');
	$closedReq = $db->selectQueryArray('SELECT jobReqID, dateSubmitted, requestor, org, dept, grp, subgrp, title, closedBy, dateClosed, appID, CONCAT(fname," ",lname) AS name FROM jobReqData LEFT JOIN newPositions ON positionID = posID LEFT JOIN applicants ON id=appID WHERE jobReqData.status = 1');
	$canceledReq = $db->selectQueryArray('SELECT jobReqID, dateSubmitted, requestor, org, dept, grp, subgrp, title, closedBy, dateClosed, cancelRemarks FROM jobReqData LEFT JOIN newPositions ON positionID = posID WHERE jobReqData.status = 2');
	$pooledQuery = $db->selectQueryArray('SELECT jobReqID, dateSubmitted, requestor, org, dept, grp, subgrp, title, closedBy, dateClosed, cancelRemarks, pooledBy, datePooled FROM jobReqData LEFT JOIN newPositions ON positionID = posID WHERE jobReqData.status = 3');
		
?>
	<ul class="tabs">
		<li class="tab-link current" data-tab="tab-1">Job Requisitions (<?= count($infoQuery) ?>)</li>
		<li class="tab-link" data-tab="tab-4">Pooled Requisitions (<?= count($pooledQuery) ?>)</li>
		<li class="tab-link" data-tab="tab-2">Closed Job Requisitions (<?= count($closedReq) ?>)</li>
		<li class="tab-link" data-tab="tab-3">Cancelled Job Requisitions (<?= count($canceledReq) ?>)</li>
	</ul>

	<div id="tab-1" class="tab-content current">
		<table id="jobrequests" cellpadding=5 cellspacing=5 width="100%">
			<thead style="border-bottom:1px solid #000;">
			<tr>
				<th>Job Requisition ID</th>
				<th>Date request is<br/>submitted (timestamp)</th>
				<th>Requestor</th>
				<th>Is this a new position?</th>
				<th>Organization</th>
				<th>Department</th>
				<th>Group</th>
				<th>Subgroup</th>
				<th>Name of Position</th>
				<th colspan=2>Status of requisition</th>
			</tr>
			</thead>
			<tbody>
			<?php	
				$ctn = 0;
				foreach($infoQuery AS $info){
					if($cnt%2==0) echo '<tr bgcolor="#dcdcdc">';
					else echo '<tr>';				
					if($info['status']==1) echo '<tr style="background-color:#E0EEE0;">';
					
					echo '	<td><a class="iframe" href="'.HOME_URL.'req-info.php?id='.$info['jobReqID'].'&no=head">'.$info['jobReqID'].'<a></td>
							<td>'.date('m/d/Y h:s', strtotime($info['dateSubmitted'])).'</td>
							<td>'.$info['requestor'].'</td>
							<td>'.ucfirst($info['requestType']).'</td>
							<td>'.$info['org'].'</td>
							<td>'.$info['dept'].'</td>
							<td>'.$info['grp'].'</td>
							<td>'.$info['subgrp'].'</td>
							<td><a href="applicant-matches.php?id='.$info['jobReqID'].'">'.$info['title'].'</a></td>
							<td>'.$info['numHired'].'/'.$info['num'].'</td>';
					if($info['status']==0){
							echo '<td>Open</td>';
					}else if($info['status']==2){
						echo '<td><span style="color:red;">Cancelled<span></td>';
					}else{
						echo '<td>Closed';
						if($info['appID']!=0){ echo '<br/><a href="/view_info.php?id='.$info['appID'].'"/>View info</a>'; }
						echo '</td>';
					}
					echo 	'</tr>';
					$cnt++;
				}
			?>
			</tbody>		
		</table>
	</div>
	<div id="tab-2" class="tab-content">
		<?php			
			if( count($closedReq) > 0 ){
		?>
		<table cellpadding=5 cellspacing=5 width="100%">
			<thead style="border-bottom:1px solid #000;">
			<tr>
				<th>Job Requisition ID</th>
				<th>Date request submitted</th>
				<th>Requestor</th>
				<th>Organization</th>
				<th>Department</th>
				<th>Group</th>
				<th>Subgroup</th>
				<th>Name of Position</th>
				<th>Closed By</th>
				<th>Date closed</th>
				<th>Hired Emp</th>
			</tr>
			</thead>
			<tbody>
			<?php
				$ccnt=0;
				foreach($closedReq AS $c){
					if($ccnt%2==0) echo '<tr bgcolor="#dcdcdc">';
					else echo '<tr>';	
					echo '<td><a class="iframe" href="'.HOME_URL.'req-info.php?id='.$c['jobReqID'].'&no=head">'.$c['jobReqID'].'<a></td>
							<td>'.date('m/d/Y h:s', strtotime($c['dateSubmitted'])).'</td>
							<td>'.$c['requestor'].'</td>
							<td>'.$c['org'].'</td>
							<td>'.$c['dept'].'</td>
							<td>'.$c['grp'].'</td>
							<td>'.$c['subgrp'].'</td>
							<td>'.$c['title'].'</td>
							<td>'.$c['closedBy'].'</td>
							<td>'.$c['dateClosed'].'</td>
							<td><a href="'.HOME_URL.'view_info.php?id='.$c['appID'].'" target="_blank">'.$c['name'].'</a></td>
						</tr>';
					$ccnt++;
				}
			?>			
			</tbody>
		</table>
		<?php }else{
			echo 'No closed requisitions.';
		} ?>
	</div>
	<div id="tab-3" class="tab-content">
		<?php
		if( count($canceledReq) > 0 ){
		?>
		<table cellpadding=5 cellspacing=5 width="100%">
			<thead style="border-bottom:1px solid #000;">
			<tr>
				<th>Job Requisition ID</th>
				<th>Date request submitted</th>
				<th>Requestor</th>
				<th>Name of Position</th>
				<th>Closed By</th>
				<th>Date Closed</th>
				<th width="40%">Reason</th>
			</tr>
			</thead>
			<tbody>
			<?php
				$cancnt=0;
				foreach($canceledReq AS $c){
					if($cancnt%2==0) echo '<tr bgcolor="#dcdcdc">';
					else echo '<tr>';	
					echo '<td><a class="iframe" href="'.HOME_URL.'req-info.php?id='.$c['jobReqID'].'&no=head">'.$c['jobReqID'].'<a></td>
							<td>'.date('m/d/Y h:s', strtotime($c['dateSubmitted'])).'</td>
							<td>'.$c['requestor'].'</td>
							<td>'.$c['title'].'</td>
							<td>'.$c['closedBy'].'</td>
							<td>'.$c['dateClosed'].'</td>
							<td>'.$c['cancelRemarks'].'</td>
						</tr>';
					$cancnt++;
				}
			?>			
			</tbody>
		</table>
		<?php }else{
				echo 'No cancelled requisitions.';
			} ?>
	</div>
<div id="tab-4" class="tab-content">
		<?php			
			if( count($pooledQuery) > 0 ){
		?>
		<table cellpadding=5 cellspacing=5 width="100%">
			<thead style="border-bottom:1px solid #000;">
			<tr>
				<th>Job Requisition ID</th>
				<th>Date request submitted</th>
				<th>Requestor</th>
				<th>Organization</th>
				<th>Department</th>
				<th>Group</th>
				<th>Subgroup</th>
				<th>Name of Position</th>
				<th>Pooled By</th>
				<th>Date pooled</th>
				<th>Hired Emp</th>
			</tr>
			</thead>
			<tbody>
			<?php
				$ccnt=0;
				foreach($pooledQuery AS $c){
					if($ccnt%2==0) echo '<tr bgcolor="#dcdcdc">';
					else echo '<tr>';	
					echo '<td><a class="iframe" href="'.HOME_URL.'req-info.php?id='.$c['jobReqID'].'&no=head">'.$c['jobReqID'].'<a></td>
							<td>'.date('m/d/Y h:s', strtotime($c['dateSubmitted'])).'</td>
							<td>'.$c['requestor'].'</td>
							<td>'.$c['org'].'</td>
							<td>'.$c['dept'].'</td>
							<td>'.$c['grp'].'</td>
							<td>'.$c['subgrp'].'</td>
							<td>'.$c['title'].'</td>
							<td>'.$c['pooledBy'].'</td>
							<td>';
							echo ( $c['datePooled'] != '0000-00-00 00:00:00' ) ? date('Y-m-d', strtotime($c['datePooled'])) : '0000-00-00';
							echo '</td>';
							echo '<td><a href="'.HOME_URL.'view_info.php?id='.$c['appID'].'" target="_blank">'.$c['name'].'</a></td>
						</tr>';
					$ccnt++;
				}
			?>			
			</tbody>
		</table>
		<?php }else{
			echo 'No closed requisitions.';
		} ?>
	</div>	
</div>
<script type="text/javascript">
	$(document).ready( function () {		
		$(".iframe").colorbox({iframe:true, width:"50%", height:"90%"});
		
		$('ul.tabs li').click(function(){
			var tab_id = $(this).attr('data-tab');

			$('ul.tabs li').removeClass('current');
			$('.tab-content').removeClass('current');

			$(this).addClass('current');
			$("#"+tab_id).addClass('current');
		});		
		
	});		
</script>
<?php 
	require "includes/footer.php";
?>